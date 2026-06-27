-- =============================================================================
-- WHMAZ software releases (the installable ZIP customers download)
-- =============================================================================
-- A single build serves all three plans (Basic/Pro/Max) — the release is
-- plan-agnostic. Plan differences are enforced at runtime by the installed app
-- via the license/verify phone-home feature map, not by shipping different ZIPs.
--
-- Files live in uploadedfiles/software/ (deny-all .htaccess) and are only served
-- through the license-gated download endpoints. Exactly one row is is_current=1.
--
-- Idempotent. Mirrors src/migrations/20260627130000_create_software_releases.php.
-- =============================================================================

CREATE TABLE IF NOT EXISTS `software_releases` (
  `id`            bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `version`       varchar(40)  NOT NULL,
  `file_name`     varchar(255) NOT NULL COMMENT 'Stored (random) filename in uploadedfiles/software',
  `original_name` varchar(255) DEFAULT NULL COMMENT 'Original uploaded filename',
  `file_size`     bigint(20)   DEFAULT NULL COMMENT 'Bytes',
  `changelog`     text         DEFAULT NULL,
  `is_current`    tinyint(1)   NOT NULL DEFAULT 0 COMMENT 'The release customers download',
  `status`        tinyint(4)   NOT NULL DEFAULT 1 COMMENT '1=active, 0=deleted',
  `uploaded_by`   int(11)      DEFAULT NULL,
  `uploaded_on`   datetime     DEFAULT NULL,
  `updated_on`    timestamp    NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_software_releases_current` (`is_current`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
