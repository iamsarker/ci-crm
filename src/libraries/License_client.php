<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * License_client
 * -------------------------------------------------------------------------
 * The "phone-home" half of the licensing system, built into the SOLD product.
 * Every WHMAZ install ships the SAME source; this client is what makes one
 * source enforce different tiers. It calls the vendor's
 *
 *     {LICENSE_SERVER_URL}/license/verify
 *
 * with this install's license key, caches the returned feature map, and lets
 * the Entitlement layer gate features from that remote source of truth.
 *
 * Install roles (from .env):
 *   - MASTER  (IS_LICENSE_MASTER=true)  the vendor's own CRM that SELLS
 *     licenses. Never phones home to itself; entitlements resolve from the
 *     local database per company (legacy behaviour).
 *   - CLIENT  (LICENSE_KEY + LICENSE_SERVER_URL set)  a customer's install.
 *     Phones home; the whole install runs under the one tier it paid for.
 *   - UNCONFIGURED (neither)  keeps legacy local-DB behaviour, so existing
 *     installs are unaffected until a key is written (by the installer).
 *
 * Failure handling: never throws. A stale cache is refreshed on read; if the
 * server is unreachable the last-good verdict is served for GRACE_DAYS, after
 * which the license degrades to "invalid" (baseline features only).
 *
 * Self-hosted reality: the customer has the source, so this check is
 * removable. Encode this file (IonCube / SourceGuardian) to give it teeth.
 *
 * @see src/libraries/Entitlement.php
 * @see src/modules/license/controllers/License.php  (the server side)
 */
class License_client {

	const CHECK_INTERVAL_HOURS = 12;  // re-verify at most twice a day
	const GRACE_DAYS           = 7;   // serve last-good verdict while unreachable
	const HTTP_TIMEOUT         = 8;   // seconds

	/** Hard-coded anti-piracy verification endpoint (admin-login gate). */
	const ADMIN_VERIFY_URL = 'https://whmaz.com/api/verify-license.php';

	/** @var CI_Controller */
	protected $CI;

	/** @var array|null in-request memo of the resolved state */
	private $state = null;

	/** @var array last admin_authorized() response payload (for grace info) */
	private $adminVerdict = array();

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	// ─── admin-login gate ────────────────────────────────────────────────

