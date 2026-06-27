<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Subscription_model
 * -------------------------------------------------------------------------
 * Entitlement-facing read layer. Resolves the plan currently in force for an
 * account (a customer company) from its WHMAZ SaaS subscription line in
 * `order_licenses`. Only an ACTIVE license grants entitlements.
 *
 * "account" == companies.id (== order_licenses.company_id).
 * Lifecycle write operations (subscribe / upgrade / activate) live in
 * Orderlicense_model; this model is intentionally read-only so the entitlement
 * layer stays decoupled from billing.
 *
 * @see src/models/Orderlicense_model.php
 * @see src/libraries/Entitlement.php
 */
class Subscription_model extends CI_Model {

	const STATUS_ACTIVE = 1;

	private $table = 'order_licenses';

	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * The plan_key currently in force for a company, or NULL if it has no active
	 * license. When a company somehow has more than one active license, the one
	 * renewing latest (then most recent id) wins.
	 *
	 * @param  int $company_id
	 * @return string|null
	 */
	function get_active_plan_key_for_company($company_id)
	{
		$company_id = (int) $company_id;
		if ($company_id <= 0) {
			return NULL;
		}

		$row = $this->db
			->select('p.plan_key')
			->from($this->table . ' ol')
			->join('plans p', 'p.id = ol.plan_id', 'inner')
			->where('ol.company_id', $company_id)
			->where('ol.status', self::STATUS_ACTIVE)
			->where('ol.deleted_on IS NULL', NULL, FALSE)
			->where('p.is_active', 1)
			->order_by('ol.next_renewal_date', 'DESC')
			->order_by('ol.id', 'DESC')
			->limit(1)
			->get()
			->row();

		return $row ? $row->plan_key : NULL;
	}

	/**
	 * The active license for a company (license row + plan + billing cycle), or
	 * empty array if none. Handy for the client area / upgrade screens.
	 *
	 * @param  int $company_id
	 * @return array
	 */
	function get_active_subscription_for_company($company_id)
	{
		$company_id = (int) $company_id;
		if ($company_id <= 0) {
			return array();
		}

		$row = $this->db
			->select('ol.*, p.plan_key, p.name AS plan_name, bc.cycle_key, bc.cycle_name')
			->from($this->table . ' ol')
			->join('plans p', 'p.id = ol.plan_id', 'inner')
			->join('billing_cycle bc', 'bc.id = ol.billing_cycle_id', 'left')
			->where('ol.company_id', $company_id)
			->where('ol.status', self::STATUS_ACTIVE)
			->where('ol.deleted_on IS NULL', NULL, FALSE)
			->where('p.is_active', 1)
			->order_by('ol.next_renewal_date', 'DESC')
			->order_by('ol.id', 'DESC')
			->limit(1)
			->get()
			->row_array();

		return $row ? $row : array();
	}
}
