<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Expense_category extends WHMAZADMIN_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('Expensecategory_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	public function index()
	{
		$data['results'] = $this->Expensecategory_model->loadAllData();
		$this->load->view('whmazadmin/expense_category_list', $data);
	}

	public function manage($id_val = null)
	{
		if( $this->input->post() ){
			$this->form_validation->set_rules('expense_type', 'Expense category', 'required|trim');
			$this->form_validation->set_message('expense_type', 'Expense category is required');


			if ($this->form_validation->run() == true){

				$form_data = array(
					'id'			=> safe_decode($this->input->post('id')),
					'expense_type'	=> $this->input->post('expense_type'),
					'remarks'=> $this->input->post('remarks'),
					'status'       	=> 1
				);

				if( intval($form_data['id']) > 0 ){
					$oldEntity = $this->Expensecategory_model->getDetail(safe_decode($id_val));
					$form_data['updated_on'] = getDateTime();
					$form_data['updated_by'] = getAdminId();

					$form_data['inserted_on'] = $oldEntity['inserted_on'];
					$form_data['inserted_by'] = $oldEntity['inserted_by'];
				} else {
					$form_data['inserted_on'] = getDateTime();
					$form_data['inserted_by'] = getAdminId();
				}

				if($this->Expensecategory_model->saveData($form_data)){
					$this->session->set_flashdata('alert_success', 'Expense category has been saved successfully.');
					redirect("whmazadmin/Expense_category/index");
				}else {
					$this->session->set_flashdata('alert_error', 'Something went wrong. Try again');
				}
			}

		}

		if( !empty($id_val) ){
			$data['detail'] = $this->Expensecategory_model->getDetail(safe_decode($id_val));
		} else {
			$data['detail'] = array();
		}

		$this->load->view('whmazadmin/expense_category_manage', $data);
	}

	public function delete_records($id_val)
	{
		$entity = $this->Expensecategory_model->getDetail(safe_decode($id_val));
		$entity["status"] = 0;
		$entity["deleted_on"] = getDateTime();
		$entity["deleted_by"] = getAdminId();

		$this->Expensecategory_model->saveData($entity);
		$this->session->set_flashdata('alert_success', 'Expense category has been deleted successfully.');

		redirect('whmazadmin/Expense_category/index');
	}

}
