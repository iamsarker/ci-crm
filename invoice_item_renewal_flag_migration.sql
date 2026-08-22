-- =====================================================================
-- invoice_items.is_renewal
--
-- Makes "this invoice item is a renewal" an explicit fact written by the
-- renewal cronjob, instead of something Provisioning_model has to infer
-- after the fact.
--
-- Why: Provisioning_model::isRenewalInvoiceItem() previously guessed, by
-- looking for an earlier PAID invoice carrying the same ref_id, and fell
-- back to looksLikeLaterTermFor() when it found none. Both fail on rows
-- whose original order never linked ref_id, or whose domain_order_id /
-- cp_username was never populated. The item then looks like a first-time
-- order and the registrar is asked to REGISTER a domain it already holds
-- ("The domain <x> already exists in our database"), or the control panel
-- is asked to create an account that already exists.
--
-- With the flag set, the cronjob's own intent is authoritative. The old
-- heuristics remain for items created before this migration.
--
-- Run this ONCE per environment.
--
-- ⚠️  ORDERING: run this migration BEFORE (or at the same time as)
--     deploying the updated src/models/Cronjob_model.php. That file now
--     writes is_renewal on every renewal invoice item; against a database
--     without the column, every renewal invoice insert fails with
--     "Unknown column 'is_renewal' in 'field list'".
-- =====================================================================

ALTER TABLE `invoice_items`
    ADD COLUMN `is_renewal` tinyint(1) NOT NULL DEFAULT 0
    COMMENT '1 = later-term renewal written by the renewal cronjob; provisioning renews instead of registering/creating'
    AFTER `billing_period_end`;


-- ---------------------------------------------------------------------
-- Backfill (optional but recommended)
--
-- Marks pre-existing items that are unambiguously renewals: an earlier
-- PAID invoice already billed the same ref_id + item_type. This is the
-- same rule the old primary check used, so it changes no behaviour --
-- it just records the answer instead of recomputing it.
--
-- Deliberately does NOT backfill from looksLikeLaterTermFor()'s weaker
-- signals; those stay as a runtime fallback.
-- ---------------------------------------------------------------------
UPDATE `invoice_items` ii
JOIN `invoices` inv ON inv.id = ii.invoice_id
SET ii.is_renewal = 1
WHERE ii.ref_id IS NOT NULL
  AND ii.ref_id > 0
  AND EXISTS (
      SELECT 1
      FROM `invoice_items` prev
      JOIN `invoices` previnv ON previnv.id = prev.invoice_id
      WHERE prev.ref_id      = ii.ref_id
        AND prev.item_type   = ii.item_type
        AND prev.invoice_id  < ii.invoice_id
        AND previnv.pay_status = 'PAID'
  );


-- ---------------------------------------------------------------------
-- Verify
-- ---------------------------------------------------------------------
SELECT is_renewal, COUNT(*) AS items
FROM `invoice_items`
GROUP BY is_renewal;
