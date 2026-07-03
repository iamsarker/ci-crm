# Reseller Management & Third-Party REST API

Two connected features:

1. **Reseller Management** — an account hierarchy on top of `companies`. A reseller
   is a company that owns sub-customers and (optionally) holds API credentials.
2. **Third-Party REST API** — a stateless, API-key-authenticated REST interface
   (`/api/v1/...`) letting a reseller programmatically manage customers, domains,
   hosting, orders, invoices and software licenses **scoped to their own account**.

---

## 1. Data model

### `companies` (extended)
| Column | Meaning |
|--------|---------|
| `parent_company_id` | `0` = top-level customer; `>0` = sub-customer owned by that reseller company |
| `is_reseller` | `1` = this company is a reseller (may own sub-customers + use the API) |

### `reseller_profiles` (one row per reseller company)
| Column | Meaning |
|--------|---------|
| `company_id` | FK → `companies.id` (unique) |
| `discount_type` | `percent` \| `fixed` — applied to the reseller's own orders |
| `discount_value` | discount amount |
| `credit_balance` | tracked balance (informational; no wallet debit at order time) |
| `currency_id` | credit currency |
| `allow_api` | `1` = reseller may hold active API keys |
| `status` | `1` active, `0` deleted |

### `api_keys`
| Column | Meaning |
|--------|---------|
| `company_id` | owning reseller company |
| `key_id` | public identifier (sent in `X-Api-Key`) |
| `secret_hash` | `password_hash()` of the secret (secret shown once, never stored plain) |
| `secret_preview` | last 4 chars of the secret, for the UI |
| `scopes` | JSON array of granted scope strings |
| `ip_whitelist` | newline/comma separated IPs / CIDRs; blank = any |
| `rate_limit` | requests per minute, `0` = unlimited |
| `status` | `1` active, `2` revoked, `0` deleted |
| `expires_at` | optional expiry |
| `last_used_at` / `last_used_ip` / `request_count` | usage tracking |

### `api_request_logs`
Per-request audit trail (`method`, `endpoint`, `ip`, `status_code`, `response_time_ms`)
— also the window source for per-key rate limiting.

**Views:** `reseller_view` and `api_key_view` back the admin DataTables.

**Migration:** apply `reseller_api_migration.sql` (existing installs) — `crm_db.sql`
and `crm_db_views.sql` already carry everything for fresh installs. A CI migration
also exists at `src/migrations/20260703120000_create_reseller_api.php`.

---

## 2. Admin UI

**Settings → Reseller Management** (`whmazadmin/reseller`)
- Promote a company to a reseller; set discount, credit balance, API access.
- Assign sub-customers (only non-reseller, unassigned companies are selectable).

**Settings → API Keys** (`whmazadmin/apikey`)
- Create a key for an API-enabled reseller. **The secret is shown once** at creation
  (and on regenerate) — copy it immediately.
- Per-key scopes, IP allowlist, rate limit, expiry. Revoke / re-activate / delete.

Files (mirror the Promocode admin pattern):
- `src/controllers/whmazadmin/Reseller.php`, `src/models/Reseller_model.php`,
  `src/views/whmazadmin/reseller_{list,manage}.php`
- `src/controllers/whmazadmin/Apikey.php`, `src/models/Apikey_model.php`,
  `src/views/whmazadmin/apikey_{list,manage}.php`

---

## 3. REST API

Base URL: `https://yourdomain.com/api/v1`

### Authentication
Every request must send the key + secret as headers:

```
X-Api-Key: wk_xxxxxxxxxxxxxxxxxxxxxxxx
X-Api-Secret: ws_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

or, equivalently, `Authorization: Bearer <key_id>:<secret>`.

Auth is stateless and CSRF-excluded. The key is checked for: valid secret,
`status=1`, not expired, request IP allowed, and the owning company still being an
API-enabled reseller. All data is **scoped to the reseller company and its
sub-customers** (`companies.parent_company_id = <reseller>`).

### Response envelope
```json
// success
{ "success": true, "data": { ... } }

