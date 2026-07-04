-- ---------------------------------------------------------------------------
-- license_bind_ip_migration.sql
-- Adds the bound-install IP column used by the customer-portal download gate.
-- The client must supply domain + IP to bind the license before the first
-- download (bind-once). `license_domain` already exists; this adds the IP.
--
-- Safe to run once on an existing install. Idempotent-ish: will error only if
-- the column already exists.
-- ---------------------------------------------------------------------------

ALTER TABLE `order_licenses`
  ADD COLUMN `license_ip` varchar(45) DEFAULT NULL
  COMMENT 'Install IP the client bound at download (bind-once)'
  AFTER `license_domain`;
