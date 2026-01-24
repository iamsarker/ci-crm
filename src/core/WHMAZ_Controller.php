<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/* load the MX_Router class */
require_once APPPATH . "third_party/MX/Controller.php";

class WHMAZ_Controller extends MX_Controller
{

	function __construct() 
	{
		parent::__construct();
		$this->load->model('Auth_model');
		$this->load->model('Cart_model');
		$this->loadDefaultCurrency();
	}

	public function loadDefaultCurrency(){
		if( empty($this->session->currency_id) && empty($this->session->currency_code) ){

			$cr = $this->Cart_model->getCurrencies();

			foreach ($cr as $rw){
				if( $rw['is_default'] == 1 ){
					$this->session->currency_id = $rw['id'];
					$this->session->currency_code = $rw['code'];
					break;
				}
			}
		}
	}

	function isLogin(){
		$user = $this->session->has_userdata('CUSTOMER') ? $this->session->userdata("CUSTOMER") : array();
		if( !empty($user) && $user['id'] > 0 ){
			$cnt = $this->Auth_model->countDbSession($user['id']);
			if( $cnt > 0 ){
				return true;
			} else{
				$resp = array('id' => 0, 'email' => '');
				$this->session->set_userdata('CUSTOMER', $resp); // set empty array
			}
		}
		return false;
	}

	function processRestCall(){
		$_POST = json_decode(file_get_contents('php://input'), true);

		// Send updated CSRF token in response headers for Angular
		$this->sendCsrfHeaders();
	}

	function sendCsrfHeaders(){
		// Send CSRF token in response headers so Angular can update it
		header('X-CSRF-TOKEN-NAME: ' . $this->security->get_csrf_token_name());
		header('X-CSRF-TOKEN-HASH: ' . $this->security->get_csrf_hash());
	}

	function curlGetRequest($finalUrl){
		$ch = curl_init();
		$headers = array(
			'Accept: application/json',
			'Content-Type: application/json',
		);
		curl_setopt($ch, CURLOPT_URL, $finalUrl);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		// SECURITY: Ensure SSL verification is enabled
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		$resp = curl_exec($ch);
		curl_close($ch);
		return json_decode($resp);
	}

	function AppResponse($code, $msg, $data=array() ){
		return json_encode(array("code"=>$code, "msg"=>$msg, "data"=>$data));
	}

}
