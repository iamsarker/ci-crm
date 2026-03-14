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
     * Create .env file
     */
    public function createEnvFile()
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

        if (file_put_contents($envFile, $content) === false) {
            throw new Exception("Failed to create .env file. Check file permissions.");
        }

        return true;
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

        // Update admin user
        $stmt = $this->pdo->prepare("
            UPDATE admin_users
            SET email = ?,
                mobile ='0',
                phone ='0',
                password = ?,
                username = ?,
                first_name = ?,
                last_name = ?,
                updated_on = NOW()
            WHERE id = 1
        ");

        $stmt->execute([$email, $hashedPassword, $username, $firstName, $lastName]);

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
