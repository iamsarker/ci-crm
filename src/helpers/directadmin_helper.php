<?php
/**
 * DirectAdmin API Helper Functions
 *
 * This helper provides functions for managing DirectAdmin accounts via the API.
 * Mirrors cpanel_helper.php / plesk_helper.php function signatures for consistent provisioning.
 *
 * DirectAdmin API Documentation: https://www.directadmin.com/api.php
 *
 * Server info fields used:
 *   hostname   - DirectAdmin server hostname
 *   username   - DirectAdmin admin/reseller username
 *   access_hash - DirectAdmin login key or password (double base64 encoded)
 *   port       - DirectAdmin port (default 2222)
 *   is_secure  - 1 = HTTPS, 0 = HTTP
 */

defined('BASEPATH') OR exit('No direct script access allowed');

// ============================================================
// Core API Call
// ============================================================

/**
 * Make a DirectAdmin API call
 *
 * @param array  $serverInfo Server information
 * @param string $command    API command (e.g., 'CMD_API_ACCOUNT_USER')
 * @param array  $params     POST parameters
 * @param string $method     HTTP method (GET or POST)
 * @return array Response with 'success' boolean and 'data' or 'error'
 */
if (!function_exists('da_api_call')) {
    function da_api_call($serverInfo, $command, $params = array(), $method = 'POST') {
        if (empty($serverInfo) || empty($serverInfo['hostname']) || empty($serverInfo['username']) || empty($serverInfo['access_hash'])) {
            return array('success' => false, 'error' => 'Invalid server configuration');
        }

        $port = !empty($serverInfo['port']) ? intval($serverInfo['port']) : 2222;
        $protocol = !empty($serverInfo['is_secure']) ? 'https' : 'http';
        $url = $protocol . "://" . $serverInfo['hostname'] . ":" . $port . "/" . $command;

        // Decode access hash (stored as double base64 encoded)
        $password = preg_replace("'(\r|\n)'", "", base64_decode(base64_decode($serverInfo['access_hash'])));

        $curl = curl_init();
        set_time_limit(120);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, $serverInfo['username'] . ':' . $password);
        curl_setopt($curl, CURLOPT_URL, $url);

        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
        } elseif (!empty($params)) {
            curl_setopt($curl, CURLOPT_URL, $url . '?' . http_build_query($params));
        }

        $result = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($result === false) {
            log_message('error', 'DirectAdmin API cURL error: ' . $curlError);
            return array('success' => false, 'error' => 'Failed to connect to server: ' . $curlError);
        }

        if ($httpCode === 401) {
            return array('success' => false, 'error' => 'Authentication failed - check admin credentials');
        }

        // DirectAdmin returns URL-encoded key=value pairs or JSON
        $data = array();
        $json = json_decode($result, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            $data = $json;
        } else {
            parse_str($result, $data);
        }

        // Check for error in response
        if (isset($data['error']) && $data['error'] == 1) {
            $errMsg = isset($data['text']) ? $data['text'] : (isset($data['details']) ? $data['details'] : 'Unknown error');
            return array('success' => false, 'error' => $errMsg, 'raw_response' => $data);
        }

        return array('success' => true, 'data' => $data, 'raw' => $result);
    }
}

// ============================================================
// Account Management
// ============================================================

/**
 * Create a DirectAdmin user account
 *
 * @param array  $serverInfo Server information
 * @param string $domain     Domain name
 * @param string $username   Account username
 * @param string $password   Account password
 * @param string $plan       Package name
 * @param string $email      Contact email
 * @return array Response with success status
 */
