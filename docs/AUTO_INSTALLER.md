# WHMAZ Auto-Installer Documentation

## Overview

The WHMAZ auto-installer is a standalone PHP wizard that guides users through the installation process. It's designed for CodeCanyon distribution and works independently of CodeIgniter (since CI isn't configured yet during installation).

## File Structure

```
install/
├── index.php              # Main router and controller
├── Install.php            # Core installer class with all logic
├── .htaccess              # Security rules
├── assets/
│   └── installer.css      # Styling (matches WHMAZ auth pages)
└── views/
    ├── layout.php         # Base HTML layout with step indicators
    ├── step1_welcome.php  # Welcome page with agreement checkbox
    ├── step2_requirements.php  # Server requirements check
    ├── step3_database.php # Database configuration form
    ├── step4_import.php   # SQL import with AJAX progress
    ├── step5_settings.php # Site settings & admin account
    ├── step6_complete.php # Success page with portal links
    ├── error.php          # Error display page
    └── already_installed.php # Shown if already installed
```

## Installation Flow

### Step 1: Welcome
- Displays welcome message and WHMAZ branding
- Shows pre-installation checklist
- Requires agreement checkbox to proceed

### Step 2: Server Requirements
**Checks performed:**
- PHP version (minimum 8.2.0)
- Required PHP extensions:
  - curl, gd, mbstring, xml, zip, json, mysqli, openssl, fileinfo
- Optional extensions:
  - intl (internationalization)
- Writable directories:
  - `src/sessions/`, `src/logs/`, `src/cache/`, `uploadedfiles/`
- Required files:
  - `crm_db.sql`, `crm_db_views.sql`, `.env.example`

### Step 3: Database Configuration
- Form fields: hostname, port, database name, username, password
- "Test Connection" button with AJAX validation
- Stores credentials in session for next steps

### Step 4: Database Import
- Imports `crm_db.sql` (main schema and data)
- Imports `crm_db_views.sql` (database views)
- Shows progress bar with percentage
- Handles errors gracefully, continues with other statements

### Step 5: Site Settings
**Site Information:**
- Site Name (stored in `app_settings`)
- Site URL (auto-detected, editable)

**Admin Account:**
- Admin Email (becomes login username)
- Password with real-time strength validation:
  - Minimum 8 characters
  - At least one uppercase letter
  - At least one lowercase letter
  - At least one number

**Actions performed:**
1. Creates `.env` file from `.env.example`
2. Updates `app_settings` table with site info
3. Updates `admin_users` table with new admin credentials
4. Creates `install/install.lock` file

### Step 6: Installation Complete
- Shows success message
- Displays Admin Panel and Client Portal URLs
- "Delete Install Folder" button
- Security reminder to remove `/install/` folder

## Core Components

### Install.php Class

```php
class Install {
    // Constants
    const MIN_PHP_VERSION = '8.2.0';
    const REQUIRED_EXTENSIONS = [...];
    const OPTIONAL_EXTENSIONS = [...];
    const WRITABLE_DIRS = [...];
    const REQUIRED_FILES = [...];

    // Key Methods
    public function isInstalled();           // Check if already installed
    public function checkAllRequirements();  // Run all requirement checks
    public function testDatabaseConnection(); // Test DB credentials
    public function storeDbCredentials();    // Store in session
    public function importSqlFile();         // Import SQL with progress
    public function createEnvFile();         // Generate .env from template
    public function updateAdminCredentials(); // Set admin email/password
    public function updateSiteSettings();    // Set site name/URL
    public function createLockFile();        // Create install.lock
    public function deleteInstallFolder();   // Self-destruct
}
```

### index.php Router

Handles:
- Step navigation (GET requests)
- Form submissions (POST requests)
- AJAX endpoints:
  - `action=test_db` - Test database connection
  - `action=import_sql` - Import SQL file
  - `action=delete_install` - Delete install folder

### Security Features

1. **CSRF Protection**
   - Token generated per session
   - Validated on all POST requests

2. **Installation Lock**
   - `install.lock` file created after successful install
   - Prevents reinstallation without manual intervention

3. **Already Installed Check**
   - Checks for `.env` with valid DB connection
   - Checks for `install.lock` file
   - Redirects to "Already Installed" page

4. **.htaccess Protection**
   - Blocks direct access to view files
   - Protects `Install.php` from direct access
   - Hides log and lock files

## Styling

The installer uses custom CSS (`installer.css`) that matches WHMAZ auth pages:

