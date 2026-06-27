# Plugin / Add-on System for WHMAZ CRM — Implementation Plan

> Status: **Planned** (not yet implemented). Scope locked: first-party plugins, full HMVC modules, primarily reacting to business events.

## Context

The admin wants to **upload, install, activate/deactivate, and uninstall plugins** so the CRM can be extended without editing core code. Per scoping:

- **Capability:** plugins primarily **react to business events** (order placed, payment received, service provisioned/suspended, domain registered/renewed, renewal invoice generated, ticket created). This requires a small **action/filter hook bus** in core — none exists today.
- **Trust model:** **first-party only** (you author the plugins). Upload is a convenience; we validate the manifest but do not sandbox/sign.
- **Package format:** **full HMVC module** — a plugin can carry its own controllers/views/models and auto-route via MX, in addition to event listeners.

### Why this fits the codebase
- CI3 + Wiredesignz **MX (HMVC)**. `Modules::$locations` is read from the `modules_locations` config item (`src/third_party/MX/Modules.php:7-10`), currently unset → defaults to `src/modules/`. We can register a **second location** (`plugins/`) with **zero core router edits** so plugin controllers route automatically.
- The CRM already uses a CI hook on `post_controller_constructor` (`src/config/hooks.php` → `src/hooks/ErrorHandler.php`). We add a second hook there to boot active plugins (DB + autoloaded libs are ready at that point, same as ErrorHandler).
- Admin CRUD + DataTables + upload patterns already exist to mirror (Promocode, General_setting).
- No CI migrations (disabled); plugins ship their own `install.sql` and run it programmatically on install.

---

## Architecture overview

```
plugins/                         # NEW dir at project root, registered as MX module location
  hello_dunning/                 # one plugin = one HMVC module (slug = folder name)
    plugin.json                  # manifest (slug, name, version, author, hooks[], admin_menu[], requires)
    Plugin.php                   # bootstrap class: install/uninstall/activate/deactivate/register
    install.sql                  # optional schema (run on install)
    uninstall.sql                # optional teardown (run on uninstall)
    controllers/  models/  views/  assets/   # optional, standard HMVC
    .htaccess + index.html       # deny direct access
```

- **Hook bus** = global functions backed by a static registry, available everywhere (controllers, models, cron).
- **Plugin loader** = a `post_controller_constructor` hook that, each request, reads **active** plugins from the `plugins` table, includes each `Plugin.php`, and calls `register()` so the plugin attaches its listeners before any event fires.
- **Admin manager** = `whmazadmin/plugin` controller: list, upload ZIP, install, activate, deactivate, uninstall, delete.

---

## Implementation steps

### 1. Hook bus (core, no DB)
**New:** `src/helpers/hook_helper.php` (add to `$autoload['helper']` in `src/config/autoload.php`).

Defines a singleton registry and procedural API mirroring the well-known WordPress shape:
- `add_action($event, $callback, $priority=10)`
- `do_action($event, ...$args)` — fire listeners (no return)
- `add_filter($event, $callback, $priority=10)`
- `apply_filters($event, $value, ...$args)` — listeners transform and return `$value`
- `remove_action` / `has_action` for completeness.

Internally one static class `Whmaz_Hooks` holding `['event' => [priority => [callbacks]]]`, sorted by priority. Listener exceptions are caught + logged (one bad plugin must not break checkout) via `log_message('error', ...)`.

### 2. Plugin loader hook (core)
**New:** `src/hooks/PluginLoader.php`. **Edit:** `src/config/hooks.php` to register it as a **second** `post_controller_constructor` entry (CI supports an array of hooks per point — convert the current single entry into a nested array).

`PluginLoader::__construct()`:
1. `$CI =& get_instance();`
2. Guard: if `plugins` table missing (fresh install / pre-migration), return silently.
3. Query active plugins (`status=1`). For each, `require_once plugins/{slug}/Plugin.php`, instantiate, call `->register()`. Cache include guard per request.
4. Wrap each plugin load in try/catch + log; a broken plugin is skipped, never fatal.

### 3. MX module location (core)
**Edit:** `src/config/config.php` — add:
```php
$config['modules_locations'] = array(
    APPPATH.'modules/'        => '../modules/',
    FCPATH.'plugins/'         => '../../plugins/',
);
```
(`FCPATH` = project root.) This makes `plugins/{slug}/controllers/...` route as `{slug}/...`. Plugin admin controllers extend `WHMAZADMIN_Controller` and call `$this->isLogin()` exactly like core admin controllers.

