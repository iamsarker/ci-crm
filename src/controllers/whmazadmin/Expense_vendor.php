<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Expense_vendor extends WHMAZADMIN_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('Expensevendor_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	public function index()
	{
		$data['results'] = $this->Expensevendor_model->loadAllData();
		$this->load->view('whmazadmin/expense_vendor_list', $data);
	}

	public function manage($id_val = null)
	{
		if( $this->input->post() ){
			$this->form_validation->set_rules('vendor_name', 'Expense vendor', 'required|trim');
			$this->form_validation->set_message('vendor_name', 'Expense vendor is required');


			if ($this->form_validation->run() == true){

				$form_data = array(
					'id'			=> safe_decode($this->input->post('id')),
					'vendor_name'	=> $this->input->post('vendor_name'),
					'remarks'=> $this->input->post('remarks'),
					'status'       	=> 1
				);

				if( intval($form_data['id']) > 0 ){
					$oldEntity = $this->Expensevendor_model->getDetail(safe_decode($id_val));
					$form_data['updated_on'] = getDateTime();
					$form_data['updated_by'] = getAdminId();

					$form_data['inserted_on'] = $oldEntity['inserted_on'];
					$form_data['inserted_by'] = $oldEntity['inserted_by'];
				} else {
					$form_data['inserted_on'] = getDateTime();
					$form_data['inserted_by'] = getAdminId();
				}

				if($this->Expensevendor_model->saveData($form_data)){
					$this->session->set_flashdata('admin_success', 'Expense vendor has been saved successfully.');
					redirect("whmazadmin/Expense_vendor/index");
				}else {
					$this->session->set_flashdata('admin_error', 'Something went wrong. Try again');
				}
			}

		}

		if( !empty($id_val) ){
			$data['detail'] = $this->Expensevendor_model->getDetail(safe_decode($id_val));
		} else {
			$data['detail'] = array();
		}

		$this->load->view('whmazadmin/expense_vendor_manage', $data);
	}

	public function delete_records($id_val)
	{
		$entity = $this->Expensevendor_model->getDetail(safe_decode($id_val));
		$entity["status"] = 0;
		$entity["deleted_on"] = getDateTime();
		$entity["deleted_by"] = getAdminId();

		$this->Expensevendor_model->saveData($entity);
		$this->session->set_flashdata('admin_success', 'Expense vendor has been deleted successfully.');

		redirect('whmazadmin/Expense_vendor/index');
	}

}
