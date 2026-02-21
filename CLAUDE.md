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
- **Page Header Card**: `linear-gradient(135deg, #0168fa 0%, #6f42c1 100%)` (blue-purple gradient)
- **Card Header**: Background `#f8f9fc`, icons `#0168fa` (blue)
- **Form Section Title**: Text `#1c273c` (dark navy), icons `#0168fa` (blue)
- **Auth Pages** (login, register, forgot password, reset password):
  - Header: `linear-gradient(135deg, #0168fa 0%, #6f42c1 100%)` (blue-purple gradient)
  - Buttons: Same gradient
  - Form focus/links: `#0168fa` (blue)
- **Checkout Card Header**: `linear-gradient(135deg, #667eea 0%, #764ba2 100%)` (purple gradient)

## Important Configuration Locations

### Configuration Storage (Database vs .env)

**Stored in Database (Admin Portal managed):**
| Setting | Table | Admin Location |
|---------|-------|----------------|
| Company Info | `app_settings` | Settings → General |
| Google reCAPTCHA | `app_settings` | Settings → General |
| Payment Gateways | `payment_gateway` | Settings → Payment Gateways |
| Email Templates | `email_templates` | Settings → Email Templates |
| Domain Registrar | `dom_registers` | Settings → Domain Registrar |
| Billing Configuration | `sys_cnf` | Settings → Billing |
| Automation / Cron Jobs | `sys_cnf` | Settings → Automation |
| Feature Flags | `sys_cnf` | Settings → Features |
| Notifications | `sys_cnf` | Settings → Notifications |
| Customer Portal | `sys_cnf` | Settings → Customer Portal |
| Support System | `sys_cnf` | Settings → Support |

