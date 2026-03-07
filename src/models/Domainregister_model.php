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

		// If this registrar is being set as default, unset all others first
		if (!empty($data['is_selected']) && $data['is_selected'] == 1) {
			$this->db->where('is_selected', 1);
			// Exclude current record if updating
			if (!empty($data['id'])) {
				$this->db->where('id !=', $data['id']);
			}
			$this->db->update('dom_registers', array('is_selected' => 0));
		}

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

	/**
	 * Get registrar statistics for dashboard cards
	 *
	 * @return array Stats including total, active, default, and platforms counts
	 */
	function getRegistrarStats() {
		try {
			$query = $this->db->query("
				SELECT
					COUNT(*) as total_registrars,
					SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as active_registrars,
					SUM(CASE WHEN is_selected = 1 AND status = 1 THEN 1 ELSE 0 END) as default_registrar,
					COUNT(DISTINCT platform) as platforms
				FROM {$this->table}
				WHERE status = 1
			");
			$data = $query->row_array();
			return array(
				'total_registrars' => intval($data['total_registrars'] ?? 0),
				'active_registrars' => intval($data['active_registrars'] ?? 0),
				'default_registrar' => intval($data['default_registrar'] ?? 0),
				'platforms' => intval($data['platforms'] ?? 0)
			);
		} catch (Exception $e) {
			log_message('error', 'Domainregister_model::getRegistrarStats - ' . $e->getMessage());
			return array(
				'total_registrars' => 0,
				'active_registrars' => 0,
				'default_registrar' => 0,
				'platforms' => 0
			);
		}
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
