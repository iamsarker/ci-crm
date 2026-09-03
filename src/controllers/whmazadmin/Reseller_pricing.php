<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Reseller Pricing — the one screen that serves both sides of the two-tier
 * pricing model (v2.0.0 Phase 2).
 *
 *   Reseller admin  (admin_type = 1): edits their OWN selling prices. Their cost
 *                                     is shown read-only beside each field.
 *   Platform admin  (admin_type = 0): picks a reseller from a selector and gets
 *                                     the same grid, plus the ability to set
 *                                     that reseller's negotiated cost.
 *
 * One controller rather than two because the grid, the floor rule and the save
 * path are identical -- only who owns the row and which column is editable
 * differ. Two screens would be two places to keep the floor logic correct.
 *
 * The floor itself is NOT enforced here: Pricing_model::saveResellerRetail()
 * owns it, server-side and per component, so a curl POST that skips this form
 * is checked by exactly the same code the form is.
 */
class Reseller_pricing extends WHMAZADMIN_Controller {

	/** price_overrides.item_type */
	const T_DOMAIN   = 1;
	const T_SERVICE  = 2;
	const T_SOFTWARE = 3;

	function __construct(){
		parent::__construct();
		$this->load->model('Pricing_model');
		if (!$this->isLogin()) {
			redirect('/whmazadmin/authenticate/login', 'refresh');
		}
	}

	/**
	 * The grid. ?type=domain|hosting|software and (platform admin only)
	 * ?reseller=<companies.id>.
	 */
	public function index()
	{
		$type = $this->input->get('type');
		if (!in_array($type, array('domain', 'hosting', 'software'), true)) $type = 'domain';

		$data['type']      = $type;
		$data['is_owner']  = isResellerAdmin();
		$data['resellers'] = $data['is_owner'] ? array() : $this->Pricing_model->resellerList();

		$resellerCompanyId = $this->_targetReseller();
		$data['reseller_company_id'] = $resellerCompanyId;

		$data['rows'] = array();
		if ($resellerCompanyId > 0) {
			$data['rows'] = $this->_grid($type, $resellerCompanyId);
		}

		$this->load->view('whmazadmin/reseller_pricing', $data);
	}

	/**
	 * Save one row of the grid (AJAX).
	 *
	 * One row at a time rather than a whole-grid POST: the floor can reject a
	 * single component, and a bulk save would have to either abort the whole
	 * submission over one bad cell or partially apply it. Per-row keeps the
	 * failure local and the message specific.
	 */
	public function save()
	{
		// Gate on the METHOD, not on $this->input->post(). csrf_verify() unsets
		// the token from $_POST once it has checked it, so a request carrying
		// only a token reads as an empty POST -- see CLAUDE.md, "Known Gotchas".
		if (!$this->input->is_ajax_request() || $this->input->method(TRUE) !== 'POST') {
			show_404();
		}

		$resellerCompanyId = $this->_targetReseller();
		if ($resellerCompanyId <= 0) {
			echo json_encode(buildFailedResponse('No reseller selected.'));
			return;
		}

		$itemType  = $this->_itemType($this->input->post('type'));
		$pricingId = (int) $this->input->post('pricing_id');
		if ($pricingId <= 0) {
			echo json_encode(buildFailedResponse('Invalid pricing row.'));
			return;
		}

		// A reseller may only price items they actually sell -- which is all of
		// them -- but the pricing row must exist, and saveResellerRetail()
		// refuses if it does not.
		$res = $this->Pricing_model->saveResellerRetail($itemType, $pricingId, $resellerCompanyId, array(
			'price'          => $this->input->post('price'),
			'transfer_price' => $this->input->post('transfer_price'),
			'renewal_price'  => $this->input->post('renewal_price'),
		));

		if (empty($res['success'])) {
			echo json_encode(buildFailedResponse($res['message']));
			return;
		}
		echo json_encode(buildSuccessResponse(array('floor' => $res['floor']), $res['message']));
	}

	/**
	 * Set a per-reseller negotiated COST. Platform admin only, for the obvious
	 * reason: a reseller who could edit their own cost would set it to zero.
	 */
	public function save_cost()
	{
		if (!$this->input->is_ajax_request() || $this->input->method(TRUE) !== 'POST') {
			show_404();
		}
		if (isResellerAdmin()) {
			tenant_deny('Only platform staff can set reseller costs.');
			return;
		}

		$resellerCompanyId = (int) $this->input->post('reseller_company_id');
		if ($resellerCompanyId <= 0) {
			echo json_encode(buildFailedResponse('No reseller selected.'));
			return;
		}

		$itemType  = $this->_itemType($this->input->post('type'));
		$pricingId = (int) $this->input->post('pricing_id');
		if ($pricingId <= 0) {
			echo json_encode(buildFailedResponse('Invalid pricing row.'));
			return;
		}

		$res = $this->Pricing_model->saveCostOverride($itemType, $pricingId, $resellerCompanyId, array(
			'price'          => $this->input->post('cost_price'),
			'transfer_price' => $this->input->post('cost_transfer'),
			'renewal_price'  => $this->input->post('cost_renewal'),
		));

		if (empty($res['success'])) {
			echo json_encode(buildFailedResponse($res['message']));
			return;
		}

		$msg = $res['message'];
		if (!empty($res['lifted'])) {
			$this->Pricing_model->notifyLiftedResellers($res['lifted'], $itemType, $pricingId);
			$msg .= ' Their selling price was below the new cost and has been raised to it; they have been emailed.';
		}
		echo json_encode(buildSuccessResponse(array('lifted' => !empty($res['lifted'])), $msg));
	}

