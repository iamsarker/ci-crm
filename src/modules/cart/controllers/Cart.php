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
		$data['cart_list'] = $this->Cart_model->getCartListWithChildren(); // Hierarchical cart data
		$data['payment_gateway_list'] = $this->Common_model->get_data("payment_gateway");
		$data['type'] = $type;
		$this->load->view('view_card', $data);

	}

	function delete($id)
	{
		// Delete cart item and its linked children
		$this->Cart_model->deleteCartWithChildren($id);
		echo json_encode("OK");
	}

	function delete_all()
	{
		$userId = getCustomerId();
		$sessionId = getCustomerSessionId();

		$this->Cart_model->deleteAllCarts($userId, $sessionId);

		echo json_encode("OK");
	}

	function getCount()
	{
		$count = getCartCount();
		echo json_encode(array('count' => $count));
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

			// Get hierarchical cart data (parents with children)
			$cartList = $this->Cart_model->getCartListWithChildren();

			if( !empty($cartList) ){

				$vatAmount = 0.0;
				$taxAmount = 0.0;
				$totalAmount = 0.0;

				// Calculate totals including children
				foreach ($cartList as $key => $row) {
					$vatAmount += $row['tax'];
					$taxAmount += $row['vat'];
					$totalAmount += $row['total'];

					// Add children totals
					if (!empty($row['children'])) {
						foreach ($row['children'] as $child) {
							$vatAmount += $child['tax'];
							$taxAmount += $child['vat'];
							$totalAmount += $child['total'];
						}
					}
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

				// Process each parent item and its children
				foreach ($cartList as $key => $row) {
					// Process parent item
					$parentRefId = $this->_processCartItem($row, $orderId, $invoiceId, $companyId, $userId);

					// Process children (linked items) if any
					if (!empty($row['children'])) {
						foreach ($row['children'] as $child) {
							$childRefId = $this->_processCartItem($child, $orderId, $invoiceId, $companyId, $userId);

							// Link parent and child together
							if ($parentRefId > 0 && $childRefId > 0) {
								$this->_linkOrderItems($row, $parentRefId, $child, $childRefId);
							}
						}
					}
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

	/**
	 * Process a single cart item (domain or hosting service)
	 * @return int The saved order item ID (order_domains.id or order_services.id)
	 */
	private function _processCartItem($row, $orderId, $invoiceId, $companyId, $userId)
	{
		$billingCycle = $this->Common_model->get_data_by_id("billing_cycle", $row['billing_cycle_id']);
		$cycleDays = !empty($billingCycle->cycle_days) ? $billingCycle->cycle_days : 365;

		$item = array();
		$item['order_id'] = $orderId;
		$item['company_id'] = $companyId;
		$item['first_pay_amount'] = $row['total'];
		$item['recurring_amount'] = $row['total'];
		$item['is_synced'] = 1;
		$item['remarks'] = "";
		$item['reg_date'] = getDateAddDay(0);
		$item['exp_date'] = getDateAddDay($cycleDays);
		$item['due_date'] = getDateAddDay(7);
		$item['next_renewal_date'] = getDateAddDay($cycleDays);
		$item['suspension_date'] = null;
		$item['suspension_reason'] = null;
		$item['termination_date'] = null;
		$item['auto_renew'] = 1;
		$item['inserted_on'] = getDateTime();
		$item['inserted_by'] = $userId;

		$refId = 0;

		// Invoice item data
		$invoiceItem = array();
		$invoiceItem['invoice_id'] = $invoiceId;
		$invoiceItem['item'] = $row['note'];
		$invoiceItem['item_desc'] = $row['note'] . (!empty($row['hosting_domain']) ? ' - ' . $row['hosting_domain'] : '');
		$invoiceItem['tax'] = $row['tax'];
		$invoiceItem['vat'] = $row['vat'];
		$invoiceItem['sub_total'] = $row['sub_total'];
		$invoiceItem['total'] = $row['total'];
		$invoiceItem['item_type'] = $row['item_type'];
		$invoiceItem['inserted_on'] = getDateTime();
		$invoiceItem['inserted_by'] = $userId;

		if ($row['item_type'] == 1) {
			// Domain item
			$item['dom_pricing_id'] = $row['dom_pricing_id'];
			$item['reg_period'] = 1; // 1 year by default
			$item['domain'] = $row['hosting_domain'];
			$item['epp_code'] = !empty($row['epp_code']) ? $row['epp_code'] : null;

			// Set order_type based on domain_action
			// 1=register, 2=transfer, 3=dns_update (nothing to register)
			$domainAction = !empty($row['domain_action']) ? $row['domain_action'] : 'register';
			if ($domainAction == 'register') {
				$item['order_type'] = 1;
				$item['status'] = 0; // 0=pending registration
			} elseif ($domainAction == 'transfer') {
				$item['order_type'] = 2;
				$item['status'] = 5; // 5=pending transfer
			} else {
				// dns_update - no domain registration needed
				$item['order_type'] = 3;
				$item['status'] = 1; // 1=active (just DNS update)
				$item['is_synced'] = 1;
			}

			$refId = $this->Order_model->saveOrderDomain($item);

		} else {
			// Hosting/Service item
			$item['billing_cycle_id'] = $row['billing_cycle_id'];
			$item['status'] = 0; // 0=pending
			$item['hosting_domain'] = $row['hosting_domain'];
			$item['description'] = $row['note'];
			$item['product_service_id'] = !empty($row['product_service_id']) ? $row['product_service_id'] : 0;
			$item['product_service_pricing_id'] = $row['product_service_pricing_id'];
			$item['product_service_type_key'] = $this->Common_model->getProductServiceTypeKeyByPricingId($row['product_service_pricing_id']);
			$item['is_synced'] = 0; // Will be set to 1 after provisioning

			$refId = $this->Order_model->saveOrderService($item);
		}

		// Save invoice item
		$qty = !empty($row['quantity']) ? intval($row['quantity']) : 1;
		$invoiceItem['ref_id'] = ($refId > 0) ? $refId : null;
		$invoiceItem['billing_cycle_id'] = $row['billing_cycle_id'];
		$invoiceItem['quantity'] = $qty;
		$invoiceItem['unit_price'] = ($qty > 0) ? ($row['sub_total'] / $qty) : $row['sub_total'];
		$invoiceItem['discount'] = 0;
		$invoiceItem['billing_period_start'] = getDateAddDay(0);
		$invoiceItem['billing_period_end'] = ($cycleDays > 0) ? getDateAddDay($cycleDays) : null;

		$this->Order_model->saveInvoiceItem($invoiceItem);

		return $refId;
	}

	/**
	 * Link parent and child order items together
	 */
	private function _linkOrderItems($parent, $parentRefId, $child, $childRefId)
	{
		// Determine which is hosting and which is domain
		if ($parent['item_type'] == 1) {
			// Parent is domain, child is hosting
			$domainId = $parentRefId;
			$serviceId = $childRefId;
		} else {
			// Parent is hosting, child is domain
			$domainId = $childRefId;
			$serviceId = $parentRefId;
		}

		// Update order_services with linked_domain_id
		if ($serviceId > 0 && $domainId > 0) {
			$this->Order_model->updateOrderService($serviceId, array('linked_domain_id' => $domainId));
			$this->Order_model->updateOrderDomain($domainId, array('linked_service_id' => $serviceId));
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

			$verify_url = RECAPTCHA_VERIFY_URL;
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

	public function services($type = 0, $title = NULL)
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
			$cartArr['product_service_id'] = !empty($itemPrice['product_service_id']) ? $itemPrice['product_service_id'] : 0;
		} else {
			$cartArr['dom_pricing_id'] = $orderId;
			$itemPrice = $this->Cart_model->getCartDomainPrice($orderId);
			$cartArr['product_service_id'] = 0;
		}

		$quantity = !empty($postData['quantity']) ? intval($postData['quantity']) : 1;

		$cartArr['note'] = $postData['item'];
		$cartArr['hosting_domain_type'] = $postData['hosting_domain_type'];
		$cartArr['hosting_domain'] = $postData['hosting_domain'];
		$cartArr['sub_total'] = $itemPrice['item_price'] * $quantity;
		$cartArr['billing_cycle_id'] = $itemPrice['billing_cycle_id'];
		$cartArr['billing_cycle'] = $itemPrice['cycle_name'];
		$cartArr['currency_id'] = $itemPrice['currency_id'];
		$cartArr['currency_code'] = getCurrencyCode();
		$cartArr['tax'] = 0;
		$cartArr['vat'] = 0;
		$cartArr['quantity'] = $quantity;
		$cartArr['inserted_on'] = getDateTime();
		$cartArr['inserted_by'] = getCustomerId();
		$cartArr['total'] = $itemPrice['item_price'] * $quantity;

		if ($this->Cart_model->saveCart($cartArr)) {
			echo $this->AppResponse(1, "Cart item has been added successfully");
		} else {
			echo $this->AppResponse(0, "Cart item cannot add!");
		}
	}

	/**
	 * Flow-1: Add Hosting to Cart (Domain Required)
	 * Creates hosting cart item, then requires domain selection
	 *
	 * POST params:
	 * - product_service_pricing_id: Hosting pricing ID
	 * - quantity: Number of items (default 1)
	 */
	public function addHostingToCart()
	{
		$this->processRestCall();
		$postData = $this->input->post();

		$pricingId = !empty($postData['product_service_pricing_id']) ? intval($postData['product_service_pricing_id']) : 0;
		$quantity = !empty($postData['quantity']) ? intval($postData['quantity']) : 1;

		if ($pricingId <= 0) {
			echo json_encode(buildFailedResponse("Invalid hosting package selected"));
			return;
		}

		// Get hosting pricing details
		$itemPrice = $this->Cart_model->getCartServicePrice($pricingId);
		if (empty($itemPrice)) {
			echo json_encode(buildFailedResponse("Hosting package not found"));
			return;
		}

		$pricingDetails = $this->Cart_model->getProductServicePricingById($pricingId);

		// Create hosting cart item
		$cartArr = array(
			'customer_session_id' => getCustomerSessionId(),
			'user_id' => getCustomerId(),
			'parent_cart_id' => null, // This is parent
			'item_type' => 2, // product_service
			'product_service_id' => !empty($itemPrice['product_service_id']) ? $itemPrice['product_service_id'] : 0,
			'product_service_pricing_id' => $pricingId,
			'note' => !empty($pricingDetails['product_name']) ? $pricingDetails['product_name'] : 'Hosting Package',
			'hosting_domain' => null, // Will be set when domain is added
			'hosting_domain_type' => 0,
			'domain_action' => null,
			'sub_total' => $itemPrice['item_price'] * $quantity,
			'billing_cycle_id' => $itemPrice['billing_cycle_id'],
			'billing_cycle' => $itemPrice['cycle_name'],
			'currency_id' => $itemPrice['currency_id'],
			'currency_code' => getCurrencyCode(),
			'tax' => 0,
			'vat' => 0,
			'quantity' => $quantity,
			'total' => $itemPrice['item_price'] * $quantity,
			'inserted_on' => getDateTime(),
			'inserted_by' => getCustomerId()
		);

		$cartId = $this->Cart_model->saveCart($cartArr);

		if ($cartId) {
			echo json_encode(buildSuccessResponse(
				array(
					'cart_id' => $cartId,
					'requires_domain' => true,
					'hosting_type' => !empty($pricingDetails['service_type_key']) ? $pricingDetails['service_type_key'] : 'shared'
				),
				"Hosting added to cart. Please select a domain."
			));
		} else {
			echo json_encode(buildFailedResponse("Failed to add hosting to cart"));
		}
	}

	/**
	 * Flow-1 Part 2: Link Domain to Hosting Cart Item
	 *
	 * POST params:
	 * - parent_cart_id: The hosting cart ID
	 * - domain_action: 'register', 'transfer', 'dns_update'
	 * - domain_name: The domain name
	 * - epp_code: EPP/Auth code (required for transfer)
	 * - dom_pricing_id: Domain pricing ID (required for register/transfer)
	 */
	public function linkDomainToHosting()
	{
		$this->processRestCall();
		$postData = $this->input->post();

		$parentCartId = !empty($postData['parent_cart_id']) ? intval($postData['parent_cart_id']) : 0;
		$domainAction = !empty($postData['domain_action']) ? $postData['domain_action'] : '';
		$domainName = !empty($postData['domain_name']) ? trim($postData['domain_name']) : '';
		$eppCode = !empty($postData['epp_code']) ? trim($postData['epp_code']) : null;
		$domPricingId = !empty($postData['dom_pricing_id']) ? intval($postData['dom_pricing_id']) : 0;

		// Validate inputs
		if ($parentCartId <= 0) {
			echo json_encode(buildFailedResponse("Invalid hosting cart ID"));
			return;
		}

		if (empty($domainName)) {
			echo json_encode(buildFailedResponse("Domain name is required"));
			return;
		}

		if (!in_array($domainAction, array('register', 'transfer', 'dns_update'))) {
			echo json_encode(buildFailedResponse("Invalid domain action"));
			return;
		}

		// Check parent cart exists
		$parentCart = $this->Cart_model->getCartById($parentCartId);
		if (empty($parentCart) || $parentCart['item_type'] != 2) {
			echo json_encode(buildFailedResponse("Hosting cart item not found"));
			return;
		}

		// Validate transfer requires EPP code
		if ($domainAction == 'transfer' && empty($eppCode)) {
			echo json_encode(buildFailedResponse("EPP/Auth code is required for domain transfer"));
			return;
		}

		// For register/transfer, validate domain pricing
		$domainTotal = 0;
		$billingCycleId = 1; // Default yearly for domains
		$billingCycle = 'Yearly';

		if ($domainAction == 'register' || $domainAction == 'transfer') {
			if ($domPricingId <= 0) {
				echo json_encode(buildFailedResponse("Domain pricing is required"));
				return;
			}

			$domPrice = $this->Cart_model->getCartDomainPrice($domPricingId);
			if (empty($domPrice)) {
				echo json_encode(buildFailedResponse("Domain pricing not found"));
				return;
			}

			$domainTotal = $domainAction == 'transfer' ?
				(!empty($domPrice['transfer']) ? $domPrice['transfer'] : $domPrice['item_price']) :
				$domPrice['item_price'];

			$billingCycleId = !empty($domPrice['billing_cycle_id']) ? $domPrice['billing_cycle_id'] : 1;
		}

		// Map domain_action to hosting_domain_type for legacy compatibility
		$hostingDomainType = 0; // DNS
		if ($domainAction == 'register') $hostingDomainType = 1;
		if ($domainAction == 'transfer') $hostingDomainType = 2;

		// Create domain cart item linked to hosting
		$domainCart = array(
			'customer_session_id' => getCustomerSessionId(),
			'user_id' => getCustomerId(),
			'parent_cart_id' => $parentCartId,
			'item_type' => 1, // domain
			'product_service_id' => 0,
			'dom_pricing_id' => $domPricingId,
			'note' => ucfirst($domainAction) . ': ' . $domainName,
			'hosting_domain' => $domainName,
			'hosting_domain_type' => $hostingDomainType,
			'domain_action' => $domainAction,
			'epp_code' => $eppCode,
			'sub_total' => $domainTotal,
			'billing_cycle_id' => $billingCycleId,
			'billing_cycle' => $billingCycle,
			'currency_id' => getCurrencyId(),
			'currency_code' => getCurrencyCode(),
			'tax' => 0,
			'vat' => 0,
			'quantity' => 1,
			'total' => $domainTotal,
			'inserted_on' => getDateTime(),
			'inserted_by' => getCustomerId()
		);

		$domainCartId = $this->Cart_model->saveCart($domainCart);

		if ($domainCartId) {
			// Update parent hosting cart with domain name
			$this->Cart_model->updateCart($parentCartId, array(
				'hosting_domain' => $domainName,
				'hosting_domain_type' => $hostingDomainType,
				'domain_action' => $domainAction,
				'updated_on' => getDateTime()
			));

			echo json_encode(buildSuccessResponse(
				array('domain_cart_id' => $domainCartId),
				"Domain linked to hosting successfully"
			));
		} else {
			echo json_encode(buildFailedResponse("Failed to add domain to cart"));
		}
	}

	/**
	 * Flow-2: Add Domain to Cart (Hosting Optional)
	 *
	 * POST params:
	 * - domain_action: 'register', 'transfer'
	 * - domain_name: The domain name
	 * - dom_pricing_id: Domain pricing ID
	 * - epp_code: EPP/Auth code (required for transfer)
	 */
	public function addDomainToCart()
	{
		$this->processRestCall();
		$postData = $this->input->post();

		$domainAction = !empty($postData['domain_action']) ? $postData['domain_action'] : '';
		$domainName = !empty($postData['domain_name']) ? trim($postData['domain_name']) : '';
		$domPricingId = !empty($postData['dom_pricing_id']) ? intval($postData['dom_pricing_id']) : 0;
		$eppCode = !empty($postData['epp_code']) ? trim($postData['epp_code']) : null;

		// Validate inputs
		if (empty($domainName)) {
			echo json_encode(buildFailedResponse("Domain name is required"));
			return;
		}

		if (!in_array($domainAction, array('register', 'transfer'))) {
			echo json_encode(buildFailedResponse("Invalid domain action. Use 'register' or 'transfer'"));
			return;
		}

		if ($domPricingId <= 0) {
			echo json_encode(buildFailedResponse("Domain pricing is required"));
			return;
		}

		// Validate transfer requires EPP code
		if ($domainAction == 'transfer' && empty($eppCode)) {
			echo json_encode(buildFailedResponse("EPP/Auth code is required for domain transfer"));
			return;
		}

		// Get domain pricing
		$domPrice = $this->Cart_model->getCartDomainPrice($domPricingId);
		if (empty($domPrice)) {
			echo json_encode(buildFailedResponse("Domain pricing not found"));
			return;
		}

		$domainTotal = $domainAction == 'transfer' ?
			(!empty($domPrice['transfer']) ? $domPrice['transfer'] : $domPrice['item_price']) :
			$domPrice['item_price'];

		// Map domain_action to hosting_domain_type
		$hostingDomainType = $domainAction == 'register' ? 1 : 2;

		// Create domain cart item (standalone, no parent)
		$domainCart = array(
			'customer_session_id' => getCustomerSessionId(),
			'user_id' => getCustomerId(),
			'parent_cart_id' => null, // Standalone domain
			'item_type' => 1, // domain
			'product_service_id' => 0,
			'dom_pricing_id' => $domPricingId,
			'note' => ucfirst($domainAction) . ': ' . $domainName,
			'hosting_domain' => $domainName,
			'hosting_domain_type' => $hostingDomainType,
			'domain_action' => $domainAction,
			'epp_code' => $eppCode,
			'sub_total' => $domainTotal,
			'billing_cycle_id' => !empty($domPrice['billing_cycle_id']) ? $domPrice['billing_cycle_id'] : 1,
			'billing_cycle' => 'Yearly',
			'currency_id' => getCurrencyId(),
			'currency_code' => getCurrencyCode(),
			'tax' => 0,
			'vat' => 0,
			'quantity' => 1,
			'total' => $domainTotal,
			'inserted_on' => getDateTime(),
			'inserted_by' => getCustomerId()
		);

		$domainCartId = $this->Cart_model->saveCart($domainCart);

		if ($domainCartId) {
			echo json_encode(buildSuccessResponse(
				array(
					'cart_id' => $domainCartId,
					'show_hosting_options' => true
				),
				"Domain added to cart. You can optionally add hosting."
			));
		} else {
			echo json_encode(buildFailedResponse("Failed to add domain to cart"));
		}
	}

	/**
	 * Flow-2 Part 2: Link Hosting to Domain Cart Item (Optional)
	 *
	 * POST params:
	 * - parent_cart_id: The domain cart ID
	 * - product_service_pricing_id: Hosting pricing ID
	 * - quantity: Number of items (default 1)
	 */
	public function linkHostingToDomain()
	{
		$this->processRestCall();
		$postData = $this->input->post();

		$parentCartId = !empty($postData['parent_cart_id']) ? intval($postData['parent_cart_id']) : 0;
		$pricingId = !empty($postData['product_service_pricing_id']) ? intval($postData['product_service_pricing_id']) : 0;
		$quantity = !empty($postData['quantity']) ? intval($postData['quantity']) : 1;

		// Validate inputs
		if ($parentCartId <= 0) {
			echo json_encode(buildFailedResponse("Invalid domain cart ID"));
			return;
		}

		if ($pricingId <= 0) {
			echo json_encode(buildFailedResponse("Invalid hosting package selected"));
			return;
		}

		// Check parent cart exists and is a domain
		$parentCart = $this->Cart_model->getCartById($parentCartId);
		if (empty($parentCart) || $parentCart['item_type'] != 1) {
			echo json_encode(buildFailedResponse("Domain cart item not found"));
			return;
		}

		// Get hosting pricing details
		$itemPrice = $this->Cart_model->getCartServicePrice($pricingId);
		if (empty($itemPrice)) {
			echo json_encode(buildFailedResponse("Hosting package not found"));
			return;
		}

		$pricingDetails = $this->Cart_model->getProductServicePricingById($pricingId);

		// Create hosting cart item linked to domain
		$hostingCart = array(
			'customer_session_id' => getCustomerSessionId(),
			'user_id' => getCustomerId(),
			'parent_cart_id' => $parentCartId,
			'item_type' => 2, // product_service
			'product_service_id' => !empty($itemPrice['product_service_id']) ? $itemPrice['product_service_id'] : 0,
			'product_service_pricing_id' => $pricingId,
			'note' => !empty($pricingDetails['product_name']) ? $pricingDetails['product_name'] : 'Hosting Package',
			'hosting_domain' => $parentCart['hosting_domain'], // Copy domain from parent
			'hosting_domain_type' => $parentCart['hosting_domain_type'],
			'domain_action' => $parentCart['domain_action'],
			'sub_total' => $itemPrice['item_price'] * $quantity,
			'billing_cycle_id' => $itemPrice['billing_cycle_id'],
			'billing_cycle' => $itemPrice['cycle_name'],
			'currency_id' => $itemPrice['currency_id'],
			'currency_code' => getCurrencyCode(),
			'tax' => 0,
			'vat' => 0,
			'quantity' => $quantity,
			'total' => $itemPrice['item_price'] * $quantity,
			'inserted_on' => getDateTime(),
			'inserted_by' => getCustomerId()
		);

		$hostingCartId = $this->Cart_model->saveCart($hostingCart);

		if ($hostingCartId) {
			echo json_encode(buildSuccessResponse(
				array('hosting_cart_id' => $hostingCartId),
				"Hosting linked to domain successfully"
			));
		} else {
			echo json_encode(buildFailedResponse("Failed to add hosting to cart"));
		}
	}

	/**
	 * Get cart list with hierarchical structure
	 * Returns items grouped by parent-child relationship
	 */
	public function getCartWithLinkedItems()
	{
		$cartList = $this->Cart_model->getCartListWithChildren();
		echo json_encode(buildSuccessResponse($cartList));
	}

}
