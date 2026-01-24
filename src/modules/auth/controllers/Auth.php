<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends WHMAZ_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('Auth_model');
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
				$this->session->set_userdata("CUSTOMER", $resp['data']);

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

			} else if ($resp['status_code'] == -1) {
				$this->session->set_flashdata('alert', errorAlert('Please check your mail. A confirmation message has been sent !!!'));
			} else if ($resp['status_code'] == -2) {
				$this->session->set_flashdata('alert', errorAlert('Please Enter your current email address !!!'));
			} else {
				$this->session->set_flashdata('alert', errorAlert('Invalid username/password. Try Again'));
			}

		}
		$this->load->view('auth_login');
	}


	public function register()
	{
		if ($this->input->post()) {

			$newUserReq = xss_cleaner($this->input->post('reg'));
			$resp = $this->Auth_model->newRegistration($newUserReq);
			if ($resp['success'] == 1) {
				$this->session->set_flashdata('alert', successAlert('Registration has been completed successfully. A confirmation link has been sent through email.'));
			} else {
				$this->session->set_flashdata('alert', errorAlert('Failed to register. Try Again'));
			}

		}

		$this->load->view('auth_register');
	}

	public function logout()
	{
		$resp = array('id' => 0, 'email' => '');
		$this->session->unset_userdata('CUSTOMER', $resp);
		$this->session->unset_userdata('CUSTOMER');
		$this->session->sess_destroy();
		$this->session->set_flashdata('alert', errorAlert('Logout success !!!'));
		redirect('/auth/login', 'refresh');
	}

	public function forgetpaswrd()
	{

		if ($this->input->post()) {
			$username = xss_cleaner($this->input->post('username'));
			$resp = $this->Auth_model->forgetpaswrd($username);

//			if($findemail){
//				$this->usermodel->sendpassword($findemail);
//			}else{
//				$this->session->set_flashdata('msg',' Email not found!');
			var_dump($resp);
			exit();

		}
		$this->load->view('auth_forgetpass');

	}

	public function change_currency($id, $code)
	{
		changeCurrency($id, $code);
	}
}
