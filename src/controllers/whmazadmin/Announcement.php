<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Announcement extends WHMAZADMIN_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('Announcement_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	public function index()
	{
		$data['results'] = array();
		$this->load->view('whmazadmin/announcement_list', $data);
	}

	public function manage($id_val = null)
	{
		if( $this->input->post() ){
			$this->form_validation->set_rules('title', 'Title', 'required|trim');
			$this->form_validation->set_message('title', 'Title is required');

			$this->form_validation->set_rules('description', 'Description', 'required|trim');
			$this->form_validation->set_message('description', 'Description is required');

			if ($this->form_validation->run() == true){

				$form_data = array(
					'id'			=> safe_decode($this->input->post('id')),
					'title'	=> $this->input->post('title'),
					'tags'=> $this->input->post('tags'),
					'description'=> $this->input->post('description'),
					'slug'=> $this->input->post('slug'),
					'publish_date'=> $this->input->post('publish_date'),
					'is_published'=> $this->input->post('is_published') ? 1 : 0,
					'status'       	=> 1
				);

				if( intval($form_data['id']) > 0 ){
					$oldEntity = $this->Announcement_model->getDetail(safe_decode($id_val));

					if( $form_data['is_published'] == 1 && empty($oldEntity['publish_date']) ){
						$form_data['publish_date'] = getDateOnly();
					}

					$form_data['updated_on'] = getDateTime();
					$form_data['updated_by'] = getAdminId();

					$form_data['inserted_on'] = $oldEntity['inserted_on'];
					$form_data['inserted_by'] = $oldEntity['inserted_by'];
				} else {
					$form_data['inserted_on'] = getDateTime();
					$form_data['inserted_by'] = getAdminId();
				}

				if($this->Announcement_model->saveData($form_data)){
					$this->session->set_flashdata('alert_success', 'Announcement has been saved successfully.');
					redirect("whmazadmin/announcement/index");
				}else {
					$this->session->set_flashdata('alert_error', 'Something went wrong. Try again');
				}
			}

		}

		if( !empty($id_val) ){
			$data['detail'] = $this->Announcement_model->getDetail(safe_decode($id_val));
		} else {
			$data['detail'] = array();
		}

		$this->load->view('whmazadmin/announcement_manage', $data);
	}

	public function delete_records($id_val)
	{
		$entity = $this->Announcement_model->getDetail(safe_decode($id_val));
		$entity["status"] = 0;
		$entity["deleted_on"] = getDateTime();
		$entity["deleted_by"] = getAdminId();

		$this->Announcement_model->saveData($entity);
		$this->session->set_flashdata('alert_success', 'Announcement has been deleted successfully.');

		redirect('whmazadmin/announcement/index');
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

			$sqlQuery = ssp_sql_query($params, "announcements", $bindings, $where);

			$data = $this->Announcement_model->getDataTableRecords($sqlQuery, $bindings);

			$response = array(
				"draw"            => !empty( $params['draw'] ) ? intval($params['draw']) : 0,
				"recordsTotal"    => intval( $this->Announcement_model->countDataTableTotalRecords() ),
				"recordsFiltered" => intval( $this->Announcement_model->countDataTableFilterRecords($where, $bindings) ),
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
