<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * ApiOrder — read the reseller's orders.
 *
 *   GET /api/v1/orders             list orders                     [orders:read]
 *   GET /api/v1/orders/view/{id}   order + domain/service items    [orders:read]
 *
 * Placing an order is done through the shared cart/checkout code, exposed at
 * /api/v1/cart/* (ApiCart) and /api/v1/checkout (ApiCheckout). That path reuses
 * the exact storefront checkoutSubmit() — order/invoice assembly is not
 * duplicated here.
 */
class ApiOrder extends API_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Order_model');
	}

	public function index()
	{
		$this->requireScope('orders:read');
		list($limit, $offset, $page) = $this->pagination();
		$ids = $this->scopedCompanyIds();
		$in  = implode(',', array_fill(0, count($ids), '?'));

		$rows = $this->db->query(
			"SELECT id, order_uuid, order_no, company_id, currency_code, order_date,
			        amount, discount_amount, total_amount, status
			 FROM orders
			 WHERE company_id IN ($in) AND status = 1
			 ORDER BY id DESC LIMIT ?, ?",
			array_merge($ids, array($offset, $limit))
		)->result_array();

		$total = $this->db->query("SELECT COUNT(*) AS cnt FROM orders WHERE company_id IN ($in) AND status = 1", $ids)->row_array();

		$this->ok(array(
			'orders'     => array_map(array($this, 'shapeOrder'), $rows),
			'pagination' => array('page' => $page, 'per_page' => $limit, 'total' => intval($total['cnt'])),
		));
	}

	public function view($id = 0)
	{
		$this->requireScope('orders:read');
		$order = $this->Order_model->getOrderWithItems(intval($id));
		if (empty($order) || !$this->ownsCompany($order['company_id'])) {
			$this->fail(404, 'Order not found.', 'not_found');
		}
		$data = $this->shapeOrder($order);
		$data['domains']  = $order['domains'] ?? array();
		$data['services'] = $order['services'] ?? array();
		$this->ok(array('order' => $data));
	}

	private function shapeOrder($row)
	{
		return array(
			'id'         => intval($row['id']),
			'order_no'   => $row['order_no'] ?? null,
			'uuid'       => $row['order_uuid'] ?? null,
			'company_id' => intval($row['company_id']),
			'currency'   => $row['currency_code'] ?? null,
			'order_date' => $row['order_date'] ?? null,
			'amount'     => isset($row['amount']) ? (float) $row['amount'] : null,
			'discount'   => isset($row['discount_amount']) ? (float) $row['discount_amount'] : 0,
			'total'      => isset($row['total_amount']) ? (float) $row['total_amount'] : null,
			'status'     => intval($row['status']) === 1 ? 'active' : 'cancelled',
		);
	}
}
