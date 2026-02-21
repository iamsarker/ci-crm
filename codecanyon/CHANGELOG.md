# Changelog

All notable changes to **WHMAZ - CI-CRM (Hosting & Service Provider CRM System)** will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.9.0] - 2026-02-22

### New Feature - Auto-Provisioning System

#### Domain Provisioning
- **Domain Registration**: Automatically registers domain at registrar after payment
- **Domain Transfer**: Initiates transfer with EPP code after payment
- **Domain Renewal**: Renews domain at registrar, updates expiry date
- Creates customer and contact at registrar if not exists
- Supports ResellerClub/Resell.biz API (extensible for other registrars)

#### Hosting Provisioning
- **New Hosting**: Creates cPanel account via WHM API after payment
- **Hosting Renewal**: Unsuspends account if suspended, updates dates
- Sends welcome email with cPanel credentials

#### Provisioning Model
- `Provisioning_model::provisionInvoiceItems($invoiceId)` - Main entry point
- Handles all provisioning based on invoice items
- Logs all provisioning attempts to `provisioning_logs` table
- Supports retry for failed provisioning

#### New Files
- `src/models/Provisioning_model.php` - Main provisioning logic
- `src/helpers/domain_helper.php` - Domain registrar API functions
- `migrations/provisioning_system.sql` - Provisioning logs table

#### Domain Helper Functions
- `registrar_register_domain()` - Register domain (dispatcher)
- `registrar_transfer_domain()` - Transfer domain (dispatcher)
- `registrar_renew_domain()` - Renew domain (dispatcher)
- `registrar_get_or_create_customer()` - Create customer at registrar
- `registrar_create_contact()` - Create contact for domain
- ResellerClub-specific implementations included

#### Modified Files
- `src/models/Invoice_model.php` - Now uses Provisioning_model
- `src/config/autoload.php` - Added domain_helper to autoload
- `CLAUDE.md` - Added provisioning documentation

---

## [1.8.0] - 2026-02-22

### New Feature - Email Notifications System

#### Order Confirmation Emails
- Automatic email to customer after successful order placement
- Automatic email to admin when new order is placed (configurable via `notify_admin_new_order` in sys_cnf)
- Email templates: `order_confirmation`, `admin_order_notification`
- Includes order details, invoice information, and order items table
- Payment link for customer to pay invoice

#### Ticket Notification Emails
- **Client creates ticket** → Email sent to department (uses `ticket_depts.email`)
- **Admin creates ticket** → Email sent to customer
- **Admin replies to ticket** → Email sent to customer
- **Client replies to ticket** → Email sent to department
- Email templates: `ticket_new_to_department`, `ticket_new_to_customer`, `ticket_reply_to_customer`, `ticket_reply_to_department`
- Includes ticket details, customer info, and message content
- Links to view ticket in respective portal

#### Database Changes
- New email templates in `email_templates` table (6 new templates)
- Uses existing `ticket_depts.email` field for department notifications

#### New Migration Files
- `migrations/order_confirmation_emails.sql` - Order email templates
- `migrations/ticket_notification_emails.sql` - Ticket email templates

#### New Model Methods
- `Order_model::sendOrderConfirmationEmails($orderId, $invoiceId)`
- `Support_model::sendNewTicketToDepartment($ticketId)`
- `Support_model::sendNewTicketToCustomer($ticketId)`
- `Support_model::sendTicketReplyToCustomer($ticketId, $message)`
- `Support_model::sendTicketReplyToDepartment($ticketId, $message)`

#### Modified Files
- `src/models/Order_model.php` - Added email sending methods
- `src/models/Support_model.php` - Added ticket email methods
- `src/modules/cart/controllers/Cart.php` - Calls order confirmation emails
- `src/modules/tickets/controllers/Tickets.php` - Calls ticket emails (client)
- `src/controllers/whmazadmin/Ticket.php` - Calls ticket emails (admin)
- `CLAUDE.md` - Updated documentation

---

## [1.7.0] - 2026-02-13

### New Feature - Contact Us Page & Client Portal Beautification

#### Contact Us Page
- New Contact Us page in client portal (`supports/contactus`)
- Department selection dropdown from `ticket_depts` table
- Sends contact form submissions to selected department email
- Google reCAPTCHA integration for spam protection
- Form validation for all required fields
- CSRF protection
- Mobile-responsive design with teal color scheme

