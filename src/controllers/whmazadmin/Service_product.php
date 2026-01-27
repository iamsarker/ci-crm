<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Service_product extends WHMAZADMIN_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('Serviceproduct_model');
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

			$response = array(
				"draw"            => !empty( $params['draw'] ) ? intval($params['draw']) : 0,
				"recordsTotal"    => intval( $this->Serviceproduct_model->countDataTableTotalRecords() ),
				"recordsFiltered" => intval( $this->Serviceproduct_model->countDataTableFilterRecords($where, $bindings) ),
				"data"            => $data
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

			$this->form_validation->set_rules('product_service_type_id', 'Service Type', 'required|trim');
			$this->form_validation->set_message('product_service_type_id', 'Service type is required');

			$this->form_validation->set_rules('product_service_module_id', 'Module', 'required|trim');
			$this->form_validation->set_message('product_service_module_id', 'Module is required');

			if ($this->form_validation->run() == true){

				$form_data = array(
					'id'						=> safe_decode($this->input->post('id')),
					'product_name'				=> $this->input->post('product_name'),
					'product_service_group_id'	=> $this->input->post('product_service_group_id'),
					'product_service_type_id'	=> $this->input->post('product_service_type_id'),
					'product_service_module_id'	=> $this->input->post('product_service_module_id'),
					'server_id'					=> $this->input->post('server_id'),
					'product_desc'				=> $this->input->post('product_desc'),
					'cp_package'				=> $this->input->post('cp_package'),
					'is_hidden'					=> $this->input->post('is_hidden') ? 1 : 0,
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

				if($this->Serviceproduct_model->saveData($form_data)){
					$this->session->set_flashdata('alert_success', 'Service product has been saved successfully.');
					redirect("whmazadmin/service_product/index");
				}else {
					$this->session->set_flashdata('alert_error', 'Something went wrong. Try again');
				}
			}

		}

		if( !empty($id_val) ){
			$data['detail'] = $this->Serviceproduct_model->getDetail(safe_decode($id_val));
		} else {
			$data['detail'] = array();
		}

		$data['service_groups'] = $this->Common_model->generate_dropdown('product_service_groups', 'id', 'group_name');
		$data['service_types'] = $this->Common_model->generate_dropdown('product_service_types', 'id', 'servce_type_name');
		$data['service_modules'] = $this->Common_model->generate_dropdown('product_service_modules', 'id', 'module_name');
		$data['servers'] = $this->Common_model->generate_dropdown('servers', 'id', 'name');

		$this->load->view('whmazadmin/service_product_manage', $data);
	}

	public function delete_records($id_val)
	{
		$entity = $this->Serviceproduct_model->getDetail(safe_decode($id_val));
		$entity["status"] = 0;
		$entity["deleted_on"] = getDateTime();
		$entity["deleted_by"] = getAdminId();

		$this->Serviceproduct_model->saveData($entity);
		$this->session->set_flashdata('alert_success', 'Service product has been deleted successfully.');

		redirect('whmazadmin/service_product/index');
	}

}
