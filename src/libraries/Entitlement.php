<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Entitlement
 * -------------------------------------------------------------------------
 * Feature-gating layer. Resolves the plan currently in force for an account
 * (a customer company) and answers whether a feature is granted / its value.
 *
 * "Account" == companies.id (a.k.a. order_licenses.company_id). When an account
 * has no active plan subscription it defaults to the most restrictive plan
 * (Basic), so callers always get a safe answer.
 *
 * Usage:
 *   $this->load->library('entitlement');
 *   $this->entitlement->can($companyId, 'branding_removal');        // bool
 *   $this->entitlement->value($companyId, 'support_response_hours'); // int
 *
 * Or via the procedural wrappers (autoloaded helper):
 *   entitlement_can($companyId, 'branding_removal');
 *   entitlement_value($companyId, 'support_response_hours');
 *
 * @see src/helpers/entitlement_helper.php
 * @see src/models/Subscription_model.php
 */
class Entitlement {

	/** @var CI_Controller */
	protected $CI;

	/** Per-request cache of resolved feature maps, keyed by company id. */
	private $cache = array();

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->config->load('plans', TRUE, TRUE);
		$this->CI->load->model('Plan_model');
		$this->CI->load->model('Subscription_model');
	}

	/**
	 * Whether an account is entitled to a feature flag.
	 * Universal features are always TRUE; unknown flags default to FALSE.
	 *
	 * @param  int    $company_id
	 * @param  string $feature_key
	 * @return bool
	 */
	public function can($company_id, $feature_key)
	{
		if ($this->_isUniversal($feature_key)) {
			return TRUE;
		}

		$features = $this->_featuresFor($company_id);
		return isset($features[$feature_key]) ? (bool) $features[$feature_key] : FALSE;
	}

	/**
	 * Numeric value of a feature for an account (e.g. support_response_hours).
	 * Unknown flags default to 0.
	 *
	 * @param  int    $company_id
	 * @param  string $feature_key
	 * @return int
	 */
	public function value($company_id, $feature_key)
	{
		$features = $this->_featuresFor($company_id);
		return isset($features[$feature_key]) ? (int) $features[$feature_key] : 0;
	}

	/**
	 * The full resolved feature map for an account (universal + differentiated).
	 *
	 * @param  int $company_id
	 * @return array feature_key => bool|int
	 */
	public function all($company_id)
	{
		return $this->_featuresFor($company_id);
	}

	/**
	 * The plan_key currently in force for an account, defaulting to the most
	 * restrictive plan when there is no active subscription.
	 *
	 * @param  int $company_id
	 * @return string
	 */
	public function plan_key($company_id)
	{
		$key = $this->CI->Subscription_model->get_active_plan_key_for_company($company_id);
		return $key ?: (string) $this->CI->config->item('plan_default_key', 'plans');
	}

	// ─── internals ───────────────────────────────────────────────────────

	private function _featuresFor($company_id)
	{
		$company_id = (int) $company_id;

		if ( ! isset($this->cache[$company_id])) {
			$plan = $this->CI->Plan_model->get_by_key($this->plan_key($company_id));
			$this->cache[$company_id] = isset($plan['features']) ? $plan['features'] : array();
		}

		return $this->cache[$company_id];
	}

	private function _isUniversal($feature_key)
	{
		$universal = (array) $this->CI->config->item('plan_universal_features', 'plans');
		return in_array($feature_key, $universal, TRUE);
	}
}
