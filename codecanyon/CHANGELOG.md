# Changelog

All notable changes to **WHMAZ - Web Host Manager A to Z solutions. Lightweight Domain Hosting Management System** will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.0] - 2026-03-14

### Initial Release

This is the first stable release of WHMAZ, a comprehensive domain hosting management system for web hosting companies and service providers built with CodeIgniter 3.x.

---

## Complete Feature List

### Dual Portal Architecture

#### Client Portal (Customer Area)
- Self-service customer dashboard
- Modern, responsive UI with blue-purple gradient theme
- Mobile-friendly design
- All CSS externalized (CodeCanyon compliant)

#### Admin Portal (Management Area)
- Comprehensive admin dashboard with analytics
- AdminLTE 4.0.0 theme (MIT License)
- Server-side DataTables with pagination
- Real-time statistics and charts

---

### Order Management System

#### Shopping Cart
- Product/Service catalog browsing
- Multiple billing cycles support (Monthly, Quarterly, Semi-Annual, Annual, Biennial, Triennial)
- Cart item count badge with real-time updates
- Cart session persistence using cookies
- Automatic cart transfer from guest to user on login
- Domain + Hosting linking (purchase together)

#### Order Processing
- Complete order lifecycle management (Pending → Active → Expired)
- Manual order creation by admin
- Order status management
- Order notes and tracking

#### Admin Order Management
- Dedicated order management page for existing orders
- View order overview, customer info, domain items, and hosting items
- **Domain Management:**
  - Change domain registrar with optional transfer initiation
  - Cancel domain (immediate or end-of-period)
  - Status tracking (Pending, Active, Expired, Grace, Cancelled, Pending Transfer)
- **Hosting Management:**
  - Change hosting package with optional cPanel upgrade
  - Change server with optional account migration
  - Cancel service (immediate or end-of-period with optional cPanel deletion)
- **Order Cancellation:**
  - Cancel entire order with all domains and services
  - Automatic cancellation of unpaid/partially paid invoices
  - Cancellation reason tracking

---

### Domain Management

#### Domain Registration
- Domain availability check via registrar API
- Domain name suggestions
- Multiple TLD support with pricing
- Registration periods (1-10 years)
- Domain registration, transfer, and renewal

#### Domain Registrar Integration
- ResellerClub/Resell.biz API integration
- Namecheap API ready
- Live domain availability checking
- WHOIS information management
- DNS management interface
- EPP/Auth code management

#### Domain Pricing
- TLD pricing configuration
- Registration, transfer, and renewal pricing
- Multi-currency support
- Bulk pricing management

---

### Hosting/Service Management

#### Service Products
- Full CRUD for service products
- Service groups and categories
- Service modules
- Server assignment
- Pricing configuration with multiple billing cycles

#### Control Panel Integration (Multi-Module)
- **Supported panels:** cPanel/WHM, Plesk, DirectAdmin
- Module assigned per server — products inherit automatically
- Dynamic package dropdown from server API (all panels)
- Auto-populate product description from server package details
- **Real-time Hosting Usage Sync:**
  - Disk space usage
  - Bandwidth usage
  - Email accounts
  - Databases
  - Addon domains
  - Subdomains
- Single sign-on to control panel and Webmail
- Usage data persistence with sync timestamps

#### Server Management
- Server configuration with module assignment (cPanel, Plesk, DirectAdmin)
- API credentials management
- Server status monitoring

---

### Auto-Provisioning System

#### Domain Provisioning (After Payment)
- Automatic domain registration at registrar
- Domain transfer initiation with EPP code
- Domain renewal with expiry date update
- Customer and contact creation at registrar

#### Hosting Provisioning (After Payment)
- Automatic account creation via server module API (cPanel, Plesk, DirectAdmin)
- Module-specific welcome email with credentials
- Auto-unsuspend on renewal payment (if suspended)

#### Provisioning Management
- Admin Provisioning Logs UI with stats dashboard
- Filter by status (success/failed), item type, action
- Server-side DataTable with sorting and search
- Log details modal with API response data
- Retry failed provisioning items individually
- Extensible for additional control panels and registrars

---

### Promo Code / Coupon System

