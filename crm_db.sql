-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 13, 2026 at 02:38 AM
-- Server version: 10.11.11-MariaDB-cll-lve
-- PHP Version: 8.3.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tong0bari_whmaz`
--

-- --------------------------------------------------------

--
-- Table structure for table `add_to_carts`
--

CREATE TABLE `add_to_carts` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `customer_session_id` bigint(20) DEFAULT NULL,
  `item_type` tinyint(4) NOT NULL COMMENT '1=domain, 2=product_service',
  `product_service_id` int(11) NOT NULL DEFAULT 0,
  `note` text DEFAULT NULL,
  `hosting_domain` varchar(200) DEFAULT NULL,
  `hosting_domain_type` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0=DNS,1=REGISTER,2=TRANSFER',
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
(1, 1, '2024-07-30 16:43:29', '0', '::1', NULL, 1),
(2, 1, '2024-07-31 01:28:14', '0', '::1', NULL, 1),
(3, 1, '2024-08-01 17:21:08', '0', '::1', NULL, 1),
(4, 1, '2024-08-02 03:49:29', '0', '::1', NULL, 1),
(5, 1, '2024-08-02 08:06:58', '0', '::1', NULL, 1),
(6, 1, '2024-08-02 16:13:39', '0', '::1', NULL, 1),
(7, 1, '2024-08-03 03:57:12', '0', '::1', NULL, 1),
(8, 1, '2024-08-03 17:50:03', '0', '::1', NULL, 1),
(9, 1, '2024-08-04 03:22:29', '0', '::1', NULL, 1),
(10, 1, '2024-08-04 15:52:13', '0', '::1', NULL, 1),
(11, 1, '2024-08-05 06:57:46', '0', '::1', NULL, 1),
(12, 1, '2024-08-05 10:44:03', '0', '::1', NULL, 1),
(13, 1, '2024-08-05 12:21:22', '0', '::1', NULL, 1),
(14, 1, '2024-08-06 03:40:42', '0', '::1', NULL, 1),
(15, 1, '2024-08-07 04:07:38', '0', '::1', NULL, 1),
(16, 1, '2024-08-07 16:46:33', '0', '::1', NULL, 1),
(17, 1, '2024-08-08 03:38:55', '0', '::1', NULL, 1),
(18, 1, '2024-08-09 04:16:12', '0', '::1', NULL, 1),
(19, 1, '2024-08-09 15:02:04', '0', '::1', NULL, 1),
(20, 1, '2024-08-09 15:21:36', '0', '::1', NULL, 1),
(21, 1, '2024-08-09 15:28:56', '0', '::1', NULL, 1),
(22, 1, '2024-08-09 15:40:07', '0', '::1', NULL, 1),
(23, 1, '2024-08-10 03:13:25', '0', '::1', NULL, 1),
(24, 1, '2024-08-10 04:15:18', '0', '::1', NULL, 1),
(25, 1, '2024-08-11 01:44:05', '0', '::1', NULL, 1),
(26, 1, '2024-08-11 01:51:54', '0', '::1', NULL, 1),
(27, 1, '2024-08-11 01:52:56', '0', '::1', NULL, 1),
(28, 1, '2024-08-11 02:10:34', '0', '::1', NULL, 1),
(29, 1, '2024-08-17 13:35:37', '0', '::1', NULL, 1),
(30, 1, '2024-08-17 14:03:42', '0', '::1', NULL, 1),
(31, 1, '2024-08-20 17:08:24', '0', '::1', NULL, 1),
(32, 1, '2024-08-21 03:30:01', '0', '::1', NULL, 1),
(33, 1, '2024-08-22 02:17:40', '0', '::1', NULL, 1),
(34, 1, '2024-08-30 05:43:55', '0', '::1', NULL, 1),
(35, 1, '2024-11-05 15:18:09', '0', '::1', NULL, 1),
(36, 1, '2024-11-16 02:50:40', '0', '::1', NULL, 1),
(37, 1, '2024-11-16 03:44:35', '0', '::1', NULL, 1),
(38, 1, '2024-11-16 07:10:31', '0', '::1', NULL, 1),
(39, 1, '2024-11-16 07:51:44', '0', '::1', NULL, 1),
(40, 1, '2024-11-16 08:16:39', '0', '::1', NULL, 1),
(41, 1, '2024-11-16 17:03:44', '0', '::1', NULL, 1),
(42, 1, '2024-11-21 15:30:06', '0', '::1', NULL, 1),
(43, 1, '2024-11-22 15:44:47', '0', '::1', NULL, 1),
(44, 1, '2024-12-25 04:27:29', '0', '::1', NULL, 1),
(45, 1, '2025-01-15 03:26:14', '0', '::1', NULL, 1),
(46, 1, '2025-01-23 16:33:50', '0', '::1', NULL, 1),
(47, 1, '2025-05-03 05:27:10', '0', '::1', NULL, 1),
(48, 1, '2025-05-03 00:17:43', '0', '103.199.84.169', NULL, 1),
(49, 1, '2025-05-05 19:13:52', '0', '103.199.84.169', NULL, 1),
(50, 1, '2025-05-06 02:15:54', '0', '::1', NULL, 1),
(51, 1, '2025-08-12 03:34:00', '0', '::1', NULL, 1),
(52, 1, '2025-08-23 15:32:47', '0', '::1', NULL, 1),
(53, 1, '2025-09-24 17:01:27', '0', '::1', NULL, 1),
(54, 1, '2025-10-27 15:19:00', '0', '::1', NULL, 1),
(55, 1, '2025-12-01 16:41:10', '0', '::1', NULL, 1),
(56, 1, '2025-12-04 14:29:30', '0', '::1', NULL, 1),
(57, 1, '2025-12-30 03:09:44', '0', '::1', NULL, 1),
(58, 1, '2025-12-29 20:10:23', '0', '103.159.72.16', NULL, 1),
(59, 1, '2026-01-13 16:00:23', '0', '::1', NULL, 1),
(60, 1, '2026-01-13 16:26:17', '0', '::1', NULL, 1),
(61, 1, '2026-01-22 04:05:42', '0', '127.0.0.1', NULL, 1),
(62, 1, '2026-01-23 01:44:45', '0', '127.0.0.1', NULL, 1),
(63, 1, '2026-01-23 14:15:25', '0', '127.0.0.1', NULL, 1),
(64, 1, '2026-01-24 03:47:19', '0', '127.0.0.1', NULL, 1),
(65, 1, '2026-01-24 07:52:36', '0', '127.0.0.1', NULL, 1),
(66, 1, '2026-01-24 13:50:18', '0', '127.0.0.1', NULL, 1),
(67, 1, '2026-01-24 14:14:16', '0', '127.0.0.1', NULL, 1),
(68, 1, '2026-01-24 17:16:11', '0', '127.0.0.1', NULL, 1),
(69, 1, '2026-01-25 00:59:51', '0', '127.0.0.1', NULL, 1),
(70, 1, '2026-01-25 14:22:36', '0', '127.0.0.1', NULL, 1),
(71, 1, '2026-01-26 17:11:16', '0', '127.0.0.1', NULL, 1),
(72, 1, '2026-01-27 01:35:12', '0', '127.0.0.1', NULL, 1),
(73, 1, '2026-01-27 14:06:40', '0', '127.0.0.1', NULL, 1),
(74, 1, '2026-01-27 16:31:03', '0', '127.0.0.1', NULL, 1),
(75, 1, '2026-01-28 01:57:53', '0', '127.0.0.1', NULL, 1),
(76, 1, '2026-01-29 03:27:26', '0', '127.0.0.1', NULL, 1),
(77, 1, '2026-01-30 14:48:04', '0', '::1', NULL, 1),
(78, 1, '2026-01-30 15:23:38', '0', '::1', NULL, 1),
(79, 1, '2026-02-07 05:04:05', '0', '127.0.0.1', NULL, 1),
(80, 1, '2026-02-08 01:15:56', '0', '127.0.0.1', NULL, 1),
(81, 1, '2026-02-09 01:22:21', '0', '127.0.0.1', NULL, 1),
(82, 1, '2026-02-08 19:59:25', '0', '103.159.72.16', NULL, 1),
(83, 1, '2026-02-12 05:37:30', '0', '127.0.0.1', NULL, 1),
(84, 1, '2026-02-13 01:22:34', '0', '127.0.0.1', NULL, 1);

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
(1, 1, 'Md', 'Shahadat', 'iamsarker', '$2y$10$8LvuVUE2IqNUGuuh5VaEK.tmKBT2x.p0K20CtbLVFido5gHJCKscO', 'test@admin.com', '01824880161', '01536121323', NULL, NULL, NULL, '1,2', NULL, NULL, NULL, 1, NULL, NULL, '2024-07-30 14:39:39', NULL);

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
(1, 'Tong Bari | Solution under one roof', 'Tong Bari | Solution under one roof', '', 'favicon_ed6d384d93d183751bb364d239d05765.ico', '0e358147-e5e3-11ee-9a16-14857fbdbc3f.png', NULL, 'Tong Bari', 'Dhaka', '1219', NULL, NULL, NULL, NULL, NULL, 'support@tongbari.com', '2345', '12345', 'mail.tongbari.com', '587', 'support@tongbari.com', 'pz3jlnMaW3k=', '6Lej8lUsAAAAAASMAoMFwiMIvx1_Qkh8pKVLJ6Qo', '6Lej8lUsAAAAAEVPLbN0Yyqyb8_Zoiqtlu0s9bOw', NULL, NULL, '2026-02-12 06:18:12', 1);

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

INSERT INTO `companies` (`id`, `name`, `mobile`, `phone`, `email`, `address`, `city`, `state`, `zip_code`, `first_name`, `last_name`, `country`, `kam_id`, `kam_name`, `lead_id`, `opportunity_id`, `quotation_id`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(1, 'Tong Bari', '+8801824880161', '+8801824880161', 'info.errorpoint@gmail.com', 'Rampura Banasree', 'Dhaka North', 'Dhaka', '1219', 'Md. Shahadat', 'Sarker', 'Bangladesh', 0, '', NULL, NULL, NULL, 1, '2014-02-21 15:11:42', 1, '2024-08-10 05:02:15', 1, NULL, NULL),
(2, 'Sky Digital Ltd', '+8801730704604', '+8801730704604', 'ismail4g@gmail.com', 'Banasree, Rampura', 'Dhaka North', 'Dhaka', '1219', 'Md', 'Ismail', 'Bangladesh', 0, '', NULL, NULL, NULL, 1, '2024-08-10 04:17:03', 1, '2024-08-10 06:05:51', 1, NULL, NULL),
(3, 'Techlog', '+8801636020790', '+8801636020790', 'techlogbd@gmail.com', '176/3, Jomidar goli, Opposite Bornomala School, East Ulon, Rampura', 'Dhaka North', 'Dhaka', '1219', 'Md. Shafiqul', 'Islam', 'Bangladesh', 0, '', NULL, NULL, NULL, 1, '2024-08-10 04:22:23', 1, '2024-08-10 06:05:54', 1, NULL, NULL),
(4, 'Best Brand Apparels', '+261.345002256', '+261.345002256', 'shumanmahbub@gmail.com', 'Lot 129 O Ambohitrinimanga 103 Antananarivo-Avaradrano', 'Antananarivo', 'Avaradrano', '103', 'Mahbub Hasan', 'Shuman', 'Madagascar', 0, '', NULL, NULL, NULL, 1, '2024-08-10 04:25:01', 1, '2024-08-10 06:05:45', 1, NULL, NULL),
(5, 'Rhine Sourcing', '+8801713257212', '+8801713257212', 'shahid.swapon@gmail.com', 'Uttara', 'Dhaka North', 'Dhaka', '1215', 'Shahid', 'Swapon', 'Bangladesh', 0, '', NULL, NULL, NULL, 1, '2024-08-10 04:27:07', 1, '2024-08-10 06:05:49', 1, NULL, NULL),
(6, 'Ajier Bazar', '+8801610215271', '+8801610215271', 'abm.ataullah@gmail.com', 'Charia, Hathazari', 'Chittagong', 'Chittagong', '1212', 'ABM', 'Ataullah', 'Bangladesh', 0, '', NULL, NULL, NULL, 1, '2024-08-10 04:28:56', 1, '2024-08-10 06:05:41', 1, NULL, NULL),
(7, 'Technician Services 24', '+8801674196502', '+8801674196502', 'aashrafuzzaman@yahoo.com', 'Rampura', 'Dhaka North', 'Dhaka', '1219', 'A.K.M', 'Ashrafuzzaman', 'Bangladesh', 0, '', NULL, NULL, NULL, 1, '2024-08-10 04:57:10', 1, NULL, NULL, NULL, NULL),
(8, 'Ultimate Apparel Sourcing', '8801716057226', '8801716057226', 'jameshabib02@gmail.com', 'IECL Naim Vai', 'Gazipur', 'Dhaka', '1730', 'James', 'Habib', 'Bangladesh', 0, '', NULL, NULL, NULL, 1, '2024-08-22 02:19:05', 1, NULL, NULL, NULL, NULL),
(9, 'Shaikh and Khan Services', '+8801716382979', '+8801716382979', 'arkhan.office@gmail.com', 'House#6, Rafiq Housing Shekhertek-10, Mohammadpur', 'Dhaka', 'Dhaka', '1207', 'Arifur Rahman', 'Khan', 'Bangladesh', 0, '', NULL, NULL, NULL, 1, '2024-11-05 15:21:16', 1, NULL, NULL, NULL, NULL),
(10, 'Apparel Glow Ltd', '+8801715309710', '+8801715309710', 'apparelglowltd@gmail.com', 'H-32, R - 7, Sector - 14, Uttara', 'Dhaka North', 'Dhaka', '1230', 'Masud', 'Rana', 'Bangladesh', 0, '', NULL, NULL, NULL, 1, '2024-11-21 15:59:49', 1, '2024-11-21 16:00:13', 1, NULL, NULL),
(11, 'Alaka Bhattacharjee', '+8801731795993', '+8801731795993', 'aporajita2001@gmail.com', 'Kumarpara', 'Sylhet', 'Sylhet', '3100', 'Alaka', 'Bhattacharjee', 'Bangladesh', 0, '', NULL, NULL, NULL, 1, '2024-11-22 16:22:00', 1, '2024-11-22 16:22:43', 1, NULL, NULL),
(12, 'Ali Soft BD', '8801515293030', '8801515293030', 'alisoftbdinfo@gmail.com', '477, Dokhina Abashon, South Paikpara, Mirpur', 'Dhaka North', 'Dhaka', '1216', 'Zuel', 'Ali', 'Bangladesh', 0, '', NULL, NULL, NULL, 1, '2025-05-03 05:43:20', 1, NULL, NULL, NULL, NULL),
(13, 'Zakaria Imtiaz', '8801980484968', '', 'imtiaz71985@gmail.com', 'Mirpur', 'Dhaka', 'Dhaka', '1207', 'Zakaria', 'Imtiaz', 'Bangladesh', 0, '', NULL, NULL, NULL, 1, '2025-05-06 02:19:58', 1, NULL, NULL, NULL, NULL),
(14, 'Dhaka Trading', '+8801536194103', '+8801536194103', 'emon072033@gmail.com', 'House 1, Lane 3, Block C, Mirpur, 10', 'Dhaka', 'Dhaka', '1216', 'Md. Saidul Islam', 'Emon', 'Bangladesh', 0, '', NULL, NULL, NULL, 1, '2025-08-12 03:37:38', 1, NULL, NULL, NULL, NULL);

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
  `job_name` varchar(150) NOT NULL,
  `execute_dt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cron_jobs`
--

INSERT INTO `cron_jobs` (`id`, `job_name`, `execute_dt`) VALUES
(1, 'Invoicegenerator', '2024-11-05 22:59:30'),
(2, 'Invoicegenerator', '2024-11-05 23:09:21'),
(3, 'Invoicegenerator', '2024-11-05 23:10:39'),
(4, 'Invoicegenerator', '2024-11-16 21:22:49'),
(5, 'Invoicegenerator', '2024-11-16 21:29:47'),
(6, 'Invoicegenerator', '2024-11-16 22:20:04'),
(7, 'Invoicegenerator', '2024-11-16 22:27:06'),
(8, 'Invoicegenerator', '2024-11-16 22:30:03'),
(9, 'Invoicegenerator', '2024-11-16 22:30:40'),
(10, 'Invoicegenerator', '2024-11-16 22:31:45'),
(11, 'Invoicegenerator', '2024-11-16 22:35:33'),
(12, 'Invoicegenerator', '2024-11-16 22:38:05'),
(13, 'Invoicegenerator', '2024-11-16 22:42:10'),
(14, 'Invoicegenerator', '2024-11-16 22:44:23'),
(15, 'Invoicegenerator', '2024-11-16 22:47:34'),
(16, 'Invoicegenerator', '2024-11-16 22:49:43'),
(17, 'Invoicegenerator', '2024-11-16 22:51:26'),
(18, 'Invoicegenerator', '2024-11-16 23:18:32'),
(19, 'Invoicegenerator', '2024-11-16 23:20:28'),
(20, 'Invoicegenerator', '2024-11-16 23:20:47'),
(21, 'Invoicegenerator', '2024-11-16 23:21:28'),
(22, 'Invoicegenerator', '2024-11-21 21:12:22'),
(23, 'Invoicegenerator', '2024-11-21 21:17:03');

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
(2, '৳', 'BDT', 121, 1, 0, 1, NULL, NULL, '2024-08-09 00:09:53', 1, NULL, NULL);

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
  `price_list_api` text DEFAULT NULL,
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

INSERT INTO `dom_registers` (`id`, `name`, `platform`, `api_base_url`, `domain_check_api`, `suggestion_api`, `domain_reg_api`, `ns_update_api`, `contact_details_api`, `contact_update_api`, `price_list_api`, `auth_userid`, `auth_apikey`, `is_selected`, `def_ns1`, `def_ns2`, `def_ns3`, `def_ns4`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`) VALUES
(1, 'Resell.Biz', 'STARGATE', 'https://httpapi.com/api/domains', 'https://httpapi.com/api/domains/available.json?', 'https://httpapi.com/api/domains/v5/suggest-names.json?', 'https://httpapi.com/api/domains/register.xml?', 'https://httpapi.com/api/domains/modify-ns.json', 'https://httpapi.com/api/domains/details-by-name.json', 'https://httpapi.com/api/domains/modify-contact.json', 'test', '572394', 'IPE8mDT7ZoyQc6FmIzRp5b3lxfvabPOA', 1, 'ns1.whmaz.com', 'ns2.whmaz.com', '', '', 1, NULL, NULL, '2026-02-10 16:48:26', 1),
(2, 'Namecheap', 'NAMECHEAP', 'https://api.namecheap.com/xml.response', 'https://api.namecheap.com/xml.response', 'https://api.namecheap.com/xml.response', 'https://api.sandbox.namecheap.com/xml.response', 'https://api.namecheap.com/xml.response', 'https://api.sandbox.namecheap.com/xml.response', 'https://api.sandbox.namecheap.com/xml.response', '', 'tongbaritest', '993d950d882041ed90eea8158abec1d3', 0, NULL, NULL, NULL, NULL, 1, NULL, NULL, '2026-02-10 16:49:03', NULL);

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
  `body` text NOT NULL,
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

