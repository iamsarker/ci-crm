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
| `subscription` | GET | **My Software** (`subscription_index.php`) — every license with product, price/cycle, key, next renewal, status, per-license Download / Upgrade / **Reset IP** actions, and the bound domain·IP under the key |
| `subscription/download/{license_id}` | GET | License-gated download. Verifies **ownership + active status**, then the **install-binding gate** (see below): unbound → renders the bind form; bound → resolves the release via `Software_model::getReleaseForProduct()` (product's `current_release_id` → product-scoped `is_current` → global `is_current`) and streams it. No id → the sole active license, else redirects to My Software. |
| `subscription/bind` | POST | Binds the install **domain + server IP** to the license (CSRF). Validates domain shape + IP (`FILTER_VALIDATE_IP`, v4/v6), only collects the field(s) still empty, then `Orderlicense_model::bindInstall()` → redirect back to `download`. |
| `subscription/reset_ip` | POST | Customer self-service (CSRF + JS confirm): `Orderlicense_model::resetIp()` clears `license_ip` (domain stays locked) so a new server IP can be set on the next download. |
| `subscription/upgrade/{license_id}` | GET | Same-family upgrade/downgrade options (same currency + cycle), each showing the **prorated charge** for the days remaining until renewal |
| `subscription/do_upgrade` | POST | Applies the change (form POST + CSRF): upgrade → prorated DUE invoice + `setPendingPlanChange()`, redirect to `billing/pay`; downgrade/same-price → `changePlan()` immediately |

**Install-binding gate (anti-piracy):** each license binds to one install identity — `order_licenses.license_domain` + **`license_ip`** (varchar 60; added by `license_bind_ip_migration.sql`). `download()` calls `Orderlicense_model::isBound()` (both fields set); if not, it renders `subscription_bind.php` instead of streaming. The **domain is bind-once** (written only while empty, then locked and shown read-only); the **IP is resettable** by the customer, so a server migration doesn't require support. `bindInstall()` writes only the empty field(s) — a locked domain in the payload is ignored. Validation errors and success surface as the existing customer toasts (`alert_error`/`alert_success`). **Enforcement is store-only for now** — `license/verify` records domain/IP but doesn't reject mismatches (see Pending follow-ups); the bound values are in place to compare against when you're ready to enforce.

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

## License Client — enforcement inside the sold product (`License_client`)

`license/verify` is only the **server** half. The **client** half — `src/libraries/License_client.php` — ships *inside the product* and is what makes **one source version** enforce different tiers. Every install runs the same code; the license decides what unlocks.

**Install roles (from `.env`):**

| Role | `.env` | Behaviour |
|------|--------|-----------|
| **Master** | `IS_LICENSE_MASTER=true` | The vendor CRM that *sells* licenses. Never phones home; `Entitlement` resolves per-company from the local DB (legacy). |
| **Client** | `LICENSE_KEY=…` + `LICENSE_SERVER_URL=…` | A purchased copy. Phones home to `{LICENSE_SERVER_URL}/license/verify`; the **whole install** runs under the one tier it paid for. |
| **Unconfigured** | none set | Legacy local-DB behaviour — enforcement is dormant until a key is written. |

**How it resolves:** `Entitlement::_featuresFor()` / `plan_key()` call `license_client->is_managed_client()`. On a client install they ignore `company_id` and return the **cached remote feature map** (the same map `license/verify` returns). An invalid / suspended / expired license degrades to baseline (universal features only).

**Caching & resilience:** the verdict is cached at `uploadedfiles/license/state.json` (deny-all `.htaccess`, git-ignored). Re-verified at most every `CHECK_INTERVAL_HOURS` (12h) on read; if the server is unreachable the last-good verdict is served for `GRACE_DAYS` (7), then degrades to invalid. `/cronjobs/run` force-refreshes the cache each day (client installs only). The client never throws.

**Entering the key:** the installer's **step 5** (Site Settings) has a *Software License* section — license key + vendor server URL, a **Verify** button (`install/index.php` `verify_license` AJAX → `Install::verifyLicenseKey()`), and a "this is the master server" checkbox. `Install::createEnvFile()` writes `IS_LICENSE_MASTER` / `LICENSE_KEY` / `LICENSE_SERVER_URL` into `.env`.

**Helpers:** `license_client()` (library accessor) and `license_state()` (display-friendly verdict) in `entitlement_helper.php`.

> **Self-hosted caveat:** the customer has the source, so this check is removable. Encode `License_client.php` (IonCube / SourceGuardian) for real teeth — same model WHMCS uses. See **[Files to encode](#files-to-encode-ioncube--sourceguardian)** below — `License_client.php` also houses the admin-login gate, so encoding it covers both.

### Feature gates — enforcing the tier in the product

Two gate primitives (in `entitlement_helper.php`), both autoloaded:

| Call | Scope | Use for |
|------|-------|---------|
| `feature_enabled($key)` → bool | **install-level** — "is THIS install licensed for X?" | Gating product capabilities in the self-hosted model. Client install → licensed tier; master/unconfigured → TRUE (full). Invalid/suspended client → FALSE. |
| `feature_value($key, $default)` → int | install-level numeric | e.g. `support_response_hours` from the tier |
| `require_feature($key, $redirect_uri)` | controller guard | Flash + redirect when a gated page is hit directly |
| `entitlement_can($companyId, $key)` | **per-customer-company** (legacy multi-tenant) | Kept for the old SaaS model; **not** the right call for self-hosted gating |

> Why install-level and not `entitlement_can()`: on the vendor's own **master** CRM no company owns a license, so `entitlement_can()` would (correctly for SaaS, wrongly here) resolve to the most-restrictive default and gate the vendor's own panel. `feature_enabled()` returns TRUE for master/unconfigured, gates only real client installs.

**Wired call sites:**

| Feature key | Where enforced |
|-------------|----------------|
| `branding_removal` | Client footer "Maintain by WHMAZ" attribution hidden (`templates/customer/footer.php`) |
| `software_license_selling` | Admin menu (Software Products/Releases), `Softwareproduct` + `Software` controller guards, customer `Cart::software()` + `addSoftwareToCart()` (JSON), "Buy Software" nav (`templates/customer/header.php`) |
| `domain_registration_transfers` | Admin menu (Domain Register/Pricing), `Domain_register` controller guard |

**Not gated in code (deliberately):** `priority_support`, `support_response_hours`, `dedicated_account_manager`, `sla_guarantee` are **service-level** promises (the vendor's support to the client), shown on the pricing cards only — nothing to enforce in the product. `dns_management`, `reseller_management`, `api_expose_for_third_party`, `advanced_modules`, `automatic_updates` are gateable but need subsystem-specific guards; add them with the same `feature_enabled()` / `require_feature()` pattern at those call sites.

---

## Admin-Login License Gate (anti-piracy)

A **separate** mechanism from the entitlement client above: it protects **this** install from being run cracked, rather than gating tiers. It lives in the same file (`License_client.php`) but is independent of the master/client/unconfigured roles — it always runs on `whmazadmin` login.

**Flow:** `Authenticate::login()` (`src/controllers/whmazadmin/Authenticate.php`) calls `License_client::admin_authorized()` **before** the credential check (and before reCAPTCHA). A cracked/unlicensed copy is blocked even with correct admin credentials.

**The check** (`admin_authorized()`):
- Reads `LICENSE_KEY` from `.env` (empty or the `XXXX-…` placeholder ⇒ `false`).
- Pure `GET` to the **hard-coded** endpoint `https://whmaz.com/api/verify-license.php?license_key=<KEY>` (`Accept: application/json`). The URL is a class const **on purpose** — not a `.env`/DB value — so a cracker can't repoint it at a fake "always-authorized" server.
- Authorized ⇔ JSON `authorized: true`. No local cache and no local grace: the vendor server is the sole source of truth (a network outage at login ⇒ blocked; this was a deliberate "simple, no cache" choice).

**Response shape** (server-controlled):
```json
{ "status": "authorized", "authorized": true, "ip": "103.159.72.16",
  "ip_match": null, "grace": true, "grace_until": "2026-07-09" }
```

**Grace window:** when the server reports `authorized:true` with `grace:true` + `grace_until`, login is allowed and:
- `Authenticate::login()` stores `LICENSE_GRACE_UNTIL` in the session on successful login (cleared when not in grace; wiped on logout via `sess_destroy`).
- `whmazadmin/include/header.php` renders a persistent `alert-warning` banner at the top of **every** admin page ("License grace period … until *Jul 9, 2026* (N days remaining)"), refreshed each login.
- Library getters: `admin_in_grace()`, `admin_grace_until()` (populated from the last `admin_authorized()` call). When the window lapses the server returns `authorized:false` and the login gate blocks outright.

**Failure UX:** on block, `login()` sets a dedicated `license_error` flashdata (not the generic `admin_error` toast) and re-renders the login view, which shows a **SweetAlert modal** ("License Verification Failed") — SweetAlert2 is already loaded via `footer_script.php`.

## Files to encode (IonCube / SourceGuardian)

These checks are plain PHP a self-hosted buyer can read and edit. **Encoding buys two different things** — decide which you want, they're not the same:

- **Tamper-resistance** — stop a cracker *modifying* the decision (the real point of encoding).
- **Concealment** — stop a cracker *reading* how the scheme works. Note this is only achievable for the whole enforcement surface: the licensing model still leaks from anything left plaintext (the `require_feature()` call sites name what's gated, `config/plans.php` lists the feature keys, the helper spells out the remote-map model). Encoding one file in isolation does **not** conceal the scheme.

The canonical file list also lives at **`ioncube-encodes-files.txt`** in the repo root (build script input).

### Must — tamper-critical decision points

| File | Why |
|------|-----|
| `src/libraries/License_client.php` | The check logic itself — hard-coded admin-verify URL, `admin_authorized()`, plus the entitlement phone-home client. **Primary target;** covers both the admin gate and the tier feature map (one file). |
| `src/controllers/whmazadmin/Authenticate.php` | The **call site** of the admin gate. Without encoding it, a cracker just removes the two-line `admin_authorized()` call and the encoded library never runs. |

### Defense-in-depth set — encode together, not individually

Only worthwhile if you want to harden **tier feature-gating** *and* obscure the licensing layer. Encode these **as a set** — encoding one alone leaves the model readable in the others (and `Entitlement.php` has **0 gate call sites**, so on its own it protects nothing):

| File | Why |
|------|-----|
| `src/helpers/entitlement_helper.php` | Holds the **used** gate functions (`feature_enabled()` / `require_feature()` / `feature_value()`) plus the `license_client()` accessor — the single-point bypass all 10 gates funnel through. The higher-value member of the set. |
| `src/libraries/Entitlement.php` | Per-company entitlement resolver. **Not** on any current gate path (legacy multi-tenant API), but its source reveals the scheme, so it belongs in the *concealment* set if you encode the layer. Skip if you only care about tamper-resistance. |

### Stays plaintext (do not encode)

- `src/config/plans.php` — config (feature keys / universal flags / labels), read as data; encoding config is fragile for no gain.
- The `require_feature()` / `feature_enabled()` **call sites** scattered in controllers/views — impractical to encode the whole app; they leak *what* is gated but can't flip the verdict once the decision files above are encoded.
- Customer **download-binding** (`subscription` module, `Orderlicense_model` bind methods) — not anti-piracy; real enforcement would live server-side in `license/verify`, which runs on the vendor's own server and never ships.

> **Encoding notes:** CI loads these via the loader then instantiates them, so IonCube works normally — ensure the target server has a matching IonCube loader installed. Keep **unencoded copies in source control**; encode only the distributed build. `entitlement_helper.php` and `Entitlement.php` also run on the **master/vendor** install, so test the encoded build there before shipping.

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
- `license/verify` **records but does not hard-reject domain/IP mismatches** (lenient, to allow legitimate server migrations). The customer now binds `license_domain` + `license_ip` at download (domain bind-once, IP resettable), so those bound values exist to enforce against when desired — flip `validateLicense()` to compare the calling install's domain/IP to the bound values.
- **Admin-login gate has no local grace/cache** — if `whmaz.com` is unreachable at login, admins are blocked until it responds. Grace is entirely server-driven (`grace`/`grace_until` in the response). Consider a short local fail-open window if vendor-server availability becomes a concern.
- `paddle_*` columns exist but Paddle (Merchant of Record) wiring is not implemented yet.
- The retired `subscription/plans` endpoint is dormant; consider removing it once the cart flow is confirmed in production.
