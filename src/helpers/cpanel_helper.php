<?php
/**
 * cPanel/WHM API Helper Functions
 *
 * This helper provides functions for managing cPanel accounts via WHM API.
 * Used for both manual management from admin panel and automatic provisioning.
 */

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Make a WHM API call
 *
 * @param array $serverInfo Server information with hostname, username, access_hash
 * @param string $endpoint API endpoint (e.g., 'createacct', 'suspendacct')
 * @param array $params Query parameters for the API call
 * @param string $method HTTP method (GET or POST)
 * @return array Response with 'success' boolean and 'data' or 'error' message
 */
if (!function_exists('whm_api_call')) {
    function whm_api_call($serverInfo, $endpoint, $params = array(), $method = 'GET') {
        // Validate server info
        if (empty($serverInfo) || empty($serverInfo['hostname']) || empty($serverInfo['username']) || empty($serverInfo['access_hash'])) {
            return array(
                'success' => false,
                'error' => 'Invalid server configuration'
            );
        }

        // Build API URL
        $url = "https://" . $serverInfo['hostname'] . ":2087/json-api/" . $endpoint;

        // Add API version
        $params['api.version'] = 1;

        if ($method === 'GET' && !empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        // Initialize cURL
        $curl = curl_init();

        // SSL options - disabled for self-signed certificates on WHM servers
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);

        // Decode access hash (stored as double base64 encoded)
        $accessHash = preg_replace("'(\r|\n)'", "", base64_decode(base64_decode($serverInfo['access_hash'])));

        // Set authorization header
        $headers = array(
            "Authorization: WHM " . $serverInfo['username'] . ":" . $accessHash
        );

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $url);

        // For POST requests
        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        // Execute request
        $result = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        // Handle cURL errors
        if ($result === false) {
            log_message('error', 'WHM API cURL error: ' . $curlError);
            return array(
                'success' => false,
                'error' => 'Failed to connect to server: ' . $curlError
            );
        }

        // Decode JSON response
        $response = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            log_message('error', 'WHM API JSON decode error: ' . json_last_error_msg());
            return array(
                'success' => false,
                'error' => 'Invalid response from server'
            );
        }

        // Check for API errors
        if (isset($response['metadata']['result']) && $response['metadata']['result'] == 0) {
            $errorMsg = isset($response['metadata']['reason']) ? $response['metadata']['reason'] : 'Unknown error';
            return array(
                'success' => false,
                'error' => $errorMsg,
                'raw_response' => $response
            );
        }

        // For older API format
        if (isset($response['result']) && is_array($response['result'])) {
            if (isset($response['result'][0]['status']) && $response['result'][0]['status'] == 0) {
                $errorMsg = isset($response['result'][0]['statusmsg']) ? $response['result'][0]['statusmsg'] : 'Unknown error';
                return array(
                    'success' => false,
                    'error' => $errorMsg,
                    'raw_response' => $response
                );
            }
        }

        return array(
            'success' => true,
            'data' => $response
        );
    }
}

/**
 * Create a cPanel account via WHM API
 *
 * @param array $serverInfo Server information
 * @param string $domain Domain name for the account
 * @param string $username cPanel username
 * @param string $password Account password
 * @param string $plan Hosting plan/package name (from cp_package)
 * @param string $email Contact email for the account
 * @return array Response with success status and account details or error
 */
if (!function_exists('whm_create_account')) {
    function whm_create_account($serverInfo, $domain, $username, $password, $plan, $email) {
        $params = array(
            'username' => $username,
            'domain' => $domain,
            'password' => $password,
            'contactemail' => $email,
            'plan' => $plan,
            'quota' => 0,  // Use package limits
            'bwlimit' => 0, // Use package limits
            'hasshell' => 0,
            'cgi' => 1,
            'frontpage' => 0,
            'cpmod' => 'paper_lantern',
            'maxftp' => 'unlimited',
            'maxsql' => 'unlimited',
            'maxpop' => 'unlimited',
            'maxlst' => 'unlimited',
            'maxsub' => 'unlimited',
            'maxpark' => 'unlimited',
            'maxaddon' => 'unlimited',
            'featurelist' => 'default',
            'dkim' => 1,
            'spf' => 1
        );

        $response = whm_api_call($serverInfo, 'createacct', $params);

        if ($response['success']) {
            log_message('info', 'cPanel account created successfully: ' . $username . ' for domain: ' . $domain);
        } else {
            log_message('error', 'Failed to create cPanel account: ' . $username . ' - ' . $response['error']);
        }

        return $response;
    }
}

