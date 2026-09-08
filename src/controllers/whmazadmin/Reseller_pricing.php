<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Reseller Pricing — one screen, two audiences, ONE editable number.
 *
 *   Reseller admin  (admin_type = 1): READ-ONLY. Per item they see the retail
 *                                     price their customer pays, the cost they
 *                                     pay the platform, and the margin between.
 *                                     They cannot change any of it.
 *   Platform admin  (admin_type = 0): the same grid for a chosen reseller, plus
 *                                     the ability to set that reseller's
 *                                     negotiated COST.
 *
 * v2.1 removed the reseller's selling price entirely. A reseller charges their
 * client the platform's retail price and pays the platform less for it; the
 * margin is settled through the wallet, not through a price the reseller sets.
 * That is what makes the storefront price buyer-independent -- see the header
 * of Pricing_model for why the cart depended on it.
 *
 * ⚠️ Do not add a write path for the reseller here. capabilities.php narrows
 * this controller to index() for exactly that reason, and save_cost() refuses a
 * reseller admin on its own account as well.
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

		// The global default discount is the bottom rung of the cost ladder and
		// lives in sys_cnf, which has no dedicated screen. Surface it here --
		// this is the page someone is on when they are reasoning about margins.
		$this->load->model('Syscnf_model');
		$data['global_discount'] = $this->Syscnf_model->getByGroup('RESELLER');

		$resellerCompanyId = $this->_targetReseller();
		$data['reseller_company_id'] = $resellerCompanyId;

		$data['rows'] = array();
		if ($resellerCompanyId > 0) {
			$data['rows'] = $this->_grid($type, $resellerCompanyId);
		}

		$this->load->view('whmazadmin/reseller_pricing', $data);
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

		// No follow-up needed since v2.1: the reseller sets no selling price, so
		// a cost change cannot strand one below the new floor.
		echo json_encode(buildSuccessResponse(array(), $res['message']));
	}

	// -----------------------------------------------------------------

	/**
	 * Whose numbers this request is looking at.
	 *
	 * A reseller admin is pinned to their own company and the request parameter
	 * is ignored entirely -- otherwise ?reseller=<other id> would expose a
	 * competitor's cost, and the capability hook cannot catch it because it
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
	 * Every sellable pricing row for one item type, each carrying the platform
	 * retail price, this reseller's cost, and the margin between them.
	 *
	 * Costs go through Pricing_model::costFor() rather than a raw
	 * price_overrides read, so every rung of the ladder is included -- the
	 * per-reseller override, the platform default, the profile discount and the
	 * global default -- and the number shown is the one a real order would use.
	 */
	private function _grid($type, $resellerCompanyId)
	{
		$itemType = $this->_itemType($type);

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

			$basePrice    = (float) $row['price'];
			$baseTransfer = (float) $row['transfer'];
			$baseRenewal  = (float) $row['renewal'];

			$out[] = array(
				'pricing_id'      => $pid,
				'label'           => $row['label'],
				'sub'             => isset($row[$sub]) ? $row[$sub] : '',
				'currency_code'   => $row['currency_code'],
				'currency_symbol' => $row['currency_symbol'],
				'base_price'      => $basePrice,
				'base_transfer'   => $baseTransfer,
				'base_renewal'    => $baseRenewal,
				'cost_price'      => $cost['price'],
				'cost_transfer'   => $cost['transfer'],
				'cost_renewal'    => $cost['renewal'],

				// Margin can be NEGATIVE, and is shown that way on purpose. The
				// resolver no longer clamps sell up to cost, so a cost set above
				// retail is a real loss the platform admin needs to see rather
				// than have quietly corrected into a break-even.
				'margin_price'    => round($basePrice    - $cost['price'],    2),
				'margin_transfer' => round($baseTransfer - $cost['transfer'], 2),
				'margin_renewal'  => round($baseRenewal  - $cost['renewal'],  2),
				'margin_pct'      => $basePrice > 0
					? round(($basePrice - $cost['price']) / $basePrice * 100, 2)
					: 0.0,
			);
		}
		return $out;
	}
}
