-- Migration: Cart Enhancements for Hosting + Domain Flows
-- Run: mysql -u username -p database_name < migrations/cart_enhancements.sql

-- Add parent_cart_id for linking related cart items (domain ↔ hosting)
ALTER TABLE `add_to_carts`
ADD COLUMN `parent_cart_id` BIGINT(20) DEFAULT NULL
COMMENT 'Links related cart items (e.g., domain linked to hosting)'
AFTER `customer_session_id`;

-- Add domain_action enum for clearer domain action type
ALTER TABLE `add_to_carts`
ADD COLUMN `domain_action` ENUM('register', 'transfer', 'dns_update') DEFAULT NULL
COMMENT 'Domain action: register, transfer, or dns_update'
AFTER `hosting_domain_type`;

-- Add epp_code for domain transfers
ALTER TABLE `add_to_carts`
ADD COLUMN `epp_code` VARCHAR(100) DEFAULT NULL
COMMENT 'EPP/Auth code for domain transfers'
AFTER `domain_action`;

-- Add index for parent_cart_id lookups
ALTER TABLE `add_to_carts`
ADD INDEX `idx_parent_cart` (`parent_cart_id`);

-- Add foreign key constraint (optional, for referential integrity)
-- ALTER TABLE `add_to_carts`
-- ADD CONSTRAINT `fk_parent_cart` FOREIGN KEY (`parent_cart_id`)
-- REFERENCES `add_to_carts` (`id`) ON DELETE CASCADE;

-- ========================================
-- Order tables linking (for checkout)
-- ========================================

-- Add linked_domain_id to order_services (hosting service linked to domain order)
ALTER TABLE `order_services`
ADD COLUMN `linked_domain_id` BIGINT(20) DEFAULT NULL
COMMENT 'Links to order_domains.id when domain is purchased with hosting'
AFTER `hosting_domain`;

-- Add linked_service_id to order_domains (domain linked to hosting service)
ALTER TABLE `order_domains`
ADD COLUMN `linked_service_id` BIGINT(20) DEFAULT NULL
COMMENT 'Links to order_services.id when hosting is purchased with domain'
AFTER `domain`;

-- Add indexes for linking lookups
ALTER TABLE `order_services` ADD INDEX `idx_linked_domain` (`linked_domain_id`);
ALTER TABLE `order_domains` ADD INDEX `idx_linked_service` (`linked_service_id`);