**Stored in .env file (environment-specific):**
- Database credentials (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
- Encryption key
- Session configuration
- CSRF settings
- Environment mode (development/production)

**DO NOT put in .env:**
- Payment gateway API keys (use Admin Portal)
- Company information (use Admin Portal)
- Google reCAPTCHA keys (use Admin Portal)
- Domain registrar credentials (use Admin Portal)
- Any setting that has an Admin Portal interface

**Accessing sys_cnf values in code:**
```php
// Load the model
$this->load->model('Syscnf_model');

// Get single value
$invoicePrefix = $this->Syscnf_model->getValue('invoice_prefix');

// Get with type casting and default
$taxRate = $this->Syscnf_model->get('tax_rate', 0, 'float');
$taxEnabled = $this->Syscnf_model->get('tax_enabled', false, 'bool');

// Get all values in a group
$billingConfig = $this->Syscnf_model->getByGroup('BILLING');
// Returns: ['invoice_prefix' => 'INV-', 'tax_rate' => '10.00', ...]
```

### Security
- **Content Security Policy (CSP)**: `src/config/config.php` (line ~599)
  - Add external script domains here for payment gateways, analytics, etc.

### Payment Gateways

**Implementation Status:**
| Gateway | Status | Webhook | Notes |
|---------|--------|---------|-------|
| Stripe | ✅ Working | ✅ Implemented | Full integration with PaymentIntent API |
| SSLCommerz | ✅ Working | ✅ Implemented | Session restoration for external redirects |
| PayPal | ⚠️ Partial | ✅ Implemented | Webhook ready, needs testing |

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

- **Admin Payment Gateway Pages**:
  - List page: `src/views/whmazadmin/paymentgateway_list.php`
  - Configure page: `src/views/whmazadmin/paymentgateway_manage.php`
  - Transactions: `src/views/whmazadmin/paymentgateway_transactions.php`
  - Webhook logs: `src/views/whmazadmin/paymentgateway_webhooks.php`

- **Admin Gateway Features**:
  - Toggle gateway status (enable/disable) with SweetAlert2 confirmation
  - Test connection button for Stripe/PayPal
  - View transaction history with DataTables
  - View webhook logs with filtering

### Webhook Configuration
Payment webhooks are handled by `src/modules/webhook/controllers/Webhook.php`

**Webhook URLs:**
| Gateway | Webhook URL |
|---------|-------------|
| Stripe | `https://yourdomain.com/webhook/stripe` |
| PayPal | `https://yourdomain.com/webhook/paypal` |
| SSLCommerz | `https://yourdomain.com/webhook/sslcommerz` |

**Database Fields (payment_gateway table):**
| Field | Description |
|-------|-------------|
| `public_key` | Live publishable/public key |
| `secret_key` | Live secret key |
| `webhook_secret` | Live webhook signing secret |
| `test_public_key` | Test/Sandbox public key |
| `test_secret_key` | Test/Sandbox secret key |
| `test_webhook_secret` | Test/Sandbox webhook signing secret |
| `is_test_mode` | 1 = Test mode, 0 = Live mode |

**Stripe Webhook Events Handled:**
- `payment_intent.succeeded` - Payment completed
- `payment_intent.payment_failed` - Payment failed
- `checkout.session.completed` - Checkout session completed
- `charge.refunded` - Refund processed

**Webhook Security:**
- Stripe: Signature verification via `Stripe::verifyWebhook()`
- PayPal: Signature verification via `Paypal::verifyWebhook()`
- All webhooks logged to `webhook_logs` table
- Duplicate event detection via `Payment_model::isWebhookProcessed()`

**Payment Confirmation Emails:**
- Sent automatically when invoice is marked as PAID
- Customer receives: `invoice_payment_confirmation` template
- Admin receives: `admin_payment_notification` template (if `notify_admin_payment` is enabled in sys_cnf)
- Method: `Payment_model::sendPaymentConfirmationEmails($transactionId)`
- Called from: `Payment_model::processSuccessfulPayment()`

**Order Confirmation Emails:**
- Sent automatically after successful order placement and invoice generation
- Customer receives: `order_confirmation` template
- Admin receives: `admin_order_notification` template (if `notify_admin_new_order` is enabled in sys_cnf)
- Method: `Order_model::sendOrderConfirmationEmails($orderId, $invoiceId)`
- Called from: `Cart::checkoutSubmit()`

**Ticket Notification Emails:**
- New ticket by client → Department receives: `ticket_new_to_department` template
  - Method: `Support_model::sendNewTicketToDepartment($ticketId)`
  - Called from: `Tickets::newticket()` (client module)
- New ticket by admin → Customer receives: `ticket_new_to_customer` template
  - Method: `Support_model::sendNewTicketToCustomer($ticketId)`
  - Called from: `Ticket::add()` (admin controller)
- Reply by admin → Customer receives: `ticket_reply_to_customer` template
  - Method: `Support_model::sendTicketReplyToCustomer($ticketId, $message)`
  - Called from: `Ticket::replyticket()` (admin controller)
- Reply by client → Department receives: `ticket_reply_to_department` template
  - Method: `Support_model::sendTicketReplyToDepartment($ticketId, $message)`
  - Called from: `Tickets::replyticket()` (client module)

### Provisioning System

**Auto-Provisioning Flow:**
After successful payment (webhook or admin "Mark as Paid"), the system automatically provisions:

| Order Type | Action | API Called |
|------------|--------|------------|
| Domain Registration | Register domain at registrar | ResellerClub/Resell.biz API |
| Domain Transfer | Initiate transfer with EPP code | ResellerClub/Resell.biz API |
| Domain Renewal | Renew domain, update expiry | ResellerClub/Resell.biz API |
| New Hosting | Create cPanel account | WHM API |
| Hosting Renewal | Unsuspend if suspended, update dates | WHM API (if suspended) |

**Key Files:**
- `src/models/Provisioning_model.php` - Main provisioning logic
- `src/helpers/domain_helper.php` - Domain registrar API functions
- `src/helpers/cpanel_helper.php` - cPanel/WHM API functions

**Admin Provisioning Logs:**
- **URL**: `whmazadmin/provisioning/index`
- **Menu**: Orders → Provisioning Logs
- **Controller**: `src/controllers/whmazadmin/Provisioning.php`
- **View**: `src/views/whmazadmin/provisioning_logs.php`

| Feature | Description |
|---------|-------------|
| Stats Dashboard | Total, success, failed, and today's counts |
| Filters | Status (success/failed), item type (domain/service), action |
| DataTable | Server-side pagination with sorting and search |
| Log Details | Modal with error messages and API response data |
| Retry | Retry failed provisioning items individually |

**Admin Controller Methods:**
- `index()` - Main page with stats cards
- `logs_list_api()` - Server-side DataTable endpoint
- `log_detail($id)` - AJAX endpoint for log details
- `retry_item($logId)` - Retry single failed item
- `retry($invoiceId)` - Retry all failed items for invoice
- `failed_count_api()` - Get failed count (for dashboard widget)

**Provisioning Entry Points:**
- `Payment_model::processSuccessfulPayment()` → `Invoice_model::provisionPaidServices()`
- `Invoice_model::updateInvoiceStatus()` (admin mark as paid) → `provisionPaidServices()`

**How It Works:**
1. Payment confirmed (webhook or admin action)
2. `provisionPaidServices($invoiceId)` called
3. Loops through `invoice_items` with `ref_id`
4. Determines type: domain (`item_type=1`) or service (`item_type=2`)
5. For domains: checks `order_type` (1=register, 2=transfer, 3=dns_only) and if renewal
6. For services: checks if new (create account) or renewal (unsuspend if suspended)
7. Calls appropriate API (registrar or WHM)
8. Updates order status, logs result to `provisioning_logs`

**Adding New Registrar Support:**
1. Add functions to `domain_helper.php` (e.g., `enom_register_domain()`)
2. Update switch statements in `registrar_*` dispatcher functions
3. Match on `dom_registers.platform` field

### Database
- **Database Config**: `src/config/database.php`
- **SQL Schema**: `crm_db.sql`
- **DB Views**: `crm_db_views.sql`
- **Migrations**: `migrations/` directory

### Database Migrations
When adding new features that require schema changes:
1. Create migration file in `migrations/` directory
2. Update main `crm_db.sql` schema for fresh installs
3. Document migration in this file

**Available Migrations:**
| Migration | Description |
|-----------|-------------|
| `cart_enhancements.sql` | Cart linking (parent_cart_id, domain_action, epp_code) + Order linking (linked_domain_id, linked_service_id) |
| `sys_cnf_billing_automation.sql` | System config + Payment email templates (Billing, Automation, Features, Notifications, Portal, Support) |
| `order_confirmation_emails.sql` | Order confirmation email templates (order_confirmation, admin_order_notification) |
| `ticket_notification_emails.sql` | Ticket notification email templates (ticket_new_to_department, ticket_new_to_customer, ticket_reply_to_customer, ticket_reply_to_department) |
| `provisioning_system.sql` | Provisioning logs table and status columns for order_services/order_domains |

### Views
- **Customer Payment Page**: `src/modules/billing/views/billing_pay.php`
- **Admin Gateway Management**: `src/views/whmazadmin/paymentgateway_manage.php`

## Known Gotchas

### processRestCall() Issue
- `$this->processRestCall()` reads JSON from `php://input` and overwrites `$_POST`
- **DO NOT use** in AJAX handlers that receive form-urlencoded data (`application/x-www-form-urlencoded`)
- Only use for true REST/JSON API endpoints

### Payment Gateway Session Loss (SameSite Cookie Issue)
When payment gateways redirect back to the app via **external POST**, browsers block session cookies due to `SameSite` policy, causing user logout.

**Gateway Types:**
| Gateway | Flow Type | Session Issue? |
|---------|-----------|----------------|
| Stripe | AJAX + client-side JS → same-origin redirect | No |
| PayPal | AJAX + popup → AJAX capture → same-origin redirect | No |
| Razorpay | AJAX + popup → same-origin redirect | No |
| SSLCommerz | AJAX → **external redirect** → **external POST back** | **Yes** |

**Solution for external-redirect gateways (SSLCommerz, etc.):**
1. Generate secure token on init: `$paymentToken = bin2hex(random_bytes(32));`
2. Store in transaction metadata: `'payment_token' => $paymentToken, 'user_id' => $userId`
3. Pass to gateway (e.g., SSLCommerz `value_c` parameter)
4. On callback, verify token and restore session via `Auth_model->getUserSessionData($userId)`

**Reference implementation:** `src/modules/billing/controllers/Pay.php`
- `sslcommerz_init()` - generates and stores token
- `sslcommerz_success/fail/cancel()` - calls `_restoreSessionFromTransaction()`
- `_restoreSessionFromTransaction()` - verifies token, restores session

**When adding new payment gateways:**
- If gateway uses popup/modal (Razorpay, PayPal style): No special handling needed
- If gateway does full browser redirect + POST callback: Implement token-based session restoration

## Cart & Checkout Implementation

### Cart User Flows

**Flow-1: Hosting → Domain (Required)**
1. User selects hosting package (SHARED/RESELLER/VPS)
2. User must provide domain info:
   - **Register**: Search domain availability via registrar API
   - **Transfer**: Enter EPP/Auth code
   - **DNS Update**: Just enter domain name
3. Creates 2 linked cart records:
   - Record 1: Hosting (`item_type=2`, `parent_cart_id=NULL`)
   - Record 2: Domain (`item_type=1`, `parent_cart_id=Record1.id`)

**Flow-2: Domain → Hosting (Optional)**
1. User searches/selects domain
2. Hosting selection is optional
3. Creates 1 or 2 records:
   - Domain only: 1 record (`item_type=1`, `parent_cart_id=NULL`)
   - Domain + Hosting: Domain record + Hosting record (`parent_cart_id=Domain.id`)

### Cart Table Fields
```
add_to_carts:
- item_type: 1=domain, 2=product_service
- parent_cart_id: Links related items (domain ↔ hosting)
- domain_action: 'register', 'transfer', 'dns_update'
- epp_code: For domain transfers
- hosting_domain: The domain name
- hosting_domain_type: 0=DNS, 1=REGISTER, 2=TRANSFER (legacy, use domain_action)
```

### Cart Linking Logic
- Parent item: `parent_cart_id = NULL`
- Child item: `parent_cart_id = parent.id`
- When deleting parent, also delete children
- Invoice shows hierarchical line items

### Order Table Linking
```
order_services:
- linked_domain_id: Links to order_domains.id when domain is purchased with hosting

order_domains:
- linked_service_id: Links to order_services.id when hosting is purchased with domain
```

### Checkout Process
1. `checkoutSubmit()` uses `getCartListWithChildren()` for hierarchical data
2. Parent items processed first, then children
3. `_processCartItem()` handles domain/service record creation
4. `_linkOrderItems()` establishes bi-directional linking between order_services and order_domains

### Cart API Endpoints
| Endpoint | Method | Description |
|----------|--------|-------------|
| `cart/services/{groupId}` | GET | Browse hosting packages by group |
| `cart/view` | GET | View cart with hierarchical items |
| `cart/addHostingToCart` | POST | Flow-1: Add hosting to cart |
| `cart/linkDomainToHosting` | POST | Flow-1: Link domain to hosting |
| `cart/addDomainToCart` | POST | Flow-2: Add domain to cart |
| `cart/linkHostingToDomain` | POST | Flow-2: Link hosting to domain |
| `cart/delete/{id}` | GET | Delete cart item (with children) |
| `cart/delete_all` | GET | Empty entire cart |
| `cart/checkoutSubmit` | POST | Process checkout |
| `cart/getCount` | GET | Get cart item count |
| `domain-search` | GET | Search domain availability |
| `domain-suggestion` | GET | Get domain name suggestions |

### Domain Order Types
```
order_domains.order_type:
- 1 = Registration (new domain)
- 2 = Transfer (from another registrar)
- 3 = DNS Update only (no registration needed)
```

### Cart Files Reference
- **Controller**: `src/modules/cart/controllers/Cart.php`
- **Model**: `src/models/Cart_model.php`
- **Views**:
  - `src/modules/cart/views/cart_services.php` - Hosting packages listing
  - `src/modules/cart/views/view_card.php` - Cart view with items
  - `src/modules/cart/views/cart_regnewdomain.php` - Domain search page
- **Angular**: `resources/angular/app/services_controller.js`

### Hosting Types
- SHARED (product_service_type_key = 'shared')
- RESELLER (product_service_type_key = 'reseller')
- VPS (product_service_type_key = 'vps')

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

## Domain Registrar Integration

### API Configuration
- **Database Table**: `dom_registers`
- **Key Fields**: `auth_userid`, `auth_apikey`, `domain_check_api`, `suggestion_api`
- **Model**: `src/models/Cart_model.php` → `getDomRegister()`

### Troubleshooting Domain Search

**Common Errors:**

| Error | Cause | Solution |
|-------|-------|----------|
| "No response from domain registrar API" | cURL failed or empty response | Check server logs for details |
| "HTTP Error: 403" | IP not whitelisted or invalid credentials | Whitelist server IP in registrar panel |
| "Domain registrar not configured" | Missing `domain_check_api` in database | Configure registrar in admin panel |

**403 Forbidden - "Request forbidden by administrative rules":**
1. **Whitelist production server IP** in registrar panel (Settings → API → Allowed IPs)
2. Try re-adding the IP if already listed
3. Check if registrar has multiple whitelist locations
4. Verify API credentials match registrar panel exactly
5. Ensure using correct API endpoint (Live vs Test/OTE)

**Debug from server:**
```bash
# Check server's outgoing IP
curl ifconfig.me

# Test API directly (replace credentials)
curl -v "https://domaincheck.httpapi.com/api/domains/available.json?auth-userid=YOUR_ID&api-key=YOUR_KEY&domain-name=test&tlds=com"
```

**Logs location:** `src/logs/log-YYYY-MM-DD.php`

### cURL Request Handling
- **Base Controller**: `src/core/WHMAZ_Controller.php`
- **Method**: `curlGetRequest($url)` - Returns decoded JSON or null on error
- **Error Access**: `$this->getLastCurlError()` - Returns last cURL/HTTP error message
- SSL verification disabled for domain registrar APIs (some use self-signed certs)

## Payment Page Styling Notes

### Stripe Card Element
The Stripe card element requires explicit width styling to render properly:

```css
#stripe-form {
    width: 100% !important;
}

#stripe-card-element {
    width: 100% !important;
    min-height: 44px;
    box-sizing: border-box;
}
```

**Initialization timing:** Mount Stripe element with a small delay (100ms) after showing the form to ensure container has proper dimensions:
```javascript
setTimeout(function() {
    cardElement.mount('#stripe-card-element');
}, 100);
```

**Reference:** `src/modules/billing/views/billing_pay.php`
