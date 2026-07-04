-- ============================================================
-- bKash Payment Gateway - migration for existing installs
-- Adds the bKash tokenized-checkout gateway to payment_gateway.
-- Safe to run once on an existing DB (no-op if the row exists).
--
-- Credential storage:
--   App Key    -> public_key      / test_public_key   (set in admin)
--   App Secret -> secret_key      / test_secret_key   (set in admin)
--   Username   -> extra_config.username      / extra_config.sandbox_username
--   Password   -> extra_config.password      / extra_config.sandbox_password
--
-- Seeded disabled (status=0) in sandbox mode (is_test_mode=1).
-- Configure credentials in Settings -> Payment Gateways, then enable.
-- ============================================================

INSERT INTO `payment_gateway`
(`name`, `gateway_code`, `gateway_type`, `icon_fa_unicode`, `pay_type`,
 `public_key`, `secret_key`, `webhook_secret`, `is_test_mode`,
 `test_public_key`, `test_secret_key`, `test_webhook_secret`, `extra_config`,
 `supported_currencies`, `min_amount`, `max_amount`,
 `fee_type`, `fee_fixed`, `fee_percent`, `fee_bearer`,
 `display_name`, `description`, `sort_order`, `status`, `inserted_on`)
SELECT
 'bKash', 'bkash', 'online_wallet', NULL, 'ONLINE',
 '', '', '', 1,
 '', '', NULL,
 '{"username":"","password":"","sandbox_username":"","sandbox_password":"","sandbox_url":"https://tokenized.sandbox.bka.sh/v1.2.0-beta","live_url":"https://tokenized.pay.bka.sh/v1.2.0-beta"}',
 'BDT', 1.00, 0.00,
 'none', 0.00, 0.00, 'merchant',
 'bKash', 'Pay securely with your bKash account.', 6, 0, NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `payment_gateway` WHERE `gateway_code` = 'bkash'
);
