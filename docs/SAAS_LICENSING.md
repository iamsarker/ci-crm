# Software Selling & Self-Hosted Licensing

ci-crm sells **software products** (the WHMAZ app tiers, add-on modules, or any downloadable software). Each product behaves like a hosting package: it has details, an entitlement feature map, **per-currency × per-billing-cycle pricing**, and is browsed and bought **through the cart**. Delivery is a downloadable ZIP; the installed software **phones home** to enforce its license.

> **Architecture note (2026-07):** This started as three fixed tiers (Basic / Pro / Max) sold through a dedicated subscription checkout. It is now a **generic product catalog purchased through the cart** like hosting/domains. The physical table is still named `plans` (kept to avoid breaking the `order_licenses` FK, `Plan_model`, and the entitlement layer), but a row is a **software product**.

---

## Terminology mapping

| Concept | ci-crm reality |
|---------|----------------|
| "account" | `companies.id` (== `order_licenses.company_id`). Entitlements key on `company_id`. |
| software product | a `plans` row (`plan_key` UNIQUE = slug). Pricing lives in `software_pricing`. |
| a customer's purchase | an `order_licenses` row whose `plan_id` points at the product — its own line, separate from `order_services` (hosting) and `order_domains` (domains). |
| billing cycle | `order_licenses.billing_cycle_id` (any active cycle; `ONE_TIME` = perpetual license) |
| status | `order_licenses.status` (0=pending, 1=active, 2=expired, 3=suspended, 4=terminated) |

**Self-hosted = no remote control.** Customers download a ZIP and install the software on their own server, so suspension/termination are **soft** (status flip only). Enforcement happens when the install **phones home** to `license/verify`.

---

## Tables

| Table | Purpose |
|-------|---------|
| `plans` | Software product catalog: `plan_key` UNIQUE (slug), `family_group` (upgrade tier grouping), `name`, `tagline`, `description` (HTML), `current_release_id`, `is_popular`, `sort_order`, `is_active`, `paddle_*` (nullable). **No price columns** — pricing is in `software_pricing`. |
| `software_pricing` | Per product × currency × billing cycle. `first_pay_amount` (first invoice) + `recurring_amount` (renewals); `UNIQUE(product_id, currency_id, billing_cycle_id)`; FK → `plans` ON DELETE CASCADE. Mirrors `product_service_pricing`. |
| `plan_features` | Differentiated entitlement flags per product. `feature_value` stores '1'/'0' or numbers as strings; `UNIQUE(plan_id, feature_key)`; FK → `plans` ON DELETE CASCADE |
| `order_licenses` | A customer's purchased product line. Lifecycle mirrors `order_services`; adds `license_key`, `paddle_subscription_id`, `license_domain`, `last_check_in`, `last_check_ip`, and the `pending_plan_id` / `pending_billing_cycle_id` / `pending_invoice_id` trio (a plan change awaiting its proration invoice); FK → `plans` |
| `software_releases` | The installable ZIPs. `product_id` scopes a release to a product (NULL = global); `is_current` flags the download |

`invoice_items.item_type`: **1 = domain, 2 = service, 3 = license/software**. `add_to_carts.item_type` uses the same convention (`3` = software; `product_service_id` holds the `plan_id`, `product_service_pricing_id` holds the `software_pricing.id`).

### Schema & migrations

- The **canonical schema is `crm_db.sql`** (the full export). Incremental changes ship as standalone idempotent migration `.sql` files that are run against an existing DB, after which `crm_db.sql` is re-exported:
  - `software_catalog_migration.sql` — fixed tiers → generic catalog (adds `software_pricing`, `plans.description`/`current_release_id`, `software_releases.product_id`; drops old price columns; resets seeded demo tiers).
  - `software_family_upgrade_migration.sql` — adds `plans.family_group` and the `order_licenses.pending_*` columns for family-scoped prorated upgrades.
- The old CI `dbforge` migrations (`src/migrations/2026062712*`, `2026062713*`) describe the original fixed-tier schema and are **superseded**. CI migrations are disabled (`migration_enabled = FALSE`); the `.sql` files are the apply path.

### Product families

Products sharing a non-empty `plans.family_group` are **upgrade tiers of the same software** (e.g. Basic/Pro/Max all `family_group = 'whmaz-app'`). A customer can only switch a license between products in its own family, on the same currency and billing cycle. A blank `family_group` means a standalone product with no upgrade path.

---

## Config — `src/config/plans.php`

Config now holds **entitlement logic only** — pricing and the product list are database-managed by the admin.

