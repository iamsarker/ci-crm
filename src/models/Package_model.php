<?php
class Package_model extends CI_Model{
	var $table;
	var $pricing_table;

	function __construct(){
		parent::__construct();
		$this->load->database();
		$this->table = "product_services";
		$this->pricing_table = "product_service_pricing";
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
		// SECURITY FIX: Use prepared statement to prevent SQL injection
		if (!is_numeric($id) || $id <= 0) {
			return array();
		}

		$sql = "SELECT * FROM " . $this->table . " WHERE id=? AND status=1";
		$data = $this->db->query($sql, array(intval($id)))->result_array();

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

	// CRUD operations for product_service_pricing table
	function loadAllPricingData() {
		$sql = "SELECT psp.*, ps.product_name, c.code as currency_code, c.symbol as currency_symbol, bc.cycle_name
				FROM product_service_pricing psp
				LEFT JOIN product_services ps ON psp.product_service_id = ps.id
				LEFT JOIN currencies c ON psp.currency_id = c.id
				LEFT JOIN billing_cycle bc ON psp.billing_cycle_id = bc.id
				WHERE psp.status=1
				ORDER BY psp.id DESC";
		$data = $this->db->query($sql)->result_array();

		return $data;
	}

	function getPricingDetail($id) {
		$sql = "SELECT * FROM product_service_pricing WHERE id=? and status=1 ";
		$data = $this->db->query($sql, array($id))->result_array();

		return !empty($data) ? $data[0] : array();
	}

	function savePricingData($data) {
		$return = array();

		if ($this->db->replace('product_service_pricing', $data)) {
			$return['success'] = 1;
		} else {
			$return['success'] = 0;
		}
		return $return;
	}

	// Get all active product services for dropdown
	function getAllServices() {
		$sql = "SELECT id, product_name FROM product_services WHERE status=1 ORDER BY product_name";
		return $this->db->query($sql)->result_array();
	}

	// Get all active currencies for dropdown
	function getAllCurrencies() {
		$sql = "SELECT id, code, symbol FROM currencies WHERE status=1 ORDER BY code";
		return $this->db->query($sql)->result_array();
	}

	// Get all active billing cycles for dropdown
	function getAllBillingCycles() {
		$sql = "SELECT id, cycle_name FROM billing_cycle WHERE status=1 ORDER BY id";
		return $this->db->query($sql)->result_array();
	}

	// Server-side pagination methods
	function getDataTableRecords($sqlQuery, $bindings) {
		$data = $this->db->query($sqlQuery, $bindings)->result_array();

		// Add encoded ID to each row
		foreach ($data as &$row) {
			$row['encoded_id'] = safe_encode($row['id']);
		}

		return $data;
	}

	function countDataTableTotalRecords() {
		$sql = "SELECT COUNT(psp.id) as cnt
				FROM product_service_pricing psp
				WHERE psp.status=1";
		$query = $this->db->query($sql);
		$data = $query->result_array();
		return !empty($data) ? $data[0]['cnt'] : 0;
	}

	function countDataTableFilterRecords($where, $bindings) {
		$sql = "SELECT COUNT(psp.id) as cnt
				FROM product_service_pricing psp
				LEFT JOIN product_services ps ON psp.product_service_id = ps.id
				LEFT JOIN currencies c ON psp.currency_id = c.id
				LEFT JOIN billing_cycle bc ON psp.billing_cycle_id = bc.id
				$where";
		$query = $this->db->query($sql, $bindings);
		$data = $query->result_array();
		return !empty($data) ? $data[0]['cnt'] : 0;
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
		$where = str_replace('`id`', '`psp`.`id`', $where);
		$where = str_replace('`product_name`', '`ps`.`product_name`', $where);
		$where = str_replace('`currency_code`', '`c`.`code`', $where);
		$where = str_replace('`currency_symbol`', '`c`.`symbol`', $where);
		$where = str_replace('`cycle_name`', '`bc`.`cycle_name`', $where);
		$where = str_replace('`price`', 'CAST(`psp`.`price` AS CHAR)', $where);
		$where = str_replace('`updated_on`', '`psp`.`updated_on`', $where);
		$where = str_replace('`status`', '`psp`.`status`', $where);

		// If there's already a WHERE clause, add status condition
		if (!empty($where)) {
			$where .= " AND psp.status=1";
		} else {
			$where = "WHERE psp.status=1";
		}

		// Replace column names in ORDER BY clause
		$order = str_replace('`id`', '`psp`.`id`', $order);
		$order = str_replace('`product_name`', '`ps`.`product_name`', $order);
		$order = str_replace('`currency_code`', '`c`.`code`', $order);
		$order = str_replace('`currency_symbol`', '`c`.`symbol`', $order);
		$order = str_replace('`cycle_name`', '`bc`.`cycle_name`', $order);
		$order = str_replace('`price`', '`psp`.`price`', $order);
		$order = str_replace('`updated_on`', '`psp`.`updated_on`', $order);

		// Main query to get the data
		$sql = "SELECT psp.id, psp.product_service_id, psp.currency_id, psp.billing_cycle_id,
					   psp.price, psp.status, psp.updated_on,
					   ps.product_name, c.code as currency_code, c.symbol as currency_symbol,
					   bc.cycle_name
				FROM product_service_pricing psp
				LEFT JOIN product_services ps ON psp.product_service_id = ps.id
				LEFT JOIN currencies c ON psp.currency_id = c.id
				LEFT JOIN billing_cycle bc ON psp.billing_cycle_id = bc.id
				$where $order $limit";

		return $sql;
	}
}
?>
