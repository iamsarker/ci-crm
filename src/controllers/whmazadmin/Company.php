<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Company extends WHMAZADMIN_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('Company_model');
		$this->load->model('Common_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	public function index()
	{
		$data['results'] = array();
		$this->load->view('whmazadmin/company_list', $data);
	}

	public function manage($id_val = null)
	{
		if( $this->input->post() ){
			$this->form_validation->set_rules('name', 'Name', 'required|trim');
			$this->form_validation->set_message('name', 'Name is required');

			$this->form_validation->set_rules('mobile', 'mobile', 'required|trim');
			$this->form_validation->set_message('mobile', 'mobile is required');

			$this->form_validation->set_rules('email', 'email', 'required|trim');
			$this->form_validation->set_message('email', 'email is required');

			$this->form_validation->set_rules('zip_code', 'zip code', 'required|trim');
			$this->form_validation->set_message('zip_code', 'zip code is required');

			$this->form_validation->set_rules('city', 'city', 'required|trim');
			$this->form_validation->set_message('city', 'city is required');

			$this->form_validation->set_rules('state', 'state', 'required|trim');
			$this->form_validation->set_message('state', 'state is required');

			$this->form_validation->set_rules('address', 'address', 'required|trim');
			$this->form_validation->set_message('address', 'address is required');

			$this->form_validation->set_rules('country', 'country', 'required|trim');
			$this->form_validation->set_message('country', 'country is required');

			if ($this->form_validation->run() == true){

				$form_data = array(
					'id'			=> safe_decode($this->input->post('id')),
					'name'			=> $this->input->post('name'),
					'first_name'	=> $this->input->post('first_name'),
					'last_name'		=> $this->input->post('last_name'),
					'mobile'		=> $this->input->post('mobile'),
					'email'			=> $this->input->post('email'),
					'phone'			=> $this->input->post('phone'),
					'city'			=> $this->input->post('city'),
					'state'			=> $this->input->post('state'),
					'zip_code'		=> $this->input->post('zip_code'),
					'address'		=> $this->input->post('address'),
					'country'		=> $this->input->post('country'),
					'status'       	=> 1
				);

				if( strlen($form_data['id']) > 0 ){
					$oldEntity = $this->Company_model->getDetail(safe_decode($id_val));
					$form_data['updated_on'] = getDateTime();
					$form_data['updated_by'] = getAdminId();

					$form_data['inserted_on'] = $oldEntity['inserted_on'];
					$form_data['inserted_by'] = $oldEntity['inserted_by'];
				} else {
					$form_data['inserted_on'] = getDateTime();
					$form_data['inserted_by'] = getAdminId();
				}

				$resp = $this->Company_model->saveData($form_data);
				if($resp){

					if( $form_data['id'] == 0 ){
						// SECURITY FIX: Generate secure random password instead of hardcoded password
						$temp_password = generate_secure_password(12, true); // Generate 12-character random password

						$user['first_name'] = $form_data['first_name'];
						$user['last_name'] = $form_data['last_name'];
						$user['email'] = $form_data['email'];
						$user['mobile'] = $form_data['mobile'];
						$user['phone'] = $form_data['phone'];
						$user['designation'] = 'Company Owner';
						$user['password'] = password_hash($temp_password, PASSWORD_DEFAULT);
						$user['company_id'] = $resp['id'];
						$user['user_type'] = '0'; // owner
						$user['status'] = '1'; // active
						$user['login_try'] = '0';
						$user['inserted_on'] = getDateTime();
						$user['inserted_by'] = $form_data['inserted_by'];
						$this->Common_model->save("users", $user);

						// SECURITY: Store temporary password in session to display to admin
						$this->session->set_flashdata('new_user_credentials', array(
							'email' => $form_data['email'],
							'password' => $temp_password,
							'company_name' => $form_data['name']
						));
					}

					$this->session->set_flashdata('admin_success', 'Customer has been saved successfully.');
					redirect("whmazadmin/company/index");
				}else {
					$this->session->set_flashdata('admin_error', 'Something went wrong. Try again');
				}
			}

		}

		if( !empty($id_val) ){
			$data['detail'] = $this->Company_model->getDetail(safe_decode($id_val));
		} else {
			$data['detail'] = array();
		}

		$data['countries'] = $this->Common_model->generate_dropdown('countries','country_name','country_name');

		$this->load->view('whmazadmin/company_manage', $data);
	}

	public function delete_records($id_val)
	{
		$entity = $this->Company_model->getDetail(safe_decode($id_val));
		$entity["status"] = 0;
		$entity["deleted_on"] = getDateTime();
		$entity["deleted_by"] = getAdminId();

		$this->Company_model->saveData($entity);
		$this->session->set_flashdata('admin_success', 'Customer has been deleted successfully.');

		redirect('whmazadmin/company/index');
	}

	public function ssp_list_api()
	{
		$this->processRestCall();

		// Set proper JSON headers
		header('Content-Type: application/json');

		try {
			$params = $this->input->get();

			$bindings = array();
			$where = '';

			$sqlQuery = ssp_sql_query($params, "companies", $bindings, $where);

			$data = $this->Company_model->getDataTableRecords($sqlQuery, $bindings);

			$response = array(
				"draw"            => !empty( $params['draw'] ) ? intval($params['draw']) : 0,
				"recordsTotal"    => intval( $this->Company_model->countDataTableTotalRecords() ),
				"recordsFiltered" => intval( $this->Company_model->countDataTableFilterRecords($where, $bindings) ),
				"data"            => $data
			);

			echo json_encode($response);
			exit;

		} catch (Exception $e) {
			// SECURITY: Log database error
			ErrorHandler::log_database_error('ssp_list_api', 'DataTables API', $e->getMessage());

			// Return error in DataTables format
			echo json_encode(array(
				"draw"            => 0,
				"recordsTotal"    => 0,
				"recordsFiltered" => 0,
				"data"            => array(),
				"error"           => $e->getMessage()
			));
			exit;
		}
	}

	/**
	 * Server-side DataTable API for Services (order_services)
	 */
	public function ssp_services_api($tmpCompanyId = null)
	{
		$this->processRestCall();

		header('Content-Type: application/json');

		try {
			$params = $this->input->get();
			$companyId = !empty($tmpCompanyId) ? safe_decode($tmpCompanyId) : 0;

			// Inject company_id filter
			if ($companyId > 0) {
				for ($i = 0; $i < count($params["columns"]); $i++) {
					if ($params["columns"][$i]['data'] == "company_id") {
						$params["columns"][$i]["search"]["value"] = intval($companyId);
						break;
					}
				}
			}

			$bindings = array();
			$where = '';

			$sqlQuery = ssp_sql_query($params, "order_services", $bindings, $where);

			$data = $this->Company_model->getServicesDataTableRecords($sqlQuery, $bindings);

			$response = array(
				"draw"            => !empty($params['draw']) ? intval($params['draw']) : 0,
				"recordsTotal"    => intval($this->Company_model->countServicesDataTableTotalRecords($companyId)),
				"recordsFiltered" => intval($this->Company_model->countServicesDataTableFilterRecords($where, $bindings)),
				"data"            => $data
			);

			echo json_encode($response);
			exit;

		} catch (Exception $e) {
			ErrorHandler::log_database_error('ssp_services_api', 'DataTables API', $e->getMessage());

			echo json_encode(array(
				"draw"            => 0,
				"recordsTotal"    => 0,
				"recordsFiltered" => 0,
				"data"            => array(),
				"error"           => $e->getMessage()
			));
			exit;
		}
	}

	/**
	 * Server-side DataTable API for Domains (order_domains)
	 */
	public function ssp_domains_api($tmpCompanyId = null)
	{
		$this->processRestCall();

		header('Content-Type: application/json');

		try {
			$params = $this->input->get();
			$companyId = !empty($tmpCompanyId) ? safe_decode($tmpCompanyId) : 0;

			// Inject company_id filter
			if ($companyId > 0) {
				for ($i = 0; $i < count($params["columns"]); $i++) {
					if ($params["columns"][$i]['data'] == "company_id") {
						$params["columns"][$i]["search"]["value"] = $companyId;
						break;
					}
				}
			}

			$bindings = array();
			$where = '';

			$sqlQuery = ssp_sql_query($params, "order_domains", $bindings, $where);

			$data = $this->Company_model->getDomainsDataTableRecords($sqlQuery, $bindings);

			$response = array(
				"draw"            => !empty($params['draw']) ? intval($params['draw']) : 0,
				"recordsTotal"    => intval($this->Company_model->countDomainsDataTableTotalRecords($companyId)),
				"recordsFiltered" => intval($this->Company_model->countDomainsDataTableFilterRecords($where, $bindings)),
				"data"            => $data
			);

			echo json_encode($response);
			exit;

		} catch (Exception $e) {
			ErrorHandler::log_database_error('ssp_domains_api', 'DataTables API', $e->getMessage());

			echo json_encode(array(
				"draw"            => 0,
				"recordsTotal"    => 0,
				"recordsFiltered" => 0,
				"data"            => array(),
				"error"           => $e->getMessage()
			));
			exit;
		}
	}

	/**
	 * Get service detail for management modal
	 * @param int $serviceId Service ID
	 * @param int $companyId Company ID for security validation
	 */
	public function get_service_detail($serviceId = null, $companyId = null)
	{
		$this->processRestCall();
		header('Content-Type: application/json');

		if (empty($serviceId) || !is_numeric($serviceId) || empty($companyId) || !is_numeric($companyId)) {
			echo json_encode(array('success' => false, 'message' => 'Invalid parameters'));
			exit;
		}

		try {
			$service = $this->Company_model->getServiceDetail($serviceId, $companyId);

			if (empty($service)) {
				echo json_encode(array('success' => false, 'message' => 'Service not found'));
				exit;
			}

			echo json_encode(array('success' => true, 'data' => $service));
		} catch (Exception $e) {
			ErrorHandler::log_database_error('get_service_detail', 'Service detail fetch', $e->getMessage());
			echo json_encode(array('success' => false, 'message' => 'Failed to load service details'));
		}
		exit;
	}

	/**
	 * Update service details (cp_username, status)
	 * @param int $serviceId Service ID
	 */
	public function update_service($serviceId = null)
	{
		$this->processRestCall();
		header('Content-Type: application/json');

		if (empty($serviceId) || !is_numeric($serviceId)) {
			echo json_encode(array('success' => false, 'message' => 'Invalid service ID'));
			exit;
		}

		$cpUsername = $this->input->post('cp_username');
		$status = $this->input->post('status');

		// Validate cPanel username format if provided
		if (!empty($cpUsername) && !preg_match('/^[a-z][a-z0-9]{0,7}$/', $cpUsername)) {
			echo json_encode(array('success' => false, 'message' => 'Invalid cPanel username format'));
			exit;
		}

		// Validate status
		$validStatuses = array(0, 1, 2, 3, 4);
		if (!in_array((int)$status, $validStatuses)) {
			echo json_encode(array('success' => false, 'message' => 'Invalid status'));
			exit;
		}

		try {
			$updateData = array(
				'cp_username' => $cpUsername,
				'status' => (int)$status,
				'updated_on' => getDateTime(),
				'updated_by' => getAdminId()
			);

			$result = $this->Company_model->updateService($serviceId, $updateData);

			if ($result) {
				echo json_encode(array('success' => true, 'message' => 'Service updated successfully'));
			} else {
				echo json_encode(array('success' => false, 'message' => 'Failed to update service'));
			}
		} catch (Exception $e) {
			ErrorHandler::log_database_error('update_service', 'Service update', $e->getMessage());
			echo json_encode(array('success' => false, 'message' => 'Error updating service'));
		}
		exit;
	}

	/**
	 * Create cPanel account for a service
	 * @param int $serviceId Service ID
	 */
	public function create_cpanel_account($serviceId = null)
	{
		$this->processRestCall();
		header('Content-Type: application/json');

		if (empty($serviceId) || !is_numeric($serviceId)) {
			echo json_encode(array('success' => false, 'message' => 'Invalid service ID'));
			exit;
		}

		$cpUsername = $this->input->post('cp_username');

		if (empty($cpUsername)) {
			echo json_encode(array('success' => false, 'message' => 'cPanel username is required'));
			exit;
		}

		// Validate cPanel username format
		if (!preg_match('/^[a-z][a-z0-9]{0,7}$/', $cpUsername)) {
			echo json_encode(array('success' => false, 'message' => 'Invalid cPanel username format'));
			exit;
		}

		try {
			// Get service details
			$service = $this->Company_model->getServiceDetailForCpanel($serviceId);

			if (empty($service)) {
				echo json_encode(array('success' => false, 'message' => 'Service not found'));
				exit;
			}

			// Check if hosting domain is set
			if (empty($service['hosting_domain'])) {
				echo json_encode(array('success' => false, 'message' => 'Hosting domain is not configured for this service'));
				exit;
			}

			// Get server info
			$serverInfo = $this->Common_model->getServerInfoByOrderServiceId($serviceId, $service['company_id']);

			if (empty($serverInfo)) {
				echo json_encode(array('success' => false, 'message' => 'Server information not found for this service'));
				exit;
			}

			// Get customer info for email
			$company = $this->Company_model->getDetail($service['company_id']);

			// Generate secure password
			$password = generate_secure_password(16, true);

			// Get cPanel package name
			$cpPackage = !empty($service['cp_package']) ? $service['cp_package'] : 'default';

			// Create cPanel account via WHM API
			$result = whm_create_account(
				$serverInfo,
				$service['hosting_domain'],
				$cpUsername,
				$password,
				$cpPackage,
				$company['email']
			);

			if ($result['success']) {
				// Update service with cp_username and mark as synced
				$updateData = array(
					'cp_username' => $cpUsername,
					'is_synced' => 1,
					'status' => 1, // Active
					'updated_on' => getDateTime(),
					'updated_by' => getAdminId()
				);
				$this->Company_model->updateService($serviceId, $updateData);

				// Send welcome email to customer
				$customerName = trim($company['first_name'] . ' ' . $company['last_name']);
				if (empty($customerName)) {
					$customerName = $company['name'];
				}

				send_cpanel_welcome_email(
					$company['email'],
					$customerName,
					$service['hosting_domain'],
					$cpUsername,
					$password,
					$serverInfo['hostname']
				);

				echo json_encode(array(
					'success' => true,
					'message' => 'cPanel account created successfully. Welcome email sent to customer.'
				));
			} else {
				echo json_encode(array(
					'success' => false,
					'message' => 'Failed to create cPanel account: ' . $result['error']
				));
			}
		} catch (Exception $e) {
			ErrorHandler::log_database_error('create_cpanel_account', 'cPanel account creation', $e->getMessage());
			echo json_encode(array('success' => false, 'message' => 'Error creating cPanel account'));
		}
		exit;
	}

	/**
	 * Sync cPanel account usage stats from WHM server
	 * @param int $serviceId Service ID
	 */
	public function sync_cpanel_account($serviceId = null)
	{
		$this->processRestCall();
		header('Content-Type: application/json');

		if (empty($serviceId) || !is_numeric($serviceId)) {
			echo json_encode(array('success' => false, 'message' => 'Invalid service ID'));
			exit;
		}

		try {
			// Get service details
			$service = $this->Company_model->getServiceDetailForCpanel($serviceId);

			if (empty($service)) {
				echo json_encode(array('success' => false, 'message' => 'Service not found'));
				exit;
			}

			if (empty($service['cp_username'])) {
				echo json_encode(array('success' => false, 'message' => 'No cPanel username configured'));
				exit;
			}

			// Get server info
			$serverInfo = $this->Common_model->getServerInfoByOrderServiceId($serviceId, $service['company_id']);

			if (empty($serverInfo)) {
				echo json_encode(array('success' => false, 'message' => 'Server information not found'));
				exit;
			}

			// Load cPanel helper
			$this->load->helper('cpanel');

			// Get usage stats from cPanel (like client portal)
			$statsResult = whm_get_account_stats($serverInfo, $service['cp_username']);

			if ($statsResult['success']) {
				// Update sync status
				$updateData = array(
					'is_synced' => 1,
					'updated_on' => getDateTime(),
					'updated_by' => getAdminId()
				);
				$this->Company_model->updateService($serviceId, $updateData);

				// Save stats to database if model method exists
				if (method_exists($this->Company_model, 'saveCpanelUsageStats')) {
					$this->Company_model->saveCpanelUsageStats($serviceId, $service['company_id'], $statsResult['stats']);
				}

				echo json_encode(array(
					'success' => true,
					'message' => 'cPanel usage stats synced successfully',
					'stats' => $statsResult['stats']
				));
			} else {
				// Mark as not synced if account not found
				$updateData = array(
					'is_synced' => 0,
					'updated_on' => getDateTime(),
					'updated_by' => getAdminId()
				);
				$this->Company_model->updateService($serviceId, $updateData);

				echo json_encode(array(
					'success' => false,
					'message' => 'Failed to fetch cPanel stats: ' . ($statsResult['error'] ?? 'Unknown error')
				));
			}
		} catch (Exception $e) {
			ErrorHandler::log_database_error('sync_cpanel_account', 'cPanel sync', $e->getMessage());
			echo json_encode(array('success' => false, 'message' => 'Error syncing cPanel account'));
		}
		exit;
	}

	/**
	 * Suspend cPanel account
	 * @param int $serviceId Service ID
	 */
	public function suspend_cpanel_account($serviceId = null)
	{
		$this->processRestCall();
		header('Content-Type: application/json');

		if (empty($serviceId) || !is_numeric($serviceId)) {
			echo json_encode(array('success' => false, 'message' => 'Invalid service ID'));
			exit;
		}

		try {
			$service = $this->Company_model->getServiceDetailForCpanel($serviceId);

			if (empty($service) || empty($service['cp_username'])) {
				echo json_encode(array('success' => false, 'message' => 'Service or cPanel username not found'));
				exit;
			}

			$serverInfo = $this->Common_model->getServerInfoByOrderServiceId($serviceId, $service['company_id']);

			if (empty($serverInfo)) {
				echo json_encode(array('success' => false, 'message' => 'Server information not found'));
				exit;
			}

			$result = whm_suspend_account($serverInfo, $service['cp_username'], 'Suspended by administrator');

			if ($result['success']) {
				// Update service status to suspended (3)
				$updateData = array(
					'status' => 3,
					'updated_on' => getDateTime(),
					'updated_by' => getAdminId()
				);
				$this->Company_model->updateService($serviceId, $updateData);

				echo json_encode(array('success' => true, 'message' => 'cPanel account suspended successfully'));
			} else {
				echo json_encode(array('success' => false, 'message' => 'Failed to suspend: ' . $result['error']));
			}
		} catch (Exception $e) {
			ErrorHandler::log_database_error('suspend_cpanel_account', 'cPanel suspend', $e->getMessage());
			echo json_encode(array('success' => false, 'message' => 'Error suspending cPanel account'));
		}
		exit;
	}

	/**
	 * Unsuspend cPanel account
	 * @param int $serviceId Service ID
	 */
	public function unsuspend_cpanel_account($serviceId = null)
	{
		$this->processRestCall();
		header('Content-Type: application/json');

		if (empty($serviceId) || !is_numeric($serviceId)) {
			echo json_encode(array('success' => false, 'message' => 'Invalid service ID'));
			exit;
		}

		try {
			$service = $this->Company_model->getServiceDetailForCpanel($serviceId);

			if (empty($service) || empty($service['cp_username'])) {
				echo json_encode(array('success' => false, 'message' => 'Service or cPanel username not found'));
				exit;
			}

			$serverInfo = $this->Common_model->getServerInfoByOrderServiceId($serviceId, $service['company_id']);

			if (empty($serverInfo)) {
				echo json_encode(array('success' => false, 'message' => 'Server information not found'));
				exit;
			}

			$result = whm_unsuspend_account($serverInfo, $service['cp_username']);

			if ($result['success']) {
				// Update service status to active (1)
				$updateData = array(
					'status' => 1,
					'updated_on' => getDateTime(),
					'updated_by' => getAdminId()
				);
				$this->Company_model->updateService($serviceId, $updateData);

				echo json_encode(array('success' => true, 'message' => 'cPanel account unsuspended successfully'));
			} else {
				echo json_encode(array('success' => false, 'message' => 'Failed to unsuspend: ' . $result['error']));
			}
		} catch (Exception $e) {
			ErrorHandler::log_database_error('unsuspend_cpanel_account', 'cPanel unsuspend', $e->getMessage());
			echo json_encode(array('success' => false, 'message' => 'Error unsuspending cPanel account'));
		}
		exit;
	}

	/**
	 * Terminate cPanel account
	 * @param int $serviceId Service ID
	 */
	public function terminate_cpanel_account($serviceId = null)
	{
		$this->processRestCall();
		header('Content-Type: application/json');

		if (empty($serviceId) || !is_numeric($serviceId)) {
			echo json_encode(array('success' => false, 'message' => 'Invalid service ID'));
			exit;
		}

		try {
			$service = $this->Company_model->getServiceDetailForCpanel($serviceId);

			if (empty($service) || empty($service['cp_username'])) {
				echo json_encode(array('success' => false, 'message' => 'Service or cPanel username not found'));
				exit;
			}

			$serverInfo = $this->Common_model->getServerInfoByOrderServiceId($serviceId, $service['company_id']);

			if (empty($serverInfo)) {
				echo json_encode(array('success' => false, 'message' => 'Server information not found'));
				exit;
			}

			$result = whm_terminate_account($serverInfo, $service['cp_username']);

			if ($result['success']) {
				// Update service status to terminated (4) and clear cp_username
				$updateData = array(
					'status' => 4,
					'cp_username' => null,
					'is_synced' => 0,
					'updated_on' => getDateTime(),
					'updated_by' => getAdminId()
				);
				$this->Company_model->updateService($serviceId, $updateData);

				echo json_encode(array('success' => true, 'message' => 'cPanel account terminated successfully'));
			} else {
				echo json_encode(array('success' => false, 'message' => 'Failed to terminate: ' . $result['error']));
			}
		} catch (Exception $e) {
			ErrorHandler::log_database_error('terminate_cpanel_account', 'cPanel terminate', $e->getMessage());
			echo json_encode(array('success' => false, 'message' => 'Error terminating cPanel account'));
		}
		exit;
	}

}
