# SaaS Subscription Plans & Self-Hosted Licensing

WHMAZ itself is sold as a **self-hosted SaaS** in three tiers (Basic / Pro / Max). The marketing site (`whmaz.com/pricing.php`) only renders the pricing table; **everything else — payment, subscription, suspension, termination, upgrade, and software delivery — runs in ci-crm.**

---

## Terminology mapping

The generic "account / subscription" wording doesn't exist natively in this codebase. It maps as follows:

| Concept | ci-crm reality |
|---------|----------------|
| "account" | `companies.id` (== `order_licenses.company_id`). Entitlements key on `company_id`. |
| "subscription" | an `order_licenses` row whose `plan_id` points at a plan — its own product line, separate from `order_services` (hosting) and `order_domains` (domains). |
| billing cycle | `order_licenses.billing_cycle_id` (MONTHLY / YEARLY) |
| status | `order_licenses.status` (0=pending, 1=active, 2=expired, 3=suspended, 4=terminated) |

**Self-hosted = no remote control.** Customers download a ZIP and install WHMAZ on their own server, so suspension/termination are **soft** (status flip only). Enforcement happens when the install **phones home** to `license/verify`.

---

## Tables

| Table | Purpose |
|-------|---------|
| `plans` | Catalog tiers (`plan_key` UNIQUE, `price_monthly`/`price_annual`, `is_popular`, `sort_order`, `is_active`, `paddle_product_id`/`paddle_price_monthly_id`/`paddle_price_annual_id` nullable) |
| `plan_features` | Differentiated flags per plan. `feature_value` stores '1'/'0' or numbers as strings; `UNIQUE(plan_id, feature_key)`; FK → `plans` ON DELETE CASCADE |
| `order_licenses` | A WHMAZ SaaS subscription line. Lifecycle mirrors `order_services`; adds `license_key`, `paddle_subscription_id`, `license_domain`, `last_check_in`, `last_check_ip`; FK → `plans` |
| `software_releases` | The installable ZIPs (exactly one row `is_current=1`) |

`invoice_items.item_type`: **1 = domain, 2 = service, 3 = license**.

### Schema & migrations

- SQL (idempotent, what the project actually ships): `plans_subscription_schema.sql`, `software_releases_schema.sql`
- CI `dbforge` migrations (mirror the SQL): `src/migrations/20260627120000_create_plans_subscription.php`, `src/migrations/20260627130000_create_software_releases.php`
- CI migrations are disabled by default (`migration_enabled = FALSE`); the `.sql` files are the apply path.

---

## Config — `src/config/plans.php` (single source of truth)

| Key | Meaning |
|-----|---------|
| `plan_discount_rate` = `0.15` | Annual vs monthly×12. **Stored prices are authoritative** — this only describes the saving. |
| `plan_keys` = `['basic','pro','max']` | Canonical ordered tiers |
| `plan_default_key` = `basic` | Most-restrictive fallback when no active subscription |
| `plan_universal_features` | TRUE for ALL plans, **never gated or stored** |
| `plan_numeric_features` | Cast to int (e.g. `support_response_hours`) |
| `plan_boolean_features` | Cast to bool |

**Universal features** (always true, not stored in `plan_features`): `billing_automation`, `customer_portal`, `server_provisioning` (cPanel/Plesk/DirectAdmin), `domain_management`, `support_tickets`, `knowledge_base`, `multi_currency`, `payment_gateways`. Clients, connected servers and admin/staff seats are **unlimited on every plan** (no limit keys anywhere).

### Differentiated flags (seeded in `plan_features`)

| Flag | basic | pro | max |
|------|-------|-----|-----|
| `support_response_hours` | 72 | 48 | 24 |
| `priority_support` | false | true | true |
| `advanced_modules` | false | true | true |
| `automatic_updates` | false | true | true |
| `branding_removal` | false | false | true |
| `dedicated_account_manager` | false | false | true |
| `sla_guarantee` | false | false | true |

### Plan pricing (USD)

| Key | Name | Tagline | Monthly | Annual | Popular |
|-----|------|---------|---------|--------|---------|
| basic | Basic | For new & small hosts | 10.95 | 111.72 | no |
| pro | Pro | For growing hosts | 15.95 | 162.69 | yes |
| max | Max | For established hosts | 24.95 | 254.49 | no |

---

## Entitlements

- **Library** `src/libraries/Entitlement.php`: `can($companyId, $flag)`, `value($companyId, $flag)`, `all($companyId)`, `plan_key($companyId)`. Universal flags short-circuit to TRUE; resolves the company's active plan, defaults to Basic. Per-request cache.
- **Helper** `src/helpers/entitlement_helper.php` (autoloaded): `entitlement_can()`, `entitlement_value()`, `entitlement_plan_key()`.

```php
if (entitlement_can($companyId, 'branding_removal')) { /* hide WHMAZ branding */ }
$sla = entitlement_value($companyId, 'support_response_hours'); // 72|48|24
```

---

## Models

