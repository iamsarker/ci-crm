<?php
/**
 * WHMAZ Auto-Installer
 *
 * Main installer class handling all installation logic
 *
 * @package WHMAZ
 * @version 1.0.0
 */

class Install
{
    private $basePath;
    private $pdo;
    private $errors = [];
    private $sessionStarted = false;

    // Minimum PHP version required
    const MIN_PHP_VERSION = '8.2.0';

    // Required PHP extensions
    const REQUIRED_EXTENSIONS = [
        'curl' => 'cURL (API calls)',
        'gd' => 'GD Library (Image processing)',
        'mbstring' => 'Multibyte String',
        'xml' => 'XML Parser',
        'zip' => 'Zip Archive',
        'json' => 'JSON',
        'mysqli' => 'MySQLi',
        'openssl' => 'OpenSSL',
        'fileinfo' => 'File Info',
    ];

    // Optional extensions
    const OPTIONAL_EXTENSIONS = [
        'intl' => 'Internationalization',
    ];

    // Writable directories (relative to base path)
    // '.' = root directory (for .env file)
    // 'install' = installer directory (for install.log and install.lock)
    const WRITABLE_DIRS = [
        '.',
        'install',
        'src/sessions',
        'src/logs',
        'src/cache',
        'uploadedfiles',
    ];

    // Required files
    const REQUIRED_FILES = [
        'crm_db.sql',
        'crm_db_views.sql',
        '.env.example',
    ];

