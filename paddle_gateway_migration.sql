-- ============================================================
-- Paddle Billing Payment Gateway - migration for existing installs
-- Adds the Paddle (Merchant of Record) gateway to payment_gateway.
-- Safe to run once (no-op if the row already exists).
--
-- Credential storage:
--   Client-side token => public_key      / test_public_key   (Paddle.js overlay)
--   API key (Bearer)  => secret_key      / test_secret_key   (server API)
--   Webhook secret    => webhook_secret  / test_webhook_secret (signature check)
--
-- Payment confirmation is server-to-server at /webhook/paddle
-- (transaction.completed), verified by HMAC signature, so that URL must be
-- publicly reachable and registered as a Paddle notification destination.
-- Also add https://cdn.paddle.com to the CSP (see src/config/config.php).
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
 'Paddle', 'paddle', 'online_card', NULL, 'ONLINE',
 '', '', '', 1,
 '', '', '', NULL,
 'USD,EUR,GBP,AUD,CAD,CHF,SGD,INR,JPY', 1.00, 0.00,
 'none', 0.00, 0.00, 'merchant',
 'Paddle', 'Pay securely by card via Paddle.', 8, 0, NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `payment_gateway` WHERE `gateway_code` = 'paddle'
);
