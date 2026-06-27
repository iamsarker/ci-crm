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
		$plan['id']            = (int) $plan['id'];
		$plan['price_monthly'] = (float) $plan['price_monthly'];
		$plan['price_annual']  = (float) $plan['price_annual'];
		$plan['is_popular']    = (bool) $plan['is_popular'];
		$plan['sort_order']    = (int) $plan['sort_order'];
		$plan['is_active']     = (bool) $plan['is_active'];

		return $plan;
	}
}
