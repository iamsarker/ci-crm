<?php
class Promocode_model extends CI_Model {
	var $table;

	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->table = "promo_codes";
	}

	// ─── CRUD ────────────────────────────────────────────────

	function getDetail($id) {
		if (empty($id) || !is_numeric($id) || intval($id) <= 0) {
			return array();
		}
		$sql = "SELECT * FROM {$this->table} WHERE id = ? AND status = 1";
		$data = $this->db->query($sql, array(intval($id)))->result_array();
		return !empty($data) ? $data[0] : array();
	}

	function saveData($data) {
		$return = array('id' => 0);
		if (!empty($data['id']) && $data['id'] > 0) {
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

	function getLastId() {
		$sql = "SELECT id FROM {$this->table} WHERE status = 1 ORDER BY id DESC LIMIT 1";
		$data = $this->db->query($sql)->result_array();
		return !empty($data) ? $data[0]['id'] : 0;
	}

	function loadAllData() {
		$sql = "SELECT * FROM {$this->table} WHERE status = 1";
		return $this->db->query($sql)->result_array();
	}

	// ─── Mappings ────────────────────────────────────────────

	function saveMappings($promoId, $productIds, $companyIds) {
		// Clear existing mappings
		$this->db->where('promo_code_id', $promoId);
		$this->db->delete('promo_code_products');

		$this->db->where('promo_code_id', $promoId);
		$this->db->delete('promo_code_customers');

		// Save product mappings
		if (!empty($productIds)) {
			foreach ($productIds as $pid) {
				$this->db->insert('promo_code_products', array(
					'promo_code_id' => $promoId,
					'product_service_id' => intval($pid)
				));
			}
		}

		// Save customer mappings
		if (!empty($companyIds)) {
			foreach ($companyIds as $cid) {
				$this->db->insert('promo_code_customers', array(
					'promo_code_id' => $promoId,
					'company_id' => intval($cid)
				));
			}
		}
	}

	function getProductMappings($promoId) {
		$sql = "SELECT product_service_id FROM promo_code_products WHERE promo_code_id = ?";
		$rows = $this->db->query($sql, array(intval($promoId)))->result_array();
		return array_column($rows, 'product_service_id');
	}

	function getCustomerMappings($promoId) {
		$sql = "SELECT company_id FROM promo_code_customers WHERE promo_code_id = ?";
		$rows = $this->db->query($sql, array(intval($promoId)))->result_array();
		return array_column($rows, 'company_id');
	}

	// ─── Validation ──────────────────────────────────────────

	/**
	 * Validate a promo code for checkout
	 * @param string $code
	 * @param int $companyId
	 * @param float $cartSubTotal
	 * @param int $currencyId
	 * @param array $cartProductIds - product_service_ids in cart
	 * @return array ['valid'=>bool, 'promo'=>[], 'discount_amount'=>float, 'message'=>string]
	 */
	function validatePromoCode($code, $companyId, $cartSubTotal, $currencyId, $cartProductIds = array()) {
		$result = array('valid' => false, 'promo' => array(), 'discount_amount' => 0, 'message' => '');

		$code = strtoupper(trim($code));
		if (empty($code)) {
			$result['message'] = 'Please enter a promo code.';
			return $result;
		}

		// 1. Check exists and active
		$sql = "SELECT * FROM {$this->table} WHERE code = ? AND status = 1";
		$rows = $this->db->query($sql, array($code))->result_array();
		if (empty($rows)) {
			$result['message'] = 'Invalid promo code.';
			return $result;
		}
		$promo = $rows[0];

		if (!$promo['is_active']) {
			$result['message'] = 'This promo code is no longer active.';
			return $result;
		}

		// 2. Date range (skip if lifetime)
		if (!$promo['is_lifetime']) {
			$today = date('Y-m-d');
			if (!empty($promo['start_date']) && $today < $promo['start_date']) {
				$result['message'] = 'This promo code is not yet valid.';
				return $result;
			}
			if (!empty($promo['end_date']) && $today > $promo['end_date']) {
				$result['message'] = 'This promo code has expired.';
				return $result;
			}
		}

		// 3. Currency match for fixed discount
		if ($promo['discount_type'] === 'fixed' && !empty($promo['currency_id'])) {
			if (intval($promo['currency_id']) !== intval($currencyId)) {
				$result['message'] = 'This promo code is not valid for your selected currency.';
				return $result;
			}
		}

		// 4. Max uses (global)
		if ($promo['max_uses'] > 0 && $promo['total_used'] >= $promo['max_uses']) {
			$result['message'] = 'This promo code has reached its usage limit.';
			return $result;
		}

		// 5. Per-customer uses
		if ($promo['max_uses_per_customer'] > 0 && $companyId > 0) {
			$sql = "SELECT COUNT(*) as cnt FROM promo_code_usage WHERE promo_code_id = ? AND company_id = ?";
			$usage = $this->db->query($sql, array($promo['id'], $companyId))->row_array();
			if ($usage['cnt'] >= $promo['max_uses_per_customer']) {
				$result['message'] = 'You have already used this promo code the maximum number of times.';
				return $result;
			}
		}

		// 6. Minimum order amount
		if ($promo['min_order_amount'] > 0 && $cartSubTotal < $promo['min_order_amount']) {
			$result['message'] = 'Minimum order amount of ' . number_format($promo['min_order_amount'], 2) . ' required for this promo code.';
			return $result;
		}

		// 7. Product eligibility (when applies_to = 'products')
		if ($promo['applies_to'] === 'products') {
			$mappedProducts = $this->getProductMappings($promo['id']);
			if (empty($mappedProducts)) {
				$result['message'] = 'This promo code has no eligible products configured.';
				return $result;
			}
			if (empty($cartProductIds) || empty(array_intersect($cartProductIds, $mappedProducts))) {
				$result['message'] = 'This promo code does not apply to any items in your cart.';
				return $result;
			}
		}

		// 8. Customer eligibility (when applies_to = 'customers')
		if ($promo['applies_to'] === 'customers') {
			$mappedCustomers = $this->getCustomerMappings($promo['id']);
			if (empty($mappedCustomers) || !in_array($companyId, $mappedCustomers)) {
				$result['message'] = 'This promo code is not available for your account.';
				return $result;
			}
		}

		// 9. Calculate discount
		$discountAmount = 0;
		if ($promo['discount_type'] === 'percentage') {
			$discountAmount = ($cartSubTotal * $promo['discount_value']) / 100;
			// Apply cap if set
			if ($promo['max_discount_amount'] > 0 && $discountAmount > $promo['max_discount_amount']) {
				$discountAmount = $promo['max_discount_amount'];
			}
		} else {
			$discountAmount = $promo['discount_value'];
		}

		// Don't let discount exceed cart subtotal
		if ($discountAmount > $cartSubTotal) {
			$discountAmount = $cartSubTotal;
		}

		$discountAmount = round($discountAmount, 2);

		$result['valid'] = true;
		$result['promo'] = $promo;
		$result['discount_amount'] = $discountAmount;
		$result['message'] = 'Promo code applied! You save ' . number_format($discountAmount, 2);

		return $result;
	}

	// ─── Usage Tracking ──────────────────────────────────────

	/**
	 * Record promo code usage (race-condition-safe increment)
	 */
	function recordUsage($promoCodeId, $companyId, $orderId, $discountAmount) {
		// Insert usage record
		$this->db->insert('promo_code_usage', array(
			'promo_code_id' => intval($promoCodeId),
			'company_id' => intval($companyId),
			'order_id' => intval($orderId),
			'discount_amount' => $discountAmount,
			'used_on' => date('Y-m-d H:i:s')
		));

		// Atomic increment to prevent race conditions
		$this->db->query(
			"UPDATE {$this->table} SET total_used = total_used + 1 WHERE id = ?",
			array(intval($promoCodeId))
		);
	}

	// ─── Stats ───────────────────────────────────────────────

	function getPromoStats() {
		$stats = array(
			'total' => 0,
			'active' => 0,
			'expired' => 0,
			'total_usage' => 0
		);

		$sql = "SELECT
					COUNT(*) as total,
					SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
					SUM(CASE WHEN is_lifetime = 0 AND end_date IS NOT NULL AND end_date < CURDATE() THEN 1 ELSE 0 END) as expired,
					SUM(total_used) as total_usage
				FROM {$this->table} WHERE status = 1";
		$row = $this->db->query($sql)->row_array();
		if (!empty($row)) {
			$stats['total'] = intval($row['total']);
			$stats['active'] = intval($row['active']);
			$stats['expired'] = intval($row['expired']);
			$stats['total_usage'] = intval($row['total_usage']);
		}
		return $stats;
	}

	// ─── Toggle Active ───────────────────────────────────────

	function toggleActive($id) {
		$detail = $this->getDetail($id);
		if (empty($detail)) return false;

		$newStatus = $detail['is_active'] ? 0 : 1;
		$this->db->where('id', $id);
		$this->db->update($this->table, array(
			'is_active' => $newStatus,
			'updated_on' => date('Y-m-d H:i:s')
		));
		return $newStatus;
	}

	// ─── DataTable ───────────────────────────────────────────

	function getDataTableRecords($sqlQuery, $bindings) {
		$data = $this->db->query($sqlQuery, $bindings);
		$results = $data->result_array();

		foreach ($results as &$row) {
			$row['encoded_id'] = safe_encode($row['id']);
		}
		return $results;
	}

	function countDataTableTotalRecords() {
		$query = $this->db->query("SELECT COUNT(id) as cnt FROM {$this->table} WHERE status = 1");
		$data = $query->result_array();
		return !empty($data) ? $data[0]['cnt'] : 0;
	}

	function countDataTableFilterRecords($where, $bindings) {
		$query = $this->db->query("SELECT COUNT(id) as cnt FROM {$this->table} $where", $bindings);
		$data = $query->result_array();
		return !empty($data) ? $data[0]['cnt'] : 0;
	}

	// ─── Dropdown Helpers ────────────────────────────────────

	function getAllProducts() {
		$sql = "SELECT ps.id, ps.product_name, pst.servce_type_name AS type_name
				FROM product_services ps
				LEFT JOIN product_service_types pst ON ps.product_service_type_id = pst.id
				WHERE ps.status = 1
				ORDER BY pst.servce_type_name, ps.product_name";
		return $this->db->query($sql)->result_array();
	}

	function getAllCompanies() {
		$sql = "SELECT id, name AS company_name FROM companies WHERE status = 1 ORDER BY name";
		return $this->db->query($sql)->result_array();
	}

	function getCurrencies() {
		$sql = "SELECT id, code AS currency_code, symbol AS currency_name FROM currencies WHERE status = 1 ORDER BY code";
		return $this->db->query($sql)->result_array();
	}
}
?>
