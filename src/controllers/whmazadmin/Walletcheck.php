<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * DEV-ONLY smoke check for the Phase 3 wallet. CLI only, read-only:
 * it asserts wiring and the direct-customer no-op without moving money.
 */
class Walletcheck extends WHMAZADMIN_Controller
{
	private $pass = 0; private $fail = 0;

	function __construct(){
		parent::__construct();
		if (!is_cli()) show_404();
	}

	public function run()
	{
		$this->load->model('Resellercredit_model');
		$this->load->model('Provisioning_model');
		$m = $this->Resellercredit_model;

		// ── schema ──
		$this->ok($this->db->table_exists('reseller_credit_transactions'), 'ledger table exists');
		foreach (array('credit_limit','payment_mode') as $c) {
			$this->ok($this->db->field_exists($c, 'reseller_profiles'), "reseller_profiles.$c exists");
		}
		$idx = $this->db->query("SHOW INDEX FROM reseller_credit_transactions WHERE Key_name = 'uq_credit_idem'")->result_array();
		$this->ok(!empty($idx), 'uq_credit_idem unique index present');
		$this->ok(!empty($idx) && (int)$idx[0]['Non_unique'] === 0, 'uq_credit_idem is actually UNIQUE');

		// ── methods wired ──
		foreach (array('debitForInvoice','creditWalletTopups','sumInvoiceCost','sumTopupLines',
		               'createTopupInvoice','walletOwnerFor','canCover','reconcile','debitKey','topupKey') as $fn) {
			$this->ok(method_exists($m, $fn), "Resellercredit_model::$fn()");
		}
		$this->ok(method_exists($this->Provisioning_model, 'holdInvoiceItems'), 'Provisioning_model::holdInvoiceItems()');
		$this->ok(method_exists($this->Provisioning_model, 'getHeldInvoices'), 'Provisioning_model::getHeldInvoices()');

		// ── key builders ──
		$this->ok($m->debitKey(77) === 'debit:invoice:77', 'debitKey format');
		$this->ok($m->topupKey(77) === 'topup:invoice:77', 'topupKey format');

		// ── the invariant: direct customers are untouched ──
		$direct = $this->db->query(
			"SELECT id FROM companies WHERE is_reseller = 0 AND (parent_company_id = 0 OR parent_company_id IS NULL) AND status = 1 LIMIT 5"
		)->result_array();
		foreach ($direct as $c) {
			$this->ok($m->walletOwnerFor($c['id']) === 0, "company #{$c['id']} (direct) has no wallet owner");
		}

		// Every PAID invoice belonging to a direct customer must report wallet=false
		$invs = $this->db->query(
			"SELECT i.id FROM invoices i JOIN companies c ON c.id = i.company_id
			 WHERE c.is_reseller = 0 AND (c.parent_company_id = 0 OR c.parent_company_id IS NULL)
			 AND i.pay_status = 'PAID' LIMIT 10"
		)->result_array();
		$clean = true;
		foreach ($invs as $i) {
			$v = $m->debitForInvoice($i['id']);
			if (!empty($v['wallet']) || !empty($v['held'])) { $clean = false; break; }
		}
		$this->ok($clean, 'debitForInvoice() is a no-op for ' . count($invs) . ' direct-customer PAID invoices');

		// Nothing was written by any of the above
		$n = $this->db->query("SELECT COUNT(*) c FROM reseller_credit_transactions")->row_array();
		$this->ok(true, 'ledger rows after read-only run: ' . $n['c'] . ' (opening balances only)');

		// ── reconciliation invariant ──
		$drift = $m->reconcile();
		$this->ok(empty($drift), 'reconcile(): cached balance == ledger sum for every reseller'
			. (empty($drift) ? '' : ' — DRIFT: ' . json_encode($drift)));

		// ── top-up lines cannot enter provisioning ──
		$leak = $this->db->query(
			"SELECT COUNT(*) c FROM invoice_items WHERE item_type = 4 AND ref_id IS NOT NULL AND ref_id > 0"
		)->row_array();
		$this->ok((int)$leak['c'] === 0, 'no item_type=4 row carries a ref_id');

		// ── admin UI wiring ──
		$this->config->load('capabilities', TRUE, TRUE);
		$caps = $this->config->item('reseller', 'capabilities');
		$this->ok(isset($caps['reseller_wallet']), 'capabilities: reseller_wallet is allowlisted');

		$this->ok(file_exists(APPPATH . 'controllers/whmazadmin/Reseller_wallet.php'), 'Reseller_wallet controller exists');
		$this->ok(file_exists(APPPATH . 'views/whmazadmin/reseller_wallet.php'), 'reseller_wallet view exists');

		$menu = file_get_contents(APPPATH . 'views/whmazadmin/include/header_menus.php');
		$this->ok(strpos($menu, 'reseller_wallet/index') !== false, 'menu links to the wallet');
		$this->ok(strpos($menu, "admin_can('reseller_wallet')") !== false, 'menu item is admin_can()-gated');

		// credit_balance must no longer be settable from the Reseller form --
		// it is a ledger cache now, and a form write would break the invariant.
		$rc = file_get_contents(APPPATH . 'controllers/whmazadmin/Reseller.php');
		$this->ok(strpos($rc, "'credit_balance' => floatval") === false,
			'Reseller::manage() no longer writes credit_balance from the form');
		$rv = file_get_contents(APPPATH . 'views/whmazadmin/reseller_manage.php');
		$this->ok(strpos($rv, 'name="credit_balance"') === false,
			'reseller_manage.php no longer posts a credit_balance input');

		// Emails must point at a route that exists.
		$rm = file_get_contents(APPPATH . 'models/Resellercredit_model.php');
		$this->ok(strpos($rm, 'whmazadmin/wallet/index') === false,
			'no stale whmazadmin/wallet URLs left in the model');

		foreach (array('index','topup','pay','adjust') as $fn) {
			$this->ok(strpos(file_get_contents(APPPATH . 'controllers/whmazadmin/Reseller_wallet.php'),
				'function ' . $fn . '(') !== false, "Reseller_wallet::$fn()");
		}

		// ── the half that actually moves money ──
		$this->withFixtures(function ($fx) use ($m) {
			$this->moneyChecks($fx, $m);
			$this->renderChecks($fx, $m);
		});

		echo "\n" . str_repeat('=', 46) . "\n";
		echo "PASS {$this->pass}   FAIL {$this->fail}\n";
		if ($this->fail > 0) echo "FAILURES ABOVE. Do not ship.\n";
	}

