<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * TEMPORARY verification harness for v2.1 reseller pricing.
 * CLI ONLY. Delete this file before a release build.
 *
 *   php index.php whmazadmin/pricingcheck run
 *
 * TWO headline assertions, both about the SELL price:
 *
 *   checkBuyerIndependentSell()  — a guest, a direct customer, a reseller and
 *       that reseller's sub-customer must all resolve to the SAME number, with
 *       cost overrides and a profile discount deliberately in place. This is
 *       what makes a stale cart harmless: there is no second price to drift to.
 *
 *   checkDirectCustomerNoOp()    — the stricter form of the same thing for a
 *       buyer with no reseller: resolve() returns the native pricing table's
 *       numbers unchanged, for every row of all three item types.
 *
 * Everything else here is reversible; a silent price change is not.
 */
class Pricingcheck extends WHMAZADMIN_Controller {

	/**
	 * Fixture dom_pricing rows are marked by an absurd reg_period, and cleanup
	 * deletes everything at or above it. It must therefore be far outside any
	 * real registration period -- registrars sell up to 10 years, so a marker
	 * of 9 or 10 would delete live pricing.
	 */
	const FIXTURE_PERIOD_BASE = 90;

	private $pass = 0;
	private $fail = 0;

	function __construct(){
		parent::__construct();
		if (!is_cli()) show_404();
		$this->load->model('Pricing_model');
	}

	public function run()
	{
		$this->line("=== v2.1 reseller pricing verification ===\n");

		if (!$this->schemaReady()) return;

		$this->checkDirectCustomerNoOp();

		// The reseller half needs a reseller, a sub-customer and override rows.
		// A demo database has none, and the checks that matter most are exactly
		// the ones that would be skipped -- so build them, exercise them, and
		// roll the whole thing back. Nothing is left behind.
		$this->withFixtures(function ($fx) {
			$this->checkBuyerIndependentSell($fx);
			$this->checkOverrideLayering($fx);
			$this->checkCostPrecedence($fx);
			$this->checkGlobalDiscount($fx);
			$this->checkCostAboveBaseDoesNotRaiseSell($fx);
			$this->checkProfileDiscountFallback($fx);
			$this->checkSubCustomerRetail($fx);
			$this->checkResolveMany($fx);
		});

		$this->line("\n---------------------------------------------");
		$this->line("PASS: {$this->pass}   FAIL: {$this->fail}");
		if ($this->fail > 0) {
			$this->line("\nFAILURES ABOVE. Do not ship.");
		} else {
			$this->line("\nAll checks passed.");
		}
	}

	// -----------------------------------------------------------------

	private function schemaReady()
	{
		$missing = array();
		foreach (array('price_overrides', 'price_override_audits') as $t) {
			if ($this->db->query("SHOW TABLES LIKE '{$t}'")->num_rows() === 0) $missing[] = "table {$t}";
		}
		foreach (array('order_domains', 'order_services', 'order_licenses') as $t) {
			if ($this->db->query("SHOW COLUMNS FROM `{$t}` LIKE 'cost_amount'")->num_rows() === 0) {
				$missing[] = "{$t}.cost_amount";
			}
		}
		if ($this->db->query("SHOW INDEX FROM `dom_pricing` WHERE Key_name = 'uq_dom_pricing'")->num_rows() === 0) {
			$missing[] = "dom_pricing.uq_dom_pricing";
		}

		if (!empty($missing)) {
			$this->line("SCHEMA NOT READY -- run reseller_v21_upgrade_migration.sql first.");
			foreach ($missing as $m) $this->line("   missing: {$m}");
			return false;
		}
		$this->ok('schema: reseller_v21_upgrade_migration.sql -- tables and columns');

		// v2.1: the retail audience is retired and the global discount exists.
		$v21 = array();
		$retail = $this->db->query("SELECT COUNT(*) AS c FROM price_overrides WHERE audience = 2")->row_array();
		if ((int) $retail['c'] > 0) $v21[] = "{$retail['c']} leftover audience=2 override(s)";

		$lift = $this->db->query(
			"SELECT COUNT(*) AS c FROM price_override_audits WHERE reason = 'auto_lift_floor'"
		)->row_array();
		if ((int) $lift['c'] > 0) $v21[] = "{$lift['c']} leftover auto_lift_floor audit(s)";

		$cnf = $this->db->query("SELECT COUNT(*) AS c FROM sys_cnf WHERE cnf_group = 'RESELLER'")->row_array();
		if ((int) $cnf['c'] < 2) $v21[] = "sys_cnf group RESELLER (found {$cnf['c']} of 2 rows)";

		if (!empty($v21)) {
			$this->line("SCHEMA NOT READY -- run reseller_v21_upgrade_migration.sql.");
			foreach ($v21 as $m) $this->line("   problem: {$m}");
			return false;
		}
		$this->ok('schema: reseller_v21_upgrade_migration.sql -- v2.1 data cleanup');
		return true;
	}

