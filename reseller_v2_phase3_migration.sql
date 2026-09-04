-- =====================================================================
-- v2.0.0 Phase 3 — Reseller prepaid wallet / credit ledger
--
-- Phases 1 and 2 gave a reseller an identity (admin_users.admin_type = 1)
-- and a cost (price_overrides + the frozen order_*.cost_amount snapshots).
-- Neither moved any money. This phase adds the account those costs are
-- charged against.
--
-- The money model, decided with the product owner on 2026-09-02:
--   * PREPAID. The reseller tops up, then sub-customer orders debit the
--     wallet at COST when provisioning fires.
--   * Top-up is a NORMAL invoice (invoice_items.item_type = 4, ref_id
--     NULL) paid through the gateways that already exist -- so signature
--     verification, Paddle's API re-confirmation, retry semantics, card
--     capture and the receipt email are all inherited, not rebuilt.
--   * Debit happens at PROVISIONING, not at checkout. Checkout creates a
--     DUE invoice that may never be paid; debiting there would let
--     abandoned carts drain a reseller's balance.
--
-- Run this ONCE per environment.
--
-- ⚠️  ORDERING: run this migration BEFORE deploying the Phase 3 code.
--     src/models/Resellercredit_model.php SELECTs credit_limit from
--     reseller_profiles on every wallet operation and INSERTs into
--     reseller_credit_transactions. Against a database without them the
--     first sub-customer payment dies mid-provisioning -- the same
--     failure class as the documented invoice_item_renewal_flag and
--     admin_type incidents.
--
-- ⚠️  DEPENDS ON PHASE 2. The debit reads order_*.cost_amount, which
--     reseller_v2_phase2_migration.sql adds. Do not run this file first.
-- =====================================================================


