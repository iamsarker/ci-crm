<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Expense extends WHMAZADMIN_Controller {

	var $img_path;
	function __construct(){
		parent::__construct();
		$this->load->model('Expense_model');
		$this->load->model('Expensecategory_model');
		$this->load->model('Expensevendor_model');
		$this->load->model('Common_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
		$this->img_path = realpath(APPPATH . '../uploadedfiles/expenses/');
	}

	public function index()
	{
		$data['results'] = array();
		$this->load->view('whmazadmin/expense_list', $data);
	}

	public function manage($id_val = null)
	{
		if( $this->input->post() ){
			$this->form_validation->set_rules('expense_type_id', 'Expense category', 'required|trim');
			$this->form_validation->set_message('expense_type_id', 'Expense category is required');

			$this->form_validation->set_rules('expense_vendor_id', 'Expense vendor', 'required|trim');
			$this->form_validation->set_message('expense_vendor_id', 'Expense vendor is required');

			$this->form_validation->set_rules('expense_date', 'Expense date', 'required|trim');
			$this->form_validation->set_message('expense_date', 'Expense date is required');

			$this->form_validation->set_rules('exp_amount', 'Expense amount', 'required|trim');
			$this->form_validation->set_message('exp_amount', 'Expense amount is required');

			$this->form_validation->set_rules('paid_amount', 'Paid amount', 'required|trim');
			$this->form_validation->set_message('paid_amount', 'Paid amount is required');

			if ($this->form_validation->run() == true){

				$image_name = '';
				if(!empty($_FILES['attachment']['name'][0]) && $_FILES['attachment']['size'][0] > 0){
					$image = $this->Common_model->upload_files($this->img_path, round(microtime(true) * 100), $_FILES['attachment']);
					if(is_array($image) && !empty($image)){
						$image_name = implode(",", $image);
					}
				}

				$form_data = array(
					'id'			=> safe_decode($this->input->post('id')),
					'expense_type_id'	=> $this->input->post('expense_type_id'),
					'expense_vendor_id'	=> $this->input->post('expense_vendor_id'),
					'expense_date'	=> $this->input->post('expense_date'),
					'exp_amount'	=> $this->input->post('exp_amount'),
					'paid_amount'	=> $this->input->post('paid_amount'),
					'attachment'	=> $image_name,
					'remarks'=> $this->input->post('remarks'),
					'status'       	=> 1
				);

				if( intval($form_data['id']) > 0 ){
					$oldEntity = $this->Expense_model->getDetail(safe_decode($id_val));
					$form_data['updated_on'] = getDateTime();
					$form_data['updated_by'] = getAdminId();

					if( $image_name == '' ){
						$form_data['attachment'] = $oldEntity['attachment'];
					}

					$form_data['inserted_on'] = $oldEntity['inserted_on'];
					$form_data['inserted_by'] = $oldEntity['inserted_by'];
				} else {
					$form_data['inserted_on'] = getDateTime();
					$form_data['inserted_by'] = getAdminId();
				}

				if($this->Expense_model->saveData($form_data)){
					$this->session->set_flashdata('admin_success', 'Expense has been saved successfully.');
					redirect("whmazadmin/Expense/index");
				}else {
					$this->session->set_flashdata('admin_error', 'Something went wrong. Try again');
				}
			}

		}

		if( !empty($id_val) ){
			$data['detail'] = $this->Expense_model->getDetail(safe_decode($id_val));
		} else {
			$data['detail'] = array();
		}

		$data['categories'] = $this->Expensecategory_model->loadAllData();
		$data['vendors'] = $this->Expensevendor_model->loadAllData();

		$this->load->view('whmazadmin/expense_manage', $data);
	}

	public function delete_records($id_val)
	{
		$entity = $this->Expense_model->getDetail(safe_decode($id_val));
		$entity["status"] = 0;
		$entity["deleted_on"] = getDateTime();
		$entity["deleted_by"] = getAdminId();

		$this->Expense_model->saveData($entity);
		$this->session->set_flashdata('admin_success', 'Expense has been deleted successfully.');

		redirect('whmazadmin/Expense/index');
	}

	public function ssp_list_api()
	{
		$this->processRestCall();

		// Set proper JSON headers
		header('Content-Type: application/json');

		try {
			$params = $this->input->get();

			$bindings = array();
			$where = '';

			$sqlQuery = ssp_sql_query($params, "expense_view", $bindings, $where);

			$data = $this->Expense_model->getDataTableRecords($sqlQuery, $bindings);

			$response = array(
				"draw"            => !empty( $params['draw'] ) ? intval($params['draw']) : 0,
				"recordsTotal"    => intval( $this->Expense_model->countDataTableTotalRecords() ),
				"recordsFiltered" => intval( $this->Expense_model->countDataTableFilterRecords($where, $bindings) ),
				"data"            => $data
			);

			echo json_encode($response);
			exit;

		} catch (Exception $e) {
			// Return error in DataTables format
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

}