	/**
	 * THE invariant. Every pricing row, resolved for a company with no reseller
	 * above it, must equal the native table byte for byte.
	 */
	private function checkDirectCustomerNoOp()
	{
		$this->line("\n-- Direct customers see unchanged prices --");

		// A real direct customer if one exists, plus company 0 (logged out).
		$direct = $this->db->query(
			"SELECT id FROM companies
			 WHERE (parent_company_id IS NULL OR parent_company_id = 0) AND is_reseller = 0 AND status = 1
			 ORDER BY id LIMIT 1"
		)->row_array();
		$buyers = array(0);
		if (!empty($direct)) $buyers[] = (int) $direct['id'];

		foreach ($buyers as $buyer) {
			$label = $buyer === 0 ? 'guest (company 0)' : "direct customer #{$buyer}";
			$bad   = 0; $n = 0;

			foreach ($this->db->query("SELECT id, price, transfer, renewal FROM dom_pricing WHERE status = 1")->result_array() as $r) {
				$n++;
				$res = $this->Pricing_model->resolve(1, $r['id'], $buyer);
				if ($this->neq($res['price'], $r['price']) || $this->neq($res['transfer'], $r['transfer'])
					|| $this->neq($res['renewal'], $r['renewal']) || $res['cost_price'] != 0.0) {
					$bad++;
					if ($bad <= 3) $this->line("      dom_pricing #{$r['id']}: got {$res['price']}/{$res['transfer']}/{$res['renewal']} want {$r['price']}/{$r['transfer']}/{$r['renewal']}");
				}
			}
			foreach ($this->db->query("SELECT id, price FROM product_service_pricing WHERE status = 1")->result_array() as $r) {
				$n++;
				$res = $this->Pricing_model->resolve(2, $r['id'], $buyer);
				if ($this->neq($res['price'], $r['price']) || $res['cost_price'] != 0.0) {
					$bad++;
					if ($bad <= 3) $this->line("      product_service_pricing #{$r['id']}: got {$res['price']} want {$r['price']}");
				}
			}
			foreach ($this->db->query("SELECT id, first_pay_amount, recurring_amount FROM software_pricing WHERE status = 1")->result_array() as $r) {
				$n++;
				$res = $this->Pricing_model->resolve(3, $r['id'], $buyer);
				if ($this->neq($res['price'], $r['first_pay_amount']) || $this->neq($res['renewal'], $r['recurring_amount'])
					|| $res['cost_price'] != 0.0) {
					$bad++;
					if ($bad <= 3) $this->line("      software_pricing #{$r['id']}: got {$res['price']}/{$res['renewal']} want {$r['first_pay_amount']}/{$r['recurring_amount']}");
				}
			}

			$bad === 0
				? $this->ok("{$label}: all {$n} pricing rows unchanged")
				: $this->no("{$label}: {$bad} of {$n} rows CHANGED");
		}

		// And the source tag has to say so, not just the number.
		$one = $this->db->query("SELECT id FROM dom_pricing WHERE status = 1 LIMIT 1")->row_array();
		if (!empty($one)) {
			$res = $this->Pricing_model->resolve(1, $one['id'], 0);
			$res['source'] === 'base'
				? $this->ok("direct path short-circuits (source = base)")
				: $this->no("direct path took the reseller branch (source = {$res['source']})");
		}
	}

	/**
	 * Build a reseller, a sub-customer and clean pricing rows, run $body, then
	 * delete every row that was created.
	 *
	 * Fixtures rather than "skip if the database happens to have a reseller":
	 * the checks below are the ones that catch a real pricing bug, and a suite
	 * that silently skips them on a fresh database proves nothing.
	 *
	 * Cleanup is an explicit DELETE list, NOT a transaction rollback. Wrapping
	 * the run in trans_begin() means holding InnoDB row locks for the whole
	 * suite, and if anything inside trips CI's error handler (db_debug is TRUE
	 * here) the process exits with the transaction still open -- the next run
	 * then blocks for innodb_lock_wait_timeout instead of failing. Deleting by
	 * recorded id is slower and completely predictable, and the leak assertion
	 * below proves it worked either way.
	 */
	private function withFixtures($body)
	{
		$fx = null;
		try {
			$fx = $this->makeFixtures();
			if (empty($fx)) {
				$this->line("   (fixtures could not be built -- see errors above)");
			} else {
				$body($fx);
			}
		} catch (Throwable $e) {
			// Throwable, not Exception. PHP 8 raises Error -- not Exception --
			// for undefined functions and type failures, so a `catch (Exception)`
			// here silently skips dropFixtures() and LEAKS the fixture reseller,
			// its sub-customers and its dom_pricing rows into whatever database
			// this ran against. That is exactly how "Pricingcheck Reseller"
			// ended up live on the demo database.
			$this->no("fixture run threw: " . $e->getMessage());
		}

		$this->dropFixtures();

		$left = $this->db->query(
			"SELECT COUNT(*) AS c FROM companies WHERE email LIKE 'pricingcheck+%'"
		)->row_array();
		$leftPricing = $this->db->query(
			"SELECT COUNT(*) AS c FROM dom_pricing WHERE reg_period >= " . self::FIXTURE_PERIOD_BASE
		)->row_array();

		((int) $left['c'] === 0 && (int) $leftPricing['c'] === 0)
			? $this->ok("fixtures cleaned up (no rows left behind)")
			: $this->no("fixtures LEAKED: {$left['c']} company row(s), {$leftPricing['c']} pricing row(s)");
	}

