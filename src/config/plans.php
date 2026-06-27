<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Subscription Plans configuration
 * -------------------------------------------------------------------------
 * Single source of truth for pricing logic and the entitlement layer.
 *
 * WHMAZ is sold as a SaaS in three tiers (Basic / Pro / Max). The marketing
 * site only renders the pricing table; everything else (payment, subscription,
 * suspension, termination, upgrade) is handled here in ci-crm.
 *
 * In this codebase a "plan" is a catalog row in `plans`, and a customer's
 * "subscription" is an `order_licenses` row whose `plan_id` points at a plan
 * (its own product line, separate from order_services=hosting and
 * order_domains=domains). The owning "account" is the customer company
 * (`companies.id`, a.k.a. `order_licenses.company_id`).
 *
 * @see src/models/Plan_model.php
 * @see src/models/Subscription_model.php
 * @see src/libraries/Entitlement.php
 */

/*
 | Annual discount applied versus paying monthly x 12.
 | Stored prices are authoritative; this rate exists so pricing/marketing
 | code can describe the saving from one place.
 */
$config['plan_discount_rate'] = 0.15;

/*
 | Plan keys (the canonical ordered set of tiers).
 */
$config['plan_keys'] = array('basic', 'pro', 'max');

/*
 | Default / most-restrictive plan. The entitlement layer falls back to this
 | when an account has no active plan subscription.
 */
$config['plan_default_key'] = 'basic';

/*
 | Universal features — TRUE for every plan and therefore NOT gated.
 | Clients, connected servers and admin/staff seats are UNLIMITED on every
 | plan, so there are deliberately no limit keys here.
 |
 | These are NOT stored in `plan_features`; the entitlement layer short-circuits
 | any of these keys to TRUE regardless of the resolved plan.
 */
$config['plan_universal_features'] = array(
    'billing_automation',
    'customer_portal',
    'server_provisioning',   // cPanel / Plesk / DirectAdmin
    'domain_management',
    'support_tickets',
    'knowledge_base',
    'multi_currency',
    'payment_gateways',
);

/*
 | Differentiated feature keys actually stored per plan in `plan_features`.
 | `numeric` keys are cast to int (e.g. support_response_hours); every other
 | differentiated key is a boolean stored as '1'/'0' and cast to bool.
 */
$config['plan_numeric_features'] = array(
    'support_response_hours',
);

$config['plan_boolean_features'] = array(
    'priority_support',
    'advanced_modules',
    'automatic_updates',
    'branding_removal',
    'dedicated_account_manager',
    'sla_guarantee',
);
