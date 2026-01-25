# 📦 WHMAZ Installation Guide

> **Complete step-by-step installation guide for WHMAZ CRM**

This guide will walk you through the complete installation process of WHMAZ, from server preparation to final configuration. Follow each step carefully to ensure a successful installation.

---

## 📖 Table of Contents

- [Prerequisites](#-prerequisites)
- [Installation Methods](#-installation-methods)
- [Method 1: cPanel Installation](#method-1-cpanel-installation-recommended)
- [Method 2: Apache/Ubuntu Installation](#method-2-apacheubuntu-installation)
- [Method 3: Nginx Installation](#method-3-nginx-installation)
- [Post-Installation Configuration](#-post-installation-configuration)
- [First Login & Security](#-first-login--security)
- [Advanced Configuration](#-advanced-configuration)
- [Troubleshooting](#-troubleshooting)
- [Performance Optimization](#-performance-optimization)
- [Uninstallation](#-uninstallation)

---

## ✅ Prerequisites

Before installing WHMAZ, ensure your server meets these requirements:

### Server Requirements

| Component | Minimum | Recommended |
|-----------|---------|-------------|
| **PHP** | 8.2.0 | 8.3+ |
| **MySQL** | 5.7 | 8.0+ |
| **MariaDB** | 10.3 | 10.6+ |
| **Apache** | 2.4 | 2.4+ |
| **Web Server Disk Space** | 500 MB | 1 GB+ |
| **Database Size** | 50 MB | 200 MB+ |
| **RAM** | 512 MB | 2 GB+ |
| **PHP Memory Limit** | 128 MB | 256 MB+ |
| **Max Execution Time** | 300 seconds | 600 seconds |

### Required PHP Extensions

Verify these extensions are enabled in `php.ini`:

```bash
# Check installed PHP extensions
php -m | grep -E "curl|gd|mbstring|xml|zip|json|mysqli|openssl|fileinfo"
```

**Required Extensions:**
- ✅ `php-curl` - For API calls (domain registration, payment gateways)
- ✅ `php-gd` - For image manipulation
- ✅ `php-mbstring` - For multi-byte string handling
- ✅ `php-xml` - For XML parsing
- ✅ `php-zip` - For file compression
- ✅ `php-json` - For JSON handling
- ✅ `php-mysqli` - For database connectivity
- ✅ `php-openssl` - For secure connections
- ✅ `php-fileinfo` - For file type detection
- ✅ `php-intl` - For internationalization (optional but recommended)

### Required Apache Modules

```bash
# Check Apache modules
apache2ctl -M | grep -E "rewrite|headers"
```

**Required Modules:**
- ✅ `mod_rewrite` - For clean URLs
- ✅ `mod_headers` - For security headers

### Database Requirements

- MySQL 5.7+ or MariaDB 10.3+
- InnoDB storage engine support
- UTF-8 (utf8mb4) character set support
- CREATE, DROP, ALTER privileges

### File Permissions

You'll need the ability to:
- Upload files via FTP/SFTP or File Manager
- Set folder permissions (chmod)
- Create and edit files
- Access MySQL/PhpMyAdmin

---

## 🎯 Installation Methods

Choose the installation method that matches your server setup:

1. **[cPanel Installation](#method-1-cpanel-installation-recommended)** - For shared hosting with cPanel (Recommended for beginners)
2. **[Apache/Ubuntu Installation](#method-2-apacheubuntu-installation)** - For VPS/Dedicated servers with Ubuntu
3. **[Nginx Installation](#method-3-nginx-installation)** - For servers running Nginx

---

## Method 1: cPanel Installation (Recommended)

**Estimated Time:** 15-20 minutes

This is the easiest method for shared hosting environments.

### Step 1: Download WHMAZ

1. Download the WHMAZ package from CodeCanyon
2. Extract the ZIP file on your computer
3. You should see this file structure:

```
whmaz-crm/
├── index.php
├── .htaccess
├── crm_db.sql
├── crm_db_views.sql
├── src/
├── resources/
├── whmaz/
├── README.md
└── ... other files
```

### Step 2: Create MySQL Database

1. **Login to cPanel**
2. Navigate to **MySQL® Databases**
3. Create a new database:

![Create Database](screenshots/install/cpanel-create-db.png)

```
Database Name: youruser_whmaz
```

4. Create a database user:

![Create User](screenshots/install/cpanel-create-user.png)

```
Username: youruser_whmaz_user
Password: [Generate Strong Password]
```

> ⚠️ **Save these credentials!** You'll need them for configuration.

5. **Add user to database** with **ALL PRIVILEGES**

![Add User to Database](screenshots/install/cpanel-add-user.png)

### Step 3: Upload Files

**Option A: File Manager (Easy)**

1. Open **File Manager** in cPanel
2. Navigate to `public_html/` (or your domain folder)
3. Click **Upload**
4. Upload the WHMAZ ZIP file
5. Right-click → **Extract**
6. Move all extracted files to the root of your domain folder

**Option B: FTP (Recommended for large files)**

1. Use FileZilla or any FTP client
2. Connect to your server:
   - Host: `ftp.yourdomain.com`
   - Username: Your cPanel username
   - Password: Your cPanel password
   - Port: 21

3. Navigate to `public_html/`
4. Upload all WHMAZ files

**Final Structure:**
```
public_html/
├── index.php
├── .htaccess
├── crm_db.sql
├── src/
├── resources/
└── ...
```

### Step 4: Import Database

1. **Open phpMyAdmin** from cPanel
2. Select your database (`youruser_whmaz`)
3. Click **Import** tab
4. Click **Choose File** and select `crm_db.sql`
5. Click **Go** to import

![Import Database](screenshots/install/phpmyadmin-import.png)

6. **Repeat** for `crm_db_views.sql`

> ⏱️ Import may take 1-2 minutes depending on server speed

**Verify Import:**
```sql
SHOW TABLES;
```
You should see 40+ tables.

### Step 5: Configure Database Connection

1. Navigate to `public_html/src/config/`
2. Edit `database.php`

**Find this section:**
```php
$db['default'] = array(
    'dsn'   => '',
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'whmaz_crm',
```

**Replace with your details:**
```php
$db['default'] = array(
    'dsn'   => '',
    'hostname' => 'localhost',              // Usually 'localhost'
    'username' => 'youruser_whmaz_user',    // Database username
    'password' => 'YOUR_DB_PASSWORD',        // Database password
    'database' => 'youruser_whmaz',          // Database name
```

> 💡 **Tip:** In cPanel, the hostname is almost always `localhost`

### Step 6: Configure Base URL

1. Edit `src/config/config.php`
2. Find the base URL setting:

```php
$config['base_url'] = '';
```

**Update to your domain:**
```php
$config['base_url'] = 'https://yourdomain.com/';
```

**Important:**
- Include `https://` if you have SSL (recommended)
- Include trailing slash `/`
- If installed in a subfolder: `https://yourdomain.com/whmaz/`

### Step 7: Set File Permissions

**Using File Manager:**

1. Navigate to these folders:
   - `src/sessions/`
   - `src/logs/`
   - `src/cache/`
   - `resources/uploads/`

2. Right-click → **Change Permissions**
3. Set to **755** (or 777 if 755 doesn't work)

![Set Permissions](screenshots/install/cpanel-permissions.png)

**Using FTP:**

Right-click folders → File Attributes → `755`

### Step 8: Verify Installation

1. **Visit your website:** `https://yourdomain.com/`

You should see the WHMAZ client portal homepage.

![Client Portal](screenshots/install/client-homepage.png)

2. **Access Admin Portal:** `https://yourdomain.com/whmazadmin/authenticate/login`

![Admin Login](screenshots/install/admin-login.png)

**Default Admin Credentials:**
```
Email: admin@demo.com
Password: Admin@123
```

> ✅ **Success!** If you can access both portals, installation is complete!

### Step 9: Change Default Password (CRITICAL!)

1. Login to admin panel
2. Navigate to **Settings → My Profile**
3. Change password immediately
4. Update email address

---

## Method 2: Apache/Ubuntu Installation

**Estimated Time:** 20-30 minutes

For VPS or dedicated servers running Ubuntu/Debian with Apache.

### Step 1: Update System

```bash
sudo apt update
sudo apt upgrade -y
```

### Step 2: Install LAMP Stack

**Install Apache:**
```bash
sudo apt install apache2 -y
sudo systemctl start apache2
sudo systemctl enable apache2
```

**Install MySQL:**
```bash
sudo apt install mysql-server -y
sudo mysql_secure_installation
```

**Install PHP 8.2+:**
```bash
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-curl php8.2-gd \
  php8.2-mbstring php8.2-xml php8.2-zip php8.2-intl php8.2-fileinfo -y
```

**Verify PHP version:**
```bash
php -v
# Should show: PHP 8.2.x
```

### Step 3: Configure Apache

**Enable required modules:**
```bash
sudo a2enmod rewrite
sudo a2enmod headers
sudo systemctl restart apache2
```

**Create Virtual Host:**
```bash
sudo nano /etc/apache2/sites-available/whmaz.conf
```

**Add this configuration:**
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/html/whmaz

    <Directory /var/www/html/whmaz>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/whmaz-error.log
    CustomLog ${APACHE_LOG_DIR}/whmaz-access.log combined
</VirtualHost>
```

**Enable site:**
```bash
sudo a2ensite whmaz.conf
sudo systemctl reload apache2
```

### Step 4: Create Database

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE whmaz_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'whmaz_user'@'localhost' IDENTIFIED BY 'StrongPassword123!';
GRANT ALL PRIVILEGES ON whmaz_crm.* TO 'whmaz_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 5: Upload and Extract Files

```bash
# Create directory
sudo mkdir -p /var/www/html/whmaz
cd /var/www/html/whmaz

# Upload your whmaz-crm.zip file to server
# Using SCP from local machine:
# scp whmaz-crm.zip user@yourserver:/tmp/

# Extract
sudo unzip /tmp/whmaz-crm.zip -d /var/www/html/whmaz

# Set ownership
sudo chown -R www-data:www-data /var/www/html/whmaz
```

### Step 6: Import Database

```bash
mysql -u whmaz_user -p whmaz_crm < /var/www/html/whmaz/crm_db.sql
mysql -u whmaz_user -p whmaz_crm < /var/www/html/whmaz/crm_db_views.sql
```

### Step 7: Configure WHMAZ

**Edit database config:**
```bash
sudo nano /var/www/html/whmaz/src/config/database.php
```

Update credentials:
```php
'hostname' => 'localhost',
'username' => 'whmaz_user',
'password' => 'StrongPassword123!',
'database' => 'whmaz_crm',
```

**Edit base URL:**
```bash
sudo nano /var/www/html/whmaz/src/config/config.php
```

```php
$config['base_url'] = 'http://yourdomain.com/';
```

### Step 8: Set Permissions

```bash
sudo chmod -R 755 /var/www/html/whmaz/src/sessions/
sudo chmod -R 755 /var/www/html/whmaz/src/logs/
sudo chmod -R 755 /var/www/html/whmaz/src/cache/
sudo chmod -R 755 /var/www/html/whmaz/resources/uploads/
sudo chown -R www-data:www-data /var/www/html/whmaz
```

### Step 9: Configure SSL (Recommended)

**Install Certbot:**
```bash
sudo apt install certbot python3-certbot-apache -y
```

**Get SSL Certificate:**
```bash
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
```

**Auto-renewal:**
```bash
sudo certbot renew --dry-run
```

### Step 10: Verify Installation

Visit: `https://yourdomain.com/`

---

## Method 3: Nginx Installation

**Estimated Time:** 25-35 minutes

For servers running Nginx instead of Apache.

### Step 1: Install Nginx, PHP-FPM, MySQL

```bash
sudo apt update
sudo apt install nginx mysql-server -y
sudo apt install php8.2-fpm php8.2-mysql php8.2-curl php8.2-gd \
  php8.2-mbstring php8.2-xml php8.2-zip php8.2-intl php8.2-fileinfo -y
```

### Step 2: Configure Nginx

**Create server block:**
```bash
sudo nano /etc/nginx/sites-available/whmaz
```

**Add configuration:**
```nginx
server {
    listen 80;
    listen [::]:80;

    server_name yourdomain.com www.yourdomain.com;
    root /var/www/html/whmaz;
    index index.php index.html;

    # Logging
    access_log /var/log/nginx/whmaz-access.log;
    error_log /var/log/nginx/whmaz-error.log;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM configuration
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }

    # Deny access to sensitive directories
    location ~ ^/(src/config|src/logs|src/cache) {
        deny all;
    }
}
```

**Enable site:**
```bash
sudo ln -s /etc/nginx/sites-available/whmaz /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Step 3: Configure PHP-FPM

```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

**Update these values:**
```ini
upload_max_filesize = 50M
post_max_size = 50M
memory_limit = 256M
max_execution_time = 600
date.timezone = America/New_York  # Your timezone
```

**Restart PHP-FPM:**
```bash
sudo systemctl restart php8.2-fpm
```

### Step 4: Complete Installation

Follow **Steps 4-10 from Method 2** (Apache installation), using the same database and file configuration steps.

### Step 5: SSL with Certbot (Nginx)

```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

---

## 🔧 Post-Installation Configuration

After successful installation, configure these essential settings:

### 1. Email Configuration

**Edit:** `src/config/email.php`

**For SMTP (Recommended):**
```php
$config['protocol'] = 'smtp';
$config['smtp_host'] = 'mail.yourdomain.com';
$config['smtp_port'] = 587;
$config['smtp_crypto'] = 'tls';  // or 'ssl' for port 465
$config['smtp_user'] = 'noreply@yourdomain.com';
$config['smtp_pass'] = 'your_email_password';
$config['mailtype'] = 'html';
$config['charset'] = 'utf-8';
$config['newline'] = "\r\n";
```

**Test email:** Send a test ticket to verify email is working

### 2. Set Up Cron Jobs

Cron jobs automate important tasks like invoice generation and reminders.

**Edit crontab:**
```bash
crontab -e
```

**Add these cron jobs:**
```bash
# Generate daily invoices (runs at midnight)
0 0 * * * php /var/www/html/whmaz/index.php cron/generate_invoices >> /var/log/whmaz-cron.log 2>&1

# Send payment reminders (runs at 9 AM)
0 9 * * * php /var/www/html/whmaz/index.php cron/send_reminders >> /var/log/whmaz-cron.log 2>&1

# Sync domains (runs every 6 hours)
0 */6 * * * php /var/www/html/whmaz/index.php cron/sync_domains >> /var/log/whmaz-cron.log 2>&1

# Clean old sessions (daily at 2 AM)
0 2 * * * php /var/www/html/whmaz/index.php cron/clean_sessions >> /var/log/whmaz-cron.log 2>&1
```

**Verify cron jobs:**
```bash
crontab -l
```

### 3. Configure Currency

1. Login to admin panel
2. Navigate to **Settings → Currency Management**
3. Add your currencies:
   - USD (United States Dollar)
   - EUR (Euro)
   - GBP (British Pound)
   - BDT (Bangladeshi Taka)
4. Set one as **default currency**

### 4. Set Up Payment Gateways

Navigate to **Admin → Settings → Payment Gateways**

Configure your preferred payment methods:
- **Stripe** - Credit card processing
- **PayPal** - PayPal payments
- **Bank Transfer** - Manual bank transfer
- **Cash** - Cash on delivery

### 5. Configure Domain Registrar

If using domain registration features:

1. Sign up at [ResellerClub](https://resellerclub.com) or [Resell.biz](https://resell.biz)
2. Get API credentials
3. Navigate to **Admin → Domain → Domain Registrars**
4. Add your registrar:
   - Name: ResellerClub
   - API URL: `https://httpapi.com/api/domains/available.json?`
   - Auth User ID: Your reseller ID
   - API Key: Your API key
5. **Whitelist your server IP** in registrar control panel

---

## 🔐 First Login & Security

### Default Credentials

**Admin Portal:** `https://yourdomain.com/whmazadmin/authenticate/login`
```
Email: admin@demo.com
Password: Admin@123
```

**Demo Customer:** `https://yourdomain.com/auth/login`
```
Email: customer@demo.com
Password: Demo@123
```

### Critical Security Steps (DO IMMEDIATELY!)

#### 1. Change Admin Password

```
Admin Panel → Settings → My Profile → Change Password
```

Choose a strong password:
- Minimum 12 characters
- Mix of uppercase, lowercase, numbers, symbols
- Use password manager

#### 2. Change Admin Email

Update to your actual email address:
```
Admin Panel → Settings → My Profile → Email Address
```

#### 3. Delete or Update Demo Accounts

**Option A: Delete demo customer**
```
Admin Panel → Customers → Companies → Delete demo accounts
```

**Option B: Update demo customer**
```
Change email and password to real customer for testing
```

#### 4. Configure Security Settings

**Edit:** `src/config/config.php`

```php
// Enable CSRF protection (should already be enabled)
$config['csrf_protection'] = TRUE;
$config['csrf_token_name'] = 'csrf_token_whmaz';
$config['csrf_cookie_name'] = 'csrf_cookie_whmaz';
$config['csrf_expire'] = 7200;

// Session security
$config['sess_cookie_name'] = 'whmaz_session';
$config['sess_expire_on_close'] = TRUE;
$config['sess_encryption_key'] = 'CHANGE_THIS_TO_RANDOM_32_CHARS';
$config['sess_time_to_update'] = 300;
```

**Generate session encryption key:**
```bash
php -r "echo bin2hex(random_bytes(16));"
```

#### 5. Secure File Permissions

```bash
# Make config files read-only
chmod 644 src/config/*.php

# Protect .htaccess
chmod 644 .htaccess

# Secure database credentials
chmod 600 src/config/database.php
```

#### 6. Remove Installation Files (Optional)

After successful installation:
```bash
rm crm_db.sql
rm crm_db_views.sql
rm update_domain_api_production.sql
rm test_domain_api.php
```

---

## ⚙️ Advanced Configuration

### Custom Domain for Admin Panel

By default, admin is at `/whmazadmin/`

**To use subdomain** (e.g., `admin.yourdomain.com`):

1. **Create subdomain** in cPanel or DNS
2. **Point to same document root**
3. **Update routes** in `src/config/routes.php`

### Multi-Currency Exchange Rates

**Option 1: Manual rates**
```
Admin → Settings → Currency → Edit → Exchange Rate
```

**Option 2: Auto-update (requires API)**
Integrate with currency API services like:
- exchangerate-api.com
- currencyapi.com

### Custom Email Templates

Edit email templates:
```
Admin → Settings → Email Templates
```

Available variables:
- `{company_name}`
- `{customer_name}`
- `{invoice_number}`
- `{amount}`
- `{due_date}`

### Backup Configuration

**Automated Database Backups:**

Create backup script: `/var/www/html/whmaz/backup.sh`

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/whmaz"
DB_NAME="whmaz_crm"
DB_USER="whmaz_user"
DB_PASS="your_password"

mkdir -p $BACKUP_DIR
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/whmaz_$DATE.sql.gz

# Keep only last 30 days
find $BACKUP_DIR -name "whmaz_*.sql.gz" -mtime +30 -delete
```

**Add to cron:**
```bash
0 3 * * * /var/www/html/whmaz/backup.sh
```

---

## 🔍 Troubleshooting

### Common Installation Issues

#### Issue 1: Blank White Page

**Symptoms:** Website shows blank white page

**Causes:**
- PHP errors
- Memory limit exceeded
- Missing PHP extensions

**Solutions:**

1. **Enable error display:**
```php
// Add to top of index.php temporarily
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

2. **Check error logs:**
```bash
# Apache
tail -f /var/log/apache2/error.log

# Nginx
tail -f /var/log/nginx/error.log

# PHP-FPM
tail -f /var/log/php8.2-fpm.log

# WHMAZ logs
tail -f /var/www/html/whmaz/src/logs/log-*.php
```

3. **Increase PHP memory:**
```ini
memory_limit = 256M
```

#### Issue 2: 404 Error on Admin Login

**Symptoms:** `/whmazadmin/authenticate/login` shows 404

**Causes:**
- `.htaccess` not working
- `mod_rewrite` not enabled
- Incorrect base URL

**Solutions:**

1. **Verify .htaccess exists:**
```bash
ls -la /var/www/html/whmaz/.htaccess
```

2. **Check mod_rewrite (Apache):**
```bash
apache2ctl -M | grep rewrite
# Should show: rewrite_module
```

3. **Enable mod_rewrite:**
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

4. **Check AllowOverride:**
Edit `/etc/apache2/sites-available/whmaz.conf`
```apache
<Directory /var/www/html/whmaz>
    AllowOverride All  # Must be "All", not "None"
</Directory>
```

5. **For Nginx:** Ensure server block configuration is correct

#### Issue 3: Database Connection Error

**Symptoms:** "Unable to connect to database"

**Solutions:**

1. **Verify credentials:**
```bash
mysql -u whmaz_user -p
# Enter password and try to connect
```

2. **Check database exists:**
```sql
SHOW DATABASES;
USE whmaz_crm;
SHOW TABLES;
```

3. **Verify hostname:**
- cPanel: Almost always `localhost`
- Remote DB: Use IP address or hostname

4. **Check MySQL is running:**
```bash
sudo systemctl status mysql
```

5. **Grant privileges again:**
```sql
GRANT ALL PRIVILEGES ON whmaz_crm.* TO 'whmaz_user'@'localhost';
FLUSH PRIVILEGES;
```

#### Issue 4: Permission Denied Errors

**Symptoms:** "Permission denied" or "Unable to write file"

**Solutions:**

1. **Set folder permissions:**
```bash
chmod -R 755 src/sessions/
chmod -R 755 src/logs/
chmod -R 755 src/cache/
chmod -R 755 resources/uploads/
```

2. **Set ownership (Ubuntu/Debian):**
```bash
sudo chown -R www-data:www-data /var/www/html/whmaz
```

3. **Set ownership (CentOS/RHEL):**
```bash
sudo chown -R apache:apache /var/www/html/whmaz
```

4. **SELinux issues (CentOS):**
```bash
sudo setenforce 0  # Temporary
# Or configure SELinux properly
```

#### Issue 5: CSS/JS Not Loading

**Symptoms:** Page shows but no styling, broken layout

**Causes:**
- Incorrect base URL
- .htaccess issues
- File permissions

**Solutions:**

1. **Check base URL:**
```php
// src/config/config.php
$config['base_url'] = 'https://yourdomain.com/';  # Must match actual URL
```

2. **Verify file permissions:**
```bash
chmod -R 644 resources/
```

3. **Check browser console:**
Press F12 → Console tab → Look for 404 errors

4. **Clear browser cache:**
Ctrl+Shift+Delete → Clear cache

#### Issue 6: Email Not Sending

**Symptoms:** No emails received (invoices, notifications)

**Solutions:**

1. **Check email config:**
```php
// src/config/email.php
$config['protocol'] = 'smtp';  # Not 'mail'
```

2. **Test SMTP connection:**
```bash
telnet mail.yourdomain.com 587
```

3. **Check firewall:**
```bash
# Allow SMTP ports
sudo ufw allow 587/tcp
sudo ufw allow 465/tcp
```

4. **Enable debug mode:**
```php
// Temporarily in email.php
$config['smtp_debug'] = 2;
```

5. **Check spam folder**

6. **Verify SMTP credentials:**
Try logging into email account manually

#### Issue 7: Session Errors

**Symptoms:** "Session expired" or constant logouts

**Solutions:**

1. **Check session path writable:**
```bash
chmod -R 755 src/sessions/
```

2. **Clear old sessions:**
```bash
rm -rf src/sessions/*
```

3. **Check session configuration:**
```php
// src/config/config.php
$config['sess_save_path'] = APPPATH . 'sessions/';
$config['sess_driver'] = 'database';  # or 'files'
```

4. **Verify sessions table exists:**
```sql
SELECT * FROM sessions LIMIT 1;
```

#### Issue 8: Cron Jobs Not Running

**Symptoms:** Invoices not generating automatically

**Solutions:**

1. **Verify cron syntax:**
```bash
crontab -l
```

2. **Check PHP path:**
```bash
which php
# Use full path in cron: /usr/bin/php
```

3. **Test cron manually:**
```bash
php /var/www/html/whmaz/index.php cron/generate_invoices
```

4. **Check cron logs:**
```bash
tail -f /var/log/syslog | grep CRON
```

5. **Add logging:**
```bash
0 0 * * * php /var/www/html/whmaz/index.php cron/generate_invoices >> /tmp/cron.log 2>&1
```

---

## ⚡ Performance Optimization

### Enable OPcache

**Edit:** `/etc/php/8.2/fpm/php.ini`

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

**Restart PHP:**
```bash
sudo systemctl restart php8.2-fpm
```

### Enable Gzip Compression

**Apache (.htaccess):**
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
```

**Nginx:**
```nginx
gzip on;
gzip_vary on;
gzip_types text/plain text/css text/xml text/javascript application/javascript application/json;
gzip_min_length 1000;
```

### Enable Browser Caching

**Apache (.htaccess):**
```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

### Database Optimization

```sql
-- Optimize all tables
OPTIMIZE TABLE companies, orders, invoices, tickets;

-- Add indexes if needed (already included in schema)
SHOW INDEX FROM orders;
```

### CDN Integration (Optional)

Upload static assets to CDN:
- Bootstrap CSS/JS
- jQuery
- Font Awesome
- Custom images

Update links in templates to use CDN URLs.

---

## 🗑️ Uninstallation

If you need to completely remove WHMAZ:

### Step 1: Backup Data (Optional)

```bash
# Backup database
mysqldump -u whmaz_user -p whmaz_crm > whmaz_backup.sql

# Backup uploads
tar -czf whmaz_uploads.tar.gz resources/uploads/
```

### Step 2: Remove Database

```sql
DROP DATABASE whmaz_crm;
DROP USER 'whmaz_user'@'localhost';
FLUSH PRIVILEGES;
```

### Step 3: Remove Files

```bash
# Remove all WHMAZ files
rm -rf /var/www/html/whmaz

# Remove logs
rm -f /var/log/nginx/whmaz-*
rm -f /var/log/apache2/whmaz-*
```

### Step 4: Remove Cron Jobs

```bash
crontab -e
# Remove WHMAZ related lines
```

### Step 5: Remove Virtual Hosts

**Apache:**
```bash
sudo a2dissite whmaz.conf
sudo rm /etc/apache2/sites-available/whmaz.conf
sudo systemctl reload apache2
```

**Nginx:**
```bash
sudo rm /etc/nginx/sites-available/whmaz
sudo rm /etc/nginx/sites-enabled/whmaz
sudo systemctl reload nginx
```

---

## 📞 Still Need Help?

### Support Channels

**Email Support:** support@yourcompany.com
**Documentation:** [View All Guides](README.md)
**FAQ:** [Common Questions](README.md#frequently-asked-questions)

### Before Contacting Support

Please provide:
1. **Server Information:**
   - PHP version: `php -v`
   - MySQL version: `mysql -V`
   - Web server: Apache or Nginx
   - Operating system

2. **Error Details:**
   - Error message (screenshot or copy-paste)
   - Steps to reproduce
   - What you've already tried

3. **Log Files:**
   - `/var/log/apache2/error.log` or `/var/log/nginx/error.log`
   - `src/logs/log-[date].php`

### Quick Diagnostic Commands

```bash
# Check PHP version and extensions
php -v
php -m

# Check web server status
sudo systemctl status apache2  # or nginx

# Check MySQL status
sudo systemctl status mysql

# Check disk space
df -h

# Check memory usage
free -m

# Test database connection
mysql -u whmaz_user -p -e "USE whmaz_crm; SHOW TABLES;"

# Check file permissions
ls -la /var/www/html/whmaz/src/sessions/
```

---

## ✅ Installation Checklist

Use this checklist to ensure complete installation:

### Pre-Installation
- [ ] Server meets minimum requirements
- [ ] PHP 8.2+ installed with all required extensions
- [ ] MySQL/MariaDB installed
- [ ] Web server (Apache/Nginx) installed
- [ ] Domain/subdomain configured
- [ ] SSL certificate installed (optional but recommended)

### Installation
- [ ] Files uploaded to server
- [ ] Database created
- [ ] Database user created with privileges
- [ ] crm_db.sql imported successfully
- [ ] crm_db_views.sql imported successfully
- [ ] database.php configured with correct credentials
- [ ] config.php base URL set correctly
- [ ] File permissions set (755 on writable folders)
- [ ] .htaccess working (for Apache)
- [ ] Can access client portal
- [ ] Can access admin portal

### Security
- [ ] Admin password changed
- [ ] Admin email updated
- [ ] Demo accounts deleted or updated
- [ ] Session encryption key changed
- [ ] Config files set to read-only
- [ ] Installation files removed

### Configuration
- [ ] Email settings configured
- [ ] Test email sent successfully
- [ ] Currency added and default set
- [ ] Payment gateways configured
- [ ] Cron jobs set up
- [ ] Backup system configured

### Testing
- [ ] Create test customer account
- [ ] Place test order
- [ ] Generate test invoice
- [ ] Submit test ticket
- [ ] Test email notifications
- [ ] Verify cron jobs running

---

<div align="center">

**Installation Complete! 🎉**

Your WHMAZ CRM is now ready to use!

[Back to README](README.md) • [User Guide](USER_GUIDE.md) • [Get Support](mailto:support@yourcompany.com)

**Made with ❤️ by Your Company**

</div>