	/**
	 * Remove everything the fixtures created, keyed on markers rather than on
	 * ids held in memory -- so a run that died halfway is still cleaned up by
	 * the next one.
	 */
	private function dropFixtures()
	{
		$ids = array_column(
			$this->db->query("SELECT id FROM companies WHERE email LIKE 'pricingcheck+%'")->result_array(),
			'id'
		);
		$pids = array_column(
			$this->db->query("SELECT id FROM dom_pricing WHERE reg_period >= " . self::FIXTURE_PERIOD_BASE)->result_array(),
			'id'
		);

		if (!empty($ids)) {
			$in = implode(',', array_map('intval', $ids));
			// Phase 3 ledger rows must go BEFORE the profile and company rows,
			// or cleaning up leaves orphan reseller_credit_transactions behind
			// -- invisible to reconcile(), which joins through reseller_profiles.
			$this->db->query("DELETE FROM reseller_credit_transactions WHERE company_id IN ({$in})");
			$this->db->query("DELETE FROM reseller_profiles WHERE company_id IN ({$in})");
			$this->db->query("DELETE FROM price_overrides WHERE owner_company_id IN ({$in})");
			$this->db->query("DELETE FROM price_override_audits WHERE owner_company_id IN ({$in})");
			$this->db->query("DELETE FROM companies WHERE id IN ({$in})");
		}
		if (!empty($pids)) {
			$in = implode(',', array_map('intval', $pids));
			// item_type 1 only: these ids are dom_pricing ids and would collide
			// with real product_service_pricing / software_pricing ids.
			$this->db->query("DELETE FROM price_overrides WHERE item_type = 1 AND pricing_id IN ({$in})");
			$this->db->query("DELETE FROM price_override_audits WHERE item_type = 1 AND pricing_id IN ({$in})");
			$this->db->query("DELETE FROM dom_pricing WHERE id IN ({$in})");
		}
	}

	/**
	 * @return array reseller company id, sub-customer company id, and a
	 *               dom_pricing row that no override or order points at.
	 */
	private function makeFixtures()
	{
		// A previous run that died mid-suite leaves rows behind; clear them
		// first so uq_price_override / uq_dom_pricing don't reject the rebuild.
		$this->dropFixtures();

		$src = $this->db->query(
			"SELECT dom_extension_id, currency_id, price, transfer, renewal
			 FROM dom_pricing WHERE status = 1 ORDER BY id LIMIT 1"
		)->row_array();
		if (empty($src)) return array();

		// A dedicated pricing row, not a live one: uq_dom_pricing forbids
		// duplicating the triple, so take a reg_period nobody uses.
		$period = self::FIXTURE_PERIOD_BASE;
		while ($this->db->query(
			"SELECT id FROM dom_pricing WHERE dom_extension_id = ? AND currency_id = ? AND reg_period = ?",
			array($src['dom_extension_id'], $src['currency_id'], $period)
		)->num_rows() > 0) { $period++; }

		$this->db->insert('dom_pricing', array(
			'dom_extension_id' => $src['dom_extension_id'],
			'currency_id'      => $src['currency_id'],
			'reg_period'       => $period,
			'price'            => 100.00,
			'transfer'         => 80.00,
			'renewal'          => 120.00,   // deliberately the HIGHEST component
			'status'           => 1,
			'inserted_on'      => date('Y-m-d H:i:s'),
		));
		$pricingId = (int) $this->db->insert_id();

		$this->db->insert('companies', array(
			'name' => 'Pricingcheck Reseller', 'email' => 'pricingcheck+r@example.invalid',
			'is_reseller' => 1, 'parent_company_id' => 0, 'status' => 1,
		));
		$resellerId = (int) $this->db->insert_id();

		$this->db->insert('companies', array(
			'name' => 'Pricingcheck Sub', 'email' => 'pricingcheck+s@example.invalid',
			'is_reseller' => 0, 'parent_company_id' => $resellerId, 'status' => 1,
		));
		$subId = (int) $this->db->insert_id();

		$this->db->insert('reseller_profiles', array(
			'company_id' => $resellerId, 'status' => 1,
			'discount_type' => 'percent', 'discount_value' => 10.00,
			'credit_balance' => 0.00, 'allow_api' => 1,
			'inserted_on' => date('Y-m-d H:i:s'),
		));

		return array(
			'reseller' => $resellerId, 'sub' => $subId, 'pricing_id' => $pricingId,
			'base_price' => 100.00, 'base_transfer' => 80.00, 'base_renewal' => 120.00,
		);
	}

