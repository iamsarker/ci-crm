<?php 
class Kb_model extends CI_Model{
	var $table;

	function __construct(){
		parent::__construct();
		$this->load->database();
		$this->table = "kbs";
	}

	function loadAllData() {
		$sql = "SELECT * FROM $this->table WHERE status=1 ";
		$data = $this->db->query($sql)->result_array();
		
		return $data;
 	}

	function getDetail($id) {
		// SECURITY FIX: Use prepared statement to prevent SQL injection
		// Validate ID parameter
		if (empty($id) || !is_numeric($id) || intval($id) <= 0) {
			return array();
		}

		$sql = "SELECT k.*, group_concat(cat.kb_cat_id) kb_cat_ids FROM kbs k left join kb_cat_mapping cat on k.id=cat.kb_id WHERE k.id=? and k.status=1";
		$data = $this->db->query($sql, array(intval($id)))->result_array();

		return !empty($data) ? $data[0] : array();
	}

	function getLastId() {
		$sql = "SELECT id FROM $this->table WHERE status=1 order by id desc limit 0,1";
		$data = $this->db->query($sql)->result_array();

		return !empty($data) ? $data[0]['id'] : 0;
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

	function saveMappingData($data) {
		$return = array();

		if ($this->db->replace('kb_cat_mapping', $data)) {
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
		$query = $this->db->query("select count(id) as cnt from ".$this->table." where status=1");
		$data = $query->result_array();
		return !empty($data) ? $data[0]['cnt'] : 0;
	}

	function countDataTableFilterRecords($where, $bindings) {
		$query = $this->db->query("select count(id) as cnt from ".$this->table." $where", $bindings);
		$data = $query->result_array();
		return !empty($data) ? $data[0]['cnt'] : 0;
	}
}
?>
