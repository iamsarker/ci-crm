-- Migration: Increase pay_status field length to accommodate 'CANCELLED' status
-- Date: 2026-02-27

ALTER TABLE `invoices`
MODIFY COLUMN `pay_status` VARCHAR(10) NOT NULL DEFAULT 'DUE' COMMENT 'DUE,PAID,PARTIAL,CANCELLED';
