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
	 * Get pending services that need provisioning for an invoice
	 *
	 * @param int $invoiceId Invoice ID
	 * @return array List of services needing provisioning
	 */
	function getPendingServicesForInvoice($invoiceId) {
		$sql = "SELECT os.*, ii.item_type, ps.cp_package, ps.server_id, s.hostname, s.username, s.access_hash
				FROM invoice_items ii
				JOIN order_services os ON ii.ref_id = os.id AND ii.item_type = 2
				LEFT JOIN product_services ps ON os.product_service_id = ps.id
				LEFT JOIN servers s ON ps.server_id = s.id
				WHERE ii.invoice_id = ?
				AND os.is_synced = 0
				AND os.status = 0
				AND os.product_service_type_key IN ('SHARED_HOSTING', 'RESELLER_HOSTING')
				AND os.hosting_domain IS NOT NULL
				AND os.hosting_domain != ''";

		return $this->db->query($sql, array(intval($invoiceId)))->result_array();
	}

	/**
	 * Provision services after payment is confirmed
	 *
	 * @param int $invoiceId Invoice ID
	 * @return void
	 */
	function provisionPaidServices($invoiceId) {
		// Load required helpers and models
		$CI =& get_instance();
		$CI->load->helper('cpanel_helper');
		$CI->load->model('Common_model');
		$CI->load->model('Order_model');
		$CI->load->model('Company_model');

		$pendingServices = $this->getPendingServicesForInvoice($invoiceId);

		foreach ($pendingServices as $service) {
			// Skip if no server configured
			if (empty($service['hostname']) || empty($service['access_hash'])) {
				log_message('error', 'Provisioning skipped for order_service #' . $service['id'] . ': No server configured');
				continue;
			}

			$serverInfo = array(
				'hostname' => $service['hostname'],
				'username' => $service['username'],
				'access_hash' => $service['access_hash']
			);

			$cpPackage = !empty($service['cp_package']) ? $service['cp_package'] : 'default';

			// Get company info
			$company = $CI->Company_model->getDetail($service['company_id']);
			if (empty($company) || empty($company['email'])) {
				log_message('error', 'Provisioning skipped for order_service #' . $service['id'] . ': Company info not found');
				continue;
			}

			// Generate cPanel username and password
			$cpUsername = generate_cpanel_username($service['hosting_domain'], $serverInfo);
			$cpPassword = generate_secure_password(16, true);

			// Create cPanel account
			$result = whm_create_account(
				$serverInfo,
				$service['hosting_domain'],
				$cpUsername,
				$cpPassword,
				$cpPackage,
				$company['email']
			);

			if ($result['success']) {
				// Update order_service
				$updateData = array(
					'cp_username' => $cpUsername,
					'is_synced' => 1,
					'status' => 1  // Active
				);
				$CI->Order_model->updateOrderService($service['id'], $updateData);

				// Send welcome email
				$customerName = trim($company['first_name'] . ' ' . $company['last_name']);
				if (empty($customerName)) {
					$customerName = $company['name'];
				}

				send_cpanel_welcome_email(
					$company['email'],
					$customerName,
					$service['hosting_domain'],
					$cpUsername,
					$cpPassword,
					$serverInfo['hostname']
				);

				log_message('info', 'Provisioning successful for order_service #' . $service['id'] . ' after payment');
			} else {
				log_message('error', 'Provisioning failed for order_service #' . $service['id'] . ': ' . $result['error']);
			}
		}
	}

}
?>
