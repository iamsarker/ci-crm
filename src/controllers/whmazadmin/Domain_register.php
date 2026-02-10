<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Domain_register extends WHMAZADMIN_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('Domainregister_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	public function index()
	{
		$data['results'] = array();
		$this->load->view('whmazadmin/domain_register_list', $data);
	}

	public function ssp_list_api()
	{
		$this->processRestCall();
		$params = $this->input->get();

		$bindings = array();
		$where = '';

		try {
			$sqlQuery = $this->Domainregister_model->buildDataTableQuery($params, $bindings, $where);
			$data = $this->Domainregister_model->getDataTableRecords($sqlQuery, $bindings);

			$response = array(
				"draw"            => !empty($params['draw']) ? intval($params['draw']) : 0,
				"recordsTotal"    => intval($this->Domainregister_model->countDataTableTotalRecords()),
				"recordsFiltered" => intval($this->Domainregister_model->countDataTableFilterRecords($where, $bindings)),
				"data"            => $data
			);

			header('Content-Type: application/json');
			echo json_encode($response);
			exit;
		} catch (Exception $e) {
			header('Content-Type: application/json');
			echo json_encode(array("error" => $e->getMessage()));
			exit;
		}
	}

	public function manage($id_val = null)
	{
		if( $this->input->post() ){
			$this->form_validation->set_rules('name', 'Registrar Name', 'required|trim');
			$this->form_validation->set_message('name', 'Registrar name is required');

			$this->form_validation->set_rules('platform', 'Platform', 'required|trim');
			$this->form_validation->set_message('platform', 'Platform is required');

			$this->form_validation->set_rules('api_base_url', 'API Base URL', 'required|trim');
			$this->form_validation->set_message('api_base_url', 'API Base URL is required');

			$this->form_validation->set_rules('auth_userid', 'Auth User ID', 'required|trim');
			$this->form_validation->set_message('auth_userid', 'Auth User ID is required');

			$this->form_validation->set_rules('auth_apikey', 'Auth API Key', 'required|trim');
			$this->form_validation->set_message('auth_apikey', 'Auth API Key is required');

			if ($this->form_validation->run() == true){

				$form_data = array(
					'id'				=> safe_decode($this->input->post('id')),
					'name'				=> $this->input->post('name'),
					'platform'			=> $this->input->post('platform'),
					'api_base_url'		=> $this->input->post('api_base_url'),
					'domain_check_api'	=> $this->input->post('domain_check_api'),
					'suggestion_api'	=> $this->input->post('suggestion_api'),
					'domain_reg_api'	=> $this->input->post('domain_reg_api'),
					'ns_update_api'		=> $this->input->post('ns_update_api'),
					'contact_details_api'	=> $this->input->post('contact_details_api'),
					'contact_update_api'	=> $this->input->post('contact_update_api'),
					'price_list_api'	=> $this->input->post('price_list_api'),
					'auth_userid'		=> $this->input->post('auth_userid'),
					'auth_apikey'		=> $this->input->post('auth_apikey'),
					'is_selected'		=> $this->input->post('is_selected') ? 1 : 0,
					'def_ns1'			=> $this->input->post('def_ns1'),
					'def_ns2'			=> $this->input->post('def_ns2'),
					'def_ns3'			=> $this->input->post('def_ns3'),
					'def_ns4'			=> $this->input->post('def_ns4'),
					'status'       		=> 1
				);

				if( intval($form_data['id']) > 0 ){
					$oldEntity = $this->Domainregister_model->getDetail(safe_decode($id_val));
					$form_data['updated_on'] = getDateTime();
					$form_data['updated_by'] = getAdminId();

					$form_data['inserted_on'] = $oldEntity['inserted_on'];
					$form_data['inserted_by'] = $oldEntity['inserted_by'];
				} else {
					$form_data['inserted_on'] = getDateTime();
					$form_data['inserted_by'] = getAdminId();
				}

				if($this->Domainregister_model->saveData($form_data)){
					$this->session->set_flashdata('admin_success', 'Domain registrar has been saved successfully.');
					redirect("whmazadmin/domain_register/index");
				}else {
					$this->session->set_flashdata('admin_error', 'Something went wrong. Try again');
				}
			}

		}

		if( !empty($id_val) ){
			$data['detail'] = $this->Domainregister_model->getDetail(safe_decode($id_val));
		} else {
			$data['detail'] = array();
		}

		$this->load->view('whmazadmin/domain_register_manage', $data);
	}

	public function delete_records($id_val)
	{
		$entity = $this->Domainregister_model->getDetail(safe_decode($id_val));
		$entity["status"] = 0;
		$entity["deleted_on"] = getDateTime();
		$entity["deleted_by"] = getAdminId();

		$this->Domainregister_model->saveData($entity);
		$this->session->set_flashdata('admin_success', 'Domain registrar has been deleted successfully.');

		redirect('whmazadmin/domain_register/index');
	}

}