	// -----------------------------------------------------------------

	/**
	 * Whose prices this request is editing.
	 *
	 * A reseller admin is pinned to their own company and the request parameter
	 * is ignored entirely -- otherwise ?reseller=<other id> would be a
	 * cross-tenant write, and the capability hook cannot catch it because it
	 * knows the controller and method but not which company an id names.
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

	private function _itemType($type)
	{
		if ($type === 'hosting')  return self::T_SERVICE;
		if ($type === 'software') return self::T_SOFTWARE;
		return self::T_DOMAIN;
	}

	/**
	 * Every sellable pricing row for one item type, each carrying the
	 * reseller's cost (the floor) and their current selling price.
	 *
	 * Costs are resolved through Pricing_model::costFor() rather than read
	 * straight from price_overrides, so the profile-discount fallback is
	 * included and the number shown is the same one the floor check will use.
	 */
	private function _grid($type, $resellerCompanyId)
	{
		$itemType = $this->_itemType($type);
		$retail   = $this->Pricing_model->overridesFor($itemType, $resellerCompanyId, 2);

		if ($itemType === self::T_DOMAIN) {
			$base = $this->db->query(
				"SELECT dp.id, dp.price, dp.transfer, dp.renewal, dp.reg_period,
				        de.extension AS label, c.code AS currency_code, c.symbol AS currency_symbol
				 FROM dom_pricing dp
				 JOIN dom_extensions de ON de.id = dp.dom_extension_id
				 LEFT JOIN currencies c ON c.id = dp.currency_id
				 WHERE dp.status = 1 AND de.status = 1
				 ORDER BY de.extension, c.code, dp.reg_period"
			)->result_array();
			$sub = 'reg_period';
		} elseif ($itemType === self::T_SERVICE) {
			$base = $this->db->query(
				"SELECT psp.id, psp.price, psp.price AS transfer, psp.price AS renewal,
				        ps.product_name AS label, bc.cycle_name, c.code AS currency_code, c.symbol AS currency_symbol
				 FROM product_service_pricing psp
				 JOIN product_services ps ON ps.id = psp.product_service_id
				 LEFT JOIN billing_cycle bc ON bc.id = psp.billing_cycle_id
				 LEFT JOIN currencies c ON c.id = psp.currency_id
				 WHERE psp.status = 1 AND ps.status = 1
				 ORDER BY ps.product_name, c.code, bc.cycle_name"
			)->result_array();
			$sub = 'cycle_name';
		} else {
			$base = $this->db->query(
				"SELECT sp.id, sp.first_pay_amount AS price, sp.first_pay_amount AS transfer,
				        sp.recurring_amount AS renewal,
				        p.name AS label, bc.cycle_name, c.code AS currency_code, c.symbol AS currency_symbol
				 FROM software_pricing sp
				 JOIN plans p ON p.id = sp.product_id
				 LEFT JOIN billing_cycle bc ON bc.id = sp.billing_cycle_id
				 LEFT JOIN currencies c ON c.id = sp.currency_id
				 WHERE sp.status = 1 AND p.is_active = 1
				 ORDER BY p.name, c.code, bc.cycle_name"
			)->result_array();
			$sub = 'cycle_name';
		}

		$out = array();
		foreach ($base as $row) {
			$pid  = (int) $row['id'];
			$cost = $this->Pricing_model->costFor($itemType, $pid, $resellerCompanyId);
			$mine = isset($retail[$pid]) ? $retail[$pid] : array();

			$out[] = array(
				'pricing_id'      => $pid,
				'label'           => $row['label'],
				'sub'             => isset($row[$sub]) ? $row[$sub] : '',
				'currency_code'   => $row['currency_code'],
				'currency_symbol' => $row['currency_symbol'],
				'base_price'      => (float) $row['price'],
				'base_transfer'   => (float) $row['transfer'],
				'base_renewal'    => (float) $row['renewal'],
				'cost_price'      => $cost['price'],
				'cost_transfer'   => $cost['transfer'],
				'cost_renewal'    => $cost['renewal'],
				'my_price'        => isset($mine['price'])          ? $mine['price']          : '',
				'my_transfer'     => isset($mine['transfer_price']) ? $mine['transfer_price'] : '',
				'my_renewal'      => isset($mine['renewal_price'])  ? $mine['renewal_price']  : '',
			);
		}
		return $out;
	}
}
