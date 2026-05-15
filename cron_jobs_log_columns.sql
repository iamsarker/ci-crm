-- Extend `cron_jobs` to also store run logs written by Cronjob_model::logCronjobExecution().
-- Old rows used (job_name, execute_dt); new rows use (job_type, status, details, items_processed, executed_on).
-- The legacy columns are made nullable so log inserts don't have to populate them.

ALTER TABLE `cron_jobs`
    MODIFY `job_name`  varchar(150) NULL,
    MODIFY `execute_dt` datetime    NULL,
    ADD COLUMN `job_type`        varchar(100) NULL AFTER `job_name`,
    ADD COLUMN `status`          varchar(20)  NULL AFTER `job_type`,
    ADD COLUMN `details`         text         NULL AFTER `status`,
    ADD COLUMN `items_processed` int(11)      NOT NULL DEFAULT 0 AFTER `details`,
    ADD COLUMN `executed_on`     datetime     NULL AFTER `items_processed`,
    ADD INDEX `idx_cron_jobs_job_type_executed_on` (`job_type`, `executed_on`);
