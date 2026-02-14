-- =====================================================
-- Payment Gateway Enhancement Migration
-- Version: 1.8.0
-- Date: 2026-02-13
-- Description: Enhanced payment gateway table for multi-gateway support
-- =====================================================

-- Backup existing data first (run manually if needed)
-- CREATE TABLE payment_gateway_backup AS SELECT * FROM payment_gateway;

-- =====================================================
-- Step 1: Alter existing payment_gateway table
-- =====================================================

ALTER TABLE `payment_gateway`
    -- Add gateway identification
    ADD COLUMN `gateway_code` VARCHAR(50) NOT NULL DEFAULT 'manual' COMMENT 'Unique code: stripe, paypal, razorpay, paystack, sslcommerz, bank_transfer, manual' AFTER `name`,
    ADD COLUMN `gateway_type` ENUM('online_card', 'online_wallet', 'bank_transfer', 'manual', 'crypto') NOT NULL DEFAULT 'manual' COMMENT 'Type of payment method' AFTER `gateway_code`,

    -- API credentials (encrypted recommended)
    ADD COLUMN `public_key` VARCHAR(500) DEFAULT NULL COMMENT 'Public/Publishable API key' AFTER `pay_type`,
    ADD COLUMN `secret_key` VARCHAR(500) DEFAULT NULL COMMENT 'Secret/Private API key (store encrypted)' AFTER `public_key`,
    ADD COLUMN `webhook_secret` VARCHAR(255) DEFAULT NULL COMMENT 'Webhook signature verification secret' AFTER `secret_key`,

    -- Mode and environment
    ADD COLUMN `is_test_mode` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Sandbox/Test, 0=Live/Production' AFTER `webhook_secret`,
    ADD COLUMN `test_public_key` VARCHAR(500) DEFAULT NULL COMMENT 'Test/Sandbox public key' AFTER `is_test_mode`,
    ADD COLUMN `test_secret_key` VARCHAR(500) DEFAULT NULL COMMENT 'Test/Sandbox secret key' AFTER `test_public_key`,

    -- Gateway-specific configuration (JSON)
    ADD COLUMN `extra_config` JSON DEFAULT NULL COMMENT 'Gateway-specific settings as JSON' AFTER `test_secret_key`,

    -- Bank transfer specific fields
    ADD COLUMN `bank_name` VARCHAR(200) DEFAULT NULL AFTER `extra_config`,
    ADD COLUMN `account_name` VARCHAR(200) DEFAULT NULL AFTER `bank_name`,
    ADD COLUMN `account_number` VARCHAR(100) DEFAULT NULL AFTER `account_name`,
    ADD COLUMN `routing_number` VARCHAR(50) DEFAULT NULL COMMENT 'Routing/Sort code' AFTER `account_number`,
    ADD COLUMN `swift_code` VARCHAR(20) DEFAULT NULL COMMENT 'SWIFT/BIC for international' AFTER `routing_number`,
    ADD COLUMN `iban` VARCHAR(50) DEFAULT NULL AFTER `swift_code`,

    -- Currency and limits
    ADD COLUMN `supported_currencies` VARCHAR(500) DEFAULT 'USD' COMMENT 'Comma-separated currency codes' AFTER `iban`,
    ADD COLUMN `min_amount` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Minimum transaction amount' AFTER `supported_currencies`,
    ADD COLUMN `max_amount` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Maximum transaction amount (0=unlimited)' AFTER `min_amount`,

    -- Processing fees
    ADD COLUMN `fee_type` ENUM('none', 'fixed', 'percentage', 'both') NOT NULL DEFAULT 'none' AFTER `max_amount`,
    ADD COLUMN `fee_fixed` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Fixed fee amount' AFTER `fee_type`,
    ADD COLUMN `fee_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Percentage fee (e.g., 2.9 for 2.9%)' AFTER `fee_fixed`,
    ADD COLUMN `fee_bearer` ENUM('merchant', 'customer') NOT NULL DEFAULT 'merchant' COMMENT 'Who pays the fee' AFTER `fee_percent`,

    -- Display settings
    ADD COLUMN `logo` VARCHAR(255) DEFAULT NULL COMMENT 'Gateway logo filename' AFTER `fee_bearer`,
    ADD COLUMN `display_name` VARCHAR(100) DEFAULT NULL COMMENT 'Name shown to customers' AFTER `logo`,
    ADD COLUMN `description` TEXT DEFAULT NULL COMMENT 'Description shown during checkout' AFTER `display_name`,
    ADD COLUMN `sort_order` INT(11) NOT NULL DEFAULT 0 COMMENT 'Display order (lower=first)' AFTER `description`,

    -- Webhook URL for reference
    ADD COLUMN `webhook_url` VARCHAR(500) DEFAULT NULL COMMENT 'Auto-generated webhook URL' AFTER `sort_order`,

    -- Add unique index on gateway_code
    ADD UNIQUE INDEX `idx_gateway_code` (`gateway_code`);

-- Update existing record
UPDATE `payment_gateway`
SET `gateway_code` = 'manual',
    `gateway_type` = 'manual',
    `display_name` = 'Manual Payment',
    `description` = 'Pay via bank transfer or other offline methods. Your order will be processed after payment confirmation.',
    `is_test_mode` = 0
WHERE `id` = 1;


-- =====================================================
-- Step 2: Insert default payment gateways
-- =====================================================

-- Stripe
INSERT INTO `payment_gateway` (
    `name`, `gateway_code`, `gateway_type`, `pay_type`, `display_name`, `description`,
    `supported_currencies`, `fee_type`, `fee_percent`, `fee_fixed`,
    `is_test_mode`, `sort_order`, `status`, `inserted_on`
) VALUES (
    'Stripe', 'stripe', 'online_card', 'ONLINE', 'Credit/Debit Card',
    'Pay securely with your credit or debit card via Stripe.',
    'USD,EUR,GBP,CAD,AUD,INR,SGD,JPY', 'both', 2.90, 0.30,
    1, 1, 0, NOW()
);

-- PayPal
INSERT INTO `payment_gateway` (
    `name`, `gateway_code`, `gateway_type`, `pay_type`, `display_name`, `description`,
    `supported_currencies`, `fee_type`, `fee_percent`, `fee_fixed`,
    `is_test_mode`, `sort_order`, `status`, `inserted_on`
) VALUES (
    'PayPal', 'paypal', 'online_wallet', 'ONLINE', 'PayPal',
    'Pay securely using your PayPal account or card.',
    'USD,EUR,GBP,CAD,AUD,JPY,CHF,HKD,SGD,SEK,DKK,PLN,NOK,CZK,ILS,MXN,BRL,PHP,TWD,THB,MYR', 'both', 2.90, 0.30,
    1, 2, 0, NOW()
);

-- Razorpay (India)
INSERT INTO `payment_gateway` (
    `name`, `gateway_code`, `gateway_type`, `pay_type`, `display_name`, `description`,
    `supported_currencies`, `fee_type`, `fee_percent`,
    `is_test_mode`, `sort_order`, `status`, `inserted_on`
) VALUES (
    'Razorpay', 'razorpay', 'online_card', 'ONLINE', 'Razorpay',
    'Pay with UPI, Cards, Netbanking, or Wallets via Razorpay.',
    'INR', 'percentage', 2.00,
    1, 3, 0, NOW()
);

-- PayStack (Africa)
INSERT INTO `payment_gateway` (
    `name`, `gateway_code`, `gateway_type`, `pay_type`, `display_name`, `description`,
    `supported_currencies`, `fee_type`, `fee_percent`, `fee_fixed`,
    `is_test_mode`, `sort_order`, `status`, `inserted_on`
) VALUES (
    'Paystack', 'paystack', 'online_card', 'ONLINE', 'Paystack',
    'Pay with card or bank transfer via Paystack.',
    'NGN,GHS,ZAR,KES', 'both', 1.50, 100.00,
    1, 4, 0, NOW()
);

-- SSLCommerz (Bangladesh)
INSERT INTO `payment_gateway` (
    `name`, `gateway_code`, `gateway_type`, `pay_type`, `display_name`, `description`,
    `extra_config`, `supported_currencies`, `fee_type`, `fee_percent`,
    `is_test_mode`, `sort_order`, `status`, `inserted_on`
) VALUES (
    'SSLCommerz', 'sslcommerz', 'online_card', 'ONLINE', 'SSLCommerz',
    'Pay with bKash, Nagad, Cards, or Mobile Banking.',
    '{"store_id": "", "store_password": "", "sandbox_url": "https://sandbox.sslcommerz.com", "live_url": "https://securepay.sslcommerz.com"}',
    'BDT', 'percentage', 2.00,
    1, 5, 0, NOW()
);

-- Bank Transfer
INSERT INTO `payment_gateway` (
    `name`, `gateway_code`, `gateway_type`, `pay_type`, `display_name`, `description`,
    `supported_currencies`, `is_test_mode`, `sort_order`, `status`, `inserted_on`
) VALUES (
    'Bank Transfer', 'bank_transfer', 'bank_transfer', 'OFFLINE', 'Bank Transfer',
    'Transfer funds directly to our bank account. Order will be processed after payment confirmation.',
    'USD,EUR,GBP,BDT,INR', 0, 10, 0, NOW()
);


-- =====================================================
-- Step 3: Create payment_transactions table (enhanced invoice_txn)
-- =====================================================

CREATE TABLE IF NOT EXISTS `payment_transactions` (
    `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
    `transaction_uuid` VARCHAR(36) NOT NULL,
    `invoice_id` BIGINT(20) NOT NULL,
    `payment_gateway_id` INT(11) NOT NULL,
    `gateway_code` VARCHAR(50) NOT NULL,

    -- Transaction identifiers
    `gateway_transaction_id` VARCHAR(255) DEFAULT NULL COMMENT 'Transaction ID from payment gateway',
    `gateway_payment_id` VARCHAR(255) DEFAULT NULL COMMENT 'Payment ID (for gateways like Razorpay)',
    `gateway_order_id` VARCHAR(255) DEFAULT NULL COMMENT 'Order ID created at gateway',
    `gateway_subscription_id` VARCHAR(255) DEFAULT NULL COMMENT 'For recurring payments',

    -- Amount details
    `amount` DECIMAL(15,2) NOT NULL,
    `fee_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Processing fee charged',
    `net_amount` DECIMAL(15,2) NOT NULL COMMENT 'Amount after fee deduction',
    `currency_code` VARCHAR(3) NOT NULL,
    `exchange_rate` DECIMAL(10,6) DEFAULT 1.000000 COMMENT 'Exchange rate if currency converted',

    -- Transaction type and status
    `txn_type` ENUM('payment', 'refund', 'partial_refund', 'chargeback', 'credit') NOT NULL DEFAULT 'payment',
    `status` ENUM('pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded') NOT NULL DEFAULT 'pending',
    `failure_reason` VARCHAR(500) DEFAULT NULL,

    -- Payment method details
    `payment_method` VARCHAR(50) DEFAULT NULL COMMENT 'card, bank, wallet, upi, etc.',
    `card_brand` VARCHAR(20) DEFAULT NULL COMMENT 'visa, mastercard, amex, etc.',
    `card_last4` VARCHAR(4) DEFAULT NULL,
    `card_exp_month` TINYINT(2) DEFAULT NULL,
    `card_exp_year` SMALLINT(4) DEFAULT NULL,
    `bank_name` VARCHAR(100) DEFAULT NULL,
    `wallet_name` VARCHAR(50) DEFAULT NULL COMMENT 'paypal, gpay, applepay, etc.',

    -- Customer details (from gateway)
    `payer_email` VARCHAR(255) DEFAULT NULL,
    `payer_name` VARCHAR(200) DEFAULT NULL,
    `payer_phone` VARCHAR(20) DEFAULT NULL,

    -- Gateway response storage
    `gateway_response` JSON DEFAULT NULL COMMENT 'Full response from gateway',
    `webhook_payload` JSON DEFAULT NULL COMMENT 'Webhook data received',

    -- IP and metadata
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(500) DEFAULT NULL,
    `metadata` JSON DEFAULT NULL COMMENT 'Additional transaction metadata',

    -- Timestamps
    `initiated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `completed_at` DATETIME DEFAULT NULL,
    `refunded_at` DATETIME DEFAULT NULL,

    -- Audit fields
    `inserted_on` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `inserted_by` BIGINT(20) DEFAULT NULL,
    `updated_on` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `updated_by` BIGINT(20) DEFAULT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_transaction_uuid` (`transaction_uuid`),
    KEY `idx_invoice_id` (`invoice_id`),
    KEY `idx_gateway_txn` (`gateway_transaction_id`),
    KEY `idx_status` (`status`),
    KEY `idx_created` (`initiated_at`),
    KEY `idx_gateway` (`payment_gateway_id`, `gateway_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- =====================================================
