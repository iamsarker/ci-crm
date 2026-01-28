<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Email_template extends WHMAZADMIN_Controller {

	function __construct() {
		parent::__construct();
		$this->load->model('Emailtemplate_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	public function index() {
		$data['results'] = array();
		$this->load->view('whmazadmin/email_template_list', $data);
	}

	public function manage($id_val = null) {
		if ($this->input->post()) {
			$this->form_validation->set_rules('template_key', 'Template Key', 'required|trim|alpha_dash');
			$this->form_validation->set_rules('template_name', 'Template Name', 'required|trim');
			$this->form_validation->set_rules('subject', 'Subject', 'required|trim');
			$this->form_validation->set_rules('category', 'Category', 'required|trim');
			$this->form_validation->set_rules('body', 'Body', 'required');

			if ($this->form_validation->run() == true) {

				$id = intval(safe_decode($this->input->post('id')));
				$template_key = $this->input->post('template_key');

				// Check duplicate template_key
				if ($this->Emailtemplate_model->isKeyExists($template_key, $id)) {
					$this->session->set_flashdata('alert_error', 'Template key "' . htmlspecialchars($template_key) . '" already exists.');
					redirect('whmazadmin/email_template/manage' . (!empty($id_val) ? '/' . $id_val : ''));
					return;
				}

				$form_data = array(
					'id'            => $id,
					'template_key'  => $template_key,
					'template_name' => $this->input->post('template_name'),
					'subject'       => $this->input->post('subject'),
					'body'          => $this->input->post('body'),
					'category'      => $this->input->post('category'),
					'status'        => $this->input->post('status') ? 1 : 0,
				);

				if ($id > 0) {
					$form_data['updated_on'] = getDateTime();
					$form_data['updated_by'] = getAdminId();
				} else {
					$form_data['inserted_on'] = getDateTime();
					$form_data['inserted_by'] = getAdminId();
				}

				$resp = $this->Emailtemplate_model->saveData($form_data);
				if ($resp['success'] == 1) {
					$this->session->set_flashdata('alert_success', 'Email template has been saved successfully.');
					redirect('whmazadmin/email_template/index');
				} else {
					$this->session->set_flashdata('alert_error', 'Something went wrong. Please try again.');
				}
			} else {
				$this->session->set_flashdata('alert_error', 'Validation error. Please check the form.');
			}
		}

		if (!empty($id_val)) {
			$data['detail'] = $this->Emailtemplate_model->getDetail(safe_decode($id_val));
		} else {
			$data['detail'] = array();
		}

		$this->load->view('whmazadmin/email_template_manage', $data);
	}

	public function delete_records($id_val) {
		$resp = $this->Emailtemplate_model->deleteData(safe_decode($id_val));
		if ($resp['success'] == 1) {
			$this->session->set_flashdata('alert_success', 'Email template has been deleted successfully.');
		} else {
			$this->session->set_flashdata('alert_error', 'Failed to delete email template.');
		}
		redirect('whmazadmin/email_template/index');
	}

	public function ssp_list_api() {
		$this->processRestCall();

		header('Content-Type: application/json');

		try {
			$params = $this->input->get();

			$bindings = array();
			$where = '';

			$sqlQuery = ssp_sql_query($params, "email_templates", $bindings, $where, "deleted_on IS NULL");

			$data = $this->Emailtemplate_model->getDataTableRecords($sqlQuery, $bindings);

			$response = array(
				"draw"            => !empty($params['draw']) ? intval($params['draw']) : 0,
				"recordsTotal"    => intval($this->Emailtemplate_model->countDataTableTotalRecords()),
				"recordsFiltered" => intval($this->Emailtemplate_model->countDataTableFilterRecords($where, $bindings)),
				"data"            => $data
			);

			echo json_encode($response);
			exit;

		} catch (Exception $e) {
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
