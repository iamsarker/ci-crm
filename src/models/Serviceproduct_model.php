<?php
class Serviceproduct_model extends CI_Model{
	var $table;

	function __construct(){
		parent::__construct();
		$this->load->database();
		$this->table = "product_services";
	}

	function loadAllData() {
		$sql = "SELECT ps.*, psg.group_name, psm.module_name, pst.servce_type_name
				FROM product_services ps
				LEFT JOIN product_service_groups psg ON ps.product_service_group_id = psg.id
				LEFT JOIN product_service_modules psm ON ps.product_service_module_id = psm.id
				LEFT JOIN product_service_types pst ON ps.product_service_type_id = pst.id
				WHERE ps.status=1";
		$data = $this->db->query($sql)->result_array();

		return $data;
 	}

	function getDetail($id) {
		if (!is_numeric($id) || $id <= 0) {
			return array();
		}

		$sql = "SELECT * FROM $this->table WHERE id=? AND status=1";
		$data = $this->db->query($sql, array(intval($id)))->result_array();

		return !empty($data) ? $data[0] : array();
	}

	function saveData($data) {
		$return = array();

		if (!empty($data['id']) && $data['id'] > 0) {
			$this->db->where('id', $data['id']);
			if ($this->db->update($this->table, $data)) {
				$return['success'] = 1;
			} else {
				$return['success'] = 0;
			}
		} else {
			if ($this->db->insert($this->table, $data)) {
				$return['success'] = 1;
				$return['id'] = $this->db->insert_id();
			} else {
				$return['success'] = 0;
			}
		}

		return $return;
 	}

	// ============================================
	// Server-side DataTable Methods
	// ============================================

	function getDataTableRecords($sqlQuery, $bindings) {
		try {
			$data = $this->db->query($sqlQuery, $bindings);
			return $data->result_array();
		} catch (Exception $e) {
			ErrorHandler::log_database_error('getDataTableRecords', $this->db->last_query(), $e->getMessage());
			return array();
		}
	}

	function countDataTableTotalRecords() {
		try {
			$query = $this->db->query("SELECT COUNT(id) as cnt FROM product_service_view WHERE status=1");
			$data = $query->result_array();
			return !empty($data) ? $data[0]['cnt'] : 0;
		} catch (Exception $e) {
			ErrorHandler::log_database_error('countDataTableTotalRecords', $this->db->last_query(), $e->getMessage());
			return 0;
		}
	}

	function countDataTableFilterRecords($where, $bindings) {
		try {
			$query = $this->db->query("SELECT COUNT(id) as cnt FROM product_service_view $where", $bindings);
			$data = $query->result_array();
			return !empty($data) ? $data[0]['cnt'] : 0;
		} catch (Exception $e) {
			ErrorHandler::log_database_error('countDataTableFilterRecords', $this->db->last_query(), $e->getMessage());
			return 0;
		}
	}
}
?>
