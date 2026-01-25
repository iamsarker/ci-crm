# PHP 8.2+ Upgrade & Troubleshooting Guide

## Table of Contents
1. [PHP 8.2+ Compatibility Fixes](#php-82-compatibility-fixes)
2. [Base URL Configuration Fix](#base-url-configuration-fix)
3. [Domain Search Functionality](#domain-search-functionality)
4. [Resources Loading Issues](#resources-loading-issues)
5. [Common Issues & Solutions](#common-issues--solutions)

---

## PHP 8.2+ Compatibility Fixes

### Issue 1: `str_replace()` Null Parameter Deprecation

**Error:**
```
Severity: 8192
Message: str_replace(): Passing null to parameter #1 ($search) of type array|string is deprecated
Filename: MX/Controller.php
Line Number: 45
```

**Fix Applied:**
File: `src/third_party/MX/Controller.php` (Line 45)

```php
// BEFORE
$class = str_replace(CI::$APP->config->item('controller_suffix'), '', get_class($this));

// AFTER
$class = str_replace(CI::$APP->config->item('controller_suffix') ?? '', '', get_class($this));
```

**Explanation:** Added null coalescing operator (`?? ''`) to provide an empty string default when `controller_suffix` config is null.

---

### Issue 2: Dynamic Property Creation Deprecation

#### Fix 2a: MX_Controller Class

**Error:**
```
Severity: 8192
Message: Creation of dynamic property Auth::$load is deprecated
Filename: MX/Controller.php
Line Number: 50
```

**Fix Applied:**
File: `src/third_party/MX/Controller.php` (Line 42)

```php
class MX_Controller
{
    public $autoload = array();
    public $load;  // <-- Added explicit property declaration

    // ... rest of the class
}
```

#### Fix 2b: MX_Loader Class

**Error:**
```
Severity: 8192
Message: Creation of dynamic property WHMAZ_Loader::$controller is deprecated
Filename: MX/Loader.php
Line Number: 52
```

**Fix Applied:**
File: `src/third_party/MX/Loader.php` (Line 42)

```php
class MX_Loader extends CI_Loader
{
    protected $_module;

    public $_ci_plugins = array();
    public $_ci_cached_vars = array();
    public $controller;  // <-- Added explicit property declaration

    // ... rest of the class
}
```

**Explanation:** PHP 8.2+ requires all class properties to be explicitly declared. Dynamic property creation is deprecated.

---

## Base URL Configuration Fix

### Issue: Incorrect Base URL Detection

**Problem:**
- Base URL was incorrectly using URI segments (e.g., `/auth`) as the project folder
- URLs like `http://localhost/auth` were being treated as base URL
- Resource files (CSS/JS) couldn't be loaded
- Links were broken

**Example of the Problem:**
- Visiting: `http://localhost/auth/login`
- Base URL became: `http://localhost/auth/`
- Resource path: `http://localhost/auth/resources/css/style.css` ❌ (Wrong!)
- Should be: `http://localhost/resources/css/style.css` ✓

**Fix Applied:**
File: `src/config/config.php` (Lines 27-35)

```php
// BEFORE (INCORRECT)
$protocol = (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http')) . '://';
$host = (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost') . '/';
$project = isset($_SERVER['REQUEST_URI']) ? explode('/', $_SERVER['REQUEST_URI'])[1] : '';
$config['base_url'] = $protocol . $host . $project;

// AFTER (CORRECT)
$protocol = (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http')) . '://';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

// Detect base path from SCRIPT_NAME
$script_name = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
$base_path = str_replace('\\', '/', dirname($script_name));
$base_path = ($base_path === '/') ? '' : $base_path;

$config['base_url'] = $protocol . $host . $base_path . '/';
```

**How It Works:**
1. Uses `$_SERVER['SCRIPT_NAME']` to detect where `index.php` is located
2. Extracts the directory path using `dirname()`
3. Normalizes path separators (Windows compatibility)
4. Always includes trailing slash

**Examples:**
- **Root Installation:**
  - Script Name: `/index.php`
  - Base Path: `/` → becomes empty string
  - Base URL: `http://localhost/` ✓

- **Subdirectory Installation:**
  - Script Name: `/myproject/index.php`
  - Base Path: `/myproject`
  - Base URL: `http://localhost/myproject/` ✓

---

## Domain Search Functionality

### Overview
The domain registration page (`/cart/domain/register`) allows users to:
1. Search for domain availability
2. View available domain extensions and pricing
3. Get domain name suggestions
4. Add domains to cart

### Architecture

#### Frontend (AngularJS)
- **Controller:** `resources/angular/app/services_controller.js`
- **View:** `src/modules/cart/views/cart_regnewdomain.php`

#### Backend (CodeIgniter)
- **Controller:** `src/modules/cart/controllers/Cart.php`
- **Model:** `src/models/Cart_model.php`
- **Routes:** `src/config/routes.php`

### API Routes

File: `src/config/routes.php`

```php
$route['domain-search/(:any)/(:any)'] = 'cart/domain_search/$1/$2';
$route['domain-suggestion/(:any)'] = 'cart/get_domain_suggestions/$1';
```

### Fix Applied: Display Issue

**Problem:**
Suggestions section wasn't showing after search due to invalid CSS display value.

**Fix Applied:**
File: `resources/angular/app/services_controller.js` (Line 131)

```javascript
// BEFORE
document.getElementById('domain-suggestions').style.display = 'show';  // Invalid!

// AFTER
document.getElementById('domain-suggestions').style.display = 'block';  // Correct!
```

### Domain Registrar Configuration

The system integrates with domain registrar APIs for real-time domain checking and suggestions.

#### Database Table: `dom_registers`

```sql
CREATE TABLE `dom_registers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `platform` varchar(60) NOT NULL,
  `api_base_url` varchar(255) NOT NULL,
  `domain_check_api` text NOT NULL,
  `suggestion_api` text DEFAULT NULL,
  `domain_reg_api` text DEFAULT NULL,
  `price_list_api` text DEFAULT NULL,
  `auth_userid` varchar(50) NOT NULL,
  `auth_apikey` varchar(100) NOT NULL,
  `is_selected` tinyint(4) NOT NULL DEFAULT 1,
  `def_ns1` varchar(150) DEFAULT NULL,
  `def_ns2` varchar(150) DEFAULT NULL,
  `def_ns3` varchar(150) DEFAULT NULL,
  `def_ns4` varchar(150) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1
);
```

#### Supported Platforms

1. **ResellerClub/Resell.Biz (STARGATE Platform)**
   - Domain Check API: `https://domaincheck.httpapi.com/api/domains/available.json?`
   - Suggestions API: `https://test.httpapi.com/api/domains/v5/suggest-names.json?`
   - Registration API: `https://test.httpapi.com/api/domains/register.xml?`

2. **Namecheap**
   - Base URL: `https://api.namecheap.com/xml.response` (Production)
   - Sandbox URL: `https://api.sandbox.namecheap.com/xml.response` (Testing)

### Setting Up Production API

#### Step 1: Get API Credentials

**For ResellerClub/Resell.Biz:**
1. Sign up at https://www.resellerclub.com/ or https://resell.biz/
2. Navigate to Settings > API
3. Get your Reseller ID (auth_userid)
4. Generate API Key (auth_apikey)

**For Namecheap:**
1. Sign up at https://www.namecheap.com/
2. Enable API Access in your account
3. Get API Username and API Key
4. Whitelist your server IP

#### Step 2: Update Database

```sql
-- Update ResellerClub credentials
UPDATE `dom_registers`
SET
  `auth_userid` = 'YOUR_RESELLER_ID',
  `auth_apikey` = 'YOUR_API_KEY',
  `domain_check_api` = 'https://httpapi.com/api/domains/available.json?',
  `suggestion_api` = 'https://httpapi.com/api/domains/v5/suggest-names.json?',
  `domain_reg_api` = 'https://httpapi.com/api/domains/register.xml?',
  `is_selected` = 1
WHERE `id` = 1;
```

#### Step 3: Test the Integration

1. Navigate to: `http://localhost/cart/domain/register`
2. Search for a domain: `example.com`
3. Check browser console for API responses
4. Verify suggestions load correctly

### API Flow

```
User enters domain
       ↓
btnSearchDomain() triggered
       ↓
GET /domain-search/register/{domain}
       ↓
Cart::domain_search() method
       ↓
External API call to registrar
       ↓
Parse response & return availability
       ↓
Display result
       ↓
getDomainSuggestion() triggered
       ↓
GET /domain-suggestion/{domain}
       ↓
Cart::get_domain_suggestions() method
       ↓
External API call for suggestions
       ↓
Display suggestions list
```

### Testing with Mock Data

For testing without API credentials, you can uncomment the mock response in `Cart.php`:

File: `src/modules/cart/controllers/Cart.php` (Line 253)

```php
public function domain_search($type = NULL, $domkeyword = NULL)
{
    // ... actual implementation ...

    // FOR TESTING: Uncomment the line below to use mock data
    // echo json_encode(json_decode('{"status":1,"info":{"name":"mdshahadatsarker.com","price":"12.99", "domPriceId":1}}'));
}
```

---

## Resources Loading Issues

### Problem Description
CSS and JavaScript files were not loading because:
1. Base URL was missing trailing slash
2. Template paths concatenated incorrectly

### Resource Structure
```
E:\OwnLibs\ci-crm\
├── resources/
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── img/
│   ├── lib/
│   │   ├── bootstrap/
│   │   ├── jquery/
│   │   └── ...
│   └── angular/
│       └── app/
└── src/
    └── views/
        └── templates/
            └── customer/
                ├── header.php
                └── footer_script.php
```

### Template Files

#### Header Template
File: `src/views/templates/customer/header.php`

```php
<!-- CSS Resources -->
<link href="<?=base_url()?>resources/assets/css/dashforge.css" rel="stylesheet">
<link href="<?=base_url()?>resources/lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">

<!-- Note: base_url() must return URL with trailing slash -->
```

#### Footer Script Template
File: `src/views/templates/customer/footer_script.php`

```php
<!-- JavaScript Resources -->
<script src="<?=base_url()?>resources/lib/jquery/jquery.min.js"></script>
<script src="<?=base_url()?>resources/lib/bootstrap/js/bootstrap.bundle.min.js"></script>
```

### Verification

After fixing base_url configuration, verify resources load:

1. Open browser DevTools (F12)
2. Navigate to Network tab
3. Refresh the page
4. All resources should return **200 OK** status
5. Check Console for any 404 errors

---

## Common Issues & Solutions

### Issue: 404 Error on Resources

**Symptoms:**
- CSS styles not applied
- JavaScript not working
- Console shows 404 errors for `/resources/*` files

**Solution:**
1. Verify base_url configuration in `src/config/config.php`
2. Check that `resources` folder exists at project root
3. Verify file permissions (should be readable)
4. Clear browser cache

**Debug:**
```php
// Add to any controller to debug base_url
echo "Base URL: " . base_url() . "<br>";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
```

---

### Issue: AngularJS Not Loading

**Symptoms:**
- `{{ }}` bindings visible in HTML
- AngularJS directives not working
- Console shows AngularJS errors

**Solution:**
1. Verify AngularJS library loads: `resources/angular/angular.min.js`
2. Check browser console for JavaScript errors
3. Verify `ng-app` directive in view files
4. Check controller initialization

**Debug:**
```javascript
// Add to browser console
console.log('Angular:', typeof angular);  // Should be 'object'
console.log('App:', angular.module('ServicesApp'));  // Should not throw error
```

---

### Issue: Domain Search Returns Empty

**Symptoms:**
- Search button works but no results
- Console shows API errors
- Suggestions don't load

**Checklist:**
1. ✓ Database has `dom_registers` table
2. ✓ At least one registrar has `status=1` and `is_selected=1`
3. ✓ API credentials are correct
4. ✓ API URLs are accessible (check firewall)
5. ✓ `dom_pricing` table has pricing data
6. ✓ Currency is configured correctly

**Debug:**
```php
// Add to Cart::domain_search() method
error_log("Domain keyword: " . $domkeyword);
error_log("Registrar: " . print_r($regVendor, true));
error_log("API Response: " . print_r($resp, true));
```

---

### Issue: Sessions Not Working

**Symptoms:**
- Can't login
- Cart items disappear
- Session data lost

**Solution:**
1. Create sessions directory:
```bash
mkdir -p src/sessions
chmod 777 src/sessions
```

2. Verify session configuration in `src/config/config.php`:
```php
$config['sess_driver'] = 'files';
$config['sess_save_path'] = APPPATH . 'sessions';
```

---

### Issue: Database Connection Failed

**Symptoms:**
- "Unable to connect to database" error
- 500 Internal Server Error
- Blank page

**Solution:**
1. Check database configuration: `src/config/database.php`
2. Verify MySQL service is running
3. Test database credentials:
```php
// Test connection
$conn = mysqli_connect('localhost', 'username', 'password', 'database');
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "Connected successfully";
```

---

## Development Tips

### Enable Error Reporting

For development, enable detailed error reporting:

File: `index.php` (Line ~55)

```php
define('ENVIRONMENT', 'development');  // Change to 'development'
```

File: `src/config/config.php`

```php
$config['log_threshold'] = 4;  // Log all messages
```

### Database Query Debugging

Add to any model method:

```php
echo $this->db->last_query();
die();
```

### CodeIgniter Profiler

Enable profiler in any controller:

```php
$this->output->enable_profiler(TRUE);
```

---

## Upgrade Checklist

When upgrading to PHP 8.2+:

- [ ] Fix null parameter deprecations (use `??` operator)
- [ ] Declare all class properties explicitly
- [ ] Update deprecated functions (e.g., `create_function()`)
- [ ] Check session handling compatibility
- [ ] Test file upload functionality
- [ ] Verify encryption/decryption works
- [ ] Test email sending
- [ ] Check API integrations
- [ ] Review third-party library compatibility
- [ ] Update `.htaccess` if needed
- [ ] Test on staging before production

---

## Support & Resources

### Documentation
- [CodeIgniter 3 User Guide](https://codeigniter.com/userguide3/)
- [PHP 8.2 Migration Guide](https://www.php.net/manual/en/migration82.php)
- [ResellerClub API Documentation](https://manage.resellerclub.com/kb/answer/751)
- [Namecheap API Documentation](https://www.namecheap.com/support/api/)

### Common Commands

```bash
# Check PHP version
php -v

# List PHP modules
php -m

# Check CodeIgniter routes
php index.php tools routes

# Clear sessions
rm -rf src/sessions/*

# Fix permissions
chmod -R 755 src/
chmod -R 777 src/sessions/
chmod -R 777 uploadedfiles/
```

---

## Version History

| Date       | Version | Changes                                      |
|------------|---------|----------------------------------------------|
| 2026-01-20 | 1.0     | Initial documentation                        |
|            |         | - PHP 8.2+ compatibility fixes               |
|            |         | - Base URL configuration fix                 |
|            |         | - Domain search functionality documentation  |
|            |         | - Resources loading troubleshooting          |

---

## Contributors

- Initial Setup & PHP 8.2+ Upgrade: [Your Name]
- Documentation: Claude AI Assistant
- Project: CI-CRM (WHMAZ)

---

## License

This project follows the same license as the main CI-CRM project.