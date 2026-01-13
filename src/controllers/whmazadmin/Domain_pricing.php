<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Domain_pricing extends WHMAZADMIN_Controller {

	function __construct(){
		parent::__construct();

		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
		$this->load->model('Domainpricing_model');
		$this->load->model('Common_model');
	}

	public function index()
	{
		$data['summary'] = array();
		$this->load->view('whmazadmin/domain_pricing_list', $data);
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
