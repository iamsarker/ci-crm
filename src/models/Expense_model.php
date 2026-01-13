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
		$sql = "SELECT * FROM expenses WHERE id=$id and status=1 ";
		$data = $this->db->query($sql)->result_array();

		return !empty($data) ? $data[0] : array();
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
}
?>
