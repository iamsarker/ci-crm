<?php 
class Announcement_model extends CI_Model{
	var $table;

	function __construct(){
		parent::__construct();
		$this->load->database();
		$this->table = "announcements";
	}

	function loadAllData() {
		$sql = "SELECT * FROM $this->table WHERE status=1 ";
		$data = $this->db->query($sql)->result_array();
		
		return $data;
 	}

	function getDetail($id) {
		// Validate ID parameter
		if (empty($id) || !is_numeric($id) || intval($id) <= 0) {
			return array();
		}

		$this->db->select('*');
		$this->db->from($this->table);
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
