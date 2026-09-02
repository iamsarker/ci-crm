-- =====================================================================
-- v2.0.0 Phase 1 — Reseller tenancy on the admin portal
--
-- Adds the identity half of "resellers log in through the admin login
-- page and see only their own customers".
--
-- Why: admin_users has no link to a company, and the admin portal has no
-- authorization layer at all -- every one of the 34 admin controllers
-- carries exactly one guard (`if (!$this->isLogin())`) and isLogin()
-- returns a bare boolean. admin_role_id is SELECTed by Adminauth_model
-- but never copied into the session, and admin_roles has zero rows and
-- zero PHP references, so it is dead scaffolding, not a role system.
--
-- admin_type is a binary TENANCY discriminator, not a role:
--     0 = platform staff  (company_id = 0, sees everything, unchanged)
--     1 = reseller admin  (company_id = the reseller's companies.id)
--
-- The existing seeded admin keeps admin_type = 0 / company_id = 0, so
-- current installs behave exactly as before.
--
-- Run this ONCE per environment.
--
-- ⚠️  ORDERING: run this migration BEFORE deploying the updated
--     src/models/Adminauth_model.php. That file adds admin_type and
--     company_id to its login SELECT; against a database without the
--     columns the query fails with "Unknown column 'admin_type' in
--     'field list'" and NOBODY -- reseller or platform staff -- can log
--     into the admin portal. There is no second way in.
-- =====================================================================

ALTER TABLE `admin_users`
    ADD COLUMN `admin_type` tinyint(4) NOT NULL DEFAULT 0
        COMMENT '0=platform staff, 1=reseller admin'
        AFTER `admin_role_id`,
    ADD COLUMN `company_id` bigint(20) NOT NULL DEFAULT 0
        COMMENT 'reseller companies.id when admin_type=1; 0 for platform staff'
        AFTER `admin_type`,
    ADD KEY `idx_admin_tenant` (`admin_type`, `company_id`);


-- ---------------------------------------------------------------------
-- company_transfers — audit trail for requirement 9
--
-- Moving a customer between resellers rewrites companies.parent_company_id,
-- which silently changes who bills them and whose wallet is debited. That
-- is a money-affecting change with no other record, so it gets its own
-- audit row rather than relying on companies.updated_by.
--
-- Transfer moves FUTURE billing only: existing order_* rows keep their
-- frozen prices and any in-flight wallet debits stay with the old reseller.
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
-- Verify
--
-- Expect: every existing admin row is platform staff (admin_type = 0),
-- and company_transfers exists and is empty.
-- ---------------------------------------------------------------------
SELECT `admin_type`, COUNT(*) AS admins FROM `admin_users` GROUP BY `admin_type`;
SELECT COUNT(*) AS transfers FROM `company_transfers`;