| Key | Meaning |
|-----|---------|
| `plan_discount_rate` | Informational (annual vs monthly saving); prices in `software_pricing` are authoritative |
| `plan_default_key` | Most-restrictive fallback when a company has no active license |
| `plan_universal_features` | TRUE for ALL products, **never gated or stored** |
| `plan_numeric_features` | Cast to int (e.g. `support_response_hours`) |
| `plan_boolean_features` | Documentation of the known boolean keys (casting itself defaults to bool for any non-numeric key) |
| `plan_feature_labels` | Feature key → human display label for the customer cards |

**Universal features** (always true, not stored in `plan_features`): `billing_automation`, `customer_portal`, `server_provisioning`, `domain_management`, `support_tickets`, `knowledge_base`, `multi_currency`, `payment_gateways`, `tax_management`, `credit_system`, `service_package_management`.

**Differentiated features are admin-defined per product** in the Software Product editor (key/value → `plan_features`). Known keys: `support_response_hours` (numeric), `priority_support`, `advanced_modules`, `automatic_updates`, `branding_removal`, `domain_registration_transfers`, `dns_management`, `software_license_selling`, `reseller_management`, `api_expose_for_third_party`, `dedicated_account_manager`, `sla_guarantee` (booleans, `1`/`0`). A key set to `0` (or omitted) simply doesn't render on that tier's card.

**Feature labels:** the customer plan cards render each feature via `feature_label($key)` — it looks up `plan_feature_labels` in this config and falls back to a humanized key. Add an entry here to get "DNS management" / "Third-party API access" instead of "Dns Management".

---

## Entitlements

- **Library** `src/libraries/Entitlement.php`: `can($companyId, $flag)`, `value($companyId, $flag)`, `all($companyId)`, `plan_key($companyId)`. Universal flags short-circuit to TRUE; resolves the company's active product, defaults to `plan_default_key`. Per-request cache.
- **Helper** `src/helpers/entitlement_helper.php` (autoloaded): `entitlement_can()`, `entitlement_value()`, `entitlement_plan_key()`, and `feature_label($key)` (display label lookup).

```php
if (entitlement_can($companyId, 'branding_removal')) { /* hide branding */ }
$sla = entitlement_value($companyId, 'support_response_hours');
echo feature_label('dns_management'); // "DNS management"
```

---

## Models

| Model | Role |
|-------|------|
| `Plan_model` | Read: `get_active_plans()`, `get_by_key()`, `getCatalogForCustomer($currencyId)`, `getFamilyProducts($family, $currencyId, $cycleId, $excludeId)` (same-family upgrade options), `getAllFamilies()`. Admin write: `saveProduct()`, `savePricingMatrix()`, `saveFeatures()`, `getPricingMatrix()`, `getStoredFeatures()`, `getPrice()`, `deleteProduct()`, `toggleActive()` |
| `Cart_model` | `getCartSoftwarePrice($pricingId)` (software line pricing); generic cart save/list already handle item_type=3 |
| `Subscription_model` | Read resolver: `get_active_plan_key_for_company()`, `get_active_subscription_for_company()`, `get_licenses_for_company()` (all licenses for My Software), `get_company_license($id, $companyId)` (ownership-scoped) |
| `Orderlicense_model` | Lifecycle: `saveLicense()`, `changePlan()` (prices from `software_pricing`), `setPendingPlanChange()` / `applyPendingPlanChange()` (prorated upgrade, applied on payment), `activateLicense()`, `suspendLicense()`/`unsuspendLicense()`/`terminateLicense()` (soft), `validateLicense()` (phone-home), `getLicenseByKey()`. `createSubscription()` is **retired** (cart is the checkout path). |
| `Software_model` | Release CRUD + `getReleases($productId=null)`, `getReleaseForProduct($productId)` (per-product download resolution), `getCurrentRelease()`, `filePath()`, `storageDir()` |

---

## Admin — Software Product Catalog

**Settings → Software Products** (`whmazadmin/softwareproduct`, controller `src/controllers/whmazadmin/Softwareproduct.php`, views `softwareproduct_list.php` / `softwareproduct_manage.php`).

| Action | Route |
|--------|-------|
| List | `whmazadmin/softwareproduct/index` |
| Create / edit | `whmazadmin/softwareproduct/manage/{id}` |
| Toggle active | `whmazadmin/softwareproduct/toggle_active/{id}` |
| Delete | `whmazadmin/softwareproduct/delete_records/{id}` (blocked if a customer owns it) |