if (!function_exists('da_create_account')) {
    function da_create_account($serverInfo, $domain, $username, $password, $plan, $email) {
        $params = array(
            'action'   => 'create',
            'add'      => 'Submit',
            'username' => $username,
            'email'    => $email,
            'passwd'   => $password,
            'passwd2'  => $password,
            'domain'   => $domain,
            'package'  => $plan,
            'notify'   => 'no'
        );

        $response = da_api_call($serverInfo, 'CMD_API_ACCOUNT_USER', $params);

        if ($response['success']) {
            // DirectAdmin returns error=0 on success in the parsed data
            if (isset($response['data']['error']) && $response['data']['error'] == 0) {
                log_message('info', 'DirectAdmin account created: ' . $username . ' for domain: ' . $domain);
                return array('success' => true, 'data' => $response['data']);
            }
            // If no explicit error field but API call succeeded, treat as success
            if (!isset($response['data']['error'])) {
                log_message('info', 'DirectAdmin account created: ' . $username . ' for domain: ' . $domain);
                return array('success' => true, 'data' => $response['data']);
            }
        }

        $error = $response['error'] ?? 'Unknown error creating account';
        log_message('error', 'DirectAdmin create account failed for ' . $domain . ': ' . $error);
        return array('success' => false, 'error' => $error);
    }
}

/**
 * Suspend a DirectAdmin account
 *
 * @param array  $serverInfo Server information
 * @param string $username   Username to suspend
 * @param string $reason     Reason for suspension
 * @return array Response with success status
 */
if (!function_exists('da_suspend_account')) {
    function da_suspend_account($serverInfo, $username, $reason = 'Account suspended by administrator') {
        $params = array(
            'suspend' => 'Suspend',
            'select0' => $username
        );

        $response = da_api_call($serverInfo, 'CMD_API_SELECT_USERS', $params);

        if ($response['success']) {
            log_message('info', 'DirectAdmin account suspended: ' . $username);
            return array('success' => true);
        }

        log_message('error', 'DirectAdmin suspend failed for ' . $username . ': ' . ($response['error'] ?? ''));
        return $response;
    }
}

/**
 * Unsuspend a DirectAdmin account
 *
 * @param array  $serverInfo Server information
 * @param string $username   Username to unsuspend
 * @return array Response with success status
 */
if (!function_exists('da_unsuspend_account')) {
    function da_unsuspend_account($serverInfo, $username) {
        $params = array(
            'unsuspend' => 'Unsuspend',
            'select0'   => $username
        );

        $response = da_api_call($serverInfo, 'CMD_API_SELECT_USERS', $params);

        if ($response['success']) {
            log_message('info', 'DirectAdmin account unsuspended: ' . $username);
            return array('success' => true);
        }

        log_message('error', 'DirectAdmin unsuspend failed for ' . $username . ': ' . ($response['error'] ?? ''));
        return $response;
    }
}

/**
 * Terminate (delete) a DirectAdmin account
 *
 * @param array  $serverInfo Server information
 * @param string $username   Username to terminate
 * @return array Response with success status
 */
if (!function_exists('da_terminate_account')) {
    function da_terminate_account($serverInfo, $username) {
        $params = array(
            'confirmed' => 'Confirm',
            'delete'    => 'yes',
            'select0'   => $username
        );

        $response = da_api_call($serverInfo, 'CMD_API_SELECT_USERS', $params);

        if ($response['success']) {
            log_message('info', 'DirectAdmin account terminated: ' . $username);
            return array('success' => true);
        }

        log_message('error', 'DirectAdmin terminate failed for ' . $username . ': ' . ($response['error'] ?? ''));
        return $response;
    }
}

// ============================================================
// Account Info & Utilities
// ============================================================

/**
 * Get DirectAdmin account info
 *
 * @param array  $serverInfo Server information
 * @param string $username   Username to look up
 * @return array Response with account details or error
 */
if (!function_exists('da_get_account_info')) {
    function da_get_account_info($serverInfo, $username) {
        $params = array('user' => $username);

        $response = da_api_call($serverInfo, 'CMD_API_SHOW_USER_CONFIG', $params, 'GET');

        return $response;
    }
}

/**
 * Change DirectAdmin account password
 *
 * @param array  $serverInfo  Server information
 * @param string $username    Username
 * @param string $newPassword New password
 * @return array Response with success status
 */
if (!function_exists('da_change_password')) {
    function da_change_password($serverInfo, $username, $newPassword) {
        $params = array(
            'username' => $username,
            'passwd'   => $newPassword,
            'passwd2'  => $newPassword
        );

        $response = da_api_call($serverInfo, 'CMD_API_USER_PASSWD', $params);

        if ($response['success']) {
            log_message('info', 'DirectAdmin password changed for: ' . $username);
            return array('success' => true);
        }

        log_message('error', 'DirectAdmin change password failed for ' . $username . ': ' . ($response['error'] ?? ''));
        return $response;
    }
}

