<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Package extends WHMAZADMIN_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('Package_model');
		$this->load->model('Common_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	public function index()
	{
		$data['results'] = array();
		$this->load->view('whmazadmin/package_list', $data);
	}

	public function ssp_list_api()
	{
		$this->processRestCall();
		$params = $this->input->get();

		$bindings = array();
		$where = '';

		try {
			$sqlQuery = $this->Package_model->buildDataTableQuery($params, $bindings, $where);
			$data = $this->Package_model->getDataTableRecords($sqlQuery, $bindings);

			$response = array(
				"draw"            => !empty($params['draw']) ? intval($params['draw']) : 0,
				"recordsTotal"    => intval($this->Package_model->countDataTableTotalRecords()),
				"recordsFiltered" => intval($this->Package_model->countDataTableFilterRecords($where, $bindings)),
				"data"            => $data
			);

			header('Content-Type: application/json');
			echo json_encode($response);
			exit;
		} catch (Exception $e) {
			header('Content-Type: application/json');
			echo json_encode(array(
				"error" => $e->getMessage()
			));
			exit;
		}
	}

	public function manage($id_val = null)
	{
		if( $this->input->post() ){
			$this->form_validation->set_rules('product_service_id', 'Product Service', 'required|trim');
			$this->form_validation->set_message('product_service_id', 'Product Service is required');

			$this->form_validation->set_rules('currency_id', 'Currency', 'required|trim');
			$this->form_validation->set_message('currency_id', 'Currency is required');

			$this->form_validation->set_rules('billing_cycle_id', 'Billing Cycle', 'required|trim');
			$this->form_validation->set_message('billing_cycle_id', 'Billing Cycle is required');

			$this->form_validation->set_rules('price', 'Price', 'required|trim|numeric');
			$this->form_validation->set_message('price', 'Price is required and must be numeric');

			if ($this->form_validation->run() == true){

				$form_data = array(
					'id'				=> safe_decode($this->input->post('id')),
					'product_service_id'=> $this->input->post('product_service_id'),
					'currency_id'		=> $this->input->post('currency_id'),
					'billing_cycle_id'	=> $this->input->post('billing_cycle_id'),
					'price'				=> $this->input->post('price'),
					'status'       		=> 1
				);

				if( intval($form_data['id']) > 0 ){
					$oldEntity = $this->Package_model->getPricingDetail(safe_decode($id_val));
					$form_data['updated_on'] = getDateTime();
					$form_data['updated_by'] = getAdminId();

					$form_data['inserted_on'] = $oldEntity['inserted_on'];
					$form_data['inserted_by'] = $oldEntity['inserted_by'];
				} else {
					$form_data['inserted_on'] = getDateTime();
					$form_data['inserted_by'] = getAdminId();
				}

				if($this->Package_model->savePricingData($form_data)){
					$this->session->set_flashdata('admin_success', 'Package pricing has been saved successfully.');
					redirect("whmazadmin/package/index");
				}else {
					$this->session->set_flashdata('admin_error', 'Something went wrong. Try again');
				}
			}

		}

		if( !empty($id_val) ){
			$data['detail'] = $this->Package_model->getPricingDetail(safe_decode($id_val));
		} else {
			$data['detail'] = array();
		}

		// Load dropdown data
		$data['services'] = $this->Package_model->getAllServices();
		$data['currencies'] = $this->Package_model->getAllCurrencies();
		$data['billing_cycles'] = $this->Package_model->getAllBillingCycles();

		$this->load->view('whmazadmin/package_manage', $data);
	}

	public function delete_records($id_val)
	{
		$entity = $this->Package_model->getPricingDetail(safe_decode($id_val));
		$entity["status"] = 0;
		$entity["deleted_on"] = getDateTime();
		$entity["deleted_by"] = getAdminId();

		$this->Package_model->savePricingData($entity);
		$this->session->set_flashdata('admin_success', 'Package pricing has been deleted successfully.');

		redirect('whmazadmin/package/index');
	}

	/**
	 * API: Filter hosting packages by module, server, and service group
	 */
	public function filter_api()
	{
		$this->processRestCall();

		$params = array(
			'module_id' => intval($this->input->post('module_id')),
			'server_id' => intval($this->input->post('server_id')),
			'service_group_id' => intval($this->input->post('service_group_id'))
		);

		// Validate required parameters
		if ($params['module_id'] <= 0 || $params['server_id'] <= 0 || $params['service_group_id'] <= 0) {
			header('Content-Type: application/json');
			echo json_encode(array('code' => 400, 'msg' => 'Invalid parameters', 'data' => array()));
			return;
		}

		$data = $this->Package_model->filterData($params);

		header('Content-Type: application/json');
		echo json_encode(array('code' => 200, 'msg' => 'OK', 'data' => $data));
	}

	/**
	 * API: Get hosting package price by product, currency, and billing cycle
	 */
	public function prices()
	{
		$this->processRestCall();

		$params = array(
			'product_service_id' => intval($this->input->post('product_service_id')),
			'currency_id' => intval($this->input->post('currency_id')),
			'billing_cycle_id' => intval($this->input->post('billing_cycle_id'))
		);

		// Validate required parameters
		if ($params['product_service_id'] <= 0 || $params['currency_id'] <= 0 || $params['billing_cycle_id'] <= 0) {
			header('Content-Type: application/json');
			echo json_encode(array('code' => 400, 'msg' => 'Invalid parameters', 'data' => null));
			return;
		}

		$data = $this->Package_model->priceData($params);

		header('Content-Type: application/json');
		echo json_encode(array('code' => 200, 'msg' => 'OK', 'data' => $data));
	}

}
