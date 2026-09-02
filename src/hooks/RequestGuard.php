<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * RequestGuard — admin portal tenant authorization (v2.0.0).
 *
 * Runs on `post_controller_constructor`, i.e. after the controller's
 * constructor (so the isLogin() redirect has already had its say) but BEFORE
 * the requested method executes. That is the only point where a single file
 * can block a method it does not know about.
 *
 * Why a hook rather than a per-controller guard: the admin portal has 34
 * controllers, and adding a call to each would mean editing ~23 files — 7 of
 * which are IonCube-encoded and would need re-encoding on every change. This
 * file is not in the encode set, and it blocks all of them.
 *
 * Platform admins (admin_type = 0) are returned early and see NO behaviour
 * change whatsoever. Customers and guests never reach the check either.
 *
 * ⚠️ Scope: fires only for the ROUTED controller. Modules::run() sub-calls do
 *    not re-trigger it. That is correct for the admin surface (always the
 *    routed controller); the api/ module keeps its own API_Controller scoping.
 *
 * @see src/config/capabilities.php  the allowlist this enforces
 * @see src/helpers/tenant_helper.php  admin_can() / isResellerAdmin()
 */
class RequestGuard
{
	public function __construct()
	{
		$CI = & get_instance();

		// Cheapest check first, and the one that keeps this a no-op for
		// everyone who is not a reseller tenant.
		if (!function_exists('isResellerAdmin') || !isResellerAdmin()) {
			return;
		}

		if (!$this->isAdminPortalRequest($CI)) {
			return;
		}

		$class  = $CI->router->fetch_class();
		$method = $CI->router->fetch_method();

		if (admin_can($class, $method)) {
			return;
		}

		$this->deny($CI, $class, $method);
	}

	/**
	 * Is this a request into src/controllers/whmazadmin/?
	 *
	 * Checks the router's directory AND the raw URI, and treats a match on
	 * either as admin. Deliberately redundant: if the router's directory were
	 * ever empty or shaped differently, relying on it alone would fail OPEN and
	 * silently hand a reseller the server credentials page.
	 */
	private function isAdminPortalRequest($CI)
	{
		$dir = trim(strtolower((string) $CI->router->fetch_directory()), '/');
		if ($dir === 'whmazadmin') {
			return true;
		}

		$seg = strtolower((string) $CI->uri->segment(1));
		return $seg === 'whmazadmin';
	}

	/** Refuse the request. Delegates to tenant_deny() so every refusal — hook
	 *  or record-level guard — looks the same to the client. */
	private function deny($CI, $class, $method)
	{
		log_message(
			'error',
			'RequestGuard: reseller admin #' . getAdminId() . ' (company ' . adminCompanyId() . ')'
			. ' denied ' . $class . '/' . $method
		);

		// Dashboard is always in the allowlist so this cannot loop — but were it
		// ever removed, redirecting there from itself would bounce forever.
		$redirectTo = (strtolower($class) === 'dashboard') ? '' : 'whmazadmin/dashboard/index';

		tenant_deny('Your account does not have access to this area.', $redirectTo);
	}
}
