<?php
/**
 * Plesk XML API Helper Functions
 *
 * This helper provides functions for managing Plesk subscriptions via the XML API.
 * Mirrors cpanel_helper.php function signatures for consistent provisioning.
 *
 * Plesk API Documentation: https://docs.plesk.com/en-US/obsidian/api-rpc/
 *
 * Server info fields used:
 *   hostname  - Plesk server hostname
 *   username  - Plesk admin username (typically 'admin')
 *   access_hash - Plesk API key (double base64 encoded, same as cPanel convention)
 *   port      - Plesk API port (default 8443)
 *   is_secure - Always uses HTTPS
 */

defined('BASEPATH') OR exit('No direct script access allowed');

// ============================================================
// Core API Call
// ============================================================

/**
 * Make a Plesk XML API call
 *
 * @param array  $serverInfo Server information with hostname, username, access_hash
 * @param string $xmlPayload XML request body
 * @return array Response with 'success' boolean and 'data' (parsed XML) or 'error'
 */
if (!function_exists('plesk_api_call')) {
    function plesk_api_call($serverInfo, $xmlPayload) {
        if (empty($serverInfo) || empty($serverInfo['hostname']) || empty($serverInfo['username']) || empty($serverInfo['access_hash'])) {
            return array('success' => false, 'error' => 'Invalid server configuration');
        }

        $port = !empty($serverInfo['port']) ? intval($serverInfo['port']) : 8443;
        $url = "https://" . $serverInfo['hostname'] . ":" . $port . "/enterprise/control/agent.php";

        // Decode access hash (stored as double base64 encoded — same convention as cPanel)
        $apiKey = preg_replace("'(\r|\n)'", "", base64_decode(base64_decode($serverInfo['access_hash'])));

        $curl = curl_init();
        set_time_limit(120);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $xmlPayload);

        $headers = array(
            'Content-Type: text/xml',
            'HTTP_AUTH_LOGIN: ' . $serverInfo['username'],
            'HTTP_AUTH_PASSWD: ' . $apiKey,
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($curl);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($result === false) {
            log_message('error', 'Plesk API cURL error: ' . $curlError);
            return array('success' => false, 'error' => 'Failed to connect to server: ' . $curlError);
        }

        // Parse XML response
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($result);

        if ($xml === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            $errMsg = !empty($errors) ? $errors[0]->message : 'Unknown XML parse error';
            log_message('error', 'Plesk API XML parse error: ' . $errMsg);
            return array('success' => false, 'error' => 'Invalid response from server');
        }

        return array('success' => true, 'data' => $xml, 'raw' => $result);
    }
}

/**
 * Check Plesk API response for errors
 *
 * @param SimpleXMLElement $xml  Response XML
 * @param string           $path Dot-separated path to the result node (e.g. 'webspace.add.result')
 * @return array ['ok' => bool, 'error' => string|null, 'node' => SimpleXMLElement|null]
 */
if (!function_exists('plesk_check_response')) {
    function plesk_check_response($xml, $path) {
        $parts = explode('.', $path);
        $node = $xml;

        foreach ($parts as $part) {
            if (!isset($node->{$part})) {
                return array('ok' => false, 'error' => "Missing response node: $part", 'node' => null);
            }
            $node = $node->{$part};
        }

        if (isset($node->status) && (string)$node->status === 'error') {
            $errMsg = isset($node->errtext) ? (string)$node->errtext : (isset($node->errcode) ? 'Error code: ' . (string)$node->errcode : 'Unknown error');
            return array('ok' => false, 'error' => $errMsg, 'node' => $node);
        }

        return array('ok' => true, 'error' => null, 'node' => $node);
    }
}

// ============================================================
// Account (Webspace/Subscription) Management
// ============================================================

/**
 * Create a Plesk webspace (hosting subscription)
 *
 * @param array  $serverInfo Server information
 * @param string $domain     Domain name for the subscription
 * @param string $username   System username (FTP/SSH login)
 * @param string $password   Account password
 * @param string $plan       Service plan name
 * @param string $email      Contact email
 * @return array Response with success status and subscription details or error
 */
