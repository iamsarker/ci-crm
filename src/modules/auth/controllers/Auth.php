<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends WHMAZ_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('Auth_model');
		$this->load->model('Appsetting_model');
	}

	public function index()
	{
		redirect('/auth/login', 'refresh');
	}

	public function login()
	{
		$redirectUrl = isset($_GET["redirect-url"]) ? $_GET["redirect-url"] : "";

		if ($this->input->post()) {

			$username = xss_cleaner($this->input->post('username'));
			$password = xss_cleaner($this->input->post('password'));

			$resp = $this->Auth_model->doLogin($username, $password);
			if ($resp['status_code'] == 1) {
				// Get customer_session_id before setting user session
				$customerSessionId = getCustomerSessionId();

				$this->session->set_userdata("CUSTOMER", $resp['data']);

				// Transfer guest cart items to logged-in user
				$userId = $resp['data']['id'];
				if ($customerSessionId > 0 && $userId > 0) {
					$this->load->database();
					$this->db->where('customer_session_id', $customerSessionId);
					$this->db->where('user_id', 0);
					$this->db->update('add_to_carts', array('user_id' => $userId));
				}

				// SECURITY FIX: Validate redirect URL to prevent open redirect vulnerability
				if( !empty($redirectUrl) ){
					// Only allow internal redirects (relative URLs starting with /)
					if (strpos($redirectUrl, '/') === 0 && strpos($redirectUrl, '//') !== 0) {
						// Valid internal redirect
						redirect($redirectUrl, 'refresh');
					} else {
						// Invalid redirect URL, go to default page
						redirect('/clientarea/index', 'refresh');
					}
				} else{
					redirect('/clientarea/index', 'refresh');
				}

			} else if ($resp['status_code'] == -100) {
				// SECURITY: Rate limiting - too many failed attempts
				$this->session->set_flashdata('alert_error', $resp['message']);
			} else if ($resp['status_code'] == -1) {
				$remaining = isset($resp['remaining_attempts']) ? $resp['remaining_attempts'] : '';
				$msg = 'Please check your mail. A confirmation message has been sent !!!';
				if ($remaining > 0 && $remaining <= 3) {
					$msg .= " ({$remaining} attempts remaining)";
				}
				$this->session->set_flashdata('alert_error', $msg);
			} else if ($resp['status_code'] == -2) {
				$remaining = isset($resp['remaining_attempts']) ? $resp['remaining_attempts'] : '';
				$msg = 'Please Enter your current email address !!!';
				if ($remaining > 0 && $remaining <= 3) {
					$msg .= " ({$remaining} attempts remaining)";
				}
				$this->session->set_flashdata('alert_error', $msg);
			} else {
				$remaining = isset($resp['remaining_attempts']) ? $resp['remaining_attempts'] : '';
				$msg = 'Invalid username/password. Try Again';
				if ($remaining > 0 && $remaining <= 3) {
					$msg .= " ({$remaining} attempts remaining)";
				}
				$this->session->set_flashdata('alert_error', $msg);
			}

		}
		$this->load->view('auth_login');
	}


	public function register()
	{
		// Get app settings for reCAPTCHA keys
		$app_settings = $this->Appsetting_model->getSettings();
		$captcha_site_key = !empty($app_settings['captcha_site_key']) ? $app_settings['captcha_site_key'] : '';
		$captcha_secret_key = !empty($app_settings['captcha_secret_key']) ? $app_settings['captcha_secret_key'] : '';

		// Get countries for dropdown
		$countries = $this->Appsetting_model->getCountries();

		if ($this->input->post()) {

			// Verify reCAPTCHA only if keys are configured
			if (!empty($captcha_site_key) && !empty($captcha_secret_key)) {
				$recaptcha_response = $this->input->post('g-recaptcha-response');
				if (empty($recaptcha_response)) {
					$this->session->set_flashdata('alert_error', 'Please complete the reCAPTCHA verification.');
					$data['captcha_site_key'] = $captcha_site_key;
					$data['countries'] = $countries;
					$this->load->view('auth_register', $data);
					return;
				}

				$verify_url = RECAPTCHA_VERIFY_URL;
				$post_data = array(
					'secret' => $captcha_secret_key,
					'response' => $recaptcha_response,
					'remoteip' => $this->input->ip_address()
				);

				$options = array(
					'http' => array(
						'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
						'method'  => 'POST',
						'content' => http_build_query($post_data)
					)
				);

				$context  = stream_context_create($options);
				$result = file_get_contents($verify_url, false, $context);
				$recaptcha_result = json_decode($result, true);

				if (!$recaptcha_result['success']) {
					$this->session->set_flashdata('alert_error', 'reCAPTCHA verification failed. Please try again.');
					$data['captcha_site_key'] = $captcha_site_key;
					$data['countries'] = $countries;
					$this->load->view('auth_register', $data);
					return;
				}
			}

			$newUserReq = xss_cleaner($this->input->post('reg'));
			$resp = $this->Auth_model->newRegistration($newUserReq);
			if ($resp['success'] == 1) {
				$this->Auth_model->sendVerificationEmail(
					$resp['email'],
					!empty($newUserReq['first_name']) ? $newUserReq['first_name'] : '',
					$resp['verification_code']
				);
				$this->session->set_flashdata('alert_success', 'Registration has been completed successfully. A confirmation link has been sent to your email.');
			} else {
				$this->session->set_flashdata('alert_error', 'Failed to register. Try Again');
			}

		}

		$data['captcha_site_key'] = $captcha_site_key;
		$data['countries'] = $countries;
		$this->load->view('auth_register', $data);
	}

	public function logout()
	{
		$resp = array('id' => 0, 'email' => '');
		$this->session->unset_userdata('CUSTOMER', $resp);
		$this->session->unset_userdata('CUSTOMER');
		$this->session->sess_destroy();
		$this->session->set_flashdata('alert_success', 'Logout success !!!');
		redirect('/auth/login', 'refresh');
	}

	public function forgetpaswrd()
	{
		if ($this->input->post()) {
			$username = xss_cleaner($this->input->post('username'));
			$this->Auth_model->forgetpaswrd($username);

			$this->session->set_flashdata('alert_success', 'If that email exists in our system, a password reset link has been sent.');
		}
		$this->load->view('auth_forgetpass');
	}

	public function resetpassword($token = null)
	{
		if (empty($token)) {
			redirect('/auth/login', 'refresh');
			return;
		}

		$token = str_replace(' ', '', urldecode($token));
		$user = $this->Auth_model->validateResetToken($token);

		if (!$user) {
			$this->session->set_flashdata('alert_error', 'This reset link is invalid or has expired.');
			redirect('/auth/login', 'refresh');
			return;
		}

		if ($this->input->post()) {
			$password = $this->input->post('password');
			$confirm  = $this->input->post('confirm_password');

			if (strlen($password) < 8) {
				$this->session->set_flashdata('alert_error', 'Password must be at least 8 characters.');
				$data['token'] = $token;
				$this->load->view('auth_resetpassword', $data);
				return;
			}

			if ($password !== $confirm) {
				$this->session->set_flashdata('alert_error', 'Passwords do not match.');
				$data['token'] = $token;
				$this->load->view('auth_resetpassword', $data);
				return;
			}

			$result = $this->Auth_model->resetPassword($user, $password);

			if ($result) {
				$this->session->set_flashdata('alert_success', 'Your password has been reset successfully. Please login.');
			} else {
				$this->session->set_flashdata('alert_error', 'This reset link is invalid or has expired.');
			}
			redirect('/auth/login', 'refresh');
			return;
		} else {
			$data['token'] = $token;
			$this->load->view('auth_resetpassword', $data);
		}
	}

	public function verify($hash = null)
	{
		if (empty($hash)) {
			redirect('/auth/login', 'refresh');
			return;
		}

		$hash = xss_cleaner($hash);
		$result = $this->Auth_model->verifyUser($hash);

		if ($result == 1) {
			$this->session->set_flashdata('alert_success', 'Your email has been verified successfully. You can now login.');
		} else {
			$this->session->set_flashdata('alert_error', 'Invalid or expired verification link.');
		}

		redirect('/auth/login', 'refresh');
	}

	public function change_currency($id, $code)
	{
		changeCurrency($id, $code);
	}
}
