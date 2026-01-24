<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Centralized Error Handler
 *
 * Provides comprehensive error handling, logging, and user-friendly error messages
 * for the CI-CRM application.
 *
 * @package    CI-CRM
 * @subpackage Hooks
 * @category   Error Handling
 * @author     CI-CRM Security Team
 * @version    1.0.0
 */
class ErrorHandler
{
    /**
     * Log file directory
     */
    private $log_path;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->log_path = APPPATH . 'logs/';

        // Ensure logs directory exists
        if (!is_dir($this->log_path)) {
            mkdir($this->log_path, 0755, true);
        }

        // Register custom error and exception handlers
        $this->register_handlers();
    }

    /**
     * Register custom PHP error and exception handlers
     */
    private function register_handlers()
    {
        // Set custom error handler
        set_error_handler(array($this, 'handle_error'));

        // Set custom exception handler
        set_exception_handler(array($this, 'handle_exception'));

        // Register shutdown function for fatal errors
        register_shutdown_function(array($this, 'handle_shutdown'));
    }

    /**
     * Custom error handler
     *
     * @param int $severity Error severity level
     * @param string $message Error message
     * @param string $file File where error occurred
     * @param int $line Line number where error occurred
     * @return bool
     */
    public function handle_error($severity, $message, $file, $line)
    {
        // Don't handle suppressed errors (@)
        if (!(error_reporting() & $severity)) {
            return false;
        }

        // Map severity to error type
        $error_types = array(
            E_ERROR             => 'ERROR',
            E_WARNING           => 'WARNING',
            E_PARSE             => 'PARSE ERROR',
            E_NOTICE            => 'NOTICE',
            E_CORE_ERROR        => 'CORE ERROR',
            E_CORE_WARNING      => 'CORE WARNING',
            E_COMPILE_ERROR     => 'COMPILE ERROR',
            E_COMPILE_WARNING   => 'COMPILE WARNING',
            E_USER_ERROR        => 'USER ERROR',
            E_USER_WARNING      => 'USER WARNING',
            E_USER_NOTICE       => 'USER NOTICE',
            E_STRICT            => 'STRICT NOTICE',
            E_RECOVERABLE_ERROR => 'RECOVERABLE ERROR',
            E_DEPRECATED        => 'DEPRECATED',
            E_USER_DEPRECATED   => 'USER DEPRECATED'
        );

        $error_type = isset($error_types[$severity]) ? $error_types[$severity] : 'UNKNOWN ERROR';

        // Log the error
        $this->log_error($error_type, $message, $file, $line);

        // In production, don't display errors
        if (ENVIRONMENT === 'production') {
            return true; // Suppress error display
        }

        // In development, let PHP handle the error display
        return false;
    }

    /**
     * Custom exception handler
     *
     * @param Exception|Throwable $exception The exception object
     */
    public function handle_exception($exception)
    {
        // Log the exception
        $this->log_exception($exception);

        // Display user-friendly error page
        $this->display_error_page($exception);
    }

    /**
     * Handle fatal errors on shutdown
     */
    public function handle_shutdown()
    {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR))) {
            $this->log_error('FATAL ERROR', $error['message'], $error['file'], $error['line']);

            // Display generic error page in production
            if (ENVIRONMENT === 'production') {
                $this->display_generic_error();
            }
        }
    }

    /**
     * Log error to file
     *
     * @param string $type Error type
     * @param string $message Error message
     * @param string $file File path
     * @param int $line Line number
     */
    private function log_error($type, $message, $file, $line)
    {
        $log_file = $this->log_path . 'error-' . date('Y-m-d') . '.log';

        $log_message = sprintf(
            "[%s] %s: %s in %s on line %d\n",
            date('Y-m-d H:i:s'),
            $type,
            $message,
            $file,
            $line
        );

        // Add stack trace in development
        if (ENVIRONMENT === 'development') {
            $log_message .= "Stack trace:\n" . $this->get_stack_trace() . "\n";
        }

        // Add request information
        $log_message .= $this->get_request_info() . "\n";
        $log_message .= str_repeat('-', 80) . "\n";

        // Write to log file
        error_log($log_message, 3, $log_file);

        // SECURITY: Alert on critical errors in production
        if (ENVIRONMENT === 'production' && in_array($type, array('ERROR', 'FATAL ERROR', 'CORE ERROR'))) {
            $this->alert_critical_error($type, $message, $file, $line);
        }
    }

    /**
     * Log exception
     *
     * @param Exception|Throwable $exception
     */
    private function log_exception($exception)
    {
        $log_file = $this->log_path . 'exceptions-' . date('Y-m-d') . '.log';

        $log_message = sprintf(
            "[%s] %s: %s in %s on line %d\n",
            date('Y-m-d H:i:s'),
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );

        $log_message .= "Stack trace:\n" . $exception->getTraceAsString() . "\n";
        $log_message .= $this->get_request_info() . "\n";
        $log_message .= str_repeat('-', 80) . "\n";

        error_log($log_message, 3, $log_file);
    }

    /**
     * Get stack trace as string
     *
     * @return string
     */
    private function get_stack_trace()
    {
        $trace = debug_backtrace();
        $output = '';

        foreach ($trace as $index => $frame) {
            if ($index === 0) continue; // Skip this function

            $file = isset($frame['file']) ? $frame['file'] : 'unknown';
            $line = isset($frame['line']) ? $frame['line'] : 'unknown';
            $function = isset($frame['function']) ? $frame['function'] : 'unknown';
            $class = isset($frame['class']) ? $frame['class'] : '';
            $type = isset($frame['type']) ? $frame['type'] : '';

            $output .= sprintf("#%d %s(%s): %s%s%s()\n", $index - 1, $file, $line, $class, $type, $function);
        }

        return $output;
    }

    /**
     * Get request information
     *
     * @return string
     */
    private function get_request_info()
    {
        $info = "Request Info:\n";
        $info .= "  URI: " . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'N/A') . "\n";
        $info .= "  Method: " . (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'N/A') . "\n";
        $info .= "  IP: " . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'N/A') . "\n";
        $info .= "  User Agent: " . (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'N/A') . "\n";

        return $info;
    }

    /**
     * Display error page
     *
     * @param Exception|Throwable $exception
     */
    private function display_error_page($exception)
    {
        // Set HTTP status code
        http_response_code(500);

        if (ENVIRONMENT === 'production') {
            // Show generic error in production
            $this->display_generic_error();
        } else {
            // Show detailed error in development
            $this->display_detailed_error($exception);
        }

        exit(1);
    }

    /**
     * Display generic error page (production)
     */
    private function display_generic_error()
    {
        echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Error</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .error-container { background: white; border-radius: 10px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); max-width: 600px; width: 100%; padding: 40px; text-align: center; }
        .error-icon { font-size: 64px; margin-bottom: 20px; }
        h1 { color: #333; font-size: 28px; margin-bottom: 10px; }
        p { color: #666; font-size: 16px; line-height: 1.6; margin-bottom: 20px; }
        .error-code { background: #f5f5f5; border-radius: 5px; padding: 10px; font-family: monospace; font-size: 14px; color: #e74c3c; margin: 20px 0; }
        .actions { margin-top: 30px; }
        .btn { display: inline-block; background: #667eea; color: white; padding: 12px 30px; border-radius: 5px; text-decoration: none; font-weight: 500; transition: background 0.3s; }
        .btn:hover { background: #5568d3; }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1>Oops! Something Went Wrong</h1>
        <p>We apologize for the inconvenience. Our system encountered an unexpected error.</p>
        <div class="error-code">Error Reference: ' . date('YmdHis') . '-' . mt_rand(1000, 9999) . '</div>
        <p>Our team has been notified and is working to resolve the issue. Please try again in a few moments.</p>
        <div class="actions">
            <a href="javascript:history.back()" class="btn">Go Back</a>
            <a href="/" class="btn" style="background: #6c757d; margin-left: 10px;">Home Page</a>
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Display detailed error page (development)
     *
     * @param Exception|Throwable $exception
     */
    private function display_detailed_error($exception)
    {
        echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exception Details</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Courier New", monospace; background: #1a1a1a; color: #f0f0f0; padding: 20px; }
        .exception-container { background: #2d2d2d; border-left: 4px solid #e74c3c; padding: 20px; margin-bottom: 20px; border-radius: 4px; }
        h1 { color: #e74c3c; font-size: 24px; margin-bottom: 10px; }
        .exception-type { color: #3498db; font-size: 18px; margin-bottom: 20px; }
        .section { background: #1a1a1a; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .section-title { color: #f39c12; font-weight: bold; margin-bottom: 10px; font-size: 16px; }
        .file-line { color: #95a5a6; font-size: 14px; margin: 5px 0; }
        .trace { background: #1a1a1a; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .trace-line { color: #7f8c8d; font-size: 13px; padding: 2px 0; }
        pre { white-space: pre-wrap; word-wrap: break-word; }
    </style>
</head>
<body>
    <div class="exception-container">
        <h1>🛑 Exception Occurred</h1>
        <div class="exception-type">' . htmlspecialchars(get_class($exception), ENT_QUOTES, 'UTF-8') . '</div>

        <div class="section">
            <div class="section-title">Message:</div>
            <pre>' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>
        </div>

        <div class="section">
            <div class="section-title">Location:</div>
            <div class="file-line">
                File: ' . htmlspecialchars($exception->getFile(), ENT_QUOTES, 'UTF-8') . '<br>
                Line: ' . $exception->getLine() . '
            </div>
        </div>

        <div class="section">
            <div class="section-title">Stack Trace:</div>
            <div class="trace"><pre>' . htmlspecialchars($exception->getTraceAsString(), ENT_QUOTES, 'UTF-8') . '</pre></div>
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Send alert for critical errors (production only)
     *
     * @param string $type Error type
     * @param string $message Error message
     * @param string $file File path
     * @param int $line Line number
     */
    private function alert_critical_error($type, $message, $file, $line)
    {
        // SECURITY: Log critical errors to special file for monitoring
        $critical_log = $this->log_path . 'critical-errors-' . date('Y-m-d') . '.log';

        $alert_message = sprintf(
            "[%s] CRITICAL %s: %s in %s:%d\n",
            date('Y-m-d H:i:s'),
            $type,
            $message,
            $file,
            $line
        );

        $alert_message .= $this->get_request_info() . "\n";
        $alert_message .= str_repeat('=', 80) . "\n";

        error_log($alert_message, 3, $critical_log);

        // TODO: In production, you may want to send email/SMS alerts
        // Example: mail('admin@example.com', 'Critical Error Alert', $alert_message);
    }

    /**
     * Log database error
     *
     * @param string $operation Database operation
     * @param string $query SQL query
     * @param string $error Error message
     */
    public static function log_database_error($operation, $query, $error)
    {
        $log_path = APPPATH . 'logs/';
        $log_file = $log_path . 'database-errors-' . date('Y-m-d') . '.log';

        $log_message = sprintf(
            "[%s] Database Error\nOperation: %s\nQuery: %s\nError: %s\n%s\n",
            date('Y-m-d H:i:s'),
            $operation,
            $query,
            $error,
            str_repeat('-', 80)
        );

        error_log($log_message, 3, $log_file);
    }
}