if (!function_exists('plesk_create_account')) {
    function plesk_create_account($serverInfo, $domain, $username, $password, $plan, $email) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <packet>
            <webspace>
                <add>
                    <gen_setup>
                        <name>' . htmlspecialchars($domain, ENT_XML1) . '</name>
                        <owner-login>' . htmlspecialchars($serverInfo['username'], ENT_XML1) . '</owner-login>
                        <htype>vrt_hst</htype>
                        <ip_address>' . htmlspecialchars($serverInfo['ip_addr'] ?? '', ENT_XML1) . '</ip_address>
                    </gen_setup>
                    <hosting>
                        <vrt_hst>
                            <property>
                                <name>ftp_login</name>
                                <value>' . htmlspecialchars($username, ENT_XML1) . '</value>
                            </property>
                            <property>
                                <name>ftp_password</name>
                                <value>' . htmlspecialchars($password, ENT_XML1) . '</value>
                            </property>
                            <ip_address>' . htmlspecialchars($serverInfo['ip_addr'] ?? '', ENT_XML1) . '</ip_address>
                        </vrt_hst>
                    </hosting>
                    <plan-name>' . htmlspecialchars($plan, ENT_XML1) . '</plan-name>
                </add>
            </webspace>
        </packet>';

        $response = plesk_api_call($serverInfo, $xml);

        if (!$response['success']) {
            log_message('error', 'Plesk create account failed for ' . $domain . ': ' . $response['error']);
            return $response;
        }

        $check = plesk_check_response($response['data'], 'webspace.add.result');
        if (!$check['ok']) {
            log_message('error', 'Plesk create account error for ' . $domain . ': ' . $check['error']);
            return array('success' => false, 'error' => $check['error'], 'raw_response' => $response['raw']);
        }

        $subscriptionId = isset($check['node']->id) ? (string)$check['node']->id : null;
        log_message('info', 'Plesk subscription created: ' . $domain . ' (ID: ' . $subscriptionId . ')');

        return array('success' => true, 'data' => array('subscription_id' => $subscriptionId));
    }
}

/**
 * Suspend a Plesk subscription
 *
 * @param array  $serverInfo Server information
 * @param string $domain     Domain name of the subscription
 * @param string $reason     Reason for suspension
 * @return array Response with success status
 */
if (!function_exists('plesk_suspend_account')) {
    function plesk_suspend_account($serverInfo, $domain, $reason = 'Account suspended by administrator') {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <packet>
            <webspace>
                <set>
                    <filter>
                        <name>' . htmlspecialchars($domain, ENT_XML1) . '</name>
                    </filter>
                    <values>
                        <gen_setup>
                            <status>16</status>
                        </gen_setup>
                    </values>
                </set>
            </webspace>
        </packet>';

        $response = plesk_api_call($serverInfo, $xml);

        if (!$response['success']) {
            log_message('error', 'Plesk suspend failed for ' . $domain . ': ' . $response['error']);
            return $response;
        }

        $check = plesk_check_response($response['data'], 'webspace.set.result');
        if (!$check['ok']) {
            log_message('error', 'Plesk suspend error for ' . $domain . ': ' . $check['error']);
            return array('success' => false, 'error' => $check['error']);
        }

        log_message('info', 'Plesk subscription suspended: ' . $domain);
        return array('success' => true);
    }
}

/**
 * Unsuspend a Plesk subscription
 *
 * @param array  $serverInfo Server information
 * @param string $domain     Domain name of the subscription
 * @return array Response with success status
 */
if (!function_exists('plesk_unsuspend_account')) {
    function plesk_unsuspend_account($serverInfo, $domain) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <packet>
            <webspace>
                <set>
                    <filter>
                        <name>' . htmlspecialchars($domain, ENT_XML1) . '</name>
                    </filter>
                    <values>
                        <gen_setup>
                            <status>0</status>
                        </gen_setup>
                    </values>
                </set>
            </webspace>
        </packet>';

        $response = plesk_api_call($serverInfo, $xml);

        if (!$response['success']) {
            log_message('error', 'Plesk unsuspend failed for ' . $domain . ': ' . $response['error']);
            return $response;
        }

        $check = plesk_check_response($response['data'], 'webspace.set.result');
        if (!$check['ok']) {
            log_message('error', 'Plesk unsuspend error for ' . $domain . ': ' . $check['error']);
            return array('success' => false, 'error' => $check['error']);
        }

        log_message('info', 'Plesk subscription unsuspended: ' . $domain);
        return array('success' => true);
    }
}

/**
 * Terminate (delete) a Plesk subscription
 *
 * @param array  $serverInfo Server information
 * @param string $domain     Domain name of the subscription
 * @return array Response with success status
 */