| Model | Role |
|-------|------|
| `Plan_model` | `get_active_plans()`, `get_by_key()` — merged & typed feature maps |
| `Subscription_model` | Read-only resolver: `get_active_plan_key_for_company()`, `get_active_subscription_for_company()` (queries `order_licenses`) |
| `Orderlicense_model` | Lifecycle: `createSubscription()`, `changePlan()` (upgrade), `activateLicense()`, `suspendLicense()` / `unsuspendLicense()` / `terminateLicense()` (soft), `validateLicense()` (phone-home), `getLicenseByKey()` |
| `Software_model` | Release CRUD + `getCurrentRelease()`, `filePath()`, `storageDir()` |

---

## Checkout & Upgrade — `subscription` module (customer)

| Endpoint | Method | Description |
|----------|--------|-------------|
| `subscription/plans` | GET | Active plans + feature maps (JSON) |
| `subscription/subscribe` | POST | Dedicated checkout → order + `order_licenses` (pending) + DUE invoice; returns invoice UUID to redirect to `billing/pay` |
| `subscription/upgrade` | POST | Switch plan/cycle on the active license (immediate; new price from next renewal) |
| `subscription/download` | GET | License-gated software download (active license required) |

On payment, the existing provisioning path activates the license:

```
provisionPaidServices(invoiceId)
  → Provisioning_model::provisionInvoiceItems()
    → provisionLicense()              (item_type = 3)
      → Orderlicense_model::activateLicense()   issues license_key, status = 1
```

---

## Phone-home Licensing — `license` module (public, key-authenticated)

The installed software calls these endpoints. Unauthenticated except for the license key itself; all are CSRF-excluded.

| Endpoint | Description |
|----------|-------------|
| `license/verify` | Returns `{ valid, status, plan_key, expires, features:{...}, message }`. Binds the install domain on first contact; records IP + check-in time. Returns the **same feature map** the entitlement layer uses, so installs gate features locally. |
| `license/latest` | Current release version info (JSON) + whether the key may download |
| `license/download` | Streams the current release ZIP if the license validates as active |

`validateLicense()` reports terminated / suspended / pending / expired keys as **not valid** — this is how soft suspension/termination is enforced on a server we don't control.

Example response from `license/verify`:

```json
{
  "valid": true,
  "status": "active",
  "plan_key": "pro",
  "expires": "2027-06-27",
  "features": { "priority_support": true, "advanced_modules": true, "support_response_hours": 48, "...": "..." },
  "message": "License is active."
}
```

---

## Overdue-License Soft-Suspension Cron

Runs as part of `/cronjobs/run` (step 3); also standalone at `/cronjobs/suspendOverdueLicenses?key=SECRET`.

- `Cronjob_model::getLicensesOverdueForSuspension($days)` — active licenses with a `DUE` invoice (`invoice_items.item_type = 3`) overdue by ≥ N days. **No server-module join** (self-hosted).
- Grace period: dedicated `sys_cnf` key **`license_suspension_days_after_due`** (AUTOMATION group, default 7) — independent of hosting's `suspension_days_after_due`.
- Soft only: `Orderlicense_model::suspendLicense()` flips status to 3. Email via the `dunning_suspended` template (`plan_name` → `{service_name}`, `license_domain` → `{hosting_domain}`).
- Idempotent: once `status = 3`, the candidate query (which requires `status = 1`) skips it. Run logged as `license_suspensions`.

---

## Software Releases (admin upload + gated download)

- **Admin**: Settings → Software Releases (`whmazadmin/software`, controller `src/controllers/whmazadmin/Software.php`, view `src/views/whmazadmin/software_manage.php`). Upload ZIP, set current, delete, download.
- **One ZIP serves all 3 plans** — releases are plan-agnostic. Plan differences are enforced at runtime via the license feature map, **not** separate builds.
- **Storage**: `uploadedfiles/software/` with a deny-all `.htaccess`; random filenames; the real path is never exposed. Files are streamed via `stream_file_download()` (in `whmaz_helper.php`, uses `readfile()` so large files don't exhaust memory).
- **Download channels**:

  | Channel | Endpoint | Gate |
  |---------|----------|------|
  | Customer (browser) | `subscription/download` | Logged in + active license |
  | Installer / updater | `license/download` + `license/latest` | `license_key`; suspended/expired/terminated refused |
  | Admin | `whmazadmin/software/download/{id}` | Admin session |

- **Client dashboard**: `clientarea/index` shows a "Download Software" card (current version + button) only when the company has an active license.

### Admin menu

Settings dropdown → … → Email Template → Dynamic Pages → *(divider)* → **Software Releases**.

---

## Operational notes

- **Apply schema first**: run `plans_subscription_schema.sql` and `software_releases_schema.sql` before using the feature (the tables must exist).
- **Large ZIP uploads**: raise `upload_max_filesize` and `post_max_size` in `php.ini` (the code sets no CI size cap, but PHP's limits still apply).
- **CSRF**: `subscription/subscribe`, `subscription/upgrade`, `license/verify`, `license/latest`, `license/download` are in `csrf_exclude_uris` (`src/config/config.php`).

---

## Pending follow-ups

- License **renewal invoices** are not yet generated by the renewal cron (`Cronjob_model` handles domains/services only).
- **Upgrade** is immediate — no proration invoice; the new price bills from the next renewal.
- `subscription/subscribe` **skips the order-confirmation email** (the existing template assumes hosting/domain items).
- `license/verify` **records but does not hard-reject domain mismatches** (lenient, to allow legitimate server migrations).
- `paddle_*` columns exist but Paddle (Merchant of Record) wiring is not implemented yet.
