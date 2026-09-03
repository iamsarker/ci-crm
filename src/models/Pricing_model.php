<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pricing_model — the single resolver for two-tier reseller pricing (v2.0.0 Phase 2).
 *
 * Before this file there was exactly one price per (product, currency, cycle):
 * whatever sat in dom_pricing / product_service_pricing / software_pricing.
 * Those three tables are still the only place a platform-retail price lives and
 * this model never writes to them. Everything reseller-specific is layered on
 * top from `price_overrides`.
 *
 * THE INVARIANT THAT MAKES THIS SAFE (step 2 of resolve()):
 *     a buyer whose company has no parent_company_id and is not itself a
 *     reseller never touches price_overrides at all -- sell = base, cost = 0.
 * Every direct customer's cart total is therefore bit-identical to pre-Phase-2
 * by construction, not by testing.
 *
 * Vocabulary, because three different words all sound like "price":
 *     base   -- the native table's number. Platform retail. Never overridden.
 *     cost   -- what a reseller pays the platform. audience = AUD_COST.
 *     sell   -- what THIS buyer is charged. For a reseller buying for
 *               themselves that is their cost; for a sub-customer it is the
 *               reseller's own price. audience = AUD_RETAIL.
 *
 * Domains carry three components (register / transfer / renewal) and each is
 * resolved and floored independently -- a single blended price would let a
 * reseller set renewal below cost and bleed the platform on every renewal for
 * years, which is the one direction the money keeps flowing.
 */
class Pricing_model extends CI_Model
{
	/** item_type, matching invoice_items.item_type / add_to_carts.item_type. */
	const ITEM_DOMAIN   = 1;
	const ITEM_SERVICE  = 2;
	const ITEM_SOFTWARE = 3;

	/** price_overrides.audience */
	const AUD_COST   = 1; // what the reseller pays us
	const AUD_RETAIL = 2; // what the reseller charges their customer

	/** Request cache. Never persisted: a price change must land on the next request. */
	private $resolveCache = array();
	private $tenantCache  = array();

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
	 *     price, transfer, renewal   -- what this buyer pays (sell)
	 *     cost_price, cost_transfer, cost_renewal -- what the reseller pays us; 0 for direct
	 *     base_price, base_transfer, base_renewal -- platform retail, for UI comparison
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
	 * 1. base = the native pricing row.
	 * 2. No reseller in the picture -> sell = base, cost = 0. Done.
	 * 3. cost = per-reseller override ?? platform-wide reseller cost
	 *           ?? reseller_profiles discount applied to base ?? base.
	 * 4. Buyer IS the reseller -> they pay cost.
	 * 5. Buyer is a sub-customer -> they pay the reseller's retail override,
	 *    falling back to base (platform retail) when the reseller has not
	 *    priced this item. Falling back to base and not to cost matters: an
	 *    unpriced item must not be sold at the platform's wholesale number.
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

		// --- Step 2: the direct-customer short circuit. ---
		if ($R <= 0) {
			$out['price']         = (float) $base['price'];
			$out['transfer']      = (float) $base['transfer'];
			$out['renewal']       = (float) $base['renewal'];
			$out['cost_price']    = 0.0;
			$out['cost_transfer'] = 0.0;
			$out['cost_renewal']  = 0.0;
			$out['source']        = 'base';
			return $out;
		}

		// --- Step 3: cost. ---
		$costSource = 'cost_override_reseller';
		$cost = $this->override($itemType, $pricingId, $R, self::AUD_COST);
		if (empty($cost)) {
			$costSource = 'cost_override_platform';
			$cost = $this->override($itemType, $pricingId, 0, self::AUD_COST);
		}
		if (empty($cost)) {
			// reseller_profiles.discount_type / discount_value have been stored
			// since v1 and applied to precisely nothing. This is where they
			// finally do work: every existing reseller gets a coherent cost on
			// day one with no data entry at all.
			$disc = $this->resellerDiscount($R);
			if ($disc !== null) {
				$costSource = 'profile_discount';
				$cost = array(
					'price'          => $this->applyDiscount($base['price'],    $disc),
					'transfer_price' => $this->applyDiscount($base['transfer'], $disc),
					'renewal_price'  => $this->applyDiscount($base['renewal'],  $disc),
				);
			}
		}
		if (empty($cost)) {
			$costSource = 'base';
			$cost = array('price' => $base['price'], 'transfer_price' => $base['transfer'], 'renewal_price' => $base['renewal']);
		}

