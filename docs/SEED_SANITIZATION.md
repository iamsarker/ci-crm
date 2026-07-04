# Seed Sanitization (crm_db.sql) — CodeCanyon Distribution

Whenever `crm_db.sql` is re-exported from a working/dev database, the dump carries
real credentials and personal data. Before shipping, sanitize the seed so it
contains only neutral placeholders. The installer overwrites the buyer-facing
values (admin account, nameservers, license, admin notification email) at install
time; everything else must ship blank for the buyer to configure in admin.

> **Scope:** config-only. Transactional tables (companies, orders, invoices,
> tickets, logs, etc.) are emptied by the installer's
> `Install::truncateDataTables()` at install time — this document does **not**
> strip them from the dump. If a fully data-free dump is needed, truncate those
> tables in the export instead.

## Reusable prompt

Paste this to Claude Code after re-exporting `crm_db.sql`:

---

Sanitize the seed data in `crm_db.sql` for CodeCanyon distribution — replace all
of my real data/credentials with neutral placeholders. The installer overwrites
the buyer-facing ones at install time; the rest must ship blank. Make these exact
changes (find each seed row by its `id`/`cnf_key` and edit in place; keep row
structure and column counts intact; verify no listed secret remains afterward
with a grep):

**`admin_users` id=1** → `first_name`='Admin', `last_name`='User',
`username`='admin', `email`='admin@example.com', `mobile`=NULL, `phone`=NULL. For
`password`, generate a fresh bcrypt hash with
`php -r "echo password_hash('ChangeMe@123', PASSWORD_DEFAULT);"` and use its
output. Keep `admin_role_id`=1, `support_depts`='1,2', `status`=1.

**`app_settings` id=1** → `admin_url`='', `company_address`='Your Company Address',
`zip_code`='00000', `email`='admin@example.com', `fax`='', `phone`='',
`smtp_host`=NULL, `smtp_username`=NULL, `smtp_authkey`=NULL, `captcha_site_key`='',
`captcha_secret_key`='', `license_auth`=NULL, `license_hash`=NULL. Keep
`site_name`/`site_desc`/`company_name`='WHMAZ', the favicon/logo filenames, and
`smtp_port`='587'.

**`sys_cnf`** → `DefaultNameServer1`/`DefaultNameServer2` = NULL (3/4 already NULL);
`cron_secret_key` = ''; `admin_notification_email` = 'admin@example.com'.

**`dom_registers`** (all rows) → empty `whitelisted_ip`, `auth_userid`,
`auth_apikey`, `def_ns1`, `def_ns2`, `def_ns3`, `def_ns4` (set to ''). Keep
`name`/`platform`/`api_base_url` and the public API endpoint URLs.

**`payment_gateway`** (all rows) → for every gateway empty these to '' (or NULL
where the column is NULL-typed): `public_key`, `secret_key`, `webhook_secret`,
`test_public_key`, `test_secret_key`, `test_webhook_secret`, `webhook_url`,
`merchant_id`, `merchant_pwd`, and the bank fields
`bank_name`/`account_name`/`account_number`/`routing_number`/`swift_code`/`iban`.
Inside any `extra_config` JSON, blank credential values (e.g. `store_id`,
`store_password`) but keep the JSON valid and keep non-secret URLs
(sandbox_url/live_url). Do not remove rows or change
`gateway_code`/currencies/fees.

After editing, grep the file to confirm none of my real values survive (old API
keys, `whmaz.com` nameservers/emails, `localhost` webhook URLs, the cron hex, SMTP
password, reCAPTCHA keys, bank details). Report anything that still looks like real
data — especially any NEW table/column in the re-export that holds credentials or
personal data and isn't in this list.

---

## Placeholder reference (what each row should end up as)

| Table / row | Column(s) | Placeholder |
|---|---|---|
| `admin_users` id=1 | first_name / last_name | `Admin` / `User` |
| | username / email | `admin` / `admin@example.com` |
| | password | bcrypt of `ChangeMe@123` (regenerate each time) |
| | mobile / phone | NULL |
| `app_settings` id=1 | admin_url | `''` |
| | company_address / zip_code | `Your Company Address` / `00000` |
| | email / fax / phone | `admin@example.com` / `''` / `''` |
| | smtp_host / smtp_username / smtp_authkey | NULL |
| | captcha_site_key / captcha_secret_key | `''` |
| | license_auth / license_hash | NULL |
| `sys_cnf` | DefaultNameServer1..4 | NULL |
| | cron_secret_key | `''` |
| | admin_notification_email | `admin@example.com` |
| `dom_registers` (all) | whitelisted_ip, auth_userid, auth_apikey, def_ns1..4 | `''` |
| `payment_gateway` (all) | public/secret/webhook keys (live + test), webhook_url, merchant_id, merchant_pwd | `''` / NULL |
| | bank_name, account_name, account_number, routing_number, swift_code, iban | `''` |
| | extra_config JSON credential values (store_id, store_password, …) | `''` (keep JSON valid) |

## Verification grep

After sanitizing, run a scan for known-secret patterns (update the alternation
with the specific values from the current export if they differ):

```bash
grep -nE "whmaz\.com|localhost/webhook|localhost/ci-crm/webhook|pk_(test|live)_|sk_(test|live)_|whsec_|auth_apikey.*[A-Za-z0-9]{20}" crm_db.sql
```

Expect no matches in the seed `INSERT` rows (schema comments/URLs like the public
sandbox endpoints are fine). Anything else that looks like a real key, email, IP,
or bank detail should be blanked.

## Notes

- **New credential columns:** a re-export from a newer schema may add columns this
  list doesn't cover. The prompt explicitly asks Claude to flag any new
  credential/PII-bearing table or column so it isn't silently missed.
- **Gateway status:** sanitized `payment_gateway` rows keep `status=1` with empty
  keys — the buyer fills them in **Settings → Payment Gateways**. Set `status=0`
  instead if a fresh install should hide unconfigured gateways at checkout.
- **Related install logic:** `install/Install.php` (`truncateDataTables()`,
  `updateAdminCredentials()`, `updateNameservers()`, `storeLicenseInSettings()`)
  and `install/index.php` step 5 are what re-populate these rows at install time.