The manage form has: **details** (name, auto-slug key, **family group** for upgrade tiers, tagline, HTML description, popular flag, active toggle, sort order), a **pricing grid** (currency rows × billing-cycle columns; one price per cell → stored as both `first_pay_amount` and `recurring_amount`; use the **One-Time** cycle for a perpetual license), an **entitlement features** repeater (key/value → `plan_features`), and a **linked release** dropdown. The family group field offers a datalist of existing families — assign the same value to products that should be upgrade tiers of each other.

Releases are uploaded under **Settings → Software Releases** and can be tagged to a product or left global.

**Reference seed:** `whmaz_plans_seed.sql` creates the canonical 3-tier WHMAZ family (Basic / Pro / Max, `family_group = 'whmaz'`) with USD monthly + yearly pricing and the full differentiated feature matrix — a working example of a family catalog. Idempotent; resolves currency/cycle ids by key. Run it, then re-export `crm_db.sql` and upload a global release ZIP.

---

## Customer — Browse & Buy (cart)

Software is purchased through the **same cart as hosting/domains**.

| Endpoint | Method | Description |
|----------|--------|-------------|
| `cart/software` | GET | Browse page (`cart_software.php`) — product cards with a billing-cycle selector, price, features, and **Add to Cart**. Public (guest may browse). |
| `cart/addSoftwareToCart` | POST (JSON) | Adds an `add_to_carts` row (`item_type=3`, `product_service_id`=plan_id, `product_service_pricing_id`=software_pricing.id). CSRF-excluded. |
| `cart/checkoutSubmit` | POST | Shared checkout — builds order + invoice; `Cart::_processCartItem()` has an `item_type==3` branch that creates the `order_licenses` row (pending) via `Orderlicense_model::saveLicense()` and the `item_type=3` invoice item |

Navigation (guest **and** logged-in): a **Software** entry in the customer header (`Buy Software` → `cart/software`, `My Software` → `subscription`). The cart view (`view_card.php`) shows a **Software** badge for these lines.

On payment, the existing provisioning path activates the license:

```
provisionPaidServices(invoiceId)
  → Provisioning_model::provisionInvoiceItems()
    → provisionLicense()              (item_type = 3)
      → Orderlicense_model::activateLicense()   issues license_key, status = 1
```

`provisionLicense()` routes each paid `item_type=3` line three ways:
- **Upgrade** — the license's `pending_invoice_id` matches this invoice → `applyPendingPlanChange()` (switch plan, keep dates/key).
- **Renewal** — an earlier PAID invoice references the same license → `activateLicense(isRenewal=true)` (extend dates, keep key).
- **New activation** — otherwise → `activateLicense()` (issue key, set dates).

---

## Customer — My Software, Downloads & Upgrades (`subscription` module)

A company can own **multiple** licenses; each is an independent `order_licenses` row with its own key.

| Endpoint | Method | Description |
|----------|--------|-------------|
| `subscription` | GET | **My Software** (`subscription_index.php`) — every license with product, price/cycle, key, next renewal, status, and per-license Download / Upgrade actions |
| `subscription/download/{license_id}` | GET | License-gated download. Verifies **ownership + active status**, then resolves the release via `Software_model::getReleaseForProduct()`: product's `current_release_id` → product-scoped `is_current` → global `is_current`. No id → the sole active license, else redirects to My Software. |
| `subscription/upgrade/{license_id}` | GET | Same-family upgrade/downgrade options (same currency + cycle), each showing the **prorated charge** for the days remaining until renewal |
| `subscription/do_upgrade` | POST | Applies the change (form POST + CSRF): upgrade → prorated DUE invoice + `setPendingPlanChange()`, redirect to `billing/pay`; downgrade/same-price → `changePlan()` immediately |

**Prorated upgrade lifecycle:** `do_upgrade` computes `(new_recurring − old_recurring) × remaining_days / cycle_days`. If positive, it creates a DUE proration invoice (`item_type=3`, ref = license) and records the target on the license (`pending_plan_id` + `pending_invoice_id`); the plan switch happens only when that invoice is paid (see `provisionLicense` above). Non-positive (downgrade / same price) applies immediately with the new price billed from the next renewal — no invoice.

The `clientarea` dashboard shows a **My Software** card linking to `subscription` when the company has an active license.

---

## Phone-home Licensing — `license` module (public, key-authenticated)

The installed software calls these endpoints. All are CSRF-excluded.

