<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Resellercredit_model — the reseller prepaid wallet ledger (v2.0.0 Phase 3).
 *
 * Phase 1 gave a reseller an identity, Phase 2 gave it a cost. This is the
 * account those costs are charged against. Every movement of wallet money is an
 * append-only row in `reseller_credit_transactions`; nothing else in the
 * codebase may write `reseller_profiles.credit_balance`, which is now a CACHE
 * of SUM(amount) rather than the free-text admin field it was in v1.
 *
 * THE INVARIANT:
 *     reseller_profiles.credit_balance == SUM(reseller_credit_transactions.amount)
 *     for every reseller, at every instant an observer can see.
 * Both are written inside one transaction, under a row lock on the profile, so
 * they cannot drift. reconcile() proves it; the migration seeds opening rows so
 * it holds for legacy balances too.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * WHY THE LOCKING LOOKS LIKE THIS
 *
 * `Payment_model::processSuccessfulPayment()` has no already-PAID guard and is
 * re-entrant across all 11 of its call sites: every webhook redelivery re-marks
 * the invoice, re-calls provisionPaidServices() and re-sends emails. Paddle
 * retries with backoff over roughly three days. So a wallet write can be asked
 * to happen twice, concurrently, for the same invoice.
 *
 * Two mechanisms answer that, and they are not redundant:
 *
 *   1. `SELECT ... FOR UPDATE` on the reseller's profile row serialises every
 *      wallet operation for that reseller. Two concurrent deliveries queue,
 *      so the second one's in-lock idempotency check SEES the first one's row.
 *      This is what makes the check reliable rather than a TOCTOU race, and it
 *      is also what makes balance_after correct -- it is derived INSIDE the
 *      lock, never from a value read before it.
 *
 *   2. `uq_credit_idem`, the UNIQUE index on idempotency_key, is the backstop
 *      for anything that gets past (1) -- a second application server, a
 *      future caller that forgets the lock. A PHP status check cannot do this
 *      job because only the database evaluates constraints under concurrency.
 *
 * In normal operation (1) means the constraint never actually fires. It firing
 * is a bug signal, not an expected path -- but record() still degrades to
 * "already recorded" rather than a fatal, because a white screen mid-webhook
 * would leave the payment un-provisioned and Paddle would retry into the same
 * wall.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * MONEY SIGN CONVENTION
 *
 * `amount` is signed: positive credits the wallet, negative debits it. The
 * public credit()/debit() methods both take a POSITIVE magnitude and apply the
 * sign themselves, so no caller has to remember which way round it goes -- a
 * caller passing a negative to debit() would otherwise silently top the wallet
 * up. Both reject a non-positive magnitude outright.
 *
 * @see reseller_v2_phase3_migration.sql
 * @see Pricing_model  (Phase 2 — where cost_amount comes from)
 */
class Resellercredit_model extends CI_Model
{
	var $table;

	/** reseller_credit_transactions.txn_type */
	const TYPE_TOPUP      = 'topup';
	const TYPE_DEBIT      = 'debit';
	const TYPE_REFUND     = 'refund';
	const TYPE_ADJUSTMENT = 'adjustment';

