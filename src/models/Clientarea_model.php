<?php
class Clientarea_model extends CI_Model{

	function __construct(){
		parent::__construct();
		$this->load->database();
	}

	function loadSummaryData($id) {
		// SECURITY: Validate input to prevent SQL injection
		if (!is_numeric($id) || $id <= 0) {
			return array();
		}

		$id = intval($id);

		$sql = "SELECT COUNT(id) cnt FROM order_services WHERE company_id = ? UNION ALL
				SELECT COUNT(id) cnt FROM order_domains WHERE company_id = ? UNION ALL
				SELECT COUNT(id) cnt FROM tickets WHERE company_id = ? UNION ALL
				SELECT COUNT(id) cnt FROM invoices WHERE company_id = ?";

		$data = $this->db->query($sql, array($id, $id, $id, $id))->result_array();

		return $data;
	}

	function getServerDnsInfo($id) {
		// SECURITY: Validate input to prevent SQL injection
		if (!is_numeric($id) || $id <= 0) {
			return array();
		}

		$sql = "SELECT s.name, s.dns1, s.dns2, s.dns3, s.dns4, s.ip_addr primar_ip
			FROM product_service_pricing psp
			JOIN product_services ps ON psp.product_service_id = ps.id
			JOIN servers s ON ps.server_id = s.id
			WHERE psp.id = ? LIMIT 1";

		$data = $this->db->query($sql, array(intval($id)))->result_array();

		return $data;
	}

 	function countDbSession($user_id){
		$this->db->select('id');
		$this->db->from('user_logins');
		$this->db->where(array(
			'user_id'=>$user_id,
			'active'=>1
		));
		$num_results = $this->db->count_all_results();
		
		return $num_results;
	}

	function changePassword($userId, $currentPassword, $newPassword)
	{
		$sql = "SELECT password, first_name, email FROM users WHERE id = ? AND status = 1";
		$query = $this->db->query($sql, array($userId));

		if ($query->num_rows() == 0) {
			return ['success' => false, 'msg' => 'User not found.'];
		}

		$user = $query->row();

		if (!password_verify($currentPassword, $user->password)) {
			return ['success' => false, 'msg' => 'Current password is incorrect.'];
		}

		$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
		$sql = "UPDATE users SET password = ? WHERE id = ?";
		$this->db->query($sql, array($hashedPassword, $userId));

		return ['success' => true, 'email' => $user->email, 'first_name' => $user->first_name];
	}

	function isEmailExists($email){
		$this->db->select('id');
		$this->db->from('users');
		$this->db->where(array(
			'email'=>$email
		));
		$num_results = $this->db->count_all_results();

		return $num_results;
	}

	/**
	 * Get domain registrar info for API calls
	 */
	function getDomainRegistrarInfo($domainId, $companyId) {
		if (!is_numeric($domainId) || !is_numeric($companyId) || $domainId <= 0 || $companyId <= 0) {
			return array();
		}

		try {
			$sql = "SELECT od.*, dr.platform, dr.api_base_url, dr.auth_userid, dr.auth_apikey,
						   dr.ns_update_api, dr.contact_details_api, dr.contact_update_api
					FROM order_domains od
					LEFT JOIN dom_registers dr ON od.dom_register_id = dr.id
					WHERE od.id = ? AND od.company_id = ?";

			$data = $this->db->query($sql, array(intval($domainId), intval($companyId)))->result_array();

			return !empty($data) ? $data[0] : array();
		} catch (Exception $e) {
			log_message('error', 'getDomainRegistrarInfo error: ' . $e->getMessage());
			return array();
		}
	}

	/**
	 * Update nameservers in database
	 */
	function updateDomainNameservers($domainId, $companyId, $ns1, $ns2, $ns3, $ns4, $dnsType) {
		if (!is_numeric($domainId) || !is_numeric($companyId) || $domainId <= 0 || $companyId <= 0) {
			return array('success' => false, 'msg' => 'Invalid domain ID');
		}

		// Validate at least NS1 and NS2 are provided
		if (empty(trim($ns1)) || empty(trim($ns2))) {
			return array('success' => false, 'msg' => 'Nameserver 1 and Nameserver 2 are required');
		}

		// Validate nameserver format (basic validation)
		$nsPattern = '/^[a-zA-Z0-9][a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}$/';
		if (!preg_match($nsPattern, trim($ns1)) || !preg_match($nsPattern, trim($ns2))) {
			return array('success' => false, 'msg' => 'Invalid nameserver format');
		}

		if (!empty(trim($ns3)) && !preg_match($nsPattern, trim($ns3))) {
			return array('success' => false, 'msg' => 'Invalid nameserver 3 format');
		}

		if (!empty(trim($ns4)) && !preg_match($nsPattern, trim($ns4))) {
			return array('success' => false, 'msg' => 'Invalid nameserver 4 format');
		}

		$data = array(
			'ns1' => trim($ns1),
			'ns2' => trim($ns2),
			'ns3' => trim($ns3),
			'ns4' => trim($ns4),
			'dns_type' => $dnsType,
			'updated_on' => date('Y-m-d H:i:s')
		);

		$this->db->where('id', intval($domainId));
		$this->db->where('company_id', intval($companyId));

		if ($this->db->update('order_domains', $data)) {
			return array('success' => true, 'msg' => 'Nameservers updated in database successfully');
		}

		return array('success' => false, 'msg' => 'Failed to update nameservers in database');
	}

