-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 03, 2026 at 08:50 AM
-- Server version: 10.11.18-MariaDB-cll-lve
-- PHP Version: 8.3.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `whmazc_demodb`
--

-- --------------------------------------------------------

--
-- Table structure for table `add_to_carts`
--

CREATE TABLE `add_to_carts` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `customer_session_id` bigint(20) DEFAULT NULL,
  `parent_cart_id` bigint(20) DEFAULT NULL COMMENT 'Links related cart items (e.g., domain linked to hosting)',
  `item_type` tinyint(4) NOT NULL COMMENT '1=domain, 2=product_service',
  `product_service_id` int(11) NOT NULL DEFAULT 0,
  `note` text DEFAULT NULL,
  `hosting_domain` varchar(200) DEFAULT NULL,
  `hosting_domain_type` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0=DNS,1=REGISTER,2=TRANSFER',
  `domain_action` enum('register','transfer','dns_update') DEFAULT NULL COMMENT 'Domain action: register, transfer, or dns_update',
  `epp_code` varchar(100) DEFAULT NULL COMMENT 'EPP/Auth code for domain transfers',
  `sub_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(15,2) NOT NULL DEFAULT 0.00,
  `vat` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `product_service_pricing_id` int(11) NOT NULL DEFAULT 0,
  `dom_pricing_id` int(11) NOT NULL DEFAULT 0,
  `currency_id` int(11) NOT NULL,
  `currency_code` varchar(3) DEFAULT NULL,
  `billing_cycle_id` int(11) NOT NULL,
  `billing_cycle` varchar(12) DEFAULT NULL,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_logins`
--

CREATE TABLE `admin_logins` (
  `id` bigint(20) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `login_time` datetime NOT NULL,
  `session_val` varchar(50) NOT NULL,
  `terminal` varchar(40) DEFAULT NULL,
  `device_info` text DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_logins`
--

INSERT INTO `admin_logins` (`id`, `admin_id`, `login_time`, `session_val`, `terminal`, `device_info`, `active`) VALUES
(1, 1, '2026-03-07 00:10:08', '0', '103.159.72.16', NULL, 1),
(2, 1, '2026-03-07 00:11:05', '0', '103.159.72.16', NULL, 1),
(3, 1, '2026-03-07 02:27:04', '0', '103.159.72.16', NULL, 1),
(4, 1, '2026-03-07 11:44:15', '0', '::1', NULL, 1),
(5, 1, '2026-03-14 06:44:15', '0', '::1', NULL, 1),
(6, 1, '2026-03-15 15:03:26', '0', '::1', NULL, 1),
(7, 1, '2026-03-17 05:06:01', '0', '::1', NULL, 1),
(8, 1, '2026-03-17 07:41:14', '0', '::1', NULL, 1),
(9, 1, '2026-03-17 14:41:22', '0', '::1', NULL, 1),
(10, 1, '2026-03-17 15:32:00', '0', '::1', NULL, 1),
(11, 1, '2026-03-18 14:43:37', '0', '::1', NULL, 1),
(12, 1, '2026-03-19 03:31:00', '0', '103.159.72.16', NULL, 1),
(13, 1, '2026-03-25 02:12:46', '0', '::1', NULL, 1),
(14, 1, '2026-05-10 08:49:39', '0', '31.183.178.77', NULL, 1),
(15, 1, '2026-06-27 06:24:36', '0', '103.159.72.16', NULL, 1),
(16, 1, '2026-07-03 07:21:56', '0', '::1', NULL, 1),
(17, 1, '2026-07-03 09:59:53', '0', '::1', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `admin_roles`
--

CREATE TABLE `admin_roles` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `remarks` text NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `admin_role_id` int(11) NOT NULL,
  `first_name` varchar(150) NOT NULL,
  `last_name` varchar(150) NOT NULL,
  `username` text NOT NULL,
  `password` text NOT NULL,
  `email` text NOT NULL,
  `mobile` varchar(16) DEFAULT NULL,
  `phone` varchar(16) DEFAULT NULL,
  `designation` varchar(150) DEFAULT NULL,
  `signature` text DEFAULT NULL,
  `profile_pic` varchar(200) DEFAULT NULL,
  `support_depts` varchar(255) DEFAULT NULL COMMENT 'comma separator',
  `pass_reset_key` text DEFAULT NULL,
  `pass_reset_data` text DEFAULT NULL,
  `pass_reset_expiry` datetime DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `admin_role_id`, `first_name`, `last_name`, `username`, `password`, `email`, `mobile`, `phone`, `designation`, `signature`, `profile_pic`, `support_depts`, `pass_reset_key`, `pass_reset_data`, `pass_reset_expiry`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`) VALUES
