<?php
class Billing_model extends CI_Model{

	function __construct(){
		parent::__construct();
		$this->load->database();
	}

	function loadInvoiceList($companyId, $limit) {
		// SECURITY: Validate inputs to prevent SQL injection
		$bindings = array();

		$sql = "SELECT * FROM invoices WHERE status=1 ";

		if (is_numeric($companyId) && $companyId > 0) {
			$sql .= " AND company_id = ? ";
			$bindings[] = intval($companyId);
		}

		$sql .= " ORDER BY updated_on DESC ";

		if (is_numeric($limit) && $limit > 0) {
			$sql .= " LIMIT " . intval($limit);
		}

		$data = $this->db->query($sql, $bindings)->result_array();

		return $data;
 	}

	function getInvoiceByUuid($invoice_uuid, $companyId) {
		// SECURITY: Validate inputs
		if (empty($invoice_uuid) || !is_numeric($companyId) || $companyId <= 0) {
			return array();
		}

		$sql = "SELECT inv.*, o.discount_amount, o.total_amount order_amount, o.payment_gateway_id,
				c.name company_name, c.address company_address, c.city company_city,
				c.state company_state, c.zip_code, c.country
			FROM invoices inv
			JOIN orders o ON inv.order_id = o.id
			JOIN companies c ON inv.company_id = c.id
			WHERE inv.invoice_uuid = ? AND inv.company_id = ? AND inv.status = 1
			ORDER BY inv.updated_on DESC ";

		$data = $this->db->query($sql, array($invoice_uuid, intval($companyId)))->result_array();

		if ($data) {
			return $data[0];
		}
		return array();
	}

	function invoiceSummary($companyId){
		// SECURITY: Use parameterized query
		$bindings = array();

		$sql = "SELECT
				SUM(CASE WHEN UPPER(pay_status) = 'PAID' THEN 1 ELSE 0 END) paid,
				SUM(CASE WHEN UPPER(pay_status) = 'DUE' THEN 1 ELSE 0 END) due,
				SUM(CASE WHEN UPPER(pay_status) = 'PARTIAL' THEN 1 ELSE 0 END) partialy
			FROM invoices WHERE status = 1 ";

		if (is_numeric($companyId) && $companyId > 0) {
			$sql .= " AND company_id = ? ";
			$bindings[] = intval($companyId);
		}

		$data = $this->db->query($sql, $bindings)->result_array();

		return $data;
	}

	function getInvoiceItems($invoiceId) {
		// SECURITY: Validate input
		if (!is_numeric($invoiceId) || $invoiceId <= 0) {
			return array();
		}

		$sql = "SELECT ii.* FROM invoice_items ii WHERE ii.invoice_id = ?";

		return $this->db->query($sql, array(intval($invoiceId)))->result_array();
	}

	function allCurrencies() {
		$sql = "SELECT * FROM currencies WHERE status = 1 ORDER BY updated_on DESC";

		$data = $this->db->query($sql)->result_array();

		return $data;
	}

}
?>
