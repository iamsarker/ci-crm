-- Diagnostic queries for "service not suspended despite overdue invoice"

-- 1. Check the relevant sys_cnf flags
SELECT cnf_key, cnf_val
FROM sys_cnf
WHERE cnf_key IN ('cron_enabled', 'cron_secret_key', 'suspension_days_after_due');

-- 2. Show every (service, invoice) pair the suspension query considers,
--    PLUS the reason any of them might be filtered out. Run this with the
--    same days threshold from sys_cnf (default 7).
SET @days := COALESCE(
    (SELECT cnf_val FROM sys_cnf WHERE cnf_key = 'suspension_days_after_due'),
    7
);

SELECT  os.id                AS service_id,
        os.status            AS svc_status,            -- needs to be 1
        os.is_synced         AS svc_is_synced,         -- needs to be 1
        os.cp_username,                                -- must not be empty (cpanel/da)
        os.hosting_domain,                             -- must not be empty (plesk)
        os.deleted_on,                                 -- must be NULL
        sm.module_name       AS server_module,         -- must be cpanel/plesk/directadmin
        i.id                 AS invoice_id,
        i.invoice_no,
        i.status             AS inv_status,            -- needs to be 1
        i.pay_status,                                  -- needs to be 'DUE' (PARTIAL excluded)
        i.due_date,
        DATEDIFF(CURDATE(), i.due_date) AS days_overdue,
        @days                AS threshold_days,
        CASE
            WHEN os.status     <> 1                                THEN 'svc not Active'
            WHEN os.is_synced  <> 1                                THEN 'svc not synced'
            WHEN os.cp_username IS NULL OR os.cp_username = ''     THEN 'no cp_username'
            WHEN os.deleted_on IS NOT NULL                         THEN 'svc soft-deleted'
            WHEN LOWER(sm.module_name) NOT IN ('cpanel','plesk','directadmin')
                                                                   THEN 'unsupported module'
            WHEN i.status      <> 1                                THEN 'invoice inactive'
            WHEN i.pay_status  <> 'DUE'                            THEN CONCAT('pay_status=', i.pay_status)
            WHEN DATEDIFF(CURDATE(), i.due_date) < @days           THEN 'not overdue enough'
            ELSE 'CANDIDATE'
        END AS verdict
FROM order_services os
JOIN invoice_items  ii ON ii.ref_id = os.id AND ii.item_type = 2
JOIN invoices       i  ON ii.invoice_id = i.id
JOIN product_services ps ON os.product_service_id = ps.id
JOIN servers        s  ON ps.server_id = s.id
JOIN server_modules sm ON s.product_service_module_id = sm.id
WHERE i.pay_status IN ('DUE', 'PARTIAL')
ORDER BY os.id, i.due_date;

-- 3. Last cronjob runs (if cronjob_logs table exists)
SELECT * FROM cronjob_logs
WHERE job_name IN ('service_suspensions','renewal_invoices')
ORDER BY id DESC
LIMIT 10;
