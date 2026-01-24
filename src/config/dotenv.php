<?php
/**
 * Simple .env file loader for CodeIgniter 3
 * Loads environment variables from .env file into $_ENV and getenv()
 *
 * Note: This file is loaded early in index.php before BASEPATH is defined,
 * so we cannot use the standard BASEPATH security check here.
 */

if (!function_exists('load_dotenv')) {
    function load_dotenv($path = null) {
        if ($path === null) {
            // Default to root directory (two levels up from src/config)
            $path = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . '.env';
        }

        if (!file_exists($path)) {
            // .env file not found, skip loading
            return false;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse KEY=VALUE format
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove quotes if present
                $value = trim($value, '"\'');

                // Set environment variable
                if (!empty($key)) {
                    putenv("$key=$value");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }

        return true;
    }
}

if (!function_exists('env')) {
    /**
     * Get environment variable value
     *
     * @param string $key Environment variable key
     * @param mixed $default Default value if not found
     * @return mixed
     */
    function env($key, $default = null) {
        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        // Convert string booleans to actual booleans
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'null':
            case '(null)':
                return null;
            case 'empty':
            case '(empty)':
                return '';
        }

        return $value;
    }
}

// Load .env file automatically
load_dotenv();
