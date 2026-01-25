<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Domain_pricing extends WHMAZADMIN_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('Domainpricing_model');
		$this->load->model('Common_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	public function index()
	{
		$data['results'] = array();
		$this->load->view('whmazadmin/domain_pricing_list', $data);
	}

	public function ssp_list_api()
	{
		$this->processRestCall();
		$params = $this->input->get();

		$bindings = array();
		$where = '';

		try {
			$sqlQuery = $this->Domainpricing_model->buildDataTableQuery($params, $bindings, $where);
			$data = $this->Domainpricing_model->getDataTableRecords($sqlQuery, $bindings);

			$response = array(
				"draw"            => !empty($params['draw']) ? intval($params['draw']) : 0,
				"recordsTotal"    => intval($this->Domainpricing_model->countDataTableTotalRecords()),
				"recordsFiltered" => intval($this->Domainpricing_model->countDataTableFilterRecords($where, $bindings)),
				"data"            => $data
			);

			header('Content-Type: application/json');
			echo json_encode($response);
			exit;
		} catch (Exception $e) {
			ErrorHandler::log_database_error('ssp_list_api', 'DataTables API', $e->getMessage());
			header('Content-Type: application/json');
			echo json_encode(array(
				"draw" => 0,
				"recordsTotal" => 0,
				"recordsFiltered" => 0,
				"data" => array(),
				"error" => $e->getMessage()
			));
			exit;
		}
	}

	public function manage($id_val = null)
	{
		if( $this->input->post() ){
			$this->form_validation->set_rules('dom_extension_id', 'Domain Extension', 'required|trim');
			$this->form_validation->set_message('dom_extension_id', 'Domain Extension is required');

			$this->form_validation->set_rules('currency_id', 'Currency', 'required|trim');
			$this->form_validation->set_message('currency_id', 'Currency is required');

			$this->form_validation->set_rules('reg_period', 'Registration Period', 'required|trim|integer');
			$this->form_validation->set_message('reg_period', 'Registration Period is required and must be an integer');

			$this->form_validation->set_rules('price', 'Registration Price', 'required|trim|numeric');
			$this->form_validation->set_message('price', 'Registration Price is required and must be numeric');

			$this->form_validation->set_rules('transfer', 'Transfer Price', 'required|trim|numeric');
			$this->form_validation->set_message('transfer', 'Transfer Price is required and must be numeric');

			$this->form_validation->set_rules('renewal', 'Renewal Price', 'required|trim|numeric');
			$this->form_validation->set_message('renewal', 'Renewal Price is required and must be numeric');

			if ($this->form_validation->run() == true){

				$form_data = array(
					'id'				=> safe_decode($this->input->post('id')),
					'dom_extension_id'	=> $this->input->post('dom_extension_id'),
					'currency_id'		=> $this->input->post('currency_id'),
					'reg_period'		=> $this->input->post('reg_period'),
					'price'				=> $this->input->post('price'),
					'transfer'			=> $this->input->post('transfer'),
					'renewal'			=> $this->input->post('renewal'),
					'status'       		=> 1
				);

				if( intval($form_data['id']) > 0 ){
					$oldEntity = $this->Domainpricing_model->getDetail(safe_decode($id_val));
					$form_data['updated_on'] = getDateTime();
					$form_data['updated_by'] = getAdminId();

					$form_data['inserted_on'] = $oldEntity['inserted_on'];
					$form_data['inserted_by'] = $oldEntity['inserted_by'];
				} else {
					$form_data['inserted_on'] = getDateTime();
					$form_data['inserted_by'] = getAdminId();
				}

				if($this->Domainpricing_model->saveData($form_data)){
					$this->session->set_flashdata('alert_success', 'Domain pricing has been saved successfully.');
					redirect("whmazadmin/domain_pricing/index");
				}else {
					$this->session->set_flashdata('alert_error', 'Something went wrong. Try again');
				}
			}

		}

		if( !empty($id_val) ){
			$data['detail'] = $this->Domainpricing_model->getDetail(safe_decode($id_val));
		} else {
			$data['detail'] = array();
		}

		// Load dropdown data
		$data['extensions'] = $this->Domainpricing_model->getAllExtensions();
		$data['currencies'] = $this->Domainpricing_model->getAllCurrencies();

		$this->load->view('whmazadmin/domain_pricing_manage', $data);
	}

	public function delete_records($id_val)
	{
		$entity = $this->Domainpricing_model->getDetail(safe_decode($id_val));
		$entity["status"] = 0;
		$entity["deleted_on"] = getDateTime();
		$entity["deleted_by"] = getAdminId();

		$this->Domainpricing_model->saveData($entity);
		$this->session->set_flashdata('alert_success', 'Domain pricing has been deleted successfully.');

		redirect('whmazadmin/domain_pricing/index');
	}

	public function prices()
	{
		$this->processRestCall();
		$rqData = $this->input->post();

		$domain_array = explode(".", $rqData['domain']);
		if ( count($domain_array) == 3 ){
			$extension = ".".$domain_array[1].'.'.$domain_array[2];
		} else if ( count($domain_array) == 2 ){
			$extension = ".".$domain_array[1];
		} else {
			$extension = "";
		}
		echo json_encode(buildSuccessResponse($this->Common_model->getDomainPrices($rqData['currency_id'], $rqData['reg_period'], $extension), "OK"));
	}

}