		// A NULL transfer/renewal override means "same as price", not "free".
		$out['cost_price']    = (float) $cost['price'];
		$out['cost_transfer'] = $this->orFallback($cost['transfer_price'], $cost['price']);
		$out['cost_renewal']  = $this->orFallback($cost['renewal_price'],  $cost['price']);

		// --- Step 4: the reseller buying for themselves pays cost. ---
		if (!empty($tenant['is_reseller'])) {
			$out['price']    = $out['cost_price'];
			$out['transfer'] = $out['cost_transfer'];
			$out['renewal']  = $out['cost_renewal'];
			$out['source']   = $costSource . '/self';
			return $out;
		}

		// --- Step 5: the sub-customer pays the reseller's price. ---
		$retail = $this->override($itemType, $pricingId, $R, self::AUD_RETAIL);
		if (!empty($retail)) {
			$out['price']    = (float) $retail['price'];
			$out['transfer'] = $this->orFallback($retail['transfer_price'], $retail['price']);
			$out['renewal']  = $this->orFallback($retail['renewal_price'],  $retail['price']);
			$out['source']   = 'retail_override';
		} else {
			$out['price']    = (float) $base['price'];
			$out['transfer'] = (float) $base['transfer'];
			$out['renewal']  = (float) $base['renewal'];
			$out['source']   = 'base_retail';
		}

		// The floor is enforced on save (saveResellerRetail), but a cost RAISE
		// can strand an existing override underneath it between the raise and
		// the auto-lift pass. Clamp on read too so no sale is ever below cost.
		$out['price']    = max($out['price'],    $out['cost_price']);
		$out['transfer'] = max($out['transfer'], $out['cost_transfer']);
		$out['renewal']  = max($out['renewal'],  $out['cost_renewal']);

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
			   AND is_active = 1 AND status = 1",
			array((int) $itemType)
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
				foreach (array(self::AUD_COST, self::AUD_RETAIL) as $aud) {
					$ck = 'o:' . (int)$itemType . ':' . $pid . ':' . $own . ':' . $aud;
					if (!isset($found[$ck])) $this->resolveCache[$ck] = array();
				}
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
	 * A reseller (or the platform admin acting for one) sets their selling price.
	 *
	 * The floor is enforced HERE, server-side and PER COMPONENT. Any JS hint on
	 * the form is decoration -- this is the check that counts, and the reason it
	 * is per component is that a blended floor lets a reseller price
	 * registration above cost and renewal below it, which loses money quietly
	 * for as long as the domain lives.
	 *
	 * @param array $prices ['price'=>, 'transfer_price'=>, 'renewal_price'=>]
	 *                      blank/null components fall back to price on read.
	 * @return array ['success'=>bool, 'message'=>string, 'floor'=>array]
	 */
	public function saveResellerRetail($itemType, $pricingId, $resellerCompanyId, $prices)
	{
		$itemType          = (int) $itemType;
		$pricingId         = (int) $pricingId;
		$resellerCompanyId = (int) $resellerCompanyId;

		if ($resellerCompanyId <= 0) {
			return array('success' => false, 'message' => 'A reseller must be selected.');
		}
		$base = $this->basePrice($itemType, $pricingId);
		if (empty($base)) {
			return array('success' => false, 'message' => 'That pricing row does not exist.');
		}

		// The floor is the reseller's own cost, resolved exactly the way a real
		// purchase would resolve it -- profile-discount fallback included.
		$floor = $this->costFor($itemType, $pricingId, $resellerCompanyId, $base);

		$components = array(
			'price'          => array('label' => 'Registration price', 'floor' => $floor['price']),
			'transfer_price' => array('label' => 'Transfer price',     'floor' => $floor['transfer']),
			'renewal_price'  => array('label' => 'Renewal price',      'floor' => $floor['renewal']),
		);

		$clean = array();
		foreach ($components as $col => $meta) {
			$raw = isset($prices[$col]) ? trim((string) $prices[$col]) : '';

			if ($raw === '') {
				// Only the registration price is mandatory; the other two are
				// allowed to be blank and inherit it on read.
				if ($col === 'price') {
					return array('success' => false, 'message' => 'Registration price is required.');
				}
				$clean[$col] = null;
				continue;
			}
			if (!is_numeric($raw) || (float) $raw < 0) {
				return array('success' => false, 'message' => $meta['label'] . ' must be a positive number.');
			}
			if ((float) $raw < $meta['floor']) {
				return array(
					'success' => false,
					'floor'   => $floor,
					'message' => $meta['label'] . ' cannot be below your cost of ' . number_format($meta['floor'], 2) . '.',
				);
			}
			$clean[$col] = round((float) $raw, 2);
		}

		// A blank transfer/renewal inherits `price`, so `price` alone has to
		// clear all three floors or the inherited value lands underwater.
		if ($clean['transfer_price'] === null && $clean['price'] < $floor['transfer']) {
			return array('success' => false, 'floor' => $floor,
				'message' => 'Leave transfer blank only if the registration price is at least your transfer cost of ' . number_format($floor['transfer'], 2) . '.');
		}
		if ($clean['renewal_price'] === null && $clean['price'] < $floor['renewal']) {
			return array('success' => false, 'floor' => $floor,
				'message' => 'Leave renewal blank only if the registration price is at least your renewal cost of ' . number_format($floor['renewal'], 2) . '.');
		}

		$this->upsertOverride($itemType, $pricingId, $resellerCompanyId, self::AUD_RETAIL, $clean);
		$this->resolveCache = array();

		return array('success' => true, 'message' => 'Price saved.', 'floor' => $floor);
	}

