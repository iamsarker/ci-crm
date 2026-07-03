<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * ApiHosting — the reseller's hosting services + lifecycle actions.
 *
 *   GET  /api/v1/hosting                  list services            [hosting:read]
 *   GET  /api/v1/hosting/view/{id}        single service           [hosting:read]
 *   POST /api/v1/hosting/suspend/{id}     suspend account          [hosting:write]
 *   POST /api/v1/hosting/unsuspend/{id}   lift suspension          [hosting:write]
 *   POST /api/v1/hosting/terminate/{id}   cancel + delete account  [hosting:write]
 */
class ApiHosting extends API_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Order_model');
		$this->load->model('Provisioning_model');
	}

	public function index()
	{
		$this->requireScope('hosting:read');
		list($limit, $offset, $page) = $this->pagination();
		$ids = $this->scopedCompanyIds();
		$in  = implode(',', array_fill(0, count($ids), '?'));

		$rows = $this->db->query(
			"SELECT os.id, os.company_id, os.hosting_domain, os.status, os.cp_username,
			        os.reg_date, os.exp_date, os.next_renewal_date, os.recurring_amount,
			        ps.product_name
			 FROM order_services os
			 LEFT JOIN product_services ps ON ps.id = os.product_service_id
			 WHERE os.company_id IN ($in) AND os.deleted_on IS NULL
			 ORDER BY os.id DESC LIMIT ?, ?",
			array_merge($ids, array($offset, $limit))
		)->result_array();

		$total = $this->db->query("SELECT COUNT(*) AS cnt FROM order_services WHERE company_id IN ($in) AND deleted_on IS NULL", $ids)->row_array();

		$this->ok(array(
			'services'   => array_map(array($this, 'shapeService'), $rows),
			'pagination' => array('page' => $page, 'per_page' => $limit, 'total' => intval($total['cnt'])),
		));
	}

	public function view($id = 0)
	{
		$this->requireScope('hosting:read');
		$service = $this->_ownedService($id);
		$this->ok(array('service' => $this->shapeService($service)));
	}

	public function suspend($id = 0)
	{
		$this->requireScope('hosting:write');
		$this->requireMethod('POST');
		$service = $this->_ownedService($id);

		$reason = trim((string) $this->param('reason')) ?: 'Suspended via API';
		$result = $this->Provisioning_model->suspendService($service, $reason);
		if (empty($result['success'])) {
			$this->fail(502, $result['error'] ?? 'Suspend failed at the server module.', 'provisioning_error');
		}
		$this->ok(array('service_id' => intval($id), 'status' => 'suspended', 'module' => $result['module'] ?? null));
	}

	public function unsuspend($id = 0)
	{
		$this->requireScope('hosting:write');
		$this->requireMethod('POST');
		$service = $this->_ownedService($id);

		$result = $this->Provisioning_model->unsuspendService($service);
		if (empty($result['success'])) {
			$this->fail(502, $result['error'] ?? 'Unsuspend failed at the server module.', 'provisioning_error');
		}
		$this->ok(array('service_id' => intval($id), 'status' => 'active'));
	}

	public function terminate($id = 0)
	{
		$this->requireScope('hosting:write');
		$this->requireMethod('POST');
		$service = $this->_ownedService($id);

		$reason = trim((string) $this->param('reason')) ?: 'Terminated via API';
		// Immediate cancellation also deletes the server account (see Order_model).
		$this->Order_model->cancelService($service['id'], 'immediate', $reason);
		$this->ok(array('service_id' => intval($id), 'status' => 'terminated'));
	}

	/** Fetch a service row and assert it belongs to this reseller's scope. */
	private function _ownedService($id)
	{
		$service = $this->Order_model->getServiceItem(intval($id));
		if (empty($service) || !$this->ownsCompany($service['company_id'])) {
			$this->fail(404, 'Service not found.', 'not_found');
		}
		return $service;
	}

	private function shapeService($row)
	{
		$statusMap = array(0 => 'pending', 1 => 'active', 2 => 'expired', 3 => 'suspended', 4 => 'terminated');
		return array(
			'id'                => intval($row['id']),
			'company_id'        => intval($row['company_id']),
			'product'           => $row['product_name'] ?? null,
			'hosting_domain'    => $row['hosting_domain'] ?? null,
			'cp_username'       => $row['cp_username'] ?? null,
			'status'            => $statusMap[intval($row['status'])] ?? 'unknown',
			'status_code'       => intval($row['status']),
			'reg_date'          => $row['reg_date'] ?? null,
			'exp_date'          => $row['exp_date'] ?? null,
			'next_renewal_date' => $row['next_renewal_date'] ?? null,
			'recurring_amount'  => isset($row['recurring_amount']) ? (float) $row['recurring_amount'] : null,
		);
	}
}
