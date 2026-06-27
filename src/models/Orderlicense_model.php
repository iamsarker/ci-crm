<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Orderlicense_model
 * -------------------------------------------------------------------------
 * Write/lifecycle layer for WHMAZ SaaS subscriptions (`order_licenses`):
 *
 *   * createSubscription() - dedicated checkout: builds order + order_licenses
 *                            + invoice + invoice_item (item_type=3) and returns
 *                            the invoice for redirect to the existing pay page.
 *   * changePlan()         - upgrade / downgrade: switches plan_id (+ billing
 *                            cycle + recurring amount) on a license.
 *   * activateLicense()    - called from provisioning when the invoice is paid.
 *
 * Mirrors Cart::checkoutSubmit() order/invoice conventions. The entitlement read
 * side lives in Subscription_model.
 *
 * @see src/models/Subscription_model.php
 * @see src/models/Provisioning_model.php  (provisionLicense)
 */
class Orderlicense_model extends CI_Model {

	const ITEM_TYPE_LICENSE = 3; // invoice_items.item_type for a license line

	const STATUS_PENDING    = 0;
	const STATUS_ACTIVE     = 1;
	const STATUS_EXPIRED    = 2;
	const STATUS_SUSPENDED  = 3;
	const STATUS_TERMINATED = 4;

