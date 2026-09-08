<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pricing_model — the single resolver for reseller pricing.
 *
 * v2.0.0 Phase 2 introduced this file with a TWO-TIER model: the platform set
 * a cost and the reseller set their own selling price for their sub-customers
 * (price_overrides.audience = 2). v2.1 removed the second tier. A reseller now
 * sets no price of any kind; they collect the platform's retail price from
 * their client and pay the platform less for it.
 *
 * THE INVARIANT THAT MAKES THIS SAFE — and it is now two invariants:
 *
 *   1. SELL IS BUYER-INDEPENDENT. resolve() returns the native table's price
 *      to every buyer: guest, direct customer, sub-customer, reseller. There
 *      is no branch, and there must never be one again. add_to_carts stores
 *      sub_total/total once at add time and checkoutSubmit() sums them
 *      verbatim, so a buyer-dependent price meant a visitor could be quoted
 *      one number as a guest and charged another after logging in, with
 *      nothing in between to re-price the cart.
 *
 *   2. A buyer with no live reseller above them never touches price_overrides
 *      at all — sell = base, cost = 0 (step 2 of resolveFromBase). Every
 *      direct customer's cart total is bit-identical to pre-Phase-2 by
 *      construction, not by testing.
 *
 * Vocabulary, because two different words both sound like "price":
 *     base / sell -- the native table's number. Platform retail. What EVERY
 *                    buyer is quoted and charged. Never overridden.
 *     cost        -- what a reseller pays the platform. audience = AUD_COST.
 *                    Feeds only the frozen order_*.cost_amount snapshot and
 *                    the Phase 3 wallet debit; never quoted to anyone.
 *
 * The reseller's margin is the gap between the two, and it is settled through
 * the wallet, not through the price the customer sees.
 *
 * Domains carry three components (register / transfer / renewal) and each is
 * costed independently -- a single blended cost would misprice renewals, which
 * are the ones that repeat for the life of the domain.
 */
class Pricing_model extends CI_Model
{
	/** item_type, matching invoice_items.item_type / add_to_carts.item_type. */
	const ITEM_DOMAIN   = 1;
	const ITEM_SERVICE  = 2;
	const ITEM_SOFTWARE = 3;

	/** price_overrides.audience */
	const AUD_COST   = 1; // what the reseller pays us

	/**
	 * RETIRED in v2.1. No code reads or writes this audience any more, and
	 * reseller_v21_upgrade_migration.sql deletes every such row.
	 * Kept only so the numeral in price_overrides.audience still has a name.
	 * Do not resurrect it: a reseller-set selling price is exactly the
	 * buyer-dependent price that broke the cart.
	 */
	const AUD_RETAIL = 2;

	/** Request cache. Never persisted: a price change must land on the next request. */
	private $resolveCache = array();
	private $tenantCache  = array();

	/**
	 * The sys_cnf global discount, or false once looked up and found unset.
	 * Deliberately NOT in $resolveCache, which is wiped on every override save.
	 */
	private $globalDiscountCache = null;

	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	// -----------------------------------------------------------------
	// Resolution
	// -----------------------------------------------------------------

	/**
	 * Resolve one pricing row for one buyer.
	 *
	 * @param int      $itemType  ITEM_DOMAIN | ITEM_SERVICE | ITEM_SOFTWARE
	 * @param int      $pricingId row id in the native pricing table
	 * @param int|null $companyId buying companies.id; null = the logged-in customer
	 *
	 * @return array {
	 *     price, transfer, renewal   -- what this buyer pays. Since v2.1 these
	 *                                   are ALWAYS equal to base_* -- the keys
	 *                                   are kept so the ~20 call sites that
	 *                                   overwrite them in place need no change.
	 *     cost_price, cost_transfer, cost_renewal -- what the reseller pays us;
	 *                                   0 for a direct customer. Wallet-facing.
	 *     base_price, base_transfer, base_renewal -- platform retail
	 *     currency_id, reseller_company_id, is_reseller_buyer, source
	 * }
	 * Empty array when the pricing row does not exist -- callers already treat
	 * an empty pricing lookup as "not for sale", so that path is unchanged.
	 */
	public function resolve($itemType, $pricingId, $companyId = null)
	{
		$itemType  = (int) $itemType;
		$pricingId = (int) $pricingId;
		if ($companyId === null) $companyId = (int) getCompanyId();
		$companyId = (int) $companyId;

		$ck = $itemType . ':' . $pricingId . ':' . $companyId;
		if (isset($this->resolveCache[$ck])) return $this->resolveCache[$ck];

		$base = $this->basePrice($itemType, $pricingId);
		if (empty($base)) return $this->resolveCache[$ck] = array();

		$out = $this->resolveFromBase($base, $itemType, $pricingId, $companyId);
		return $this->resolveCache[$ck] = $out;
	}

