# CI-CRM Project Documentation

## Table of Contents
1. [Project Overview](#project-overview)
2. [Dual Portal Architecture](#dual-portal-architecture)
3. [Custom Folder Structure](#custom-folder-structure)
4. [Client Portal Features](#client-portal-features)
5. [Admin Portal Features](#admin-portal-features)
6. [View Templates and UI Structure](#view-templates-and-ui-structure)
7. [Database Schema](#database-schema)
8. [Authentication System](#authentication-system)
9. [Technical Architecture](#technical-architecture)
10. [Configuration Guide](#configuration-guide)

## Related Documentation
- **[Coding Standards and Patterns](CODING_STANDARDS_AND_PATTERNS.md)** - Detailed coding guidelines, patterns, and examples
- **[Portal Structure Guide](PORTAL_STRUCTURE_GUIDE.md)** - Quick reference for dual portal architecture

---

## Project Overview

**Project Type:** Hosting/Service Provider CRM System (WHMCS Alternative)
**Framework:** CodeIgniter 3.x with HMVC (Modular Extensions)
**Architecture Pattern:** HMVC (Hierarchical Model-View-Controller)
**Purpose:** Complete business management system for hosting/service providers including order management, billing, invoicing, customer support, and domain management.

**Project Goal:** Develop a WHMCS-like (Web Host Manager Complete Solution) system - a comprehensive web hosting automation platform and client management system.

**Application Type:** Dual Portal System
- **Client Portal:** Customer-facing application for ordering services, viewing invoices, managing tickets
- **Admin Portal:** Backend administration panel for managing services, customers, billing, and system configuration

---

## Dual Portal Architecture

This application consists of **TWO separate portals** with distinct access levels and functionality:

### 1. Client Portal (Customer-Facing)

**Purpose:** Interface for customers to interact with services, place orders, manage accounts

**Architecture:**
- **Controllers:** HMVC Modules in `src/modules/`
- **Views:** Module-specific views in `src/modules/{module}/views/` and `src/views/templates/`
- **Base Controller:** `WHMAZ_Controller` (src/core/WHMAZ_Controller.php)
- **Authentication:** Customer login with email/password
- **Session Key:** `CUSTOMER`
- **URL Pattern:** `http://domain.com/module/controller/method`

**Main Modules:**
- `auth/` - Customer authentication (login, register, password reset)
- `cart/` - Shopping cart and checkout
- `order/` - Order placement and tracking
- `billing/` - Invoice viewing and payment
- `clientarea/` - Customer dashboard and account management
- `tickets/` - Support ticket submission and tracking
- `supports/` - Customer support interface

**Access:** Public access for registration; authenticated access for customer features

### 2. Admin Portal (Backend Administration)

**Purpose:** Complete system administration and business management interface

**Architecture:**
- **Controllers:** `src/controllers/whmazadmin/`
- **Views:** `src/views/whmazadmin/`
- **Base Controller:** `WHMAZADMIN_Controller` (src/core/WHMAZADMIN_Controller.php)
- **Authentication:** Admin login with username/email and password
- **Session Key:** `ADMIN`
- **URL Pattern:** `http://domain.com/whmazadmin/controller/method`

**Controller Files Location:**
```
src/controllers/whmazadmin/
├── Authenticate.php          # Admin login/logout
├── Dashboard.php             # Admin dashboard
├── General_setting.php       # Application settings (site info, SMTP, reCAPTCHA)
├── Company.php               # Customer company management
├── Currency.php              # Currency settings
├── Server.php                # Server management
├── Package.php               # Service packages
├── Service_category.php      # Service categories
├── Service_group.php         # Service groups
├── Service_module.php        # Service modules
├── Domain_pricing.php        # Domain pricing
├── Order.php                 # Order management
├── Invoice.php               # Invoice management
├── Expense.php               # Expense tracking
├── Expense_category.php      # Expense categories
├── Expense_vendor.php        # Vendor management
├── Ticket.php                # Ticket management
├── Ticket_department.php     # Support departments
├── Kb.php                    # Knowledge base articles
├── Kb_category.php           # KB categories
└── Announcement.php          # Announcements
```

**View Files Location:**
```
src/views/whmazadmin/
├── dashboard/
├── company/
├── currency/
├── server/
├── package/
├── service_category/
├── service_group/
├── domain_pricing/
├── order/
├── invoice/
├── expense/
├── ticket/
├── kb/
├── announcement/
└── includes/
    ├── header.php
    ├── sidebar.php
    └── footer.php
```

**Access:** Restricted to admin users only with role-based permissions

---

## Custom Folder Structure

### Key Modifications from Standard CI 3.x

This project uses a custom folder structure that significantly differs from standard CodeIgniter:

```
ci-crm/
├── index.php                 # Entry point (defines custom paths)
├── whmaz/                    # CUSTOM: Renamed 'system' folder
├── src/                      # CUSTOM: Renamed 'application' folder
│   ├── config/
│   ├── controllers/
│   │   └── whmazadmin/      # ADMIN PORTAL: Admin controllers
│   ├── core/                # Custom core extensions
│   │   ├── WHMAZ_Controller.php         # Client portal base controller
│   │   ├── WHMAZADMIN_Controller.php    # Admin portal base controller
│   │   ├── WHMAZ_Loader.php
│   │   └── WHMAZ_Router.php
│   ├── models/
│   ├── views/
│   │   ├── whmazadmin/      # ADMIN PORTAL: Admin views
│   │   ├── templates/       # CLIENT PORTAL: Frontend templates
│   │   └── email/           # Email templates
│   ├── modules/             # CLIENT PORTAL: HMVC modules
│   │   ├── auth/            # Customer authentication
│   │   │   ├── controllers/
│   │   │   ├── models/
│   │   │   └── views/
│   │   ├── cart/            # Shopping cart
│   │   ├── billing/         # Customer billing/invoices
│   │   ├── clientarea/      # Customer dashboard
│   │   ├── order/           # Customer orders
│   │   ├── supports/        # Customer support
│   │   ├── tickets/         # Support tickets
│   │   ├── report/          # Customer reports
│   │   └── cronjobs/        # Automated tasks
│   ├── libraries/
│   ├── helpers/
│   └── third_party/
│       └── MX/              # HMVC extension library
├── resources/               # Frontend assets (Angular, SCSS, JS)
└── uploadedfiles/           # User uploaded files storage
```

### Path Configuration

**index.php:**
```php
$system_path = 'whmaz';           // Instead of 'system'
$application_folder = 'src';       // Instead of 'application'
```

---

## Client Portal Features

The Client Portal is built using HMVC modules and provides customer-facing functionality.

### 1. Customer Authentication & Registration
**Module:** `src/modules/auth/`
**Controller:** `controllers/Auth.php`
**Views:** `views/` within auth module
**Model:** `Auth_model.php`

**Features:**
- Customer registration with email verification
- Secure login (email + password)
- Password reset/recovery
- Email verification
- Session management
- Multi-tenant company access

**Key URLs:**
- `/auth/login` - Customer login
- `/auth/register` - New customer registration
- `/auth/logout` - Logout
- `/auth/forgot_password` - Password recovery
- `/auth/verify_email` - Email verification

### 2. Shopping Cart
**Module:** `src/modules/cart/`
**Database Table:** `add_to_carts`

**Features:**
- Add services to cart
- Add domains to cart
- Cart item management (add/remove/update)
- Multi-item cart support
- Price calculation with currency conversion
- Session-based cart (guest users)
- User-based cart (logged-in customers)
- Domain search functionality
- Domain suggestions

**Key URLs:**
- `/cart/` - View cart
- `/cart/add_service` - Add service to cart
- `/cart/add_domain` - Add domain to cart
- `/domain-search/{tld}/{domain}` - Domain search
- `/domain-suggestion/{domain}` - Domain suggestions

### 3. Order Placement
**Module:** `src/modules/order/`
**Database Tables:** `orders`, `order_services`, `order_domains`

**Features:**
- Place orders from cart
- Order confirmation
- Order tracking
- Order history
- Service order details
- Domain order details
- Order status updates

### 4. Customer Billing & Invoices
**Module:** `src/modules/billing/`
**Database Tables:** `invoices`, `invoice_items`, `invoice_txn`

**Features:**
- View invoices
- Invoice details
- Download invoice PDF
- Payment processing
- Payment history
- Transaction records
- Multiple payment gateway support
- Outstanding balance tracking

### 5. Client Area Dashboard
**Module:** `src/modules/clientarea/`

**Features:**
- Customer dashboard overview
- Active services list
- Active domains list
- Recent invoices
- Recent tickets
- Account summary
- Profile management
- Password change
- Contact information update
- Quick actions

### 6. Support Ticket System (Client Side)
**Module:** `src/modules/tickets/` and `src/modules/supports/`

**Features:**
- Create support tickets
- View ticket list
- View ticket details
- Reply to tickets
- Upload attachments
- Ticket status tracking
- Priority levels
- Department selection
- Ticket history

### 7. Customer Reports
**Module:** `src/modules/report/`

**Features:**
- Invoice reports
- Service usage reports
- Payment history
- Order history
- Custom date range reports
- Export functionality

### 8. Multi-Currency Support (Client Side)
**Route:** `/change-currency/{currency_id}/{redirect_url}`

**Features:**
- Switch between available currencies
- Session-based currency selection
- Automatic price conversion
- Currency display across all pages

---

## Admin Portal Features

The Admin Portal provides complete system administration capabilities through traditional controllers.

**Base Path:** `src/controllers/whmazadmin/`
**Views Path:** `src/views/whmazadmin/`
**URL Prefix:** `/whmazadmin/`

### 1. Admin Authentication
**Controller:** `whmazadmin/Authenticate.php`
**Views:** `src/views/whmazadmin/login/`
**Model:** `Adminauth_model.php`

**Features:**
- Admin login (username or email + password)
- Role-based access control (RBAC)
- Department assignment for support staff
- Session tracking with IP logging
- Profile management with signature support
- Secure password verification
- Password hashing

**Key URLs:**
- `/whmazadmin/authenticate/login` - Admin login page
- `/whmazadmin/authenticate/logout` - Admin logout
- `/whmazadmin/authenticate/profile` - Admin profile

**Session Storage:**
- Session key: `ADMIN`
- Tracking table: `admin_logins`
- Role management: `admin_roles` table

### 2. Admin Dashboard
**Controller:** `whmazadmin/Dashboard.php`
**Views:** `src/views/whmazadmin/dashboard/`

**Features:**
- Summary statistics API
- Total customers count
- Active services count
- Total revenue
- Pending orders
- Open tickets count
- Recent activities
- Revenue charts
- Quick action buttons

**Key URLs:**
- `/whmazadmin/dashboard` - Main dashboard
- `/whmazadmin/dashboard/summary_api` - Statistics API endpoint

### 3. Customer & Company Management
**Controller:** `whmazadmin/Company.php`
**Views:** `src/views/whmazadmin/company/`
**Database Table:** `companies`

**Features:**
- List all customer companies
- Add new company/customer
- Edit company details
- View company profile
- Contact information management
- Billing address management
- Company status (Active/Inactive)
- Associated users listing
- Company-based filtering

**Key URLs:**
- `/whmazadmin/company` - Company listing
- `/whmazadmin/company/create` - Add new company
- `/whmazadmin/company/edit/{id}` - Edit company
- `/whmazadmin/company/view/{id}` - View company details

### 4. Product & Service Management

#### 4.1 Service Packages & Pricing
**Controller:** `whmazadmin/Package.php`
**Views:**
- `src/views/whmazadmin/package_list.php` - Package pricing list
- `src/views/whmazadmin/package_manage.php` - Add/edit pricing
**Database Tables:**
- `product_service_pricing` - Pricing per billing cycle (main)
- `product_services` - Service packages
- `currencies` - Currency definitions
- `billing_cycle` - Billing cycle definitions

**Features:**
- **Package Pricing Management** (Full CRUD)
  - Create/edit/delete pricing for service packages
  - Multi-currency support
  - Multiple billing cycles (monthly, yearly, etc.)
  - Server-side DataTables pagination for large datasets
  - Real-time search and filtering
  - Soft delete (status-based)

- **Service Packages** (Legacy features)
  - Server assignment
  - Service type categorization
  - Module integration for provisioning

**Implementation Details:**
- Uses server-side DataTables with JOINs across 4 tables
- Custom `buildDataTableQuery()` method for complex JOIN queries
- URL-safe ID encoding using `safe_encode()`
- Form validation for all required fields
- Dropdown population from related tables

**Related Tables:**
- `product_service_groups` - Service grouping
- `product_service_modules` - Provisioning modules
- `product_service_types` - Service type definitions

**Key URLs:**
- `/whmazadmin/package/index` - Package pricing listing with server-side pagination
- `/whmazadmin/package/manage` - Add new pricing
- `/whmazadmin/package/manage/{encoded_id}` - Edit existing pricing
- `/whmazadmin/package/delete_records/{encoded_id}` - Soft delete pricing
- `/whmazadmin/package/ssp_list_api` - Server-side DataTables API endpoint

**Code Example:**
See **Pattern 3: Server-Side DataTable with JOINs** in [CODING_STANDARDS_AND_PATTERNS.md](CODING_STANDARDS_AND_PATTERNS.md#pattern-3-server-side-datatable-with-joins-package-pricing-example) for complete implementation

#### 4.2 Server Management
**Controller:** `whmazadmin/Server.php`
**Views:** `src/views/whmazadmin/server/`
**Database Table:** `servers`

**Features:**
- Server configuration management
- Multiple server support
- Server capacity tracking
- Service allocation to servers

**Key URLs:**
- `/whmazadmin/server` - Server listing
- `/whmazadmin/server/create` - Add new server
- `/whmazadmin/server/edit/{id}` - Edit server

#### 4.3 Service Categories & Groups
**Controllers:**
- `whmazadmin/Service_category.php` - Categories
- `whmazadmin/Service_group.php` - Groups
**Views:**
- `src/views/whmazadmin/service_category/`
- `src/views/whmazadmin/service_group/`

**Features:**
- Hierarchical service organization
- Category-based service filtering
- Group-based pricing and features

#### 4.4 Service Modules
**Controller:** `whmazadmin/Service_module.php`
**Views:** `src/views/whmazadmin/service_module/`

**Features:**
- Provisioning module configuration
- Module activation/deactivation
- Module parameters setup
- API integration settings

---

### 5. Domain Management

#### 5.1 Domain Extensions & Pricing
**Controller:** `whmazadmin/Domain_pricing.php`
**Views:** `src/views/whmazadmin/domain_pricing/`
**Database Tables:**
- `dom_extensions` (.com, .net, etc.)
- `dom_pricing` (pricing by extension)
- `dom_registers` (registrar API configs)

**Features:**
- Multi-registrar support
- Per-extension pricing
- Registration/renewal/transfer pricing
- Domain availability checking
- Domain search functionality
- Domain suggestions

**Key URLs:**
- `/whmazadmin/domain_pricing` - Domain pricing list
- `/whmazadmin/domain_pricing/create` - Add new extension
- `/whmazadmin/domain_pricing/edit/{id}` - Edit pricing

---

### 6. Order Management

**Controller:** `whmazadmin/Order.php`
**Views:** `src/views/whmazadmin/order/`
**Database Tables:**
- `orders` - Master order records
- `order_services` - Ordered hosting services
- `order_domains` - Ordered domains

**Features:**
- Order creation and tracking
- Service order management
- Domain order management
- Order status workflow
- Order history
- Order-to-invoice conversion

**Order Statuses:**
- Pending
- Processing
- Active
- Cancelled
- Fraud

**Key URLs:**
- `/whmazadmin/order` - Order listing
- `/whmazadmin/order/view/{id}` - View order details
- `/whmazadmin/order/edit/{id}` - Edit order
- `/whmazadmin/order/change_status/{id}` - Update order status

---

### 7. Invoice & Billing Management

**Controller:** `whmazadmin/Invoice.php`
**Views:** `src/views/whmazadmin/invoice/`
**Database Tables:**
- `invoices` - Invoice master
- `invoice_items` - Line items
- `invoice_txn` - Payment transactions

**Features:**
- Automated invoice generation
- PDF invoice creation (DOMPDF)
- Multiple payment gateway support
- Payment tracking
- Refund management
- Tax calculation
- Multi-currency support
- Invoice numbering system

**Key Functions:**
- Invoice PDF generation
- Payment recording
- Invoice status management (Unpaid, Paid, Cancelled, Refunded)
- Due date tracking
- Late fee calculation

**Database View:**
- `invoice_view` - Comprehensive joined invoice data

**Key URLs:**
- `/whmazadmin/invoice` - Invoice listing
- `/whmazadmin/invoice/create` - Create new invoice
- `/whmazadmin/invoice/view/{id}` - View invoice
- `/whmazadmin/invoice/edit/{id}` - Edit invoice
- `/whmazadmin/invoice/pdf/{id}` - Generate PDF
- `/whmazadmin/invoice/record_payment/{id}` - Record payment

#### 7.1 Payment Gateway Configuration
**Database Table:** `payment_gateway`

**Features:**
- Multiple gateway support framework
- Gateway configuration storage
- Payment processing hooks
- Transaction logging

---

### 8. Currency Management

**Controller:** `whmazadmin/Currency.php`
**Views:** `src/views/whmazadmin/currency/`
**Database Table:** `currencies`

**Features:**
- Multi-currency support
- Exchange rate management
- Default currency configuration
- Session-based currency switching
- Automatic price conversion

**Key URLs:**
- `/whmazadmin/currency` - Currency listing
- `/whmazadmin/currency/create` - Add currency
- `/whmazadmin/currency/edit/{id}` - Edit currency
- `/whmazadmin/currency/set_default/{id}` - Set default currency

---

### 9. Support & Ticket Management

#### 9.1 Ticket Management
**Controller:** `whmazadmin/Ticket.php`
**Views:** `src/views/whmazadmin/ticket/`
**Database Tables:**
- `tickets` - Support tickets
- `ticket_replies` - Ticket responses
- `ticket_depts` - Support departments

**Features:**
- Department-based ticket routing
- Priority levels
- Status tracking (Open, In Progress, Closed)
- Customer and admin replies
- File attachment support
- Email notifications
- Department assignment to admin staff

**Key URLs:**
- `/whmazadmin/ticket` - Ticket listing
- `/whmazadmin/ticket/view/{id}` - View ticket details
- `/whmazadmin/ticket/reply/{id}` - Reply to ticket
- `/whmazadmin/ticket/change_status/{id}` - Change ticket status

#### 9.2 Support Departments
**Controller:** `whmazadmin/Ticket_department.php`
**Views:** `src/views/whmazadmin/ticket_department/`
**Features:**
- Create/manage support departments
- Assign staff to departments
- Department-specific email settings

**Key URLs:**
- `/whmazadmin/ticket_department` - Department listing
- `/whmazadmin/ticket_department/create` - Create department
- `/whmazadmin/ticket_department/edit/{id}` - Edit department

#### 9.3 Knowledge Base Management
**Controllers:**
- `whmazadmin/Kb.php` - Articles
- `whmazadmin/Kb_category.php` - Categories
**Views:**
- `src/views/whmazadmin/kb/`
- `src/views/whmazadmin/kb_category/`

**Database Tables:**
- `kbs` - Knowledge base articles
- `kb_cats` - KB categories
- `kb_cat_mapping` - Category relationships

**Features:**
- Article creation and management
- Category organization
- Search functionality
- Public/private article visibility
- Popular articles tracking

**Key URLs:**
- `/whmazadmin/kb` - KB article listing
- `/whmazadmin/kb/create` - Create article
- `/whmazadmin/kb/edit/{id}` - Edit article
- `/whmazadmin/kb_category` - Category management

---

### 10. Expense Management

#### 10.1 Expense Tracking
**Controller:** `whmazadmin/Expense.php`
**Views:** `src/views/whmazadmin/expense/`
**Database Table:** `expenses`

**Features:**
- Expense recording
- Date tracking
- Category assignment
- Vendor assignment
- Amount tracking
- Attachment support

**Key URLs:**
- `/whmazadmin/expense` - Expense listing
- `/whmazadmin/expense/create` - Add expense
- `/whmazadmin/expense/edit/{id}` - Edit expense

#### 10.2 Expense Categories
**Controller:** `whmazadmin/Expense_category.php`
**Views:** `src/views/whmazadmin/expense_category/`
**Database Table:** `expense_types`

**Features:**
- Category creation
- Category-based reporting
- Expense categorization

**Key URLs:**
- `/whmazadmin/expense_category` - Category listing
- `/whmazadmin/expense_category/create` - Add category

#### 10.3 Vendor Management
**Controller:** `whmazadmin/Expense_vendor.php`
**Views:** `src/views/whmazadmin/expense_vendor/`
**Database Table:** `expense_vendors`

**Features:**
- Vendor profiles
- Contact information
- Vendor-expense relationships

**Key URLs:**
- `/whmazadmin/expense_vendor` - Vendor listing
- `/whmazadmin/expense_vendor/create` - Add vendor

---

### 11. Communication & Announcements

**Controller:** `whmazadmin/Announcement.php`
**Views:** `src/views/whmazadmin/announcement/`
**Database Table:** `announcements`

**Features:**
- System-wide announcements
- Customer-facing announcements
- Publish date control
- Visibility management

**Key URLs:**
- `/whmazadmin/announcement` - Announcement listing
- `/whmazadmin/announcement/create` - Create announcement
- `/whmazadmin/announcement/edit/{id}` - Edit announcement

#### 11.1 Alert System
**Database Tables:**
- `alerts` - Alert definitions
- `alert_recipients` - Alert recipients

**Features:**
- System notifications
- User-specific alerts
- Alert broadcasting
- Read/unread tracking

---

### 12. Admin User & Role Management

**Features:**
- Create/edit admin users
- Role assignment
- Permission management
- Department assignment
- Profile management
- Access control

**Database Tables:**
- `admin_users` - Admin accounts
- `admin_roles` - Role definitions
- `admin_logins` - Login tracking

---

### 13. Reporting & Analytics

**Controller:** Admin-side reporting
**Module:** `src/modules/report/` (may be used by admin)

**Features:**
- Sales reports
- Revenue analytics
- Service reports
- Customer reports
- Order statistics
- Invoice statistics
- Custom date ranges
- Export functionality (PDF/Excel)

---

### 14. System Configuration

**Features:**
- Application settings (`app_settings` table)
- Email configuration (SMTP settings)
- Payment gateway configuration
- Tax settings
- Number generation formats
- System preferences

---

### 15. Automated Tasks Management

**Module:** `src/modules/cronjobs/`
**Database Tables:**
- `cron_jobs` - Job definitions
- `pending_executions` - Pending operations queue

**Features:**
- Scheduled task execution
- Invoice generation automation
- Service renewal processing
- Payment reminders
- Domain renewal reminders
- Execution logging
- Task scheduling configuration

---

## View Templates and UI Structure

### Frontend Technology Stack

**CSS Framework:** Bootstrap 5 with DashForge Theme
**JavaScript Framework:** AngularJS 1.x for dynamic content
**Icons:** Feather Icons, FontAwesome, Ionicons, Typicons, Remixicon
**Data Tables:** DataTables with responsive extension
**Dialogs:** SweetAlert2
**Enhanced Selects:** Select2
**Editors:** Quill (rich text editor)

### Admin Portal View Structure

#### Component Hierarchy
```
View Page
├── whmazadmin/include/header.php (wrapper)
│   ├── header_script.php (CSS, meta tags, fonts)
│   └── header_menus.php (navigation sidebar)
├── Page Content
│   ├── Breadcrumb navigation
│   ├── Flash messages
│   └── Main content area
├── whmazadmin/include/footer_script.php (JavaScript libraries)
├── Page-specific JavaScript
└── whmazadmin/include/footer.php (footer + theme customizer)
```

#### Standard Admin View Pattern
Every admin view follows this structure:
1. Load header (includes CSS and navigation)
2. Content wrapper with container
3. Page title with action buttons
4. Breadcrumb navigation
5. Flash message area
6. Main content (tables, forms, etc.)
7. Load footer scripts (jQuery, Bootstrap, DataTables, AngularJS)
8. Page-specific JavaScript
9. Load footer (footer text and theme customizer)

#### Admin View File Organization
```
src/views/whmazadmin/
├── include/
│   ├── header.php              # Main wrapper
│   ├── header_script.php       # CSS includes
│   ├── header_menus.php        # Navigation sidebar
│   ├── footer.php              # Footer with customizer
│   └── footer_script.php       # JavaScript libraries
├── admin_login.php
├── dashboard_index.php
├── company_list.php            # List views
├── company_manage.php          # Add/Edit forms
├── invoice_list.php
├── invoice_view.php
├── invoice_pdf_html.php        # PDF templates
└── [other entity views...]
```

### Client Portal View Structure

#### Component Hierarchy
```
View Page
├── templates/customer/header.php
│   ├── Meta tags, CSS includes
│   └── Navigation menu
├── Page Content
│   ├── Breadcrumb navigation
│   ├── Page title with quick actions
│   ├── Flash messages
│   └── Main content area
├── templates/customer/footer_script.php
├── Page-specific JavaScript
└── templates/customer/footer.php
```

#### Client View File Organization
```
src/modules/*/views/
├── auth/views/
│   ├── auth_login.php
│   ├── auth_register.php
│   └── auth_forgetpass.php
├── billing/views/
│   ├── billing_invoices.php
│   ├── billing_viewinvoice.php
│   └── billing_invoice_pdf_html.php
├── cart/views/
│   ├── cart_services.php
│   ├── view_card.php
│   └── view_checkout.php
├── clientarea/views/
│   ├── clientarea_index.php
│   ├── clientarea_services.php
│   └── clientarea_domains.php
└── supports/views/
    ├── support_kb_list.php
    └── support_announcement_list.php
```

### Common UI Patterns

#### Page Title with Action Button
```php
<h3 class="d-flex justify-content-between">
    <span>Page Title</span>
    <a href="<?=base_url()?>whmazadmin/entity/action" class="btn btn-sm btn-secondary">
        <i class="fa fa-plus-square"></i>&nbsp;Add New
    </a>
</h3>
```

#### Breadcrumb Navigation
```php
<nav aria-label="breadcrumb">
    <ol class="breadcrumb breadcrumb-style1 mg-b-0">
        <li class="breadcrumb-item">
            <a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">Current Page</a>
        </li>
    </ol>
</nav>
```

#### Flash Messages
```php
<?php if ($this->session->flashdata('alert')) { ?>
    <?= $this->session->flashdata('alert') ?>
<?php } ?>
```

#### Tabbed Interface
```php
<ul class="nav nav-tabs" id="pageTab" role="pageTablist">
    <li class="nav-item">
        <a class="nav-link active" id="info-tab" data-bs-toggle="tab" href="#general-info">
            <i class="fa fa-info-circle"></i>&nbsp;General info
        </a>
    </li>
</ul>
<div class="tab-content bd bd-gray-300 bd-t-0 pd-20">
    <div class="tab-pane fade show active" id="general-info">
        <!-- Tab content -->
    </div>
</div>
```

### Bootstrap 5 Utility Classes Used

**Spacing:**
- `mg-*` - Margin (custom DashForge classes)
- `pd-*` - Padding (custom DashForge classes)
- `mt-*`, `mb-*`, `ml-*`, `mr-*` - Bootstrap margins
- `pt-*`, `pb-*`, `pl-*`, `pr-*` - Bootstrap paddings

**Text:**
- `tx-*` - Text utilities (custom DashForge classes)
- `tx-color-01`, `tx-color-02`, `tx-color-03` - Text colors
- `tx-uppercase`, `tx-semibold`, `tx-normal` - Text styles

**Width/Height:**
- `wd-*` - Width utilities (e.g., `wd-20p` = 20%)
- `ht-*` - Height utilities

**Borders:**
- `bd-*` - Border utilities (custom DashForge classes)

**Backgrounds:**
- `bg-primary`, `bg-success`, `bg-danger`, `bg-warning`, `bg-info`
- `bg-*-light` - Light background variants

### DataTable UI Patterns

#### Client-Side DataTable
- Used for smaller datasets (< 1000 rows)
- All data loaded at once
- Fast client-side filtering and sorting

#### Server-Side DataTable
- Used for large datasets
- AJAX-based data loading
- Server-side filtering, sorting, pagination
- Custom render functions for actions and status badges

### Form UI Components

**Standard Form Controls:**
- Text inputs: `<input type="text" class="form-control">`
- Email inputs: `<input type="email" class="form-control">`
- Textareas: `<textarea class="form-control">`
- Select dropdowns: CodeIgniter's `form_dropdown()` with Select2
- File uploads: `<input type="file" class="form-control">`
- Checkboxes and radios: Bootstrap custom controls

**Enhanced Components:**
- **Select2:** Enhanced dropdowns with search
- **Quill Editor:** Rich text editing for descriptions
- **DatePicker:** Date selection (via Bootstrap datepicker)

### AngularJS Dynamic Content

**Used for:**
- Dashboard statistics with real-time updates
- Dynamic lists (orders, tickets, invoices)
- Live data refresh without page reload
- Interactive widgets

**Pattern:**
```html
<div ng-app="AppName">
    <div ng-controller="CtrlName">
        <div ng-init="loadData()">
            <span>{{data.field}}</span>
        </div>
    </div>
</div>
```

### Icon Usage

**Feather Icons (Primary):**
```html
<i data-feather="home"></i>
<i data-feather="users"></i>
<i data-feather="settings"></i>
```

**FontAwesome (Secondary):**
```html
<i class="fa fa-plus-square"></i>
<i class="fa fa-check-circle"></i>
<i class="fa fa-trash"></i>
```

### Color Scheme

**Status Colors:**
- **Success:** Green (`bg-success`, `badge bg-success`)
- **Danger:** Red (`bg-danger`, `badge bg-danger`)
- **Warning:** Yellow (`bg-warning`, `badge bg-warning`)
- **Info:** Blue (`bg-info`, `badge bg-info`)
- **Primary:** Brand color (`bg-primary`, `btn-primary`)
- **Secondary:** Gray (`bg-secondary`, `btn-secondary`)

**Usage:**
- Success: Paid invoices, active services, completed actions
- Danger: Due invoices, errors, delete actions
- Warning: Partial payments, warnings
- Info: Information messages, in-progress status
- Primary: Primary actions, important elements
- Secondary: Secondary actions, neutral elements

### Responsive Design

**Breakpoints:**
- `col-sm-*`: Small devices (≥576px)
- `col-md-*`: Medium devices (≥768px)
- `col-lg-*`: Large devices (≥992px)
- `col-xl-*`: Extra large devices (≥1200px)

**Mobile Considerations:**
- Responsive navigation menu
- DataTables responsive extension
- Mobile-friendly forms
- Touch-friendly buttons and dropdowns

### Theme Customization

**DashForge Theme Modes:**
- Light mode (default)
- Dark mode
- Navigation styles (Side, Top, Fixed)
- Sidebar modes (Minimized, Expanded)

**Customizer Location:**
Available in footer for both admin and client portals

---

## Database Schema

### Total Tables: 48

### User & Authentication Tables
| Table | Purpose |
|-------|---------|
| `users` | Customer user accounts |
| `user_logins` | Customer login session tracking |
| `admin_users` | Admin staff accounts |
| `admin_logins` | Admin login session tracking |
| `admin_roles` | Role-based access control |
| `companies` | Customer companies/organizations |
| `password_resets` | Password reset tokens |

### Product & Service Tables
| Table | Purpose |
|-------|---------|
| `product_services` | Hosting/service packages |
| `product_service_groups` | Service grouping |
| `product_service_modules` | Provisioning modules |
| `product_service_types` | Service type definitions |
| `product_service_pricing` | Pricing per billing cycle |
| `servers` | Server information |
| `billing_cycle` | Billing cycle definitions |

### Domain Management Tables
| Table | Purpose |
|-------|---------|
| `dom_extensions` | Domain extensions (.com, .net, etc.) |
| `dom_pricing` | Domain pricing by extension |
| `dom_registers` | Domain registrar API configs |

### Order & Cart Tables
| Table | Purpose |
|-------|---------|
| `orders` | Master order records |
| `order_services` | Ordered hosting services |
| `order_domains` | Ordered domains |
| `add_to_carts` | Shopping cart items |

#### `order_services` Table Structure
| Column | Type | Description |
|--------|------|-------------|
| `id` | BIGINT(20) | Primary key, auto-increment |
| `order_id` | BIGINT(20) | Foreign key to `orders` table |
| `company_id` | BIGINT(20) | Foreign key to `companies` table |
| `product_service_id` | INT(11) | Foreign key to `product_services` table |
| `product_service_pricing_id` | INT(11) | Foreign key to `product_service_pricing` table |
| `product_service_type_key` | VARCHAR(150) | Service type identifier key (e.g., 'SHARED_HOSTING', 'RESELLER_HOSTING') |
| `cp_username` | VARCHAR(50) | cPanel username for hosting accounts (used for SSO) |
| `billing_cycle_id` | INT(11) | Foreign key to `billing_cycle` table |
| `description` | TEXT | Service description |
| `first_pay_amount` | DOUBLE | Initial payment amount |
| `recurring_amount` | DOUBLE | Recurring payment amount |
| `hosting_domain` | VARCHAR(200) | Associated domain name |
| `reg_date` | DATE | Service registration date |
| `exp_date` | DATE | Service expiry date |
| `next_due_date` | DATE | Next billing due date |
| `is_synced` | TINYINT(4) | Sync status with provisioning system (1=synced) |
| `status` | TINYINT(4) | 0=pending, 1=active, 2=expired, 3=suspended, 4=terminated |
| `remarks` | VARCHAR(255) | Additional remarks |
| `inserted_on` | DATETIME | Record creation timestamp |
| `inserted_by` | INT(11) | User who created the record |
| `updated_on` | TIMESTAMP | Last update timestamp |
| `updated_by` | INT(11) | User who last updated the record |
| `deleted_on` | DATETIME | Soft delete timestamp |
| `deleted_by` | INT(11) | User who deleted the record |

### Billing & Invoice Tables
| Table | Purpose |
|-------|---------|
| `invoices` | Invoice master records |
| `invoice_items` | Invoice line items |
| `invoice_txn` | Payment transactions |
| `payment_gateway` | Payment processor configs |

### Support Tables
| Table | Purpose |
|-------|---------|
| `tickets` | Support tickets |
| `ticket_depts` | Support departments |
| `ticket_replies` | Ticket responses |
| `kbs` | Knowledge base articles |
| `kb_cats` | KB categories |
| `kb_cat_mapping` | KB category relationships |

### Expense Management Tables
| Table | Purpose |
|-------|---------|
| `expenses` | Expense records |
| `expense_types` | Expense categories |
| `expense_vendors` | Expense vendors |

### System Tables
| Table | Purpose |
|-------|---------|
| `currencies` | Multi-currency support |
| `countries` | Country data |
| `announcements` | System announcements |
| `alerts` | Alert/notification system |
| `alert_recipients` | Alert recipients |
| `app_settings` | Application configuration |
| `gen_numbers` | Auto-incrementing number generator |
| `cron_jobs` | Scheduled job definitions |
| `pending_executions` | Pending operations queue |

### Database Views
| View | Purpose |
|------|---------|
| `invoice_view` | Comprehensive joined invoice data |
| `order_view` | Comprehensive joined order data |
| `product_service_view` | Comprehensive joined product/service data |

---

## Authentication System

### Dual Authentication Architecture

#### Customer Authentication Flow
1. **Login Process:**
   - User submits email + password
   - `Auth_model::validate_login()` checks credentials
   - Password verification using `password_verify()`
   - Session created with key `CUSTOMER`
   - Login tracked in `user_logins` table

2. **Base Controller:** `WHMAZ_Controller`
   - Extends `MX_Controller` (HMVC)
   - Auto-loads `Auth_model` and `Cart_model`
   - Validates customer session on each request
   - Initializes default currency

3. **Helper Functions (whmaz_helper.php):**
   - `is_customer_loggedin()` - Check login status
   - `get_customer_id()` - Get current customer ID
   - `get_customer_data()` - Get customer session data
   - `get_customer_company_id()` - Get company ID

#### Admin Authentication Flow
1. **Login Process:**
   - Admin submits username/email + password
   - `Adminauth_model::validate_admin_login()` checks credentials
   - Password verification using `password_verify()`
   - Session created with key `ADMIN`
   - Login tracked in `admin_logins` table
   - Role permissions loaded

2. **Base Controller:** `WHMAZADMIN_Controller`
   - Extends `MX_Controller` (HMVC)
   - Auto-loads `Adminauth_model`
   - Validates admin session on each request
   - Supports REST API endpoints

3. **Helper Functions (whmaz_helper.php):**
   - `is_admin_loggedin()` - Check admin login status
   - `get_admin_id()` - Get current admin ID
   - `get_admin_data()` - Get admin session data

---

## Technical Architecture

### HMVC Implementation

**Third-party Library:** Wiredesignz Modular Extensions (MX)
**Location:** `src/third_party/MX/`

**Custom Core Extensions:**
- `WHMAZ_Loader` extends `MX_Loader`
- `WHMAZ_Router` extends `MX_Router`
- `WHMAZ_Controller` - Customer base controller
- `WHMAZADMIN_Controller` - Admin base controller

**Module Structure:**
```
src/modules/{module_name}/
├── controllers/
├── models/
└── views/
```

**Benefits:**
- Modular, self-contained features
- Code reusability
- Independent development
- Clear separation of concerns

### Custom Libraries

#### AppService (`src/libraries/AppService.php`)
Base service class for business logic

#### PDF Generation (`src/libraries/Pdf.php`)
Wrapper for DOMPDF library for invoice PDF generation

#### Server-Side Processing (`src/libraries/Ssp.php`)
DataTables server-side processing for large datasets

### Custom Helpers

#### whmaz_helper.php (50+ functions)

**Alert Functions:**
- `success_alert($message)` - Success notification
- `error_alert($message)` - Error notification
- `primary_alert($message)` - Info notification

**Security Functions:**
- `xss_cleaner($data)` - XSS protection
- `xssCleaner($data)` - Alternative XSS cleaner

**Session Helpers:**
- `is_customer_loggedin()` - Customer auth check
- `is_admin_loggedin()` - Admin auth check
- `get_customer_id()` - Get customer ID
- `get_admin_id()` - Get admin ID

**Currency Helpers:**
- `get_default_currency()` - Get default currency
- `get_session_currency()` - Get active currency
- `convert_currency($amount, $from, $to)` - Currency conversion

**Date/Time Formatters:**
- `format_date($date)` - Format date
- `format_datetime($datetime)` - Format datetime
- `time_ago($timestamp)` - Relative time

**Status Badge Generators:**
- `invoice_status_badge($status)` - Invoice status HTML
- `order_status_badge($status)` - Order status HTML
- `ticket_status_badge($status)` - Ticket status HTML

**Utility Functions:**
- `generate_uuid()` - Generate unique identifier
- `encode_id($id)` - Encode ID for URLs
- `decode_id($encoded)` - Decode ID from URLs

---

## Configuration Guide

### Base Configuration

**File:** `src/config/config.php`

**Dynamic Base URL:**
```php
$base_url = ((isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http') . '://' .
             (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost'));
$project_folder = substr($_SERVER['REQUEST_URI'], 0,
                         strpos($_SERVER['REQUEST_URI'], '/index.php'));
$config['base_url'] = $base_url . $project_folder . '/';
```

**Key Settings:**
```php
$config['index_page'] = '';              // Clean URLs via .htaccess
$config['uri_protocol'] = 'REQUEST_URI';
$config['encryption_key'] = '';          // Set your encryption key
$config['sess_save_path'] = '';          // Session save path
$config['sess_driver'] = 'database';     // Database sessions
```

### Database Configuration

**File:** `src/config/database.php`

Configure your database connection:
```php
$db['default'] = array(
    'dsn'      => '',
    'hostname' => 'localhost',
    'username' => 'your_username',
    'password' => 'your_password',
    'database' => 'your_database',
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8',
    'dbcollat' => 'utf8_general_ci',
);
```

### Routes Configuration

**File:** `src/config/routes.php`

**Default Routes:**
```php
$route['default_controller'] = 'auth';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
```

**Custom Routes:**
```php
$route['change-currency/(:any)/(:any)'] = 'auth/change_currency/$1/$2';
$route['domain-search/(:any)/(:any)'] = 'cart/domain_search/$1/$2';
$route['domain-suggestion/(:any)'] = 'cart/get_domain_suggestions/$1';
```

### Autoload Configuration

**File:** `src/config/autoload.php`

**Libraries:**
```php
$autoload['libraries'] = array(
    'database',
    'session',
    'upload',
    'encryption',
    'form_validation',
    'pagination',
    'email',
    'AppService'
);
```

**Helpers:**
```php
$autoload['helper'] = array('url', 'file', 'whmaz', 'ssp');
```

### Email Configuration

**Stored in:** `app_settings` database table

**SMTP Settings:**
- SMTP host
- SMTP port
- SMTP username
- SMTP password/auth key

### Google reCAPTCHA Configuration

**Stored in:** `app_settings` database table

**Settings:**
- `captcha_site_key` - Public site key for frontend widget
- `captcha_secret_key` - Private secret key for server-side validation

**Used in:**
- Customer registration page (`auth/register`)

**Admin Configuration:** `whmazadmin/general_setting/manage`

---

## Frontend Assets

**Location:** `/resources/`

**Technologies:**
- Angular framework
- SCSS stylesheets
- Bootstrap framework
- jQuery library
- DataTables plugin
- Modern JavaScript libraries

---

## File Upload Configuration

**Upload Directory:** `/uploadedfiles/`

**Configured via:** `src/config/upload.php` or in controllers

**Typical Use Cases:**
- Ticket attachments
- Invoice documents
- Company logos
- Knowledge base images
- Expense receipts

---

## Development Notes

### Custom System Folder Name
The CodeIgniter system folder has been renamed from `system` to `whmaz`. This is configured in `index.php`:
```php
$system_path = 'whmaz';
```

### Custom Application Folder Name
The application folder has been renamed from `application` to `src`. This is configured in `index.php`:
```php
$application_folder = 'src';
```

### HMVC Module Loading
Modules are loaded automatically by the MX library. To call a module controller:
```php
$this->load->module('auth');
$this->auth->method_name();
```

### Number Generation System
**Table:** `gen_numbers`

Used for generating sequential numbers for:
- Invoice numbers
- Order numbers
- Ticket numbers

### Password Security
All passwords are hashed using PHP's `password_hash()` with `PASSWORD_DEFAULT` algorithm and verified with `password_verify()`.

---

## API Endpoints

### Admin REST API
The `WHMAZADMIN_Controller` supports REST API endpoints for admin operations.

**Example Endpoint Pattern:**
```
/whmazadmin/dashboard/summary_api
```

**Authentication:** Session-based (ADMIN session key)

---

## Future Development Considerations

1. **Module Expansion:** The HMVC structure makes it easy to add new modules
2. **Payment Gateways:** Framework in place for additional gateway integrations
3. **Domain Registrars:** Extensible registrar API system
4. **Reporting:** Dedicated report module for analytics expansion
5. **API Development:** REST API foundation in admin controller
6. **Automation:** Cronjobs module for scheduled tasks

---

## Maintenance & Backup

### Database Backup
Regular backups of the database are recommended, especially:
- `invoices`, `invoice_items`, `invoice_txn` tables
- `orders`, `order_services`, `order_domains` tables
- `users`, `companies` tables
- `app_settings` table

### File Backup
Important directories to backup:
- `/uploadedfiles/` - User uploads
- `/src/config/` - Configuration files
- `/resources/` - Frontend assets

---

## Security Considerations

1. **XSS Protection:** Use `xss_cleaner()` helper for all user input
2. **SQL Injection:** Use CodeIgniter Query Builder or prepared statements
3. **Password Security:** All passwords hashed with `password_hash()`
4. **Session Security:** Database-backed sessions with IP tracking
5. **File Upload Validation:** Validate file types and sizes
6. **CSRF Protection:** Enable CSRF protection in config
7. **Encryption:** Sensitive data encrypted using CI encryption library

---

## Support & Documentation

For CodeIgniter 3.x documentation: https://codeigniter.com/userguide3/

For HMVC documentation: https://github.com/jenssegers/codeigniter-hmvc

---

**Documentation Version:** 1.0
**Last Updated:** 2026-01-13
**Project Status:** Active Development
