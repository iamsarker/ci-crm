# CI-CRM Portal Structure Quick Reference

## Portal Overview

This application has **TWO distinct portals**:

---

## 1. CLIENT PORTAL (Customer-Facing)

### Architecture
- **Type:** HMVC Modules
- **Base Controller:** `WHMAZ_Controller`
- **Location:** `src/modules/`
- **Views:** Module-specific (`src/modules/{module}/views/`) and `src/views/templates/`
- **Authentication:** Customer (email + password)
- **Session Key:** `CUSTOMER`
- **URL Pattern:** `domain.com/module/method`

### Modules Structure
```
src/modules/
├── auth/               # Customer login, registration, password reset
│   ├── controllers/
│   ├── models/
│   └── views/
├── cart/               # Shopping cart, domain search
│   ├── controllers/
│   ├── models/
│   └── views/
├── order/              # Order placement and tracking
│   ├── controllers/
│   ├── models/
│   └── views/
├── billing/            # Invoice viewing, payments
│   ├── controllers/
│   ├── models/
│   └── views/
├── clientarea/         # Customer dashboard
│   ├── controllers/
│   ├── models/
│   └── views/
├── tickets/            # Support ticket system
│   ├── controllers/
│   ├── models/
│   └── views/
├── supports/           # Customer support interface
│   ├── controllers/
│   ├── models/
│   └── views/
├── report/             # Customer reports
│   ├── controllers/
│   ├── models/
│   └── views/
└── cronjobs/           # Automated tasks
    ├── controllers/
    ├── models/
    └── views/
```

### Client Portal Features
| Module | Purpose | Key URLs |
|--------|---------|----------|
| **auth** | Authentication | `/auth/login`, `/auth/register` |
| **cart** | Shopping Cart | `/cart/`, `/cart/add_service` |
| **order** | Orders | `/order/place`, `/order/history` |
| **billing** | Invoices & Payments | `/billing/invoices`, `/billing/pay` |
| **clientarea** | Dashboard | `/clientarea/dashboard`, `/clientarea/profile` |
| **tickets** | Support Tickets | `/tickets/create`, `/tickets/view` |
| **supports** | Support Center | `/supports/` |
| **report** | Reports | `/report/invoices`, `/report/services` |

---

## 2. ADMIN PORTAL (Backend Administration)

### Architecture
- **Type:** Traditional Controllers
- **Base Controller:** `WHMAZADMIN_Controller`
- **Controllers:** `src/controllers/whmazadmin/`
- **Views:** `src/views/whmazadmin/`
- **Authentication:** Admin (username/email + password)
- **Session Key:** `ADMIN`
- **URL Pattern:** `domain.com/whmazadmin/controller/method`

### Controller Structure
```
src/controllers/whmazadmin/
├── Authenticate.php          # Admin login/logout
├── Dashboard.php             # Admin dashboard with statistics
├── Company.php               # Customer company management
├── Currency.php              # Currency settings
├── Server.php                # Server configuration
├── Package.php               # Service packages
├── Service_category.php      # Service categories
├── Service_group.php         # Service groups
├── Service_module.php        # Provisioning modules
├── Service_product.php       # Service product management (cPanel integration)
├── Domain_pricing.php        # Domain pricing management
├── Order.php                 # Order management
├── Invoice.php               # Invoice management & PDF
├── Expense.php               # Expense tracking
├── Expense_category.php      # Expense categories
├── Expense_vendor.php        # Vendor management
├── Ticket.php                # Ticket management
├── Ticket_department.php     # Support departments
├── Kb.php                    # Knowledge base articles
├── Kb_category.php           # KB categories
└── Announcement.php          # System announcements
```

### View Structure
```
src/views/whmazadmin/
├── login/                    # Admin login views
├── dashboard/                # Dashboard views
├── company/                  # Company management views
├── currency/                 # Currency views
├── server/                   # Server management views
├── package/                  # Package views
├── service_category/         # Category views
├── service_group/            # Group views
├── service_module/           # Module views
├── service_product/          # Service product views (list + manage)
├── domain_pricing/           # Domain pricing views
├── order/                    # Order management views
├── invoice/                  # Invoice views
├── expense/                  # Expense views
├── expense_category/         # Category views
├── expense_vendor/           # Vendor views
├── ticket/                   # Ticket views
├── ticket_department/        # Department views
├── kb/                       # Knowledge base views
├── kb_category/              # KB category views
├── announcement/             # Announcement views
└── includes/                 # Shared components
    ├── header.php
    ├── sidebar.php
    ├── footer.php
    └── scripts.php
```

