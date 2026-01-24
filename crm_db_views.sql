
-- invoice_view

create or replace view invoice_view AS
SELECT DISTINCT i.id, i.invoice_uuid, i.company_id, i.order_id, i.currency_id, i.currency_code, i.invoice_no, i.sub_total, i.tax, i.vat, i.total, i.due_date, i.order_date, i.cancel_date, i.refund_date, i.remarks, i.status, i.pay_status, i.inserted_on, i.updated_on,
c.name company_name, c.mobile company_mobile, c.email company_email, c.address company_address, c.city company_city, c.state company_state, c.zip_code company_zipcode, c.country, c.phone company_phone, o.order_uuid, o.order_no
FROM invoices i
JOIN companies c on i.company_id=c.id
JOIN orders o on i.order_id=o.id;


-- order_view

CREATE or REPLACE view order_view AS
SELECT o.id, o.order_uuid, o.order_no, o.company_id, o.currency_id, o.currency_code, o.order_date, o.amount, o.vat_amount, o.tax_amount, o.coupon_code, o.coupon_amount, o.discount_amount, o.total_amount, o.payment_gateway_id, o.remarks, o.instructions, o.status, o.inserted_on, o.updated_on,
c.name company_name, c.email company_email, c.mobile company_mobile, c.phone company_phone, c.address company_address, c.country, c.zip_code company_zipcode, c.city company_city, c.state company_state, p.name payment_gateway_name, p.icon_fa_unicode payment_gateway_fa_icon
FROM orders o 
JOIN companies c on o.company_id=c.id 
JOIN payment_gateway p on o.payment_gateway_id=p.id


-- product_service_view or package_view

CREATE OR REPLACE VIEW product_service_view AS
SELECT DISTINCT ps.id, ps.product_service_group_id, ps.server_id, ps.product_service_module_id, ps.product_service_type_id, ps.product_name, ps.product_desc, ps.is_hidden, ps.status,
psg.group_name, psg.group_headline, s.name server_name, s.hostname server_hostname, s.ip_addr server_ip, psm.module_name
FROM product_services ps
JOIN product_service_groups psg on ps.product_service_group_id=psg.id
JOIN servers s on ps.server_id=s.id
JOIN product_service_modules psm on ps.product_service_module_id=psm.id;


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
