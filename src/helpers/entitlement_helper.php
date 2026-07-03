<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Entitlement helper
 * -------------------------------------------------------------------------
 * Thin procedural wrappers around the Entitlement library so feature gating
 * reads naturally anywhere in the app (controllers, models, views):
 *
 *   if (entitlement_can($companyId, 'branding_removal')) { ... }
 *   $sla = entitlement_value($companyId, 'support_response_hours');
 *
 * The library is loaded on first use. "account" == companies.id.
 *
 * @see src/libraries/Entitlement.php
 */

if ( ! function_exists('_entitlement')) {
	/**
	 * Lazily load and return the shared Entitlement library instance.
	 *
	 * @return Entitlement
	 */
	function _entitlement()
	{
		$CI =& get_instance();
		if ( ! isset($CI->entitlement)) {
			$CI->load->library('entitlement');
		}
		return $CI->entitlement;
	}
}

if ( ! function_exists('entitlement_can')) {
	/**
	 * Whether an account is entitled to a boolean feature flag.
	 *
	 * @param  int    $company_id
	 * @param  string $feature_key
	 * @return bool
	 */
	function entitlement_can($company_id, $feature_key)
	{
		return _entitlement()->can($company_id, $feature_key);
	}
}

if ( ! function_exists('entitlement_value')) {
	/**
	 * Numeric value of a feature for an account (e.g. support_response_hours).
	 *
	 * @param  int    $company_id
	 * @param  string $feature_key
	 * @return int
	 */
	function entitlement_value($company_id, $feature_key)
	{
		return _entitlement()->value($company_id, $feature_key);
	}
}

if ( ! function_exists('entitlement_plan_key')) {
	/**
	 * The plan_key currently in force for an account (defaults to the most
	 * restrictive plan when there is no active subscription).
	 *
	 * @param  int $company_id
	 * @return string
	 */
	function entitlement_plan_key($company_id)
	{
		return _entitlement()->plan_key($company_id);
	}
}

if ( ! function_exists('feature_label')) {
	/**
	 * Human-friendly display label for a feature key. Uses the map in
	 * config/plans.php; falls back to a humanized form of the key.
	 *
	 * @param  string $key
	 * @return string
	 */
	function feature_label($key)
	{
		$CI =& get_instance();
		$CI->config->load('plans', TRUE, TRUE);
		$labels = (array) $CI->config->item('plan_feature_labels', 'plans');

		if (isset($labels[$key])) {
			return $labels[$key];
		}
		return ucwords(str_replace('_', ' ', (string) $key));
	}
}
