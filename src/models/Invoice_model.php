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
		$query = $this->db->query("select count(id) as cnt from invoice_view where status=1");
		$data = $query->result_array();
		return !empty($data) ? $data[0]['cnt'] : 0;
	}

	function countDataTableFilterRecords($where, $bindings) {
		$query = $this->db->query("select count(id) as cnt from invoice_view $where", $bindings);
		$data = $query->result_array();
		return !empty($data) ? $data[0]['cnt'] : 0;
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

		// If marked as PAID, trigger service provisioning
		if ($result && strtoupper($pay_status) === 'PAID') {
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