	/**
	 * Update domain contact information in database
	 */
	function updateDomainContacts($domainId, $companyId, $contactData) {
		if (!is_numeric($domainId) || !is_numeric($companyId) || $domainId <= 0 || $companyId <= 0) {
			return array('success' => false, 'msg' => 'Invalid domain ID');
		}

		// Validate required fields
		if (empty(trim($contactData['contact_name'] ?? '')) || empty(trim($contactData['contact_email'] ?? ''))) {
			return array('success' => false, 'msg' => 'Contact name and email are required');
		}

		// Validate email format
		if (!filter_var(trim($contactData['contact_email']), FILTER_VALIDATE_EMAIL)) {
			return array('success' => false, 'msg' => 'Invalid email format');
		}

		$data = array(
			'contact_name' => trim($contactData['contact_name'] ?? ''),
			'contact_company' => trim($contactData['contact_company'] ?? ''),
			'contact_email' => trim($contactData['contact_email'] ?? ''),
			'contact_phone' => trim($contactData['contact_phone'] ?? ''),
			'contact_address1' => trim($contactData['contact_address1'] ?? ''),
			'contact_address2' => trim($contactData['contact_address2'] ?? ''),
			'contact_city' => trim($contactData['contact_city'] ?? ''),
			'contact_state' => trim($contactData['contact_state'] ?? ''),
			'contact_zip' => trim($contactData['contact_zip'] ?? ''),
			'contact_country' => trim($contactData['contact_country'] ?? ''),
			'updated_on' => date('Y-m-d H:i:s')
		);

		$this->db->where('id', intval($domainId));
		$this->db->where('company_id', intval($companyId));

		if ($this->db->update('order_domains', $data)) {
			return array('success' => true, 'msg' => 'Contact information updated successfully');
		}

		return array('success' => false, 'msg' => 'Failed to update contact information');
	}

	/**
	 * Save synced data from registrar API
	 */
	function saveSyncedDomainData($domainId, $companyId, $syncData) {
		if (!is_numeric($domainId) || !is_numeric($companyId) || $domainId <= 0 || $companyId <= 0) {
			return array('success' => false, 'msg' => 'Invalid domain ID');
		}

		$data = array(
			'last_contact_sync' => date('Y-m-d H:i:s'),
			'updated_on' => date('Y-m-d H:i:s')
		);

		// Add nameservers if provided
		if (isset($syncData['ns1'])) $data['ns1'] = trim($syncData['ns1']);
		if (isset($syncData['ns2'])) $data['ns2'] = trim($syncData['ns2']);
		if (isset($syncData['ns3'])) $data['ns3'] = trim($syncData['ns3']);
		if (isset($syncData['ns4'])) $data['ns4'] = trim($syncData['ns4']);

		// Add contact info if provided
		if (isset($syncData['contact_name'])) $data['contact_name'] = trim($syncData['contact_name']);
		if (isset($syncData['contact_company'])) $data['contact_company'] = trim($syncData['contact_company']);
		if (isset($syncData['contact_email'])) $data['contact_email'] = trim($syncData['contact_email']);
		if (isset($syncData['contact_phone'])) $data['contact_phone'] = trim($syncData['contact_phone']);
		if (isset($syncData['contact_address1'])) $data['contact_address1'] = trim($syncData['contact_address1']);
		if (isset($syncData['contact_address2'])) $data['contact_address2'] = trim($syncData['contact_address2']);
		if (isset($syncData['contact_city'])) $data['contact_city'] = trim($syncData['contact_city']);
		if (isset($syncData['contact_state'])) $data['contact_state'] = trim($syncData['contact_state']);
		if (isset($syncData['contact_zip'])) $data['contact_zip'] = trim($syncData['contact_zip']);
		if (isset($syncData['contact_country'])) $data['contact_country'] = trim($syncData['contact_country']);

		$this->db->where('id', intval($domainId));
		$this->db->where('company_id', intval($companyId));

		if ($this->db->update('order_domains', $data)) {
			return array('success' => true, 'msg' => 'Domain data synced successfully');
		}

		return array('success' => false, 'msg' => 'Failed to sync domain data');
	}

	/**
	 * Get all active countries for dropdown
	 */
	function getCountries() {
		$sql = "SELECT country_code, country_name FROM countries WHERE status = 1 ORDER BY country_name ASC";
		return $this->db->query($sql)->result_array();
	}
}
?>
