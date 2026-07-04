-- ============================================================
-- PayHere Payment Gateway (Sri Lanka) - migration for existing installs
-- Adds the PayHere Checkout gateway to payment_gateway.
-- Safe to run once (no-op if the row already exists).
--
-- Credential storage:
--   Merchant ID     -> public_key      / test_public_key   (set in admin)
--   Merchant Secret -> secret_key      / test_secret_key   (set in admin)
--
-- Payment confirmation is server-to-server at /webhook/payhere (notify_url),
-- so that URL must be publicly reachable by PayHere.
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
 'PayHere', 'payhere', 'online_card', NULL, 'ONLINE',
 '', '', '', 1,
 '', '', NULL, NULL,
 'LKR,USD,GBP,EUR,AUD', 1.00, 0.00,
 'none', 0.00, 0.00, 'merchant',
 'PayHere', 'Pay with Visa, Mastercard, or mobile wallets via PayHere.', 7, 0, NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `payment_gateway` WHERE `gateway_code` = 'payhere'
);