-- ---------------------------------------------------------------------
-- 1. reseller_credit_transactions — the ledger
--
-- Append-only. Every movement of wallet money is a row here; nothing
-- else may write reseller_profiles.credit_balance, which becomes a
-- cached running total (see section 2).
--
-- `amount` is SIGNED (+ credit, - debit) rather than a separate
-- debit/credit column pair, so the balance is a plain SUM() and a
-- reconciliation check is one query with no CASE. `balance_after` is the
-- running balance AS OF this row, derived inside the row lock -- it is
-- what makes a statement renderable without re-summing history, and what
-- makes a divergence between ledger and cache detectable.
--
-- ⚠️  uq_credit_idem IS THE ENTIRE RE-ENTRANCY DEFENCE, AND IT IS
--     LOAD-BEARING. Payment_model::processSuccessfulPayment() has NO
--     already-PAID guard: it re-marks the invoice, re-calls
--     provisionPaidServices() and re-sends emails on every webhook
--     redelivery, across all 11 of its call sites. Paddle alone retries
--     with backoff over ~3 days. A PHP status check cannot protect the
--     ledger against that -- only the unique constraint can, because
--     only the constraint is evaluated by the database under
--     concurrency. Do not drop this index to "fix" a duplicate-key
--     error; the duplicate key IS the fix working.
--
--     Key convention (Resellercredit_model builds these):
--         topup:invoice:{invoice_id}     one credit per paid top-up
--         debit:invoice:{invoice_id}     one debit per provisioned invoice
--         adjust:{adminId}:{companyId}:{amount}:{ts}  manual correction
--         opening:company:{company_id}   the section-3 backfill
--
--     idempotency_key is NULLable and UNIQUE: MySQL/MariaDB does not
--     collapse NULLs in a unique index, so rows that genuinely have no
--     natural key can still be written without colliding.
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
-- 2. reseller_profiles — overdraft limit and sub-customer payment mode
--
-- credit_limit: the permitted overdraft, default 0.00 = none. It exists
-- because the shortfall path is a SOFT block, not a hard stop. When a
-- sub-customer's payment lands and the reseller cannot cover the cost,
-- the customer has ALREADY PAID -- refusing the debit would leave a PAID
-- invoice with no service and no ledger trace, which is materially worse
-- than a negative balance. So the debit is written, the balance goes
-- negative, and PROVISIONING is what gets held. With the default 0.00
-- the effective behaviour is still "no overdraft".
--
-- payment_mode: per-reseller, because both models are legitimate --
-- some resellers want to collect from their own customers and mark
-- invoices paid themselves; others want the platform's gateways to take
-- the money directly. 0 = reseller collects & marks paid (default,
-- matching how a reseller's customers behave today), 1 = customers pay
-- via the platform gateways.
-- ---------------------------------------------------------------------
ALTER TABLE `reseller_profiles`
    ADD COLUMN `credit_limit` decimal(14,2) NOT NULL DEFAULT 0.00
        COMMENT 'permitted overdraft; 0.00 = none'
        AFTER `credit_balance`,
    ADD COLUMN `payment_mode` tinyint(4) NOT NULL DEFAULT 0
        COMMENT '0=reseller collects & marks paid, 1=sub-customers pay via platform gateways'
        AFTER `credit_limit`;


-- ---------------------------------------------------------------------
-- 3. Opening balances — reconcile the cache with the ledger on day one
--
-- reseller_profiles.credit_balance has existed since v1 as a free-text
-- admin field: someone typed a number, nothing read it, nothing audited
-- it. From now on it is a CACHE of SUM(amount), so a non-zero legacy
-- value with no matching ledger row would make the very first
-- reconciliation report wrong and every explanation of it a story about
-- history rather than a bug hunt.
--
-- Write each non-zero legacy balance as an explicit opening adjustment.
-- balance_after equals the amount because it is by definition the first
-- row for that company.
--
-- Re-runnable via INSERT IGNORE against uq_credit_idem rather than a NOT
-- EXISTS subquery on the table being inserted into: the unique index is
-- already the authority on "has this movement been written", so re-using
-- it here keeps one rule instead of two, and it sidesteps MySQL's
-- restrictions on reading the INSERT target inside the same statement.
-- The only constraint a row here can violate is that key, so IGNORE is
-- not hiding anything else; the reconciliation query at the foot of this
-- file proves the outcome either way.
--
-- Resellers already at exactly 0.00 need no row -- an empty ledger sums
-- to 0, which already reconciles.
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
-- 4. invoice_items.item_type — document 3 and 4
--
-- The comment has been stale since the software catalog shipped (it
-- still claims only 1 and 2 exist while item_type = 3 has been written
-- by the cart for months). Phase 3 adds 4 for wallet top-ups, so correct
-- the whole enumeration rather than appending to a wrong list.
--
-- COMMENT-ONLY CHANGE -- the column type is unchanged, and there is no
-- CHECK constraint on item_type to widen.
--
-- A top-up line carries ref_id NULL. provisionPaidServices() loops only
-- items WITH a ref_id, so it skips top-up lines for free; Phase 3 code
-- adds an explicit item_type = 4 guard in
-- Provisioning_model::isRenewalInvoiceItem() as well, so a top-up can
-- never reach the registrar dispatcher even if that loop is changed.
-- ---------------------------------------------------------------------
ALTER TABLE `invoice_items`
    MODIFY COLUMN `item_type` tinyint(4) NOT NULL
        COMMENT '1=domain, 2=product_service, 3=software/license, 4=reseller wallet top-up (ref_id NULL)';


-- ---------------------------------------------------------------------
-- 5. Email templates for the wallet
--
-- Both are sent by Phase 3 code, which carries hardcoded fallback bodies
-- so a missing row degrades to a plainer email rather than to silence --
-- but seed them so the wording stays editable in Settings -> Email
-- Template like every other notice.
-- ---------------------------------------------------------------------

-- 5a. Top-up received and credited.
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

-- 5b. Balance could not cover a cost; provisioning is held.
--
-- The customer has already paid at this point, so the tone is "we are
-- holding your customer's order", not "your payment failed". The order
-- is recoverable: a top-up plus the release cron completes it.
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


-- =====================================================================
-- Verification
--
--   SHOW COLUMNS FROM `reseller_profiles` LIKE 'credit_limit';
--   SHOW COLUMNS FROM `reseller_profiles` LIKE 'payment_mode';
--   SHOW INDEX FROM `reseller_credit_transactions` WHERE Key_name = 'uq_credit_idem';
--
-- The reconciliation invariant this phase now maintains -- the cached
-- balance must equal the ledger sum for every reseller. Expect ZERO
-- rows, both immediately after this migration and at any later point:
--
--   SELECT rp.company_id, rp.credit_balance,
--          COALESCE(SUM(t.amount), 0) AS ledger_sum
--   FROM reseller_profiles rp
--   LEFT JOIN reseller_credit_transactions t
--          ON t.company_id = rp.company_id AND t.status = 1
--   GROUP BY rp.company_id, rp.credit_balance
--   HAVING rp.credit_balance <> COALESCE(SUM(t.amount), 0);
--
-- No wallet money moves until Phase 3 code is deployed: with the ledger
-- empty apart from opening rows, every balance reads exactly what
-- credit_balance already said, and a direct customer's company has no
-- parent_company_id so it never reaches the wallet at all.
-- =====================================================================