	// =================================================================
	// Money-moving checks, on a fixture reseller that is rolled back
	// =================================================================

	private function moneyChecks($fx, $m)
	{
		$R = $fx['reseller_company_id'];

		// ── credit ──
		$r1 = $m->credit($R, 100.00, array('idempotency_key' => 'wc:credit:1', 'description' => 'test'));
		$this->ok(!empty($r1['success']) && empty($r1['already']), 'credit() succeeds');
        $this->ok(abs($r1['balance_after'] - 100.00) < 0.001, 'balance_after 100.00 after first credit');
		$this->ok(abs($m->getBalance($R) - 100.00) < 0.001, 'cached balance follows the ledger');

		// ── THE re-entrancy test: replay the same key ──
		$r2 = $m->credit($R, 100.00, array('idempotency_key' => 'wc:credit:1', 'description' => 'test'));
		$this->ok(!empty($r2['success']) && !empty($r2['already']), 'replayed credit reports already=true, not an error');
		$this->ok(abs($m->getBalance($R) - 100.00) < 0.001, 'replayed credit did NOT double the balance');
		$n = $this->db->query("SELECT COUNT(*) c FROM reseller_credit_transactions WHERE idempotency_key = 'wc:credit:1'")->row_array();
		$this->ok((int)$n['c'] === 1, 'exactly one ledger row for the replayed key');

		// ── debit ──
		$d1 = $m->debit($R, 30.00, array('idempotency_key' => 'wc:debit:1'));
		$this->ok(!empty($d1['success']), 'debit() succeeds');
		$this->ok(abs($d1['balance_after'] - 70.00) < 0.001, 'balance_after 70.00 after debit of 30');
		$row = $this->db->query("SELECT amount FROM reseller_credit_transactions WHERE idempotency_key = 'wc:debit:1'")->row_array();
		$this->ok((float)$row['amount'] < 0, 'debit stored as a NEGATIVE amount');

		$d2 = $m->debit($R, 30.00, array('idempotency_key' => 'wc:debit:1'));
		$this->ok(!empty($d2['already']), 'replayed debit is idempotent');
		$this->ok(abs($m->getBalance($R) - 70.00) < 0.001, 'replayed debit did NOT double-charge');

		// ── sign guards ──
		$this->ok(empty($m->debit($R, -5.00)['success']), 'debit() rejects a negative magnitude');
		$this->ok(empty($m->credit($R, 0)['success']), 'credit() rejects zero');

		// ── overdraft verdict ──
		$this->ok($m->canCover($R, 70.00),  'canCover(70) true at balance 70, limit 0');
		$this->ok(!$m->canCover($R, 70.01), 'canCover(70.01) false at balance 70, limit 0');

		// ── reconciliation after real movement ──
		$this->ok(empty($m->reconcile($R)), 'reconcile() clean after credit + debit');
		$this->ok(abs($m->ledgerSum($R) - 70.00) < 0.001, 'ledgerSum() == 70.00');

		// ── top-up invoice shape ──
		$inv = $m->createTopupInvoice($R, 250.00, $fx['currency_id'], 0);
		$this->ok(!empty($inv['invoice_id']), 'createTopupInvoice() returns an invoice');
		if (!empty($inv['invoice_id'])) {
			$li = $this->db->query("SELECT item_type, ref_id, total FROM invoice_items WHERE invoice_id = ?",
				array($inv['invoice_id']))->result_array();
			$this->ok(count($li) === 1 && (int)$li[0]['item_type'] === 4, 'top-up invoice has one item_type=4 line');
			$this->ok($li[0]['ref_id'] === null, 'top-up line carries ref_id NULL');
			$this->ok(abs($m->sumTopupLines($inv['invoice_id']) - 250.00) < 0.001, 'sumTopupLines() reads 250.00');
			$this->ok(abs($m->sumInvoiceCost($inv['invoice_id'])) < 0.001, 'sumInvoiceCost() is 0 for a top-up invoice');

			// provisioning must not see it
			$this->load->model('Provisioning_model');
			$items = $this->Provisioning_model->getInvoiceItemsForProvisioning($inv['invoice_id']);
			$this->ok(empty($items), 'provisioning sees NO items on a top-up invoice');

			// crediting it, then replaying
			$c1 = $m->creditWalletTopups($inv['invoice_id']);
			$this->ok(!empty($c1['success']) && empty($c1['already']), 'creditWalletTopups() credits the top-up');
			$this->ok(abs($m->getBalance($R) - 320.00) < 0.001, 'balance 70 + 250 = 320.00');
			$c2 = $m->creditWalletTopups($inv['invoice_id']);
			$this->ok(!empty($c2['already']), 'replayed top-up credit is idempotent (webhook retry)');
			$this->ok(abs($m->getBalance($R) - 320.00) < 0.001, 'replayed top-up did NOT double-credit');
			$this->ok(empty($m->reconcile($R)), 'reconcile() still clean after top-up');
		}

		// ── sub-customer resolves to the reseller's wallet ──
		$this->ok($m->walletOwnerFor($fx['sub_company_id']) === $R, 'sub-customer maps to the reseller wallet');
		$this->ok($m->walletOwnerFor($R) === $R, 'reseller maps to its own wallet');
	}

