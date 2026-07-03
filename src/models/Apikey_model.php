<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Apikey_model
 * -------------------------------------------------------------------------
 * Manages third-party REST API credentials (table `api_keys`). Each key
 * belongs to a reseller `companies` row. The public identifier (`key_id`) is
 * stored plain; the secret is stored only as password_hash() and shown to the
 * admin exactly once at creation / regeneration.
 *
 * This model is the single source of truth for:
 *   - the canonical scope list (availableScopes / scopeGroups)
 *   - credential generation
 *   - request-time authentication (authenticate), used by the /api module
 *   - request logging + per-key rate limiting
 *
 * @see src/controllers/whmazadmin/Apikey.php (admin CRUD)
 * @see src/modules/api/controllers/  (consumers of authenticate/logRequest)
 */
class Apikey_model extends CI_Model {

	var $table;

	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->table = "api_keys";
	}

	// ─── Scopes (canonical list) ─────────────────────────────

	/** Scopes grouped by resource, for the admin UI. */
	public static function scopeGroups() {
		return array(
			'Domains'   => array('domains:read'   => 'View domains', 'domains:write'   => 'Register / transfer / renew domains'),
			'Hosting'   => array('hosting:read'   => 'View services', 'hosting:write'   => 'Create / suspend / terminate services'),
			'Orders'    => array('orders:read'    => 'View orders',   'orders:write'    => 'Create orders'),
			'Invoices'  => array('invoices:read'  => 'View invoices', 'invoices:write'  => 'Update invoice payment status'),
			'Customers' => array('customers:read' => 'View customers','customers:write' => 'Create / update sub-customers'),
			'Licenses'  => array('licenses:read'  => 'View licenses', 'licenses:write'  => 'Issue / manage licenses'),
		);
	}

	/** Flat list of every valid scope string. */
	public static function availableScopes() {
		$all = array();
		foreach (self::scopeGroups() as $scopes) {
			$all = array_merge($all, array_keys($scopes));
		}
		return $all;
	}

	// ─── CRUD ────────────────────────────────────────────────

	function getDetail($id) {
		if (empty($id) || !is_numeric($id) || intval($id) <= 0) {
			return array();
		}
		// Active (1) or revoked (2) — not soft-deleted (0).
		$sql = "SELECT ak.*, c.name AS company_name
				FROM {$this->table} ak
				JOIN companies c ON c.id = ak.company_id
				WHERE ak.id = ? AND ak.status IN (1,2)";
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

	// ─── Credential generation ──────────────────────────────

	/** Generate a fresh key_id + secret pair (secret returned in clear once). */
	function generateCredentials() {
		$keyId  = 'wk_' . bin2hex(random_bytes(12));   // public identifier
		$secret = 'ws_' . bin2hex(random_bytes(24));   // shown once
		return array(
			'key_id'         => $keyId,
			'secret'         => $secret,
			'secret_hash'    => password_hash($secret, PASSWORD_DEFAULT),
			'secret_preview' => substr($secret, -4),
		);
	}

	/** Rotate the secret on an existing key. Returns the new clear secret. */
	function regenerateSecret($id) {
		$cred = $this->generateCredentials();
		$this->db->where('id', intval($id));
		$this->db->update($this->table, array(
			'secret_hash'    => $cred['secret_hash'],
			'secret_preview' => $cred['secret_preview'],
			'updated_on'     => getDateTime(),
			'updated_by'     => getAdminId(),
		));
		return $cred['secret'];
	}

	function setStatus($id, $status) {
		$this->db->where('id', intval($id));
		$this->db->update($this->table, array(
			'status'     => intval($status),
			'updated_on' => getDateTime(),
			'updated_by' => getAdminId(),
		));
	}

	// ─── Request-time authentication (used by /api module) ──

	/**
	 * Authenticate an incoming API request.
	 *
	 * @return array ['ok'=>bool, 'code'=>int(http), 'error'=>string, 'key'=>row]
	 *   On success ok=true and 'key' holds the api_keys row (with company_id).
	 */
	function authenticate($keyId, $secret, $ip) {
		$keyId  = trim((string) $keyId);
		$secret = (string) $secret;

		if ($keyId === '' || $secret === '') {
			return array('ok' => false, 'code' => 401, 'error' => 'Missing API credentials.');
		}

		$row = $this->db->query(
			"SELECT * FROM {$this->table} WHERE key_id = ? LIMIT 1",
			array($keyId)
		)->row_array();

		if (empty($row)) {
			return array('ok' => false, 'code' => 401, 'error' => 'Invalid API key.');
		}
		if (intval($row['status']) !== 1) {
			return array('ok' => false, 'code' => 403, 'error' => 'API key is revoked or disabled.');
		}
		if (!empty($row['expires_at']) && strtotime($row['expires_at']) < time()) {
			return array('ok' => false, 'code' => 403, 'error' => 'API key has expired.');
		}
		if (!password_verify($secret, $row['secret_hash'])) {
			return array('ok' => false, 'code' => 401, 'error' => 'Invalid API secret.');
		}
		if (!$this->ipAllowed($row['ip_whitelist'], $ip)) {
			return array('ok' => false, 'code' => 403, 'error' => 'Request IP not allowed for this key.');
		}

		// The owning company must still be an API-enabled reseller.
		$company = $this->db->query(
			"SELECT c.id, c.is_reseller, rp.allow_api
			 FROM companies c
			 LEFT JOIN reseller_profiles rp ON rp.company_id = c.id AND rp.status = 1
			 WHERE c.id = ? AND c.status = 1 LIMIT 1",
			array($row['company_id'])
		)->row_array();

		if (empty($company) || intval($company['is_reseller']) !== 1 || intval($company['allow_api']) !== 1) {
			return array('ok' => false, 'code' => 403, 'error' => 'API access is disabled for this account.');
		}

		return array('ok' => true, 'code' => 200, 'error' => '', 'key' => $row);
	}

	/** True if $ip is permitted by the whitelist (empty whitelist = allow any). */
	function ipAllowed($whitelist, $ip) {
		$whitelist = trim((string) $whitelist);
		if ($whitelist === '') return true;

		$entries = preg_split('/[\s,]+/', $whitelist, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($entries as $entry) {
			if (strpos($entry, '/') !== false) {
				if ($this->cidrMatch($ip, $entry)) return true;
			} elseif ($entry === $ip) {
				return true;
			}
		}
		return false;
	}

	/** IPv4 CIDR match. */
	private function cidrMatch($ip, $cidr) {
		list($subnet, $bits) = array_pad(explode('/', $cidr, 2), 2, null);
		if ($bits === null || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) || !filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
			return false;
		}
		$ipLong     = ip2long($ip);
		$subnetLong = ip2long($subnet);
		$bits       = (int) $bits;
		if ($bits < 0 || $bits > 32) return false;
		$mask = $bits === 0 ? 0 : (-1 << (32 - $bits)) & 0xFFFFFFFF;
		return ($ipLong & $mask) === ($subnetLong & $mask);
	}

	/** Record usage on the key row (called after a successful auth). */
	function touchUsage($keyId, $ip) {
		$this->db->query(
			"UPDATE {$this->table}
			 SET last_used_at = ?, last_used_ip = ?, request_count = request_count + 1
			 WHERE id = ?",
			array(getDateTime(), $ip, intval($keyId))
		);
	}

	/**
	 * Insert a request log row at the START of a request (status pending) and
	 * return its id. Counting at request-start makes the per-second TPS cap hold
	 * under concurrency (the row is visible to sibling requests immediately).
	 */
	function startRequestLog($apiKeyId, $companyId, $method, $endpoint, $ip) {
		$this->db->set('api_key_id', intval($apiKeyId));
		$this->db->set('company_id', intval($companyId));
		$this->db->set('method', substr((string) $method, 0, 10));
		$this->db->set('endpoint', substr((string) $endpoint, 0, 255));
		$this->db->set('ip', $ip);
		// DB clock (NOW()) so the per-second window matches countRequestsThisSecond,
		// regardless of PHP/MySQL timezone or clock skew (DB may be remote).
		$this->db->set('created_on', 'NOW()', FALSE);
		$this->db->insert('api_request_logs');
		return $this->db->insert_id();
	}

	/** Finalise a started request log with its status + response time. */
	function finishRequestLog($logId, $statusCode, $responseMs = null) {
		$this->db->where('id', intval($logId));
		$this->db->update('api_request_logs', array(
			'status_code'      => intval($statusCode),
			'response_time_ms' => $responseMs !== null ? intval($responseMs) : null,
		));
	}

	/** Append a request to the audit log (one-shot; used for rejected requests). */
	function logRequest($apiKeyId, $companyId, $method, $endpoint, $ip, $statusCode, $responseMs = null) {
		$this->db->set('api_key_id', intval($apiKeyId));
		$this->db->set('company_id', intval($companyId));
		$this->db->set('method', substr((string) $method, 0, 10));
		$this->db->set('endpoint', substr((string) $endpoint, 0, 255));
		$this->db->set('ip', $ip);
		$this->db->set('status_code', intval($statusCode));
		if ($responseMs !== null) {
			$this->db->set('response_time_ms', intval($responseMs));
		}
		$this->db->set('created_on', 'NOW()', FALSE);   // DB clock, matches the rate-limit window
		$this->db->insert('api_request_logs');
	}

	/**
	 * Requests logged for a key within the current calendar second (TPS cap).
	 * Fixed 1-second window so the count resets each second.
	 */
	function countRequestsThisSecond($apiKeyId) {
		$row = $this->db->query(
			"SELECT COUNT(*) AS cnt FROM api_request_logs
			 WHERE api_key_id = ? AND created_on >= DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')",
			array(intval($apiKeyId))
		)->row_array();
		return !empty($row) ? intval($row['cnt']) : 0;
	}

	/** Requests logged for a key within the last N seconds (rate limiting). */
	function countRecentRequests($apiKeyId, $seconds) {
		$row = $this->db->query(
			"SELECT COUNT(*) AS cnt FROM api_request_logs
			 WHERE api_key_id = ? AND created_on >= (NOW() - INTERVAL ? SECOND)",
			array(intval($apiKeyId), intval($seconds))
		)->row_array();
		return !empty($row) ? intval($row['cnt']) : 0;
	}

	// ─── DataTable ───────────────────────────────────────────

	function getDataTableRecords($sqlQuery, $bindings) {
		$results = $this->db->query($sqlQuery, $bindings)->result_array();
		foreach ($results as &$row) {
			$row['encoded_id'] = safe_encode($row['id']);
		}
		return $results;
	}

	function countDataTableTotalRecords() {
		$data = $this->db->query("SELECT COUNT(id) as cnt FROM api_key_view WHERE status IN (1,2)")->result_array();
		return !empty($data) ? $data[0]['cnt'] : 0;
	}

	function countDataTableFilterRecords($where, $bindings) {
		$data = $this->db->query("SELECT COUNT(id) as cnt FROM api_key_view $where", $bindings)->result_array();
		return !empty($data) ? $data[0]['cnt'] : 0;
	}

	// ─── Stats ───────────────────────────────────────────────

	function getStats() {
		$stats = array('total' => 0, 'active' => 0, 'revoked' => 0, 'requests_today' => 0);
		$row = $this->db->query("SELECT
					SUM(CASE WHEN status IN (1,2) THEN 1 ELSE 0 END) AS total,
					SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS active,
					SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) AS revoked
				FROM {$this->table}")->row_array();
		if (!empty($row)) {
			$stats['total']   = intval($row['total']);
			$stats['active']  = intval($row['active']);
			$stats['revoked'] = intval($row['revoked']);
		}
		$req = $this->db->query("SELECT COUNT(*) AS cnt FROM api_request_logs WHERE DATE(created_on) = CURDATE()")->row_array();
		$stats['requests_today'] = !empty($req) ? intval($req['cnt']) : 0;
		return $stats;
	}

	// ─── Dropdown Helpers ────────────────────────────────────

	/** Reseller companies that may hold API keys. */
	function getApiEnabledResellers() {
		$sql = "SELECT c.id, c.name AS company_name, c.email
				FROM companies c
				JOIN reseller_profiles rp ON rp.company_id = c.id AND rp.status = 1
				WHERE c.status = 1 AND c.is_reseller = 1 AND rp.allow_api = 1
				ORDER BY c.name";
		return $this->db->query($sql)->result_array();
	}
}
