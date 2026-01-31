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
			$username = xss_cleaner($this->input->post('username'));
			$resp = $this->Adminauth_model->forgetpaswrd($username);

//			if($findemail){
//				$this->usermodel->sendpassword($findemail);
//			}else{
//				$this->session->set_flashdata('msg',' Email not found!');
			var_dump($resp);
			exit();

		}
		$this->load->view('auth_forgetpass');

	}

}
