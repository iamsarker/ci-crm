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

if ( ! function_exists('license_client')) {
	/**
	 * Lazily load and return the shared License_client library instance.
	 * Use to read this install's own license status / tier.
	 *
	 * @return License_client
	 */
	function license_client()
	{
		$CI =& get_instance();
		if ( ! isset($CI->license_client)) {
			$CI->load->library('license_client');
		}
		return $CI->license_client;
	}
}

if ( ! function_exists('license_state')) {
	/**
	 * The resolved license state for this install. On a configured client
	 * install this is the cached phone-home verdict
	 * ({valid, status, plan_key, expires, features, message}); on a master or
	 * unconfigured install it is a benign "not enforced" verdict.
	 *
	 * @return array
	 */
	function license_state()
	{
		$lc = license_client();
		if ( ! $lc->is_managed_client()) {
			return array(
				'valid'    => true,
				'status'   => $lc->is_master() ? 'master' : 'unmanaged',
				'plan_key' => null,
				'features' => array(),
				'message'  => 'License enforcement not active on this install.',
			);
		}
		return $lc->state();
	}
}

if ( ! function_exists('feature_enabled')) {
	/**
	 * INSTALL-LEVEL feature gate — "is THIS install licensed for feature X?".
	 * This is the primitive to use when gating product capabilities in the
	 * self-hosted model (one install = one license = one tier).
	 *
	 *   - Client install:  gated to the purchased tier's remote feature map.
	 *                      An invalid/suspended/expired license → FALSE.
	 *   - Master / unconfigured: TRUE (the vendor's own CRM and dev installs
	 *                      run at full capability).
	 *
	 * Contrast with entitlement_can($companyId, $key), which answers the same
	 * question per CUSTOMER COMPANY (the legacy multi-tenant SaaS model).
	 *
	 *   if ( ! feature_enabled('branding_removal')) { echo $poweredBy; }
	 *
	 * @param  string $key
	 * @return bool
	 */
	function feature_enabled($key)
	{
		$lc = license_client();
		if ( ! $lc->is_managed_client()) {
			return TRUE; // vendor master / dev install: no gating
		}
		if ( ! $lc->is_valid()) {
			return FALSE; // suspended / expired / invalid client: baseline only
		}
		$features = $lc->features();
		return isset($features[$key]) ? (bool) $features[$key] : FALSE;
	}
}

if ( ! function_exists('feature_value')) {
	/**
	 * INSTALL-LEVEL numeric feature value (e.g. support_response_hours) from the
	 * licensed tier. Returns $default on master/unconfigured installs (no tier
	 * in force) or when the key is absent.
	 *
	 * @param  string $key
	 * @param  int    $default
	 * @return int
	 */
	function feature_value($key, $default = 0)
	{
		$lc = license_client();
		if ( ! $lc->is_managed_client() || ! $lc->is_valid()) {
			return $default;
		}
		$features = $lc->features();
		return isset($features[$key]) ? (int) $features[$key] : $default;
	}
}

if ( ! function_exists('require_feature')) {
	/**
	 * Controller guard: allow the request only if this install is licensed for
	 * $key, otherwise flash a message and redirect. Use at the top of a gated
	 * controller action / constructor:
	 *
	 *   require_feature('software_license_selling', 'whmazadmin/dashboard');
	 *
	 * For AJAX/JSON endpoints prefer an inline feature_enabled() check that
	 * returns a JSON error instead of redirecting.
	 *
	 * @param  string $key
	 * @param  string $redirect_uri  where to send a blocked request (CI route)
	 * @param  string $message       flash message (optional)
	 * @return void   redirects (and exits) when not entitled
	 */
	function require_feature($key, $redirect_uri = '', $message = null)
	{
		if (feature_enabled($key)) {
			return;
		}
		$CI =& get_instance();
		$msg = $message ?: 'This feature is not included in your current license plan.';
		if (isset($CI->session)) {
			$CI->session->set_flashdata('alert_error', $msg);
		}
		redirect($redirect_uri !== '' ? $redirect_uri : '/');
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