/**
 * Modify DirectAdmin account package
 *
 * @param array  $serverInfo Server information
 * @param string $username   Username
 * @param string $newPlan    New package name
 * @return array Response with success status
 */
if (!function_exists('da_modify_account')) {
    function da_modify_account($serverInfo, $username, $newPlan) {
        $params = array(
            'action'  => 'package',
            'user'    => $username,
            'package' => $newPlan
        );

        $response = da_api_call($serverInfo, 'CMD_API_MODIFY_USER', $params);

        if ($response['success']) {
            log_message('info', 'DirectAdmin account modified: ' . $username . ' to plan: ' . $newPlan);
            return array('success' => true);
        }

        log_message('error', 'DirectAdmin modify failed for ' . $username . ': ' . ($response['error'] ?? ''));
        return $response;
    }
}

// ============================================================
// Packages & Listing
// ============================================================

/**
 * List all packages on a DirectAdmin server
 *
 * @param array $serverInfo Server information
 * @return array Response with package list or error
 */
if (!function_exists('da_list_packages')) {
    function da_list_packages($serverInfo) {
        $response = da_api_call($serverInfo, 'CMD_API_PACKAGES_USER', array(), 'GET');

        if (!$response['success']) {
            return array('success' => false, 'error' => $response['error'] ?? 'Failed to fetch packages');
        }

        $packages = array();
        $packageNames = array();

        // DirectAdmin returns list[] = package_name format
        if (isset($response['data']['list']) && is_array($response['data']['list'])) {
            $packageNames = $response['data']['list'];
        } else {
            // Or it may return numbered keys: list[]=name
            foreach ($response['data'] as $key => $val) {
                if (strpos($key, 'list') === 0 && !empty($val)) {
                    $packageNames[] = $val;
                }
            }
        }

        // Get details for each package
        foreach ($packageNames as $pkgName) {
            $detail = da_api_call($serverInfo, 'CMD_API_PACKAGES_USER', array('package' => $pkgName), 'GET');

            $pkg = array(
                'name'     => $pkgName,
                'quota'    => 'unlimited',
                'bwlimit'  => 'unlimited',
                'maxftp'   => 'unlimited',
                'maxsql'   => 'unlimited',
                'maxpop'   => 'unlimited',
                'maxpark'  => 'unlimited',
                'maxaddon' => 'unlimited',
                'maxsub'   => 'unlimited',
                'maxlst'   => 'unlimited',
                'hasshell' => 'n',
                'cgi'      => 'y',
            );

            if ($detail['success'] && !empty($detail['data'])) {
                $d = $detail['data'];
                $pkg['quota']    = isset($d['quota']) && $d['quota'] != 'unlimited' ? intval($d['quota']) : 'unlimited';
                $pkg['bwlimit']  = isset($d['bandwidth']) && $d['bandwidth'] != 'unlimited' ? intval($d['bandwidth']) : 'unlimited';
                $pkg['maxftp']   = isset($d['ftp']) ? $d['ftp'] : 'unlimited';
                $pkg['maxsql']   = isset($d['mysql']) ? $d['mysql'] : 'unlimited';
                $pkg['maxpop']   = isset($d['nemails']) ? $d['nemails'] : 'unlimited';
                $pkg['maxpark']  = isset($d['vdomains']) ? $d['vdomains'] : 'unlimited';
                $pkg['maxaddon'] = isset($d['nsubdomains']) ? $d['nsubdomains'] : 'unlimited';
                $pkg['maxsub']   = isset($d['nsubdomains']) ? $d['nsubdomains'] : 'unlimited';
                $pkg['hasshell'] = (isset($d['ssh']) && $d['ssh'] === 'ON') ? 'y' : 'n';
                $pkg['cgi']      = (isset($d['cgi']) && $d['cgi'] === 'ON') ? 'y' : 'n';
            }

            $packages[] = $pkg;
        }

        usort($packages, function($a, $b) { return strcmp($a['name'], $b['name']); });

        return array('success' => true, 'packages' => $packages);
    }
}

