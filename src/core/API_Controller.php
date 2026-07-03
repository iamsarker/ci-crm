<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . "third_party/MX/Controller.php";

/**
 * API_Controller
 * -------------------------------------------------------------------------
 * Base controller for the stateless, key-authenticated third-party REST API
 * (module `api`, routed under /api/v1/...). Autoloaded from src/core by the
 * MX module autoloader — same mechanism as WHMAZ_Controller / WHMAZADMIN_Controller.
 *
 * Every request must present:
 *   X-Api-Key:    <key_id>       (public identifier)
 *   X-Api-Secret: <secret>       (issued once at key creation)
 * or, equivalently:  Authorization: Bearer <key_id>:<secret>
 *
 * On success the request is scoped to the owning reseller company:
 *   $this->company_id  — the reseller's companies.id
 *   $this->scopes      — granted scope strings (see Apikey_model::scopeGroups)
 * Sub-resources are always constrained to that reseller and its sub-customers
 * (companies.parent_company_id = company_id) — see scopedCompanyIds().
 *
 * Responses use a fixed envelope:
 *   success: { "success": true,  "data": {...} }
 *   error:   { "success": false, "error": { "code": "...", "message": "..." } }
 *
 * @see src/models/Apikey_model.php  (authenticate / logRequest / rate limiting)
 */
class API_Controller extends MX_Controller
{
	/**
	 * Hard per-key throttle: maximum API requests allowed per second, applied
	 * to every key regardless of its (optional, per-minute) `rate_limit`.
	 * Central knob — change here to adjust the platform-wide TPS ceiling.
	 */
	const RATE_LIMIT_PER_SECOND = 5;

	/** @var array authenticated api_keys row */
	protected $api_key = array();
	/** @var int owning reseller companies.id */
	protected $company_id = 0;
	/** @var array granted scope strings */
	protected $scopes = array();
	/** @var array decoded JSON body merged with POST */
	protected $request = array();
	/** @var float request start (for response_time_ms) */
	private $started_at = 0.0;
	/** @var int api_request_logs id for this request (0 until accepted) */
	private $log_id = 0;

	public function __construct()
	{
		parent::__construct();
		$this->started_at = microtime(true);
		$this->load->model('Apikey_model');
		$this->load->helper('url');

		$this->_parseBody();
		$this->_authenticate();
	}

	// ─── Authentication ─────────────────────────────────────

	private function _authenticate()
	{
		list($keyId, $secret) = $this->_readCredentials();

		$result = $this->Apikey_model->authenticate($keyId, $secret, $this->input->ip_address());
		if (empty($result['ok'])) {
			$this->fail($result['code'], $result['error'], 'unauthorized');
		}

		$this->api_key    = $result['key'];
		$this->company_id = intval($result['key']['company_id']);
		$decoded          = json_decode($result['key']['scopes'], true);
		$this->scopes     = is_array($decoded) ? $decoded : array();

		// Hard per-key throttle: max N requests per second (applies to every key).
		if (self::RATE_LIMIT_PER_SECOND > 0
			&& $this->Apikey_model->countRequestsThisSecond($this->api_key['id']) >= self::RATE_LIMIT_PER_SECOND) {
			header('Retry-After: 1');
			$this->fail(429, 'Rate limit exceeded: max ' . self::RATE_LIMIT_PER_SECOND . ' requests per second.', 'rate_limited');
		}

		// Optional per-key rate limit (requests per minute; 0 = unlimited).
		$limit = intval($result['key']['rate_limit']);
		if ($limit > 0 && $this->Apikey_model->countRecentRequests($this->api_key['id'], 60) >= $limit) {
			header('Retry-After: 60');
			$this->fail(429, 'Rate limit exceeded. Try again shortly.', 'rate_limited');
		}

		$this->Apikey_model->touchUsage($this->api_key['id'], $this->input->ip_address());

		// Count this accepted request immediately (visible to sibling requests
		// for the per-second cap); finalised with status/timing in _respond().
		$this->log_id = $this->Apikey_model->startRequestLog(
			$this->api_key['id'], $this->company_id,
			$this->input->method(), $this->uri->uri_string(), $this->input->ip_address()
		);
	}

