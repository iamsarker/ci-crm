-- ===========================================
-- Dynamic Pages System
-- Created: 2024
-- ===========================================

-- Main Pages Table
CREATE TABLE IF NOT EXISTS `pages` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `page_title` VARCHAR(255) NOT NULL,
    `page_slug` VARCHAR(255) NOT NULL,
    `page_content` LONGTEXT NULL,
    `meta_title` VARCHAR(255) NULL,
    `meta_description` TEXT NULL,
    `meta_keywords` VARCHAR(500) NULL,
    `is_published` TINYINT(1) NOT NULL DEFAULT 0,
    `is_system` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'System pages cannot be deleted',
    `sort_order` INT(11) NOT NULL DEFAULT 0,
    `total_view` INT(11) NOT NULL DEFAULT 0,
    `inserted_on` DATETIME NULL,
    `inserted_by` INT(11) NULL,
    `updated_on` DATETIME NULL,
    `updated_by` INT(11) NULL,
    `deleted_on` DATETIME NULL,
    `deleted_by` INT(11) NULL,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_page_slug` (`page_slug`),
    KEY `idx_status` (`status`),
    KEY `idx_is_published` (`is_published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Page History Table for Version Control
CREATE TABLE IF NOT EXISTS `page_history` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `page_id` INT(11) UNSIGNED NOT NULL,
    `page_title` VARCHAR(255) NOT NULL,
    `page_content` LONGTEXT NULL,
    `meta_title` VARCHAR(255) NULL,
    `meta_description` TEXT NULL,
    `change_type` ENUM('created', 'updated', 'restored') NOT NULL DEFAULT 'updated',
    `change_note` VARCHAR(500) NULL,
    `changed_by` INT(11) NULL,
    `changed_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_page_id` (`page_id`),
    KEY `idx_changed_at` (`changed_at`),
    CONSTRAINT `fk_page_history_page` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default pages
INSERT INTO `pages` (`page_title`, `page_slug`, `page_content`, `meta_title`, `is_published`, `is_system`, `sort_order`, `inserted_on`, `status`) VALUES
('Terms and Conditions', 'terms-and-conditions', '<h2>Terms and Conditions</h2><p>Please add your terms and conditions here.</p>', 'Terms and Conditions', 1, 1, 1, NOW(), 1),
('Privacy Policy', 'privacy-policy', '<h2>Privacy Policy</h2><p>Please add your privacy policy here.</p>', 'Privacy Policy', 1, 1, 2, NOW(), 1),
('Refund Policy', 'refund-policy', '<h2>Refund Policy</h2><p>Please add your refund policy here.</p>', 'Refund Policy', 0, 0, 3, NOW(), 1);

-- Create view for listing
CREATE OR REPLACE VIEW `pages_view` AS
SELECT
    p.*,
    u1.username as created_by_name,
    u2.username as updated_by_name
FROM `pages` p
LEFT JOIN `admin_users` u1 ON p.inserted_by = u1.id
LEFT JOIN `admin_users` u2 ON p.updated_by = u2.id
WHERE p.status = 1;
