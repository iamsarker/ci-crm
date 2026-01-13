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

}
?>