/**
 * List all user accounts on a DirectAdmin server
 *
 * @param array $serverInfo Server information
 * @return array Response with list of accounts or error
 */
if (!function_exists('da_list_accounts')) {
    function da_list_accounts($serverInfo) {
        $response = da_api_call($serverInfo, 'CMD_API_SHOW_ALL_USERS', array(), 'GET');

        return $response;
    }
}

/**
 * Get DirectAdmin account usage statistics
 *
 * @param array  $serverInfo Server information
 * @param string $username   Username
 * @return array Response with usage stats or error
 */
if (!function_exists('da_get_account_stats')) {
    function da_get_account_stats($serverInfo, $username) {
        $info = da_get_account_info($serverInfo, $username);

        if (!$info['success']) {
            return $info;
        }

        $d = $info['data'];
        $stats = array(
            'disk_used'         => isset($d['disk_usage']) ? floatval($d['disk_usage']) : 0,
            'disk_limit'        => isset($d['quota']) && $d['quota'] != 'unlimited' ? floatval($d['quota']) : 'unlimited',
            'disk_percent'      => 0,
            'bandwidth_used'    => isset($d['bandwidth_usage']) ? floatval($d['bandwidth_usage']) : 0,
            'bandwidth_limit'   => isset($d['bandwidth']) && $d['bandwidth'] != 'unlimited' ? floatval($d['bandwidth']) : 'unlimited',
            'bandwidth_percent' => 0,
            'email_accounts'    => 0,
            'email_limit'       => isset($d['nemails']) ? $d['nemails'] : 'unlimited',
            'databases'         => 0,
            'database_limit'    => isset($d['mysql']) ? $d['mysql'] : 'unlimited',
            'addon_domains'     => 0,
            'addon_limit'       => isset($d['vdomains']) ? $d['vdomains'] : 'unlimited',
            'subdomains'        => 0,
            'subdomain_limit'   => isset($d['nsubdomains']) ? $d['nsubdomains'] : 'unlimited',
            'last_sync'         => date('Y-m-d H:i:s')
        );

        // Calculate percentages
        if ($stats['disk_limit'] !== 'unlimited' && $stats['disk_limit'] > 0) {
            $stats['disk_percent'] = min(100, round(($stats['disk_used'] / $stats['disk_limit']) * 100, 1));
        }
        if ($stats['bandwidth_limit'] !== 'unlimited' && $stats['bandwidth_limit'] > 0) {
            $stats['bandwidth_percent'] = min(100, round(($stats['bandwidth_used'] / $stats['bandwidth_limit']) * 100, 1));
        }

        return array('success' => true, 'stats' => $stats);
    }
}

/**
 * Generate a valid DirectAdmin username from domain
 *
 * DirectAdmin username rules:
 * - Lowercase letters and numbers
 * - Must start with a letter
 * - Max 10 characters
 *
 * @param string $domain Domain name
 * @return string Generated username
 */
if (!function_exists('generate_da_username')) {
    function generate_da_username($domain) {
        $parts = explode('.', $domain);
        $name = $parts[0];

        if (strtolower($name) === 'www' && count($parts) > 2) {
            $name = $parts[1];
        }

        $name = preg_replace('/[^a-z0-9]/', '', strtolower($name));

        if (empty($name) || !ctype_alpha($name[0])) {
            $name = 'u' . $name;
        }

        $base = substr($name, 0, 7);
        return $base . substr(str_shuffle('abcdefghjkmnpqrstuvwxyz'), 0, 3);
    }
}

/**
 * Send DirectAdmin welcome email to customer
 *
 * @param string $toEmail      Customer email
 * @param string $customerName Customer full name
 * @param string $domain       Domain name
 * @param string $daUsername    DirectAdmin username
 * @param string $daPassword   DirectAdmin password
 * @param string $serverHostname Server hostname
 * @return bool True if sent
 */
