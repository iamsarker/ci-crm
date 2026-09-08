-- =====================================================================
-- ci-crm — UPGRADE TO v2.1  (resellers: tenancy + pricing + wallet)
--
-- ONE FILE. Run it once, on any database from v1 onward. It replaces:
--     reseller_v2_phase1_migration.sql      (admin-portal tenancy)
--     reseller_v2_phase2_migration.sql      (reseller cost pricing)
--     reseller_v2_phase3_migration.sql      (prepaid wallet)
--     reseller_v21_pricing_simplify_migration.sql
--
-- WHAT IT ADDS
--   * Reseller admins log in through the admin portal and see only their
--     own customers (admin_users.admin_type / company_id).
--   * A reseller COST per item, set only by platform staff
--     (price_overrides), frozen onto each order (order_*.cost_amount).
--   * A prepaid wallet those costs are charged against
--     (reseller_credit_transactions).
--   * A global default reseller discount (sys_cnf group RESELLER).
--
-- WHAT IT DOES NOT DO
--   Nothing here changes a single price. With no override rows and the
--   global discount seeded at 0.00, the resolver falls straight through
--   to dom_pricing / product_service_pricing / software_pricing for
--   every buyer, exactly as before. Prices only move once a cost or a
--   discount is entered in the admin portal.
--
-- ORDER OF OPERATIONS
--   Run this BEFORE deploying the v2.1 code. Two of these columns are
--   read on paths with no fallback: Adminauth_model SELECTs admin_type
--   on login (missing it locks EVERYONE out of the admin portal, there
--   is no second way in), and checkout INSERTs order_*.cost_amount.
--
-- RE-RUNNABLE. Every step is guarded, so running it twice, or running it
--   against a database already part-way through the old phase files, is
--   safe and does nothing the second time. The guards use
--   information_schema rather than MariaDB's ADD COLUMN IF NOT EXISTS so
--   the file also runs on MySQL 5.7 / 8.
--
-- ⚠️  ONE STEP CAN FAIL ON PURPOSE — step 6, the dom_pricing unique key.
--     If it errors, read the comment there; do not work around it.
--     A failure aborts the rest of the file, which is intended: fix the
--     duplicates and re-run, and the completed steps will skip.
--
-- ⚠️  DESTRUCTIVE STEP: section 13 deletes reseller SELLING prices
--     (price_overrides.audience = 2) if any exist. Only a database that
--     ran the old Phase 2 can have them. Back that table up first if
--     those numbers still mean something to you.
-- =====================================================================


-- =====================================================================
-- PART 0 — PREFLIGHT. Read this output before anything else.
--
-- Every row must say OK. A MISSING row means this database is older than
-- v2 assumes and the run WILL fail part-way through, leaving a partial
-- upgrade (which is safe to resume -- the file is re-runnable -- but you
-- want to know now, not at step 8).
--
-- reseller_profiles in particular comes from the Reseller Management /
-- REST API feature that predates all of this; sections 8 and 10 ALTER it
-- and read it.
-- =====================================================================
SELECT 'admin_users'       AS prerequisite_table, IF(COUNT(*) = 1, 'OK', 'MISSING') AS result
  FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admin_users'
UNION ALL SELECT 'reseller_profiles', IF(COUNT(*) = 1, 'OK', 'MISSING') FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reseller_profiles'
UNION ALL SELECT 'dom_pricing', IF(COUNT(*) = 1, 'OK', 'MISSING') FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'dom_pricing'
UNION ALL SELECT 'order_domains', IF(COUNT(*) = 1, 'OK', 'MISSING') FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'order_domains'
UNION ALL SELECT 'order_services', IF(COUNT(*) = 1, 'OK', 'MISSING') FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'order_services'
UNION ALL SELECT 'order_licenses', IF(COUNT(*) = 1, 'OK', 'MISSING') FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'order_licenses'
UNION ALL SELECT 'invoice_items', IF(COUNT(*) = 1, 'OK', 'MISSING') FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'invoice_items'
UNION ALL SELECT 'sys_cnf', IF(COUNT(*) = 1, 'OK', 'MISSING') FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sys_cnf'
UNION ALL SELECT 'email_templates', IF(COUNT(*) = 1, 'OK', 'MISSING') FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'email_templates';


