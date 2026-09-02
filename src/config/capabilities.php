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
| Deliberately NOT listed, and why:
|   server, server_module        — infrastructure; also holds root credentials
|   domain_register              — registrar API keys, a hard secret leak
|   general_setting              — app settings, sys_cnf, install_crontab()
|   currency, paymentgateway     — gateway credentials + platform-wide config
|   domain_pricing               — platform wholesale pricing
|   service_category/group/product, software, softwareproduct — global catalog
|   email_template               — no owner column; per-reseller templates are
|                                  a later release (see the plan, Phase 4)
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

	// --- Added in later phases; keep commented so they stay denied until built ---
	// 'promocode'        => array('*'),   // Phase 4: needs promo_codes.company_id first,
	//                                     // or a reseller edits PLATFORM promo codes
	// 'reseller_pricing' => array('*'),   // Phase 2
	// 'reseller_wallet'  => array('*'),   // Phase 3
);
