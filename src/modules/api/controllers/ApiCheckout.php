<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * ApiCheckout — place the order for the acting customer's cart.
 *
 *   POST /api/v1/checkout    [orders:write]
 *
 * Reuses the storefront checkoutSubmit() verbatim: after establishing the
 * customer session it delegates to `cart/checkoutSubmit`, which builds the
 * order + invoice + items from `add_to_carts` (all pending / DUE — nothing is
 * provisioned yet). Pay + provision afterwards via POST /api/v1/invoices/pay/{uuid}.
 *
 * Body: { customer_id?, currency_id?, payment_gateway, instructions? }
 * (payment_gateway + instructions are read by checkoutSubmit from the body.)
 */
class ApiCheckout extends API_Controller
{
	public function index()
	{
		$this->requireMethod('POST');
		$this->requireScope('orders:write');
		$this->actAsCustomer();

		if ($this->param('payment_gateway') === null) {
			$this->fail(422, 'payment_gateway is required.', 'validation_error');
		}
		$this->delegate('cart/checkoutSubmit');
	}
}