#### Promo Code Management (Admin)
- Create promo codes with fixed amount or percentage discounts
- Lifetime or date-range validity periods
- Global usage limits and per-customer usage limits
- Minimum order amount requirement
- Maximum discount cap for percentage-based codes
- Target: all orders, specific products, or specific customers
- Enable/disable toggle without deleting
- DataTables list with stats cards (total, active, expired, redemptions)
- Select2 multi-select for product and customer targeting

#### Cart Promo Code Integration (Client)
- Promo code input in shopping cart
- Real-time AJAX validation and discount preview
- Apply/remove promo code without page reload
- Discount line shown in cart totals (subtotal, discount, order total)
- Re-validation at checkout to prevent stale codes

#### Promo Code Tracking
- Usage recording per order with discount amount
- Race-condition-safe usage counter (atomic increment)
- Coupon code and discount shown on orders, invoices, and PDF

---

### Billing & Invoicing

#### Invoice Management
- Automated invoice generation
- Manual invoice creation
- Invoice editing and management
- Invoice status tracking (Due, Partial, Paid, Cancelled)
- PDF invoice generation and download
- Invoice line items with descriptions

#### Automated Renewal Invoices
- Cronjob-based renewal invoice generation
- 15 days before expiry notification
- Supports both services and domains
- Duplicate invoice prevention
- Respects auto_renew flag
- Email notifications for renewal invoices

#### Tax & Currency
- Multiple currency support
- Currency exchange rates
- Tax calculation
- VAT/GST support ready

---

### Payment Gateways

#### Stripe Integration
- Full PaymentIntent API integration
- Webhook support for payment events
- Test and Live mode configuration
- Refund processing
- Credit/Debit card payments

#### SSLCommerz Integration (Bangladesh)
- Complete payment integration
- IPN (Instant Payment Notification) callback
- Automatic session restoration for external redirects
- Test and Live mode support

#### Bank Transfer
- Manual payment recording
- Bank account details display
- Payment instructions for customers

#### Payment Features
- Payment confirmation emails (customer & admin)
- Transaction history and logs
- Webhook event logging
- Duplicate payment detection

---

### Support System

#### Ticket Management
- Multi-department ticket system
- Priority levels (Low, Medium, High, Critical)
- Ticket status tracking (Open, Pending, Resolved, Closed)
- File attachments support (secure viewing)
- Ticket conversation history with message bubbles
- Related service linking
- Internal notes for staff
- CSRF protection on all forms

#### Ticket Notifications
- **New ticket by client** → Department email notification
- **New ticket by admin** → Customer email notification
- **Admin reply** → Customer email notification
- **Client reply** → Department email notification

#### Contact Us Page
- Public contact form
- Department selection dropdown
- Google reCAPTCHA spam protection
- Email notifications to selected department

---

### Knowledge Base

#### Public Knowledge Base (No Auth Required)
- Category-based article organization
- Article search functionality
- Server-side pagination (10 articles per page)
- View count tracking
- Article ratings (Helpful/Not Helpful)
- Tags support
- Category sidebar navigation
- SEO-friendly URLs

#### KB Management (Admin)
- Article creation with Quill rich text editor
- Category management
- Publish/unpublish toggle
- Featured articles
- Article analytics

---

### Announcements

#### Public Announcements (No Auth Required)
- Announcement list with pagination
- Year-month archive grouping
- Archive filtering by month
- Social share buttons (Facebook, Twitter, LinkedIn)
- View count tracking

#### Announcement Management (Admin)
- Create and publish announcements
- Schedule announcements
- Category management
- Email notification options

---

### Dynamic Pages System

#### Page Management
- Create dynamic content pages (Terms & Conditions, Privacy Policy, Refund Policy, etc.)
- Rich text editor (Quill) for content
- SEO settings (meta title, description, keywords)
- Publish/unpublish toggle
- Sort order configuration
- System pages protection (cannot be deleted)
- View count tracking

#### Version History
- Complete version history tracking
- View previous versions
- One-click restore to any version
- Change type tracking (created, updated, restored)
- Admin user attribution