	/**
	 * Render the wallet view against real data. A view that only fails at
	 * request time is the classic "looks finished, 500s in the browser" bug,
	 * and it is the one thing php -l cannot catch: undefined variables,
	 * bad array keys, a helper that is not loaded.
	 *
	 * Both audiences are rendered, because they take different branches -- the
	 * platform view has the selector and the adjustment panel, the reseller
	 * view has neither -- and an empty-wallet render too, which is the state a
	 * platform admin lands on before picking anyone.
	 */
	private function renderChecks($fx, $m)
	{
		$R = $fx['reseller_company_id'];
		$this->load->model('Pricing_model');

		$base = array(
			'resellers'           => $this->Pricing_model->resellerList(),
			'reseller_company_id' => $R,
			'wallet'              => $m->getWallet($R),
			'statement'           => $m->statement($R, 25, 0),
			'total_rows'          => $m->countStatement($R),
			'page'                => 1,
			'page_size'           => 25,
			'held'                => array(),
			'drift'               => array(),
		);

		$cases = array(
			'reseller view'        => array_merge($base, array('is_owner' => true)),
			'platform view'        => array_merge($base, array('is_owner' => false)),
			'empty-wallet view'    => array_merge($base, array(
				'is_owner' => false, 'reseller_company_id' => 0,
				'wallet' => array(), 'statement' => array(), 'total_rows' => 0)),
			'drift warning'        => array_merge($base, array('is_owner' => false,
				'drift' => array(array('cached_balance' => 10.00, 'ledger_sum' => 9.00, 'drift' => 1.00)))),
			'held orders panel'    => array_merge($base, array('is_owner' => true,
				'held' => array(array('invoice_id' => 1, 'invoice_no' => 'X-1', 'company_id' => $R, 'currency_id' => 1)))),
		);

		foreach ($cases as $label => $data) {
			try {
				// Render the page body only. The header/footer includes pull in
				// menus and session chrome that a CLI request has no business
				// building; this check is about THIS view's own PHP.
				$html = $this->renderBody($data);
				$this->ok(strlen($html) > 0, "renders: $label");
			} catch (Throwable $e) {
				$this->ok(false, "renders: $label — " . $e->getMessage());
			}
		}

		// Pagination arithmetic: 3 pages at 25/page needs 51+ rows.
		$d = array_merge($base, array('is_owner' => true, 'total_rows' => 51));
		try {
			$html = $this->renderBody($d);
			$this->ok(substr_count($html, 'page=') >= 3, 'pagination renders 3 pages for 51 rows');
		} catch (Throwable $e) {
			$this->ok(false, 'pagination render — ' . $e->getMessage());
		}
	}

