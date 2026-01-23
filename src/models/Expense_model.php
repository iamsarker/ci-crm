<?php 
class Expense_model extends CI_Model{

	function __construct(){
		parent::__construct();
		$this->load->database();
	}

	function loadAllData() {
		$sql = "SELECT e.*, et.expense_type, ev.vendor_name FROM expenses e join expense_types et on e.expense_type_id=et.id join expense_vendors ev on e.expense_vendor_id=ev.id WHERE e.status=1 ";
		$data = $this->db->query($sql)->result_array();
		
		return $data;
 	}

	function getDetail($id) {
		// Validate ID parameter
		if (empty($id) || !is_numeric($id) || intval($id) <= 0) {
			return array();
		}

		$this->db->select('*');
		$this->db->from('expenses');
		$this->db->where('id', intval($id));
		$this->db->where('status', 1);
		$data = $this->db->get();

		if ($data && $data->num_rows() > 0) {
			return $data->row_array();
		} else {
			return array();
		}
	}

	function saveData($data) {
		$return = array();

		if ($this->db->replace('expenses', $data)) {
			$return['success'] = 1;
		} else {
			$return['success'] = 0;
		}
		return $return;
 	}

	function getDataTableRecords($sqlQuery, $bindings) {
		$data = $this->db->query($sqlQuery, $bindings);
		$results = $data->result_array();

		// Add encoded ID for URLs
		foreach ($results as &$row) {
			$row['encoded_id'] = safe_encode($row['id']);
		}

		return $results;
	}

	function countDataTableTotalRecords() {
		$query = $this->db->query("select count(id) as cnt from expense_view where status=1");
		$data = $query->result_array();
		return !empty($data) ? $data[0]['cnt'] : 0;
	}

	function countDataTableFilterRecords($where, $bindings) {
		$query = $this->db->query("select count(id) as cnt from expense_view $where", $bindings);
		$data = $query->result_array();
		return !empty($data) ? $data[0]['cnt'] : 0;
	}
}
?>
