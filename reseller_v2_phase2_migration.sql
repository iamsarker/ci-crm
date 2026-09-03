-- =====================================================================
-- v2.0.0 Phase 2 — Two-tier and per-reseller pricing
--
-- Until now ci-crm has exactly one price per (product, currency, cycle):
-- whatever is in dom_pricing / product_service_pricing / software_pricing.
-- There is no cost tier, no per-reseller price, and no markup anywhere in
-- the schema or the code (zero hits for price_level, cost_price,
-- reseller_price, markup, tier).
--
-- reseller_profiles.discount_type / discount_value have been stored since
-- v1 and the admin form claims they are "applied to this reseller's own
-- orders" -- but NO code reads them for pricing. Phase 2 finally gives
-- them a job: they become the fallback cost basis when no explicit cost
-- override exists, so every existing reseller gets a sane cost on day one
-- with zero data entry.
--
-- The three native pricing tables are NOT touched and keep meaning
-- "platform retail". That is what makes this refactor provably safe: a
-- direct customer (no parent_company_id) never reads an override row, so
-- their prices are bit-identical to pre-Phase-2.
--
-- Run this ONCE per environment.
--
-- ⚠️  ORDERING: run this migration BEFORE deploying the Phase 2 code.
--     src/models/Pricing_model.php SELECTs from price_overrides on every
--     cart page load, and Cart::_processCartItem() INSERTs cost_amount
--     into order_domains / order_services / order_licenses. Against a
--     database without them, checkout dies on "Unknown column
--     'cost_amount'" -- the same failure class as the documented
--     invoice_item_renewal_flag_migration.sql incident.
--
-- ⚠️  Phase 3 (the prepaid wallet) reads order_*.cost_amount added here.
--     It cannot deploy without this file.
-- =====================================================================


