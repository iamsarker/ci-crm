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