#### Public Page Display
- Clean, professional page template
- Breadcrumb navigation
- Last updated date display
- Mobile-responsive design
- No login required for published pages

---

### Email System

#### Email Template Management
- Full CRUD for email templates
- Quill rich text editor
- Template categories: DUNNING, INVOICE, ORDER, SERVICE, SUPPORT, AUTH, GENERAL
- Placeholder system for dynamic content:
  - `{client_name}`, `{company_name}`
  - `{invoice_no}`, `{amount_due}`, `{due_date}`
  - `{order_id}`, `{order_date}`
  - `{ticket_id}`, `{ticket_subject}`
  - `{site_name}`, `{site_url}`
- 10+ default templates included

#### Email Notifications
- Order confirmation emails (customer & admin)
- Payment confirmation emails (customer & admin)
- Invoice created notifications
- Ticket notification emails
- Password reset emails
- Email verification after registration
- Password change notifications

#### Raw SMTP Helper
- Direct PHP SMTP email sender
- Fixes CodeIgniter email line-length wrapping issues
- Base64 Content-Transfer-Encoding
- SSL and STARTTLS encryption support
- Auto-reads SMTP settings from database

---

### Dunning System

#### Dunning Rules Management
- Configurable dunning workflow
- Step-based rules (days after due)
- Action types: EMAIL, SUSPEND, TERMINATE
- Email template integration
- Workflow preview visualization
- Duplicate step validation

#### Dunning Automation
- Automated overdue payment collection
- Email reminders at configured intervals
- Service suspension/termination automation
- Dunning log tracking

---

### Cronjob System

#### Automated Tasks
- Renewal invoice generation
- Payment reminders
- Dunning workflow execution
- Domain sync
- Session cleanup

#### Cronjob Management (Admin)
- Configure cronjob schedules
- Quick preset buttons (hourly, daily, weekly, monthly)
- Enable/disable individual cronjobs
- Preview generated crontab content
- One-click copy to clipboard
- Auto-install to Linux crontab
- Manual installation instructions

#### Cronjob Security
- Secret key authentication
- CLI request bypass for server cron execution
- URL parameter validation

---

### Customer Management (CRM)

#### Company/Customer Database
- Complete customer profiles
- Contact management
- Server-side pagination with DataTables
- Advanced search and filtering
- Customer status management
- Export customer data

#### Customer Portal Features
- Profile management
- Password change with email notification
- Service history
- Invoice history
- Ticket history

---

### Expense Tracking

#### Expense Management
- Record business expenses
- Expense categories
- Vendor management
- Date-based tracking
- Receipt attachments

#### Expense Reporting
- Last 12 Months Expenses chart (Chart.js)
- Expense analytics
- Category breakdown
- Profit/loss calculation

---

### Admin Dashboard

#### Statistics & Metrics
- Revenue statistics with charts
- Order statistics (daily, monthly, yearly)
- Active services count
- Open tickets count
- Recent orders overview
- Recent invoices summary

#### Dashboard Widgets
- Last 12 Months Expenses chart
- Domain Selling Prices widget
- Quick action buttons
- Real-time metrics refresh

---

### System Configuration

#### General Settings
- Company information (name, address, BIN/Tax ID, city, state, country)
- Contact details
- Business hours
- Tax settings
- Invoice prefix/suffix
- Date/time formats
- Timezone configuration

#### System Config (sys_cnf)
- Key-value configuration management
- Grouped display by category
- Inline value editing
- Sensitive value masking
- Billing, Automation, Features, Notifications, Portal, Support settings

#### Email Configuration
- SMTP settings management
- Email templates
- Test email functionality
- Email logs

#### Payment Gateway Configuration
- Gateway enable/disable toggle
- API credentials (test/live modes)
- Webhook secrets
- Test connection button
- Transaction history viewing
- Webhook logs with filtering

---

### Security Features

#### Authentication Security
- CSRF protection on all forms
- XSS prevention with htmlspecialchars()
- SQL injection prevention (parameterized queries)
- Password hashing with bcrypt
- Session security with database storage
- HTTP-only cookies
- Email verification after registration

