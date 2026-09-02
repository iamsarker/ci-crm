<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Tenant helper — reseller tenancy for the admin portal (v2.0.0).
 *
 * The admin portal historically had no authorization layer: every logged-in
 * admin was omnipotent, and `admin_roles` is dead scaffolding (no rows, no PHP
 * references). This file is the single source of truth for "which companies may
 * the logged-in admin see", used by the RequestGuard hook, ssp_sql_query(), the
 * WHMAZADMIN_Controller guards, and the admin menu.
 *
 * Deliberately NOT added to whmaz_helper.php: that file is IonCube-encoded and
 * autoloaded on both portals, so every edit there costs a re-encode and a
 * client-portal smoke test. This file is not in the encode set.
 *
 * Tenancy is a binary discriminator on admin_users, not a role:
 *     admin_type 0 = platform staff  (company_id 0, sees everything)
 *     admin_type 1 = reseller admin  (company_id = the reseller's companies.id)
 */

if (!function_exists('adminType')) {
	/** 0 = platform staff, 1 = reseller admin. Unknown/logged-out reads as 0. */
	function adminType() {
		$ci = & get_instance();
		$admin = $ci->session->userdata('ADMIN');
		return !empty($admin['admin_type']) ? (int) $admin['admin_type'] : 0;
	}
}

if (!function_exists('isResellerAdmin')) {
	/** True when the current admin session is a reseller tenant. */
	function isResellerAdmin() {
		return adminType() === 1;
	}
}

if (!function_exists('adminCompanyId')) {
	/** The reseller's companies.id, or 0 for platform staff. */
	function adminCompanyId() {
		$ci = & get_instance();
		$admin = $ci->session->userdata('ADMIN');
		return !empty($admin['company_id']) ? (int) $admin['company_id'] : 0;
	}
}

if (!function_exists('tenant_scope_ids_for')) {
	/**
	 * THE definition of "tenant scope": the company ids owned by $companyId —
	 * itself plus its sub-customers.
	 *
	 * Both entry points delegate here so the predicate exists exactly once:
	 *   - adminScopeIds()                    — admin portal, session-driven
	 *   - API_Controller::scopedCompanyIds() — REST API, api-key-driven
	 *
	 * Single level only: sub-resellers are not supported (v2.0.0 req. 6), and
	 * Reseller_model::assignSubCustomers() enforces is_reseller = 0 on subs.
	 *
	 * Request-cached per company id. Never cache this in the SESSION —
	 * transferring a customer between resellers must take effect immediately,
	 * and a session-cached list would stay a live cross-tenant read hole for
	 * the rest of that session.
	 */
	function tenant_scope_ids_for($companyId) {
		$companyId = (int) $companyId;
		// Fail closed: an unbound tenant sees nothing, rather than everything.
		if ($companyId <= 0) return array(0);

		static $cache = array();
		if (array_key_exists($companyId, $cache)) return $cache[$companyId];

		$ci = & get_instance();
		$rows = $ci->db->query(
			"SELECT id FROM companies WHERE (id = ? OR parent_company_id = ?) AND status = 1",
			array($companyId, $companyId)
		)->result_array();

		$ids = array_map('intval', array_column($rows, 'id'));
		// The tenant's own company is always in scope, even if its row is
		// mid-edit — otherwise an empty list could be misread as "no filter".
		if (!in_array($companyId, $ids, true)) $ids[] = $companyId;

		$cache[$companyId] = $ids;
		return $ids;
	}
}

if (!function_exists('adminScopeIds')) {
	/**
	 * Company ids the current ADMIN may see. Empty array for platform staff,
	 * meaning "no filter" — callers must gate on isResellerAdmin(), never on
	 * emptiness (see adminScopeSql).
	 */
	function adminScopeIds() {
		if (!isResellerAdmin()) return array();
		return tenant_scope_ids_for(adminCompanyId());
	}
}

if (!function_exists('adminScopeSql')) {
	/**
	 * SQL fragment restricting $col to the current admin's tenant scope.
	 * Returns '' for platform staff (no restriction).
	 *
	 * Gated on isResellerAdmin(), never on whether the id list is empty, so a
	 * bug that empties the list can never widen access to everything.
	 *
	 * Ids are integers from a controlled query and are cast again here, so the
	 * fragment is safe to concatenate. Written inline rather than with the
	 * query builder because this CI build ships a trimmed builder with no
	 * where_group_start()/where_group_end().
	 *
	 * @param string $col Column, optionally table-qualified (e.g. "o.company_id")
	 */
	function adminScopeSql($col) {
		if (!isResellerAdmin()) return '';

		$ids = adminScopeIds();
		if (empty($ids)) return ' 1 = 0 ';   // fail closed, never fail open

		return ' ' . $col . ' IN (' . implode(',', array_map('intval', $ids)) . ') ';
	}
}

if (!function_exists('adminOwnsCompany')) {
	/**
	 * True if $companyId is inside the current admin's tenant scope.
	 * Always true for platform staff.
	 */
	function adminOwnsCompany($companyId) {
		if (!isResellerAdmin()) return true;
		return in_array((int) $companyId, adminScopeIds(), true);
	}
}

if (!function_exists('tenant_deny')) {
	/**
	 * Refuse a request and STOP. Shared by RequestGuard (controller-level
	 * denial) and WHMAZADMIN_Controller::guardCompany() (record-level denial)
	 * so both refuse the same way.
	 *
	 * Never returns.
	 *
	 * ⚠️ Sends the body directly instead of $this->output->set_output().
	 * The Output class only flushes during CI's end-of-request _display(), so
	 * set_output() followed by exit() produces an EMPTY body with
	 * `content-type: text/html` — the trap documented in CLAUDE.md that every
	 * webhook and ssp endpoint works around the same way.
	 *
	 * @param string $message    Shown to the user / returned in the JSON envelope
	 * @param string $redirectTo Admin route for non-AJAX requests; '' = plain 403
	 */
	function tenant_deny($message = 'You do not have access to that area.', $redirectTo = 'whmazadmin/dashboard/index') {
		$ci = & get_instance();

		$isJson = $ci->input->is_ajax_request()
			|| preg_match('/_api$/i', (string) $ci->router->fetch_method());

		if ($isJson) {
			set_status_header(403);
			header('Content-Type: application/json');
			echo json_encode(array(
				'success' => false,
				'error'   => array('code' => 'forbidden', 'message' => $message),
			));
			exit;
		}

		if ($redirectTo === '') {
			set_status_header(403);
			header('Content-Type: text/plain');
			echo 'Forbidden';
			exit;
		}

		$ci->session->set_flashdata('admin_error', $message);
		redirect($redirectTo, 'refresh');
		exit;
	}
}

if (!function_exists('admin_can')) {
	/**
	 * Capability check against src/config/capabilities.php.
	 *
	 * ALLOWLIST, default-deny: a controller absent from the map is denied to
	 * resellers. This direction is deliberate — enumerating blocked controllers
	 * instead would mean any controller added later ships silently exposed.
	 *
	 * Platform staff always pass. Both the RequestGuard hook and the admin menu
	 * read this, so enforcement and navigation cannot drift apart.
	 *
	 * @param string $class  Controller name (case-insensitive), e.g. "order"
	 * @param string $method Method name; '' checks controller-level access only
	 */
	function admin_can($class, $method = '') {
		if (!isResellerAdmin()) return true;

		$ci = & get_instance();
		static $map = null;
		if ($map === null) {
			$ci->config->load('capabilities', TRUE, TRUE);
			$map = $ci->config->item('reseller', 'capabilities');
			if (!is_array($map)) $map = array();
		}

		$class = strtolower(trim((string) $class));
		if (!isset($map[$class])) return false;

		$allowed = $map[$class];
		if (in_array('*', $allowed, true)) return true;
		if ($method === '') return true;   // controller is listed at all

		return in_array(strtolower(trim((string) $method)), array_map('strtolower', $allowed), true);
	}
}
