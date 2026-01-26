# Changelog

All notable changes to **WHMAZ - CI-CRM (Hosting & Service Provider CRM System)** will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
- Automated service provisioning (cPanel, Plesk integration)
- Domain reseller management
- Email marketing integration
- Social media login (OAuth)
- Advanced security features (IP whitelisting, geo-blocking)

---

## Version History

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

**Current Version:** 1.0.1
**Release Date:** January 25, 2026
**Status:** Stable Production Release (Security Patch)
