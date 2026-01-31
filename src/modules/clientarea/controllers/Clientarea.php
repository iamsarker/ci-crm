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

		$serviceDetail = $this->Order_model->loadOrderServiceById(getCompanyId(), $serviceDetailId);

		if (empty($serviceDetail) || empty($serviceDetail['cp_username'])) {
			$this->session->set_flashdata('alert_error', 'cPanel username not found for this service');
			redirect('clientarea/service_detail/' . $serviceDetailId, 'refresh');
		}

		$serverInfo = $this->Common_model->getServerInfoByOrderServiceId($serviceDetailId, getCompanyId());

		if (empty($serverInfo) || empty($serverInfo['hostname'])) {
			$this->session->set_flashdata('alert_error', 'Server information not found');
			redirect('clientarea/service_detail/' . $serviceDetailId, 'refresh');
		}

		$cpUsername = $serviceDetail['cp_username'];
		$query = "https://".$serverInfo["hostname"].":2087/json-api/create_user_session?api.version=1&user=".urlencode($cpUsername)."&service=cpaneld";

		$curl = curl_init();
		// NOTE: SSL verification disabled for WHM servers with self-signed certificates
		// For production with valid SSL, set CURLOPT_SSL_VERIFYPEER to true
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);

		$header[0] = "Authorization: WHM ".$serverInfo['username'].":" . preg_replace("'(\r|\n)'","", base64_decode(base64_decode($serverInfo['access_hash'])));
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl, CURLOPT_URL, $query);
		$result = curl_exec($curl);

		if ($result == false) {
			$this->session->set_flashdata('alert_error', 'Failed to connect to server: ' . curl_error($curl));
			curl_close($curl);
			redirect('clientarea/service_detail/' . $serviceDetailId, 'refresh');
		}

		curl_close($curl);

		$decoded_response = json_decode($result, true);

		if (isset($decoded_response['data']) && !empty($decoded_response['data']['url'])) {
			$url = $decoded_response['data']['url'];
			header("Location: $url");
			exit;
		}

		$this->session->set_flashdata('alert_error', 'Failed to create cPanel session');
		redirect('clientarea/service_detail/' . $serviceDetailId, 'refresh');
	}

	public function webmail_single_sign_on($orderId, $serviceDetailId){

		$serviceDetail = $this->Order_model->loadOrderServiceById(getCompanyId(), $serviceDetailId);

		if (empty($serviceDetail) || empty($serviceDetail['cp_username'])) {
			$this->session->set_flashdata('alert_error', 'cPanel username not found for this service');
			redirect('clientarea/service_detail/' . $serviceDetailId, 'refresh');
		}

		$serverInfo = $this->Common_model->getServerInfoByOrderServiceId($serviceDetailId, getCompanyId());

		if (empty($serverInfo) || empty($serverInfo['hostname'])) {
			$this->session->set_flashdata('alert_error', 'Server information not found');
			redirect('clientarea/service_detail/' . $serviceDetailId, 'refresh');
		}

		$cpUsername = $serviceDetail['cp_username'];
		$query = "https://".$serverInfo["hostname"].":2087/json-api/create_user_session?api.version=1&user=".urlencode($cpUsername)."&service=webmaild";

		$curl = curl_init();
		// NOTE: SSL verification disabled for WHM servers with self-signed certificates
		// For production with valid SSL, set CURLOPT_SSL_VERIFYPEER to true
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);

		$header[0] = "Authorization: WHM ".$serverInfo['username'].":" . preg_replace("'(\r|\n)'","", base64_decode(base64_decode($serverInfo['access_hash'])));
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl, CURLOPT_URL, $query);
		$result = curl_exec($curl);

		if ($result == false) {
			$this->session->set_flashdata('alert_error', 'Failed to connect to server: ' . curl_error($curl));
			curl_close($curl);
			redirect('clientarea/service_detail/' . $serviceDetailId, 'refresh');
		}

		curl_close($curl);

		$decoded_response = json_decode($result, true);

		if (isset($decoded_response['data']) && !empty($decoded_response['data']['url'])) {
			$url = $decoded_response['data']['url'];
			header("Location: $url");
			exit;
		}

		$this->session->set_flashdata('alert_error', 'Failed to create webmail session');
		redirect('clientarea/service_detail/' . $serviceDetailId, 'refresh');
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

	public function changePassword()
	{
		if (!$this->input->post()) {
			$this->load->view('clientarea_changepassword');
			return;
		}

		$currentPassword = $this->input->post('current_password');
		$newPassword     = $this->input->post('new_password');
		$confirmPassword = $this->input->post('confirm_password');

		if (strlen($newPassword) < 8) {
			$this->session->set_flashdata('alert_error', 'New password must be at least 8 characters.');
			redirect('/clientarea/changePassword', 'refresh');
			return;
		}

		if ($newPassword !== $confirmPassword) {
			$this->session->set_flashdata('alert_error', 'New passwords do not match.');
			redirect('/clientarea/changePassword', 'refresh');
			return;
		}

		$result = $this->Clientarea_model->changePassword(getCustomerId(), $currentPassword, $newPassword);

		if ($result['success']) {
			$appSettings = getAppSettings();
			$userName = !empty($result['first_name']) ? htmlspecialchars($result['first_name']) : 'User';

			$body = 'Dear ' . $userName . ',<br><br>';
			$body .= 'Your password has been changed successfully.<br><br>';
			$body .= 'If you did not make this change, please contact us immediately.<br><br>';
			$body .= 'Thanks & Regards<br>';
			$body .= $appSettings->company_name . ' Support';

			$subject = "Password Changed - " . $appSettings->company_name;
			sendHtmlEmail($result['email'], $subject, $body);

			$this->session->set_flashdata('alert_success', 'Password changed successfully.');
			redirect('/clientarea/changePassword', 'refresh');
		} else {
			$this->session->set_flashdata('alert_error', $result['msg']);
			redirect('/clientarea/changePassword', 'refresh');
		}
	}

	public function summary_api() {
		// Send CSRF headers for Angular to update token
		$this->sendCsrfHeaders();
		header('Content-Type: application/json');

		echo json_encode($this->Clientarea_model->loadSummaryData(getCompanyId()));
	}
}
