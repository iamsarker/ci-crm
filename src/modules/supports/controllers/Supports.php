<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Supports extends WHMAZ_Controller {

	function __construct(){
		parent::__construct();
		$this->load->model('Support_model');
		$this->load->model('Common_model');
	}

	public function KB()
	{
		$data['cats'] = $this->Support_model->loadKBCatList(-1);
		$data['results'] = $this->Support_model->loadKBList(-1);
		$this->load->view('support_kb_list', $data);
	}


	public function view_kb($id, $slug)
	{
		$data['cats'] = $this->Support_model->loadKBCatList(-1);
		$data['details'] = $this->Support_model->loadKbDetails($id, $slug);
		$this->load->view('support_kb_details', $data);
	}


	public function announcements()
	{
		$data['cats'] = $this->Support_model->loadKBCatList(-1);
		$data['results'] = $this->Support_model->loadAnnouncements(-1);
		$this->load->view('support_announcement_list', $data);
	}


	public function view_announcement($id, $slug)
	{
		$data['cats'] = $this->Support_model->loadKBCatList(-1);
		$data['details'] = $this->Support_model->loadAnnouncementDetail($id, $slug);
		$this->load->view('support_announcement_detail', $data);
	}

}
