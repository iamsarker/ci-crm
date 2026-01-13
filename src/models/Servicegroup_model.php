<?php 
class Servicegroup_model extends CI_Model{

	function __construct(){
		parent::__construct();
		$this->load->database();
	}

	function loadAllData() {
		$sql = "SELECT * FROM product_service_groups WHERE status=1 ";
		$data = $this->db->query($sql)->result_array();
		
		return $data;
 	}

	function getDetail($id) {
		$sql = "SELECT * FROM product_service_groups WHERE id=$id and status=1 ";
		$data = $this->db->query($sql)->result_array();

		return !empty($data) ? $data[0] : array();
	}

	function saveData($data) {
		$return = array();

		if ($this->db->replace('product_service_groups', $data)) {
			$return['success'] = 1;
		} else {
			$return['success'] = 0;
		}
		return $return;
 	}
}
?>