	/**
	 * Batch sibling of resolve(): one query per override lookup instead of N.
	 *
	 * The TLD grid renders every extension the platform sells, so resolving it
	 * one row at a time would turn a single query into ~40 round-trips on a page
	 * that is already the storefront's first paint. Same contract as resolve();
	 * returns [pricing_id => resolved array].
	 *
	 * @param array $bases rows from the native pricing table, each carrying at
	 *                     least id + the price columns for $itemType. Passing
	 *                     the rows in (rather than a list of ids) lets callers
	 *                     reuse the SELECT they already ran.
	 */
	public function resolveMany($itemType, $bases, $companyId = null)
	{
		$itemType = (int) $itemType;
		if ($companyId === null) $companyId = (int) getCompanyId();
		$companyId = (int) $companyId;

		$out = array();
		if (empty($bases)) return $out;

		$ids = array();
		foreach ($bases as $b) {
			if (!empty($b['id'])) $ids[] = (int) $b['id'];
		}
		if (empty($ids)) return $out;

		$tenant = $this->tenantFor($companyId);

		// Warm the override cache for the whole set in one pass, so the
		// per-row resolveFromBase() calls below hit memory only.
		$this->primeOverrides($itemType, $ids, $tenant['reseller_company_id']);

		foreach ($bases as $b) {
			$pid = (int) $b['id'];
			$out[$pid] = $this->resolveFromBase(
				$this->normaliseBase($itemType, $b), $itemType, $pid, $companyId
			);
		}
		return $out;
	}

	/**
	 * The actual decision, shared by resolve() and resolveMany().
	 *
	 * 1. sell = base. Unconditionally, before any branch. Every buyer -- guest,
	 *    direct customer, sub-customer, reseller -- is quoted platform retail.
	 * 2. No reseller in the picture -> cost = 0. Done, price_overrides untouched.
	 * 3. cost = costLadder(): negotiated ?? platform default ?? profile discount
	 *    ?? global discount ?? base. Feeds ONLY the frozen order_*.cost_amount
	 *    snapshot and the wallet debit -- never what anyone is quoted.
	 */
	private function resolveFromBase($base, $itemType, $pricingId, $companyId)
	{
		$tenant = $this->tenantFor($companyId);
		$R      = (int) $tenant['reseller_company_id'];

		$out = array(
			'currency_id'         => isset($base['currency_id']) ? (int) $base['currency_id'] : 0,
			'base_price'          => (float) $base['price'],
			'base_transfer'       => (float) $base['transfer'],
			'base_renewal'        => (float) $base['renewal'],
			'reseller_company_id' => $R,
			'is_reseller_buyer'   => (bool) $tenant['is_reseller'],
		);

		// --- Step 1: sell. No branches, by design. ---
		//
		// The sell price is buyer-INDEPENDENT and this is the whole point of
		// v2.1. add_to_carts.sub_total/total are written once at add time and
		// checkoutSubmit() sums them verbatim, so any buyer-dependent price
		// meant a guest could add at one number and check out at another with
		// nothing in between to re-price the cart. A cart cannot go stale about
		// a number that never varies.
		//
		// ⚠️ Do NOT reintroduce a branch here -- not for the reseller, not for
		// a sub-customer, not "just for display". The reseller's margin is the
		// gap between this number and cost_*, taken from their wallet.
		$out['price']    = (float) $base['price'];
		$out['transfer'] = (float) $base['transfer'];
		$out['renewal']  = (float) $base['renewal'];

		// --- Step 2: the direct-customer short circuit. ---
		// A buyer with no live reseller above them never reads price_overrides
		// at all, which is what makes reseller pricing provably a no-op for
		// every direct customer -- by construction, not by testing.
		if ($R <= 0) {
			$out['cost_price']    = 0.0;
			$out['cost_transfer'] = 0.0;
			$out['cost_renewal']  = 0.0;
			$out['source']        = 'base';
			return $out;
		}

		// --- Step 3: cost. Wallet-facing only; never quoted to anyone. ---
		//
		// Deliberately NOT clamped to base. If a cost ends up above retail the
		// snapshot must record that honestly so the margin shows as negative on
		// the admin grid; clamping sell up to meet it would push one reseller's
		// sub-customers above the platform price and re-arm the exact
		// buyer-dependence step 1 exists to remove.
		$cost = $this->costLadder($itemType, $pricingId, $R, $base);

		$out['cost_price']    = $cost['price'];
		$out['cost_transfer'] = $cost['transfer'];
		$out['cost_renewal']  = $cost['renewal'];
		$out['source']        = $cost['source'] . (!empty($tenant['is_reseller']) ? '/self' : '');

		return $out;
	}