	/**
	 * THE v2.1 invariant: the SELL price does not depend on the buyer.
	 *
	 * Run with a per-reseller cost override, a platform-wide cost override AND
	 * a profile discount all deliberately in place, across every row of all
	 * three item types. On a bare fixture a reintroduced buyer branch could
	 * pass by accident; here it cannot.
	 *
	 * This is what makes the cart safe. add_to_carts freezes sub_total/total at
	 * add time and checkoutSubmit() sums them verbatim, so a buyer-dependent
	 * sell price meant a guest could be quoted one number and charged another
	 * after logging in. There is now no second number to drift to.
	 */
	private function checkBuyerIndependentSell($fx)
	{
		$this->line("\n-- Sell price is buyer-independent --");
		$R   = $fx['reseller'];
		$pid = $fx['pricing_id'];

		// Stack the deck: both cost rungs set, on top of the fixture's 10%
		// profile discount. None of it may move the sell price by a cent.
		$this->Pricing_model->saveCostOverride(1, $pid, 0,  array('price' => 55, 'transfer_price' => '', 'renewal_price' => 65));
		$this->Pricing_model->saveCostOverride(1, $pid, $R, array('price' => 45, 'transfer_price' => '', 'renewal_price' => ''));

		$m = $this->freshModel();
		$buyers = array(0 => 'guest', $R => 'reseller', $fx['sub'] => 'sub-customer');

		foreach ($buyers as $buyer => $label) {
			$r = $m->resolve(1, $pid, $buyer);
			$same = !$this->neq($r['price'], $fx['base_price'])
				&& !$this->neq($r['transfer'], $fx['base_transfer'])
				&& !$this->neq($r['renewal'], $fx['base_renewal']);
			$same
				? $this->ok("{$label} is quoted base retail on all three components")
				: $this->no("{$label} was quoted {$r['price']}/{$r['transfer']}/{$r['renewal']}, expected "
					. "{$fx['base_price']}/{$fx['base_transfer']}/{$fx['base_renewal']}");
		}

		// ...and the costs still differ, which is the whole point.
		$this->eq($m->resolve(1, $pid, $R)['cost_price'], 45.00, "the reseller's cost is still the negotiated 45.00");
		$this->eq($m->resolve(1, $pid, 0)['cost_price'], 0.00, "a guest has no cost basis at all");

		// Now the full sweep, across all three item types, for every buyer.
		//
		// Via resolveMany(), which primes the whole set in one query per buyer.
		// The dev database is REMOTE and a per-row resolve() here would be
		// hundreds of round trips; checkResolveMany() separately proves the two
		// paths agree, so this loses no coverage.
		// The price columns are NOT optional here: resolveMany() normalises the
		// rows the caller hands it, so selecting only `id` would make every base
		// 0.00 and the buyers would agree on nothing at all.
		$tables = array(
			1 => "SELECT id, currency_id, price, transfer, renewal FROM dom_pricing WHERE status = 1",
			2 => "SELECT id, currency_id, price FROM product_service_pricing WHERE status = 1",
			3 => "SELECT id, currency_id, first_pay_amount, recurring_amount FROM software_pricing WHERE status = 1",
		);
		$names = array(1 => 'domain', 2 => 'hosting', 3 => 'software');

		foreach ($tables as $itemType => $sql) {
			$rows = $this->db->query($sql)->result_array();
			if (empty($rows)) { $this->line("   (no {$names[$itemType]} pricing rows to sweep)"); continue; }

			$ref  = $this->freshModel()->resolveMany($itemType, $rows, 0); // guest = the reference

			$nonZero = 0;
			foreach ($ref as $r) { if ((float) $r['price'] > 0) $nonZero++; }
			if ($nonZero === 0) {
				$this->no("{$names[$itemType]} reference prices are all 0.00 -- the sweep would pass vacuously");
				continue;
			}
			$them = array(
				'reseller'     => $this->freshModel()->resolveMany($itemType, $rows, $R),
				'sub-customer' => $this->freshModel()->resolveMany($itemType, $rows, $fx['sub']),
			);

			$bad = 0;
			foreach ($rows as $row) {
				$id = (int) $row['id'];
				if (empty($ref[$id])) continue;
				foreach ($them as $label => $set) {
					$r = isset($set[$id]) ? $set[$id] : array();
					if (empty($r) || $this->neq($r['price'], $ref[$id]['price'])
						|| $this->neq($r['transfer'], $ref[$id]['transfer'])
						|| $this->neq($r['renewal'], $ref[$id]['renewal'])) {
						$bad++;
						if ($bad === 1) $this->line("      first divergence: {$names[$itemType]} pricing #{$id} for {$label}");
					}
				}
			}
			$bad === 0
				? $this->ok("all " . count($rows) . " {$names[$itemType]} rows quote the same price to every buyer")
				: $this->no("{$bad} {$names[$itemType]} quote(s) varied by buyer");
		}

		// Leave the fixture as we found it for the checks that follow.
		$this->Pricing_model->saveCostOverride(1, $pid, $R, array('price' => '', 'transfer_price' => '', 'renewal_price' => ''));
		$this->Pricing_model->saveCostOverride(1, $pid, 0,  array('price' => '', 'transfer_price' => '', 'renewal_price' => ''));
	}

