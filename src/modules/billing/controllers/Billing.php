<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Billing extends WHMAZ_Controller
{
	var $img_path;
	var $upload_dir;

	function __construct()
	{
		parent::__construct();
		$this->load->model('Billing_model');
		$this->load->model('Common_model');

		if (!$this->isLogin()) {
			redirect('/auth/login', 'refresh');
		}
		$this->img_path = realpath(APPPATH . '../uploadedfiles/billing/');
		$this->upload_dir = realpath(APPPATH . '../uploadedfiles/');
	}

	public function invoice_list_api()
	{
		$this->processRestCall();
		$rqData = $this->input->post();
		echo json_encode($this->Billing_model->loadInvoiceList(getCompanyId(), $rqData['limit']));
	}

	public function invoices()
	{
		$companyId = getCompanyId();
		$data['summary'] = $this->Billing_model->invoiceSummary($companyId)[0];
		$data['results'] = $this->Billing_model->loadInvoiceList($companyId, -1);

		$this->load->view('billing_invoices', $data);
	}


	public function view_invoice($invoice_uuid)
	{
		$companyId = getCompanyId();
		$data['companyInfo'] = $this->Common_model->get_sys_config("COMPANY_INFO");
		$data['summary'] = $this->Billing_model->invoiceSummary($companyId)[0];
		$data['invoice'] = $this->Billing_model->getInvoiceByUuid($invoice_uuid, $companyId);
		$data['invoiceItems'] = $this->Billing_model->getInvoiceItems($data['invoice']['id']);
		$data['logoBase64'] = convertImageToBase65($this->upload_dir.'/'.$data['companyInfo']['CompanyLogo']->cnf_val);
		$data['txnHistory'] = array();
		$data['viewMode'] = "HTML";

		$htmlData = $this->load->view('billing_invoice_pdf_html', $data, TRUE);
		$data['htmlData'] = $htmlData;

		$this->load->view('billing_viewinvoice', $data);
	}

	public function download_invoice($invoice_uuid)
	{
		$this->load->library('Pdf');

		$companyId = getCompanyId();

		$data['companyInfo'] = $this->Common_model->get_sys_config("COMPANY_INFO");
		$data['summary'] = $this->Billing_model->invoiceSummary($companyId)[0];
		$data['invoice'] = $this->Billing_model->getInvoiceByUuid($invoice_uuid, $companyId);
		$data['invoiceItems'] = $this->Billing_model->getInvoiceItems($data['invoice']['id']);
		$data['logoBase64'] = convertImageToBase65($this->upload_dir.'/'.$data['companyInfo']['CompanyLogo']->cnf_val);
		$data['txnHistory'] = array();
		$data['viewMode'] = "PDF";

		$this->pdf->download_view('billing_invoice_pdf_html', $data, "Invoice-".$data['invoice']['invoice_no'].".pdf");
	}


	public function quotes()
	{
		$companyId = getCompanyId();
		$data['summary'] = $this->Billing_model->invoiceSummary($companyId)[0];
		$data['results'] = $this->Billing_model->loadInvoiceList($companyId, -1);

		$this->load->view('billing_quotes', $data);
	}


}
