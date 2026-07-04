<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Subscription (customer)
 * -------------------------------------------------------------------------
 * "My Software" for the client area: lists the company's licenses, streams the
 * correct download per license, and handles same-family plan upgrades (a
 * prorated difference invoice that applies the switch once paid).
 *
 *   GET  subscription                       My Software list
 *   GET  subscription/download[/{id}]       license-gated download (per product)
 *   GET  subscription/upgrade/{id}          upgrade options for a license
 *   POST subscription/do_upgrade            create proration invoice / apply
 *   GET  subscription/plans                 active plans (JSON)
 *
 * @see src/models/Orderlicense_model.php
 * @see src/models/Subscription_model.php
 */
class Subscription extends WHMAZ_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('Plan_model');
		$this->load->model('Orderlicense_model');
		$this->load->model('Subscription_model');
		$this->load->model('Order_model');
		$this->load->model('Software_model');
	}

	/** "My Software" — all of the company's licenses. */
	public function index()
	{
		if (!$this->isLogin()) {
			redirect('/auth/login?redirect-url=' . base_url() . 'subscription', 'refresh');
			return;
		}

		$data['licenses'] = $this->Subscription_model->get_licenses_for_company(getCompanyId());
		$this->load->view('subscription_index', $data);
	}

	/**
	 * License-gated software download. With an id, streams that license's product
	 * release (after an ownership + active check). Without an id, streams the sole
	 * active license or falls back to the My Software list.
	 */
	public function download($licenseId = null)
	{
		if (!$this->isLogin()) {
			redirect('/auth/login', 'refresh');
			return;
		}
		$companyId = getCompanyId();

		if (empty($licenseId)) {
			$active = array_filter($this->Subscription_model->get_licenses_for_company($companyId), function ($l) {
				return (int) $l['status'] === 1;
			});
			if (count($active) === 1) {
				$licenseId = reset($active)['id'];
			} else {
				redirect('/subscription', 'refresh');
				return;
			}
		}

		$license = $this->Subscription_model->get_company_license((int) $licenseId, $companyId);
		if (empty($license)) {
			$this->session->set_flashdata('alert_error', 'Software not found.');
			redirect('/subscription', 'refresh');
			return;
		}
		if ((int) $license['status'] !== 1) {
			$this->session->set_flashdata('alert_error', 'This license is not active. Please clear any outstanding invoice.');
			redirect('/subscription', 'refresh');
			return;
		}

		// Bind-once gate: before the first download the client must bind the
		// install domain + IP to this license key. Once bound it's locked, and
		// subsequent downloads skip straight through.
		if (!$this->Orderlicense_model->isBound($license)) {
			$data['license']      = $license;
			$data['suggested_ip'] = $this->input->ip_address();
			$this->load->view('subscription_bind', $data);
			return;
		}

		$release = $this->Software_model->getReleaseForProduct((int) $license['plan_id']);
		$path = $this->Software_model->filePath($release);
		if (empty($path)) {
			$this->session->set_flashdata('alert_error', 'No download is available for this product yet. Please check back shortly.');
			redirect('/subscription', 'refresh');
			return;
		}

		$slug = preg_replace('/[^a-z0-9\-]+/', '-', strtolower($license['plan_key']));
		stream_file_download($path, $slug . '-' . $release['version'] . '.zip');
	}

	/**
	 * Bind the install domain + IP to a license, then continue to the download.
	 * Bind-once: rejected if the license is already bound. POST: license_id,
	 * domain, ip.
	 */
	public function bind()
	{
		if (!$this->isLogin()) {
			redirect('/auth/login', 'refresh');
			return;
		}
		$companyId = getCompanyId();
		$licenseId = (int) $this->input->post('license_id');

		$license = $this->Subscription_model->get_company_license($licenseId, $companyId);
		if (empty($license) || (int) $license['status'] !== 1) {
			$this->session->set_flashdata('alert_error', 'License not available.');
			redirect('/subscription', 'refresh');
			return;
		}

		// Already locked — nothing to change; go straight to the download.
		if ($this->Orderlicense_model->isBound($license)) {
			redirect('/subscription/download/' . $licenseId, 'refresh');
			return;
		}

		$domain = strtolower(trim((string) $this->input->post('domain')));
		$ip     = trim((string) $this->input->post('ip'));

		// Accept a pasted URL and reduce it to a bare host.
		if (strpos($domain, '://') !== false) {
			$domain = parse_url($domain, PHP_URL_HOST) ?: $domain;
		}
		$domain = preg_replace('#^www\.#', '', $domain);

		$errors = array();
		if ($domain === '' || !preg_match('/^([a-z0-9](-?[a-z0-9])*\.)+[a-z]{2,}$/i', $domain)) {
			$errors[] = 'Enter a valid install domain (e.g. app.example.com).';
		}
		if ($ip === '' || filter_var($ip, FILTER_VALIDATE_IP) === false) {
			$errors[] = 'Enter a valid server IP address (IPv4 or IPv6).';
		}
		if ($errors) {
			$this->session->set_flashdata('alert_error', implode(' ', $errors));
			redirect('/subscription/download/' . $licenseId, 'refresh');
			return;
		}

		if (!$this->Orderlicense_model->bindInstall($licenseId, $domain, $ip)) {
			$this->session->set_flashdata('alert_error', 'Could not bind this license. Please try again.');
			redirect('/subscription/download/' . $licenseId, 'refresh');
			return;
		}

		$this->session->set_flashdata('alert_success', 'License bound to ' . $domain . ' (' . $ip . '). Your download will begin shortly.');
		redirect('/subscription/download/' . $licenseId, 'refresh');
	}

	/** Show same-family upgrade/downgrade options for a license. */
	public function upgrade($licenseId = null)
	{
		if (!$this->isLogin()) {
			redirect('/auth/login', 'refresh');
			return;
		}
		$companyId = getCompanyId();

		$license = $this->Subscription_model->get_company_license((int) $licenseId, $companyId);
		if (empty($license) || (int) $license['status'] !== 1) {
			$this->session->set_flashdata('alert_error', 'License not available for changes.');
			redirect('/subscription', 'refresh');
			return;
		}
		if (empty($license['family_group'])) {
			$this->session->set_flashdata('alert_error', 'This product has no upgrade options.');
			redirect('/subscription', 'refresh');
			return;
		}

		$options = $this->Plan_model->getFamilyProducts(
			$license['family_group'], (int) $license['currency_id'], (int) $license['billing_cycle_id'], (int) $license['plan_id']
		);

		// Attach a prorated charge to each option (positive = payable now).
		$remaining = $this->_remainingDays($license['next_renewal_date']);
		$cycleDays = $this->_cycleDays((int) $license['billing_cycle_id']);
		foreach ($options as &$opt) {
			$diff = (float) $opt['recurring_amount'] - (float) $license['recurring_amount'];
			$opt['proration'] = ($diff > 0 && $cycleDays > 0)
				? round($diff * ($remaining / $cycleDays), 2) : 0.0;
			$opt['is_upgrade'] = $diff > 0;
		}
		unset($opt);

		$data['license']       = $license;
		$data['options']       = $options;
		$data['remaining_days'] = $remaining;
		$data['currency_code'] = !empty($license['currency_code']) ? $license['currency_code'] : getCurrencyCode();
		$this->load->view('subscription_upgrade', $data);
	}

	/**
	 * Apply a plan change. Upgrades (positive proration) create a DUE invoice and
	 * mark the change pending until it's paid; same-price/downgrades apply now.
	 * POST: license_id, pricing_id
	 */
	public function do_upgrade()
	{
		if (!$this->isLogin()) {
			redirect('/auth/login', 'refresh');
			return;
		}
		$userId    = getCustomerId();
		$companyId = getCompanyId();

		$licenseId = (int) $this->input->post('license_id');
		$pricingId = (int) $this->input->post('pricing_id');

		$license = $this->Subscription_model->get_company_license($licenseId, $companyId);
		if (empty($license) || (int) $license['status'] !== 1) {
			$this->session->set_flashdata('alert_error', 'License not available for changes.');
			redirect('/subscription', 'refresh');
			return;
		}
		if (!empty($license['pending_invoice_id'])) {
			$this->session->set_flashdata('alert_error', 'A plan change is already pending payment for this license.');
			redirect('/subscription', 'refresh');
			return;
		}

		// Target must be a priced, active, same-family product on the same currency/cycle.
		$target = $this->db->query(
			"SELECT sp.id AS pricing_id, sp.recurring_amount, p.id AS plan_id, p.plan_key, p.name, p.family_group
			 FROM software_pricing sp JOIN plans p ON sp.product_id = p.id
			 WHERE sp.id = ? AND sp.status = 1 AND p.is_active = 1
			   AND sp.currency_id = ? AND sp.billing_cycle_id = ?",
			array($pricingId, (int) $license['currency_id'], (int) $license['billing_cycle_id'])
		)->row_array();

		if (empty($target)
			|| (int) $target['plan_id'] === (int) $license['plan_id']
			|| empty($license['family_group'])
			|| $target['family_group'] !== $license['family_group']) {
			$this->session->set_flashdata('alert_error', 'Invalid plan selection.');
			redirect('/subscription/upgrade/' . $licenseId, 'refresh');
			return;
		}

		$remaining = $this->_remainingDays($license['next_renewal_date']);
		$cycleDays = $this->_cycleDays((int) $license['billing_cycle_id']);
		$diff      = (float) $target['recurring_amount'] - (float) $license['recurring_amount'];
		$proration = ($diff > 0 && $cycleDays > 0) ? round($diff * ($remaining / $cycleDays), 2) : 0.0;

		// Same price or downgrade: apply immediately, no invoice.
		if ($proration <= 0) {
			$this->Orderlicense_model->changePlan($licenseId, $target['plan_key'], null, $userId);
			$this->session->set_flashdata('alert_success', 'Your plan has been changed to ' . $target['name'] . '.');
			redirect('/subscription', 'refresh');
			return;
		}

		// Upgrade: prorated DUE invoice; the switch applies once it's paid.
		$currencyCode = !empty($license['currency_code']) ? $license['currency_code'] : getCurrencyCode();
		$invoiceUuid  = gen_uuid();
		$invoiceId = $this->Order_model->saveInvoice(array(
			'invoice_uuid'  => $invoiceUuid,
			'company_id'    => $companyId,
			'order_id'      => (int) $license['order_id'],
			'currency_id'   => (int) $license['currency_id'],
			'currency_code' => $currencyCode,
			'invoice_no'    => $this->Order_model->generateNumber('INVOICE'),
			'sub_total'     => $proration,
			'tax'           => 0,
			'vat'           => 0,
			'discount'      => 0,
			'total'         => $proration,
			'order_date'    => getDateAddDay(0),
			'due_date'      => getDateAddDay(0),
			'status'        => 1,
			'pay_status'    => 'DUE',
			'remarks'       => 'Prorated plan upgrade: ' . $license['plan_name'] . ' -> ' . $target['name'],
			'inserted_on'   => getDateTime(),
			'inserted_by'   => $userId,
		));

		$this->Order_model->saveInvoiceItem(array(
			'invoice_id'           => $invoiceId,
			'item'                 => 'Plan Upgrade',
			'item_desc'            => 'Upgrade ' . $license['plan_name'] . ' -> ' . $target['name'] . ' (prorated for ' . $remaining . ' days)',
			'item_type'            => 3,
			'ref_id'               => $licenseId,
			'billing_cycle_id'     => (int) $license['billing_cycle_id'],
			'quantity'             => 1,
			'unit_price'           => $proration,
			'discount'             => 0,
			'sub_total'            => $proration,
			'tax'                  => 0,
			'vat'                  => 0,
			'total'                => $proration,
			'billing_period_start' => getDateAddDay(0),
			'billing_period_end'   => $license['next_renewal_date'],
			'inserted_on'          => getDateTime(),
			'inserted_by'          => $userId,
		));

		$this->Orderlicense_model->setPendingPlanChange($licenseId, (int) $target['plan_id'], (int) $license['billing_cycle_id'], $invoiceId);

		redirect('/billing/pay/invoice/' . $invoiceUuid, 'refresh');
	}

	/** Active plans with merged feature maps (JSON). */
	public function plans()
	{
		echo json_encode(buildSuccessResponse($this->Plan_model->get_active_plans(), 'OK'));
	}

	// ─── helpers ─────────────────────────────────────────────────────────

	private function _remainingDays($nextRenewalDate)
	{
		if (empty($nextRenewalDate)) {
			return 0;
		}
		$days = (int) floor((strtotime($nextRenewalDate) - strtotime(getDateAddDay(0))) / 86400);
		return $days > 0 ? $days : 0;
	}

	private function _cycleDays($billingCycleId)
	{
		$row = $this->db->select('cycle_days')->get_where('billing_cycle', array('id' => (int) $billingCycleId))->row();
		return ($row && (int) $row->cycle_days > 0) ? (int) $row->cycle_days : 30;
	}
}