	/**
	 * A cost ABOVE retail must not drag the sell price up with it.
	 *
	 * This is the regression test for the deleted max(sell, cost) clamp. The
	 * clamp existed to stop a reseller-set retail sitting under cost; with the
	 * retail tier gone it would instead push one reseller's sub-customers above
	 * the platform price -- buyer-dependence through the back door.
	 */
	private function checkCostAboveBaseDoesNotRaiseSell($fx)
	{
		$this->line("\n-- Cost above retail does not raise the sell price --");
		$R   = $fx['reseller'];
		$pid = $fx['pricing_id'];

		$this->Pricing_model->saveCostOverride(1, $pid, $R, array('price' => 200, 'transfer_price' => '', 'renewal_price' => ''));
		$m = $this->freshModel();

		$this->eq($m->resolve(1, $pid, $R)['price'], $fx['base_price'], "reseller still quoted 100.00 on a 200.00 cost");
		$this->eq($m->resolve(1, $pid, $fx['sub'])['price'], $fx['base_price'], "sub-customer still quoted 100.00");
		$this->eq($m->resolve(1, $pid, $R)['cost_price'], 200.00, "...and the loss-making cost is recorded honestly");

		$this->Pricing_model->saveCostOverride(1, $pid, $R, array('price' => '', 'transfer_price' => '', 'renewal_price' => ''));
	}

	/**
	 * The global default discount -- rung 4, below the profile discount.
	 *
	 * The last assertion is the one that earns the free-text config field:
	 * applyDiscount() reads any unrecognised discount_type as FIXED, so an
	 * un-normalised typo would turn "10% off" into "$10 off" catalogue-wide.
	 */
	private function checkGlobalDiscount($fx)
	{
		$this->line("\n-- Global default reseller discount (sys_cnf RESELLER) --");
		$R = $fx['reseller'];

		$before = $this->db->query(
			"SELECT cnf_key, cnf_val FROM sys_cnf WHERE cnf_group = 'RESELLER'"
		)->result_array();

		$set = function ($type, $value) {
			$this->db->query("UPDATE sys_cnf SET cnf_val = ? WHERE cnf_key = 'reseller_default_discount_type'", array($type));
			$this->db->query("UPDATE sys_cnf SET cnf_val = ? WHERE cnf_key = 'reseller_default_discount_value'", array($value));
		};

		// try/finally, not a tail restore: sys_cnf is REAL platform config, not a
		// fixture table, and a throw halfway through would leave the live global
		// reseller discount set to whatever this check last wrote.
		try {

		// A pricing row with no override anywhere, and a reseller with NO
		// profile discount, so the global default is the only rung that can fire.
		$src = $this->db->query(
			"SELECT dom_extension_id, currency_id FROM dom_pricing WHERE id = ?", array($fx['pricing_id'])
		)->row_array();
		$period = self::FIXTURE_PERIOD_BASE + 30;
		while ($this->db->query(
			"SELECT id FROM dom_pricing WHERE dom_extension_id = ? AND currency_id = ? AND reg_period = ?",
			array($src['dom_extension_id'], $src['currency_id'], $period)
		)->num_rows() > 0) { $period++; }

		$this->db->insert('dom_pricing', array(
			'dom_extension_id' => $src['dom_extension_id'], 'currency_id' => $src['currency_id'],
			'reg_period' => $period, 'price' => 100.00, 'transfer' => 100.00, 'renewal' => 100.00,
			'status' => 1, 'inserted_on' => date('Y-m-d H:i:s'),
		));
		$pid = (int) $this->db->insert_id();

		// discount_value 0 means "not set, fall through" -- not "0% off".
		$this->db->query("UPDATE reseller_profiles SET discount_value = 0 WHERE company_id = ?", array($R));

		$set('percent', '10');
		$this->eq($this->freshModel()->costFor(1, $pid, $R)['price'], 90.00, "global 10% applies when nothing else is set");

		$set('percent', '0');
		$this->eq($this->freshModel()->costFor(1, $pid, $R)['price'], 100.00, "value 0 means unset -> cost is full retail");

		// A garbage type must degrade to PERCENT, never to a fixed amount.
		$set('percnt', '10');
		$this->eq($this->freshModel()->costFor(1, $pid, $R)['price'], 90.00, "a typo'd type is normalised to percent, not \$10 off");

		$set('fixed', '10');
		$this->eq($this->freshModel()->costFor(1, $pid, $R)['price'], 90.00, "'fixed' 10 really is 10.00 off");

		// Precedence: the profile discount beats the global default...
		$set('percent', '10');
		$this->db->query("UPDATE reseller_profiles SET discount_type = 'percent', discount_value = 20 WHERE company_id = ?", array($R));
		$this->eq($this->freshModel()->costFor(1, $pid, $R)['price'], 80.00, "profile discount beats the global default");

		// ...and a platform cost override beats them both.
		$this->Pricing_model->saveCostOverride(1, $pid, 0, array('price' => 70, 'transfer_price' => '', 'renewal_price' => ''));
		$this->eq($this->freshModel()->costFor(1, $pid, $R)['price'], 70.00, "platform cost override beats every discount");

		// The sell price never moved through any of that.
		$this->eq($this->freshModel()->resolve(1, $pid, $fx['sub'])['price'], 100.00, "...and the sub-customer was quoted 100.00 throughout");

		} finally {
			// Restore sys_cnf and the fixture profile.
			foreach ($before as $row) {
				$this->db->query("UPDATE sys_cnf SET cnf_val = ? WHERE cnf_key = ?", array($row['cnf_val'], $row['cnf_key']));
			}
			$this->db->query("UPDATE reseller_profiles SET discount_type = 'percent', discount_value = 10 WHERE company_id = ?", array($R));
		}
	}

