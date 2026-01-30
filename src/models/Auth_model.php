<?php
class Auth_model extends CI_Model{

	function __construct(){
		parent::__construct();
		$this->load->database();
		$this->load->model('Loginattempt_model');
	}

	/**
	 * Check if login is allowed (rate limiting)
	 *
	 * @param string $email User email
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

		try {
			// SECURITY: Check rate limiting before processing login
			$rate_check = $this->Loginattempt_model->checkLoginAllowed($email, $ip);
			if (!$rate_check['allowed']) {
				$return['status_code'] = -100; // Rate limited
				$return['message'] = $rate_check['message'];
				$return['minutes_remaining'] = $rate_check['minutes_remaining'];
				return $return;
			}

			// SECURITY FIX: Use prepared statement to prevent SQL injection
			$sql = "SELECT * FROM `users` WHERE users.email = ?";
			$query = $this->db->query($sql, array($email));
			if ($query->num_rows() == 0){
				// Record failed attempt
				$this->Loginattempt_model->recordFailedAttempt($email, $ip, $user_agent);
				$return['status_code'] = -2;
				$return['remaining_attempts'] = $rate_check['remaining_attempts'] - 1;
				return $return;
			}

			// SECURITY FIX: Use prepared statement to prevent SQL injection
			$sql = "SELECT u.*, c.name company, c.address, c.city, c.state, c.zip_code, c.country FROM users u join companies c on u.company_id=c.id WHERE u.email = ? and u.status = 1 and c.status=1";
			$query = $this->db->query($sql, array($email));
			if ($query->num_rows() == 0){
				// Record failed attempt
				$this->Loginattempt_model->recordFailedAttempt($email, $ip, $user_agent);
				$return['status_code'] = -1;
				$return['remaining_attempts'] = $rate_check['remaining_attempts'] - 1;
				return $return;
			}

			$userdata = $query->row();
			if (password_verify($password,$userdata->password)) {
				$this->Loginattempt_model->clearAllAttempts($email, $ip);

				$resp = array();
				$resp['id'] = $userdata->id;
				$resp['first_name'] = $userdata->first_name;
				$resp['last_name'] = $userdata->last_name;
				$resp['email'] = $userdata->email;
				$resp['mobile'] = $userdata->mobile;
				$resp['phone'] = $userdata->phone;
				$resp['address'] = $userdata->address;
				$resp['zip_code'] = $userdata->zip_code;
				$resp['city'] = $userdata->city;
				$resp['state'] = $userdata->state;
				$resp['country'] = $userdata->country;
				$resp['company'] = $userdata->company;
				$resp['designation'] = $userdata->designation;
				$resp['user_type'] = $userdata->user_type;
				$resp['company_id'] = $userdata->company_id;
				$resp['profile_pic'] = $userdata->profile_pic;
				$resp['terminal'] = getClientIp();

				$logins = array();
				$logins['user_id'] = $userdata->id;
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
		} catch (Exception $e) {
			// SECURITY: Log database error
			ErrorHandler::log_database_error('doLogin', $this->db->last_query(), $e->getMessage());
			$return['status_code'] = -99; // Error code for database failure
			return $return;
		}
 	}

 	function saveUserLogins($data){
 		try {
			$data['active'] = 1;
			if ($this->db->insert('user_logins', $data)) {
			}
		} catch (Exception $e) {
			// SECURITY: Log database error
			ErrorHandler::log_database_error('saveUserLogins', $this->db->last_query(), $e->getMessage());
		}
 	}

	function newRegistration($data) {
		$return = array();

		try {
			$data['status'] = "2";
			$data['inserted_on'] = getDateTime();
			//$data['terminal'] = getClientIp();

			$data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

			$uniqueVarificationCode = uniqid().$data['email'].uniqid().time();
			$verification_code = hash('sha256', 'TB'.$uniqueVarificationCode);
			$data['verify_hash'] = $verification_code;

			if ($this->db->insert('users', $data)) {
				$return['success'] = 1;
				$return['email'] = $data['email'];
				$return['verification_code'] = $verification_code;
			} else {
				$return['success'] = 0;
			};
			return $return;
		} catch (Exception $e) {
			// SECURITY: Log database error
			ErrorHandler::log_database_error('newRegistration', $this->db->last_query(), $e->getMessage());
			$return['success'] = 0;
			return $return;
		}
 	}


	public function forgetpaswrd($username)
	{
		try {
			$this->db->select('email');
			$this->db->from('users');
			$this->db->where('email', $username);
			$query=$this->db->get();
			if ($query->num_rows()){
				$this->sendPassword($username);
			}

			return 0;
		} catch (Exception $e) {
			// SECURITY: Log database error
			ErrorHandler::log_database_error('forgetpaswrd', $this->db->last_query(), $e->getMessage());
			return 0;
		}

	}


	 function sendPassword($email) {
		 $return = array();

		try {
			// SECURITY FIX: Use prepared statement to prevent SQL injection
			$sql = "SELECT * FROM users WHERE email = ?";
			$query1 = $this->db->query($sql, array($email));
			$row = $query1->result();

			if ($query1->num_rows() > 0) {
				$passwordPlain = bin2hex(random_bytes(16));
				$newPass = password_hash($passwordPlain, PASSWORD_DEFAULT);

				$sql = "UPDATE `users` SET `password` = ? WHERE `users`.`email` = ?";
				$query = $this->db->query($sql, array($newPass, $email));
				if ($query){
					$this->sendResetPassEmail($row, $email, $passwordPlain);

				}else {
					$return['status_code'] = -1;
					$return['msg'] = "Unable to update the password";
				}

				return $return;

			}
		} catch (Exception $e) {
			ErrorHandler::log_database_error('sendPassword', $this->db->last_query(), $e->getMessage());
			$return['status_code'] = -99;
			$return['msg'] = "Database error occurred";
			return $return;
		}
	}

	function sendResetPassEmail($info, $emailTo, $passwordplain){
		$appSettings = getAppSettings();
		$this->load->library('email');
		$this->email->clear();

		$config = Array(
			'wordwrap' => TRUE,
			'smtp_host' => $appSettings->smtp_host,
			'smtp_port' => $appSettings->smtp_port,
			'smtp_user' => $appSettings->smtp_username,
			'smtp_pass' => $appSettings->smtp_authkey,
			'mailtype'  => 'html'
		);
		$this->email->initialize($config);
		$this->email->set_newline("\r\n");
		$this->email->from($appSettings->email, $appSettings->company_name);

		$this->email->to($emailTo);
		$this->email->subject("Password Reset - Tong Bari");

		$userName = !empty($info[0]->first_name) ? $info[0]->first_name : 'User';
		$body = 'Dear ' . htmlspecialchars($userName) . ',<br><br>';
		$body .= 'You have requested to reset your password.<br><br>';
		$body .= 'Your new temporary password is: <b>' . htmlspecialchars($passwordplain) . '</b><br><br>';
		$body .= 'Please login and change this password immediately for security reasons.<br><br>';
		$body .= 'If you did not request this password reset, please contact us immediately.<br><br>';
		$body .= 'Thanks & Regards<br>';
		$body .= $appSettings->company_name . ' Support';

		$this->email->message($body);

		if( $this->email->send() ){
			$this->session->set_flashdata('alert', successAlert('Password reset email has been sent'));
		} else{
			echo "0";
		}
	}

 	function verifyUser($verificationCode){
 		$return = 0;

		try {
			$this->db->select('id');
			$this->db->from('users');
			$this->db->where(array(
				'verify_hash'=>$verificationCode,
				'status'=>'2'
			));
			$num_results = $this->db->count_all_results();
			if ($num_results == 1) {
				$data['status'] = '1';
				$this->db->where('verify_hash', $verificationCode);
				if($this->db->update('users', $data)) {
					$return = 1;
				}
			}
			return $return;
		} catch (Exception $e) {
			// SECURITY: Log database error
			ErrorHandler::log_database_error('verifyUser', $this->db->last_query(), $e->getMessage());
			return 0;
		}
 	}

 	function countDbSession($user_id){
		try {
			$this->db->select('id');
			$this->db->from('user_logins');
			$this->db->where(array(
				'user_id'=>$user_id,
				'active'=>1
			));
			$num_results = $this->db->count_all_results();

			return $num_results;
		} catch (Exception $e) {
			// SECURITY: Log database error
			ErrorHandler::log_database_error('countDbSession', $this->db->last_query(), $e->getMessage());
			return 0;
		}
	}

	function isEmailExists($email){
		try {
			$this->db->select('id');
			$this->db->from('users');
			$this->db->where(array(
				'email'=>$email
			));
			$num_results = $this->db->count_all_results();

			return $num_results;
		} catch (Exception $e) {
			// SECURITY: Log database error
			ErrorHandler::log_database_error('isEmailExists', $this->db->last_query(), $e->getMessage());
			return 0;
		}
	}
}
?>