#### Login Security
- Google reCAPTCHA v2 on admin login
- Google reCAPTCHA on registration (configurable)
- Login rate limiting (brute force protection)
  - Maximum 5 failed attempts
  - 15-minute lockout duration
  - Tracks by IP and email
  - Shows remaining attempts warning
- Clickjacking protection (JavaScript frame-busting)

#### Content Security
- Content Security Policy (CSP) headers
- X-Frame-Options headers
- Rich text content sanitization
- Secure file upload handling (MIME validation, size limits)
- Secure ticket attachment viewing with authorization

#### Webhook Security
- Stripe signature verification
- SSLCommerz IPN validation
- Duplicate event detection
- All webhooks logged

---

### Technical Stack

#### Backend
- PHP 8.2+ compatible
- CodeIgniter 3.1.13 framework
- HMVC modular architecture
- MySQL 5.7+ / MariaDB 10.3+ support

#### Frontend
- Bootstrap 5.x responsive design
- AdminLTE 4.0.0 admin theme (MIT License)
- jQuery 3.x
- AngularJS for dynamic features
- DataTables with server-side processing
- Chart.js for data visualization
- Font Awesome 6.x icons
- Bootstrap Icons 1.13.1
- SweetAlert2 for alerts
- Quill rich text editor
- Select2 for enhanced dropdowns
- Moment.js for date handling

#### Code Quality
- PSR-2 coding standards
- Clean MVC architecture
- Comprehensive error handling
- Logging system
- All CSS externalized (CodeCanyon compliant)

---

### Database

- Normalized database design
- Foreign key constraints
- Indexes for performance
- Transaction support
- Database views for complex queries
- Complete schema in `crm_db.sql`
- Database views in `crm_db_views.sql`

---

### Server Requirements

| Component | Minimum | Recommended |
|-----------|---------|-------------|
| PHP | 8.2.0 | 8.3+ |
| MySQL | 5.7 | 8.0+ |
| MariaDB | 10.3 | 10.6+ |
| Apache | 2.4 | 2.4+ |
| Disk Space | 500 MB | 1 GB+ |
| RAM | 512 MB | 2 GB+ |

#### Required PHP Extensions
- curl, gd, mbstring, xml, zip, json, mysqli, openssl, fileinfo, intl

---

### Browser Compatibility

| Browser | Minimum | Recommended |
|---------|---------|-------------|
| Google Chrome | 90+ | Latest |
| Mozilla Firefox | 88+ | Latest |
| Safari | 14+ | Latest |
| Microsoft Edge | 90+ | Latest |
| Opera | 76+ | Latest |

Mobile browsers: Chrome Mobile, Safari iOS, Firefox Mobile, Samsung Internet

---

### Documentation

- README.md - Product overview and features
- INSTALLATION.md - Step-by-step installation guide (cPanel, Apache, Nginx)
- USER_GUIDE.md - Complete user manual for admins and customers
- CHANGELOG.md - Version history (this file)
- CREDITS.md - Third-party library credits
- license.txt - EULA license agreement

---

### Future Enhancements (Planned)

- Two-factor authentication (2FA) with Google Authenticator
- Multi-language support
- Mobile app (iOS/Android)
- Live chat integration
- Plesk integration
- Additional domain registrar integrations
- Social media login (OAuth)
- Affiliate/referral system
- Advanced reporting dashboard
- White-label branding options

---

## Support

- **Email:** support@whmaz.com
- **Response Time:** Within 48 hours (business days)
- **Support Period:** 6 months included (extendable)
- **Documentation:** README.md, INSTALLATION.md, USER_GUIDE.md

---

## Security

If you discover a security vulnerability, please email:
- **Email:** security@whmaz.com
- **Response Time:** Within 24 hours

**Do NOT** create public issues for security vulnerabilities.

---

## License

This project is licensed under the CodeCanyon License.
- **Regular License:** For single end product (one website)
- **Extended License:** For SaaS applications (multiple end users)

See license.txt for complete terms.

---

**Product:** WHMAZ - Web Host Manager A to Z solutions. Lightweight Domain Hosting Management System
**Version:** 1.0.0
**Release Date:** March 14, 2026
**Status:** Stable Production Release
**Copyright:** © 2026 WHMAZ. All Rights Reserved.