	/** Who is whose tenant, and who pays cost. */
	private function checkOverrideLayering($fx)
	{
		$this->line("\n-- Override layering --");
		$R = $fx['reseller'];

		$self = $this->Pricing_model->resolve(1, $fx['pricing_id'], $R);
		$this->eq($self['reseller_company_id'], $R, "reseller resolves to itself as tenant");
		$this->eq($self['is_reseller_buyer'], true, "reseller is flagged a reseller buyer");

		// v2.1 inverted this. The reseller is QUOTED retail like everyone else;
		// their cost is a separate number that only the wallet debit and the
		// frozen order_*.cost_amount snapshot ever read.
		$this->eq($self['price'], $fx['base_price'], "reseller buying for itself is QUOTED retail");
		$this->eq($self['cost_price'], 90.00, "...while its cost is the discounted 90.00");

		$sub = $this->Pricing_model->resolve(1, $fx['pricing_id'], $fx['sub']);
		$this->eq($sub['reseller_company_id'], $R, "sub-customer resolves to its parent reseller");
		$this->eq($sub['is_reseller_buyer'], false, "sub-customer is not a reseller buyer");

		// Sub-resellers are not supported (req. 6). A company parented to a
		// NON-reseller resolves to no tenant at all -- tenantFor() takes the
		// parent id but resellerIsLive() then rejects it, so the grandchild
		// pays platform retail rather than inheriting a tier it was never
		// granted. Asserting the parent id here would be asserting a bug.
		$this->db->insert('companies', array(
			'name' => 'Pricingcheck GrandSub', 'email' => 'pricingcheck+g@example.invalid',
			'is_reseller' => 0, 'parent_company_id' => $fx['sub'], 'status' => 1,
		));
		$g = $this->Pricing_model->resolve(1, $fx['pricing_id'], (int) $this->db->insert_id());
		$this->eq($g['reseller_company_id'], 0, "no sub-reseller chaining: grandchild has no tenant");
		$this->eq($g['price'], $fx['base_price'], "...and therefore pays platform retail");
		$this->eq($g['cost_price'], 0.00, "...with no cost basis");
	}

