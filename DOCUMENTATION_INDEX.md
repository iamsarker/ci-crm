# CI-CRM Documentation Index

Welcome to the CI-CRM project documentation. This index will guide you to the right documentation based on your needs.

---

## Quick Start

**New to the project?** Start here:
1. Read [Project Overview](#project-overview) below
2. Review [Portal Structure Guide](PORTAL_STRUCTURE_GUIDE.md)
3. Check [Coding Standards](CODING_STANDARDS_AND_PATTERNS.md)

---

## Project Overview

**Project Name:** CI-CRM - Web Host Manager A to Z Solutions
**Type:** Hosting/Service Provider CRM System (WHMCS Alternative)
**Framework:** CodeIgniter 3.x with HMVC Architecture
**Version:** 1.0

### What is CI-CRM?

CI-CRM is a complete business management system designed for hosting and service providers, being developed as an **open-source alternative to WHMCS** (Web Host Manager Complete Solution). It includes:

- **Dual Portal System:**
  - **Client Portal:** Customer-facing interface for ordering services, managing accounts, viewing invoices
  - **Admin Portal:** Backend administration panel for managing the entire business

- **Core Features:**
  - Service and domain management
  - Order processing and shopping cart
  - Invoice generation and payment processing
  - Support ticket system
  - Knowledge base
  - Multi-currency support
  - Automated tasks (cron jobs)
  - Expense tracking
  - Reporting and analytics

---

## Documentation Structure

### 1. [PHP82_UPGRADE_AND_TROUBLESHOOTING.md](PHP82_UPGRADE_AND_TROUBLESHOOTING.md) ⭐ NEW
**Purpose:** PHP 8.2+ upgrade fixes and comprehensive troubleshooting guide
**Best for:** Resolving PHP 8.2+ compatibility issues, debugging common problems

**Contents:**
- PHP 8.2+ compatibility fixes (deprecation warnings)
- Base URL configuration fixes
- Domain search functionality setup
- Resources loading troubleshooting
- Common issues and solutions
- Development tips and debugging
- Upgrade checklist

**When to use:**
- Upgrading to PHP 8.2+
- Fixing deprecation warnings
- Troubleshooting resource loading issues
- Setting up domain registrar integration
- Debugging base URL problems
- General troubleshooting

---

### 2. [WHMCS_FEATURE_COMPARISON.md](WHMCS_FEATURE_COMPARISON.md)
**Purpose:** WHMCS feature parity tracking and development roadmap
**Best for:** Understanding project goals, feature priorities, and development timeline

**Contents:**
- Current implementation status vs WHMCS
- Feature comparison checklist
- Missing features to implement
- Development roadmap by phase
- Priority matrix
- Competitive advantages
- Success metrics

**When to use:**
- Planning new features
- Prioritizing development work
- Understanding project vision
- Comparing with WHMCS functionality

---

### 3. [PROJECT_DOCUMENTATION.md](PROJECT_DOCUMENTATION.md)
**Purpose:** Complete technical reference for the entire project
**Best for:** Understanding the system architecture, features, and database structure

**Contents:**
- Project overview and architecture
- Dual portal system explanation
- Complete feature documentation (Client Portal & Admin Portal)
- View templates and UI structure
- Database schema (48 tables)
- Authentication system details
- Technical architecture (HMVC implementation)
- Configuration guides
- Security considerations

**When to use:**
- Need comprehensive information about any feature
- Want to understand the complete system
- Looking for database table details
- Need configuration help

---

### 4. [PORTAL_STRUCTURE_GUIDE.md](PORTAL_STRUCTURE_GUIDE.md)
**Purpose:** Quick reference for the dual portal architecture
**Best for:** Understanding the separation between client and admin portals

**Contents:**
- Client Portal structure (HMVC modules)
- Admin Portal structure (traditional controllers)
- Folder organization comparison
- Base controller differences
- Authentication flow comparison
- URL patterns and routing
- Helper function references
- Key differences summary table

**When to use:**
- Need to understand which portal handles what
- Looking for specific controller or view locations
- Want to know URL patterns
- Need authentication flow details

---

### 5. [CODING_STANDARDS_AND_PATTERNS.md](CODING_STANDARDS_AND_PATTERNS.md)
**Purpose:** Comprehensive coding guidelines and best practices
**Best for:** Developers writing new code or maintaining existing code

**Contents:**
- View template patterns (Admin & Client)
- Controller patterns with examples
- Model structure and patterns
- Database query patterns
- Form validation patterns
- JavaScript/AJAX patterns (AngularJS & jQuery)
- Helper function usage
- Naming conventions
- Security best practices
- Complete code examples

**When to use:**
- Writing new controllers or models
- Creating new views
- Implementing forms with validation
- Working with DataTables
- Need code examples
- Want to follow project standards

---

## Common Tasks & Where to Find Help

### Fixing PHP 8.2+ Issues
1. Check **PHP 8.2+ Compatibility Fixes** in [PHP82_UPGRADE_AND_TROUBLESHOOTING.md](PHP82_UPGRADE_AND_TROUBLESHOOTING.md#php-82-compatibility-fixes)
2. Review **Common Issues & Solutions** in [PHP82_UPGRADE_AND_TROUBLESHOOTING.md](PHP82_UPGRADE_AND_TROUBLESHOOTING.md#common-issues--solutions)
3. Follow **Upgrade Checklist** in [PHP82_UPGRADE_AND_TROUBLESHOOTING.md](PHP82_UPGRADE_AND_TROUBLESHOOTING.md#upgrade-checklist)

### Troubleshooting Resources Loading
1. Review **Base URL Configuration Fix** in [PHP82_UPGRADE_AND_TROUBLESHOOTING.md](PHP82_UPGRADE_AND_TROUBLESHOOTING.md#base-url-configuration-fix)
2. Check **Resources Loading Issues** in [PHP82_UPGRADE_AND_TROUBLESHOOTING.md](PHP82_UPGRADE_AND_TROUBLESHOOTING.md#resources-loading-issues)

### Setting Up Domain Search
1. Check **Domain Search Functionality** in [PHP82_UPGRADE_AND_TROUBLESHOOTING.md](PHP82_UPGRADE_AND_TROUBLESHOOTING.md#domain-search-functionality)
2. Follow **Domain Registrar Configuration** steps in [PHP82_UPGRADE_AND_TROUBLESHOOTING.md](PHP82_UPGRADE_AND_TROUBLESHOOTING.md#domain-registrar-configuration)

### Adding a New Admin Feature
1. Review **Admin Portal Features** in [PROJECT_DOCUMENTATION.md](PROJECT_DOCUMENTATION.md#admin-portal-features)
2. Follow **Controller Patterns** in [CODING_STANDARDS_AND_PATTERNS.md](CODING_STANDARDS_AND_PATTERNS.md#controller-patterns)
3. Use **View Template Patterns** in [CODING_STANDARDS_AND_PATTERNS.md](CODING_STANDARDS_AND_PATTERNS.md#view-templates-structure)
4. Check **Admin Portal Structure** in [PORTAL_STRUCTURE_GUIDE.md](PORTAL_STRUCTURE_GUIDE.md#2-admin-portal-backend-administration)

### Adding a New Client Feature
1. Review **Client Portal Features** in [PROJECT_DOCUMENTATION.md](PROJECT_DOCUMENTATION.md#client-portal-features)
2. Follow **HMVC Module Pattern** in [CODING_STANDARDS_AND_PATTERNS.md](CODING_STANDARDS_AND_PATTERNS.md#client-controller-pattern-hmvc-module)
3. Check **Client Portal Structure** in [PORTAL_STRUCTURE_GUIDE.md](PORTAL_STRUCTURE_GUIDE.md#1-client-portal-customer-facing)

### Creating Forms
1. Check **Form Structure Pattern** in [CODING_STANDARDS_AND_PATTERNS.md](CODING_STANDARDS_AND_PATTERNS.md#form-structure-pattern)
2. Review **Form Validation** in [CODING_STANDARDS_AND_PATTERNS.md](CODING_STANDARDS_AND_PATTERNS.md#form-and-validation-patterns)
3. Use **Form UI Components** in [PROJECT_DOCUMENTATION.md](PROJECT_DOCUMENTATION.md#form-ui-components)

### Working with Database
1. Review **Database Schema** in [PROJECT_DOCUMENTATION.md](PROJECT_DOCUMENTATION.md#database-schema)
2. Follow **Database Query Patterns** in [CODING_STANDARDS_AND_PATTERNS.md](CODING_STANDARDS_AND_PATTERNS.md#database-query-patterns)
3. Use **Model Patterns** in [CODING_STANDARDS_AND_PATTERNS.md](CODING_STANDARDS_AND_PATTERNS.md#model-patterns)

### Implementing DataTables
1. Check **DataTable Patterns** in [CODING_STANDARDS_AND_PATTERNS.md](CODING_STANDARDS_AND_PATTERNS.md#datatable-patterns)
2. For server-side with JOINs, see **Pattern 3: Package Pricing Example** in [CODING_STANDARDS_AND_PATTERNS.md](CODING_STANDARDS_AND_PATTERNS.md#pattern-3-server-side-datatable-with-joins-package-pricing-example)
3. Review **DataTable UI Patterns** in [PROJECT_DOCUMENTATION.md](PROJECT_DOCUMENTATION.md#datatable-ui-patterns)

### Security Implementation
1. Review **Security Best Practices** in [CODING_STANDARDS_AND_PATTERNS.md](CODING_STANDARDS_AND_PATTERNS.md#security-best-practices)
2. Check **Authentication System** in [PROJECT_DOCUMENTATION.md](PROJECT_DOCUMENTATION.md#authentication-system)

---

## File Locations Quick Reference

### Admin Portal
- **Controllers:** `src/controllers/whmazadmin/`
- **Views:** `src/views/whmazadmin/`
- **Base Controller:** `src/core/WHMAZADMIN_Controller.php`
- **URL Pattern:** `domain.com/whmazadmin/controller/method`

### Client Portal
- **Controllers:** `src/modules/{module}/controllers/`
- **Views:** `src/modules/{module}/views/`
- **Base Controller:** `src/core/WHMAZ_Controller.php`
- **URL Pattern:** `domain.com/module/controller/method`

### Shared Resources
- **Models:** `src/models/`
- **Libraries:** `src/libraries/`
- **Helpers:** `src/helpers/`
- **Config:** `src/config/`
- **Assets:** `resources/`
- **Uploads:** `uploadedfiles/`

---

## Technology Stack Summary

### Backend
- **Framework:** CodeIgniter 3.x
- **Architecture:** HMVC (Wiredesignz Modular Extensions)
- **PHP Version:** 8.2+ (7.4+ minimum)
- **Database:** MySQL/MariaDB

### Frontend
- **CSS Framework:** Bootstrap 5
- **Theme:** DashForge
- **JavaScript:** jQuery 3.x, AngularJS 1.x
- **Icons:** Feather Icons, FontAwesome
- **Data Tables:** DataTables
- **Dialogs:** SweetAlert2
- **Enhanced Selects:** Select2
- **Editor:** Quill

---

## Key Features by Portal

### Client Portal (Customer-Facing)
- Customer authentication and registration
- Shopping cart and checkout
- Service and domain ordering
- Invoice viewing and payment
- Support ticket system
- Customer dashboard
- Profile management
- Multi-currency support

### Admin Portal (Backend)
- Admin authentication with RBAC
- Dashboard with analytics
- Customer/company management
- Service package management
- Server configuration
- Domain pricing management
- Order management
- Invoice generation and PDF
- Payment tracking
- Expense management
- Support ticket management
- Knowledge base management
- Announcement system
- Reporting and analytics
- System configuration

---

## Database Overview

**Total Tables:** 48

**Key Table Groups:**
- User & Authentication (7 tables)
- Product & Service (7 tables)
- Domain Management (3 tables)
- Order & Cart (4 tables)
- Billing & Invoice (4 tables)
- Support System (6 tables)
- Expense Management (3 tables)
- System Configuration (14 tables)

**Database Views:** 3 (invoice_view, order_view, product_service_view)

---

## Development Workflow

### Setting Up Development Environment
1. Install PHP 8.2+ and MySQL/MariaDB (see [PHP82_UPGRADE_AND_TROUBLESHOOTING.md](PHP82_UPGRADE_AND_TROUBLESHOOTING.md))
2. Configure database connection in `src/config/database.php`
3. Set base URL in `src/config/config.php` (see [Base URL Configuration](PHP82_UPGRADE_AND_TROUBLESHOOTING.md#base-url-configuration-fix))
4. Configure encryption key in `src/config/config.php`
5. Set up .htaccess for clean URLs
6. Import database schema
7. Create sessions directory: `mkdir -p src/sessions && chmod 777 src/sessions`

### Creating a New Feature
1. **Plan the feature** - Determine which portal (Admin/Client)
2. **Database changes** - Update tables if needed
3. **Create model** - Follow model patterns
4. **Create controller** - Use appropriate base controller
5. **Create views** - Follow view template patterns
6. **Test thoroughly** - Both functionality and security
7. **Document** - Update relevant documentation

### Coding Standards Checklist
- [ ] Follow naming conventions
- [ ] Use XSS cleaning on all inputs
- [ ] Implement form validation
- [ ] Use parameter binding for queries
- [ ] Hash passwords properly
- [ ] Check authentication in constructors
- [ ] Encode IDs in URLs
- [ ] Use flash messages for feedback
- [ ] Add breadcrumb navigation
- [ ] Include proper comments

---

## Support and Maintenance

### Backup Checklist
- [ ] Database backup (all tables)
- [ ] `/uploadedfiles/` directory
- [ ] `/src/config/` files
- [ ] `/resources/` assets

### Regular Maintenance
- Review application logs
- Database optimization
- Clean up old sessions
- Update dependencies
- Security audits

---

## Version Information

- **Documentation Version:** 1.1
- **Last Updated:** 2026-01-22
- **Project Status:** Active Development
- **Maintained By:** TongBari (https://tongbari.com/)

### Recent Updates (v1.1 - 2026-01-22)
- Added **Pattern 3: Server-Side DataTable with JOINs** (Package Pricing Example) to CODING_STANDARDS_AND_PATTERNS.md
- Updated Package Pricing feature documentation in PROJECT_DOCUMENTATION.md
- Complete CRUD implementation with server-side pagination example

---

## Quick Links

- **[PHP 8.2+ Upgrade & Troubleshooting Guide](PHP82_UPGRADE_AND_TROUBLESHOOTING.md)** ⭐ NEW
- **[WHMCS Feature Comparison & Roadmap](WHMCS_FEATURE_COMPARISON.md)**
- [Full Project Documentation](PROJECT_DOCUMENTATION.md)
- [Portal Structure Guide](PORTAL_STRUCTURE_GUIDE.md)
- [Coding Standards](CODING_STANDARDS_AND_PATTERNS.md)
- [CodeIgniter 3.x Documentation](https://codeigniter.com/userguide3/)
- [HMVC Documentation](https://github.com/jenssegers/codeigniter-hmvc)
- [WHMCS Official Site](https://www.whmcs.com/) (for feature reference)

---

## Need Help?

**For troubleshooting & PHP 8.2+ issues:** Check [PHP82_UPGRADE_AND_TROUBLESHOOTING.md](PHP82_UPGRADE_AND_TROUBLESHOOTING.md)
**For feature documentation:** Check [PROJECT_DOCUMENTATION.md](PROJECT_DOCUMENTATION.md)
**For architecture questions:** Check [PORTAL_STRUCTURE_GUIDE.md](PORTAL_STRUCTURE_GUIDE.md)
**For coding examples:** Check [CODING_STANDARDS_AND_PATTERNS.md](CODING_STANDARDS_AND_PATTERNS.md)

---

**Happy Coding!**
