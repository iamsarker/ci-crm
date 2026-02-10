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
		$data['countries'] = $this->Clientarea_model->getCountries();

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

		if (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[a-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
			$this->session->set_flashdata('alert_error', 'Password must contain at least one uppercase letter, one lowercase letter, and one number.');
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

	/**
	 * Update domain nameservers via AJAX
	 * Updates both database and registrar API
	 */
	public function update_nameservers() {
		$this->sendCsrfHeaders();
		header('Content-Type: application/json');

		// Only accept POST requests
		if (!$this->input->post()) {
			echo json_encode(array('success' => false, 'msg' => 'Invalid request method'));
			return;
		}

		$domainId = $this->input->post('domain_id');
		$ns1 = $this->input->post('ns1');
		$ns2 = $this->input->post('ns2');
		$ns3 = $this->input->post('ns3');
		$ns4 = $this->input->post('ns4');
		$dnsType = $this->input->post('dns_type');

		// Validate domain ID
		if (!is_numeric($domainId) || $domainId <= 0) {
			echo json_encode(array('success' => false, 'msg' => 'Invalid domain ID'));
			return;
		}

		$companyId = getCompanyId();

		// Get domain and registrar info
		$domainInfo = $this->Clientarea_model->getDomainRegistrarInfo($domainId, $companyId);

		if (empty($domainInfo)) {
			echo json_encode(array('success' => false, 'msg' => 'Domain not found or access denied'));
			return;
		}

		$apiResult = array('success' => true, 'msg' => '');

		// If registrar is configured, update via API
		if (!empty($domainInfo['dom_register_id']) && !empty($domainInfo['platform'])) {
			$apiResult = $this->updateNameserversViaApi($domainInfo, $ns1, $ns2, $ns3, $ns4);
		}

		// Update database regardless of API result (user might want local update only)
		$dbResult = $this->Clientarea_model->updateDomainNameservers(
			$domainId, $companyId, $ns1, $ns2, $ns3, $ns4, $dnsType
		);

		if ($dbResult['success']) {
			if ($apiResult['success']) {
				echo json_encode(array('success' => true, 'msg' => 'Nameservers updated successfully'));
			} else {
				// DB updated but API failed
				echo json_encode(array(
					'success' => true,
					'msg' => 'Nameservers saved locally. Registrar API: ' . $apiResult['msg']
				));
			}
		} else {
			echo json_encode($dbResult);
		}
	}

	/**
	 * Update nameservers via domain registrar API
	 */
	private function updateNameserversViaApi($domainInfo, $ns1, $ns2, $ns3, $ns4) {
		$platform = strtoupper($domainInfo['platform']);

		switch ($platform) {
			case 'STARGATE': // ResellerClub, Resell.biz
				return $this->updateNameserversStargate($domainInfo, $ns1, $ns2, $ns3, $ns4);

			case 'NAMECHEAP':
				return $this->updateNameserversNamecheap($domainInfo, $ns1, $ns2, $ns3, $ns4);

			default:
				return array('success' => false, 'msg' => 'Unsupported registrar platform: ' . $platform);
		}
	}

	/**
	 * Update nameservers via Stargate (ResellerClub) API
	 */
	private function updateNameserversStargate($domainInfo, $ns1, $ns2, $ns3, $ns4) {
		if (empty($domainInfo['domain_order_id'])) {
			return array('success' => false, 'msg' => 'Domain order ID not found for registrar');
		}

		// Use ns_update_api from database, fallback to base_url + default endpoint
		$nsUpdateApi = !empty($domainInfo['ns_update_api'])
			? rtrim(strval($domainInfo['ns_update_api']), '/')
			: rtrim(strval($domainInfo['api_base_url'] ?? ''), '/') . '/modify-ns.json';

		$authUserId = strval($domainInfo['auth_userid'] ?? '');
		$apiKey = strval($domainInfo['auth_apikey'] ?? '');
		$orderId = strval($domainInfo['domain_order_id'] ?? '');

		// Build nameserver string
		$nsList = array(strval($ns1), strval($ns2));
		if (!empty(trim(strval($ns3)))) $nsList[] = strval($ns3);
		if (!empty(trim(strval($ns4)))) $nsList[] = strval($ns4);

		$postData = array(
			'auth-userid' => $authUserId,
			'api-key' => $apiKey,
			'order-id' => $orderId,
			'ns' => $nsList
		);

		$response = $this->makeApiRequest($nsUpdateApi, $postData, 'POST');

		if ($response === false) {
			return array('success' => false, 'msg' => 'Failed to connect to registrar API');
		}

		$result = json_decode($response, true);

		if (isset($result['status']) && strtolower($result['status']) == 'success') {
			return array('success' => true, 'msg' => 'Nameservers updated at registrar');
		}

		$errorMsg = isset($result['message']) ? $result['message'] : 'Unknown API error';
		return array('success' => false, 'msg' => $errorMsg);
	}

	/**
	 * Update nameservers via Namecheap API
	 */
	private function updateNameserversNamecheap($domainInfo, $ns1, $ns2, $ns3, $ns4) {
		$domain = strval($domainInfo['domain'] ?? '');
		$domainParts = explode('.', $domain, 2);

		if (count($domainParts) < 2) {
			return array('success' => false, 'msg' => 'Invalid domain format');
		}

		$sld = $domainParts[0];
		$tld = $domainParts[1];

		// Use ns_update_api from database, fallback to api_base_url
		$nsUpdateApi = !empty($domainInfo['ns_update_api'])
			? rtrim(strval($domainInfo['ns_update_api']), '/')
			: rtrim(strval($domainInfo['api_base_url'] ?? ''), '/');

		$apiUser = strval($domainInfo['auth_userid'] ?? '');
		$apiKey = strval($domainInfo['auth_apikey'] ?? '');

		// Build nameserver list
		$nsList = strval($ns1) . ',' . strval($ns2);
		if (!empty(trim(strval($ns3)))) $nsList .= ',' . strval($ns3);
		if (!empty(trim(strval($ns4)))) $nsList .= ',' . strval($ns4);

		// Get client IP for Namecheap API
		$clientIp = strval($this->input->ip_address());

		$url = $nsUpdateApi . '?ApiUser=' . urlencode($apiUser)
			. '&ApiKey=' . urlencode($apiKey)
			. '&UserName=' . urlencode($apiUser)
			. '&Command=namecheap.domains.dns.setCustom'
			. '&ClientIp=' . urlencode($clientIp)
			. '&SLD=' . urlencode($sld)
			. '&TLD=' . urlencode($tld)
			. '&Nameservers=' . urlencode($nsList);

		$response = $this->makeApiRequest($url, array(), 'GET');

		if ($response === false) {
			return array('success' => false, 'msg' => 'Failed to connect to Namecheap API');
		}

		// Parse XML response
		$xml = @simplexml_load_string($response);
		if ($xml === false) {
			return array('success' => false, 'msg' => 'Invalid API response');
		}

		$status = (string)$xml->attributes()->Status;
		if (strtolower($status) == 'ok') {
			return array('success' => true, 'msg' => 'Nameservers updated at Namecheap');
		}

		$errorMsg = 'API Error';
		if (isset($xml->Errors->Error)) {
			$errorMsg = (string)$xml->Errors->Error;
		}

		return array('success' => false, 'msg' => $errorMsg);
	}

	/**
	 * Make HTTP API request
	 */
	private function makeApiRequest($url, $data = array(), $method = 'GET') {
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		// Note: SSL verification disabled for development/Windows compatibility
		// For production with valid SSL, set CURLOPT_SSL_VERIFYPEER to true
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

		if ($method == 'POST') {
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
			curl_setopt($curl, CURLOPT_URL, $url);
		} else {
			curl_setopt($curl, CURLOPT_URL, $url);
		}

		log_message('debug', 'API Request URL: ' . $url);

		$response = curl_exec($curl);

		if ($response === false) {
			$error = curl_error($curl);
			$errno = curl_errno($curl);
			log_message('error', 'API Request Failed [' . $errno . ']: ' . $error . ' | URL: ' . $url);
		}

		curl_close($curl);

		return $response;
	}

	/**
	 * Sync domain data (nameservers + contacts) from registrar API
	 */
	public function sync_domain_data() {
		$this->sendCsrfHeaders();
		header('Content-Type: application/json');

		try {
			if (!$this->input->post()) {
				echo json_encode(array('success' => false, 'msg' => 'Invalid request method'));
				return;
			}

			$domainId = $this->input->post('domain_id');
			$companyId = getCompanyId();

			if (!is_numeric($domainId) || $domainId <= 0) {
				echo json_encode(array('success' => false, 'msg' => 'Invalid domain ID'));
				return;
			}

			$domainInfo = $this->Clientarea_model->getDomainRegistrarInfo($domainId, $companyId);

			if (empty($domainInfo)) {
				echo json_encode(array('success' => false, 'msg' => 'Domain not found or access denied'));
				return;
			}

			if (empty($domainInfo['dom_register_id']) || empty($domainInfo['platform'])) {
				echo json_encode(array('success' => false, 'msg' => 'No registrar configured for this domain. Please contact support.'));
				return;
			}

			$syncResult = $this->syncDomainFromApi($domainInfo);

			if ($syncResult['success'] && !empty($syncResult['data'])) {
				$saveResult = $this->Clientarea_model->saveSyncedDomainData($domainId, $companyId, $syncResult['data']);
				if ($saveResult['success']) {
					echo json_encode(array(
						'success' => true,
						'msg' => 'Domain data synced successfully',
						'data' => $syncResult['data']
					));
				} else {
					echo json_encode($saveResult);
				}
			} else {
				echo json_encode($syncResult);
			}
		} catch (Exception $e) {
			log_message('error', 'sync_domain_data error: ' . $e->getMessage());
			echo json_encode(array('success' => false, 'msg' => 'Server error: ' . $e->getMessage()));
		}
	}

	/**
	 * Sync domain data from registrar API
	 */
	private function syncDomainFromApi($domainInfo) {
		$platform = strtoupper($domainInfo['platform']);

		switch ($platform) {
			case 'STARGATE':
				return $this->syncDomainStargate($domainInfo);
			case 'NAMECHEAP':
				return $this->syncDomainNamecheap($domainInfo);
			default:
				return array('success' => false, 'msg' => 'Unsupported registrar platform');
		}
	}

	/**
	 * Sync domain from Stargate (ResellerClub) API
	 */
	private function syncDomainStargate($domainInfo) {
		// Validate required registrar configuration
		if (empty($domainInfo['api_base_url']) && empty($domainInfo['contact_details_api'])) {
			return array('success' => false, 'msg' => 'Registrar API URL not configured. Please contact support.');
		}

		if (empty($domainInfo['auth_userid']) || empty($domainInfo['auth_apikey'])) {
			return array('success' => false, 'msg' => 'Registrar API credentials not configured. Please contact support.');
		}

		$contactDetailsApi = !empty($domainInfo['contact_details_api'])
			? strval($domainInfo['contact_details_api'])
			: rtrim(strval($domainInfo['api_base_url']), '/') . '/details-by-name.json';

		$domain = strval($domainInfo['domain'] ?? '');
		$authUserId = strval($domainInfo['auth_userid'] ?? '');
		$apiKey = strval($domainInfo['auth_apikey'] ?? '');

		$url = $contactDetailsApi . '?auth-userid=' . urlencode($authUserId)
			. '&api-key=' . urlencode($apiKey)
			. '&domain-name=' . urlencode($domain)
			. '&options=All';

		log_message('debug', 'Stargate Sync URL: ' . preg_replace('/api-key=[^&]+/', 'api-key=***', $url));

		$response = $this->makeApiRequest($url, array(), 'GET');

		if ($response === false) {
			return array('success' => false, 'msg' => 'Failed to connect to registrar API. Please verify your internet connection and registrar credentials.');
		}

		// Check for empty response
		if (empty($response)) {
			return array('success' => false, 'msg' => 'Empty response from registrar API');
		}

		$result = json_decode($response, true);

		if (isset($result['status']) && strtolower($result['status']) == 'error') {
			$errorMsg = isset($result['message']) ? $result['message'] : 'API Error';
			return array('success' => false, 'msg' => $errorMsg);
		}

		// Parse the response data
		$syncData = array();

		// Nameservers - ensure string values
		if (isset($result['ns1'])) $syncData['ns1'] = is_array($result['ns1']) ? ($result['ns1'][0] ?? '') : strval($result['ns1']);
		if (isset($result['ns2'])) $syncData['ns2'] = is_array($result['ns2']) ? ($result['ns2'][0] ?? '') : strval($result['ns2']);
		if (isset($result['ns3'])) $syncData['ns3'] = is_array($result['ns3']) ? ($result['ns3'][0] ?? '') : strval($result['ns3']);
		if (isset($result['ns4'])) $syncData['ns4'] = is_array($result['ns4']) ? ($result['ns4'][0] ?? '') : strval($result['ns4']);

		// Contact info - Stargate returns contact IDs, we need to fetch contact details
		if (isset($result['registrantcontact']) || isset($result['registrantcontactid'])) {
			$contactId = $result['registrantcontact'] ?? $result['registrantcontactid'] ?? null;
			// Handle if contactId is an array (extract first value or 'contactid' key)
			if (is_array($contactId)) {
				$contactId = $contactId['contactid'] ?? $contactId[0] ?? null;
			}
			if ($contactId && !is_array($contactId)) {
				$contactDetails = $this->fetchStargateContactDetails($domainInfo, strval($contactId));
				if (!empty($contactDetails)) {
					$syncData = array_merge($syncData, $contactDetails);
				}
			}
		}

		// If direct contact info is available - ensure string values
		if (isset($result['registrant_name'])) $syncData['contact_name'] = is_array($result['registrant_name']) ? '' : strval($result['registrant_name']);
		if (isset($result['registrant_company'])) $syncData['contact_company'] = is_array($result['registrant_company']) ? '' : strval($result['registrant_company']);
		if (isset($result['registrant_email'])) $syncData['contact_email'] = is_array($result['registrant_email']) ? '' : strval($result['registrant_email']);
		if (isset($result['registrant_phone'])) $syncData['contact_phone'] = is_array($result['registrant_phone']) ? '' : strval($result['registrant_phone']);
		if (isset($result['registrant_address1'])) $syncData['contact_address1'] = is_array($result['registrant_address1']) ? '' : strval($result['registrant_address1']);
		if (isset($result['registrant_city'])) $syncData['contact_city'] = is_array($result['registrant_city']) ? '' : strval($result['registrant_city']);
		if (isset($result['registrant_state'])) $syncData['contact_state'] = is_array($result['registrant_state']) ? '' : strval($result['registrant_state']);
		if (isset($result['registrant_zip'])) $syncData['contact_zip'] = is_array($result['registrant_zip']) ? '' : strval($result['registrant_zip']);
		if (isset($result['registrant_country'])) $syncData['contact_country'] = is_array($result['registrant_country']) ? '' : strval($result['registrant_country']);

		return array('success' => true, 'msg' => 'Data synced', 'data' => $syncData);
	}

	/**
	 * Fetch contact details from Stargate API
	 */
	private function fetchStargateContactDetails($domainInfo, $contactId) {
		$baseUrl = rtrim(strval($domainInfo['api_base_url'] ?? ''), '/');
		// Remove /domains from the base URL if present for contacts API
		$baseUrl = str_replace('/domains', '', $baseUrl);

		$url = $baseUrl . '/contacts/details.json?auth-userid=' . urlencode(strval($domainInfo['auth_userid'] ?? ''))
			. '&api-key=' . urlencode(strval($domainInfo['auth_apikey'] ?? ''))
			. '&contact-id=' . urlencode(strval($contactId));

		$response = $this->makeApiRequest($url, array(), 'GET');

		if ($response === false) {
			return array();
		}

		$result = json_decode($response, true);

		if (empty($result) || isset($result['status'])) {
			return array();
		}

		return array(
			'contact_name' => ($result['name'] ?? ''),
			'contact_company' => ($result['company'] ?? ''),
			'contact_email' => ($result['emailaddr'] ?? ''),
			'contact_phone' => ($result['telnocc'] ?? '') . ($result['telno'] ?? ''),
			'contact_address1' => ($result['address1'] ?? ''),
			'contact_address2' => ($result['address2'] ?? ''),
			'contact_city' => ($result['city'] ?? ''),
			'contact_state' => ($result['state'] ?? ''),
			'contact_zip' => ($result['zip'] ?? ''),
			'contact_country' => ($result['country'] ?? '')
		);
	}

	/**
	 * Sync domain from Namecheap API
	 */
	private function syncDomainNamecheap($domainInfo) {
		$domain = strval($domainInfo['domain'] ?? '');
		$domainParts = explode('.', $domain, 2);

		if (count($domainParts) < 2) {
			return array('success' => false, 'msg' => 'Invalid domain format');
		}

		$sld = $domainParts[0];
		$tld = $domainParts[1];

		$contactDetailsApi = !empty($domainInfo['contact_details_api'])
			? strval($domainInfo['contact_details_api'])
			: strval($domainInfo['api_base_url'] ?? '');

		$apiUser = strval($domainInfo['auth_userid'] ?? '');
		$apiKey = strval($domainInfo['auth_apikey'] ?? '');
		$clientIp = $this->input->ip_address();

		// Get contacts
		$url = $contactDetailsApi . '?ApiUser=' . urlencode($apiUser)
			. '&ApiKey=' . urlencode($apiKey)
			. '&UserName=' . urlencode($apiUser)
			. '&Command=namecheap.domains.getContacts'
			. '&ClientIp=' . urlencode($clientIp)
			. '&DomainName=' . urlencode($domain);

		$response = $this->makeApiRequest($url, array(), 'GET');

		if ($response === false) {
			return array('success' => false, 'msg' => 'Failed to connect to Namecheap API');
		}

		$xml = @simplexml_load_string($response);
		if ($xml === false) {
			return array('success' => false, 'msg' => 'Invalid API response');
		}

		$status = (string)$xml->attributes()->Status;
		if (strtolower($status) != 'ok') {
			$errorMsg = 'API Error';
			if (isset($xml->Errors->Error)) {
				$errorMsg = (string)$xml->Errors->Error;
			}
			return array('success' => false, 'msg' => $errorMsg);
		}

		$syncData = array();

		// Parse registrant contact
		if (isset($xml->CommandResponse->DomainContactsResult->Registrant)) {
			$reg = $xml->CommandResponse->DomainContactsResult->Registrant;
			$syncData['contact_name'] = (string)($reg->FirstName ?? '') . ' ' . (string)($reg->LastName ?? '');
			$syncData['contact_company'] = (string)($reg->OrganizationName ?? '');
			$syncData['contact_email'] = (string)($reg->EmailAddress ?? '');
			$syncData['contact_phone'] = (string)($reg->Phone ?? '');
			$syncData['contact_address1'] = (string)($reg->Address1 ?? '');
			$syncData['contact_address2'] = (string)($reg->Address2 ?? '');
			$syncData['contact_city'] = (string)($reg->City ?? '');
			$syncData['contact_state'] = (string)($reg->StateProvince ?? '');
			$syncData['contact_zip'] = (string)($reg->PostalCode ?? '');
			$syncData['contact_country'] = (string)($reg->Country ?? '');
		}

		// Now get nameservers
		$nsUrl = $contactDetailsApi . '?ApiUser=' . urlencode($apiUser)
			. '&ApiKey=' . urlencode($apiKey)
			. '&UserName=' . urlencode($apiUser)
			. '&Command=namecheap.domains.dns.getList'
			. '&ClientIp=' . urlencode(strval($clientIp))
			. '&SLD=' . urlencode(strval($sld))
			. '&TLD=' . urlencode(strval($tld));

		$nsResponse = $this->makeApiRequest($nsUrl, array(), 'GET');

		if ($nsResponse !== false) {
			$nsXml = @simplexml_load_string($nsResponse);
			if ($nsXml !== false && strtolower((string)$nsXml->attributes()->Status) == 'ok') {
				if (isset($nsXml->CommandResponse->DomainDNSGetListResult->Nameserver)) {
					$ns = $nsXml->CommandResponse->DomainDNSGetListResult->Nameserver;
					if (isset($ns[0])) $syncData['ns1'] = (string)$ns[0];
					if (isset($ns[1])) $syncData['ns2'] = (string)$ns[1];
					if (isset($ns[2])) $syncData['ns3'] = (string)$ns[2];
					if (isset($ns[3])) $syncData['ns4'] = (string)$ns[3];
				}
			}
		}

		return array('success' => true, 'msg' => 'Data synced', 'data' => $syncData);
	}

	/**
	 * Update domain contact information via AJAX
	 */
	public function update_contacts() {
		$this->sendCsrfHeaders();
		header('Content-Type: application/json');

		if (!$this->input->post()) {
			echo json_encode(array('success' => false, 'msg' => 'Invalid request method'));
			return;
		}

		$domainId = $this->input->post('domain_id');
		$companyId = getCompanyId();

		if (!is_numeric($domainId) || $domainId <= 0) {
			echo json_encode(array('success' => false, 'msg' => 'Invalid domain ID'));
			return;
		}

		$contactData = array(
			'contact_name' => $this->input->post('contact_name'),
			'contact_company' => $this->input->post('contact_company'),
			'contact_email' => $this->input->post('contact_email'),
			'contact_phone' => $this->input->post('contact_phone'),
			'contact_address1' => $this->input->post('contact_address1'),
			'contact_address2' => $this->input->post('contact_address2'),
			'contact_city' => $this->input->post('contact_city'),
			'contact_state' => $this->input->post('contact_state'),
			'contact_zip' => $this->input->post('contact_zip'),
			'contact_country' => $this->input->post('contact_country')
		);

		$domainInfo = $this->Clientarea_model->getDomainRegistrarInfo($domainId, $companyId);

		if (empty($domainInfo)) {
			echo json_encode(array('success' => false, 'msg' => 'Domain not found or access denied'));
			return;
		}

		$apiResult = array('success' => true, 'msg' => '');

		// If registrar is configured, update via API
		if (!empty($domainInfo['dom_register_id']) && !empty($domainInfo['platform'])) {
			$apiResult = $this->updateContactsViaApi($domainInfo, $contactData);
		}

		// Update database
		$dbResult = $this->Clientarea_model->updateDomainContacts($domainId, $companyId, $contactData);

		if ($dbResult['success']) {
			if ($apiResult['success']) {
				echo json_encode(array('success' => true, 'msg' => 'Contact information updated successfully'));
			} else {
				echo json_encode(array(
					'success' => true,
					'msg' => 'Contacts saved locally. Registrar API: ' . $apiResult['msg']
				));
			}
		} else {
			echo json_encode($dbResult);
		}
	}

	/**
	 * Update contacts via registrar API
	 */
	private function updateContactsViaApi($domainInfo, $contactData) {
		$platform = strtoupper($domainInfo['platform']);

		switch ($platform) {
			case 'STARGATE':
				return $this->updateContactsStargate($domainInfo, $contactData);
			case 'NAMECHEAP':
				return $this->updateContactsNamecheap($domainInfo, $contactData);
			default:
				return array('success' => false, 'msg' => 'Unsupported registrar platform');
		}
	}

	/**
	 * Update contacts via Stargate (ResellerClub) API
	 */
	private function updateContactsStargate($domainInfo, $contactData) {
		// Note: ResellerClub requires updating contact by contact-id
		// This is a simplified implementation - full implementation would need contact ID management
		$contactUpdateApi = !empty($domainInfo['contact_update_api'])
			? strval($domainInfo['contact_update_api'])
			: rtrim(strval($domainInfo['api_base_url'] ?? ''), '/') . '/modify-contact.json';

		if (empty($domainInfo['domain_order_id'])) {
			return array('success' => false, 'msg' => 'Domain order ID not found');
		}

		$postData = array(
			'auth-userid' => strval($domainInfo['auth_userid'] ?? ''),
			'api-key' => strval($domainInfo['auth_apikey'] ?? ''),
			'order-id' => strval($domainInfo['domain_order_id'] ?? ''),
			'reg-contact-id' => strval($domainInfo['domain_cust_id'] ?? ''),
			'admin-contact-id' => strval($domainInfo['domain_cust_id'] ?? ''),
			'tech-contact-id' => strval($domainInfo['domain_cust_id'] ?? ''),
			'billing-contact-id' => strval($domainInfo['domain_cust_id'] ?? '')
		);

		$response = $this->makeApiRequest($contactUpdateApi, $postData, 'POST');

		if ($response === false) {
			return array('success' => false, 'msg' => 'Failed to connect to registrar API');
		}

		$result = json_decode($response, true);

		if (isset($result['status']) && strtolower($result['status']) == 'success') {
			return array('success' => true, 'msg' => 'Contacts updated at registrar');
		}

		$errorMsg = isset($result['message']) ? $result['message'] : 'API update not fully supported';
		return array('success' => false, 'msg' => $errorMsg);
	}

	/**
	 * Update contacts via Namecheap API
	 */
	private function updateContactsNamecheap($domainInfo, $contactData) {
		$contactUpdateApi = !empty($domainInfo['contact_update_api'])
			? strval($domainInfo['contact_update_api'])
			: strval($domainInfo['api_base_url'] ?? '');

		$domain = strval($domainInfo['domain'] ?? '');
		$apiUser = strval($domainInfo['auth_userid'] ?? '');
		$apiKey = strval($domainInfo['auth_apikey'] ?? '');
		$clientIp = strval($this->input->ip_address());

		// Parse name into first/last
		$nameParts = explode(' ', trim(strval($contactData['contact_name'] ?? '')), 2);
		$firstName = $nameParts[0] ?? '';
		$lastName = $nameParts[1] ?? $firstName;

		$url = $contactUpdateApi . '?ApiUser=' . urlencode($apiUser)
			. '&ApiKey=' . urlencode($apiKey)
			. '&UserName=' . urlencode($apiUser)
			. '&Command=namecheap.domains.setContacts'
			. '&ClientIp=' . urlencode($clientIp)
			. '&DomainName=' . urlencode($domain)
			. '&RegistrantFirstName=' . urlencode(strval($firstName))
			. '&RegistrantLastName=' . urlencode(strval($lastName))
			. '&RegistrantOrganizationName=' . urlencode(strval($contactData['contact_company'] ?? ''))
			. '&RegistrantEmailAddress=' . urlencode(strval($contactData['contact_email'] ?? ''))
			. '&RegistrantPhone=' . urlencode(strval($contactData['contact_phone'] ?? ''))
			. '&RegistrantAddress1=' . urlencode(strval($contactData['contact_address1'] ?? ''))
			. '&RegistrantCity=' . urlencode(strval($contactData['contact_city'] ?? ''))
			. '&RegistrantStateProvince=' . urlencode(strval($contactData['contact_state'] ?? ''))
			. '&RegistrantPostalCode=' . urlencode(strval($contactData['contact_zip'] ?? ''))
			. '&RegistrantCountry=' . urlencode(strval($contactData['contact_country'] ?? ''))
			// Admin, Tech, AuxBilling - same as registrant
			. '&AdminFirstName=' . urlencode(strval($firstName))
			. '&AdminLastName=' . urlencode(strval($lastName))
			. '&AdminOrganizationName=' . urlencode(strval($contactData['contact_company'] ?? ''))
			. '&AdminEmailAddress=' . urlencode(strval($contactData['contact_email'] ?? ''))
			. '&AdminPhone=' . urlencode(strval($contactData['contact_phone'] ?? ''))
			. '&AdminAddress1=' . urlencode(strval($contactData['contact_address1'] ?? ''))
			. '&AdminCity=' . urlencode(strval($contactData['contact_city'] ?? ''))
			. '&AdminStateProvince=' . urlencode(strval($contactData['contact_state'] ?? ''))
			. '&AdminPostalCode=' . urlencode(strval($contactData['contact_zip'] ?? ''))
			. '&AdminCountry=' . urlencode(strval($contactData['contact_country'] ?? ''))
			. '&TechFirstName=' . urlencode(strval($firstName))
			. '&TechLastName=' . urlencode(strval($lastName))
			. '&TechOrganizationName=' . urlencode(strval($contactData['contact_company'] ?? ''))
			. '&TechEmailAddress=' . urlencode(strval($contactData['contact_email'] ?? ''))
			. '&TechPhone=' . urlencode(strval($contactData['contact_phone'] ?? ''))
			. '&TechAddress1=' . urlencode(strval($contactData['contact_address1'] ?? ''))
			. '&TechCity=' . urlencode(strval($contactData['contact_city'] ?? ''))
			. '&TechStateProvince=' . urlencode(strval($contactData['contact_state'] ?? ''))
			. '&TechPostalCode=' . urlencode(strval($contactData['contact_zip'] ?? ''))
			. '&TechCountry=' . urlencode(strval($contactData['contact_country'] ?? ''))
			. '&AuxBillingFirstName=' . urlencode(strval($firstName))
			. '&AuxBillingLastName=' . urlencode(strval($lastName))
			. '&AuxBillingOrganizationName=' . urlencode(strval($contactData['contact_company'] ?? ''))
			. '&AuxBillingEmailAddress=' . urlencode(strval($contactData['contact_email'] ?? ''))
			. '&AuxBillingPhone=' . urlencode(strval($contactData['contact_phone'] ?? ''))
			. '&AuxBillingAddress1=' . urlencode(strval($contactData['contact_address1'] ?? ''))
			. '&AuxBillingCity=' . urlencode(strval($contactData['contact_city'] ?? ''))
			. '&AuxBillingStateProvince=' . urlencode(strval($contactData['contact_state'] ?? ''))
			. '&AuxBillingPostalCode=' . urlencode(strval($contactData['contact_zip'] ?? ''))
			. '&AuxBillingCountry=' . urlencode(strval($contactData['contact_country'] ?? ''));

		$response = $this->makeApiRequest($url, array(), 'GET');

		if ($response === false) {
			return array('success' => false, 'msg' => 'Failed to connect to Namecheap API');
		}

		$xml = @simplexml_load_string($response);
		if ($xml === false) {
			return array('success' => false, 'msg' => 'Invalid API response');
		}

		$status = (string)$xml->attributes()->Status;
		if (strtolower($status) == 'ok') {
			return array('success' => true, 'msg' => 'Contacts updated at Namecheap');
		}

		$errorMsg = 'API Error';
		if (isset($xml->Errors->Error)) {
			$errorMsg = (string)$xml->Errors->Error;
		}

		return array('success' => false, 'msg' => $errorMsg);
	}
}