    // Fixed vendor license server. Every install is a master copy that enforces
    // its LICENSE_KEY against this host via the admin-login gate.
    const LICENSE_SERVER_URL = 'https://whmaz.com';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->basePath = dirname(__DIR__);
        $this->startSession();
    }

    /**
     * Start session if not already started
     */
    private function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            $this->sessionStarted = true;
        }

        // Generate CSRF token if not exists
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    /**
     * Get CSRF token
     */
    public function getCsrfToken()
    {
        return $_SESSION['csrf_token'] ?? '';
    }

    /**
     * Validate CSRF token
     */
    public function validateCsrf($token)
    {
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }

    /**
     * Check if already installed
     */
    public function isInstalled()
    {
        $envFile = $this->basePath . '/.env';
        $lockFile = $this->basePath . '/install/install.lock';

        // Check for lock file
        if (file_exists($lockFile)) {
            return true;
        }

        // Check for .env with valid database connection
        if (file_exists($envFile)) {
            $env = $this->parseEnvFile($envFile);
            if (!empty($env['DB_DATABASE']) && !empty($env['DB_USERNAME'])) {
                // Try to connect and check if tables exist
                try {
                    $pdo = $this->createConnection(
                        $env['DB_HOSTNAME'] ?? 'localhost',
                        $env['DB_PORT'] ?? '3306',
                        $env['DB_DATABASE'],
                        $env['DB_USERNAME'],
                        $env['DB_PASSWORD'] ?? ''
                    );

                    // Check if admin_users table exists
                    $stmt = $pdo->query("SHOW TABLES LIKE 'admin_users'");
                    if ($stmt->rowCount() > 0) {
                        return true;
                    }
                } catch (Exception $e) {
                    // Connection failed, not installed
                    return false;
                }
            }
        }

        return false;
    }

    /**
     * Parse .env file
     */
    private function parseEnvFile($file)
    {
        $env = [];
        $content = file_get_contents($file);
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }

            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $env[trim($key)] = trim($value);
            }
        }

        return $env;
    }

    /**
     * Check PHP version requirement
     */
    public function checkPhpVersion()
    {
        return [
            'required' => self::MIN_PHP_VERSION,
            'current' => PHP_VERSION,
            'passed' => version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '>=')
        ];
    }

    /**
     * Check required PHP extensions
     */
    public function checkExtensions()
    {
        $results = [];

        foreach (self::REQUIRED_EXTENSIONS as $ext => $name) {
            $results[$ext] = [
                'name' => $name,
                'loaded' => extension_loaded($ext),
                'required' => true
            ];
        }

        foreach (self::OPTIONAL_EXTENSIONS as $ext => $name) {
            $results[$ext] = [
                'name' => $name,
                'loaded' => extension_loaded($ext),
                'required' => false
            ];
        }

        return $results;
    }

    /**
     * Check writable directories
     */
    public function checkWritableDirs()
    {
        $results = [];

        // User-friendly names for directories
        $dirLabels = [
            '.' => 'Root Directory (for .env file)',
            'install' => 'Install Directory (for logs)',
        ];

        foreach (self::WRITABLE_DIRS as $dir) {
            $path = $this->basePath . '/' . $dir;
            $exists = is_dir($path);
            $writable = $exists && is_writable($path);

            $results[$dir] = [
                'path' => $dir,
                'label' => $dirLabels[$dir] ?? $dir,
                'exists' => $exists,
                'writable' => $writable,
                'passed' => $writable
            ];
        }

        return $results;
    }

    /**
     * Check required files exist
     */
    public function checkRequiredFiles()
    {
        $results = [];

        foreach (self::REQUIRED_FILES as $file) {
            $path = $this->basePath . '/' . $file;
            $exists = file_exists($path);

            $results[$file] = [
                'file' => $file,
                'exists' => $exists,
                'passed' => $exists
            ];
        }

        return $results;
    }

    /**
     * Check all requirements
     */
    public function checkAllRequirements()
    {
        $php = $this->checkPhpVersion();
        $extensions = $this->checkExtensions();
        $dirs = $this->checkWritableDirs();
        $files = $this->checkRequiredFiles();

        // Check if all required items pass
        $allPassed = $php['passed'];

        foreach ($extensions as $ext) {
            if ($ext['required'] && !$ext['loaded']) {
                $allPassed = false;
                break;
            }
        }

        foreach ($dirs as $dir) {
            if (!$dir['passed']) {
                $allPassed = false;
                break;
            }
        }

        foreach ($files as $file) {
            if (!$file['passed']) {
                $allPassed = false;
                break;
            }
        }

        return [
            'php' => $php,
            'extensions' => $extensions,
            'directories' => $dirs,
            'files' => $files,
            'all_passed' => $allPassed
        ];
    }

    /**
     * Create database connection
     */
    public function createConnection($host, $port, $database, $username, $password)
    {
        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];

        return new PDO($dsn, $username, $password, $options);
    }

    /**
     * Test database connection
     */
    public function testDatabaseConnection($host, $port, $database, $username, $password)
    {
        try {
            $this->pdo = $this->createConnection($host, $port, $database, $username, $password);

            // Test query
            $this->pdo->query("SELECT 1");

            // Check for existing tables
            $stmt = $this->pdo->query("SHOW TABLES");
            $tableCount = $stmt->rowCount();

            return [
                'success' => true,
                'message' => 'Connection successful!',
                'table_count' => $tableCount,
                'has_tables' => $tableCount > 0
            ];
        } catch (PDOException $e) {
            $message = $e->getMessage();

            // Make error messages more user-friendly
            if (strpos($message, 'Unknown database') !== false) {
                $message = "Database '{$database}' does not exist. Please create it first.";
            } elseif (strpos($message, 'Access denied') !== false) {
                $message = "Access denied. Please check your username and password.";
            } elseif (strpos($message, 'Connection refused') !== false) {
                $message = "Connection refused. Please check your hostname and port.";
            }

            return [
                'success' => false,
                'message' => $message
            ];
        }
    }

    /**
     * Store database credentials in session
     */
    public function storeDbCredentials($host, $port, $database, $username, $password)
    {
        $_SESSION['db_config'] = [
            'host' => $host,
            'port' => $port,
            'database' => $database,
            'username' => $username,
            'password' => $password
        ];
    }

    /**
     * Get stored database credentials
     */
    public function getDbCredentials()
    {
        return $_SESSION['db_config'] ?? null;
    }

    /**
     * Import SQL file
     */
    public function importSqlFile($filename, $progressCallback = null)
    {
        $filepath = $this->basePath . '/' . $filename;

        if (!file_exists($filepath)) {
            throw new Exception("SQL file not found: {$filename}");
        }

        $db = $this->getDbCredentials();
        if (!$db) {
            throw new Exception("Database credentials not found in session");
        }

        $this->pdo = $this->createConnection(
            $db['host'], $db['port'], $db['database'], $db['username'], $db['password']
        );

        // Read SQL file
        $sql = file_get_contents($filepath);

        // Remove comments
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

        // Split into statements
        $statements = $this->splitSqlStatements($sql);

        $total = count($statements);
        $completed = 0;
        $errors = [];

        // Disable foreign key checks during import
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (empty($statement)) {
                continue;
            }

            try {
                $this->pdo->exec($statement);
                $completed++;

                if ($progressCallback) {
                    $progressCallback($completed, $total);
                }
            } catch (PDOException $e) {
                $errors[] = [
                    'error' => $e->getMessage(),
                    'statement' => substr($statement, 0, 100) . '...'
                ];
                // Continue with other statements
            }
        }

        // Re-enable foreign key checks
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

        return [
            'total' => $total,
            'completed' => $completed,
            'errors' => $errors,
            'success' => empty($errors)
        ];
    }

    /**
     * Split SQL into individual statements
     */
    private function splitSqlStatements($sql)
    {
        $statements = [];
        $currentStatement = '';
        $inString = false;
        $stringChar = '';
        $inDelimiterBlock = false;
        $delimiter = ';';

        $length = strlen($sql);
        $i = 0;

        while ($i < $length) {
            $char = $sql[$i];

            // Handle DELIMITER commands
            if (!$inString && strtoupper(substr($sql, $i, 9)) === 'DELIMITER') {
                $endOfLine = strpos($sql, "\n", $i);
                if ($endOfLine === false) $endOfLine = $length;
                $delimiterLine = trim(substr($sql, $i + 9, $endOfLine - $i - 9));

                if ($delimiterLine === ';') {
                    $inDelimiterBlock = false;
                    $delimiter = ';';
                } else {
                    $inDelimiterBlock = true;
                    $delimiter = $delimiterLine;
                }

                $i = $endOfLine + 1;
                continue;
            }

            // Handle string literals
            if (!$inString && ($char === '"' || $char === "'")) {
                $inString = true;
                $stringChar = $char;
                $currentStatement .= $char;
                $i++;
                continue;
            }

            if ($inString && $char === $stringChar) {
                // Check for escaped quote
                if ($i + 1 < $length && $sql[$i + 1] === $stringChar) {
                    $currentStatement .= $char . $sql[$i + 1];
                    $i += 2;
                    continue;
                }
                $inString = false;
                $currentStatement .= $char;
                $i++;
                continue;
            }

            // Handle escape characters in strings
            if ($inString && $char === '\\' && $i + 1 < $length) {
                $currentStatement .= $char . $sql[$i + 1];
                $i += 2;
                continue;
            }

            // Check for delimiter
            if (!$inString && substr($sql, $i, strlen($delimiter)) === $delimiter) {
                $statement = trim($currentStatement);
                if (!empty($statement)) {
                    $statements[] = $statement;
                }
                $currentStatement = '';
                $i += strlen($delimiter);
                continue;
            }

            $currentStatement .= $char;
            $i++;
        }

        // Add final statement if any
        $statement = trim($currentStatement);
        if (!empty($statement)) {
            $statements[] = $statement;
        }

        return $statements;
    }

    /**
     * Truncate transactional / customer-data tables carried over from the
     * development dump in crm_db.sql, so each fresh install starts clean.
     *
     * Seed/config tables (app_settings, sys_cnf, email_templates, billing_cycle,
     * currencies, plans, product catalog, servers, dom_registers, etc.) are
     * intentionally KEPT and are not listed below.
     *
     * Called right after crm_db.sql is imported (step 4), before the admin
     * account is created in step 5 — so admin_users is safely emptied here and
     * the buyer's admin is INSERTed fresh by updateAdminCredentials().
     *
     * To adjust what gets wiped, just add/remove a name in $tables — nothing
     * else needs to change. Non-existent tables are skipped, not fatal.
     *
     * @return array {truncated: string[], skipped: string[]}
     */
    public function truncateDataTables()
    {
        $db = $this->getDbCredentials();
        if (!$db) {
            throw new Exception("Database credentials not found in session");
        }

        $this->pdo = $this->createConnection(
            $db['host'], $db['port'], $db['database'], $db['username'], $db['password']
        );

        // Tables emptied on every fresh install.
        $tables = [
            // --- Customer & order data ---
            'companies', 'users',
            'orders', 'order_services', 'order_domains', 'order_licenses',
            'add_to_carts',
            // --- Billing data ---
            'invoices', 'invoice_items', 'invoice_txn',
            'payment_transactions', 'payment_refunds', 'dunning_log',
            // --- Support ---
            'tickets', 'ticket_replies',
            // --- Vendor content / catalog data ---
            'expenses', 'expense_types', 'expense_vendors',
            'announcements', 'software_releases',
            'promo_codes', 'promo_code_usage',
            'promo_code_customers', 'promo_code_products',
            // --- Hosting catalog & infrastructure ---
            'product_services', 'product_service_types',
            'product_service_groups', 'product_service_pricing',
            'servers', 'dom_registers', 'dom_extensions', 'dom_pricing',
            'ticket_depts',
            // --- Reseller / API ---
            'reseller_profiles', 'api_keys', 'api_request_logs',
            // --- Logs, history & queues ---
            'provisioning_logs', 'webhook_logs', 'cron_jobs',
            'admin_logins', 'user_logins', 'login_attempts', 'password_resets',
            'pending_executions',
            // --- Admin accounts (buyer's admin is created fresh in step 5) ---
            'admin_users',
        ];

        $truncated = [];
        $skipped = [];

        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

        foreach ($tables as $table) {
            try {
                // Only touch tables that actually exist in this database
                $stmt = $this->pdo->query("SHOW TABLES LIKE " . $this->pdo->quote($table));
                if ($stmt->rowCount() === 0) {
                    $skipped[] = $table;
                    continue;
                }
                $this->pdo->exec("TRUNCATE TABLE `{$table}`");
                $truncated[] = $table;
            } catch (PDOException $e) {
                $skipped[] = $table;
                $this->log("Truncate skipped for {$table}: " . $e->getMessage(), 'warning');
            }
        }

        // Reset invoice/order sequence counters so numbering starts clean
        // (first generated number will be 101).
        try {
            $this->pdo->exec("UPDATE `gen_numbers` SET `last_no` = 100");
        } catch (PDOException $e) {
            $this->log("gen_numbers reset failed: " . $e->getMessage(), 'warning');
        }

        // Sanitize credential-bearing config rows that must NOT be truncated
        // (the app expects the row to exist / to keep other config).
        // app_settings: clear the vendor's SMTP + reCAPTCHA secrets (buyer sets
        // their own in admin). Columns are NOT NULL varchar, so use ''.
        try {
            $this->pdo->exec("
                UPDATE `app_settings`
                SET `smtp_username`      = NULL,
                    `smtp_authkey`       = NULL,
                    `captcha_site_key`   = '',
                    `captcha_secret_key` = ''
            ");
        } catch (PDOException $e) {
            $this->log("app_settings sanitize failed: " . $e->getMessage(), 'warning');
        }

        // sys_cnf: blank the cron secret so buyers don't share the vendor's key
        // (a blank key denies HTTP cron access until the buyer sets their own).
        try {
            $this->pdo->exec("UPDATE `sys_cnf` SET `cnf_val` = '' WHERE `cnf_key` = 'cron_secret_key'");
        } catch (PDOException $e) {
            $this->log("cron_secret_key reset failed: " . $e->getMessage(), 'warning');
        }

        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

        $this->log('Data tables truncated: ' . implode(', ', $truncated), 'info');

        return ['truncated' => $truncated, 'skipped' => $skipped];
    }

    /**
     * Create .env file
     */
    public function createEnvFile($license = [])
    {
        $db = $this->getDbCredentials();
        if (!$db) {
            throw new Exception("Database credentials not found");
        }

        $template = $this->basePath . '/.env.example';
        $envFile = $this->basePath . '/.env';

        if (!file_exists($template)) {
            throw new Exception(".env.example not found");
        }

        $content = file_get_contents($template);

        // Replace database values
        $content = preg_replace('/^DB_HOSTNAME=.*$/m', 'DB_HOSTNAME=' . $db['host'], $content);
        $content = preg_replace('/^DB_PORT=.*$/m', 'DB_PORT=' . $db['port'], $content);
        $content = preg_replace('/^DB_DATABASE=.*$/m', 'DB_DATABASE=' . $db['database'], $content);
        $content = preg_replace('/^DB_USERNAME=.*$/m', 'DB_USERNAME=' . $db['username'], $content);
        $content = preg_replace('/^DB_PASSWORD=.*$/m', 'DB_PASSWORD=' . $db['password'], $content);

        // Software license (self-hosted phone-home). Every install ships as a
        // master copy pinned to the vendor server; the buyer's key is enforced
        // by the admin-login gate against LICENSE_SERVER_URL.
        $key = trim($license['license_key'] ?? '');

        $content = preg_replace('/^IS_LICENSE_MASTER=.*$/m', 'IS_LICENSE_MASTER=true', $content);
        $content = preg_replace('/^LICENSE_KEY=.*$/m', 'LICENSE_KEY=' . $key, $content);
        $content = preg_replace('/^LICENSE_SERVER_URL=.*$/m', 'LICENSE_SERVER_URL=' . self::LICENSE_SERVER_URL, $content);

        if (file_put_contents($envFile, $content) === false) {
            throw new Exception("Failed to create .env file. Check file permissions.");
        }

        return true;
    }

    /**
     * Verify a license key against the vendor's license server during install.
     * Best-effort: used by the wizard's "Verify" button so the customer can
     * confirm their key before finishing. Never blocks installation.
     *
     * @param string $key License key (WHMAZ-XXXXX-...)
     * @return array {success, status, plan_key, message}
     */
    public function verifyLicenseKey($key)
    {
        $key = trim($key);
        $serverUrl = rtrim(self::LICENSE_SERVER_URL, '/');

        if ($key === '') {
            return ['success' => false, 'message' => 'License key is required.'];
        }

        $ch = curl_init($serverUrl . '/license/verify');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'license_key' => $key,
                'domain'      => $this->detectSiteUrl(),
            ]),
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT      => 'WHMAZ-License-Client/1.0',
        ]);
        $body = curl_exec($ch);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($body === false || $body === '') {
            return ['success' => false, 'message' => 'Could not reach the license server. ' . $err];
        }

        $data = json_decode($body, true);
        if (!is_array($data) || !array_key_exists('valid', $data)) {
            return ['success' => false, 'message' => 'Unexpected response from the license server.'];
        }

        return [
            'success'  => (bool) $data['valid'],
            'status'   => $data['status'] ?? 'invalid',
            'plan_key' => $data['plan_key'] ?? null,
            'message'  => $data['message'] ?? ($data['valid'] ? 'License is valid.' : 'License is not valid.'),
        ];
    }

    /**
     * Update admin credentials
     */
    public function updateAdminCredentials($email, $password, $firstName = '', $lastName = '')
    {
        $db = $this->getDbCredentials();
        if (!$db) {
            throw new Exception("Database credentials not found");
        }

        $this->pdo = $this->createConnection(
            $db['host'], $db['port'], $db['database'], $db['username'], $db['password']
        );

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Use email as username (before @ part)
        $username = strstr($email, '@', true) ?: 'admin';

        // Upsert the buyer's admin as id=1. admin_users is emptied during the
        // import step (truncateDataTables), so INSERT creates it fresh; the
        // ON DUPLICATE clause keeps this safe if the row already exists.
        $stmt = $this->pdo->prepare("
            INSERT INTO admin_users
                (id, admin_role_id, first_name, last_name, username, password,
                 email, mobile, phone, support_depts, status, inserted_on, updated_on)
            VALUES (1, 1, ?, ?, ?, ?, ?, '0', '0', '1,2', 1, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                first_name    = VALUES(first_name),
                last_name     = VALUES(last_name),
                username      = VALUES(username),
                password      = VALUES(password),
                email         = VALUES(email),
                mobile        = VALUES(mobile),
                phone         = VALUES(phone),
                support_depts = VALUES(support_depts),
                status        = 1,
                updated_on    = NOW()
        ");

        $stmt->execute([$firstName, $lastName, $username, $hashedPassword, $email]);

        // Point admin notifications at the buyer's admin email (sys_cnf,
        // group NOTIFICATIONS). Insert the row if a dump is missing it.
        $check = $this->pdo->prepare("SELECT id FROM sys_cnf WHERE cnf_key = 'admin_notification_email' LIMIT 1");
        $check->execute();
        if ($check->fetch()) {
            $ns = $this->pdo->prepare(
                "UPDATE sys_cnf SET cnf_val = ?, updated_on = NOW() WHERE cnf_key = 'admin_notification_email'"
            );
            $ns->execute([$email]);
        } else {
            $ns = $this->pdo->prepare(
                "INSERT INTO sys_cnf (cnf_key, cnf_val, cnf_group, created_on, updated_on)
                 VALUES ('admin_notification_email', ?, 'NOTIFICATIONS', NOW(), NOW())"
            );
            $ns->execute([$email]);
        }

        return true;
    }

    /**
     * Store the buyer's license key in app_settings.
     *
     * license_auth = the raw key (as entered / stored in .env LICENSE_KEY),
     * license_hash = its SHA-256 for a tamper-evident local record. A master
     * install (no key) clears both columns.
     *
     * @param string $key License key ('' for master installs)
     * @return bool
     */
    public function storeLicenseInSettings($key)
    {
        $db = $this->getDbCredentials();
        if (!$db) {
            throw new Exception("Database credentials not found");
        }

        $this->pdo = $this->createConnection(
            $db['host'], $db['port'], $db['database'], $db['username'], $db['password']
        );

        $key = trim((string) $key);
        $auth = ($key === '') ? null : $key;
        $hash = ($key === '') ? null : hash('sha256', $key);

        $stmt = $this->pdo->prepare("
            UPDATE app_settings
            SET license_auth = ?,
                license_hash = ?,
                updated_on = NOW()
            WHERE id = 1
        ");
        $stmt->execute([$auth, $hash]);

        return true;
    }

    /**
     * Store the default nameservers in sys_cnf (group DNS). Rows
     * DefaultNameServer1..4 ship seeded; empty values are stored as NULL.
     * Robust against a dump missing the rows (inserts them if absent).
     *
     * @param array $nameservers [1 => 'ns1..', 2 => 'ns2..', 3 => '', 4 => '']
     * @return bool
     */
    public function updateNameservers($nameservers)
    {
        $db = $this->getDbCredentials();
        if (!$db) {
            throw new Exception("Database credentials not found");
        }

        $this->pdo = $this->createConnection(
            $db['host'], $db['port'], $db['database'], $db['username'], $db['password']
        );

        for ($i = 1; $i <= 4; $i++) {
            $key = 'DefaultNameServer' . $i;
            $val = trim((string) ($nameservers[$i] ?? ''));
            $val = ($val === '') ? null : $val;

            $check = $this->pdo->prepare("SELECT id FROM sys_cnf WHERE cnf_key = ? LIMIT 1");
            $check->execute([$key]);

            if ($check->fetch()) {
                $stmt = $this->pdo->prepare(
                    "UPDATE sys_cnf SET cnf_val = ?, updated_on = NOW() WHERE cnf_key = ?"
                );
                $stmt->execute([$val, $key]);
            } else {
                $stmt = $this->pdo->prepare(
                    "INSERT INTO sys_cnf (cnf_key, cnf_val, cnf_group, created_on, updated_on)
                     VALUES (?, ?, 'DNS', NOW(), NOW())"
                );
                $stmt->execute([$key, $val]);
            }
        }

        return true;
    }

    /**
     * Update site settings
     */
    public function updateSiteSettings($siteName, $siteUrl)
    {
        $db = $this->getDbCredentials();
        if (!$db) {
            throw new Exception("Database credentials not found");
        }

        $this->pdo = $this->createConnection(
            $db['host'], $db['port'], $db['database'], $db['username'], $db['password']
        );

        // Ensure URL has trailing slash
        $siteUrl = rtrim($siteUrl, '/') . '/';

        // Update app_settings (single row table with direct columns)
        $stmt = $this->pdo->prepare("
            UPDATE app_settings
            SET site_name = ?,
                site_desc = ?,
                company_name = ?,
                updated_on = NOW()
            WHERE id = 1
        ");
        $stmt->execute([$siteName, $siteName, $siteName]);

        return true;
    }

    /**
     * Create installation lock file
     */
    public function createLockFile()
    {
        $lockFile = $this->basePath . '/install/install.lock';
        $content = "Installation completed on " . date('Y-m-d H:i:s') . "\n";
        $content .= "Please delete the /install folder for security.";

        return file_put_contents($lockFile, $content) !== false;
    }

    /**
     * Delete install folder
     */
    public function deleteInstallFolder()
    {
        $installDir = $this->basePath . '/install';
        return $this->deleteDirectory($installDir);
    }

    /**
     * Recursively delete directory
     */
    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return true;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        return rmdir($dir);
    }

    /**
     * Detect site URL
     */
    public function detectSiteUrl()
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

        // Remove /install/index.php from path
        $basePath = dirname(dirname($scriptName));
        $basePath = ($basePath === '/' || $basePath === '\\') ? '' : $basePath;

        return $protocol . '://' . $host . $basePath . '/';
    }

    /**
     * Validate email format
     */
    public function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate password strength
     */
    public function validatePassword($password)
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long";
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Get current step from session
     */
    public function getCurrentStep()
    {
        return $_SESSION['install_step'] ?? 1;
    }

    /**
     * Set current step in session
     */
    public function setCurrentStep($step)
    {
        $_SESSION['install_step'] = $step;
    }

    /**
     * Clear installation session data
     */
    public function clearSession()
    {
        unset($_SESSION['install_step']);
        unset($_SESSION['db_config']);
        unset($_SESSION['csrf_token']);
    }

    /**
     * Get base path
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Log message
     */
    public function log($message, $type = 'info')
    {
        $logFile = $this->basePath . '/install/install.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$type}] {$message}\n";

        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}
