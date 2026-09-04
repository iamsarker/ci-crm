<?php 
class Invoice_model extends CI_Model{

	function __construct(){
		parent::__construct();
		$this->load->database();
	}

	function getDataTableRecords($sqlQuery, $bindings) {
		$data = $this->db->query($sqlQuery, $bindings);
		return $data->result_array();
	}

	function countDataTableTotalRecords() {
		// SECURITY: bypasses the $where that ssp_sql_query() scopes — see the
		// matching note in Order_model::countDataTableTotalRecords().
		$scope = adminScopeSql('company_id');
		$scope = ($scope !== '') ? " AND {$scope}" : '';
		$query = $this->db->query("select count(id) as cnt from invoice_view where status=1 {$scope}");
		$data = $query->result_array();
		return !empty($data) ? $data[0]['cnt'] : 0;
	}

	function countDataTableFilterRecords($where, $bindings) {
		$query = $this->db->query("select count(id) as cnt from invoice_view $where", $bindings);
		$data = $query->result_array();
		return !empty($data) ? $data[0]['cnt'] : 0;
	}

	/**
	 * Get invoice statistics for dashboard cards
	 *
	 * @return array Stats including total, paid, due counts and total amount
	 */
	function getInvoiceStats() {
		// SECURITY: total_amount is platform-wide billings. Same leak shape as
		// Order_model::getOrderStats() — scoped here, not by the caller.
		$scope = adminScopeSql('company_id');
		$scope = ($scope !== '') ? " AND {$scope}" : '';
		$query = $this->db->query("
			SELECT
				COUNT(*) as total_invoices,
				SUM(CASE WHEN pay_status = 'PAID' THEN 1 ELSE 0 END) as paid_invoices,
				SUM(CASE WHEN pay_status = 'DUE' THEN 1 ELSE 0 END) as due_invoices,
				COALESCE(SUM(total), 0) as total_amount
			FROM invoice_view
			WHERE status = 1 {$scope}
		");
		$data = $query->row_array();
		return array(
			'total_invoices' => intval($data['total_invoices'] ?? 0),
			'paid_invoices' => intval($data['paid_invoices'] ?? 0),
			'due_invoices' => intval($data['due_invoices'] ?? 0),
			'total_amount' => floatval($data['total_amount'] ?? 0)
		);
	}

	function getInvoiceByUuid($invoice_uuid) {
		$this->db->select('*');
		$this->db->from("invoices");
		$this->db->where('invoice_uuid', $invoice_uuid);
		$this->db->where('status', 1);
		$data = $this->db->get();
		if ($data && $data->num_rows() > 0) {
			return $data->row_array();
		} else {
			return array();
		}
	}

	function updateInvoiceStatus($invoice_uuid, $pay_status, $updated_by) {
		$invoice = $this->getInvoiceByUuid($invoice_uuid);

		if (empty($invoice)) {
			return false;
		}

		$update_data = array(
			'pay_status' => strtoupper($pay_status),
			'updated_on' => date('Y-m-d H:i:s'),
			'updated_by' => $updated_by
		);

		$this->db->where('invoice_uuid', $invoice_uuid);
		$this->db->where('status', 1);

		$result = $this->db->update('invoices', $update_data);

		// If marked as PAID, credit any wallet top-up then provision.
		//
		// The credit call is duplicated from
		// Payment_model::processSuccessfulPayment() on purpose: these are two
		// INDEPENDENT routes to a PAID invoice, and this one is not a subset of
		// the other. This is the path an admin takes when marking a bank
		// transfer received -- which is exactly how a reseller without a card
		// tops up -- and also the path behind the API's POST /invoices/pay.
		// Without it those top-ups would be marked paid and silently never
		// credited. Both calls are idempotent on topup:invoice:{id}, so an
		// invoice that somehow travels both routes is credited once.
		if ($result && strtoupper($pay_status) === 'PAID') {
			$CI =& get_instance();
			$CI->load->model('Resellercredit_model');
			$CI->Resellercredit_model->creditWalletTopups($invoice['id']);

			$this->provisionPaidServices($invoice['id']);
		}

		return $result;
	}

	/**
	 * Provision services and domains after payment is confirmed
	 *
	 * This method uses the Provisioning_model to handle all types of provisioning:
	 * - Domain registration, transfer, and renewal
	 * - Hosting account creation and unsuspend
	 *
	 * @param int $invoiceId Invoice ID
	 * @return array Provisioning results
	 */
	function provisionPaidServices($invoiceId) {
		$CI =& get_instance();
		$CI->load->model('Provisioning_model');
		$CI->load->model('Resellercredit_model');

		// Charge the reseller's wallet BEFORE anything is provisioned
		// (v2.0.0 Phase 3).
		//
		// This is the point at which the platform actually incurs the registrar
		// / server cost, which is why the debit lives here and not at checkout:
		// a checkout that ends in an abandoned DUE invoice must not move money.
		//
		// For a DIRECT customer this returns wallet = false and does nothing at
		// all -- no ledger read, no write. That short circuit is what makes the
		// wallet inert for every non-reseller order.
		$wallet = $CI->Resellercredit_model->debitForInvoice($invoiceId);

		if (!empty($wallet['held'])) {
			// The debit has already been written (the balance is allowed to go
			// negative on purpose) -- what is being withheld is the registrar /
			// server call, not the accounting. Park the items so the release
			// pass can find them once the account recovers.
			log_message('error', 'provisionPaidServices: invoice #' . $invoiceId
				. ' HELD - ' . $wallet['hold_reason']);
			return $CI->Provisioning_model->holdInvoiceItems($invoiceId, $wallet['hold_reason']);
		}

		$results = $CI->Provisioning_model->provisionInvoiceItems($invoiceId);

		log_message('info', 'provisionPaidServices completed for invoice #' . $invoiceId .
			' - Success: ' . $results['items_success'] . '/' . $results['items_processed']);

		return $results;
	}

	/**
	 * Retry failed provisioning for an invoice
	 *
	 * @param int $invoiceId Invoice ID
	 * @return array Provisioning results
	 */
	function retryProvisioning($invoiceId) {
		$CI =& get_instance();
		$CI->load->model('Provisioning_model');

		return $CI->Provisioning_model->retryProvisioning($invoiceId);
	}

	/**
	 * Get provisioning logs for an invoice
	 *
	 * @param int $invoiceId Invoice ID
	 * @return array Provisioning logs
	 */
	function getProvisioningLogs($invoiceId) {
		$CI =& get_instance();
		$CI->load->model('Provisioning_model');

		return $CI->Provisioning_model->getProvisioningLogs($invoiceId);
	}

}
?>
