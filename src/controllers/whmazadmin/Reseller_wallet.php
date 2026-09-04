<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Reseller Wallet — the prepaid credit account (v2.0.0 Phase 3).
 *
 *   Reseller admin  (admin_type = 1): sees their OWN balance and statement, and
 *                                     tops up through the normal gateways.
 *   Platform admin  (admin_type = 0): picks a reseller, sees the same, and can
 *                                     post an audited manual adjustment.
 *
 * One screen for both, as with Reseller_pricing: the statement and balance are
 * identical, only who owns the row and which actions are offered differ.
 *
 * Nothing here computes a balance or writes one. Every number comes from
 * Resellercredit_model, which is the only thing permitted to write the ledger
 * or the cached credit_balance -- so this controller cannot introduce a drift
 * between the two no matter what it does.
 */
class Reseller_wallet extends WHMAZADMIN_Controller {

	/** Statement rows per page. */
	const PAGE_SIZE = 25;

	function __construct(){
		parent::__construct();
		$this->load->model('Resellercredit_model');
		$this->load->model('Pricing_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	// -----------------------------------------------------------------
	// Screen
	// -----------------------------------------------------------------

	public function index()
	{
		$data['is_owner']  = isResellerAdmin();
		$data['resellers'] = $data['is_owner'] ? array() : $this->Pricing_model->resellerList();

		$companyId = $this->_targetReseller();
		$data['reseller_company_id'] = $companyId;

		$data['wallet']     = array();
		$data['statement']  = array();
		$data['total_rows'] = 0;
		$data['page']       = 1;
		$data['page_size']  = self::PAGE_SIZE;
		$data['held']       = array();
		$data['drift']      = array();

		if ($companyId > 0) {
			$data['wallet'] = $this->Resellercredit_model->getWallet($companyId);

			$page = (int) $this->input->get('page');
			if ($page < 1) $page = 1;
			$data['page'] = $page;

			$data['total_rows'] = $this->Resellercredit_model->countStatement($companyId);
			$data['statement']  = $this->Resellercredit_model->statement(
				$companyId, self::PAGE_SIZE, ($page - 1) * self::PAGE_SIZE
			);

			$data['held'] = $this->_heldForReseller($companyId);

			// Surface a ledger/cache divergence rather than rendering a number
			// that quietly disagrees with its own history. It should always be
			// empty; if it is not, the balance on screen is not trustworthy and
			// saying so beats displaying it as fact.
			$data['drift'] = $this->Resellercredit_model->reconcile($companyId);
		}

		$this->load->view('whmazadmin/reseller_wallet', $data);
	}

	// -----------------------------------------------------------------
	// Actions
	// -----------------------------------------------------------------

	/**
	 * Raise a top-up invoice, then hand straight off to the payment page.
	 *
	 * The invoice is an ordinary one (item_type = 4 line, ref_id NULL), so from
	 * here on it is the same code path as any other invoice -- which is the
	 * whole point of modelling a top-up this way.
	 */
	public function topup()
	{
		// Gate on the METHOD. csrf_verify() unsets the token from $_POST once it
		// has checked it, so a form carrying only a token reads as an empty POST
		// -- see CLAUDE.md, "Known Gotchas".
		if ($this->input->method(TRUE) !== 'POST') show_404();

		$companyId = $this->_targetReseller();
		if ($companyId <= 0) {
			$this->session->set_flashdata('admin_error', 'No reseller selected.');
			redirect('whmazadmin/reseller_wallet/index');
			return;
		}

		$amount = round((float) $this->input->post('amount'), 2);
		if ($amount <= 0) {
			$this->session->set_flashdata('admin_error', 'Enter a top-up amount greater than zero.');
			redirect($this->_indexUrl($companyId));
			return;
		}

		$wallet = $this->Resellercredit_model->getWallet($companyId);
		if (empty($wallet)) {
			$this->session->set_flashdata('admin_error', 'That reseller has no wallet.');
			redirect('whmazadmin/reseller_wallet/index');
			return;
		}
		if (empty($wallet['currency_id'])) {
			// createTopupInvoice() would refuse anyway; say why, here, where the
			// person can fix it.
			$this->session->set_flashdata('admin_error',
				'Set a Credit Currency on the reseller profile before topping up — an invoice needs a currency.');
			redirect($this->_indexUrl($companyId));
			return;
		}

		$inv = $this->Resellercredit_model->createTopupInvoice(
			$companyId, $amount, (int) $wallet['currency_id'], getAdminId()
		);

		if (empty($inv['invoice_id'])) {
			$this->session->set_flashdata('admin_error', 'Could not raise the top-up invoice.');
			redirect($this->_indexUrl($companyId));
			return;
		}

		// A platform admin raising this on a reseller's behalf should NOT be
		// pushed into the reseller's payment session (see pay()). Hand them the
		// invoice instead; they can email it, or mark it paid on a bank
		// transfer, which credits the wallet through the same hook.
		if (!isResellerAdmin()) {
			$this->session->set_flashdata('admin_success',
				'Top-up invoice #' . $inv['invoice_no'] . ' raised for ' . number_format($amount, 2)
				. '. The wallet is credited when it is paid or marked as paid.');
			redirect($this->_indexUrl($companyId));
			return;
		}

		redirect('whmazadmin/reseller_wallet/pay/' . safe_encode($inv['invoice_id']));
	}

	/**
	 * Pay a top-up invoice: mint a CUSTOMER session, then hand off to the
	 * storefront payment page.
	 *
	 * Phase 1 moved resellers onto the admin login, so they have no CUSTOMER
	 * session -- and the whole payment stack (every gateway init, the SameSite
	 * token restore, the return handlers) reads one. Rather than teach six
	 * gateways about admin sessions, mint the session they already expect.
	 *
	 * ⚠️ RESELLER ADMINS ONLY, deliberately. actAsCompanyCustomer() is
	 * impersonation: for a reseller it means acting as their own company's user,
	 * which is unremarkable, but a platform admin doing it would be silently
	 * logged into the client portal AS that reseller. Platform staff already
	 * have a non-impersonating route to the same outcome -- mark the invoice
	 * paid on Invoice -- so there is nothing to trade away by refusing here.
	 */
	public function pay($encodedInvoiceId = null)
	{
		if (!isResellerAdmin()) {
			tenant_deny('Use Invoicing → View Invoices to take payment on a reseller\'s behalf.');
			return;
		}

		$invoiceId = (int) safe_decode($encodedInvoiceId);
		if ($invoiceId <= 0) show_404();

		// Tenancy first, impersonation second -- actAsCompanyCustomer() does not
		// check scope, on purpose, so the guard has to be here.
		$companyId = $this->guardRecord('invoices', $invoiceId);

		$invoice = $this->db->query(
			"SELECT id, invoice_uuid, pay_status FROM invoices WHERE id = ? LIMIT 1",
			array($invoiceId)
		)->row_array();
		if (empty($invoice)) show_404();

		if ($invoice['pay_status'] === 'PAID') {
			$this->session->set_flashdata('admin_success', 'That invoice is already paid.');
			redirect('whmazadmin/reseller_wallet/index');
			return;
		}

		if (!$this->actAsCompanyCustomer($companyId)) {
			$this->session->set_flashdata('admin_error',
				'Could not start a payment session. The reseller account needs an active customer user.');
			redirect('whmazadmin/reseller_wallet/index');
			return;
		}

		redirect('invoicing/pay/invoice/' . $invoice['invoice_uuid']);
	}

	/**
	 * Post a manual adjustment. Platform staff only, for the obvious reason.
	 *
	 * This is what replaces typing a number into reseller_profiles.credit_balance
	 * on the Reseller Management form, which accepted any value including a
	 * negative one, with no author, no reason and no trail. It is also the only
	 * mechanism for refunds and corrections.
	 */
	public function adjust()
	{
		if ($this->input->method(TRUE) !== 'POST') show_404();

		if (isResellerAdmin()) {
			tenant_deny('Only platform staff can adjust a credit balance.');
			return;
		}

		$companyId = (int) $this->input->post('reseller_company_id');
		if ($companyId <= 0) {
			$this->session->set_flashdata('admin_error', 'No reseller selected.');
			redirect('whmazadmin/reseller_wallet/index');
			return;
		}

		$amount    = round((float) $this->input->post('amount'), 2);
		$direction = $this->input->post('direction') === 'debit' ? -1 : 1;
		$note      = trim((string) $this->input->post('note'));

		if ($amount <= 0) {
			$this->session->set_flashdata('admin_error', 'Enter an adjustment amount greater than zero.');
			redirect($this->_indexUrl($companyId));
			return;
		}
		if ($note === '') {
			// Required, not optional: an unexplained adjustment is exactly the
			// v1 behaviour this screen exists to end.
			$this->session->set_flashdata('admin_error', 'Give a reason for the adjustment.');
			redirect($this->_indexUrl($companyId));
			return;
		}

		$res = $this->Resellercredit_model->adjust($companyId, $direction * $amount, $note, getAdminId());

		if (empty($res['success'])) {
			$this->session->set_flashdata('admin_error', $res['error']);
		} elseif (!empty($res['already'])) {
			// Same admin, same reseller, same amount, same second — a
			// double-submitted form. Say so rather than claiming a second
			// adjustment was posted.
			$this->session->set_flashdata('admin_success', 'That adjustment was already recorded.');
		} else {
			$this->session->set_flashdata('admin_success',
				'Adjustment recorded. New balance: ' . number_format($res['balance_after'], 2) . '.');
		}

		redirect($this->_indexUrl($companyId));
	}

	// -----------------------------------------------------------------
	// Internals
	// -----------------------------------------------------------------

	/**
	 * Whose wallet this request is acting on.
	 *
	 * A reseller admin is pinned to their own company and the request parameter
	 * is ignored entirely. Same reasoning as Reseller_pricing::_targetReseller():
	 * the capability hook knows the controller and method but not which company
	 * an id names, so ?reseller=<someone else> has to be neutralised here or it
	 * is a cross-tenant read of a competitor's trading position -- and, through
	 * topup(), a way to raise invoices against them.
	 */
	private function _targetReseller()
	{
		if (isResellerAdmin()) {
			return (int) adminCompanyId();
		}
		$id = (int) $this->input->get('reseller');
		if ($id <= 0) $id = (int) $this->input->post('reseller_company_id');
		return $id > 0 ? $id : 0;
	}

	private function _indexUrl($companyId)
	{
		return 'whmazadmin/reseller_wallet/index'
			. (isResellerAdmin() ? '' : '?reseller=' . (int) $companyId);
	}

	/**
	 * Invoices for this reseller currently held for credit.
	 *
	 * Filtered from the global held list rather than queried per reseller: the
	 * "still held" predicate (a held log row with no later successful row for
	 * the same item) is subtle enough that a second copy of it here would
	 * eventually disagree with the release cron's copy, and the two disagreeing
	 * is how an order gets shown as released while staying parked.
	 */
	private function _heldForReseller($companyId)
	{
		$this->load->model('Provisioning_model');
		$scope = tenant_scope_ids_for($companyId);

		$out = array();
		foreach ($this->Provisioning_model->getHeldInvoices(200) as $row) {
			if (in_array((int) $row['company_id'], $scope, true)) {
				$out[] = $row;
			}
		}
		return $out;
	}
}
