<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Company extends WHMAZADMIN_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('Company_model');
		$this->load->model('Common_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	public function index()
	{
		$data['results'] = $this->Company_model->loadAllData();
		$this->load->view('whmazadmin/company_list', $data);
	}

	public function manage($id_val = null)
	{
		if( $this->input->post() ){
			$this->form_validation->set_rules('name', 'Name', 'required|trim');
			$this->form_validation->set_message('name', 'Name is required');

			$this->form_validation->set_rules('mobile', 'mobile', 'required|trim');
			$this->form_validation->set_message('mobile', 'mobile is required');

			$this->form_validation->set_rules('email', 'email', 'required|trim');
			$this->form_validation->set_message('email', 'email is required');

			$this->form_validation->set_rules('zip_code', 'zip code', 'required|trim');
			$this->form_validation->set_message('zip_code', 'zip code is required');

			$this->form_validation->set_rules('city', 'city', 'required|trim');
			$this->form_validation->set_message('city', 'city is required');

			$this->form_validation->set_rules('state', 'state', 'required|trim');
			$this->form_validation->set_message('state', 'state is required');

			$this->form_validation->set_rules('address', 'address', 'required|trim');
			$this->form_validation->set_message('address', 'address is required');

			$this->form_validation->set_rules('country', 'country', 'required|trim');
			$this->form_validation->set_message('country', 'country is required');

			if ($this->form_validation->run() == true){

				$form_data = array(
					'id'			=> safe_decode($this->input->post('id')),
					'name'			=> $this->input->post('name'),
					'first_name'	=> $this->input->post('first_name'),
					'last_name'		=> $this->input->post('last_name'),
					'mobile'		=> $this->input->post('mobile'),
					'email'			=> $this->input->post('email'),
					'phone'			=> $this->input->post('phone'),
					'city'			=> $this->input->post('city'),
					'state'			=> $this->input->post('state'),
					'zip_code'		=> $this->input->post('zip_code'),
					'address'		=> $this->input->post('address'),
					'country'		=> $this->input->post('country'),
					'status'       	=> 1
				);

				if( strlen($form_data['id']) > 0 ){
					$oldEntity = $this->Company_model->getDetail(safe_decode($id_val));
					$form_data['updated_on'] = getDateTime();
					$form_data['updated_by'] = getAdminId();

					$form_data['inserted_on'] = $oldEntity['inserted_on'];
					$form_data['inserted_by'] = $oldEntity['inserted_by'];
				} else {
					$form_data['inserted_on'] = getDateTime();
					$form_data['inserted_by'] = getAdminId();
				}

				$resp = $this->Company_model->saveData($form_data);
				if($resp){

					if( $form_data['id'] == 0 ){
						$user['first_name'] = $form_data['first_name'];
						$user['last_name'] = $form_data['last_name'];
						$user['email'] = $form_data['email'];
						$user['mobile'] = $form_data['mobile'];
						$user['phone'] = $form_data['phone'];
						$user['designation'] = 'Company Owner';
						$user['password'] = password_hash('AbXy@2018', PASSWORD_DEFAULT);
						$user['company_id'] = $resp['id'];
						$user['user_type'] = '0'; // owner
						$user['status'] = '1'; // active
						$user['login_try'] = '0';
						$user['inserted_on'] = getDateTime();
						$user['inserted_by'] = $form_data['inserted_by'];
						$this->Common_model->save("users", $user);
					}

					$this->session->set_flashdata('alert_success', 'Customer has been saved successfully.');
					redirect("whmazadmin/company/index");
				}else {
					$this->session->set_flashdata('alert_error', 'Something went wrong. Try again');
				}
			}

		}

		if( !empty($id_val) ){
			$data['detail'] = $this->Company_model->getDetail(safe_decode($id_val));
		} else {
			$data['detail'] = array();
		}

		$data['countries'] = $this->Common_model->generate_dropdown('countries','country_name','country_name');

		$this->load->view('whmazadmin/company_manage', $data);
	}

	public function delete_records($id_val)
	{
		$entity = $this->Company_model->getDetail(safe_decode($id_val));
		$entity["status"] = 0;
		$entity["deleted_on"] = getDateTime();
		$entity["deleted_by"] = getAdminId();

		$this->Company_model->saveData($entity);
		$this->session->set_flashdata('alert_success', 'Customer has been deleted successfully.');

		redirect('whmazadmin/company/index');
	}

}
