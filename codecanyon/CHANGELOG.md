# Changelog

All notable changes to **WHMAZ - CI-CRM (Hosting & Service Provider CRM System)** will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
- **Email:** security@yourcompany.com
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
- **Support Email:** support@yourcompany.com
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

**Current Version:** 1.0.6
**Release Date:** February 8, 2026
**Status:** Stable Production Release (Feature Update)