(1, 1, 'Admin', 'User', 'admin', '$2y$12$rdf4.Xl6CAEO53LX2395OenB.XWGxmzhjCS3B1Y5CI9HTvyjXJKtC', 'admin@example.com', NULL, NULL, NULL, NULL, NULL, '1,2', NULL, NULL, NULL, 1, NULL, NULL, '2026-03-07 06:24:41', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--

CREATE TABLE `alerts` (
  `alert_id` bigint(20) NOT NULL,
  `alert_sub` varchar(255) NOT NULL,
  `alert_msg` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `alert_type` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1=sms, 2=web, 3=email, 4=fcm',
  `recipient_type` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1=admin, 2=customer',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0=inactive, 1=active',
  `created_on` datetime DEFAULT current_timestamp(),
  `created_by` bigint(20) DEFAULT NULL,
  `updated_on` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alert_recipients`
--

CREATE TABLE `alert_recipients` (
  `alert_recipient_id` bigint(20) NOT NULL,
  `alert_id` bigint(20) NOT NULL,
  `recipient_id` bigint(20) NOT NULL,
  `mobile_no` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `fcm_topic` varchar(100) DEFAULT NULL,
  `is_sent` tinyint(4) NOT NULL DEFAULT 0 COMMENT '1=sent',
  `is_resend` tinyint(4) NOT NULL DEFAULT 0,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0=inactive, 1=active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `is_published` tinyint(4) NOT NULL DEFAULT 1,
  `publish_date` date DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `total_view` int(11) NOT NULL DEFAULT 0,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_notifications`
-- In-app notifications with per-recipient read state (admin + customer portals)
--

CREATE TABLE `app_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recipient_type` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1=admin, 2=customer',
  `recipient_id` int(11) NOT NULL DEFAULT 0 COMMENT 'admin_users.id or companies.id',
  `type` varchar(50) DEFAULT 'system' COMMENT 'order, payment, ticket, cancellation, customer, system',
  `title` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `is_read` tinyint(4) NOT NULL DEFAULT 0,
  `read_on` datetime DEFAULT NULL,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_recipient` (`recipient_type`,`recipient_id`,`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `slug`, `description`, `is_published`, `publish_date`, `tags`, `status`, `total_view`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(1, 'test announcement', 'test-announcement', '<p>test announcement jashdk jksadnkfs. test announcement jashdk jksadnkfs. test announcement jashdk jksadnkfs. test announcement jashdk jksadnkfs. test announcement jashdk jksadnkfs. test announcement jashdk jksadnkfs. test announcement jashdk jksadnkfs. test announcement <strong>jashdk</strong> jksadnkfs. </p><p><br></p><p>test announcement jashdk jksadnkfs. test announcement jashdk jksadnkfs. test announcement jashdk jksadnkfs. test announcement jashdk jksadnkfs. test announcement jashdk.</p><p><br></p><p><br></p><p><br></p>', 1, '2026-01-23', 'test, testtag', 1, 0, '2026-01-23 15:34:28', NULL, '2026-01-23 15:34:28', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `app_settings`
--

CREATE TABLE `app_settings` (
  `id` int(11) NOT NULL,
  `site_name` varchar(255) NOT NULL,
  `site_desc` varchar(255) NOT NULL,
  `admin_url` varchar(255) NOT NULL,
  `favicon` varchar(255) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `bin_tax` varchar(60) DEFAULT NULL,
  `company_name` varchar(255) NOT NULL,
  `company_address` varchar(255) NOT NULL,
  `zip_code` varchar(20) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `license_auth` varchar(255) DEFAULT NULL,
  `license_hash` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `fax` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `smtp_host` varchar(150) DEFAULT NULL,
  `smtp_port` varchar(8) DEFAULT NULL,
  `smtp_username` varchar(255) DEFAULT NULL,
  `smtp_authkey` varchar(255) DEFAULT NULL,
  `captcha_site_key` varchar(100) NOT NULL,
  `captcha_secret_key` varchar(100) NOT NULL,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `app_settings`
--

INSERT INTO `app_settings` (`id`, `site_name`, `site_desc`, `admin_url`, `favicon`, `logo`, `bin_tax`, `company_name`, `company_address`, `zip_code`, `city`, `state`, `country`, `license_auth`, `license_hash`, `email`, `fax`, `phone`, `smtp_host`, `smtp_port`, `smtp_username`, `smtp_authkey`, `captcha_site_key`, `captcha_secret_key`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`) VALUES
(1, 'WHMAZ', 'Web Host Manager A to Z solutions. Lightweight Domain Hosting Management System', '', 'favicon_ea001f05de00bfe79ffb1864114d868e.ico', 'logo_c741480362e311d793820fd49d7b6e33.png', '', 'WHMAZ', 'Your Company Address', '00000', '', '', '', NULL, NULL, 'admin@example.com', '', '', NULL, '587', NULL, NULL, '', '', NULL, NULL, '2026-03-17 14:57:33', 1);

-- --------------------------------------------------------

--
-- Table structure for table `billing_cycle`
--

CREATE TABLE `billing_cycle` (
  `id` int(11) NOT NULL,
  `cycle_key` varchar(10) NOT NULL,
  `cycle_name` varchar(60) NOT NULL,
  `cycle_days` int(11) NOT NULL,
  `sl` int(11) NOT NULL,
  `inserted_on` datetime NOT NULL DEFAULT current_timestamp(),
  `inserted_by` bigint(20) DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `billing_cycle`
--

INSERT INTO `billing_cycle` (`id`, `cycle_key`, `cycle_name`, `cycle_days`, `sl`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `status`) VALUES
(1, 'MONTHLY', 'Monthly', 30, 1, '2022-10-09 09:59:22', NULL, NULL, NULL, 1),
(2, 'QUARTERLY', 'Quarterly', 91, 2, '2022-10-09 10:00:01', NULL, NULL, NULL, 1),
(3, 'HALF_YEAR', 'Half-Yearly', 182, 3, '2022-10-09 10:00:13', NULL, NULL, NULL, 1),
(4, 'YEARLY', 'Yearly', 365, 4, '2022-10-09 10:00:19', NULL, NULL, NULL, 1),
(5, 'ONE_TIME', 'One Time', 0, 5, '2022-10-09 10:00:24', NULL, NULL, NULL, 1),
(6, 'FREE', 'Free', 0, 6, '2022-10-09 10:00:28', NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` bigint(20) NOT NULL,
  `parent_company_id` bigint(20) NOT NULL DEFAULT 0,
  `is_reseller` tinyint(4) NOT NULL DEFAULT 0,
  `name` varchar(150) NOT NULL,
  `mobile` varchar(16) DEFAULT NULL,
  `phone` varchar(16) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `zip_code` varchar(10) DEFAULT NULL,
  `first_name` varchar(150) DEFAULT NULL,
  `last_name` varchar(150) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `registrar_customer_id` varchar(50) DEFAULT NULL,
  `kam_id` bigint(20) NOT NULL,
  `kam_name` varchar(200) NOT NULL,
  `lead_id` bigint(20) DEFAULT NULL,
  `opportunity_id` bigint(20) DEFAULT NULL,
  `quotation_id` bigint(20) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `name`, `mobile`, `phone`, `email`, `address`, `city`, `state`, `zip_code`, `first_name`, `last_name`, `country`, `registrar_customer_id`, `kam_id`, `kam_name`, `lead_id`, `opportunity_id`, `quotation_id`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(1, 'Demo Client', '+94788888888', '+94788888888', 'client@whmaz.com', '201 Shanti Villa, Silk house Street', 'KANDY', 'KANDY', '20000', 'A. L', 'Perera', 'Sri Lanka', 'NC_4c37fc5fb1d2c4873ce6b69c6ff87772', 0, '', NULL, NULL, NULL, 1, '2014-02-21 15:11:42', 1, '2026-03-18 14:57:40', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_domain_child_ns`
-- Customer-managed child / private nameservers (glue records) for a domain,
-- e.g. ns1.example.com -> 1.2.3.4. Mirrored at the registrar on add/delete.
--

CREATE TABLE `order_domain_child_ns` (
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

-- --------------------------------------------------------

--
-- Table structure for table `domain_cancellation_requests`
-- Customer-submitted domain cancellation requests, processed by an admin.
-- status: 0=pending, 1=processed (domain cancelled), 2=dismissed.
--

CREATE TABLE `domain_cancellation_requests` (
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

-- --------------------------------------------------------

--
-- Table structure for table `reseller_profiles`
-- Per-reseller config: discount applied to the reseller's orders, tracked
-- credit balance, and whether the reseller may use the third-party API.
--

CREATE TABLE `reseller_profiles` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `discount_type` varchar(20) NOT NULL DEFAULT 'percent',
  `discount_value` decimal(12,2) NOT NULL DEFAULT 0.00,
  `credit_balance` decimal(14,2) NOT NULL DEFAULT 0.00,
  `currency_id` int(11) DEFAULT NULL,
  `allow_api` tinyint(4) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_reseller_company` (`company_id`),
  KEY `idx_reseller_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `api_keys`
-- Third-party REST API credentials. key_id is the public identifier;
-- secret is stored only as password_hash() in secret_hash. scopes is a JSON
-- array of granted scope strings. status: 1=active, 2=revoked, 0=deleted.
--

CREATE TABLE `api_keys` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `company_id` bigint(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `key_id` varchar(48) NOT NULL,
  `secret_hash` varchar(255) NOT NULL,
  `secret_preview` varchar(16) DEFAULT NULL,
  `scopes` text DEFAULT NULL,
  `ip_whitelist` text DEFAULT NULL,
  `rate_limit` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `expires_at` datetime DEFAULT NULL,
  `last_used_at` datetime DEFAULT NULL,
  `last_used_ip` varchar(45) DEFAULT NULL,
  `request_count` bigint(20) NOT NULL DEFAULT 0,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_api_key_id` (`key_id`),
  KEY `idx_api_keys_company` (`company_id`),
  KEY `idx_api_keys_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `api_request_logs`
-- Per-request audit trail for the third-party API; also the window source
-- for per-key rate limiting.
--

CREATE TABLE `api_request_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `api_key_id` bigint(20) NOT NULL,
  `company_id` bigint(20) NOT NULL,
  `method` varchar(10) DEFAULT NULL,
  `endpoint` varchar(255) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `status_code` int(11) DEFAULT NULL,
  `response_time_ms` int(11) DEFAULT NULL,
  `created_on` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_api_logs_key` (`api_key_id`),
  KEY `idx_api_logs_company` (`company_id`),
  KEY `idx_api_logs_created` (`created_on`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` int(11) NOT NULL,
  `country_code` char(2) NOT NULL,
  `country_name` varchar(100) NOT NULL,
  `dial_code` varchar(6) NOT NULL,
  `currency_symbol` varchar(10) DEFAULT NULL,
  `currency` varchar(3) DEFAULT NULL,
  `iso` char(3) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `country_code`, `country_name`, `dial_code`, `currency_symbol`, `currency`, `iso`, `status`) VALUES
(1, 'AF', 'Afghanistan', '93', '؋', 'AFN', 'AFG', 1),
(2, 'AX', 'Åland Islands', '358', '€', 'EUR', 'ALA', 1),
(3, 'AL', 'Albania', '355', 'Lek', 'ALL', 'ALB', 1),
(4, 'DZ', 'Algeria', '213', 'دج', 'DZD', 'DZA', 1),
(5, 'AS', 'American Samoa', '1', '$', 'USD', 'ASM', 1),
(6, 'AD', 'Andorra', '376', '€', 'EUR', 'AND', 1),
(7, 'AO', 'Angola', '244', 'Kz', 'AOA', 'AGO', 1),
(8, 'AI', 'Anguilla', '1', '$', 'XCD', 'AIA', 1),
(9, 'AQ', 'Antarctica', '672', '$', 'AAD', 'ATA', 1),
(10, 'AG', 'Antigua and Barbuda', '1', '$', 'XCD', 'ATG', 1),
(11, 'AR', 'Argentina', '54', '$', 'ARS', 'ARG', 1),
(12, 'AM', 'Armenia', '374', '֏', 'AMD', 'ARM', 1),
(13, 'AW', 'Aruba', '297', 'ƒ', 'AWG', 'ABW', 1),
(14, 'AU', 'Australia', '61', '$', 'AUD', 'AUS', 1),
(15, 'AT', 'Austria', '43', '€', 'EUR', 'AUT', 1),
(16, 'AZ', 'Azerbaijan', '994', 'm', 'AZN', 'AZE', 1),
(17, 'BS', 'Bahamas', '1', 'B$', 'BSD', 'BHS', 1),
(18, 'BH', 'Bahrain', '973', '.د.ب', 'BHD', 'BHR', 1),
(19, 'BD', 'Bangladesh', '880', '৳', 'BDT', 'BGD', 1),
(20, 'BB', 'Barbados', '1', 'Bds$', 'BBD', 'BRB', 1),
(21, 'BY', 'Belarus', '375', 'Br', 'BYN', 'BLR', 1),
(22, 'BE', 'Belgium', '32', '€', 'EUR', 'BEL', 1),
(23, 'BZ', 'Belize', '501', '$', 'BZD', 'BLZ', 1),
(24, 'BJ', 'Benin', '229', 'CFA', 'XOF', 'BEN', 1),
(25, 'BM', 'Bermuda', '1', '$', 'BMD', 'BMU', 1),
(26, 'BT', 'Bhutan', '975', 'Nu.', 'BTN', 'BTN', 1),
(27, 'BO', 'Bolivia (Plurinational State of)', '591', 'Bs.', 'BOB', 'BOL', 1),
(28, 'BA', 'Bosnia and Herzegovina', '387', 'KM', 'BAM', 'BIH', 1),
(29, 'BW', 'Botswana', '267', 'P', 'BWP', 'BWA', 1),
(30, 'BV', 'Bouvet Island', '55', 'kr', 'NOK', 'BVT', 1),
(31, 'BR', 'Brazil', '55', 'R$', 'BRL', 'BRA', 1),
(32, 'IO', 'British Indian Ocean Territory', '246', '$', 'USD', 'IOT', 1),
(33, 'BN', 'Brunei Darussalam', '673', 'B$', 'BND', 'BRN', 1),
(34, 'BG', 'Bulgaria', '359', 'Лв.', 'BGN', 'BGR', 1),
(35, 'BF', 'Burkina Faso', '226', 'CFA', 'XOF', 'BFA', 1),
(36, 'BI', 'Burundi', '257', 'FBu', 'BIF', 'BDI', 1),
(37, 'CV', 'Cabo Verde', '238', '$', 'CVE', 'CPV', 1),
(38, 'KH', 'Cambodia', '855', 'KHR', 'KHR', 'KHM', 1),
(39, 'CM', 'Cameroon', '237', 'FCFA', 'XAF', 'CMR', 1),
(40, 'CA', 'Canada', '1', '$', 'CAD', 'CAN', 1),
(41, 'BQ', 'Caribbean Netherlands', '599', '$', 'USD', 'BES', 1),
(42, 'KY', 'Cayman Islands', '1', '$', 'KYD', 'CYM', 1),
(43, 'CF', 'Central African Republic', '236', 'FCFA', 'XAF', 'CAF', 1),
(44, 'TD', 'Chad', '235', 'FCFA', 'XAF', 'TCD', 1),
(45, 'CL', 'Chile', '56', '$', 'CLP', 'CHL', 1),
(46, 'CN', 'China', '86', '¥', 'CNY', 'CHN', 1),
(47, 'CX', 'Christmas Island', '61', '$', 'AUD', 'CXR', 1),
(48, 'CC', 'Cocos (Keeling) Islands', '61', '$', 'AUD', 'CCK', 1),
(49, 'CO', 'Colombia', '57', '$', 'COP', 'COL', 1),
(50, 'KM', 'Comoros', '269', 'CF', 'KMF', 'COM', 1),
(51, 'CG', 'Congo', '242', 'FC', 'XAF', 'COG', 1),
(52, 'CD', 'Congo, Democratic Republic of the', '243', 'FC', 'CDF', 'COD', 1),
(53, 'CK', 'Cook Islands', '682', '$', 'NZD', 'COK', 1),
(54, 'CR', 'Costa Rica', '506', '₡', 'CRC', 'CRI', 1),
(55, 'HR', 'Croatia', '385', 'kn', 'HRK', 'HRV', 1),
(56, 'CU', 'Cuba', '53', '$', 'CUP', 'CUB', 1),
(57, 'CW', 'Curaçao', '599', 'ƒ', 'ANG', 'CUW', 1),
(58, 'CY', 'Cyprus', '357', '€', 'EUR', 'CYP', 1),
(59, 'CZ', 'Czech Republic', '420', 'Kč', 'CZK', 'CZE', 1),
(60, 'CI', 'Côte d\'Ivoire', '225', 'CFA', 'XOF', 'CIV', 1),
(61, 'DK', 'Denmark', '45', 'Kr.', 'DKK', 'DNK', 1),
(62, 'DJ', 'Djibouti', '253', 'Fdj', 'DJF', 'DJI', 1),
(63, 'DM', 'Dominica', '1', '$', 'XCD', 'DMA', 1),
(64, 'DO', 'Dominican Republic', '1', '$', 'DOP', 'DOM', 1),
(65, 'EC', 'Ecuador', '593', '$', 'USD', 'ECU', 1),
(66, 'EG', 'Egypt', '20', 'ج.م', 'EGP', 'EGY', 1),
(67, 'SV', 'El Salvador', '503', '$', 'USD', 'SLV', 1),
(68, 'GQ', 'Equatorial Guinea', '240', 'FCFA', 'XAF', 'GNQ', 1),
(69, 'ER', 'Eritrea', '291', 'Nfk', 'ERN', 'ERI', 1),
(70, 'EE', 'Estonia', '372', '€', 'EUR', 'EST', 1),
(71, 'SZ', 'Eswatini (Swaziland)', '268', 'E', 'SZL', 'SWZ', 1),
(72, 'ET', 'Ethiopia', '251', 'Nkf', 'ETB', 'ETH', 1),
(73, 'FK', 'Falkland Islands (Malvinas)', '500', '£', 'FKP', 'FLK', 1),
(74, 'FO', 'Faroe Islands', '298', 'Kr.', 'DKK', 'FRO', 1),
(75, 'FJ', 'Fiji', '679', 'FJ$', 'FJD', 'FJI', 1),
(76, 'FI', 'Finland', '358', '€', 'EUR', 'FIN', 1),
(77, 'FR', 'France', '33', '€', 'EUR', 'FRA', 1),
(78, 'GF', 'French Guiana', '594', '€', 'EUR', 'GUF', 1),
(79, 'PF', 'French Polynesia', '689', '₣', 'XPF', 'PYF', 1),
(80, 'TF', 'French Southern Territories', '262', '€', 'EUR', 'ATF', 1),
(81, 'GA', 'Gabon', '241', 'FCFA', 'XAF', 'GAB', 1),
(82, 'GM', 'Gambia', '220', 'D', 'GMD', 'GMB', 1),
(83, 'GE', 'Georgia', '995', 'ლ', 'GEL', 'GEO', 1),
(84, 'DE', 'Germany', '49', '€', 'EUR', 'DEU', 1),
(85, 'GH', 'Ghana', '233', 'GH₵', 'GHS', 'GHA', 1),
(86, 'GI', 'Gibraltar', '350', '£', 'GIP', 'GIB', 1),
(87, 'GR', 'Greece', '30', '€', 'EUR', 'GRC', 1),
(88, 'GL', 'Greenland', '299', 'Kr.', 'DKK', 'GRL', 1),
(89, 'GD', 'Grenada', '1', '$', 'XCD', 'GRD', 1),
(90, 'GP', 'Guadeloupe', '590', '€', 'EUR', 'GLP', 1),
(91, 'GU', 'Guam', '1', '$', 'USD', 'GUM', 1),
(92, 'GT', 'Guatemala', '502', 'Q', 'GTQ', 'GTM', 1),
(93, 'GG', 'Guernsey', '44', '£', 'GBP', 'GGY', 1),
(94, 'GN', 'Guinea', '224', 'FG', 'GNF', 'GIN', 1),
(95, 'GW', 'Guinea-Bissau', '245', 'CFA', 'XOF', 'GNB', 1),
(96, 'GY', 'Guyana', '592', '$', 'GYD', 'GUY', 1),
(97, 'HT', 'Haiti', '509', 'G', 'HTG', 'HTI', 1),
(98, 'HM', 'Heard Island and Mcdonald Islands', '61', '$', 'AUD', 'HMD', 1),
(99, 'HN', 'Honduras', '504', 'L', 'HNL', 'HND', 1),
(100, 'HK', 'Hong Kong', '852', '$', 'HKD', 'HKG', 1),
(101, 'HU', 'Hungary', '36', 'Ft', 'HUF', 'HUN', 1),
(102, 'IS', 'Iceland', '354', 'kr', 'ISK', 'ISL', 1),
(103, 'IN', 'India', '91', '₹', 'INR', 'IND', 1),
(104, 'ID', 'Indonesia', '62', 'Rp', 'IDR', 'IDN', 1),
(105, 'IR', 'Iran', '98', '﷼', 'IRR', 'IRN', 1),
(106, 'IQ', 'Iraq', '964', 'د.ع', 'IQD', 'IRQ', 1),
(107, 'IE', 'Ireland', '353', '€', 'EUR', 'IRL', 1),
(108, 'IM', 'Isle of Man', '44', '£', 'GBP', 'IMN', 1),
(109, 'IL', 'Israel', '972', '₪', 'ILS', 'ISR', 1),
(110, 'IT', 'Italy', '39', '€', 'EUR', 'ITA', 1),
(111, 'JM', 'Jamaica', '1', 'J$', 'JMD', 'JAM', 1),
(112, 'JP', 'Japan', '81', '¥', 'JPY', 'JPN', 1),
(113, 'JE', 'Jersey', '44', '£', 'GBP', 'JEY', 1),
(114, 'JO', 'Jordan', '962', 'ا.د', 'JOD', 'JOR', 1),
(115, 'KZ', 'Kazakhstan', '7', 'лв', 'KZT', 'KAZ', 1),
(116, 'KE', 'Kenya', '254', 'KSh', 'KES', 'KEN', 1),
(117, 'KI', 'Kiribati', '686', '$', 'AUD', 'KIR', 1),
(118, 'KP', 'Korea, North', '850', '₩', 'KPW', 'PRK', 1),
(119, 'KR', 'Korea, South', '82', '₩', 'KRW', 'KOR', 1),
(120, 'XK', 'Kosovo', '383', '€', 'EUR', 'XKX', 1),
(121, 'KW', 'Kuwait', '965', 'ك.د', 'KWD', 'KWT', 1),
(122, 'KG', 'Kyrgyzstan', '996', 'лв', 'KGS', 'KGZ', 1),
(123, 'LA', 'Lao People\'s Democratic Republic', '856', '₭', 'LAK', 'LAO', 1),
(124, 'LV', 'Latvia', '371', '€', 'EUR', 'LVA', 1),
(125, 'LB', 'Lebanon', '961', '£', 'LBP', 'LBN', 1),
(126, 'LS', 'Lesotho', '266', 'L', 'LSL', 'LSO', 1),
(127, 'LR', 'Liberia', '231', '$', 'LRD', 'LBR', 1),
(128, 'LY', 'Libya', '218', 'د.ل', 'LYD', 'LBY', 1),
(129, 'LI', 'Liechtenstein', '423', 'CHf', 'CHF', 'LIE', 1),
(130, 'LT', 'Lithuania', '370', '€', 'EUR', 'LTU', 1),
(131, 'LU', 'Luxembourg', '352', '€', 'EUR', 'LUX', 1),
(132, 'MO', 'Macao', '853', '$', 'MOP', 'MAC', 1),
(133, 'MK', 'Macedonia North', '389', 'ден', 'MKD', 'MKD', 1),
(134, 'MG', 'Madagascar', '261', 'Ar', 'MGA', 'MDG', 1),
(135, 'MW', 'Malawi', '265', 'MK', 'MWK', 'MWI', 1),
(136, 'MY', 'Malaysia', '60', 'RM', 'MYR', 'MYS', 1),
(137, 'MV', 'Maldives', '960', 'Rf', 'MVR', 'MDV', 1),
(138, 'ML', 'Mali', '223', 'CFA', 'XOF', 'MLI', 1),
(139, 'MT', 'Malta', '356', '€', 'EUR', 'MLT', 1),
(140, 'MH', 'Marshall Islands', '692', '$', 'USD', 'MHL', 1),
(141, 'MQ', 'Martinique', '596', '€', 'EUR', 'MTQ', 1),
(142, 'MR', 'Mauritania', '222', 'MRU', 'MRO', 'MRT', 1),
(143, 'MU', 'Mauritius', '230', '₨', 'MUR', 'MUS', 1),
(144, 'YT', 'Mayotte', '262', '€', 'EUR', 'MYT', 1),
(145, 'MX', 'Mexico', '52', '$', 'MXN', 'MEX', 1),
(146, 'FM', 'Micronesia', '691', '$', 'USD', 'FSM', 1),
(147, 'MD', 'Moldova', '373', 'L', 'MDL', 'MDA', 1),
(148, 'MC', 'Monaco', '377', '€', 'EUR', 'MCO', 1),
(149, 'MN', 'Mongolia', '976', '₮', 'MNT', 'MNG', 1),
(150, 'ME', 'Montenegro', '382', '€', 'EUR', 'MNE', 1),
(151, 'MS', 'Montserrat', '1', '$', 'XCD', 'MSR', 1),
(152, 'MA', 'Morocco', '212', 'DH', 'MAD', 'MAR', 1),
(153, 'MZ', 'Mozambique', '258', 'MT', 'MZN', 'MOZ', 1),
(154, 'MM', 'Myanmar (Burma)', '95', 'K', 'MMK', 'MMR', 1),
(155, 'NA', 'Namibia', '264', '$', 'NAD', 'NAM', 1),
(156, 'NR', 'Nauru', '674', '$', 'AUD', 'NRU', 1),
(157, 'NP', 'Nepal', '977', '₨', 'NPR', 'NPL', 1),
(158, 'NL', 'Netherlands', '31', '€', 'EUR', 'NLD', 1),
(159, 'AN', 'Netherlands Antilles', '599', 'NAf', 'ANG', 'ANT', 1),
(160, 'NC', 'New Caledonia', '687', '₣', 'XPF', 'NCL', 1),
(161, 'NZ', 'New Zealand', '64', '$', 'NZD', 'NZL', 1),
(162, 'NI', 'Nicaragua', '505', 'C$', 'NIO', 'NIC', 1),
(163, 'NE', 'Niger', '227', 'CFA', 'XOF', 'NER', 1),
(164, 'NG', 'Nigeria', '234', '₦', 'NGN', 'NGA', 1),
(165, 'NU', 'Niue', '683', '$', 'NZD', 'NIU', 1),
(166, 'NF', 'Norfolk Island', '672', '$', 'AUD', 'NFK', 1),
(167, 'MP', 'Northern Mariana Islands', '1', '$', 'USD', 'MNP', 1),
(168, 'NO', 'Norway', '47', 'kr', 'NOK', 'NOR', 1),
(169, 'OM', 'Oman', '968', '.ع.ر', 'OMR', 'OMN', 1),
(170, 'PK', 'Pakistan', '92', '₨', 'PKR', 'PAK', 1),
(171, 'PW', 'Palau', '680', '$', 'USD', 'PLW', 1),
(172, 'PS', 'Palestine', '970', '₪', 'ILS', 'PSE', 1),
(173, 'PA', 'Panama', '507', 'B/.', 'PAB', 'PAN', 1),
(174, 'PG', 'Papua New Guinea', '675', 'K', 'PGK', 'PNG', 1),
(175, 'PY', 'Paraguay', '595', '₲', 'PYG', 'PRY', 1),
(176, 'PE', 'Peru', '51', 'S/.', 'PEN', 'PER', 1),
(177, 'PH', 'Philippines', '63', '₱', 'PHP', 'PHL', 1),
(178, 'PN', 'Pitcairn Islands', '64', '$', 'NZD', 'PCN', 1),
(179, 'PL', 'Poland', '48', 'zł', 'PLN', 'POL', 1),
(180, 'PT', 'Portugal', '351', '€', 'EUR', 'PRT', 1),
(181, 'PR', 'Puerto Rico', '1', '$', 'USD', 'PRI', 1),
(182, 'QA', 'Qatar', '974', 'ق.ر', 'QAR', 'QAT', 1),
(183, 'RE', 'Reunion', '262', '€', 'EUR', 'REU', 1),
(184, 'RO', 'Romania', '40', 'lei', 'RON', 'ROM', 1),
(185, 'RU', 'Russian Federation', '7', '₽', 'RUB', 'RUS', 1),
(186, 'RW', 'Rwanda', '250', 'FRw', 'RWF', 'RWA', 1),
(187, 'BL', 'Saint Barthelemy', '590', '€', 'EUR', 'BLM', 1),
(188, 'SH', 'Saint Helena', '290', '£', 'SHP', 'SHN', 1),
(189, 'KN', 'Saint Kitts and Nevis', '1', '$', 'XCD', 'KNA', 1),
(190, 'LC', 'Saint Lucia', '1', '$', 'XCD', 'LCA', 1),
(191, 'MF', 'Saint Martin', '590', '€', 'EUR', 'MAF', 1),
(192, 'PM', 'Saint Pierre and Miquelon', '508', '€', 'EUR', 'SPM', 1),
(193, 'VC', 'Saint Vincent and the Grenadines', '1', '$', 'XCD', 'VCT', 1),
(194, 'WS', 'Samoa', '685', 'SAT', 'WST', 'WSM', 1),
(195, 'SM', 'San Marino', '378', '€', 'EUR', 'SMR', 1),
(196, 'ST', 'Sao Tome and Principe', '239', 'Db', 'STD', 'STP', 1),
(197, 'SA', 'Saudi Arabia', '966', '﷼', 'SAR', 'SAU', 1),
(198, 'SN', 'Senegal', '221', 'CFA', 'XOF', 'SEN', 1),
(199, 'RS', 'Serbia', '381', 'din', 'RSD', 'SRB', 1),
(200, 'CS', 'Serbia and Montenegro', '381', 'din', 'RSD', 'SCG', 1),
(201, 'SC', 'Seychelles', '248', 'SRe', 'SCR', 'SYC', 1),
(202, 'SL', 'Sierra Leone', '232', 'Le', 'SLL', 'SLE', 1),
(203, 'SG', 'Singapore', '65', '$', 'SGD', 'SGP', 1),
(204, 'SX', 'Sint Maarten', '1', 'ƒ', 'ANG', 'SXM', 1),
(205, 'SK', 'Slovakia', '421', '€', 'EUR', 'SVK', 1),
(206, 'SI', 'Slovenia', '386', '€', 'EUR', 'SVN', 1),
(207, 'SB', 'Solomon Islands', '677', 'Si$', 'SBD', 'SLB', 1),
(208, 'SO', 'Somalia', '252', 'Sh.so.', 'SOS', 'SOM', 1),
(209, 'ZA', 'South Africa', '27', 'R', 'ZAR', 'ZAF', 1),
(210, 'GS', 'South Georgia and the South Sandwich Islands', '500', '£', 'GBP', 'SGS', 1),
(211, 'SS', 'South Sudan', '211', '£', 'SSP', 'SSD', 1),
(212, 'ES', 'Spain', '34', '€', 'EUR', 'ESP', 1),
(213, 'LK', 'Sri Lanka', '94', 'Rs', 'LKR', 'LKA', 1),
(214, 'SD', 'Sudan', '249', '.س.ج', 'SDG', 'SDN', 1),
(215, 'SR', 'Suriname', '597', '$', 'SRD', 'SUR', 1),
(216, 'SJ', 'Svalbard and Jan Mayen', '47', 'kr', 'NOK', 'SJM', 1),
(217, 'SE', 'Sweden', '46', 'kr', 'SEK', 'SWE', 1),
(218, 'CH', 'Switzerland', '41', 'CHf', 'CHF', 'CHE', 1),
(219, 'SY', 'Syria', '963', 'LS', 'SYP', 'SYR', 1),
(220, 'TW', 'Taiwan', '886', '$', 'TWD', 'TWN', 1),
(221, 'TJ', 'Tajikistan', '992', 'SM', 'TJS', 'TJK', 1),
(222, 'TZ', 'Tanzania', '255', 'TSh', 'TZS', 'TZA', 1),
(223, 'TH', 'Thailand', '66', '฿', 'THB', 'THA', 1),
(224, 'TL', 'Timor-Leste', '670', '$', 'USD', 'TLS', 1),
(225, 'TG', 'Togo', '228', 'CFA', 'XOF', 'TGO', 1),
(226, 'TK', 'Tokelau', '690', '$', 'NZD', 'TKL', 1),
(227, 'TO', 'Tonga', '676', '$', 'TOP', 'TON', 1),
(228, 'TT', 'Trinidad and Tobago', '1', '$', 'TTD', 'TTO', 1),
(229, 'TN', 'Tunisia', '216', 'ت.د', 'TND', 'TUN', 1),
(230, 'TR', 'Turkey (Türkiye)', '90', '₺', 'TRY', 'TUR', 1),
(231, 'TM', 'Turkmenistan', '993', 'T', 'TMT', 'TKM', 1),
(232, 'TC', 'Turks and Caicos Islands', '1', '$', 'USD', 'TCA', 1),
(233, 'TV', 'Tuvalu', '688', '$', 'AUD', 'TUV', 1),
(234, 'UM', 'U.S. Outlying Islands', '1', '$', 'USD', 'UMI', 1),
(235, 'UG', 'Uganda', '256', 'USh', 'UGX', 'UGA', 1),
(236, 'UA', 'Ukraine', '380', '₴', 'UAH', 'UKR', 1),
(237, 'AE', 'United Arab Emirates', '971', 'إ.د', 'AED', 'ARE', 1),
(238, 'GB', 'United Kingdom', '44', '£', 'GBP', 'GBR', 1),
(239, 'US', 'United States', '1', '$', 'USD', 'USA', 1),
(240, 'UY', 'Uruguay', '598', '$', 'UYU', 'URY', 1),
(241, 'UZ', 'Uzbekistan', '998', 'лв', 'UZS', 'UZB', 1),
(242, 'VU', 'Vanuatu', '678', 'VT', 'VUV', 'VUT', 1),
(243, 'VA', 'Vatican City Holy See', '39', '€', 'EUR', 'VAT', 1),
(244, 'VE', 'Venezuela', '58', 'Bs', 'VEF', 'VEN', 1),
(245, 'VN', 'Vietnam', '84', '₫', 'VND', 'VNM', 1),
(246, 'VG', 'Virgin Islands, British', '1', '$', 'USD', 'VGB', 1),
(247, 'VI', 'Virgin Islands, U.S', '1', '$', 'USD', 'VIR', 1),
(248, 'WF', 'Wallis and Futuna', '681', '₣', 'XPF', 'WLF', 1),
(249, 'EH', 'Western Sahara', '212', 'MAD', 'MAD', 'ESH', 1),
(250, 'YE', 'Yemen', '967', '﷼', 'YER', 'YEM', 1),
(251, 'ZM', 'Zambia', '260', 'ZK', 'ZMW', 'ZMB', 1),
(252, 'ZW', 'Zimbabwe', '263', '$', 'ZWL', 'ZWE', 1);

-- --------------------------------------------------------

--
-- Table structure for table `cron_jobs`
--

CREATE TABLE `cron_jobs` (
  `id` bigint(20) NOT NULL,
  `job_name` varchar(150) DEFAULT NULL,
  `job_type` varchar(100) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `items_processed` int(11) NOT NULL DEFAULT 0,
  `executed_on` datetime DEFAULT NULL,
  `execute_dt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cron_jobs`
--

INSERT INTO `cron_jobs` (`id`, `job_name`, `job_type`, `status`, `details`, `items_processed`, `executed_on`, `execute_dt`) VALUES
(1, 'Invoicegenerator', NULL, NULL, NULL, 0, NULL, '2024-11-05 22:59:30'),
(2, 'Invoicegenerator', NULL, NULL, NULL, 0, NULL, '2024-11-05 23:09:21'),
(3, 'Invoicegenerator', NULL, NULL, NULL, 0, NULL, '2024-11-05 23:10:39'),
(4, 'Invoicegenerator', NULL, NULL, NULL, 0, NULL, '2024-11-16 21:22:49'),
(5, 'Invoicegenerator', NULL, NULL, NULL, 0, NULL, '2024-11-16 21:29:47'),
(6, 'Invoicegenerator', NULL, NULL, NULL, 0, NULL, '2024-11-16 22:20:04'),
(7, 'Invoicegenerator', NULL, NULL, NULL, 0, NULL, '2024-11-16 22:27:06'),
(8, 'Invoicegenerator', NULL, NULL, NULL, 0, NULL, '2024-11-16 22:30:03'),
(9, 'Invoicegenerator', NULL, NULL, NULL, 0, NULL, '2024-11-16 22:30:40'),
(10, 'Invoicegenerator', NULL, NULL, NULL, 0, NULL, '2024-11-16 22:31:45'),
(11, 'Invoicegenerator', NULL, NULL, NULL, 0, NULL, '2024-11-16 22:35:33'),
(12, 'Invoicegenerator', NULL, NULL, NULL, 0, NULL, '2024-11-16 22:38:05'),
(13, 'Invoicegenerator', NULL, NULL, NULL, 0, NULL, '2024-11-16 22:42:10'),
(14, 'Invoicegenerator', NULL, NULL, NULL, 0, NULL, '2024-11-16 22:44:23'),
(15, 'Invoicegenerator', NULL, NULL, NULL, 0, NULL, '2024-11-16 22:47:34'),
(16, 'Invoicegenerator', NULL, NULL, NULL, 0, NULL, '2024-11-16 22:49:43'),
(17, 'Invoicegenerator', NULL, NULL, NULL, 0, NULL, '2024-11-16 22:51:26'),
(18, 'Invoicegenerator', NULL, NULL, NULL, 0, NULL, '2024-11-16 23:18:32'),
(19, 'Invoicegenerator', NULL, NULL, NULL, 0, NULL, '2024-11-16 23:20:28'),
(20, 'Invoicegenerator', NULL, NULL, NULL, 0, NULL, '2024-11-16 23:20:47'),
(21, 'Invoicegenerator', NULL, NULL, NULL, 0, NULL, '2024-11-16 23:21:28'),
(22, 'Invoicegenerator', NULL, NULL, NULL, 0, NULL, '2024-11-21 21:12:22'),
(23, 'Invoicegenerator', NULL, NULL, NULL, 0, NULL, '2024-11-21 21:17:03');

-- --------------------------------------------------------

--
-- Table structure for table `cron_schedules`
--

CREATE TABLE `cron_schedules` (
  `id` int(11) NOT NULL,
  `job_name` varchar(100) NOT NULL,
  `job_description` varchar(255) DEFAULT NULL,
  `job_command` varchar(500) NOT NULL,
  `schedule_minute` varchar(20) NOT NULL DEFAULT '*',
  `schedule_hour` varchar(20) NOT NULL DEFAULT '*',
  `schedule_day` varchar(20) NOT NULL DEFAULT '*',
  `schedule_month` varchar(20) NOT NULL DEFAULT '*',
  `schedule_weekday` varchar(20) NOT NULL DEFAULT '*',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_run` datetime DEFAULT NULL,
  `created_on` datetime DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cron_schedules`
--

INSERT INTO `cron_schedules` (`id`, `job_name`, `job_description`, `job_command`, `schedule_minute`, `schedule_hour`, `schedule_day`, `schedule_month`, `schedule_weekday`, `is_active`, `last_run`, `created_on`, `updated_on`) VALUES
(1, 'renewal_invoices', 'Generate renewal invoices for expiring services/domains', '/cronjobs/run', '0', '6', '*', '*', '*', 1, NULL, '2026-02-11 23:59:44', '2026-02-11 23:59:44');

-- --------------------------------------------------------

--
-- Table structure for table `currencies`
--

CREATE TABLE `currencies` (
  `id` int(11) NOT NULL,
  `symbol` varchar(6) NOT NULL,
  `code` varchar(6) NOT NULL,
  `rate` double NOT NULL DEFAULT 1,
  `format` tinyint(4) NOT NULL DEFAULT 1,
  `is_default` tinyint(4) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `currencies`
--

INSERT INTO `currencies` (`id`, `symbol`, `code`, `rate`, `format`, `is_default`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(1, '$', 'USD', 1, 1, 1, 1, NULL, NULL, '2020-08-21 13:37:30', NULL, NULL, NULL),
(2, 'රු', 'LKR', 312, 1, 0, 1, NULL, NULL, '2026-03-07 06:18:52', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `dom_extensions`
--

CREATE TABLE `dom_extensions` (
  `id` int(11) NOT NULL,
  `dom_register_id` int(11) NOT NULL,
  `extension` varchar(100) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dom_extensions`
--

INSERT INTO `dom_extensions` (`id`, `dom_register_id`, `extension`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(1, 1, '.com', 1, '2020-08-22 07:31:04', NULL, '2024-03-22 15:07:08', NULL, NULL, NULL),
(2, 1, '.net', 1, '2020-09-08 09:35:25', NULL, '2020-09-08 03:35:39', NULL, NULL, NULL),
(3, 1, '.org', 1, '2020-09-08 09:35:25', NULL, '2020-09-08 03:35:42', NULL, NULL, NULL),
(4, 1, '.info', 1, '2020-09-08 09:36:06', NULL, '2020-09-08 03:36:06', NULL, NULL, NULL),
(5, 1, '.online', 1, '2020-08-22 07:31:04', NULL, '2024-03-22 15:06:58', NULL, NULL, NULL),
(6, 1, '.store', 1, '2020-09-08 09:35:25', NULL, '2020-09-08 03:35:39', NULL, NULL, NULL),
(7, 1, '.biz', 1, '2020-09-08 09:35:25', NULL, '2020-09-08 03:35:42', NULL, NULL, NULL),
(8, 1, '.me', 1, '2020-09-08 09:36:06', NULL, '2020-09-08 03:36:06', NULL, NULL, NULL),
(9, 1, '.site', 1, '2020-08-22 07:31:04', NULL, '2024-03-22 15:07:03', NULL, NULL, NULL),
(10, 1, '.tech', 1, '2020-09-08 09:35:25', NULL, '2020-09-08 03:35:39', NULL, NULL, NULL),
(11, 1, '.scot', 1, '2020-09-08 09:35:25', NULL, '2020-09-08 03:35:42', NULL, NULL, NULL),
(12, 1, '.website', 1, '2020-09-08 09:36:06', NULL, '2020-09-08 03:36:06', NULL, NULL, NULL),
(13, 1, '.space', 1, '2020-09-08 09:36:06', NULL, '2020-09-08 03:36:06', NULL, NULL, NULL),
(14, 1, '.live', 1, '2020-09-08 09:36:06', NULL, '2020-09-08 03:36:06', NULL, NULL, NULL),
(15, 1, '.asia', 1, '2020-09-08 09:36:06', NULL, '2020-09-08 03:36:06', NULL, NULL, NULL),
(16, 1, '.xyz', 1, '2020-09-08 09:36:06', NULL, '2020-09-08 03:36:06', NULL, NULL, NULL),
(17, 1, '.uk', 1, '2020-09-08 09:36:06', NULL, '2020-09-08 03:36:06', NULL, NULL, NULL),
(18, 1, '.us', 1, '2020-09-08 09:36:06', NULL, '2020-09-08 03:36:06', NULL, NULL, NULL),
(19, 1, '.club', 1, '2020-09-08 09:36:06', NULL, '2020-09-08 03:36:06', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `dom_pricing`
--

CREATE TABLE `dom_pricing` (
  `id` int(11) NOT NULL,
  `dom_extension_id` int(11) NOT NULL,
  `currency_id` int(11) NOT NULL,
  `reg_period` int(11) NOT NULL DEFAULT 1,
  `price` float NOT NULL,
  `transfer` float NOT NULL,
  `renewal` float NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dom_pricing`
--

INSERT INTO `dom_pricing` (`id`, `dom_extension_id`, `currency_id`, `reg_period`, `price`, `transfer`, `renewal`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(1, 1, 1, 1, 12.99, 12.99, 12.99, 1, '2020-09-05 08:17:52', NULL, '2024-08-10 01:54:45', NULL, NULL, NULL),
(2, 1, 2, 1, 1550, 1550, 1550, 1, '2020-09-05 08:17:52', NULL, '2026-02-09 07:44:46', 1, NULL, NULL),
(3, 2, 1, 1, 13.25, 13.25, 13.25, 1, '2020-09-05 08:17:52', NULL, '2024-08-10 01:54:54', NULL, NULL, NULL),
(4, 2, 2, 1, 1510, 1510, 1510, 1, '2020-09-05 08:17:52', NULL, '2024-08-10 01:54:54', NULL, NULL, NULL),
(5, 3, 1, 1, 13.85, 13.85, 13.85, 1, '2020-09-05 08:17:52', NULL, '2024-08-10 01:54:54', NULL, NULL, NULL),
(6, 3, 2, 1, 1580, 1580, 1580, 1, '2020-09-05 08:17:52', NULL, '2024-08-10 01:54:54', NULL, NULL, NULL),
(7, 4, 1, 1, 20.15, 40.91, 40.91, 1, '2020-09-05 08:17:52', NULL, '2024-08-10 01:54:54', NULL, NULL, NULL),
(8, 4, 2, 1, 2299, 2299, 2299, 1, '2020-09-05 08:17:52', NULL, '2024-08-10 01:54:54', NULL, NULL, NULL),
(9, 5, 1, 1, 40.91, 12.99, 12.99, 1, '2020-09-05 08:17:52', NULL, '2024-08-10 01:54:54', NULL, NULL, NULL),
(10, 5, 2, 1, 4660, 4660, 4660, 1, '2020-09-05 08:17:52', NULL, '2024-08-10 01:54:54', NULL, NULL, NULL),
(11, 6, 1, 1, 66.39, 66.39, 66.39, 1, '2020-09-05 08:17:52', NULL, '2024-08-10 01:54:54', NULL, NULL, NULL),
(12, 6, 2, 1, 7560, 7560, 7560, 1, '2020-09-05 08:17:52', NULL, '2024-08-10 01:54:54', NULL, NULL, NULL),
(13, 7, 1, 1, 19.89, 19.89, 19.89, 1, '2020-09-05 08:17:52', NULL, '2024-08-10 01:54:54', NULL, NULL, NULL),
(14, 7, 2, 1, 2270, 2270, 2270, 1, '2020-09-05 08:17:52', NULL, '2024-08-10 01:54:54', NULL, NULL, NULL),
(15, 8, 1, 1, 36.15, 36.15, 36.15, 1, '2020-09-05 08:17:52', NULL, '2024-08-10 01:54:54', NULL, NULL, NULL),
(16, 8, 2, 1, 4125, 4125, 4125, 1, '2020-09-05 08:17:52', NULL, '2024-08-10 01:54:54', NULL, NULL, NULL),
(17, 16, 2, 1, 1640, 1640, 1640, 1, '2026-01-25 01:01:34', 1, '2026-01-25 07:03:06', 1, NULL, NULL),
(18, 16, 1, 1, 12.95, 12.95, 12.95, 1, '2026-01-25 01:02:09', 1, '2026-01-25 01:02:09', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `dom_registers`
--

CREATE TABLE `dom_registers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `platform` varchar(60) NOT NULL,
  `api_base_url` varchar(255) NOT NULL,
  `domain_check_api` text NOT NULL,
  `suggestion_api` text DEFAULT NULL,
  `domain_reg_api` text DEFAULT NULL,
  `ns_update_api` text DEFAULT NULL,
  `contact_details_api` text DEFAULT NULL,
  `contact_update_api` text DEFAULT NULL,
  `whitelisted_ip` varchar(60) DEFAULT NULL COMMENT 'Server IP whitelisted at registrar',
  `auth_userid` varchar(50) NOT NULL,
  `auth_apikey` varchar(100) NOT NULL,
  `is_selected` tinyint(4) NOT NULL DEFAULT 1,
  `def_ns1` varchar(150) DEFAULT NULL,
  `def_ns2` varchar(150) DEFAULT NULL,
  `def_ns3` varchar(150) DEFAULT NULL,
  `def_ns4` varchar(150) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dom_registers`
--

INSERT INTO `dom_registers` (`id`, `name`, `platform`, `api_base_url`, `domain_check_api`, `suggestion_api`, `domain_reg_api`, `ns_update_api`, `contact_details_api`, `contact_update_api`, `whitelisted_ip`, `auth_userid`, `auth_apikey`, `is_selected`, `def_ns1`, `def_ns2`, `def_ns3`, `def_ns4`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`) VALUES
(1, 'Resell.Biz', 'STARGATE', 'https://test.httpapi.com', 'https://test.httpapi.com', 'https://test.httpapi.com', 'https://test.httpapi.com', 'https://test.httpapi.com', 'https://test.httpapi.com', 'https://test.httpapi.com', '', '', '', 0, '', '', '', '', 1, NULL, NULL, '2026-03-07 07:44:10', 1),
(2, 'Namecheap', 'NAMECHEAP', 'https://api.sandbox.namecheap.com/xml.response', 'https://api.sandbox.namecheap.com/xml.response', 'https://api.sandbox.namecheap.com/xml.response', 'https://api.sandbox.namecheap.com/xml.response', 'https://api.sandbox.namecheap.com/xml.response', 'https://api.sandbox.namecheap.com/xml.response', 'https://api.sandbox.namecheap.com/xml.response', '', '', '', 1, '', '', '', '', 1, NULL, NULL, '2026-03-07 08:36:20', 1);

-- --------------------------------------------------------

--
-- Table structure for table `dunning_log`
--

CREATE TABLE `dunning_log` (
  `id` bigint(20) NOT NULL,
  `invoice_id` bigint(20) NOT NULL,
  `company_id` bigint(20) NOT NULL,
  `dunning_step` int(11) NOT NULL,
  `action_type` varchar(20) NOT NULL,
  `action_date` datetime NOT NULL,
  `next_action_date` date DEFAULT NULL,
  `email_sent` tinyint(4) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dunning_rules`
--

CREATE TABLE `dunning_rules` (
  `id` bigint(20) NOT NULL,
  `step_number` int(11) NOT NULL,
  `days_after_due` int(11) NOT NULL,
  `action_type` varchar(20) NOT NULL,
  `email_template` text DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `id` bigint(20) NOT NULL,
  `template_key` varchar(100) NOT NULL,
  `template_name` varchar(200) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` longtext DEFAULT NULL,
  `placeholders` longtext DEFAULT NULL,
  `category` varchar(20) NOT NULL DEFAULT 'GENERAL' COMMENT 'DUNNING, INVOICE, ORDER, SERVICE, SUPPORT, AUTH, GENERAL',
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` bigint(20) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_templates`
--

INSERT INTO `email_templates` (`id`, `template_key`, `template_name`, `subject`, `body`, `placeholders`, `category`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(1, 'dunning_reminder_1', 'Dunning - First Reminder', 'Invoice #{invoice_no} - Payment Reminder', '<p>Dear {client_name},</p><p>This is a friendly reminder that your invoice <strong>#{invoice_no}</strong> for <strong>{currency} {amount_due}</strong> was due on <strong>{due_date}</strong>.</p><p>Please make payment at your earliest convenience to avoid any service interruption.</p><p><a href=\"{invoice_url}\">Click here to view and pay your invoice</a></p><p>If you have already made this payment, please disregard this notice.</p><p>Thank you,<br>{site_name}</p>', NULL, 'DUNNING', 1, '2026-01-27 19:52:54', NULL, '2026-01-28 01:52:54', NULL, NULL, NULL),
(2, 'dunning_reminder_2', 'Dunning - Second Reminder', 'Invoice #{invoice_no} - Payment Overdue ({days_overdue} days)', '<p>Dear {client_name},</p><p>Your invoice <strong>#{invoice_no}</strong> for <strong>{currency} {amount_due}</strong> is now <strong>{days_overdue} days overdue</strong>.</p><p>We kindly request that you settle this payment as soon as possible to prevent any disruption to your services.</p><p><a href=\"{invoice_url}\">Click here to view and pay your invoice</a></p><p>If you are experiencing any difficulties with payment, please contact our support team.</p><p>Regards,<br>{site_name}</p>', NULL, 'DUNNING', 1, '2026-01-27 19:52:54', NULL, '2026-01-28 01:52:54', NULL, NULL, NULL),
(3, 'dunning_reminder_3', 'Dunning - Final Warning Before Suspension', 'URGENT: Invoice #{invoice_no} - Service Suspension Warning', '<p>Dear {client_name},</p><p><strong>This is an urgent notice.</strong></p><p>Your invoice <strong>#{invoice_no}</strong> for <strong>{currency} {amount_due}</strong> remains unpaid and is now <strong>{days_overdue} days overdue</strong>.</p><p>If payment is not received promptly, your services will be <strong>suspended</strong>.</p><p><a href=\"{invoice_url}\">Click here to pay now and avoid service interruption</a></p><p>Please contact us immediately if you need assistance.</p><p>Regards,<br>{site_name}</p>', NULL, 'DUNNING', 1, '2026-01-27 19:52:54', NULL, '2026-01-28 01:52:54', NULL, NULL, NULL),
(4, 'dunning_suspended', 'Dunning - Service Suspended', 'Invoice #{invoice_no} - Service Suspended Due to Non-Payment', '<p>Dear {client_name},</p><p>Due to non-payment of invoice <strong>#{invoice_no}</strong> ({currency} {amount_due}), your service has been <strong>suspended</strong>.</p><p>Your data is still preserved. To reactivate your service, please make payment immediately:</p><p><a href=\"{invoice_url}\">Click here to pay and reactivate your service</a></p><p>If payment is not received within the next few days, your service may be permanently terminated.</p><p>Regards,<br>{site_name}</p>', NULL, 'DUNNING', 1, '2026-01-27 19:52:54', NULL, '2026-01-28 01:52:54', NULL, NULL, NULL),
(5, 'dunning_terminated', 'Dunning - Service Terminated', 'Invoice #{invoice_no} - Service Terminated', '<p>Dear {client_name},</p><p>Due to prolonged non-payment of invoice <strong>#{invoice_no}</strong> ({currency} {amount_due}), your service has been <strong>terminated</strong>.</p><p>If you wish to restore your service, please contact our support team. Data recovery may not be possible depending on how long ago the service was terminated.</p><p>Regards,<br>{site_name}</p>', NULL, 'DUNNING', 1, '2026-01-27 19:52:54', NULL, '2026-01-28 01:52:54', NULL, NULL, NULL),
(6, 'invoice_created', 'Invoice Created', 'New Invoice #{invoice_no} - {currency} {amount_due}', '<p>Dear {client_name},</p><p>A new invoice has been generated for your account.</p><p><strong>Invoice No:</strong> #{invoice_no}<br><strong>Amount:</strong> {currency} {amount_due}<br><strong>Due Date:</strong> {due_date}</p><p><a href=\"{invoice_url}\">Click here to view and pay your invoice</a></p><p>Thank you for your business.</p><p>Regards,<br>{site_name}</p>', NULL, 'INVOICE', 1, '2026-01-27 19:52:54', NULL, '2026-01-28 01:52:54', NULL, NULL, NULL),
(7, 'invoice_paid', 'Invoice Paid Confirmation', 'Payment Received - Invoice #{invoice_no}', '<p>Dear {client_name},</p><p>Thank you! We have received your payment for invoice <strong>#{invoice_no}</strong>.</p><p><strong>Amount Paid:</strong> {currency} {amount_due}<br><strong>Date:</strong> {invoice_date}</p><p><a href=\"{invoice_url}\">Click here to view your invoice</a></p><p>Thank you for your business.</p><p>Regards,<br>{site_name}</p>', NULL, 'INVOICE', 1, '2026-01-27 19:52:54', NULL, '2026-01-28 01:52:54', NULL, NULL, NULL),
(8, 'order_confirmation', 'Order Confirmation', 'Order Confirmed - #{invoice_no}', '<p>Dear {client_name},</p><p>Thank you for your order! Your order has been received and is being processed.</p><p>You will receive further updates once your order has been reviewed.</p><p>Thank you for choosing {site_name}.</p><p>Regards,<br>{site_name}</p>', NULL, 'ORDER', 1, '2026-01-27 19:52:54', NULL, '2026-02-21 22:39:58', NULL, NULL, NULL),
(9, 'welcome_email', 'Welcome Email', 'Welcome to {site_name}', '<p>Dear {client_name},</p><p>Welcome to <strong>{site_name}</strong>! Your account has been created successfully.</p><p>You can log in to your client area at: <a href=\"{site_url}\">{site_url}</a></p><p>Thank you for choosing us.</p><p>Regards,<br>{site_name}</p>', NULL, 'AUTH', 1, '2026-01-27 19:52:54', NULL, '2026-01-28 01:52:54', NULL, NULL, NULL),
(10, 'password_reset', 'Password Reset', 'Password Reset Request - {site_name}', '<p>Dear {client_name},</p><p>We received a request to reset your password for your account at {site_name}.</p><p><a href=\"{reset_link}\" style=\"background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;\">Reset Your Password</a></p><p>Or copy and paste this link into your browser:</p><p>{reset_link}</p><p>This link will expire in 1 hour.</p><p>If you did not request this, please ignore this email.</p><p>Regards,<br>{site_name}</p>', '{client_name}, {site_name}, {site_url}, {reset_link}', 'AUTH', 1, '2026-01-27 19:52:54', NULL, '2026-03-24 11:08:19', 1, NULL, NULL),
(11, 'invoice_payment_confirmation', 'Payment Confirmation', 'Payment Received - Invoice #{invoice_no}', '<p>Dear {client_name},</p>\r\n<p>Thank you for your payment! We have successfully received your payment for Invoice <strong>#{invoice_no}</strong>.</p>\r\n<h3>Payment Details:</h3>\r\n<table style=\"border-collapse: collapse; width: 100%; max-width: 500px;\">\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Invoice Number:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">#{invoice_no}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Amount Paid:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{currency_symbol}{amount}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Payment Method:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{payment_method}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Transaction ID:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{transaction_id}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Payment Date:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{payment_date}</td></tr>\r\n</table>\r\n<p style=\"margin-top: 20px;\">You can view your invoice and payment history by logging into your account.</p>\r\n<p>If you have any questions about this payment, please don\'t hesitate to contact us.</p>\r\n<p>Thank you for your business!</p>\r\n<p>Best Regards,<br>{company_name}</p>', '{client_name}, {invoice_no}, {amount}, {currency_symbol}, {payment_method}, {transaction_id}, {payment_date}, {company_name}', 'INVOICE', 1, '2026-02-21 00:54:23', NULL, '2026-02-21 06:54:23', NULL, NULL, NULL),
(12, 'admin_payment_notification', 'Admin Payment Notification', 'New Payment Received - Invoice #{invoice_no} - {currency_symbol}{amount}', '<p>A new payment has been received.</p>\r\n<h3>Payment Details:</h3>\r\n<table style=\"border-collapse: collapse; width: 100%; max-width: 500px;\">\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Customer:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{client_name}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Company:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{company_name_customer}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Email:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{client_email}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Invoice Number:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">#{invoice_no}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Amount:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{currency_symbol}{amount}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Payment Method:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{payment_method}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Gateway:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{gateway_name}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Transaction ID:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{transaction_id}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Payment Date:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{payment_date}</td></tr>\r\n</table>\r\n<p style=\"margin-top: 20px;\"><a href=\"{admin_invoice_url}\" style=\"background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;\">View Invoice</a></p>', '{client_name}, {company_name_customer}, {client_email}, {invoice_no}, {amount}, {currency_symbol}, {payment_method}, {gateway_name}, {transaction_id}, {payment_date}, {admin_invoice_url}', 'INVOICE', 1, '2026-02-21 00:54:23', NULL, '2026-02-21 06:54:23', NULL, NULL, NULL),
(13, 'ticket_new_to_department', 'New Ticket - Department Notification', 'New Support Ticket #{ticket_id} - {ticket_subject}', '<p>A new support ticket has been submitted.</p>\r\n<h3>Ticket Details:</h3>\r\n<table style=\"border-collapse: collapse; width: 100%; max-width: 500px;\">\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Ticket ID:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">#{ticket_id}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Subject:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{ticket_subject}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Priority:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{ticket_priority}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Department:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{department_name}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Created:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{ticket_date}</td></tr>\r\n</table>\r\n<h3>Customer Details:</h3>\r\n<table style=\"border-collapse: collapse; width: 100%; max-width: 500px;\">\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Name:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{client_name}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Company:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{company_name_customer}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Email:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{client_email}</td></tr>\r\n</table>\r\n<h3>Message:</h3>\r\n<div style=\"padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px; margin: 10px 0;\">\r\n{ticket_message}\r\n</div>\r\n<p style=\"margin-top: 20px;\"><a href=\"{admin_ticket_url}\" style=\"background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;\">View & Reply to Ticket</a></p>', '{ticket_id}, {ticket_subject}, {ticket_priority}, {department_name}, {ticket_date}, {client_name}, {company_name_customer}, {client_email}, {ticket_message}, {admin_ticket_url}', 'TICKET', 1, '2026-02-21 16:38:55', 1, '2026-02-21 22:38:55', 1, NULL, NULL),
(14, 'ticket_new_to_customer', 'New Ticket - Customer Notification', 'Support Ticket Created - #{ticket_id}', '<p>Dear {client_name},</p>\r\n<p>A support ticket has been created on your behalf.</p>\r\n<h3>Ticket Details:</h3>\r\n<table style=\"border-collapse: collapse; width: 100%; max-width: 500px;\">\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Ticket ID:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">#{ticket_id}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Subject:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{ticket_subject}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Priority:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{ticket_priority}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Department:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{department_name}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Created:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{ticket_date}</td></tr>\r\n</table>\r\n<h3>Message:</h3>\r\n<div style=\"padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px; margin: 10px 0;\">\r\n{ticket_message}\r\n</div>\r\n<p style=\"margin-top: 20px;\">You can view and reply to this ticket by logging into your account.</p>\r\n<p><a href=\"{ticket_url}\" style=\"background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;\">View Ticket</a></p>\r\n<p>If you have any questions, please don\'t hesitate to contact us.</p>\r\n<p>Best Regards,<br>{company_name}</p>', '{client_name}, {ticket_id}, {ticket_subject}, {ticket_priority}, {department_name}, {ticket_date}, {ticket_message}, {ticket_url}, {company_name}', 'TICKET', 1, '2026-02-21 16:38:55', 1, '2026-02-21 22:38:55', 1, NULL, NULL),
(15, 'ticket_reply_to_customer', 'Ticket Reply - Customer Notification', 'Reply to Your Ticket #{ticket_id} - {ticket_subject}', '<p>Dear {client_name},</p>\r\n<p>Our support team has replied to your ticket.</p>\r\n<h3>Ticket Details:</h3>\r\n<table style=\"border-collapse: collapse; width: 100%; max-width: 500px;\">\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Ticket ID:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">#{ticket_id}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Subject:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{ticket_subject}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Department:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{department_name}</td></tr>\r\n</table>\r\n<h3>Reply:</h3>\r\n<div style=\"padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px; margin: 10px 0;\">\r\n{reply_message}\r\n</div>\r\n<p style=\"margin-top: 20px;\">You can view the full conversation and reply by logging into your account.</p>\r\n<p><a href=\"{ticket_url}\" style=\"background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;\">View Ticket</a></p>\r\n<p>Best Regards,<br>{company_name}</p>', '{client_name}, {ticket_id}, {ticket_subject}, {department_name}, {reply_message}, {ticket_url}, {company_name}', 'TICKET', 1, '2026-02-21 16:38:55', 1, '2026-02-21 22:38:55', 1, NULL, NULL),
(16, 'ticket_reply_to_department', 'Ticket Reply - Department Notification', 'Customer Reply - Ticket #{ticket_id} - {ticket_subject}', '<p>A customer has replied to a support ticket.</p>\r\n<h3>Ticket Details:</h3>\r\n<table style=\"border-collapse: collapse; width: 100%; max-width: 500px;\">\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Ticket ID:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">#{ticket_id}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Subject:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{ticket_subject}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Priority:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{ticket_priority}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Department:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{department_name}</td></tr>\r\n</table>\r\n<h3>Customer:</h3>\r\n<table style=\"border-collapse: collapse; width: 100%; max-width: 500px;\">\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Name:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{client_name}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Email:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{client_email}</td></tr>\r\n</table>\r\n<h3>Reply:</h3>\r\n<div style=\"padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px; margin: 10px 0;\">\r\n{reply_message}\r\n</div>\r\n<p style=\"margin-top: 20px;\"><a href=\"{admin_ticket_url}\" style=\"background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;\">View & Reply to Ticket</a></p>', '{ticket_id}, {ticket_subject}, {ticket_priority}, {department_name}, {client_name}, {client_email}, {reply_message}, {admin_ticket_url}', 'TICKET', 1, '2026-02-21 16:38:55', 1, '2026-02-21 22:38:55', 1, NULL, NULL),
(18, 'admin_order_notification', 'Admin Order Notification', 'New Order Received - #{order_no} - {currency_symbol}{total_amount}', '<p>A new order has been placed.</p>\r\n<h3>Customer Details:</h3>\r\n<table style=\"border-collapse: collapse; width: 100%; max-width: 500px;\">\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Customer Name:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{client_name}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Company:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{company_name_customer}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Email:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{client_email}</td></tr>\r\n</table>\r\n<h3>Order Details:</h3>\r\n<table style=\"border-collapse: collapse; width: 100%; max-width: 500px;\">\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Order Number:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">#{order_no}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Order Date:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{order_date}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Invoice Number:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">#{invoice_no}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Total Amount:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{currency_symbol}{total_amount}</td></tr>\r\n<tr><td style=\"padding: 8px; border: 1px solid #ddd; background: #f5f5f5;\"><strong>Payment Status:</strong></td><td style=\"padding: 8px; border: 1px solid #ddd;\">{pay_status}</td></tr>\r\n</table>\r\n<h3>Order Items:</h3>\r\n{order_items}\r\n<p style=\"margin-top: 20px;\">\r\n<a href=\"{admin_order_url}\" style=\"background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-right: 10px;\">View Order</a>\r\n<a href=\"{admin_invoice_url}\" style=\"background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;\">View Invoice</a>\r\n</p>', '{client_name}, {company_name_customer}, {client_email}, {order_no}, {order_date}, {invoice_no}, {total_amount}, {currency_symbol}, {pay_status}, {order_items}, {admin_order_url}, {admin_invoice_url}', 'ORDER', 1, '2026-02-21 16:39:58', 1, '2026-02-21 22:39:58', 1, NULL, NULL),
(19, 'email_verification', 'Email Verification', 'Verify your email address - {site_name}', '<p>Dear {client_name},</p><p>Thank you for registering with <strong>{site_name}</strong>. Please verify your email address to activate your account.</p><p><a href=\"{verification_link}\" style=\"background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;\">Verify My Email</a></p><p>Or copy and paste this link into your browser:</p><p>{verification_link}</p><p>If you did not create this account, please ignore this email.</p><p>Regards,<br>{site_name}</p>', '{client_name}, {site_name}, {site_url}, {verification_link}', 'AUTH', 1, '2026-02-21 16:39:58', 1, '2026-02-21 22:39:58', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `expense_type_id` int(11) NOT NULL,
  `expense_vendor_id` int(11) NOT NULL DEFAULT 0,
  `exp_amount` double NOT NULL,
  `paid_amount` double NOT NULL DEFAULT 0,
  `expense_date` date NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `expense_type_id`, `expense_vendor_id`, `exp_amount`, `paid_amount`, `expense_date`, `attachment`, `remarks`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(1, 1, 2, 100, 100, '2024-08-04', '172270785810_Screenshot_from_2024-07-01_20-33-28.png', 'test', 1, '2024-08-03 19:36:49', 1, '2024-08-03 21:31:04', 1, NULL, NULL),
(2, 1, 2, 1, 1, '2026-02-13', '686b378044e7c04d1238fb87ee411f97.jpg', 'test', 1, '2026-02-13 02:38:29', 1, '2026-02-13 02:38:32', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `expense_types`
--

CREATE TABLE `expense_types` (
  `id` int(11) NOT NULL,
  `expense_type` varchar(255) NOT NULL,
  `remarks` text DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expense_types`
--

INSERT INTO `expense_types` (`id`, `expense_type`, `remarks`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(1, 'VPS Hosting', 'testt', 1, NULL, NULL, '2024-08-03 03:01:16', 1, NULL, NULL),
(2, 'Domain register - Resell.biz', 'Domain register - Resell.biz', 1, '2024-08-03 09:01:36', 1, '2024-08-03 07:01:36', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `expense_vendors`
--

CREATE TABLE `expense_vendors` (
  `id` int(11) NOT NULL,
  `vendor_name` varchar(255) NOT NULL,
  `remarks` text DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expense_vendors`
--

INSERT INTO `expense_vendors` (`id`, `vendor_name`, `remarks`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(1, 'Digital Ocean', NULL, 1, NULL, NULL, '2020-08-22 01:42:20', NULL, NULL, NULL),
(2, 'Knownhost', 'Hosting provider', 1, '2024-08-03 09:09:54', 1, '2024-08-03 03:10:05', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `expense_view`
-- (See below for the actual view)
--
CREATE TABLE `expense_view` (
`id` int(11)
,`expense_type_id` int(11)
,`expense_vendor_id` int(11)
,`exp_amount` double
,`paid_amount` double
,`expense_date` date
,`attachment` varchar(255)
,`remarks` text
,`status` tinyint(4)
,`inserted_on` datetime
,`updated_on` timestamp
,`expense_type` varchar(255)
,`vendor_name` varchar(255)
);

-- --------------------------------------------------------

--
-- Table structure for table `gen_numbers`
--

CREATE TABLE `gen_numbers` (
  `id` bigint(20) NOT NULL,
  `no_type` varchar(10) NOT NULL,
  `last_no` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gen_numbers`
--

INSERT INTO `gen_numbers` (`id`, `no_type`, `last_no`) VALUES
(1, 'ORDER', 2038),
(2, 'INVOICE', 1548);

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` bigint(20) NOT NULL,
  `invoice_uuid` varchar(40) NOT NULL,
  `company_id` bigint(20) NOT NULL DEFAULT 0,
  `order_id` bigint(20) NOT NULL,
  `currency_id` int(11) NOT NULL,
  `currency_code` varchar(3) DEFAULT NULL,
  `invoice_no` varchar(20) NOT NULL,
  `sub_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(15,2) NOT NULL DEFAULT 0.00,
  `vat` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `coupon_code` varchar(32) DEFAULT NULL,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `due_date` date NOT NULL,
  `order_date` date DEFAULT NULL,
  `cancel_date` date DEFAULT NULL,
  `refund_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `pay_status` varchar(10) NOT NULL DEFAULT 'DUE' COMMENT 'DUE,PAID,PARTIAL,CANCELLED',
  `need_api_call` tinyint(4) NOT NULL DEFAULT 0,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_uuid`, `company_id`, `order_id`, `currency_id`, `currency_code`, `invoice_no`, `sub_total`, `tax`, `vat`, `discount`, `coupon_code`, `total`, `due_date`, `order_date`, `cancel_date`, `refund_date`, `remarks`, `status`, `pay_status`, `need_api_call`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(561, '49af90f2-d430-4e60-b6b4-1704bcae8cfd', 1, 735, 1, 'USD', '1543', 12.95, 0.00, 0.00, 0.00, NULL, 12.95, '2026-03-07', '2026-03-07', NULL, NULL, NULL, 1, 'DUE', 0, '2026-03-07 02:39:39', 1, '2026-03-07 09:48:56', NULL, NULL, NULL),
(562, '60cb4a18-97a8-44c3-9290-e84362a21267', 1, 736, 1, 'USD', '1544', 13.25, 0.00, 0.00, 2.00, 'XE983SVP', 11.25, '2026-03-17', '2026-03-17', NULL, NULL, NULL, 1, 'DUE', 0, '2026-03-17 15:42:37', 1, '2026-03-17 14:42:37', NULL, NULL, NULL),
(565, 'e05eeee6-d48c-4d2c-af42-502cebeceb15', 1, 738, 1, 'USD', '1547', 10.00, 0.00, 0.00, 0.00, '', 10.00, '2026-07-03', '2026-07-03', NULL, NULL, NULL, 1, 'PAID', 0, '2026-07-03 09:58:35', 1, '2026-07-03 15:04:49', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` bigint(20) NOT NULL,
  `invoice_id` bigint(20) NOT NULL,
  `item` text NOT NULL,
  `item_desc` text NOT NULL,
  `item_type` tinyint(4) NOT NULL COMMENT '1=domain, 2=product_service',
  `ref_id` bigint(20) DEFAULT NULL COMMENT 'FK to order_domains.id or order_services.id',
  `billing_cycle_id` int(11) DEFAULT NULL COMMENT 'Billing cycle from the source order item',
  `note` text DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `sub_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(15,2) NOT NULL DEFAULT 0.00,
  `vat` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `billing_period_start` date DEFAULT NULL COMMENT 'Start of billing period (NULL for one-time)',
  `billing_period_end` date DEFAULT NULL COMMENT 'End of billing period (NULL for one-time)',
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoice_items`
--

INSERT INTO `invoice_items` (`id`, `invoice_id`, `item`, `item_desc`, `item_type`, `ref_id`, `billing_cycle_id`, `note`, `quantity`, `unit_price`, `discount`, `sub_total`, `tax`, `vat`, `total`, `billing_period_start`, `billing_period_end`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`) VALUES
(579, 561, 'Register: testing-ecomz.xyz', 'Register: testing-ecomz.xyz - testing-ecomz.xyz', 1, 731, 1, NULL, 1, 12.95, 0.00, 12.95, 0.00, 0.00, 12.95, '2026-03-07', '2026-04-06', '2026-03-07 02:39:39', 1, '2026-03-07 08:39:39', NULL),
(580, 562, 'Register: testingsarkerdom.net', 'Register: testingsarkerdom.net - testingsarkerdom.net', 1, 732, 1, NULL, 1, 13.25, 0.00, 13.25, 0.00, 0.00, 13.25, '2026-03-17', '2026-04-16', '2026-03-17 15:42:38', 1, '2026-03-17 14:42:39', NULL),
(583, 565, 'TongBari Product', 'TongBari Product', 3, 2, 1, NULL, 1, 10.00, 0.00, 10.00, 0.00, 0.00, 10.00, '2026-07-03', '2026-08-02', '2026-07-03 09:58:36', 1, '2026-07-03 07:58:37', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `invoice_txn`
--

CREATE TABLE `invoice_txn` (
  `id` bigint(20) NOT NULL,
  `invoice_id` bigint(20) DEFAULT NULL,
  `payment_gateway_id` int(11) DEFAULT NULL COMMENT 'FK to payment gateway',
  `payment_transaction_id` bigint(20) DEFAULT NULL COMMENT 'FK to payment_transactions',
  `transaction_id` varchar(100) DEFAULT NULL COMMENT 'External reference from payment gateway',
  `txn_date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency_code` varchar(3) DEFAULT NULL,
  `type` enum('payment','refund','credit') NOT NULL DEFAULT 'payment',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0=failed, 1=success, 2=pending',
  `remarks` text DEFAULT NULL,
  `attachments` text DEFAULT NULL,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` bigint(20) DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL,
  `updated_by` bigint(20) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoice_txn`
--

INSERT INTO `invoice_txn` (`id`, `invoice_id`, `payment_gateway_id`, `payment_transaction_id`, `transaction_id`, `txn_date`, `amount`, `currency_code`, `type`, `status`, `remarks`, `attachments`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(10, 561, 2, 9, 'pi_3T8GPXQ1EoHKEhCU0sqybXnR', '2026-03-07', 12.95, 'USD', 'payment', 1, 'Payment via Stripe', NULL, '2026-03-07 02:40:43', 1, NULL, NULL, NULL, NULL),
(11, 565, 6, 10, '2607031404382PrrxBHgpewcu8W', '2026-07-03', 10.00, 'USD', 'payment', 1, 'Payment via Sslcommerz', NULL, '2026-07-03 10:05:08', 1, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `invoice_view`
-- (See below for the actual view)
--
CREATE TABLE `invoice_view` (
`id` bigint(20)
,`invoice_uuid` varchar(40)
,`company_id` bigint(20)
,`order_id` bigint(20)
,`currency_id` int(11)
,`currency_code` varchar(3)
,`invoice_no` varchar(20)
,`sub_total` decimal(15,2)
,`tax` decimal(15,2)
,`vat` decimal(15,2)
,`total` decimal(15,2)
,`due_date` date
,`order_date` date
,`cancel_date` date
,`refund_date` date
,`remarks` text
,`status` tinyint(4)
,`pay_status` varchar(10)
,`need_api_call` tinyint(4)
,`inserted_on` datetime
,`updated_on` timestamp
,`company_name` varchar(150)
,`company_mobile` varchar(16)
,`company_email` varchar(255)
,`company_address` varchar(255)
,`company_city` varchar(100)
,`company_state` varchar(100)
,`company_zipcode` varchar(10)
,`country` varchar(100)
,`company_phone` varchar(16)
,`order_uuid` varchar(40)
,`order_no` bigint(20)
,`total_paid` decimal(37,2)
,`balance_due` decimal(38,2)
,`last_payment_date` date
);

-- --------------------------------------------------------

--
-- Table structure for table `kbs`
--

CREATE TABLE `kbs` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `slug` varchar(255) NOT NULL,
  `article` text NOT NULL,
  `tags` text DEFAULT NULL COMMENT 'with comma',
  `total_view` int(11) NOT NULL DEFAULT 0,
  `useful` int(11) NOT NULL DEFAULT 0,
  `upvote` int(11) NOT NULL DEFAULT 0,
  `downvote` int(11) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 1,
  `is_hidden` tinyint(4) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kbs`
--

INSERT INTO `kbs` (`id`, `title`, `slug`, `article`, `tags`, `total_view`, `useful`, `upvote`, `downvote`, `sort_order`, `is_hidden`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(1, 'Hello World', 'hello-world', '<p>Hello World. <b>This is knowledge base article</b></p>', 'Hello, World, Hello World', 0, 0, 0, 0, 1, 0, 1, NULL, NULL, '2024-08-04 17:08:26', 1, NULL, NULL),
(2, 'Test', 'test', '<p>THis is test. <b>This is knowledge base article</b></p>', 'test', 0, 0, 0, 0, 1, 0, 1, NULL, NULL, '2024-08-04 17:08:45', 1, NULL, NULL),
(3, 'How to register domain', 'how-to-register-domain', '<h2>How to register domain.</h2>', 'asdfasd', 0, 0, 0, 0, 1, 0, 1, '2024-08-04 17:25:40', 1, '2024-08-05 07:06:06', 1, NULL, NULL),
(4, 'vps server management', 'vps-server-management', '<p>vps-server-management. vps-server-management</p>', '', 0, 0, 0, 0, 1, 0, 1, '2026-02-08 01:17:22', 1, NULL, NULL, NULL, NULL),
(5, 'vps server login', 'vps-server-login', '<p>vps server login. vps server login</p>', 'vps', 0, 0, 0, 0, 1, 0, 1, '2026-02-08 01:18:14', 1, NULL, NULL, NULL, NULL),
(6, 'How to create Database in CPanel', 'how-to-create-database-in-cpanel', '<p>How to create Database in CPanel</p>', 'cpanel, database', 0, 0, 0, 0, 1, 0, 1, '2026-02-08 01:20:46', 1, NULL, NULL, NULL, NULL),
(7, 'How to create database user with password in cpanel', 'how-to-create-database-user-with-password-in-cpanel', '<p>How to create database user with password in cpanel</p>', 'cpanel, database', 0, 0, 0, 0, 1, 0, 1, '2026-02-08 01:21:45', 1, NULL, NULL, NULL, NULL),
(8, 'How to allow remote access to cpanel database', 'how-to-allow-remote-access-to-cpanel-database', '<p>How to allow remote access to cpanel database</p>', 'cpanel, database', 0, 0, 0, 0, 1, 0, 1, '2026-02-08 01:23:17', 1, NULL, NULL, NULL, NULL),
(9, 'How to change password of cpanel database user', 'how-to-change-password-of-cpanel-database-user', '<p>How to change password of cpanel database user</p>', 'cpanel, database', 0, 0, 0, 0, 1, 0, 1, '2026-02-08 01:24:07', 1, NULL, NULL, NULL, NULL),
(10, 'How to create subdomain in cpanel', 'how-to-create-subdomain-in-cpanel', '<p>How to create subdomain in cpanel</p>', 'cpanel, subdomain', 0, 0, 0, 0, 1, 0, 1, '2026-02-08 01:24:57', 1, NULL, NULL, NULL, NULL),
(11, 'How to create email account in cpanel', 'how-to-create-email-account-in-cpanel', '<p>How to create email account in cpanel</p>', 'cpanel, email account', 0, 0, 0, 0, 1, 0, 1, '2026-02-08 01:26:04', 1, NULL, NULL, NULL, NULL),
(12, 'How to login in email account in cpanel', 'how-to-login-in-email-account-in-cpanel', '<p>How to login in email account in cpanel</p>', 'cpanel, email account', 0, 0, 0, 0, 1, 0, 1, '2026-02-08 01:27:15', 1, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `kb_cats`
--

CREATE TABLE `kb_cats` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `cat_title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_hidden` tinyint(4) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kb_cats`
--

INSERT INTO `kb_cats` (`id`, `parent_id`, `cat_title`, `slug`, `description`, `is_hidden`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(1, 0, 'Hosting Services', 'hosting-services', 'How to purchase/renew shared hosting. How to purchase/renew reseller hosting.', 0, 1, '2024-08-05 07:02:44', 1, NULL, NULL, NULL, NULL),
(2, 0, 'Domain Management', 'domain-management', 'How to register domain. How to transfer domain. How to manage domain', 0, 1, '2024-08-05 07:02:44', 1, NULL, NULL, NULL, NULL),
(3, 0, 'VPS Server', 'vps-server', 'VPS server (Managed & Un-managed)', 0, 1, '2024-08-05 07:02:44', 1, NULL, NULL, NULL, NULL),
(4, 0, 'Database', 'database', 'Database', 0, 1, '2026-02-08 01:19:24', 1, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `kb_cat_mapping`
--

CREATE TABLE `kb_cat_mapping` (
  `id` bigint(20) NOT NULL,
  `kb_id` int(5) NOT NULL,
  `kb_cat_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kb_cat_mapping`
--

INSERT INTO `kb_cat_mapping` (`id`, `kb_id`, `kb_cat_id`) VALUES
(5, 1, 1),
(6, 1, 2),
(7, 2, 1),
(8, 2, 2),
(12, 3, 2),
(15, 4, 3),
(16, 5, 3),
(17, 6, 4),
(18, 7, 4),
(19, 8, 4),
(20, 9, 4),
(21, 10, 1),
(22, 11, 1),
(23, 12, 1);

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) UNSIGNED NOT NULL,
  `identifier` varchar(255) NOT NULL COMMENT 'IP address or email',
  `identifier_type` enum('ip','email') NOT NULL DEFAULT 'ip',
  `ip_address` varchar(45) NOT NULL COMMENT 'IP address of the attempt',
  `user_agent` varchar(255) DEFAULT NULL,
  `is_successful` tinyint(1) NOT NULL DEFAULT 0,
  `attempt_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_100000_create_password_resets_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) NOT NULL,
  `order_uuid` varchar(40) NOT NULL,
  `order_no` bigint(20) DEFAULT NULL,
  `company_id` bigint(20) NOT NULL,
  `currency_id` int(11) NOT NULL,
  `currency_code` varchar(3) DEFAULT NULL,
  `order_date` date DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `vat_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `coupon_code` varchar(32) DEFAULT NULL,
  `coupon_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_gateway_id` int(11) NOT NULL DEFAULT 0,
  `remarks` text DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `instructions` text DEFAULT NULL,
  `has_notification` tinyint(4) NOT NULL DEFAULT 0,
  `need_api_call` tinyint(4) NOT NULL DEFAULT 0,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_uuid`, `order_no`, `company_id`, `currency_id`, `currency_code`, `order_date`, `amount`, `vat_amount`, `tax_amount`, `coupon_code`, `coupon_amount`, `discount_amount`, `total_amount`, `payment_gateway_id`, `remarks`, `status`, `instructions`, `has_notification`, `need_api_call`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(735, '8b10d04a-e485-498a-baaf-88d5c493c4a7', 2034, 1, 1, 'USD', '2026-03-07', 12.95, 0.00, 0.00, '', 0.00, 0.00, 12.95, 7, '', 1, '', 0, 0, '2026-03-07 02:39:39', 1, '2026-03-07 08:40:43', NULL, NULL, NULL),
(736, '213c0861-b953-428a-9fd0-0a9da7a2092b', 2035, 1, 1, 'USD', '2026-03-17', 13.25, 0.00, 0.00, 'XE983SVP', 2.00, 2.00, 11.25, 7, '', 1, '', 0, 0, '2026-03-17 15:42:36', 1, '2026-03-17 14:42:35', NULL, NULL, NULL),
(738, '5697819b-f2b9-4395-a619-56ca2b9320d0', 2037, 1, 1, 'USD', '2026-07-03', 10.00, 0.00, 0.00, '', 0.00, 0.00, 10.00, 7, '', 1, '', 0, 0, '2026-07-03 09:58:34', 1, '2026-07-03 07:58:35', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_domains`
--

CREATE TABLE `order_domains` (
  `id` bigint(20) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `company_id` bigint(20) NOT NULL,
  `dom_register_id` int(11) NOT NULL,
  `dom_pricing_id` int(11) NOT NULL,
  `order_type` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1=reg, 2=transfer, 3=dns_only, 4=existing',
  `epp_code` varchar(200) DEFAULT NULL,
  `domain` text NOT NULL,
  `linked_service_id` bigint(20) DEFAULT NULL COMMENT 'Links to order_services.id when hosting is purchased with domain',
  `first_pay_amount` decimal(15,2) NOT NULL,
  `recurring_amount` decimal(15,2) NOT NULL,
  `reg_date` date NOT NULL,
  `reg_period` int(2) NOT NULL DEFAULT 1 COMMENT 'year',
  `exp_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL COMMENT 'Invoice payment due date',
  `next_renewal_date` date DEFAULT NULL COMMENT 'Domain renewal date',
  `suspension_date` date DEFAULT NULL COMMENT 'Date when domain was/will be suspended',
  `suspension_reason` varchar(255) DEFAULT NULL COMMENT 'Reason for suspension',
  `termination_date` date DEFAULT NULL COMMENT 'Date when domain was/will be terminated',
  `is_synced` tinyint(4) NOT NULL DEFAULT 1,
  `last_sync_dt` datetime DEFAULT NULL,
  `domain_cust_id` bigint(20) DEFAULT NULL,
  `domain_order_id` bigint(20) DEFAULT NULL,
  `dns_management` tinyint(4) NOT NULL DEFAULT 0,
  `email_forwarding` tinyint(4) NOT NULL DEFAULT 0,
  `id_protection` tinyint(4) NOT NULL DEFAULT 0,
  `auto_renew` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=auto renew, 0=manual',
  `transfer_lock` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=locked, 0=unlocked',
  `dns_type` varchar(12) NOT NULL DEFAULT 'default_ns' COMMENT 'default_ns, custom_ns, records',
  `ns1` varchar(150) DEFAULT NULL,
  `ns2` varchar(150) DEFAULT NULL,
  `ns3` varchar(150) DEFAULT NULL,
  `ns4` varchar(150) DEFAULT NULL,
  `contact_name` varchar(150) DEFAULT NULL,
  `contact_company` varchar(150) DEFAULT NULL,
  `contact_email` varchar(150) DEFAULT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `contact_address1` varchar(255) DEFAULT NULL,
  `contact_address2` varchar(255) DEFAULT NULL,
  `contact_city` varchar(100) DEFAULT NULL,
  `contact_state` varchar(100) DEFAULT NULL,
  `contact_zip` varchar(20) DEFAULT NULL,
  `contact_country` varchar(5) DEFAULT NULL,
  `last_contact_sync` datetime DEFAULT NULL,
  `status` tinyint(4) NOT NULL COMMENT '0=pending reg, 1=active, 2=expired, 3=grace, 4=cancelled, 5=pending transfer, 6=deleted',
  `provisioning_status` varchar(20) DEFAULT 'pending' COMMENT 'pending, in_progress, completed, failed',
  `remarks` varchar(255) DEFAULT NULL,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_domains`
--

INSERT INTO `order_domains` (`id`, `order_id`, `company_id`, `dom_register_id`, `dom_pricing_id`, `order_type`, `epp_code`, `domain`, `linked_service_id`, `first_pay_amount`, `recurring_amount`, `reg_date`, `reg_period`, `exp_date`, `due_date`, `next_renewal_date`, `suspension_date`, `suspension_reason`, `termination_date`, `is_synced`, `last_sync_dt`, `domain_cust_id`, `domain_order_id`, `dns_management`, `email_forwarding`, `id_protection`, `auto_renew`, `transfer_lock`, `dns_type`, `ns1`, `ns2`, `ns3`, `ns4`, `contact_name`, `contact_company`, `contact_email`, `contact_phone`, `contact_address1`, `contact_address2`, `contact_city`, `contact_state`, `contact_zip`, `contact_country`, `last_contact_sync`, `status`, `provisioning_status`, `remarks`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(731, 735, 1, 2, 18, 1, NULL, 'testing-ecomz.xyz', NULL, 12.95, 12.95, '2026-03-07', 1, '2027-03-07', '2026-03-14', '2027-03-07', NULL, NULL, NULL, 1, '2026-03-07 02:40:43', 0, 1106489, 0, 0, 0, 1, 1, 'default_ns', 'verify-contact-details.namecheap.com', 'failed-whois-verification.namecheap.com', NULL, NULL, 'A. L Perera', '', 'client@whmaz.com', '+94.788888888', '201 Shanti VillaSilkhouse Street', '', 'KANDY', 'KANDY', '20000', 'LK', '2026-06-13 11:31:41', 1, 'pending', '', '2026-03-07 02:39:39', 1, '2026-06-13 16:31:41', NULL, NULL, NULL),
(732, 736, 1, 2, 3, 1, NULL, 'testingsarkerdom.net', NULL, 13.25, 13.25, '2026-03-17', 1, '2027-03-17', '2026-03-24', '2027-03-17', NULL, NULL, NULL, 1, NULL, NULL, NULL, 0, 0, 0, 1, 1, 'default_ns', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'pending', '', '2026-03-17 15:42:38', 1, '2026-03-17 14:42:38', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_licenses`
--

CREATE TABLE `order_licenses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `company_id` bigint(20) NOT NULL,
  `plan_id` bigint(20) UNSIGNED NOT NULL,
  `pending_plan_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Target product for a paid-pending plan change',
  `pending_billing_cycle_id` int(11) DEFAULT NULL COMMENT 'Target billing cycle for a paid-pending plan change',
  `pending_invoice_id` bigint(20) DEFAULT NULL COMMENT 'Proration invoice whose payment applies the pending plan change',
  `billing_cycle_id` int(11) NOT NULL,
  `currency_id` int(11) NOT NULL DEFAULT 0,
  `currency_code` varchar(3) DEFAULT NULL,
  `first_pay_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `recurring_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `license_key` varchar(255) DEFAULT NULL COMMENT 'Issued on activation',
  `paddle_subscription_id` varchar(100) DEFAULT NULL COMMENT 'Paddle (MoR) subscription id',
  `auto_renew` tinyint(1) NOT NULL DEFAULT 1,
  `reg_date` date DEFAULT NULL,
  `exp_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `next_renewal_date` date DEFAULT NULL,
  `suspension_date` date DEFAULT NULL,
  `suspension_reason` varchar(255) DEFAULT NULL,
  `termination_date` date DEFAULT NULL,
  `is_synced` tinyint(4) NOT NULL DEFAULT 1,
  `last_sync_dt` datetime DEFAULT NULL,
  `license_domain` varchar(255) DEFAULT NULL COMMENT 'Install domain bound on first phone-home',
  `license_ip` varchar(60) DEFAULT NULL COMMENT 'Install IP the client bound at download (bind-once)',
  `last_check_in` datetime DEFAULT NULL COMMENT 'Last successful phone-home',
  `last_check_ip` varchar(45) DEFAULT NULL COMMENT 'IP of last phone-home',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0=pending,1=active,2=expired,3=suspended,4=terminated',
  `remarks` varchar(255) DEFAULT NULL,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_licenses`
--

INSERT INTO `order_licenses` (`id`, `order_id`, `company_id`, `plan_id`, `pending_plan_id`, `pending_billing_cycle_id`, `pending_invoice_id`, `billing_cycle_id`, `currency_id`, `currency_code`, `first_pay_amount`, `recurring_amount`, `license_key`, `paddle_subscription_id`, `auto_renew`, `reg_date`, `exp_date`, `due_date`, `next_renewal_date`, `suspension_date`, `suspension_reason`, `termination_date`, `is_synced`, `last_sync_dt`, `license_domain`, `last_check_in`, `last_check_ip`, `status`, `remarks`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(2, 738, 1, 5, NULL, NULL, NULL, 1, 1, 'USD', 10.00, 10.00, 'WHMAZ-7C3CC-D5171-63422-B187C', NULL, 1, '2026-07-03', '2026-08-02', '2026-07-10', '2026-08-02', NULL, NULL, NULL, 1, '2026-07-03 10:04:51', NULL, NULL, NULL, 1, '', '2026-07-03 09:58:36', 1, '2026-07-03 08:04:51', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_services`
--

CREATE TABLE `order_services` (
  `id` bigint(20) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `company_id` bigint(20) NOT NULL,
  `product_service_id` int(11) NOT NULL,
  `product_service_pricing_id` int(11) NOT NULL,
  `product_service_type_key` varchar(150) NOT NULL,
  `cp_username` varchar(100) DEFAULT NULL COMMENT 'cPanel username',
  `cp_password` varchar(150) DEFAULT '0',
  `cp_disk_used` decimal(10,2) DEFAULT 0.00,
  `cp_disk_limit` decimal(10,2) DEFAULT 0.00,
  `cp_bandwidth_used` decimal(10,2) DEFAULT 0.00,
  `cp_bandwidth_limit` decimal(10,2) DEFAULT 0.00,
  `cp_email_accounts` int(11) DEFAULT 0,
  `cp_email_limit` int(11) DEFAULT 0,
  `cp_databases` int(11) DEFAULT 0,
  `cp_database_limit` int(11) DEFAULT 0,
  `cp_addon_domains` int(11) DEFAULT 0,
  `cp_addon_limit` int(11) DEFAULT 0,
  `cp_subdomains` int(11) DEFAULT 0,
  `cp_subdomain_limit` int(11) DEFAULT 0,
  `cp_last_sync` datetime DEFAULT NULL,
  `billing_cycle_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `first_pay_amount` decimal(15,2) NOT NULL,
  `recurring_amount` decimal(15,2) NOT NULL,
  `hosting_domain` varchar(200) DEFAULT NULL,
  `linked_domain_id` bigint(20) DEFAULT NULL COMMENT 'Links to order_domains.id when domain is purchased with hosting',
  `license_key` varchar(255) DEFAULT NULL COMMENT 'Software license key',
  `license_seats` int(11) DEFAULT NULL COMMENT 'Number of allowed activations/seats',
  `auto_renew` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=auto renew, 0=manual',
  `reg_date` date NOT NULL,
  `exp_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL COMMENT 'Invoice payment due date',
  `next_renewal_date` date DEFAULT NULL COMMENT 'Service renewal date',
  `suspension_date` date DEFAULT NULL COMMENT 'Date when service was/will be suspended',
  `suspension_reason` varchar(255) DEFAULT NULL COMMENT 'Reason for suspension',
  `termination_date` date DEFAULT NULL COMMENT 'Date when service was/will be terminated',
  `is_synced` tinyint(4) NOT NULL DEFAULT 1,
  `last_sync_dt` datetime DEFAULT NULL,
  `status` tinyint(4) NOT NULL COMMENT '0=pending, 1=active, 2=expired, 3=suspended, 4=terminated',
  `provisioning_status` varchar(20) DEFAULT 'pending' COMMENT 'pending, in_progress, completed, failed',
  `remarks` varchar(255) DEFAULT NULL,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `order_view`
-- (See below for the actual view)
--
CREATE TABLE `order_view` (
`id` bigint(20)
,`order_uuid` varchar(40)
,`order_no` bigint(20)
,`company_id` bigint(20)
,`currency_id` int(11)
,`currency_code` varchar(3)
,`order_date` date
,`amount` decimal(15,2)
,`vat_amount` decimal(15,2)
,`tax_amount` decimal(15,2)
,`coupon_code` varchar(32)
,`coupon_amount` decimal(15,2)
,`discount_amount` decimal(15,2)
,`total_amount` decimal(15,2)
,`payment_gateway_id` int(11)
,`remarks` text
,`instructions` text
,`status` tinyint(4)
,`inserted_on` datetime
,`updated_on` timestamp
,`company_name` varchar(150)
,`company_email` varchar(255)
,`company_mobile` varchar(16)
,`company_phone` varchar(16)
,`company_address` varchar(255)
,`country` varchar(100)
,`company_zipcode` varchar(10)
,`company_city` varchar(100)
,`company_state` varchar(100)
,`payment_gateway_name` varchar(100)
,`payment_gateway_fa_icon` varchar(30)
,`service_count` bigint(21)
,`domain_count` bigint(21)
,`services_recurring_total` decimal(37,2)
,`domains_recurring_total` decimal(37,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(11) UNSIGNED NOT NULL,
  `page_title` varchar(255) NOT NULL,
  `page_slug` varchar(255) NOT NULL,
  `page_content` longtext DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` varchar(500) DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `is_system` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'System pages cannot be deleted',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `total_view` int(11) NOT NULL DEFAULT 0,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `page_title`, `page_slug`, `page_content`, `meta_title`, `meta_description`, `meta_keywords`, `is_published`, `is_system`, `sort_order`, `total_view`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`, `status`) VALUES
(1, 'Terms and Conditions', 'terms-and-conditions', '<h2>Terms and Conditions</h2><p>Please add your terms and conditions here. <strong>updated</strong></p>', 'Terms and Conditions', '', '', 1, 1, 1, 46, '2026-02-12 21:24:40', NULL, '2026-02-13 03:35:49', 1, NULL, NULL, 1),
(2, 'Privacy Policy', 'privacy-policy', '<h2>Privacy Policy</h2><p>Please add your privacy policy here.</p>', 'Privacy Policy', NULL, NULL, 1, 1, 2, 52, '2026-02-12 21:24:40', NULL, NULL, NULL, NULL, NULL, 1),
(3, 'Refund Policy', 'refund-policy', '<h2>Refund Policy</h2><p>Please add your refund policy here.</p>', 'Refund Policy', '', '', 1, 0, 3, 55, '2026-02-12 21:24:40', NULL, '2026-02-13 03:38:09', 1, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Stand-in structure for view `pages_view`
-- (See below for the actual view)
--
CREATE TABLE `pages_view` (
`id` int(11) unsigned
,`page_title` varchar(255)
,`page_slug` varchar(255)
,`page_content` longtext
,`meta_title` varchar(255)
,`meta_description` text
,`meta_keywords` varchar(500)
,`is_published` tinyint(1)
,`is_system` tinyint(1)
,`sort_order` int(11)
,`total_view` int(11)
,`inserted_on` datetime
,`inserted_by` int(11)
,`updated_on` datetime
,`updated_by` int(11)
,`deleted_on` datetime
,`deleted_by` int(11)
,`status` tinyint(1)
,`created_by_name` text
,`updated_by_name` text
);

-- --------------------------------------------------------

--
-- Table structure for table `page_history`
--

CREATE TABLE `page_history` (
  `id` int(11) UNSIGNED NOT NULL,
  `page_id` int(11) UNSIGNED NOT NULL,
  `page_title` varchar(255) NOT NULL,
  `page_content` longtext DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `change_type` enum('created','updated','restored') NOT NULL DEFAULT 'updated',
  `change_note` varchar(500) DEFAULT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `changed_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `page_history`
--

INSERT INTO `page_history` (`id`, `page_id`, `page_title`, `page_content`, `meta_title`, `meta_description`, `change_type`, `change_note`, `changed_by`, `changed_at`) VALUES
(1, 1, 'Terms and Conditions', '<h2>Terms and Conditions</h2><p>Please add your terms and conditions here. <strong>updated</strong></p>', 'Terms and Conditions', '', 'updated', '', 1, '2026-02-13 03:35:50'),
(2, 3, 'Refund Policy', '<h2>Refund Policy</h2><p>Please add your refund policy here.</p>', 'Refund Policy', '', 'updated', '', 1, '2026-02-13 03:38:09');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_gateway`
--

CREATE TABLE `payment_gateway` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `gateway_code` varchar(50) NOT NULL DEFAULT 'manual' COMMENT 'Unique code: stripe, paypal, razorpay, paystack, sslcommerz, bank_transfer, manual',
  `gateway_type` enum('online_card','online_wallet','bank_transfer','manual','crypto') NOT NULL DEFAULT 'manual' COMMENT 'Type of payment method',
  `icon_fa_unicode` varchar(30) DEFAULT NULL,
  `pay_type` varchar(8) NOT NULL DEFAULT 'OFFLINE' COMMENT 'OFFLINE,ONLINE',
  `public_key` varchar(500) DEFAULT NULL COMMENT 'Public/Publishable API key',
  `secret_key` varchar(500) DEFAULT NULL COMMENT 'Secret/Private API key (store encrypted)',
  `webhook_secret` varchar(255) DEFAULT NULL COMMENT 'Webhook signature verification secret',
  `is_test_mode` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Sandbox/Test, 0=Live/Production',
  `test_public_key` varchar(500) DEFAULT NULL COMMENT 'Test/Sandbox public key',
  `test_secret_key` varchar(500) DEFAULT NULL COMMENT 'Test/Sandbox secret key',
  `test_webhook_secret` varchar(255) DEFAULT NULL COMMENT 'Test/Sandbox webhook secret',
  `extra_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Gateway-specific settings as JSON' CHECK (json_valid(`extra_config`)),
  `bank_name` varchar(200) DEFAULT NULL,
  `account_name` varchar(200) DEFAULT NULL,
  `account_number` varchar(100) DEFAULT NULL,
  `routing_number` varchar(50) DEFAULT NULL COMMENT 'Routing/Sort code',
  `swift_code` varchar(20) DEFAULT NULL COMMENT 'SWIFT/BIC for international',
  `iban` varchar(50) DEFAULT NULL,
  `supported_currencies` varchar(500) DEFAULT 'USD' COMMENT 'Comma-separated currency codes',
  `min_amount` decimal(15,2) DEFAULT 0.00 COMMENT 'Minimum transaction amount',
  `max_amount` decimal(15,2) DEFAULT 0.00 COMMENT 'Maximum transaction amount (0=unlimited)',
  `fee_type` enum('none','fixed','percentage','both') NOT NULL DEFAULT 'none',
  `fee_fixed` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Fixed fee amount',
  `fee_percent` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Percentage fee (e.g., 2.9 for 2.9%)',
  `fee_bearer` enum('merchant','customer') NOT NULL DEFAULT 'merchant' COMMENT 'Who pays the fee',
  `logo` varchar(255) DEFAULT NULL COMMENT 'Gateway logo filename',
  `display_name` varchar(100) DEFAULT NULL COMMENT 'Name shown to customers',
  `description` text DEFAULT NULL COMMENT 'Description shown during checkout',
  `sort_order` int(11) NOT NULL DEFAULT 0 COMMENT 'Display order (lower=first)',
  `webhook_url` varchar(500) DEFAULT NULL COMMENT 'Auto-generated webhook URL',
  `merchant_id` varchar(100) DEFAULT NULL,
  `merchant_pwd` varchar(255) DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` datetime DEFAULT NULL,
  `updated_on` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_gateway`
--

INSERT INTO `payment_gateway` (`id`, `name`, `gateway_code`, `gateway_type`, `icon_fa_unicode`, `pay_type`, `public_key`, `secret_key`, `webhook_secret`, `is_test_mode`, `test_public_key`, `test_secret_key`, `test_webhook_secret`, `extra_config`, `bank_name`, `account_name`, `account_number`, `routing_number`, `swift_code`, `iban`, `supported_currencies`, `min_amount`, `max_amount`, `fee_type`, `fee_fixed`, `fee_percent`, `fee_bearer`, `logo`, `display_name`, `description`, `sort_order`, `webhook_url`, `merchant_id`, `merchant_pwd`, `instructions`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`) VALUES
(1, 'Offline Payment', 'manual', 'manual', 'f3d1', 'OFFLINE', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'USD', 0.00, 0.00, 'none', 0.00, 0.00, 'merchant', NULL, 'Manual Payment', 'Pay via bank transfer or other offline methods. Your order will be processed after payment confirmation.', 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, '2026-02-21 12:22:53', NULL),
(2, 'Stripe', 'stripe', 'online_card', NULL, 'ONLINE', '', '', '', 1, '', '', '', NULL, '', '', '', '', '', '', 'USD,EUR,GBP,CAD,AUD,INR,SGD,JPY', 1.00, 0.00, 'both', 0.30, 2.90, 'merchant', NULL, 'Stripe Card Payment', 'Pay securely with your credit or debit card via Stripe.', 1, '', '', NULL, '', 1, '2026-02-13 10:28:21', NULL, '2026-03-18 19:44:43', 1),
(3, 'PayPal', 'paypal', 'online_wallet', NULL, 'ONLINE', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'USD,EUR,GBP,CAD,AUD,JPY,CHF,HKD,SGD,SEK,DKK,PLN,NOK,CZK,ILS,MXN,BRL,PHP,TWD,THB,MYR', 0.00, 0.00, 'both', 0.30, 2.90, 'merchant', NULL, 'PayPal', 'Pay securely using your PayPal account or card.', 2, NULL, NULL, NULL, NULL, 0, '2026-02-13 10:28:21', NULL, NULL, NULL),
(4, 'Razorpay', 'razorpay', 'online_card', NULL, 'ONLINE', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'INR', 0.00, 0.00, 'percentage', 0.00, 2.00, 'merchant', NULL, 'Razorpay', 'Pay with UPI, Cards, Netbanking, or Wallets via Razorpay.', 3, NULL, NULL, NULL, NULL, 0, '2026-02-13 10:28:21', NULL, NULL, NULL),
(5, 'Paystack', 'paystack', 'online_card', NULL, 'ONLINE', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'NGN,GHS,ZAR,KES', 0.00, 0.00, 'both', 100.00, 1.50, 'merchant', NULL, 'Paystack', 'Pay with card or bank transfer via Paystack.', 4, NULL, NULL, NULL, NULL, 0, '2026-02-13 10:28:21', NULL, NULL, NULL),
(6, 'SSLCommerz', 'sslcommerz', 'online_card', NULL, 'ONLINE', '', '', '', 1, '', '', NULL, '{\r\n    \"store_id\": \"\",\r\n    \"store_password\": \"\",\r\n    \"sandbox_url\": \"https:\\/\\/sandbox.sslcommerz.com\",\r\n    \"live_url\": \"https:\\/\\/securepay.sslcommerz.com\"\r\n}', '', '', '', '', '', '', 'USD,BDT', 0.10, 0.00, 'percentage', 0.00, 2.00, 'merchant', NULL, 'SSLCommerz', 'Pay with bKash, Nagad, Cards, or Mobile Banking.', 5, '', '', NULL, '', 1, '2026-02-13 10:28:21', NULL, '2026-07-03 15:03:13', 1),
(7, 'Bank Transfer', 'bank_transfer', 'bank_transfer', NULL, 'OFFLINE', '', '', '', 0, '', '', NULL, NULL, '', '', '', '', '', '', 'USD,EUR,GBP,BDT,INR', 0.10, 0.00, 'none', 0.00, 0.00, 'merchant', NULL, 'Bank Transfer', 'Transfer funds directly to our bank account. Order will be processed after payment confirmation.', 10, '', '', NULL, 'Please share the TXN number after payment', 1, '2026-02-13 10:28:21', NULL, '2026-02-14 08:56:29', 1),
(8, 'bKash', 'bkash', 'online_wallet', NULL, 'ONLINE', '', '', '', 1, '', '', NULL, '{"username":"","password":"","sandbox_username":"","sandbox_password":"","sandbox_url":"https://tokenized.sandbox.bka.sh/v1.2.0-beta","live_url":"https://tokenized.pay.bka.sh/v1.2.0-beta"}', '', '', '', '', '', '', 'BDT', 1.00, 0.00, 'none', 0.00, 0.00, 'merchant', NULL, 'bKash', 'Pay securely with your bKash account.', 6, '', '', NULL, '', 0, '2026-07-04 00:00:00', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payment_refunds`
--

CREATE TABLE `payment_refunds` (
  `id` bigint(20) NOT NULL,
  `refund_uuid` varchar(36) NOT NULL,
  `transaction_id` bigint(20) NOT NULL COMMENT 'FK to payment_transactions',
  `invoice_id` bigint(20) NOT NULL,
  `gateway_refund_id` varchar(255) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency_code` varchar(3) NOT NULL,
  `reason` varchar(500) DEFAULT NULL,
  `status` enum('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
  `failure_reason` varchar(500) DEFAULT NULL,
  `gateway_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gateway_response`)),
  `requested_by` bigint(20) DEFAULT NULL,
  `requested_at` datetime NOT NULL DEFAULT current_timestamp(),
  `processed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_transactions`
--

CREATE TABLE `payment_transactions` (
  `id` bigint(20) NOT NULL,
  `transaction_uuid` varchar(36) NOT NULL,
  `invoice_id` bigint(20) NOT NULL,
  `payment_gateway_id` int(11) NOT NULL,
  `gateway_code` varchar(50) NOT NULL,
  `gateway_transaction_id` varchar(255) DEFAULT NULL COMMENT 'Transaction ID from payment gateway',
  `gateway_payment_id` varchar(255) DEFAULT NULL COMMENT 'Payment ID (for gateways like Razorpay)',
  `gateway_order_id` varchar(255) DEFAULT NULL COMMENT 'Order ID created at gateway',
  `gateway_subscription_id` varchar(255) DEFAULT NULL COMMENT 'For recurring payments',
  `amount` decimal(15,2) NOT NULL,
  `fee_amount` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Processing fee charged',
  `net_amount` decimal(15,2) NOT NULL COMMENT 'Amount after fee deduction',
  `currency_code` varchar(3) NOT NULL,
  `exchange_rate` decimal(10,6) DEFAULT 1.000000 COMMENT 'Exchange rate if currency converted',
  `txn_type` enum('payment','refund','partial_refund','chargeback','credit') NOT NULL DEFAULT 'payment',
  `status` enum('pending','processing','completed','failed','cancelled','refunded') NOT NULL DEFAULT 'pending',
  `failure_reason` varchar(500) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL COMMENT 'card, bank, wallet, upi, etc.',
  `card_brand` varchar(20) DEFAULT NULL COMMENT 'visa, mastercard, amex, etc.',
  `card_last4` varchar(4) DEFAULT NULL,
  `card_exp_month` tinyint(2) DEFAULT NULL,
  `card_exp_year` smallint(4) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `wallet_name` varchar(50) DEFAULT NULL COMMENT 'paypal, gpay, applepay, etc.',
  `payer_email` varchar(255) DEFAULT NULL,
  `payer_name` varchar(200) DEFAULT NULL,
  `payer_phone` varchar(20) DEFAULT NULL,
  `gateway_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Full response from gateway' CHECK (json_valid(`gateway_response`)),
  `webhook_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Webhook data received' CHECK (json_valid(`webhook_payload`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Additional transaction metadata' CHECK (json_valid(`metadata`)),
  `initiated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  `refunded_at` datetime DEFAULT NULL,
  `inserted_on` datetime DEFAULT current_timestamp(),
  `inserted_by` bigint(20) DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `updated_by` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_transactions`
--

INSERT INTO `payment_transactions` (`id`, `transaction_uuid`, `invoice_id`, `payment_gateway_id`, `gateway_code`, `gateway_transaction_id`, `gateway_payment_id`, `gateway_order_id`, `gateway_subscription_id`, `amount`, `fee_amount`, `net_amount`, `currency_code`, `exchange_rate`, `txn_type`, `status`, `failure_reason`, `payment_method`, `card_brand`, `card_last4`, `card_exp_month`, `card_exp_year`, `bank_name`, `wallet_name`, `payer_email`, `payer_name`, `payer_phone`, `gateway_response`, `webhook_payload`, `ip_address`, `user_agent`, `metadata`, `initiated_at`, `completed_at`, `refunded_at`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`) VALUES
(9, '8f659de9-e9d7-4aec-b1ff-ad8349328fab', 561, 2, 'stripe', 'pi_3T8GPXQ1EoHKEhCU0sqybXnR', NULL, 'pi_3T8GPXQ1EoHKEhCU0sqybXnR', NULL, 12.95, 0.00, 12.95, 'USD', 1.000000, 'payment', 'completed', NULL, NULL, 'visa', '4242', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"id\":\"pi_3T8GPXQ1EoHKEhCU0sqybXnR\",\"object\":\"payment_intent\",\"amount\":1295,\"amount_capturable\":0,\"amount_details\":{\"tip\":[]},\"amount_received\":1295,\"application\":null,\"application_fee_amount\":null,\"automatic_payment_methods\":{\"allow_redirects\":\"always\",\"enabled\":true},\"canceled_at\":null,\"cancellation_reason\":null,\"capture_method\":\"automatic\",\"client_secret\":\"pi_3T8GPXQ1EoHKEhCU0sqybXnR_secret_mkA45tMnsQBq89M9ruatYmQco\",\"confirmation_method\":\"automatic\",\"created\":1772872835,\"currency\":\"usd\",\"customer\":null,\"customer_account\":null,\"description\":null,\"excluded_payment_method_types\":null,\"invoice\":null,\"last_payment_error\":null,\"latest_charge\":{\"id\":\"ch_3T8GPXQ1EoHKEhCU0Y7wFiT1\",\"object\":\"charge\",\"amount\":1295,\"amount_captured\":1295,\"amount_refunded\":0,\"application\":null,\"application_fee\":null,\"application_fee_amount\":null,\"balance_transaction\":\"txn_3T8GPXQ1EoHKEhCU0AmaUpDF\",\"billing_details\":{\"address\":{\"city\":null,\"country\":null,\"line1\":null,\"line2\":null,\"postal_code\":\"90254\",\"state\":null},\"email\":null,\"name\":null,\"phone\":null,\"tax_id\":null},\"calculated_statement_descriptor\":\"Stripe\",\"captured\":true,\"created\":1772872835,\"currency\":\"usd\",\"customer\":null,\"description\":null,\"destination\":null,\"dispute\":null,\"disputed\":false,\"failure_balance_transaction\":null,\"failure_code\":null,\"failure_message\":null,\"fraud_details\":[],\"invoice\":null,\"livemode\":false,\"metadata\":{\"invoice_id\":\"561\",\"invoice_uuid\":\"49af90f2-d430-4e60-b6b4-1704bcae8cfd\",\"transaction_id\":\"9\"},\"on_behalf_of\":null,\"order\":null,\"outcome\":{\"advice_code\":null,\"network_advice_code\":null,\"network_decline_code\":null,\"network_status\":\"approved_by_network\",\"reason\":null,\"risk_level\":\"normal\",\"risk_score\":23,\"seller_message\":\"Payment complete.\",\"type\":\"authorized\"},\"paid\":true,\"payment_intent\":\"pi_3T8GPXQ1EoHKEhCU0sqybXnR\",\"payment_method\":\"pm_1T8GPXQ1EoHKEhCURJDPwzBx\",\"payment_method_details\":{\"card\":{\"amount_authorized\":1295,\"authorization_code\":\"502000\",\"brand\":\"visa\",\"checks\":{\"address_line1_check\":null,\"address_postal_code_check\":\"pass\",\"cvc_check\":\"pass\"},\"country\":\"US\",\"exp_month\":6,\"exp_year\":2028,\"extended_authorization\":{\"status\":\"disabled\"},\"fingerprint\":\"M0ZBagDykUho10VO\",\"funding\":\"credit\",\"incremental_authorization\":{\"status\":\"unavailable\"},\"installments\":null,\"last4\":\"4242\",\"mandate\":null,\"multicapture\":{\"status\":\"unavailable\"},\"network\":\"visa\",\"network_token\":{\"used\":false},\"network_transaction_id\":\"774890669710368\",\"overcapture\":{\"maximum_amount_capturable\":1295,\"status\":\"unavailable\"},\"regulated_status\":\"unregulated\",\"three_d_secure\":null,\"wallet\":null},\"type\":\"card\"},\"radar_options\":[],\"receipt_email\":null,\"receipt_number\":null,\"receipt_url\":\"https:\\/\\/pay.stripe.com\\/receipts\\/payment\\/CAcaFwoVYWNjdF8xVDBaN3ZRMUVvSEtFaENVKIXJr80GMgbavnGW7i06LBaQPm0p8yrH97np-xg4xeVlHjiPR3w1zRAPMLkz-hHYr3z0sr8I-EDdTXXv\",\"refunded\":false,\"review\":null,\"shipping\":null,\"source\":null,\"source_transfer\":null,\"statement_descriptor\":null,\"statement_descriptor_suffix\":null,\"status\":\"succeeded\",\"transfer_data\":null,\"transfer_group\":null},\"livemode\":false,\"metadata\":{\"invoice_id\":\"561\",\"invoice_uuid\":\"49af90f2-d430-4e60-b6b4-1704bcae8cfd\",\"transaction_id\":\"9\"},\"next_action\":null,\"on_behalf_of\":null,\"payment_method\":\"pm_1T8GPXQ1EoHKEhCURJDPwzBx\",\"payment_method_configuration_details\":{\"id\":\"pmc_1T0Z8RQ1EoHKEhCUitfkyb1e\",\"parent\":null},\"payment_method_options\":{\"card\":{\"installments\":null,\"mandate_options\":null,\"network\":null,\"request_three_d_secure\":\"automatic\"},\"link\":{\"persistent_token\":null}},\"payment_method_types\":[\"card\",\"link\"],\"processing\":null,\"receipt_email\":null,\"review\":null,\"setup_future_usage\":null,\"shipping\":null,\"source\":null,\"statement_descriptor\":null,\"statement_descriptor_suffix\":null,\"status\":\"succeeded\",\"transfer_data\":null,\"transfer_group\":null}', NULL, '103.159.72.16', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"invoice_no\":\"1543\",\"company_id\":\"1\"}', '2026-03-07 02:40:34', '2026-03-07 02:40:37', NULL, '2026-03-07 02:40:34', 1, '2026-03-07 02:40:37', NULL),
(10, 'f841da8d-deae-4fb2-8a74-b06331ed455b', 565, 6, 'sslcommerz', '2607031404382PrrxBHgpewcu8W', NULL, 'EF932EAE35B0D5DED35309E371873486', NULL, 10.00, 0.00, 10.00, 'USD', 1.000000, 'payment', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"status\":\"VALID\",\"tran_date\":\"2026-07-03 14:04:25\",\"tran_id\":\"f841da8d-deae-4fb2-8a74-b06331ed455b\",\"val_id\":\"260703140438MRhWc3khFLkgUc4\",\"amount\":\"831.85\",\"store_amount\":\"811.05\",\"currency\":\"BDT\",\"bank_tran_id\":\"2607031404382PrrxBHgpewcu8W\",\"card_type\":\"BKASH-BKash\",\"card_no\":\"\",\"card_issuer\":\"BKash Mobile Banking\",\"card_brand\":\"MOBILEBANKING\",\"card_category\":\"MOBILE\",\"card_sub_brand\":\"\",\"card_issuer_country\":\"Bangladesh\",\"card_issuer_country_code\":\"BD\",\"currency_type\":\"USD\",\"currency_amount\":\"10.00\",\"currency_rate\":\"83.1850\",\"base_fair\":\"0.00\",\"value_a\":\"f841da8d-deae-4fb2-8a74-b06331ed455b\",\"value_b\":\"e05eeee6-d48c-4d2c-af42-502cebeceb15\",\"value_c\":\"8cf2304fedc88591cbdfad1078f4167f2026e77aa2e32056ef63bd9287d3b518\",\"value_d\":\"\",\"emi_instalment\":\"0\",\"emi_amount\":\"0.00\",\"emi_description\":\"\",\"emi_issuer\":\"BKash Mobile Banking\",\"account_details\":\"\",\"risk_title\":\"Safe\",\"risk_level\":\"0\",\"discount_percentage\":\"0\",\"discount_amount\":\"0.00\",\"discount_remarks\":\"\",\"APIConnect\":\"DONE\",\"validated_on\":\"2026-07-03 14:04:46\",\"gw_version\":\"\",\"offer_avail\":1,\"card_ref_id\":\"dc1da4f52669828139e81ef5eb0f48a5a99ea054a131e00a562887d455417dd914\",\"isTokeizeSuccess\":0,\"campaign_code\":\"\"}', NULL, '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', '{\"invoice_no\":\"1547\",\"company_id\":\"1\",\"user_id\":\"1\",\"payment_token\":\"8cf2304fedc88591cbdfad1078f4167f2026e77aa2e32056ef63bd9287d3b518\"}', '2026-07-03 10:04:23', '2026-07-03 10:04:47', NULL, '2026-07-03 10:04:23', 1, '2026-07-03 10:04:47', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pending_executions`
--

CREATE TABLE `pending_executions` (
  `id` bigint(20) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `invoice_id` bigint(20) NOT NULL,
  `exe_type` varchar(12) NOT NULL COMMENT 'REGISTER,TRANSFER,RENEW,SUSPEND,TERMINATE,UNSUSPEND',
  `service` varchar(10) NOT NULL COMMENT 'DOMAIN,HOSTING',
  `is_completed` tinyint(4) NOT NULL DEFAULT 0,
  `completed_on` datetime DEFAULT NULL,
  `inserted_on` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `plans`
--

CREATE TABLE `plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `plan_key` varchar(32) NOT NULL,
  `family_group` varchar(64) DEFAULT NULL COMMENT 'Products sharing this value are upgrade tiers of one software',
  `name` varchar(60) NOT NULL,
  `tagline` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL COMMENT 'Full product description (HTML)',
  `current_release_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'software_releases.id shipped for this product',
  `is_popular` tinyint(4) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  `paddle_product_id` varchar(100) DEFAULT NULL,
  `paddle_price_monthly_id` varchar(100) DEFAULT NULL,
  `paddle_price_annual_id` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plans`
--

INSERT INTO `plans` (`id`, `plan_key`, `family_group`, `name`, `tagline`, `description`, `current_release_id`, `is_popular`, `sort_order`, `is_active`, `paddle_product_id`, `paddle_price_monthly_id`, `paddle_price_annual_id`, `created_at`, `updated_at`) VALUES
(5, 'tong_bari_product', NULL, 'TongBari Product', 'TEST, Product, TongBari', 'Test description', NULL, 1, 1, 1, NULL, NULL, NULL, '2026-07-03 08:12:23', '2026-07-03 08:14:35'),
(8, 'whmaz-basic', 'whmaz', 'Basic', 'For new & small hosts', NULL, NULL, 0, 1, 1, NULL, NULL, NULL, '2026-07-03 08:30:55', '2026-07-03 08:30:55'),
(9, 'whmaz-pro', 'whmaz', 'Pro', 'For growing hosts', NULL, NULL, 1, 2, 1, NULL, NULL, NULL, '2026-07-03 08:30:55', '2026-07-03 08:30:55'),
(10, 'whmaz-max', 'whmaz', 'Max', 'For established hosts', NULL, NULL, 0, 3, 1, NULL, NULL, NULL, '2026-07-03 08:30:55', '2026-07-03 08:30:55');

-- --------------------------------------------------------

--
-- Table structure for table `plan_features`
--

CREATE TABLE `plan_features` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `plan_id` bigint(20) UNSIGNED NOT NULL,
  `feature_key` varchar(64) NOT NULL,
  `feature_value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plan_features`
--

INSERT INTO `plan_features` (`id`, `plan_id`, `feature_key`, `feature_value`) VALUES
(24, 5, 'support_response_hours', '1'),
(25, 5, 'Reseller_Management', '1'),
(26, 8, 'support_response_hours', '72'),
(27, 8, 'priority_support', '0'),
(28, 8, 'advanced_modules', '0'),
(29, 8, 'automatic_updates', '0'),
(30, 8, 'branding_removal', '0'),
(31, 8, 'domain_registration_transfers', '1'),
(32, 8, 'dns_management', '1'),
(33, 8, 'software_license_selling', '0'),
(34, 8, 'reseller_management', '0'),
(35, 8, 'api_expose_for_third_party', '0'),
(36, 9, 'support_response_hours', '48'),
(37, 9, 'priority_support', '1'),
(38, 9, 'advanced_modules', '1'),
(39, 9, 'automatic_updates', '1'),
(40, 9, 'branding_removal', '1'),
(41, 9, 'domain_registration_transfers', '1'),
(42, 9, 'dns_management', '1'),
(43, 9, 'software_license_selling', '1'),
(44, 9, 'reseller_management', '0'),
(45, 9, 'api_expose_for_third_party', '0'),
(46, 10, 'support_response_hours', '24'),
(47, 10, 'priority_support', '1'),
(48, 10, 'advanced_modules', '1'),
(49, 10, 'automatic_updates', '1'),
(50, 10, 'branding_removal', '1'),
(51, 10, 'domain_registration_transfers', '1'),
(52, 10, 'dns_management', '1'),
(53, 10, 'software_license_selling', '1'),
(54, 10, 'reseller_management', '1'),
(55, 10, 'api_expose_for_third_party', '1');

-- --------------------------------------------------------

--
-- Table structure for table `product_services`
--

CREATE TABLE `product_services` (
  `id` int(11) NOT NULL,
  `product_service_group_id` int(11) NOT NULL,
  `server_id` int(11) DEFAULT NULL,
  `product_service_module_id` int(11) NOT NULL,
  `product_service_type_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_desc` text DEFAULT NULL,
  `is_hidden` tinyint(4) NOT NULL DEFAULT 0 COMMENT '1=hidden, 0=not hidden',
  `pricing_type` varchar(10) NOT NULL DEFAULT 'recurring',
  `cp_package` varchar(150) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_services`
--

INSERT INTO `product_services` (`id`, `product_service_group_id`, `server_id`, `product_service_module_id`, `product_service_type_id`, `product_name`, `product_desc`, `is_hidden`, `pricing_type`, `cp_package`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(1, 1, 1, 2, 1, '1GB_SHARED', '<ul>\r\n<li><strong>whmazc_1GB_SHARED</strong></li>\r\n<li>1.0 GB Disk Space</li>\r\n<li>9.8 GB Bandwidth</li>\r\n<li>5 Addon Domains</li>\r\n<li>5 Parked Domains</li>\r\n<li>Unlimited Subdomains</li>\r\n<li>Unlimited FTP Accounts</li>\r\n<li>Unlimited MySQL Databases</li>\r\n<li>Unlimited Email Accounts</li>\r\n<li>Unlimited Mailing Lists</li>\r\n<li>No Shell</li>\r\n<li>CGI Access</li>\r\n</ul>', 0, 'recurring', 'whmazc_1GB_SHARED', 1, NULL, NULL, '2026-01-27 22:02:15', 1, NULL, NULL),
(2, 1, 1, 2, 1, '2GB_SHARED', '<ul>\r\n<li><strong>whmazc_2GB_SHARED</strong></li>\r\n<li>2.0 GB Disk Space</li>\r\n<li>19.5 GB Bandwidth</li>\r\n<li>5 Addon Domains</li>\r\n<li>5 Parked Domains</li>\r\n<li>Unlimited Subdomains</li>\r\n<li>Unlimited FTP Accounts</li>\r\n<li>Unlimited MySQL Databases</li>\r\n<li>Unlimited Email Accounts</li>\r\n<li>Unlimited Mailing Lists</li>\r\n<li>No Shell</li>\r\n<li>CGI Access</li>\r\n</ul>', 0, 'recurring', 'whmazc_2GB_SHARED', 1, NULL, NULL, '2026-01-27 22:01:03', 1, NULL, NULL),
(3, 1, 1, 2, 1, '3GB_SHARED', '<ul>\r\n<li><strong>whmazc_3GB_SHARED</strong></li>\r\n<li>3.0 GB Disk Space</li>\r\n<li>Unlimited Bandwidth</li>\r\n<li>Unlimited Addon Domains</li>\r\n<li>Unlimited Parked Domains</li>\r\n<li>Unlimited Subdomains</li>\r\n<li>Unlimited FTP Accounts</li>\r\n<li>Unlimited MySQL Databases</li>\r\n<li>Unlimited Email Accounts</li>\r\n<li>Unlimited Mailing Lists</li>\r\n<li>No Shell</li>\r\n<li>CGI Access</li>\r\n</ul>', 0, 'recurring', 'whmazc_3GB_SHARED', 1, NULL, NULL, '2026-01-27 22:00:13', 1, NULL, NULL),
(4, 1, 1, 2, 1, '5GB_SHARED', '<ul>\r\n<li><strong>whmazc_5GB_SHARED</strong></li>\r\n<li>5.0 GB Disk Space</li>\r\n<li>Unlimited Bandwidth</li>\r\n<li>Unlimited Addon Domains</li>\r\n<li>Unlimited Parked Domains</li>\r\n<li>Unlimited Subdomains</li>\r\n<li>Unlimited FTP Accounts</li>\r\n<li>Unlimited MySQL Databases</li>\r\n<li>Unlimited Email Accounts</li>\r\n<li>Unlimited Mailing Lists</li>\r\n<li>No Shell</li>\r\n<li>CGI Access</li>\r\n</ul>', 0, 'recurring', 'whmazc_5GB_SHARED', 1, NULL, NULL, '2026-01-27 21:59:25', 1, NULL, NULL),
(5, 5, 1, 2, 1, 'Corp_3GB', '<ul>\r\n<li><strong>whmazc_3GB_SHARED</strong></li>\r\n<li>3.0 GB Disk Space</li>\r\n<li>Unlimited Bandwidth</li>\r\n<li>Unlimited Addon Domains</li>\r\n<li>Unlimited Parked Domains</li>\r\n<li>Unlimited Subdomains</li>\r\n<li>Unlimited FTP Accounts</li>\r\n<li>Unlimited MySQL Databases</li>\r\n<li>Unlimited Email Accounts</li>\r\n<li>Unlimited Mailing Lists</li>\r\n<li>No Shell</li>\r\n<li>CGI Access</li>\r\n</ul>', 0, 'recurring', 'whmazc_3GB_SHARED', 1, NULL, NULL, '2026-01-27 21:58:23', 1, NULL, NULL),
(6, 5, 1, 2, 1, 'Corp_5GB', '<ul>\r\n<li><strong>whmazc_5GB_SHARED</strong></li>\r\n<li>5.0 GB Disk Space</li>\r\n<li>Unlimited Bandwidth</li>\r\n<li>Unlimited Addon Domains</li>\r\n<li>Unlimited Parked Domains</li>\r\n<li>Unlimited Subdomains</li>\r\n<li>Unlimited FTP Accounts</li>\r\n<li>Unlimited MySQL Databases</li>\r\n<li>Unlimited Email Accounts</li>\r\n<li>Unlimited Mailing Lists</li>\r\n<li>No Shell</li>\r\n<li>CGI Access</li>\r\n</ul>', 0, 'recurring', 'whmazc_5GB_SHARED', 1, NULL, NULL, '2026-01-27 21:57:30', 1, NULL, NULL),
(7, 5, 1, 2, 1, 'Corp_10GB', '<ul>\r\n<li><strong>whmazc_10GB_SHARED</strong></li>\r\n<li>9.8 GB Disk Space</li>\r\n<li>Unlimited Bandwidth</li>\r\n<li>Unlimited Addon Domains</li>\r\n<li>Unlimited Parked Domains</li>\r\n<li>Unlimited Subdomains</li>\r\n<li>Unlimited FTP Accounts</li>\r\n<li>Unlimited MySQL Databases</li>\r\n<li>Unlimited Email Accounts</li>\r\n<li>Unlimited Mailing Lists</li>\r\n<li>No Shell</li>\r\n<li>CGI Access</li>\r\n</ul>', 0, 'recurring', 'whmazc_10GB_SHARED', 1, NULL, NULL, '2026-01-27 21:56:31', 1, NULL, NULL),
(11, 4, 3, 1, 3, 'uVPS-1', '<strong>200 GB of space</strong><br />\r\nunlimited bandwidth<br />\r\n8 GB RAM (guaranteed)<br />\r\n4 CPU cores<br />\r\nNo control panel<br />\r\n<strong>Full root access</strong><br />\r\nPure SSD, KVM', 0, 'recurring', NULL, 1, NULL, NULL, '2024-08-21 23:48:36', NULL, NULL, NULL),
(12, 4, 3, 1, 3, 'uVPS-2', '<strong>400 GB of space</strong><br />\r\nunlimited bandwidth<br />\r\n16 GB RAM (guaranteed)<br />\r\n6 CPU cores<br />\r\nNo control panel<br />\r\n<strong>Full root access</strong><br />\r\nPure SSD, KVM', 0, 'recurring', NULL, 1, NULL, NULL, '2024-08-21 23:48:39', NULL, NULL, NULL),
(13, 4, 3, 1, 3, 'uVPS-3', '<strong>800 GB of space</strong><br />\r\nunlimited bandwidth<br />\r\n30 GB RAM (guaranteed)<br />\r\n8 CPU cores<br />\r\nNo control panel<br />\r\n<strong>Full root access</strong><br />\r\nPure SSD, KVM', 0, 'recurring', NULL, 1, NULL, NULL, '2024-08-21 23:48:42', NULL, NULL, NULL),
(15, 2, 1, 2, 2, 'Small', '<strong>15GB disk space</strong><br />\r\n150 GB bandwidth per month<br />\r\n10 cPanel Accounts<br />\r\nUpto 50 Website point<br />\r\n<strong>No WHMCS</strong><br />\r\nUnlimited database<br />\r\nUnlimited ftp accounts<br />\r\n<strong>No Shell</strong>', 0, 'recurring', NULL, 1, NULL, NULL, '2022-10-09 10:53:19', NULL, NULL, NULL),
(16, 2, 1, 2, 2, 'Professional', '<strong>100 GB disk space</strong><br />\r\nUnlimited bandwidth<br />\r\n50 Accounts<br />\r\n<strong>WHMCS</strong><br />\r\n<strong>Shell Access</strong>', 0, 'recurring', NULL, 1, NULL, NULL, '2022-10-09 10:53:19', NULL, NULL, NULL),
(17, 2, 1, 2, 2, 'Starter', '<strong>50 GB disk space</strong><br />\r\nUnlimited bandwidth<br />\r\n30 Accounts<br />\r\n<strong>WHMCS</strong><br />\r\n<strong>No Shell</strong>', 0, 'recurring', NULL, 1, NULL, NULL, '2022-10-09 10:53:19', NULL, NULL, NULL),
(18, 2, 1, 2, 2, 'Basic', '<strong>25 GB disk space</strong><br />\r\nUnlimited bandwidth<br />\r\n20 Accounts<br />\r\n<strong>No WHMCS</strong><br />\r\n<strong>No Shell</strong>', 0, 'recurring', NULL, 1, NULL, NULL, '2022-10-09 10:53:19', NULL, NULL, NULL),
(19, 1, 1, 2, 1, '6GB_SHARED', '<ul>\r\n<li><strong>whmazc_6GB_SHARED</strong></li>\r\n<li>6.0 GB Disk Space</li>\r\n<li>Unlimited Bandwidth</li>\r\n<li>Unlimited Addon Domains</li>\r\n<li>Unlimited Parked Domains</li>\r\n<li>Unlimited Subdomains</li>\r\n<li>Unlimited FTP Accounts</li>\r\n<li>Unlimited MySQL Databases</li>\r\n<li>Unlimited Email Accounts</li>\r\n<li>Unlimited Mailing Lists</li>\r\n<li>No Shell</li>\r\n<li>CGI Access</li>\r\n</ul>', 0, 'recurring', 'whmazc_6GB_SHARED', 1, NULL, NULL, '2026-01-27 21:54:54', 1, NULL, NULL),
(20, 1, 1, 2, 1, '10GB_SHARED', '<ul>\r\n<li><strong>whmazc_10GB_SHARED</strong></li>\r\n<li>9.8 GB Disk Space</li>\r\n<li>Unlimited Bandwidth</li>\r\n<li>Unlimited Addon Domains</li>\r\n<li>Unlimited Parked Domains</li>\r\n<li>Unlimited Subdomains</li>\r\n<li>Unlimited FTP Accounts</li>\r\n<li>Unlimited MySQL Databases</li>\r\n<li>Unlimited Email Accounts</li>\r\n<li>Unlimited Mailing Lists</li>\r\n<li>No Shell</li>\r\n<li>CGI Access</li>\r\n</ul>', 0, 'recurring', '', 1, NULL, NULL, '2026-03-15 20:49:04', 1, NULL, NULL),
(21, 5, 3, 1, 6, 'One time Package', 'One time Package', 0, 'onetime', '', 1, '2026-03-15 16:09:51', 1, '2026-03-15 21:18:37', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_service_groups`
--

CREATE TABLE `product_service_groups` (
  `id` int(11) NOT NULL,
  `product_service_type_id` int(11) NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `group_headline` varchar(255) NOT NULL,
  `tags` varchar(255) DEFAULT NULL COMMENT 'comma separate',
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_service_groups`
--

INSERT INTO `product_service_groups` (`id`, `product_service_type_id`, `group_name`, `group_headline`, `tags`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(1, 1, 'Shared Hosting', 'Find best shared hosting in Asia', 'shared, hosting', 1, NULL, NULL, '2024-08-07 15:42:49', NULL, NULL, NULL),
(2, 2, 'Reseller Hosting', 'Start your business with us', 'Reseller, Hosting, Reseller Hosting', 1, '2024-08-03 07:02:11', 1, '2024-08-07 15:42:52', NULL, NULL, NULL),
(3, 3, 'Managed VPS', 'Enlarge your business with us', 'Server,VPS', 1, NULL, NULL, '2024-08-07 15:42:55', NULL, '2024-08-03 05:00:47', 1),
(4, 3, 'Un-managed VPS', 'Other products', 'Other, products', 1, NULL, NULL, '2024-08-07 15:42:57', NULL, '2024-08-03 05:13:02', 1),
(5, 1, 'Corporate Hosting', 'Best hosting packages for your business', 'corporate, business, hosting', 1, NULL, NULL, '2024-08-21 23:54:18', 1, '2024-08-03 07:10:43', 1);

-- --------------------------------------------------------

--
-- Table structure for table `product_service_pricing`
--

CREATE TABLE `product_service_pricing` (
  `id` int(11) NOT NULL,
  `product_service_id` int(11) NOT NULL,
  `currency_id` int(11) NOT NULL,
  `price` float NOT NULL,
  `billing_cycle_id` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_service_pricing`
--

INSERT INTO `product_service_pricing` (`id`, `product_service_id`, `currency_id`, `price`, `billing_cycle_id`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(1, 1, 1, 10.15, 4, 1, '2020-09-05 08:17:52', NULL, '2022-10-09 03:53:33', NULL, NULL, NULL),
(2, 1, 2, 850, 4, 1, '2020-09-05 08:17:52', NULL, '2022-10-09 03:53:36', NULL, NULL, NULL),
(3, 2, 1, 20.15, 4, 1, '2020-09-05 08:17:52', NULL, '2024-11-22 15:33:07', NULL, NULL, NULL),
(4, 2, 2, 1700, 4, 1, '2020-09-05 08:17:52', NULL, '2024-11-22 15:32:48', NULL, NULL, NULL),
(5, 3, 1, 25.95, 4, 1, '2020-09-05 08:17:52', NULL, '2024-11-22 15:33:52', NULL, NULL, NULL),
(6, 3, 2, 3200, 4, 1, '2020-09-05 08:17:52', NULL, '2024-11-22 15:33:29', NULL, NULL, NULL),
(7, 4, 1, 32.95, 4, 1, '2020-09-05 08:17:52', NULL, '2024-11-22 15:34:57', NULL, NULL, NULL),
(8, 4, 2, 3999, 4, 1, '2020-09-05 08:17:52', NULL, '2024-11-22 15:34:22', NULL, NULL, NULL),
(9, 5, 1, 37.95, 4, 1, '2020-09-05 08:17:52', NULL, '2022-10-09 03:53:48', NULL, NULL, NULL),
(10, 5, 2, 4250, 4, 1, '2020-09-05 08:17:52', NULL, '2022-10-09 03:53:51', NULL, NULL, NULL),
(11, 6, 1, 42.95, 4, 1, '2020-09-05 08:17:52', NULL, '2024-11-21 15:30:33', NULL, NULL, NULL),
(12, 6, 2, 5299, 4, 1, '2020-09-05 08:17:52', NULL, '2024-11-21 15:30:50', NULL, NULL, NULL),
(13, 7, 1, 72.95, 4, 1, '2020-09-05 08:17:52', NULL, '2024-11-21 15:29:33', NULL, NULL, NULL),
(14, 7, 2, 8999, 4, 1, '2020-09-05 08:17:52', NULL, '2024-11-21 15:30:12', NULL, NULL, NULL),
(15, 15, 1, 73, 4, 1, '2020-09-05 08:17:52', NULL, '2024-08-21 23:49:04', NULL, NULL, NULL),
(16, 15, 2, 6500, 4, 1, '2020-09-05 08:17:52', NULL, '2024-08-21 23:49:07', NULL, NULL, NULL),
(17, 16, 1, 970, 4, 1, '2020-09-05 08:17:52', NULL, '2024-08-21 23:49:04', NULL, NULL, NULL),
(18, 16, 2, 85500, 4, 1, '2020-09-05 08:17:52', NULL, '2024-08-21 23:49:07', NULL, NULL, NULL),
(19, 17, 1, 340, 4, 1, '2020-09-05 08:17:52', NULL, '2024-08-21 23:49:04', NULL, NULL, NULL),
(20, 17, 2, 30100, 4, 1, '2020-09-05 08:17:52', NULL, '2024-08-21 23:49:07', NULL, NULL, NULL),
(21, 18, 1, 130, 4, 1, '2020-09-05 08:17:52', NULL, '2024-08-21 23:49:04', NULL, NULL, NULL),
(22, 18, 2, 11500, 4, 1, '2020-09-05 08:17:52', NULL, '2024-08-21 23:49:07', NULL, NULL, NULL),
(23, 11, 1, 69.5, 4, 1, '2020-09-05 08:17:52', NULL, '2024-08-21 23:49:04', NULL, NULL, NULL),
(24, 11, 2, 8800, 4, 1, '2020-09-05 08:17:52', NULL, '2024-08-21 23:49:04', NULL, NULL, NULL),
(25, 19, 1, 38, 4, 1, '2020-09-05 08:17:52', NULL, '2025-05-03 03:56:53', NULL, NULL, NULL),
(26, 19, 2, 4788, 4, 1, '2020-09-05 08:17:52', NULL, '2024-11-22 15:34:22', NULL, NULL, NULL),
(27, 20, 1, 65, 4, 1, '2020-09-05 08:17:52', NULL, '2026-03-15 20:49:05', 1, NULL, NULL),
(28, 20, 2, 8190, 4, 1, '2020-09-05 08:17:52', NULL, '2026-03-15 20:49:07', 1, NULL, NULL),
(29, 20, 1, 13, 1, 1, '2026-03-15 15:49:04', 1, '2026-03-15 14:49:03', NULL, NULL, NULL),
(31, 20, 2, 1651, 1, 1, '2026-03-15 15:49:06', 1, '2026-03-15 14:49:05', NULL, NULL, NULL),
(37, 21, 1, 10, 5, 1, '2026-03-15 16:18:39', 1, '2026-03-15 15:18:38', NULL, NULL, NULL),
(38, 21, 2, 1270, 5, 1, '2026-03-15 16:18:39', 1, '2026-03-15 15:18:38', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_service_types`
--

CREATE TABLE `product_service_types` (
  `id` int(11) NOT NULL,
  `servce_type_name` varchar(150) NOT NULL,
  `key_name` varchar(150) NOT NULL,
  `remarks` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_service_types`
--

INSERT INTO `product_service_types` (`id`, `servce_type_name`, `key_name`, `remarks`, `sort_order`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(1, 'Shared Hosting', 'SHARED_HOSTING', NULL, 1, 1, '2020-08-20 20:26:51', NULL, '2024-08-07 15:36:09', NULL, NULL, NULL),
(2, 'Reseller Hosting', 'RESELLER_HOSTING', NULL, 2, 1, '2020-08-20 20:26:51', NULL, '2024-08-07 15:36:15', NULL, NULL, NULL),
(3, 'Server/VPS', 'SERVER_VPS', NULL, 3, 1, '2020-08-20 20:26:51', NULL, '2024-08-07 15:36:23', NULL, NULL, NULL),
(4, 'Other', 'OTHER', 'Other', 8, 1, '2020-08-20 20:26:51', NULL, '2024-08-07 15:36:02', 1, NULL, NULL),
(6, 'Software License', 'SOFTWARE_LICENSE', 'Software License', 4, 1, '2024-08-07 17:44:19', 1, '2024-08-07 15:44:19', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `product_service_view`
-- (See below for the actual view)
--
CREATE TABLE `product_service_view` (
`id` int(11)
,`product_service_group_id` int(11)
,`server_id` int(11)
,`product_service_module_id` int(11)
,`product_service_type_id` int(11)
,`product_name` varchar(255)
,`product_desc` text
,`is_hidden` tinyint(4)
,`cp_package` varchar(150)
,`status` tinyint(4)
,`updated_on` timestamp
,`group_name` varchar(100)
,`group_headline` varchar(255)
,`server_name` varchar(255)
,`server_hostname` varchar(160)
,`server_ip` varchar(160)
,`module_name` varchar(100)
,`servce_type_name` varchar(150)
);

-- --------------------------------------------------------

--
-- Table structure for table `promo_codes`
--

CREATE TABLE `promo_codes` (
  `id` bigint(20) NOT NULL,
  `code` varchar(32) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_type` enum('fixed','percentage') NOT NULL DEFAULT 'fixed',
  `discount_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `currency_id` int(11) DEFAULT NULL COMMENT 'Required for fixed type only',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_lifetime` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=no expiry',
  `max_uses` int(11) NOT NULL DEFAULT 0 COMMENT '0=unlimited',
  `max_uses_per_customer` int(11) NOT NULL DEFAULT 0 COMMENT '0=unlimited',
  `min_order_amount` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT '0=no minimum',
  `max_discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Caps percentage discounts; 0=no cap',
  `applies_to` enum('all','products','customers') NOT NULL DEFAULT 'all',
  `total_used` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1=active, 0=soft deleted',
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promo_codes`
--

INSERT INTO `promo_codes` (`id`, `code`, `description`, `discount_type`, `discount_value`, `currency_id`, `start_date`, `end_date`, `is_lifetime`, `max_uses`, `max_uses_per_customer`, `min_order_amount`, `max_discount_amount`, `applies_to`, `total_used`, `is_active`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(1, 'XE983SVP', 'testing', 'fixed', 2.00, 1, NULL, NULL, 1, 1, 1, 10.00, 0.00, 'all', 1, 1, 1, '2026-03-17 15:36:04', 1, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `promo_code_customers`
--

CREATE TABLE `promo_code_customers` (
  `id` bigint(20) NOT NULL,
  `promo_code_id` bigint(20) NOT NULL,
  `company_id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promo_code_products`
--

CREATE TABLE `promo_code_products` (
  `id` bigint(20) NOT NULL,
  `promo_code_id` bigint(20) NOT NULL,
  `product_service_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promo_code_products`
--

INSERT INTO `promo_code_products` (`id`, `promo_code_id`, `product_service_id`) VALUES
(1, 1, 16);

-- --------------------------------------------------------

--
-- Table structure for table `promo_code_usage`
--

CREATE TABLE `promo_code_usage` (
  `id` bigint(20) NOT NULL,
  `promo_code_id` bigint(20) NOT NULL,
  `company_id` bigint(20) NOT NULL,
  `order_id` bigint(20) NOT NULL,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `used_on` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promo_code_usage`
--

INSERT INTO `promo_code_usage` (`id`, `promo_code_id`, `company_id`, `order_id`, `discount_amount`, `used_on`) VALUES
(1, 1, 1, 736, 2.00, '2026-03-17 15:42:40');

-- --------------------------------------------------------

--
-- Table structure for table `provisioning_logs`
--

CREATE TABLE `provisioning_logs` (
  `id` bigint(20) NOT NULL,
  `invoice_id` bigint(20) NOT NULL,
  `invoice_item_id` bigint(20) NOT NULL,
  `item_type` tinyint(4) NOT NULL COMMENT '1=domain, 2=product_service',
  `ref_id` bigint(20) NOT NULL COMMENT 'FK to order_domains or order_services',
  `action` varchar(50) NOT NULL COMMENT 'register, transfer, renew, create, unsuspend, etc.',
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `error_message` text DEFAULT NULL,
  `response_data` text DEFAULT NULL COMMENT 'JSON response from API',
  `retry_count` int(11) NOT NULL DEFAULT 0,
  `inserted_on` datetime NOT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `provisioning_logs`
--

INSERT INTO `provisioning_logs` (`id`, `invoice_id`, `invoice_item_id`, `item_type`, `ref_id`, `action`, `success`, `error_message`, `response_data`, `retry_count`, `inserted_on`, `updated_on`) VALUES
(8, 561, 579, 1, 731, 'register', 1, NULL, '{\"success\":true,\"action\":\"register\",\"order_id\":\"1106489\",\"error\":null}', 0, '2026-03-07 02:40:43', '2026-03-07 08:40:43'),
(11, 565, 583, 3, 2, 'activate', 1, '', '{\"success\":true,\"action\":\"activate\",\"error\":\"\"}', 0, '2026-07-03 10:04:52', '2026-07-03 08:04:52');

-- --------------------------------------------------------

--
-- Table structure for table `servers`
--

CREATE TABLE `servers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `ip_addr` varchar(160) NOT NULL,
  `hostname` varchar(160) NOT NULL,
  `dns1` varchar(160) DEFAULT NULL,
  `dns1_ip` varchar(160) DEFAULT NULL,
  `dns2` varchar(160) DEFAULT NULL,
  `dns2_ip` varchar(160) DEFAULT NULL,
  `dns3` varchar(160) DEFAULT NULL,
  `dns3_ip` varchar(160) DEFAULT NULL,
  `dns4` varchar(160) DEFAULT NULL,
  `dns4_ip` varchar(160) DEFAULT NULL,
  `max_accounts` int(11) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `product_service_module_id` int(11) DEFAULT NULL,
  `username` text DEFAULT NULL,
  `authpass` text DEFAULT NULL,
  `access_hash` text DEFAULT NULL,
  `port` int(11) DEFAULT NULL,
  `is_secure` tinyint(4) DEFAULT NULL,
  `noc` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `servers`
--

INSERT INTO `servers` (`id`, `name`, `ip_addr`, `hostname`, `dns1`, `dns1_ip`, `dns2`, `dns2_ip`, `dns3`, `dns3_ip`, `dns4`, `dns4_ip`, `max_accounts`, `type`, `product_service_module_id`, `username`, `authpass`, `access_hash`, `port`, `is_secure`, `noc`, `remarks`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(1, 'Reseller Server', '170.249.236.236', 'server.resellerdemo.net', 'cp1.resellerdemo.net', '158.106.136.90', 'cp2.resellerdemo.net', '108.160.157.7', 'cp3.resellerdemo.net', '158.106.136.36', 'cp4.resellerdemo.net', '158.106.136.90', NULL, NULL, 2, 'enamingo', NULL, '.', 2087, 1, NULL, 'reseller hosting server', 1, '2024-08-09 16:06:20', 1, '2026-03-17 10:50:31', 1, '2026-02-12 09:19:47', 1),
(3, 'Demo Server', '102.103.104.105', 'server.demo.com', 'ns1.demo.com', '', 'ns2.demo.com', '', '', '', '', '', NULL, NULL, 2, 'demoserver', NULL, 'demoserverdemoserver12312313', 2087, 1, NULL, 'demoserver', 1, '2026-03-07 00:27:40', 1, '2026-03-17 10:48:46', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `server_modules`
--

CREATE TABLE `server_modules` (
  `id` int(11) NOT NULL,
  `module_name` varchar(100) NOT NULL,
  `remarks` text DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `server_modules`
--

INSERT INTO `server_modules` (`id`, `module_name`, `remarks`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`) VALUES
(1, 'No Module', 'No modules', 1, '2020-08-20 20:26:51', 1, '2026-03-17 06:19:50', 1),
(2, 'cPanel', 'cPanel server', 1, '2020-08-20 20:26:51', 1, '2026-03-17 06:20:01', 1),
(3, 'Plesk', 'Plesk server', 1, '2026-03-17 00:10:15', 1, '2026-03-17 06:20:01', 1),
(4, 'DirectAdmin', 'DirectAdmin server', 1, '2026-03-17 00:37:25', 1, '2026-03-17 06:32:04', 1);

-- --------------------------------------------------------

--
-- Table structure for table `software_pricing`
--

CREATE TABLE `software_pricing` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL COMMENT 'plans.id',
  `currency_id` int(11) NOT NULL,
  `billing_cycle_id` int(11) NOT NULL,
  `first_pay_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `recurring_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `software_pricing`
--

INSERT INTO `software_pricing` (`id`, `product_id`, `currency_id`, `billing_cycle_id`, `first_pay_amount`, `recurring_amount`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(1, 5, 1, 1, 10.00, 10.00, 1, '2026-07-03 08:12:23', 1, '2026-07-03 13:14:36', 1, NULL, NULL),
(2, 5, 1, 4, 110.00, 110.00, 1, '2026-07-03 08:12:23', 1, '2026-07-03 13:14:36', 1, NULL, NULL),
(3, 5, 2, 1, 3500.00, 3500.00, 1, '2026-07-03 08:12:23', 1, '2026-07-03 13:14:36', 1, NULL, NULL),
(4, 5, 2, 4, 36000.00, 36000.00, 1, '2026-07-03 08:12:23', 1, '2026-07-03 13:14:36', 1, NULL, NULL),
(11, 8, 1, 1, 11.95, 11.95, 1, '2026-07-03 08:31:18', NULL, '2026-07-03 13:31:18', NULL, NULL, NULL),
(12, 8, 1, 4, 131.95, 131.95, 1, '2026-07-03 08:31:18', NULL, '2026-07-03 13:31:18', NULL, NULL, NULL),
(13, 9, 1, 1, 16.95, 16.95, 1, '2026-07-03 08:31:18', NULL, '2026-07-03 13:31:18', NULL, NULL, NULL),
(14, 9, 1, 4, 191.95, 191.95, 1, '2026-07-03 08:31:18', NULL, '2026-07-03 13:31:18', NULL, NULL, NULL),
(15, 10, 1, 1, 25.95, 25.95, 1, '2026-07-03 08:31:18', NULL, '2026-07-03 13:31:18', NULL, NULL, NULL),
(16, 10, 1, 4, 291.49, 291.49, 1, '2026-07-03 08:31:18', NULL, '2026-07-03 13:31:18', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `software_releases`
--

CREATE TABLE `software_releases` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'plans.id this release belongs to (NULL = global/legacy)',
  `version` varchar(40) NOT NULL,
  `file_name` varchar(255) NOT NULL COMMENT 'Stored (random) filename in uploadedfiles/software',
  `original_name` varchar(255) DEFAULT NULL COMMENT 'Original uploaded filename',
  `file_size` bigint(20) DEFAULT NULL COMMENT 'Bytes',
  `changelog` text DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'The release customers download',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1=active, 0=deleted',
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_on` datetime DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `software_releases`
--

INSERT INTO `software_releases` (`id`, `product_id`, `version`, `file_name`, `original_name`, `file_size`, `changelog`, `is_current`, `status`, `uploaded_by`, `uploaded_on`, `updated_on`) VALUES
(1, 5, '1.0.0', 'whmaz_df6369186de334a985618ba65202079e.zip', 'jquery-dist-3.6.3.zip', 483328, 'first release', 1, 1, 1, '2026-07-03 08:19:17', '2026-07-03 06:19:18');

-- --------------------------------------------------------

--
-- Table structure for table `sys_cnf`
--

CREATE TABLE `sys_cnf` (
  `id` int(11) NOT NULL,
  `cnf_key` varchar(100) DEFAULT NULL,
  `cnf_val` text DEFAULT NULL,
  `cnf_group` varchar(20) NOT NULL,
  `created_on` datetime DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sys_cnf`
--

INSERT INTO `sys_cnf` (`id`, `cnf_key`, `cnf_val`, `cnf_group`, `created_on`, `updated_on`) VALUES
(1, 'DefaultNameServer1', NULL, 'DNS', '2022-10-09 16:43:09', '2026-03-07 00:16:00'),
(2, 'DefaultNameServer2', NULL, 'DNS', '2022-10-09 16:43:09', '2026-03-07 00:16:10'),
(3, 'DefaultNameServer3', NULL, 'DNS', '2022-10-09 16:43:09', '2022-10-09 16:43:09'),
(4, 'DefaultNameServer4', NULL, 'DNS', '2022-10-09 16:43:09', '2022-10-09 16:43:09'),
(5, 'cron_secret_key', '', 'SYSTEM', '2026-02-11 23:12:43', '2026-02-11 23:12:43'),
(6, 'invoice_prefix', 'INV-', 'BILLING', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(7, 'invoice_starting_number', '1000', 'BILLING', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(8, 'invoice_due_days', '7', 'BILLING', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(9, 'tax_enabled', '1', 'BILLING', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(10, 'tax_name', 'VAT', 'BILLING', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(11, 'tax_rate', '10.00', 'BILLING', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(12, 'tax_inclusive', '0', 'BILLING', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(13, 'late_fee_enabled', '0', 'BILLING', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(14, 'late_fee_amount', '5.00', 'BILLING', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(15, 'late_fee_percentage', '0', 'BILLING', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(16, 'default_currency_code', 'USD', 'BILLING', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(17, 'default_currency_symbol', '$', 'BILLING', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(18, 'multi_currency_enabled', '0', 'BILLING', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(19, 'cron_enabled', '1', 'AUTOMATION', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(20, 'invoice_generation_days_before', '7', 'AUTOMATION', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(21, 'payment_reminder_first', '3', 'AUTOMATION', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(22, 'payment_reminder_second', '1', 'AUTOMATION', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(23, 'payment_reminder_overdue', '3', 'AUTOMATION', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(24, 'suspension_days_after_due', '5', 'AUTOMATION', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(25, 'cancellation_days_after_suspension', '30', 'AUTOMATION', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(26, 'domain_reminder_first', '30', 'AUTOMATION', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(27, 'domain_reminder_second', '15', 'AUTOMATION', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(28, 'domain_reminder_third', '7', 'AUTOMATION', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(29, 'feature_customer_registration', '1', 'FEATURES', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(30, 'feature_domain_registration', '1', 'FEATURES', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(31, 'feature_support_tickets', '1', 'FEATURES', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(32, 'feature_knowledge_base', '1', 'FEATURES', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(33, 'feature_announcements', '1', 'FEATURES', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(34, 'feature_affiliate_system', '0', 'FEATURES', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(35, 'feature_two_factor_auth', '0', 'FEATURES', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(36, 'feature_social_login', '0', 'FEATURES', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(37, 'admin_notification_email', 'admin@example.com', 'NOTIFICATIONS', '2026-02-21 00:05:42', '2026-03-07 00:16:33'),
(38, 'notify_admin_new_order', '1', 'NOTIFICATIONS', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(39, 'notify_admin_new_ticket', '1', 'NOTIFICATIONS', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(40, 'notify_admin_new_customer', '1', 'NOTIFICATIONS', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(41, 'notify_admin_payment', '1', 'NOTIFICATIONS', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(42, 'allow_customer_registration', '1', 'PORTAL', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(43, 'email_verification_required', '1', 'PORTAL', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(44, 'default_account_status', 'active', 'PORTAL', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(45, 'dashboard_show_services', '1', 'PORTAL', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(46, 'dashboard_show_invoices', '1', 'PORTAL', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(47, 'dashboard_show_tickets', '1', 'PORTAL', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(48, 'dashboard_show_announcements', '1', 'PORTAL', '2026-02-21 00:05:42', '2026-02-21 00:05:42'),
(49, 'default_ticket_department', 'General Support', 'SUPPORT', '2026-02-21 00:05:43', '2026-02-21 00:05:43'),
(50, 'ticket_departments', 'Technical Support,Billing Support,Sales,General Inquiry', 'SUPPORT', '2026-02-21 00:05:43', '2026-02-21 00:05:43'),
(51, 'ticket_priorities', 'Low,Medium,High,Urgent', 'SUPPORT', '2026-02-21 00:05:43', '2026-02-21 00:05:43'),
(52, 'auto_close_tickets_after', '7', 'SUPPORT', '2026-02-21 00:05:43', '2026-02-21 00:05:43'),
(53, 'notify_customer_ticket_reply', '1', 'SUPPORT', '2026-02-21 00:05:43', '2026-02-21 00:05:43'),
(54, 'ticket_attachments_enabled', '1', 'SUPPORT', '2026-02-21 00:05:43', '2026-02-21 00:05:43'),
(55, 'ticket_max_attachment_size', '5120', 'SUPPORT', '2026-02-21 00:05:43', '2026-02-21 00:05:43'),
(56, 'license_suspension_days_after_due', '7', 'AUTOMATION', '2026-06-27 11:31:42', '2026-06-27 11:31:42');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` bigint(20) NOT NULL,
  `company_id` bigint(20) NOT NULL,
  `ticket_dept_id` int(11) NOT NULL,
  `order_service_id` bigint(20) NOT NULL DEFAULT 0,
  `order_domain_id` bigint(20) NOT NULL DEFAULT 0,
  `title` text NOT NULL,
  `message` text NOT NULL,
  `priority` tinyint(4) NOT NULL DEFAULT 2 COMMENT '1=low, 2=medium, 3=high, 4=critical',
  `attachment` text DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0=deleted',
  `flag` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1=open, 2=answered, 3=customer reply, 4=closed',
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `company_id`, `ticket_dept_id`, `order_service_id`, `order_domain_id`, `title`, `message`, `priority`, `attachment`, `status`, `flag`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`) VALUES
(1, 1, 1, 0, 0, 'Test', 'Test', 2, NULL, 1, 4, '2020-09-04 17:20:25', NULL, '2020-09-04 16:02:58', NULL),
(2, 1, 2, 0, 0, 'asdad', 'asaf adsads', 1, 'test_private_key_tomcat.png', 1, 1, '2020-09-04 17:20:25', NULL, '2020-09-04 16:03:08', NULL),
(3, 1, 2, 0, 0, 'Top-X Example', '<p>adfsvs s<b>dfd</b>sf</p>', 2, NULL, 1, 1, '2020-09-04 17:20:25', NULL, '2020-09-04 16:27:12', NULL),
(4, 1, 2, 0, 0, 'Top-X Example Top-X Example', '<p>adfsvs sdfdsf</p>', 2, 'test_private_key_tomcat.png', 1, 1, '2020-09-04 17:20:25', NULL, '2020-09-04 16:03:08', NULL),
(5, 1, 2, 0, 0, 'Top-X Example Top-X Example', '<p>adfsvs sdfdsf</p>', 2, 'test_private_key_tomcat.png', 1, 4, '2020-09-04 17:20:25', NULL, '2020-09-04 16:03:08', NULL),
(6, 1, 2, 0, 0, 'Top-X Example Top-X Example', '<p>Top-X Example Top-X Example </p>', 2, '1599200685579_view 0.png', 1, 3, '2020-09-04 17:20:25', NULL, '2020-09-04 16:03:08', NULL),
(7, 1, 1, 0, 0, 'sdfsfg', '<p>fd d dfgfdgfd dfgdgfdg</p>', 3, NULL, 1, 2, '2022-10-01 12:45:00', 1, '2026-01-23 15:23:34', 1),
(8, 1, 1, 0, 0, 'ticket security testing. ok now. <script>alert(\"OK\");</script>', '<p>ticket security testing. ok now. <script>alert(\"msg\");</script>', 4, '', 1, 3, '2026-02-11 02:16:00', 1, '2026-02-11 02:28:26', 1),
(9, 1, 1, 0, 0, 'ticket security testing. ok now. <script>alert(\"attachment\");</script>', '<p>ticket <em><u>security </u></em>testing. <strong>ok now</strong>. &lt;script&gt;alert(\"attachment\");&lt;/script&gt;</p>', 4, '9a8443e219122a09315c2b64a5858a7c.jpg,9949b6b6603b6946583dc3a79eff0f0a.png', 1, 1, '2026-02-11 02:39:00', 1, '2026-02-11 02:39:39', NULL),
(10, 1, 1, 0, 0, 'testing', '<p>testing <strong><em><u>attachemt</u></em></strong></p>', 4, '1d49f4d037bd624d267f00aaf241f8a7.jpg,f5401f8fa1b30c08a128f1abcb40a6ee.jpg', 1, 1, '2026-02-11 03:25:00', 1, '2026-02-11 03:25:26', NULL),
(11, 1, 1, 0, 0, 'test', '<p>test</p>', 3, '6f1fd016fa2edada45edd1fad26e17e3.png,f685ce3e44bfae7bfc24e3b5e8430215.jpg', 1, 3, '2026-02-11 04:29:00', 1, '2026-02-11 04:30:35', 1);

-- --------------------------------------------------------

--
-- Table structure for table `ticket_depts`
--

CREATE TABLE `ticket_depts` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 1,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket_depts`
--

INSERT INTO `ticket_depts` (`id`, `name`, `description`, `email`, `sort_order`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`) VALUES
(1, 'Support', 'Technical supports', 'support@whmaz.com', 1, 1, NULL, NULL, '2026-03-07 06:13:14', 1),
(2, 'Billing', 'Bill payments department.', 'billing@whmaz.com', 2, 1, NULL, NULL, '2024-08-03 03:42:56', 1);

-- --------------------------------------------------------

--
-- Table structure for table `ticket_replies`
--

CREATE TABLE `ticket_replies` (
  `id` bigint(20) NOT NULL,
  `ticket_id` bigint(20) NOT NULL,
  `company_id` bigint(20) NOT NULL DEFAULT 0,
  `admin_id` int(11) NOT NULL DEFAULT 0,
  `message` text NOT NULL,
  `attachment` text DEFAULT NULL,
  `rating` float NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket_replies`
--

INSERT INTO `ticket_replies` (`id`, `ticket_id`, `company_id`, `admin_id`, `message`, `attachment`, `rating`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`) VALUES
(1, 6, 1, 0, 'This is manual inserted', NULL, 0, 1, '2020-09-04 17:20:02', 1, '2024-03-20 15:11:25', NULL),
(2, 2, 1, 0, '<p><strong>gjhgjh </strong>ihihi</p>', NULL, 0, 1, '2021-03-20 02:35:00', 1, '2024-03-20 15:11:22', NULL),
(3, 7, 1, 0, '<p>re-structure</p>', NULL, 5, 1, '2024-03-20 16:28:00', 1, '2024-03-20 15:29:41', 1),
(4, 7, 1, 0, '<p>attachment</p>', NULL, 0, 1, '2024-03-20 16:30:00', 1, '2024-03-20 15:30:33', NULL),
(5, 7, 1, 0, '<p>attachments sdfsdf</p>', NULL, 0, 1, '2024-03-20 16:36:00', 1, '2024-03-20 15:36:28', NULL),
(6, 7, 1, 0, '<p>Admin reply testing</p>', '', 0, 1, '2026-01-23 15:23:34', 1, '2026-01-23 15:23:33', NULL),
(7, 8, 1, 0, '<p>User reply testing 3</p>', '', 0, 1, '2026-02-11 02:28:00', 1, '2026-02-11 02:32:42', NULL),
(8, 8, 1, 0, '<p><span style=\"color: rgb(60, 72, 88);\">ticket security testing. ok now. &lt;script&gt;alert(\"reply\");&lt;/script&gt;</span></p>', '', 0, 1, '2026-02-11 02:33:00', 1, '2026-02-11 02:33:18', NULL),
(9, 11, 1, 0, '<p>reply with <strong>attachment</strong></p>', '2a63f893ffaae646631a88657f4b9b4e.jpg', 0, 1, '2026-02-11 04:30:00', 1, '2026-02-11 04:30:35', NULL);

-- --------------------------------------------------------

--
-- Stand-in structure for view `ticket_view`
-- (See below for the actual view)
--
CREATE TABLE `ticket_view` (
`id` bigint(20)
,`company_id` bigint(20)
,`ticket_dept_id` int(11)
,`order_service_id` bigint(20)
,`order_domain_id` bigint(20)
,`title` text
,`message` text
,`priority` tinyint(4)
,`attachment` text
,`status` tinyint(4)
,`flag` tinyint(4)
,`inserted_on` datetime
,`updated_on` timestamp
,`company_name` varchar(150)
,`company_email` varchar(255)
,`company_mobile` varchar(16)
,`dept_name` varchar(255)
,`user_name` varchar(201)
);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `verify_hash` varchar(200) DEFAULT NULL,
  `verify_data` text DEFAULT NULL,
  `otp_code` varchar(10) DEFAULT NULL,
  `company_id` bigint(20) NOT NULL DEFAULT 0,
  `user_type` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0=owner,1=support',
  `login_try` tinyint(4) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1=active, 0=deleted, 2=pending',
  `profile_pic` varchar(100) DEFAULT NULL,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `pass_reset_key` varchar(150) DEFAULT NULL,
  `pass_reset_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `mobile`, `phone`, `designation`, `password`, `verify_hash`, `verify_data`, `otp_code`, `company_id`, `user_type`, `login_try`, `status`, `profile_pic`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`, `pass_reset_key`, `pass_reset_expiry`) VALUES
(1, 'A. L', 'Perera', 'client@whmaz.com', '+94799999999', '+94799999999', 'Company Owner', '$2y$10$HjX8Hz.af07jH5ACIp4aX.i7umbHLgoPkYXvlK.FFNzh30oaXmYVa', NULL, NULL, NULL, 1, 0, 0, 1, NULL, '2024-08-10 06:05:41', 1, '2026-03-07 06:24:13', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_logins`
--

CREATE TABLE `user_logins` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `login_time` datetime NOT NULL,
  `session_val` varchar(50) NOT NULL,
  `terminal` varchar(40) DEFAULT NULL,
  `device_info` text DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_logins`
--

INSERT INTO `user_logins` (`id`, `user_id`, `login_time`, `session_val`, `terminal`, `device_info`, `active`) VALUES
(1, 1, '2026-03-07 01:32:40', '0', '103.159.72.16', NULL, 1),
(2, 1, '2026-03-07 02:39:28', '0', '103.159.72.16', NULL, 1),
(3, 1, '2026-03-07 02:53:31', '0', '103.159.72.16', NULL, 1),
(4, 1, '2026-03-07 10:37:58', '0', '::1', NULL, 1),
(5, 1, '2026-03-07 04:58:29', '0', '103.159.72.16', NULL, 1),
(6, 1, '2026-03-14 00:08:39', '0', '103.159.72.16', NULL, 1),
(7, 1, '2026-03-17 14:14:34', '0', '::1', NULL, 1),
(8, 1, '2026-03-17 15:36:59', '0', '::1', NULL, 1),
(9, 1, '2026-03-25 02:01:42', '0', '::1', NULL, 1),
(10, 1, '2026-06-13 11:31:16', '0', '103.159.72.16', NULL, 1),
(11, 1, '2026-06-27 06:23:00', '0', '103.159.72.16', NULL, 1),
(12, 1, '2026-07-03 09:58:05', '0', '::1', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `webhook_logs`
--

CREATE TABLE `webhook_logs` (
  `id` bigint(20) NOT NULL,
  `gateway_code` varchar(50) NOT NULL,
  `event_type` varchar(100) DEFAULT NULL COMMENT 'Event type from gateway',
  `event_id` varchar(255) DEFAULT NULL COMMENT 'Event ID from gateway',
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Raw webhook payload' CHECK (json_valid(`payload`)),
  `headers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Request headers' CHECK (json_valid(`headers`)),
  `signature` varchar(500) DEFAULT NULL COMMENT 'Signature header value',
  `signature_valid` tinyint(1) DEFAULT NULL COMMENT 'Was signature valid?',
  `processed` tinyint(1) NOT NULL DEFAULT 0,
  `processed_at` datetime DEFAULT NULL,
  `process_result` varchar(500) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `received_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `add_to_carts`
--
ALTER TABLE `add_to_carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `customer_session_id` (`customer_session_id`),
  ADD KEY `idx_parent_cart` (`parent_cart_id`);

--
-- Indexes for table `admin_logins`
--
ALTER TABLE `admin_logins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_roles`
--
ALTER TABLE `admin_roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`alert_id`);

--
-- Indexes for table `alert_recipients`
--
ALTER TABLE `alert_recipients`
  ADD PRIMARY KEY (`alert_recipient_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `app_settings`
--
ALTER TABLE `app_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `billing_cycle`
--
ALTER TABLE `billing_cycle`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `country_name` (`country_name`);

--
-- Indexes for table `cron_jobs`
--
ALTER TABLE `cron_jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cron_jobs_job_type_executed_on` (`job_type`,`executed_on`);

--
-- Indexes for table `cron_schedules`
--
ALTER TABLE `cron_schedules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `job_name` (`job_name`);

--
-- Indexes for table `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `dom_extensions`
--
ALTER TABLE `dom_extensions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dom_pricing`
--
ALTER TABLE `dom_pricing`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dom_extension_id` (`dom_extension_id`);

--
-- Indexes for table `dom_registers`
--
ALTER TABLE `dom_registers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dunning_log`
--
ALTER TABLE `dunning_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dunning_rules`
--
ALTER TABLE `dunning_rules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unq_template_key` (`template_key`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expense_types`
--
ALTER TABLE `expense_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expense_vendors`
--
ALTER TABLE `expense_vendors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gen_numbers`
--
ALTER TABLE `gen_numbers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number_type_quk` (`no_type`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unq_invoice_no` (`invoice_no`),
  ADD UNIQUE KEY `invoice_uuid` (`invoice_uuid`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `idx_invoice_items_ref` (`item_type`,`ref_id`),
  ADD KEY `idx_invoice_items_billing_cycle` (`billing_cycle_id`);

--
-- Indexes for table `invoice_txn`
--
ALTER TABLE `invoice_txn`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice_txn_invoice` (`invoice_id`),
  ADD KEY `idx_invoice_txn_transaction` (`transaction_id`),
  ADD KEY `idx_payment_transaction` (`payment_transaction_id`);

--
-- Indexes for table `kbs`
--
ALTER TABLE `kbs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kb_cats`
--
ALTER TABLE `kb_cats`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kb_cat_mapping`
--
ALTER TABLE `kb_cat_mapping`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kb_id` (`kb_id`,`kb_cat_id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_identifier` (`identifier`,`identifier_type`),
  ADD KEY `idx_attempt_time` (`attempt_time`),
  ADD KEY `idx_ip_time` (`ip_address`,`attempt_time`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_uuid` (`order_uuid`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `order_domains`
--
ALTER TABLE `order_domains`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `idx_linked_service` (`linked_service_id`);

--
-- Indexes for table `order_licenses`
--
ALTER TABLE `order_licenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_licenses_company` (`company_id`),
  ADD KEY `idx_order_licenses_order` (`order_id`),
  ADD KEY `idx_order_licenses_plan` (`plan_id`),
  ADD KEY `idx_order_licenses_status` (`status`);

--
-- Indexes for table `order_services`
--
ALTER TABLE `order_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `idx_linked_domain` (`linked_domain_id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_page_slug` (`page_slug`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_is_published` (`is_published`);

--
-- Indexes for table `page_history`
--
ALTER TABLE `page_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_page_id` (`page_id`),
  ADD KEY `idx_changed_at` (`changed_at`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `payment_gateway`
--
ALTER TABLE `payment_gateway`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_gateway_code` (`gateway_code`);

--
-- Indexes for table `payment_refunds`
--
ALTER TABLE `payment_refunds`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_refund_uuid` (`refund_uuid`),
  ADD KEY `idx_transaction` (`transaction_id`),
  ADD KEY `idx_invoice` (`invoice_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_transaction_uuid` (`transaction_uuid`),
  ADD KEY `idx_invoice_id` (`invoice_id`),
  ADD KEY `idx_gateway_txn` (`gateway_transaction_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`initiated_at`),
  ADD KEY `idx_gateway` (`payment_gateway_id`,`gateway_code`);

--
-- Indexes for table `pending_executions`
--
ALTER TABLE `pending_executions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `is_completed` (`is_completed`);

--
-- Indexes for table `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_plans_plan_key` (`plan_key`),
  ADD KEY `idx_plans_family_group` (`family_group`);

--
-- Indexes for table `plan_features`
--
ALTER TABLE `plan_features`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_plan_features_plan_feature` (`plan_id`,`feature_key`);

--
-- Indexes for table `product_services`
--
ALTER TABLE `product_services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_service_groups`
--
ALTER TABLE `product_service_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_service_pricing`
--
ALTER TABLE `product_service_pricing`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_product_currency_cycle` (`product_service_id`,`currency_id`,`billing_cycle_id`);

--
-- Indexes for table `product_service_types`
--
ALTER TABLE `product_service_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_code` (`code`);

--
-- Indexes for table `promo_code_customers`
--
ALTER TABLE `promo_code_customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_promo_customer` (`promo_code_id`,`company_id`);

--
-- Indexes for table `promo_code_products`
--
ALTER TABLE `promo_code_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_promo_product` (`promo_code_id`,`product_service_id`);

--
-- Indexes for table `promo_code_usage`
--
ALTER TABLE `promo_code_usage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_promo_company` (`promo_code_id`,`company_id`);

--
-- Indexes for table `provisioning_logs`
--
ALTER TABLE `provisioning_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_invoice_id` (`invoice_id`),
  ADD KEY `idx_item_type_ref` (`item_type`,`ref_id`),
  ADD KEY `idx_success` (`success`),
  ADD KEY `idx_created_on` (`inserted_on`);

--
-- Indexes for table `servers`
--
ALTER TABLE `servers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `server_modules`
--
ALTER TABLE `server_modules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `software_pricing`
--
ALTER TABLE `software_pricing`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_software_pricing` (`product_id`,`currency_id`,`billing_cycle_id`),
  ADD KEY `idx_software_pricing_product` (`product_id`);

--
-- Indexes for table `software_releases`
--
ALTER TABLE `software_releases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_software_releases_current` (`is_current`,`status`),
  ADD KEY `idx_software_releases_product` (`product_id`);

--
-- Indexes for table `sys_cnf`
--
ALTER TABLE `sys_cnf`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cnf_key` (`cnf_key`),
  ADD KEY `cnf_group` (`cnf_group`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ticket_depts`
--
ALTER TABLE `ticket_depts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ticket_replies`
--
ALTER TABLE `ticket_replies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_logins`
--
ALTER TABLE `user_logins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `webhook_logs`
--
ALTER TABLE `webhook_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_gateway` (`gateway_code`),
  ADD KEY `idx_event` (`event_type`),
  ADD KEY `idx_processed` (`processed`),
  ADD KEY `idx_received` (`received_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `add_to_carts`
--
ALTER TABLE `add_to_carts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `admin_logins`
--
ALTER TABLE `admin_logins`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `admin_roles`
--
ALTER TABLE `admin_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `alert_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `alert_recipients`
--
ALTER TABLE `alert_recipients`
  MODIFY `alert_recipient_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `billing_cycle`
--
ALTER TABLE `billing_cycle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=253;

--
-- AUTO_INCREMENT for table `cron_jobs`
--
ALTER TABLE `cron_jobs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `cron_schedules`
--
ALTER TABLE `cron_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `currencies`
--
ALTER TABLE `currencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `dom_extensions`
--
ALTER TABLE `dom_extensions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `dom_pricing`
--
ALTER TABLE `dom_pricing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `dom_registers`
--
ALTER TABLE `dom_registers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `dunning_log`
--
ALTER TABLE `dunning_log`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dunning_rules`
--
ALTER TABLE `dunning_rules`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `expense_types`
--
ALTER TABLE `expense_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `expense_vendors`
--
ALTER TABLE `expense_vendors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `gen_numbers`
--
ALTER TABLE `gen_numbers`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=567;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=585;

--
-- AUTO_INCREMENT for table `invoice_txn`
--
ALTER TABLE `invoice_txn`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `kbs`
--
ALTER TABLE `kbs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `kb_cats`
--
ALTER TABLE `kb_cats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `kb_cat_mapping`
--
ALTER TABLE `kb_cat_mapping`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=740;

--
-- AUTO_INCREMENT for table `order_domains`
--
ALTER TABLE `order_domains`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=733;

--
-- AUTO_INCREMENT for table `order_licenses`
--
ALTER TABLE `order_licenses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_services`
--
ALTER TABLE `order_services`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=720;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `page_history`
--
ALTER TABLE `page_history`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment_gateway`
--
ALTER TABLE `payment_gateway`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `payment_refunds`
--
ALTER TABLE `payment_refunds`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `pending_executions`
--
ALTER TABLE `pending_executions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `plans`
--
ALTER TABLE `plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `plan_features`
--
ALTER TABLE `plan_features`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `product_services`
--
ALTER TABLE `product_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `product_service_groups`
--
ALTER TABLE `product_service_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `product_service_pricing`
--
ALTER TABLE `product_service_pricing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `product_service_types`
--
ALTER TABLE `product_service_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `promo_codes`
--
ALTER TABLE `promo_codes`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `promo_code_customers`
--
ALTER TABLE `promo_code_customers`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promo_code_products`
--
ALTER TABLE `promo_code_products`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `promo_code_usage`
--
ALTER TABLE `promo_code_usage`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `provisioning_logs`
--
ALTER TABLE `provisioning_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `servers`
--
ALTER TABLE `servers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `server_modules`
--
ALTER TABLE `server_modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `software_pricing`
--
ALTER TABLE `software_pricing`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `software_releases`
--
ALTER TABLE `software_releases`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sys_cnf`
--
ALTER TABLE `sys_cnf`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `ticket_depts`
--
ALTER TABLE `ticket_depts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ticket_replies`
--
ALTER TABLE `ticket_replies`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `user_logins`
--
ALTER TABLE `user_logins`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `webhook_logs`
--
ALTER TABLE `webhook_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Structure for view `expense_view`
--
DROP TABLE IF EXISTS `expense_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`whmazc`@`localhost` SQL SECURITY DEFINER VIEW `expense_view`  AS SELECT `e`.`id` AS `id`, `e`.`expense_type_id` AS `expense_type_id`, `e`.`expense_vendor_id` AS `expense_vendor_id`, `e`.`exp_amount` AS `exp_amount`, `e`.`paid_amount` AS `paid_amount`, `e`.`expense_date` AS `expense_date`, `e`.`attachment` AS `attachment`, `e`.`remarks` AS `remarks`, `e`.`status` AS `status`, `e`.`inserted_on` AS `inserted_on`, `e`.`updated_on` AS `updated_on`, `et`.`expense_type` AS `expense_type`, `ev`.`vendor_name` AS `vendor_name` FROM ((`expenses` `e` join `expense_types` `et` on(`e`.`expense_type_id` = `et`.`id`)) join `expense_vendors` `ev` on(`e`.`expense_vendor_id` = `ev`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `invoice_view`
--
DROP TABLE IF EXISTS `invoice_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`whmazc`@`localhost` SQL SECURITY DEFINER VIEW `invoice_view`  AS SELECT DISTINCT `i`.`id` AS `id`, `i`.`invoice_uuid` AS `invoice_uuid`, `i`.`company_id` AS `company_id`, `i`.`order_id` AS `order_id`, `i`.`currency_id` AS `currency_id`, `i`.`currency_code` AS `currency_code`, `i`.`invoice_no` AS `invoice_no`, `i`.`sub_total` AS `sub_total`, `i`.`tax` AS `tax`, `i`.`vat` AS `vat`, `i`.`total` AS `total`, `i`.`due_date` AS `due_date`, `i`.`order_date` AS `order_date`, `i`.`cancel_date` AS `cancel_date`, `i`.`refund_date` AS `refund_date`, `i`.`remarks` AS `remarks`, `i`.`status` AS `status`, `i`.`pay_status` AS `pay_status`, `i`.`need_api_call` AS `need_api_call`, `i`.`inserted_on` AS `inserted_on`, `i`.`updated_on` AS `updated_on`, `c`.`name` AS `company_name`, `c`.`mobile` AS `company_mobile`, `c`.`email` AS `company_email`, `c`.`address` AS `company_address`, `c`.`city` AS `company_city`, `c`.`state` AS `company_state`, `c`.`zip_code` AS `company_zipcode`, `c`.`country` AS `country`, `c`.`phone` AS `company_phone`, `o`.`order_uuid` AS `order_uuid`, `o`.`order_no` AS `order_no`, coalesce(`t`.`total_paid`,0) AS `total_paid`, `i`.`total`- coalesce(`t`.`total_paid`,0) AS `balance_due`, `t`.`last_payment_date` AS `last_payment_date` FROM (((`invoices` `i` join `companies` `c` on(`i`.`company_id` = `c`.`id`)) join `orders` `o` on(`i`.`order_id` = `o`.`id`)) left join (select `invoice_txn`.`invoice_id` AS `invoice_id`,sum(case when `invoice_txn`.`type` = 'payment' and `invoice_txn`.`status` = 1 then `invoice_txn`.`amount` when `invoice_txn`.`type` = 'refund' and `invoice_txn`.`status` = 1 then -`invoice_txn`.`amount` when `invoice_txn`.`type` = 'credit' and `invoice_txn`.`status` = 1 then `invoice_txn`.`amount` else 0 end) AS `total_paid`,max(case when `invoice_txn`.`status` = 1 then `invoice_txn`.`txn_date` end) AS `last_payment_date` from `invoice_txn` where `invoice_txn`.`deleted_on` is null group by `invoice_txn`.`invoice_id`) `t` on(`i`.`id` = `t`.`invoice_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `order_view`
--
DROP TABLE IF EXISTS `order_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`whmazc`@`localhost` SQL SECURITY DEFINER VIEW `order_view`  AS SELECT `o`.`id` AS `id`, `o`.`order_uuid` AS `order_uuid`, `o`.`order_no` AS `order_no`, `o`.`company_id` AS `company_id`, `o`.`currency_id` AS `currency_id`, `o`.`currency_code` AS `currency_code`, `o`.`order_date` AS `order_date`, `o`.`amount` AS `amount`, `o`.`vat_amount` AS `vat_amount`, `o`.`tax_amount` AS `tax_amount`, `o`.`coupon_code` AS `coupon_code`, `o`.`coupon_amount` AS `coupon_amount`, `o`.`discount_amount` AS `discount_amount`, `o`.`total_amount` AS `total_amount`, `o`.`payment_gateway_id` AS `payment_gateway_id`, `o`.`remarks` AS `remarks`, `o`.`instructions` AS `instructions`, `o`.`status` AS `status`, `o`.`inserted_on` AS `inserted_on`, `o`.`updated_on` AS `updated_on`, `c`.`name` AS `company_name`, `c`.`email` AS `company_email`, `c`.`mobile` AS `company_mobile`, `c`.`phone` AS `company_phone`, `c`.`address` AS `company_address`, `c`.`country` AS `country`, `c`.`zip_code` AS `company_zipcode`, `c`.`city` AS `company_city`, `c`.`state` AS `company_state`, `p`.`name` AS `payment_gateway_name`, `p`.`icon_fa_unicode` AS `payment_gateway_fa_icon`, coalesce(`sv`.`service_count`,0) AS `service_count`, coalesce(`dm`.`domain_count`,0) AS `domain_count`, coalesce(`sv`.`total_recurring`,0) AS `services_recurring_total`, coalesce(`dm`.`total_recurring`,0) AS `domains_recurring_total` FROM ((((`orders` `o` join `companies` `c` on(`o`.`company_id` = `c`.`id`)) join `payment_gateway` `p` on(`o`.`payment_gateway_id` = `p`.`id`)) left join (select `order_services`.`order_id` AS `order_id`,count(0) AS `service_count`,sum(`order_services`.`recurring_amount`) AS `total_recurring` from `order_services` where `order_services`.`deleted_on` is null group by `order_services`.`order_id`) `sv` on(`o`.`id` = `sv`.`order_id`)) left join (select `order_domains`.`order_id` AS `order_id`,count(0) AS `domain_count`,sum(`order_domains`.`recurring_amount`) AS `total_recurring` from `order_domains` where `order_domains`.`deleted_on` is null group by `order_domains`.`order_id`) `dm` on(`o`.`id` = `dm`.`order_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `pages_view`
--
DROP TABLE IF EXISTS `pages_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`whmazc`@`localhost` SQL SECURITY DEFINER VIEW `pages_view`  AS SELECT `p`.`id` AS `id`, `p`.`page_title` AS `page_title`, `p`.`page_slug` AS `page_slug`, `p`.`page_content` AS `page_content`, `p`.`meta_title` AS `meta_title`, `p`.`meta_description` AS `meta_description`, `p`.`meta_keywords` AS `meta_keywords`, `p`.`is_published` AS `is_published`, `p`.`is_system` AS `is_system`, `p`.`sort_order` AS `sort_order`, `p`.`total_view` AS `total_view`, `p`.`inserted_on` AS `inserted_on`, `p`.`inserted_by` AS `inserted_by`, `p`.`updated_on` AS `updated_on`, `p`.`updated_by` AS `updated_by`, `p`.`deleted_on` AS `deleted_on`, `p`.`deleted_by` AS `deleted_by`, `p`.`status` AS `status`, `u1`.`username` AS `created_by_name`, `u2`.`username` AS `updated_by_name` FROM ((`pages` `p` left join `admin_users` `u1` on(`p`.`inserted_by` = `u1`.`id`)) left join `admin_users` `u2` on(`p`.`updated_by` = `u2`.`id`)) WHERE `p`.`status` = 1 ;

-- --------------------------------------------------------

--
-- Structure for view `product_service_view`
--
DROP TABLE IF EXISTS `product_service_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`whmazc`@`localhost` SQL SECURITY DEFINER VIEW `product_service_view`  AS SELECT DISTINCT `ps`.`id` AS `id`, `ps`.`product_service_group_id` AS `product_service_group_id`, `ps`.`server_id` AS `server_id`, `ps`.`product_service_module_id` AS `product_service_module_id`, `ps`.`product_service_type_id` AS `product_service_type_id`, `ps`.`product_name` AS `product_name`, `ps`.`product_desc` AS `product_desc`, `ps`.`is_hidden` AS `is_hidden`, `ps`.`cp_package` AS `cp_package`, `ps`.`status` AS `status`, `ps`.`updated_on` AS `updated_on`, `psg`.`group_name` AS `group_name`, `psg`.`group_headline` AS `group_headline`, `s`.`name` AS `server_name`, `s`.`hostname` AS `server_hostname`, `s`.`ip_addr` AS `server_ip`, `psm`.`module_name` AS `module_name`, `pst`.`servce_type_name` AS `servce_type_name` FROM ((((`product_services` `ps` join `product_service_groups` `psg` on(`ps`.`product_service_group_id` = `psg`.`id`)) left join `servers` `s` on(`ps`.`server_id` = `s`.`id`)) join `server_modules` `psm` on(`ps`.`product_service_module_id` = `psm`.`id`)) join `product_service_types` `pst` on(`ps`.`product_service_type_id` = `pst`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `ticket_view`
--
DROP TABLE IF EXISTS `ticket_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`whmazc`@`localhost` SQL SECURITY DEFINER VIEW `ticket_view`  AS SELECT `tk`.`id` AS `id`, `tk`.`company_id` AS `company_id`, `tk`.`ticket_dept_id` AS `ticket_dept_id`, `tk`.`order_service_id` AS `order_service_id`, `tk`.`order_domain_id` AS `order_domain_id`, `tk`.`title` AS `title`, `tk`.`message` AS `message`, `tk`.`priority` AS `priority`, `tk`.`attachment` AS `attachment`, `tk`.`status` AS `status`, `tk`.`flag` AS `flag`, `tk`.`inserted_on` AS `inserted_on`, `tk`.`updated_on` AS `updated_on`, `c`.`name` AS `company_name`, `c`.`email` AS `company_email`, `c`.`mobile` AS `company_mobile`, `td`.`name` AS `dept_name`, concat(`u`.`first_name`,' ',`u`.`last_name`) AS `user_name` FROM (((`tickets` `tk` join `companies` `c` on(`tk`.`company_id` = `c`.`id`)) join `ticket_depts` `td` on(`tk`.`ticket_dept_id` = `td`.`id`)) join `users` `u` on(`tk`.`inserted_by` = `u`.`id`)) ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_licenses`
--
ALTER TABLE `order_licenses`
  ADD CONSTRAINT `fk_order_licenses_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`);

--
-- Constraints for table `page_history`
--
ALTER TABLE `page_history`
  ADD CONSTRAINT `fk_page_history_page` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `plan_features`
--
ALTER TABLE `plan_features`
  ADD CONSTRAINT `fk_plan_features_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `software_pricing`
--
ALTER TABLE `software_pricing`
  ADD CONSTRAINT `fk_software_pricing_product` FOREIGN KEY (`product_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