INSERT INTO `email_templates` (`id`, `template_key`, `template_name`, `subject`, `body`, `category`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(1, 'dunning_reminder_1', 'Dunning - First Reminder', 'Invoice #{invoice_no} - Payment Reminder', '<p>Dear {client_name},</p><p>This is a friendly reminder that your invoice <strong>#{invoice_no}</strong> for <strong>{currency} {amount_due}</strong> was due on <strong>{due_date}</strong>.</p><p>Please make payment at your earliest convenience to avoid any service interruption.</p><p><a href=\"{invoice_url}\">Click here to view and pay your invoice</a></p><p>If you have already made this payment, please disregard this notice.</p><p>Thank you,<br>{site_name}</p>', 'DUNNING', 1, '2026-01-27 19:52:54', NULL, '2026-01-28 01:52:54', NULL, NULL, NULL),
(2, 'dunning_reminder_2', 'Dunning - Second Reminder', 'Invoice #{invoice_no} - Payment Overdue ({days_overdue} days)', '<p>Dear {client_name},</p><p>Your invoice <strong>#{invoice_no}</strong> for <strong>{currency} {amount_due}</strong> is now <strong>{days_overdue} days overdue</strong>.</p><p>We kindly request that you settle this payment as soon as possible to prevent any disruption to your services.</p><p><a href=\"{invoice_url}\">Click here to view and pay your invoice</a></p><p>If you are experiencing any difficulties with payment, please contact our support team.</p><p>Regards,<br>{site_name}</p>', 'DUNNING', 1, '2026-01-27 19:52:54', NULL, '2026-01-28 01:52:54', NULL, NULL, NULL),
(3, 'dunning_reminder_3', 'Dunning - Final Warning Before Suspension', 'URGENT: Invoice #{invoice_no} - Service Suspension Warning', '<p>Dear {client_name},</p><p><strong>This is an urgent notice.</strong></p><p>Your invoice <strong>#{invoice_no}</strong> for <strong>{currency} {amount_due}</strong> remains unpaid and is now <strong>{days_overdue} days overdue</strong>.</p><p>If payment is not received promptly, your services will be <strong>suspended</strong>.</p><p><a href=\"{invoice_url}\">Click here to pay now and avoid service interruption</a></p><p>Please contact us immediately if you need assistance.</p><p>Regards,<br>{site_name}</p>', 'DUNNING', 1, '2026-01-27 19:52:54', NULL, '2026-01-28 01:52:54', NULL, NULL, NULL),
(4, 'dunning_suspended', 'Dunning - Service Suspended', 'Invoice #{invoice_no} - Service Suspended Due to Non-Payment', '<p>Dear {client_name},</p><p>Due to non-payment of invoice <strong>#{invoice_no}</strong> ({currency} {amount_due}), your service has been <strong>suspended</strong>.</p><p>Your data is still preserved. To reactivate your service, please make payment immediately:</p><p><a href=\"{invoice_url}\">Click here to pay and reactivate your service</a></p><p>If payment is not received within the next few days, your service may be permanently terminated.</p><p>Regards,<br>{site_name}</p>', 'DUNNING', 1, '2026-01-27 19:52:54', NULL, '2026-01-28 01:52:54', NULL, NULL, NULL),
(5, 'dunning_terminated', 'Dunning - Service Terminated', 'Invoice #{invoice_no} - Service Terminated', '<p>Dear {client_name},</p><p>Due to prolonged non-payment of invoice <strong>#{invoice_no}</strong> ({currency} {amount_due}), your service has been <strong>terminated</strong>.</p><p>If you wish to restore your service, please contact our support team. Data recovery may not be possible depending on how long ago the service was terminated.</p><p>Regards,<br>{site_name}</p>', 'DUNNING', 1, '2026-01-27 19:52:54', NULL, '2026-01-28 01:52:54', NULL, NULL, NULL),
(6, 'invoice_created', 'Invoice Created', 'New Invoice #{invoice_no} - {currency} {amount_due}', '<p>Dear {client_name},</p><p>A new invoice has been generated for your account.</p><p><strong>Invoice No:</strong> #{invoice_no}<br><strong>Amount:</strong> {currency} {amount_due}<br><strong>Due Date:</strong> {due_date}</p><p><a href=\"{invoice_url}\">Click here to view and pay your invoice</a></p><p>Thank you for your business.</p><p>Regards,<br>{site_name}</p>', 'INVOICE', 1, '2026-01-27 19:52:54', NULL, '2026-01-28 01:52:54', NULL, NULL, NULL),
(7, 'invoice_paid', 'Invoice Paid Confirmation', 'Payment Received - Invoice #{invoice_no}', '<p>Dear {client_name},</p><p>Thank you! We have received your payment for invoice <strong>#{invoice_no}</strong>.</p><p><strong>Amount Paid:</strong> {currency} {amount_due}<br><strong>Date:</strong> {invoice_date}</p><p><a href=\"{invoice_url}\">Click here to view your invoice</a></p><p>Thank you for your business.</p><p>Regards,<br>{site_name}</p>', 'INVOICE', 1, '2026-01-27 19:52:54', NULL, '2026-01-28 01:52:54', NULL, NULL, NULL),
(8, 'order_confirmation', 'Order Confirmation', 'Order Confirmed - #{invoice_no}', '<p>Dear {client_name},</p><p>Thank you for your order! Your order has been received and is being processed.</p><p>You will receive further updates once your order has been reviewed.</p><p>Thank you for choosing {site_name}.</p><p>Regards,<br>{site_name}</p>', 'ORDER', 1, '2026-01-27 19:52:54', NULL, '2026-01-28 01:52:54', NULL, NULL, NULL),
(9, 'welcome_email', 'Welcome Email', 'Welcome to {site_name}', '<p>Dear {client_name},</p><p>Welcome to <strong>{site_name}</strong>! Your account has been created successfully.</p><p>You can log in to your client area at: <a href=\"{site_url}\">{site_url}</a></p><p>Thank you for choosing us.</p><p>Regards,<br>{site_name}</p>', 'AUTH', 1, '2026-01-27 19:52:54', NULL, '2026-01-28 01:52:54', NULL, NULL, NULL),
(10, 'password_reset', 'Password Reset', 'Password Reset Request - {site_name}', '<p>Dear {client_name},We received a request to reset your password for your account at {site_name}.Click here to reset your passwordIf you did not request this, please ignore this email.Regards,{site_name}</p><p><br></p><p><br></p><p><br></p><p><br></p><p>Thanks</p>', 'AUTH', 1, '2026-01-27 19:52:54', NULL, '2026-01-29 09:45:21', 1, NULL, NULL);

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
(1, 'ORDER', 2025),
(2, 'INVOICE', 1534);

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
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `due_date` date NOT NULL,
  `order_date` date DEFAULT NULL,
  `cancel_date` date DEFAULT NULL,
  `refund_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `pay_status` varchar(8) NOT NULL DEFAULT 'DUE' COMMENT 'DUE,PAID,PARTIAL',
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

INSERT INTO `invoices` (`id`, `invoice_uuid`, `company_id`, `order_id`, `currency_id`, `currency_code`, `invoice_no`, `sub_total`, `tax`, `vat`, `total`, `due_date`, `order_date`, `cancel_date`, `refund_date`, `remarks`, `status`, `pay_status`, `need_api_call`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(501, '992901c5-875b-46d1-b77a-227ba7e0ab3e', 1, 702, 1, 'USD', '1501', 24.14, 0.00, 0.00, 24.14, '2024-08-10', '2024-08-10', NULL, NULL, NULL, 1, 'PAID', 0, '2024-08-10 07:56:40', 1, '2026-01-23 08:16:45', 1, NULL, NULL),
(502, 'a9c4c3ee-1f8d-49ac-90f1-8b6f9429bec9', 1, 705, 1, 'USD', '1503', 12.99, 0.00, 0.00, 12.99, '2024-08-11', '2024-08-11', NULL, NULL, NULL, 1, 'PAID', 0, '2024-08-11 02:26:38', 1, '2026-01-23 08:17:15', 1, NULL, NULL),
(503, '76d66505-4fba-46ed-bc18-ca4948763e32', 2, 707, 1, 'USD', '1505', 12.99, 0.00, 0.00, 11.25, '2024-08-22', '2022-08-22', NULL, NULL, NULL, 1, 'PAID', 0, '2024-08-21 03:32:52', 1, '2025-01-23 15:44:49', NULL, NULL, NULL),
(504, '92222387-2dee-438d-8cbb-a244eed8dd75', 2, 708, 1, 'USD', '1506', 37.95, 0.00, 0.00, 37.95, '2024-08-22', '2022-08-22', NULL, NULL, NULL, 1, 'PAID', 0, '2024-08-21 03:41:24', 1, '2025-01-23 15:44:12', NULL, NULL, NULL),
(505, '1b960619-242b-40b6-88a1-0085be232f05', 8, 710, 1, 'USD', '1507', 57.95, 0.00, 0.00, 23.00, '2024-08-10', '2020-08-10', NULL, NULL, NULL, 1, 'PAID', 0, '2024-08-22 02:21:52', 1, '2024-08-22 00:32:22', NULL, NULL, NULL),
(506, '52aa322e-f1ac-416f-bbf0-fea8830223b3', 6, 711, 1, 'USD', '1508', 13.85, 0.00, 0.00, 12.99, '2024-08-19', '2020-08-19', NULL, NULL, NULL, 1, 'PAID', 0, '2024-08-30 05:50:05', 1, '2024-08-30 04:10:21', NULL, NULL, NULL),
(507, 'c2bf4cdd-a08c-47f6-95e3-5a20f3eeb738', 6, 712, 1, 'USD', '1509', 12.99, 0.00, 0.00, 11.25, '2024-03-04', '2024-03-04', NULL, NULL, NULL, 1, 'PAID', 0, '2024-08-30 06:28:13', 1, '2024-08-30 04:34:41', NULL, NULL, NULL),
(508, '90e384cc-ed7d-436e-934f-4a0ec08aac8a', 6, 713, 1, 'USD', '1510', 73.00, 0.00, 0.00, 53.00, '2024-08-27', '2022-08-27', NULL, NULL, NULL, 1, 'DUE', 0, '2024-08-30 07:11:19', 1, '2024-08-30 05:12:54', NULL, NULL, NULL),
(509, '117d94b8-c658-4953-9f1f-bc87b565f762', 9, 714, 1, 'USD', '1511', 50.94, 0.00, 0.00, 47.44, '2023-11-26', '2021-11-26', NULL, NULL, NULL, 1, 'PAID', 0, '2023-11-19 15:27:57', 1, '2023-11-19 09:27:57', NULL, NULL, NULL),
(510, 'affb2b5a-e2ea-4c9d-a83e-42a226f5732e', 9, 715, 1, 'USD', '1512', 12.99, 0.00, 0.00, 11.70, '2023-11-26', '2021-11-26', NULL, NULL, NULL, 1, 'PAID', 0, '2023-11-19 15:27:57', 1, '2023-11-19 09:27:57', NULL, NULL, NULL),
(527, '1e3a52fb-d960-49f1-bc1c-aadb36240760', 9, 714, 1, 'USD', '1513', 50.94, 0.00, 0.00, 47.44, '2024-11-26', '2021-11-26', NULL, NULL, NULL, 1, 'PAID', 0, '2024-11-16 22:51:26', 1, '2024-11-21 14:54:32', NULL, NULL, NULL),
(528, 'e0c1771e-e0c6-4728-837a-de5f46ad8751', 9, 715, 1, 'USD', '1514', 12.99, 0.00, 0.00, 12.99, '2024-11-26', '2021-11-26', NULL, NULL, NULL, 1, 'DUE', 0, '2024-11-16 22:51:28', 1, '2024-11-16 16:51:28', NULL, NULL, NULL),
(531, '270bae77-dd6f-4448-9cfb-dec47330261b', 10, 716, 1, 'USD', '1517', 70.94, 0.00, 0.00, 55.94, '2023-11-26', '2022-11-26', NULL, NULL, NULL, 1, 'PAID', 0, '2024-11-21 16:03:43', 1, '2024-11-21 15:16:45', NULL, NULL, NULL),
(532, '18aefab7-dcaf-4135-b12c-cd425f3ea6a4', 10, 716, 1, 'USD', '1518', 85.94, 0.00, 0.00, 77.99, '2024-11-26', '2022-11-26', NULL, NULL, NULL, 1, 'DUE', 0, '2024-11-21 21:17:03', 1, '2024-11-21 15:34:13', NULL, NULL, NULL),
(533, 'faa8a1f4-785b-42da-9954-c1679f9a4651', 2, 717, 1, 'USD', '1519', 12.99, 0.00, 0.00, 12.00, '2025-11-22', '2024-11-22', NULL, NULL, NULL, 1, 'PAID', 0, '2024-11-22 15:47:27', 1, '2024-12-25 03:43:34', NULL, NULL, NULL),
(534, 'aa62a096-dd78-4fd6-9724-46d11d30385a', 11, 718, 1, 'USD', '1520', 38.94, 0.00, 0.00, 32.94, '2024-03-29', '2016-03-29', NULL, NULL, NULL, 1, 'PAID', 0, '2024-11-22 16:36:57', 1, '2024-11-22 15:42:39', NULL, NULL, NULL),
(535, '1b42f089-5da0-4a8d-9c1e-73a384bd9f7c', 2, 719, 1, 'USD', '1521', 12.99, 0.00, 0.00, 11.99, '2024-11-29', '2023-11-29', NULL, NULL, NULL, 1, 'PAID', 0, '2024-12-25 04:29:58', 1, '2025-05-06 00:15:07', NULL, NULL, NULL),
(536, 'a26a4187-295c-4a0d-8b6b-c95ed23bbeba', 2, 720, 1, 'USD', '1522', 69.50, 0.00, 0.00, 69.50, '2026-01-16', '2025-01-16', NULL, NULL, NULL, 1, 'PAID', 0, '2025-01-23 16:43:04', 1, '2025-05-03 04:54:43', NULL, NULL, NULL),
(537, '624e3258-a756-4b73-a5a6-d578269f8f10', 12, 721, 1, 'USD', '1523', 48.00, 0.00, 0.00, 48.00, '2024-05-04', '2024-05-04', NULL, NULL, NULL, 1, 'PAID', 0, '2024-05-04 05:59:07', 1, '2025-05-06 00:11:24', NULL, NULL, NULL),
(539, '724e3258-a756-4b73-a5a6-d578269f8f11', 12, 721, 1, 'USD', '1524', 48.00, 0.00, 0.00, 48.00, '2025-05-04', '2024-05-04', NULL, NULL, NULL, 1, 'PAID', 0, '2024-05-04 05:59:07', 1, '2025-05-06 00:07:49', NULL, NULL, NULL),
(541, 'f9640b18-0424-467c-a0bc-600a203a7e11', 13, 723, 1, 'USD', '1526', 37.95, 0.00, 0.00, 28.00, '2024-12-03', '2024-12-03', NULL, NULL, NULL, 1, 'PAID', 0, '2024-12-03 02:25:46', 1, '2025-08-23 13:33:48', NULL, NULL, NULL),
(542, 'c4979812-06c6-4bf7-b615-a7e0fee68411', 14, 724, 1, 'USD', '1527', 90.49, 0.00, 0.00, 90.49, '2026-08-09', '2025-08-09', NULL, NULL, NULL, 1, 'PAID', 0, '2025-08-09 03:41:23', 1, '2025-08-23 13:33:56', NULL, NULL, NULL),
(545, '77d66505-4fba-46ed-bc18-ca4948763e33', 2, 707, 1, 'USD', '1528', 12.99, 0.00, 0.00, 12.99, '2025-08-23', '2022-08-22', NULL, NULL, NULL, 1, 'PAID', 0, '2025-08-23 03:32:52', 1, '2025-09-24 15:08:31', NULL, NULL, NULL),
(546, '93222387-2dee-438d-8cbb-a244eed8dd76', 2, 708, 1, 'USD', '1529', 37.95, 0.00, 0.00, 37.95, '2025-08-23', '2022-08-22', NULL, NULL, NULL, 1, 'PAID', 0, '2025-08-23 03:41:24', 1, '2025-09-24 15:08:30', NULL, NULL, NULL),
(547, '7b960619-242b-40b6-88a1-0085be232e23', 8, 710, 1, 'USD', '1530', 22.00, 0.00, 0.00, 17.00, '2025-08-10', '2020-08-10', NULL, NULL, NULL, 1, 'PAID', 0, '2025-08-10 02:21:52', 1, '2025-10-27 14:28:48', NULL, NULL, NULL),
(549, '2a3a52fb-d960-49f1-bc1c-aadb36240780', 9, 714, 1, 'USD', '1531', 50.94, 0.00, 0.00, 47.44, '2025-11-26', '2021-11-26', NULL, NULL, NULL, 1, 'PAID', 0, '2025-11-16 22:51:26', 1, '2025-12-04 13:37:39', NULL, NULL, NULL),
(550, '91edf252-bca1-40d9-bf3f-71ab2f56d726', 2, 725, 1, 'USD', '1532', 69.50, 0.00, 0.00, 69.50, '2026-12-04', '2025-12-04', NULL, NULL, NULL, 1, 'PAID', 0, '2025-12-04 14:32:13', 1, '2026-01-13 15:04:40', NULL, NULL, NULL),
(551, 'b26a4187-295c-4a0d-8b6b-c95ed23bbebc', 2, 720, 1, 'USD', '1533', 69.50, 0.00, 0.00, 69.50, '2027-01-16', '2025-01-16', NULL, NULL, NULL, 1, 'PAID', 0, '2026-01-13 16:43:04', 1, '2026-01-23 08:06:39', 1, NULL, NULL),
(552, '9fcf6d9c-90c3-4317-bc77-75a07bc44ee0', 14, 726, 1, 'USD', '1534', 33.14, 0.00, 0.00, 33.14, '2025-04-21', '2025-04-14', NULL, NULL, NULL, 1, 'PAID', 0, '2025-04-14 10:24:18', 1, '2026-02-12 16:35:27', 1, NULL, NULL);

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
(501, 501, 'Domain registration', 'erpboi.com - 1 year(s)', 1, NULL, 4, NULL, 1, 12.99, 0.00, 12.99, 0.00, 0.00, 12.99, NULL, NULL, '2024-08-10 07:56:40', 1, '2026-02-12 10:34:38', NULL),
(502, 501, 'Hosting package', 'Yearly Basic for erpboi.com domain', 2, NULL, 4, NULL, 1, 11.15, 0.00, 11.15, 0.00, 0.00, 11.15, NULL, NULL, '2024-08-10 07:56:40', 1, '2026-02-12 10:34:38', NULL),
(503, 502, 'Domain registration', 'finboi.com - 1 year(s)', 1, NULL, 4, NULL, 1, 12.99, 0.00, 12.99, 0.00, 0.00, 12.99, NULL, NULL, '2024-08-11 02:26:38', 1, '2026-02-12 10:34:38', NULL),
(504, 503, 'Domain registration', 'Domain Renewal - skydigitalbd.com - 1 Year/s (23/08/2024 - 22/08/2025)\n + DNS Management', 1, NULL, 4, NULL, 1, 12.99, 0.00, 12.99, 0.00, 0.00, 11.25, NULL, NULL, '2024-08-21 03:32:52', 1, '2026-02-12 10:34:38', NULL),
(506, 504, 'Hosting package', 'Yearly Corp_3GB for skydigitalbd.com domain', 2, NULL, 4, NULL, 1, 37.95, 0.00, 37.95, 0.00, 0.00, 37.95, NULL, NULL, '2024-08-21 03:41:24', 1, '2026-02-12 10:34:38', NULL),
(507, 505, 'Domain registration', 'ultimateappearelsourcingbd.com.bd -  year(s)', 1, NULL, 4, NULL, 1, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, NULL, '2024-08-22 02:21:52', 1, '2026-02-12 10:33:38', NULL),
(508, 505, 'Hosting package', 'Yearly Corp_5GB for ultimateappearelsourcingbd.com.bd domain', 2, NULL, 4, NULL, 1, 57.95, 0.00, 57.95, 0.00, 0.00, 57.95, NULL, NULL, '2024-08-22 02:21:52', 1, '2026-02-12 10:34:38', NULL),
(509, 506, 'Domain registration', 'authorservice.org - 1 year(s)', 1, NULL, 4, NULL, 1, 13.85, 0.00, 13.85, 0.00, 0.00, 13.85, NULL, NULL, '2024-08-30 05:50:05', 1, '2026-02-12 10:34:38', NULL),
(510, 507, 'Domain registration', 'truefarmersagro.com - 1 year(s)', 1, NULL, 4, NULL, 1, 12.99, 0.00, 12.99, 0.00, 0.00, 12.99, NULL, NULL, '2024-08-30 06:28:13', 1, '2026-02-12 10:34:38', NULL),
(511, 508, 'Domain registration', 'authorservice.org -  year(s)', 1, NULL, 4, NULL, 1, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, NULL, '2024-08-30 07:11:19', 1, '2026-02-12 10:33:38', NULL),
(512, 508, 'Hosting package', 'Yearly reseller package (Small) for authorservice.org domain', 2, NULL, 4, NULL, 1, 73.00, 0.00, 73.00, 0.00, 0.00, 73.00, NULL, NULL, '2024-08-30 07:11:19', 1, '2026-02-12 10:34:38', NULL),
(513, 509, 'Domain registration', 'snkbd.com - 1 year(s)', 1, NULL, 4, NULL, 1, 12.99, 0.00, 12.99, 0.00, 0.00, 12.99, NULL, NULL, '2023-11-19 15:27:57', 1, '2026-02-12 10:34:38', NULL),
(514, 509, 'Hosting package', 'Yearly Corp_3GB for snkbd.com domain', 2, NULL, 4, NULL, 1, 37.95, 0.00, 37.95, 0.00, 0.00, 37.95, NULL, NULL, '2023-11-19 15:27:57', 1, '2026-02-12 10:34:38', NULL),
(515, 510, 'Domain registration', 'shaikhandkhanservices.com - 1 year(s)', 1, NULL, 4, NULL, 1, 12.99, 0.00, 12.99, 0.00, 0.00, 12.99, NULL, NULL, '2023-11-19 15:27:57', 1, '2026-02-12 10:34:38', NULL),
(537, 527, 'Domain renewal', 'Renewal of Domain -> snkbd.com', 1, NULL, 4, '', 1, 12.99, 0.00, 12.99, 0.00, 0.00, 12.99, NULL, NULL, '2024-11-16 22:51:26', 1, '2026-02-12 10:34:38', NULL),
(538, 527, 'Hosting package', 'Renewal of hosting package -> Corp_3GB', 1, NULL, 4, '', 1, 37.95, 0.00, 37.95, 0.00, 0.00, 37.95, NULL, NULL, '2024-11-16 22:51:26', 1, '2026-02-12 10:34:38', NULL),
(539, 528, 'Domain renewal', 'Renewal of Domain -> shaikhandkhanservices.com', 1, NULL, 4, '', 1, 12.99, 0.00, 12.99, 0.00, 0.00, 12.99, NULL, NULL, '2024-11-16 22:51:28', 1, '2026-02-12 10:34:38', NULL),
(543, 531, 'Domain registration', 'apparelglowltd.com - 1 year(s)', 1, NULL, 4, NULL, 1, 12.99, 0.00, 12.99, 0.00, 0.00, 12.99, NULL, NULL, '2024-11-21 16:03:43', 1, '2026-02-12 10:34:38', NULL),
(544, 531, 'Hosting package', 'Yearly Corp_5GB for apparelglowltd.com domain', 2, NULL, 4, NULL, 1, 57.95, 0.00, 57.95, 0.00, 0.00, 57.95, NULL, NULL, '2024-11-21 16:03:43', 1, '2026-02-12 10:34:38', NULL),
(545, 532, 'Domain renewal', 'Renewal of Domain -> apparelglowltd.com', 1, NULL, 4, '', 1, 12.99, 0.00, 12.99, 0.00, 0.00, 12.99, NULL, NULL, '2024-11-21 21:17:03', 1, '2026-02-12 10:34:38', NULL),
(546, 532, 'Hosting package', 'Renewal/upgrade of hosting package -> Corp_10GB', 1, NULL, 4, '', 1, 72.95, 0.00, 72.95, 0.00, 0.00, 72.95, NULL, NULL, '2024-11-21 21:17:03', 1, '2026-02-12 10:34:38', NULL),
(547, 533, 'Domain registration', 'koreshikhi.com - 1 year(s)', 1, NULL, 4, NULL, 1, 12.99, 0.00, 12.99, 0.00, 0.00, 12.99, NULL, NULL, '2024-11-22 15:47:27', 1, '2026-02-12 10:34:38', NULL),
(548, 534, 'Domain registration', 'alakabhattacharjee.com - 1 year(s)', 1, NULL, 4, NULL, 1, 12.99, 0.00, 12.99, 0.00, 0.00, 12.99, NULL, NULL, '2024-11-22 16:36:57', 1, '2026-02-12 10:34:38', NULL),
(549, 534, 'Hosting package', 'Yearly Starter for alakabhattacharjee.com domain', 2, NULL, 4, NULL, 1, 25.95, 0.00, 25.95, 0.00, 0.00, 25.95, NULL, NULL, '2024-11-22 16:36:57', 1, '2026-02-12 10:34:38', NULL),
(550, 535, 'Domain registration', 'bcsaaa.com - 1 year(s)', 1, NULL, 4, NULL, 1, 12.99, 0.00, 12.99, 0.00, 0.00, 12.99, NULL, NULL, '2024-12-25 04:29:58', 1, '2026-02-12 10:34:38', NULL),
(551, 536, 'Hosting package', 'Yearly renew of uVPS-1 (Parliament)', 2, NULL, 4, NULL, 1, 69.50, 0.00, 69.50, 0.00, 0.00, 69.50, NULL, NULL, '2025-01-23 16:43:04', NULL, '2026-02-12 10:34:38', NULL),
(552, 537, 'Domain registration', 'alisoftbd.com - 1 year(s)', 1, NULL, 4, NULL, 1, 12.99, 0.00, 12.99, 0.00, 0.00, 12.99, NULL, NULL, '2025-05-03 05:59:10', 1, '2026-02-12 10:34:38', NULL),
(553, 537, 'Hosting package', 'Yearly 6GB for alisoftbd.com domain', 2, NULL, 4, NULL, 1, 38.00, 0.00, 38.00, 0.00, 0.00, 38.00, NULL, NULL, '2025-05-03 05:59:11', 1, '2026-02-12 10:34:38', NULL),
(554, 539, 'Domain registration', 'alisoftbd.com - 1 year(s)', 1, NULL, 4, NULL, 1, 12.99, 0.00, 12.99, 0.00, 0.00, 12.99, NULL, NULL, '2025-05-03 05:59:10', 1, '2026-02-12 10:34:38', NULL),
(555, 539, 'Hosting package', 'Yearly 6GB for alisoftbd.com domain', 2, NULL, 4, NULL, 1, 38.00, 0.00, 38.00, 0.00, 0.00, 38.00, NULL, NULL, '2025-05-03 05:59:11', 1, '2026-02-12 10:34:38', NULL),
(557, 541, 'Hosting package', 'Yearly Corp_3GB for dwa.com.bd domain', 2, NULL, 4, NULL, 1, 37.95, 0.00, 37.95, 0.00, 0.00, 37.95, NULL, NULL, '2025-05-06 02:25:52', 1, '2026-02-12 10:34:38', NULL),
(558, 542, 'Domain registration', 'brandnstitch.com - 1 year(s)', 1, NULL, 4, NULL, 1, 12.99, 0.00, 12.99, 0.00, 0.00, 12.99, NULL, NULL, '2025-08-12 03:41:27', 1, '2026-02-12 10:34:38', NULL),
(559, 542, 'Hosting package', 'Yearly 10GB_SHARED for brandnstitch.com domain', 2, NULL, 4, NULL, 1, 78.50, 0.00, 78.50, 0.00, 0.00, 78.50, NULL, NULL, '2025-08-12 03:41:28', 1, '2026-02-12 10:34:38', NULL),
(560, 545, 'Domain registration', 'Domain Renewal - skydigitalbd.com - 1 Year/s (23/08/2025 - 22/08/2026)\r\n + DNS Management', 1, NULL, 4, NULL, 1, 12.99, 0.00, 12.99, 0.00, 0.00, 12.99, NULL, NULL, '2025-08-23 03:32:52', 1, '2026-02-12 10:34:38', NULL),
(561, 546, 'Hosting package', 'Yearly Corp_3GB for skydigitalbd.com domain', 2, NULL, 4, NULL, 1, 37.95, 0.00, 37.95, 0.00, 0.00, 37.95, NULL, NULL, '2025-08-23 03:32:52', 1, '2026-02-12 10:34:38', NULL),
(562, 547, 'Domain registration', 'ultimateappearelsourcingbd.com.bd -  year(s)', 1, NULL, 4, NULL, 1, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, NULL, '2025-08-22 02:21:52', 1, '2026-02-12 10:33:06', NULL),
(563, 547, 'Hosting package', 'Yearly Corp_3GB for ultimateappearelsourcingbd.com.bd domain', 2, NULL, 4, NULL, 1, 22.00, 0.00, 22.00, 0.00, 0.00, 22.00, NULL, NULL, '2025-08-22 02:21:52', 1, '2026-02-12 10:34:38', NULL),
(564, 549, 'Domain renewal', 'Renewal of Domain -> snkbd.com', 1, NULL, 4, '', 1, 12.99, 0.00, 12.99, 0.00, 0.00, 12.99, NULL, NULL, '2025-11-16 22:51:26', 1, '2026-02-12 10:34:38', NULL),
(565, 549, 'Hosting package', 'Renewal of hosting package -> Corp_3GB', 1, NULL, 4, '', 1, 37.95, 0.00, 37.95, 0.00, 0.00, 37.95, NULL, NULL, '2025-11-16 22:51:26', 1, '2026-02-12 10:34:38', NULL),
(566, 550, 'Hosting package', 'Yearly uVPS-1 for  domain', 2, NULL, 4, NULL, 1, 69.50, 0.00, 69.50, 0.00, 0.00, 69.50, NULL, NULL, '2025-12-04 14:32:18', NULL, '2026-02-12 10:34:38', NULL),
(567, 551, 'Hosting package', 'Yearly renew of uVPS-1 (Parliament)', 2, NULL, 4, NULL, 1, 69.50, 0.00, 69.50, 0.00, 0.00, 69.50, NULL, NULL, '2026-01-13 16:43:04', NULL, '2026-02-12 10:34:11', NULL),
(568, 552, 'Domain registration', 'emonislam.com - 1 year(s)', 1, 724, 4, NULL, 1, 12.99, 0.00, 12.99, 0.00, 0.00, 12.99, '2025-04-14', '2026-04-14', '2025-04-14 10:24:21', 1, '2026-02-12 10:32:53', NULL),
(569, 552, 'Hosting package', 'Yearly 2GB_SHARED for emonislam.com domain', 2, 715, 4, NULL, 1, 20.15, 0.00, 20.15, 0.00, 0.00, 20.15, '2025-04-14', '2026-04-14', '2025-04-14 10:24:22', 1, '2026-02-12 10:31:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `invoice_txn`
--

CREATE TABLE `invoice_txn` (
  `id` bigint(20) NOT NULL,
  `invoice_id` bigint(20) DEFAULT NULL,
  `payment_gateway_id` int(11) DEFAULT NULL COMMENT 'FK to payment gateway',
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

INSERT INTO `invoice_txn` (`id`, `invoice_id`, `payment_gateway_id`, `transaction_id`, `txn_date`, `amount`, `currency_code`, `type`, `status`, `remarks`, `attachments`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(1, 505, NULL, NULL, '2024-08-22', 23.00, NULL, 'payment', 1, 'paid - 2250 tk', NULL, '2024-08-22 10:19:48', 1, NULL, NULL, NULL, NULL),
(2, 506, NULL, NULL, '2024-08-30', 12.99, NULL, 'payment', 1, 'paid - 1600 tk', NULL, '2024-08-22 10:19:48', 1, NULL, NULL, NULL, NULL);

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

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `identifier`, `identifier_type`, `ip_address`, `user_agent`, `is_successful`, `attempt_time`) VALUES
(20, 'info.errorpoint@gmail.com', 'email', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 0, '2026-02-13 01:22:16');

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
  `coupon_code` varchar(16) DEFAULT NULL,
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
(702, 'f635039b-ecbb-4a89-9b4a-9d06e1797cfb', 2002, 1, 1, 'USD', '2024-08-10', 24.14, 0.00, 0.00, '', 0.00, 0.00, 24.14, 1, NULL, 1, '', 0, 0, '2024-08-10 07:56:40', 0, '2024-08-21 01:42:40', NULL, NULL, NULL),
(705, '7134013a-9d47-44ea-b54f-f6834c01d60a', 2005, 1, 1, 'USD', '2024-08-11', 12.99, 0.00, 0.00, '', 0.00, 0.00, 12.99, 1, NULL, 1, '', 0, 0, '2024-08-11 02:26:38', 1, '2024-08-21 01:42:44', NULL, NULL, NULL),
(707, '48d7d562-52b6-479e-b5dd-b5d09ae1cdee', 2007, 2, 1, 'USD', '2024-08-21', 12.99, 0.00, 0.00, '', 0.00, 10.00, 12.99, 1, NULL, 1, '', 0, 0, '2024-08-21 03:32:52', 0, '2025-08-23 13:41:58', NULL, NULL, NULL),
(708, '73f60dfb-6639-4769-87ba-fb74e766485d', 2008, 2, 1, 'USD', '2024-08-21', 37.95, 0.00, 0.00, '', 0.00, 8.95, 29.00, 1, NULL, 1, '', 0, 0, '2024-08-21 03:41:24', 0, '2024-08-21 23:36:00', NULL, NULL, NULL),
(710, '703bebb3-3c38-457f-ae09-5e881f5561f0', 2009, 8, 1, 'USD', '2020-08-10', 22.00, 0.00, 0.00, '', 0.00, 5.00, 17.00, 1, NULL, 1, '', 0, 0, '2024-08-22 02:21:52', 7, '2025-10-27 14:25:24', NULL, NULL, NULL),
(711, '41ede5d6-5c91-477d-b0e0-b4b02f8f08d7', 2010, 6, 1, 'USD', '2020-08-19', 13.85, 0.00, 0.00, '', 0.00, 0.86, 12.99, 1, NULL, 1, '', 0, 0, '2024-08-30 05:50:05', 1, '2024-08-30 03:51:16', NULL, NULL, NULL),
(712, '8422c823-406e-4bc5-b440-6477190fb5a8', 2011, 6, 1, 'USD', '2024-03-04', 12.99, 0.00, 0.00, '', 0.00, 1.74, 11.25, 1, NULL, 1, '', 0, 0, '2024-08-30 06:28:13', 0, '2024-08-30 04:29:28', NULL, NULL, NULL),
(713, 'ac9acb35-67bb-4954-80c0-78a5cf7561d1', 2012, 5, 1, 'USD', '2022-08-27', 73.00, 0.00, 0.00, '', 0.00, 20.00, 53.00, 1, NULL, 1, '', 0, 0, '2024-08-30 07:11:19', 0, '2026-01-27 14:24:47', NULL, NULL, NULL),
(714, '169fcc7d-3678-4166-8162-fda4c41fe063', 2013, 9, 1, 'USD', '2021-11-26', 50.94, 0.00, 0.00, '', 0.00, 3.50, 47.44, 0, NULL, 1, '', 0, 0, '2021-11-26 15:27:57', 0, '2021-11-26 14:27:57', NULL, NULL, NULL),
(715, '549f9323-dbd9-4b13-9712-3e1bbbfdcebc', 2014, 9, 1, 'USD', '2021-11-26', 12.99, 0.00, 0.00, '', 0.00, 1.29, 11.70, 1, NULL, 1, '', 0, 0, '2021-11-26 15:27:57', 1, '2021-11-26 14:27:57', NULL, NULL, NULL),
(716, 'd34c5ca4-a9b0-4858-8beb-df44c254fbb5', 2015, 10, 1, 'USD', '2022-11-26', 85.94, 0.00, 0.00, '', 0.00, 7.95, 77.99, 1, NULL, 1, '', 0, 0, '2024-11-21 16:03:43', 0, '2024-11-21 15:38:03', NULL, NULL, NULL),
(717, 'bfea7872-5775-4862-adb2-889931bc2ab1', 2016, 2, 1, 'USD', '2024-11-22', 12.99, 0.00, 0.00, '', 0.00, 0.99, 12.00, 1, NULL, 1, '', 0, 0, '2024-11-22 15:47:27', 0, '2024-11-22 14:47:27', NULL, NULL, NULL),
(718, 'ce152d84-e9ed-4e50-a6ac-d9427b78874c', 2017, 11, 1, 'USD', '2026-03-29', 38.94, 0.00, 0.00, '', 0.00, 6.00, 32.94, 1, NULL, 1, '', 0, 0, '2024-11-22 16:36:57', 0, '2024-11-22 15:47:53', NULL, NULL, NULL),
(719, '4e14b229-8904-4fed-9552-500efa09d231', 2018, 2, 1, 'USD', '2024-11-29', 12.99, 0.00, 0.00, '', 0.00, 1.00, 11.99, 1, NULL, 1, '', 0, 0, '2024-12-25 04:29:58', 0, '2024-12-25 03:32:08', NULL, NULL, NULL),
(720, 'c826a694-4ab5-47e3-89aa-6d819f6ab8f6', 2019, 2, 1, 'USD', '2025-01-16', 69.50, 0.00, 0.00, '', 0.00, 0.00, 69.50, 1, NULL, 1, '', 0, 0, '2025-01-23 16:43:04', 0, '2025-01-23 15:49:22', NULL, NULL, NULL),
(721, '9fb7021e-46e1-472f-a548-39f3881be477', 2020, 12, 1, 'USD', '2024-05-04', 50.99, 0.00, 0.00, '', 0.00, 2.99, 48.00, 1, NULL, 1, '', 0, 0, '2025-05-03 05:59:08', 7, '2025-05-06 00:07:23', NULL, NULL, NULL),
(723, 'ca821065-8ecf-44ca-99d5-2499f802ed8b', 2022, 13, 1, 'USD', '2024-12-03', 37.95, 0.00, 0.00, '', 0.00, 9.95, 28.00, 1, NULL, 1, '', 0, 0, '2024-12-03 02:25:47', 0, '2025-05-06 00:40:36', NULL, NULL, NULL),
(724, 'de8a552d-abcc-4b9c-81d4-595eb0594bb2', 2023, 14, 1, 'USD', '2025-08-12', 91.49, 0.00, 0.00, '', 0.00, 1.00, 90.49, 1, NULL, 1, '', 0, 0, '2025-08-12 03:41:24', 0, '2025-08-12 01:50:35', NULL, NULL, NULL),
(725, '9aecd9e7-7267-4cca-8729-45d7f60d7c3d', 2024, 2, 1, 'USD', '2025-12-04', 69.50, 0.00, 0.00, '', 0.00, 0.00, 69.50, 1, NULL, 1, '', 0, 0, '2025-12-04 14:32:14', 0, '2025-12-04 13:32:14', NULL, NULL, NULL),
(726, 'a9d3e28c-1ac5-40e8-b571-25fc4ef08152', 2025, 14, 1, 'USD', '2025-04-14', 33.14, 0.00, 0.00, '', 0.00, 0.00, 33.14, 1, NULL, 1, '', 0, 0, '2025-04-14 10:24:19', 1, '2026-02-12 10:27:17', NULL, NULL, NULL);

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
  `order_type` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1=reg, 2=transfer, 3=nothing',
  `epp_code` varchar(200) DEFAULT NULL,
  `domain` text NOT NULL,
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

INSERT INTO `order_domains` (`id`, `order_id`, `company_id`, `dom_register_id`, `dom_pricing_id`, `order_type`, `epp_code`, `domain`, `first_pay_amount`, `recurring_amount`, `reg_date`, `reg_period`, `exp_date`, `due_date`, `next_renewal_date`, `suspension_date`, `suspension_reason`, `termination_date`, `is_synced`, `last_sync_dt`, `domain_cust_id`, `domain_order_id`, `dns_management`, `email_forwarding`, `id_protection`, `auto_renew`, `dns_type`, `ns1`, `ns2`, `ns3`, `ns4`, `contact_name`, `contact_company`, `contact_email`, `contact_phone`, `contact_address1`, `contact_address2`, `contact_city`, `contact_state`, `contact_zip`, `contact_country`, `last_contact_sync`, `status`, `remarks`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(702, 702, 1, 2, 1, 1, NULL, 'erpboi.com', 12.99, 12.99, '2023-11-29', 1, '2025-11-29', '2025-11-29', '2025-11-29', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 1, 'default_ns', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '', '2024-08-10 07:56:40', 1, '2026-02-12 09:44:14', NULL, NULL, NULL),
(705, 705, 1, 1, 1, 1, '', 'finboi.com', 12.99, 12.99, '2024-08-11', 1, '2025-08-11', '2025-08-11', '2025-08-11', NULL, NULL, NULL, 0, NULL, NULL, 107940477, 0, 0, 0, 1, 'default_ns', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '', '2024-08-11 02:26:38', 1, '2026-02-12 09:44:14', NULL, NULL, NULL),
(707, 707, 2, 1, 1, 1, '', 'skydigitalbd.com', 12.99, 12.99, '2022-08-22', 1, '2025-08-21', '2025-08-21', '2025-08-21', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 1, 'default_ns', 'ns1.whmaz.com', 'ns2.whmaz.com', NULL, NULL, 'Md Ismail', 'Sky Digital Ltd', 'ismail4g@gmail.com', '8801730704604', 'Banasree', '', 'Dhaka', 'Dhaka', '1219', 'BD', '2026-02-10 17:17:42', 1, '', '2024-08-21 03:32:52', 1, '2026-02-12 09:44:14', NULL, NULL, NULL),
(710, 710, 8, 0, 0, 3, '', 'ultimateapparelsourcing.com.bd', 0.00, 0.00, '2020-08-10', 1, '2020-08-10', '2020-08-10', '2020-08-10', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 1, 'default_ns', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '', '2024-08-22 02:21:52', 1, '2026-02-12 09:44:14', NULL, NULL, NULL),
(711, 711, 6, 0, 5, 1, '', 'authorservice.org', 13.85, 13.85, '2020-08-19', 1, '2025-08-19', '2025-08-19', '2025-08-19', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 1, 'default_ns', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '', '2024-08-30 05:50:05', 1, '2026-02-12 09:44:14', NULL, NULL, NULL),
(712, 712, 6, 0, 1, 1, '', 'truefarmersagro.com', 12.99, 12.99, '2024-03-04', 1, '2025-03-04', '2025-03-04', '2025-03-04', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 1, 'default_ns', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '', '2024-08-30 06:28:13', 1, '2026-02-12 09:44:14', NULL, NULL, NULL),
(713, 713, 5, 0, 0, 1, '', 'authorservice.org', 0.00, 0.00, '2024-08-30', 1, '2024-08-30', '2024-08-30', '2024-08-30', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 1, 'default_ns', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '', '2024-08-30 07:11:19', 1, '2026-02-12 09:44:14', NULL, NULL, NULL),
(714, 714, 9, 1, 1, 1, '', 'snkbd.com', 12.99, 12.99, '2021-11-26', 1, '2024-11-26', '2024-11-26', '2024-11-26', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 1, 'default_ns', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '', '2021-11-26 15:27:57', 1, '2026-02-12 09:44:14', NULL, NULL, NULL),
(715, 715, 9, 1, 1, 1, '', 'shaikhandkhanservices.com', 12.99, 12.99, '2021-11-26', 1, '2024-11-26', '2024-11-26', '2024-11-26', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 1, 'default_ns', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '', '2021-11-26 15:27:57', 1, '2026-02-12 09:44:14', NULL, NULL, NULL),
(716, 716, 10, 1, 1, 1, '', 'apparelglowltd.com', 12.99, 12.99, '2022-11-26', 1, '2024-11-26', '2024-11-26', '2024-11-26', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 1, 'default_ns', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '', '2024-11-21 16:03:43', 1, '2026-02-12 09:44:14', NULL, NULL, NULL),
(717, 717, 2, 1, 1, 1, '', 'koreshikhi.com', 12.99, 12.99, '2024-11-22', 1, '2025-11-22', '2025-11-22', '2025-11-22', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 1, 'default_ns', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '', '2024-11-22 15:47:27', 1, '2026-02-12 09:44:14', NULL, NULL, NULL),
(718, 718, 11, 1, 1, 1, '', 'alakabhattacharjee.com', 12.99, 12.99, '2016-03-29', 1, '2024-03-29', '2024-03-29', '2024-03-29', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 1, 'default_ns', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '', '2024-11-22 16:36:57', 1, '2026-02-12 09:44:14', NULL, NULL, NULL),
(719, 719, 2, 1, 1, 1, '', 'bcsaaa.com', 12.99, 12.99, '2024-12-25', 1, '2025-12-25', '2025-12-25', '2025-12-25', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 1, 'default_ns', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '', '2024-12-25 04:29:58', 1, '2026-02-12 11:12:44', NULL, NULL, NULL),
(720, 721, 12, 1, 1, 1, '', 'alisoftbd.com', 12.99, 12.99, '2024-05-04', 1, '2026-05-04', '2026-05-04', '2026-05-04', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 1, 'default_ns', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '', '2025-05-03 05:59:08', 1, '2026-02-12 11:12:40', NULL, NULL, NULL),
(722, 723, 13, 0, 0, 1, '', 'dwa.com.bd', 0.00, 0.00, '2025-05-06', 1, '2024-12-03', '2024-12-03', '2024-12-03', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 1, 'default_ns', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '', '2025-05-06 02:25:47', 1, '2026-02-12 09:44:14', NULL, NULL, NULL),
(723, 724, 14, 1, 1, 1, '', 'brandnstitch.com', 12.99, 12.99, '2025-08-12', 1, '2026-08-12', '2026-08-12', '2026-08-12', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 1, 'default_ns', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '', '2025-08-12 03:41:24', 1, '2026-02-12 11:12:33', NULL, NULL, NULL),
(724, 726, 14, 1, 1, 1, '', 'emonislam.com', 12.99, 12.99, '2025-04-14', 1, '2026-04-14', '2025-04-21', '2026-04-14', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 1, 'default_ns', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '', '2025-04-14 10:24:19', 1, '2026-02-12 11:12:37', NULL, NULL, NULL);

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
  `remarks` varchar(255) DEFAULT NULL,
  `inserted_on` datetime DEFAULT NULL,
  `inserted_by` int(11) DEFAULT NULL,
  `updated_on` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_services`
--

INSERT INTO `order_services` (`id`, `order_id`, `company_id`, `product_service_id`, `product_service_pricing_id`, `product_service_type_key`, `cp_username`, `cp_password`, `cp_disk_used`, `cp_disk_limit`, `cp_bandwidth_used`, `cp_bandwidth_limit`, `cp_email_accounts`, `cp_email_limit`, `cp_databases`, `cp_database_limit`, `cp_addon_domains`, `cp_addon_limit`, `cp_subdomains`, `cp_subdomain_limit`, `cp_last_sync`, `billing_cycle_id`, `description`, `first_pay_amount`, `recurring_amount`, `hosting_domain`, `license_key`, `license_seats`, `auto_renew`, `reg_date`, `exp_date`, `due_date`, `next_renewal_date`, `suspension_date`, `suspension_reason`, `termination_date`, `is_synced`, `last_sync_dt`, `status`, `remarks`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(701, 702, 1, 2, 3, 'SHARED_HOSTING', NULL, '0', 0.00, 0.00, 0.00, 0.00, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 4, '', 11.15, 11.15, 'erpboi.com', NULL, NULL, 1, '2024-08-10', '2025-08-10', '2025-08-10', '2025-08-10', NULL, NULL, NULL, 1, NULL, 1, '', '2024-08-10 07:56:40', 1, '2026-02-12 09:44:14', NULL, NULL, NULL),
(702, 708, 2, 5, 9, 'SHARED_HOSTING', 'skydigit', '0', 3191.00, 5096.00, 274.45, 0.00, 9, 0, 3, 0, 1, 0, 5, 0, '2026-02-11 06:02:43', 4, '', 37.95, 37.95, 'skydigitalbd.com', NULL, NULL, 1, '2024-08-21', '2024-08-21', '2024-08-21', '2024-08-21', NULL, NULL, NULL, 1, NULL, 1, '', '2024-08-21 03:41:24', 1, '2026-02-12 09:44:14', 1, NULL, NULL),
(704, 710, 8, 5, 9, 'SHARED_HOSTING', 'ultimate', '0', 0.00, 0.00, 0.00, 0.00, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 4, '', 37.95, 57.95, 'ultimateapparelsourcing.com.bd', NULL, NULL, 1, '2020-08-10', '2025-08-10', '2025-08-10', '2025-08-10', NULL, NULL, NULL, 0, NULL, 1, '', '2024-08-22 02:21:52', 1, '2026-02-12 09:44:14', NULL, NULL, NULL),
(705, 713, 5, 15, 15, 'RESELLER_HOSTING', NULL, '0', 0.00, 0.00, 0.00, 0.00, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 4, '', 73.00, 73.00, 'authorservice.org', NULL, NULL, 1, '2024-08-30', '2024-08-27', '2024-08-27', '2024-08-27', NULL, NULL, NULL, 0, NULL, 1, '', '2024-08-30 07:11:19', 1, '2026-02-12 11:14:56', NULL, NULL, NULL),
(706, 714, 9, 5, 9, 'SHARED_HOSTING', 'snkbdcom', '0', 0.00, 0.00, 0.00, 0.00, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 4, '', 37.95, 37.95, 'snkbd.com', NULL, NULL, 1, '2021-11-26', '2024-11-26', '2024-11-26', '2024-11-26', NULL, NULL, NULL, 0, NULL, 1, '', '2021-11-26 15:27:57', 1, '2026-02-12 09:44:14', NULL, NULL, NULL),
(707, 716, 10, 7, 13, 'SHARED_HOSTING', 'apparelglow', '0', 0.00, 0.00, 0.00, 0.00, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 4, '', 72.95, 65.00, 'apparelglowltd.com', NULL, NULL, 1, '2022-11-26', '2024-11-26', '2024-11-26', '2024-11-26', NULL, NULL, NULL, 0, NULL, 1, '', '2024-11-21 16:03:43', 1, '2026-02-12 09:44:14', NULL, NULL, NULL),
(708, 718, 11, 3, 5, 'SHARED_HOSTING', 'alakabhattacharj', '0', 0.00, 0.00, 0.00, 0.00, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 4, '', 25.95, 25.95, 'alakabhattacharjee.com', NULL, NULL, 1, '2016-03-29', '2024-03-29', '2024-03-29', '2024-03-29', NULL, NULL, NULL, 0, NULL, 1, '', '2024-11-22 16:36:57', 1, '2026-02-12 09:44:14', NULL, NULL, NULL),
(709, 720, 2, 11, 23, 'SERVER_VPS', NULL, '0', 0.00, 0.00, 0.00, 0.00, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 4, '', 69.50, 69.50, '', NULL, NULL, 1, '2025-01-16', '2025-01-16', '2025-01-16', '2025-01-16', NULL, NULL, NULL, 0, NULL, 1, '', '2025-01-23 16:43:04', 1, '2026-02-12 09:44:14', NULL, NULL, NULL),
(710, 721, 12, 19, 25, 'SHARED_HOSTING', 'alisoftbd', '0', 0.00, 0.00, 0.00, 0.00, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 4, '', 38.00, 38.00, 'alisoftbd.com', NULL, NULL, 1, '2024-05-04', '2026-05-04', '2026-05-04', '2026-05-04', NULL, NULL, NULL, 0, NULL, 1, '', '2025-05-03 05:59:08', 1, '2026-02-12 09:44:14', NULL, NULL, NULL),
(712, 723, 13, 5, 9, 'SHARED_HOSTING', 'dwacombd', '0', 0.00, 0.00, 0.00, 0.00, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 4, '', 37.95, 37.95, 'dwa.com.bd', NULL, NULL, 1, '2024-12-03', '2024-12-03', '2025-12-03', '2024-12-03', NULL, NULL, NULL, 0, NULL, 1, '', '2025-05-06 02:25:47', 1, '2026-02-12 09:44:14', NULL, NULL, NULL),
(713, 724, 14, 20, 27, 'SHARED_HOSTING', 'brandnstitc', '0', 0.00, 0.00, 0.00, 0.00, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 4, '', 65.00, 65.00, 'brandnstitch.com', NULL, NULL, 1, '2025-08-12', '2026-08-12', '2026-08-12', '2026-08-12', NULL, NULL, NULL, 1, NULL, 1, '', '2025-08-12 03:41:24', 1, '2026-02-12 17:23:21', 1, NULL, NULL),
(714, 725, 2, 11, 23, 'SERVER_VPS', NULL, '0', 0.00, 0.00, 0.00, 0.00, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 4, '', 69.50, 69.50, '', NULL, NULL, 1, '2025-12-04', '2025-12-04', '2025-12-04', '2025-12-04', NULL, NULL, NULL, 0, NULL, 1, '', '2025-12-04 14:32:15', 1, '2026-02-12 11:14:53', NULL, NULL, NULL),
(715, 726, 14, 2, 3, 'SHARED_HOSTING', 'emonisla', '0', 0.00, 0.00, 0.00, 0.00, 0, 0, 0, 0, 0, 0, 0, 0, NULL, 4, '', 20.15, 20.15, 'emonislam.com', NULL, NULL, 1, '2025-04-14', '2026-04-14', '2025-04-21', '2026-04-14', NULL, NULL, NULL, 1, NULL, 1, '', '2025-04-14 10:24:19', 1, '2026-02-12 17:22:31', 1, NULL, NULL);

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
(1, 'Terms and Conditions', 'terms-and-conditions', '<h2>Terms and Conditions</h2><p>Please add your terms and conditions here. <strong>updated</strong></p>', 'Terms and Conditions', '', '', 1, 1, 1, 3, '2026-02-12 21:24:40', NULL, '2026-02-13 03:35:49', 1, NULL, NULL, 1),
(2, 'Privacy Policy', 'privacy-policy', '<h2>Privacy Policy</h2><p>Please add your privacy policy here.</p>', 'Privacy Policy', NULL, NULL, 1, 1, 2, 1, '2026-02-12 21:24:40', NULL, NULL, NULL, NULL, NULL, 1),
(3, 'Refund Policy', 'refund-policy', '<h2>Refund Policy</h2><p>Please add your refund policy here.</p>', 'Refund Policy', '', '', 1, 0, 3, 0, '2026-02-12 21:24:40', NULL, '2026-02-13 03:38:09', 1, NULL, NULL, 1);

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
  `icon_fa_unicode` varchar(30) DEFAULT NULL,
  `pay_type` varchar(8) NOT NULL DEFAULT 'OFFLINE' COMMENT 'OFFLINE,ONLINE',
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

INSERT INTO `payment_gateway` (`id`, `name`, `icon_fa_unicode`, `pay_type`, `merchant_id`, `merchant_pwd`, `instructions`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`) VALUES
(1, 'Offline Payment', 'f3d1', 'OFFLINE', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL);

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

INSERT INTO `product_services` (`id`, `product_service_group_id`, `server_id`, `product_service_module_id`, `product_service_type_id`, `product_name`, `product_desc`, `is_hidden`, `cp_package`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(1, 1, 1, 2, 1, '1GB_SHARED', '<ul>\r\n<li><strong>whmazc_1GB_SHARED</strong></li>\r\n<li>1.0 GB Disk Space</li>\r\n<li>9.8 GB Bandwidth</li>\r\n<li>5 Addon Domains</li>\r\n<li>5 Parked Domains</li>\r\n<li>Unlimited Subdomains</li>\r\n<li>Unlimited FTP Accounts</li>\r\n<li>Unlimited MySQL Databases</li>\r\n<li>Unlimited Email Accounts</li>\r\n<li>Unlimited Mailing Lists</li>\r\n<li>No Shell</li>\r\n<li>CGI Access</li>\r\n</ul>', 0, 'whmazc_1GB_SHARED', 1, NULL, NULL, '2026-01-27 22:02:15', 1, NULL, NULL),
(2, 1, 1, 2, 1, '2GB_SHARED', '<ul>\r\n<li><strong>whmazc_2GB_SHARED</strong></li>\r\n<li>2.0 GB Disk Space</li>\r\n<li>19.5 GB Bandwidth</li>\r\n<li>5 Addon Domains</li>\r\n<li>5 Parked Domains</li>\r\n<li>Unlimited Subdomains</li>\r\n<li>Unlimited FTP Accounts</li>\r\n<li>Unlimited MySQL Databases</li>\r\n<li>Unlimited Email Accounts</li>\r\n<li>Unlimited Mailing Lists</li>\r\n<li>No Shell</li>\r\n<li>CGI Access</li>\r\n</ul>', 0, 'whmazc_2GB_SHARED', 1, NULL, NULL, '2026-01-27 22:01:03', 1, NULL, NULL),
(3, 1, 1, 2, 1, '3GB_SHARED', '<ul>\r\n<li><strong>whmazc_3GB_SHARED</strong></li>\r\n<li>3.0 GB Disk Space</li>\r\n<li>Unlimited Bandwidth</li>\r\n<li>Unlimited Addon Domains</li>\r\n<li>Unlimited Parked Domains</li>\r\n<li>Unlimited Subdomains</li>\r\n<li>Unlimited FTP Accounts</li>\r\n<li>Unlimited MySQL Databases</li>\r\n<li>Unlimited Email Accounts</li>\r\n<li>Unlimited Mailing Lists</li>\r\n<li>No Shell</li>\r\n<li>CGI Access</li>\r\n</ul>', 0, 'whmazc_3GB_SHARED', 1, NULL, NULL, '2026-01-27 22:00:13', 1, NULL, NULL),
(4, 1, 1, 2, 1, '5GB_SHARED', '<ul>\r\n<li><strong>whmazc_5GB_SHARED</strong></li>\r\n<li>5.0 GB Disk Space</li>\r\n<li>Unlimited Bandwidth</li>\r\n<li>Unlimited Addon Domains</li>\r\n<li>Unlimited Parked Domains</li>\r\n<li>Unlimited Subdomains</li>\r\n<li>Unlimited FTP Accounts</li>\r\n<li>Unlimited MySQL Databases</li>\r\n<li>Unlimited Email Accounts</li>\r\n<li>Unlimited Mailing Lists</li>\r\n<li>No Shell</li>\r\n<li>CGI Access</li>\r\n</ul>', 0, 'whmazc_5GB_SHARED', 1, NULL, NULL, '2026-01-27 21:59:25', 1, NULL, NULL),
(5, 5, 1, 2, 1, 'Corp_3GB', '<ul>\r\n<li><strong>whmazc_3GB_SHARED</strong></li>\r\n<li>3.0 GB Disk Space</li>\r\n<li>Unlimited Bandwidth</li>\r\n<li>Unlimited Addon Domains</li>\r\n<li>Unlimited Parked Domains</li>\r\n<li>Unlimited Subdomains</li>\r\n<li>Unlimited FTP Accounts</li>\r\n<li>Unlimited MySQL Databases</li>\r\n<li>Unlimited Email Accounts</li>\r\n<li>Unlimited Mailing Lists</li>\r\n<li>No Shell</li>\r\n<li>CGI Access</li>\r\n</ul>', 0, 'whmazc_3GB_SHARED', 1, NULL, NULL, '2026-01-27 21:58:23', 1, NULL, NULL),
(6, 5, 1, 2, 1, 'Corp_5GB', '<ul>\r\n<li><strong>whmazc_5GB_SHARED</strong></li>\r\n<li>5.0 GB Disk Space</li>\r\n<li>Unlimited Bandwidth</li>\r\n<li>Unlimited Addon Domains</li>\r\n<li>Unlimited Parked Domains</li>\r\n<li>Unlimited Subdomains</li>\r\n<li>Unlimited FTP Accounts</li>\r\n<li>Unlimited MySQL Databases</li>\r\n<li>Unlimited Email Accounts</li>\r\n<li>Unlimited Mailing Lists</li>\r\n<li>No Shell</li>\r\n<li>CGI Access</li>\r\n</ul>', 0, 'whmazc_5GB_SHARED', 1, NULL, NULL, '2026-01-27 21:57:30', 1, NULL, NULL),
(7, 5, 1, 2, 1, 'Corp_10GB', '<ul>\r\n<li><strong>whmazc_10GB_SHARED</strong></li>\r\n<li>9.8 GB Disk Space</li>\r\n<li>Unlimited Bandwidth</li>\r\n<li>Unlimited Addon Domains</li>\r\n<li>Unlimited Parked Domains</li>\r\n<li>Unlimited Subdomains</li>\r\n<li>Unlimited FTP Accounts</li>\r\n<li>Unlimited MySQL Databases</li>\r\n<li>Unlimited Email Accounts</li>\r\n<li>Unlimited Mailing Lists</li>\r\n<li>No Shell</li>\r\n<li>CGI Access</li>\r\n</ul>', 0, 'whmazc_10GB_SHARED', 1, NULL, NULL, '2026-01-27 21:56:31', 1, NULL, NULL),
(11, 4, 3, 1, 3, 'uVPS-1', '<strong>200 GB of space</strong><br />\r\nunlimited bandwidth<br />\r\n8 GB RAM (guaranteed)<br />\r\n4 CPU cores<br />\r\nNo control panel<br />\r\n<strong>Full root access</strong><br />\r\nPure SSD, KVM', 0, NULL, 1, NULL, NULL, '2024-08-21 23:48:36', NULL, NULL, NULL),
(12, 4, 3, 1, 3, 'uVPS-2', '<strong>400 GB of space</strong><br />\r\nunlimited bandwidth<br />\r\n16 GB RAM (guaranteed)<br />\r\n6 CPU cores<br />\r\nNo control panel<br />\r\n<strong>Full root access</strong><br />\r\nPure SSD, KVM', 0, NULL, 1, NULL, NULL, '2024-08-21 23:48:39', NULL, NULL, NULL),
(13, 4, 3, 1, 3, 'uVPS-3', '<strong>800 GB of space</strong><br />\r\nunlimited bandwidth<br />\r\n30 GB RAM (guaranteed)<br />\r\n8 CPU cores<br />\r\nNo control panel<br />\r\n<strong>Full root access</strong><br />\r\nPure SSD, KVM', 0, NULL, 1, NULL, NULL, '2024-08-21 23:48:42', NULL, NULL, NULL),
(15, 2, 1, 2, 2, 'Small', '<strong>15GB disk space</strong><br />\r\n150 GB bandwidth per month<br />\r\n10 cPanel Accounts<br />\r\nUpto 50 Website point<br />\r\n<strong>No WHMCS</strong><br />\r\nUnlimited database<br />\r\nUnlimited ftp accounts<br />\r\n<strong>No Shell</strong>', 0, NULL, 1, NULL, NULL, '2022-10-09 10:53:19', NULL, NULL, NULL),
(16, 2, 1, 2, 2, 'Professional', '<strong>100 GB disk space</strong><br />\r\nUnlimited bandwidth<br />\r\n50 Accounts<br />\r\n<strong>WHMCS</strong><br />\r\n<strong>Shell Access</strong>', 0, NULL, 1, NULL, NULL, '2022-10-09 10:53:19', NULL, NULL, NULL),
(17, 2, 1, 2, 2, 'Starter', '<strong>50 GB disk space</strong><br />\r\nUnlimited bandwidth<br />\r\n30 Accounts<br />\r\n<strong>WHMCS</strong><br />\r\n<strong>No Shell</strong>', 0, NULL, 1, NULL, NULL, '2022-10-09 10:53:19', NULL, NULL, NULL),
(18, 2, 1, 2, 2, 'Basic', '<strong>25 GB disk space</strong><br />\r\nUnlimited bandwidth<br />\r\n20 Accounts<br />\r\n<strong>No WHMCS</strong><br />\r\n<strong>No Shell</strong>', 0, NULL, 1, NULL, NULL, '2022-10-09 10:53:19', NULL, NULL, NULL),
(19, 1, 1, 2, 1, '6GB_SHARED', '<ul>\r\n<li><strong>whmazc_6GB_SHARED</strong></li>\r\n<li>6.0 GB Disk Space</li>\r\n<li>Unlimited Bandwidth</li>\r\n<li>Unlimited Addon Domains</li>\r\n<li>Unlimited Parked Domains</li>\r\n<li>Unlimited Subdomains</li>\r\n<li>Unlimited FTP Accounts</li>\r\n<li>Unlimited MySQL Databases</li>\r\n<li>Unlimited Email Accounts</li>\r\n<li>Unlimited Mailing Lists</li>\r\n<li>No Shell</li>\r\n<li>CGI Access</li>\r\n</ul>', 0, 'whmazc_6GB_SHARED', 1, NULL, NULL, '2026-01-27 21:54:54', 1, NULL, NULL),
(20, 1, 1, 2, 1, '10GB_SHARED', '<ul>\r\n<li><strong>whmazc_10GB_SHARED</strong></li>\r\n<li>9.8 GB Disk Space</li>\r\n<li>Unlimited Bandwidth</li>\r\n<li>Unlimited Addon Domains</li>\r\n<li>Unlimited Parked Domains</li>\r\n<li>Unlimited Subdomains</li>\r\n<li>Unlimited FTP Accounts</li>\r\n<li>Unlimited MySQL Databases</li>\r\n<li>Unlimited Email Accounts</li>\r\n<li>Unlimited Mailing Lists</li>\r\n<li>No Shell</li>\r\n<li>CGI Access</li>\r\n</ul>', 0, 'whmazc_10GB_SHARED', 1, NULL, NULL, '2026-01-27 21:54:13', 1, NULL, NULL);

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
-- Table structure for table `product_service_modules`
--

CREATE TABLE `product_service_modules` (
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
-- Dumping data for table `product_service_modules`
--

INSERT INTO `product_service_modules` (`id`, `module_name`, `remarks`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`) VALUES
(1, 'No Module', 'No modules', 1, '2020-08-20 20:26:51', NULL, '2024-08-03 01:29:42', 1),
(2, 'cPanel', 'cPanel server', 1, '2020-08-20 20:26:51', NULL, '2024-08-03 01:30:03', 1);

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
(27, 20, 1, 65, 4, 1, '2020-09-05 08:17:52', NULL, '2025-05-03 03:56:53', NULL, NULL, NULL),
(28, 20, 2, 8190, 4, 1, '2020-09-05 08:17:52', NULL, '2026-01-22 11:37:14', 1, NULL, NULL);

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

INSERT INTO `servers` (`id`, `name`, `ip_addr`, `hostname`, `dns1`, `dns1_ip`, `dns2`, `dns2_ip`, `dns3`, `dns3_ip`, `dns4`, `dns4_ip`, `max_accounts`, `type`, `username`, `authpass`, `access_hash`, `port`, `is_secure`, `noc`, `remarks`, `status`, `inserted_on`, `inserted_by`, `updated_on`, `updated_by`, `deleted_on`, `deleted_by`) VALUES
(1, 'Reseller-ns1.whmaz.com', '67.222.24.126', 'server.whmaz.com', 'ns1.whmaz.com', '67.222.24.126', 'ns2.whmaz.com', '67.222.24.126', '', '', '', '', NULL, NULL, 'whmazc', NULL, 'U0ZJd1RUVk5Remt4U1RGRU1VVkhNRVZJU1ZOTlVqSTNPVmszTlRKR1YxWT0=', 2087, 1, NULL, 'testing', 1, NULL, NULL, '2026-01-25 21:05:17', 1, '2024-08-04 04:03:16', 1),
(2, 'KNOWHOST - RESELLER', '170.249.236.236', 'cp22-ga.privatesystems.net', 'cp1.privatesystems.net', '158.106.136.90', 'cp2.privatesystems.net', '108.160.157.7', 'cp3.privatesystems.net', '158.106.136.36', 'cp4.privatesystems.net', '158.106.136.90', NULL, NULL, 'enamingo', NULL, '.', 2087, 1, NULL, 'reseller hosting server', 0, '2024-08-09 16:06:20', 1, '2024-08-09 14:06:20', NULL, '2026-02-12 09:19:47', 1),
(3, 'Contabo', '1.1.1.1', 'self', 'self1.contabo.com', '1.1.1.1', 'self2.contabo.com', '1.1.1.1', 'self3.contabo.com', '1.1.1.1', 'self4.contabo.com', '1.1.1.1', NULL, NULL, 'root', NULL, 'root', 80, 0, NULL, 'root', 1, '2024-08-20 17:22:41', 1, '2024-08-20 15:22:41', NULL, NULL, NULL);

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
(1, 'DefaultNameServer1', 'ns1.tongbari.com', 'DNS', '2022-10-09 16:43:09', '2022-10-09 16:43:09'),
(2, 'DefaultNameServer2', 'ns2.tongbari.com', 'DNS', '2022-10-09 16:43:09', '2022-10-09 16:43:09'),
(3, 'DefaultNameServer3', NULL, 'DNS', '2022-10-09 16:43:09', '2022-10-09 16:43:09'),
(4, 'DefaultNameServer4', NULL, 'DNS', '2022-10-09 16:43:09', '2022-10-09 16:43:09'),
(5, 'cron_secret_key', 'a7f3b2c9d8e4f1a0b5c6d7e8f9a0b1c2d3e4f5a6b7c8d9e0f1a2b3c4d5e6f7a8', 'SYSTEM', '2026-02-11 23:12:43', '2026-02-11 23:12:43');

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
(1, 'Support', 'Technical supports', 'support@whmaz.com', 1, 1, NULL, NULL, '2020-08-30 03:50:59', NULL),
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
(1, 'Md.', 'Sarker', 'info.errorpoint@gmail.com', '+8801824880161', '+8801824880161', 'Company Owner', '$2y$10$K5sPdY44CQnB.SSedQ5bTukbhSmxidJYpTLs1MhTBhrx995MR7C4e', 'd0bd456b40118604bafe0f2966cace3db3c4791532357cfa6f28f239a8bd7751', NULL, NULL, 1, 0, 0, 1, '', '2020-08-29 10:25:41', 1, '2026-01-31 16:38:40', NULL, NULL, NULL, NULL, NULL),
(3, 'A.K.M', 'Ashrafuzzaman', 'aashrafuzzaman@yahoo.com', '+8801674196502', '+8801674196502', 'Company Owner', '$2y$10$70a6oyF1KBIXUM0DJj.qbuGyD9T1hg.OIiQOpEeOi239y083QbBPa', NULL, NULL, NULL, 7, 0, 0, 1, NULL, '2024-08-10 08:57:10', 1, '2024-08-10 02:58:59', NULL, NULL, NULL, NULL, NULL),
(4, 'ABM', 'Ataullah', 'abm.ataullah@gmail.com', '+8801610215271', '+8801610215271', 'Company Owner', '$2y$10$dmRxtkp5n0FK3kttdJaSl.qifFps1GLHPe5fgPzac6zF/gfHImTpK', NULL, NULL, NULL, 6, 0, 0, 1, NULL, '2024-08-10 06:05:41', 1, '2024-08-10 04:05:41', NULL, NULL, NULL, NULL, NULL),
(5, 'Mahbub Hasan', 'Shuman', 'shumanmahbub@gmail.com', '+261.345002256', '+261.345002256', 'Company Owner', '$2y$10$R0KgH5m7t5cobqKIE2i3yuO.5IYX/PtJ4m2K2jcOmgtboMvJGpe9C', NULL, NULL, NULL, 4, 0, 0, 1, NULL, '2024-08-10 06:05:45', 1, '2024-08-10 04:05:45', NULL, NULL, NULL, NULL, NULL),
(6, 'Shahid', 'Swapon', 'shahid.swapon@gmail.com', '+8801713257212', '+8801713257212', 'Company Owner', '$2y$10$dQCqGjkFEcP.tEApFUtRiehPrHR9Fs2uyWWJW3Kb.SYvt.aS4k7zK', NULL, NULL, NULL, 5, 0, 0, 1, NULL, '2024-08-10 06:05:49', 1, '2024-08-10 04:05:49', NULL, NULL, NULL, NULL, NULL),
(7, 'Md', 'Ismail', 'ismail4g@gmail.com', '+8801730704604', '+8801730704604', 'Company Owner', '$2y$10$T5WrLNGOYDTumIzvZXOePezDJSOohoUfv8brThSF9TPX/8gvbyNcS', NULL, NULL, NULL, 2, 0, 0, 1, NULL, '2024-08-10 06:05:52', 1, '2024-08-10 04:05:52', NULL, NULL, NULL, NULL, NULL),
(8, 'Md. Shafiqul', 'Islam', 'techlogbd@gmail.com', '+8801636020790', '+8801636020790', 'Company Owner', '$2y$10$60JSl8tHBNfZw/Cf6HLkV.fGFKiu/n031u18BE1cVPT7mZl.0NnEK', NULL, NULL, NULL, 3, 0, 0, 1, NULL, '2024-08-10 06:05:54', 1, '2024-08-10 04:05:54', NULL, NULL, NULL, NULL, NULL),
(9, 'James', 'Habib', 'jameshabib02@gmail.com', '8801716057226', '8801716057226', 'Company Owner', '$2y$10$xcmPIh3/py6tg3LRsaNvdOAprXPoYu2TkJn1iFXvQpTbwV12T8lOG', NULL, NULL, NULL, 8, 0, 0, 1, NULL, '2024-08-22 02:19:05', 1, '2024-08-22 00:19:05', NULL, NULL, NULL, NULL, NULL),
(10, 'Arifur Rahman', 'Khan', 'arkhan.office@gmail.com', '+8801716382979', '+8801716382979', 'Company Owner', '$2y$10$Zc7QJD.1G.78bFFNoinPUO2q6C7P026jEQfHqniUttInnlNjZXl4C', NULL, NULL, NULL, 9, 0, 0, 1, NULL, '2024-11-05 15:21:16', 1, '2024-11-05 14:21:16', NULL, NULL, NULL, NULL, NULL),
(11, 'Masud', 'Rana', 'apparelglowltd@gmail.com', '01715309710', '01715309710', 'Company Owner', '$2y$10$Zs0gv6iq0i4TQpXcUA3pa.Jrpeav7el4u8HAFu.PUyTIbpu.IIOMW', NULL, NULL, NULL, 10, 0, 0, 1, NULL, '2024-11-21 15:59:49', 1, '2024-11-21 14:59:49', NULL, NULL, NULL, NULL, NULL),
(12, 'Alaka', 'Bhattacharjee', 'aporajita2001@gmail.com', '+8801731795993', '+8801731795993', 'Company Owner', '$2y$10$pkXufw0rR/unfSzmuhVJx.9f8.FR5Z8veHayJaM50xqbM95iQ.SWy', NULL, NULL, NULL, 11, 0, 0, 1, NULL, '2024-11-22 16:22:00', 1, '2024-11-22 15:22:00', NULL, NULL, NULL, NULL, NULL),
(13, 'Zuel', 'Ali', 'alisoftbdinfo@gmail.com', '8801515293030', '8801515293030', 'Company Owner', '$2y$10$SDeaZqAM0VJ2sgOIqkI3Vupbw7sjvc8Eq4g3nYmo65jD2kgAjdRZ2', NULL, NULL, NULL, 12, 0, 0, 1, NULL, '2025-05-03 05:43:20', 1, '2025-05-03 03:43:20', NULL, NULL, NULL, NULL, NULL),
(14, 'Zakaria', 'Imtiaz', 'imtiaz71985@gmail.com', '8801980484968', '', 'Company Owner', '$2y$10$eKUBYUhPdKQPqyXJPox2g.vd7AQPxCQY5mAgOJ.Sb0YhtpAp/H87.', NULL, NULL, NULL, 13, 0, 0, 1, NULL, '2025-05-06 02:19:59', 1, '2025-05-06 00:19:58', NULL, NULL, NULL, NULL, NULL),
(15, 'Md. Saidul Islam', 'Emon', 'emon072033@gmail.com', '+8801536194103', '+8801536194103', 'Company Owner', '$2y$10$4s99C6tURzo8goJk6cCsEu.v3V9ckd3jFm27KQ5NQIk.81hWIxgIe', NULL, NULL, NULL, 14, 0, 0, 1, NULL, '2025-08-12 03:37:39', 1, '2026-02-10 04:15:52', NULL, NULL, NULL, NULL, NULL);

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
(1, 1, '2020-08-29 11:35:47', '0', '::1', NULL, 0),
(2, 1, '2020-08-29 11:37:05', '0', '::1', NULL, 0),
(3, 1, '2020-08-29 11:37:16', '0', '::1', NULL, 0),
(4, 1, '2020-08-29 11:37:27', '0', '::1', NULL, 0),
(5, 1, '2020-08-29 11:43:11', '0', '::1', NULL, 0),
(6, 1, '2020-08-29 11:43:34', '0', '::1', NULL, 0),
(7, 1, '2020-08-30 07:39:35', '0', '::1', NULL, 0),
(8, 1, '2020-08-30 08:31:26', '0', '::1', NULL, 1),
(9, 1, '2020-08-30 10:33:07', '0', '::1', NULL, 1),
(10, 1, '2020-09-01 07:51:34', '0', '::1', NULL, 1),
(11, 1, '2020-09-01 10:36:40', '0', '::1', NULL, 1),
(12, 1, '2020-09-01 18:48:20', '0', '::1', NULL, 1),
(13, 1, '2020-09-03 09:37:06', '0', '::1', NULL, 1),
(14, 1, '2020-09-03 10:07:44', '0', '::1', NULL, 1),
(15, 1, '2020-09-04 06:34:48', '0', '::1', NULL, 1),
(16, 1, '2020-09-04 17:09:26', '0', '::1', NULL, 1),
(17, 1, '2020-09-04 21:28:02', '0', '::1', NULL, 1),
(18, 1, '2020-09-05 08:23:09', '0', '::1', NULL, 1),
(19, 1, '2020-09-05 16:04:47', '0', '::1', NULL, 1),
(20, 1, '2020-09-08 09:02:04', '0', '::1', NULL, 1),
(21, 1, '2020-09-08 19:28:57', '0', '::1', NULL, 1),
(22, 1, '2020-09-12 06:19:22', '0', '::1', NULL, 1),
(23, 1, '2021-03-20 06:39:45', '0', '::1', NULL, 1),
(24, 1, '2022-09-17 17:07:41', '0', '::1', NULL, 1),
(25, 1, '2022-09-29 15:13:49', '0', '::1', NULL, 1),
(26, 1, '2022-10-01 11:33:43', '0', '::1', NULL, 1),
(27, 1, '2022-10-01 15:44:59', '0', '::1', NULL, 1),
(28, 1, '2022-10-01 16:20:08', '0', '::1', NULL, 1),
(29, 1, '2022-10-06 11:43:18', '0', '::1', NULL, 1),
(30, 1, '2022-10-09 16:14:04', '0', '::1', NULL, 1),
(31, 1, '2022-10-09 16:17:47', '0', '::1', NULL, 1),
(32, 1, '2022-11-02 09:29:17', '0', '::1', NULL, 1),
(33, 1, '2023-06-24 10:31:25', '0', '::1', NULL, 1),
(34, 1, '2024-02-26 12:49:13', '0', '::1', NULL, 1),
(35, 1, '2024-02-26 12:52:21', '0', '::1', NULL, 1),
(36, 1, '2024-02-26 13:04:46', '0', '::1', NULL, 1),
(37, 1, '2024-03-01 15:21:02', '0', '::1', NULL, 1),
(38, 1, '2024-03-04 17:04:10', '0', '::1', NULL, 1),
(39, 1, '2024-03-05 16:27:17', '0', '::1', NULL, 1),
(40, 1, '2024-03-06 15:40:54', '0', '::1', NULL, 1),
(41, 1, '2024-03-09 15:54:02', '0', '::1', NULL, 1),
(42, 1, '2024-03-09 15:56:52', '0', '::1', NULL, 1),
(43, 1, '2024-03-17 16:33:20', '0', '::1', NULL, 1),
(44, 1, '2024-03-19 12:10:53', '0', '::1', NULL, 1),
(45, 1, '2024-03-20 12:36:38', '0', '::1', NULL, 1),
(46, 1, '2024-03-20 13:01:59', '0', '::1', NULL, 1),
(47, 1, '2024-03-20 13:07:15', '0', '::1', NULL, 1),
(48, 1, '2024-03-20 16:24:27', '0', '::1', NULL, 1),
(49, 1, '2024-03-21 12:36:10', '0', '::1', NULL, 1),
(50, 1, '2024-03-21 16:18:14', '0', '::1', NULL, 1),
(51, 1, '2024-03-22 06:16:06', '0', '::1', NULL, 1),
(52, 1, '2024-03-22 15:23:56', '0', '::1', NULL, 1),
(53, 1, '2024-03-22 16:16:19', '0', '::1', NULL, 1),
(54, 1, '2024-03-23 03:31:46', '0', '::1', NULL, 1),
(55, 1, '2024-03-23 04:32:48', '0', '::1', NULL, 1),
(56, 1, '2024-03-26 09:44:55', '0', '::1', NULL, 1),
(57, 1, '2024-03-26 13:03:45', '0', '::1', NULL, 1),
(58, 1, '2024-07-22 04:40:33', '0', '::1', NULL, 1),
(59, 1, '2024-07-22 16:59:09', '0', '::1', NULL, 1),
(60, 1, '2024-07-23 06:37:37', '0', '::1', NULL, 1),
(61, 1, '2024-07-26 02:44:44', '0', '::1', NULL, 1),
(62, 1, '2024-07-26 03:18:38', '0', '::1', NULL, 1),
(63, 1, '2024-07-26 08:07:44', '0', '::1', NULL, 1),
(64, 1, '2024-07-29 16:38:17', '0', '::1', NULL, 1),
(65, 1, '2024-07-30 14:33:25', '0', '::1', NULL, 1),
(66, 1, '2024-07-30 14:34:41', '0', '::1', NULL, 1),
(67, 1, '2024-07-30 14:35:51', '0', '::1', NULL, 1),
(68, 1, '2024-08-02 04:23:53', '0', '::1', NULL, 1),
(69, 1, '2024-08-02 07:45:00', '0', '::1', NULL, 1),
(70, 1, '2024-08-05 10:42:43', '0', '::1', NULL, 1),
(71, 1, '2024-08-05 12:20:27', '0', '::1', NULL, 1),
(72, 1, '2024-08-07 18:36:34', '0', '::1', NULL, 1),
(73, 1, '2024-08-11 02:11:22', '0', '::1', NULL, 1),
(74, 7, '2024-08-17 13:31:18', '0', '::1', NULL, 1),
(75, 7, '2024-08-17 13:35:22', '0', '::1', NULL, 1),
(76, 1, '2024-08-17 13:58:07', '0', '::1', NULL, 1),
(77, 7, '2024-08-21 03:44:23', '0', '::1', NULL, 1),
(78, 7, '2024-08-22 01:31:16', '0', '::1', NULL, 1),
(79, 1, '2024-11-16 03:21:31', '0', '::1', NULL, 1),
(80, 1, '2024-11-16 07:52:01', '0', '::1', NULL, 1),
(81, 10, '2024-11-16 08:16:12', '0', '::1', NULL, 1),
(82, 10, '2024-11-16 16:29:39', '0', '::1', NULL, 1),
(83, 7, '2025-05-03 05:13:52', '0', '::1', NULL, 1),
(84, 7, '2025-05-03 05:25:33', '0', '::1', NULL, 1),
(85, 7, '2025-05-03 00:11:58', '0', '103.199.84.169', NULL, 1),
(86, 7, '2025-08-23 07:04:41', '0', '::1', NULL, 1),
(87, 7, '2026-01-13 16:37:51', '0', '::1', NULL, 1),
(88, 7, '2026-01-23 16:23:16', '0', '127.0.0.1', NULL, 1),
(89, 7, '2026-01-24 02:35:40', '0', '127.0.0.1', NULL, 1),
(90, 7, '2026-01-26 13:53:01', '0', '127.0.0.1', NULL, 1),
(91, 7, '2026-01-26 13:56:31', '0', '127.0.0.1', NULL, 1),
(92, 7, '2026-01-26 13:59:06', '0', '127.0.0.1', NULL, 1),
(93, 7, '2026-01-26 14:01:33', '0', '127.0.0.1', NULL, 1),
(94, 7, '2026-01-26 15:42:30', '0', '127.0.0.1', NULL, 1),
(95, 7, '2026-01-30 15:18:55', '0', '::1', NULL, 1),
(96, 1, '2026-01-31 16:44:10', '0', '::1', NULL, 1),
(97, 1, '2026-01-31 16:47:46', '0', '::1', NULL, 1),
(98, 15, '2026-02-09 22:16:02', '0', '182.163.99.83', NULL, 1),
(99, 7, '2026-02-10 15:48:28', '0', '127.0.0.1', NULL, 1),
(100, 1, '2026-02-10 17:51:37', '0', '127.0.0.1', NULL, 1),
(101, 1, '2026-02-11 01:47:29', '0', '127.0.0.1', NULL, 1),
(102, 7, '2026-02-11 05:49:24', '0', '127.0.0.1', NULL, 1),
(103, 1, '2026-02-12 02:59:35', '0', '127.0.0.1', NULL, 1),
(104, 1, '2026-02-12 03:49:45', '0', '127.0.0.1', NULL, 1),
(105, 1, '2026-02-12 03:52:45', '0', '127.0.0.1', NULL, 1),
(106, 1, '2026-02-12 04:19:26', '0', '127.0.0.1', NULL, 1),
(107, 1, '2026-02-12 06:59:30', '0', '127.0.0.1', NULL, 1),
(108, 1, '2026-02-12 06:24:59', '0', '103.159.72.16', NULL, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `add_to_carts`
--
ALTER TABLE `add_to_carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `customer_session_id` (`customer_session_id`);

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
  ADD PRIMARY KEY (`id`);

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
  ADD KEY `idx_invoice_txn_transaction` (`transaction_id`);

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
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `order_services`
--
ALTER TABLE `order_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `company_id` (`company_id`);

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
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pending_executions`
--
ALTER TABLE `pending_executions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `is_completed` (`is_completed`);

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
-- Indexes for table `product_service_modules`
--
ALTER TABLE `product_service_modules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_service_pricing`
--
ALTER TABLE `product_service_pricing`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_service_types`
--
ALTER TABLE `product_service_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `servers`
--
ALTER TABLE `servers`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `add_to_carts`
--
ALTER TABLE `add_to_carts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `admin_logins`
--
ALTER TABLE `admin_logins`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

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
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

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
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=553;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=570;

--
-- AUTO_INCREMENT for table `invoice_txn`
--
ALTER TABLE `invoice_txn`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=727;

--
-- AUTO_INCREMENT for table `order_domains`
--
ALTER TABLE `order_domains`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=725;

--
-- AUTO_INCREMENT for table `order_services`
--
ALTER TABLE `order_services`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=716;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `page_history`
--
ALTER TABLE `page_history`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment_gateway`
--
ALTER TABLE `payment_gateway`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pending_executions`
--
ALTER TABLE `pending_executions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_services`
--
ALTER TABLE `product_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `product_service_groups`
--
ALTER TABLE `product_service_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `product_service_modules`
--
ALTER TABLE `product_service_modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `product_service_pricing`
--
ALTER TABLE `product_service_pricing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `product_service_types`
--
ALTER TABLE `product_service_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `servers`
--
ALTER TABLE `servers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sys_cnf`
--
ALTER TABLE `sys_cnf`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `user_logins`
--
ALTER TABLE `user_logins`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `page_history`
--
ALTER TABLE `page_history`
  ADD CONSTRAINT `fk_page_history_page` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