/**
 * Suspend a cPanel account
 *
 * @param array $serverInfo Server information
 * @param string $username cPanel username to suspend
 * @param string $reason Reason for suspension
 * @return array Response with success status
 */
if (!function_exists('whm_suspend_account')) {
    function whm_suspend_account($serverInfo, $username, $reason = 'Account suspended by administrator') {
        $params = array(
            'user' => $username,
            'reason' => $reason
        );

        $response = whm_api_call($serverInfo, 'suspendacct', $params);

        if ($response['success']) {
            log_message('info', 'cPanel account suspended: ' . $username);
        } else {
            log_message('error', 'Failed to suspend cPanel account: ' . $username . ' - ' . $response['error']);
        }

        return $response;
    }
}

/**
 * Unsuspend a cPanel account
 *
 * @param array $serverInfo Server information
 * @param string $username cPanel username to unsuspend
 * @return array Response with success status
 */
if (!function_exists('whm_unsuspend_account')) {
    function whm_unsuspend_account($serverInfo, $username) {
        $params = array(
            'user' => $username
        );

        $response = whm_api_call($serverInfo, 'unsuspendacct', $params);

        if ($response['success']) {
            log_message('info', 'cPanel account unsuspended: ' . $username);
        } else {
            log_message('error', 'Failed to unsuspend cPanel account: ' . $username . ' - ' . $response['error']);
        }

        return $response;
    }
}

/**
 * Terminate (remove) a cPanel account
 *
 * @param array $serverInfo Server information
 * @param string $username cPanel username to terminate
 * @param bool $keepDns Keep DNS zone (default: false)
 * @return array Response with success status
 */
if (!function_exists('whm_terminate_account')) {
    function whm_terminate_account($serverInfo, $username, $keepDns = false) {
        $params = array(
            'user' => $username,
            'keepdns' => $keepDns ? 1 : 0
        );

        $response = whm_api_call($serverInfo, 'removeacct', $params);

        if ($response['success']) {
            log_message('info', 'cPanel account terminated: ' . $username);
        } else {
            log_message('error', 'Failed to terminate cPanel account: ' . $username . ' - ' . $response['error']);
        }

        return $response;
    }
}

/**
 * Get cPanel account information
 *
 * @param array $serverInfo Server information
 * @param string $username cPanel username to look up
 * @return array Response with account details or error
 */
if (!function_exists('whm_get_account_info')) {
    function whm_get_account_info($serverInfo, $username) {
        $params = array(
            'user' => $username
        );

        $response = whm_api_call($serverInfo, 'accountsummary', $params);

        return $response;
    }
}

/**
 * Check if a cPanel username is available
 *
 * @param array $serverInfo Server information
 * @param string $username Username to check
 * @return bool True if available, false if taken or error
 */
if (!function_exists('whm_check_username_available')) {
    function whm_check_username_available($serverInfo, $username) {
        $response = whm_api_call($serverInfo, 'verify_new_username', array('user' => $username));

        // If API call failed, assume username is not available (safer)
        if (!$response['success']) {
            return false;
        }

        // Check if username is valid/available
        if (isset($response['data']['data']) && $response['data']['data']['valid'] == 1) {
            return true;
        }

        return false;
    }
}

/**
 * Generate a valid cPanel username from domain
 *
 * cPanel username requirements:
 * - Must start with a letter
 * - Can only contain lowercase letters and numbers
 * - Maximum 8 characters (for compatibility with older systems)
 * - Must be unique on the server
 *
 * @param string $domain Domain name to generate username from
 * @param array $serverInfo Server info to check availability (optional)
 * @return string Generated username
 */
