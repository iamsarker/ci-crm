<?php
/**
 * WHMAZ Auto-Installer
 *
 * Main entry point for the installation wizard
 *
 * @package WHMAZ
 * @version 1.0.0
 */

// Error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the installer class
require_once __DIR__ . '/Install.php';

// Initialize installer
$installer = new Install();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // CSRF validation for all POST requests except AJAX
    if (!in_array($action, ['test_db', 'import_sql', 'delete_install'])) {
        if (!$installer->validateCsrf($_POST['csrf_token'] ?? '')) {
            die('Invalid CSRF token. Please refresh the page and try again.');
        }
    }

    // Handle AJAX actions
    if (in_array($action, ['test_db', 'import_sql', 'delete_install'])) {
        header('Content-Type: application/json');

        // CSRF validation for AJAX
        if (!$installer->validateCsrf($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            exit;
        }

        switch ($action) {
            case 'test_db':
                $result = $installer->testDatabaseConnection(
                    $_POST['db_host'] ?? 'localhost',
                    $_POST['db_port'] ?? '3306',
                    $_POST['db_name'] ?? '',
                    $_POST['db_user'] ?? '',
                    $_POST['db_pass'] ?? ''
                );
                echo json_encode($result);
                exit;

            case 'import_sql':
                try {
                    $file = $_POST['file'] ?? '';
                    if (!in_array($file, ['crm_db.sql', 'crm_db_views.sql'])) {
                        throw new Exception('Invalid file specified');
                    }

                    $result = $installer->importSqlFile($file);

                    if (empty($result['errors'])) {
                        echo json_encode([
                            'success' => true,
                            'message' => 'Import completed successfully',
                            'completed' => $result['completed'],
                            'total' => $result['total']
                        ]);
                    } else {
                        echo json_encode([
                            'success' => true,
                            'message' => 'Import completed with some warnings',
                            'completed' => $result['completed'],
                            'total' => $result['total'],
                            'errors' => $result['errors']
                        ]);
                    }
                } catch (Exception $e) {
                    $installer->log($e->getMessage(), 'error');
                    echo json_encode([
                        'success' => false,
                        'message' => $e->getMessage()
                    ]);
                }
                exit;

            case 'delete_install':
                try {
                    if ($installer->deleteInstallFolder()) {
                        echo json_encode([
                            'success' => true,
                            'message' => 'Install folder deleted successfully. Redirecting...'
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Could not delete install folder.'
                        ]);
                    }
                } catch (Exception $e) {
                    echo json_encode([
                        'success' => false,
                        'message' => $e->getMessage()
                    ]);
                }
                exit;
        }
    }
}

// Check if already installed
if ($installer->isInstalled()) {
    $currentStep = 0;
    $stepView = __DIR__ . '/views/already_installed.php';
    $csrfToken = $installer->getCsrfToken();
    include __DIR__ . '/views/layout.php';
    exit;
}

// Get current step
$currentStep = isset($_GET['step']) ? intval($_GET['step']) : 1;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postStep = intval($_POST['step'] ?? 1);
    $action = $_POST['action'] ?? '';

    switch ($postStep) {
        case 1:
            // Welcome page - just proceed to next step
            if ($action === 'next') {
                $installer->setCurrentStep(2);
                header('Location: index.php?step=2');
                exit;
            }
            break;

        case 2:
            // Requirements check
            if ($action === 'next') {
                $requirements = $installer->checkAllRequirements();
                if ($requirements['all_passed']) {
                    $installer->setCurrentStep(3);
                    header('Location: index.php?step=3');
                    exit;
                }
            } elseif ($action === 'recheck') {
                header('Location: index.php?step=2');
                exit;
            }
            break;

        case 3:
            // Database configuration
            if ($action === 'next') {
                $host = $_POST['db_host'] ?? 'localhost';
                $port = $_POST['db_port'] ?? '3306';
                $database = $_POST['db_name'] ?? '';
                $username = $_POST['db_user'] ?? '';
                $password = $_POST['db_pass'] ?? '';

                // Test connection
                $result = $installer->testDatabaseConnection($host, $port, $database, $username, $password);

                if ($result['success']) {
                    // Store credentials in session
                    $installer->storeDbCredentials($host, $port, $database, $username, $password);
                    $installer->setCurrentStep(4);
                    header('Location: index.php?step=4');
                    exit;
                } else {
                    $error = $result['message'];
                }
            }
            break;

        case 4:
            // Database import - proceed to settings
            if ($action === 'next') {
                $installer->setCurrentStep(5);
                header('Location: index.php?step=5');
                exit;
            }
            break;

        case 5:
            // Site settings and admin account
            if ($action === 'next') {
                $siteName = $_POST['site_name'] ?? 'WHMAZ';
                $siteUrl = $_POST['site_url'] ?? '';
                $adminFirstName = trim($_POST['admin_first_name'] ?? '');
                $adminLastName = trim($_POST['admin_last_name'] ?? '');
                $adminEmail = $_POST['admin_email'] ?? '';
                $adminPassword = $_POST['admin_password'] ?? '';
                $adminPasswordConfirm = $_POST['admin_password_confirm'] ?? '';

                // Validate
                $errors = [];

                if (empty($siteName)) {
                    $errors[] = 'Site name is required';
                }

                if (empty($siteUrl)) {
                    $errors[] = 'Site URL is required';
                }

                if (empty($adminFirstName)) {
                    $errors[] = 'First name is required';
                }

                if (empty($adminLastName)) {
                    $errors[] = 'Last name is required';
                }

                if (!$installer->validateEmail($adminEmail)) {
                    $errors[] = 'Valid email address is required';
                }

                $passwordValidation = $installer->validatePassword($adminPassword);
                if (!$passwordValidation['valid']) {
                    $errors = array_merge($errors, $passwordValidation['errors']);
                }

                if ($adminPassword !== $adminPasswordConfirm) {
                    $errors[] = 'Passwords do not match';
                }

                if (empty($errors)) {
                    try {
                        // Create .env file
                        $installer->createEnvFile();

                        // Update site settings
                        $installer->updateSiteSettings($siteName, $siteUrl);

                        // Update admin credentials
                        $installer->updateAdminCredentials($adminEmail, $adminPassword, $adminFirstName, $adminLastName);

                        // Create lock file
                        $installer->createLockFile();

                        // Store site URL for completion page
                        $_SESSION['site_url'] = $siteUrl;

                        // Log success
                        $installer->log('Installation completed successfully', 'info');

                        $installer->setCurrentStep(6);
                        header('Location: index.php?step=6');
                        exit;
                    } catch (Exception $e) {
                        $error = $e->getMessage();
                        $installer->log($e->getMessage(), 'error');
                    }
                } else {
                    $error = implode('. ', $errors);
                }
            }
            break;
    }
}

// Validate step access (can't skip steps)
$maxAllowedStep = $installer->getCurrentStep();
if ($currentStep > $maxAllowedStep) {
    $currentStep = $maxAllowedStep;
}

// Ensure step is within valid range
$currentStep = max(1, min(6, $currentStep));

// Determine which view to load
$stepViews = [
    1 => 'step1_welcome.php',
    2 => 'step2_requirements.php',
    3 => 'step3_database.php',
    4 => 'step4_import.php',
    5 => 'step5_settings.php',
    6 => 'step6_complete.php',
];

$stepView = __DIR__ . '/views/' . $stepViews[$currentStep];

// Get CSRF token
$csrfToken = $installer->getCsrfToken();

// Render the page
$error = $error ?? null;
$success = $success ?? null;

include __DIR__ . '/views/layout.php';
