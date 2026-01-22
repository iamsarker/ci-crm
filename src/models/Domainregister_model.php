<?php
class Domainregister_model extends CI_Model{
	var $table;

	function __construct(){
		parent::__construct();
		$this->load->database();
		$this->table = "dom_registers";
	}

	// CRUD operations for dom_registers table
	function loadAllData() {
		$sql = "SELECT * FROM dom_registers WHERE status=1 ORDER BY id DESC";
		$data = $this->db->query($sql)->result_array();
		return $data;
	}

	function getDetail($id) {
		$sql = "SELECT * FROM dom_registers WHERE id=? and status=1";
		$data = $this->db->query($sql, array($id))->result_array();
		return !empty($data) ? $data[0] : array();
	}

	function saveData($data) {
		$return = array();
		if ($this->db->replace('dom_registers', $data)) {
			$return['success'] = 1;
		} else {
			$return['success'] = 0;
		}
		return $return;
	}

	// Server-side pagination methods
	function getDataTableRecords($sqlQuery, $bindings) {
		$data = $this->db->query($sqlQuery, $bindings)->result_array();

		// Add encoded ID to each row for URL-safe links
		foreach ($data as &$row) {
			$row['encoded_id'] = safe_encode($row['id']);
		}

		return $data;
	}

	function countDataTableTotalRecords() {
		$sql = "SELECT COUNT(id) as cnt FROM dom_registers WHERE status=1";
		$query = $this->db->query($sql);
		$data = $query->result_array();
		return !empty($data) ? $data[0]['cnt'] : 0;
	}

	function countDataTableFilterRecords($where, $bindings) {
		$sql = "SELECT COUNT(id) as cnt FROM dom_registers $where";
		$query = $this->db->query($sql, $bindings);
		$data = $query->result_array();
		return !empty($data) ? $data[0]['cnt'] : 0;
	}

	function buildDataTableQuery($request, &$bindings, &$where) {
		// Build the SQL query
		$limit = ssp_limit($request);
		$order = ssp_order($request);
		$where = ssp_filter($request, $bindings);

		// Add wildcards to bindings for LIKE clauses
		for ($i = 0; $i < count($bindings); $i++) {
			$bindings[$i] = '%' . $bindings[$i] . '%';
		}

		// Add status condition
		if (!empty($where)) {
			$where .= " AND status=1";
		} else {
			$where = "WHERE status=1";
		}

		// Main query to get the data
		$sql = "SELECT id, name, platform, api_base_url, is_selected, status, updated_on
				FROM dom_registers
				$where $order $limit";

		return $sql;
	}
}
?>
