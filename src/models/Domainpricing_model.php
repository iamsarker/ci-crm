<?php 
class Domainpricing_model extends CI_Model{
	var $table;

	function __construct(){
		parent::__construct();
		$this->load->database();
		$this->table = "dom_pricing";
	}

	function priceData($params) {
		$sql = "SELECT * FROM $this->table WHERE dom_extension_id=? and currency_id=? and reg_period=? and status=1 ";
		$data = $this->db->query($sql, array($params['dom_extension_id'], $params['currency_id'], $params['reg_period']))->result_array();

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
