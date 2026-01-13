<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ticket_department extends WHMAZADMIN_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('Ticketdepartment_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	public function index()
	{
		$data['results'] = $this->Ticketdepartment_model->loadAllData();
		$this->load->view('whmazadmin/ticket_department_list', $data);
	}

	public function manage($id_val = null)
	{
		if( $this->input->post() ){
			$this->form_validation->set_rules('name', 'Name', 'required|trim');
			$this->form_validation->set_message('name', 'Name is required');

			$this->form_validation->set_rules('email', 'Email', 'required|trim');
			$this->form_validation->set_message('email', 'Email is required');

			$this->form_validation->set_rules('sort_order', 'Sort order', 'required|trim');
			$this->form_validation->set_message('sort_order', 'Sort order is required');

			if ($this->form_validation->run() == true){

				$form_data = array(
					'id'			=> safe_decode($this->input->post('id')),
					'name'	=> $this->input->post('name'),
					'email'	=> $this->input->post('email'),
					'description'	=> $this->input->post('description'),
					'sort_order'=> $this->input->post('sort_order'),
					'status'       	=> 1
				);

				if( intval($form_data['id']) > 0 ){
					$oldEntity = $this->Ticketdepartment_model->getDetail(safe_decode($id_val));
					$form_data['updated_on'] = getDateTime();
					$form_data['updated_by'] = getAdminId();

					$form_data['inserted_on'] = $oldEntity['inserted_on'];
					$form_data['inserted_by'] = $oldEntity['inserted_by'];
				} else {
					$form_data['inserted_on'] = getDateTime();
					$form_data['inserted_by'] = getAdminId();
				}

				if($this->Ticketdepartment_model->saveData($form_data)){
					$this->session->set_flashdata('alert', successAlert('Support dept has been saved successfully.'));
					redirect("whmazadmin/ticket_department/index");
				}else {
					$this->session->set_flashdata('alert', errorAlert('Something went wrong. Try again'));
				}
			}

		}

		if( !empty($id_val) ){
			$data['detail'] = $this->Ticketdepartment_model->getDetail(safe_decode($id_val));
		} else {
			$data['detail'] = array();
		}

		$this->load->view('whmazadmin/ticket_department_manage', $data);
	}

	public function delete_records($id_val)
	{
		$entity = $this->Ticketdepartment_model->getDetail(safe_decode($id_val));
		$entity["status"] = 0;
		$entity["deleted_on"] = getDateTime();
		$entity["deleted_by"] = getAdminId();

		$this->Ticketdepartment_model->saveData($entity);
		$this->session->set_flashdata('alert', successAlert('Support dept has been deleted successfully.'));

		redirect('whmazadmin/ticket_department/index');
	}

}