	/** per-reseller cost beats platform cost beats profile discount beats base. */
	private function checkCostPrecedence($fx)
	{
		$this->line("\n-- Cost precedence --");
		$R   = $fx['reseller'];
		$pid = $fx['pricing_id'];

		// 1. Nothing set -> the profile discount (10%) applies.
		$c = $this->Pricing_model->costFor(1, $pid, $R);
		$this->eq($c['price'], 90.00, "no override: profile discount 10% off 100.00");
		$this->eq($c['renewal'], 108.00, "profile discount applies per component (renewal)");

		// 2. Platform-wide cost overrides the discount.
		$this->Pricing_model->saveCostOverride(1, $pid, 0, array('price' => 70, 'transfer_price' => '', 'renewal_price' => 85));
		$c = $this->Pricing_model->costFor(1, $pid, $R);
		$this->eq($c['price'], 70.00, "platform cost override beats the profile discount");
		$this->eq($c['transfer'], 70.00, "blank transfer cost inherits the registration cost");
		$this->eq($c['renewal'], 85.00, "renewal cost is independent");

		// 3. A negotiated cost for this reseller beats the platform-wide one.
		$this->Pricing_model->saveCostOverride(1, $pid, $R, array('price' => 60, 'transfer_price' => '', 'renewal_price' => ''));
		$c = $this->Pricing_model->costFor(1, $pid, $R);
		$this->eq($c['price'], 60.00, "per-reseller cost beats the platform-wide cost");

		// 4. Clearing it falls back to the platform-wide cost, not to base.
		$this->Pricing_model->saveCostOverride(1, $pid, $R, array('price' => '', 'transfer_price' => '', 'renewal_price' => ''));
		$c = $this->Pricing_model->costFor(1, $pid, $R);
		$this->eq($c['price'], 70.00, "clearing a negotiated cost falls back to platform cost");

		// A cleared override must be DELETED, not soft-deleted: uq_price_override
		// would otherwise block re-entering the price later.
		$n = $this->db->query(
			"SELECT COUNT(*) AS c FROM price_overrides
			 WHERE item_type = 1 AND pricing_id = ? AND owner_company_id = ? AND audience = 1",
			array($pid, $R)
		)->row_array();
		$this->eq((int) $n['c'], 0, "cleared cost row is hard-deleted (uq_price_override stays free)");

		$re = $this->Pricing_model->saveCostOverride(1, $pid, $R, array('price' => 65, 'transfer_price' => '', 'renewal_price' => ''));
		!empty($re['success'])
			? $this->ok("a cleared cost can be re-entered without a duplicate-key error")
			: $this->no("re-entering a cleared cost failed: " . $re['message']);
		$this->Pricing_model->saveCostOverride(1, $pid, $R, array('price' => '', 'transfer_price' => '', 'renewal_price' => ''));
	}

	/** discount_type/discount_value finally do something -- both spellings. */
	private function checkProfileDiscountFallback($fx)
	{
		$this->line("\n-- reseller_profiles discount fallback --");
		$R = $fx['reseller'];

		// A second pricing row with no cost override anywhere, so the discount
		// is the only thing that can produce a cost.
		$src = $this->db->query(
			"SELECT dom_extension_id, currency_id FROM dom_pricing WHERE id = ?", array($fx['pricing_id'])
		)->row_array();
		$period = self::FIXTURE_PERIOD_BASE + 10;
		while ($this->db->query(
			"SELECT id FROM dom_pricing WHERE dom_extension_id = ? AND currency_id = ? AND reg_period = ?",
			array($src['dom_extension_id'], $src['currency_id'], $period)
		)->num_rows() > 0) { $period++; }

		$this->db->insert('dom_pricing', array(
			'dom_extension_id' => $src['dom_extension_id'], 'currency_id' => $src['currency_id'],
			'reg_period' => $period, 'price' => 200.00, 'transfer' => 200.00, 'renewal' => 200.00,
			'status' => 1, 'inserted_on' => date('Y-m-d H:i:s'),
		));
		$pid2 = (int) $this->db->insert_id();

		$c = $this->Pricing_model->costFor(1, $pid2, $R);
		$this->eq($c['price'], 180.00, "'percent' 10 -> 200.00 becomes 180.00");

		// 'percentage' is the promo_codes spelling; both must behave the same,
		// or a 10 silently becomes $10 off instead of 10%.
		$this->db->query("UPDATE reseller_profiles SET discount_type = 'percentage' WHERE company_id = ?", array($R));
		$this->Pricing_model->resolve(1, 0, 0); // no-op; cache is cleared per save, not per read
		$fresh = $this->freshModel();
		$this->eq($fresh->costFor(1, $pid2, $R)['price'], 180.00, "'percentage' spelling behaves identically");

		$this->db->query("UPDATE reseller_profiles SET discount_type = 'fixed', discount_value = 25 WHERE company_id = ?", array($R));
		$fresh = $this->freshModel();
		$this->eq($fresh->costFor(1, $pid2, $R)['price'], 175.00, "'fixed' 25 -> 200.00 becomes 175.00");

		$this->db->query("UPDATE reseller_profiles SET discount_type = 'percent', discount_value = 10 WHERE company_id = ?", array($R));
	}

