<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/* load the MX_Router class */
require_once APPPATH . "third_party/MX/Controller.php";

class WHMAZADMIN_Controller extends MX_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('Adminauth_model');

		// csrf_regenerate is TRUE, so EVERY POST rotates the cookie. A page only
		// knows the token it was rendered with, so without this the second AJAX
		// POST from any page posts a dead token and 403s — the first one works,
		// which is what makes it look intermittent. Sent on every response (not
		// just processRestCall) so the $.ajaxSetup `complete` handler in
		// include/footer_script.php can keep the page's token in step.
		$this->sendCsrfHeaders();
	}

	function isLogin(){
		$admin = $this->session->has_userdata('ADMIN') ? $this->session->userdata('ADMIN') : array('id' => 0, 'email' => '');
		if( !empty($admin) && $admin['id'] > 0 ){
			$cnt = $this->Adminauth_model->countDbSession($admin['id']);
			if( $cnt > 0 ){
				// Reseller tenants must still be live. admin_logins.active is
				// never set to 0 anywhere, so countDbSession() above really
				// means "has ever logged in successfully" and can never revoke
				// anything. Without this clause, "deactivate reseller" would be
				// purely cosmetic until the session happened to expire.
				if( !empty($admin['admin_type']) && (int)$admin['admin_type'] === 1 ){
					if( !$this->resellerAdminStillLive($admin) ){
						$this->session->set_userdata('ADMIN', array('id' => 0, 'email' => ''));
						return false;
					}
				}
				return true;
			} else{
				$resp = array('id' => 0, 'email' => '');
				$this->session->set_userdata('ADMIN', $resp); // set empty array
			}
		}
		return false;
	}

	/**
	 * Is this reseller admin's company still an active reseller?
	 *
	 * Checked on every request rather than cached in the session, so that
	 * deactivating a reseller takes effect on their very next click.
	 */
	private function resellerAdminStillLive($admin){
		$companyId = !empty($admin['company_id']) ? (int)$admin['company_id'] : 0;
		if( $companyId <= 0 ) return false;   // unbound reseller admin: fail closed

		$row = $this->db->query(
			"SELECT 1 AS ok
			   FROM companies c
			   JOIN reseller_profiles rp ON rp.company_id = c.id
			  WHERE c.id = ? AND c.status = 1 AND c.is_reseller = 1 AND rp.status = 1
			  LIMIT 1",
			array($companyId)
		)->row_array();

		return !empty($row);
	}

	/**
	 * Abort unless $companyId is inside the current admin's tenant scope.
	 * Always passes for platform staff. Never returns on failure.
	 *
	 * The RequestGuard hook blocks whole controllers, but it cannot know which
	 * record a positional id in the URL refers to. This is the per-record half:
	 * without it, a reseller can act on another tenant's data through an
	 * allowed controller simply by guessing an integer — including irreversible
	 * operations like Company::terminate_cpanel_account().
	 */
	protected function guardCompany($companyId){
		if( adminOwnsCompany($companyId) ) return true;

		log_message('error',
			'guardCompany: reseller admin #' . getAdminId() . ' (company ' . adminCompanyId() . ')'
			. ' denied access to company ' . (int)$companyId
			. ' via ' . $this->router->fetch_class() . '/' . $this->router->fetch_method()
		);
		tenant_deny('That record does not belong to your account.');
	}

	/**
	 * Look up a record's owning company and guard on it.
	 *
	 * Denies when the row does not exist too: a missing row cannot be proven to
	 * belong to this tenant, and answering "not found" vs "forbidden"
	 * differently would leak which ids exist.
	 *
	 * @param  string $table Table or view holding the record
	 * @param  int    $id    Primary key value
	 * @param  string $col   Column naming the owning company
	 * @return int           The owning company id, for reuse by the caller
	 */
	protected function guardRecord($table, $id, $col = 'company_id'){
		return $this->guardRecordBy($table, 'id', (int)$id, $col);
	}

	/**
	 * As guardRecord(), but keyed on an arbitrary column — for records
	 * addressed by uuid rather than by primary key (invoices, orders).
	 *
	 * @param  string $table    Table or view holding the record
	 * @param  string $keyCol   Column to look the record up by
	 * @param  mixed  $keyVal   Value to match (bound, not interpolated)
	 * @param  string $ownerCol Column naming the owning company
	 * @return int              The owning company id, for reuse by the caller
	 */
	protected function guardRecordBy($table, $keyCol, $keyVal, $ownerCol = 'company_id'){
		// Identifiers are developer-supplied constants, never request input,
		// but scrub them anyway so a future careless call site cannot inject.
		$table    = preg_replace('/[^A-Za-z0-9_]/', '', (string)$table);
		$keyCol   = preg_replace('/[^A-Za-z0-9_]/', '', (string)$keyCol);
		$ownerCol = preg_replace('/[^A-Za-z0-9_]/', '', (string)$ownerCol);

		$row = $this->db->query(
			"SELECT `{$ownerCol}` AS owner_company_id FROM `{$table}` WHERE `{$keyCol}` = ? LIMIT 1",
			array($keyVal)
		)->row_array();

		if( empty($row) ){
			// Platform staff: behave exactly as before and let the caller 404.
			if( !isResellerAdmin() ) return 0;
			// Reseller: a missing row cannot be proven to belong to this tenant,
			// and answering "not found" differently from "forbidden" would leak
			// which ids exist.
			tenant_deny('That record does not belong to your account.');
		}

		$this->guardCompany($row['owner_company_id']);
		return (int)$row['owner_company_id'];
	}

	/**
	 * Mint a CUSTOMER session for a company's owner user, so admin-portal code
	 * can hand off to the storefront payment flow (v2.0.0 Phase 3).
	 *
	 * Why this is needed at all: Phase 1 moved resellers onto the ADMIN login,
	 * and the whole payment stack -- invoicing/pay/*, every gateway init, the
	 * SameSite token restore -- reads getCustomerId()/getCompanyId() off a
	 * CUSTOMER session that a reseller admin therefore does not have. Rather
	 * than teach the payment stack about admin sessions (six gateways, each
	 * with its own return path), mint the session the stack already expects.
	 *
	 * Ported from API_Controller::actAsCustomer(), which solved the identical
	 * problem for stateless API requests. Same owner-user selection --
	 * ORDER BY user_type ASC, id ASC -- so the admin portal and the API act as
	 * the same user for the same company.
	 *
	 * ⚠️ This is why the reseller's `users` owner row must survive the move to
	 * admin-only login: it is the identity every company_id join and the payment
	 * session hang off.
	 *
	 * ⚠️ AND IT MUST STAY status = 1. Auth_model::getUserSessionData() -- which
	 * builds the session, and which is IonCube-encoded so it is not casually
	 * changed -- filters `u.status = 1 AND c.status = 1`. Retiring the
	 * reseller's client-portal access by setting their user row to status 0
	 * would therefore break top-up payments, and the symptom would be a silent
	 * failure at the Pay click rather than anything naming the user row. Block
	 * the client-portal LOGIN if that is wanted; do not overload status, which
	 * a dozen joins already read as "does this row count at all".
	 *
	 * ⚠️ SCOPE. Callers MUST guardCompany($companyId) first. This method
	 * deliberately does not, because it is also the path a platform admin uses
	 * to pay on a reseller's behalf; enforcing scope here would either block
	 * that or silently widen it. Impersonation without a guard in front of it
	 * is a tenancy hole.
	 *
	 * @param  int $companyId companies.id to act as
	 * @return int            users.id now in session, or 0 on failure
	 */
	protected function actAsCompanyCustomer($companyId){
		$companyId = (int) $companyId;
		if( $companyId <= 0 ) return 0;

		// status = 1 mirrors API_Controller::actAsCustomer() exactly, and is
		// also forced by getUserSessionData() below -- selecting a disabled
		// user here would just fail one step later with a vaguer message.
		$owner = $this->db->query(
			"SELECT id FROM users WHERE company_id = ? AND status = 1 ORDER BY user_type ASC, id ASC LIMIT 1",
			array($companyId)
		)->row_array();

		if( empty($owner) ){
			log_message('error', 'actAsCompanyCustomer: company #' . $companyId . ' has no active user row to act as.');
			return 0;
		}

		$this->load->model('Auth_model');
		$userData = $this->Auth_model->getUserSessionData((int) $owner['id']);
		if( empty($userData) ){
			log_message('error', 'actAsCompanyCustomer: could not build a session for user #' . $owner['id']);
			return 0;
		}

		$this->session->set_userdata('CUSTOMER', $userData);
		return (int) $owner['id'];
	}

	function processRestCall(){
		$_POST = json_decode(file_get_contents('php://input'), true);

		// Send updated CSRF token in response headers for Angular
		$this->sendCsrfHeaders();
	}

	function sendCsrfHeaders(){
		// Send CSRF token in response headers so Angular can update it
		header('X-CSRF-TOKEN-NAME: ' . $this->security->get_csrf_token_name());
		header('X-CSRF-TOKEN-HASH: ' . $this->security->get_csrf_hash());
	}

	function curlGetRequest($finalUrl){
		$ch = curl_init();
		$headers = array(
			'Accept: application/json',
			'Content-Type: application/json',
		);
		curl_setopt($ch, CURLOPT_URL, $finalUrl);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		// SECURITY: Ensure SSL verification is enabled
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		$resp = curl_exec($ch);
		curl_close($ch);
		return json_decode($resp);
	}

	function AppResponse($code, $msg, $data=array() ){
		return json_encode(array("code"=>$code, "msg"=>$msg, "data"=>$data));
	}

}
