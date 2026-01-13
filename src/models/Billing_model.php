<?php 
class Billing_model extends CI_Model{

	function __construct(){
		parent::__construct();
		$this->load->database();
	}

	function loadInvoiceList($companyId, $limit) {

		$usrCondition = " WHERE status=1 ";
		if( is_numeric($companyId) && $companyId > 0 ){
			$usrCondition = " WHERE company_id=$companyId AND status=1 ";
		}

		$sql = "SELECT * FROM invoices $usrCondition ORDER BY updated_on DESC ";
		if( is_numeric($limit) && $limit > 0 ){
			$sql .= " LIMIT $limit ";
		}

		$data = $this->db->query($sql)->result_array();

		return $data;
 	}

	function getInvoiceByUuid($invoice_uuid, $companyId) {

		$sql = "SELECT inv.*, o.discount_amount, o.total_amount order_amount, o.payment_gateway_id, c.name company_name, c.address company_address, c.city company_city, c.state company_state, c.zip_code, c.country
			FROM invoices inv 
			join orders o on inv.order_id=o.id 
			join companies c on inv.company_id=c.id 
			WHERE inv.invoice_uuid='$invoice_uuid' and inv.company_id=$companyId AND inv.status=1 ORDER BY inv.updated_on DESC ";

		$data = $this->db->query($sql)->result_array();

		if( $data ){
			return $data[0];
		}
		return array();
	}

	function invoiceSummary($companyId){
		$sql = " SELECT sum(CASE WHEN upper(pay_status)='PAID' THEN 1 ELSE 0 END) paid, 
			sum(CASE WHEN upper(pay_status)='DUE' THEN 1 ELSE 0 END) due, 
			sum(CASE WHEN upper(pay_status)='PARTIAL' THEN 1 ELSE 0 END) partialy
			FROM invoices WHERE ";

		if( is_numeric($companyId) && $companyId > 0 ){
			$sql .= " company_id=$companyId and  ";
		}
		$sql .= " status=1 ";

		$data = $this->db->query($sql)->result_array();

		return $data;
	}

	function getInvoiceItems($invoiceId) {

		$sql = "SELECT ii.*
			FROM invoice_items ii 
			WHERE ii.invoice_id=$invoiceId ";

		return $this->db->query($sql)->result_array();
	}

	function allCurrencies() {
		$sql = "SELECT * FROM currencies WHERE status=1 ORDER BY updated_on DESC ";

		$data = $this->db->query($sql)->result_array();

		return $data;
	}


}
?>
