<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Order extends WHMAZADMIN_Controller
{
	var $img_path;
	var $upload_dir;

	function __construct()
	{
		parent::__construct();
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}

		$this->load->model('Order_model');
		$this->load->model('Common_model');
		$this->load->model('Currency_model');

		$this->img_path = realpath(APPPATH . '../uploadedfiles/billing/');
		$this->upload_dir = realpath(APPPATH . '../uploadedfiles/');
	}

	public function index()
	{
		$data['summary'] = array();
		$data['results'] = array();

		$this->load->view('whmazadmin/order_list', $data);
	}

	public function manage($id_val = null)
	{
		if( $this->input->post() ){
			$this->form_validation->set_rules('company_id', 'Company/customer', 'required|trim');
			$this->form_validation->set_message('company_id', 'Company/customer is required');

			$this->form_validation->set_rules('currency_id', 'currency', 'required|trim');
			$this->form_validation->set_message('currency_id', 'currency is required');

			if( $this->input->post('module_id') > 0 || $this->input->post('server_id') > 0 ){

				$this->form_validation->set_rules('billing_cycle_id', 'billing cycle', 'required|trim');
				$this->form_validation->set_message('billing_cycle_id', 'billing cycle is required');

				$this->form_validation->set_rules('product_service_id', 'package', 'required|trim');
				$this->form_validation->set_message('product_service_id', 'package is required');
			}

			if( $this->input->post('dom_register_id') > 0 ){
				$this->form_validation->set_rules('domain', 'domain', 'required|trim');
				$this->form_validation->set_message('domain', 'domain is required');
			}

			if( $this->input->post('product_service_id') == "" && $this->input->post('domain') == "" ){
				$this->form_validation->set_rules('product_service_id', 'package', 'required|trim');
				$this->form_validation->set_message('product_service_id', 'domain or hosting is required');

				$this->form_validation->set_rules('domain', 'domain', 'required|trim');
				$this->form_validation->set_message('domain', 'domain or hosting is required');
			}

			if ($this->form_validation->run() == true){

				$form_data = array(
					'id'					=> safe_decode($this->input->post('id')),
					'company_id'			=> $this->input->post('company_id'),
					'currency_id'			=> $this->input->post('currency_id'),
					'billing_cycle_id'		=> $this->input->post('billing_cycle_id'),
					'module_id'				=> $this->input->post('module_id'),
					'server_id'				=> $this->input->post('server_id'),
					'payment_gateway_id'	=> $this->input->post('payment_gateway_id'),
					'product_service_group_id'	=> $this->input->post('product_service_group_id'),
					'product_service_id'	=> $this->input->post('product_service_id'),
					'dom_register_id'		=> $this->input->post('dom_register_id'),
					'order_type'			=> $this->input->post('order_type'),
					'domain'				=> $this->input->post('domain'),
					'epp_code'				=> $this->input->post('epp_code'),
					'remarks'				=> $this->input->post('remarks'),
					'package_amount'		=> $this->input->post('package_amount'),
					'domain_amount'			=> $this->input->post('domain_amount'),
					'sub_total'				=> $this->input->post('sub_total'),
					'coupon_code'			=> $this->input->post('coupon_code'),
					'coupon_amount'			=> $this->input->post('coupon_amount'),
					'discount_amount'		=> $this->input->post('discount_amount'),
					'total_amount'			=> $this->input->post('total_amount'),
					'reg_period'			=> $this->input->post('reg_period'),
					'has_notification'		=> $this->input->post('has_notification') ? 1 : 0,
					'need_api_call'			=> $this->input->post('need_api_call') ? 1 : 0,
				);

				$order = $this->saveOrderTable($form_data);
				$this->saveOrderItemTable($order, $form_data);

				if( !empty($form_data['billing_cycle_id']) && $form_data['billing_cycle_id'] > 0 ){
					$billingCycle = $this->Common_model->get_data_by_id("billing_cycle", $form_data['billing_cycle_id']);
				} else {
					$billingCycle = array();
				}

				$invoice = $this->saveInvoiceTable($order, $billingCycle);
				$this->saveInvoiceItemTable($invoice, $form_data, $billingCycle);

				if( $order['id'] > 0 && $invoice['id'] > 0 ){
					$this->session->set_flashdata('alert_success', 'Order has been saved successfully.');
					redirect("whmazadmin/order/index");
				} else {
					$this->session->set_flashdata('alert_error', 'Something went wrong. Try again');
				}
			}

		}

		if( !empty($id_val) && safe_decode($id_val) > 0 ){
			$data['detail'] = $this->Order_model->getDetail(safe_decode($id_val));
		} else {
			$data['detail'] = array();
		}

		$data['companies'] = $this->Common_model->generate_dropdown('companies', 'id', "name", "first_name", "last_name");
		$data['currencies'] = $this->Common_model->generate_dropdown('currencies', 'id', "code");
		$data['billing_cycles'] = $this->Common_model->generate_dropdown('billing_cycle', 'id', "cycle_name");
		$data['servers'] = $this->Common_model->generate_dropdown('servers', 'id', "name", "hostname");
		$data['modules'] = $this->Common_model->generate_dropdown('product_service_modules', 'id', "module_name");
		$data['service_groups'] = $this->Common_model->generate_dropdown('product_service_groups', 'id', "group_name");
		$data['payment_gateways'] = $this->Common_model->generate_dropdown('payment_gateway', 'id', "name");

		$data['dom_registers'] = $this->Common_model->generate_dropdown('dom_registers', 'id', "name");

		$this->load->view('whmazadmin/order_manage', $data);
	}

	public function saveOrderTable($form_data){
		$order = array();

		if( intval($form_data['id']) > 0 ){
			$order = $this->Order_model->getDetail($form_data['id']);
			$order['updated_on'] = getDateTime();
			$order['updated_by'] = getAdminId();

			$order['inserted_on'] = $order['inserted_on'];
			$order['inserted_by'] = $order['inserted_by'];
		} else {
			$order['inserted_on'] = getDateTime();
			$order['inserted_by'] = getAdminId();
			$order['status'] = 1;
		}

		$currency =$this->Currency_model->getDetail($form_data['currency_id']);

		$order['order_uuid'] = gen_uuid();
		$order['order_no'] = $this->Order_model->generateNumber('ORDER');
		$order['company_id'] = $form_data['company_id'];
		$order['currency_id'] = $form_data['currency_id'];
		$order['currency_code'] = $currency['code'];
		$order['payment_gateway_id'] = !empty($form_data['payment_gateway_id']) ? $form_data['payment_gateway_id'] : 0;
		$order['order_date'] = getDateAddDay(0);
		$order['amount'] = empty($form_data['sub_total']) ? 0 : $form_data['sub_total'];
		$order['vat_amount'] = 0.0;
		$order['tax_amount'] = 0.0;
		$order['coupon_code'] = $form_data['coupon_code'];
		$order['coupon_amount'] = empty($form_data['coupon_amount']) ? 0 : $form_data['coupon_amount'];
		$order['discount_amount'] = empty($form_data['discount_amount']) ? 0 : $form_data['discount_amount'];
		$order['total_amount'] = empty($form_data['total_amount']) ? 0 : $form_data['total_amount'];
		$order['remarks'] = $form_data['remarks'];
		$order['has_notification'] = $form_data['has_notification'];
		$order['need_api_call'] = $form_data['need_api_call'];
		$order['instructions'] = '';

		$orderId = $this->Order_model->saveOrder($order);
		$order['id'] = $orderId;

		return $order;
	}

	public function saveOrderItemTable($order, $form_data){

		$item['order_id'] = $order['id'];
		$item['company_id'] = $order['company_id'];
		$item['is_synced'] = 0;
		$item['remarks'] = "";
		$item['reg_date'] = getDateAddDay(0);
		$item['exp_date'] = getDateAddYear($form_data['reg_period']);
		$item['next_due_date'] = getDateAddYear($form_data['reg_period']);
		$item['inserted_on'] = getDateTime();
		$item['inserted_by'] = getAdminId();

		$domain_name = $form_data['domain'];
		$domain_array = explode(".", $domain_name);
		if ( count($domain_array) == 3 ){
			$extension = '.'.$domain_array[1].'.'.$domain_array[2];
		} else if ( count($domain_array) == 2 ){
			$extension = '.'.$domain_array[1];
		} else {
			$extension = "";
		}

		$domain_prices = $this->Common_model->getDomainPrices($form_data['currency_id'], $form_data['reg_period'], $extension);

		if( !empty($form_data['domain']) ){ // order_domain
			$domain_arr = $item;
			$domain_arr['domain'] = $form_data['domain'];
			$domain_arr['epp_code'] = !empty($form_data['epp_code']) ? $form_data['epp_code'] : '';
			$domain_arr['reg_period'] = $form_data['reg_period'];
			$domain_arr['dom_register_id'] = $form_data['dom_register_id'];
			$domain_arr['dom_pricing_id'] = !empty($domain_prices) ? $domain_prices['id'] : 0;
			$domain_arr['recurring_amount'] = !empty($domain_prices) ? $domain_prices['renewal'] : 0;
			$domain_arr['first_pay_amount'] = $form_data['domain_amount'];

			 // domain status; 0=pending reg, 1=active, 2=expired, 3=grace, 4=cancelled, 5=pending transfer

			if( $form_data['order_type'] == 3 ){
				$domain_arr['reg_period'] = 1;
				$domain_arr['status'] = 1;

			} else if( $form_data['order_type'] == 2 ){
				$domain_arr['status'] = 5;

			} else if( $form_data['order_type'] == 1 ){
				$domain_arr['status'] = 0;

			}

			$this->Order_model->saveOrderDomain($domain_arr);

		}

		if ( !empty($form_data['product_service_id']) ){
			$service_arr = $item;

			$hostingPrices = $this->Common_model->getHostingPrices($form_data['currency_id'], $form_data['product_service_id'], $form_data['billing_cycle_id']);

			$service_arr['billing_cycle_id'] = $form_data['billing_cycle_id'];
			$service_arr['status'] = 0; // 0=pending, 1=active, 2=expired, 3=suspended, 4=terminated
			$service_arr['hosting_domain'] = $form_data['domain'];
			$service_arr['first_pay_amount'] = $form_data['package_amount'];
			$service_arr['recurring_amount'] = $hostingPrices['price'];
			$service_arr['description'] = '';
			$service_arr['product_service_pricing_id'] = $hostingPrices['id'];
			$service_arr['product_service_id'] = $form_data['product_service_id'];

			$this->Order_model->saveOrderService($service_arr);
		}
	}

	public function saveInvoiceTable($order, $billing_cycle){
		$invoice['invoice_uuid'] = gen_uuid();
		$invoice['company_id'] = $order['company_id'];
		$invoice['order_id'] = $order['id'];
		$invoice['currency_id'] = $order['currency_id'];
		$invoice['currency_code'] = $order['currency_code'];
		$invoice['invoice_no'] = $this->Order_model->generateNumber('INVOICE');
		$invoice['sub_total'] = $order['amount'];
		$invoice['tax'] = 0.0;
		$invoice['vat'] = 0.0;
		$invoice['total'] = $order['total_amount'];
		$invoice['order_date'] = getDateAddDay(0);
		$invoice['due_date'] = getDateAddDay($billing_cycle->cycle_days);
		$invoice['status'] = 1;
		$invoice['pay_status'] = 'DUE';
		$invoice['need_api_call'] = $order['need_api_call'];
		$invoice['inserted_on'] = $order['inserted_on'];
		$invoice['inserted_by'] = $order['inserted_by'];

		$invoiceId = $this->Order_model->saveInvoice($invoice);
		$invoice['id'] = $invoiceId;

		return $invoice;
	}

	public function saveInvoiceItemTable($invoice, $form_data, $billingCycle){

		$invoiceItem['invoice_id'] = $invoice['id'];

		if( !empty($form_data['domain']) && $form_data['domain'] != "") {
			$invoiceItem['item'] = 'Domain registration';
			$invoiceItem['item_desc'] = $form_data['domain'] . ' - ' . $form_data['reg_period'] . ' year(s)';

			$invoiceItem['tax'] = 0.0;
			$invoiceItem['vat'] = 0.0;
			$invoiceItem['sub_total'] = $form_data['domain_amount'];
			$invoiceItem['total'] = $form_data['domain_amount'];
			$invoiceItem['item_type'] = 1; // domain
			$invoiceItem['inserted_on'] = getDateTime();
			$invoiceItem['inserted_by'] = $invoice['inserted_by'];

			$this->Order_model->saveInvoiceItem($invoiceItem);
		}

		if( !empty($form_data['product_service_id']) && $form_data['product_service_id'] > 0 ) {
			$package = $this->Common_model->get_data_by_id("product_services", $form_data['product_service_id']);

			$invoiceItem['item'] = 'Hosting package';
			$invoiceItem['item_desc'] = $billingCycle->cycle_name . ' ' . $package->product_name . ' for ' . $form_data['domain'] . ' domain';

			$invoiceItem['tax'] = 0.0;
			$invoiceItem['vat'] = 0.0;
			$invoiceItem['sub_total'] = $form_data['package_amount'];
			$invoiceItem['total'] = $form_data['package_amount'];
			$invoiceItem['item_type'] = 2; // product service or hosting
			$invoiceItem['inserted_on'] = getDateTime();

			$this->Order_model->saveInvoiceItem($invoiceItem);
		}
	}

	public function delete_records($id_val)
	{
		$entity = $this->Expensecategory_model->getDetail(safe_decode($id_val));
		$entity["status"] = 0;
		$entity["deleted_on"] = getDateTime();
		$entity["deleted_by"] = getAdminId();

		$this->Expensecategory_model->saveData($entity);
		$this->session->set_flashdata('alert_success', 'Order has been deleted successfully.');

		redirect('whmazadmin/order/index');
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

			$sqlQuery = ssp_sql_query($params, "order_view", $bindings, $where);

			$data = $this->Order_model->getDataTableRecords($sqlQuery, $bindings);

			$response = array(
				"draw"            => !empty( $params['draw'] ) ? intval($params['draw']) : 0,
				"recordsTotal"    => intval( $this->Order_model->countDataTableTotalRecords() ),
				"recordsFiltered" => intval( $this->Order_model->countDataTableFilterRecords($where, $bindings) ),
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
		echo json_encode($this->Order_model->loadOrderList(-1, $rqData['limit']));
	}


}