	// -----------------------------------------------------------------
	// Base prices
	// -----------------------------------------------------------------

	/**
	 * The native pricing row, normalised to price/transfer/renewal/currency_id.
	 *
	 * Services and software have no transfer or renewal concept, so both mirror
	 * the recurring figure -- that keeps every downstream caller on one shape
	 * instead of branching on item type.
	 */
	private function basePrice($itemType, $pricingId)
	{
		switch ($itemType) {
			case self::ITEM_DOMAIN:
				$row = $this->db->query(
					"SELECT id, currency_id, price, transfer, renewal, reg_period
					 FROM dom_pricing WHERE id = ? AND status = 1", array($pricingId)
				)->row_array();
				break;

			case self::ITEM_SERVICE:
				$row = $this->db->query(
					"SELECT id, currency_id, price, billing_cycle_id, product_service_id
					 FROM product_service_pricing WHERE id = ? AND status = 1", array($pricingId)
				)->row_array();
				break;

			case self::ITEM_SOFTWARE:
				$row = $this->db->query(
					"SELECT id, currency_id, first_pay_amount, recurring_amount, billing_cycle_id, product_id
					 FROM software_pricing WHERE id = ? AND status = 1", array($pricingId)
				)->row_array();
				break;

			default:
				return array();
		}
		return empty($row) ? array() : $this->normaliseBase($itemType, $row);
	}

	/** Fold a native pricing row into the common price/transfer/renewal shape. */
	private function normaliseBase($itemType, $row)
	{
		if ($itemType == self::ITEM_SOFTWARE) {
			$first  = isset($row['first_pay_amount']) ? (float) $row['first_pay_amount'] : 0.0;
			$recur  = isset($row['recurring_amount']) ? (float) $row['recurring_amount'] : $first;
			return array(
				'id'          => isset($row['id']) ? (int) $row['id'] : 0,
				'currency_id' => isset($row['currency_id']) ? (int) $row['currency_id'] : 0,
				'price'       => $first,
				'transfer'    => $first,   // not a software concept
				'renewal'     => $recur,
			);
		}

		$price = isset($row['price']) ? (float) $row['price'] : 0.0;
		return array(
			'id'          => isset($row['id']) ? (int) $row['id'] : 0,
			'currency_id' => isset($row['currency_id']) ? (int) $row['currency_id'] : 0,
			'price'       => $price,
			'transfer'    => isset($row['transfer']) ? (float) $row['transfer'] : $price,
			'renewal'     => isset($row['renewal'])  ? (float) $row['renewal']  : $price,
		);
	}

	// -----------------------------------------------------------------
	// Overrides
	// -----------------------------------------------------------------

