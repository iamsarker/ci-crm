<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Clientarea extends WHMAZ_Controller {

	function __construct() {
		parent::__construct();
		$this->load->model('Clientarea_model');
		$this->load->model('Billing_model');
		$this->load->model('Common_model');
		$this->load->model('Order_model');
		$this->load->model('Support_model');

		if( !$this->isLogin() ){
			redirect('/auth/login', 'refresh');
		}
	}

	public function index() {
		$this->load->view('clientarea_index');
	}

	public function services() {
		$data['summary'] = $this->Billing_model->invoiceSummary(getCompanyId())[0];
		$data['results'] = $this->Order_model->loadOrderServices(getCompanyId(), -1);
		$this->load->view('clientarea_services', $data);
	}

	public function service_detail($id) {
		$data['detail'] = $this->Order_model->loadOrderServiceById(getCompanyId(), $id);

		if( !empty($data['detail']) && !empty($data['detail']['product_service_pricing_id']) && is_numeric($data['detail']['product_service_pricing_id'])){
			$data['dns'] = $this->Clientarea_model->getServerDnsInfo($data['detail']['product_service_pricing_id'])[0];
		} else {
			$data['dns'] = array();
		}

		$this->load->view('clientarea_service_detail', $data);
	}

	public function cpanel_single_sign_on($orderId, $serviceDetailId){

		$serverInfo = $this->Common_model->getServerInfoByOrderServiceId($serviceDetailId, getCompanyId());

		$query = "https://".$serverInfo["hostname"].":2087/json-api/create_user_session?api.version=1&user=tong0bari&service=cpaneld";

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);

		$header[0] = "Authorization: WHM ".$serverInfo['username'].":" . preg_replace("'(\r|\n)'","", base64_decode(base64_decode($serverInfo['access_hash'])));
		curl_setopt($curl,CURLOPT_HTTPHEADER,$header);
		curl_setopt($curl, CURLOPT_URL, $query);
		$result = curl_exec($curl);

		if ($result == false) {
			echo json_encode("ERROR FOUND");
			die();
		}

		if($result){
			$decoded_response = json_decode( $result, true );

			if(isset($decoded_response['data']) && !empty($decoded_response['data'])){
				$url = $decoded_response['data']['url'];
				header("Location: $url");
			}

		}

		echo json_encode("ERROR FOUND");
		die();
	}

	public function webmail_single_sign_on($orderId, $serviceDetailId){

		$serverInfo = $this->Common_model->getServerInfoByOrderServiceId($serviceDetailId, getCompanyId());

		$query = "https://".$serverInfo["hostname"].":2087/json-api/create_user_session?api.version=1&user=tong0bari&service=cpaneld";

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);

		$header[0] = "Authorization: WHM ".$serverInfo['username'].":" . preg_replace("'(\r|\n)'","", base64_decode(base64_decode($serverInfo['access_hash'])));
		curl_setopt($curl,CURLOPT_HTTPHEADER,$header);
		curl_setopt($curl, CURLOPT_URL, $query);
		$result = curl_exec($curl);

		if ($result == false) {
			echo json_encode("ERROR FOUND");
			die();
		}

		if($result){
			$decoded_response = json_decode( $result, true );

			if(isset($decoded_response['data']) && !empty($decoded_response['data'])){
				$url = $decoded_response['data']['url'];
				header("Location: $url");
			}

		}

		echo json_encode("ERROR FOUND");
		die();
	}

	public function domains() {
		$data['summary'] = $this->Billing_model->invoiceSummary(getCompanyId())[0];
		$data['results'] = $this->Order_model->loadOrderDomains(getCompanyId(), -1);
		$this->load->view('clientarea_domains', $data);
	}

	public function domain_detail($id) {
		$data['detail'] = $this->Order_model->loadOrderDomainById(getCompanyId(), $id);

		$this->load->view('clientarea_domain_detail', $data);
	}

	public function summary_api() {
		echo json_encode($this->Clientarea_model->loadSummaryData(getCompanyId()));
	}
}
