-- ============================================================
-- Domain features migration
-- Adds:
--   1. order_domain_child_ns       — customer-managed child/private nameservers
--   2. domain_cancellation_requests — customer domain cancellation requests
--
-- Canonical schema also lives in crm_db.sql. Safe to run once on an existing DB.
-- ============================================================

CREATE TABLE IF NOT EXISTS `order_domain_child_ns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `hostname` varchar(255) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=active, 0=deleted',
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_child_ns_domain` (`domain_id`),
  KEY `idx_child_ns_company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `domain_cancellation_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `company_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL COMMENT 'users.id who requested',
  `domain` varchar(255) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=pending, 1=processed, 2=dismissed',
  `admin_note` varchar(255) DEFAULT NULL,
  `requested_on` datetime DEFAULT NULL,
  `processed_on` datetime DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cancel_domain` (`domain_id`),
  KEY `idx_cancel_company` (`company_id`),
  KEY `idx_cancel_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
