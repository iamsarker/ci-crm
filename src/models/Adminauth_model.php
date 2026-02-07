<?php
class Adminauth_model extends CI_Model{

	function __construct(){
		parent::__construct();
		$this->load->database();
		$this->load->model('Loginattempt_model');
	}

	/**
	 * Check if login is allowed (rate limiting)
	 *
	 * @param string $email User email or username
	 * @return array ['allowed' => bool, 'message' => string]
	 */
	function checkRateLimit($email) {
		$ip = $this->input->ip_address();
		return $this->Loginattempt_model->checkLoginAllowed($email, $ip);
	}

	function doLogin($email, $password) {
		$return = array();
		$ip = $this->input->ip_address();
		$user_agent = $this->input->user_agent();

		// SECURITY: Check rate limiting before processing login
		$rate_check = $this->Loginattempt_model->checkLoginAllowed($email, $ip);
		if (!$rate_check['allowed']) {
			$return['status_code'] = -100; // Rate limited
			$return['message'] = $rate_check['message'];
			$return['minutes_remaining'] = $rate_check['minutes_remaining'];
			return $return;
		}

		// SECURITY FIX: Use prepared statement to prevent SQL injection in admin login
		$sql = "SELECT id, admin_role_id, first_name, last_name, username, password, email, mobile, phone, designation, signature, support_depts, profile_pic FROM admin_users WHERE (username = ? or email = ?) and status = 1";
		$query = $this->db->query($sql, array($email, $email));
		if ($query->num_rows() == 0){
			// SECURITY: Record failed attempt
			$this->Loginattempt_model->recordFailedAttempt($email, $ip, $user_agent);
			$return['status_code'] = -1;
			$return['remaining_attempts'] = $rate_check['remaining_attempts'] - 1;
			return $return;
		}

		$userdata = $query->row();
		if (password_verify($password, $userdata->password)) {
			// SECURITY: Clear failed attempts on successful login
			$this->Loginattempt_model->clearAllAttempts($email, $ip);

			$resp = array();
			$resp['id'] = $userdata->id;
			$resp['first_name'] = $userdata->first_name;
			$resp['last_name'] = $userdata->last_name;
			$resp['email'] = $userdata->email;
			$resp['mobile'] = $userdata->mobile;
			$resp['phone'] = $userdata->phone;
			$resp['designation'] = $userdata->designation;
			$resp['signature'] = $userdata->signature;
			$resp['support_depts'] = $userdata->support_depts;
			$resp['profile_pic'] = $userdata->profile_pic;
			$resp['terminal'] = getClientIp();

			$logins = array();
			$logins['admin_id'] = $userdata->id;
			$logins['login_time'] = getDateTime();
			$logins['session_val'] = 0;
			$logins['terminal'] = $resp['terminal'];
			$this->saveUserLogins($logins);

			$return['status_code'] = 1;
			$return['data'] = $resp;
			return $return;
		}

		// SECURITY: Record failed attempt for wrong password
		$this->Loginattempt_model->recordFailedAttempt($email, $ip, $user_agent);
		$return['status_code'] = 0;
		$return['remaining_attempts'] = $rate_check['remaining_attempts'] - 1;
		return $return;
	}

	function saveUserLogins($data){
		$data['active'] = 1;
		if ($this->db->insert('admin_logins', $data)) {
		}
	}

 	function countDbSession($user_id){
		$this->db->select('id');
		$this->db->from('admin_logins');
		$this->db->where(array(
			'admin_id'=>$user_id,
			'active'=>1
		));
		$num_results = $this->db->count_all_results();

		return $num_results;
	}

	function isEmailExists($email){
		$this->db->select('id');
		$this->db->from('admin_users');
		$this->db->where(array(
			'email'=>$email
		));
		$num_results = $this->db->count_all_results();

		return $num_results;
	}

	public function forgetpaswrd($email)
	{
		try {
			$sql = "SELECT id, first_name, email FROM admin_users WHERE (email = ? OR username = ?) AND status = 1";
			$query = $this->db->query($sql, array($email, $email));

			if ($query->num_rows() > 0) {
				$user = $query->row();
				$text = trim(gen_uuid().'whmaz'.gen_uuid());
				$token = preg_replace('/[^a-z0-9]/i', '', $text);

				$sql = "UPDATE admin_users SET pass_reset_key = ?, pass_reset_expiry = NOW() + INTERVAL 1 HOUR WHERE id = ?";
				$this->db->query($sql, array($token, $user->id));

				$this->sendResetLinkEmail($user, $token);
			}

			return 1;
		} catch (Exception $e) {
			ErrorHandler::log_database_error('admin_forgetpaswrd', $this->db->last_query(), $e->getMessage());
			return 0;
		}
	}

	function sendResetLinkEmail($user, $token)
	{
		$appSettings = getAppSettings();

		$resetLink = base_url('whmazadmin/authenticate/resetpassword/' . $token);
		$userName = !empty($user->first_name) ? htmlspecialchars($user->first_name) : 'User';

		$body = 'Dear ' . $userName . ',<br><br>';
		$body .= 'We received a request to reset your admin password.<br><br>';
		$body .= 'Click the link below to set a new password:<br>';
		$body .= '<a href="' . $resetLink . '">Reset Your Password</a><br><br>';
		$body .= 'This link will expire in 1 hour.<br><br>';
		$body .= 'If you did not request this, please ignore this email.<br><br>';
		$body .= 'Thanks & Regards<br>';
		$body .= $appSettings->company_name . ' Support';

		$subject = "Admin Password Reset - " . $appSettings->company_name;

		return sendHtmlEmail($user->email, $subject, $body);
	}

	public function validateResetToken($token)
	{
		$sql = "SELECT id, email, first_name FROM admin_users WHERE pass_reset_key = ? AND pass_reset_expiry > NOW() AND status = 1";
		$query = $this->db->query($sql, array($token));

		if ($query->num_rows() == 0) {
			return false;
		}
		return $query->row();
	}

	public function resetPassword($user, $newPassword)
	{
		try {
			$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
			$sql = "UPDATE admin_users SET password = ?, pass_reset_key = NULL, pass_reset_expiry = NULL WHERE id = ?";
			$this->db->query($sql, array($hashedPassword, $user->id));

			return true;
		} catch (Exception $e) {
			ErrorHandler::log_database_error('admin_resetPassword', $this->db->last_query(), $e->getMessage());
			return false;
		}
	}

	/**
	 * Change password for admin user (requires current password verification)
	 *
	 * @param int $adminId Admin user ID
	 * @param string $currentPassword Current password
	 * @param string $newPassword New password
	 * @return array ['success' => bool, 'msg' => string, 'email' => string, 'first_name' => string]
	 */
	public function changePassword($adminId, $currentPassword, $newPassword)
	{
		$sql = "SELECT password, first_name, email FROM admin_users WHERE id = ? AND status = 1";
		$query = $this->db->query($sql, array($adminId));

		if ($query->num_rows() == 0) {
			return ['success' => false, 'msg' => 'Admin user not found.'];
		}

		$user = $query->row();

		if (!password_verify($currentPassword, $user->password)) {
			return ['success' => false, 'msg' => 'Current password is incorrect.'];
		}

		$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
		$sql = "UPDATE admin_users SET password = ? WHERE id = ?";
		$this->db->query($sql, array($hashedPassword, $adminId));

		return ['success' => true, 'email' => $user->email, 'first_name' => $user->first_name];
	}
}
?>