if (!function_exists('send_da_welcome_email')) {
    function send_da_welcome_email($toEmail, $customerName, $domain, $daUsername, $daPassword, $serverHostname) {
        $ci =& get_instance();
        $ci->load->library('email');
        $ci->load->model('Common_model');

        $sysConfig = $ci->Common_model->get_sys_config('email');

        $config = array(
            'protocol'    => 'smtp',
            'smtp_host'   => isset($sysConfig['smtp_host']) ? $sysConfig['smtp_host']->cnf_val : '',
            'smtp_port'   => isset($sysConfig['smtp_port']) ? $sysConfig['smtp_port']->cnf_val : 587,
            'smtp_user'   => isset($sysConfig['smtp_user']) ? $sysConfig['smtp_user']->cnf_val : '',
            'smtp_pass'   => isset($sysConfig['smtp_pass']) ? $sysConfig['smtp_pass']->cnf_val : '',
            'smtp_crypto' => isset($sysConfig['smtp_crypto']) ? $sysConfig['smtp_crypto']->cnf_val : 'tls',
            'mailtype'    => 'html',
            'charset'     => 'UTF-8',
            'wordwrap'    => TRUE
        );

        $ci->email->initialize($config);

        $companyConfig = $ci->Common_model->get_sys_config('company');
        $companyName = isset($companyConfig['company_name']) ? $companyConfig['company_name']->cnf_val : 'Hosting Company';
        $fromEmail = isset($sysConfig['from_email']) ? $sysConfig['from_email']->cnf_val : 'noreply@example.com';
        $fromName = isset($sysConfig['from_name']) ? $sysConfig['from_name']->cnf_val : $companyName;

        $port = 2222;
        $daUrl = "https://" . $serverHostname . ":" . $port;
        $webmailUrl = "https://webmail." . $domain;

        $subject = "Your Hosting Account Details for " . htmlspecialchars($domain, ENT_QUOTES, 'UTF-8');

        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #2d8c3c; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .credentials { background-color: #fff; border: 1px solid #ddd; padding: 15px; margin: 20px 0; }
                .credentials table { width: 100%; border-collapse: collapse; }
                .credentials td { padding: 10px; border-bottom: 1px solid #eee; }
                .credentials td:first-child { font-weight: bold; width: 40%; }
                .button { display: inline-block; padding: 12px 24px; background-color: #2d8c3c; color: white; text-decoration: none; border-radius: 4px; margin: 5px; }
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
                            <tr><td>Domain:</td><td>" . htmlspecialchars($domain, ENT_QUOTES, 'UTF-8') . "</td></tr>
                            <tr><td>Username:</td><td><strong>" . htmlspecialchars($daUsername, ENT_QUOTES, 'UTF-8') . "</strong></td></tr>
                            <tr><td>Password:</td><td><strong>" . htmlspecialchars($daPassword, ENT_QUOTES, 'UTF-8') . "</strong></td></tr>
                            <tr><td>Control Panel:</td><td><a href='" . htmlspecialchars($daUrl, ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($daUrl, ENT_QUOTES, 'UTF-8') . "</a></td></tr>
                            <tr><td>Webmail:</td><td><a href='" . htmlspecialchars($webmailUrl, ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($webmailUrl, ENT_QUOTES, 'UTF-8') . "</a></td></tr>
                        </table>
                    </div>

                    <div class='warning'>
                        <strong>Important:</strong> Please change your password after your first login for security purposes.
                    </div>

                    <p style='text-align: center;'>
                        <a href='" . htmlspecialchars($daUrl, ENT_QUOTES, 'UTF-8') . "' class='button'>Login to DirectAdmin</a>
                    </p>

                    <p>If you have any questions, please don't hesitate to contact our support team.</p>
                    <p>Best regards,<br>" . htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') . " Team</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " " . htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8') . ". All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";

        $ci->email->from($fromEmail, $fromName);
        $ci->email->to($toEmail);
        $ci->email->subject($subject);
        $ci->email->message($message);

        if ($ci->email->send()) {
            log_message('info', 'DirectAdmin welcome email sent to: ' . $toEmail . ' for domain: ' . $domain);
            return true;
        } else {
            log_message('error', 'Failed to send DirectAdmin welcome email to: ' . $toEmail);
            return false;
        }
    }
}

?>
