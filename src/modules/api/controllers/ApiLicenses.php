<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * ApiLicenses — software licenses owned by the reseller / its sub-customers.
 *
 *   GET  /api/v1/licenses                 list licenses               [licenses:read]
 *   GET  /api/v1/licenses/view/{id}       single license              [licenses:read]
 *   POST /api/v1/licenses/verify          validate a license key      [licenses:read]
 *   POST /api/v1/licenses/activate/{id}   activate / (re)issue key    [licenses:write]
 *   POST /api/v1/licenses/suspend/{id}    soft-suspend                [licenses:write]
 *   POST /api/v1/licenses/unsuspend/{id}  lift suspension             [licenses:write]
 *   POST /api/v1/licenses/terminate/{id}  terminate                   [licenses:write]
 *
 * Self-hosted licensing is soft: these flip order_licenses.status; enforcement
 * happens when the install phones home to license/verify.
 */
class ApiLicenses extends API_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Orderlicense_model');
	}

	public function index()
	{
		$this->requireScope('licenses:read');
		list($limit, $offset, $page) = $this->pagination();
		$ids = $this->scopedCompanyIds();
		$in  = implode(',', array_fill(0, count($ids), '?'));

		$rows = $this->db->query(
			"SELECT ol.id, ol.company_id, ol.license_key, ol.status, ol.license_domain,
			        ol.next_renewal_date, ol.last_check_in,
			        p.plan_key, p.name AS plan_name
			 FROM order_licenses ol
			 LEFT JOIN plans p ON p.id = ol.plan_id
			 WHERE ol.company_id IN ($in)
			 ORDER BY ol.id DESC LIMIT ?, ?",
			array_merge($ids, array($offset, $limit))
		)->result_array();

		$total = $this->db->query("SELECT COUNT(*) AS cnt FROM order_licenses WHERE company_id IN ($in)", $ids)->row_array();

		$this->ok(array(
			'licenses'   => array_map(array($this, 'shapeLicense'), $rows),
			'pagination' => array('page' => $page, 'per_page' => $limit, 'total' => intval($total['cnt'])),
		));
	}

	public function view($id = 0)
	{
		$this->requireScope('licenses:read');
		$lic = $this->_ownedLicense($id);
		$this->ok(array('license' => $this->shapeLicense($lic)));
	}

	/** Validate a license key (same verdict the phone-home endpoint returns). */
	public function verify()
	{
		$this->requireScope('licenses:read');
		$key    = trim((string) $this->param('license_key'));
		$domain = trim((string) $this->param('domain'));
		if ($key === '') {
			$this->fail(422, 'license_key is required.', 'validation_error');
		}
		$result = $this->Orderlicense_model->validateLicense($key, $domain, $this->input->ip_address());
		$this->ok($result);
	}

	public function activate($id = 0)  { $this->_lifecycle($id, 'activate'); }
	public function suspend($id = 0)   { $this->_lifecycle($id, 'suspend'); }
	public function unsuspend($id = 0) { $this->_lifecycle($id, 'unsuspend'); }
	public function terminate($id = 0) { $this->_lifecycle($id, 'terminate'); }

	private function _lifecycle($id, $action)
	{
		$this->requireScope('licenses:write');
		$this->requireMethod('POST');
		$lic = $this->_ownedLicense($id);
		$reason = trim((string) $this->param('reason'));

		switch ($action) {
			case 'activate':   $result = $this->Orderlicense_model->activateLicense($lic['id']); break;
			case 'suspend':    $result = $this->Orderlicense_model->suspendLicense($lic['id'], $reason, 0); break;
			case 'unsuspend':  $result = $this->Orderlicense_model->unsuspendLicense($lic['id'], 0); break;
			case 'terminate':  $result = $this->Orderlicense_model->terminateLicense($lic['id'], $reason, 0); break;
			default:           $this->fail(400, 'Unknown action.', 'bad_request'); return;
		}

		if (empty($result['success'])) {
			$this->fail(502, $result['message'] ?? 'License action failed.', 'action_failed');
		}
		$fresh = $this->Orderlicense_model->getLicense($lic['id']);
		$this->ok(array(
			'license'     => $this->shapeLicense($fresh),
			'message'     => $result['message'] ?? null,
			'license_key' => $result['license_key'] ?? ($fresh['license_key'] ?? null),
		));
	}

	private function _ownedLicense($id)
	{
		$lic = $this->Orderlicense_model->getLicense(intval($id));
		if (empty($lic) || !$this->ownsCompany($lic['company_id'])) {
			$this->fail(404, 'License not found.', 'not_found');
		}
		return $lic;
	}

	private function shapeLicense($row)
	{
		$statusMap = array(0 => 'pending', 1 => 'active', 2 => 'expired', 3 => 'suspended', 4 => 'terminated');
		return array(
			'id'                => intval($row['id']),
			'company_id'        => intval($row['company_id']),
			'plan_key'          => $row['plan_key'] ?? null,
			'plan_name'         => $row['plan_name'] ?? null,
			'license_key'       => $row['license_key'] ?? null,
			'status'            => $statusMap[intval($row['status'])] ?? 'unknown',
			'status_code'       => intval($row['status']),
			'license_domain'    => $row['license_domain'] ?? null,
			'next_renewal_date' => $row['next_renewal_date'] ?? null,
			'last_check_in'     => $row['last_check_in'] ?? null,
		);
	}
}
