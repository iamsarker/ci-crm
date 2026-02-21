-- Migration: System Configuration Settings
-- Date: 2026-02-21
-- Run: mysql -u username -p database_name < migrations/sys_cnf_billing_automation.sql
--
-- This migration adds system configuration settings to the sys_cnf table.
-- These settings are managed via Admin Portal instead of .env file.

-- =============================================================================
-- BILLING CONFIGURATION
-- =============================================================================
-- Admin Location: Settings → Billing

INSERT INTO `sys_cnf` (`cnf_key`, `cnf_val`, `cnf_group`, `created_on`, `updated_on`) VALUES
('invoice_prefix', 'INV-', 'BILLING', NOW(), NOW()),
('invoice_starting_number', '1000', 'BILLING', NOW(), NOW()),
('invoice_due_days', '7', 'BILLING', NOW(), NOW()),
('tax_enabled', '1', 'BILLING', NOW(), NOW()),
('tax_name', 'VAT', 'BILLING', NOW(), NOW()),
('tax_rate', '10.00', 'BILLING', NOW(), NOW()),
('tax_inclusive', '0', 'BILLING', NOW(), NOW()),
('late_fee_enabled', '0', 'BILLING', NOW(), NOW()),
('late_fee_amount', '5.00', 'BILLING', NOW(), NOW()),
('late_fee_percentage', '0', 'BILLING', NOW(), NOW()),
('default_currency_code', 'USD', 'BILLING', NOW(), NOW()),
('default_currency_symbol', '$', 'BILLING', NOW(), NOW()),
('multi_currency_enabled', '0', 'BILLING', NOW(), NOW());

-- =============================================================================
-- AUTOMATION / CRON JOBS
-- =============================================================================
-- Admin Location: Settings → Automation

INSERT INTO `sys_cnf` (`cnf_key`, `cnf_val`, `cnf_group`, `created_on`, `updated_on`) VALUES
('cron_enabled', '1', 'AUTOMATION', NOW(), NOW()),
('invoice_generation_days_before', '7', 'AUTOMATION', NOW(), NOW()),
('payment_reminder_first', '3', 'AUTOMATION', NOW(), NOW()),
('payment_reminder_second', '1', 'AUTOMATION', NOW(), NOW()),
('payment_reminder_overdue', '3', 'AUTOMATION', NOW(), NOW()),
('suspension_days_after_due', '5', 'AUTOMATION', NOW(), NOW()),
('cancellation_days_after_suspension', '30', 'AUTOMATION', NOW(), NOW()),
('domain_reminder_first', '30', 'AUTOMATION', NOW(), NOW()),
('domain_reminder_second', '15', 'AUTOMATION', NOW(), NOW()),
('domain_reminder_third', '7', 'AUTOMATION', NOW(), NOW());

-- =============================================================================
-- FEATURE FLAGS
-- =============================================================================
-- Admin Location: Settings → Features

INSERT INTO `sys_cnf` (`cnf_key`, `cnf_val`, `cnf_group`, `created_on`, `updated_on`) VALUES
('feature_customer_registration', '1', 'FEATURES', NOW(), NOW()),
('feature_domain_registration', '1', 'FEATURES', NOW(), NOW()),
('feature_support_tickets', '1', 'FEATURES', NOW(), NOW()),
('feature_knowledge_base', '1', 'FEATURES', NOW(), NOW()),
('feature_announcements', '1', 'FEATURES', NOW(), NOW()),
('feature_affiliate_system', '0', 'FEATURES', NOW(), NOW()),
('feature_two_factor_auth', '0', 'FEATURES', NOW(), NOW()),
('feature_social_login', '0', 'FEATURES', NOW(), NOW());

-- =============================================================================
-- NOTIFICATIONS
-- =============================================================================
-- Admin Location: Settings → Notifications

INSERT INTO `sys_cnf` (`cnf_key`, `cnf_val`, `cnf_group`, `created_on`, `updated_on`) VALUES
('admin_notification_email', 'admin@yourcompany.com', 'NOTIFICATIONS', NOW(), NOW()),
('notify_admin_new_order', '1', 'NOTIFICATIONS', NOW(), NOW()),
('notify_admin_new_ticket', '1', 'NOTIFICATIONS', NOW(), NOW()),
('notify_admin_new_customer', '1', 'NOTIFICATIONS', NOW(), NOW()),
('notify_admin_payment', '1', 'NOTIFICATIONS', NOW(), NOW());

-- =============================================================================
-- CUSTOMER PORTAL SETTINGS
-- =============================================================================
-- Admin Location: Settings → Customer Portal

INSERT INTO `sys_cnf` (`cnf_key`, `cnf_val`, `cnf_group`, `created_on`, `updated_on`) VALUES
('allow_customer_registration', '1', 'PORTAL', NOW(), NOW()),
('email_verification_required', '1', 'PORTAL', NOW(), NOW()),
('default_account_status', 'active', 'PORTAL', NOW(), NOW()),
('dashboard_show_services', '1', 'PORTAL', NOW(), NOW()),
('dashboard_show_invoices', '1', 'PORTAL', NOW(), NOW()),
('dashboard_show_tickets', '1', 'PORTAL', NOW(), NOW()),
('dashboard_show_announcements', '1', 'PORTAL', NOW(), NOW());

-- =============================================================================
-- SUPPORT SYSTEM
-- =============================================================================
-- Admin Location: Settings → Support

INSERT INTO `sys_cnf` (`cnf_key`, `cnf_val`, `cnf_group`, `created_on`, `updated_on`) VALUES
('default_ticket_department', 'General Support', 'SUPPORT', NOW(), NOW()),
('ticket_departments', 'Technical Support,Billing Support,Sales,General Inquiry', 'SUPPORT', NOW(), NOW()),
('ticket_priorities', 'Low,Medium,High,Urgent', 'SUPPORT', NOW(), NOW()),
('auto_close_tickets_after', '7', 'SUPPORT', NOW(), NOW()),
('notify_customer_ticket_reply', '1', 'SUPPORT', NOW(), NOW()),
('ticket_attachments_enabled', '1', 'SUPPORT', NOW(), NOW()),
('ticket_max_attachment_size', '5120', 'SUPPORT', NOW(), NOW());

-- =============================================================================
-- VERIFICATION QUERY
-- =============================================================================
-- Run this to verify the migration was successful:
-- SELECT cnf_group, COUNT(*) as count FROM sys_cnf GROUP BY cnf_group;