	/** One override row, or empty. Request-cached; primeOverrides() fills it in bulk. */
	private function override($itemType, $pricingId, $ownerCompanyId, $audience)
	{
		$ck = 'o:' . (int)$itemType . ':' . (int)$pricingId . ':' . (int)$ownerCompanyId . ':' . (int)$audience;
		if (array_key_exists($ck, $this->resolveCache)) return $this->resolveCache[$ck];

		$row = $this->db->query(
			"SELECT price, transfer_price, renewal_price
			 FROM price_overrides
			 WHERE item_type = ? AND pricing_id = ? AND owner_company_id = ? AND audience = ?
			   AND is_active = 1 AND status = 1 LIMIT 1",
			array((int)$itemType, (int)$pricingId, (int)$ownerCompanyId, (int)$audience)
		)->row_array();

		return $this->resolveCache[$ck] = (empty($row) ? array() : $row);
	}

	/**
	 * Load every override that resolveMany() is about to ask for, in one query
	 * per audience-owner pair, and seed the same cache keys override() reads.
	 * Misses are cached as empty so a missing row still costs zero queries.
	 */
	private function primeOverrides($itemType, $pricingIds, $resellerCompanyId)
	{
		$pricingIds = array_values(array_unique(array_map('intval', $pricingIds)));
		if (empty($pricingIds)) return;

		$owners = array(0);
		if ((int) $resellerCompanyId > 0) $owners[] = (int) $resellerCompanyId;

		$in = implode(',', $pricingIds); // ints only, cast above
		$rows = $this->db->query(
			"SELECT pricing_id, owner_company_id, audience, price, transfer_price, renewal_price
			 FROM price_overrides
			 WHERE item_type = ? AND pricing_id IN ({$in})
			   AND owner_company_id IN (" . implode(',', $owners) . ")
			   AND audience = ?
			   AND is_active = 1 AND status = 1",
			array((int) $itemType, self::AUD_COST)
		)->result_array();

		$found = array();
		foreach ($rows as $r) {
			$ck = 'o:' . (int)$itemType . ':' . (int)$r['pricing_id'] . ':' . (int)$r['owner_company_id'] . ':' . (int)$r['audience'];
			$this->resolveCache[$ck] = array(
				'price'          => $r['price'],
				'transfer_price' => $r['transfer_price'],
				'renewal_price'  => $r['renewal_price'],
			);
			$found[$ck] = true;
		}

		// Negative caching: without this every unpriced TLD still costs a query.
		foreach ($pricingIds as $pid) {
			foreach ($owners as $own) {
				$ck = 'o:' . (int)$itemType . ':' . $pid . ':' . $own . ':' . self::AUD_COST;
				if (!isset($found[$ck])) $this->resolveCache[$ck] = array();
			}
		}
	}
	// -----------------------------------------------------------------
	// Tenancy
	// -----------------------------------------------------------------

	/**
	 * Which reseller, if any, governs this buyer.
	 *
	 * A reseller buying for themselves and their sub-customer both resolve to
	 * the same R; is_reseller is what separates "pays cost" from "pays the
	 * reseller's price". Only the reseller's own liveness gates this -- a
	 * suspended reseller's customers fall back to platform retail rather than
	 * getting a free pass on price.
	 */
	private function tenantFor($companyId)
	{
		$companyId = (int) $companyId;
		if (isset($this->tenantCache[$companyId])) return $this->tenantCache[$companyId];

		$none = array('reseller_company_id' => 0, 'is_reseller' => false);
		if ($companyId <= 0) return $this->tenantCache[$companyId] = $none;

		$row = $this->db->query(
			"SELECT id, is_reseller, parent_company_id FROM companies WHERE id = ? LIMIT 1",
			array($companyId)
		)->row_array();
		if (empty($row)) return $this->tenantCache[$companyId] = $none;

		if ((int) $row['is_reseller'] === 1) {
			$out = array('reseller_company_id' => $companyId, 'is_reseller' => true);
		} else {
			$parent = (int) $row['parent_company_id'];
			// Sub-resellers are not supported (req. 6): one level, no walk up.
			$out = array('reseller_company_id' => $parent, 'is_reseller' => false);
		}

		// A reseller whose profile is inactive stops being a pricing tenant.
		if ($out['reseller_company_id'] > 0 && !$this->resellerIsLive($out['reseller_company_id'])) {
			$out = $none;
		}
		return $this->tenantCache[$companyId] = $out;
	}

