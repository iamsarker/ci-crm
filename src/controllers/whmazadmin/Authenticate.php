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

				if( !empty($redirectUrl) ){
					header("Location: ".$redirectUrl);
					die();
				} else{
					redirect('/whmazadmin/dashboard/index', 'refresh');
				}

			} else if ($resp['status_code'] == -1) {
				$this->session->set_flashdata('alert_error', 'Please check your mail. A confirmation message has been sent !!!');
			} else if ($resp['status_code'] == -2) {
				$this->session->set_flashdata('alert_error', 'Please Enter your current email address !!!');
			} else {
				$this->session->set_flashdata('alert_error', 'Invalid username/password. Try Again');
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
		$this->session->set_flashdata('alert_error', 'Logout success !!!');
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
