<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Invoice extends WHMAZADMIN_Controller
{
	var $img_path;

	function __construct()
	{
		parent::__construct();
		$this->load->model('Billing_model');
		$this->load->model('Common_model');
		$this->load->model('Invoice_model');

		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
		$this->img_path = realpath(APPPATH . '../uploadedfiles/billing/');
		$this->upload_dir = realpath(APPPATH . '../uploadedfiles/');
	}

	public function index()
	{
		$data['summary'] = array();
		$data['results'] = array();

		$this->load->view('whmazadmin/invoice_list', $data);
	}


	public function view_invoice($companyId, $invoice_uuid)
	{
		$data['companyInfo'] = $this->Common_model->get_sys_config("COMPANY_INFO");
		$data['summary'] = $this->Billing_model->invoiceSummary($companyId)[0];
		$data['invoice'] = $this->Billing_model->getInvoiceByUuid($invoice_uuid, $companyId);
		$data['invoiceItems'] = $this->Billing_model->getInvoiceItems($data['invoice']['id']);
		$data['logoBase64'] = convertImageToBase65($this->upload_dir.'/'.$data['companyInfo']['CompanyLogo']->cnf_val);
		$data['txnHistory'] = array();
		$data['viewMode'] = "HTML";

		$htmlData = $this->load->view('whmazadmin/invoice_pdf_html', $data, TRUE);
		$data['htmlData'] = $htmlData;
		$data['company_id'] = $companyId;
		$data['invoice_uuid'] = $invoice_uuid;

		$this->load->view('whmazadmin/invoice_view', $data);
	}

	public function download_invoice($companyId, $invoice_uuid)
	{
		$this->load->library('Pdf');

		$data['companyInfo'] = $this->Common_model->get_sys_config("COMPANY_INFO");
		$data['summary'] = $this->Billing_model->invoiceSummary($companyId)[0];
		$data['invoice'] = $this->Billing_model->getInvoiceByUuid($invoice_uuid, $companyId);
		$data['invoiceItems'] = $this->Billing_model->getInvoiceItems($data['invoice']['id']);
		$data['logoBase64'] = convertImageToBase65($this->upload_dir.'/'.$data['companyInfo']['CompanyLogo']->cnf_val);
		$data['txnHistory'] = array();
		$data['viewMode'] = "PDF";

		$this->pdf->download_view('whmazadmin/invoice_pdf_html', $data, "Invoice-".$data['invoice']['invoice_no'].".pdf");
	}

	public function ssp_list_api($tmpCompanyId=null)
	{
		$this->processRestCall();
		$params = $this->input->get();

		$companyId = !empty($tmpCompanyId) ? safe_decode($tmpCompanyId) : 0;

		if( $companyId > 0 ){
			for ( $i=0 ; $i<count($params["columns"]) ; $i++){
				if( $params["columns"][$i]['data'] == "company_id" ){
					$params["columns"][$i]["search"]["value"] = $companyId;
					break;
				}
			}
		}

		$bindings = array();
		$where = array();

		$sqlQuery = ssp_sql_query($params, "invoice_view",$bindings, $where);

		$data = $this->Invoice_model->getDataTableRecords($sqlQuery, $bindings);

		echo json_encode(array(
			"draw"            => !empty( $params['draw'] ) ? $params['draw'] : 0,
			"recordsTotal"    => intval( $this->Invoice_model->countDataTableTotalRecords() ),
			"recordsFiltered" => intval( $this->Invoice_model->countDataTableFilterRecords($where, $bindings) ),
			"data"            => $data
		));
	}


	public function recent_list_api()
	{
		$this->processRestCall();
		$rqData = $this->input->post();
		echo json_encode($this->Billing_model->loadInvoiceList(-1, $rqData['limit']));
	}


}
