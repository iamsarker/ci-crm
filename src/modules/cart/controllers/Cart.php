<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cart extends WHMAZ_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('Common_model');
		$this->load->model('Cart_model');
		$this->load->model('Order_model');
		$this->load->model('Appsetting_model');
	}

	public function view()
	{
		$type = NULL;
		$data['currency'] = $this->Cart_model->getCurrencies();
		$data['dom_prices'] = $this->Cart_model->getDomPricing();
		$data['services'] = $this->Cart_model->getServiceGroups();
		$data['currency'] = $this->Cart_model->getCurrencies();
		$data['cart_list'] = $this->Cart_model->getCartListData();
		$data['payment_gateway_list'] = $this->Common_model->get_data("payment_gateway");
		$data['type'] = $type;
		$this->load->view('view_card', $data);

	}

	function delete($id)
	{
		//delete employee record
		$this->db->where('id', $id);
		$this->db->delete('add_to_carts');
		echo json_encode("OK");
	}

	function delete_all()
	{
		$userId = getCustomerId();
		$sessionId = getCustomerSessionId();

		$this->Cart_model->deleteAllCarts($userId, $sessionId);

		echo json_encode("OK");
	}


	function checkout()
	{
		$userId = getCustomerId();
		if( $userId > 0 ){

			$type = NULL;
			$data['currency'] = $this->Cart_model->getCurrencies();
			$data['dom_prices'] = $this->Cart_model->getDomPricing();
			$data['services'] = $this->Cart_model->getServiceGroups();
			$data['currency'] = $this->Cart_model->getCurrencies();
			$data['cart_list'] = $this->Cart_model->getCartListData();
			$data['type'] = $type;
			$this->load->view('view_checkout', $data);

		} else{
			redirect( base_url()."auth/login?redirect-url=".base_url()."cart/checkout");
		}
	}


	function checkoutSubmit()
	{
		$userId = getCustomerId();
		$companyId = getCompanyId();
		$sessionId = getCustomerSessionId();

		if( $userId > 0 ){
			$this->processRestCall();
			$postData = $this->input->post();

			$cartList = $this->Cart_model->getCartListData();

			if( !empty($cartList) ){

				$vatAmount = 0.0;
				$taxAmount = 0.0;
				$totalAmount = 0.0;

				foreach ($cartList as $key => $row) {
					$vatAmount += $row['tax'];
					$taxAmount += $row['vat'];
					$totalAmount += $row['total'];
				}

				$grandTotal = $totalAmount + $vatAmount + $taxAmount;

				$order['order_uuid'] = gen_uuid();
				$order['order_no'] = $this->Order_model->generateNumber('ORDER');
				$order['company_id'] = $companyId;
				$order['currency_id'] = getCurrencyId();
				$order['currency_code'] = getCurrencyCode();
				$order['order_date'] = getDateAddDay(0);
				$order['amount'] = $totalAmount;
				$order['vat_amount'] = $vatAmount;
				$order['tax_amount'] = $taxAmount;
				$order['coupon_code'] = $postData['promo_code'];
				$order['coupon_amount'] = 0.0;
				$order['discount_amount'] = 0.0;
				$order['total_amount'] = $grandTotal;
				$order['payment_gateway_id'] = $postData['payment_gateway'];
				$order['remarks'] = '';
				$order['instructions'] = $postData['instructions'];
				$order['inserted_on'] = getDateTime();
				$order['inserted_by'] = $userId;

				$orderId = $this->Order_model->saveOrder($order);

				$invoice['invoice_uuid'] = gen_uuid();
				$invoice['company_id'] = $companyId;
				$invoice['order_id'] = $orderId;
				$invoice['currency_id'] = $order['currency_id'];
				$invoice['currency_code'] = $order['currency_code'];
				$invoice['invoice_no'] = $this->Order_model->generateNumber('INVOICE');
				$invoice['sub_total'] = $totalAmount;
				$invoice['tax'] = $taxAmount;
				$invoice['vat'] = $vatAmount;
				$invoice['total'] = $grandTotal;
				$invoice['order_date'] = getDateAddDay(0);
				$invoice['due_date'] = getDateAddDay(0);
				$invoice['status'] = 1;
				$invoice['pay_status'] = 'DUE';
				$invoice['inserted_on'] = getDateTime();
				$invoice['inserted_by'] = $userId;

				$invoiceId = $this->Order_model->saveInvoice($invoice);
				$invoice['id'] = $invoiceId;

				foreach ($cartList as $key => $row) {
					$billingCycle = $this->Common_model->get_data_by_id("billing_cycle", $row['billing_cycle_id']);

					$item['order_id'] = $orderId;
					$item['company_id'] = $userId;
					$item['first_pay_amount'] = $row['total'];
					$item['recurring_amount'] = $row['total'];
					$item['is_synced'] = 1;
					$item['remarks'] = "";
					$item['reg_date'] = getDateAddDay(0);
					$item['exp_date'] = getDateAddDay($billingCycle->cycle_days);
					$item['next_due_date'] = getDateAddDay($billingCycle->cycle_days);
					$item['inserted_on'] = getDateTime();
					$item['inserted_by'] = $userId;

					$invoiceItem['invoice_id'] = $invoiceId;
					$invoiceItem['item'] = $row['note'];
					$invoiceItem['item_desc'] = $row['note'].' - '.$row['hosting_domain'];
					$invoiceItem['tax'] = $row['tax'];
					$invoiceItem['vat'] = $row['vat'];
					$invoiceItem['sub_total'] = $row['sub_total'];
					$invoiceItem['total'] = $row['total'];
					$invoiceItem['item_type'] = $row['item_type'];
					$invoiceItem['inserted_on'] = getDateTime();
					$invoiceItem['inserted_by'] = $userId;

					if( $row['item_type'] == 1 ){ // 1=domain, 2=product_service
						$item['dom_pricing_id'] = $row['dom_pricing_id'];
						$item['reg_period'] = 1; // 1 year by default
						$item['status'] = 0; // 0=pending reg, 1=active, 2=expired, 3=grace, 4=cancelled, 5=pending transfer

						$this->Order_model->saveOrderDomain($item);

					} else {
						$item['billing_cycle_id'] = $row['billing_cycle_id'];
						$item['status'] = 0; // 0=pending, 1=active, 2=expired, 3=suspended, 4=terminated
						$item['hosting_domain'] = $row['hosting_domain'];
						$item['description'] = $row['note'];
						$item['product_service_pricing_id'] = $row['product_service_pricing_id'];
						$item['product_service_pricing_id'] = $row['product_service_pricing_id'];

						$this->Order_model->saveOrderService($item);
					}

					$this->Order_model->saveInvoiceItem($invoiceItem);

				}

				$this->Cart_model->deleteAllCarts($userId, $sessionId);

				echo json_encode(buildSuccessResponse($invoice, "Order has been placed successfully"));

			} else {
				echo json_encode(buildFailedResponse("Cart data is not available!"));
			}

		} else{
			echo json_encode(buildFailedResponse("Please login first to proceed the checkout!"));
		}
	}



	public function domain() // type = register, transfer
	{
		$type       = $this->input->get('type');
		$domkeyword = $this->input->get('domkeyword');

		// Get app settings for reCAPTCHA keys
		$app_settings = $this->Appsetting_model->getSettings();
		$data['captcha_site_key'] = !empty($app_settings['captcha_site_key']) ? $app_settings['captcha_site_key'] : '';

		$data['services'] = $this->Cart_model->getServiceGroups();
		$data['dom_prices'] = $this->Cart_model->getDomPricing();
		$data['currency'] = $this->Cart_model->getCurrencies();
		$data['domkeyword'] = $domkeyword;
		$data['type'] = $type;
		$this->load->view('cart_regnewdomain', $data);
	}

	public function domain_search() // type = register, transfer
	{
		$type       = $this->input->get('type');
		$domkeyword = $this->input->get('domkeyword');
		$recaptcha_token = $this->input->get('recaptcha_token');

		$resp_data = array();
		$resp_data['status'] = 0;
		$resp_data['info'] = array();

		// Get app settings for reCAPTCHA keys
		$app_settings = $this->Appsetting_model->getSettings();
		$captcha_site_key = !empty($app_settings['captcha_site_key']) ? $app_settings['captcha_site_key'] : '';
		$captcha_secret_key = !empty($app_settings['captcha_secret_key']) ? $app_settings['captcha_secret_key'] : '';

		// Verify reCAPTCHA if keys are configured
		if (!empty($captcha_site_key) && !empty($captcha_secret_key)) {
			if (empty($recaptcha_token)) {
				$resp_data['error'] = 'Please complete the reCAPTCHA verification.';
				echo json_encode($resp_data);
				return;
			}

			$verify_url = 'https://www.google.com/recaptcha/api/siteverify';
			$verify_data = array(
				'secret' => $captcha_secret_key,
				'response' => $recaptcha_token,
				'remoteip' => $this->input->ip_address()
			);

			$options = array(
				'http' => array(
					'header' => "Content-type: application/x-www-form-urlencoded\r\n",
					'method' => 'POST',
					'content' => http_build_query($verify_data)
				)
			);
			$context = stream_context_create($options);
			$verify_response = file_get_contents($verify_url, false, $context);
			$response_data = json_decode($verify_response, true);

			if (empty($response_data['success']) || $response_data['success'] !== true) {
				$resp_data['error'] = 'reCAPTCHA verification failed. Please try again.';
				echo json_encode($resp_data);
				return;
			}
		}

		try {
			if (!empty($domkeyword)) {
				$domArr = explode(".", $domkeyword);

				$keywrd = $domArr[0];
				$extension = "com";
				$len = count($domArr);
				if ($len > 1) {
					$extension = $domArr[$len - 1];
				}

				// BUGFIX: Use actual extension instead of hardcoded ".com"
				$regVendor = $this->Cart_model->getDomRegister("." . $extension);

				// Validate registrar configuration
				if (empty($regVendor) || empty($regVendor['domain_check_api'])) {
					$resp_data['error'] = 'Domain registrar not configured';
					echo json_encode($resp_data);
					return;
				}

				$priceList = $this->Cart_model->getDomPricing();

				$url = $regVendor['domain_check_api'] . 'auth-userid=' . $regVendor['auth_userid'] . '&api-key=' . $regVendor['auth_apikey'];
				$checkUrl = $url . '&domain-name=' . $keywrd . '&tlds=' . $extension;

				$resp = $this->curlGetRequest($checkUrl);

				$tmp = array();

				// BUGFIX: Don't return early, continue to echo JSON
				if( is_null($resp) || empty($resp) ){
					$resp_data['error'] = 'No response from domain registrar API';
					echo json_encode($resp_data);
					return;
				}

				// Process API response
				// Response format: {"domain.com": {"classkey": "domcno", "status": "available"}}
				foreach( $resp as $domainName => $domainInfo ){
					// Check if domain is available
					if( isset($domainInfo['status']) && $domainInfo['status'] == "available" ){
						// Extract extension from domain name (e.g., "example.com" -> "com")
						$extArr = explode(".", $domainName);
						$cnt = count($extArr);
						$ext = $extArr[$cnt - 1];

						// Get price for this extension from database
						$domPriceObject = $this->getDomainPrice($priceList, ".".$ext);

						// Only add if we have pricing for this extension
						if( !empty($domPriceObject) ){
							$tmp[] = array(
								"name" => $domainName,
								"price" => !empty($domPriceObject["price"]) ? $domPriceObject["price"] : 0.0,
								"domPriceId" => !empty($domPriceObject["id"]) ? $domPriceObject["id"] : 0
							);
						}
					}
				}

				if( !empty($tmp) ) {
					$resp_data['status'] = 1;
				}
				$resp_data['info'] = $tmp;
			}
		} catch (Exception $e) {
			// Log error
			log_message('error', 'Domain Search Error: ' . $e->getMessage());
			ErrorHandler::log_database_error('domain_search', 'API Call', $e->getMessage());
			$resp_data['error'] = 'Domain search failed: ' . $e->getMessage();
		}

		echo json_encode($resp_data);
	}


	public function get_domain_suggestions()
	{
		$domkeyword = $this->input->get('domkeyword');
		$resp_data = $this->domainLiveSuggestions($domkeyword);
		echo json_encode($resp_data);
	}

	private function domainLiveSuggestions($domkeyword = NULL){

		try {
			if (!empty($domkeyword)) {
				$domArr = explode(".", $domkeyword);

				$keywrd = $domArr[0];
				$extension = "com";
				$len = count($domArr);
				if ($len > 1) {
					$extension = $domArr[$len - 1];
				}

				$regVendor = $this->Cart_model->getDomRegister("." . $extension);

				// Validate registrar configuration
				if (empty($regVendor) || empty($regVendor['suggestion_api'])) {
					return array();
				}

				$priceList = $this->Cart_model->getDomPricing();

				$url = $regVendor['suggestion_api'] . 'auth-userid=' . $regVendor['auth_userid'] . '&api-key=' . $regVendor['auth_apikey'];
				$suggsurl = $url . '&keyword=' . $keywrd. '&tld-only='.$extension;

				// BUGFIX: Initialize as empty array instead of array(array())
				$tmp = array();
				$list = $this->curlGetRequest($suggsurl);

				// Log API response for debugging
				log_message('debug', 'Domain Suggestion API Response: ' . json_encode($list));

				// BUGFIX: Return empty array properly
				if( is_null($list) || empty($list) ){
					return array();
				}

				$idx=0;
				foreach( $list as $domainName => $domainInfo ){
					// Check if domain is available
					if( isset($domainInfo['status']) && $domainInfo['status'] == "available" ){

						$extArr = explode(".", $domainName);
						$cnt = count($extArr);
						$ext = $extArr[$cnt - 1];
						$domPriceObject = $this->getDomainPrice($priceList, ".".$ext);

						// Only add if we have pricing for this extension
						if( !empty($domPriceObject) ){
							$tmp[$idx]["name"] = $domainName;
							$tmp[$idx]["transfer"] = !empty($domPriceObject["transfer"]) ? $domPriceObject["transfer"] : 0.0;
							$tmp[$idx]["renewal"] = !empty($domPriceObject["renewal"]) ? $domPriceObject["renewal"] : 0.0;
							$tmp[$idx]["price"] = !empty($domPriceObject["price"]) ? $domPriceObject["price"] : 0.0;
							$tmp[$idx]["domPriceId"] = !empty($domPriceObject["id"]) ? $domPriceObject["id"] : 0;

							$idx++;
						}
					}
				}

				return $tmp;
			}
		} catch (Exception $e) {
			// Log error
			log_message('error', 'Domain Suggestion Error: ' . $e->getMessage());
			ErrorHandler::log_database_error('domainLiveSuggestions', 'API Call', $e->getMessage());
		}

		return array();
	}

	private function getDomainPrice($priceList, $domainExt){
		foreach ($priceList as $row){
			if( $row["extension"] == $domainExt && $row["currency_id"] == getCurrencyId() ){
				return $row;
			}
		}
		return array();
	}

	public function services($type, $title = NULL)
	{

		$data['currency'] = $this->Cart_model->getCurrencies();
		$data['services'] = $this->Cart_model->getServiceGroups();
		$data['items'] = $this->Cart_model->getProductServiceItems($type);
		$data['query_title'] = $title;
		$data['type'] = $type;
		$this->load->view('cart_services', $data);
	}

	public function addToCartAjax($type, $orderId)
	{
		$this->processRestCall();
		$postData = $this->input->post();

		$cartArr = array();
		$cartArr['customer_session_id'] = getCustomerSessionId();
		$cartArr['user_id'] = getCustomerId();
		$cartArr['item_type'] = $type;

		if ($type == 2) {
			$cartArr['product_service_pricing_id'] = $orderId;
			$itemPrice = $this->Cart_model->getCartServicePrice($orderId);
		} else {
			$cartArr['dom_pricing_id'] = $orderId;
			$itemPrice = $this->Cart_model->getCartDomainPrice($orderId);
		}

		$cartArr['note'] = $postData['item'];
		$cartArr['hosting_domain_type'] = $postData['hosting_domain_type'];
		$cartArr['hosting_domain'] = $postData['hosting_domain'];
		$cartArr['sub_total'] = $itemPrice['item_price'];
		$cartArr['billing_cycle_id'] = $itemPrice['billing_cycle_id'];
		$cartArr['billing_cycle'] = $itemPrice['cycle_name'];
		$cartArr['currency_id'] = $itemPrice['currency_id'];
		$cartArr['currency_code'] = getCurrencyCode();
		$cartArr['tax'] = 0;
		$cartArr['vat'] = 0;
		$cartArr['inserted_on'] = getDateTime();
		$cartArr['inserted_by'] = getCustomerId();
		$cartArr['total'] = $itemPrice['item_price'];

		if ($this->Cart_model->saveCart($cartArr)) {
			echo $this->AppResponse(1, "Cart item has been added successfully");
		} else {
			echo $this->AppResponse(0, "Cart item cannot add!");
		}
	}

}