	/** Extract (key_id, secret) from headers. Supports X-Api-* and Bearer. */
	private function _readCredentials()
	{
		$keyId  = $this->_header('X-Api-Key', 'HTTP_X_API_KEY');
		$secret = $this->_header('X-Api-Secret', 'HTTP_X_API_SECRET');

		if (empty($keyId) || empty($secret)) {
			$auth = $this->_header('Authorization', 'HTTP_AUTHORIZATION');
			if (!empty($auth) && stripos($auth, 'Bearer ') === 0) {
				$token = trim(substr($auth, 7));
				if (strpos($token, ':') !== false) {
					list($keyId, $secret) = explode(':', $token, 2);
				}
			}
		}
		return array(trim((string) $keyId), (string) $secret);
	}

	/** Read a request header via CI, falling back to the raw $_SERVER key. */
	private function _header($name, $serverKey)
	{
		$val = $this->input->get_request_header($name, false);
		if (empty($val) && isset($_SERVER[$serverKey])) {
			$val = $_SERVER[$serverKey];
		}
		return $val;
	}

	// ─── Scope enforcement ──────────────────────────────────

	/** Abort with 403 unless the key holds $scope. */
	protected function requireScope($scope)
	{
		if (!in_array($scope, $this->scopes, true)) {
			$this->fail(403, "Missing required scope: {$scope}", 'insufficient_scope');
		}
	}

	/** Company ids this key may see: the reseller + its sub-customers. */
	protected function scopedCompanyIds()
	{
		$rows = $this->db->query(
			"SELECT id FROM companies WHERE (id = ? OR parent_company_id = ?) AND status = 1",
			array($this->company_id, $this->company_id)
		)->result_array();
		$ids = array_map('intval', array_column($rows, 'id'));
		if (empty($ids)) $ids = array($this->company_id);
		return $ids;
	}

	/** True if $companyId belongs to this reseller's scope. */
	protected function ownsCompany($companyId)
	{
		return in_array(intval($companyId), $this->scopedCompanyIds(), true);
	}

	// ─── Request input ──────────────────────────────────────

	private function _parseBody()
	{
		$raw = file_get_contents('php://input');
		$json = json_decode($raw, true);
		$this->request = is_array($json) ? $json : array();
		// Fall back to form/query params.
		$this->request = array_merge((array) $this->input->get(), (array) $this->input->post(), $this->request);
	}

	/** Read a single request field (JSON body > POST > GET). */
	protected function param($key, $default = null)
	{
		return array_key_exists($key, $this->request) ? $this->request[$key] : $default;
	}

	/** Ensure the HTTP method matches; else 405. */
	protected function requireMethod($method)
	{
		if (strtoupper($this->input->method()) !== strtoupper($method)) {
			$this->fail(405, "This endpoint requires HTTP {$method}.", 'method_not_allowed');
		}
	}

	// ─── Responses ──────────────────────────────────────────

	/** Success envelope. Terminates the request. */
	protected function ok($data = array(), $httpCode = 200)
	{
		$this->_respond($httpCode, array('success' => true, 'data' => $data));
	}

	/** Error envelope. Terminates the request. */
	protected function fail($httpCode, $message, $code = 'error')
	{
		$this->_respond($httpCode, array(
			'success' => false,
			'error'   => array('code' => $code, 'message' => $message),
		));
	}

	private function _respond($httpCode, array $payload)
	{
		$ms = (int) round((microtime(true) - $this->started_at) * 1000);

		// Log the request (best-effort; never let logging break the response).
		try {
			if ($this->log_id > 0) {
				// Accepted request — finalise its start-log row.
				$this->Apikey_model->finishRequestLog($this->log_id, $httpCode, $ms);
			} elseif (!empty($this->api_key)) {
				// Rejected after auth (e.g. rate limited / bad scope) — one-shot log.
				$this->Apikey_model->logRequest(
					$this->api_key['id'], $this->company_id,
					$this->input->method(), $this->uri->uri_string(),
					$this->input->ip_address(), $httpCode, $ms
				);
			}
		} catch (Exception $e) { /* ignore */ }

		// Emit directly (not via the Output class): we exit here to stop further
		// processing, and CI only flushes Output during its end-of-request
		// _display(), which exit skips — so set_output()+exit would send an empty
		// body. set_status_header() and header()/echo write immediately.
		$this->output->set_status_header($httpCode);
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($payload);
		exit;
	}