	/**
	 * The platform sets a reseller cost. owner_company_id 0 = the default cost
	 * every reseller inherits; a company id = one negotiated deal.
	 *
	 * Raising a cost is the dangerous direction: existing retail overrides that
	 * were legal yesterday are now below the floor. Auto-lift them here rather
	 * than leaving underwater prices live.
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
					return array('success' => true, 'message' => 'Cost cleared.', 'lifted' => array());
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

		$lifted = $this->liftUnderwaterRetail($itemType, $pricingId, $ownerCompanyId);

		return array('success' => true, 'message' => 'Cost saved.', 'lifted' => $lifted);
	}

	/**
	 * Pull every retail override for this pricing row up to its new floor.
	 *
	 * Scope: when the platform-wide cost (owner 0) moves, every reseller who
	 * does not have their own negotiated cost is affected; when one reseller's
	 * cost moves, only they are. Each lifted component writes an audit row, and
	 * the caller is handed the affected resellers so it can email them.
	 */
	public function liftUnderwaterRetail($itemType, $pricingId, $changedOwnerCompanyId)
	{
		$itemType  = (int) $itemType;
		$pricingId = (int) $pricingId;

		$base = $this->basePrice($itemType, $pricingId);
		if (empty($base)) return array();

		if ((int) $changedOwnerCompanyId > 0) {
			$targets = array((int) $changedOwnerCompanyId);
		} else {
			$rows = $this->db->query(
				"SELECT owner_company_id FROM price_overrides
				 WHERE item_type = ? AND pricing_id = ? AND audience = ? AND is_active = 1 AND status = 1
				   AND owner_company_id > 0",
				array($itemType, $pricingId, self::AUD_RETAIL)
			)->result_array();
			$targets = array_map('intval', array_column($rows, 'owner_company_id'));
		}

		$lifted = array();
		foreach ($targets as $companyId) {
			$retail = $this->override($itemType, $pricingId, $companyId, self::AUD_RETAIL);
			if (empty($retail)) continue;

			$floor  = $this->costFor($itemType, $pricingId, $companyId, $base);
			$update = array();
			$trail  = array();

			$map = array(
				'transfer_price' => $floor['transfer'],
				'renewal_price'  => $floor['renewal'],
			);
			foreach ($map as $col => $f) {
				// A NULL component inherits `price`, so it is lifted THROUGH
				// that column rather than by materialising a value the reseller
				// never set. Collected below into $needed.
				if ($retail[$col] === null || $retail[$col] === '') continue;
				if ((float) $retail[$col] >= $f) continue;

				$update[$col] = round($f, 2);
				$trail[]      = array('component' => $col, 'old' => (float) $retail[$col], 'new' => round($f, 2));
			}

			// `price` must clear its own floor AND the floor of every component
			// that inherits from it. Computed in one place, after the explicit
			// components are settled -- lifting price first and then checking
			// the inherited floors against the OLD price is how an inherited
			// renewal ends up below cost when the renewal floor is the highest
			// of the three.
			$needed = max((float) $retail['price'], $floor['price']);
			foreach ($map as $col => $f) {
				$inherits = ($retail[$col] === null || $retail[$col] === '') && !isset($update[$col]);
				if ($inherits && $needed < $f) $needed = $f;
			}
			if ($needed > (float) $retail['price']) {
				$trail[]         = array('component' => 'price', 'old' => (float) $retail['price'], 'new' => round($needed, 2));
				$update['price'] = round($needed, 2);
			}

			if (empty($update)) continue;

			$this->db->where(array(
				'item_type' => $itemType, 'pricing_id' => $pricingId,
				'owner_company_id' => $companyId, 'audience' => self::AUD_RETAIL,
			));
			$this->db->update('price_overrides', array_merge($update, array(
				'updated_on' => getDateTime(), 'updated_by' => getAdminId(),
			)));

			$overrideId = $this->overrideId($itemType, $pricingId, $companyId, self::AUD_RETAIL);
			foreach ($trail as $t) {
				$this->db->insert('price_override_audits', array(
					'price_override_id' => $overrideId,
					'owner_company_id'  => $companyId,
					'item_type'         => $itemType,
					'pricing_id'        => $pricingId,
					'component'         => $t['component'],
					'old_value'         => $t['old'],
					'new_value'         => $t['new'],
					'reason'            => 'auto_lift_floor',
					'note'              => 'Lifted to cost floor after a platform cost change.',
					'inserted_on'       => getDateTime(),
					'inserted_by'       => getAdminId(),
				));
			}
			$lifted[$companyId] = $trail;
		}

		$this->resolveCache = array();
		return $lifted;
	}

