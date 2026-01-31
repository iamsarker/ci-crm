<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Service_group extends WHMAZADMIN_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('Servicegroup_model');
		$this->load->model('Common_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	public function index()
	{
		$data['results'] = $this->Servicegroup_model->loadAllData();
		$this->load->view('whmazadmin/service_group_list', $data);
	}

	public function manage($id_val = null)
	{
		if( $this->input->post() ){
			$this->form_validation->set_rules('group_name', 'Group Name', 'required|trim');
			$this->form_validation->set_message('group_name', 'Group name is required');

			$this->form_validation->set_rules('product_service_type_id', 'Group category', 'required|trim');
			$this->form_validation->set_message('product_service_type_id', 'Group category is required');

			$this->form_validation->set_rules('group_headline', 'Group headline', 'required|trim');
			$this->form_validation->set_message('group_headline', 'Group headline is required');

			if ($this->form_validation->run() == true){

				$form_data = array(
					'id'			=> safe_decode($this->input->post('id')),
					'group_name'	=> $this->input->post('group_name'),
					'product_service_type_id'	=> $this->input->post('product_service_type_id'),
					'group_headline'=> $this->input->post('group_headline'),
					'tags'			=> $this->input->post('tags'),
					'status'       	=> 1
				);

				if( intval($form_data['id']) > 0 ){
					$oldEntity = $this->Servicegroup_model->getDetail(safe_decode($id_val));
					$form_data['updated_on'] = getDateTime();
					$form_data['updated_by'] = getAdminId();

					$form_data['inserted_on'] = $oldEntity['inserted_on'];
					$form_data['inserted_by'] = $oldEntity['inserted_by'];
				} else {
					$form_data['inserted_on'] = getDateTime();
					$form_data['inserted_by'] = getAdminId();
				}

				if($this->Servicegroup_model->saveData($form_data)){
					$this->session->set_flashdata('admin_success', 'Service group has been saved successfully.');
					redirect("whmazadmin/Service_group/index");
				}else {
					$this->session->set_flashdata('admin_error', 'Something went wrong. Try again');
				}
			}

		}

		if( !empty($id_val) ){
			$data['detail'] = $this->Servicegroup_model->getDetail(safe_decode($id_val));
		} else {
			$data['detail'] = array();
		}

		$data['categories'] = $this->Common_model->generate_dropdown('product_service_types', 'id', "servce_type_name");

		$this->load->view('whmazadmin/service_group_manage', $data);
	}

	public function delete_records($id_val)
	{
		$entity = $this->Servicegroup_model->getDetail(safe_decode($id_val));
		$entity["status"] = 0;
		$entity["deleted_on"] = getDateTime();
		$entity["deleted_by"] = getAdminId();

		$this->Servicegroup_model->saveData($entity);
		$this->session->set_flashdata('admin_success', 'Service group has been deleted successfully.');

		redirect('whmazadmin/Service_group/index');
	}

}
