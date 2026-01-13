<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Report extends WHMAZ_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('Common_model');
		if (!$this->isLogin()) {
			redirect('/auth/login', 'refresh');
		}
	}

	// http://localhost/ci-crm/report/invoice/view/9fc36180-024c-46eb-bfb4-2bf784af82a4
	// http://localhost/ci-crm/report/invoice/download/9fc36180-024c-46eb-bfb4-2bf784af82a4

	function invoice($type, $uuid){
		$this->load->library('Pdf');

		if( $type == 'view' ){
			$this->pdf->load_view('mypdf');
		}

		if( $type == 'download' ){
			$this->pdf->download_view('mypdf');
		}
	}
}

