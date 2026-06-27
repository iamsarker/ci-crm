-- =============================================================================
-- WHMAZ SaaS subscription plans: catalog + entitlement schema and seed data
-- =============================================================================
-- The marketing site (whmaz.com/pricing.php) only renders the pricing table.
-- Payment, subscription, suspension, termination and upgrade all run in ci-crm.
--
-- Model used here:
--   * `plans`          - the 3 catalog tiers (Basic / Pro / Max)
--   * `plan_features`  - differentiated entitlement flags per plan
--   * `order_licenses` - a customer's WHMAZ SaaS subscription (its own product
--     line, separate from order_services=hosting / order_domains=domains).
--     The "account" is the owning company (order_licenses.company_id).
--
-- Universal features (billing_automation, customer_portal, server_provisioning,
-- domain_management, support_tickets, knowledge_base, multi_currency,
-- payment_gateways) are TRUE on every plan and are NOT stored here -- see
-- src/config/plans.php. Clients, servers and staff seats are UNLIMITED on every
-- plan, so there are no limit columns anywhere below.
--
-- Idempotent: safe to run repeatedly (CREATE TABLE IF NOT EXISTS,
-- ADD COLUMN/KEY IF NOT EXISTS, INSERT ... ON DUPLICATE KEY UPDATE).
-- Mirrors src/migrations/20260627120000_create_plans_subscription.php.
-- Target: MariaDB.
-- =============================================================================

