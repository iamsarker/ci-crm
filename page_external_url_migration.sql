-- ============================================================
-- Migration: External URL support for Dynamic Pages
-- Adds an optional external_url column to `pages`.
-- When set, the front-end (/pages/{slug}) redirects to this URL
-- instead of rendering local page_content. Lets admins point a
-- page (e.g. Terms & Conditions, Privacy Policy, Refund Policy)
-- to an externally-hosted URL instead of storing content.
-- Run once on existing installs. crm_db.sql already carries this
-- column for fresh installs.
-- ============================================================

ALTER TABLE `pages`
  ADD COLUMN `external_url` VARCHAR(500) DEFAULT NULL
  COMMENT 'If set, /pages/{slug} redirects here instead of rendering page_content'
  AFTER `page_content`;
