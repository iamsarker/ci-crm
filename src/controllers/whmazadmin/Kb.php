<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kb extends WHMAZADMIN_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('Kb_model');
		$this->load->model('Kbcat_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	public function index()
	{
		$data['results'] = $this->Kb_model->loadAllData();
		$this->load->view('whmazadmin/kb_list', $data);
	}

	public function manage($id_val = null)
	{
		if( $this->input->post() ){
			$this->form_validation->set_rules('title', 'Title', 'required|trim');
			$this->form_validation->set_message('title', 'Title is required');

			$this->form_validation->set_rules('article', 'Article', 'required|trim');
			$this->form_validation->set_message('article', 'Article is required');

			if ($this->form_validation->run() == true){

				$form_data = array(
					'id'			=> safe_decode($this->input->post('id')),
					'title'	=> $this->input->post('title'),
					'tags'=> $this->input->post('tags'),
					'article'=> $this->input->post('article'),
					'slug'=> $this->input->post('slug'),
					'sort_order'=> $this->input->post('sort_order'),
					'is_hidden'=> $this->input->post('is_hidden') ? 1 : 0,
					'status'       	=> 1
				);
				$kb_cat_ids = $this->input->post('kb_cat_id');

				$pkId = 0;
				if( intval($form_data['id']) > 0 ){
					$oldEntity = $this->Kb_model->getDetail(safe_decode($id_val));
					$pkId = $oldEntity['id'];
					$form_data['updated_on'] = getDateTime();
					$form_data['updated_by'] = getAdminId();

					$form_data['inserted_on'] = $oldEntity['inserted_on'];
					$form_data['inserted_by'] = $oldEntity['inserted_by'];
				} else {
					$form_data['inserted_on'] = getDateTime();
					$form_data['inserted_by'] = getAdminId();
				}

				if($this->Kb_model->saveData($form_data)){
					if( $pkId == 0 ){
						$pkId = $this->Kb_model->getLastId();
					}

					foreach( $kb_cat_ids as $cat_id ){
						$cat_data = array();
						$cat_data['kb_id'] = $pkId;
						$cat_data['kb_cat_id'] = $cat_id;
						$this->Kb_model->saveMappingData($cat_data);
					}

					$this->session->set_flashdata('alert', successAlert('KB has been saved successfully.'));
					redirect("whmazadmin/kb/index");
				}
			} else {
				$this->session->set_flashdata('alert', errorAlert('Validation error. Try again'));
			}

		}

		if( !empty($id_val) ){
			$data['detail'] = $this->Kb_model->getDetail(safe_decode($id_val));
		} else {
			$data['detail'] = array();
		}
		$data['categories'] = $this->Kbcat_model->loadAllData();

		$this->load->view('whmazadmin/kb_manage', $data);
	}

	public function delete_records($id_val)
	{
		$entity = $this->Kb_model->getDetail(safe_decode($id_val));
		$entity["status"] = 0;
		$entity["deleted_on"] = getDateTime();
		$entity["deleted_by"] = getAdminId();

		$this->Kb_model->saveData($entity);
		$this->session->set_flashdata('alert', successAlert('KB has been deleted successfully.'));

		redirect('whmazadmin/kb/index');
	}

}