#### Client Portal Auth Pages Beautification
- Redesigned Login page with modern card layout
- Redesigned Registration page with two-column layout
- Redesigned Forgot Password page with orange theme
- Redesigned Reset Password page with orange theme
- Consistent teal gradient styling (#00897B to #00695C)
- Input groups with icons
- Mobile-responsive design

#### Code Improvements
- Added `RECAPTCHA_VERIFY_URL` constant in `constants.php`
- Centralized reCAPTCHA verify URL configuration
- Environment variable support via `.env` file
- Removed hardcoded URLs from controllers

#### New Files
- `src/modules/supports/views/support_contactus.php` - Contact Us view

#### Modified Files
- `src/modules/supports/controllers/Supports.php` - Added contactus method
- `src/models/Support_model.php` - Added department methods
- `src/modules/auth/views/auth_login.php` - Beautified
- `src/modules/auth/views/auth_register.php` - Beautified
- `src/modules/auth/views/auth_forgetpass.php` - Beautified
- `src/modules/auth/views/auth_resetpassword.php` - Beautified
- `src/config/constants.php` - Added RECAPTCHA_VERIFY_URL constant
- `resources/assets/css/custom.css` - Added auth page styles
- `.env` - Added RECAPTCHA_VERIFY_URL

---

## [1.6.0] - 2026-02-13

### New Feature - Dynamic Pages Management with Version History

#### Dynamic Pages System
- Create and manage dynamic content pages (Terms & Conditions, Privacy Policy, Refund Policy, etc.)
- Rich text editor (Quill) for page content with full formatting support
- SEO settings per page (meta title, description, keywords)
- Publish/unpublish toggle for controlling page visibility
- Sort order for organizing pages
- System pages protection (cannot be deleted)
- View count tracking per page
- Public page URLs accessible without login

#### Version History & Restore
- Complete version history tracking for all page changes
- View previous versions of any page
- Restore any historical version with one click
- Change type tracking (created, updated, restored)
- Change notes for restoration actions
- Admin user attribution for all changes

#### Admin Portal Features
- Dynamic Pages list with server-side DataTable pagination
- Stats cards showing total pages, published, drafts, and total views
- Quick toggle for publish/unpublish status
- History viewer with timeline display
- Restore confirmation with SweetAlert2

#### Public Page Viewing
- Clean, professional page display template
- Breadcrumb navigation
- Last updated date display
- Mobile-responsive design
- No login required to view published pages

#### Footer Integration
- Updated client portal footer with page links
- Updated admin portal footer with page links
- Links to Terms & Conditions, Privacy Policy, Refund Policy

#### Database Changes
- New `pages` table for storing dynamic pages
- New `page_history` table for version control
- `pages_view` database view for listing with admin names

#### New Files
- `src/controllers/Pages.php` - Public page controller
- `src/controllers/whmazadmin/Pages.php` - Admin page controller
- `src/models/Page_model.php` - Page model with CRUD and history
- `src/views/whmazadmin/page_list.php` - Admin list view
- `src/views/whmazadmin/page_manage.php` - Admin create/edit view
- `src/views/whmazadmin/page_history.php` - Version history view
- `src/views/templates/page_view.php` - Public page template
- `database/migrations/create_pages_tables.sql` - Database migration

#### Modified Files
- `src/views/templates/customer/footer.php` - Added page links
- `src/views/whmazadmin/include/footer.php` - Added page links
- `src/views/whmazadmin/include/header_menus.php` - Added Dynamic Pages menu
- `src/config/routes.php` - Added pages route

---

## [1.5.0] - 2026-02-12

### New Feature - Admin Portal General Settings Enhancements

#### System Config Tab
- New System Config tab in General Settings for managing `sys_cnf` table key-value pairs
- Grouped display by `cnf_group` (DNS, SYSTEM, COMPANY_INFO, etc.)
- Inline value editing with save/cancel buttons
- Sensitive value masking (keys containing "secret", "password", "authkey", "api_key", "token")
- Read-only keys and groups (only values editable)

#### Cronjobs Tab with Linux Integration
- New Cronjobs tab in General Settings for managing scheduled tasks
- Configure cronjob schedules (minute, hour, day, month, weekday)
- Quick preset buttons (Every hour, Daily midnight, Daily 6 AM, Weekly, Monthly)
- Enable/disable cronjobs with toggle button
- Preview generated crontab content
- One-click copy to clipboard
- Auto-install to Linux crontab (requires server permissions)
- Manual installation instructions provided

#### Company Information Section Updates
- Added BIN / Tax ID field
- Added City, State fields
- Country field changed from text input to dropdown (from `countries` table)
- Reorganized layout for better UX

#### Database Changes
- New `cron_schedules` table for cronjob configuration
- New columns in `app_settings`: `bin_tax`, `city`, `state`, `country`

#### New Files
- `src/models/Syscnf_model.php` - System config CRUD operations
- `src/models/Cronschedule_model.php` - Cronjob schedule management with crontab generation

#### Modified Files
- `src/controllers/whmazadmin/General_setting.php` - Added sys_cnf and cronjob methods
- `src/views/whmazadmin/general_setting_manage.php` - Added System Config and Cronjobs tabs
- `src/models/Cronjob_model.php` - Added `getSysConfig()` method for secure cronjob authentication

#### Cronjob Security
- Secret key authentication via `sys_cnf` table (`cron_secret_key`)
- CLI requests bypass authentication (for server cron execution)
- URL parameter validation: `/cronjobs/run?key=YOUR_SECRET_KEY`

---

## [1.4.0] - 2026-02-12

### New Feature - Automated Renewal Invoice Generation

#### Renewal Invoice Cronjob System
- Automated generation of renewal invoices 15 days before service/domain expiry
- Supports both services (hosting packages) and domains
- Prevents duplicate invoices using billing period tracking
- Respects `auto_renew` flag on services/domains
- Excludes one-time and free billing cycles
- Transaction-safe invoice creation

#### Cronjob Endpoints
- `/cronjobs/run` - Main entry point, runs all automated tasks
- `/cronjobs/generateRenewalInvoices` - Generate renewal invoices only
- `/cronjobs/testRenewal/{days}` - Test mode: preview expiring items without creating invoices
- `/cronjobs` - Show cronjob system info and setup instructions

#### Email Notifications
- Automatic email to customers when renewal invoice is generated
- Uses `invoice_created` email template with placeholders
- Includes invoice details, due date, and payment link

#### New Files
- `src/models/Cronjob_model.php` - Complete renewal invoice logic with:
  - `getExpiringServices()` - Find services expiring within X days
  - `getExpiringDomains()` - Find domains expiring within X days
  - `createServiceRenewalInvoice()` - Create invoice for service renewal
  - `createDomainRenewalInvoice()` - Create invoice for domain renewal
  - `updateNextDueDateAfterPayment()` - Update dates when invoice is paid

#### Modified Files
- `src/modules/cronjobs/controllers/Cronjobs.php` - Complete rewrite with:
  - Main `run()` method for cron execution
  - `generateRenewalInvoices()` with service and domain processing
  - `sendRenewalInvoiceEmail()` for customer notifications
  - CLI and HTTP response support

#### Cron Setup
```bash
# Run daily at 6:00 AM
0 6 * * * curl -s http://yoursite.com/cronjobs/run > /dev/null 2>&1
```

#### How It Works
1. Cronjob runs daily and checks for items expiring in next 15 days
2. For each expiring item with `auto_renew=1`:
   - Creates invoice with due_date = next_due_date
   - Links invoice_item to order_service/order_domain via ref_id
   - Records billing_period_start and billing_period_end
3. Sends email notification to customer
4. When invoice is paid, next_due_date is updated to billing_period_end

---

## [1.3.0] - 2026-02-12

### New Feature - Cart Item Count Badge

#### Cart Count Display in Header
- Real-time cart item count badge displayed in client portal header
- AJAX-powered updates without page reload
- Badge shows item count (1-99) or "99+" for larger quantities
- Badge automatically hidden when cart is empty
- Red circular badge with white text for visibility

#### Cart Session Persistence Fix
- Changed `customer_session_id` storage from PHP session to cookie
- Cookie persists for 30 days with httponly flag
- Fixes cart loss during session regeneration on login
- Guest cart items now properly transfer to user account on login

#### Automatic Cart Transfer on Login
- Guest cart items automatically assigned to user's `user_id` after login
- Seamless shopping experience from guest to registered user
- No cart items lost during authentication process

#### New Helper Functions
- `getCartCount()` - Returns total cart item count for current user/session
- Updated `getCustomerSessionId()` - Now uses cookies for persistence

#### New API Endpoint
- `cart/getCount` - Returns JSON with cart item count `{"count": N}`

#### JavaScript Integration
- `loadCartCount()` - Global function to refresh cart count via AJAX
- Auto-loads on page load
- Can be called after add/remove cart operations

#### Modified Files
- `src/helpers/whmaz_helper.php` - Added `getCartCount()`, updated `getCustomerSessionId()` to use cookies
- `src/modules/cart/controllers/Cart.php` - Added `getCount()` endpoint
- `src/modules/auth/controllers/Auth.php` - Added cart transfer on login
- `src/views/templates/customer/header.php` - Added cart count badge element
- `src/views/templates/customer/footer_script.php` - Added `loadCartCount()` AJAX function
- `resources/assets/css/custom.css` - Added `.cart-count-badge` styling

---

## [1.2.0] - 2026-02-11

### Enhancement - cPanel Real-Time Usage Sync

#### cPanel Usage Statistics Sync
- Real-time sync of hosting account usage from cPanel/WHM servers
- Sync button in Service Detail page "Package / Usage" section
- Fetches live data: Disk space, Bandwidth, Email accounts, Databases, Addon domains, Subdomains
- Progress bars with percentage visualization
- Last sync timestamp display
- Data saved to database for persistence between page loads

#### New Helper Functions
- `whm_cpanel_api2_call()` - Call cPanel API2 functions via WHM API
- `whm_get_account_stats()` - Fetch complete account usage statistics

#### New Model Methods
- `saveCpanelUsageStats()` - Save cPanel stats to order_services table
- `getCpanelUsageStats()` - Retrieve stored cPanel stats with percentage calculations

#### New API Endpoint
- `clientarea/sync_cpanel_usage` - AJAX endpoint for syncing cPanel data

#### Service Detail Page Beautification
- Modern page header with gradient background and breadcrumbs
- Domain banner with hostname, product name, and status badge
- Sidebar with DNS card and quick actions
- Order details card with icons
- Usage/package card with real-time sync capability
- Conditional description and instructions cards
- Responsive design for mobile devices

#### Database Changes
New columns in `order_services` table:
- `cp_disk_used`, `cp_disk_limit` - Disk usage in MB
- `cp_bandwidth_used`, `cp_bandwidth_limit` - Bandwidth in MB
- `cp_email_accounts`, `cp_email_limit` - Email account count
- `cp_databases`, `cp_database_limit` - Database count
- `cp_addon_domains`, `cp_addon_limit` - Addon domain count
- `cp_subdomains`, `cp_subdomain_limit` - Subdomain count
- `cp_last_sync` - Last sync timestamp

#### Modified Files
- `src/helpers/cpanel_helper.php` - New API2 call function and stats fetcher
- `src/models/Clientarea_model.php` - Save/get cPanel usage methods
- `src/modules/clientarea/controllers/Clientarea.php` - Sync endpoint
- `src/modules/clientarea/views/clientarea_service_detail.php` - Complete redesign with sync
- `resources/assets/css/custom.css` - Service detail page styles

---

## [1.1.0] - 2026-02-11

### Enhancement - Ticket System & Security Fixes

#### New Ticket Page Beautification
- Complete redesign of "Open New Ticket" page with modern form layout
- Organized form sections: Contact Information, Ticket Details, Message, Attachments
- Input groups with icons for all form fields
- Required field indicators with red asterisks
- Gradient header with title and subtitle
- Modern Quill editor wrapper with styled toolbar
- Improved attachment upload with add button
- Gradient submit button with hover effects
- Responsive design for mobile devices

#### Related Service Dropdown
- Added "Related Service" dropdown to ticket form
- Populated from active services (`order_services` table) for logged-in user
- Displays product name with domain (e.g., "Basic Hosting (example.com)")
- New `getActiveServicesDropdown()` method in `Support_model.php`
- Saves `order_service_id` with ticket for service-specific support

#### Dashboard List Items Beautification
- Styled "Recent Support Tickets" and "Recent Invoices" sections
- Card-like appearance with 3px margin between items
- Gradient backgrounds with rounded corners (10px border-radius)
- Subtle box-shadow for depth
- Hover effects with blue border highlight and slide animation
- Color-coded icon shadows for tickets and invoices

#### Quill Editor Message Fix
- Fixed form submission breaking when message contains quotes
- Changed from dynamic hidden input to hidden textarea
- Use jQuery `.val()` method instead of string concatenation
- Applies to both `newticket.php` and `viewticket.php`

#### Enhanced HTML Sanitization
- Updated `sanitize_html()` to **escape** dangerous tags instead of removing
- Tags like `<script>`, `<iframe>`, `<form>` now display as visible text
- Prevents XSS while allowing support staff to see original message content
- Dangerous tags list: script, iframe, object, embed, form, input, button, textarea, select, style, link, meta, base

#### Secure Ticket Attachment Viewing
- Implemented `vtattachments()` method in Tickets controller
- Company validation (users can only view their own ticket attachments)
- Directory traversal prevention using `basename()`
- MIME type validation (whitelist: gif, jpeg, png, pdf, txt)
- URL changed to query parameter: `/tickets/vtattachments/{id}?file={filename}`
- Multiple attachments per ticket/reply supported

#### File Upload Fix
- Fixed upload path using `FCPATH` instead of `realpath()` (prevents false on Windows)
- Auto-create upload directory if it doesn't exist
- Fixed `upload_files()` to return actual filename from `$upload_data['file_name']`
- Proper handling of multiple file uploads

#### CSRF Token Fixes
- Added `<?= csrf_field() ?>` to `newticket.php` form
- Added `<?= csrf_field() ?>` to `viewticket.php` reply form

#### Modified Files
- `resources/assets/css/custom.css` - Dashboard list and ticket form styles
- `src/modules/tickets/views/newticket.php` - Complete redesign
- `src/modules/tickets/views/viewticket.php` - Attachment display, CSRF, Quill fix
- `src/modules/tickets/controllers/Tickets.php` - Attachment viewer, file upload fixes
- `src/models/Support_model.php` - `getActiveServicesDropdown()` method
- `src/models/Common_model.php` - Fixed upload filename return
- `src/helpers/whmaz_helper.php` - Enhanced `sanitize_html()`

---

## [1.0.9] - 2026-02-11

### UI Enhancement - Client Portal Beautification

#### Complete Client Portal Redesign
- Modern, professional UI for all client portal pages
- Consistent blue-purple gradient theme (`#0168fa` to `#6f42c1`) across all pages
- All CSS externalized to `resources/assets/css/custom.css` (CodeCanyon compliant)
- Responsive design with mobile-friendly layouts
- Fixed footer positioning with flexbox layout

#### Dashboard (clientarea_index.php)
- Redesigned welcome banner with gradient background
- Modern stat cards for Services, Domains, Tickets, and Invoices
- Activity cards for recent tickets and invoices with styled list items
- Quick action buttons with hover effects
- Refresh buttons for real-time data updates

#### Services List Page (clientarea_services.php)
- Styled page header with gradient background
- Modern table design with hover effects
- Clickable order links with pill-style buttons
- Service description with domain info
- Date cells with calendar icons
- Invoice summary sidebar with styled header

#### Domains List Page (clientarea_domains.php)
- Consistent gradient header matching other pages
- Domain name cells with globe icons
- Registration and expiry dates with icons
- Invoice summary sidebar with styled header
- Empty state design for no domains

#### Domain Detail Page (clientarea_domain_detail.php)
- Nameserver management section with DNS type selector
- Contact information form with country dropdown
- Sync from registrar API button
- Send EPP Code functionality with AJAX
- Styled sidebar with domain information

#### Invoices List Page (billing_invoices.php)
- Modern table with styled invoice links
- Amount cells with currency highlighting
- Status badges with appropriate colors
- Action buttons with hover animations
- Invoice summary sidebar

#### View Invoice Page (billing_viewinvoice.php)
- Fixed footer overlap issue
- Proper content-footer spacing with flexbox

#### Tickets List Page (tickets.php)
- Styled ticket links with hover effects
- Priority and status badges
- Department badges
- Ticket subject with ellipsis for long titles
- Invoice summary sidebar

#### View Ticket Page (viewticket.php)
- Ticket information sidebar with styled labels
- Reply form card with Quill editor wrapper
- Conversation thread with message bubbles
- Color-coded messages (customer: blue, staff: green, original: purple)
- Message avatars with gradient backgrounds
- Helpful/Not Helpful feedback buttons
- Attachment view buttons
- Responsive message layout

#### Technical Implementation
- Added 400+ lines of CSS to `custom.css`
- Flexbox layout for footer positioning
- CSS transitions and hover effects
- Media queries for responsive design

#### Modified Files
- `resources/assets/css/custom.css` - All new styles (CodeCanyon compliant)
- `src/modules/clientarea/views/clientarea_index.php` - Dashboard redesign
- `src/modules/clientarea/views/clientarea_services.php` - Services list
- `src/modules/clientarea/views/clientarea_domains.php` - Domains list
- `src/modules/clientarea/views/clientarea_domain_detail.php` - Domain detail
- `src/modules/billing/views/billing_invoices.php` - Invoices list
- `src/modules/billing/views/billing_viewinvoice.php` - View invoice
- `src/modules/tickets/views/tickets.php` - Tickets list
- `src/modules/tickets/views/viewticket.php` - View ticket
- `src/modules/clientarea/controllers/Clientarea.php` - Domain API methods
- `src/models/Clientarea_model.php` - Domain model methods
- `src/views/templates/customer/domain_nav.php` - EPP code functionality

---

## [1.0.8] - 2026-02-09

### Feature Enhancement - Admin Dashboard Widgets

#### Last 12 Months Expenses Chart
- Added interactive bar chart using Chart.js to visualize monthly expenses
- Displays total expenses summary in card header
- Auto-fills missing months with zero values for consistent display
- Empty state handling when no expense data available
- Refresh button to reload chart data
- Link to expenses management page

#### Domain Selling Prices Widget
- Added domain pricing table showing registration, transfer, and renewal prices
- Displays domain extensions with color-coded pricing (green/blue/orange)
- Scrollable list supporting up to 10 domain extensions
- Shows currency symbol from database configuration
- Link to domain pricing management page

#### Technical Implementation
- Added `getDomainPrices()` method in Dashboard_model
- Added `getLast12MonthsExpenses()` method in Dashboard_model
- Added `domain_prices_api` endpoint in Dashboard controller
- Added `expenses_chart_api` endpoint in Dashboard controller
- Updated AngularJS controller with chart rendering logic
- Integrated Chart.js library in admin footer scripts

#### Modified Files
- `src/models/Dashboard_model.php` - Added 2 new methods for data retrieval
- `src/controllers/whmazadmin/Dashboard.php` - Added 2 API endpoints
- `resources/angular/app/admindashboard_controller.js` - Added chart functions
- `src/views/whmazadmin/dashboard_index.php` - Updated widget UI
- `src/views/whmazadmin/include/footer_script.php` - Added Chart.js include
- `src/config/config.php` - Added API endpoints to CSRF exclusion

---

## [1.0.7] - 2026-02-09

### Security Enhancement - Admin Login reCAPTCHA & Clickjacking Protection

#### Google reCAPTCHA on Admin Login
- Added Google reCAPTCHA v2 protection to Admin Portal login page
- Prevents automated brute force attacks on admin accounts
- Uses same reCAPTCHA configuration as user registration (from `app_settings` table)
- reCAPTCHA widget only appears if keys are configured in General Settings

#### Clickjacking Protection (Defense-in-Depth)
- Added JavaScript frame-busting to both Customer Portal and Admin Portal
- Provides additional layer of protection beyond HTTP headers (X-Frame-Options, CSP frame-ancestors)
- Attempts to break out of iframe or displays security warning if embedded
- Protects login pages from being loaded in malicious iframes

#### Modified Files
- `src/controllers/whmazadmin/Authenticate.php` - Added reCAPTCHA verification in login method
- `src/views/whmazadmin/admin_login.php` - Added reCAPTCHA script and widget
- `src/views/templates/customer/header.php` - Added frame-busting JavaScript
- `src/views/whmazadmin/include/header_script.php` - Added frame-busting JavaScript

---

## [1.0.6] - 2026-02-08

### New Features - Public Knowledge Base & Announcements UI

#### Knowledge Base (Public Pages - No Auth Required)
- **KB List Page** (`/supports/KB`) - Modern card-based category grid with article list
  - Category cards with article counts in a responsive grid layout
  - Searchable article list with icons, tags, and view counts
  - Sidebar with category navigation
  - Server-side pagination (10 articles per page)
- **KB Category Page** (`/supports/kb_category/{id}/{slug}`) - Filter articles by category
  - Active category highlighting in sidebar
  - Breadcrumb navigation
  - Pagination support
- **KB Detail Page** (`/supports/view_kb/{id}/{slug}`) - Article view with rating
  - Article content with meta info (tags, views)
  - Helpful rating buttons (thumbs up/down)
  - Category sidebar navigation

#### Announcements (Public Pages - No Auth Required)
- **Announcement List Page** (`/supports/announcements`) - Clean list view
  - List view with icons and view counts
  - Year-month archive sidebar grouping with counts
  - Server-side pagination
- **Announcement Archive Page** (`/supports/announcements_archive/{year}/{month}`) - Filter by month
  - Month-filtered announcements
  - Active month highlighting in sidebar
  - Pagination support
- **Announcement Detail Page** (`/supports/view_announcement/{id}/{slug}`) - Full announcement view
  - Social share buttons (Facebook, Twitter, LinkedIn, Copy Link)
  - Archive sidebar navigation

#### Model Enhancements
- Added `loadKBList($limit, $offset)` - Pagination support for KB list
- Added `countKBList()` - Total KB count
- Added `loadKBListByCategory($catId, $limit, $offset)` - Paginated category filter
- Added `countKBListByCategory($catId)` - Category article count
- Added `getKBCategoryById($catId)` - Get category details
- Added `loadAnnouncements($limit, $offset)` - Pagination support
- Added `countAnnouncements()` - Total announcements count
- Added `getAnnouncementArchive()` - Year-month grouping with counts
- Added `loadAnnouncementsByMonth($year, $month, $limit, $offset)` - Month filter
- Added `countAnnouncementsByMonth($year, $month)` - Month count

#### Controller Updates
- Updated `Supports.php` controller with pagination parameters
- Added `kb_category($catId, $slug, $page)` method
- Added `announcements_archive($year, $month, $page)` method
- Configurable `$per_page = 10` property

#### New View Files
- `support_kb_category.php` - KB articles filtered by category
- `support_announcement_archive.php` - Announcements filtered by month

#### Modified Files
- `src/modules/supports/controllers/Supports.php` - Added pagination and new methods
- `src/models/Support_model.php` - Added pagination and archive methods
- `src/modules/supports/views/support_kb_list.php` - Complete UI redesign
- `src/modules/supports/views/support_kb_details.php` - Improved layout
- `src/modules/supports/views/support_announcement_list.php` - List view with archive sidebar
- `src/modules/supports/views/support_announcement_detail.php` - Share buttons, archive sidebar

---

## [1.0.5] - 2026-01-31

### New Features - Email System & Account Security

#### Raw SMTP Email Helper
- Added `sendHtmlEmail()` function to `whmaz_helper.php` — raw PHP SMTP email sender bypassing CI3's email library
- Fixes CI3 email library line-length wrapping issue that broke long URLs (e.g., password reset links)
- Uses base64 Content-Transfer-Encoding to avoid line-length issues entirely
- Supports SSL and STARTTLS encryption, AUTH LOGIN authentication
- Auto-reads SMTP settings from `app_settings` table
- Error logging via CI's `log_message()`

#### Email Verification After Registration
- Registration now sends a verification email with a clickable link
- New `sendVerificationEmail()` method in `Auth_model.php`
- New `verify($hash)` endpoint in `Auth.php` controller (`/auth/verify/{hash}`)
- Users must verify email before logging in (status `2` → `1`)
- Uses `sendHtmlEmail()` helper for reliable email delivery

#### Change Password (Client Portal)
- New Change Password page at `/clientarea/changePassword`
- **New View:** `src/modules/clientarea/views/clientarea_changepassword.php`
- Validates current password before allowing change
- Minimum 8-character password requirement with confirm match
- Sends email notification to user after successful password change
- New `changePassword()` method in `Clientarea_model.php`

#### Code Cleanup
- Removed duplicate toast/flashdata script blocks from 21 module view files
- Toast messages now rendered from a single location: `templates/customer/footer.php`
- 14 standalone toast script blocks removed entirely
- 7 mixed script blocks had only toast lines removed (preserved other JS)

#### Modified Files
- `src/helpers/whmaz_helper.php` — Added `sendHtmlEmail()` function
- `src/models/Auth_model.php` — Updated `sendResetLinkEmail()` to use `sendHtmlEmail()`, added `sendVerificationEmail()`
- `src/modules/auth/controllers/Auth.php` — Added `verify()` endpoint, updated `register()` to send verification email
- `src/modules/clientarea/controllers/Clientarea.php` — Added `changePassword()` method
- `src/models/Clientarea_model.php` — Added `changePassword()` method
- `src/modules/clientarea/views/clientarea_changepassword.php` — **New file** (change password page)
- 21 module view files — Removed duplicate toast/flashdata code

---

## [1.0.4] - 2026-01-28

### New Feature - Email Template Management & Dunning System

#### Email Template Management
- Added full CRUD for email templates (`email_templates` table)
- **New Files:**
  - `src/models/Emailtemplate_model.php` - Email template model with SSP support
  - `src/controllers/whmazadmin/Email_template.php` - Admin controller
  - `src/views/whmazadmin/email_template_list.php` - Template listing with server-side DataTable
  - `src/views/whmazadmin/email_template_manage.php` - Add/edit with Quill rich text editor
- Template categories: DUNNING, INVOICE, ORDER, SERVICE, SUPPORT, AUTH, GENERAL
- Unique template keys for programmatic access (e.g., `dunning_reminder_1`)
- Placeholder system: `{client_name}`, `{invoice_no}`, `{amount_due}`, `{due_date}`, `{days_overdue}`, `{invoice_url}`, `{currency}`, `{site_name}`, `{site_url}`
- 10 default templates included (5 dunning, 2 invoice, 1 order, 2 auth)
- **SQL File:** `crm_email_templates.sql`

#### Dunning Rules Management
- Added dunning rules configuration in General Settings (new "Dunning" tab)
- **New Files:**
  - `src/models/Dunningrule_model.php` - Dunning rule model
- AJAX-based CRUD via modal (step number, days after due, action type, email template dropdown)
- Action types: EMAIL, SUSPEND, TERMINATE
- Dunning workflow preview visualization
- Email template dropdown filtered by DUNNING category with link to template management
- Duplicate step number validation
- **Database Tables:** `dunning_rules`, `dunning_log`

#### Enhancements
- Updated `ssp_helper.php` with `$extraWhere` parameter for additional WHERE conditions (soft delete filter)
- General Settings page now has two tabs: General Setting and Dunning

---

## [1.0.3] - 2026-01-27

### New Feature - Service Product Management

#### Service Product CRUD
- Added full CRUD for service products (`product_services` table)
- **New Files:**
  - `src/models/Serviceproduct_model.php` - Service product model with SSP support
  - `src/controllers/whmazadmin/Service_product.php` - Admin controller
  - `src/views/whmazadmin/service_product_list.php` - Product listing with server-side DataTable
  - `src/views/whmazadmin/service_product_manage.php` - Add/edit product form
- Server-side DataTable pagination via `product_service_view` database view
- Service group, service type, module, and server assignment
- Hidden/visible toggle and soft delete support

#### cPanel/WHM Integration
- Added dynamic cPanel package dropdown on service product manage page
- Loads available hosting packages from selected WHM server via API
- Triggers when service type is `SHARED_HOSTING` or `RESELLER_HOSTING` and module is `cpanel`
- Auto-populates product description from cPanel package details (disk space, bandwidth, addon domains, FTP accounts, email accounts, databases, subdomains, shell access)
- **New Helper:** `src/helpers/cpanel_helper.php` with `whm_list_packages()` function
- Added `cpanel` helper to autoload configuration

#### Bug Fixes
- Fixed `Cart.php` bug where `company_id` was incorrectly set to `$userId` instead of `$companyId` when saving order services (caused "Service not found" error in company management)
- Fixed `ssp_helper.php` column search: numeric values now use exact match (`=`) instead of `LIKE` to prevent false matches (e.g., searching for company ID "2" no longer returns "12")

#### Database Updates
- Updated `product_service_view` to include `cp_package`, `updated_on`, `servce_type_name` columns
- Changed `servers` join from `JOIN` to `LEFT JOIN` in `product_service_view` (server_id can be NULL)
- Added `product_service_types` join for service type name

---

## [1.0.2] - 2026-01-26

### Security Enhancement - Rate Limiting

#### Login Rate Limiting (Brute Force Protection)
- Implemented comprehensive rate limiting for both Customer Portal and Admin Portal
- **Configuration:**
  - Maximum 5 failed attempts before lockout
  - 15-minute lockout duration
  - Auto-cleanup of records older than 24 hours
- **Features:**
  - Tracks failed attempts by both IP address and email
  - Shows remaining attempts warning (when 3 or fewer left)
  - Displays unlock time when locked out
  - Clears failed attempts on successful login
- **New Files:**
  - `src/models/Loginattempt_model.php` - Rate limiting model
  - `database/migrations/003_create_login_attempts_table.sql` - Database migration
- **Modified Files:**
  - `src/models/Auth_model.php` - Added rate limiting checks
  - `src/models/Adminauth_model.php` - Added rate limiting checks
  - `src/modules/auth/controllers/Auth.php` - Handle rate limit errors
  - `src/controllers/whmazadmin/Authenticate.php` - Handle rate limit errors

#### Additional SQL Injection Fixes
- Fixed SQL injection in 5 more model files:
  - `Currency_model.php` - getDetail() method
  - `Kbcat_model.php` - getDetail() method
  - `Expensecategory_model.php` - getDetail() method
  - `Expensevendor_model.php` - getDetail() method
  - `Package_model.php` - getDetail() method

#### Additional XSS Fixes
- Fixed `addslashes()` to `json_encode()` in 10 more list view files for proper JavaScript context escaping

#### Onclick Handler XSS Fixes
- Fixed unescaped title parameters in onclick handlers using `json_encode()` for 9 list view files:
  - `currency_list.php`, `expense_category_list.php`, `expense_vendor_list.php`
  - `kb_category_list.php`, `server_list.php`, `service_category_list.php`
  - `service_group_list.php`, `service_module_list.php`, `ticket_department_list.php`

#### Rich Text Content Sanitization
- Added new `sanitize_html()` helper function for safe rich text rendering
  - Allows safe formatting tags (p, b, i, a, ul, ol, li, h1-h6, etc.)
  - Strips dangerous elements (onclick, javascript:, etc.)
- Applied sanitization to rich text content display in:
  - `ticket_manage.php` (admin) - ticket messages and replies
  - `viewticket.php` (client) - ticket messages and replies
  - `support_kb_details.php` - knowledge base articles
  - `support_announcement_detail.php` - announcements

---

## [1.0.1] - 2026-01-25

### Security Patch

#### SQL Injection Fixes
- Fixed SQL injection vulnerabilities in 6 additional model files:
  - `Server_model.php` - getDetail() method
  - `Servicecategory_model.php` - getDetail() method
  - `Servicegroup_model.php` - getDetail() method
  - `Servicemodule_model.php` - getDetail() method
  - `Ticketdepartment_model.php` - getDetail() method
  - `Support_model.php` - loadKBCatList(), loadKBList(), loadAnnouncements() methods
- All queries now use prepared statements with parameter binding

#### XSS Prevention Fixes
- Added `htmlspecialchars()` with null coalescing operator (`??`) to 7 view files:
  - `service_category_manage.php`
  - `service_group_manage.php`
  - `service_module_manage.php`
  - `ticket_department_manage.php`
  - `ticket_manage.php`
  - `server_manage.php`
  - `package_manage.php`
- Fixed JavaScript flash messages to use `json_encode()` instead of `addslashes()`

#### Invoice Tab Enhancement
- Updated `company_manage.php` invoice tab with DataTable for server-side processing
- Added "Mark as Paid" functionality for invoices
- Invoice view and PDF download from company management page

---

## [1.0.0] - 2026-01-25

### Initial Release

This is the first stable release of WHMAZ - CI-CRM, a comprehensive CRM system for hosting and service providers built with CodeIgniter 3.x.

---

## Features

### Client Portal (Customer Area)

#### Account Management
- **User Registration & Authentication**
  - Self-service registration with email verification
  - Secure login with CSRF protection
  - Password reset functionality
  - Two-factor authentication ready
  - Session management with database storage

- **Profile Management**
  - Complete profile editing
  - Contact information updates
  - Password change
  - Security settings
  - Email preferences

#### Service Management
- **Order Management**
  - View all active and past orders
  - Order details with service information
  - Order status tracking
  - Service upgrade/downgrade requests
  - Renewal management

- **Product & Services**
  - Hosting packages catalog
  - Service specifications
  - Package comparison
  - Add-on services
  - Custom service requests

- **Domain Management**
  - Domain search with live availability checking
  - Domain registration
  - Domain transfer
  - Domain renewal
  - WHOIS management
  - DNS management interface
  - Domain pricing by TLD
  - Multi-year registration support

#### Billing & Invoicing
- **Invoice Management**
  - View all invoices
  - Invoice details with line items
  - Payment history
  - Download invoices (PDF)
  - Invoice status tracking
  - Automated invoice generation

- **Payment Processing**
  - Multiple payment gateway support
  - Credit card payments
  - PayPal integration ready
  - Stripe integration ready
  - Bank transfer information
  - Payment confirmation emails
  - Transaction history

- **Billing Features**
  - Recurring billing automation
  - Prorated billing
  - Tax calculation
  - Multiple currency support
  - Credit balance management
  - Payment reminders

#### Support System
- **Ticket System**
  - Create support tickets
  - Ticket categories/departments
  - Priority levels (Low, Medium, High, Urgent)
  - Ticket status tracking (Open, Pending, Resolved, Closed)
  - File attachments support
  - Ticket conversation history
  - Email notifications
  - Ticket search and filtering

- **Knowledge Base**
  - Browse articles by category
  - Article search functionality
  - Related articles suggestions
  - Article ratings/feedback
  - Popular articles
  - Recent articles

- **Announcements**
  - View system announcements
  - Service updates
  - Maintenance notifications
  - News and updates

### Admin Portal (Management Area)

#### Dashboard & Analytics
- **Admin Dashboard**
  - Revenue statistics with charts
  - Order statistics (daily, monthly, yearly)
  - Active services count
  - Recent orders overview
  - Recent invoices summary
  - Open tickets count
  - Pending tasks
  - Quick action buttons
  - Real-time metrics

- **Reports & Analytics**
  - Sales reports
  - Revenue analysis
  - Customer growth metrics
  - Service performance
  - Support metrics
  - Export to CSV/Excel

#### Customer Management
- **Customer Database**
  - Complete customer list with server-side pagination
  - Advanced search and filtering
  - Customer details view
  - Registration date tracking
  - Customer status management
  - Bulk operations
  - Export customer data

- **Customer Services**
  - View customer orders
  - Service history
  - Billing history
  - Support ticket history
  - Notes and comments
  - Customer tags/labels

#### Order & Service Management
- **Order Management**
  - Order list with DataTables pagination
  - Order creation (manual orders)
  - Order editing
  - Order status management (Pending, Active, Suspended, Cancelled, Fraud)
  - Order approval workflow
  - Service provisioning
  - Order notes
  - Order search by multiple criteria

- **Package Management**
  - Hosting package creation
  - Package specifications (CPU, RAM, Disk, Bandwidth)
  - Pricing configuration
  - Package features list
  - Package groups/categories
  - Enable/disable packages
  - Package ordering
  - Promotional pricing

#### Domain Management
- **Domain Pricing**
  - TLD pricing configuration
  - Registration pricing by period
  - Transfer pricing
  - Renewal pricing
  - Multi-currency support
  - Bulk pricing import
  - Price history tracking

- **Domain Registration Integration**
  - Resell.biz API integration
  - ResellerClub API support
  - Domain availability checking
  - Live domain suggestions
  - Domain registration automation
  - Domain transfer handling
  - WHOIS information management

- **Domain Extensions**
  - TLD management
  - Extension enable/disable
  - Popular TLDs highlighting
  - Extension grouping

#### Financial Management
- **Invoice Management**
  - Invoice generation
  - Invoice editing
  - Invoice status management
  - Payment recording
  - Refund processing
  - Invoice templates
  - Automated recurring invoices
  - Invoice reminders

- **Expense Tracking**
  - Record expenses
  - Expense categories
  - Vendor management
  - Expense reports
  - Profit/loss calculation
  - Receipt attachments

- **Currency Management**
  - Multiple currencies support
  - Currency rates
  - Default currency setting
  - Currency symbols
  - Automatic rate updates

#### Support Management
- **Ticket Management**
  - View all tickets with pagination
  - Ticket assignment
  - Department management
  - Priority management
  - Status updates
  - Internal notes
  - Canned responses
  - Ticket merging
  - Mass actions

- **Knowledge Base Management**
  - Article creation
  - Article categories
  - Article publishing
  - Featured articles
  - Article analytics
  - SEO optimization
  - Rich text editor

- **Announcement Management**
  - Create announcements
  - Publish to client portal
  - Schedule announcements
  - Announcement categories
  - Email notifications

#### System Configuration
- **Company Settings**
  - Company information
  - Contact details
  - Business hours
  - Tax settings
  - Invoice prefix/suffix
  - Date/time formats
  - Timezone configuration

- **Email Configuration**
  - SMTP settings
  - Email templates
  - Email notifications
  - Email queue
  - Test email functionality
  - Email logs

- **Payment Gateway Configuration**
  - Gateway enable/disable
  - API credentials
  - Gateway settings
  - Test mode
  - Transaction fees

- **Security Settings**
  - CSRF protection (enabled by default)
  - XSS filtering
  - SQL injection prevention
  - Password hashing (bcrypt)
  - Session security
  - IP blocking
  - Login attempt limits
  - Security logs

- **System Maintenance**
  - Database backup
  - System logs
  - Error logs
  - Activity logs
  - Cache management
  - System updates

### Technical Features

#### Security & Performance
- **Security Features**
  - CSRF protection on all forms
  - XSS prevention with escapeXSS() function
  - SQL injection prevention (parameterized queries)
  - Password hashing with password_hash() (bcrypt)
  - Session security with database storage
  - Session encryption
  - Input validation and sanitization
  - HTTP-only cookies
  - Error logging and monitoring
  - Secure file upload handling

- **Performance Optimization**
  - Server-side pagination for large datasets
  - Database query optimization
  - Session file storage
  - Gzip compression ready
  - Browser caching configuration
  - OPcache support
  - Efficient AJAX loading
  - Lazy loading support

#### User Interface
- **Modern UI/UX**
  - Bootstrap 5 responsive design
  - Mobile-friendly interface
  - Tablet optimized
  - Clean and professional design
  - Intuitive navigation
  - Feather Icons
  - Font Awesome icons
  - Loading indicators
  - Toast notifications
  - Modal dialogs
  - Form validation feedback

- **Data Tables**
  - DataTables with server-side processing
  - Advanced search
  - Column sorting
  - Pagination
  - Responsive tables
  - Export functionality (CSV, Excel, PDF)
  - Bulk actions
  - Inline editing

#### Framework & Architecture
- **CodeIgniter 3.x HMVC**
  - Modular structure
  - Clean MVC architecture
  - Reusable components
  - Helper functions
  - Custom libraries
  - Hooks system
  - Database abstraction layer

- **Database**
  - MySQL 5.7+ / MariaDB 10.3+ support
  - Normalized database design
  - Foreign key constraints
  - Indexes for performance
  - Transaction support
  - Migration ready

#### Integration & APIs
- **Third-Party Integrations**
  - Resell.biz API (domain registration)
  - ResellerClub API support
  - PayPal payment gateway ready
  - Stripe payment gateway ready
  - SMTP email delivery
  - SMS gateway ready (Twilio, etc.)

- **JavaScript Libraries**
  - jQuery 3.x
  - AngularJS for dynamic features
  - Chart.js for analytics
  - FullCalendar for scheduling
  - SweetAlert2 for alerts
  - Quill Editor for rich text
  - Select2 for enhanced dropdowns
  - Moment.js for date handling

#### Developer Features
- **Code Quality**
  - PSR-2 coding standards
  - Clean code architecture
  - Comprehensive comments
  - Error handling
  - Logging system
  - Debugging tools (development mode)

- **Customization**
  - Template system
  - Custom hooks
  - Plugin architecture
  - Theme support ready
  - Configuration files
  - Environment-based settings

### System Requirements

#### Server Requirements
- PHP 8.2 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache 2.4+ or Nginx 1.18+
- mod_rewrite enabled (Apache)
- SSL certificate (recommended)

#### Required PHP Extensions
- mysqli
- mbstring
- openssl
- xml
- json
- curl
- zip
- gd or imagick
- fileinfo

#### Recommended Server Specifications
- 2+ CPU cores
- 4GB+ RAM
- 20GB+ SSD storage
- 100Mbps+ network

### Known Issues

#### Version 1.0.0 Known Issues
- None reported at initial release

### Future Enhancements (Planned)

#### Upcoming Features
- Two-factor authentication (2FA) with Google Authenticator
- Advanced reporting dashboard
- Client portal API
- Mobile app (iOS/Android)
- Live chat integration
- Automated backup system
- Multi-language support
- White-label branding options
- Advanced tax calculation (VAT, GST, etc.)
- Affiliate/referral system
- Client credit system
- Product bundles
- Automated service provisioning (cPanel account creation/suspension, Plesk integration)
- Domain reseller management
- Email marketing integration
- Social media login (OAuth)
- Advanced security features (IP whitelisting, geo-blocking)

---

## Version History

### [1.6.0] - 2026-02-13 - New Feature
- Dynamic Pages Management with version history
- Create/edit Terms & Conditions, Privacy Policy, Refund Policy pages
- Rich text editor (Quill) for page content
- SEO settings (meta title, description, keywords)
- Version history with restore functionality
- Public page viewing without login required
- Footer links updated in client and admin portals

### [1.5.0] - 2026-02-12 - New Feature
- System Config tab for managing sys_cnf key-value pairs
- Cronjobs tab for Linux crontab schedule management
- Company Information updates (BIN/Tax ID, City, State, Country dropdown)
- One-click crontab installation to Linux
- Cronjob schedule quick presets

### [1.4.0] - 2026-02-12 - New Feature
- Automated renewal invoice generation 15 days before expiry
- Cronjob system with `/cronjobs/run` endpoint
- Email notifications for renewal invoices
- Support for both services and domains
- Duplicate invoice prevention using billing period tracking

### [1.3.0] - 2026-02-12 - New Feature
- Added cart item count badge in client portal header
- AJAX-powered real-time cart count display
- Fixed cart session persistence using cookies instead of PHP session
- Automatic cart transfer from guest to user on login
- Added `getCartCount()` helper and `cart/getCount` API endpoint
- Added global `loadCartCount()` JavaScript function

### [1.1.0] - 2026-02-11 - Enhancement
- Beautified New Ticket page with modern form design
- Added Related Service dropdown from active services
- Beautified dashboard list items with 3px margin
- Fixed Quill editor message submission
- Enhanced sanitize_html() to escape dangerous tags
- Implemented secure ticket attachment viewing
- Fixed file upload filename handling
- Added CSRF tokens to ticket forms

### [1.0.9] - 2026-02-11 - UI Enhancement
- Complete client portal UI beautification
- Modern dashboard with welcome banner and stat cards
- Redesigned services, domains, invoices, and tickets list pages
- Enhanced view ticket page with conversation thread styling
- Consistent gradient theme across all client portal pages
- All CSS externalized (CodeCanyon compliant)
- Fixed footer positioning issue

### [1.0.8] - 2026-02-09 - Feature Enhancement
- Added Last 12 Months Expenses chart to admin dashboard (Chart.js bar chart)
- Added Domain Selling Prices widget with pricing table
- Integrated Chart.js for data visualization

### [1.0.7] - 2026-02-09 - Security Enhancement
- Added Google reCAPTCHA v2 to Admin Portal login page
- Protects admin accounts from automated brute force attacks
- Uses existing reCAPTCHA configuration from General Settings
- Added JavaScript frame-busting for clickjacking protection (defense-in-depth)

### [1.0.6] - 2026-02-08 - Feature Update
- Added public Knowledge Base pages with modern card-based UI (no authentication required)
- Added KB category filtering with pagination
- Added public Announcements pages with year-month archive grouping
- Added announcement archive filtering by month
- Added server-side pagination for KB and Announcements (10 items per page)
- Added social share buttons on announcement detail page

### [1.0.5] - 2026-01-31 - Feature Update
- Added raw SMTP `sendHtmlEmail()` helper (fixes CI3 email line-length wrapping issue)
- Added email verification after registration with verify endpoint
- Added Change Password page in Client Portal with email notification
- Removed duplicate toast/flashdata code from 21 module view files (centralized in footer)

### [1.0.4] - 2026-01-28 - Feature Update
- Added Email Template Management (CRUD with Quill editor, categories, placeholders, 10 defaults)
- Added Dunning Rules Management in General Settings (configurable steps, email template integration)
- Added `dunning_rules`, `dunning_log`, `email_templates` tables
- Updated SSP helper with `$extraWhere` parameter

### [1.0.3] - 2026-01-27 - Feature Update
- Added Service Product Management (CRUD with server-side DataTable)
- Added cPanel/WHM integration (dynamic package dropdown, auto-populate description)
- Added `cpanel_helper.php` for WHM API calls
- Fixed Cart.php company_id bug
- Fixed SSP helper numeric column search exact match
- Updated `product_service_view` database view

### [1.0.2] - 2026-01-26 - Security Enhancement
- Login rate limiting (brute force protection)
- Additional SQL injection and XSS fixes
- Rich text content sanitization

### [1.0.1] - 2026-01-25 - Security Patch
- Fixed SQL injection in 6 model files (Server, Servicecategory, Servicegroup, Servicemodule, Ticketdepartment, Support)
- Fixed XSS vulnerabilities in 7 admin view files
- Added null coalescing for safer data handling
- Enhanced invoice management in company_manage page

### [1.0.0] - 2026-01-25 - Initial Release
- First stable production release
- Complete CRM functionality for hosting providers
- Dual portal system (Client + Admin)
- Domain management with Resell.biz integration
- Comprehensive billing and invoicing
- Full support ticket system
- Knowledge base
- Expense tracking
- Multi-currency support
- Security hardened
- CodeCanyon compliance achieved

---

## Upgrade Instructions

### Upgrading to Future Versions

When upgrading from one version to another:

1. **Backup Everything**
   ```bash
   # Backup database
   mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql

   # Backup files
   tar -czf backup_files_$(date +%Y%m%d).tar.gz /path/to/whmaz/
   ```

2. **Read Release Notes**
   - Always read the changelog for breaking changes
   - Review new features and configuration requirements
   - Check for deprecated features

3. **Test on Staging First**
   - Never upgrade directly on production
   - Test the upgrade process on staging environment
   - Verify all functionality works

4. **Follow Upgrade Guide**
   - Each version may include specific upgrade instructions
   - Run any required database migrations
   - Update configuration files as needed

5. **Clear Cache**
   ```bash
   # Clear application cache
   rm -rf application/cache/*

   # Clear session data (if safe)
   rm -rf application/sessions/*
   ```

---

## Security Updates

### Reporting Security Issues

If you discover a security vulnerability, please email:
- **Email:** security@whmaz.com
- **Response Time:** Within 24 hours

**Do NOT** create public GitHub issues for security vulnerabilities.

### Security Changelog

#### Version 1.0.0 Security Features
- ✅ CSRF protection enabled globally
- ✅ XSS prevention implemented
- ✅ SQL injection prevention (parameterized queries)
- ✅ Password hashing with bcrypt
- ✅ Session security hardened
- ✅ HTTP-only cookies enabled
- ✅ Secure file upload handling
- ✅ Input validation on all forms
- ✅ Error logging without exposing sensitive data

---

## Breaking Changes

### Version 1.0.0
- Initial release - no breaking changes

---

## Deprecations

### Version 1.0.0
- No deprecated features in initial release

---

## License

This project is licensed under the terms specified in LICENSE.txt

For commercial use, please refer to CodeCanyon licensing:
- **Regular License:** For end products (single website)
- **Extended License:** For SaaS applications (multiple end users)

---

## Support

### Getting Help

- **Documentation:** See README.md and INSTALLATION.md
- **Support Email:** support@whmaz.com
- **Support Period:** 6 months included (extendable)
- **Response Time:** Within 48 hours (business days)

### Community

- **GitHub Issues:** For bug reports (after verifying it's not configuration)
- **Feature Requests:** Email with subject "Feature Request: [Title]"
- **FAQ:** See README.md FAQ section

---

## Credits

See CREDITS.md for complete list of third-party libraries and their licenses.

---

## Acknowledgments

Special thanks to:
- CodeIgniter Framework team
- Bootstrap team
- All open-source contributors whose libraries make this project possible

---

**Note:** This changelog will be updated with each new release. Stay tuned for exciting features and improvements!

**Current Version:** 1.9.0
**Release Date:** February 22, 2026
**Status:** Stable Production Release (New Feature)
