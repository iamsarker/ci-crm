<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Server_module extends WHMAZADMIN_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('Servermodule_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	public function index()
	{
		$data['results'] = $this->Servermodule_model->loadAllData();
		$this->load->view('whmazadmin/server_module_list', $data);
	}

	/**
	 * AJAX: Toggle module status (active/inactive)
	 */
	public function toggle_status_api()
	{
		header('Content-Type: application/json');

		$id = safe_decode($this->input->post('id'));
		if (empty($id) || !is_numeric($id) || $id <= 0) {
			echo json_encode(array('success' => false, 'message' => 'Invalid module ID'));
			exit;
		}

		$result = $this->Servermodule_model->toggleStatus($id);
		if ($result) {
			echo json_encode(array('success' => true, 'message' => 'Module status updated successfully'));
		} else {
			echo json_encode(array('success' => false, 'message' => 'Failed to update status'));
		}
		exit;
	}

}
