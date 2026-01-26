<?php
/**
 * Login Attempt Model
 *
 * Handles rate limiting for login attempts to prevent brute force attacks.
 *
 * SECURITY FEATURE:
 * - Tracks failed login attempts per IP and email
 * - Blocks login after MAX_ATTEMPTS failures
 * - Auto-unlocks after LOCKOUT_TIME minutes
 *
 * @package CI-CRM
 * @since 1.0.2
 */
class Loginattempt_model extends CI_Model {

	// Configuration constants
	const MAX_ATTEMPTS = 5;           // Maximum failed attempts before lockout
	const LOCKOUT_TIME = 15;          // Lockout duration in minutes
	const CLEANUP_PROBABILITY = 10;   // 10% chance to cleanup old records on each check

	private $table = 'login_attempts';

	function __construct() {
		parent::__construct();
		$this->load->database();
	}

	/**
	 * Check if login is allowed for given identifier (IP or email)
	 *
	 * @param string $identifier IP address or email
	 * @param string $type 'ip' or 'email'
	 * @return array ['allowed' => bool, 'remaining_attempts' => int, 'unlock_time' => string|null]
	 */
	public function isLoginAllowed($identifier, $type = 'ip') {
		// Occasionally cleanup old records (10% chance)
		if (rand(1, 100) <= self::CLEANUP_PROBABILITY) {
			$this->cleanupOldAttempts();
		}

		$lockout_threshold = date('Y-m-d H:i:s', strtotime('-' . self::LOCKOUT_TIME . ' minutes'));

		// Count recent failed attempts
		$sql = "SELECT COUNT(*) as attempt_count, MAX(attempt_time) as last_attempt
				FROM {$this->table}
				WHERE identifier = ?
				AND identifier_type = ?
				AND attempt_time > ?
				AND is_successful = 0";

		$query = $this->db->query($sql, array($identifier, $type, $lockout_threshold));
		$result = $query->row();

		$attempt_count = $result ? (int)$result->attempt_count : 0;
		$remaining = self::MAX_ATTEMPTS - $attempt_count;

		if ($attempt_count >= self::MAX_ATTEMPTS) {
			// Calculate unlock time
			$last_attempt = strtotime($result->last_attempt);
			$unlock_time = date('Y-m-d H:i:s', $last_attempt + (self::LOCKOUT_TIME * 60));

			return array(
				'allowed' => false,
				'remaining_attempts' => 0,
				'unlock_time' => $unlock_time,
				'minutes_remaining' => ceil(($last_attempt + (self::LOCKOUT_TIME * 60) - time()) / 60)
			);
		}

		return array(
			'allowed' => true,
			'remaining_attempts' => $remaining,
			'unlock_time' => null,
			'minutes_remaining' => 0
		);
	}

	/**
	 * Record a login attempt
	 *
	 * @param string $identifier IP address or email
	 * @param string $type 'ip' or 'email'
	 * @param bool $is_successful Whether login was successful
	 * @param string $user_agent Browser user agent
	 * @return bool
	 */
	public function recordAttempt($identifier, $type = 'ip', $is_successful = false, $user_agent = '') {
		$data = array(
			'identifier' => $identifier,
			'identifier_type' => $type,
			'ip_address' => $this->input->ip_address(),
			'user_agent' => substr($user_agent, 0, 255),
			'is_successful' => $is_successful ? 1 : 0,
			'attempt_time' => date('Y-m-d H:i:s')
		);

		return $this->db->insert($this->table, $data);
	}

	/**
	 * Clear failed attempts for an identifier (call on successful login)
	 *
	 * @param string $identifier IP address or email
	 * @param string $type 'ip' or 'email'
	 * @return bool
	 */
	public function clearFailedAttempts($identifier, $type = 'ip') {
		$this->db->where('identifier', $identifier);
		$this->db->where('identifier_type', $type);
		$this->db->where('is_successful', 0);
		return $this->db->delete($this->table);
	}

	/**
	 * Clear all attempts for both IP and email on successful login
	 *
	 * @param string $email User email
	 * @param string $ip User IP address
	 * @return void
	 */
	public function clearAllAttempts($email, $ip) {
		$this->clearFailedAttempts($email, 'email');
		$this->clearFailedAttempts($ip, 'ip');
	}

	/**
	 * Record failed login attempt for both IP and email
	 *
	 * @param string $email User email
	 * @param string $ip User IP address
	 * @param string $user_agent Browser user agent
	 * @return void
	 */
	public function recordFailedAttempt($email, $ip, $user_agent = '') {
		$this->recordAttempt($ip, 'ip', false, $user_agent);
		if (!empty($email)) {
			$this->recordAttempt($email, 'email', false, $user_agent);
		}
	}

	/**
	 * Check if login is allowed based on both IP and email
	 *
	 * @param string $email User email
	 * @param string $ip User IP address
	 * @return array ['allowed' => bool, 'message' => string, 'minutes_remaining' => int]
	 */
	public function checkLoginAllowed($email, $ip) {
		// Check IP-based rate limit
		$ip_check = $this->isLoginAllowed($ip, 'ip');
		if (!$ip_check['allowed']) {
			return array(
				'allowed' => false,
				'message' => "Too many login attempts from your IP address. Please try again in {$ip_check['minutes_remaining']} minute(s).",
				'minutes_remaining' => $ip_check['minutes_remaining'],
				'type' => 'ip'
			);
		}

		// Check email-based rate limit
		if (!empty($email)) {
			$email_check = $this->isLoginAllowed($email, 'email');
			if (!$email_check['allowed']) {
				return array(
					'allowed' => false,
					'message' => "Too many login attempts for this account. Please try again in {$email_check['minutes_remaining']} minute(s).",
					'minutes_remaining' => $email_check['minutes_remaining'],
					'type' => 'email'
				);
			}
		}

		return array(
			'allowed' => true,
			'message' => '',
			'minutes_remaining' => 0,
			'remaining_attempts' => min($ip_check['remaining_attempts'], isset($email_check) ? $email_check['remaining_attempts'] : self::MAX_ATTEMPTS)
		);
	}

	/**
	 * Cleanup old attempt records (older than 24 hours)
	 *
	 * @return int Number of deleted records
	 */
	public function cleanupOldAttempts() {
		$threshold = date('Y-m-d H:i:s', strtotime('-24 hours'));
		$this->db->where('attempt_time <', $threshold);
		$this->db->delete($this->table);
		return $this->db->affected_rows();
	}

	/**
	 * Get recent login attempts for monitoring/admin purposes
	 *
	 * @param int $limit Number of records to retrieve
	 * @return array
	 */
	public function getRecentAttempts($limit = 100) {
		$sql = "SELECT * FROM {$this->table} ORDER BY attempt_time DESC LIMIT ?";
		$query = $this->db->query($sql, array($limit));
		return $query->result_array();
	}

	/**
	 * Get failed attempts count for an identifier in the last hour
	 *
	 * @param string $identifier IP or email
	 * @param string $type 'ip' or 'email'
	 * @return int
	 */
	public function getFailedAttemptsCount($identifier, $type = 'ip') {
		$one_hour_ago = date('Y-m-d H:i:s', strtotime('-1 hour'));

		$sql = "SELECT COUNT(*) as cnt FROM {$this->table}
				WHERE identifier = ? AND identifier_type = ?
				AND attempt_time > ? AND is_successful = 0";

		$query = $this->db->query($sql, array($identifier, $type, $one_hour_ago));
		$result = $query->row();

		return $result ? (int)$result->cnt : 0;
	}
}
?>
