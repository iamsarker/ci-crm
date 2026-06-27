<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Subscription
 * -------------------------------------------------------------------------
 * Dedicated checkout for the WHMAZ SaaS plans (separate from the hosting/domain
 * cart). Creates the order + order_licenses + invoice and hands back the invoice
 * UUID so the client redirects to the existing payment page (billing/pay).
 * Plan upgrade/downgrade switches the plan on the active license.
 *
 *   POST subscription/subscribe   plan_key, billing_cycle, payment_gateway
 *   POST subscription/upgrade     plan_key[, billing_cycle]
 *   GET  subscription/plans       active plans (JSON, for the upgrade UI)
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

	/**
	 * License-gated software download for the logged-in customer. Streams the
	 * current release only if the company has an active license. The stored file
	 * path is never exposed.
	 */
	public function download()
	{
		if (!$this->isLogin()) {
			redirect('/auth/login', 'refresh');
			return;
		}

		$subscription = $this->Subscription_model->get_active_subscription_for_company(getCompanyId());
		if (empty($subscription)) {
			$this->session->set_flashdata('error', 'An active subscription is required to download the software.');
			redirect('/clientarea', 'refresh');
			return;
		}

		$release = $this->Software_model->getCurrentRelease();
		$path = $this->Software_model->filePath($release);
		if (empty($path)) {
			$this->session->set_flashdata('error', 'No software release is available yet. Please check back shortly.');
			redirect('/clientarea', 'refresh');
			return;
		}

		stream_file_download($path, 'whmaz-' . $release['version'] . '.zip');
	}

	/** Active plans with merged feature maps (JSON). */
	public function plans()
	{
		echo json_encode(buildSuccessResponse($this->Plan_model->get_active_plans(), 'OK'));
	}

	/**
	 * Create a new plan subscription and return the invoice for payment redirect.
	 */
	public function subscribe()
	{
		$userId    = getCustomerId();
		$companyId = getCompanyId();

		if ($userId <= 0 || $companyId <= 0) {
			echo json_encode(array('code' => 401, 'msg' => 'Please login first to subscribe!', 'data' => null));
			return;
		}

		$planKey = $this->input->post('plan_key');
		$cycle   = $this->input->post('billing_cycle');
		$gateway = $this->input->post('payment_gateway');
		$cycle   = ($cycle === 'annual') ? 'annual' : 'monthly';

		if (empty($planKey)) {
			echo json_encode(buildFailedResponse('Please choose a plan.'));
			return;
		}

		$result = $this->Orderlicense_model->createSubscription(array(
			'company_id'         => $companyId,
			'user_id'            => $userId,
			'currency_id'        => getCurrencyId(),
			'currency_code'      => getCurrencyCode(),
			'plan_key'           => $planKey,
			'billing_cycle'      => $cycle,
			'payment_gateway_id' => $gateway,
			'instructions'       => $this->input->post('instructions'),
		));

		if (empty($result['success'])) {
			echo json_encode(buildFailedResponse($result['message']));
			return;
		}

		echo json_encode(buildSuccessResponse(array(
			'invoice_id'   => $result['invoice_id'],
			'invoice_uuid' => $result['invoice_uuid'],
			'invoice_no'   => $result['invoice_no'],
			'total'        => $result['total'],
		), 'Subscription created. Redirecting to payment...'));
	}

	/**
	 * Upgrade/downgrade the company's active plan. Takes effect immediately;
	 * the new price is billed from the next renewal.
	 */
	public function upgrade()
	{
		$userId    = getCustomerId();
		$companyId = getCompanyId();

		if ($userId <= 0 || $companyId <= 0) {
			echo json_encode(array('code' => 401, 'msg' => 'Please login first!', 'data' => null));
			return;
		}

		$planKey = $this->input->post('plan_key');
		$cycle   = $this->input->post('billing_cycle'); // optional
		if (!empty($cycle)) {
			$cycle = ($cycle === 'annual') ? 'annual' : 'monthly';
		} else {
			$cycle = null;
		}

		if (empty($planKey)) {
			echo json_encode(buildFailedResponse('Please choose a plan.'));
			return;
		}

		$current = $this->Subscription_model->get_active_subscription_for_company($companyId);
		if (empty($current)) {
			echo json_encode(buildFailedResponse('No active subscription to change.'));
			return;
		}

		if ($current['plan_key'] === $planKey && empty($cycle)) {
			echo json_encode(buildFailedResponse('You are already on this plan.'));
			return;
		}

		$result = $this->Orderlicense_model->changePlan($current['id'], $planKey, $cycle, $userId);

		if (empty($result['success'])) {
			echo json_encode(buildFailedResponse($result['message']));
			return;
		}

		echo json_encode(buildSuccessResponse(array(
			'old_plan_key' => $result['old_plan_key'],
			'new_plan_key' => $result['new_plan_key'],
		), 'Your plan has been updated.'));
	}
}
