<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Service_category extends WHMAZADMIN_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('Servicecategory_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	public function index()
	{
		$data['results'] = $this->Servicecategory_model->loadAllData();
		$this->load->view('whmazadmin/service_category_list', $data);
	}

	public function manage($id_val = null)
	{
		if( $this->input->post() ){
			$this->form_validation->set_rules('servce_type_name', 'Category Name', 'required|trim');
			$this->form_validation->set_message('servce_type_name', 'Category name is required');

			$this->form_validation->set_rules('sort_order', 'Sort order', 'required|trim');
			$this->form_validation->set_message('sort_order', 'Sort order is required');

			if ($this->form_validation->run() == true){

				$key_name = str_replace(" ", "_", $this->input->post('servce_type_name'));
				$key_name = str_replace("/", "_", $key_name);
				$key_name = str_replace(",", "_", $key_name);
				$key_name = str_replace(".", "_", $key_name);
				$key_name = str_replace("!", "_", $key_name);
				$key_name = str_replace("@", "_", $key_name);
				$key_name = str_replace("#", "_", $key_name);
				$key_name = str_replace("-", "_", $key_name);

				$form_data = array(
					'id'			=> safe_decode($this->input->post('id')),
					'servce_type_name'	=> $this->input->post('servce_type_name'),
					'key_name'	=> strtoupper($key_name),
					'sort_order'=> $this->input->post('sort_order'),
					'remarks'=> $this->input->post('remarks'),
					'status'       	=> 1
				);

				if( intval($form_data['id']) > 0 ){
					$oldEntity = $this->Servicecategory_model->getDetail(safe_decode($id_val));
					$form_data['updated_on'] = getDateTime();
					$form_data['updated_by'] = getAdminId();

					$form_data['inserted_on'] = $oldEntity['inserted_on'];
					$form_data['inserted_by'] = $oldEntity['inserted_by'];
				} else {
					$form_data['inserted_on'] = getDateTime();
					$form_data['inserted_by'] = getAdminId();
				}

				if($this->Servicecategory_model->saveData($form_data)){
					$this->session->set_flashdata('alert_success', 'Service category has been saved successfully.');
					redirect("whmazadmin/Service_category/index");
				}else {
					$this->session->set_flashdata('alert_error', 'Something went wrong. Try again');
				}
			}

		}

		if( !empty($id_val) ){
			$data['detail'] = $this->Servicecategory_model->getDetail(safe_decode($id_val));
		} else {
			$data['detail'] = array();
		}

		$this->load->view('whmazadmin/service_category_manage', $data);
	}

	public function delete_records($id_val)
	{
		$entity = $this->Servicecategory_model->getDetail(safe_decode($id_val));
		$entity["status"] = 0;
		$entity["deleted_on"] = getDateTime();
		$entity["deleted_by"] = getAdminId();

		$this->Servicecategory_model->saveData($entity);
		$this->session->set_flashdata('alert_success', 'Service category has been deleted successfully.');

		redirect('whmazadmin/Service_category/index');
	}

}
