<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Invoice extends WHMAZADMIN_Controller
{
	var $img_path;
	var $upload_dir;

	function __construct()
	{
		parent::__construct();
		$this->load->model('Billing_model');
		$this->load->model('Common_model');
		$this->load->model('Invoice_model');
		$this->load->model('Appsetting_model');

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
		$data['companyInfo'] = $this->Appsetting_model->getSettings();
		$data['summary'] = $this->Billing_model->invoiceSummary($companyId)[0];
		$data['invoice'] = $this->Billing_model->getInvoiceByUuid($invoice_uuid, $companyId);
		$data['invoiceItems'] = $this->Billing_model->getInvoiceItems($data['invoice']['id']);
		$logoPath = !empty($data['companyInfo']['logo']) ? $this->upload_dir.'/mics/'.$data['companyInfo']['logo'] : '';
		$data['logoBase64'] = !empty($logoPath) && file_exists($logoPath) ? convertImageToBase65($logoPath) : '';
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

		$data['companyInfo'] = $this->Appsetting_model->getSettings();
		$data['summary'] = $this->Billing_model->invoiceSummary($companyId)[0];
		$data['invoice'] = $this->Billing_model->getInvoiceByUuid($invoice_uuid, $companyId);
		$data['invoiceItems'] = $this->Billing_model->getInvoiceItems($data['invoice']['id']);
		$logoPath = !empty($data['companyInfo']['logo']) ? $this->upload_dir.'/mics/'.$data['companyInfo']['logo'] : '';
		$data['logoBase64'] = !empty($logoPath) && file_exists($logoPath) ? convertImageToBase65($logoPath) : '';
		$data['txnHistory'] = array();
		$data['viewMode'] = "PDF";

		$this->pdf->download_view('whmazadmin/invoice_pdf_html', $data, "Invoice-".$data['invoice']['invoice_no'].".pdf");
	}

	public function ssp_list_api($tmpCompanyId=null)
	{
		$this->processRestCall();

		// Set proper JSON headers
		header('Content-Type: application/json');

		try {
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
			$where = '';

			$sqlQuery = ssp_sql_query($params, "invoice_view", $bindings, $where);

			$data = $this->Invoice_model->getDataTableRecords($sqlQuery, $bindings);

			$response = array(
				"draw"            => !empty( $params['draw'] ) ? intval($params['draw']) : 0,
				"recordsTotal"    => intval( $this->Invoice_model->countDataTableTotalRecords() ),
				"recordsFiltered" => intval( $this->Invoice_model->countDataTableFilterRecords($where, $bindings) ),
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


	public function recent_list_api()
	{
		$this->processRestCall();
		$rqData = $this->input->post();
		echo json_encode($this->Billing_model->loadInvoiceList(-1, $rqData['limit']));
	}

	public function mark_as_paid()
	{
		$this->processRestCall();

		// Set proper JSON headers
		header('Content-Type: application/json');

		try {
			$invoice_uuid = $this->input->post('invoice_uuid');

			// Validate input
			if (empty($invoice_uuid)) {
				echo json_encode(array(
					'success' => false,
					'message' => 'Invoice UUID is required'
				));
				exit;
			}

			// Update invoice status to PAID
			$updated = $this->Invoice_model->updateInvoiceStatus($invoice_uuid, 'PAID', getAdminId());

			if ($updated) {
				echo json_encode(array(
					'success' => true,
					'message' => 'Invoice marked as paid successfully'
				));
			} else {
				echo json_encode(array(
					'success' => false,
					'message' => 'Failed to update invoice or invoice not found'
				));
			}
			exit;

		} catch (Exception $e) {
			echo json_encode(array(
				'success' => false,
				'message' => 'Error: ' . $e->getMessage()
			));
			exit;
		}
	}


}
