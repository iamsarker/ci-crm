<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Service_product extends WHMAZADMIN_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('Serviceproduct_model');
		$this->load->model('Servicegroup_model');
		$this->load->model('Server_model');
		$this->load->model('Common_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	public function index()
	{
		$data['results'] = array();
		$this->load->view('whmazadmin/service_product_list', $data);
	}

	public function ssp_list_api()
	{
		$this->processRestCall();

		header('Content-Type: application/json');

		try {
			$params = $this->input->get();

			$bindings = array();
			$where = '';

			$sqlQuery = ssp_sql_query($params, "product_service_view", $bindings, $where);

			$data = $this->Serviceproduct_model->getDataTableRecords($sqlQuery, $bindings);

			// Get product stats for dashboard cards
			$stats = $this->Serviceproduct_model->getProductStats();

			$response = array(
				"draw"            => !empty( $params['draw'] ) ? intval($params['draw']) : 0,
				"recordsTotal"    => intval( $this->Serviceproduct_model->countDataTableTotalRecords() ),
				"recordsFiltered" => intval( $this->Serviceproduct_model->countDataTableFilterRecords($where, $bindings) ),
				"data"            => $data,
				"stats"           => $stats
			);

			echo json_encode($response);
			exit;

		} catch (Exception $e) {
			ErrorHandler::log_database_error('ssp_list_api', 'DataTables API', $e->getMessage());

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

	public function manage($id_val = null)
	{
		if( $this->input->post() ){
			$this->form_validation->set_rules('product_name', 'Product Name', 'required|trim');
			$this->form_validation->set_message('product_name', 'Product name is required');

			$this->form_validation->set_rules('product_service_group_id', 'Service Group', 'required|trim');
			$this->form_validation->set_message('product_service_group_id', 'Service group is required');

			if ($this->form_validation->run() == true){

				// Auto-resolve type from group
				$groupTypeMap = $this->Servicegroup_model->getGroupTypeMap();
				$groupId = $this->input->post('product_service_group_id');
				$typeId = isset($groupTypeMap[$groupId]) ? $groupTypeMap[$groupId] : 0;

				$form_data = array(
					'id'						=> safe_decode($this->input->post('id')),
					'product_name'				=> $this->input->post('product_name'),
					'product_service_group_id'	=> $groupId,
					'product_service_type_id'	=> $typeId,
					'product_service_module_id'	=> $this->input->post('product_service_module_id'),
					'server_id'					=> $this->input->post('server_id'),
					'product_desc'				=> $this->input->post('product_desc'),
					'cp_package'				=> $this->input->post('cp_package'),
					'is_hidden'					=> $this->input->post('is_hidden') ? 1 : 0,
					'pricing_type'				=> in_array($this->input->post('pricing_type'), array('recurring', 'onetime', 'free')) ? $this->input->post('pricing_type') : 'recurring',
					'status'					=> 1
				);

				if( intval($form_data['id']) > 0 ){
					$oldEntity = $this->Serviceproduct_model->getDetail(safe_decode($id_val));
					$form_data['updated_on'] = getDateTime();
					$form_data['updated_by'] = getAdminId();

					$form_data['inserted_on'] = $oldEntity['inserted_on'];
					$form_data['inserted_by'] = $oldEntity['inserted_by'];
				} else {
					$form_data['inserted_on'] = getDateTime();
					$form_data['inserted_by'] = getAdminId();
				}

				$result = $this->Serviceproduct_model->saveData($form_data);
				if(!empty($result['success'])){
					$productId = !empty($result['id']) ? $result['id'] : intval($form_data['id']);

					// Save pricing based on selected type
					$pricingType = $form_data['pricing_type'];
					$billingCycles = $this->Serviceproduct_model->getBillingCycles();
					$recurringCycleIds = array_map(function($c) { return $c['id']; }, $billingCycles);
					$oneTimeCycleId = $this->Serviceproduct_model->getCycleIdByKey('ONE_TIME');
					$freeCycleId = $this->Serviceproduct_model->getCycleIdByKey('FREE');

					if ($pricingType === 'recurring') {
						$pricingData = $this->input->post('pricing');
						if (!empty($pricingData) && is_array($pricingData)) {
							$this->Serviceproduct_model->savePricingMatrix($productId, $pricingData);
						}
						$this->Serviceproduct_model->deletePricingExcept($productId, $recurringCycleIds);

					} else if ($pricingType === 'onetime') {
						$pricingData = $this->input->post('pricing');
						if (!empty($pricingData) && is_array($pricingData)) {
							$this->Serviceproduct_model->savePricingMatrix($productId, $pricingData);
						}
						$keepIds = $oneTimeCycleId ? array($oneTimeCycleId) : array();
						$this->Serviceproduct_model->deletePricingExcept($productId, $keepIds);

					} else if ($pricingType === 'free') {
						$currencies = $this->Serviceproduct_model->getCurrencies();
						$this->Serviceproduct_model->saveFreePricing($productId, true, $freeCycleId, $currencies);
						$keepIds = $freeCycleId ? array($freeCycleId) : array();
						$this->Serviceproduct_model->deletePricingExcept($productId, $keepIds);
					}

					$this->session->set_flashdata('admin_success', 'Service product has been saved successfully.');
					redirect("whmazadmin/service_product/index");
				}else {
					$this->session->set_flashdata('admin_error', 'Something went wrong. Try again');
				}
			}

		}

		if( !empty($id_val) ){
			$data['detail'] = $this->Serviceproduct_model->getDetail(safe_decode($id_val));
		} else {
			$data['detail'] = array();
		}

		$data['service_groups'] = $this->Common_model->generate_dropdown('product_service_groups', 'id', 'group_name');
		$data['service_modules'] = $this->Common_model->generate_dropdown('product_service_modules', 'id', 'module_name');
		$data['servers_list'] = $this->Server_model->getActiveServersList();

		// Mappings for JS (group→type, cPanel visibility)
		$data['group_type_map'] = $this->Servicegroup_model->getGroupTypeMap();
		$data['service_type_keys'] = $this->Serviceproduct_model->getServiceTypeKeys();
		$data['module_keys'] = $this->Serviceproduct_model->getModuleKeys();

		// Pricing matrix data
		$data['billing_cycles'] = $this->Serviceproduct_model->getBillingCycles();
		$data['currencies'] = $this->Serviceproduct_model->getCurrencies();
		$data['one_time_cycle_id'] = $this->Serviceproduct_model->getCycleIdByKey('ONE_TIME');
		$data['pricing_matrix'] = array();
		if (!empty($id_val)) {
			$data['pricing_matrix'] = $this->Serviceproduct_model->getPricingMatrix(safe_decode($id_val));
		}

		$this->load->view('whmazadmin/service_product_manage', $data);
	}

	/**
	 * AJAX: Get cPanel packages from a server
	 * @param int $serverId Server ID
	 */
	public function get_server_packages($serverId = null)
	{
		$this->processRestCall();
		header('Content-Type: application/json');

		if (empty($serverId) || !is_numeric($serverId)) {
			echo json_encode(array('success' => false, 'message' => 'Invalid server ID'));
			exit;
		}

		try {
			$serverInfo = $this->Common_model->get_data_by_id('servers', intval($serverId));

			if (empty($serverInfo)) {
				echo json_encode(array('success' => false, 'message' => 'Server not found'));
				exit;
			}

			$serverArr = (array) $serverInfo;

			// Get module name from server
			$moduleName = '';
			if (!empty($serverArr['product_service_module_id'])) {
				$module = $this->Common_model->get_data_by_id('product_service_modules', $serverArr['product_service_module_id']);
				$moduleName = strtolower(trim($module->module_name ?? ''));
			}

			// Dispatch based on module
			if ($moduleName === 'cpanel') {
				$result = whm_list_packages($serverArr);
			} elseif ($moduleName === 'plesk') {
				$result = plesk_list_packages($serverArr);
			} elseif ($moduleName === 'directadmin') {
				$result = da_list_packages($serverArr);
			} else {
				echo json_encode(array('success' => false, 'message' => 'Server has no provisioning module configured'));
				exit;
			}

			if ($result['success']) {
				echo json_encode(array('success' => true, 'packages' => $result['packages']));
			} else {
				echo json_encode(array('success' => false, 'message' => $result['error']));
			}
		} catch (Exception $e) {
			log_message('error', 'get_server_packages error: ' . $e->getMessage());
			echo json_encode(array('success' => false, 'message' => 'Error fetching packages from server'));
		}
		exit;
	}

	public function delete_records($id_val)
	{
		$entity = $this->Serviceproduct_model->getDetail(safe_decode($id_val));
		$entity["status"] = 0;
		$entity["deleted_on"] = getDateTime();
		$entity["deleted_by"] = getAdminId();

		$this->Serviceproduct_model->saveData($entity);
		$this->session->set_flashdata('admin_success', 'Service product has been deleted successfully.');

		redirect('whmazadmin/service_product/index');
	}

}
