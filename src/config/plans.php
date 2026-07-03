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
    'tax_management',
    'credit_system',
    'service_package_management',
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
    'domain_registration_transfers',
    'dns_management',
    'software_license_selling',
    'reseller_management',
    'api_expose_for_third_party',
);

/*
 | Human-friendly display labels for feature keys (customer plan cards). Any key
 | not listed falls back to a humanized form of the key (see feature_label()).
 | Keeps DNS/API-style acronyms readable instead of "Dns Management".
 */
$config['plan_feature_labels'] = array(
    // Universal
    'billing_automation'             => 'Automated invoicing',
    'customer_portal'                => 'Customer self-service portal',
    'server_provisioning'            => 'Server provisioning (cPanel, Plesk, DirectAdmin)',
    'domain_management'              => 'Domain management',
    'support_tickets'                => 'Support ticket system',
    'knowledge_base'                 => 'Knowledge base',
    'multi_currency'                 => 'Multi-currency',
    'payment_gateways'               => 'Multiple payment gateways',
    'tax_management'                 => 'Tax management',
    'credit_system'                  => 'Credit system',
    'service_package_management'     => 'Service & package management',
    // Differentiated
    'support_response_hours'         => 'Support response (hours)',
    'priority_support'               => 'Priority support',
    'advanced_modules'               => 'Advanced modules',
    'automatic_updates'              => 'Automatic updates',
    'branding_removal'               => 'Branding removal',
    'domain_registration_transfers'  => 'Domain registration & transfers',
    'dns_management'                 => 'DNS management',
    'software_license_selling'       => 'Software license selling',
    'reseller_management'            => 'Reseller management',
    'api_expose_for_third_party'     => 'Third-party API access',
    'dedicated_account_manager'      => 'Dedicated account manager',
    'sla_guarantee'                  => 'SLA guarantee',
);
