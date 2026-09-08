<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Admin portal capability map — reseller tenancy (v2.0.0)
| -------------------------------------------------------------------------
|
| Which whmazadmin controllers a RESELLER admin (admin_users.admin_type = 1)
| may reach. Platform staff (admin_type = 0) are never checked against this.
|
| ⚠️  This is an ALLOWLIST and it is default-deny. A controller that is not
|     listed here is blocked for resellers. Do not invert it into a blocklist:
|     enumerating the ~23 forbidden controllers would mean every controller
|     added in a later release ships silently EXPOSED to every reseller.
|
| Read by two places, which is what keeps enforcement and navigation in step:
|   - src/hooks/RequestGuard.php   — the actual enforcement, on every request
|   - views/whmazadmin/include/header_menus.php — which menu items render
|
| Value is either array('*') for the whole controller, or an explicit list of
| allowed method names.
|
| ⚠️  admin_can('x') WITH NO METHOD returns TRUE whenever the controller key
|     exists at all -- see tenant_helper.php. So any controller listed here
|     with an explicit method list MUST be gated in header_menus.php with its
|     method too, e.g. admin_can('domain_pricing', 'index'). Gate it on the
|     bare controller name and the menu item renders for resellers and then
|     RequestGuard denies the click, which is exactly the nav/enforcement
|     drift this file exists to prevent.
|
| Deliberately NOT listed, and why:
|   server, server_module        — infrastructure; also holds root credentials
|   domain_register              — registrar API keys, a hard secret leak
|   general_setting              — app settings, sys_cnf, install_crontab()
|   currency, paymentgateway     — gateway credentials + platform-wide config
|   domain_pricing (CRUD)        — ALL pricing is platform-owned (v2.1); only
|                                  the prices() lookup is granted, below
|   promocode                    — a reseller may not create or edit promo
|                                  codes, ever. Prices and discounts belong to
|                                  the platform: a reseller's margin is the gap
|                                  between platform retail and the cost the
|                                  platform sets for them, and nothing else.
|                                  This is a product decision, not a "not yet" —
|                                  do not add it.
|   service_category/group/product, software, softwareproduct — global catalog
|   email_template               — no owner column; per-reseller templates are
|                                  a later release
|   expense, expense_category, expense_vendor — the platform operator's own P&L
|   kb, kb_category, page, announcement, ticket_department — global content
|   reseller                     — resellers must not manage resellers (req. 6)
|   admin_user                   — platform staff account management
*/

$config['reseller'] = array(

	// --- Auth: must never be blocked, or a reseller cannot log in or out ---
	'authenticate'  => array('*'),

	// --- Own account / chrome ---
	'dashboard'     => array('*'),   // scoped in Dashboard_model; expenses widget removed
	'notification'  => array('*'),   // already per-admin via getAdminId()

	// --- Tenant-scoped: rows filtered to adminScopeIds() ---
	'company'       => array('*'),
	'order'         => array('*'),
	'invoice'       => array('*'),
	'ticket'        => array('*'),
	'apikey'        => array('*'),
	'provisioning'  => array('*'),
	'cancellation'  => array('*'),

	// --- Catalog lookups for the new-order form ---
	// Explicit method list, not '*'. These read product_service_view, which has
	// no company_id and exposes server_name/server_hostname/server_ip, so the
	// controller additionally drops those columns for resellers. Requirement 8
	// ("reseller cannot manage servers") is a disclosure rule, not just a CRUD one.
	'package'       => array('filter_api', 'prices'),

	// Domain price lookup for that same form -- the METHOD only, never the
	// domain_pricing CRUD screens, which are platform wholesale. Without this
	// a reseller's new-order form silently fails to price any domain.
	// Both lookups resolve for the selected customer and guardCompany() the
	// posted company_id, so neither can be used to read another tenant's price.
	'domain_pricing' => array('prices'),

	// --- Own wholesale pricing: READ-ONLY (v2.1) ---
	// index() only. A reseller sets no price of any kind; this screen shows
	// them platform retail, their cost and the margin between.
	//
	// Narrowed from '*' deliberately. save_cost() is platform-staff-only and
	// refuses a reseller on its own account, but listing the method here means
	// RequestGuard denies it BEFORE the controller is even constructed. The
	// controller also pins a reseller to their own company id and ignores
	// ?reseller=, so index() cannot read another tenant's cost.
	'reseller_pricing' => array('index'),

	// --- Own prepaid credit account (Phase 3) ---
	// Like reseller_pricing, the controller pins a reseller admin to their own
	// company and ignores ?reseller=, so '*' cannot reach another tenant's
	// wallet. adjust() refuses resellers outright (only platform staff may post
	// a manual correction) and pay() refuses PLATFORM staff outright (it is
	// impersonation, and they have a non-impersonating route via Invoice).
	'reseller_wallet' => array('*'),

);