	private $table = 'order_licenses';

	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->model('Plan_model');
		$this->load->model('Order_model');
	}

	// ─── Checkout ──────────────────────────────────────────────────────────

	/**
	 * Create a new license subscription (dedicated checkout).
	 *
	 * Expected $params:
	 *   company_id, user_id, plan_key, billing_cycle ('monthly'|'annual'),
	 *   payment_gateway_id, currency_id (optional), currency_code (optional),
	 *   instructions (optional)
	 *
	 * @return array {success, message, order_id, license_id, invoice_id,
	 *                invoice_uuid, invoice_no, total}
	 */
	function createSubscription($params)
	{
		$companyId = isset($params['company_id']) ? (int) $params['company_id'] : 0;
		$userId    = isset($params['user_id']) ? (int) $params['user_id'] : 0;
		$planKey   = isset($params['plan_key']) ? $params['plan_key'] : '';
		$cycle     = isset($params['billing_cycle']) ? $params['billing_cycle'] : 'monthly';

		if ($companyId <= 0) {
			return $this->_fail('Invalid company.');
		}

		$plan = $this->Plan_model->get_by_key($planKey);
		if (empty($plan) || empty($plan['is_active'])) {
			return $this->_fail('Selected plan is not available.');
		}

		$cycleRow = $this->_billingCycle($cycle);
		if (empty($cycleRow)) {
			return $this->_fail('Invalid billing cycle.');
		}
		$cycleDays = (int) $cycleRow['cycle_days'];

		$price       = $this->_priceFor($plan, $cycle);
		$currencyId  = isset($params['currency_id']) ? (int) $params['currency_id'] : 0;
		$currencyCode = isset($params['currency_code']) ? $params['currency_code'] : $plan['currency'];

		// ── order ──
		$order = array(
			'order_uuid'         => gen_uuid(),
			'order_no'           => $this->Order_model->generateNumber('ORDER'),
			'company_id'         => $companyId,
			'currency_id'        => $currencyId,
			'currency_code'      => $currencyCode,
			'order_date'         => getDateAddDay(0),
			'amount'             => $price,
			'vat_amount'         => 0,
			'tax_amount'         => 0,
			'total_amount'       => $price,
			'payment_gateway_id' => isset($params['payment_gateway_id']) ? (int) $params['payment_gateway_id'] : 0,
			'remarks'            => 'WHMAZ ' . $plan['name'] . ' plan (' . $cycle . ')',
			'instructions'       => isset($params['instructions']) ? $params['instructions'] : '',
			'inserted_on'        => getDateTime(),
			'inserted_by'        => $userId,
		);
		$orderId = $this->Order_model->saveOrder($order);
		if ($orderId <= 0) {
			return $this->_fail('Could not create order.');
		}

		// ── invoice ──
		$invoiceUuid = gen_uuid();
		$invoiceNo   = $this->Order_model->generateNumber('INVOICE');
		$invoice = array(
			'invoice_uuid' => $invoiceUuid,
			'company_id'   => $companyId,
			'order_id'     => $orderId,
			'currency_id'  => $currencyId,
			'currency_code'=> $currencyCode,
			'invoice_no'   => $invoiceNo,
			'sub_total'    => $price,
			'tax'          => 0,
			'vat'          => 0,
			'discount'     => 0,
			'total'        => $price,
			'order_date'   => getDateAddDay(0),
			'due_date'     => getDateAddDay(0),
			'status'       => 1,
			'pay_status'   => 'DUE',
			'inserted_on'  => getDateTime(),
			'inserted_by'  => $userId,
		);
		$invoiceId = $this->Order_model->saveInvoice($invoice);

		// ── license (pending until paid) ──
		$license = array(
			'order_id'          => $orderId,
			'company_id'        => $companyId,
			'plan_id'           => (int) $plan['id'],
			'billing_cycle_id'  => (int) $cycleRow['id'],
			'currency_id'       => $currencyId,
			'currency_code'     => $currencyCode,
			'first_pay_amount'  => $price,
			'recurring_amount'  => $price,
			'auto_renew'        => 1,
			'reg_date'          => getDateAddDay(0),
			'exp_date'          => getDateAddDay($cycleDays),
			'due_date'          => getDateAddDay(0),
			'next_renewal_date' => getDateAddDay($cycleDays),
			'is_synced'         => 0,
			'status'            => self::STATUS_PENDING,
			'inserted_on'       => getDateTime(),
			'inserted_by'       => $userId,
		);
		$licenseId = $this->saveLicense($license);

		// ── invoice item (item_type=3 -> ref_id = order_licenses.id) ──
		$this->Order_model->saveInvoiceItem(array(
			'invoice_id'           => $invoiceId,
			'item'                 => 'WHMAZ ' . $plan['name'] . ' Plan',
			'item_desc'            => 'WHMAZ ' . $plan['name'] . ' plan - ' . $cycleRow['cycle_name'],
			'item_type'            => self::ITEM_TYPE_LICENSE,
			'ref_id'               => $licenseId,
			'billing_cycle_id'     => (int) $cycleRow['id'],
			'quantity'             => 1,
			'unit_price'           => $price,
			'discount'             => 0,
			'sub_total'            => $price,
			'tax'                  => 0,
			'vat'                  => 0,
			'total'                => $price,
			'billing_period_start' => getDateAddDay(0),
			'billing_period_end'   => $cycleDays > 0 ? getDateAddDay($cycleDays) : null,
			'inserted_on'          => getDateTime(),
			'inserted_by'          => $userId,
		));

		return array(
			'success'      => true,
			'message'      => 'Subscription created.',
			'order_id'     => $orderId,
			'license_id'   => $licenseId,
			'invoice_id'   => $invoiceId,
			'invoice_uuid' => $invoiceUuid,
			'invoice_no'   => $invoiceNo,
			'total'        => $price,
		);
	}

	// ─── Upgrade / downgrade ────────────────────────────────────────────────

	/**
	 * Switch a license to a different plan (and optionally billing cycle). This
	 * sets plan_id + recurring_amount immediately so entitlements take effect at
	 * once; the price difference is reflected from the next renewal invoice.
	 * (Immediate proration billing is a separate concern.)
	 *
	 * @param  int    $licenseId
	 * @param  string $newPlanKey
	 * @param  string|null $newBillingCycle 'monthly'|'annual' or null to keep
	 * @param  int    $userId
	 * @return array {success, message, old_plan_key, new_plan_key}
	 */
	function changePlan($licenseId, $newPlanKey, $newBillingCycle = null, $userId = 0)
	{
		$licenseId = (int) $licenseId;
		$license = $this->getLicense($licenseId);
		if (empty($license)) {
			return $this->_fail('License not found.');
		}

		$plan = $this->Plan_model->get_by_key($newPlanKey);
		if (empty($plan) || empty($plan['is_active'])) {
			return $this->_fail('Selected plan is not available.');
		}

		$oldPlan = $this->db->select('plan_key')
			->get_where('plans', array('id' => (int) $license['plan_id']))->row();
		$oldPlanKey = $oldPlan ? $oldPlan->plan_key : null;

		// Resolve target billing cycle (keep current unless a change is requested).
		if ( ! empty($newBillingCycle)) {
			$cycleRow = $this->_billingCycle($newBillingCycle);
			if (empty($cycleRow)) {
				return $this->_fail('Invalid billing cycle.');
			}
			$cycle     = $newBillingCycle;
			$cycleId   = (int) $cycleRow['id'];
		} else {
			$cycle   = $this->_cycleKeyToName($license['billing_cycle_id']);
			$cycleId = (int) $license['billing_cycle_id'];
		}

		$update = array(
			'plan_id'          => (int) $plan['id'],
			'billing_cycle_id' => $cycleId,
			'recurring_amount' => $this->_priceFor($plan, $cycle),
			'remarks'          => 'Plan changed ' . ($oldPlanKey ?: '?') . ' -> ' . $plan['plan_key'],
			'updated_by'       => (int) $userId,
		);

		$this->db->where('id', $licenseId)->update($this->table, $update);

		return array(
			'success'      => true,
			'message'      => 'Plan updated.',
			'old_plan_key' => $oldPlanKey,
			'new_plan_key' => $plan['plan_key'],
		);
	}

	// ─── Activation (called on payment, from provisioning) ───────────────────

	/**
	 * Activate a license after payment. Issues a license_key on first activation;
	 * on renewal it extends the dates and keeps the existing key.
	 *
	 * @param  int  $licenseId
	 * @param  bool $isRenewal
	 * @return array {success, message, license_key}
	 */
	function activateLicense($licenseId, $isRenewal = false)
	{
		$licenseId = (int) $licenseId;
		$license = $this->getLicense($licenseId);
		if (empty($license)) {
			return $this->_fail('License not found.');
		}

		$cycleDays = $this->_cycleDays((int) $license['billing_cycle_id']);

		$update = array(
			'status'       => self::STATUS_ACTIVE,
			'is_synced'    => 1,
			'last_sync_dt' => getDateTime(),
		);

		if ($isRenewal) {
			// Extend from the later of current expiry / today.
			$base = ! empty($license['exp_date']) && $license['exp_date'] > getDateAddDay(0)
				? $license['exp_date'] : getDateAddDay(0);
			$update['exp_date']          = date('Y-m-d', strtotime($base . " +{$cycleDays} days"));
			$update['next_renewal_date'] = $update['exp_date'];
			$update['suspension_date']   = null;
			$update['suspension_reason'] = null;
			$key = $license['license_key'];
		} else {
			if (empty($license['reg_date'])) {
				$update['reg_date'] = getDateAddDay(0);
			}
			$update['exp_date']          = getDateAddDay($cycleDays);
			$update['next_renewal_date'] = getDateAddDay($cycleDays);
			$key = ! empty($license['license_key']) ? $license['license_key'] : $this->_generateLicenseKey();
			$update['license_key'] = $key;
		}

		$this->db->where('id', $licenseId)->update($this->table, $update);

		return array('success' => true, 'message' => 'License activated.', 'license_key' => $key);
	}

	// ─── Soft suspend / terminate (self-hosted: status-only, no remote API) ──

	/**
	 * Soft-suspend a license. We can't touch the customer's own server, so this
	 * only flips status; enforcement happens when the install phones home and
	 * validateLicense() reports 'suspended'.
	 *
	 * @return array {success, message}
	 */
	function suspendLicense($licenseId, $reason = '', $userId = 0)
	{
		$licenseId = (int) $licenseId;
		$license = $this->getLicense($licenseId);
		if (empty($license)) {
			return $this->_fail('License not found.');
		}
		if ((int) $license['status'] === self::STATUS_TERMINATED) {
			return $this->_fail('License is terminated.');
		}

		$this->db->where('id', $licenseId)->update($this->table, array(
			'status'            => self::STATUS_SUSPENDED,
			'suspension_date'   => getDateAddDay(0),
			'suspension_reason' => $reason !== '' ? $reason : 'Suspended',
			'updated_by'        => (int) $userId,
		));

		return array('success' => true, 'message' => 'License suspended.');
	}

	/**
	 * Lift a soft suspension (back to active). Does not extend dates.
	 *
	 * @return array {success, message}
	 */
	function unsuspendLicense($licenseId, $userId = 0)
	{
		$licenseId = (int) $licenseId;
		$license = $this->getLicense($licenseId);
		if (empty($license)) {
			return $this->_fail('License not found.');
		}
		if ((int) $license['status'] !== self::STATUS_SUSPENDED) {
			return $this->_fail('License is not suspended.');
		}

		$this->db->where('id', $licenseId)->update($this->table, array(
			'status'            => self::STATUS_ACTIVE,
			'suspension_date'   => null,
			'suspension_reason' => null,
			'updated_by'        => (int) $userId,
		));

		return array('success' => true, 'message' => 'License reactivated.');
	}

	/**
	 * Soft-terminate a license (permanent). Status-only; the install loses
	 * access on its next phone-home.
	 *
	 * @return array {success, message}
	 */
	function terminateLicense($licenseId, $reason = '', $userId = 0)
	{
		$licenseId = (int) $licenseId;
		$license = $this->getLicense($licenseId);
		if (empty($license)) {
			return $this->_fail('License not found.');
		}

		$this->db->where('id', $licenseId)->update($this->table, array(
			'status'           => self::STATUS_TERMINATED,
			'termination_date' => getDateAddDay(0),
			'remarks'          => $reason !== '' ? $reason : 'Terminated',
			'updated_by'       => (int) $userId,
		));

		return array('success' => true, 'message' => 'License terminated.');
	}

	// ─── Phone-home validation (called by the self-hosted install) ───────────

	/**
	 * Validate a license key for a self-hosted install. Records the check-in,
	 * binds the install domain on first contact, and returns the current status
	 * plus the plan's feature map so the install can gate features locally.
	 *
	 * Expiry is treated as inactive even if status wasn't flipped yet.
	 *
	 * @param  string $key
	 * @param  string $domain install's domain (optional)
	 * @param  string $ip     install's IP (optional)
	 * @return array {valid, status, plan_key, expires, features, message}
	 */
	function validateLicense($key, $domain = '', $ip = '')
	{
		$key = trim((string) $key);
		if ($key === '') {
			return $this->_verdict(false, 'invalid', 'Missing license key.');
		}

		$license = $this->getLicenseByKey($key);
		if (empty($license)) {
			return $this->_verdict(false, 'invalid', 'License key not recognised.');
		}

		// Record the phone-home (and bind the domain on first contact).
		$track = array(
			'last_check_in' => getDateTime(),
			'last_check_ip' => $ip !== '' ? substr($ip, 0, 45) : $license['last_check_ip'],
		);
		if (empty($license['license_domain']) && $domain !== '') {
			$track['license_domain'] = substr($domain, 0, 255);
		}
		$this->db->where('id', (int) $license['id'])->update($this->table, $track);

		$status = (int) $license['status'];
		$expired = ! empty($license['exp_date']) && $license['exp_date'] < getDateAddDay(0);

		if ($status === self::STATUS_TERMINATED) {
			return $this->_verdict(false, 'terminated', 'This license has been terminated.', $license);
		}
		if ($status === self::STATUS_SUSPENDED) {
			return $this->_verdict(false, 'suspended', 'This license is suspended. Please clear any outstanding invoice.', $license);
		}
		if ($status === self::STATUS_PENDING) {
			return $this->_verdict(false, 'pending', 'This license is awaiting payment.', $license);
		}
		if ($status === self::STATUS_EXPIRED || $expired) {
			return $this->_verdict(false, 'expired', 'This license has expired. Please renew.', $license);
		}

		return $this->_verdict(true, 'active', 'License is active.', $license);
	}

	function getLicenseByKey($key)
	{
		$key = trim((string) $key);
		if ($key === '') {
			return array();
		}
		return $this->db
			->where('license_key', $key)
			->where('deleted_on IS NULL', NULL, FALSE)
			->get($this->table)
			->row_array() ?: array();
	}

	// ─── Basic accessors ────────────────────────────────────────────────────

	function getLicense($id)
	{
		$id = (int) $id;
		if ($id <= 0) {
			return array();
		}
		return $this->db->get_where($this->table, array('id' => $id))->row_array() ?: array();
	}

	function saveLicense($data)
	{
		if ( ! empty($data['id'])) {
			$id = (int) $data['id'];
			unset($data['id']);
			$this->db->where('id', $id)->update($this->table, $data);
			return $id;
		}
		$this->db->insert($this->table, $data);
		return (int) $this->db->insert_id();
	}

	// ─── internals ──────────────────────────────────────────────────────────

	private function _priceFor($plan, $cycle)
	{
		return ($cycle === 'annual')
			? (float) $plan['price_annual']
			: (float) $plan['price_monthly'];
	}

	/** Map 'monthly'|'annual' to a billing_cycle row. */
	private function _billingCycle($cycle)
	{
		$key = ($cycle === 'annual') ? 'YEARLY' : 'MONTHLY';
		return $this->db->get_where('billing_cycle', array('cycle_key' => $key))->row_array() ?: array();
	}

	private function _cycleKeyToName($billingCycleId)
	{
		$row = $this->db->select('cycle_key')
			->get_where('billing_cycle', array('id' => (int) $billingCycleId))->row();
		return ($row && $row->cycle_key === 'YEARLY') ? 'annual' : 'monthly';
	}

	private function _cycleDays($billingCycleId)
	{
		$row = $this->db->select('cycle_days')
			->get_where('billing_cycle', array('id' => (int) $billingCycleId))->row();
		return ($row && (int) $row->cycle_days > 0) ? (int) $row->cycle_days : 30;
	}

	private function _generateLicenseKey()
	{
		$raw = strtoupper(bin2hex(random_bytes(10))); // 20 hex chars
		return 'WHMAZ-' . implode('-', str_split($raw, 5));
	}

	private function _fail($message)
	{
		return array('success' => false, 'message' => $message);
	}

	/**
	 * Build a phone-home validation verdict, including the plan's feature map so
	 * the self-hosted install can gate features from the same source of truth.
	 */
	private function _verdict($valid, $status, $message, $license = array())
	{
		$planKey  = null;
		$features = array();
		$expires  = null;

		if ( ! empty($license)) {
			$expires = isset($license['exp_date']) ? $license['exp_date'] : null;
			$planRow = $this->db->select('plan_key')
				->get_where('plans', array('id' => (int) $license['plan_id']))->row();
			if ($planRow) {
				$planKey = $planRow->plan_key;
				$plan = $this->Plan_model->get_by_key($planKey);
				$features = isset($plan['features']) ? $plan['features'] : array();
			}
		}

		return array(
			'valid'    => (bool) $valid,
			'status'   => $status,
			'plan_key' => $planKey,
			'expires'  => $expires,
			'features' => $features,
			'message'  => $message,
		);
	}
}
