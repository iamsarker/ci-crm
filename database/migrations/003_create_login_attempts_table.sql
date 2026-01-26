-- =====================================================
-- Migration: Create login_attempts table
-- Description: Rate limiting for login attempts (brute force protection)
-- Version: 1.0.2
-- Date: 2026-01-26
-- =====================================================

-- Create login_attempts table for rate limiting
CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `identifier` VARCHAR(255) NOT NULL COMMENT 'IP address or email',
    `identifier_type` ENUM('ip', 'email') NOT NULL DEFAULT 'ip',
    `ip_address` VARCHAR(45) NOT NULL COMMENT 'IP address of the attempt',
    `user_agent` VARCHAR(255) DEFAULT NULL,
    `is_successful` TINYINT(1) NOT NULL DEFAULT 0,
    `attempt_time` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_identifier` (`identifier`, `identifier_type`),
    INDEX `idx_attempt_time` (`attempt_time`),
    INDEX `idx_ip_time` (`ip_address`, `attempt_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- RATE LIMITING CONFIGURATION (in Loginattempt_model.php):
-- - MAX_ATTEMPTS: 5 failed attempts before lockout
-- - LOCKOUT_TIME: 15 minutes lockout duration
-- - Auto-cleanup: Records older than 24 hours are deleted
-- =====================================================