-- Step 4: Create webhook_logs table for debugging
-- =====================================================

CREATE TABLE IF NOT EXISTS `webhook_logs` (
    `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
    `gateway_code` VARCHAR(50) NOT NULL,
    `event_type` VARCHAR(100) DEFAULT NULL COMMENT 'Event type from gateway',
    `event_id` VARCHAR(255) DEFAULT NULL COMMENT 'Event ID from gateway',
    `payload` JSON NOT NULL COMMENT 'Raw webhook payload',
    `headers` JSON DEFAULT NULL COMMENT 'Request headers',
    `signature` VARCHAR(500) DEFAULT NULL COMMENT 'Signature header value',
    `signature_valid` TINYINT(1) DEFAULT NULL COMMENT 'Was signature valid?',
    `processed` TINYINT(1) NOT NULL DEFAULT 0,
    `processed_at` DATETIME DEFAULT NULL,
    `process_result` VARCHAR(500) DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `received_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_gateway` (`gateway_code`),
    KEY `idx_event` (`event_type`),
    KEY `idx_processed` (`processed`),
    KEY `idx_received` (`received_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- =====================================================
-- Step 5: Create payment_refunds table
-- =====================================================

CREATE TABLE IF NOT EXISTS `payment_refunds` (
    `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
    `refund_uuid` VARCHAR(36) NOT NULL,
    `transaction_id` BIGINT(20) NOT NULL COMMENT 'FK to payment_transactions',
    `invoice_id` BIGINT(20) NOT NULL,

    `gateway_refund_id` VARCHAR(255) DEFAULT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `currency_code` VARCHAR(3) NOT NULL,
    `reason` VARCHAR(500) DEFAULT NULL,
    `status` ENUM('pending', 'processing', 'completed', 'failed') NOT NULL DEFAULT 'pending',
    `failure_reason` VARCHAR(500) DEFAULT NULL,

    `gateway_response` JSON DEFAULT NULL,

    `requested_by` BIGINT(20) DEFAULT NULL,
    `requested_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `processed_at` DATETIME DEFAULT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_refund_uuid` (`refund_uuid`),
    KEY `idx_transaction` (`transaction_id`),
    KEY `idx_invoice` (`invoice_id`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- =====================================================
-- Step 6: Update invoice_txn to link with new table
-- =====================================================

ALTER TABLE `invoice_txn`
    ADD COLUMN `payment_transaction_id` BIGINT(20) DEFAULT NULL COMMENT 'FK to payment_transactions' AFTER `payment_gateway_id`,
    ADD KEY `idx_payment_transaction` (`payment_transaction_id`);


-- =====================================================
-- Done! Run this SQL on your database
-- =====================================================