// error
{ "success": false, "error": { "code": "not_found", "message": "..." } }
```
HTTP status codes are meaningful: `200/201` ok, `401` bad credentials, `403`
scope/IP/expiry, `404` not found/not owned, `405` wrong method, `409` conflict,
`422` validation, `429` rate limited, `5xx` server/upstream.

### Rate limiting
Every API key is capped at **5 requests per second** (a hard, platform-wide
ceiling — `API_Controller::RATE_LIMIT_PER_SECOND`). Exceeding it returns `429`
with `{ "error": { "code": "rate_limited" } }` and a `Retry-After: 1` header.
Requests are counted per key in a fixed 1-second window, recorded in
`api_request_logs` at request start (so the cap holds under concurrency).

Keys may **also** carry an optional per-minute cap (`api_keys.rate_limit`,
`0` = unlimited) set in the admin UI; when both apply, either can trip the `429`
(the per-minute response sends `Retry-After: 60`).

### Scopes
| Scope | Grants |
|-------|--------|
| `domains:read` / `domains:write` | view domains / (registration via orders) |
| `hosting:read` / `hosting:write` | view services / suspend, unsuspend, terminate |
| `orders:read` / `orders:write` | view orders / place orders |
| `invoices:read` / `invoices:write` | view invoices / mark paid |
| `customers:read` / `customers:write` | view sub-customers / create sub-customers |
| `licenses:read` / `licenses:write` | view/verify licenses / issue & lifecycle |

### Endpoints

| Method | Path | Scope | Description |
|--------|------|-------|-------------|
| GET | `/ping` | (any key) | Auth + health check |
| GET | `/me` | (any key) | Key + reseller account info |
| GET | `/customers` | customers:read | List sub-customers |
| GET | `/customers/view/{id}` | customers:read | One sub-customer |
| POST | `/customers/create` | customers:write | Create sub-customer (+ owner login) |
| GET | `/products/software` | (any key) | Software catalog + pricing |
| GET | `/products/hosting` | (any key) | Hosting packages |
| GET | `/products/currencies` | (any key) | Currencies |
| GET | `/products/cycles` | (any key) | Billing cycles |
| GET | `/domains/check?domain=` | domains:read | Availability + `dom_pricing_id` (default registrar) |
| GET | `/domains/suggest?keyword=` | domains:read | Name suggestions with pricing |
| GET | `/domains/transfer_price?domain=` | domains:read | Transfer/renewal price for a TLD |
| GET | `/domains` | domains:read | List domains |
| GET | `/domains/view/{id}` | domains:read | One domain |
| GET | `/hosting` | hosting:read | List services |
| GET | `/hosting/view/{id}` | hosting:read | One service |
| POST | `/hosting/suspend/{id}` | hosting:write | Suspend account |
| POST | `/hosting/unsuspend/{id}` | hosting:write | Lift suspension |
| POST | `/hosting/terminate/{id}` | hosting:write | Cancel + delete account |
| GET | `/orders` | orders:read | List orders |
| GET | `/orders/view/{id}` | orders:read | Order + items |
| GET | `/cart` | orders:read | View the acting customer's cart |
| POST | `/cart/add_domain` | domains:write | Add a domain to the cart |
| POST | `/cart/add_hosting` | hosting:write | Add a hosting package to the cart |
| POST | `/cart/add_software` | licenses:write | Add a software license to the cart |
| POST | `/cart/link_domain_to_hosting` | domains:write | Attach a domain to a cart hosting item |
| POST | `/cart/link_hosting_to_domain` | hosting:write | Attach hosting to a cart domain item |
| POST | `/cart/delete/{id}` | orders:write | Remove a cart item |
| POST | `/checkout` | orders:write | Place the order from the cart |
| GET | `/invoices` | invoices:read | List invoices |
| GET | `/invoices/view/{uuid}` | invoices:read | Invoice + line items |
| POST | `/invoices/pay/{uuid}` | invoices:write | Mark paid → provision |
| GET | `/licenses` | licenses:read | List licenses |
| GET | `/licenses/view/{id}` | licenses:read | One license |
| POST | `/licenses/verify` | licenses:read | Validate a license key |
| POST | `/licenses/activate/{id}` | licenses:write | Activate / (re)issue key |
| POST | `/licenses/suspend/{id}` | licenses:write | Soft-suspend |
| POST | `/licenses/unsuspend/{id}` | licenses:write | Lift suspension |
| POST | `/licenses/terminate/{id}` | licenses:write | Terminate |

### Placing an order (cart → checkout)
Order placement **reuses the exact storefront cart/checkout code** — it is not
re-implemented in the API. Each call establishes a customer session for the acting
company (the reseller or an owned sub-customer via `customer_id`), then delegates
to the storefront `cart` controller / `checkoutSubmit()` via `Modules::run()`.

The cart persists in `add_to_carts` keyed by the customer's user_id, so add → link
→ checkout compose across separate stateless requests targeting the same
`customer_id`. Request bodies mirror the storefront endpoints. Get the pricing ids
from `/products/hosting` (`product_service_pricing_id`), `/products/software`
(`software_pricing_id`) and `/domains/check` or `/domains/suggest` (`dom_pricing_id`).

**Nothing is provisioned at checkout** — items are created pending / `DUE`. Pay and
provision afterwards with `POST /invoices/pay/{uuid}`.

Typical flow (hosting + domain for a sub-customer):
```bash
BASE=https://yourdomain.com/api/v1
AUTH=(-H "X-Api-Key: wk_..." -H "X-Api-Secret: ws_..." -H "Content-Type: application/json")

