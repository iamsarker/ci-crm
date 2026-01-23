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

		return $this->db->update('invoices', $update_data);
	}

}
?>
