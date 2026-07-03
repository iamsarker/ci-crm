<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * ApiCart — cart operations for the reseller API (URL segment: /api/v1/cart/...).
 *
 * A thin wrapper that REUSES the storefront cart code verbatim. Per request it
 * establishes a customer session for the acting company (the reseller itself or
 * an owned sub-customer) via API_Controller::actAsCustomer(), then delegates to
 * the identical `cart` module controller methods through Modules::run(). No cart
 * logic is re-implemented here.
 *
 * The cart persists in `add_to_carts` keyed by the customer's user_id, so the
 * add → link → checkout calls compose across separate stateless requests as long
 * as they target the same customer_id. Placing the order is ApiCheckout.
 *
 *   GET  /api/v1/cart                          view cart              [orders:read]
 *   POST /api/v1/cart/add_domain               add domain             [domains:write]
 *   POST /api/v1/cart/add_hosting              add hosting            [hosting:write]
 *   POST /api/v1/cart/add_software             add software license   [licenses:write]
 *   POST /api/v1/cart/link_domain_to_hosting   attach domain to host  [domains:write]
 *   POST /api/v1/cart/link_hosting_to_domain   attach host to domain  [hosting:write]
 *   POST /api/v1/cart/delete/{id}              remove item            [orders:write]
 *
 * Request bodies mirror the storefront endpoints (see docs/RESELLER_API.md),
 * plus an optional `customer_id` (defaults to the reseller) and `currency_id`.
 */
class ApiCart extends API_Controller
{
	public function index()
	{
		$this->requireScope('orders:read');
		$this->actAsCustomer();
		$this->delegate('cart/getCartWithLinkedItems');
	}

	public function add_domain()
	{
		$this->requireMethod('POST');
		$this->requireScope('domains:write');
		$this->actAsCustomer();
		$this->delegate('cart/addDomainToCart');
	}

	public function add_hosting()
	{
		$this->requireMethod('POST');
		$this->requireScope('hosting:write');
		$this->actAsCustomer();
		$this->delegate('cart/addHostingToCart');
	}

	public function add_software()
	{
		$this->requireMethod('POST');
		$this->requireScope('licenses:write');
		$this->actAsCustomer();
		$this->delegate('cart/addSoftwareToCart');
	}

	public function link_domain_to_hosting()
	{
		$this->requireMethod('POST');
		$this->requireScope('domains:write');
		$this->actAsCustomer();
		$this->delegate('cart/linkDomainToHosting');
	}

	public function link_hosting_to_domain()
	{
		$this->requireMethod('POST');
		$this->requireScope('hosting:write');
		$this->actAsCustomer();
		$this->delegate('cart/linkHostingToDomain');
	}

	public function delete($id = 0)
	{
		$this->requireMethod('POST');
		$this->requireScope('orders:write');
		$this->actAsCustomer();
		$this->delegate('cart/delete', intval($id));
	}
}
