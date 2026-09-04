<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * TEMPORARY verification harness for v2.0.0 Phase 2 pricing.
 * CLI ONLY. Delete this file once the phase is signed off.
 *
 *   php index.php whmazadmin/pricingcheck run
 *
 * The headline assertion is the one the whole refactor rests on: for a buyer
 * with no reseller above them, resolve() must return the native pricing table's
 * numbers unchanged, for every row of all three item types. Everything else in
 * Phase 2 is reversible; a silent price change for direct customers is not.
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
		$this->line("=== v2.0.0 Phase 2 pricing verification ===\n");

		if (!$this->schemaReady()) return;

		$this->checkDirectCustomerNoOp();

		// The reseller half needs a reseller, a sub-customer and override rows.
		// A demo database has none, and the checks that matter most are exactly
		// the ones that would be skipped -- so build them, exercise them, and
		// roll the whole thing back. Nothing is left behind.
		$this->withFixtures(function ($fx) {
			$this->checkOverrideLayering($fx);
			$this->checkCostPrecedence($fx);
			$this->checkFloor($fx);
			$this->checkAutoLift($fx);
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

		if (empty($missing)) {
			$this->ok('schema: reseller_v2_phase2_migration.sql has been applied');
			return true;
		}
		$this->line("SCHEMA NOT READY -- run reseller_v2_phase2_migration.sql first.");
		foreach ($missing as $m) $this->line("   missing: {$m}");
		return false;
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

	/** Who is whose tenant, and who pays cost. */
	private function checkOverrideLayering($fx)
	{
		$this->line("\n-- Override layering --");
		$R = $fx['reseller'];

		$self = $this->Pricing_model->resolve(1, $fx['pricing_id'], $R);
		$this->eq($self['reseller_company_id'], $R, "reseller resolves to itself as tenant");
		$this->eq($self['is_reseller_buyer'], true, "reseller is flagged a reseller buyer");
		$this->eq($self['price'], $self['cost_price'], "reseller buying for itself pays cost");

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

	/** The floor rejects per component, server-side, with no form involved. */
	private function checkFloor($fx)
	{
		$this->line("\n-- Price floor (server-side, per component) --");
		$R   = $fx['reseller'];
		$pid = $fx['pricing_id'];
		// Cost is now platform-wide 70 / 70 / 85 from checkCostPrecedence.

		$res = $this->Pricing_model->saveResellerRetail(1, $pid, $R, array(
			'price' => 69, 'transfer_price' => '', 'renewal_price' => '',
		));
		empty($res['success'])
			? $this->ok("register price below cost rejected: " . $res['message'])
			: $this->no("register price below cost was ACCEPTED");

		// The case a single blended floor waves through: register is healthy,
		// renewal is not. Renewal repeats for the life of the domain.
		$res = $this->Pricing_model->saveResellerRetail(1, $pid, $R, array(
			'price' => 200, 'transfer_price' => 200, 'renewal_price' => 84,
		));
		empty($res['success'])
			? $this->ok("renewal below cost rejected despite a healthy register price")
			: $this->no("renewal below cost ACCEPTED -- per-component floor is broken");

		// Blank renewal inherits `price`, so `price` must clear the RENEWAL floor
		// (85), not just its own (70).
		$res = $this->Pricing_model->saveResellerRetail(1, $pid, $R, array(
			'price' => 80, 'transfer_price' => '', 'renewal_price' => '',
		));
		empty($res['success'])
			? $this->ok("blank renewal forces price to clear the renewal floor too")
			: $this->no("price 80 accepted while the inherited renewal floor is 85");

		$res = $this->Pricing_model->saveResellerRetail(1, $pid, $R, array(
			'price' => 'free', 'transfer_price' => '', 'renewal_price' => '',
		));
		empty($res['success'])
			? $this->ok("non-numeric price rejected (not silently coerced to 0)")
			: $this->no("non-numeric price was ACCEPTED");

		$res = $this->Pricing_model->saveResellerRetail(1, $pid, $R, array(
			'price' => 150, 'transfer_price' => 140, 'renewal_price' => 160,
		));
		!empty($res['success'])
			? $this->ok("a price above every floor saves")
			: $this->no("a valid price was rejected: " . $res['message']);
	}

	/** Raising a cost must pull stranded selling prices up with it. */
	private function checkAutoLift($fx)
	{
		$this->line("\n-- Auto-lift on a cost rise --");
		$R   = $fx['reseller'];
		$pid = $fx['pricing_id'];
		// Retail is 150 / 140 / 160 from checkFloor.

		$res = $this->Pricing_model->saveCostOverride(1, $pid, 0, array(
			'price' => 155, 'transfer_price' => 145, 'renewal_price' => 165,
		));
		!empty($res['lifted'][$R])
			? $this->ok("cost rise reported " . count($res['lifted'][$R]) . " lifted component(s)")
			: $this->no("cost rise did NOT lift the underwater selling price");

		$now = $this->Pricing_model->resolve(1, $pid, $fx['sub']);
		$this->eq($now['price'], 155.00, "register price lifted to the new cost");
		$this->eq($now['transfer'], 145.00, "transfer price lifted independently");
		$this->eq($now['renewal'], 165.00, "renewal price lifted independently");

		$audits = $this->db->query(
			"SELECT COUNT(*) AS c FROM price_override_audits
			 WHERE owner_company_id = ? AND pricing_id = ? AND reason = 'auto_lift_floor'",
			array($R, $pid)
		)->row_array();
		((int) $audits['c'] >= 3)
			? $this->ok("{$audits['c']} audit rows written for the lift")
			: $this->no("expected 3+ audit rows, found {$audits['c']}");

		// Lowering a cost must NOT touch a price the reseller chose.
		$this->Pricing_model->saveCostOverride(1, $pid, 0, array(
			'price' => 50, 'transfer_price' => 50, 'renewal_price' => 50,
		));
		$after = $this->Pricing_model->resolve(1, $pid, $fx['sub']);
		$this->eq($after['price'], 155.00, "lowering a cost leaves the reseller's price alone");
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
		$this->eq($m->resolve(1, $pid3, $R)['price'], 100.00, "...and the reseller itself still pays cost");

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
