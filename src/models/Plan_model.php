<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Plan_model
 * -------------------------------------------------------------------------
 * Read access to the SaaS plan catalog (`plans` + `plan_features`).
 *
 * A plan's feature map merges the universal features (TRUE on every plan, from
 * config/plans.php) with the differentiated flags stored in `plan_features`.
 * Numeric features (e.g. support_response_hours) are cast to int; every other
 * flag is cast to bool.
 *
 * @see src/config/plans.php
 * @see src/libraries/Entitlement.php
 */
class Plan_model extends CI_Model {

	private $table          = 'plans';
	private $features_table = 'plan_features';

	function __construct()
	{
		parent::__construct();
		$this->load->database();
		// fail_gracefully = TRUE so a second load in the same request is a no-op.
		$this->config->load('plans', TRUE, TRUE);
	}

	/**
	 * Active plans ordered by sort_order, each with its features merged in.
	 *
	 * @return array[] list of plan rows, each with a 'features' assoc array
	 */
	function get_active_plans()
	{
		$plans = $this->db
			->where('is_active', 1)
			->order_by('sort_order', 'ASC')
			->get($this->table)
			->result_array();

		if (empty($plans)) {
			return array();
		}

		// Single query for every plan's features, grouped by plan_id.
		$ids = array_map('intval', array_column($plans, 'id'));
		$featureRows = $this->db
			->where_in('plan_id', $ids)
			->get($this->features_table)
			->result_array();

		$byPlan = array();
		foreach ($featureRows as $row) {
			$byPlan[(int) $row['plan_id']][$row['feature_key']] = $row['feature_value'];
		}

		foreach ($plans as &$plan) {
			$plan = $this->_castPlan($plan);
			$stored = isset($byPlan[(int) $plan['id']]) ? $byPlan[(int) $plan['id']] : array();
			$plan['features'] = $this->_buildFeatureMap($stored);
		}
		unset($plan);

		return $plans;
	}

	/**
	 * Single plan by its key, with merged feature map. Empty array if not found.
	 *
	 * @param  string $plan_key
	 * @return array
	 */
	function get_by_key($plan_key)
	{
		if (empty($plan_key)) {
			return array();
		}

		$plan = $this->db
			->get_where($this->table, array('plan_key' => $plan_key))
			->row_array();

		if (empty($plan)) {
			return array();
		}

		$stored = $this->db
			->get_where($this->features_table, array('plan_id' => (int) $plan['id']))
			->result_array();

		$map = array();
		foreach ($stored as $row) {
			$map[$row['feature_key']] = $row['feature_value'];
		}

		$plan = $this->_castPlan($plan);
		$plan['features'] = $this->_buildFeatureMap($map);

		return $plan;
	}

	// ─── internals ───────────────────────────────────────────────────────

	/**
	 * Merge universal (always TRUE) + stored differentiated flags into a typed
	 * feature map (numbers -> int, everything else -> bool).
	 *
	 * @param  array $stored feature_key => feature_value (strings from DB)
	 * @return array feature_key => bool|int
	 */
	private function _buildFeatureMap(array $stored)
	{
		$universal = (array) $this->config->item('plan_universal_features', 'plans');
		$numeric   = (array) $this->config->item('plan_numeric_features', 'plans');

		$map = array();
		foreach ($universal as $key) {
			$map[$key] = TRUE;
		}
		foreach ($stored as $key => $value) {
			$map[$key] = in_array($key, $numeric, TRUE) ? (int) $value : (bool) $value;
		}

		return $map;
	}

	/**
	 * Cast scalar plan columns to sane PHP types for callers/views.
	 */
	private function _castPlan(array $plan)
	{
		$plan['id']         = (int) $plan['id'];
		$plan['is_popular'] = (bool) $plan['is_popular'];
		$plan['sort_order'] = (int) $plan['sort_order'];
		$plan['is_active']  = (bool) $plan['is_active'];

		return $plan;
	}

	// ─── admin: product catalog CRUD ─────────────────────────────────────
	// A `plans` row is a software product. Pricing lives in `software_pricing`
	// (per currency x billing cycle), features in `plan_features`.

	/** Raw product row by id (any status). Empty array if not found. */
	function getDetail($id)
	{
		$id = (int) $id;
		if ($id <= 0) {
			return array();
		}
		return $this->db->get_where($this->table, array('id' => $id))->row_array() ?: array();
	}

	/** All products for the admin list (newest first), with a price snapshot. */
	function getAllForList()
	{
		return $this->db->order_by('sort_order', 'ASC')->order_by('id', 'DESC')
			->get($this->table)->result_array();
	}

	/** Is a plan_key already taken by a different product? */
	function keyExists($planKey, $exceptId = 0)
	{
		$this->db->where('plan_key', $planKey);
		if ((int) $exceptId > 0) {
			$this->db->where('id !=', (int) $exceptId);
		}
		return $this->db->count_all_results($this->table) > 0;
	}

	/**
	 * Insert or update a product. Returns ['success'=>bool, 'id'=>int].
	 */
	function saveProduct($data)
	{
		if (!empty($data['id']) && (int) $data['id'] > 0) {
			$id = (int) $data['id'];
			unset($data['id']);
			$data['updated_at'] = date('Y-m-d H:i:s');
			$ok = $this->db->where('id', $id)->update($this->table, $data);
			return array('success' => (bool) $ok, 'id' => $id);
		}

		$data['created_at'] = date('Y-m-d H:i:s');
		$data['updated_at'] = date('Y-m-d H:i:s');
		$ok = $this->db->insert($this->table, $data);
		return array('success' => (bool) $ok, 'id' => (int) $this->db->insert_id());
	}

