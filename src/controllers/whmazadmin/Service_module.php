<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Service_module extends WHMAZADMIN_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('Servicemodule_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	public function index()
	{
		$data['results'] = $this->Servicemodule_model->loadAllData();
		$this->load->view('whmazadmin/service_module_list', $data);
	}

	public function manage($id_val = null)
	{
		if( $this->input->post() ){
			$this->form_validation->set_rules('module_name', 'Module Name', 'required|trim');
			$this->form_validation->set_message('module_name', 'Module name is required');

			if ($this->form_validation->run() == true){

				$form_data = array(
					'id'			=> safe_decode($this->input->post('id')),
					'module_name'	=> $this->input->post('module_name'),
					'remarks'=> $this->input->post('remarks'),
					'status'       	=> 1
				);

				if( intval($form_data['id']) > 0 ){
					$oldEntity = $this->Servicemodule_model->getDetail(safe_decode($id_val));
					$form_data['updated_on'] = getDateTime();
					$form_data['updated_by'] = getAdminId();

					$form_data['inserted_on'] = $oldEntity['inserted_on'];
					$form_data['inserted_by'] = $oldEntity['inserted_by'];
				} else {
					$form_data['inserted_on'] = getDateTime();
					$form_data['inserted_by'] = getAdminId();
				}

				if($this->Servicemodule_model->saveData($form_data)){
					$this->session->set_flashdata('admin_success', 'Service module has been saved successfully.');
					redirect("whmazadmin/Service_module/index");
				}else {
					$this->session->set_flashdata('admin_error', 'Something went wrong. Try again');
				}
			}

		}

		if( !empty($id_val) ){
			$data['detail'] = $this->Servicemodule_model->getDetail(safe_decode($id_val));
		} else {
			$data['detail'] = array();
		}

		$this->load->view('whmazadmin/service_module_manage', $data);
	}

	public function delete_records($id_val)
	{
		$entity = $this->Servicemodule_model->getDetail(safe_decode($id_val));
		$entity["status"] = 0;
		$entity["deleted_on"] = getDateTime();
		$entity["deleted_by"] = getAdminId();

		$this->Servicemodule_model->saveData($entity);
		$this->session->set_flashdata('admin_success', 'Service module has been deleted successfully.');

		redirect('whmazadmin/Service_module/index');
	}

}