if (!function_exists('plesk_terminate_account')) {
    function plesk_terminate_account($serverInfo, $domain) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <packet>
            <webspace>
                <del>
                    <filter>
                        <name>' . htmlspecialchars($domain, ENT_XML1) . '</name>
                    </filter>
                </del>
            </webspace>
        </packet>';

        $response = plesk_api_call($serverInfo, $xml);

        if (!$response['success']) {
            log_message('error', 'Plesk terminate failed for ' . $domain . ': ' . $response['error']);
            return $response;
        }

        $check = plesk_check_response($response['data'], 'webspace.del.result');
        if (!$check['ok']) {
            log_message('error', 'Plesk terminate error for ' . $domain . ': ' . $check['error']);
            return array('success' => false, 'error' => $check['error']);
        }

        log_message('info', 'Plesk subscription terminated: ' . $domain);
        return array('success' => true);
    }
}

// ============================================================
// Account Info & Utilities
// ============================================================

/**
 * Get Plesk subscription info by domain
 *
 * @param array  $serverInfo Server information
 * @param string $domain     Domain name to look up
 * @return array Response with subscription details or error
 */
if (!function_exists('plesk_get_account_info')) {
    function plesk_get_account_info($serverInfo, $domain) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <packet>
            <webspace>
                <get>
                    <filter>
                        <name>' . htmlspecialchars($domain, ENT_XML1) . '</name>
                    </filter>
                    <dataset>
                        <gen_info/>
                        <hosting/>
                        <stat/>
                        <disk_usage/>
                    </dataset>
                </get>
            </webspace>
        </packet>';

        $response = plesk_api_call($serverInfo, $xml);

        if (!$response['success']) {
            return $response;
        }

        $check = plesk_check_response($response['data'], 'webspace.get.result');
        if (!$check['ok']) {
            return array('success' => false, 'error' => $check['error']);
        }

        return array('success' => true, 'data' => $check['node']);
    }
}

/**
 * Change Plesk subscription password (FTP/system user)
 *
 * @param array  $serverInfo Server information
 * @param string $domain     Domain name of the subscription
 * @param string $newPassword New password
 * @return array Response with success status
 */
if (!function_exists('plesk_change_password')) {
    function plesk_change_password($serverInfo, $domain, $newPassword) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <packet>
            <webspace>
                <set>
                    <filter>
                        <name>' . htmlspecialchars($domain, ENT_XML1) . '</name>
                    </filter>
                    <values>
                        <hosting>
                            <vrt_hst>
                                <property>
                                    <name>ftp_password</name>
                                    <value>' . htmlspecialchars($newPassword, ENT_XML1) . '</value>
                                </property>
                            </vrt_hst>
                        </hosting>
                    </values>
                </set>
            </webspace>
        </packet>';

        $response = plesk_api_call($serverInfo, $xml);

        if (!$response['success']) {
            log_message('error', 'Plesk change password failed for ' . $domain . ': ' . $response['error']);
            return $response;
        }

        $check = plesk_check_response($response['data'], 'webspace.set.result');
        if (!$check['ok']) {
            log_message('error', 'Plesk change password error for ' . $domain . ': ' . $check['error']);
            return array('success' => false, 'error' => $check['error']);
        }

        log_message('info', 'Plesk password changed for: ' . $domain);
        return array('success' => true);
    }
}

/**
 * Modify Plesk subscription plan
 *
 * @param array  $serverInfo Server information
 * @param string $domain     Domain name of the subscription
 * @param string $newPlan    New service plan name
 * @return array Response with success status
 */
if (!function_exists('plesk_modify_account')) {
    function plesk_modify_account($serverInfo, $domain, $newPlan) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <packet>
            <webspace>
                <switch-subscription>
                    <filter>
                        <name>' . htmlspecialchars($domain, ENT_XML1) . '</name>
                    </filter>
                    <plan-name>' . htmlspecialchars($newPlan, ENT_XML1) . '</plan-name>
                </switch-subscription>
            </webspace>
        </packet>';

        $response = plesk_api_call($serverInfo, $xml);

        if (!$response['success']) {
            log_message('error', 'Plesk modify account failed for ' . $domain . ': ' . $response['error']);
            return $response;
        }

        $check = plesk_check_response($response['data'], 'webspace.switch-subscription.result');
        if (!$check['ok']) {
            log_message('error', 'Plesk modify account error for ' . $domain . ': ' . $check['error']);
            return array('success' => false, 'error' => $check['error']);
        }

        log_message('info', 'Plesk subscription modified: ' . $domain . ' to plan: ' . $newPlan);
        return array('success' => true);
    }
}

