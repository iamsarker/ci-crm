<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends WHMAZADMIN_Controller
{
	function __construct()
	{
		parent::__construct();
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}

		$this->load->model('Dashboard_model');
	}

	public function index()
	{
		$this->load->view('whmazadmin/dashboard_index');
	}

	public function summary_api() {
		echo json_encode($this->Dashboard_model->loadSummaryData());
	}

}
