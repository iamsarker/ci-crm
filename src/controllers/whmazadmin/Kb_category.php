<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kb_category extends WHMAZADMIN_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('Kbcat_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	public function index()
	{
		$data['results'] = $this->Kbcat_model->loadAllData();
		$this->load->view('whmazadmin/kb_category_list', $data);
	}

	public function manage($id_val = null)
	{
		if( $this->input->post() ){
			$this->form_validation->set_rules('cat_title', 'Category title', 'required|trim');
			$this->form_validation->set_message('cat_title', 'Category title is required');

			$this->form_validation->set_rules('description', 'Description', 'required|trim');
			$this->form_validation->set_message('description', 'Description is required');

			if ($this->form_validation->run() == true){

				$form_data = array(
					'id'			=> safe_decode($this->input->post('id')),
					'cat_title'	=> $this->input->post('cat_title'),
					'parent_id'=> $this->input->post('parent_id'),
					'description'=> $this->input->post('description'),
					'slug'=> $this->input->post('slug'),
					'is_hidden'=> $this->input->post('is_hidden') ? 1 : 0,
					'status'       	=> 1
				);

				if( intval($form_data['id']) > 0 ){
					$oldEntity = $this->Kbcat_model->getDetail(safe_decode($id_val));
					$form_data['updated_on'] = getDateTime();
					$form_data['updated_by'] = getAdminId();

					$form_data['inserted_on'] = $oldEntity['inserted_on'];
					$form_data['inserted_by'] = $oldEntity['inserted_by'];
				} else {
					$form_data['inserted_on'] = getDateTime();
					$form_data['inserted_by'] = getAdminId();
				}

				if($this->Kbcat_model->saveData($form_data)){
					$this->session->set_flashdata('alert_success', 'KB category has been saved successfully.');
					redirect("whmazadmin/kb_category/index");
				}else {
					$this->session->set_flashdata('alert_error', 'Something went wrong. Try again');
				}
			}

		}

		if( !empty($id_val) ){
			$data['detail'] = $this->Kbcat_model->getDetail(safe_decode($id_val));
		} else {
			$data['detail'] = array();
		}

		$this->load->view('whmazadmin/kb_category_manage', $data);
	}

	public function delete_records($id_val)
	{
		$entity = $this->Kbcat_model->getDetail(safe_decode($id_val));
		$entity["status"] = 0;
		$entity["deleted_on"] = getDateTime();
		$entity["deleted_by"] = getAdminId();

		$this->Kbcat_model->saveData($entity);
		$this->session->set_flashdata('alert_success', 'KB category has been deleted successfully.');

		redirect('whmazadmin/kb_category/index');
	}

}
