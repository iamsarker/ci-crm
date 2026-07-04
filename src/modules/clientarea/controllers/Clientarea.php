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
		$this->load->model('Subscription_model');
		$this->load->model('Software_model');
		$data['subscription']    = $this->Subscription_model->get_active_subscription_for_company(getCompanyId());
		$data['current_release'] = $this->Software_model->getCurrentRelease();
		$this->load->view('clientarea_index', $data);
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
		$data['child_ns'] = !empty($data['detail']['id'])
			? $this->Clientarea_model->getChildNameservers($data['detail']['id'], getCompanyId())
			: array();

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

			// "Default NS": ignore any posted values and apply the registrar's
			// configured default nameservers (dom_registers.def_ns1..4).
			if ($dnsType === 'default_ns') {
				$ns1 = strval($domainInfo['def_ns1'] ?? '');
				$ns2 = strval($domainInfo['def_ns2'] ?? '');
				$ns3 = strval($domainInfo['def_ns3'] ?? '');
				$ns4 = strval($domainInfo['def_ns4'] ?? '');

				if (empty($ns1) || empty($ns2)) {
					echo json_encode(array('success' => false, 'msg' => 'Default nameservers are not configured for this registrar. Please contact support or choose Custom NS.'));
					return;
				}
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

		// Build nameserver list
		$nsList = array(strval($ns1), strval($ns2));
		if (!empty(trim(strval($ns3)))) $nsList[] = strval($ns3);
		if (!empty(trim(strval($ns4)))) $nsList[] = strval($ns4);

		// ResellerClub expects the "ns" parameter REPEATED (ns=a&ns=b), not
		// bracket-indexed (ns[0]=a&ns[1]=b) as http_build_query() would encode an
		// array. Build the raw query manually — same approach as the working
		// registration flow in domain_helper.php.
		$postData = http_build_query(array(
			'auth-userid' => $authUserId,
			'api-key' => $apiKey,
			'order-id' => $orderId,
		));
		foreach ($nsList as $ns) {
			$postData .= '&ns=' . urlencode($ns);
		}

		$apiResponse = domain_api_post_raw($nsUpdateApi, $postData);

		if (!$apiResponse['success'] && empty($apiResponse['data'])) {
			return array('success' => false, 'msg' => $apiResponse['error'] ?? 'Failed to connect to registrar API');
		}

		$result = $apiResponse['data'];

		if (is_array($result) && isset($result['status']) && strtolower($result['status']) == 'success') {
			return array('success' => true, 'msg' => 'Nameservers updated at registrar');
		}

		$errorMsg = (is_array($result) && isset($result['message'])) ? $result['message'] : 'Unknown API error';
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
		$baseUrl = rtrim(strval($domainInfo['api_base_url'] ?? ''), '/');
		$authUserId = strval($domainInfo['auth_userid'] ?? '');
		$apiKey = strval($domainInfo['auth_apikey'] ?? '');
		$domain = strval($domainInfo['domain'] ?? '');

		if (empty($baseUrl) || empty($authUserId) || empty($apiKey) || empty($domain)) {
			return array('success' => false, 'msg' => 'Registrar API not configured');
		}

		// Step 1: The registrant contact-id is not stored locally — look it up
		// (plus its current phone country code) from the live domain details.
		$detailsUrl = $baseUrl . '/api/domains/details-by-name.json?'
			. http_build_query(array(
				'auth-userid' => $authUserId,
				'api-key' => $apiKey,
				'domain-name' => $domain,
				'options' => 'All'
			));

		$detailsResp = domain_api_get($detailsUrl);
		if (!$detailsResp['success'] || empty($detailsResp['data']) || !is_array($detailsResp['data'])) {
			return array('success' => false, 'msg' => $detailsResp['error'] ?? 'Failed to fetch domain details from registrar');
		}
		$details = $detailsResp['data'];

		if (isset($details['status']) && strtolower($details['status']) == 'error') {
			return array('success' => false, 'msg' => $details['message'] ?? 'API Error');
		}

		// Registrant contact id can appear under a few shapes across API versions
		$contactId = $details['registrantcontactid'] ?? ($details['registrantcontact']['contactid'] ?? null);
		if (is_array($contactId)) {
			$contactId = $contactId['contactid'] ?? ($contactId[0] ?? null);
		}
		if (empty($contactId) || is_array($contactId)) {
			return array('success' => false, 'msg' => 'Could not determine registrant contact at registrar');
		}

		// Keep the existing phone country code — the single combined phone field
		// in our form can't be reliably split back into cc + number.
		$existingPhoneCc = strval($details['registrantcontact']['telnocc'] ?? '');
		$phoneDigits = preg_replace('/[^0-9]/', '', strval($contactData['contact_phone'] ?? ''));
		$phoneCc = $existingPhoneCc !== '' ? $existingPhoneCc : '1';
		if ($existingPhoneCc !== '' && strpos($phoneDigits, $existingPhoneCc) === 0) {
			$phoneDigits = substr($phoneDigits, strlen($existingPhoneCc));
		}
		if ($phoneDigits === '') $phoneDigits = '0000000000';

		// Step 2: Modify the contact record with the submitted WHOIS values.
		$modifyUrl = !empty($domainInfo['contact_update_api'])
			? strval($domainInfo['contact_update_api'])
			: $baseUrl . '/api/contacts/modify.json';

		$postData = array(
			'auth-userid' => $authUserId,
			'api-key' => $apiKey,
			'contact-id' => strval($contactId),
			'name' => !empty($contactData['contact_name']) ? $contactData['contact_name'] : 'N/A',
			'company' => !empty($contactData['contact_company']) ? $contactData['contact_company'] : 'N/A',
			'email' => strval($contactData['contact_email'] ?? ''),
			'address-line-1' => !empty($contactData['contact_address1']) ? $contactData['contact_address1'] : 'N/A',
			'city' => !empty($contactData['contact_city']) ? $contactData['contact_city'] : 'N/A',
			'country' => !empty($contactData['contact_country']) ? $contactData['contact_country'] : 'US',
			'zipcode' => !empty($contactData['contact_zip']) ? $contactData['contact_zip'] : '00000',
			'phone-cc' => $phoneCc,
			'phone' => $phoneDigits,
		);
		if (!empty($contactData['contact_address2'])) $postData['address-line-2'] = $contactData['contact_address2'];
		if (!empty($contactData['contact_state']))    $postData['state'] = $contactData['contact_state'];

		$apiResponse = domain_api_post($modifyUrl, $postData);

		if (!$apiResponse['success'] && empty($apiResponse['data'])) {
			return array('success' => false, 'msg' => $apiResponse['error'] ?? 'Failed to connect to registrar API');
		}

		$result = $apiResponse['data'];

		// contacts/modify.json returns the contact-id on success, else a status/error map
		if (is_numeric($result)) {
			return array('success' => true, 'msg' => 'Contacts updated at registrar');
		}
		if (is_array($result)) {
			if (isset($result['status']) && strtolower($result['status']) == 'success') {
				return array('success' => true, 'msg' => 'Contacts updated at registrar');
			}
			if (isset($result['message'])) {
				return array('success' => false, 'msg' => $result['message']);
			}
		}

		return array('success' => false, 'msg' => 'Registrar did not confirm the contact update');
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
	 * Add a child (private) nameserver: register at the registrar, then store locally.
	 */
	public function child_ns_add() {
		$this->sendCsrfHeaders();
		header('Content-Type: application/json');

		if (!$this->input->post()) {
			echo json_encode(array('success' => false, 'msg' => 'Invalid request method'));
			return;
		}

		$domainId = $this->input->post('domain_id');
		$hostname = strtolower(trim(strval($this->input->post('hostname'))));
		$ip = trim(strval($this->input->post('ip')));
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

		$domain = strval($domainInfo['domain'] ?? '');

		// Validate hostname: valid FQDN and a subdomain of this domain
		if (strpos($hostname, '.') === false || !preg_match('/^[a-z0-9]([a-z0-9\-\.]*[a-z0-9])?$/', $hostname)) {
			echo json_encode(array('success' => false, 'msg' => 'Enter a valid nameserver host (e.g. ns1.' . $domain . ')'));
			return;
		}
		if ($domain !== '' && substr($hostname, -strlen('.' . $domain)) !== '.' . $domain) {
			echo json_encode(array('success' => false, 'msg' => 'The child nameserver must be a subdomain of ' . $domain . ' (e.g. ns1.' . $domain . ')'));
			return;
		}

		// Validate IP (v4 or v6)
		if (!filter_var($ip, FILTER_VALIDATE_IP)) {
			echo json_encode(array('success' => false, 'msg' => 'Enter a valid IP address'));
			return;
		}

		if ($this->Clientarea_model->childNameserverExists($domainId, $companyId, $hostname)) {
			echo json_encode(array('success' => false, 'msg' => 'That nameserver host already exists'));
			return;
		}

		if (empty($domainInfo['dom_register_id']) || empty($domainInfo['platform'])) {
			echo json_encode(array('success' => false, 'msg' => 'No registrar configured for this domain. Please contact support.'));
			return;
		}

		// Register at the registrar
		$registrar = $this->_childNsRegistrarConfig($domainInfo);
		$apiResult = registrar_register_child_ns($registrar, $domainInfo['domain_order_id'] ?? '', $domain, $hostname, $ip);

		if (empty($apiResult['success'])) {
			echo json_encode(array('success' => false, 'msg' => 'Registrar error: ' . ($apiResult['error'] ?? 'Failed to add child nameserver')));
			return;
		}

		$this->Clientarea_model->addChildNameserver($domainId, $companyId, $hostname, $ip, getCustomerId());

		echo json_encode(array('success' => true, 'msg' => 'Child nameserver added successfully'));
	}

	/**
	 * Delete a child (private) nameserver: remove at the registrar, then locally.
	 */
	public function child_ns_delete() {
		$this->sendCsrfHeaders();
		header('Content-Type: application/json');

		if (!$this->input->post()) {
			echo json_encode(array('success' => false, 'msg' => 'Invalid request method'));
			return;
		}

		$domainId = $this->input->post('domain_id');
		$childId = $this->input->post('child_ns_id');
		$companyId = getCompanyId();

		if (!is_numeric($domainId) || $domainId <= 0 || !is_numeric($childId) || $childId <= 0) {
			echo json_encode(array('success' => false, 'msg' => 'Invalid request'));
			return;
		}

		$domainInfo = $this->Clientarea_model->getDomainRegistrarInfo($domainId, $companyId);
		if (empty($domainInfo)) {
			echo json_encode(array('success' => false, 'msg' => 'Domain not found or access denied'));
			return;
		}

		$child = $this->Clientarea_model->getChildNameserverById($childId, $domainId, $companyId);
		if (empty($child)) {
			echo json_encode(array('success' => false, 'msg' => 'Child nameserver not found'));
			return;
		}

		if (empty($domainInfo['dom_register_id']) || empty($domainInfo['platform'])) {
			echo json_encode(array('success' => false, 'msg' => 'No registrar configured for this domain. Please contact support.'));
			return;
		}

		$registrar = $this->_childNsRegistrarConfig($domainInfo);
		$apiResult = registrar_delete_child_ns($registrar, $domainInfo['domain_order_id'] ?? '', strval($domainInfo['domain'] ?? ''), $child['hostname'], $child['ip']);

		if (empty($apiResult['success'])) {
			echo json_encode(array('success' => false, 'msg' => 'Registrar error: ' . ($apiResult['error'] ?? 'Failed to delete child nameserver')));
			return;
		}

		$this->Clientarea_model->deleteChildNameserver($childId, $domainId, $companyId, getCustomerId());

		echo json_encode(array('success' => true, 'msg' => 'Child nameserver deleted successfully'));
	}

	/**
	 * Build a dom_registers-shaped config array from the joined domain info
	 * for the domain_helper registrar_* functions.
	 */
	private function _childNsRegistrarConfig($domainInfo) {
		return array(
			'platform'       => $domainInfo['platform'] ?? '',
			'api_base_url'   => $domainInfo['api_base_url'] ?? '',
			'auth_userid'    => $domainInfo['auth_userid'] ?? '',
			'auth_apikey'    => $domainInfo['auth_apikey'] ?? '',
			'whitelisted_ip' => $domainInfo['whitelisted_ip'] ?? '',
		);
	}

	/**
	 * Submit a domain cancellation request.
	 * Does NOT cancel the domain — it records the request, notifies the admin,
	 * and emails a confirmation to the customer. Admin processes it in the portal.
	 */
	public function domain_cancellation_request() {
		$this->sendCsrfHeaders();
		header('Content-Type: application/json');

		if (!$this->input->post()) {
			echo json_encode(array('success' => false, 'msg' => 'Invalid request method'));
			return;
		}

		$domainId = $this->input->post('domain_id');
		$reason = trim(strval($this->input->post('reason')));
		$companyId = getCompanyId();

		if (!is_numeric($domainId) || $domainId <= 0) {
			echo json_encode(array('success' => false, 'msg' => 'Invalid domain ID'));
			return;
		}

		// Verify ownership + load the domain
		$domain = $this->Order_model->loadOrderDomainById($companyId, $domainId);
		if (empty($domain)) {
			echo json_encode(array('success' => false, 'msg' => 'Domain not found or access denied'));
			return;
		}

		$domainName = strval($domain['domain'] ?? '');

		// Block duplicate pending requests for the same domain
		$existing = $this->db->query(
			"SELECT id FROM domain_cancellation_requests WHERE domain_id = ? AND company_id = ? AND status = 0 LIMIT 1",
			array(intval($domainId), intval($companyId))
		)->row_array();

		if (!empty($existing)) {
			echo json_encode(array('success' => false, 'msg' => 'A cancellation request for this domain is already pending review.'));
			return;
		}

		// Record the request for admin processing
		$this->db->insert('domain_cancellation_requests', array(
			'domain_id'    => intval($domainId),
			'order_id'     => intval($domain['order_id'] ?? 0),
			'company_id'   => intval($companyId),
			'customer_id'  => intval(getCustomerId()),
			'domain'       => $domainName,
			'reason'       => $reason,
			'status'       => 0,
			'requested_on' => date('Y-m-d H:i:s'),
		));

		$appSettings = getAppSettings();
		$customer = $this->session->userdata('CUSTOMER');
		$customerEmail = !empty($customer['email']) ? $customer['email'] : '';
		$customerName  = !empty($customer['first_name']) ? $customer['first_name'] : 'Customer';

		// Notify admin
		if (!empty($appSettings->email)) {
			$adminSubject = 'Domain Cancellation Request: ' . $domainName;
			$adminBody  = 'A customer has requested cancellation of a domain.<br><br>';
			$adminBody .= '<strong>Domain:</strong> ' . htmlspecialchars($domainName) . '<br>';
			$adminBody .= '<strong>Customer:</strong> ' . htmlspecialchars($customerName) . ' (' . htmlspecialchars($customerEmail) . ')<br>';
			$adminBody .= '<strong>Reason:</strong> ' . htmlspecialchars($reason !== '' ? $reason : 'Not provided') . '<br><br>';
			$adminBody .= 'Please review and process this request in the admin portal.';
			sendHtmlEmail($appSettings->email, $adminSubject, $adminBody);
		}

		// Confirm to customer
		if (!empty($customerEmail)) {
			$custSubject = 'Cancellation Request Received - ' . $domainName;
			$custBody  = 'Dear ' . htmlspecialchars($customerName) . ',<br><br>';
			$custBody .= 'We have received your request to cancel the domain <strong>' . htmlspecialchars($domainName) . '</strong>.<br><br>';
			$custBody .= 'Our team will review your request and contact you shortly. The domain remains active until the request is processed.<br><br>';
			$custBody .= 'Thanks &amp; Regards,<br>' . htmlspecialchars($appSettings->company_name) . ' Support';
			sendHtmlEmail($customerEmail, $custSubject, $custBody);
		}

		echo json_encode(array('success' => true, 'msg' => 'Your cancellation request has been submitted. Our team will contact you shortly.'));
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

	// ─── Reseller API Keys (self-service) ────────────────────
	// Only reseller companies (companies.is_reseller=1) may manage keys. Every
	// operation is scoped to the caller's own company; resellers cannot pick
	// scopes — keys are granted the full reseller scope set.

	/** Guard: reseller-only. Redirects non-resellers away. Loads Apikey_model. */
	private function _requireReseller() {
		if (!isReseller()) {
			$this->session->set_flashdata('alert_error', 'API access is available to reseller accounts only.');
			redirect('/clientarea', 'refresh');
			exit;
		}
		$this->load->model('Apikey_model');
	}

	/** GET: list the reseller's API keys + create form. */
	public function apikeys() {
		$this->_requireReseller();
		$data['keys']      = $this->Apikey_model->listByCompany(getCompanyId());
		$data['stats']     = $this->Apikey_model->getCompanyStats(getCompanyId());
		$data['allow_api'] = $this->Apikey_model->isApiAllowed(getCompanyId());
		$this->load->view('clientarea_apikeys', $data);
	}

	/** POST: create a new key (full reseller scope; no scope picker). */
	public function apikey_create() {
		$this->_requireReseller();
		if (!$this->input->post()) { redirect('/clientarea/apikeys', 'refresh'); return; }

		// Creation is disabled when the admin has turned off API access.
		if (!$this->Apikey_model->isApiAllowed(getCompanyId())) {
			$this->session->set_flashdata('alert_error', 'API access is disabled for your account. Please contact support.');
			redirect('/clientarea/apikeys', 'refresh');
			return;
		}

		$name = trim($this->input->post('name'));
		if ($name === '') {
			$this->session->set_flashdata('alert_error', 'Please enter a name for the key.');
			redirect('/clientarea/apikeys', 'refresh');
			return;
		}
		$ipWhitelist = trim($this->input->post('ip_whitelist'));
		$expiresAt   = $this->input->post('expires_at') ?: null;

		$cred = $this->Apikey_model->createForCompany(getCompanyId(), $name, $ipWhitelist, $expiresAt, getCustomerId());
		if (!empty($cred)) {
			$this->session->set_flashdata('new_api_credential', array(
				'key_id' => $cred['key_id'], 'secret' => $cred['secret'], 'name' => $name,
			));
			$this->session->set_flashdata('alert_success', 'API key created. Copy the secret now — it is shown only once.');
		} else {
			$this->session->set_flashdata('alert_error', 'Failed to create API key. Please try again.');
		}
		redirect('/clientarea/apikeys', 'refresh');
	}

	/** Rotate a key's secret (shown once). */
	public function apikey_regenerate($encId = '') {
		$this->_requireReseller();
		$id  = safe_decode($encId);
		$key = $this->Apikey_model->getForCompany($id, getCompanyId());
		if (!empty($key)) {
			$secret = $this->Apikey_model->regenerateSecretForCompany($id, getCompanyId(), getCustomerId());
			if ($secret) {
				$this->session->set_flashdata('new_api_credential', array(
					'key_id' => $key['key_id'], 'secret' => $secret, 'name' => $key['name'],
				));
				$this->session->set_flashdata('alert_success', 'Secret regenerated. Copy it now — it is shown only once.');
			}
		} else {
			$this->session->set_flashdata('alert_error', 'API key not found.');
		}
		redirect('/clientarea/apikeys', 'refresh');
	}

	public function apikey_revoke($encId = '')   { $this->_apikeySetStatus($encId, 2, 'API key revoked.'); }
	public function apikey_activate($encId = '') { $this->_apikeySetStatus($encId, 1, 'API key re-activated.'); }
	public function apikey_delete($encId = '')   { $this->_apikeySetStatus($encId, 0, 'API key deleted.'); }

	private function _apikeySetStatus($encId, $status, $msg) {
		$this->_requireReseller();
		$ok = $this->Apikey_model->setStatusForCompany(safe_decode($encId), getCompanyId(), $status, getCustomerId());
		$this->session->set_flashdata($ok ? 'alert_success' : 'alert_error', $ok ? $msg : 'API key not found.');
		redirect('/clientarea/apikeys', 'refresh');
	}
}
