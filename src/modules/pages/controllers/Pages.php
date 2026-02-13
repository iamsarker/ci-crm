<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pages extends WHMAZ_Controller {

	function __construct() {
		parent::__construct();
		$this->load->library('session');
		$this->load->helper('whmaz');
		$this->load->model('Page_model');
	}

	public function index($slug = null) {
		if (empty($slug)) {
			show_404();
			return;
		}

		$page = $this->Page_model->getBySlug($slug);

		if (empty($page)) {
			show_404();
			return;
		}

		// Increment view count
		$this->Page_model->incrementView($page['id']);

		$data['page'] = $page;
		$this->load->view('page_view', $data);
	}
}