**Color Scheme:**
- Primary gradient: `linear-gradient(135deg, #0168fa 0%, #6f42c1 100%)`
- Success: `#10b981`
- Error: `#ef4444`
- Warning: `#f59e0b`

**Components:**
- `.installer-wrapper` - Main container
- `.installer-header` - Blue-purple gradient header
- `.installer-steps` - Step indicator with icons
- `.installer-content` - White content area
- `.requirement-item` - Requirement check row (passed/failed/warning)
- `.progress-bar` - Animated progress bar
- `.btn-primary` - Gradient button

## Database Import Strategy

```php
public function importSqlFile($filename) {
    // 1. Read SQL file
    $sql = file_get_contents($filepath);

    // 2. Remove comments
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

    // 3. Split into statements (handles DELIMITER, quoted strings)
    $statements = $this->splitSqlStatements($sql);

    // 4. Disable foreign key checks
    $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // 5. Execute each statement
    foreach ($statements as $statement) {
        $this->pdo->exec($statement);
    }

    // 6. Re-enable foreign key checks
    $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
}
```

## Session Data

During installation, the following is stored in session:

```php
$_SESSION['csrf_token']    // CSRF protection token
$_SESSION['install_step']  // Current step number (1-6)
$_SESSION['db_config']     // Database credentials array
$_SESSION['site_url']      // Site URL for completion page
```

## Error Handling

- User-friendly error messages displayed in alerts
- Technical details logged to `install/install.log`
- Database import continues even if some statements fail
- "Try Again" options provided where possible

## Customization Guide

### Adding a New Requirement Check

1. Add to `Install.php`:
```php
const REQUIRED_EXTENSIONS = [
    // ... existing
    'newext' => 'New Extension Name',
];
```

2. The requirement will automatically appear in Step 2

### Modifying the Admin Table Update

Edit `updateAdminCredentials()` in `Install.php`:
```php
public function updateAdminCredentials($email, $password) {
    $stmt = $this->pdo->prepare("
        UPDATE admin_users
        SET email = ?, password = ?, username = ?, updated_on = NOW()
        WHERE id = 1
    ");
    // Add more fields as needed
}
```

### Changing Password Requirements

Edit `validatePassword()` in `Install.php`:
```php
public function validatePassword($password) {
    $errors = [];
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    // Add or modify rules
}
```

### Adding New Settings

1. Add form field in `step5_settings.php`
2. Process in `index.php` under case 5
3. Save to database in `updateSiteSettings()` or new method

## Testing Checklist

### Fresh Install Test
- [ ] Empty database, run installer
- [ ] All tables created successfully
- [ ] `.env` file generated correctly
- [ ] Admin credentials work
- [ ] Can access both portals

### Requirements Failure Test
- [ ] Disable a PHP extension
- [ ] Clear error message displayed
- [ ] Re-check button works
- [ ] Re-enable and retry succeeds

### Database Error Test
- [ ] Wrong credentials show clear error
- [ ] Non-existent database shows helpful message
- [ ] Test Connection button works

### Security Test
- [ ] Access installer after install shows "Already Installed"
- [ ] Delete install folder works
- [ ] Direct access to views blocked

## Troubleshooting

### "CSRF token invalid" Error
- Clear browser cookies
- Refresh the page
- Try incognito/private window

### Database Import Fails
- Check `install/install.log` for details
- Verify database user has CREATE/ALTER/DROP privileges
- Check if SQL files exist and are readable

### .env Not Created
- Verify `.env.example` exists
- Check write permissions on root directory
- Check PHP has write access

### Admin Login Not Working After Install
- Verify email format is correct
- Password meets strength requirements
- Check `admin_users` table directly

## Files Modified During Installation

| File | Action |
|------|--------|
| `.env` | Created from `.env.example` |
| `install/install.lock` | Created to prevent reinstall |
| `install/install.log` | Created with install logs |
| `app_settings` table | Updated with site info |
| `admin_users` table | Updated with admin credentials |

## Maintenance

### After Major Updates
If database schema changes:
1. Update `crm_db.sql` with new tables/columns
2. Update `crm_db_views.sql` if views changed
3. Test fresh installation

### Adding New Required Extensions
1. Add to `REQUIRED_EXTENSIONS` constant
2. Update `codecanyon/INSTALLATION.md` prerequisites
3. Test on fresh server

---

**Last Updated:** 2026-03-14
**Version:** 1.0.0
