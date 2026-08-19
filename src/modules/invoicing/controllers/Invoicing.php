<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Invoicing extends WHMAZ_Controller
{
	var $img_path;
	var $upload_dir;

	function __construct()
	{
		parent::__construct();
		$this->load->model('Billing_model');
		$this->load->model('Common_model');
		$this->load->model('Appsetting_model');

		if (!$this->isLogin()) {
			redirect('/auth/login', 'refresh');
		}
		$this->img_path = realpath(APPPATH . '../uploadedfiles/billing/');
		$this->upload_dir = realpath(APPPATH . '../uploadedfiles/');
	}

	/**
	 * Resolve the logo shown on invoices (view + PDF) as a base64 data URI.
	 * Prefers the logo uploaded in Settings -> General; falls back to the
	 * bundled resources/assets/img/logo.png so invoices are never logo-less.
	 *
	 * @param  array  $companyInfo app_settings row
	 * @return string data URI, or '' when no readable logo exists
	 */
	private function _invoiceLogoBase64($companyInfo)
	{
		// Use basename() to prevent path traversal attacks
		$logoFilename = !empty($companyInfo['logo']) ? basename($companyInfo['logo']) : '';
		$logoPath = !empty($logoFilename) ? $this->upload_dir . '/mics/' . $logoFilename : '';

		if (empty($logoPath) || !file_exists($logoPath)) {
			$logoPath = realpath(APPPATH . '../resources/assets/img/logo.png');
		}

		return (!empty($logoPath) && file_exists($logoPath)) ? convertImageToBase65($logoPath) : '';
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

		$this->load->view('invoicing_invoices', $data);
	}


	public function view_invoice($invoice_uuid)
	{
		$companyId = getCompanyId();
		$data['companyInfo'] = $this->Appsetting_model->getSettings();
		$data['summary'] = $this->Billing_model->invoiceSummary($companyId)[0];
		$data['invoice'] = $this->Billing_model->getInvoiceByUuid($invoice_uuid, $companyId);
		// Check if invoice exists
		if (empty($data['invoice']) || empty($data['invoice']['id'])) {
			$this->session->set_flashdata('alert_error', 'Invoice not found.');
			redirect('invoicing/invoices');
			return;
		}

		$data['invoiceItems'] = $this->Billing_model->getInvoiceItems($data['invoice']['id']);
		$data['logoBase64'] = $this->_invoiceLogoBase64($data['companyInfo']);
		$data['txnHistory'] = array();
		$data['viewMode'] = "HTML";

		$htmlData = $this->load->view('invoicing_invoice_pdf_html', $data, TRUE);
		$data['htmlData'] = $htmlData;

		$this->load->view('invoicing_viewinvoice', $data);
	}

	public function download_invoice($invoice_uuid)
	{
		$this->load->library('Pdf');

		$companyId = getCompanyId();

		$data['companyInfo'] = $this->Appsetting_model->getSettings();
		$data['summary'] = $this->Billing_model->invoiceSummary($companyId)[0];
		$data['invoice'] = $this->Billing_model->getInvoiceByUuid($invoice_uuid, $companyId);

		// Check if invoice exists
		if (empty($data['invoice']) || empty($data['invoice']['id'])) {
			$this->session->set_flashdata('alert_error', 'Invoice not found.');
			redirect('invoicing/invoices');
			return;
		}

		$data['invoiceItems'] = $this->Billing_model->getInvoiceItems($data['invoice']['id']);
		$data['logoBase64'] = $this->_invoiceLogoBase64($data['companyInfo']);
		$data['txnHistory'] = array();
		$data['viewMode'] = "PDF";

		$this->pdf->download_view('invoicing_invoice_pdf_html', $data, "Invoice-".$data['invoice']['invoice_no'].".pdf");
	}

}