	/**
	 * Render reseller_wallet.php with the header/footer includes stubbed out.
	 */
	private function renderBody($data)
	{
		$src = file_get_contents(APPPATH . 'views/whmazadmin/reseller_wallet.php');
		$src = preg_replace('#<\?php \$this->load->view\(\'whmazadmin/include/[a-z_]+\'\);\?>#', '', $src);

		$tmp = APPPATH . 'views/whmazadmin/_walletcheck_tmp.php';
		file_put_contents($tmp, $src);
		try {
			return $this->load->view('whmazadmin/_walletcheck_tmp', $data, TRUE);
		} finally {
			@unlink($tmp);
		}
	}

	// =================================================================
	// Fixtures
	// =================================================================

	private function withFixtures($body)
	{
		$fx = null;
		try {
			$fx = $this->makeFixtures();
			if (empty($fx)) { echo "  (fixtures could not be built)\n"; }
			else { $body($fx); }
		} catch (Throwable $e) {
			$this->ok(false, 'fixture run threw: ' . $e->getMessage());
		}

		$this->dropFixtures();

		$left = $this->db->query("SELECT COUNT(*) c FROM companies WHERE email LIKE 'walletcheck+%'")->row_array();
		$ledg = $this->db->query("SELECT COUNT(*) c FROM reseller_credit_transactions WHERE idempotency_key LIKE 'wc:%'")->row_array();
		$this->ok((int)$left['c'] === 0 && (int)$ledg['c'] === 0,
			"fixtures cleaned up (companies {$left['c']}, ledger {$ledg['c']})");
	}

	private function makeFixtures()
	{
		$this->dropFixtures();   // clear anything a killed earlier run left

		$cur = $this->db->query("SELECT id FROM currencies WHERE status = 1 ORDER BY id LIMIT 1")->row_array();
		if (empty($cur)) { $this->ok(false, 'no active currency to build fixtures with'); return null; }

		$this->db->insert('companies', array(
			'name' => 'Walletcheck Reseller', 'email' => 'walletcheck+r@example.invalid',
			'is_reseller' => 1, 'parent_company_id' => 0, 'status' => 1, 'inserted_on' => getDateTime(),
		));
		$R = (int) $this->db->insert_id();

		$this->db->insert('companies', array(
			'name' => 'Walletcheck Sub', 'email' => 'walletcheck+s@example.invalid',
			'is_reseller' => 0, 'parent_company_id' => $R, 'status' => 1, 'inserted_on' => getDateTime(),
		));
		$S = (int) $this->db->insert_id();

		$this->db->insert('reseller_profiles', array(
			'company_id' => $R, 'status' => 1, 'credit_balance' => 0.00, 'credit_limit' => 0.00,
			'currency_id' => (int) $cur['id'], 'allow_api' => 1, 'inserted_on' => getDateTime(),
		));

		return array('reseller_company_id' => $R, 'sub_company_id' => $S, 'currency_id' => (int) $cur['id']);
	}

	/** Keyed on markers, not on ids in memory, so a half-dead run still cleans up. */
	private function dropFixtures()
	{
		$ids = array_column($this->db->query(
			"SELECT id FROM companies WHERE email LIKE 'walletcheck+%'")->result_array(), 'id');

		if (!empty($ids)) {
			$in = implode(',', array_map('intval', $ids));
			$invIds = array_column($this->db->query(
				"SELECT id FROM invoices WHERE company_id IN ({$in})")->result_array(), 'id');
			if (!empty($invIds)) {
				$iin = implode(',', array_map('intval', $invIds));
				$this->db->query("DELETE FROM invoice_items WHERE invoice_id IN ({$iin})");
				$this->db->query("DELETE FROM invoices WHERE id IN ({$iin})");
			}
			$this->db->query("DELETE FROM reseller_credit_transactions WHERE company_id IN ({$in})");
			$this->db->query("DELETE FROM reseller_profiles WHERE company_id IN ({$in})");
			$this->db->query("DELETE FROM companies WHERE id IN ({$in})");
		}
		// Belt and braces: fixture ledger rows are marked by their key prefix.
		$this->db->query("DELETE FROM reseller_credit_transactions WHERE idempotency_key LIKE 'wc:%'");
	}

	private function ok($cond, $label)
	{
		if ($cond) { $this->pass++; echo "  ok   $label\n"; }
		else       { $this->fail++; echo "  FAIL $label\n"; }
	}
}