	/** Hard-delete a product (pricing + features cascade via FK). */
	function deleteProduct($id)
	{
		$id = (int) $id;
		if ($id <= 0) {
			return false;
		}
		// Refuse if a customer already owns this product.
		$inUse = $this->db->where('plan_id', $id)->count_all_results('order_licenses');
		if ($inUse > 0) {
			return false;
		}
		return $this->db->where('id', $id)->delete($this->table);
	}

	/** Toggle is_active. Returns the new value, or null on bad id. */
	function toggleActive($id)
	{
		$row = $this->getDetail($id);
		if (empty($row)) {
			return null;
		}
		$new = $row['is_active'] ? 0 : 1;
		$this->db->where('id', (int) $id)->update($this->table, array('is_active' => $new, 'updated_at' => date('Y-m-d H:i:s')));
		return $new;
	}

	// ─── admin: pricing (software_pricing) ───────────────────────────────

	/** Active billing cycles offered for software (all except FREE). */
	function getBillingCycles()
	{
		$sql = "SELECT id, cycle_key, cycle_name FROM billing_cycle
				WHERE status=1 AND cycle_key <> 'FREE' ORDER BY sl";
		return $this->db->query($sql)->result_array();
	}

	/** Active currencies. */
	function getCurrencies()
	{
		$sql = "SELECT id, code, symbol FROM currencies WHERE status=1 ORDER BY id";
		return $this->db->query($sql)->result_array();
	}

	/** Pricing matrix for a product: [currency_id][billing_cycle_id] => recurring_amount. */
	function getPricingMatrix($productId)
	{
		$productId = (int) $productId;
		if ($productId <= 0) {
			return array();
		}
		$sql = "SELECT currency_id, billing_cycle_id, first_pay_amount, recurring_amount
				FROM software_pricing WHERE product_id = ? AND status = 1";
		$rows = $this->db->query($sql, array($productId))->result_array();

		$matrix = array();
		foreach ($rows as $row) {
			$matrix[$row['currency_id']][$row['billing_cycle_id']] = $row['recurring_amount'];
		}
		return $matrix;
	}

	/**
	 * Upsert the pricing matrix. Each cell price is stored as both the first-pay
	 * and recurring amount (no separate setup fee in the UI for now). Empty cells
	 * are removed so the product simply doesn't offer that currency/cycle.
	 *
	 * @param int   $productId
	 * @param array $pricingData [currency_id][cycle_id] => price
	 */
	function savePricingMatrix($productId, $pricingData)
	{
		$productId = (int) $productId;
		if ($productId <= 0 || !is_array($pricingData)) {
			return;
		}

		$now     = date('Y-m-d H:i:s');
		$adminId = function_exists('getAdminId') ? getAdminId() : null;

		foreach ($pricingData as $currencyId => $cycles) {
			if (!is_array($cycles)) {
				continue;
			}
			foreach ($cycles as $cycleId => $price) {
				$price = trim($price);
				if ($price !== '' && is_numeric($price) && (float) $price >= 0) {
					$amt = (float) $price;
					$sql = "INSERT INTO software_pricing
								(product_id, currency_id, billing_cycle_id, first_pay_amount, recurring_amount, status, inserted_on, inserted_by)
							VALUES (?, ?, ?, ?, ?, 1, ?, ?)
							ON DUPLICATE KEY UPDATE
								first_pay_amount = VALUES(first_pay_amount),
								recurring_amount = VALUES(recurring_amount),
								status = 1, updated_on = ?, updated_by = ?";
					$this->db->query($sql, array(
						$productId, (int) $currencyId, (int) $cycleId, $amt, $amt,
						$now, $adminId, $now, $adminId,
					));
				} else {
					$this->db->query(
						"DELETE FROM software_pricing WHERE product_id = ? AND currency_id = ? AND billing_cycle_id = ?",
						array($productId, (int) $currencyId, (int) $cycleId)
					);
				}
			}
		}
	}

	// ─── admin: features (plan_features) ─────────────────────────────────

	/** Raw stored feature rows for a product: feature_key => feature_value. */
	function getStoredFeatures($productId)
	{
		$productId = (int) $productId;
		if ($productId <= 0) {
			return array();
		}
		$rows = $this->db->get_where($this->features_table, array('plan_id' => $productId))->result_array();
		$map = array();
		foreach ($rows as $row) {
			$map[$row['feature_key']] = $row['feature_value'];
		}
		return $map;
	}

	/**
	 * Replace a product's feature rows. $features is key=>value; blank keys skipped.
	 */
	function saveFeatures($productId, $features)
	{
		$productId = (int) $productId;
		if ($productId <= 0) {
			return;
		}
		$this->db->where('plan_id', $productId)->delete($this->features_table);

		if (!is_array($features)) {
			return;
		}
		foreach ($features as $key => $value) {
			$key = trim($key);
			if ($key === '') {
				continue;
			}
			$this->db->insert($this->features_table, array(
				'plan_id'       => $productId,
				'feature_key'   => $key,
				'feature_value' => (string) $value,
			));
		}
	}
}