	private function resellerIsLive($resellerCompanyId)
	{
		$row = $this->db->query(
			"SELECT rp.id FROM reseller_profiles rp
			 JOIN companies c ON c.id = rp.company_id
			 WHERE rp.company_id = ? AND rp.status = 1 AND rp.deleted_on IS NULL
			   AND c.status = 1 AND c.is_reseller = 1 LIMIT 1",
			array((int) $resellerCompanyId)
		)->row_array();
		return !empty($row);
	}

	/** discount_type/discount_value, or null when the reseller has no discount set. */
	private function resellerDiscount($resellerCompanyId)
	{
		$row = $this->db->query(
			"SELECT discount_type, discount_value FROM reseller_profiles
			 WHERE company_id = ? AND status = 1 AND deleted_on IS NULL LIMIT 1",
			array((int) $resellerCompanyId)
		)->row_array();

		if (empty($row) || (float) $row['discount_value'] <= 0) return null;
		return $row;
	}

	/**
	 * Note the vocabulary mismatch this has to absorb: reseller_profiles uses
	 * 'percent' while promo_codes.discount_type is enum('fixed','percentage').
	 * Accept both spellings rather than silently treating a 10 as $10 off.
	 */
	private function applyDiscount($base, $disc)
	{
		$base  = (float) $base;
		$value = (float) $disc['discount_value'];
		$type  = strtolower(trim($disc['discount_type']));

		if ($type === 'percent' || $type === 'percentage') {
			if ($value > 100) $value = 100;
			$out = $base - ($base * $value / 100);
		} else {
			$out = $base - $value;
		}
		return round(max(0, $out), 2);
	}

	private function orFallback($value, $fallback)
	{
		return ($value === null || $value === '') ? (float) $fallback : (float) $value;
	}

	// -----------------------------------------------------------------
	// Writes
	// -----------------------------------------------------------------

	/**
	 * The platform sets a reseller cost. owner_company_id 0 = the default cost
	 * every reseller inherits; a company id = one negotiated deal.
	 *
	 * ONLY platform staff reach this (Reseller_pricing::save_cost() and the
	 * three pricing admin screens). A reseller sets no price of any kind.
	 */
	public function saveCostOverride($itemType, $pricingId, $ownerCompanyId, $prices)
	{
		$itemType       = (int) $itemType;
		$pricingId      = (int) $pricingId;
		$ownerCompanyId = (int) $ownerCompanyId;

		$base = $this->basePrice($itemType, $pricingId);
		if (empty($base)) {
			return array('success' => false, 'message' => 'That pricing row does not exist.');
		}

		$clean = array();
		foreach (array('price', 'transfer_price', 'renewal_price') as $col) {
			$raw = isset($prices[$col]) ? trim((string) $prices[$col]) : '';
			if ($raw === '') {
				// A blank cost is a deletion, not a zero: cost 0.00 would mean
				// the platform gives the item away, which is never the intent.
				if ($col === 'price') {
					$this->deleteOverride($itemType, $pricingId, $ownerCompanyId, self::AUD_COST);
					$this->resolveCache = array();
					return array('success' => true, 'message' => 'Cost cleared.');
				}
				$clean[$col] = null;
				continue;
			}
			if (!is_numeric($raw) || (float) $raw < 0) {
				return array('success' => false, 'message' => 'Cost values must be positive numbers.');
			}
			$clean[$col] = round((float) $raw, 2);
		}

		$this->upsertOverride($itemType, $pricingId, $ownerCompanyId, self::AUD_COST, $clean);
		$this->resolveCache = array();

		return array('success' => true, 'message' => 'Cost saved.');
	}

	/**
	 * Cost as resolve() would compute it, without needing a buyer.
	 *
	 * A thin wrapper over costLadder() so the floor check, the admin grid and
	 * the resolver can never drift apart -- before v2.1 the ladder was written
	 * out twice and adding a rung meant remembering to edit both.
	 */
	public function costFor($itemType, $pricingId, $resellerCompanyId, $base = null)
	{
		if ($base === null) $base = $this->basePrice($itemType, $pricingId);
		if (empty($base)) return array('price' => 0.0, 'transfer' => 0.0, 'renewal' => 0.0);

		$cost = $this->costLadder($itemType, $pricingId, (int) $resellerCompanyId, $base);
		unset($cost['source']);
		return $cost;
	}