if (!function_exists('generate_cpanel_username')) {
    function generate_cpanel_username($domain, $serverInfo = null) {
        // Remove TLD and subdomains, get main part
        $parts = explode('.', $domain);
        $name = $parts[0];

        // Remove www if present
        if (strtolower($name) === 'www' && count($parts) > 2) {
            $name = $parts[1];
        }

        // Clean the string - only lowercase letters and numbers
        $name = preg_replace('/[^a-z0-9]/', '', strtolower($name));

        // Ensure it starts with a letter
        if (empty($name) || !ctype_alpha($name[0])) {
            $name = 'u' . $name;
        }

        // Truncate to 6 chars to leave room for random suffix
        $base = substr($name, 0, 6);

        // Add random suffix
        $username = $base . substr(str_shuffle('abcdefghjkmnpqrstuvwxyz'), 0, 2);

        // If server info provided, verify uniqueness
        if (!empty($serverInfo)) {
            $attempts = 0;
            $maxAttempts = 10;

            while (!whm_check_username_available($serverInfo, $username) && $attempts < $maxAttempts) {
                $username = $base . substr(str_shuffle('abcdefghjkmnpqrstuvwxyz23456789'), 0, 2);
                $attempts++;
            }
        }

        return $username;
    }
}

/**
 * Change cPanel account password
 *
 * @param array $serverInfo Server information
 * @param string $username cPanel username
 * @param string $newPassword New password
 * @return array Response with success status
 */
if (!function_exists('whm_change_password')) {
    function whm_change_password($serverInfo, $username, $newPassword) {
        $params = array(
            'user' => $username,
            'password' => $newPassword
        );

        $response = whm_api_call($serverInfo, 'passwd', $params);

        if ($response['success']) {
            log_message('info', 'cPanel password changed for: ' . $username);
        } else {
            log_message('error', 'Failed to change cPanel password for: ' . $username . ' - ' . $response['error']);
        }

        return $response;
    }
}

/**
 * List all cPanel accounts on a server
 *
 * @param array $serverInfo Server information
 * @param string $searchType Search type (domain, owner, user, ip, package)
 * @param string $search Search term
 * @return array Response with list of accounts or error
 */
if (!function_exists('whm_list_accounts')) {
    function whm_list_accounts($serverInfo, $searchType = null, $search = null) {
        $params = array();

        if (!empty($searchType) && !empty($search)) {
            $params['searchtype'] = $searchType;
            $params['search'] = $search;
        }

        $response = whm_api_call($serverInfo, 'listaccts', $params);

        return $response;
    }
}

/**
 * Send cPanel welcome email to customer
 *
 * @param string $toEmail Customer email address
 * @param string $customerName Customer full name
 * @param string $domain Domain name
 * @param string $cpanelUsername cPanel username
 * @param string $cpanelPassword cPanel password
 * @param string $serverHostname Server hostname
 * @return bool True if email sent successfully
 */