	/**
	 * Request cache for wallet-owner lookups. Never persisted to the session:
	 * transferring a customer between resellers must take effect on the next
	 * request, exactly as in tenant_scope_ids_for().
	 */
	private $ownerCache = array();

	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->table = 'reseller_credit_transactions';
	}

	// =================================================================
	// Wallet resolution
	// =================================================================

	/**
	 * Which wallet does an order/invoice for $companyId charge?
	 *
	 * A reseller buying for itself charges its own wallet; a sub-customer's
	 * order charges its parent reseller's. A direct customer has neither, and
	 * returns 0 — which every caller must treat as "no wallet, no-op". That
	 * zero is the same short circuit that makes Phase 2 provably a no-op for
	 * direct customers, and it is why enabling the wallet cannot change a
	 * single direct-customer code path.
	 *
	 * Sub-resellers are not supported (req. 6), so there is no walk up the
	 * chain — one level, deliberately.
	 *
	 * ⚠️ Deliberately NOT filtered on reseller_profiles.status, unlike
	 * Pricing_model::resellerIsLive(). Pricing asks "should this tenant get
	 * tenant prices?", and a deactivated reseller correctly falls back to
	 * platform retail. The ledger asks "whose account did this money move
	 * on?", and that answer does not change when an admin deactivates the
	 * profile — refusing to record would lose the audit trail for money that
	 * moved anyway, which is the failure the whole soft-block design exists to
	 * avoid. The wallet is the profile ROW; status controls trading, not
	 * bookkeeping.
	 *
	 * @return int reseller companies.id, or 0 when there is no wallet
	 */
	public function walletOwnerFor($companyId)
	{
		$companyId = (int) $companyId;
		if ($companyId <= 0) return 0;

		if (array_key_exists($companyId, $this->ownerCache)) {
			return $this->ownerCache[$companyId];
		}

		$row = $this->db->query(
			"SELECT id, is_reseller, parent_company_id FROM companies WHERE id = ? LIMIT 1",
			array($companyId)
		)->row_array();

		if (empty($row)) return $this->ownerCache[$companyId] = 0;

		$owner = ((int) $row['is_reseller'] === 1)
			? $companyId
			: (int) $row['parent_company_id'];

		// The wallet IS the reseller_profiles row. No profile, no wallet.
		if ($owner > 0 && !$this->hasWallet($owner)) $owner = 0;

		return $this->ownerCache[$companyId] = $owner;
	}

	/** Does this company have a wallet (any profile status)? */
	public function hasWallet($resellerCompanyId)
	{
		$row = $this->db->query(
			"SELECT id FROM reseller_profiles WHERE company_id = ? AND deleted_on IS NULL LIMIT 1",
			array((int) $resellerCompanyId)
		)->row_array();
		return !empty($row);
	}

	/** Wallet fields for a reseller, or an empty array when there is no wallet. */
	public function getWallet($resellerCompanyId)
	{
		$row = $this->db->query(
			"SELECT rp.id AS profile_id, rp.company_id, rp.credit_balance, rp.credit_limit,
			        rp.currency_id, rp.payment_mode, rp.status AS profile_status,
			        c.name AS company_name, c.email AS company_email,
			        cur.code AS currency_code, cur.symbol AS currency_symbol
			 FROM reseller_profiles rp
			 JOIN companies c ON c.id = rp.company_id
			 LEFT JOIN currencies cur ON cur.id = rp.currency_id
			 WHERE rp.company_id = ? AND rp.deleted_on IS NULL LIMIT 1",
			array((int) $resellerCompanyId)
		)->row_array();
		return !empty($row) ? $row : array();
	}

	/** Cached running balance. 0.00 when there is no wallet. */
	public function getBalance($resellerCompanyId)
	{
		$w = $this->getWallet($resellerCompanyId);
		return !empty($w) ? (float) $w['credit_balance'] : 0.00;
	}

	/** Permitted overdraft. 0.00 = none, which is the default. */
	public function getCreditLimit($resellerCompanyId)
	{
		$w = $this->getWallet($resellerCompanyId);
		return !empty($w) ? (float) $w['credit_limit'] : 0.00;
	}

	/**
	 * Would a debit of $amount stay within the reseller's overdraft?
	 *
	 * ⚠️ ADVISORY ONLY, and it must stay that way. A false answer does NOT mean
	 * "refuse the debit": by the time provisioning runs, the sub-customer has
	 * already paid, and a refused debit leaves a PAID invoice with no service
	 * and no ledger trace. The caller writes the debit regardless, lets the
	 * balance go negative, and HOLDS PROVISIONING instead. This method exists
	 * so the caller knows to hold, and so the UI can warn before it happens.
	 */
	public function canCover($resellerCompanyId, $amount)
	{
		$w = $this->getWallet($resellerCompanyId);
		if (empty($w)) return true;   // no wallet, nothing to cover

		$after = (float) $w['credit_balance'] - (float) $amount;
		return $after >= -((float) $w['credit_limit']);
	}

	// =================================================================
	// Writes — the only two ways wallet money moves
	// =================================================================

	/**
	 * Add money to a wallet.
	 *
	 * @param int    $resellerCompanyId
	 * @param float  $amount  POSITIVE magnitude
	 * @param array  $opts    txn_type (default topup), idempotency_key, ref_type,
	 *                        ref_id, description, currency_id, inserted_by
	 * @return array {success, already, id, balance_after, error}
	 */
	public function credit($resellerCompanyId, $amount, $opts = array())
	{
		$amount = (float) $amount;
		if ($amount <= 0) {
			return $this->fail('A credit must be a positive amount.');
		}
		$type = !empty($opts['txn_type']) ? $opts['txn_type'] : self::TYPE_TOPUP;
		return $this->record($resellerCompanyId, $amount, $type, $opts);
	}

	/**
	 * Take money out of a wallet.
	 *
	 * Takes a POSITIVE magnitude and stores it negative — see the sign
	 * convention in the class docblock.
	 *
	 * This does NOT check canCover(). The balance is allowed to go negative on
	 * purpose; the caller decides what to do about it (hold provisioning), and
	 * the ledger's job is to stay complete either way.
	 *
	 * @return array {success, already, id, balance_after, error}
	 */
	public function debit($resellerCompanyId, $amount, $opts = array())
	{
		$amount = (float) $amount;
		if ($amount <= 0) {
			return $this->fail('A debit must be a positive amount.');
		}
		$type = !empty($opts['txn_type']) ? $opts['txn_type'] : self::TYPE_DEBIT;
		return $this->record($resellerCompanyId, -$amount, $type, $opts);
	}

	/**
	 * Manual admin correction, signed (positive adds, negative removes).
	 *
	 * This replaces typing a number straight into reseller_profiles
	 * .credit_balance, which is what Reseller::manage() did in v1 — any value,
	 * including negative, with no audit trail and no idea who set it. It is
	 * also the only mechanism for refunds and corrections.
	 */
	public function adjust($resellerCompanyId, $signedAmount, $note = '', $adminId = 0)
	{
		$signedAmount = (float) $signedAmount;
		if ($signedAmount == 0.0) {
			return $this->fail('An adjustment must be a non-zero amount.');
		}

		$adminId = (int) $adminId ?: (int) getAdminId();

		return $this->record($resellerCompanyId, $signedAmount, self::TYPE_ADJUSTMENT, array(
			// Content AND time, at one-second granularity, which buys exactly the
			// semantics a money form wants: a double-submitted identical
			// correction inside the same second is deduped, while two DIFFERENT
			// corrections in that second (different reseller, or different
			// amount) stay distinct. Keying on the timestamp alone would have
			// silently swallowed the second of those and reported success.
			// The same correction repeated a week later is a real second event.
			'idempotency_key' => 'adjust:' . $adminId . ':' . $resellerCompanyId
				. ':' . number_format($signedAmount, 2, '.', '') . ':' . time(),
			'ref_type'        => 'manual',
			'description'     => $note !== '' ? $note : 'Manual adjustment by admin',
			'inserted_by'     => $adminId,
		));
	}

	// =================================================================
	// The locked core
	// =================================================================

	/**
	 * Write one ledger row and move the cached balance, atomically.
	 *
	 * Sequence, and every step is load-bearing:
	 *   1. fast-path idempotency check OUTSIDE any lock — a webhook replay is
	 *      the common case, and it should cost one indexed SELECT, not a row
	 *      lock held while other deliveries queue behind it;
	 *   2. begin;
	 *   3. SELECT ... FOR UPDATE the profile row — serialises this reseller;
	 *   4. re-check the key INSIDE the lock — now authoritative, because (3)
	 *      means no concurrent writer can be mid-insert;
	 *   5. derive balance_after from the value read inside the lock;
	 *   6. insert, then update the cache;
	 *   7. commit.
	 *
	 * ⚠️ NESTING: if a caller is already inside a transaction — and it usually
	 * is, since Pay.php wraps payment handling in trans_start() — CI's
	 * trans_begin() only increments _trans_depth and trans_commit() only
	 * decrements it. The real COMMIT belongs to the outer transaction, which is
	 * correct: the debit and the invoice status change land together or not at
	 * all. FOR UPDATE still holds, because the outer transaction is open. On
	 * failure our rollback likewise only decrements, but a failed query has
	 * already set the driver's _trans_status FALSE, so the outer
	 * trans_complete() rolls the whole group back. The failure propagates
	 * either way.
	 *
	 * @param float $signedAmount already signed by the caller
	 * @return array {success, already, id, balance_after, error}
	 */
	private function record($resellerCompanyId, $signedAmount, $txnType, $opts = array())
	{
		$resellerCompanyId = (int) $resellerCompanyId;
		$signedAmount      = round((float) $signedAmount, 2);

		if ($resellerCompanyId <= 0) {
			return $this->fail('No wallet: missing reseller company id.');
		}
		if ($signedAmount == 0.0) {
			// Rounding can land here (0.004 -> 0.00). A zero-value row would
			// pass every check and tell a reader nothing, so refuse it.
			return $this->fail('Refusing to write a zero-value ledger row.');
		}

		$key = !empty($opts['idempotency_key']) ? (string) $opts['idempotency_key'] : null;

		// ── 1. Fast path: already recorded, no lock taken ──────────────
		if ($key !== null) {
			$existing = $this->findByKey($key);
			if (!empty($existing)) return $this->alreadyDone($existing);
		}

		// A wallet must exist before we lock anything.
		if (!$this->hasWallet($resellerCompanyId)) {
			return $this->fail('Company #' . $resellerCompanyId . ' has no reseller wallet.');
		}

		// ── 2. Begin ───────────────────────────────────────────────────
		if ($this->db->trans_begin() === FALSE) {
			return $this->fail('Could not start a database transaction for the wallet write.');
		}

		// ── 3. Lock this reseller's wallet row ─────────────────────────
		$locked = $this->db->query(
			"SELECT credit_balance, currency_id FROM reseller_profiles
			 WHERE company_id = ? AND deleted_on IS NULL LIMIT 1 FOR UPDATE",
			array($resellerCompanyId)
		)->row_array();

		if (empty($locked)) {
			// Deleted between the check above and the lock. Vanishingly rare,
			// but the alternative is dividing by a wallet that isn't there.
			$this->db->trans_rollback();
			return $this->fail('Wallet for company #' . $resellerCompanyId . ' disappeared mid-write.');
		}

		// ── 4. Authoritative idempotency check, inside the lock ────────
		//
		// This plain SELECT is authoritative, and the reason is worth spelling
		// out because it looks like a TOCTOU race and is not. Step 3 is a
		// LOCKING read, which InnoDB serves from the latest committed data and
		// which does NOT open a REPEATABLE READ snapshot. So this is the
		// transaction's first consistent read, and its snapshot is taken HERE
		// — after we hold the lock. Any competing writer for this reseller must
		// have committed before releasing that lock, so its row is inside our
		// snapshot. We cannot miss it.
		//
		// ⚠️ That argument depends on step 3 coming first. Do not add a plain
		// SELECT between trans_begin() and the FOR UPDATE: it would open the
		// snapshot early, and this check would start reading a version of the
		// table from before the lock was granted.
		if ($key !== null) {
			$existing = $this->findByKey($key);
			if (!empty($existing)) {
				$this->db->trans_rollback();
				return $this->alreadyDone($existing);
			}
		}

		// ── 5. Derive the new balance from the LOCKED read, never from a
		//       value fetched before the lock ───────────────────────────
		$balanceBefore = round((float) $locked['credit_balance'], 2);
		$balanceAfter  = round($balanceBefore + $signedAmount, 2);

		$currencyId = isset($opts['currency_id']) && (int) $opts['currency_id'] > 0
			? (int) $opts['currency_id']
			: (int) $locked['currency_id'];

		$row = array(
			'company_id'      => $resellerCompanyId,
			'currency_id'     => $currencyId,
			'txn_type'        => $txnType,
			'amount'          => $signedAmount,
			'balance_after'   => $balanceAfter,
			'ref_type'        => !empty($opts['ref_type']) ? $opts['ref_type'] : null,
			'ref_id'          => isset($opts['ref_id']) && $opts['ref_id'] !== null ? (int) $opts['ref_id'] : null,
			'idempotency_key' => $key,
			// mb_substr, not substr: the column is 255 CHARACTERS and the
			// connection is utf8mb4, so a byte-wise cut could both overshoot the
			// limit and split a character into invalid bytes.
			'description'     => !empty($opts['description'])
				? (function_exists('mb_substr')
					? mb_substr($opts['description'], 0, 255, 'UTF-8')
					: substr($opts['description'], 0, 255))
				: null,
			'status'          => 1,
			'inserted_on'     => getDateTime(),
			'inserted_by'     => isset($opts['inserted_by']) ? (int) $opts['inserted_by'] : (int) getAdminId(),
		);

		// ── 6. Insert, with db_debug suppressed ────────────────────────
		//
		// db_debug is TRUE outside production (src/config/database.php), and on
		// a query error CI's driver force-rolls-back and then EXITS via
		// display_error(). If uq_credit_idem ever does fire, exiting here would
		// abort the webhook mid-flight and the gateway would retry into the
		// same wall forever. Suppress, inspect the result, decide ourselves.
		$prevDebug = $this->db->db_debug;
		$this->db->db_debug = FALSE;
		$inserted = $this->db->insert($this->table, $row);
		$insertId = $inserted ? (int) $this->db->insert_id() : 0;
		$this->db->db_debug = $prevDebug;

		if (!$inserted || $insertId <= 0) {
			$err = $this->db->error();

			// Distinguish "lost a race we thought we had locked" from a real
			// failure. If the key is present, the money DID move — under some
			// path that bypassed the lock — and reporting failure would invite
			// the caller to write it a second time.
			//
			// Read it with LOCK IN SHARE MODE, and read it BEFORE the rollback.
			// A plain SELECT here would be served from the snapshot opened in
			// step 4, which by definition did NOT contain this row; a locking
			// read goes to the latest committed version and sees it. The row is
			// known to exist (the unique key just rejected a duplicate of it),
			// so this takes a record lock, not a gap lock — no deadlock risk
			// against concurrent inserts of neighbouring keys.
			$existing = array();
			if ($key !== null) {
				$prevDebug = $this->db->db_debug;
				$this->db->db_debug = FALSE;
				$found = $this->db->query(
					"SELECT * FROM {$this->table} WHERE idempotency_key = ? LIMIT 1 LOCK IN SHARE MODE",
					array($key)
				);
				$this->db->db_debug = $prevDebug;
				if ($found) {
					$hit = $found->row_array();
					if (!empty($hit)) $existing = $hit;
				}
			}

			$this->db->trans_rollback();

			if (!empty($existing)) {
				log_message('error', 'Resellercredit: uq_credit_idem fired for key ' . $key
					. ' despite the profile row lock — a writer is bypassing record().');
				return $this->alreadyDone($existing);
			}

			log_message('error', 'Resellercredit: ledger insert failed for company #'
				. $resellerCompanyId . ' — ' . (!empty($err['message']) ? $err['message'] : 'unknown error'));
			return $this->fail('Could not write the wallet ledger entry.');
		}

		// Cache follows the ledger, in the same transaction and the same lock.
		$this->db->query(
			"UPDATE reseller_profiles SET credit_balance = ?, updated_on = ? WHERE company_id = ?",
			array($balanceAfter, getDateTime(), $resellerCompanyId)
		);

		// ── 7. Commit ──────────────────────────────────────────────────
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			return $this->fail('Wallet write rolled back: a statement failed inside the transaction.');
		}
		$this->db->trans_commit();

		return array(
			'success'        => true,
			'already'        => false,
			'id'             => $insertId,
			'amount'         => $signedAmount,
			'balance_before' => $balanceBefore,
			'balance_after'  => $balanceAfter,
			'error'          => '',
		);
	}

	// =================================================================
	// Reads
	// =================================================================

	/** The ledger row for an idempotency key, or an empty array. */
	public function findByKey($key)
	{
		if (empty($key)) return array();
		$row = $this->db->query(
			"SELECT * FROM {$this->table} WHERE idempotency_key = ? LIMIT 1",
			array((string) $key)
		)->row_array();
		return !empty($row) ? $row : array();
	}

	/** Has this exact movement already been written? */
	public function isRecorded($key)
	{
		return !empty($this->findByKey($key));
	}

	// ── Key builders ─────────────────────────────────────────────────
	//
	// Centralised so the writer and any later "did this already happen?"
	// reader cannot disagree about the string. A typo in a key silently
	// disables idempotency, and the symptom is double-charging, so these
	// are never composed at the call site.

	public function topupKey($invoiceId) { return 'topup:invoice:' . (int) $invoiceId; }
	public function debitKey($invoiceId) { return 'debit:invoice:' . (int) $invoiceId; }

	/** Newest-first statement for a reseller. */
	public function statement($resellerCompanyId, $limit = 50, $offset = 0)
	{
		return $this->db->query(
			"SELECT * FROM {$this->table}
			 WHERE company_id = ? AND status = 1
			 ORDER BY id DESC LIMIT ? OFFSET ?",
			array((int) $resellerCompanyId, (int) $limit, (int) $offset)
		)->result_array();
	}

	public function countStatement($resellerCompanyId)
	{
		$row = $this->db->query(
			"SELECT COUNT(*) AS cnt FROM {$this->table} WHERE company_id = ? AND status = 1",
			array((int) $resellerCompanyId)
		)->row_array();
		return !empty($row) ? (int) $row['cnt'] : 0;
	}

	/** Ledger total for a reseller — the authority the cache is checked against. */
	public function ledgerSum($resellerCompanyId)
	{
		$row = $this->db->query(
			"SELECT COALESCE(SUM(amount), 0) AS total FROM {$this->table}
			 WHERE company_id = ? AND status = 1",
			array((int) $resellerCompanyId)
		)->row_array();
		return !empty($row) ? round((float) $row['total'], 2) : 0.00;
	}

	/**
	 * Every reseller whose cached balance disagrees with its ledger.
	 *
	 * This is the class invariant expressed as a query. It should return an
	 * empty array forever: both sides are written in one transaction under one
	 * lock, and the migration seeds opening rows so legacy balances start
	 * reconciled. A non-empty result means something wrote credit_balance
	 * directly — which is exactly the v1 behaviour this model replaces — so
	 * treat a hit as "find the writer", not "correct the number".
	 *
	 * @param int $resellerCompanyId 0 = check every reseller
	 */
	public function reconcile($resellerCompanyId = 0)
	{
		$sql = "SELECT rp.company_id, c.name AS company_name,
				       rp.credit_balance AS cached_balance,
				       COALESCE(SUM(t.amount), 0) AS ledger_sum,
				       ROUND(rp.credit_balance - COALESCE(SUM(t.amount), 0), 2) AS drift
				FROM reseller_profiles rp
				JOIN companies c ON c.id = rp.company_id
				LEFT JOIN {$this->table} t
				       ON t.company_id = rp.company_id AND t.status = 1
				WHERE rp.deleted_on IS NULL";

		$bindings = array();
		if ((int) $resellerCompanyId > 0) {
			$sql .= " AND rp.company_id = ?";
			$bindings[] = (int) $resellerCompanyId;
		}

		$sql .= " GROUP BY rp.company_id, c.name, rp.credit_balance
				  HAVING ROUND(rp.credit_balance - COALESCE(SUM(t.amount), 0), 2) <> 0.00
				  ORDER BY rp.company_id";

		return $this->db->query($sql, $bindings)->result_array();
	}

	// =================================================================
	// Invoice integration — where wallet money actually moves
	//
	// Two hooks, and the asymmetry between them is deliberate:
	//
	//   creditWalletTopups()  runs when an invoice is marked fully PAID.
	//   debitForInvoice()     runs when that invoice PROVISIONS.
	//
	// Credit happens at payment because the money has demonstrably arrived.
	// Debit happens at provisioning because that is when the platform incurs
	// the registrar/server cost — debiting at checkout would let an abandoned
	// DUE invoice drain a reseller's balance for services never rendered.
	// =================================================================

	/** invoice_items.item_type for a wallet top-up line. ref_id is always NULL. */
	const ITEM_TYPE_TOPUP = 4;

	/**
	 * Total COST of everything on an invoice, from the frozen snapshots.
	 *
	 * Reads order_*.cost_amount, frozen at checkout by Cart::_resolveCostAmount()
	 * in Phase 2 — never recomputed here. Provisioning can run days after
	 * checkout, and recomputing would let a cost change in between silently
	 * rewrite what the reseller is billed for an order they already quoted.
	 *
	 * Top-up lines carry no ref_id and so cannot contribute; the WHERE clause
	 * mirrors getInvoiceItemsForProvisioning() exactly, so the set of items
	 * charged for is by construction the set of items provisioned.
	 */
	public function sumInvoiceCost($invoiceId)
	{
		$row = $this->db->query(
			"SELECT COALESCE(SUM(
			            CASE ii.item_type
			                WHEN 1 THEN od.cost_amount
			                WHEN 2 THEN os.cost_amount
			                WHEN 3 THEN ol.cost_amount
			                ELSE 0
			            END), 0) AS total_cost
			 FROM invoice_items ii
			 LEFT JOIN order_domains  od ON ii.item_type = 1 AND od.id = ii.ref_id
			 LEFT JOIN order_services os ON ii.item_type = 2 AND os.id = ii.ref_id
			 LEFT JOIN order_licenses ol ON ii.item_type = 3 AND ol.id = ii.ref_id
			 WHERE ii.invoice_id = ? AND ii.ref_id IS NOT NULL AND ii.ref_id > 0",
			array((int) $invoiceId)
		)->row_array();

		return !empty($row) ? round((float) $row['total_cost'], 2) : 0.00;
	}

	/** Total of the wallet top-up lines on an invoice. 0.00 = not a top-up invoice. */
	public function sumTopupLines($invoiceId)
	{
		$row = $this->db->query(
			"SELECT COALESCE(SUM(total), 0) AS total_topup FROM invoice_items
			 WHERE invoice_id = ? AND item_type = ?",
			array((int) $invoiceId, self::ITEM_TYPE_TOPUP)
		)->row_array();

		return !empty($row) ? round((float) $row['total_topup'], 2) : 0.00;
	}

	/**
	 * Charge a provisioned invoice to the reseller's wallet.
	 *
	 * Called at the top of Invoice_model::provisionPaidServices(). Returns a
	 * verdict the caller acts on rather than a bare bool:
	 *
	 *   wallet = false  no reseller above this invoice — a direct customer.
	 *                   NOTHING happens. This is the same short circuit that
	 *                   makes Phase 2 a no-op for direct customers, and it is
	 *                   why turning the wallet on cannot change a single
	 *                   direct-customer code path.
	 *   held   = true   do NOT provision; the items are parked for the release
	 *                   pass. Either the balance cannot cover the cost, or the
	 *                   invoice is in a currency this wallet does not hold.
	 *
	 * ⚠️ A shortfall does NOT block the debit. By the time this runs the
	 * sub-customer has already paid; refusing to record the movement would
	 * leave a PAID invoice with no service AND no ledger trace, which is
	 * strictly worse than a negative balance. So the debit is written, the
	 * balance goes negative, and PROVISIONING is what gets held. credit_limit
	 * defaults to 0.00, so "no overdraft" is still the default behaviour —
	 * it only changes how the shortfall is handled, not whether one is allowed.
	 *
	 * Idempotent via debit:invoice:{id}: processSuccessfulPayment() re-fires on
	 * every webhook redelivery, and each replay must re-evaluate whether to
	 * hold (a top-up since the last attempt should release the order) without
	 * charging twice.
	 */
	public function debitForInvoice($invoiceId)
	{
		$invoiceId = (int) $invoiceId;
		$verdict = array(
			'wallet'      => false,
			'held'        => false,
			'hold_reason' => '',
			'success'     => true,
			'reseller_company_id' => 0,
			'cost'        => 0.00,
			'balance'     => 0.00,
		);

		$invoice = $this->db->query(
			"SELECT id, company_id, currency_id, invoice_no FROM invoices WHERE id = ? LIMIT 1",
			array($invoiceId)
		)->row_array();
		if (empty($invoice)) return $verdict;

		$reseller = $this->walletOwnerFor($invoice['company_id']);
		if ($reseller <= 0) return $verdict;   // direct customer — untouched

		$verdict['wallet'] = true;
		$verdict['reseller_company_id'] = $reseller;

		$cost = $this->sumInvoiceCost($invoiceId);
		$verdict['cost'] = $cost;

		$wallet = $this->getWallet($reseller);
		if (empty($wallet)) {
			// walletOwnerFor() only returns a company that hasWallet(), so this
			// means the profile vanished between the two reads. Hold rather
			// than provision: an order we cannot charge anyone for is exactly
			// what the hold state is for.
			$verdict['held'] = true;
			$verdict['hold_reason'] = 'Reseller wallet could not be read.';
			return $verdict;
		}
		$verdict['balance'] = (float) $wallet['credit_balance'];

		// Nothing to charge: a top-up invoice, or an order of imported /
		// dns_update items that cost the platform nothing. Provision freely.
		if ($cost <= 0) return $verdict;

		// Single-currency wallets (v2.0.0). Converting would need an FX table
		// this app does not have, and guessing a rate to move real money is
		// worse than holding with a message someone can act on.
		$walletCurrency = (int) $wallet['currency_id'];
		if ($walletCurrency > 0 && $walletCurrency !== (int) $invoice['currency_id']) {
			$verdict['held'] = true;
			$verdict['hold_reason'] = 'Invoice currency does not match the reseller wallet currency; '
				. 'cross-currency debits are not supported.';
			log_message('error', 'Resellercredit: invoice #' . $invoiceId
				. ' held — currency ' . $invoice['currency_id'] . ' vs wallet ' . $walletCurrency);
			return $verdict;
		}

		$res = $this->debit($reseller, $cost, array(
			'idempotency_key' => $this->debitKey($invoiceId),
			'ref_type'        => 'invoice',
			'ref_id'          => $invoiceId,
			'currency_id'     => (int) $invoice['currency_id'],
			'description'     => 'Cost of invoice #' . $invoice['invoice_no'],
		));

		$verdict['success'] = !empty($res['success']);
		if (!$verdict['success']) {
			// The ledger write itself failed — a database problem, not a money
			// problem. Hold rather than provision: provisioning uncharged work
			// is the one outcome with no audit trail at all.
			$verdict['held'] = true;
			$verdict['hold_reason'] = 'Could not record the wallet debit: ' . $res['error'];
			return $verdict;
		}

		// Re-read rather than trusting the returned balance_after. On a replay
		// that figure is historical, and the question being asked here is
		// "can we provision NOW" — a top-up since the original attempt must
		// release the order.
		$balanceNow  = $this->getBalance($reseller);
		$limit       = $this->getCreditLimit($reseller);
		$verdict['balance'] = $balanceNow;

		if ($balanceNow < -$limit) {
			$verdict['held'] = true;
			$verdict['hold_reason'] = 'Insufficient account credit.';
			// Same reasoning as the top-up notice: the hold verdict is what the
			// caller acts on, and it must not depend on mail working. A held
			// order that nobody was emailed about is recoverable (the release
			// cron re-checks every run); an exception thrown here would instead
			// abort provisioning for a paid invoice.
			try {
				$this->notifyInsufficientCredit($reseller, $invoice, $cost, $balanceNow);
			} catch (Throwable $e) {
				log_message('error', 'Resellercredit: insufficient-credit notification failed for invoice #'
					. $invoiceId . ' (hold still applied) — ' . $e->getMessage());
			}
		}

		return $verdict;
	}

	/**
	 * Credit any wallet top-up lines on a freshly-PAID invoice.
	 *
	 * Called from the fully-paid branch of
	 * Payment_model::processSuccessfulPayment(), beside provisionPaidServices().
	 *
	 * A top-up is a NORMAL invoice with one item_type = 4 line, so it inherits
	 * every gateway, signature check, Paddle API re-confirmation, retry rule,
	 * card capture and receipt email that already exists — none of which was
	 * worth reimplementing for a wallet.
	 *
	 * Idempotent via topup:invoice:{id}. That key is doing the real work here:
	 * processSuccessfulPayment() has no already-PAID guard, so a Paddle retry
	 * three days later runs this again against the same invoice.
	 */
	public function creditWalletTopups($invoiceId)
	{
		$invoiceId = (int) $invoiceId;

		$amount = $this->sumTopupLines($invoiceId);
		if ($amount <= 0) return array('topup' => false, 'success' => true);

		$invoice = $this->db->query(
			"SELECT id, company_id, currency_id, invoice_no, invoice_uuid FROM invoices WHERE id = ? LIMIT 1",
			array($invoiceId)
		)->row_array();
		if (empty($invoice)) return array('topup' => false, 'success' => false);

		// A top-up invoice is raised TO the reseller's own company, so the
		// wallet owner is that company itself.
		$reseller = $this->walletOwnerFor($invoice['company_id']);
		if ($reseller <= 0) {
			log_message('error', 'Resellercredit: invoice #' . $invoiceId
				. ' carries a top-up line but company #' . $invoice['company_id'] . ' has no wallet.');
			return array('topup' => true, 'success' => false);
		}

		$res = $this->credit($reseller, $amount, array(
			'txn_type'        => self::TYPE_TOPUP,
			'idempotency_key' => $this->topupKey($invoiceId),
			'ref_type'        => 'invoice',
			'ref_id'          => $invoiceId,
			'currency_id'     => (int) $invoice['currency_id'],
			'description'     => 'Account top-up via invoice #' . $invoice['invoice_no'],
		));

		// Only on a genuinely new credit — a webhook replay must not re-send
		// the receipt.
		//
		// Notification failures are swallowed BY DESIGN. The money has already
		// been committed by this point, and this runs inside
		// processSuccessfulPayment(); letting a dead SMTP server or a missing
		// mbstring extension throw here would abort the caller BEFORE
		// provisioning, turning "the receipt did not send" into "the customer
		// paid and got nothing". The credit is durable either way, and the
		// failure is logged.
		if (!empty($res['success']) && empty($res['already'])) {
			try {
				$this->notifyTopup($reseller, $invoice, $amount, $res['balance_after']);
			} catch (Throwable $e) {
				log_message('error', 'Resellercredit: top-up notification failed for invoice #'
					. $invoiceId . ' (credit itself succeeded) — ' . $e->getMessage());
			}
		}

		return array_merge(array('topup' => true), $res);
	}

	/**
	 * Raise a top-up invoice for a reseller. Returns {invoice_id, invoice_uuid,
	 * invoice_no} or an empty array.
	 *
	 * Deliberately a plain invoice with order_id = 0: there is no order behind
	 * it, and inventing one would put a phantom row in every order report.
	 * provisionPaidServices() loops only items WITH a ref_id, so the single
	 * item_type = 4 line is skipped by provisioning for free.
	 */
	public function createTopupInvoice($resellerCompanyId, $amount, $currencyId = 0, $userId = 0)
	{
		$resellerCompanyId = (int) $resellerCompanyId;
		$amount = round((float) $amount, 2);

		if ($amount <= 0) return array();
		if (!$this->hasWallet($resellerCompanyId)) return array();

		$wallet = $this->getWallet($resellerCompanyId);
		$currencyId = (int) $currencyId ?: (int) $wallet['currency_id'];
		if ($currencyId <= 0) {
			log_message('error', 'Resellercredit: cannot raise a top-up invoice for company #'
				. $resellerCompanyId . ' — no wallet currency set.');
			return array();
		}

		$currency = $this->db->query(
			"SELECT id, code FROM currencies WHERE id = ? LIMIT 1", array($currencyId)
		)->row_array();

		$this->load->model('Order_model');

		$uuid = gen_uuid();
		$invoice = array(
			'invoice_uuid'  => $uuid,
			'company_id'    => $resellerCompanyId,
			'order_id'      => 0,
			'currency_id'   => $currencyId,
			'currency_code' => !empty($currency['code']) ? $currency['code'] : null,
			'invoice_no'    => $this->Order_model->generateNumber('INVOICE'),
			'sub_total'     => $amount,
			'tax'           => 0.00,
			'vat'           => 0.00,
			'discount'      => 0.00,
			'total'         => $amount,
			'order_date'    => getDateOnly(),
			'due_date'      => getDateOnly(),
			'status'        => 1,
			'pay_status'    => 'DUE',
			'inserted_on'   => getDateTime(),
			'inserted_by'   => (int) $userId,
		);

		$this->db->insert('invoices', $invoice);
		$invoiceId = (int) $this->db->insert_id();
		if ($invoiceId <= 0) return array();

		$this->db->insert('invoice_items', array(
			'invoice_id'  => $invoiceId,
			'item'        => 'Account Credit Top-Up',
			'item_desc'   => 'Prepaid credit added to your reseller account balance.',
			'item_type'   => self::ITEM_TYPE_TOPUP,
			'ref_id'      => null,
			'quantity'    => 1,
			'unit_price'  => $amount,
			'sub_total'   => $amount,
			'total'       => $amount,
			'inserted_on' => getDateTime(),
			'inserted_by' => (int) $userId,
		));

		return array(
			'invoice_id'   => $invoiceId,
			'invoice_uuid' => $uuid,
			'invoice_no'   => $invoice['invoice_no'],
			'total'        => $amount,
		);
	}

	// =================================================================
	// Notifications
	// =================================================================

	private function notifyTopup($resellerCompanyId, $invoice, $amount, $balanceAfter)
	{
		$w = $this->getWallet($resellerCompanyId);
		if (empty($w) || empty($w['company_email'])) return false;

		return $this->sendWalletEmail('reseller_wallet_topup', $w['company_email'], array(
			'{reseller_name}'   => $w['company_name'],
			'{amount}'          => number_format($amount, 2),
			'{balance}'         => number_format($balanceAfter, 2),
			'{currency}'        => !empty($w['currency_code']) ? $w['currency_code'] : '',
			'{currency_symbol}' => !empty($w['currency_symbol']) ? $w['currency_symbol'] : '',
			'{invoice_no}'      => $invoice['invoice_no'],
			'{invoice_url}'     => base_url() . 'invoicing/view_invoice/' . $invoice['invoice_uuid'],
		), 'Your account has been credited',
		   '<p>Dear ' . html_escape($w['company_name']) . ',</p><p>Your account has been credited with '
		   . number_format($amount, 2) . '. New balance: ' . number_format($balanceAfter, 2) . '.</p>');
	}

	private function notifyInsufficientCredit($resellerCompanyId, $invoice, $cost, $balance)
	{
		$w = $this->getWallet($resellerCompanyId);
		if (empty($w) || empty($w['company_email'])) return false;

		$sent = $this->sendWalletEmail('reseller_wallet_insufficient', $w['company_email'], array(
			'{reseller_name}'   => $w['company_name'],
			'{amount}'          => number_format($cost, 2),
			'{balance}'         => number_format($balance, 2),
			'{currency}'        => !empty($w['currency_code']) ? $w['currency_code'] : '',
			'{currency_symbol}' => !empty($w['currency_symbol']) ? $w['currency_symbol'] : '',
			'{invoice_no}'      => $invoice['invoice_no'],
			'{invoice_url}'     => base_url() . 'invoicing/view_invoice/' . $invoice['invoice_uuid'],
			'{topup_url}'       => base_url() . 'whmazadmin/reseller_wallet/index',
		), 'Action required: order held, insufficient credit',
		   '<p>Dear ' . html_escape($w['company_name']) . ',</p><p>An order on invoice #'
		   . html_escape($invoice['invoice_no']) . ' could not be provisioned: it costs '
		   . number_format($cost, 2) . ' and your balance is ' . number_format($balance, 2)
		   . '. The order is held, not cancelled — top up and it will be provisioned automatically.</p>');

		// The platform operator needs to know too: a held order is a customer
		// who has paid and has nothing, and nobody is watching the reseller's
		// inbox on our behalf.
		$settings = getAppSettings();
		$adminBody = '<p>Reseller <strong>' . html_escape($w['company_name']) . '</strong> (company #'
			. (int) $resellerCompanyId . ') has a held order on invoice #'
			. html_escape($invoice['invoice_no']) . '.</p><p>Cost ' . number_format($cost, 2)
			. ', balance ' . number_format($balance, 2) . '.</p>';

		if (!empty($settings) && !empty($settings->email)) {
			$this->sendWalletEmail('', $settings->email, array(),
				'Reseller order held: insufficient credit', $adminBody);
		}

		$this->notifyHeldInApp($resellerCompanyId, $w, $invoice, $cost, $balance);

		return $sent;
	}

	/**
	 * In-app notification for a held order — to the reseller's own admin logins
	 * and to platform staff, and to nobody else.
	 *
	 * ⚠️ Deliberately NOT Notification_model::notifyAdmins(): that helper posts
	 * to every row in admin_users, which since Phase 1 includes every OTHER
	 * reseller's admin login. It predates admin-portal tenancy and is safe only
	 * for platform-wide events; a held order names a specific reseller and
	 * their customer's invoice, so broadcasting it would leak one tenant's
	 * trading position to their competitors.
	 */
	private function notifyHeldInApp($resellerCompanyId, $wallet, $invoice, $cost, $balance)
	{
		$this->load->model('Notification_model');

		$symbol = !empty($wallet['currency_symbol']) ? $wallet['currency_symbol'] : '';
		$title  = 'Order held: insufficient credit';
		$msg    = 'Invoice #' . $invoice['invoice_no'] . ' costs ' . $symbol . number_format($cost, 2)
			. ' and the balance is ' . $symbol . number_format($balance, 2)
			. '. The order is held until the account is topped up.';

		// The reseller's own admin logins.
		$mine = $this->db->query(
			"SELECT id FROM admin_users WHERE status = 1 AND admin_type = 1 AND company_id = ?",
			array((int) $resellerCompanyId)
		)->result_array();

		// Platform staff.
		$staff = $this->db->query(
			"SELECT id FROM admin_users WHERE status = 1 AND admin_type = 0"
		)->result_array();

		foreach (array_merge($mine, $staff) as $a) {
			$this->Notification_model->add(array(
				'recipient_type' => Notification_model::RECIPIENT_ADMIN,
				'recipient_id'   => (int) $a['id'],
				'type'           => 'wallet',
				'title'          => $title,
				'message'        => $msg,
				'url'            => base_url() . 'whmazadmin/reseller_wallet/index',
				'icon'           => 'fa-wallet',
			));
		}
	}

	/**
	 * Render an email template and send it, falling back to a plainer hardcoded
	 * body when the template row is missing.
	 *
	 * The fallback is not defensive padding: these two notices are the only
	 * signal a reseller gets that their customer's paid order is sitting
	 * un-provisioned. Degrading to plainer wording beats degrading to silence.
	 * Pass an empty $templateKey to send the fallback directly.
	 */
	private function sendWalletEmail($templateKey, $toEmail, $placeholders, $fallbackSubject, $fallbackBody)
	{
		$subject = $fallbackSubject;
		$body    = $fallbackBody;

		if ($templateKey !== '') {
			$tpl = $this->db->query(
				"SELECT subject, body FROM email_templates WHERE template_key = ? AND status = 1 LIMIT 1",
				array($templateKey)
			)->row_array();

			if (!empty($tpl)) {
				$subject = $tpl['subject'];
				$body    = $tpl['body'];
			} else {
				log_message('error', 'Resellercredit: email template "' . $templateKey
					. '" missing — sent the hardcoded fallback instead.');
			}
		}

		$settings = getAppSettings();
		$placeholders['{site_name}']    = !empty($settings->company_name) ? $settings->company_name : '';
		$placeholders['{company_name}'] = $placeholders['{site_name}'];
		$placeholders['{site_url}']     = base_url();

		foreach ($placeholders as $k => $v) {
			$v = $v === null ? '' : $v;
			$subject = str_replace($k, $v, $subject);
			$body    = str_replace($k, $v, $body);
		}

		return sendHtmlEmail($toEmail, $subject, $body);
	}

	// =================================================================
	// Internals
	// =================================================================

	/**
	 * "This movement was already written." Reported as SUCCESS with
	 * already = true, never as an error: the caller asked for the money to
	 * have moved, and it has. Returning failure here is what would make a
	 * webhook retry loop look like a broken payment.
	 */
	private function alreadyDone($row)
	{
		return array(
			'success'       => true,
			'already'       => true,
			'id'            => (int) $row['id'],
			'amount'        => (float) $row['amount'],
			'balance_after' => (float) $row['balance_after'],
			'error'         => '',
		);
	}

	private function fail($message)
	{
		return array(
			'success'       => false,
			'already'       => false,
			'id'            => 0,
			'amount'        => 0.00,
			'balance_after' => 0.00,
			'error'         => $message,
		);
	}
}
