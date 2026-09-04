-- =====================================================================
-- Remove leaked verification-harness fixtures
--
-- The Phase 2 pricing harness (whmazadmin/pricingcheck) builds a throwaway
-- reseller, sub-customer, grand-sub and a few dom_pricing rows, then deletes
-- them. Its cleanup was wrapped in `catch (Exception)`, but PHP 8 raises
-- Error -- not Exception -- for things like an undefined function, so a run
-- that died that way skipped cleanup entirely and left the fixtures behind.
--
-- They are live on the demo database right now:
--     company #824  Pricingcheck Reseller    (is_reseller = 1)
--     company #825  Pricingcheck Sub         (parent 824)
--     company #826  Pricingcheck GrandSub    (parent 825)
--   + 3 dom_pricing rows marked reg_period >= 90
--   + 1 reseller_credit_transactions row (the +5.00 "test" adjustment posted
--     through the wallet UI while verifying Phase 3)
--
-- The harness bug is fixed in src/controllers/whmazadmin/Pricingcheck.php
-- (catch Throwable + the ledger delete below), so this should not recur.
-- This file removes what the broken run left behind.
--
-- ⚠️  DELETES REAL ROWS. Everything targeted is marked as fixture data --
--     companies by their @example.invalid emails, dom_pricing by an absurd
--     reg_period -- so it cannot match genuine records. Read section 0 first
--     and confirm it lists only the rows above.
--
-- Safe to re-run: every statement is keyed on those markers, not on ids.
-- =====================================================================


-- ---------------------------------------------------------------------
-- 0. LOOK FIRST. Expect exactly the rows described in the header.
--    If anything unfamiliar appears, stop.
-- ---------------------------------------------------------------------
SELECT 'companies' AS what, id, name, email, is_reseller, parent_company_id
FROM `companies`
WHERE `email` LIKE 'pricingcheck+%' OR `email` LIKE 'walletcheck+%';

SELECT 'dom_pricing' AS what, id, dom_extension_id, currency_id, reg_period, price
FROM `dom_pricing`
WHERE `reg_period` >= 90;

SELECT 'ledger' AS what, t.id, t.company_id, t.txn_type, t.amount, t.description
FROM `reseller_credit_transactions` t
JOIN `companies` c ON c.id = t.company_id
WHERE c.`email` LIKE 'pricingcheck+%' OR c.`email` LIKE 'walletcheck+%';


-- ---------------------------------------------------------------------
-- 1. Ledger rows first.
--
-- Before the profile and company rows, not after: reconcile() finds a
-- balance/ledger divergence by joining reseller_profiles -> ledger, so a
-- ledger row whose profile has already been deleted is invisible to it. An
-- orphan here would sit in the table permanently with nothing looking for it.
-- ---------------------------------------------------------------------
DELETE t FROM `reseller_credit_transactions` t
JOIN `companies` c ON c.id = t.company_id
WHERE c.`email` LIKE 'pricingcheck+%' OR c.`email` LIKE 'walletcheck+%';


-- ---------------------------------------------------------------------
-- 2. Reseller profiles and any price overrides those companies own.
-- ---------------------------------------------------------------------
DELETE rp FROM `reseller_profiles` rp
JOIN `companies` c ON c.id = rp.company_id
WHERE c.`email` LIKE 'pricingcheck+%' OR c.`email` LIKE 'walletcheck+%';

DELETE po FROM `price_overrides` po
JOIN `companies` c ON c.id = po.owner_company_id
WHERE c.`email` LIKE 'pricingcheck+%' OR c.`email` LIKE 'walletcheck+%';

DELETE poa FROM `price_override_audits` poa
JOIN `companies` c ON c.id = poa.owner_company_id
WHERE c.`email` LIKE 'pricingcheck+%' OR c.`email` LIKE 'walletcheck+%';


-- ---------------------------------------------------------------------
-- 3. Overrides keyed on the fixture dom_pricing rows, then the rows.
--
-- item_type = 1 only: pricing_id holds a dom_pricing id here, and the same
-- number is a perfectly valid product_service_pricing or software_pricing id.
-- Without that filter this deletes real overrides for other item types.
-- ---------------------------------------------------------------------
DELETE po FROM `price_overrides` po
JOIN `dom_pricing` dp ON dp.id = po.pricing_id
WHERE po.`item_type` = 1 AND dp.`reg_period` >= 90;

DELETE poa FROM `price_override_audits` poa
JOIN `dom_pricing` dp ON dp.id = poa.pricing_id
WHERE poa.`item_type` = 1 AND dp.`reg_period` >= 90;

DELETE FROM `dom_pricing` WHERE `reg_period` >= 90;


-- ---------------------------------------------------------------------
-- 4. The companies themselves, last.
-- ---------------------------------------------------------------------
DELETE FROM `companies`
WHERE `email` LIKE 'pricingcheck+%' OR `email` LIKE 'walletcheck+%';


-- =====================================================================
-- Verification — all four counts must be 0, and the drift query empty.
--
--   SELECT COUNT(*) FROM companies   WHERE email LIKE 'pricingcheck+%'
--                                       OR email LIKE 'walletcheck+%';
--   SELECT COUNT(*) FROM dom_pricing WHERE reg_period >= 90;
--   SELECT COUNT(*) FROM reseller_credit_transactions t
--     LEFT JOIN companies c ON c.id = t.company_id WHERE c.id IS NULL;
--   SELECT COUNT(*) FROM reseller_profiles rp
--     LEFT JOIN companies c ON c.id = rp.company_id WHERE c.id IS NULL;
--
--   SELECT rp.company_id, rp.credit_balance, COALESCE(SUM(t.amount),0) ledger
--   FROM reseller_profiles rp
--   LEFT JOIN reseller_credit_transactions t
--          ON t.company_id = rp.company_id AND t.status = 1
--   WHERE rp.deleted_on IS NULL
--   GROUP BY rp.company_id, rp.credit_balance
--   HAVING rp.credit_balance <> COALESCE(SUM(t.amount),0);
-- =====================================================================
