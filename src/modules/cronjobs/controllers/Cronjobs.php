<?php

class Cronjobs extends WHMAZ_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('Clientarea_model');
		$this->load->model('Billing_model');
		$this->load->model('Common_model');
		$this->load->model('Order_model');
		$this->load->model('Support_model');
	}

	function generateInvoice(){
		
	}

}