// ============================================================
// Service Plans & Listing
// ============================================================

/**
 * List all service plans on a Plesk server
 *
 * @param array $serverInfo Server information
 * @return array Response with plan list or error
 */
if (!function_exists('plesk_list_packages')) {
    function plesk_list_packages($serverInfo) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <packet>
            <service-plan>
                <get>
                    <filter/>
                </get>
            </service-plan>
        </packet>';

        $response = plesk_api_call($serverInfo, $xml);

        if (!$response['success']) {
            return array('success' => false, 'error' => $response['error']);
        }

        $packages = array();

        if (isset($response['data']->{'service-plan'}->get->result)) {
            foreach ($response['data']->{'service-plan'}->get->result as $result) {
                if (isset($result->status) && (string)$result->status === 'error') {
                    continue;
                }

                $name = isset($result->name) ? (string)$result->name : '';
                if (empty($name)) continue;

                // Extract limits from the plan
                $limits = array();
                if (isset($result->limits)) {
                    foreach ($result->limits->limit as $limit) {
                        $limits[(string)$limit->name] = (string)$limit->value;
                    }
                }

                $packages[] = array(
                    'name'      => $name,
                    'id'        => isset($result->id) ? (string)$result->id : '',
                    'quota'     => isset($limits['disk_space']) ? round(intval($limits['disk_space']) / (1024 * 1024), 0) : 'unlimited',
                    'bwlimit'   => isset($limits['max_traffic']) ? round(intval($limits['max_traffic']) / (1024 * 1024), 0) : 'unlimited',
                    'maxsql'    => isset($limits['max_db']) ? $limits['max_db'] : 'unlimited',
                    'maxpop'    => isset($limits['max_box']) ? $limits['max_box'] : 'unlimited',
                    'maxsub'    => isset($limits['max_subdom']) ? $limits['max_subdom'] : 'unlimited',
                    'maxaddon'  => isset($limits['max_dom']) ? $limits['max_dom'] : 'unlimited',
                    'maxftp'    => isset($limits['max_webapps']) ? $limits['max_webapps'] : 'unlimited',
                    'maxpark'   => 'unlimited',
                    'maxlst'    => 'unlimited',
                    'hasshell'  => 'n',
                    'cgi'       => 'y',
                );
            }
        }

        usort($packages, function($a, $b) { return strcmp($a['name'], $b['name']); });

        return array('success' => true, 'packages' => $packages);
    }
}

/**
 * List all subscriptions (webspaces) on a Plesk server
 *
 * @param array  $serverInfo Server information
 * @param string $domain     Optional domain filter
 * @return array Response with list of subscriptions or error
 */