-- ---------------------------------------------------------------------
-- 1. price_overrides — one generic table for all three tiers
--
-- pricing_id already implies product + currency + cycle (or reg_period),
-- so nothing is denormalised here. Three row shapes cover every tier:
--
--   (owner_company_id = 0, audience = 1)  the platform's reseller cost
--   (owner_company_id = R, audience = 1)  a per-reseller negotiated cost
--   (owner_company_id = R, audience = 2)  that reseller's selling price
--
-- Rejected: columns on the three pricing tables (per-reseller prices are
-- one-to-many per pricing row, so columns hold only one extra tier), and
-- three parallel *_reseller_pricing tables (triples resolver, UI and
-- migration for no gain).
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `price_overrides` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `item_type` tinyint(4) NOT NULL
        COMMENT '1=domain (dom_pricing.id), 2=service (product_service_pricing.id), 3=software (software_pricing.id)',
    `pricing_id` bigint(20) NOT NULL
        COMMENT 'row id in the native pricing table named by item_type',
    `owner_company_id` bigint(20) NOT NULL DEFAULT 0
        COMMENT '0 = platform-wide; otherwise the reseller companies.id',
    `audience` tinyint(4) NOT NULL
        COMMENT '1 = reseller cost (what the reseller pays us), 2 = end-customer retail (what the reseller charges)',
    `price` decimal(15,2) NOT NULL DEFAULT 0.00
        COMMENT 'registration / first-term price',
    `transfer_price` decimal(15,2) DEFAULT NULL
        COMMENT 'domain only; NULL = fall back to price',
    `renewal_price` decimal(15,2) DEFAULT NULL
        COMMENT 'NULL = fall back to price',
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1=active, 0=soft deleted',
    `inserted_on` datetime DEFAULT NULL,
    `inserted_by` int(11) DEFAULT NULL,
    `updated_on` datetime DEFAULT NULL,
    `updated_by` int(11) DEFAULT NULL,
    `deleted_on` datetime DEFAULT NULL,
    `deleted_by` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_price_override` (`item_type`,`pricing_id`,`owner_company_id`,`audience`),
    KEY `idx_po_owner` (`owner_company_id`,`audience`),
    KEY `idx_po_lookup` (`item_type`,`pricing_id`,`audience`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ---------------------------------------------------------------------
-- 2. price_override_audits — why a reseller's price changed
--
-- Needed for exactly one thing, and it is not bookkeeping theatre: when
-- the platform RAISES a reseller's cost, every retail override that now
-- sits below the new cost is auto-lifted to the floor
-- (price = GREATEST(price, new_cost)). That silently rewrites a number
-- the reseller typed, so it has to leave a trace they can be pointed at
-- when they ask why their price moved. Silently selling below cost is
-- worse than an unhappy email; an "underwater prices" report nobody
-- reads is worse still.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `price_override_audits` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `price_override_id` bigint(20) NOT NULL,
    `owner_company_id` bigint(20) NOT NULL DEFAULT 0,
    `item_type` tinyint(4) NOT NULL,
    `pricing_id` bigint(20) NOT NULL,
    `component` varchar(20) NOT NULL COMMENT 'price | transfer_price | renewal_price',
    `old_value` decimal(15,2) DEFAULT NULL,
    `new_value` decimal(15,2) DEFAULT NULL,
    `reason` varchar(60) NOT NULL COMMENT 'auto_lift_floor | manual | cost_change',
    `note` varchar(255) DEFAULT NULL,
    `inserted_on` datetime DEFAULT NULL,
    `inserted_by` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_poa_override` (`price_override_id`),
    KEY `idx_poa_owner` (`owner_company_id`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ---------------------------------------------------------------------
-- 3. cost_amount snapshots on the three order tables
--
-- Sell-price snapshots already exist (first_pay_amount / recurring_amount
-- are frozen at checkout). Cost was never recorded because nothing ever
-- needed it.
--
-- Phase 3 debits the reseller's wallet at COST when provisioning fires.
-- Without a frozen snapshot that debit would have to recompute cost at
-- provisioning time, so a cost change between checkout and payment would
-- silently rewrite what the reseller is billed for an order they already
-- quoted. Freeze it with the sell price, in the same INSERT.
--
-- 0.00 on every existing row is correct: direct-customer orders have no
-- cost basis, and pre-Phase-2 reseller orders were sold at retail anyway.
-- ---------------------------------------------------------------------
ALTER TABLE `order_domains`
    ADD COLUMN `cost_amount` decimal(15,2) NOT NULL DEFAULT 0.00
        COMMENT 'reseller cost frozen at checkout; 0.00 for direct customers'
        AFTER `recurring_amount`;

ALTER TABLE `order_services`
    ADD COLUMN `cost_amount` decimal(15,2) NOT NULL DEFAULT 0.00
        COMMENT 'reseller cost frozen at checkout; 0.00 for direct customers'
        AFTER `recurring_amount`;

ALTER TABLE `order_licenses`
    ADD COLUMN `cost_amount` decimal(15,2) NOT NULL DEFAULT 0.00
        COMMENT 'reseller cost frozen at checkout; 0.00 for direct customers'
        AFTER `recurring_amount`;


-- ---------------------------------------------------------------------
-- 4. dom_pricing needs a unique key — and this step can FAIL ON PURPOSE
--
-- product_service_pricing and software_pricing both carry a UNIQUE key on
-- (product, currency, cycle); dom_pricing has only a PK and a non-unique
-- index on dom_extension_id. That was survivable while a TLD price was
-- just "whatever row comes back first", but price_overrides is keyed on
-- pricing_id: duplicate (dom_extension_id, currency_id, reg_period) rows
-- carry two ids, therefore two independent override sets, and the
-- resolver would silently price from whichever the optimiser returned.
--
-- Run the SELECT below FIRST. If it returns rows, do NOT skip the ALTER
-- and do NOT let MySQL merge them -- decide by hand which id is the live
-- one (check dom_pricing_id on order_domains and add_to_carts), soft
-- delete the loser with status = 0, then re-run. Merging duplicates blind
-- would repoint live orders at a price nobody chose.
-- ---------------------------------------------------------------------

-- Step 4a — duplicate check. Expect zero rows.
SELECT `dom_extension_id`, `currency_id`, `reg_period`,
       COUNT(*) AS dup_count, GROUP_CONCAT(`id` ORDER BY `id`) AS ids
FROM `dom_pricing`
WHERE `status` = 1
GROUP BY `dom_extension_id`, `currency_id`, `reg_period`
HAVING COUNT(*) > 1;

-- Step 4b — only after 4a comes back empty.
ALTER TABLE `dom_pricing`
    ADD UNIQUE KEY `uq_dom_pricing` (`dom_extension_id`,`currency_id`,`reg_period`);


-- ---------------------------------------------------------------------
-- 5. Email template for the auto-lift notice
--
-- Sent when a platform cost rise pulls a reseller's selling price up to
-- the new floor. Pricing_model::notifyLiftedResellers() carries a
-- hardcoded fallback body, so a missing row degrades to a plainer email
-- rather than to silence -- but seed it so the wording is editable in
-- Settings -> Email Template like every other notice.
-- ---------------------------------------------------------------------
INSERT INTO `email_templates`
    (`template_key`, `template_name`, `subject`, `body`, `placeholders`, `category`, `status`, `inserted_on`)
SELECT 'reseller_price_lifted',
       'Reseller - Selling Price Auto-Adjusted',
       'Your selling price for {item_name} was adjusted',
       '<p>Dear {reseller_name},</p><p>Our cost for <strong>{item_name}</strong> has increased, and your selling price was below the new cost. To make sure you are never selling at a loss, it has been raised to the minimum allowed:</p>{price_changes}<p>This is the floor, not a recommendation &mdash; you can set a higher price at any time from your portal.</p><p>Regards,<br>{site_name}</p>',
       '{reseller_name}, {item_name}, {price_changes}, {site_name}, {company_name}, {site_url}',
       'GENERAL', 1, NOW()
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `email_templates` WHERE `template_key` = 'reseller_price_lifted'
);


-- =====================================================================
-- Verification
--
--   SHOW COLUMNS FROM `order_services` LIKE 'cost_amount';
--   SHOW INDEX FROM `dom_pricing` WHERE Key_name = 'uq_dom_pricing';
--   SELECT COUNT(*) FROM `price_overrides`;   -- 0 on a fresh migration
--
-- With zero override rows the resolver falls through to the native
-- pricing tables for everyone, so the storefront behaves exactly as it
-- did before this file ran. Nothing changes until a cost or a reseller
-- price is entered in the admin portal.
-- =====================================================================