	/**
	 * Anti-piracy check for the admin login. Hard-coded endpoint (NOT a config
	 * value, so a cracker can't repoint it at a fake server). Pure API call —
	 * no local cache: returns TRUE only if the server reports authorized for
	 * this install's LICENSE_KEY, FALSE on deny, missing key, or any error.
	 *
	 * The server may report authorized while inside a grace window (`grace` /
	 * `grace_until` in the response) — access is still granted, and the caller
	 * can surface a warning via admin_in_grace() / admin_grace_until().
	 */
	public function admin_authorized()
	{
		$this->adminVerdict = array();

		$key = trim((string) env('LICENSE_KEY', ''));
		if ($key === '' || $key === 'XXXX-XXXX-XXXX-XXXX') {
			return false;
		}

		$ch = curl_init(self::ADMIN_VERIFY_URL . '?license_key=' . rawurlencode($key));
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER     => array('Accept: application/json'),
			CURLOPT_TIMEOUT        => self::HTTP_TIMEOUT,
			CURLOPT_CONNECTTIMEOUT => self::HTTP_TIMEOUT,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
		));
		$body = curl_exec($ch);
		curl_close($ch);

		$data = json_decode((string) $body, true);
		if ( ! is_array($data)) {
			return false;
		}

		$this->adminVerdict = $data;
		return ! empty($data['authorized']);
	}

	/** TRUE if the last admin_authorized() verdict was inside a grace window. */
	public function admin_in_grace()
	{
		return ! empty($this->adminVerdict['grace']);
	}

	/** The grace expiry date (YYYY-MM-DD) from the last verdict, or '' if none. */
	public function admin_grace_until()
	{
		return isset($this->adminVerdict['grace_until'])
			? (string) $this->adminVerdict['grace_until']
			: '';
	}

	// ─── role ────────────────────────────────────────────────────────────

	/** The vendor's own selling CRM (never gated, never phones home). */
	public function is_master()
	{
		return env('IS_LICENSE_MASTER', false) === true;
	}

	public function license_key()
	{
		return trim((string) env('LICENSE_KEY', ''));
	}

	public function server_url()
	{
		return rtrim((string) env('LICENSE_SERVER_URL', ''), '/');
	}

	/**
	 * Whether entitlement resolution should defer to the remote license.
	 * TRUE only for a configured client install; master and unconfigured
	 * installs return FALSE and keep local-DB behaviour.
	 */
	public function is_managed_client()
	{
		return ! $this->is_master()
			&& $this->license_key() !== ''
			&& $this->server_url() !== '';
	}

	// ─── resolved state ──────────────────────────────────────────────────

	/**
	 * The current license state, refreshing from the server when the cache is
	 * stale. Never throws.
	 *
	 * @return array {valid, status, plan_key, expires, features, message, verified_at}
	 */
	public function state()
	{
		if ($this->state !== null) {
			return $this->state;
		}

		$cache = $this->_readCache();

		if ($this->_isFresh($cache)) {
			return $this->state = $cache;
		}

		$fresh = $this->_fetch();
		if ($fresh !== null) {
			$fresh['verified_at'] = time();
			$this->_writeCache($fresh);
			return $this->state = $fresh;
		}

		// Server unreachable — serve last-good within the grace window.
		if ($this->_withinGrace($cache)) {
			return $this->state = $cache;
		}

		return $this->state = $this->_invalid('Unable to verify license (server unreachable).');
	}

	/**
	 * Force a refresh regardless of cache freshness (installer / cron). Still
	 * falls back to the grace-window cache if the server is unreachable.
	 */
	public function verify($force = true)
	{
		if ( ! $force) {
			return $this->state();
		}

		$this->state = null;
		$fresh = $this->_fetch();
		if ($fresh !== null) {
			$fresh['verified_at'] = time();
			$this->_writeCache($fresh);
			return $this->state = $fresh;
		}

		$cache = $this->_readCache();
		return $this->state = ($this->_withinGrace($cache)
			? $cache
			: $this->_invalid('Unable to verify license (server unreachable).'));
	}

	public function features()
	{
		$s = $this->state();
		return isset($s['features']) && is_array($s['features']) ? $s['features'] : array();
	}

	public function is_valid() { $s = $this->state(); return ! empty($s['valid']); }
	public function status()   { $s = $this->state(); return isset($s['status'])   ? $s['status']   : 'invalid'; }
	public function plan_key() { $s = $this->state(); return isset($s['plan_key'])  ? $s['plan_key']  : null; }
	public function message()  { $s = $this->state(); return isset($s['message'])   ? $s['message']   : ''; }

	// ─── remote fetch ────────────────────────────────────────────────────

	/** Ping the vendor; returns the normalised verdict, or NULL on transport failure. */
	private function _fetch()
	{
		$key = $this->license_key();
		$base = $this->server_url();
		if ($key === '' || $base === '') {
			return null;
		}

		$post = http_build_query(array(
			'license_key' => $key,
			'domain'      => $this->_selfDomain(),
		));

		$ch = curl_init($base . '/license/verify');
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => $post,
			CURLOPT_TIMEOUT        => self::HTTP_TIMEOUT,
			CURLOPT_CONNECTTIMEOUT => self::HTTP_TIMEOUT,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_USERAGENT      => 'WHMAZ-License-Client/1.0',
		));
		$body = curl_exec($ch);
		$err  = curl_errno($ch);
		curl_close($ch);

		if ($err || $body === false || $body === '') {
			return null;
		}

		$data = json_decode($body, true);
		if ( ! is_array($data) || ! array_key_exists('valid', $data)) {
			return null;
		}

		return array(
			'valid'    => (bool) $data['valid'],
			'status'   => isset($data['status'])   ? $data['status']   : 'invalid',
			'plan_key' => isset($data['plan_key']) ? $data['plan_key'] : null,
			'expires'  => isset($data['expires'])  ? $data['expires']  : null,
			'features' => isset($data['features']) && is_array($data['features']) ? $data['features'] : array(),
			'message'  => isset($data['message'])  ? $data['message']  : '',
		);
	}

	private function _selfDomain()
	{
		if (function_exists('base_url')) {
			$host = parse_url(base_url(), PHP_URL_HOST);
			if ($host) {
				return $host;
			}
		}
		return isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
	}

	// ─── cache file ──────────────────────────────────────────────────────

	private function _cacheFile()
	{
		return FCPATH . 'uploadedfiles/license/state.json';
	}

	private function _readCache()
	{
		$f = $this->_cacheFile();
		if ( ! is_file($f)) {
			return array();
		}
		$data = json_decode((string) file_get_contents($f), true);
		return is_array($data) ? $data : array();
	}

	private function _writeCache($state)
	{
		$dir = dirname($this->_cacheFile());
		if ( ! is_dir($dir)) {
			@mkdir($dir, 0755, true);
		}
		@file_put_contents($this->_cacheFile(), json_encode($state));
	}

	private function _isFresh($cache)
	{
		if (empty($cache['verified_at'])) {
			return false;
		}
		return (time() - (int) $cache['verified_at']) < (self::CHECK_INTERVAL_HOURS * 3600);
	}

	private function _withinGrace($cache)
	{
		if (empty($cache['verified_at'])) {
			return false;
		}
		return (time() - (int) $cache['verified_at']) < (self::GRACE_DAYS * 86400);
	}

	private function _invalid($message)
	{
		return array(
			'valid'       => false,
			'status'      => 'invalid',
			'plan_key'    => null,
			'expires'     => null,
			'features'    => array(),
			'message'     => $message,
			'verified_at' => 0,
		);
	}
}
