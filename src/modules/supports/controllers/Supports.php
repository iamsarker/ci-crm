<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Supports extends WHMAZ_Controller {

	private $per_page = 10;

	function __construct(){
		parent::__construct();
		$this->load->model('Support_model');
		$this->load->model('Common_model');
	}

	public function KB($page = 1)
	{
		$page = max(1, intval($page));
		$offset = ($page - 1) * $this->per_page;

		$data['cats'] = $this->Support_model->loadKBCatList(-1);
		$data['results'] = $this->Support_model->loadKBList($this->per_page, $offset);
		$data['total'] = $this->Support_model->countKBList();
		$data['current_page'] = $page;
		$data['per_page'] = $this->per_page;
		$data['total_pages'] = ceil($data['total'] / $this->per_page);
		$data['base_url'] = base_url() . 'supports/KB';

		$this->load->view('support_kb_list', $data);
	}


	public function view_kb($id, $slug)
	{
		$data['cats'] = $this->Support_model->loadKBCatList(-1);
		$data['details'] = $this->Support_model->loadKbDetails($id, $slug);
		$this->load->view('support_kb_details', $data);
	}

	public function kb_category($catId, $slug, $page = 1)
	{
		$page = max(1, intval($page));
		$offset = ($page - 1) * $this->per_page;

		$data['cats'] = $this->Support_model->loadKBCatList(-1);
		$data['results'] = $this->Support_model->loadKBListByCategory($catId, $this->per_page, $offset);
		$data['category'] = $this->Support_model->getKBCategoryById($catId);
		$data['total'] = $this->Support_model->countKBListByCategory($catId);
		$data['current_page'] = $page;
		$data['per_page'] = $this->per_page;
		$data['total_pages'] = ceil($data['total'] / $this->per_page);
		$data['base_url'] = base_url() . 'supports/kb_category/' . $catId . '/' . $slug;

		$this->load->view('support_kb_category', $data);
	}


	public function announcements($page = 1)
	{
		$page = max(1, intval($page));
		$offset = ($page - 1) * $this->per_page;

		$data['archive'] = $this->Support_model->getAnnouncementArchive();
		$data['results'] = $this->Support_model->loadAnnouncements($this->per_page, $offset);
		$data['total'] = $this->Support_model->countAnnouncements();
		$data['current_page'] = $page;
		$data['per_page'] = $this->per_page;
		$data['total_pages'] = ceil($data['total'] / $this->per_page);
		$data['base_url'] = base_url() . 'supports/announcements';

		$this->load->view('support_announcement_list', $data);
	}


	public function view_announcement($id, $slug)
	{
		$data['archive'] = $this->Support_model->getAnnouncementArchive();
		$data['details'] = $this->Support_model->loadAnnouncementDetail($id, $slug);
		$this->load->view('support_announcement_detail', $data);
	}

	public function announcements_archive($year, $month, $page = 1)
	{
		$page = max(1, intval($page));
		$offset = ($page - 1) * $this->per_page;

		$data['archive'] = $this->Support_model->getAnnouncementArchive();
		$data['results'] = $this->Support_model->loadAnnouncementsByMonth($year, $month, $this->per_page, $offset);
		$data['total'] = $this->Support_model->countAnnouncementsByMonth($year, $month);
		$data['current_page'] = $page;
		$data['per_page'] = $this->per_page;
		$data['total_pages'] = $data['total'] > 0 ? ceil($data['total'] / $this->per_page) : 1;
		$data['base_url'] = base_url() . 'supports/announcements_archive/' . $year . '/' . $month;
		$data['year'] = $year;
		$data['month'] = $month;
		$data['month_name'] = date('F Y', mktime(0, 0, 0, $month, 1, $year));

		$this->load->view('support_announcement_archive', $data);
	}

}
