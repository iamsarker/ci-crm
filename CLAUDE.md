# WHMAZ CRM - Development Notes

## Folder Structure

### Admin (whmazadmin)
```
src/controllers/whmazadmin/     # Admin controllers
src/views/whmazadmin/           # Admin views
```

### Client/Customer (HMVC Modules)
```
src/modules/
├── auth/                       # Authentication
│   ├── controllers/
│   └── views/
├── billing/                    # Invoices, payments
│   ├── controllers/
│   └── views/
├── cart/                       # Shopping cart
│   ├── controllers/
│   └── views/
├── clientarea/                 # Client dashboard
│   ├── controllers/
│   └── views/
├── order/                      # Order management
│   ├── controllers/
│   └── views/
├── pages/                      # Static pages
│   ├── controllers/
│   └── views/
├── report/                     # Reports
│   ├── controllers/
│   └── views/
├── supports/                   # Support system
│   ├── controllers/
│   └── views/
├── tickets/                    # Ticket system
│   ├── controllers/
│   └── views/
├── webhook/                    # Payment webhooks
│   ├── controllers/
│   └── views/
└── cronjobs/                   # Scheduled tasks
    └── controllers/
```

### Shared Resources
```
src/models/                     # Database models (shared)
src/libraries/                  # Custom libraries (shared)
src/views/templates/            # Shared templates (header, footer)
src/views/email/                # Email templates
```

### CSS Styling
```
resources/assets/css/
├── admin.custom.css            # Admin general customizations
├── admin.list_page.css         # Admin list/table page styles
├── admin.manage_view.css       # Admin management/detail view styles
└── custom.css                  # Client area customizations
```

#### Admin Portal Colors (admin.manage_view.css)
- **Card Header**: `--md-secondary` = `linear-gradient(135deg, #546E7A 0%, #455A64 100%)` (blue-gray gradient)
- **Form Section Title**: Context-specific colors:
  - Order pages: `#1976D2` (blue) via `--md-accent`
  - Company pages: `#00897B` (teal)
  - KB pages: `#5E35B1` (purple)
  - Ticket Dept pages: `#E65100` (orange)

#### Client Portal Colors (custom.css)
- **Card Header**: Background `#f8f9fc`, icons `#0168fa` (blue)
- **Form Section Title**: Text `#1c273c` (dark navy), icons `#0168fa` (blue)
- **Register Section Title**: `#00897B` (teal)
- **Checkout Card Header**: `linear-gradient(135deg, #667eea 0%, #764ba2 100%)` (purple gradient)

## Important Configuration Locations

### Security
- **Content Security Policy (CSP)**: `src/config/config.php` (line ~599)
  - Add external script domains here for payment gateways, analytics, etc.

### Payment Gateways
- **Payment Libraries**: `src/libraries/`
  - `Stripe.php` - Stripe payment integration
  - `Paypal.php` - PayPal payment integration
  - `Sslcommerz.php` - SSLCommerz payment integration

- **Payment Controllers**:
  - `src/modules/billing/controllers/Pay.php` - Customer payment processing
  - `src/modules/webhook/controllers/Webhook.php` - Payment webhook handlers
  - `src/controllers/whmazadmin/Paymentgateway.php` - Admin gateway management

- **Payment Models**:
  - `src/models/Payment_model.php` - Transaction handling
  - `src/models/PaymentGateway_model.php` - Gateway configuration

### Database
- **Database Config**: `src/config/database.php`
- **SQL Schema**: `crm_db.sql`

### Views
- **Customer Payment Page**: `src/modules/billing/views/billing_pay.php`
- **Admin Gateway Management**: `src/views/whmazadmin/paymentgateway_manage.php`

## Known Gotchas

### processRestCall() Issue
- `$this->processRestCall()` reads JSON from `php://input` and overwrites `$_POST`
- **DO NOT use** in AJAX handlers that receive form-urlencoded data (`application/x-www-form-urlencoded`)
- Only use for true REST/JSON API endpoints

## Library Naming Convention
Libraries follow CodeIgniter standard naming:
- File: `Libraryname.php` (capital first letter only)
- Class: `Libraryname` (matches filename)
- Access: `$this->libraryname` (lowercase)

Examples:
- `Stripe.php` → class `Stripe` → `$this->stripe`
- `Paypal.php` → class `Paypal` → `$this->paypal`
- `Sslcommerz.php` → class `Sslcommerz` → `$this->sslcommerz`

## Admin Manage Page Template

Reference: `src/views/whmazadmin/paymentgateway_manage.php`

### Structure
```php
<?php $this->load->view('whmazadmin/include/header'); ?>
<link rel="stylesheet" href="<?=base_url()?>resources/assets/css/admin.manage_view.css">

<div class="content content-fluid content-wrapper">
    <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">
        <!-- Page Header -->
        <div class="order-page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3><i class="fas fa-icon me-2"></i> Page Title</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="...">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="...">Parent</a></li>
                            <li class="breadcrumb-item active"><a href="#">Current</a></li>
                        </ol>
                    </nav>
                </div>
                <a href="..." class="btn btn-back">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        <div class="manage-form-card">
            <!-- Section Cards -->
            <div class="order-card mb-4">
                <div class="card-header">
                    <div class="header-icon"><i class="fas fa-icon"></i></div>
                    <h6>Section Title</h6>
                </div>
                <div class="card-body">
                    <!-- Form fields with class="form-label" -->
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-end mt-4">
                <button type="submit" class="btn-create-order">
                    <i class="fas fa-save me-2"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>
```

### Key Classes
- `order-page-header` - Blue gradient page header
- `btn-back` - Transparent back button for header
- `manage-form-card` - White card wrapper for form
- `order-card` - Section card with blue-gray header
- `header-icon` - Icon container in card header
- `form-label` - Styled form labels
- `btn-create-order` - Blue gradient submit button
