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
		$sql = "SELECT dp.*, de.extension, c.code as currency_code, c.symbol as currency_symbol
				FROM $this->table dp
				LEFT JOIN dom_extensions de ON dp.dom_extension_id = de.id
				LEFT JOIN currencies c ON dp.currency_id = c.id
				WHERE dp.status=1
				ORDER BY dp.id DESC";
		$data = $this->db->query($sql)->result_array();

		return $data;
 	}

	function getDetail($id) {
		$sql = "SELECT * FROM $this->table WHERE id=? and status=1 ";
		$data = $this->db->query($sql, array($id))->result_array();

		return !empty($data) ? $data[0] : array();
	}

	function saveData($data) {
		$return = array();

		if ($this->db->replace($this->table, $data)) {
			$return['success'] = 1;
		} else {
			$return['success'] = 0;
		}
		return $return;
	}

	// Get all active domain extensions for dropdown
	function getAllExtensions() {
		$sql = "SELECT id, extension FROM dom_extensions WHERE status=1 ORDER BY extension";
		return $this->db->query($sql)->result_array();
	}

	// Get all active currencies for dropdown
	function getAllCurrencies() {
		$sql = "SELECT id, code, symbol FROM currencies WHERE status=1 ORDER BY code";
		return $this->db->query($sql)->result_array();
	}

	// Server-side pagination methods
	function getDataTableRecords($sqlQuery, $bindings) {
		try {
			$data = $this->db->query($sqlQuery, $bindings)->result_array();
			return $data;
		} catch (Exception $e) {
			ErrorHandler::log_database_error('getDataTableRecords', $this->db->last_query(), $e->getMessage());
			return array();
		}
	}

	function countDataTableTotalRecords() {
		try {
			$sql = "SELECT COUNT(dp.id) as cnt
					FROM $this->table dp
					WHERE dp.status=1";
			$query = $this->db->query($sql);
			$data = $query->result_array();
			return !empty($data) ? $data[0]['cnt'] : 0;
		} catch (Exception $e) {
			ErrorHandler::log_database_error('countDataTableTotalRecords', $this->db->last_query(), $e->getMessage());
			return 0;
		}
	}

	function countDataTableFilterRecords($where, $bindings) {
		try {
			$sql = "SELECT COUNT(dp.id) as cnt
					FROM $this->table dp
					LEFT JOIN dom_extensions de ON dp.dom_extension_id = de.id
					LEFT JOIN currencies c ON dp.currency_id = c.id
					$where";
			$query = $this->db->query($sql, $bindings);
			$data = $query->result_array();
			return !empty($data) ? $data[0]['cnt'] : 0;
		} catch (Exception $e) {
			ErrorHandler::log_database_error('countDataTableFilterRecords', $this->db->last_query(), $e->getMessage());
			return 0;
		}
	}

	function buildDataTableQuery($request, &$bindings, &$where) {
		// Build the SQL query with proper joins
		$limit = ssp_limit($request);
		$order = ssp_order($request);
		$where = ssp_filter($request, $bindings);

		// Add wildcards to bindings for LIKE clauses
		for ($i = 0; $i < count($bindings); $i++) {
			$bindings[$i] = '%' . $bindings[$i] . '%';
		}

		// Replace table alias for WHERE clause
		$where = str_replace('`id`', '`dp`.`id`', $where);
		$where = str_replace('`extension`', '`de`.`extension`', $where);
		$where = str_replace('`currency_code`', '`c`.`code`', $where);
		$where = str_replace('`currency_symbol`', '`c`.`symbol`', $where);
		$where = str_replace('`reg_period`', '`dp`.`reg_period`', $where);
		$where = str_replace('`price`', 'CAST(`dp`.`price` AS CHAR)', $where);
		$where = str_replace('`transfer`', 'CAST(`dp`.`transfer` AS CHAR)', $where);
		$where = str_replace('`renewal`', 'CAST(`dp`.`renewal` AS CHAR)', $where);
		$where = str_replace('`updated_on`', '`dp`.`updated_on`', $where);
		$where = str_replace('`status`', '`dp`.`status`', $where);

		// If there's already a WHERE clause, add status condition
		if (!empty($where)) {
			$where .= " AND dp.status=1";
		} else {
			$where = "WHERE dp.status=1";
		}

		// Replace column names in ORDER BY clause
		$order = str_replace('`id`', '`dp`.`id`', $order);
		$order = str_replace('`extension`', '`de`.`extension`', $order);
		$order = str_replace('`currency_code`', '`c`.`code`', $order);
		$order = str_replace('`currency_symbol`', '`c`.`symbol`', $order);
		$order = str_replace('`reg_period`', '`dp`.`reg_period`', $order);
		$order = str_replace('`price`', '`dp`.`price`', $order);
		$order = str_replace('`transfer`', '`dp`.`transfer`', $order);
		$order = str_replace('`renewal`', '`dp`.`renewal`', $order);
		$order = str_replace('`updated_on`', '`dp`.`updated_on`', $order);

		// Main query to get the data
		$sql = "SELECT dp.id, dp.dom_extension_id, dp.currency_id, dp.reg_period,
					   dp.price, dp.transfer, dp.renewal, dp.status, dp.updated_on,
					   de.extension, c.code as currency_code, c.symbol as currency_symbol
				FROM $this->table dp
				LEFT JOIN dom_extensions de ON dp.dom_extension_id = de.id
				LEFT JOIN currencies c ON dp.currency_id = c.id
				$where $order $limit";

		return $sql;
	}
}
?>
