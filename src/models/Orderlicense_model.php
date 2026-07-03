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
	 * @deprecated Software plans are now purchased through the cart like hosting
	 * (Cart::addSoftwareToCart -> Cart::checkoutSubmit, item_type=3). The dedicated
	 * checkout is retired; license order rows are created by Cart::_processCartItem
	 * via saveLicense(). Kept as a guard so any stale caller fails cleanly.
	 */
	function createSubscription($params)
	{
		return $this->_fail('Software plans are now purchased via the cart.');
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

		// New recurring amount from software_pricing (product x license currency x
		// cycle). Fall back to the current amount if that combo isn't priced.
		$priceRow  = $this->Plan_model->getPrice((int) $plan['id'], (int) $license['currency_id'], $cycleId);
		$recurring = !empty($priceRow) ? (float) $priceRow['recurring_amount'] : (float) $license['recurring_amount'];

		$update = array(
			'plan_id'          => (int) $plan['id'],
			'billing_cycle_id' => $cycleId,
			'recurring_amount' => $recurring,
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
