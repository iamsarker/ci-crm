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
}
?>
