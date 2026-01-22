<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Currency extends WHMAZADMIN_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('Currency_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	public function index()
	{
		$data['results'] = $this->Currency_model->loadAllData();
		$this->load->view('whmazadmin/currency_list', $data);
	}

	public function manage($id_val = null)
	{
		if( $this->input->post() ){
			$this->form_validation->set_rules('code', 'code', 'required|trim');
			$this->form_validation->set_message('code', 'code is required');

			$this->form_validation->set_rules('symbol', 'symbol', 'required|trim');
			$this->form_validation->set_message('symbol', 'symbol is required');

			$this->form_validation->set_rules('rate', 'rate', 'required|trim');
			$this->form_validation->set_message('rate', 'rate is required');


			if ($this->form_validation->run() == true){

				$form_data = array(
					'id'			=> safe_decode($this->input->post('id')),
					'code'			=> $this->input->post('code'),
					'symbol'		=> $this->input->post('symbol'),
					'rate'			=> $this->input->post('rate'),
					'format'		=> $this->input->post('format'),
					'is_default'	=> $this->input->post('is_default') ? 1 : 0,
					'status'       	=> 1
				);

				if( intval($form_data['id']) > 0 ){
					$oldEntity = $this->Currency_model->getDetail(safe_decode($id_val));
					$form_data['updated_on'] = getDateTime();
					$form_data['updated_by'] = getAdminId();

					$form_data['inserted_on'] = $oldEntity['inserted_on'];
					$form_data['inserted_by'] = $oldEntity['inserted_by'];
				} else {
					$form_data['inserted_on'] = getDateTime();
					$form_data['inserted_by'] = getAdminId();
				}

				if($this->Currency_model->saveData($form_data)){
					$this->session->set_flashdata('alert_success', 'Currency data has been saved successfully.');
					redirect("whmazadmin/currency/index");
				}else {
					$this->session->set_flashdata('alert_error', 'Something went wrong. Try again');
				}
			}

		}

		if( !empty($id_val) ){
			$data['detail'] = $this->Currency_model->getDetail(safe_decode($id_val));
		} else {
			$data['detail'] = array();
		}

		$this->load->view('whmazadmin/currency_manage', $data);
	}

	public function delete_records($id_val)
	{
		$entity = $this->Currency_model->getDetail(safe_decode($id_val));
		$entity["status"] = 0;
		$entity["deleted_on"] = getDateTime();
		$entity["deleted_by"] = getAdminId();

		$this->Currency_model->saveData($entity);
		$this->session->set_flashdata('alert_success', 'Currency data has been deleted successfully.');

		redirect('whmazadmin/currency/index');
	}

}