	/**
	 * What reseller R pays the platform for this item. THE cost ladder --
	 * every caller goes through here.
	 *
	 *   1. price_overrides(owner = R, audience = COST)  negotiated cost
	 *   2. price_overrides(owner = 0, audience = COST)  platform default cost
	 *   3. reseller_profiles.discount_type/value        per-reseller blanket
	 *   4. sys_cnf RESELLER                             global blanket
	 *   5. the native pricing row                       no discount at all
	 *
	 * Rungs 3 and 4 are why a brand-new reseller has a coherent cost on day
	 * one with zero per-item data entry: set one percentage and every product
	 * in the catalogue is priced.
	 *
	 * @return array price/transfer/renewal/source. A NULL transfer or renewal
	 *               on an override means "same as price", never "free".
	 */
	private function costLadder($itemType, $pricingId, $R, $base)
	{
		$source = 'cost_override_reseller';
		$cost = $this->override($itemType, $pricingId, (int) $R, self::AUD_COST);

		if (empty($cost)) {
			$source = 'cost_override_platform';
			$cost = $this->override($itemType, $pricingId, 0, self::AUD_COST);
		}
		if (empty($cost)) {
			$disc = $this->resellerDiscount($R);
			if ($disc !== null) {
				$source = 'profile_discount';
				$cost = $this->discountedBase($base, $disc);
			}
		}
		if (empty($cost)) {
			$disc = $this->globalDiscount();
			if ($disc !== null) {
				$source = 'global_discount';
				$cost = $this->discountedBase($base, $disc);
			}
		}
		if (empty($cost)) {
			$source = 'base';
			$cost = array(
				'price'          => $base['price'],
				'transfer_price' => $base['transfer'],
				'renewal_price'  => $base['renewal'],
			);
		}

		return array(
			'price'    => (float) $cost['price'],
			'transfer' => $this->orFallback($cost['transfer_price'], $cost['price']),
			'renewal'  => $this->orFallback($cost['renewal_price'],  $cost['price']),
			'source'   => $source,
		);
	}

	/** A discount applied to all three components of a base row. */
	private function discountedBase($base, $disc)
	{
		return array(
			'price'          => $this->applyDiscount($base['price'],    $disc),
			'transfer_price' => $this->applyDiscount($base['transfer'], $disc),
			'renewal_price'  => $this->applyDiscount($base['renewal'],  $disc),
		);
	}

	/**
	 * The platform-wide default reseller discount (sys_cnf group RESELLER).
	 *
	 * Rung 4 of the ladder: "give every reseller 10% off everything" without
	 * touching a single reseller profile or pricing row.
	 *
	 * Cached in its own property rather than $resolveCache, which is wiped on
	 * every override save -- this value cannot change mid-request.
	 *
	 * @return array|null discount_type/discount_value, or null when unset.
	 */
	private function globalDiscount()
	{
		if ($this->globalDiscountCache !== null) {
			return ($this->globalDiscountCache === false) ? null : $this->globalDiscountCache;
		}

		$rows = $this->db->query(
			"SELECT cnf_key, cnf_val FROM sys_cnf WHERE cnf_group = 'RESELLER'"
		)->result_array();

		$cnf = array();
		foreach ($rows as $r) $cnf[$r['cnf_key']] = $r['cnf_val'];

		$value = isset($cnf['reseller_default_discount_value'])
			? (float) $cnf['reseller_default_discount_value'] : 0.0;

		// <= 0 means "not set, fall through to base", NOT "0% off".
		if ($value <= 0) {
			$this->globalDiscountCache = false;
			return null;
		}

		// ⚠️ NORMALISE. This is a free-text config field, and applyDiscount()
		// reads ANY unrecognised string as a fixed amount -- so a typo of
		// 'percnt' would silently turn "10% off" into "$10 off" on every item
		// in the catalogue. Whitelist fixed; everything else is a percentage.
		$type = isset($cnf['reseller_default_discount_type'])
			? strtolower(trim($cnf['reseller_default_discount_type'])) : 'percent';
		$type = ($type === 'fixed' || $type === 'flat') ? 'fixed' : 'percent';

		$this->globalDiscountCache = array('discount_type' => $type, 'discount_value' => $value);
		return $this->globalDiscountCache;
	}

