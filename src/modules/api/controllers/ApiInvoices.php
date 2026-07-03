<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * ApiInvoices — the reseller's billing documents.
 *
 *   GET  /api/v1/invoices                list invoices                 [invoices:read]
 *   GET  /api/v1/invoices/view/{uuid}    single invoice + line items   [invoices:read]
 *   POST /api/v1/invoices/pay/{uuid}     mark as paid (-> provisioning)[invoices:write]
 *
 * Marking an invoice paid triggers the same provisioning path as an admin
 * "Mark as Paid" (Invoice_model::updateInvoiceStatus -> provisionPaidServices).
 */
class ApiInvoices extends API_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Invoice_model');
	}

	public function index()
	{
		$this->requireScope('invoices:read');
		list($limit, $offset, $page) = $this->pagination();
		$ids = $this->scopedCompanyIds();
		$in  = implode(',', array_fill(0, count($ids), '?'));

		$rows = $this->db->query(
			"SELECT id, invoice_uuid, invoice_no, company_id, currency_code,
			        sub_total, total, due_date, order_date, status, pay_status
			 FROM invoices
			 WHERE company_id IN ($in) AND status = 1
			 ORDER BY id DESC LIMIT ?, ?",
			array_merge($ids, array($offset, $limit))
		)->result_array();

		$total = $this->db->query("SELECT COUNT(*) AS cnt FROM invoices WHERE company_id IN ($in) AND status = 1", $ids)->row_array();

		$this->ok(array(
			'invoices'   => array_map(array($this, 'shapeInvoice'), $rows),
			'pagination' => array('page' => $page, 'per_page' => $limit, 'total' => intval($total['cnt'])),
		));
	}

	public function view($uuid = '')
	{
		$this->requireScope('invoices:read');
		$invoice = $this->_ownedInvoice($uuid);

		$items = $this->db->query(
			"SELECT item, item_desc, item_type, quantity, unit_price, discount, sub_total, total, ref_id
			 FROM invoice_items WHERE invoice_id = ?",
			array($invoice['id'])
		)->result_array();

		$typeMap = array(1 => 'domain', 2 => 'service', 3 => 'license');
		$data = $this->shapeInvoice($invoice);
		$data['items'] = array_map(function ($it) use ($typeMap) {
			return array(
				'item'        => $it['item'],
				'description' => $it['item_desc'],
				'type'        => $typeMap[intval($it['item_type'])] ?? 'other',
				'quantity'    => intval($it['quantity']),
				'unit_price'  => (float) $it['unit_price'],
				'discount'    => (float) $it['discount'],
				'total'       => (float) $it['total'],
				'ref_id'      => $it['ref_id'] !== null ? intval($it['ref_id']) : null,
			);
		}, $items);

		$this->ok(array('invoice' => $data));
	}

	public function pay($uuid = '')
	{
		$this->requireScope('invoices:write');
		$this->requireMethod('POST');
		$invoice = $this->_ownedInvoice($uuid);

		if (strtoupper($invoice['pay_status']) === 'PAID') {
			$this->fail(409, 'Invoice is already paid.', 'conflict');
		}

		// Mirrors admin "Mark as Paid": flips pay_status and provisions paid items.
		$this->Invoice_model->updateInvoiceStatus($invoice['invoice_uuid'], 'PAID', 0);

		// Reload for the fresh status.
		$fresh = $this->Invoice_model->getInvoiceByUuid($invoice['invoice_uuid']);
		$this->ok(array(
			'invoice_uuid' => $invoice['invoice_uuid'],
			'pay_status'   => $fresh['pay_status'] ?? 'PAID',
			'provisioned'  => true,
		));
	}

	private function _ownedInvoice($uuid)
	{
		$uuid = trim((string) $uuid);
		$invoice = $this->Invoice_model->getInvoiceByUuid($uuid);
		if (empty($invoice) || !$this->ownsCompany($invoice['company_id'])) {
			$this->fail(404, 'Invoice not found.', 'not_found');
		}
		return $invoice;
	}

	private function shapeInvoice($row)
	{
		return array(
			'uuid'        => $row['invoice_uuid'],
			'invoice_no'  => $row['invoice_no'],
			'company_id'  => intval($row['company_id']),
			'currency'    => $row['currency_code'] ?? null,
			'sub_total'   => isset($row['sub_total']) ? (float) $row['sub_total'] : null,
			'total'       => (float) $row['total'],
			'order_date'  => $row['order_date'] ?? null,
			'due_date'    => $row['due_date'] ?? null,
			'pay_status'  => $row['pay_status'],
		);
	}
}
