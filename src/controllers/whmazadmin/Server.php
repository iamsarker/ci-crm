<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Server extends WHMAZADMIN_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('Server_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	public function index()
	{
		$data['results'] = $this->Server_model->loadAllData();
		$this->load->view('whmazadmin/server_list', $data);
	}

	public function manage($id_val = null)
	{
		if( $this->input->post() ){
			$this->form_validation->set_rules('name', 'Name', 'required|trim');
			$this->form_validation->set_message('name', 'Server name is required');

			$this->form_validation->set_rules('ip_addr', 'IP Address', 'required|trim');
			$this->form_validation->set_message('ip_addr', 'IP Address is required');

			$this->form_validation->set_rules('hostname', 'Hostname', 'required|trim');
			$this->form_validation->set_message('hostname', 'Hostname is required');

			$this->form_validation->set_rules('dns1', 'DNS1', 'required|trim');
			$this->form_validation->set_message('dns1', 'DNS1 is required');

			$this->form_validation->set_rules('dns2', 'DNS2', 'required|trim');
			$this->form_validation->set_message('dns2', 'DNS2 is required');

			$this->form_validation->set_rules('username', 'Username', 'required|trim');
			$this->form_validation->set_message('username', 'Username is required');

			$this->form_validation->set_rules('access_hash', 'Access Hash', 'required|trim');
			$this->form_validation->set_message('access_hash', 'Access Hash is required');

			$this->form_validation->set_rules('port', 'Port', 'required|trim');
			$this->form_validation->set_message('port', 'Port is required');

			if ($this->form_validation->run() == true){

				$form_data = array(
					'id'			=> safe_decode($this->input->post('id')),
					'name'	=> $this->input->post('name'),
					'ip_addr'=> $this->input->post('ip_addr'),
					'hostname'=> $this->input->post('hostname'),
					'dns1'=> $this->input->post('dns1'),
					'dns2'=> $this->input->post('dns2'),
					'dns3'=> $this->input->post('dns3'),
					'dns4'=> $this->input->post('dns4'),
					'dns1_ip'=> $this->input->post('dns1_ip'),
					'dns2_ip'=> $this->input->post('dns2_ip'),
					'dns3_ip'=> $this->input->post('dns3_ip'),
					'dns4_ip'=> $this->input->post('dns4_ip'),
					'username'=> $this->input->post('username'),
					'type'=> $this->input->post('type'),
					'authpass'=> $this->input->post('authpass'),
					'access_hash'=> $this->input->post('access_hash'),
					'port'=> $this->input->post('port'),
					'is_secure'=> $this->input->post('is_secure') ? 1 : 0,
					'noc'=> $this->input->post('noc'),
					'remarks'=> $this->input->post('remarks'),
					'status'       	=> 1
				);

				if( intval($form_data['id']) > 0 ){
					$oldEntity = $this->Server_model->getDetail(safe_decode($id_val));
					$form_data['updated_on'] = getDateTime();
					$form_data['updated_by'] = getAdminId();

					if( $form_data['access_hash'] != $oldEntity['access_hash'] ){
						$form_data['access_hash'] = base64_encode(base64_encode($form_data['access_hash']));
					}

					$form_data['inserted_on'] = $oldEntity['inserted_on'];
					$form_data['inserted_by'] = $oldEntity['inserted_by'];
				} else {
					$form_data['inserted_on'] = getDateTime();
					$form_data['inserted_by'] = getAdminId();
				}

				if($this->Server_model->saveData($form_data)){
					$this->session->set_flashdata('admin_success', 'Server data has been saved successfully.');
					redirect("whmazadmin/server/index");
				}else {
					$this->session->set_flashdata('admin_error', 'Something went wrong. Try again');
				}
			}

		}

		if( !empty($id_val) ){
			$data['detail'] = $this->Server_model->getDetail(safe_decode($id_val));
		} else {
			$data['detail'] = array();
		}

		$this->load->view('whmazadmin/server_manage', $data);
	}

	public function delete_records($id_val)
	{
		$entity = $this->Server_model->getDetail(safe_decode($id_val));
		$entity["status"] = 0;
		$entity["deleted_on"] = getDateTime();
		$entity["deleted_by"] = getAdminId();

		$this->Server_model->saveData($entity);
		$this->session->set_flashdata('admin_success', 'Server data has been deleted successfully.');

		redirect('whmazadmin/server/index');
	}

}