	private function upsertOverride($itemType, $pricingId, $ownerCompanyId, $audience, $prices)
	{
		$now = getDateTime();
		$by  = getAdminId();

		$sql = "INSERT INTO price_overrides
					(item_type, pricing_id, owner_company_id, audience, price, transfer_price, renewal_price,
					 is_active, status, inserted_on, inserted_by)
				VALUES (?, ?, ?, ?, ?, ?, ?, 1, 1, ?, ?)
				ON DUPLICATE KEY UPDATE
					price = VALUES(price), transfer_price = VALUES(transfer_price),
					renewal_price = VALUES(renewal_price), is_active = 1, status = 1,
					updated_on = ?, updated_by = ?";

		$this->db->query($sql, array(
			(int) $itemType, (int) $pricingId, (int) $ownerCompanyId, (int) $audience,
			$prices['price'],
			isset($prices['transfer_price']) ? $prices['transfer_price'] : null,
			isset($prices['renewal_price'])  ? $prices['renewal_price']  : null,
			$now, $by, $now, $by,
		));
	}

	/**
	 * Hard delete, deliberately. A soft-deleted row would keep occupying
	 * uq_price_override, so re-entering a price after clearing it would hit a
	 * duplicate-key error -- the same trap that broke re-adding a reseller in
	 * Phase 1 (Reseller_model::getByCompany).
	 */
	private function deleteOverride($itemType, $pricingId, $ownerCompanyId, $audience)
	{
		$this->db->query(
			"DELETE FROM price_overrides
			 WHERE item_type = ? AND pricing_id = ? AND owner_company_id = ? AND audience = ?",
			array((int) $itemType, (int) $pricingId, (int) $ownerCompanyId, (int) $audience)
		);
	}

	// -----------------------------------------------------------------
	// Admin reads
	// -----------------------------------------------------------------

	/** Raw override rows for one owner+audience, keyed by pricing_id. For the admin grids. */
	public function overridesFor($itemType, $ownerCompanyId, $audience)
	{
		$rows = $this->db->query(
			"SELECT pricing_id, price, transfer_price, renewal_price FROM price_overrides
			 WHERE item_type = ? AND owner_company_id = ? AND audience = ? AND is_active = 1 AND status = 1",
			array((int) $itemType, (int) $ownerCompanyId, (int) $audience)
		)->result_array();

		$out = array();
		foreach ($rows as $r) $out[(int) $r['pricing_id']] = $r;
		return $out;
	}

	/**
	 * Is this company a LIVE reseller buying in its own name?
	 *
	 * ⚠️ NOT the same as companies.is_reseller, and the difference is a money
	 * bug. tenantFor() runs the result through resellerIsLive(), so a
	 * SUSPENDED reseller answers false here while the raw column still says 1.
	 * Callers that bill a reseller at cost must use this one: keyed on the raw
	 * column, a suspended reseller resolves to R = 0, cost_* = 0.00, and could
	 * self-issue a 0.00 invoice and be provisioned for free.
	 */
	public function isResellerBuyer($companyId)
	{
		$t = $this->tenantFor((int) $companyId);
		return !empty($t['is_reseller']);
	}

	/** Every live reseller, for the platform admin's reseller selector. */
	public function resellerList()
	{
		return $this->db->query(
			"SELECT c.id AS company_id, c.name, c.email, rp.currency_id
			 FROM reseller_profiles rp
			 JOIN companies c ON c.id = rp.company_id
			 WHERE rp.status = 1 AND rp.deleted_on IS NULL AND c.status = 1 AND c.is_reseller = 1
			 ORDER BY c.name"
		)->result_array();
	}
}
