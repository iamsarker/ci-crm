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
				LEFT JOIN server_modules psm ON ps.product_service_module_id = psm.id
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

	/**
	 * Get service type ID => key_name mapping
	 * e.g. { "1": "SHARED_HOSTING", "2": "RESELLER_HOSTING", ... }
	 */
	function getServiceTypeKeys() {
		try {
			$query = $this->db->query("SELECT id, key_name FROM product_service_types WHERE status=1");
			$data = array();
			foreach ($query->result_array() as $row) {
				$data[$row['id']] = $row['key_name'];
			}
			return $data;
		} catch (Exception $e) {
			return array();
		}
	}

	/**
	 * Get module ID => module_name mapping
	 * e.g. { "1": "No Module", "2": "cPanel" }
	 */
	function getModuleKeys() {
		try {
			$query = $this->db->query("SELECT id, module_name FROM server_modules WHERE status=1");
			$data = array();
			foreach ($query->result_array() as $row) {
				$data[$row['id']] = $row['module_name'];
			}
			return $data;
		} catch (Exception $e) {
			return array();
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

	/**
	 * Get all active billing cycles excluding ONE_TIME and FREE
	 */
	function getBillingCycles() {
		$sql = "SELECT id, cycle_key, cycle_name FROM billing_cycle WHERE status=1 AND cycle_key NOT IN ('ONE_TIME', 'FREE') ORDER BY id";
		return $this->db->query($sql)->result_array();
	}

	/**
	 * Get all active currencies
	 */
	function getCurrencies() {
		$sql = "SELECT id, code, symbol FROM currencies WHERE status=1 ORDER BY id";
		return $this->db->query($sql)->result_array();
	}

	/**
	 * Get pricing matrix for a product: [currency_id][billing_cycle_id] => price
	 */
	function getPricingMatrix($productId) {
		if (!is_numeric($productId) || $productId <= 0) {
			return array();
		}
		$sql = "SELECT currency_id, billing_cycle_id, price FROM product_service_pricing WHERE product_service_id = ? AND status = 1";
		$rows = $this->db->query($sql, array(intval($productId)))->result_array();

		$matrix = array();
		foreach ($rows as $row) {
			$matrix[$row['currency_id']][$row['billing_cycle_id']] = $row['price'];
		}
		return $matrix;
	}

	/**
	 * Reseller COST matrix for a product: [currency_id][billing_cycle_id] => cost.
	 *
	 * Cost never lives in product_service_pricing -- that table stays "platform
	 * retail" so the direct-customer price path is provably unchanged. It is
	 * read back out of price_overrides and re-keyed by (currency, cycle) so the
	 * admin grid can render it beside the retail cell it belongs to.
	 *
	 * owner_company_id 0 = the default cost every reseller inherits. Per-reseller
	 * negotiated costs are edited on the Reseller Pricing screen, not here.
	 */
	function getCostMatrix($productId) {
		if (!is_numeric($productId) || $productId <= 0) {
			return array();
		}
		$rows = $this->db->query(
			"SELECT psp.currency_id, psp.billing_cycle_id, po.price
			 FROM product_service_pricing psp
			 JOIN price_overrides po
			   ON po.item_type = 2 AND po.pricing_id = psp.id
			  AND po.owner_company_id = 0 AND po.audience = 1
			  AND po.is_active = 1 AND po.status = 1
			 WHERE psp.product_service_id = ? AND psp.status = 1",
			array(intval($productId))
		)->result_array();

		$matrix = array();
		foreach ($rows as $row) {
			$matrix[$row['currency_id']][$row['billing_cycle_id']] = $row['price'];
		}
		return $matrix;
	}

	/**
	 * Save the reseller cost matrix. Mirrors savePricingMatrix()'s rule that a
	 * blank cell is a deletion, not a zero -- a 0.00 cost would mean the
	 * platform gives the package away, which is never what a cleared field means.
	 *
	 * @return array [company_id => lifted components] for every reseller whose
	 *               selling price had to be raised to the new floor, so the
	 *               caller can email them.
	 */
	function saveCostMatrix($productId, $costData) {
		$lifted = array();
		if (!is_numeric($productId) || $productId <= 0 || !is_array($costData)) {
			return $lifted;
		}
		$this->load->model('Pricing_model');

		foreach ($costData as $currencyId => $cycles) {
			if (!is_array($cycles)) continue;
			foreach ($cycles as $cycleId => $cost) {
				$row = $this->db->query(
					"SELECT id FROM product_service_pricing
					 WHERE product_service_id = ? AND currency_id = ? AND billing_cycle_id = ? AND status = 1 LIMIT 1",
					array(intval($productId), intval($currencyId), intval($cycleId))
				)->row_array();
				// No retail price for this cell means the package is not sold on
				// that currency/cycle at all, so there is nothing to cost.
				if (empty($row)) continue;

				$res = $this->Pricing_model->saveCostOverride(2, $row['id'], 0, array('price' => $cost));
				if (!empty($res['lifted'])) {
					foreach ($res['lifted'] as $companyId => $changes) {
						$lifted[$companyId] = array_merge(
							isset($lifted[$companyId]) ? $lifted[$companyId] : array(), $changes
						);
					}
				}
			}
		}
		return $lifted;
	}

	/**
	 * Save pricing matrix using upsert (INSERT ... ON DUPLICATE KEY UPDATE)
	 */
	function savePricingMatrix($productId, $pricingData) {
		if (!is_numeric($productId) || $productId <= 0 || !is_array($pricingData)) {
			return;
		}

		$now = getDateTime();
		$adminId = getAdminId();

		foreach ($pricingData as $currencyId => $cycles) {
			if (!is_array($cycles)) continue;
			foreach ($cycles as $cycleId => $price) {
				$price = trim($price);
				if ($price !== '' && is_numeric($price) && floatval($price) >= 0) {
					$sql = "INSERT INTO product_service_pricing (product_service_id, currency_id, billing_cycle_id, price, status, inserted_on, inserted_by)
							VALUES (?, ?, ?, ?, 1, ?, ?)
							ON DUPLICATE KEY UPDATE price = VALUES(price), status = 1, updated_on = ?, updated_by = ?";
					$this->db->query($sql, array(
						intval($productId), intval($currencyId), intval($cycleId),
						floatval($price), $now, $adminId,
						$now, $adminId
					));
				} else {
					$this->db->query(
						"DELETE FROM product_service_pricing WHERE product_service_id = ? AND currency_id = ? AND billing_cycle_id = ?",
						array(intval($productId), intval($currencyId), intval($cycleId))
					);
				}
			}
		}
	}

	/**
	 * Get billing cycle ID by cycle_key
	 */
	function getCycleIdByKey($key) {
		$sql = "SELECT id FROM billing_cycle WHERE cycle_key = ? AND status = 1 LIMIT 1";
		$row = $this->db->query($sql, array($key))->row_array();
		return !empty($row) ? intval($row['id']) : null;
	}

	/**
	 * Save or remove free pricing for a product
	 */
	function saveFreePricing($productId, $isFree, $freeCycleId, $currencies) {
		if (!$freeCycleId || !is_numeric($productId) || $productId <= 0) return;

		if ($isFree) {
			foreach ($currencies as $currency) {
				$sql = "INSERT INTO product_service_pricing (product_service_id, currency_id, billing_cycle_id, price, status, inserted_on, inserted_by)
						VALUES (?, ?, ?, 0, 1, ?, ?)
						ON DUPLICATE KEY UPDATE price = 0, status = 1, updated_on = VALUES(inserted_on), updated_by = VALUES(inserted_by)";
				$this->db->query($sql, array(
					intval($productId),
					intval($currency['id']),
					intval($freeCycleId),
					getDateTime(),
					getAdminId()
				));
			}
		} else {
			$sql = "DELETE FROM product_service_pricing WHERE product_service_id = ? AND billing_cycle_id = ?";
			$this->db->query($sql, array(intval($productId), intval($freeCycleId)));
		}
	}

	/**
	 * Delete pricing for a product except given billing cycle IDs
	 */
	function deletePricingExcept($productId, $keepCycleIds = array()) {
		if (!is_numeric($productId) || $productId <= 0) return;

		if (!empty($keepCycleIds)) {
			$placeholders = implode(',', array_fill(0, count($keepCycleIds), '?'));
			$sql = "DELETE FROM product_service_pricing WHERE product_service_id = ? AND billing_cycle_id NOT IN ($placeholders)";
			$this->db->query($sql, array_merge(array(intval($productId)), array_map('intval', $keepCycleIds)));
		} else {
			$this->db->query("DELETE FROM product_service_pricing WHERE product_service_id = ?", array(intval($productId)));
		}
	}

	function getProductStats() {
		try {
			$query = $this->db->query("
				SELECT
					COUNT(*) as total_products,
					SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as active_products,
					COUNT(DISTINCT CASE WHEN status = 1 THEN product_service_group_id END) as service_groups,
					SUM(CASE WHEN status = 1 AND is_hidden = 1 THEN 1 ELSE 0 END) as hidden_products
				FROM product_services
			");
			$data = $query->row_array();
			return array(
				'total_products' => intval($data['total_products'] ?? 0),
				'active_products' => intval($data['active_products'] ?? 0),
				'service_groups' => intval($data['service_groups'] ?? 0),
				'hidden_products' => intval($data['hidden_products'] ?? 0)
			);
		} catch (Exception $e) {
			ErrorHandler::log_database_error('getProductStats', $this->db->last_query(), $e->getMessage());
			return array(
				'total_products' => 0,
				'active_products' => 0,
				'service_groups' => 0,
				'hidden_products' => 0
			);
		}
	}
}
?>