if (!function_exists('send_cpanel_welcome_email')) {
    function send_cpanel_welcome_email($toEmail, $customerName, $domain, $cpanelUsername, $cpanelPassword, $serverHostname) {
        $ci =& get_instance();
        $ci->load->library('email');

        // Get system config for email settings
        $ci->load->model('Common_model');
        $sysConfig = $ci->Common_model->get_sys_config('email');

        // Configure email
        $config = array(
            'protocol' => 'smtp',
            'smtp_host' => isset($sysConfig['smtp_host']) ? $sysConfig['smtp_host']->cnf_val : '',
            'smtp_port' => isset($sysConfig['smtp_port']) ? $sysConfig['smtp_port']->cnf_val : 587,
            'smtp_user' => isset($sysConfig['smtp_user']) ? $sysConfig['smtp_user']->cnf_val : '',
            'smtp_pass' => isset($sysConfig['smtp_pass']) ? $sysConfig['smtp_pass']->cnf_val : '',
            'smtp_crypto' => isset($sysConfig['smtp_crypto']) ? $sysConfig['smtp_crypto']->cnf_val : 'tls',
            'mailtype' => 'html',
            'charset' => 'UTF-8',
            'wordwrap' => TRUE
        );

        $ci->email->initialize($config);

        // Get company info
        $companyConfig = $ci->Common_model->get_sys_config('company');
        $companyName = isset($companyConfig['company_name']) ? $companyConfig['company_name']->cnf_val : 'Hosting Company';
        $fromEmail = isset($sysConfig['from_email']) ? $sysConfig['from_email']->cnf_val : 'noreply@example.com';
        $fromName = isset($sysConfig['from_name']) ? $sysConfig['from_name']->cnf_val : $companyName;

        // Build email content
        $cpanelUrl = "https://" . $serverHostname . ":2083";
        $webmailUrl = "https://" . $serverHostname . ":2096";

        $subject = "Your cPanel Account Details for " . htmlspecialchars($domain, ENT_QUOTES, 'UTF-8');

        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #0066cc; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .credentials { background-color: #fff; border: 1px solid #ddd; padding: 15px; margin: 20px 0; }
                .credentials table { width: 100%; border-collapse: collapse; }
                .credentials td { padding: 10px; border-bottom: 1px solid #eee; }
                .credentials td:first-child { font-weight: bold; width: 40%; }
                .button { display: inline-block; padding: 12px 24px; background-color: #0066cc; color: white; text-decoration: none; border-radius: 4px; margin: 5px; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .warning { background-color: #fff3cd; border: 1px solid #ffc107; padding: 10px; margin: 15px 0; border-radius: 4px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Welcome to " . htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') . "</h1>
                </div>
                <div class='content'>
                    <p>Dear " . htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8') . ",</p>

                    <p>Your hosting account has been successfully created. Below are your login credentials:</p>

                    <div class='credentials'>
                        <table>
                            <tr>
                                <td>Domain:</td>
                                <td>" . htmlspecialchars($domain, ENT_QUOTES, 'UTF-8') . "</td>
                            </tr>
                            <tr>
                                <td>cPanel Username:</td>
                                <td><strong>" . htmlspecialchars($cpanelUsername, ENT_QUOTES, 'UTF-8') . "</strong></td>
                            </tr>
                            <tr>
                                <td>cPanel Password:</td>
                                <td><strong>" . htmlspecialchars($cpanelPassword, ENT_QUOTES, 'UTF-8') . "</strong></td>
                            </tr>
                            <tr>
                                <td>cPanel URL:</td>
                                <td><a href='" . htmlspecialchars($cpanelUrl, ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($cpanelUrl, ENT_QUOTES, 'UTF-8') . "</a></td>
                            </tr>
                            <tr>
                                <td>Webmail URL:</td>
                                <td><a href='" . htmlspecialchars($webmailUrl, ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($webmailUrl, ENT_QUOTES, 'UTF-8') . "</a></td>
                            </tr>
                        </table>
                    </div>

                    <div class='warning'>
                        <strong>Important:</strong> Please change your password after your first login for security purposes.
                    </div>

                    <p style='text-align: center;'>
                        <a href='" . htmlspecialchars($cpanelUrl, ENT_QUOTES, 'UTF-8') . "' class='button'>Login to cPanel</a>
                        <a href='" . htmlspecialchars($webmailUrl, ENT_QUOTES, 'UTF-8') . "' class='button'>Access Webmail</a>
                    </p>

                    <p>If you have any questions, please don't hesitate to contact our support team.</p>

                    <p>Best regards,<br>" . htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') . " Team</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " " . htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') . ". All rights reserved.</p>
                    <p>This is an automated message. Please do not reply directly to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        // Send email
        $ci->email->from($fromEmail, $fromName);
        $ci->email->to($toEmail);
        $ci->email->subject($subject);
        $ci->email->message($message);

        if ($ci->email->send()) {
            log_message('info', 'cPanel welcome email sent to: ' . $toEmail . ' for domain: ' . $domain);
            return true;
        } else {
            log_message('error', 'Failed to send cPanel welcome email to: ' . $toEmail . ' - ' . $ci->email->print_debugger(array('headers')));
            return false;
        }
    }
}

/**
 * Get cPanel account disk usage
 *
 * @param array $serverInfo Server information
 * @param string $username cPanel username
 * @return array Response with disk usage info or error
 */
if (!function_exists('whm_get_disk_usage')) {
    function whm_get_disk_usage($serverInfo, $username) {
        $params = array(
            'user' => $username
        );

        return whm_api_call($serverInfo, 'showbw', $params);
    }
}

/**
 * Modify cPanel account (change package, etc.)
 *
 * @param array $serverInfo Server information
 * @param string $username cPanel username
 * @param string $newPlan New hosting plan/package name
 * @return array Response with success status
 */
if (!function_exists('whm_modify_account')) {
    function whm_modify_account($serverInfo, $username, $newPlan) {
        $params = array(
            'user' => $username,
            'pkg' => $newPlan
        );

        $response = whm_api_call($serverInfo, 'modifyacct', $params);

        if ($response['success']) {
            log_message('info', 'cPanel account modified: ' . $username . ' to plan: ' . $newPlan);
        } else {
            log_message('error', 'Failed to modify cPanel account: ' . $username . ' - ' . $response['error']);
        }

        return $response;
    }
}

?>