if (!function_exists('plesk_list_accounts')) {
    function plesk_list_accounts($serverInfo, $domain = null) {
        $filter = '';
        if (!empty($domain)) {
            $filter = '<name>' . htmlspecialchars($domain, ENT_XML1) . '</name>';
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <packet>
            <webspace>
                <get>
                    <filter>' . $filter . '</filter>
                    <dataset>
                        <gen_info/>
                    </dataset>
                </get>
            </webspace>
        </packet>';

        $response = plesk_api_call($serverInfo, $xml);

        if (!$response['success']) {
            return $response;
        }

        return array('success' => true, 'data' => $response['data']);
    }
}

/**
 * Get Plesk subscription usage statistics
 *
 * @param array  $serverInfo Server information
 * @param string $domain     Domain name
 * @return array Response with usage stats or error
 */
if (!function_exists('plesk_get_account_stats')) {
    function plesk_get_account_stats($serverInfo, $domain) {
        $info = plesk_get_account_info($serverInfo, $domain);

        if (!$info['success']) {
            return $info;
        }

        $node = $info['data'];
        $stats = array(
            'disk_used' => 0,
            'disk_limit' => 'unlimited',
            'disk_percent' => 0,
            'bandwidth_used' => 0,
            'bandwidth_limit' => 'unlimited',
            'bandwidth_percent' => 0,
            'email_accounts' => 0,
            'email_limit' => 'unlimited',
            'databases' => 0,
            'database_limit' => 'unlimited',
            'addon_domains' => 0,
            'addon_limit' => 'unlimited',
            'subdomains' => 0,
            'subdomain_limit' => 'unlimited',
            'last_sync' => date('Y-m-d H:i:s')
        );

        // Parse disk usage
        if (isset($node->data->stat)) {
            $stat = $node->data->stat;
            if (isset($stat->disk_space)) {
                $stats['disk_used'] = round(intval((string)$stat->disk_space) / (1024 * 1024), 2);
            }
            if (isset($stat->traffic)) {
                $stats['bandwidth_used'] = round(intval((string)$stat->traffic) / (1024 * 1024), 2);
            }
        }

        // Parse limits
        if (isset($node->data->gen_info)) {
            $gen = $node->data->gen_info;
            // Limits would come from the service plan — use plan info if available
        }

        return array('success' => true, 'stats' => $stats);
    }
}

/**
 * Generate FTP username for Plesk
 *
 * @param string $domain Domain name
 * @return string Generated username
 */
if (!function_exists('generate_plesk_username')) {
    function generate_plesk_username($domain) {
        $parts = explode('.', $domain);
        $name = $parts[0];

        if (strtolower($name) === 'www' && count($parts) > 2) {
            $name = $parts[1];
        }

        $name = preg_replace('/[^a-z0-9]/', '', strtolower($name));

        if (empty($name) || !ctype_alpha($name[0])) {
            $name = 'u' . $name;
        }

        // Plesk allows longer usernames than cPanel
        $base = substr($name, 0, 12);
        return $base . substr(str_shuffle('abcdefghjkmnpqrstuvwxyz'), 0, 3);
    }
}

/**
 * Send Plesk welcome email to customer
 *
 * @param string $toEmail         Customer email
 * @param string $customerName    Customer full name
 * @param string $domain          Domain name
 * @param string $ftpUsername     FTP username
 * @param string $ftpPassword    FTP password
 * @param string $serverHostname Server hostname
 * @return bool True if sent
 */
if (!function_exists('send_plesk_welcome_email')) {
    function send_plesk_welcome_email($toEmail, $customerName, $domain, $ftpUsername, $ftpPassword, $serverHostname) {
        $ci =& get_instance();
        $ci->load->library('email');
        $ci->load->model('Common_model');

        $sysConfig = $ci->Common_model->get_sys_config('email');

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

        $companyConfig = $ci->Common_model->get_sys_config('company');
        $companyName = isset($companyConfig['company_name']) ? $companyConfig['company_name']->cnf_val : 'Hosting Company';
        $fromEmail = isset($sysConfig['from_email']) ? $sysConfig['from_email']->cnf_val : 'noreply@example.com';
        $fromName = isset($sysConfig['from_name']) ? $sysConfig['from_name']->cnf_val : $companyName;

        $pleskUrl = "https://" . $serverHostname . ":8443";
        $webmailUrl = "https://webmail." . $domain;

        $subject = "Your Hosting Account Details for " . htmlspecialchars($domain, ENT_QUOTES, 'UTF-8');

        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #52BBE6; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .credentials { background-color: #fff; border: 1px solid #ddd; padding: 15px; margin: 20px 0; }
                .credentials table { width: 100%; border-collapse: collapse; }
                .credentials td { padding: 10px; border-bottom: 1px solid #eee; }
                .credentials td:first-child { font-weight: bold; width: 40%; }
                .button { display: inline-block; padding: 12px 24px; background-color: #52BBE6; color: white; text-decoration: none; border-radius: 4px; margin: 5px; }
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
                            <tr><td>FTP Username:</td><td><strong>" . htmlspecialchars($ftpUsername, ENT_QUOTES, 'UTF-8') . "</strong></td></tr>
                            <tr><td>FTP Password:</td><td><strong>" . htmlspecialchars($ftpPassword, ENT_QUOTES, 'UTF-8') . "</strong></td></tr>
                            <tr><td>Plesk Panel:</td><td><a href='" . htmlspecialchars($pleskUrl, ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($pleskUrl, ENT_QUOTES, 'UTF-8') . "</a></td></tr>
                            <tr><td>Webmail:</td><td><a href='" . htmlspecialchars($webmailUrl, ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($webmailUrl, ENT_QUOTES, 'UTF-8') . "</a></td></tr>
                        </table>
                    </div>

                    <div class='warning'>
                        <strong>Important:</strong> Please change your password after your first login for security purposes.
                    </div>

                    <p style='text-align: center;'>
                        <a href='" . htmlspecialchars($pleskUrl, ENT_QUOTES, 'UTF-8') . "' class='button'>Login to Plesk</a>
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
            log_message('info', 'Plesk welcome email sent to: ' . $toEmail . ' for domain: ' . $domain);
            return true;
        } else {
            log_message('error', 'Failed to send Plesk welcome email to: ' . $toEmail);
            return false;
        }
    }
}

?>
