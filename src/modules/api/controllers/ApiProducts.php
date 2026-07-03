<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * ApiProducts — the sellable catalog (software products + hosting packages) and
 * supporting reference lists (currencies, billing cycles).
 *
 *   GET /api/v1/products/software     software products with pricing
 *   GET /api/v1/products/hosting      hosting packages
 *   GET /api/v1/products/currencies   currencies
 *   GET /api/v1/products/cycles       billing cycles
 *
 * The catalog is not customer-specific, so any authenticated key may read it.
 */
class ApiProducts extends API_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Plan_model');
	}

	/** Software products (plans) with the full pricing matrix. */
	public function software()
	{
		$plans = $this->Plan_model->get_active_plans();
		$out = array();
		foreach ($plans as $p) {
			$pricing = $this->db->query(
				"SELECT sp.id AS software_pricing_id, sp.currency_id, cur.code AS currency_code,
				        sp.billing_cycle_id, bc.cycle_key, bc.cycle_name,
				        sp.first_pay_amount, sp.recurring_amount
				 FROM software_pricing sp
				 LEFT JOIN currencies cur ON cur.id = sp.currency_id
				 LEFT JOIN billing_cycle bc ON bc.id = sp.billing_cycle_id
				 WHERE sp.product_id = ? AND sp.status = 1",
				array($p['id'])
			)->result_array();

			$out[] = array(
				'id'           => intval($p['id']),
				'plan_key'     => $p['plan_key'] ?? null,
				'name'         => $p['plan_name'] ?? ($p['name'] ?? null),
				'family_group' => $p['family_group'] ?? null,
				'pricing'      => array_map(function ($r) {
					return array(
						'software_pricing_id' => intval($r['software_pricing_id']), // -> cart/add_software
						'currency'      => $r['currency_code'],
						'currency_id'   => intval($r['currency_id']),
						'billing_cycle' => $r['cycle_key'],
						'billing_cycle_id' => intval($r['billing_cycle_id']),
						'setup'         => (float) $r['first_pay_amount'],
						'recurring'     => (float) $r['recurring_amount'],
					);
				}, $pricing),
			);
		}
		$this->ok(array('software' => $out));
	}

	/** Hosting packages with type + server. */
	public function hosting()
	{
		$rows = $this->db->query(
			"SELECT ps.id, ps.product_name, pst.servce_type_name AS type_name,
			        pst.key_name AS type_key, ps.server_id
			 FROM product_services ps
			 LEFT JOIN product_service_types pst ON pst.id = ps.product_service_type_id
			 WHERE ps.status = 1
			 ORDER BY pst.servce_type_name, ps.product_name"
		)->result_array();

		$out = array();
		foreach ($rows as $r) {
			$pricing = $this->db->query(
				"SELECT psp.id AS product_service_pricing_id, psp.currency_id, cur.code AS currency_code,
				        psp.billing_cycle_id, bc.cycle_key, bc.cycle_name, psp.price
				 FROM product_service_pricing psp
				 LEFT JOIN currencies cur ON cur.id = psp.currency_id
				 LEFT JOIN billing_cycle bc ON bc.id = psp.billing_cycle_id
				 WHERE psp.product_service_id = ? AND psp.status = 1",
				array($r['id'])
			)->result_array();

			$out[] = array(
				'id'        => intval($r['id']),
				'name'      => $r['product_name'],
				'type'      => $r['type_key'],
				'type_name' => $r['type_name'],
				'server_id' => intval($r['server_id']),
				'pricing'   => array_map(function ($p) {
					return array(
						'product_service_pricing_id' => intval($p['product_service_pricing_id']), // -> cart/add_hosting
						'currency'      => $p['currency_code'],
						'currency_id'   => intval($p['currency_id']),
						'billing_cycle' => $p['cycle_key'],
						'billing_cycle_id' => intval($p['billing_cycle_id']),
						'price'         => (float) $p['price'],
					);
				}, $pricing),
			);
		}
		$this->ok(array('hosting' => $out));
	}

	public function currencies()
	{
		$rows = $this->db->query("SELECT id, code, symbol FROM currencies WHERE status = 1 ORDER BY id")->result_array();
		$this->ok(array('currencies' => $rows));
	}

	public function cycles()
	{
		$rows = $this->db->query("SELECT id, cycle_key, cycle_name, cycle_days FROM billing_cycle WHERE status = 1 ORDER BY sl")->result_array();
		$this->ok(array('billing_cycles' => $rows));
	}
}