	/**
	 * GET a URL and decode JSON (SSL verification disabled, as with the
	 * registrar APIs). Returns the decoded array or null on error. Mirrors
	 * WHMAZ_Controller::curlGetRequest so registrar lookups work from the API.
	 */
	protected function curlGetJson($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		$body = curl_exec($ch);
		$err  = curl_error($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if ($err || $code >= 400) {
			log_message('error', 'API curlGetJson error: ' . ($err ?: ('HTTP ' . $code)));
			return null;
		}
		return json_decode($body, true);
	}

	/** Standard pagination window from ?page & ?per_page (max 100). */
	protected function pagination()
	{
		$page    = max(1, intval($this->param('page', 1)));
		$perPage = intval($this->param('per_page', 25));
		$perPage = $perPage < 1 ? 25 : min($perPage, 100);
		return array($perPage, ($page - 1) * $perPage, $page);
	}

	// ─── Storefront cart/checkout reuse (ApiCart / ApiCheckout) ─────────────

	/**
	 * Establish a CUSTOMER session for the acting customer so the storefront
	 * cart/checkout code (which reads getCustomerId()/getCompanyId()/currency
	 * from the session) runs unchanged on this stateless request.
	 *
	 * The `customer_id` param is a **users.id** (API-only convention; matches
	 * /me.reseller.customer_id and /customers[].customer_id). It must belong to
	 * a company within this reseller's scope (the reseller or a sub-customer).
	 * Omit it to act as the reseller's own owner user.
	 */
	protected function actAsCustomer()
	{
		$userId = intval($this->param('customer_id'));

		if ($userId > 0) {
			// customer_id == users.id — resolve its company and check scope.
			$u = $this->db->query(
				"SELECT id, company_id FROM users WHERE id = ? AND status = 1 LIMIT 1",
				array($userId)
			)->row_array();
			if (empty($u) || !$this->ownsCompany($u['company_id'])) {
				$this->fail(403, 'customer_id is not within your account scope.', 'forbidden');
			}
			$actUserId = intval($u['id']);
		} else {
			// Default: the reseller's own owner login user.
			$owner = $this->db->query(
				"SELECT id FROM users WHERE company_id = ? AND status = 1 ORDER BY user_type ASC, id ASC LIMIT 1",
				array($this->company_id)
			)->row_array();
			if (empty($owner)) {
				$this->fail(409, 'Your account has no login user to own a cart.', 'no_customer_user');
			}
			$actUserId = intval($owner['id']);
		}

		$this->load->model('Auth_model');
		$userData = $this->Auth_model->getUserSessionData($actUserId);
		if (empty($userData)) {
			$this->fail(500, 'Could not establish customer context.', 'server_error');
		}
		$this->session->set_userdata('CUSTOMER', $userData);

		// Currency: explicit param, else keep the session default.
		$currencyId = intval($this->param('currency_id'));
		if ($currencyId > 0) {
			$cur = $this->db->query("SELECT id, code FROM currencies WHERE id = ? AND status = 1", array($currencyId))->row_array();
			if (!empty($cur)) {
				$this->session->set_userdata('currency_id', intval($cur['id']));
				$this->session->set_userdata('currency_code', $cur['code']);
			}
		}
		return $actUserId;
	}

	/**
	 * Run a storefront cart/checkout method (verbatim) and translate its JSON
	 * envelope ({code,msg,data}) into the API envelope. Extra args pass through
	 * to the delegated method (e.g. the item id for cart/delete).
	 */
	protected function delegate($route)
	{
		$args = func_get_args();   // [route, ...extra]
		$raw  = call_user_func_array(array('Modules', 'run'), $args);
		$decoded = json_decode((string) $raw, true);

		// Some storefront methods (e.g. cart/delete) echo plain text, not JSON.
		if (!is_array($decoded)) {
			$this->ok(array('message' => trim((string) $raw) ?: 'OK'));
		}

		$code = isset($decoded['code']) ? intval($decoded['code']) : 200;
		if ($code === 401) {
			$this->fail(403, !empty($decoded['msg']) ? $decoded['msg'] : 'Not permitted for this customer.', 'forbidden');
		}
		if ($code !== 200) {
			$this->fail(422, !empty($decoded['msg']) ? $decoded['msg'] : 'Cart operation failed.', 'cart_error');
		}

		$this->ok(array(
			'message' => isset($decoded['msg']) ? $decoded['msg'] : null,
			'result'  => isset($decoded['data']) ? $decoded['data'] : $decoded,
		));
	}
}