-- =====================================================================
-- PART A — SCHEMA
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. admin_users — the tenancy discriminator
--
-- admin_type is BINARY TENANCY, not a role:
--     0 = platform staff  (company_id = 0, sees everything, unchanged)
--     1 = reseller admin  (company_id = the reseller's companies.id)
--
-- Existing admins keep 0 / 0, so current installs behave as before. Do
-- not repurpose admin_roles for this -- it has zero rows and zero PHP
-- references, and admin_role_id is SELECTed but never put in the session.
-- ---------------------------------------------------------------------
SET @x := (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admin_users' AND COLUMN_NAME = 'admin_type');
SET @s := IF(@x > 0, 'DO 0',
    'ALTER TABLE `admin_users` ADD COLUMN `admin_type` tinyint(4) NOT NULL DEFAULT 0
        COMMENT ''0=platform staff, 1=reseller admin'' AFTER `admin_role_id`');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @x := (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admin_users' AND COLUMN_NAME = 'company_id');
SET @s := IF(@x > 0, 'DO 0',
    'ALTER TABLE `admin_users` ADD COLUMN `company_id` bigint(20) NOT NULL DEFAULT 0
        COMMENT ''reseller companies.id when admin_type=1; 0 for platform staff'' AFTER `admin_type`');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @x := (SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admin_users' AND INDEX_NAME = 'idx_admin_tenant');
SET @s := IF(@x > 0, 'DO 0',
    'ALTER TABLE `admin_users` ADD KEY `idx_admin_tenant` (`admin_type`,`company_id`)');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;


-- ---------------------------------------------------------------------
-- 2. company_transfers — audit trail for moving a customer
--
-- Moving a customer between resellers rewrites companies.parent_company_id,
-- which silently changes who bills them and whose wallet is debited. That
-- is a money-affecting change with no other record, so it gets its own
-- audit row rather than relying on companies.updated_by.
--
-- A transfer moves FUTURE billing only: existing order_* rows keep their
-- frozen prices, and in-flight wallet debits stay with the old reseller.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `company_transfers` (
    `id`              bigint(20)   NOT NULL AUTO_INCREMENT,
    `company_id`      bigint(20)   NOT NULL COMMENT 'the customer being moved',
    `from_company_id` bigint(20)   NOT NULL DEFAULT 0 COMMENT 'previous parent_company_id; 0 = was platform-direct',
    `to_company_id`   bigint(20)   NOT NULL DEFAULT 0 COMMENT 'new parent_company_id; 0 = moved back to platform-direct',
    `notes`           varchar(255)          DEFAULT NULL,
    `status`          tinyint(4)   NOT NULL DEFAULT 1 COMMENT '1=active, 0=soft deleted',
    `inserted_on`     datetime              DEFAULT NULL,
    `inserted_by`     int(11)               DEFAULT NULL,
    `updated_on`      datetime              DEFAULT NULL,
    `updated_by`      int(11)               DEFAULT NULL,
    `deleted_on`      datetime              DEFAULT NULL,
    `deleted_by`      int(11)               DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_transfer_company` (`company_id`),
    KEY `idx_transfer_to` (`to_company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ---------------------------------------------------------------------
-- 3. price_overrides — reseller COST, and nothing else
--
-- pricing_id already implies product + currency + cycle (or reg_period),
-- so nothing is denormalised here. Two row shapes are in use:
--
--   (owner_company_id = 0, audience = 1)  the platform's default cost,
--                                         inherited by every reseller
--   (owner_company_id = R, audience = 1)  one reseller's negotiated cost
--
-- The three native pricing tables are NEVER written by any of this and
-- keep meaning "platform retail". That is what makes the feature provably
-- safe: a buyer with no reseller above them never reads this table at all.
--
-- ⚠️  audience = 2 was a reseller-set SELLING price and is RETIRED in
--     v2.1. It made the storefront price depend on who was looking at it,
--     which is what broke the cart: add_to_carts freezes sub_total/total
--     once at add time, so a visitor priced as a guest was never
--     re-priced after logging in. Do not reintroduce it. Section 13
--     deletes any such rows left by the old Phase 2.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `price_overrides` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `item_type` tinyint(4) NOT NULL
        COMMENT '1=domain (dom_pricing.id), 2=service (product_service_pricing.id), 3=software (software_pricing.id)',
    `pricing_id` bigint(20) NOT NULL
        COMMENT 'row id in the native pricing table named by item_type',
    `owner_company_id` bigint(20) NOT NULL DEFAULT 0
        COMMENT '0 = platform-wide default; otherwise the reseller companies.id',
    `audience` tinyint(4) NOT NULL
        COMMENT '1 = reseller cost (what the reseller pays us). 2 = RETIRED in v2.1, never written',
    `price` decimal(15,2) NOT NULL DEFAULT 0.00
        COMMENT 'registration / first-term cost',
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
-- 4. price_override_audits
--
-- Created for schema parity with crm_db.sql, which fresh installs get.
--
-- ⚠️  It currently has NO WRITER. Its one job was recording the v2.0
--     auto-lift, which pulled a reseller's underwater selling price up to
--     a raised cost -- and selling prices no longer exist. Kept as the
--     place a future price-change trail would land rather than dropped
--     and re-added later.
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
    `reason` varchar(60) NOT NULL COMMENT 'auto_lift_floor (retired) | manual | cost_change',
    `note` varchar(255) DEFAULT NULL,
    `inserted_on` datetime DEFAULT NULL,
    `inserted_by` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_poa_override` (`price_override_id`),
    KEY `idx_poa_owner` (`owner_company_id`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ---------------------------------------------------------------------
-- 5. cost_amount snapshots on the three order tables
--
-- Sell-price snapshots already existed (first_pay_amount /
-- recurring_amount are frozen at checkout). Cost never was, because
-- nothing needed it.
--
-- The wallet debits at COST when provisioning fires, which can be days
-- after checkout. Without a frozen snapshot the debit would recompute,
-- so a cost change in between would silently rewrite what the reseller is
-- billed for an order they already quoted. Freeze it with the sell price,
-- in the same INSERT.
--
-- 0.00 on every existing row is correct: direct-customer orders have no
-- cost basis, and pre-v2 reseller orders were sold at retail anyway.
-- ---------------------------------------------------------------------
SET @x := (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'order_domains' AND COLUMN_NAME = 'cost_amount');
SET @s := IF(@x > 0, 'DO 0',
    'ALTER TABLE `order_domains` ADD COLUMN `cost_amount` decimal(15,2) NOT NULL DEFAULT 0.00
        COMMENT ''reseller cost frozen at checkout; 0.00 for direct customers'' AFTER `recurring_amount`');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @x := (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'order_services' AND COLUMN_NAME = 'cost_amount');
SET @s := IF(@x > 0, 'DO 0',
    'ALTER TABLE `order_services` ADD COLUMN `cost_amount` decimal(15,2) NOT NULL DEFAULT 0.00
        COMMENT ''reseller cost frozen at checkout; 0.00 for direct customers'' AFTER `recurring_amount`');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @x := (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'order_licenses' AND COLUMN_NAME = 'cost_amount');
SET @s := IF(@x > 0, 'DO 0',
    'ALTER TABLE `order_licenses` ADD COLUMN `cost_amount` decimal(15,2) NOT NULL DEFAULT 0.00
        COMMENT ''reseller cost frozen at checkout; 0.00 for direct customers'' AFTER `recurring_amount`');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;


-- ---------------------------------------------------------------------
-- 6. dom_pricing needs a unique key — AND THIS STEP CAN FAIL ON PURPOSE
--
-- product_service_pricing and software_pricing both carry a UNIQUE key on
-- (product, currency, cycle); dom_pricing had only a PK and a non-unique
-- index. That was survivable while a TLD price was "whatever row comes
-- back first", but price_overrides is keyed on pricing_id: duplicate
-- (dom_extension_id, currency_id, reg_period) rows carry two ids,
-- therefore two independent cost sets, and the resolver would price from
-- whichever the optimiser happened to return.
--
-- 6a below lists any duplicates. If it prints rows, the ALTER in 6b will
-- fail with a duplicate-entry error and this file will stop -- which is
-- the intended behaviour, not a bug to route around.
--
-- ⚠️  SOFT-DELETING THE LOSER DOES NOT WORK. A UNIQUE index covers every
--     row in the table regardless of status, so setting status = 0 leaves
--     the triple occupied and 6b still fails. (The check below therefore
--     does NOT filter on status either -- it has to report exactly what
--     the index will see. The Phase 2 file it replaces filtered on
--     status = 1 and told you to soft-delete, which could never succeed.)
--
--     The loser has to be REMOVED, and anything pointing at it repointed
--     first. Decide which id is live, then:
--
--       SELECT dom_pricing_id, COUNT(*) FROM order_domains
--        WHERE dom_pricing_id IN (<the ids>) GROUP BY dom_pricing_id;
--       SELECT dom_pricing_id, COUNT(*) FROM add_to_carts
--        WHERE dom_pricing_id IN (<the ids>) GROUP BY dom_pricing_id;
--
--       UPDATE order_domains SET dom_pricing_id = <keep> WHERE dom_pricing_id = <drop>;
--       UPDATE add_to_carts  SET dom_pricing_id = <keep> WHERE dom_pricing_id = <drop>;
--       DELETE FROM dom_pricing WHERE id = <drop>;
--
--     Then re-run this file; the steps already applied will skip.
--
-- ⚠️  DO NOT let MySQL merge the duplicates for you and DO NOT skip the
--     ALTER. Merging blind repoints live orders at a price nobody chose,
--     and skipping it leaves two ids for one TLD price -- two independent
--     cost sets, with the resolver picking whichever the optimiser
--     returns first.
-- ---------------------------------------------------------------------

-- 6a — duplicate check. Expect zero rows.
--      `statuses` shows each id's status so you can see at a glance
--      whether someone already tried to soft-delete one of them.
SELECT `dom_extension_id`, `currency_id`, `reg_period`,
       COUNT(*) AS dup_count,
       GROUP_CONCAT(`id` ORDER BY `id`) AS ids,
       GROUP_CONCAT(`status` ORDER BY `id`) AS statuses
FROM `dom_pricing`
GROUP BY `dom_extension_id`, `currency_id`, `reg_period`
HAVING COUNT(*) > 1;

-- 6b — the key itself.
SET @x := (SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'dom_pricing' AND INDEX_NAME = 'uq_dom_pricing');
SET @s := IF(@x > 0, 'DO 0',
    'ALTER TABLE `dom_pricing` ADD UNIQUE KEY `uq_dom_pricing` (`dom_extension_id`,`currency_id`,`reg_period`)');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;


-- ---------------------------------------------------------------------
-- 7. reseller_credit_transactions — the wallet ledger
--
-- Append-only. Every movement of wallet money is a row here; nothing else
-- may write reseller_profiles.credit_balance, which becomes a cached
-- running total (section 8).
--
-- `amount` is SIGNED (+ credit, - debit) rather than a debit/credit
-- column pair, so the balance is a plain SUM() and reconciliation is one
-- query with no CASE. `balance_after` is the running balance as of this
-- row, derived inside the row lock -- it is what makes a statement
-- renderable without re-summing history, and what makes a divergence
-- between ledger and cache detectable at all.
--
-- ⚠️  uq_credit_idem IS THE ENTIRE RE-ENTRANCY DEFENCE, AND IT IS
--     LOAD-BEARING. Payment_model::processSuccessfulPayment() has NO
--     already-PAID guard: it re-marks the invoice, re-calls
--     provisionPaidServices() and re-sends emails on every webhook
--     redelivery, across all 11 of its call sites. Paddle alone retries
--     with backoff over ~3 days. A PHP status check cannot protect the
--     ledger against that -- only the unique constraint can, because only
--     the constraint is evaluated by the database under concurrency.
--     Do not drop this index to "fix" a duplicate-key error; the
--     duplicate key IS the fix working.
--
--     Key convention (Resellercredit_model builds these):
--         topup:invoice:{invoice_id}     one credit per paid top-up
--         debit:invoice:{invoice_id}     one debit per provisioned invoice
--         adjust:{adminId}:{companyId}:{amount}:{ts}  manual correction
--         opening:company:{company_id}   the section-10 backfill
--
--     idempotency_key is NULLable and UNIQUE: MySQL/MariaDB does not
--     collapse NULLs in a unique index, so rows with no natural key can
--     still be written without colliding.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reseller_credit_transactions` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `company_id` bigint(20) NOT NULL
        COMMENT 'the RESELLER companies.id that owns the wallet, never the sub-customer',
    `currency_id` int(11) NOT NULL DEFAULT 0
        COMMENT 'wallet currency; 0 = reseller_profiles.currency_id was unset when written',
    `txn_type` varchar(20) NOT NULL
        COMMENT 'topup | debit | refund | adjustment',
    `amount` decimal(15,2) NOT NULL
        COMMENT 'SIGNED: positive credits the wallet, negative debits it',
    `balance_after` decimal(15,2) NOT NULL
        COMMENT 'running balance as of this row, derived inside the row lock',
    `ref_type` varchar(30) DEFAULT NULL
        COMMENT 'invoice | order | manual',
    `ref_id` bigint(20) DEFAULT NULL
        COMMENT 'id in the table named by ref_type',
    `idempotency_key` varchar(120) DEFAULT NULL
        COMMENT 'natural key for this movement; UNIQUE -- see the warning above',
    `description` varchar(255) DEFAULT NULL,
    `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1=active, 0=soft deleted',
    `inserted_on` datetime DEFAULT NULL,
    `inserted_by` int(11) DEFAULT NULL,
    `updated_on` datetime DEFAULT NULL,
    `updated_by` int(11) DEFAULT NULL,
    `deleted_on` datetime DEFAULT NULL,
    `deleted_by` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_credit_idem` (`idempotency_key`),
    KEY `idx_credit_company` (`company_id`,`id`),
    KEY `idx_credit_ref` (`ref_type`,`ref_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ---------------------------------------------------------------------
-- 8. reseller_profiles — overdraft limit and sub-customer payment mode
--
-- credit_limit: the permitted overdraft, default 0.00 = none. It exists
-- because a shortfall is a SOFT block, not a hard stop. When a
-- sub-customer's payment lands and the reseller cannot cover the cost,
-- the customer has ALREADY PAID -- refusing the debit would leave a PAID
-- invoice with no service AND no ledger trace, which is materially worse
-- than a negative balance. So the debit is written, the balance goes
-- negative, and PROVISIONING is what gets held. At the default 0.00 the
-- effective behaviour is still "no overdraft".
--
-- payment_mode: per-reseller, because both models are legitimate. Some
-- resellers collect from their own customers and mark invoices paid;
-- others want the platform's gateways to take the money directly.
--     0 = reseller collects & marks paid (default, matches today)
--     1 = sub-customers pay via the platform gateways
-- ---------------------------------------------------------------------
SET @x := (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reseller_profiles' AND COLUMN_NAME = 'credit_limit');
SET @s := IF(@x > 0, 'DO 0',
    'ALTER TABLE `reseller_profiles` ADD COLUMN `credit_limit` decimal(14,2) NOT NULL DEFAULT 0.00
        COMMENT ''permitted overdraft; 0.00 = none'' AFTER `credit_balance`');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @x := (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reseller_profiles' AND COLUMN_NAME = 'payment_mode');
SET @s := IF(@x > 0, 'DO 0',
    'ALTER TABLE `reseller_profiles` ADD COLUMN `payment_mode` tinyint(4) NOT NULL DEFAULT 0
        COMMENT ''0=reseller collects & marks paid, 1=sub-customers pay via platform gateways'' AFTER `credit_limit`');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;


-- ---------------------------------------------------------------------
-- 9. invoice_items.item_type — document 3 and 4
--
-- The comment had been stale since the software catalog shipped: it still
-- claimed only 1 and 2 existed while the cart had been writing 3 for
-- months. 4 is the wallet top-up line.
--
-- COMMENT-ONLY CHANGE -- the column type is unchanged and there is no
-- CHECK constraint to widen, so this is safe to re-run.
--
-- A top-up line carries ref_id NULL. provisionPaidServices() loops only
-- items WITH a ref_id, so it skips them for free; the code also carries an
-- explicit item_type = 4 guard in isRenewalInvoiceItem(), so a top-up can
-- never reach the registrar dispatcher even if that loop changes.
-- ---------------------------------------------------------------------
ALTER TABLE `invoice_items`
    MODIFY COLUMN `item_type` tinyint(4) NOT NULL
        COMMENT '1=domain, 2=product_service, 3=software/license, 4=reseller wallet top-up (ref_id NULL)';


-- =====================================================================
-- PART B — DATA
-- =====================================================================

-- ---------------------------------------------------------------------
-- 10. Opening balances — reconcile the cache with the ledger on day one
--
-- reseller_profiles.credit_balance existed since v1 as a free-text admin
-- field: someone typed a number, nothing read it, nothing audited it.
-- From now on it is a CACHE of SUM(amount), so a non-zero legacy value
-- with no matching ledger row would make the very first reconciliation
-- report wrong -- and every explanation of it a story about history
-- rather than a bug hunt.
--
-- Re-runnable via INSERT IGNORE against uq_credit_idem rather than a NOT
-- EXISTS subquery on the table being inserted into: the unique index is
-- already the authority on "has this movement been written", so reusing it
-- keeps one rule instead of two, and it sidesteps MySQL's restriction on
-- reading the INSERT target inside the same statement. The only constraint
-- a row here can violate is that key, so IGNORE hides nothing else.
--
-- Resellers already at exactly 0.00 need no row: an empty ledger sums to
-- 0, which already reconciles.
-- ---------------------------------------------------------------------
INSERT IGNORE INTO `reseller_credit_transactions`
    (`company_id`, `currency_id`, `txn_type`, `amount`, `balance_after`,
     `ref_type`, `ref_id`, `idempotency_key`, `description`, `status`, `inserted_on`)
SELECT rp.`company_id`,
       COALESCE(rp.`currency_id`, 0),
       'adjustment',
       rp.`credit_balance`,
       rp.`credit_balance`,
       'manual',
       NULL,
       CONCAT('opening:company:', rp.`company_id`),
       'Opening balance carried over from the pre-ledger credit_balance field',
       1,
       NOW()
FROM `reseller_profiles` rp
WHERE rp.`credit_balance` <> 0.00
  AND rp.`deleted_on` IS NULL;


-- ---------------------------------------------------------------------
-- 11. Global default reseller discount (sys_cnf group RESELLER)
--
-- The bottom rung of the cost ladder:
--
--   1. price_overrides(owner = R, audience = 1)   negotiated cost
--   2. price_overrides(owner = 0, audience = 1)   platform default cost
--   3. reseller_profiles.discount_type/value      per-reseller blanket
--   4. sys_cnf RESELLER (these rows)              global blanket
--   5. the native pricing row                     no discount
--
-- Seeded at 0.00 = OFF, so this migration changes no reseller's cost by
-- itself. Edit under Settings -> General Settings -> System Config, which
-- renders every sys_cnf group automatically.
--
-- ⚠️  discount_value <= 0 means "not set, fall through", NOT "0% off".
--     reseller_profiles.discount_value already worked this way, so an
--     existing reseller sitting at 0.00 now inherits the global default.
--     That is intended and is the point of the feature.
--
-- ⚠️  discount_type is free text. Pricing_model::globalDiscount()
--     whitelists 'fixed'/'flat' and treats EVERYTHING else as percent,
--     because applyDiscount() reads any unrecognised string as fixed --
--     so a typo of 'percnt' would otherwise turn "10% off" into "$10 off"
--     across the whole catalogue.
-- ---------------------------------------------------------------------
INSERT INTO `sys_cnf` (`cnf_key`, `cnf_val`, `cnf_group`, `created_on`, `updated_on`)
SELECT 'reseller_default_discount_type', 'percent', 'RESELLER', NOW(), NOW() FROM DUAL
 WHERE NOT EXISTS (SELECT 1 FROM `sys_cnf` WHERE `cnf_key` = 'reseller_default_discount_type');

INSERT INTO `sys_cnf` (`cnf_key`, `cnf_val`, `cnf_group`, `created_on`, `updated_on`)
SELECT 'reseller_default_discount_value', '0.00', 'RESELLER', NOW(), NOW() FROM DUAL
 WHERE NOT EXISTS (SELECT 1 FROM `sys_cnf` WHERE `cnf_key` = 'reseller_default_discount_value');


-- ---------------------------------------------------------------------
-- 12. Email templates
--
-- Both wallet notices carry hardcoded fallback bodies in PHP, so a missing
-- row degrades to a plainer email rather than to silence -- but seed them
-- so the wording stays editable in Settings -> Email Template like every
-- other notice.
-- ---------------------------------------------------------------------

-- 12a. Top-up received and credited.
INSERT INTO `email_templates`
    (`template_key`, `template_name`, `subject`, `body`, `placeholders`, `category`, `status`, `inserted_on`)
SELECT 'reseller_wallet_topup',
       'Reseller - Wallet Topped Up',
       'Your account has been credited with {currency_symbol}{amount}',
       '<p>Dear {reseller_name},</p><p>We have received your payment for invoice <strong>#{invoice_no}</strong> and credited your account.</p><p>Amount credited: <strong>{currency_symbol}{amount}</strong><br>New balance: <strong>{currency_symbol}{balance}</strong></p><p>This balance is used automatically when your customers'' orders are provisioned.</p><p>Regards,<br>{site_name}</p>',
       '{reseller_name}, {amount}, {balance}, {currency}, {currency_symbol}, {invoice_no}, {invoice_url}, {site_name}, {company_name}, {site_url}',
       'INVOICE', 1, NOW()
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `email_templates` WHERE `template_key` = 'reseller_wallet_topup'
);

-- 12b. Balance could not cover a cost; provisioning is held.
--
-- The customer has already paid by this point, so the tone is "we are
-- holding your customer's order", not "your payment failed". The order is
-- recoverable: a top-up plus the release cron completes it.
INSERT INTO `email_templates`
    (`template_key`, `template_name`, `subject`, `body`, `placeholders`, `category`, `status`, `inserted_on`)
SELECT 'reseller_wallet_insufficient',
       'Reseller - Insufficient Credit, Order Held',
       'Action required: order held, your balance is {currency_symbol}{balance}',
       '<p>Dear {reseller_name},</p><p>An order on invoice <strong>#{invoice_no}</strong> could not be provisioned because your account balance does not cover its cost.</p><p>Cost required: <strong>{currency_symbol}{amount}</strong><br>Current balance: <strong>{currency_symbol}{balance}</strong></p><p><strong>The order has been held, not cancelled.</strong> Top up your account and it will be provisioned automatically on the next scheduled run.</p><p><a href="{topup_url}">Click here to top up your account</a></p><p>Regards,<br>{site_name}</p>',
       '{reseller_name}, {amount}, {balance}, {currency}, {currency_symbol}, {invoice_no}, {invoice_url}, {topup_url}, {site_name}, {company_name}, {site_url}',
       'DUNNING', 1, NOW()
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `email_templates` WHERE `template_key` = 'reseller_wallet_insufficient'
);

-- 12c. The retired auto-lift notice, seeded DEACTIVATED (status = 0).
--
-- Nothing sends this any more -- it announced that a platform cost rise
-- had pulled a reseller's selling price up to the new floor, and selling
-- prices are gone. It is seeded only so an upgraded database matches
-- crm_db.sql, which still carries the row (deleting it there would leave a
-- hole in the email_templates id sequence, which is how this project's
-- AUTO_INCREMENT gotcha has bitten that table twice).
--
-- The UPDATE below deactivates it on databases that ran the old Phase 2
-- and already have it at status = 1.
INSERT INTO `email_templates`
    (`template_key`, `template_name`, `subject`, `body`, `placeholders`, `category`, `status`, `inserted_on`)
SELECT 'reseller_price_lifted',
       'Reseller - Selling Price Auto-Adjusted',
       'Your selling price for {item_name} was adjusted',
       '<p>Dear {reseller_name},</p><p>Our cost for <strong>{item_name}</strong> has increased, and your selling price was below the new cost. To make sure you are never selling at a loss, it has been raised to the minimum allowed:</p>{price_changes}<p>This is the floor, not a recommendation &mdash; you can set a higher price at any time from your portal.</p><p>Regards,<br>{site_name}</p>',
       '{reseller_name}, {item_name}, {price_changes}, {site_name}, {company_name}, {site_url}',
       'GENERAL', 0, NOW()
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `email_templates` WHERE `template_key` = 'reseller_price_lifted'
);

UPDATE `email_templates` SET `status` = 0 WHERE `template_key` = 'reseller_price_lifted';


-- ---------------------------------------------------------------------
-- 13. Retire the reseller SELLING price (v2.1)
--
-- A no-op on a database coming straight from v1 -- only one that ran the
-- old Phase 2 can hold audience = 2 rows.
--
-- ⚠️  AUDITS BEFORE PARENTS. The override delete is a hard DELETE and
--     price_override_audits has no FK or ON DELETE CASCADE, so dropping
--     the parents first would orphan the audit rows permanently with no
--     way left to identify them.
-- ---------------------------------------------------------------------

-- 13a. Audit rows belonging to selling-price overrides.
DELETE a FROM `price_override_audits` a
  JOIN `price_overrides` p ON p.id = a.price_override_id
 WHERE p.audience = 2;

-- 13b. Already-orphaned audit rows. reason = 'auto_lift_floor' was written
--      by exactly one method, and only ever against audience = 2 rows, so
--      this predicate is precisely the retail trail and touches nothing
--      else. ('manual' and 'cost_change' were declared in the schema
--      comment but no code ever wrote them.)
DELETE FROM `price_override_audits` WHERE `reason` = 'auto_lift_floor';

-- 13c. The selling prices themselves. audience = 1 (cost) is UNTOUCHED --
--      it is the whole remaining point of the table.
DELETE FROM `price_overrides` WHERE `audience` = 2;


-- =====================================================================
-- VERIFICATION — run this block after the migration.
-- Every row should read OK.
-- =====================================================================
SELECT 'admin_users.admin_type'      AS item, IF(COUNT(*) = 1, 'OK', 'MISSING') AS result
  FROM information_schema.COLUMNS
 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admin_users' AND COLUMN_NAME = 'admin_type'
UNION ALL
SELECT 'admin_users.company_id', IF(COUNT(*) = 1, 'OK', 'MISSING') FROM information_schema.COLUMNS
 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admin_users' AND COLUMN_NAME = 'company_id'
UNION ALL
SELECT 'order_domains.cost_amount', IF(COUNT(*) = 1, 'OK', 'MISSING') FROM information_schema.COLUMNS
 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'order_domains' AND COLUMN_NAME = 'cost_amount'
UNION ALL
SELECT 'order_services.cost_amount', IF(COUNT(*) = 1, 'OK', 'MISSING') FROM information_schema.COLUMNS
 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'order_services' AND COLUMN_NAME = 'cost_amount'
UNION ALL
SELECT 'order_licenses.cost_amount', IF(COUNT(*) = 1, 'OK', 'MISSING') FROM information_schema.COLUMNS
 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'order_licenses' AND COLUMN_NAME = 'cost_amount'
UNION ALL
SELECT 'reseller_profiles.credit_limit', IF(COUNT(*) = 1, 'OK', 'MISSING') FROM information_schema.COLUMNS
 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reseller_profiles' AND COLUMN_NAME = 'credit_limit'
UNION ALL
SELECT 'reseller_profiles.payment_mode', IF(COUNT(*) = 1, 'OK', 'MISSING') FROM information_schema.COLUMNS
 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reseller_profiles' AND COLUMN_NAME = 'payment_mode'
UNION ALL
SELECT 'dom_pricing.uq_dom_pricing', IF(COUNT(*) > 0, 'OK', 'MISSING') FROM information_schema.STATISTICS
 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'dom_pricing' AND INDEX_NAME = 'uq_dom_pricing'
UNION ALL
SELECT 'reseller_credit_transactions.uq_credit_idem', IF(COUNT(*) > 0, 'OK', 'MISSING') FROM information_schema.STATISTICS
 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reseller_credit_transactions' AND INDEX_NAME = 'uq_credit_idem'
UNION ALL
SELECT 'tables created (4 expected)', IF(COUNT(*) = 4, 'OK', CONCAT('ONLY ', COUNT(*))) FROM information_schema.TABLES
 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN
       ('company_transfers','price_overrides','price_override_audits','reseller_credit_transactions')
UNION ALL
SELECT 'sys_cnf RESELLER (2 expected)', IF(COUNT(*) = 2, 'OK', CONCAT('FOUND ', COUNT(*))) FROM `sys_cnf`
 WHERE `cnf_group` = 'RESELLER'
UNION ALL
SELECT 'wallet email templates (2 expected)', IF(COUNT(*) = 2, 'OK', CONCAT('FOUND ', COUNT(*))) FROM `email_templates`
 WHERE `template_key` IN ('reseller_wallet_topup','reseller_wallet_insufficient')
UNION ALL
SELECT 'retail overrides purged', IF(COUNT(*) = 0, 'OK', CONCAT(COUNT(*), ' LEFT')) FROM `price_overrides`
 WHERE `audience` = 2
UNION ALL
SELECT 'orphan audit rows', IF(COUNT(*) = 0, 'OK', CONCAT(COUNT(*), ' LEFT')) FROM `price_override_audits` a
  LEFT JOIN `price_overrides` p ON p.id = a.price_override_id WHERE p.id IS NULL;

-- The wallet invariant. The cached balance must equal the ledger sum for
-- every reseller, now and forever. Expect ZERO rows -- a hit means
-- something wrote reseller_profiles.credit_balance directly.
SELECT rp.company_id, rp.credit_balance, COALESCE(SUM(t.amount), 0) AS ledger_sum
  FROM `reseller_profiles` rp
  LEFT JOIN `reseller_credit_transactions` t
         ON t.company_id = rp.company_id AND t.status = 1
 GROUP BY rp.company_id, rp.credit_balance
HAVING rp.credit_balance <> COALESCE(SUM(t.amount), 0);
