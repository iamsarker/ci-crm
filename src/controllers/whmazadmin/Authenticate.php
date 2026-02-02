<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Authenticate extends WHMAZADMIN_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('Adminauth_model');
	}

	public function index()
	{
		redirect('/whmazadmin/authenticate/login', 'refresh');
	}

	public function login()
	{
		$redirectUrl = isset($_GET["redirect-url"]) ? $_GET["redirect-url"] : "";

		if ($this->input->post()) {

			$username = xssCleaner($this->input->post('username'));
			$password = xssCleaner($this->input->post('password'));

			$resp = $this->Adminauth_model->doLogin($username, $password);
			if ($resp['status_code'] == 1) {
				$this->session->set_userdata("ADMIN", $resp['data']);

				// SECURITY FIX: Validate redirect URL to prevent open redirect vulnerability
				if( !empty($redirectUrl) ){
					// Only allow internal redirects (relative URLs starting with /)
					if (strpos($redirectUrl, '/') === 0 && strpos($redirectUrl, '//') !== 0) {
						redirect($redirectUrl, 'refresh');
					} else {
						redirect('/whmazadmin/dashboard/index', 'refresh');
					}
				} else{
					redirect('/whmazadmin/dashboard/index', 'refresh');
				}

			} else if ($resp['status_code'] == -100) {
				// SECURITY: Rate limiting - too many failed attempts
				$this->session->set_flashdata('admin_error', $resp['message']);
			} else if ($resp['status_code'] == -1) {
				$remaining = isset($resp['remaining_attempts']) ? $resp['remaining_attempts'] : '';
				$msg = 'Invalid username or account not active.';
				if ($remaining > 0 && $remaining <= 3) {
					$msg .= " ({$remaining} attempts remaining)";
				}
				$this->session->set_flashdata('admin_error', $msg);
			} else if ($resp['status_code'] == -2) {
				$remaining = isset($resp['remaining_attempts']) ? $resp['remaining_attempts'] : '';
				$msg = 'Please Enter your current email address !!!';
				if ($remaining > 0 && $remaining <= 3) {
					$msg .= " ({$remaining} attempts remaining)";
				}
				$this->session->set_flashdata('admin_error', $msg);
			} else {
				$remaining = isset($resp['remaining_attempts']) ? $resp['remaining_attempts'] : '';
				$msg = 'Invalid username/password. Try Again';
				if ($remaining > 0 && $remaining <= 3) {
					$msg .= " ({$remaining} attempts remaining)";
				}
				$this->session->set_flashdata('admin_error', $msg);
			}

		}
		$this->load->view('whmazadmin/admin_login');
	}


	public function logout()
	{
		$resp = array('id' => 0, 'email' => '');
		$this->session->unset_userdata('ADMIN', $resp);
		$this->session->unset_userdata('ADMIN');
		$this->session->sess_destroy();
		$this->session->set_flashdata('admin_error', 'Logout success !!!');
		redirect('/whmazadmin/authenticate/login', 'refresh');
	}

	public function forgetpaswrd()
	{
		if ($this->input->post()) {
			$username = xssCleaner($this->input->post('username'));
			$this->Adminauth_model->forgetpaswrd($username);
			$this->session->set_flashdata('admin_success', 'If that email exists in our system, a password reset link has been sent.');
		}
		$this->load->view('whmazadmin/admin_forgetpass');
	}

	public function resetpassword($token = null)
	{
		if (empty($token)) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
			return;
		}

		$token = str_replace(' ', '', urldecode($token));
		$user = $this->Adminauth_model->validateResetToken($token);

		if (!$user) {
			$this->session->set_flashdata('admin_error', 'This reset link is invalid or has expired.');
			redirect('/whmazadmin/authenticate/login', 'refresh');
			return;
		}

		if ($this->input->post()) {
			$password = $this->input->post('password');
			$confirm  = $this->input->post('confirm_password');

			if (strlen($password) < 8) {
				$this->session->set_flashdata('admin_error', 'Password must be at least 8 characters.');
				$data['token'] = $token;
				$this->load->view('whmazadmin/admin_resetpassword', $data);
				return;
			}

			if ($password !== $confirm) {
				$this->session->set_flashdata('admin_error', 'Passwords do not match.');
				$data['token'] = $token;
				$this->load->view('whmazadmin/admin_resetpassword', $data);
				return;
			}

			$result = $this->Adminauth_model->resetPassword($user, $password);

			if ($result) {
				$this->session->set_flashdata('admin_success', 'Your password has been reset successfully. Please login.');
			} else {
				$this->session->set_flashdata('admin_error', 'This reset link is invalid or has expired.');
			}
			redirect('/whmazadmin/authenticate/login', 'refresh');
			return;
		} else {
			$data['token'] = $token;
			$this->load->view('whmazadmin/admin_resetpassword', $data);
		}
	}

}