# 1. add hosting  (product_service_pricing_id from /products/hosting)
curl "${AUTH[@]}" -d '{"customer_id":42,"currency_id":1,"product_service_pricing_id":10,"quantity":1}' $BASE/cart/add_hosting
# -> data.result.cart_id = 55

# 2. attach a domain to that hosting item (dom_pricing_id from /domains/check)
curl "${AUTH[@]}" -d '{"customer_id":42,"currency_id":1,"parent_cart_id":55,"domain_action":"register","domain_name":"example.com","dom_pricing_id":7}' $BASE/cart/link_domain_to_hosting

# 3. checkout  (payment_gateway required; instructions optional)
curl "${AUTH[@]}" -d '{"customer_id":42,"currency_id":1,"payment_gateway":0,"instructions":""}' $BASE/checkout
# -> data.result.invoice_uuid

# 4. mark paid -> provisioning runs
curl "${AUTH[@]}" -X POST $BASE/invoices/pay/<invoice_uuid>
```

Standalone domain or software: use `/cart/add_domain` (body: `domain_action`,
`domain_name`, `dom_pricing_id`, `epp_code`) or `/cart/add_software` (body:
`software_pricing_id`), then `/checkout`.

### Example (auth + create customer)
```bash
curl -s https://yourdomain.com/api/v1/ping \
  -H "X-Api-Key: wk_..." -H "X-Api-Secret: ws_..."

curl -s https://yourdomain.com/api/v1/customers/create \
  -H "X-Api-Key: wk_..." -H "X-Api-Secret: ws_..." \
  -H "Content-Type: application/json" \
  -d '{"name":"Acme Ltd","email":"owner@acme.test","first_name":"Jane","last_name":"Doe"}'
```

---

## 4. Code map

| Concern | File |
|---------|------|
| API routing (`/api/v1/*`) | `src/config/routes.php` |
| CSRF exclusion | `src/config/config.php` (`csrf_exclude_uris` → `api/v1/.*`) |
| Base API controller (auth, scopes, scoping, logging, rate limit) | `src/core/API_Controller.php` |
| API auth + scopes + logging model | `src/models/Apikey_model.php` |
| API resource controllers (all `Api`-prefixed) | `src/modules/api/controllers/{ApiSystem,ApiCustomers,ApiProducts,ApiDomains,ApiHosting,ApiOrder,ApiInvoices,ApiLicenses}.php` |
| API cart/checkout (reuse storefront) | `src/modules/api/controllers/{ApiCart,ApiCheckout}.php` → `Modules::run('cart/...')`; helpers `API_Controller::actAsCustomer()/delegate()` |
| Route map (URL → exact class case) | `src/config/routes.php` (`api/v1/<resource>` → `api/Api<Resource>/…`) |
| Reseller model / admin | `src/models/Reseller_model.php`, `src/controllers/whmazadmin/Reseller.php` |
| Public unsuspend wrapper (used by hosting:write) | `src/models/Provisioning_model.php` → `unsuspendService()` |

The API is a thin, authenticated, reseller-scoped layer over existing models
(`Order_model`, `Provisioning_model`, `Invoice_model`, `Orderlicense_model`,
`Company_model`, `Plan_model`, `Common_model`) — not a reimplementation.