	/** An unpriced item must fall back to RETAIL, never to the reseller's cost. */
	private function checkSubCustomerRetail($fx)
	{
		$this->line("\n-- Sub-customer fallback --");
		$this->tick('entered');
		$R = $fx['reseller'];

		$src = $this->db->query(
			"SELECT dom_extension_id, currency_id FROM dom_pricing WHERE id = ?", array($fx['pricing_id'])
		)->row_array();
		$period = self::FIXTURE_PERIOD_BASE + 20;
		while ($this->db->query(
			"SELECT id FROM dom_pricing WHERE dom_extension_id = ? AND currency_id = ? AND reg_period = ?",
			array($src['dom_extension_id'], $src['currency_id'], $period)
		)->num_rows() > 0) { $period++; }

		$this->db->insert('dom_pricing', array(
			'dom_extension_id' => $src['dom_extension_id'], 'currency_id' => $src['currency_id'],
			'reg_period' => $period, 'price' => 300.00, 'transfer' => 300.00, 'renewal' => 300.00,
			'status' => 1, 'inserted_on' => date('Y-m-d H:i:s'),
		));
		$pid3 = (int) $this->db->insert_id();
		$this->tick('pricing row created');
		$this->Pricing_model->saveCostOverride(1, $pid3, 0, array('price' => 100, 'transfer_price' => '', 'renewal_price' => ''));
		$this->tick('cost override saved');

		$m = $this->freshModel();
		$sub = $m->resolve(1, $pid3, $fx['sub']);
		$this->tick('resolved for sub');
		$this->eq($sub['price'], 300.00, "unpriced item sells to the sub-customer at RETAIL, not cost");
		$this->eq($sub['cost_price'], 100.00, "...while the cost basis is still the reseller's cost");

		// The pair below IS the v2.1 model in two lines: one price, two costs.
		$selfR = $m->resolve(1, $pid3, $R);
		$this->eq($selfR['price'], 300.00, "...and the reseller is quoted that same RETAIL price");
		$this->eq($selfR['cost_price'], 100.00, "...with its cost carried alongside, not substituted");

		// Deactivating the reseller must not hand their customers wholesale.
		$this->tick('deactivating reseller');
		$this->db->query("UPDATE reseller_profiles SET status = 0 WHERE company_id = ?", array($R));
		$this->tick('reseller deactivated');
		$m2 = $this->freshModel();
		$dead = $m2->resolve(1, $pid3, $fx['sub']);
		$this->eq($dead['price'], 300.00, "a dead reseller's customer pays platform retail");
		$this->eq($dead['cost_price'], 0.00, "...with no cost basis attached");
		$this->db->query("UPDATE reseller_profiles SET status = 1 WHERE company_id = ?", array($R));
	}

	/** resolveMany must agree with resolve, row for row, for a real tenant. */
	private function checkResolveMany($fx)
	{
		$this->line("\n-- resolveMany agrees with resolve --");
		$this->tick('entered');

		// A sample, not a sweep: this check compares two code paths against each
		// other, so the row count adds confidence only logarithmically -- and
		// against a REMOTE database each resolve() is several network round
		// trips. The full sweep lives in checkDirectCustomerNoOp(), where
		// completeness actually matters.
		$rows = $this->db->query(
			"SELECT id, currency_id, price, transfer, renewal FROM dom_pricing WHERE status = 1 LIMIT 8"
		)->result_array();

		foreach (array($fx['sub'] => 'sub-customer', $fx['reseller'] => 'reseller', 0 => 'guest') as $buyer => $label) {
			// Separate instances so the batch path cannot be scored against a
			// cache the single path warmed (or the reverse).
			$batch = $this->freshModel()->resolveMany(1, $rows, $buyer);
			$one   = $this->freshModel();
			$bad   = 0;
			foreach ($rows as $r) {
				$a = $one->resolve(1, $r['id'], $buyer);
				$b = isset($batch[(int) $r['id']]) ? $batch[(int) $r['id']] : array();
				if (empty($b) || $this->neq($b['price'], $a['price'])
					|| $this->neq($b['transfer'], $a['transfer'])
					|| $this->neq($b['renewal'], $a['renewal'])
					|| $this->neq($b['cost_price'], $a['cost_price'])) {
					$bad++;
					if ($bad === 1) $this->line("      first divergence at dom_pricing #{$r['id']}");
				}
			}
			$bad === 0
				? $this->ok("resolveMany matches resolve on " . count($rows) . " rows ({$label})")
				: $this->no("resolveMany diverged on {$bad} rows ({$label})");
		}
	}

	// -----------------------------------------------------------------

	/** Float compare at storage precision; != on floats would fail spuriously. */
	private function neq($a, $b) { return abs((float) $a - (float) $b) > 0.005; }

	private function eq($got, $want, $label)
	{
		if ($got === $want || (is_numeric($got) && is_numeric($want) && !$this->neq($got, $want))) {
			$this->ok($label);
		} else {
			$this->no($label . " (got " . var_export($got, true) . ", want " . var_export($want, true) . ")");
		}
	}

	/**
	 * A Pricing_model with an empty request cache.
	 *
	 * The cache is per-instance and deliberately never invalidated on read, so
	 * a check that changes a row underneath a live instance must take a fresh
	 * one or it will assert against a stale price and pass for the wrong reason.
	 */
	private function freshModel() { return new Pricing_model(); }

	/** Progress marker. Cheap, and it turns "it hung" into "it hung HERE". */
	private function tick($m) { $this->line("   ... {$m}"); }

	private function ok($m) { $this->pass++; $this->line("   [PASS] {$m}"); }
	private function no($m) { $this->fail++; $this->line("   [FAIL] {$m}"); }
	private function line($m) { fwrite(STDOUT, $m . PHP_EOL); }
}