### 4. Registry table + base class
**New SQL file:** `plugin_system_schema.sql` (run it manually — per the no-direct-DB-execution preference). Table `plugins`:

| col | type | notes |
|---|---|---|
| id | PK | |
| slug | varchar, unique | = folder name |
| name, version, author, description | varchar/text | from manifest |
| manifest | text/json | cached manifest |
| status | tinyint | 0=inactive, 1=active |
| is_installed | tinyint | install() ran |
| installed_on, activated_on, created_on | datetime | |

**New:** `src/libraries/Plugin_base.php` — abstract base every `Plugin.php` extends. Provides default no-op `install()/uninstall()/activate()/deactivate()/register()`, a `runSql($file)` helper (reads plugin's `install.sql`/`uninstall.sql` and executes statements via `$this->CI->db`), and a `setting()` helper.

### 5. Admin manager (mirror Promocode pattern)
- **New controller:** `src/controllers/whmazadmin/Plugin.php` — methods:
  - `index()` — list page (reads `plugins` table + scans `plugins/` dir for not-yet-registered folders).
  - `upload()` — POST; accept `.zip` only, validate via `finfo` + extension (mirror `General_setting::upload_single_file`), extract with **`ZipArchive`** into a temp dir, validate `plugin.json` (required keys: slug, name, version), reject path traversal / slug mismatch, move to `plugins/{slug}/`, insert `plugins` row as inactive+not-installed. (ZipArchive is new to the codebase — confirm `ext-zip` is enabled.)
  - `install($slug)` — run plugin `install()` (which runs `install.sql`, seeds settings), set `is_installed=1`.
  - `activate($slug)` / `deactivate($slug)` — toggle `status`, call plugin `activate()/deactivate()`.
  - `uninstall($slug)` — run plugin `uninstall()` (drops its tables via `uninstall.sql`), set `is_installed=0, status=0`.
  - `delete($slug)` — uninstall if needed, then recursively remove `plugins/{slug}/` and the row.
  - `ssp_list_api()` optional (DataTables) — or render cards directly; list is small, cards are simpler.
  All state-changing actions: POST + CSRF using `$this->security->get_csrf_token_name()/get_csrf_hash()` (per project convention), confirmations via SweetAlert, results via `set_flashdata('admin_success'/'admin_error')`.
- **New model:** `src/models/Plugin_model.php` — `getAll()`, `getBySlug()`, `insertPlugin()`, `setStatus()`, `setInstalled()`, `deleteBySlug()`, plus `scanDiskPlugins()` (read `plugins/*/plugin.json`).
- **New views:** `src/views/whmazadmin/plugin_list.php` (card grid: name, version, author, status badge, action buttons + Upload modal). No manage form needed (config lives inside each plugin's own pages).

### 6. Menu injection (core)
**Edit:** `src/views/whmazadmin/include/header_menus.php`:
- Add a static **"Plugins"** link under the Settings dropdown (after Dynamic Pages) → `whmazadmin/plugin/index`.
- Add a dynamic block: render `apply_filters('admin_menu_items', [])` so active plugins can contribute nav entries declared in their manifest. A tiny `Plugin_model::getActiveMenuItems()` (or the hook) supplies `[label, url, icon]`; loop and echo `<li>` items. Keeps menu data-driven without each plugin editing core.

### 7. Seed hook fire points (core — the actual "react to business events")
Insert `do_action(...)` (and a couple `apply_filters`) at the verified call sites below. Keep payloads as associative arrays of IDs + already-loaded rows (no extra queries):

| Event name | File : method (approx line) | Payload |
|---|---|---|
| `order.created` | `src/modules/cart/controllers/Cart.php` : `checkoutSubmit` (~200) | `order_id`, `order[]`, `company_id`, `user_id` |
| `invoice.created` | same (~221) | `invoice_id`, `invoice[]`, `order_id` |
| `payment.completed` | `src/models/Payment_model.php` : `processSuccessfulPayment` (~325/351) | `transaction_id`, `transaction[]`, `invoice_id` |
| `invoice.paid` | same (~358, after status=PAID) | `invoice_id`, `invoice[]` |
| `service.provisioned` | `src/models/Provisioning_model.php` : `createHostingAccount` (~722) | `service[]`, `server`, `username`, `result` |
| `service.renewed` | `Provisioning_model.php` : `renewService` (~775) | `service[]`, `item[]` |
| `service.suspended` | `Provisioning_model.php` : `suspendService` (~862) | `service[]`, `reason` |
| `domain.registered` | `Provisioning_model.php` : `registerDomain` (~390) | `domain[]`, `registrar`, `result` |
| `domain.renewed` | `Provisioning_model.php` : `renewDomain` (~580) | `domain[]`, `new_exp_date` |
| `invoice.renewal_generated` | `src/models/Cronjob_model.php` : create*RenewalInvoice (~149/274/367) | `invoice_id`, type, `domain[]`/`service[]` |
| `ticket.created` | `src/modules/tickets/controllers/Tickets.php` : `new_ticket` (~73) and `whmazadmin/Ticket.php` : `add` (~59) | `ticket_id`, `form_data[]` |
| `ticket.replied` | `Tickets.php` : `replyticket` (~214) and `whmazadmin/Ticket.php` : `replyticket` (~198) | `ticket_id`, message |

Each is a single non-invasive line; existing behavior unchanged if no plugin listens.

### 8. Sample plugin (reference + smoke test)
**New:** `plugins/sample_audit/` — a minimal plugin that listens to `order.created`, `payment.completed`, `service.suspended` and writes a row to its own `plugin_sample_audit_log` table (created by its `install.sql`). Proves upload → install → activate → events fire → deactivate → uninstall end-to-end, and serves as the authoring template/docs.

### 9. Docs
Add `docs/PLUGIN_SYSTEM.md`: package structure, manifest schema, `Plugin.php` lifecycle, full list of hook names + payloads, and a "build your first plugin" walkthrough referencing `sample_audit`.

---

## Files touched

**New**
- `src/helpers/hook_helper.php`
- `src/hooks/PluginLoader.php`
- `src/libraries/Plugin_base.php`
- `src/controllers/whmazadmin/Plugin.php`
- `src/models/Plugin_model.php`
- `src/views/whmazadmin/plugin_list.php`
- `plugins/.htaccess`, `plugins/index.html`, `plugins/sample_audit/*`
- `plugin_system_schema.sql` (execute manually)
- `docs/PLUGIN_SYSTEM.md`

**Edited (core, minimal)**
- `src/config/autoload.php` (add `hook_helper`)
- `src/config/hooks.php` (add `PluginLoader` to `post_controller_constructor`)
- `src/config/config.php` (add `modules_locations`)
- `src/views/whmazadmin/include/header_menus.php` (Plugins link + dynamic menu loop)
- ~12 one-line `do_action()` insertions across Cart, Payment_model, Provisioning_model, Cronjob_model, Tickets, Ticket (step 7)

---

## Verification

1. Run `plugin_system_schema.sql` against the DB (creates `plugins`).
2. Confirm `php -m | grep zip` (ZipArchive) is available on the LAMPP PHP.
3. Visit `whmazadmin/plugin/index` → see empty list + Upload button.
4. ZIP `plugins/sample_audit/`, upload it → row appears as inactive. (Also confirm a pre-placed folder is detected by the disk scan.)
5. Install → its table is created; Activate → status active.
6. Place a test order / mark an invoice paid / run the suspension cron → confirm rows land in `plugin_sample_audit_log` (proves `order.created`, `payment.completed`, `service.suspended` fired through the bus).
7. Deactivate → events stop being recorded. Uninstall → its table dropped. Delete → folder + row removed.
8. Regression: with **no** active plugins, exercise checkout + payment + cron and confirm identical behavior to before (hook calls are inert).

## Open considerations (flag, not blockers)
- `plugins/` is web-reachable at project root; the `.htaccess` deny + `defined('BASEPATH')` guards protect PHP, but worth confirming the host's Apache honors `.htaccess` (this project already relies on root `.htaccess`).
- First-party trust means uploaded PHP runs unsandboxed — acceptable per scoping, but the upload endpoint must stay admin-only (controller already enforces `isLogin()`).
- Line numbers in step 7 are approximate (from exploration) — re-confirm exact insertion points at implementation time.
