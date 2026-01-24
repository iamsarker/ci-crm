<?php 
class Company_model extends CI_Model{
	var $table;

	function __construct(){
		parent::__construct();
		$this->load->database();
		$this->table = "companies";
	}

	function loadAllData() {
		try {
			$sql = "SELECT * FROM $this->table WHERE status=1 ";
			$data = $this->db->query($sql)->result_array();

			return $data;
		} catch (Exception $e) {
			// SECURITY: Log database error
			ErrorHandler::log_database_error('loadAllData', $this->db->last_query(), $e->getMessage());
			return array();
		}
 	}

	function getDetail($id) {
		// SECURITY FIX: Validate ID and use query builder to prevent SQL injection
		if (empty($id) || !is_numeric($id) || intval($id) <= 0) {
			return array();
		}

		try {
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
		} catch (Exception $e) {
			// SECURITY: Log database error
			ErrorHandler::log_database_error('getDetail', $this->db->last_query(), $e->getMessage());
			return array();
		}
	}

	function saveData($data) {
		$return['id'] = 0;

		try {
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
		} catch (Exception $e) {
			// SECURITY: Log database error with operation details
			$operation = (!empty($data['id']) && $data['id'] > 0) ? 'UPDATE' : 'INSERT';
			ErrorHandler::log_database_error('saveData - ' . $operation, $this->db->last_query(), $e->getMessage());
			return array('id' => 0, 'error' => true, 'message' => 'Database operation failed');
		}
 	}
}
?>
