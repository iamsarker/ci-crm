<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Package extends WHMAZADMIN_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('Package_model');
		$this->load->model('Common_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	public function index()
	{
		$data['summary'] = array();
		$this->load->view('whmazadmin/ticket_list', $data);
	}


	public function ssp_list_api()
	{
		$this->processRestCall();
		$params = $this->input->get();

		$bindings = array();
		$where = array();
		$columns = array(
			array( 'db' => 'title', 'dt' => 0 ),
			array( 'db' => 'priority',  'dt' => 1 ),
			array( 'db' => 'flag',   'dt' => 2 ),
			array( 'db' => 'status', 'dt' => 3 ),
			array( 'db' => 'inserted_on','dt' => 4,
				'formatter' => function( $d, $row ) {
					return date( 'd-m-Y', strtotime($d));
				}
			),
			array( 'db' => 'id', 'dt' => 5 )
		);

		$sqlQuery = ssp_sql_query($params, "product_services", $columns, $bindings, $where);
		$data = $this->Package_model->getDataTableRecords($sqlQuery, $bindings);

		echo json_encode(array(
			"draw"            => !empty( $params['draw'] ) ? $params['draw'] : 0,
			"recordsTotal"    => intval( $this->Package_model->countDataTableTotalRecords() ),
			"recordsFiltered" => intval( $this->Package_model->countDataTableFilterRecords($where) ),
			"data"            => $data
		));
	}

	public function filter_api()
	{
		$this->processRestCall();
		$rqData = $this->input->post();
		echo json_encode(buildSuccessResponse($this->Package_model->filterData($rqData), "OK"));
	}

	public function prices()
	{
		$this->processRestCall();
		$rqData = $this->input->post();
		echo json_encode(buildSuccessResponse($this->Package_model->priceData($rqData), "OK"));
	}


}