| Endpoint | Description |
|----------|-------------|
| `license/verify` | Returns `{ valid, status, plan_key, expires, features:{...}, message }`. Binds the install domain on first contact; records IP + check-in time. Returns the **same feature map** the entitlement layer uses, so installs gate features locally. |
| `license/latest` | Current release version info (JSON) + whether the key may download |
| `license/download` | Streams the current release ZIP if the license validates as active |

`validateLicense()` reports terminated / suspended / pending / expired keys as **not valid** — this is how soft suspension/termination is enforced on a server we don't control.

```json
{
  "valid": true,
  "status": "active",
  "plan_key": "pro",
  "expires": "2027-06-27",
  "features": { "priority_support": true, "advanced_modules": true, "support_response_hours": 48 },
  "message": "License is active."
}
```

---

## Renewal Invoice Cron (software licenses)

Runs as part of `/cronjobs/run` inside `generateRenewalInvoices()` (alongside domains/services); the daily job needs no extra wiring.

- `Cronjob_model::getExpiringLicenses($days)` — active, auto-renew licenses whose `next_renewal_date` is within `RENEWAL_DAYS_BEFORE` (15), excluding perpetual (`cycle_days=0`) and already-invoiced renewals.
- `Cronjob_model::createLicenseRenewalInvoice($license)` — creates a DUE invoice + `item_type=3` invoice item (ref_id = license id). When paid, provisioning detects the renewal and extends the license.
- Renewal email uses the `invoice_created` template with a `license` item description.

---

## Overdue-License Soft-Suspension Cron

Runs as part of `/cronjobs/run`; also standalone at `/cronjobs/suspendOverdueLicenses?key=SECRET`.

- `Cronjob_model::getLicensesOverdueForSuspension($days)` — active licenses with a `DUE` invoice (`invoice_items.item_type = 3`) overdue by ≥ N days. **No server-module join** (self-hosted).
- Grace period: dedicated `sys_cnf` key **`license_suspension_days_after_due`** (AUTOMATION group, default 7) — independent of hosting's `suspension_days_after_due`.
- Soft only: `Orderlicense_model::suspendLicense()` flips status to 3. Email via the `dunning_suspended` template.
- Idempotent: once `status = 3`, the candidate query (which requires `status = 1`) skips it.

---

## Software Releases (admin upload + gated download)

- **Admin**: Settings → Software Releases (`whmazadmin/software`, `src/controllers/whmazadmin/Software.php`, view `software_manage.php`). Upload ZIP (optionally tagged to a product), set current, delete, download.
- **Storage**: `uploadedfiles/software/` with a deny-all `.htaccess`; random filenames; the real path is never exposed. Files are streamed via `stream_file_download()` (`whmaz_helper.php`, `readfile()`-based). *The directory must be writable by the web-server user (`daemon` under LAMPP).*
- **Download channels**:

  | Channel | Endpoint | Gate |
  |---------|----------|------|
  | Customer (browser) | `subscription/download/{license_id}` | Ownership + active license; resolves the release for **that** product |
  | Installer / updater | `license/download` + `license/latest` | `license_key`; suspended/expired/terminated refused |
  | Admin | `whmazadmin/software/download/{id}` | Admin session |

- **Per-product resolution**: the customer download picks the release for the license's product (`getReleaseForProduct()`) — not a single global "current" build — so a company owning several products downloads the correct ZIP for each.
- **Client dashboard**: `clientarea/index` shows a **My Software** card (linking to `subscription`) when the company has an active license.

---

## Operational notes

- **Apply schema first**: `crm_db.sql` is canonical; incremental changes ship as migration `.sql` files (`software_catalog_migration.sql`, `software_family_upgrade_migration.sql`) run against an existing DB, after which `crm_db.sql` is re-exported.
- **Large ZIP uploads**: raise `upload_max_filesize` and `post_max_size` in `php.ini` (the code sets no CI size cap, but PHP's limits still apply).
- **CSRF**: `cart/addSoftwareToCart` (and the other cart JSON endpoints), `license/verify`, `license/latest`, `license/download` are in `csrf_exclude_uris` (`src/config/config.php`). `subscription/do_upgrade` is a normal form POST with a CSRF token (not excluded).

---

## Pending follow-ups

- **Cross-cycle upgrades** aren't offered — upgrade options are restricted to the license's current billing cycle to keep proration simple.
- `license/verify` **records but does not hard-reject domain mismatches** (lenient, to allow legitimate server migrations).
- `paddle_*` columns exist but Paddle (Merchant of Record) wiring is not implemented yet.
- The retired `subscription/plans` endpoint is dormant; consider removing it once the cart flow is confirmed in production.