	/**
	 * Cost as resolve() would compute it, without needing a buyer.
	 * Shared by the floor check and the auto-lift so they can never disagree.
	 */
	public function costFor($itemType, $pricingId, $resellerCompanyId, $base = null)
	{
		if ($base === null) $base = $this->basePrice($itemType, $pricingId);
		if (empty($base)) return array('price' => 0.0, 'transfer' => 0.0, 'renewal' => 0.0);

		$cost = $this->override($itemType, $pricingId, (int) $resellerCompanyId, self::AUD_COST);
		if (empty($cost)) $cost = $this->override($itemType, $pricingId, 0, self::AUD_COST);

		if (empty($cost)) {
			$disc = $this->resellerDiscount($resellerCompanyId);
			if ($disc !== null) {
				$cost = array(
					'price'          => $this->applyDiscount($base['price'],    $disc),
					'transfer_price' => $this->applyDiscount($base['transfer'], $disc),
					'renewal_price'  => $this->applyDiscount($base['renewal'],  $disc),
				);
			}
		}
		if (empty($cost)) {
			$cost = array('price' => $base['price'], 'transfer_price' => $base['transfer'], 'renewal_price' => $base['renewal']);
		}

		return array(
			'price'    => (float) $cost['price'],
			'transfer' => $this->orFallback($cost['transfer_price'], $cost['price']),
			'renewal'  => $this->orFallback($cost['renewal_price'],  $cost['price']),
		);
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

	private function overrideId($itemType, $pricingId, $ownerCompanyId, $audience)
	{
		$row = $this->db->query(
			"SELECT id FROM price_overrides
			 WHERE item_type = ? AND pricing_id = ? AND owner_company_id = ? AND audience = ? LIMIT 1",
			array((int) $itemType, (int) $pricingId, (int) $ownerCompanyId, (int) $audience)
		)->row_array();
		return !empty($row) ? (int) $row['id'] : 0;
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

	// -----------------------------------------------------------------
	// Notification
	// -----------------------------------------------------------------

	/**
	 * Tell resellers their price moved without them touching it.
	 *
	 * An auto-lift silently rewrites a number the reseller typed, so it cannot
	 * be left to a report. Best-effort by design: a dead SMTP server must never
	 * roll back a cost change the platform admin already committed, so every
	 * failure is logged and swallowed.
	 *
	 * @param array $lifted [company_id => [ ['component','old','new'], ... ]]
	 *                      exactly what liftUnderwaterRetail() returns.
	 * @return int emails sent
	 */
	public function notifyLiftedResellers($lifted, $itemType, $pricingId)
	{
		if (empty($lifted)) return 0;

		$label    = $this->pricingLabel($itemType, $pricingId);
		$settings = $this->db->query("SELECT * FROM app_settings LIMIT 1")->row_array();
		$siteName = !empty($settings['company_name']) ? $settings['company_name'] : 'Our Company';
		$template = $this->db->query(
			"SELECT subject, body FROM email_templates WHERE template_key = ? AND status = 1 LIMIT 1",
			array('reseller_price_lifted')
		)->row_array();

		$sent = 0;
		foreach ($lifted as $companyId => $changes) {
			$co = $this->db->query(
				"SELECT name, email, first_name, last_name FROM companies WHERE id = ? LIMIT 1",
				array((int) $companyId)
			)->row_array();
			if (empty($co) || empty($co['email'])) continue;

			$rows = '';
			foreach ($changes as $c) {
				$rows .= '<tr><td>' . htmlspecialchars($this->componentLabel($c['component']))
					  . '</td><td>' . number_format((float) $c['old'], 2)
					  . '</td><td><strong>' . number_format((float) $c['new'], 2) . '</strong></td></tr>';
			}
			$table = '<table border="1" cellpadding="6" cellspacing="0"><tr>'
				   . '<th>Component</th><th>Was</th><th>Now</th></tr>' . $rows . '</table>';

			$name = trim(($co['first_name'] ?? '') . ' ' . ($co['last_name'] ?? ''));
			if ($name === '') $name = $co['name'];

			$placeholders = array(
				'{reseller_name}' => $name,
				'{item_name}'     => $label,
				'{price_changes}' => $table,
				'{site_name}'     => $siteName,
				'{company_name}'  => $siteName,
				'{site_url}'      => base_url(),
			);

			if (!empty($template)) {
				$subject = strtr($template['subject'], $placeholders);
				$body    = strtr($template['body'], $placeholders);
			} else {
				// Fallback so the notice still goes out on installs that have
				// not seeded the template -- the same guard sendVerificationEmail
				// uses, and for the same reason: a missing row must not turn
				// into silence about a price change.
				$subject = 'Your selling price for ' . $label . ' was adjusted';
				$body = '<p>Dear ' . htmlspecialchars($name) . ',</p>'
					. '<p>Our cost for <strong>' . htmlspecialchars($label) . '</strong> has increased, and your '
					. 'selling price was below the new cost. It has been raised to the minimum so you are not '
					. 'selling at a loss:</p>' . $table
					. '<p>You can set a higher price at any time from your portal.</p>'
					. '<p>Regards,<br>' . htmlspecialchars($siteName) . '</p>';
			}

			try {
				$from     = $settings['smtp_user'] ?? ($settings['site_email'] ?? null);
				if (sendHtmlEmail($co['email'], $subject, $body, $from, $siteName)) $sent++;
			} catch (Exception $e) {
				log_message('error', 'notifyLiftedResellers: ' . $e->getMessage());
			}
		}
		return $sent;
	}

	private function componentLabel($column)
	{
		$map = array(
			'price'          => 'Registration / first term',
			'transfer_price' => 'Transfer',
			'renewal_price'  => 'Renewal',
		);
		return isset($map[$column]) ? $map[$column] : $column;
	}

	/** Human name for a pricing row, for emails and audit notes. */
	public function pricingLabel($itemType, $pricingId)
	{
		$itemType  = (int) $itemType;
		$pricingId = (int) $pricingId;

		if ($itemType == self::ITEM_DOMAIN) {
			$r = $this->db->query(
				"SELECT de.extension, dp.reg_period, c.code
				 FROM dom_pricing dp
				 JOIN dom_extensions de ON de.id = dp.dom_extension_id
				 LEFT JOIN currencies c ON c.id = dp.currency_id
				 WHERE dp.id = ? LIMIT 1", array($pricingId))->row_array();
			if (empty($r)) return 'domain pricing #' . $pricingId;
			return $r['extension'] . ' (' . (int) $r['reg_period'] . 'yr, ' . $r['code'] . ')';
		}

		if ($itemType == self::ITEM_SERVICE) {
			$r = $this->db->query(
				"SELECT ps.product_name, bc.cycle_name, c.code
				 FROM product_service_pricing psp
				 JOIN product_services ps ON ps.id = psp.product_service_id
				 LEFT JOIN billing_cycle bc ON bc.id = psp.billing_cycle_id
				 LEFT JOIN currencies c ON c.id = psp.currency_id
				 WHERE psp.id = ? LIMIT 1", array($pricingId))->row_array();
			if (empty($r)) return 'hosting pricing #' . $pricingId;
			return $r['product_name'] . ' (' . $r['cycle_name'] . ', ' . $r['code'] . ')';
		}

		$r = $this->db->query(
			"SELECT p.name, bc.cycle_name, c.code
			 FROM software_pricing sp
			 JOIN plans p ON p.id = sp.product_id
			 LEFT JOIN billing_cycle bc ON bc.id = sp.billing_cycle_id
			 LEFT JOIN currencies c ON c.id = sp.currency_id
			 WHERE sp.id = ? LIMIT 1", array($pricingId))->row_array();
		if (empty($r)) return 'software pricing #' . $pricingId;
		return $r['name'] . ' (' . $r['cycle_name'] . ', ' . $r['code'] . ')';
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