-- --------------------------------------------------------
-- Table: plans
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `plans` (
  `id`                      bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `plan_key`                varchar(32)  NOT NULL,
  `name`                    varchar(60)  NOT NULL,
  `tagline`                 varchar(150) DEFAULT NULL,
  `price_monthly`           decimal(8,2) NOT NULL DEFAULT 0.00,
  `price_annual`            decimal(8,2) NOT NULL DEFAULT 0.00,
  `currency`                char(3)      NOT NULL DEFAULT 'USD',
  `is_popular`              tinyint(4)   NOT NULL DEFAULT 0,
  `sort_order`              int(11)      NOT NULL DEFAULT 0,
  `is_active`               tinyint(4)   NOT NULL DEFAULT 1,
  `paddle_product_id`       varchar(100) DEFAULT NULL,
  `paddle_price_monthly_id` varchar(100) DEFAULT NULL,
  `paddle_price_annual_id`  varchar(100) DEFAULT NULL,
  `created_at`              datetime     DEFAULT NULL,
  `updated_at`              datetime     DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_plans_plan_key` (`plan_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table: plan_features (booleans stored as '1'/'0', numbers as strings)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `plan_features` (
  `id`            bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `plan_id`       bigint(20) UNSIGNED NOT NULL,
  `feature_key`   varchar(64)  NOT NULL,
  `feature_value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_plan_features_plan_feature` (`plan_id`, `feature_key`),
  CONSTRAINT `fk_plan_features_plan`
    FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table: order_licenses  (a WHMAZ SaaS subscription line)
-- Mirrors the order_services lifecycle (status / dates / suspension /
-- termination) so the existing dunning + provisioning patterns apply.
-- status: 0=pending, 1=active, 2=expired, 3=suspended, 4=terminated
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order_licenses` (
  `id`                     bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id`               bigint(20)   NOT NULL,
  `company_id`             bigint(20)   NOT NULL,
  `plan_id`                bigint(20) UNSIGNED NOT NULL,
  `billing_cycle_id`       int(11)      NOT NULL,
  `currency_id`            int(11)      NOT NULL DEFAULT 0,
  `currency_code`          varchar(3)   DEFAULT NULL,
  `first_pay_amount`       decimal(15,2) NOT NULL DEFAULT 0.00,
  `recurring_amount`       decimal(15,2) NOT NULL DEFAULT 0.00,
  `license_key`            varchar(255) DEFAULT NULL COMMENT 'Issued on activation',
  `paddle_subscription_id` varchar(100) DEFAULT NULL COMMENT 'Paddle (MoR) subscription id',
  `auto_renew`             tinyint(1)   NOT NULL DEFAULT 1,
  `reg_date`               date         DEFAULT NULL,
  `exp_date`               date         DEFAULT NULL,
  `due_date`               date         DEFAULT NULL,
  `next_renewal_date`      date         DEFAULT NULL,
  `suspension_date`        date         DEFAULT NULL,
  `suspension_reason`      varchar(255) DEFAULT NULL,
  `termination_date`       date         DEFAULT NULL,
  `is_synced`              tinyint(4)   NOT NULL DEFAULT 1,
  `last_sync_dt`           datetime     DEFAULT NULL,
  `license_domain`         varchar(255) DEFAULT NULL COMMENT 'Install domain bound on first phone-home',
  `last_check_in`          datetime     DEFAULT NULL COMMENT 'Last successful phone-home',
  `last_check_ip`          varchar(45)  DEFAULT NULL COMMENT 'IP of last phone-home',
  `status`                 tinyint(4)   NOT NULL DEFAULT 0
                             COMMENT '0=pending,1=active,2=expired,3=suspended,4=terminated',
  `remarks`                varchar(255) DEFAULT NULL,
  `inserted_on`            datetime     DEFAULT NULL,
  `inserted_by`            int(11)      DEFAULT NULL,
  `updated_on`             timestamp    NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by`             int(11)      DEFAULT NULL,
  `deleted_on`             datetime     DEFAULT NULL,
  `deleted_by`             int(11)      DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order_licenses_company` (`company_id`),
  KEY `idx_order_licenses_order` (`order_id`),
  KEY `idx_order_licenses_plan` (`plan_id`),
  KEY `idx_order_licenses_status` (`status`),
  CONSTRAINT `fk_order_licenses_plan`
    FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================================================
-- Seed data
-- =============================================================================
-- Annual price = monthly x 12 x (1 - 0.15), rounded to cents:
--   basic 10.95 -> 111.72 | pro 15.95 -> 162.69 | max 24.95 -> 254.49
INSERT INTO `plans`
  (`plan_key`, `name`, `tagline`, `price_monthly`, `price_annual`, `currency`,
   `is_popular`, `sort_order`, `is_active`, `created_at`, `updated_at`)
VALUES
  ('basic', 'Basic', 'For new & small hosts',  10.95, 111.72, 'USD', 0, 1, 1, NOW(), NOW()),
  ('pro',   'Pro',   'For growing hosts',       15.95, 162.69, 'USD', 1, 2, 1, NOW(), NOW()),
  ('max',   'Max',   'For established hosts',   24.95, 254.49, 'USD', 0, 3, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `name`          = VALUES(`name`),
  `tagline`       = VALUES(`tagline`),
  `price_monthly` = VALUES(`price_monthly`),
  `price_annual`  = VALUES(`price_annual`),
  `currency`      = VALUES(`currency`),
  `is_popular`    = VALUES(`is_popular`),
  `sort_order`    = VALUES(`sort_order`),
  `is_active`     = VALUES(`is_active`),
  `updated_at`    = NOW();

-- Resolve plan ids (auto-increment safe across re-runs)
SET @plan_basic := (SELECT `id` FROM `plans` WHERE `plan_key` = 'basic');
SET @plan_pro   := (SELECT `id` FROM `plans` WHERE `plan_key` = 'pro');
SET @plan_max   := (SELECT `id` FROM `plans` WHERE `plan_key` = 'max');

-- Differentiated entitlement flags only (universal flags live in config)
INSERT INTO `plan_features` (`plan_id`, `feature_key`, `feature_value`) VALUES
  (@plan_basic, 'support_response_hours',    '72'),
  (@plan_basic, 'priority_support',          '0'),
  (@plan_basic, 'advanced_modules',          '0'),
  (@plan_basic, 'automatic_updates',         '0'),
  (@plan_basic, 'branding_removal',          '0'),
  (@plan_basic, 'dedicated_account_manager', '0'),
  (@plan_basic, 'sla_guarantee',             '0'),

  (@plan_pro,   'support_response_hours',    '48'),
  (@plan_pro,   'priority_support',          '1'),
  (@plan_pro,   'advanced_modules',          '1'),
  (@plan_pro,   'automatic_updates',         '1'),
  (@plan_pro,   'branding_removal',          '0'),
  (@plan_pro,   'dedicated_account_manager', '0'),
  (@plan_pro,   'sla_guarantee',             '0'),

  (@plan_max,   'support_response_hours',    '24'),
  (@plan_max,   'priority_support',          '1'),
  (@plan_max,   'advanced_modules',          '1'),
  (@plan_max,   'automatic_updates',         '1'),
  (@plan_max,   'branding_removal',          '1'),
  (@plan_max,   'dedicated_account_manager', '1'),
  (@plan_max,   'sla_guarantee',             '1')
ON DUPLICATE KEY UPDATE
  `feature_value` = VALUES(`feature_value`);

-- Dedicated grace period (days past invoice due date) before a SaaS license is
-- soft-suspended. Independent of hosting's `suspension_days_after_due`.
-- sys_cnf has no unique key on cnf_key, so guard with NOT EXISTS.
INSERT INTO `sys_cnf` (`cnf_key`, `cnf_val`, `cnf_group`, `created_on`, `updated_on`)
SELECT 'license_suspension_days_after_due', '7', 'AUTOMATION', NOW(), NOW()
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM `sys_cnf` WHERE `cnf_key` = 'license_suspension_days_after_due'
);
