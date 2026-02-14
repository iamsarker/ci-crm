<?php
/**
 * PHPUnit Bootstrap File
 *
 * This file sets up the testing environment for CodeIgniter 3
 */

// Set environment
define('ENVIRONMENT', 'testing');

// Path constants
define('BASEPATH', realpath(__DIR__ . '/../src/system/') . '/');
define('APPPATH', realpath(__DIR__ . '/../src/') . '/');
define('VIEWPATH', APPPATH . 'views/');
define('FCPATH', realpath(__DIR__ . '/../') . '/');

// Load CodeIgniter common functions
require_once BASEPATH . 'core/Common.php';

// Load constants
if (file_exists(APPPATH . 'config/constants.php')) {
    require_once APPPATH . 'config/constants.php';
}

// Composer autoload
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Load test helper functions
require_once __DIR__ . '/TestHelper.php';
