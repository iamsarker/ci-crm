# CI-CRM Development Documentation Index

Welcome to the CI-CRM project development documentation. This index will guide you to the right documentation based on your needs.

> **Note:** This folder contains development/internal documentation. For CodeCanyon buyer documentation (README, Installation Guide, User Guide, etc.), see the `codecanyon/` folder.

---

## Quick Start

**New to the project?** Start here:
1. Read [Project Overview](#project-overview) below
2. Review [Portal Structure Guide](PORTAL_STRUCTURE_GUIDE.md)
3. Check [Coding Standards](CODING_STANDARDS_AND_PATTERNS.md)

---

## Folder Structure

```
ci-crm/
├── docs/                          # Development Documentation (this folder)
│   ├── DOCUMENTATION_INDEX.md     # This file
│   ├── PROJECT_DOCUMENTATION.md   # Complete technical reference
│   ├── CODING_STANDARDS_AND_PATTERNS.md  # Coding guidelines
│   ├── PORTAL_STRUCTURE_GUIDE.md  # Dual portal architecture
│   ├── SECURITY_IMPROVEMENTS.md   # Security fixes documentation
│   ├── SECURITY_FIXES.md          # Security fix details
│   ├── SECURITY_AUDIT_REPORT.md   # Security audit results
│   ├── SECURITY_HEADERS_SETUP.md  # Security headers configuration
│   ├── PHP82_UPGRADE_AND_TROUBLESHOOTING.md  # PHP 8.2+ guide
│   └── WHMCS_FEATURE_COMPARISON.md  # Feature comparison
│
└── codecanyon/                    # CodeCanyon Buyer Documentation
    ├── README.md                  # Product overview
    ├── INSTALLATION.md            # Installation guide
    ├── USER_GUIDE.md              # End-user guide
    ├── CHANGELOG.md               # Version history
    ├── CREDITS.md                 # Third-party credits
    ├── CODECANYON_COMPLIANCE_REPORT.md  # Compliance report
    └── contributing.md            # Contribution guidelines
```

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

## Documentation Files

### 1. [PHP82_UPGRADE_AND_TROUBLESHOOTING.md](PHP82_UPGRADE_AND_TROUBLESHOOTING.md)
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

---

### 6. Security Documentation

#### [SECURITY_IMPROVEMENTS.md](SECURITY_IMPROVEMENTS.md)
**Purpose:** Comprehensive list of all security improvements made
**Contents:** SQL injection fixes, XSS fixes, CSRF protection, reCAPTCHA implementation

#### [SECURITY_AUDIT_REPORT.md](SECURITY_AUDIT_REPORT.md)
**Purpose:** Security audit results and findings

#### [SECURITY_HEADERS_SETUP.md](SECURITY_HEADERS_SETUP.md)
**Purpose:** Security headers configuration (CSP, X-Frame-Options, etc.)

#### [SECURITY_FIXES.md](SECURITY_FIXES.md)
**Purpose:** Detailed security fix documentation

---

## Common Tasks & Where to Find Help

### Adding a New Admin Feature
1. Review **Admin Portal Features** in [PROJECT_DOCUMENTATION.md](PROJECT_DOCUMENTATION.md#admin-portal-features)
2. Follow **Controller Patterns** in [CODING_STANDARDS_AND_PATTERNS.md](CODING_STANDARDS_AND_PATTERNS.md#controller-patterns)
3. Use **View Template Patterns** in [CODING_STANDARDS_AND_PATTERNS.md](CODING_STANDARDS_AND_PATTERNS.md#view-templates-structure)
4. Check **Admin Portal Structure** in [PORTAL_STRUCTURE_GUIDE.md](PORTAL_STRUCTURE_GUIDE.md#2-admin-portal-backend-administration)

### Adding a New Client Feature
1. Review **Client Portal Features** in [PROJECT_DOCUMENTATION.md](PROJECT_DOCUMENTATION.md#client-portal-features)
2. Follow **HMVC Module Pattern** in [CODING_STANDARDS_AND_PATTERNS.md](CODING_STANDARDS_AND_PATTERNS.md#client-controller-pattern-hmvc-module)
3. Check **Client Portal Structure** in [PORTAL_STRUCTURE_GUIDE.md](PORTAL_STRUCTURE_GUIDE.md#1-client-portal-customer-facing)

### Working with Database
1. Review **Database Schema** in [PROJECT_DOCUMENTATION.md](PROJECT_DOCUMENTATION.md#database-schema)
2. Follow **Database Query Patterns** in [CODING_STANDARDS_AND_PATTERNS.md](CODING_STANDARDS_AND_PATTERNS.md#database-query-patterns)
3. Use **Model Patterns** in [CODING_STANDARDS_AND_PATTERNS.md](CODING_STANDARDS_AND_PATTERNS.md#model-patterns)

### Security Implementation
1. Review **Security Best Practices** in [CODING_STANDARDS_AND_PATTERNS.md](CODING_STANDARDS_AND_PATTERNS.md#security-best-practices)
2. Check **Security Improvements** in [SECURITY_IMPROVEMENTS.md](SECURITY_IMPROVEMENTS.md)

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

## Version Information

- **Documentation Version:** 1.2
- **Last Updated:** 2026-01-25
- **Project Status:** Active Development
- **Maintained By:** TongBari (https://tongbari.com/)

### Recent Updates (v1.2 - 2026-01-25)
- Reorganized documentation into `docs/` and `codecanyon/` folders
- Added General Settings CRUD with reCAPTCHA configuration
- Fixed SQL injection in Billing_model.php and Clientarea_model.php
- Added country dropdown to registration form
- Updated form-select class across all select elements

---

## Quick Links

**Development Documentation (this folder):**
- [PHP 8.2+ Upgrade & Troubleshooting Guide](PHP82_UPGRADE_AND_TROUBLESHOOTING.md)
- [WHMCS Feature Comparison & Roadmap](WHMCS_FEATURE_COMPARISON.md)
- [Full Project Documentation](PROJECT_DOCUMENTATION.md)
- [Portal Structure Guide](PORTAL_STRUCTURE_GUIDE.md)
- [Coding Standards](CODING_STANDARDS_AND_PATTERNS.md)
- [Security Improvements](SECURITY_IMPROVEMENTS.md)

**CodeCanyon Documentation (../codecanyon/):**
- [README](../codecanyon/README.md)
- [Installation Guide](../codecanyon/INSTALLATION.md)
- [User Guide](../codecanyon/USER_GUIDE.md)
- [Changelog](../codecanyon/CHANGELOG.md)

**External Resources:**
- [CodeIgniter 3.x Documentation](https://codeigniter.com/userguide3/)
- [HMVC Documentation](https://github.com/jenssegers/codeigniter-hmvc)

---

**Happy Coding!**
