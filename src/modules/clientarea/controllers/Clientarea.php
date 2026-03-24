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
			$dnsResult = $this->Clientarea_model->getServerDnsInfo($data['detail']['product_service_pricing_id']);
			$data['dns'] = !empty($dnsResult) ? $dnsResult[0] : array();
		} else {
			$data['dns'] = array();
		}

		// Get cPanel usage stats if available
		$data['cpanel_stats'] = $this->Clientarea_model->getCpanelUsageStats($id, getCompanyId());

		$this->load->view('clientarea_service_detail', $data);
	}

	/**
	 * Sync cPanel usage stats via AJAX
	 */
	public function sync_cpanel_usage() {
		$this->sendCsrfHeaders();
		header('Content-Type: application/json');

		if (!$this->input->post()) {
			echo json_encode(array('success' => false, 'msg' => 'Invalid request method'));
			return;
		}

		$serviceId = $this->input->post('service_id');
		$companyId = getCompanyId();

		if (!is_numeric($serviceId) || $serviceId <= 0) {
			echo json_encode(array('success' => false, 'msg' => 'Invalid service ID'));
			return;
		}

		// Get service details
		$serviceDetail = $this->Order_model->loadOrderServiceById($companyId, $serviceId);

		if (empty($serviceDetail)) {
			echo json_encode(array('success' => false, 'msg' => 'Service not found or access denied'));
			return;
		}

		if (empty($serviceDetail['cp_username'])) {
			echo json_encode(array('success' => false, 'msg' => 'cPanel username not configured for this service'));
			return;
		}

		// Get server info
		$serverInfo = $this->Common_model->getServerInfoByOrderServiceId($serviceId, $companyId);

		if (empty($serverInfo) || empty($serverInfo['hostname'])) {
			echo json_encode(array('success' => false, 'msg' => 'Server information not found'));
			return;
		}

		// Load cPanel helper
		$this->load->helper('cpanel');

		// Get usage stats from cPanel
		$statsResult = whm_get_account_stats($serverInfo, $serviceDetail['cp_username']);

		if (!$statsResult['success']) {
			echo json_encode(array('success' => false, 'msg' => 'Failed to fetch cPanel stats: ' . ($statsResult['error'] ?? 'Unknown error')));
			return;
		}

		// Save stats to database
		$saveResult = $this->Clientarea_model->saveCpanelUsageStats($serviceId, $companyId, $statsResult['stats']);

		if ($saveResult['success']) {
			echo json_encode(array(
				'success' => true,
				'msg' => 'Usage stats synced successfully',
				'stats' => $statsResult['stats']
			));
		} else {
			echo json_encode($saveResult);
		}
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

		try {
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
		} catch (Exception $e) {
			log_message('error', 'update_nameservers error: ' . $e->getMessage());
			echo json_encode(array('success' => false, 'msg' => 'An unexpected error occurred. Please try again.'));
		}
	}

	/**
	 * Update nameservers via domain registrar API
	 */
	private function updateNameserversViaApi($domainInfo, $ns1, $ns2, $ns3, $ns4) {
		$platform = strtoupper($domainInfo['platform']);

		switch ($platform) {
			case 'RESELLERCLUB':
			case 'RESELLBIZ':
			case 'RESELL.BIZ':
			case 'STARGATE':
				return $this->updateNameserversResellerclub($domainInfo, $ns1, $ns2, $ns3, $ns4);

			case 'NAMECHEAP':
				return $this->updateNameserversNamecheap($domainInfo, $ns1, $ns2, $ns3, $ns4);

			default:
				return array('success' => false, 'msg' => 'Unsupported registrar platform: ' . $platform);
		}
	}

	/**
	 * Update nameservers via ResellerClub/Resell.biz/Stargate API
	 */
	private function updateNameserversResellerclub($domainInfo, $ns1, $ns2, $ns3, $ns4) {
		if (empty($domainInfo['domain_order_id'])) {
			return array('success' => false, 'msg' => 'Domain order ID not found for registrar');
		}

		// Use ns_update_api from database, fallback to base_url + default endpoint
		$nsUpdateApi = !empty($domainInfo['ns_update_api'])
			? rtrim(strval($domainInfo['ns_update_api']), '/')
			: rtrim(strval($domainInfo['api_base_url'] ?? ''), '/') . '/api/domains/modify-ns.json';

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

		// Get whitelisted IP for Namecheap API from registrar config
		$serverIp = !empty($domainInfo['whitelisted_ip']) ? $domainInfo['whitelisted_ip'] : ($_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname()));

		$url = $nsUpdateApi . '?ApiUser=' . urlencode($apiUser)
			. '&ApiKey=' . urlencode($apiKey)
			. '&UserName=' . urlencode($apiUser)
			. '&Command=namecheap.domains.dns.setCustom'
			. '&ClientIp=' . urlencode($serverIp)
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
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_USERAGENT, 'WHMAZ-CRM/1.0 (Domain Management)');

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
			case 'RESELLERCLUB':
			case 'RESELLBIZ':
			case 'RESELL.BIZ':
			case 'STARGATE':
				return $this->syncDomainResellerclub($domainInfo);
			case 'NAMECHEAP':
				return $this->syncDomainNamecheap($domainInfo);
			default:
				return array('success' => false, 'msg' => 'Unsupported registrar platform: ' . $platform);
		}
	}

	/**
	 * Sync domain from ResellerClub/Resell.biz/Stargate API
	 */
	private function syncDomainResellerclub($domainInfo) {
		if (empty($domainInfo['api_base_url']) && empty($domainInfo['contact_details_api'])) {
			return array('success' => false, 'msg' => 'Registrar API URL not configured. Please contact support.');
		}

		if (empty($domainInfo['auth_userid']) || empty($domainInfo['auth_apikey'])) {
			return array('success' => false, 'msg' => 'Registrar API credentials not configured. Please contact support.');
		}

		$baseUrl = rtrim(strval($domainInfo['api_base_url'] ?? ''), '/');
		$domain = strval($domainInfo['domain'] ?? '');
		$authUserId = strval($domainInfo['auth_userid'] ?? '');
		$apiKey = strval($domainInfo['auth_apikey'] ?? '');

		// Step 1: Get domain details (nameservers + registrant contact ID)
		$detailsUrl = $baseUrl . '/api/domains/details-by-name.json?'
			. http_build_query(array(
				'auth-userid' => $authUserId,
				'api-key' => $apiKey,
				'domain-name' => $domain,
				'options' => 'All'
			));

		$apiResponse = domain_api_get($detailsUrl);

		if (!$apiResponse['success'] || empty($apiResponse['data'])) {
			$error = $apiResponse['error'] ?? 'Failed to connect to registrar API';
			return array('success' => false, 'msg' => $error);
		}

		$result = $apiResponse['data'];

		if (isset($result['status']) && strtolower($result['status']) == 'error') {
			return array('success' => false, 'msg' => $result['message'] ?? 'API Error');
		}

		log_message('debug', 'ResellerClub Sync response keys: ' . implode(', ', array_keys($result)));

		$syncData = array();

		// Nameservers - ensure string values
		if (isset($result['ns1'])) $syncData['ns1'] = is_array($result['ns1']) ? ($result['ns1'][0] ?? '') : strval($result['ns1']);
		if (isset($result['ns2'])) $syncData['ns2'] = is_array($result['ns2']) ? ($result['ns2'][0] ?? '') : strval($result['ns2']);
		if (isset($result['ns3'])) $syncData['ns3'] = is_array($result['ns3']) ? ($result['ns3'][0] ?? '') : strval($result['ns3']);
		if (isset($result['ns4'])) $syncData['ns4'] = is_array($result['ns4']) ? ($result['ns4'][0] ?? '') : strval($result['ns4']);

		// Step 2: Get registrant contact ID and fetch contact details
		$contactId = $result['registrantcontact'] ?? $result['registrantcontactid'] ?? null;
		if (is_array($contactId)) {
			$contactId = $contactId['contactid'] ?? $contactId[0] ?? null;
		}

		if ($contactId && !is_array($contactId)) {
			$contactUrl = $baseUrl . '/api/contacts/details.json?'
				. http_build_query(array(
					'auth-userid' => $authUserId,
					'api-key' => $apiKey,
					'contact-id' => strval($contactId)
				));

			$contactResponse = domain_api_get($contactUrl);

			if ($contactResponse['success'] && !empty($contactResponse['data'])) {
				$c = $contactResponse['data'];
				if (!isset($c['status'])) {
					$syncData['contact_name'] = strval($c['name'] ?? '');
					$syncData['contact_company'] = strval($c['company'] ?? '');
					$syncData['contact_email'] = strval($c['emailaddr'] ?? '');
					$syncData['contact_phone'] = strval($c['telnocc'] ?? '') . strval($c['telno'] ?? '');
					$syncData['contact_address1'] = strval($c['address1'] ?? '');
					$syncData['contact_address2'] = strval($c['address2'] ?? '');
					$syncData['contact_city'] = strval($c['city'] ?? '');
					$syncData['contact_state'] = strval($c['state'] ?? '');
					$syncData['contact_zip'] = strval($c['zip'] ?? '');
					$syncData['contact_country'] = strval($c['country'] ?? '');
				}
			} else {
				log_message('error', 'ResellerClub contact fetch failed for ID ' . $contactId . ': ' . ($contactResponse['error'] ?? 'unknown'));
			}
		} else {
			log_message('error', 'ResellerClub Sync: No registrant contact ID found in response');
		}

		return array('success' => true, 'msg' => 'Data synced', 'data' => $syncData);
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
		// Get whitelisted IP for Namecheap API from registrar config
		$serverIp = !empty($domainInfo['whitelisted_ip']) ? $domainInfo['whitelisted_ip'] : ($_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname()));

		// Get contacts
		$url = $contactDetailsApi . '?ApiUser=' . urlencode($apiUser)
			. '&ApiKey=' . urlencode($apiKey)
			. '&UserName=' . urlencode($apiUser)
			. '&Command=namecheap.domains.getContacts'
			. '&ClientIp=' . urlencode($serverIp)
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
			. '&ClientIp=' . urlencode(strval($serverIp))
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
			case 'RESELLERCLUB':
			case 'RESELLBIZ':
			case 'RESELL.BIZ':
			case 'STARGATE':
				return $this->updateContactsResellerclub($domainInfo, $contactData);
			case 'NAMECHEAP':
				return $this->updateContactsNamecheap($domainInfo, $contactData);
			default:
				return array('success' => false, 'msg' => 'Unsupported registrar platform: ' . $platform);
		}
	}

	/**
	 * Update contacts via ResellerClub/Resell.biz/Stargate API
	 */
	private function updateContactsResellerclub($domainInfo, $contactData) {
		// Note: ResellerClub requires updating contact by contact-id
		// This is a simplified implementation - full implementation would need contact ID management
		$contactUpdateApi = !empty($domainInfo['contact_update_api'])
			? strval($domainInfo['contact_update_api'])
			: rtrim(strval($domainInfo['api_base_url'] ?? ''), '/') . '/api/contacts/modify.json';

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
		// Get whitelisted IP for Namecheap API from registrar config
		$serverIp = !empty($domainInfo['whitelisted_ip']) ? $domainInfo['whitelisted_ip'] : ($_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname()));

		// Parse name into first/last
		$nameParts = explode(' ', trim(strval($contactData['contact_name'] ?? '')), 2);
		$firstName = $nameParts[0] ?? '';
		$lastName = $nameParts[1] ?? $firstName;

		$url = $contactUpdateApi . '?ApiUser=' . urlencode($apiUser)
			. '&ApiKey=' . urlencode($apiKey)
			. '&UserName=' . urlencode($apiUser)
			. '&Command=namecheap.domains.setContacts'
			. '&ClientIp=' . urlencode($serverIp)
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

	/**
	 * Send EPP/Auth code to customer email
	 */
	public function send_epp_code() {
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

		$domainInfo = $this->Clientarea_model->getDomainRegistrarInfo($domainId, $companyId);

		if (empty($domainInfo)) {
			echo json_encode(array('success' => false, 'msg' => 'Domain not found or access denied'));
			return;
		}

		// Always fetch EPP code fresh from registrar API — never store in DB
		if (empty($domainInfo['dom_register_id']) || empty($domainInfo['platform'])) {
			echo json_encode(array('success' => false, 'msg' => 'No registrar configured for this domain. Please contact support.'));
			return;
		}

		$apiResult = $this->getEppCodeFromApi($domainInfo);
		if (!$apiResult['success'] || empty($apiResult['epp_code'])) {
			echo json_encode(array('success' => false, 'msg' => $apiResult['msg'] ?? 'Failed to retrieve EPP code from registrar'));
			return;
		}

		$eppCode = $apiResult['epp_code'];

		// Get customer email from CUSTOMER session
		$customer = $this->session->userdata('CUSTOMER');
		$customerEmail = !empty($customer['email']) ? $customer['email'] : '';
		$customerName = !empty($customer['first_name']) ? $customer['first_name'] : 'Customer';

		if (empty($customerEmail)) {
			echo json_encode(array('success' => false, 'msg' => 'Customer email not found'));
			return;
		}

		// Send email with EPP code
		$appSettings = getAppSettings();
		$domain = $domainInfo['domain'];

		$subject = "EPP/Auth Code for " . $domain . " - " . $appSettings->company_name;

		$body = "Dear " . htmlspecialchars($customerName) . ",<br><br>";
		$body .= "You have requested the EPP/Authorization code for your domain: <strong>" . htmlspecialchars($domain) . "</strong><br><br>";
		$body .= "Your EPP Code is: <strong style='font-size: 18px; color: #0168fa;'>" . htmlspecialchars($eppCode) . "</strong><br><br>";
		$body .= "<em>Please keep this code secure. It is required for transferring your domain to another registrar.</em><br><br>";
		$body .= "If you did not request this code, please contact us immediately.<br><br>";
		$body .= "Thanks & Regards,<br>";
		$body .= $appSettings->company_name . " Support";

		if (sendHtmlEmail($customerEmail, $subject, $body)) {
			echo json_encode(array('success' => true, 'msg' => 'EPP code has been sent to your email address (' . $customerEmail . ')'));
		} else {
			echo json_encode(array('success' => false, 'msg' => 'Failed to send email. Please try again or contact support.'));
		}
	}

	/**
	 * Get transfer lock status from registrar API
	 */
	public function get_transfer_lock() {
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

		$domainInfo = $this->Clientarea_model->getDomainRegistrarInfo($domainId, $companyId);

		if (empty($domainInfo)) {
			echo json_encode(array('success' => false, 'msg' => 'Domain not found or access denied'));
			return;
		}

		if (empty($domainInfo['dom_register_id']) || empty($domainInfo['platform'])) {
			// No registrar — return local DB value
			echo json_encode(array('success' => true, 'locked' => (int)($domainInfo['transfer_lock'] ?? 1)));
			return;
		}

		$result = $this->getTransferLockFromApi($domainInfo);

		if ($result['success']) {
			// Sync local DB with registrar status
			$this->db->where('id', intval($domainId));
			$this->db->where('company_id', intval($companyId));
			$this->db->update('order_domains', array('transfer_lock' => $result['locked'] ? 1 : 0));
		} else {
			// API failed (e.g. Cloudflare 403) — fall back to DB value
			$result = array('success' => true, 'locked' => (int)($domainInfo['transfer_lock'] ?? 1));
		}

		echo json_encode($result);
	}

	/**
	 * Toggle transfer lock via registrar API
	 */
	public function toggle_transfer_lock() {
		$this->sendCsrfHeaders();
		header('Content-Type: application/json');

		if (!$this->input->post()) {
			echo json_encode(array('success' => false, 'msg' => 'Invalid request method'));
			return;
		}

		$domainId = $this->input->post('domain_id');
		$action = $this->input->post('action'); // 'lock' or 'unlock'
		$companyId = getCompanyId();

		if (!is_numeric($domainId) || $domainId <= 0) {
			echo json_encode(array('success' => false, 'msg' => 'Invalid domain ID'));
			return;
		}

		if (!in_array($action, array('lock', 'unlock'))) {
			echo json_encode(array('success' => false, 'msg' => 'Invalid action'));
			return;
		}

		$domainInfo = $this->Clientarea_model->getDomainRegistrarInfo($domainId, $companyId);

		if (empty($domainInfo)) {
			echo json_encode(array('success' => false, 'msg' => 'Domain not found or access denied'));
			return;
		}

		if (empty($domainInfo['dom_register_id']) || empty($domainInfo['platform'])) {
			echo json_encode(array('success' => false, 'msg' => 'No registrar configured for this domain'));
			return;
		}

		$result = $this->setTransferLockViaApi($domainInfo, $action);

		if ($result['success']) {
			$locked = ($action === 'lock') ? 1 : 0;
			$this->db->where('id', intval($domainId));
			$this->db->where('company_id', intval($companyId));
			$this->db->update('order_domains', array('transfer_lock' => $locked));
			$result['locked'] = $locked;
		}

		echo json_encode($result);
	}

	/**
	 * Get transfer lock status from registrar API
	 */
	private function getTransferLockFromApi($domainInfo) {
		$platform = strtoupper($domainInfo['platform'] ?? '');

		switch ($platform) {
			case 'RESELLERCLUB':
			case 'RESELLBIZ':
			case 'RESELL.BIZ':
			case 'STARGATE':
				return $this->getTransferLockResellerclub($domainInfo);
			case 'NAMECHEAP':
				return $this->getTransferLockNamecheap($domainInfo);
			default:
				return array('success' => false, 'msg' => 'Unsupported registrar platform: ' . $platform);
		}
	}

	/**
	 * Set transfer lock via registrar API
	 */
	private function setTransferLockViaApi($domainInfo, $action) {
		$platform = strtoupper($domainInfo['platform'] ?? '');

		switch ($platform) {
			case 'RESELLERCLUB':
			case 'RESELLBIZ':
			case 'RESELL.BIZ':
			case 'STARGATE':
				return $this->setTransferLockResellerclub($domainInfo, $action);
			case 'NAMECHEAP':
				return $this->setTransferLockNamecheap($domainInfo, $action);
			default:
				return array('success' => false, 'msg' => 'Unsupported registrar platform: ' . $platform);
		}
	}

	/**
	 * Get transfer lock from ResellerClub API
	 * Uses simple GET: /api/domains/details.json?auth-userid=X&api-key=X&order-id=X
	 * Response contains "islocked": true/false
	 */
	private function getTransferLockResellerclub($domainInfo) {
		if (empty($domainInfo['domain_order_id'])) {
			return array('success' => false, 'msg' => 'Domain order ID not found');
		}

		$baseUrl = rtrim(strval($domainInfo['api_base_url'] ?? ''), '/');
		$url = $baseUrl . '/api/domains/details.json?'
			. http_build_query(array(
				'auth-userid' => strval($domainInfo['auth_userid'] ?? ''),
				'api-key' => strval($domainInfo['auth_apikey'] ?? ''),
				'order-id' => strval($domainInfo['domain_order_id'] ?? ''),
				'options' => 'All'
			));

		$apiResponse = domain_api_get($url);

		if (!$apiResponse['success'] || empty($apiResponse['data'])) {
			$error = $apiResponse['error'] ?? 'Failed to connect to registrar API';
			log_message('error', 'Transfer lock API error: ' . $error);
			return array('success' => false, 'msg' => $error);
		}

		$result = $apiResponse['data'];

		if (isset($result['status']) && strtolower($result['status']) == 'error') {
			return array('success' => false, 'msg' => $result['message'] ?? 'API Error');
		}

		$locked = false;
		if (isset($result['islocked'])) {
			$locked = filter_var($result['islocked'], FILTER_VALIDATE_BOOLEAN);
		} else {
			log_message('error', 'Transfer lock API: islocked key not found. Keys: ' . implode(', ', array_keys($result)));
		}

		return array('success' => true, 'locked' => $locked ? 1 : 0);
	}

	/**
	 * Set transfer lock via ResellerClub API (enable/disable theft protection)
	 * Uses domain_api_post() from domain_helper for proper headers and Cloudflare retry
	 */
	private function setTransferLockResellerclub($domainInfo, $action) {
		if (empty($domainInfo['domain_order_id'])) {
			return array('success' => false, 'msg' => 'Domain order ID not found');
		}

		$baseUrl = rtrim(strval($domainInfo['api_base_url'] ?? ''), '/');
		$endpoint = ($action === 'lock') ? '/api/domains/enable-theft-protection.json' : '/api/domains/disable-theft-protection.json';

		$postData = array(
			'auth-userid' => strval($domainInfo['auth_userid'] ?? ''),
			'api-key' => strval($domainInfo['auth_apikey'] ?? ''),
			'order-id' => strval($domainInfo['domain_order_id'] ?? '')
		);

		$apiResponse = domain_api_post($baseUrl . $endpoint, $postData);

		if (!$apiResponse['success']) {
			$error = $apiResponse['error'] ?? 'Failed to connect to registrar API';
			return array('success' => false, 'msg' => $error);
		}

		$result = $apiResponse['data'];

		if (is_array($result) && isset($result['status']) && strtolower($result['status']) == 'error') {
			return array('success' => false, 'msg' => $result['message'] ?? 'API Error');
		}

		$label = ($action === 'lock') ? 'enabled' : 'disabled';
		return array('success' => true, 'msg' => 'Transfer lock ' . $label . ' successfully');
	}

	/**
	 * Get transfer lock from Namecheap API
	 */
	private function getTransferLockNamecheap($domainInfo) {
		$domain = strval($domainInfo['domain'] ?? '');
		$apiUser = strval($domainInfo['auth_userid'] ?? '');
		$apiKey = strval($domainInfo['auth_apikey'] ?? '');
		$serverIp = !empty($domainInfo['whitelisted_ip']) ? $domainInfo['whitelisted_ip'] : ($_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname()));
		$baseUrl = strval($domainInfo['api_base_url'] ?? '');

		$url = $baseUrl . '?ApiUser=' . urlencode($apiUser)
			. '&ApiKey=' . urlencode($apiKey)
			. '&UserName=' . urlencode($apiUser)
			. '&Command=namecheap.domains.getRegistrarLock'
			. '&ClientIp=' . urlencode($serverIp)
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

		if (isset($xml->CommandResponse->DomainGetRegistrarLockResult)) {
			$lockStatus = (string)$xml->CommandResponse->DomainGetRegistrarLockResult->attributes()->RegistrarLockStatus;
			$locked = (strtolower($lockStatus) === 'true') ? 1 : 0;
			return array('success' => true, 'locked' => $locked);
		}

		return array('success' => false, 'msg' => 'Could not determine lock status');
	}

	/**
	 * Set transfer lock via Namecheap API
	 */
	private function setTransferLockNamecheap($domainInfo, $action) {
		$domain = strval($domainInfo['domain'] ?? '');
		$apiUser = strval($domainInfo['auth_userid'] ?? '');
		$apiKey = strval($domainInfo['auth_apikey'] ?? '');
		$serverIp = !empty($domainInfo['whitelisted_ip']) ? $domainInfo['whitelisted_ip'] : ($_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname()));
		$baseUrl = strval($domainInfo['api_base_url'] ?? '');

		$lockAction = ($action === 'lock') ? 'LOCK' : 'UNLOCK';

		$url = $baseUrl . '?ApiUser=' . urlencode($apiUser)
			. '&ApiKey=' . urlencode($apiKey)
			. '&UserName=' . urlencode($apiUser)
			. '&Command=namecheap.domains.setRegistrarLock'
			. '&ClientIp=' . urlencode($serverIp)
			. '&DomainName=' . urlencode($domain)
			. '&LockAction=' . urlencode($lockAction);

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

		$label = ($action === 'lock') ? 'enabled' : 'disabled';
		return array('success' => true, 'msg' => 'Transfer lock ' . $label . ' successfully');
	}

	/**
	 * Get EPP code from registrar API
	 */
	private function getEppCodeFromApi($domainInfo) {
		$platform = strtoupper($domainInfo['platform'] ?? '');

		switch ($platform) {
			case 'RESELLERCLUB':
			case 'RESELLBIZ':
			case 'RESELL.BIZ':
			case 'STARGATE':
				return $this->getEppCodeResellerclub($domainInfo);
			case 'NAMECHEAP':
				return $this->getEppCodeNamecheap($domainInfo);
			default:
				return array('success' => false, 'msg' => 'Unsupported registrar platform: ' . $platform);
		}
	}

	/**
	 * Get EPP code from ResellerClub/Resell.biz/Stargate API
	 */
	private function getEppCodeResellerclub($domainInfo) {
		if (empty($domainInfo['domain_order_id'])) {
			return array('success' => false, 'msg' => 'Domain order ID not found for registrar');
		}

		$baseUrl = rtrim(strval($domainInfo['api_base_url'] ?? ''), '/');
		$authUserId = strval($domainInfo['auth_userid'] ?? '');
		$apiKey = strval($domainInfo['auth_apikey'] ?? '');
		$orderId = strval($domainInfo['domain_order_id'] ?? '');

		// ResellerClub API endpoint — options=All includes domsecret (EPP code)
		$url = $baseUrl . '/api/domains/details.json?'
			. http_build_query(array(
				'auth-userid' => $authUserId,
				'api-key' => $apiKey,
				'order-id' => $orderId,
				'options' => 'All'
			));

		$apiResponse = domain_api_get($url);

		if (!$apiResponse['success'] || empty($apiResponse['data'])) {
			$error = $apiResponse['error'] ?? 'Failed to connect to registrar API';
			log_message('error', 'EPP code API error: ' . $error);
			return array('success' => false, 'msg' => $error);
		}

		$result = $apiResponse['data'];

		if (!is_array($result)) {
			log_message('error', 'EPP code API: Invalid response data');
			return array('success' => false, 'msg' => 'Invalid response from registrar API');
		}

		if (isset($result['status']) && strtolower($result['status']) == 'error') {
			$errorMsg = isset($result['message']) ? $result['message'] : 'API Error';
			return array('success' => false, 'msg' => $errorMsg);
		}

		if (isset($result['domsecret']) && $result['domsecret'] !== '') {
			return array('success' => true, 'epp_code' => strval($result['domsecret']));
		}

		// Try alternate key names
		if (isset($result['authcode']) && $result['authcode'] !== '') {
			return array('success' => true, 'epp_code' => strval($result['authcode']));
		}

		log_message('error', 'EPP code API: domsecret not in response. Keys: ' . implode(', ', array_keys($result)));
		return array('success' => false, 'msg' => 'EPP code not found in registrar response. The registrar may not support EPP retrieval for this domain.');
	}

	/**
	 * Get EPP code from Namecheap API using namecheap.domains.getEPPCode command
	 * Note: Domain must be unlocked (Transfer Lock OFF) before EPP code can be retrieved
	 */
	private function getEppCodeNamecheap($domainInfo) {
		$domain = strval($domainInfo['domain'] ?? '');
		$apiUser = strval($domainInfo['auth_userid'] ?? '');
		$apiKey = strval($domainInfo['auth_apikey'] ?? '');
		$serverIp = !empty($domainInfo['whitelisted_ip']) ? $domainInfo['whitelisted_ip'] : ($_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname()));
		$baseUrl = strval($domainInfo['api_base_url'] ?? '');

		$url = $baseUrl . '?ApiUser=' . urlencode($apiUser)
			. '&ApiKey=' . urlencode($apiKey)
			. '&UserName=' . urlencode($apiUser)
			. '&Command=namecheap.domains.getEPPCode'
			. '&ClientIp=' . urlencode($serverIp)
			. '&DomainName=' . urlencode($domain);

		$response = $this->makeApiRequest($url, array(), 'GET');

		if ($response === false) {
			return array('success' => false, 'msg' => 'Failed to connect to Namecheap API');
		}

		$xml = @simplexml_load_string($response);
		if ($xml === false) {
			log_message('error', 'Namecheap EPP: Invalid XML response: ' . substr($response, 0, 500));
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

		if (isset($xml->CommandResponse->DomainGetEPPCodeResult->EPPCode)) {
			$eppCode = (string)$xml->CommandResponse->DomainGetEPPCodeResult->EPPCode;
			return array('success' => true, 'epp_code' => $eppCode);
		}

		log_message('error', 'Namecheap EPP: EPPCode not found in response: ' . substr($response, 0, 500));
		return array('success' => false, 'msg' => 'EPP code not found in Namecheap response. Please ensure Transfer Lock is disabled first.');
	}
}
