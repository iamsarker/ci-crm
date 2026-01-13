<?php 
class Package_model extends CI_Model{
	var $table;

	function __construct(){
		parent::__construct();
		$this->load->database();
		$this->table = "product_services";
	}

	function filterData($params) {
		$sql = "SELECT * FROM product_service_view WHERE product_service_group_id=? and server_id=? and product_service_module_id=? and status=1 ";
		$data = $this->db->query($sql, array($params['service_group_id'], $params['server_id'], $params['module_id']))->result_array();

		return $data;
	}

	function priceData($params) {
		$sql = "SELECT * FROM product_service_pricing WHERE product_service_id=? and currency_id=? and billing_cycle_id=? and status=1 ";
		$data = $this->db->query($sql, array($params['product_service_id'], $params['currency_id'], $params['billing_cycle_id']))->result_array();

		return !empty($data) ? $data[0] : array();
	}

	function loadAllData() {
		$sql = "SELECT * FROM $this->table WHERE status=1 ";
		$data = $this->db->query($sql)->result_array();
		
		return $data;
 	}

	function getDetail($id) {
		$sql = "SELECT * FROM $this->table WHERE id=$id and status=1 ";
		$data = $this->db->query($sql)->result_array();

		return !empty($data) ? $data[0] : array();
	}

	function saveData($data) {
		$return['id'] = 0;

		if( !empty($data['id']) && $data['id'] > 0){
			$this->db->where('id', $data['id']);
			if ($this->db->update($this->table, $data)) {
				$return['id'] = $data['id'];
			}
		} else {
			if ($this->db->insert($this->table, $data)) {
				$return['id'] = $this->db->insert_id();
			}
		}

		return $return;
	}
}
?>