### Admin Portal Features
| Controller | Purpose | Key URLs |
|-----------|---------|----------|
| **Authenticate** | Admin Auth | `/whmazadmin/authenticate/login` |
| **Dashboard** | Statistics & Overview | `/whmazadmin/dashboard` |
| **Company** | Customer Management | `/whmazadmin/company`, `/whmazadmin/company/create` |
| **Currency** | Currency Settings | `/whmazadmin/currency` |
| **Server** | Server Configuration | `/whmazadmin/server` |
| **Package** | Service Packages | `/whmazadmin/package`, `/whmazadmin/package/create` |
| **Service_category** | Categories | `/whmazadmin/service_category` |
| **Service_group** | Groups | `/whmazadmin/service_group` |
| **Service_module** | Modules | `/whmazadmin/service_module` |
| **Service_product** | Service Products | `/whmazadmin/service_product`, `/whmazadmin/service_product/manage` |
| **Domain_pricing** | Domain Pricing | `/whmazadmin/domain_pricing` |
| **Order** | Order Management | `/whmazadmin/order`, `/whmazadmin/order/view/{id}` |
| **Invoice** | Invoice Management | `/whmazadmin/invoice`, `/whmazadmin/invoice/pdf/{id}` |
| **Expense** | Expense Tracking | `/whmazadmin/expense` |
| **Expense_category** | Expense Categories | `/whmazadmin/expense_category` |
| **Expense_vendor** | Vendor Management | `/whmazadmin/expense_vendor` |
| **Ticket** | Ticket Management | `/whmazadmin/ticket`, `/whmazadmin/ticket/view/{id}` |
| **Ticket_department** | Support Departments | `/whmazadmin/ticket_department` |
| **Kb** | Knowledge Base | `/whmazadmin/kb`, `/whmazadmin/kb/create` |
| **Kb_category** | KB Categories | `/whmazadmin/kb_category` |
| **Announcement** | Announcements | `/whmazadmin/announcement` |

---

## Base Controllers Comparison

### Client Portal Base Controller
**File:** `src/core/WHMAZ_Controller.php`

```php
class WHMAZ_Controller extends MX_Controller
{
    // Auto-loads:
    // - Auth_model (customer authentication)
    // - Cart_model (shopping cart)

    // Functions:
    // - Customer login validation
    // - Default currency initialization
    // - Session management
}
```

**Usage:**
```php
class Auth extends WHMAZ_Controller {
    // Customer-facing controller
}
```

### Admin Portal Base Controller
**File:** `src/core/WHMAZADMIN_Controller.php`

```php
class WHMAZADMIN_Controller extends MX_Controller
{
    // Auto-loads:
    // - Adminauth_model (admin authentication)

    // Functions:
    // - Admin login validation
    // - Role-based access control
    // - REST API support
}
```

**Usage:**
```php
class Dashboard extends WHMAZADMIN_Controller {
    // Admin-facing controller
}
```

---

## Authentication Flow Comparison

### Client Portal Authentication
1. Customer visits `/auth/login`
2. Submits email + password
3. `Auth_model::validate_login()` validates
4. Session created with key `CUSTOMER`
5. Tracked in `user_logins` table
6. Redirected to `/clientarea/dashboard`

### Admin Portal Authentication
1. Admin visits `/whmazadmin/authenticate/login`
2. Submits username/email + password
3. `Adminauth_model::validate_admin_login()` validates
4. Session created with key `ADMIN`
5. Tracked in `admin_logins` table
6. Role permissions loaded
7. Redirected to `/whmazadmin/dashboard`

---

## Helper Functions for Portal Detection

From `whmaz_helper.php`:

### Client Portal Helpers
```php
is_customer_loggedin()      // Check if customer is logged in
get_customer_id()           // Get current customer ID
get_customer_data()         // Get customer session data
get_customer_company_id()   // Get customer's company ID
```

### Admin Portal Helpers
```php
is_admin_loggedin()         // Check if admin is logged in
get_admin_id()              // Get current admin ID
get_admin_data()            // Get admin session data
```

---

## Routing Patterns

### Client Portal Routes
```php
$route['default_controller'] = 'auth';  // Default to client login
$route['change-currency/(:any)/(:any)'] = 'auth/change_currency/$1/$2';
$route['domain-search/(:any)/(:any)'] = 'cart/domain_search/$1/$2';
$route['domain-suggestion/(:any)'] = 'cart/get_domain_suggestions/$1';
```

**URL Structure:**
- `domain.com/auth/login`
- `domain.com/cart/checkout`
- `domain.com/clientarea/dashboard`

### Admin Portal Routes
**URL Structure:**
- `domain.com/whmazadmin/authenticate/login`
- `domain.com/whmazadmin/dashboard`
- `domain.com/whmazadmin/order/view/123`

---

## Key Differences Summary

| Aspect | Client Portal | Admin Portal |
|--------|---------------|--------------|
| **Architecture** | HMVC Modules | Traditional Controllers |
| **Location** | `src/modules/` | `src/controllers/whmazadmin/` |
| **Views** | Module views & templates | `src/views/whmazadmin/` |
| **Base Controller** | `WHMAZ_Controller` | `WHMAZADMIN_Controller` |
| **Session Key** | `CUSTOMER` | `ADMIN` |
| **URL Prefix** | None | `/whmazadmin/` |
| **Authentication** | Email + Password | Username/Email + Password |
| **Access Level** | Customers only | Admin staff only |
| **Purpose** | Order services, view invoices | Manage entire system |
| **User Table** | `users` | `admin_users` |
| **Login Table** | `user_logins` | `admin_logins` |

---

## Quick Access URLs

### Client Portal
- **Login:** `/auth/login`
- **Register:** `/auth/register`
- **Dashboard:** `/clientarea/dashboard`
- **Cart:** `/cart/`
- **Invoices:** `/billing/invoices`
- **Tickets:** `/tickets/`

### Admin Portal
- **Login:** `/whmazadmin/authenticate/login`
- **Dashboard:** `/whmazadmin/dashboard`
- **Customers:** `/whmazadmin/company`
- **Orders:** `/whmazadmin/order`
- **Invoices:** `/whmazadmin/invoice`
- **Tickets:** `/whmazadmin/ticket`
- **Packages:** `/whmazadmin/package`

---

**Last Updated:** 2026-01-27
