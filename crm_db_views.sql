
-- invoice_view: includes total_paid and balance_due from invoice_txn

CREATE OR REPLACE VIEW invoice_view AS
SELECT DISTINCT i.id, i.invoice_uuid, i.company_id, i.order_id, i.currency_id, i.currency_code,
    i.invoice_no, i.sub_total, i.tax, i.vat, i.total, i.due_date, i.order_date,
    i.cancel_date, i.refund_date, i.remarks, i.status, i.pay_status,
    i.need_api_call, i.inserted_on, i.updated_on,
    c.name company_name, c.mobile company_mobile, c.email company_email,
    c.address company_address, c.city company_city, c.state company_state,
    c.zip_code company_zipcode, c.country, c.phone company_phone,
    o.order_uuid, o.order_no,
    COALESCE(t.total_paid, 0) AS total_paid,
    (i.total - COALESCE(t.total_paid, 0)) AS balance_due,
    t.last_payment_date
FROM invoices i
JOIN companies c ON i.company_id = c.id
JOIN orders o ON i.order_id = o.id
LEFT JOIN (
    SELECT invoice_id,
        SUM(CASE WHEN type = 'payment' AND status = 1 THEN amount
                 WHEN type = 'refund'  AND status = 1 THEN -amount
                 WHEN type = 'credit'  AND status = 1 THEN amount
                 ELSE 0 END) AS total_paid,
        MAX(CASE WHEN status = 1 THEN txn_date END) AS last_payment_date
    FROM invoice_txn
    WHERE deleted_on IS NULL
    GROUP BY invoice_id
) t ON i.id = t.invoice_id;


-- order_view: includes service/domain counts and recurring totals

CREATE OR REPLACE VIEW order_view AS
SELECT o.id, o.order_uuid, o.order_no, o.company_id, o.currency_id, o.currency_code,
    o.order_date, o.amount, o.vat_amount, o.tax_amount, o.coupon_code, o.coupon_amount,
    o.discount_amount, o.total_amount, o.payment_gateway_id, o.remarks, o.instructions,
    o.status, o.inserted_on, o.updated_on,
    c.name company_name, c.email company_email, c.mobile company_mobile,
    c.phone company_phone, c.address company_address, c.country,
    c.zip_code company_zipcode, c.city company_city, c.state company_state,
    p.name payment_gateway_name, p.icon_fa_unicode payment_gateway_fa_icon,
    COALESCE(sv.service_count, 0) AS service_count,
    COALESCE(dm.domain_count, 0) AS domain_count,
    COALESCE(sv.total_recurring, 0) AS services_recurring_total,
    COALESCE(dm.total_recurring, 0) AS domains_recurring_total
FROM orders o
JOIN companies c ON o.company_id = c.id
JOIN payment_gateway p ON o.payment_gateway_id = p.id
LEFT JOIN (
    SELECT order_id,
        COUNT(*) AS service_count,
        SUM(recurring_amount) AS total_recurring
    FROM order_services
    WHERE deleted_on IS NULL
    GROUP BY order_id
) sv ON o.id = sv.order_id
LEFT JOIN (
    SELECT order_id,
        COUNT(*) AS domain_count,
        SUM(recurring_amount) AS total_recurring
    FROM order_domains
    WHERE deleted_on IS NULL
    GROUP BY order_id
) dm ON o.id = dm.order_id


-- product_service_view or package_view

CREATE OR REPLACE VIEW product_service_view AS
SELECT DISTINCT ps.id, ps.product_service_group_id, ps.server_id, ps.product_service_module_id, ps.product_service_type_id, ps.product_name, ps.product_desc, ps.is_hidden, ps.cp_package, ps.status, ps.updated_on,
psg.group_name, psg.group_headline, s.name server_name, s.hostname server_hostname, s.ip_addr server_ip, psm.module_name, pst.servce_type_name
FROM product_services ps
JOIN product_service_groups psg on ps.product_service_group_id=psg.id
LEFT JOIN servers s on ps.server_id=s.id
JOIN product_service_modules psm on ps.product_service_module_id=psm.id
JOIN product_service_types pst on ps.product_service_type_id=pst.id;


-- expense_view

CREATE OR REPLACE VIEW expense_view AS
SELECT e.id, e.expense_type_id, e.expense_vendor_id, e.exp_amount, e.paid_amount, e.expense_date, e.attachment, e.remarks, e.status, e.inserted_on, e.updated_on,
et.expense_type, ev.vendor_name
FROM expenses e
JOIN expense_types et on e.expense_type_id=et.id
JOIN expense_vendors ev on e.expense_vendor_id=ev.id;


-- ticket_view

CREATE OR REPLACE VIEW ticket_view AS
SELECT tk.id, tk.company_id, tk.ticket_dept_id, tk.order_service_id, tk.order_domain_id, tk.title, tk.message, tk.priority, tk.attachment, tk.status, tk.flag, tk.inserted_on, tk.updated_on,
c.name company_name, c.email company_email, c.mobile company_mobile, td.name dept_name,
CONCAT(u.first_name, ' ', u.last_name) as user_name
FROM tickets tk
JOIN companies c on tk.company_id=c.id
JOIN ticket_depts td on tk.ticket_dept_id=td.id
JOIN users u on tk.inserted_by=u.id;


-- pages_view

CREATE OR REPLACE VIEW `pages_view` AS
SELECT
	p.*,
	u1.username as created_by_name,
	u2.username as updated_by_name
FROM `pages` p
		 LEFT JOIN `admin_users` u1 ON p.inserted_by = u1.id
		 LEFT JOIN `admin_users` u2 ON p.updated_by = u2.id
WHERE p.status = 1;
