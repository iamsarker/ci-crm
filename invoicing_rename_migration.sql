-- =====================================================================
-- Billing -> Invoicing rename : stored-URL cleanup
-- ---------------------------------------------------------------------
-- The customer-portal module `billing` was renamed to `invoicing`, so the
-- public paths changed:
--     billing/invoices             -> invoicing/invoices
--     billing/view_invoice/{uuid}  -> invoicing/view_invoice/{uuid}
--     billing/download_invoice/{u} -> invoicing/download_invoice/{u}
--     billing/invoice_list_api     -> invoicing/invoice_list_api
--     billing/pay/...              -> invoicing/pay/...
--
-- Scope: only content an ADMIN can type a link into. Email templates,
-- alerts and dunning rules are NOT touched -- their invoice links come
-- from {invoice_url}-style placeholders built in PHP at send time, so
-- they already carry the new path.
--
-- Old `billing/*` URLs still resolve via the legacy routes in
-- src/config/routes.php, so this is housekeeping, not a hard requirement.
--
-- Run once per environment (prod / stage / dev). Idempotent: re-running
-- changes nothing because no `billing/` path remains afterwards.
-- Take a backup first.
-- =====================================================================

-- ---------------------------------------------------------------------
-- 0. PREVIEW (optional) -- see what will change before you commit.
--    If this returns no rows, there is nothing to migrate.
-- ---------------------------------------------------------------------
-- SELECT 'pages' AS tbl, id, page_slug AS ref FROM pages
--   WHERE page_content LIKE '%billing/%' OR external_url LIKE '%billing/%'
-- UNION ALL SELECT 'kbs', id, slug FROM kbs                     WHERE article LIKE '%billing/%'
-- UNION ALL SELECT 'announcements', id, slug FROM announcements WHERE description LIKE '%billing/%'
-- UNION ALL SELECT 'app_notifications', id, type FROM app_notifications
--   WHERE url LIKE '%billing/%' OR message LIKE '%billing/%';

START TRANSACTION;

-- ---------------------------------------------------------------------
-- 1. Dynamic pages (content + external redirect URL)
-- ---------------------------------------------------------------------
UPDATE pages
SET page_content = REPLACE(
                     REPLACE(
                       REPLACE(
                         REPLACE(page_content, 'billing/download_invoice', 'invoicing/download_invoice'),
                       'billing/view_invoice', 'invoicing/view_invoice'),
                     'billing/invoices', 'invoicing/invoices'),
                   'billing/pay', 'invoicing/pay')
WHERE page_content LIKE '%billing/%';

UPDATE pages
SET external_url = REPLACE(
                     REPLACE(
                       REPLACE(
                         REPLACE(external_url, 'billing/download_invoice', 'invoicing/download_invoice'),
                       'billing/view_invoice', 'invoicing/view_invoice'),
                     'billing/invoices', 'invoicing/invoices'),
                   'billing/pay', 'invoicing/pay')
WHERE external_url LIKE '%billing/%';

-- ---------------------------------------------------------------------
-- 2. Knowledge base articles
-- ---------------------------------------------------------------------
UPDATE kbs
SET article = REPLACE(
                REPLACE(
                  REPLACE(
                    REPLACE(article, 'billing/download_invoice', 'invoicing/download_invoice'),
                  'billing/view_invoice', 'invoicing/view_invoice'),
                'billing/invoices', 'invoicing/invoices'),
              'billing/pay', 'invoicing/pay')
WHERE article LIKE '%billing/%';

-- ---------------------------------------------------------------------
-- 3. Announcements
-- ---------------------------------------------------------------------
UPDATE announcements
SET description = REPLACE(
                    REPLACE(
                      REPLACE(
                        REPLACE(description, 'billing/download_invoice', 'invoicing/download_invoice'),
                      'billing/view_invoice', 'invoicing/view_invoice'),
                    'billing/invoices', 'invoicing/invoices'),
                  'billing/pay', 'invoicing/pay')
WHERE description LIKE '%billing/%';

-- ---------------------------------------------------------------------
-- 4. In-app notifications (deep links persisted at creation time)
-- ---------------------------------------------------------------------
UPDATE app_notifications
SET url = REPLACE(
            REPLACE(
              REPLACE(
                REPLACE(url, 'billing/download_invoice', 'invoicing/download_invoice'),
              'billing/view_invoice', 'invoicing/view_invoice'),
            'billing/invoices', 'invoicing/invoices'),
          'billing/pay', 'invoicing/pay')
WHERE url LIKE '%billing/%';

UPDATE app_notifications
SET message = REPLACE(
                REPLACE(
                  REPLACE(
                    REPLACE(message, 'billing/download_invoice', 'invoicing/download_invoice'),
                  'billing/view_invoice', 'invoicing/view_invoice'),
                'billing/invoices', 'invoicing/invoices'),
              'billing/pay', 'invoicing/pay')
WHERE message LIKE '%billing/%';

COMMIT;

-- ---------------------------------------------------------------------
-- 5. VERIFY -- every count below should be 0.
-- ---------------------------------------------------------------------
-- SELECT
--   (SELECT COUNT(*) FROM pages             WHERE page_content LIKE '%billing/%' OR external_url LIKE '%billing/%') AS pages,
--   (SELECT COUNT(*) FROM kbs               WHERE article LIKE '%billing/%') AS kbs,
--   (SELECT COUNT(*) FROM announcements     WHERE description LIKE '%billing/%') AS announcements,
--   (SELECT COUNT(*) FROM app_notifications WHERE url LIKE '%billing/%' OR message LIKE '%billing/%') AS notifications;

-- NOTE: historical ticket bodies (`tickets`, `ticket_replies`) and gateway
-- payloads (`payment_transactions.gateway_response`, `webhook_logs`) are
-- deliberately NOT rewritten -- they are records of what was actually
-- sent/received. The legacy routes keep those old links working.
