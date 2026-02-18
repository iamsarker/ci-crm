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

	// Store last cURL error for debugging
	protected $lastCurlError = '';

	function getLastCurlError() {
		return $this->lastCurlError;
	}

	function curlGetRequest($finalUrl){
		$this->lastCurlError = '';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $finalUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		// SSL verification disabled for domain registrar APIs (httpapi.com, etc.)
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

		$responseJson = curl_exec($ch);
		$error = curl_error($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($error) {
			$this->lastCurlError = $error;
			log_message('error', 'cURL Error: ' . $error . ' | URL: ' . $finalUrl);
			return null;
		}

		if ($httpCode >= 400) {
			// Log response body for debugging 403/401 errors
			$this->lastCurlError = 'HTTP Error: ' . $httpCode;
			$maskedUrl = preg_replace('/api-key=[^&]+/', 'api-key=***', $finalUrl);
			log_message('error', 'cURL HTTP Error: ' . $httpCode . ' | URL: ' . $maskedUrl . ' | Response: ' . substr($responseJson, 0, 500));
			return null;
		}

		return json_decode($responseJson, true);
	}

	function AppResponse($code, $msg, $data=array() ){
		return json_encode(array("code"=>$code, "msg"=>$msg, "data"=>$data));
	}

}
