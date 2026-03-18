<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Domain Helper
 *
 * Helper functions for domain registrar API calls
 * Supports multiple registrars: ResellerClub/Resell.biz, Enom, Namecheap, etc.
 */

// ============================================
// GENERIC API FUNCTIONS
// ============================================

/**
 * Make a GET request to registrar API
 *
 * @param string $url Full API URL with parameters
 * @param array $headers Optional headers
 * @return array Response with 'success', 'data', 'error'
 */
function domain_api_get($url, $headers = array())
{
    $maxRetries = 3;
    $retryDelay = 5; // seconds

    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            log_message('error', 'Domain API GET error: ' . $error . ' - URL: ' . $url);
            return array('success' => false, 'error' => 'cURL error: ' . $error, 'data' => null);
        }

        // Retry on 403 (Cloudflare block) or 429 (rate limit)
        if (in_array($httpCode, array(403, 429)) && $attempt < $maxRetries) {
            log_message('info', 'Domain API GET HTTP ' . $httpCode . ' (attempt ' . $attempt . '/' . $maxRetries . '), retrying in ' . $retryDelay . 's...');
            sleep($retryDelay);
            $retryDelay *= 2; // exponential backoff
            continue;
        }

        $data = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return array('success' => true, 'data' => $data, 'error' => null, 'http_code' => $httpCode);
        } else {
            log_message('error', 'Domain API GET HTTP ' . $httpCode . ' (attempt ' . $attempt . '/' . $maxRetries . '): ' . $response);
            return array('success' => false, 'data' => $data, 'error' => 'HTTP ' . $httpCode, 'http_code' => $httpCode);
        }
    }

    return array('success' => false, 'data' => null, 'error' => 'Max retries exceeded', 'http_code' => 0);
}

/**
 * Make a POST request to registrar API
 *
 * @param string $url API URL
 * @param array $params POST parameters
 * @param array $headers Optional headers
 * @return array Response with 'success', 'data', 'error'
 */
function domain_api_post($url, $params = array(), $headers = array())
{
    $maxRetries = 3;
    $retryDelay = 5; // seconds

    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            log_message('error', 'Domain API POST error: ' . $error . ' - URL: ' . $url);
            return array('success' => false, 'error' => 'cURL error: ' . $error, 'data' => null);
        }

        // Retry on 403 (Cloudflare block) or 429 (rate limit)
        if (in_array($httpCode, array(403, 429)) && $attempt < $maxRetries) {
            log_message('info', 'Domain API POST HTTP ' . $httpCode . ' (attempt ' . $attempt . '/' . $maxRetries . '), retrying in ' . $retryDelay . 's...');
            sleep($retryDelay);
            $retryDelay *= 2; // exponential backoff
            continue;
        }

        $data = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return array('success' => true, 'data' => $data, 'error' => null, 'http_code' => $httpCode);
        } else {
            log_message('error', 'Domain API POST HTTP ' . $httpCode . ' (attempt ' . $attempt . '/' . $maxRetries . '): ' . $response);
            return array('success' => false, 'data' => $data, 'error' => 'HTTP ' . $httpCode, 'http_code' => $httpCode);
        }
    }

    return array('success' => false, 'data' => null, 'error' => 'Max retries exceeded', 'http_code' => 0);
}

/**
 * Make a POST request to domain API with raw post data
 * Used when we need to send array parameters like ns[]
 */
function domain_api_post_raw($url, $postData, $headers = array())
{
    $maxRetries = 3;
    $retryDelay = 5; // seconds

    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        // Set Content-Type for form data
        $defaultHeaders = array('Content-Type: application/x-www-form-urlencoded');
        $allHeaders = array_merge($defaultHeaders, $headers);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            log_message('error', 'Domain API POST error: ' . $error . ' - URL: ' . $url);
            return array('success' => false, 'error' => 'cURL error: ' . $error, 'data' => null);
        }

        // Retry on 403 (Cloudflare block) or 429 (rate limit)
        if (in_array($httpCode, array(403, 429)) && $attempt < $maxRetries) {
            log_message('info', 'Domain API POST RAW HTTP ' . $httpCode . ' (attempt ' . $attempt . '/' . $maxRetries . '), retrying in ' . $retryDelay . 's...');
            sleep($retryDelay);
            $retryDelay *= 2;
            continue;
        }

        $data = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return array('success' => true, 'data' => $data, 'error' => null, 'http_code' => $httpCode);
        } else {
            log_message('error', 'Domain API POST HTTP ' . $httpCode . ' (attempt ' . $attempt . '/' . $maxRetries . '): ' . $response);
            return array('success' => false, 'data' => $data, 'error' => 'HTTP ' . $httpCode, 'http_code' => $httpCode);
        }
    }

    return array('success' => false, 'data' => null, 'error' => 'Max retries exceeded', 'http_code' => 0);
}

// ============================================
// RESELLERCLUB / RESELL.BIZ API FUNCTIONS
// ============================================

/**
 * Register a domain via ResellerClub/Resell.biz API
 *
 * @param array $registrar Registrar config (api_base_url, auth_userid, auth_apikey)
 * @param string $domain Full domain name (e.g., example.com)
 * @param int $years Registration period in years
 * @param array $contact Contact details (customer_id or contact info)
 * @param array $nameservers Array of nameservers
 * @return array Result with 'success', 'order_id', 'error'
 */
function resellerclub_register_domain($registrar, $domain, $years, $contact, $nameservers = array())
{
    $apiUrl = rtrim($registrar['api_base_url'], '/') . '/api/domains/register.json';

    // Debug: Log the API URL and registrar config
    log_message('debug', 'DOMAIN_DEBUG - API URL: ' . $apiUrl);
    log_message('debug', 'DOMAIN_DEBUG - Domain: ' . $domain);

    // Build parameters - ResellerClub expects full domain name (e.g., "example.com")
    $params = array(
        'auth-userid' => $registrar['auth_userid'],
        'api-key' => $registrar['auth_apikey'],
        'domain-name' => $domain,  // Full domain name including TLD
        'years' => $years,
        'customer-id' => $contact['customer_id'],
        'reg-contact-id' => $contact['reg_contact_id'],
        'admin-contact-id' => $contact['admin_contact_id'],
        'tech-contact-id' => $contact['tech_contact_id'],
        'billing-contact-id' => $contact['billing_contact_id'],
        'invoice-option' => 'NoInvoice',
        'protect-privacy' => 'false'
    );

    // Add nameservers - ResellerClub expects 'ns' as array
    $nsArray = array();
    if (!empty($nameservers)) {
        $nsArray = $nameservers;
    } else {
        // Use default nameservers from registrar
        if (!empty($registrar['def_ns1'])) $nsArray[] = $registrar['def_ns1'];
        if (!empty($registrar['def_ns2'])) $nsArray[] = $registrar['def_ns2'];
        if (!empty($registrar['def_ns3'])) $nsArray[] = $registrar['def_ns3'];
        if (!empty($registrar['def_ns4'])) $nsArray[] = $registrar['def_ns4'];
    }

    // Log nameservers for debugging
    log_message('debug', 'Domain registration nameservers: ' . json_encode($nsArray));

    // Build the POST data with ns[] array format
    $postData = http_build_query($params);
    foreach ($nsArray as $ns) {
        $postData .= '&ns=' . urlencode($ns);
    }

    // Debug: Log the POST data (mask API key)
    $debugPostData = preg_replace('/api-key=[^&]+/', 'api-key=***MASKED***', $postData);
    log_message('error', 'DOMAIN_DEBUG - POST data: ' . $debugPostData);

    $response = domain_api_post_raw($apiUrl, $postData);

    if ($response['success'] && !empty($response['data']['entityid'])) {
        return array(
            'success' => true,
            'order_id' => $response['data']['entityid'],
            'status' => $response['data']['actionstatus'] ?? 'Success',
            'error' => null
        );
    } else {
        $errorMsg = 'Registration failed';
        if (isset($response['data']['message'])) {
            $errorMsg = $response['data']['message'];
        } elseif (isset($response['data']['error'])) {
            $errorMsg = $response['data']['error'];
        }
        return array('success' => false, 'order_id' => null, 'error' => $errorMsg);
    }
}

/**
 * Transfer a domain via ResellerClub/Resell.biz API
 *
 * @param array $registrar Registrar config
 * @param string $domain Full domain name
 * @param string $eppCode EPP/Auth code
 * @param array $contact Contact details
 * @param array $nameservers Nameservers (optional)
 * @return array Result with 'success', 'order_id', 'error'
 */
function resellerclub_transfer_domain($registrar, $domain, $eppCode, $contact, $nameservers = array())
{
    $apiUrl = rtrim($registrar['api_base_url'], '/') . '/api/domains/transfer.json';

    // ResellerClub expects full domain name (e.g., "example.com")
    $params = array(
        'auth-userid' => $registrar['auth_userid'],
        'api-key' => $registrar['auth_apikey'],
        'domain-name' => $domain,  // Full domain name including TLD
        'auth-code' => $eppCode,
        'customer-id' => $contact['customer_id'],
        'reg-contact-id' => $contact['reg_contact_id'],
        'admin-contact-id' => $contact['admin_contact_id'],
        'tech-contact-id' => $contact['tech_contact_id'],
        'billing-contact-id' => $contact['billing_contact_id'],
        'invoice-option' => 'NoInvoice',
        'protect-privacy' => 'false'
    );

    // Add nameservers if provided
    if (!empty($nameservers)) {
        foreach ($nameservers as $i => $ns) {
            $params['ns' . ($i + 1)] = $ns;
        }
    }

    $response = domain_api_post($apiUrl, $params);

    if ($response['success'] && !empty($response['data']['entityid'])) {
        return array(
            'success' => true,
            'order_id' => $response['data']['entityid'],
            'status' => $response['data']['actionstatus'] ?? 'Success',
            'error' => null
        );
    } else {
        $errorMsg = 'Transfer failed';
        if (isset($response['data']['message'])) {
            $errorMsg = $response['data']['message'];
        } elseif (isset($response['data']['error'])) {
            $errorMsg = $response['data']['error'];
        }
        return array('success' => false, 'order_id' => null, 'error' => $errorMsg);
    }
}

/**
 * Renew a domain via ResellerClub/Resell.biz API
 *
 * @param array $registrar Registrar config
 * @param string $domain Full domain name
 * @param int $years Renewal period in years
 * @param string $currentExpDate Current expiration date (UNIX timestamp or date string)
 * @param int $orderId Registrar order ID (from original registration)
 * @return array Result with 'success', 'new_expiry', 'error'
 */
function resellerclub_renew_domain($registrar, $domain, $years, $currentExpDate, $orderId)
{
    $apiUrl = rtrim($registrar['api_base_url'], '/') . '/api/domains/renew.json';

    // Convert date to timestamp if needed
    if (!is_numeric($currentExpDate)) {
        $currentExpDate = strtotime($currentExpDate);
    }

    $params = array(
        'auth-userid' => $registrar['auth_userid'],
        'api-key' => $registrar['auth_apikey'],
        'order-id' => $orderId,
        'years' => $years,
        'exp-date' => $currentExpDate,
        'auto-renew' => 'false',
        'invoice-option' => 'NoInvoice',
        'discount-amount' => '0.0'
    );

    log_message('info', 'ResellerClub renew domain API call - domain: ' . $domain . ', order-id: ' . $orderId . ', years: ' . $years . ', exp-date: ' . $currentExpDate);

    $response = domain_api_post($apiUrl, $params);

    log_message('info', 'ResellerClub renew domain API response: ' . json_encode($response));

    if ($response['success'] && isset($response['data']['actionstatus'])) {
        $newExpiry = date('Y-m-d', strtotime("+{$years} years", $currentExpDate));
        return array(
            'success' => true,
            'new_expiry' => $newExpiry,
            'status' => $response['data']['actionstatus'],
            'error' => null
        );
    } else {
        $errorMsg = 'Renewal failed';
        if (isset($response['data']['message'])) {
            $errorMsg = $response['data']['message'];
        } elseif (isset($response['data']['error'])) {
            $errorMsg = $response['data']['error'];
        } elseif (isset($response['data']['status']) && $response['data']['status'] === 'ERROR') {
            $errorMsg = $response['data']['message'] ?? $response['data']['error'] ?? json_encode($response['data']);
        } elseif (is_string($response['data'])) {
            $errorMsg = $response['data'];
        } elseif (empty($response['data']) && !empty($response['error'])) {
            $errorMsg = $response['error']; // e.g. "HTTP 403"
        }
        log_message('error', 'ResellerClub renew failed for ' . $domain . ': ' . $errorMsg . ' | Full response: ' . json_encode($response));
        return array('success' => false, 'new_expiry' => null, 'error' => $errorMsg);
    }
}

/**
 * Get or create customer at ResellerClub
 *
 * @param array $registrar Registrar config
 * @param array $customerInfo Customer details (email, name, address, etc.)
 * @return array Result with 'success', 'customer_id', 'error'
 */
function resellerclub_get_or_create_customer($registrar, $customerInfo)
{
    // First, try to get existing customer by email using details API
    $detailsUrl = rtrim($registrar['api_base_url'], '/') . '/api/customers/details.json';
    $detailsParams = array(
        'auth-userid' => $registrar['auth_userid'],
        'api-key' => $registrar['auth_apikey'],
        'username' => $customerInfo['email']
    );

    $detailsResponse = domain_api_get($detailsUrl . '?' . http_build_query($detailsParams));

    // Check if customer exists (API returns customer data with customerid)
    if ($detailsResponse['success'] && !empty($detailsResponse['data'])) {
        $data = $detailsResponse['data'];

        // Check various possible response formats
        if (isset($data['customerid'])) {
            log_message('info', 'Found existing customer: ' . $customerInfo['email'] . ' with ID: ' . $data['customerid']);
            return array(
                'success' => true,
                'customer_id' => $data['customerid'],
                'existing' => true,
                'error' => null
            );
        }
    }

    // Also try search API as fallback
    $searchUrl = rtrim($registrar['api_base_url'], '/') . '/api/customers/search.json';
    $searchParams = array(
        'auth-userid' => $registrar['auth_userid'],
        'api-key' => $registrar['auth_apikey'],
        'username' => $customerInfo['email'],
        'no-of-records' => 10,
        'page-no' => 1
    );

    $searchResponse = domain_api_get($searchUrl . '?' . http_build_query($searchParams));
    log_message('debug', 'Customer search response: ' . json_encode($searchResponse));

    if ($searchResponse['success'] && !empty($searchResponse['data']) && is_array($searchResponse['data'])) {
        // Check for recsindb (records count) - if > 0, customer exists
        if (isset($searchResponse['data']['recsindb']) && $searchResponse['data']['recsindb'] > 0) {
            // Find the customer ID in the response
            foreach ($searchResponse['data'] as $key => $value) {
                if (is_array($value) && isset($value['customer.customerid'])) {
                    log_message('info', 'Found existing customer via search: ' . $customerInfo['email'] . ' with ID: ' . $value['customer.customerid']);
                    return array(
                        'success' => true,
                        'customer_id' => $value['customer.customerid'],
                        'existing' => true,
                        'error' => null
                    );
                }
            }
        }

        // Old format check
        $firstKey = array_key_first($searchResponse['data']);
        if (isset($searchResponse['data'][$firstKey]['customer.customerid'])) {
            return array(
                'success' => true,
                'customer_id' => $searchResponse['data'][$firstKey]['customer.customerid'],
                'existing' => true,
                'error' => null
            );
        }
    }

    // Create new customer
    $createUrl = rtrim($registrar['api_base_url'], '/') . '/api/customers/signup.json';
    $createParams = array(
        'auth-userid' => $registrar['auth_userid'],
        'api-key' => $registrar['auth_apikey'],
        'username' => $customerInfo['email'],
        'passwd' => generate_secure_password(12, true),
        'name' => $customerInfo['name'],
        'company' => !empty($customerInfo['company']) ? $customerInfo['company'] : 'N/A',
        'address-line-1' => !empty($customerInfo['address1']) ? $customerInfo['address1'] : 'N/A',
        'city' => !empty($customerInfo['city']) ? $customerInfo['city'] : 'N/A',
        'state' => !empty($customerInfo['state']) ? $customerInfo['state'] : 'N/A',
        'country' => !empty($customerInfo['country']) ? $customerInfo['country'] : 'US',
        'zipcode' => !empty($customerInfo['zipcode']) ? $customerInfo['zipcode'] : '00000',
        'phone-cc' => !empty($customerInfo['phone_cc']) ? $customerInfo['phone_cc'] : '1',
        'phone' => !empty($customerInfo['phone']) ? $customerInfo['phone'] : '0000000000',
        'lang-pref' => 'en'
    );

    $createResponse = domain_api_post($createUrl, $createParams);

    if ($createResponse['success'] && is_numeric($createResponse['data'])) {
        return array(
            'success' => true,
            'customer_id' => $createResponse['data'],
            'error' => null
        );
    } else {
        // Extract error message from various API response formats
        $errorMsg = 'Failed to create customer';
        if (isset($createResponse['data']['message'])) {
            $errorMsg = $createResponse['data']['message'];
        } elseif (isset($createResponse['data']['error'])) {
            $errorMsg = $createResponse['data']['error'];
        } elseif (isset($createResponse['data']['status']) && $createResponse['data']['status'] == 'ERROR') {
            $errorMsg = $createResponse['data']['message'] ?? $createResponse['data']['error'] ?? 'Unknown error';
        } elseif (is_string($createResponse['data'])) {
            $errorMsg = $createResponse['data'];
        } elseif (isset($createResponse['error'])) {
            $errorMsg = $createResponse['error'];
        }
        log_message('error', 'ResellerClub customer creation failed: ' . $errorMsg . ' | Response: ' . json_encode($createResponse));
        return array('success' => false, 'customer_id' => null, 'error' => $errorMsg);
    }
}

/**
 * Create contact at ResellerClub for domain registration
 *
 * @param array $registrar Registrar config
 * @param int $customerId Customer ID
 * @param array $contactInfo Contact details
 * @param string $type Contact type (Contact, CoopContact, UkContact, etc.)
 * @return array Result with 'success', 'contact_id', 'error'
 */
function resellerclub_create_contact($registrar, $customerId, $contactInfo, $type = 'Contact')
{
    $apiUrl = rtrim($registrar['api_base_url'], '/') . '/api/contacts/add.json';

    $params = array(
        'auth-userid' => $registrar['auth_userid'],
        'api-key' => $registrar['auth_apikey'],
        'name' => $contactInfo['name'],
        'company' => !empty($contactInfo['company']) ? $contactInfo['company'] : 'N/A',
        'email' => $contactInfo['email'],
        'address-line-1' => !empty($contactInfo['address1']) ? $contactInfo['address1'] : 'N/A',
        'city' => !empty($contactInfo['city']) ? $contactInfo['city'] : 'N/A',
        'state' => !empty($contactInfo['state']) ? $contactInfo['state'] : 'N/A',
        'country' => !empty($contactInfo['country']) ? $contactInfo['country'] : 'US',
        'zipcode' => !empty($contactInfo['zipcode']) ? $contactInfo['zipcode'] : '00000',
        'phone-cc' => !empty($contactInfo['phone_cc']) ? $contactInfo['phone_cc'] : '1',
        'phone' => !empty($contactInfo['phone']) ? preg_replace('/[^0-9]/', '', $contactInfo['phone']) : '0000000000',
        'customer-id' => $customerId,
        'type' => $type
    );

    $response = domain_api_post($apiUrl, $params);

    if ($response['success'] && is_numeric($response['data'])) {
        return array(
            'success' => true,
            'contact_id' => $response['data'],
            'error' => null
        );
    } else {
        $errorMsg = 'Failed to create contact';
        if (isset($response['data']['message'])) {
            $errorMsg = $response['data']['message'];
        }
        return array('success' => false, 'contact_id' => null, 'error' => $errorMsg);
    }
}

/**
 * Get default contacts for a customer at ResellerClub
 *
 * @param array $registrar Registrar config
 * @param int $customerId Customer ID
 * @param string $type Contact type
 * @return array Result with 'success', 'contact_id', 'error'
 */
function resellerclub_get_default_contact($registrar, $customerId, $type = 'Contact')
{
    $apiUrl = rtrim($registrar['api_base_url'], '/') . '/api/contacts/default.json';

    $params = array(
        'auth-userid' => $registrar['auth_userid'],
        'api-key' => $registrar['auth_apikey'],
        'customer-id' => $customerId,
        'type' => $type
    );

    $response = domain_api_get($apiUrl . '?' . http_build_query($params));

    if ($response['success'] && is_numeric($response['data'])) {
        return array(
            'success' => true,
            'contact_id' => $response['data'],
            'error' => null
        );
    }

    return array('success' => false, 'contact_id' => null, 'error' => 'No default contact found');
}

// ============================================
// GENERIC REGISTRAR DISPATCHER
// ============================================

/**
 * Register domain using appropriate registrar API
 *
 * @param array $registrar Registrar config with 'platform' field
 * @param string $domain Domain name
 * @param int $years Years
 * @param array $contact Contact info
 * @param array $nameservers Nameservers
 * @return array Result
 */
function registrar_register_domain($registrar, $domain, $years, $contact, $nameservers = array())
{
    $platform = strtolower($registrar['platform'] ?? 'resellerclub');

    switch ($platform) {
        case 'resellerclub':
        case 'resellbiz':
        case 'resell.biz':
        case 'stargate':
            return resellerclub_register_domain($registrar, $domain, $years, $contact, $nameservers);

        case 'namecheap':
            return namecheap_register_domain($registrar, $domain, $years, $contact, $nameservers);

        default:
            log_message('error', 'Unsupported registrar platform: ' . $platform);
            return array('success' => false, 'error' => 'Unsupported registrar platform: ' . $platform);
    }
}

/**
 * Transfer domain using appropriate registrar API
 */
function registrar_transfer_domain($registrar, $domain, $eppCode, $contact, $nameservers = array())
{
    $platform = strtolower($registrar['platform'] ?? 'resellerclub');

    switch ($platform) {
        case 'resellerclub':
        case 'resellbiz':
        case 'resell.biz':
        case 'stargate':
            return resellerclub_transfer_domain($registrar, $domain, $eppCode, $contact, $nameservers);

        case 'namecheap':
            return namecheap_transfer_domain($registrar, $domain, $eppCode, $contact, $nameservers);

        default:
            log_message('error', 'Unsupported registrar platform: ' . $platform);
            return array('success' => false, 'error' => 'Unsupported registrar platform: ' . $platform);
    }
}

/**
 * Renew domain using appropriate registrar API
 */
function registrar_renew_domain($registrar, $domain, $years, $currentExpDate, $orderId)
{
    $platform = strtolower($registrar['platform'] ?? 'resellerclub');

    switch ($platform) {
        case 'resellerclub':
        case 'resellbiz':
        case 'resell.biz':
        case 'stargate':
            return resellerclub_renew_domain($registrar, $domain, $years, $currentExpDate, $orderId);

        case 'namecheap':
            return namecheap_renew_domain($registrar, $domain, $years, $currentExpDate, $orderId);

        default:
            log_message('error', 'Unsupported registrar platform: ' . $platform);
            return array('success' => false, 'error' => 'Unsupported registrar platform: ' . $platform);
    }
}

/**
 * Get domain expiry date from registrar API
 * Used to check if domain was already renewed manually at registrar
 *
 * @param array $registrar Registrar config
 * @param string $domain Domain name (e.g. example.com)
 * @param string $orderId Domain order ID at registrar
 * @return array|null Array with 'date' (Y-m-d) and 'timestamp' (Unix), or null if unable to fetch
 */
function registrar_get_domain_expiry($registrar, $domain, $orderId)
{
    $platform = strtolower($registrar['platform'] ?? 'resellerclub');

    switch ($platform) {
        case 'resellerclub':
        case 'resellbiz':
        case 'resell.biz':
        case 'stargate':
            return resellerclub_get_domain_expiry($registrar, $orderId);

        case 'namecheap':
            return namecheap_get_domain_expiry($registrar, $domain);

        default:
            log_message('error', 'registrar_get_domain_expiry: Unsupported platform: ' . $platform);
            return null;
    }
}

/**
 * Get domain expiry from ResellerClub/Resell.biz API
 *
 * @param array $registrar Registrar config
 * @param string $orderId Domain order ID
 * @return array|null Array with 'date' (Y-m-d) and 'timestamp' (Unix), or null
 */
function resellerclub_get_domain_expiry($registrar, $orderId)
{
    $apiUrl = rtrim($registrar['api_base_url'], '/') . '/api/domains/details.json';

    $params = array(
        'auth-userid' => $registrar['auth_userid'],
        'api-key' => $registrar['auth_apikey'],
        'order-id' => $orderId,
        'options' => 'OrderDetails'
    );

    $url = $apiUrl . '?' . http_build_query($params);
    $response = domain_api_get($url);

    if ($response['success'] && !empty($response['data'])) {
        $data = $response['data'];
        // ResellerClub returns endtime as Unix timestamp
        if (!empty($data['endtime'])) {
            $rawTimestamp = intval($data['endtime']);
            $expiry = date('Y-m-d', $rawTimestamp);
            log_message('info', 'ResellerClub domain expiry for order #' . $orderId . ': ' . $expiry . ' (timestamp: ' . $rawTimestamp . ')');
            return array('date' => $expiry, 'timestamp' => $rawTimestamp);
        }
    }

    log_message('error', 'Failed to fetch domain expiry from ResellerClub for order #' . $orderId . ': ' . json_encode($response));
    return null;
}

/**
 * Get domain expiry from Namecheap API
 *
 * @param array $registrar Registrar config
 * @param string $domain Domain name (e.g. example.com)
 * @return array|null Array with 'date' (Y-m-d) and 'timestamp' (Unix), or null
 */
function namecheap_get_domain_expiry($registrar, $domain)
{
    $parts = explode('.', $domain, 2);
    if (count($parts) < 2) {
        return null;
    }

    $params = array(
        'DomainName' => $domain
    );

    $response = namecheap_api_request($registrar, 'namecheap.domains.getInfo', $params);

    if ($response['success'] && !empty($response['data'])) {
        $data = $response['data'];
        // Namecheap returns DomainGetInfoResult with DomainDetails.ExpiredDate
        if (isset($data['CommandResponse']['DomainGetInfoResult']['DomainDetails']['ExpiredDate'])) {
            $expDateStr = $data['CommandResponse']['DomainGetInfoResult']['DomainDetails']['ExpiredDate'];
            $rawTimestamp = strtotime($expDateStr);
            $expiry = date('Y-m-d', $rawTimestamp);
            log_message('info', 'Namecheap domain expiry for ' . $domain . ': ' . $expiry . ' (timestamp: ' . $rawTimestamp . ')');
            return array('date' => $expiry, 'timestamp' => $rawTimestamp);
        }
    }

    log_message('error', 'Failed to fetch domain expiry from Namecheap for ' . $domain . ': ' . json_encode($response));
    return null;
}

/**
 * Get domain order ID from registrar by domain name
 * Used to fetch missing domain_order_id for renewal
 *
 * @param array $registrar Registrar config
 * @param string $domain Domain name (e.g. example.com)
 * @return string|null Order ID or null if not found
 */
function registrar_get_domain_orderid($registrar, $domain)
{
    $platform = strtolower($registrar['platform'] ?? 'resellerclub');

    switch ($platform) {
        case 'resellerclub':
        case 'resellbiz':
        case 'resell.biz':
        case 'stargate':
            return resellerclub_get_domain_orderid($registrar, $domain);

        case 'namecheap':
            // Namecheap doesn't use order IDs the same way
            return null;

        default:
            return null;
    }
}

/**
 * Fetch domain order ID from ResellerClub/Resell.biz API
 *
 * @param array $registrar Registrar config
 * @param string $domain Domain name
 * @return string|null Order ID or null
 */
function resellerclub_get_domain_orderid($registrar, $domain)
{
    $apiUrl = rtrim($registrar['api_base_url'], '/') . '/api/domains/orderid.json';

    $parts = explode('.', $domain, 2);
    if (count($parts) < 2) {
        return null;
    }

    $params = array(
        'auth-userid' => $registrar['auth_userid'],
        'api-key' => $registrar['auth_apikey'],
        'domain-name' => $parts[0],
        'tld' => $parts[1]
    );

    $url = $apiUrl . '?' . http_build_query($params);
    $response = domain_api_get($url);

    if ($response['success'] && !empty($response['data']) && is_numeric($response['data'])) {
        return $response['data'];
    }

    log_message('error', 'Failed to fetch domain_order_id for ' . $domain . ': ' . json_encode($response));
    return null;
}

/**
 * Get or create customer at registrar
 */
function registrar_get_or_create_customer($registrar, $customerInfo)
{
    $platform = strtolower($registrar['platform'] ?? 'resellerclub');

    switch ($platform) {
        case 'resellerclub':
        case 'resellbiz':
        case 'resell.biz':
        case 'stargate':
            return resellerclub_get_or_create_customer($registrar, $customerInfo);

        case 'namecheap':
            return namecheap_get_or_create_customer($registrar, $customerInfo);

        default:
            return array('success' => false, 'error' => 'Unsupported registrar platform: ' . $platform);
    }
}

/**
 * Create contact at registrar
 */
function registrar_create_contact($registrar, $customerId, $contactInfo, $type = 'Contact')
{
    $platform = strtolower($registrar['platform'] ?? 'resellerclub');

    switch ($platform) {
        case 'resellerclub':
        case 'resellbiz':
        case 'resell.biz':
        case 'stargate':
            return resellerclub_create_contact($registrar, $customerId, $contactInfo, $type);

        case 'namecheap':
            // Namecheap doesn't use separate contact IDs - contacts are passed with each request
            return namecheap_create_contact($registrar, $customerId, $contactInfo);

        default:
            return array('success' => false, 'error' => 'Unsupported registrar platform: ' . $platform);
    }
}

// ============================================
// NAMECHEAP API FUNCTIONS
// ============================================

/**
 * Get client IP for Namecheap API (required parameter)
 * Uses whitelisted_ip from registrar config if available
 *
 * @param array $registrar Registrar config (optional)
 * @return string Client IP address
 */
function namecheap_get_client_ip($registrar = array())
{
    // Use whitelisted IP from registrar config if set
    if (!empty($registrar['whitelisted_ip'])) {
        return $registrar['whitelisted_ip'];
    }

    // Fallback to server IP
    if (!empty($_SERVER['SERVER_ADDR'])) {
        return $_SERVER['SERVER_ADDR'];
    }

    return '127.0.0.1';
}

/**
 * Make a request to Namecheap API
 *
 * @param array $registrar Registrar config
 * @param string $command API command (e.g., 'namecheap.domains.check')
 * @param array $params Additional parameters
 * @return array Response with 'success', 'data', 'error'
 */
function namecheap_api_request($registrar, $command, $params = array())
{
    $apiUrl = rtrim($registrar['api_base_url'], '/');

    // Build base parameters
    $baseParams = array(
        'ApiUser' => $registrar['auth_userid'],
        'ApiKey' => $registrar['auth_apikey'],
        'UserName' => $registrar['auth_userid'],
        'ClientIp' => namecheap_get_client_ip($registrar),
        'Command' => $command
    );

    // Merge with additional params
    $allParams = array_merge($baseParams, $params);

    // Build URL with query string
    $url = $apiUrl . '?' . http_build_query($allParams);

    // Make GET request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        log_message('error', 'Namecheap API error: ' . $error);
        return array('success' => false, 'error' => 'cURL error: ' . $error, 'data' => null);
    }

    // Parse XML response
    $data = namecheap_parse_xml($response);

    if ($data === false) {
        log_message('error', 'Namecheap API: Failed to parse XML response');
        return array('success' => false, 'error' => 'Failed to parse XML response', 'data' => null);
    }

    // Check API status
    if (isset($data['@attributes']['Status']) && $data['@attributes']['Status'] === 'OK') {
        return array('success' => true, 'data' => $data, 'error' => null);
    } else {
        $errorMsg = 'API request failed';
        if (isset($data['Errors']['Error'])) {
            $errorData = $data['Errors']['Error'];
            if (is_array($errorData) && isset($errorData['@value'])) {
                $errorMsg = $errorData['@value'];
            } elseif (is_string($errorData)) {
                $errorMsg = $errorData;
            } elseif (is_array($errorData) && isset($errorData[0]['@value'])) {
                $errorMsg = $errorData[0]['@value'];
            }
        }
        log_message('error', 'Namecheap API error: ' . $errorMsg . ' | Response: ' . $response);
        return array('success' => false, 'error' => $errorMsg, 'data' => $data);
    }
}

/**
 * Parse Namecheap XML response to array
 *
 * @param string $xmlString XML response string
 * @return array|false Parsed data or false on failure
 */
function namecheap_parse_xml($xmlString)
{
    try {
        $xml = simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($xml === false) {
            return false;
        }
        return namecheap_xml_to_array($xml);
    } catch (Exception $e) {
        log_message('error', 'Namecheap XML parse error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Convert SimpleXMLElement to array
 *
 * @param SimpleXMLElement $xml
 * @return array
 */
function namecheap_xml_to_array($xml)
{
    $result = array();

    // Get attributes
    foreach ($xml->attributes() as $key => $value) {
        $result['@attributes'][$key] = (string)$value;
    }

    // Get child elements
    foreach ($xml->children() as $key => $value) {
        $childArray = namecheap_xml_to_array($value);

        // Handle multiple elements with same name
        if (isset($result[$key])) {
            if (!isset($result[$key][0])) {
                $result[$key] = array($result[$key]);
            }
            $result[$key][] = $childArray;
        } else {
            $result[$key] = $childArray;
        }
    }

    // Get text content
    $text = trim((string)$xml);
    if (!empty($text) && empty($result)) {
        return $text;
    } elseif (!empty($text)) {
        $result['@value'] = $text;
    }

    return $result;
}

/**
 * Check domain availability via Namecheap API
 *
 * @param array $registrar Registrar config
 * @param string $domain Full domain name (e.g., example.com)
 * @return array Result with 'success', 'available', 'premium', 'error'
 */
function namecheap_check_domain($registrar, $domain)
{
    $response = namecheap_api_request($registrar, 'namecheap.domains.check', array(
        'DomainList' => $domain
    ));

    if (!$response['success']) {
        return array('success' => false, 'available' => false, 'error' => $response['error']);
    }

    $data = $response['data'];

    // Parse domain check result
    if (isset($data['CommandResponse']['DomainCheckResult'])) {
        $checkResult = $data['CommandResponse']['DomainCheckResult'];
        $attrs = $checkResult['@attributes'] ?? array();

        $available = isset($attrs['Available']) && strtolower($attrs['Available']) === 'true';
        $premium = isset($attrs['IsPremiumName']) && strtolower($attrs['IsPremiumName']) === 'true';

        return array(
            'success' => true,
            'available' => $available,
            'premium' => $premium,
            'domain' => $attrs['Domain'] ?? $domain,
            'error' => null
        );
    }

    return array('success' => false, 'available' => false, 'error' => 'Invalid response format');
}

/**
 * Register a domain via Namecheap API
 *
 * @param array $registrar Registrar config
 * @param string $domain Full domain name
 * @param int $years Registration period
 * @param array $contact Contact details
 * @param array $nameservers Nameservers
 * @return array Result with 'success', 'order_id', 'error'
 */
function namecheap_register_domain($registrar, $domain, $years, $contact, $nameservers = array())
{
    // Split domain into SLD and TLD
    $parts = explode('.', $domain, 2);
    if (count($parts) < 2) {
        return array('success' => false, 'order_id' => null, 'error' => 'Invalid domain format');
    }

    $sld = $parts[0];
    $tld = $parts[1];

    // Get contact info (stored in contact array from provisioning)
    $contactInfo = $contact['contact_info'] ?? array();

    // Build registration parameters
    $params = array(
        'DomainName' => $domain,
        'Years' => $years,
        // Registrant contact
        'RegistrantFirstName' => namecheap_get_first_name($contactInfo['name'] ?? ''),
        'RegistrantLastName' => namecheap_get_last_name($contactInfo['name'] ?? ''),
        'RegistrantAddress1' => $contactInfo['address1'] ?? 'N/A',
        'RegistrantCity' => $contactInfo['city'] ?? 'N/A',
        'RegistrantStateProvince' => $contactInfo['state'] ?? 'N/A',
        'RegistrantPostalCode' => $contactInfo['zipcode'] ?? '00000',
        'RegistrantCountry' => $contactInfo['country'] ?? 'US',
        'RegistrantPhone' => namecheap_format_phone($contactInfo['phone_cc'] ?? '1', $contactInfo['phone'] ?? '0000000000'),
        'RegistrantEmailAddress' => $contactInfo['email'] ?? '',
        // Tech contact (same as registrant)
        'TechFirstName' => namecheap_get_first_name($contactInfo['name'] ?? ''),
        'TechLastName' => namecheap_get_last_name($contactInfo['name'] ?? ''),
        'TechAddress1' => $contactInfo['address1'] ?? 'N/A',
        'TechCity' => $contactInfo['city'] ?? 'N/A',
        'TechStateProvince' => $contactInfo['state'] ?? 'N/A',
        'TechPostalCode' => $contactInfo['zipcode'] ?? '00000',
        'TechCountry' => $contactInfo['country'] ?? 'US',
        'TechPhone' => namecheap_format_phone($contactInfo['phone_cc'] ?? '1', $contactInfo['phone'] ?? '0000000000'),
        'TechEmailAddress' => $contactInfo['email'] ?? '',
        // Admin contact (same as registrant)
        'AdminFirstName' => namecheap_get_first_name($contactInfo['name'] ?? ''),
        'AdminLastName' => namecheap_get_last_name($contactInfo['name'] ?? ''),
        'AdminAddress1' => $contactInfo['address1'] ?? 'N/A',
        'AdminCity' => $contactInfo['city'] ?? 'N/A',
        'AdminStateProvince' => $contactInfo['state'] ?? 'N/A',
        'AdminPostalCode' => $contactInfo['zipcode'] ?? '00000',
        'AdminCountry' => $contactInfo['country'] ?? 'US',
        'AdminPhone' => namecheap_format_phone($contactInfo['phone_cc'] ?? '1', $contactInfo['phone'] ?? '0000000000'),
        'AdminEmailAddress' => $contactInfo['email'] ?? '',
        // Billing contact (same as registrant)
        'AuxBillingFirstName' => namecheap_get_first_name($contactInfo['name'] ?? ''),
        'AuxBillingLastName' => namecheap_get_last_name($contactInfo['name'] ?? ''),
        'AuxBillingAddress1' => $contactInfo['address1'] ?? 'N/A',
        'AuxBillingCity' => $contactInfo['city'] ?? 'N/A',
        'AuxBillingStateProvince' => $contactInfo['state'] ?? 'N/A',
        'AuxBillingPostalCode' => $contactInfo['zipcode'] ?? '00000',
        'AuxBillingCountry' => $contactInfo['country'] ?? 'US',
        'AuxBillingPhone' => namecheap_format_phone($contactInfo['phone_cc'] ?? '1', $contactInfo['phone'] ?? '0000000000'),
        'AuxBillingEmailAddress' => $contactInfo['email'] ?? ''
    );

    // Add nameservers
    if (!empty($nameservers)) {
        $params['Nameservers'] = implode(',', $nameservers);
    } elseif (!empty($registrar['def_ns1'])) {
        $ns = array();
        if (!empty($registrar['def_ns1'])) $ns[] = $registrar['def_ns1'];
        if (!empty($registrar['def_ns2'])) $ns[] = $registrar['def_ns2'];
        if (!empty($registrar['def_ns3'])) $ns[] = $registrar['def_ns3'];
        if (!empty($registrar['def_ns4'])) $ns[] = $registrar['def_ns4'];
        if (!empty($ns)) {
            $params['Nameservers'] = implode(',', $ns);
        }
    }

    $response = namecheap_api_request($registrar, 'namecheap.domains.create', $params);

    if ($response['success']) {
        $data = $response['data'];
        if (isset($data['CommandResponse']['DomainCreateResult'])) {
            $result = $data['CommandResponse']['DomainCreateResult'];
            $attrs = $result['@attributes'] ?? array();

            if (isset($attrs['Registered']) && strtolower($attrs['Registered']) === 'true') {
                return array(
                    'success' => true,
                    'order_id' => $attrs['DomainID'] ?? $attrs['OrderID'] ?? time(),
                    'domain' => $attrs['Domain'] ?? $domain,
                    'error' => null
                );
            }
        }
        return array('success' => false, 'order_id' => null, 'error' => 'Registration failed - check response');
    }

    return array('success' => false, 'order_id' => null, 'error' => $response['error']);
}

/**
 * Transfer a domain via Namecheap API
 *
 * @param array $registrar Registrar config
 * @param string $domain Full domain name
 * @param string $eppCode EPP/Auth code
 * @param array $contact Contact details
 * @param array $nameservers Nameservers (optional)
 * @return array Result with 'success', 'order_id', 'error'
 */
function namecheap_transfer_domain($registrar, $domain, $eppCode, $contact, $nameservers = array())
{
    // Get contact info
    $contactInfo = $contact['contact_info'] ?? array();

    $params = array(
        'DomainName' => $domain,
        'Years' => 1,
        'EPPCode' => $eppCode
    );

    $response = namecheap_api_request($registrar, 'namecheap.domains.transfer.create', $params);

    if ($response['success']) {
        $data = $response['data'];
        if (isset($data['CommandResponse']['DomainTransferCreateResult'])) {
            $result = $data['CommandResponse']['DomainTransferCreateResult'];
            $attrs = $result['@attributes'] ?? array();

            if (isset($attrs['Transfer']) && strtolower($attrs['Transfer']) === 'true') {
                return array(
                    'success' => true,
                    'order_id' => $attrs['TransferID'] ?? $attrs['OrderID'] ?? time(),
                    'status' => $attrs['StatusID'] ?? 'Submitted',
                    'error' => null
                );
            }

            // Transfer submitted but may need confirmation
            if (isset($attrs['TransferID'])) {
                return array(
                    'success' => true,
                    'order_id' => $attrs['TransferID'],
                    'status' => 'Pending',
                    'error' => null
                );
            }
        }
        return array('success' => false, 'order_id' => null, 'error' => 'Transfer request failed - check response');
    }

    return array('success' => false, 'order_id' => null, 'error' => $response['error']);
}

/**
 * Renew a domain via Namecheap API
 *
 * @param array $registrar Registrar config
 * @param string $domain Full domain name
 * @param int $years Renewal period
 * @param string $currentExpDate Current expiration date
 * @param int $orderId Registrar order ID (not used by Namecheap)
 * @return array Result with 'success', 'new_expiry', 'error'
 */
function namecheap_renew_domain($registrar, $domain, $years, $currentExpDate, $orderId)
{
    $params = array(
        'DomainName' => $domain,
        'Years' => $years
    );

    $response = namecheap_api_request($registrar, 'namecheap.domains.renew', $params);

    if ($response['success']) {
        $data = $response['data'];
        if (isset($data['CommandResponse']['DomainRenewResult'])) {
            $result = $data['CommandResponse']['DomainRenewResult'];
            $attrs = $result['@attributes'] ?? array();

            if (isset($attrs['Renew']) && strtolower($attrs['Renew']) === 'true') {
                // Calculate new expiry
                if (!is_numeric($currentExpDate)) {
                    $currentExpDate = strtotime($currentExpDate);
                }
                $newExpiry = date('Y-m-d', strtotime("+{$years} years", $currentExpDate));

                return array(
                    'success' => true,
                    'order_id' => $attrs['OrderID'] ?? $orderId,
                    'new_expiry' => $newExpiry,
                    'error' => null
                );
            }
        }
        return array('success' => false, 'new_expiry' => null, 'error' => 'Renewal failed - check response');
    }

    return array('success' => false, 'new_expiry' => null, 'error' => $response['error']);
}

/**
 * Get or create customer at Namecheap
 * Note: Namecheap doesn't use customer IDs like ResellerClub
 * We store contact info for use during registration
 *
 * @param array $registrar Registrar config
 * @param array $customerInfo Customer details
 * @return array Result with 'success', 'customer_id', 'error'
 */
function namecheap_get_or_create_customer($registrar, $customerInfo)
{
    // Namecheap doesn't have a customer/contact ID system
    // Return a pseudo customer ID and store contact info
    return array(
        'success' => true,
        'customer_id' => 'NC_' . md5($customerInfo['email']),
        'contact_info' => $customerInfo,
        'error' => null
    );
}

/**
 * Create contact at Namecheap
 * Note: Namecheap doesn't use separate contact IDs
 *
 * @param array $registrar Registrar config
 * @param string $customerId Customer ID (not used)
 * @param array $contactInfo Contact details
 * @return array Result with 'success', 'contact_id', 'contact_info', 'error'
 */
function namecheap_create_contact($registrar, $customerId, $contactInfo)
{
    // Namecheap doesn't use contact IDs - contacts are passed with each request
    // Store the contact info for use during domain operations
    return array(
        'success' => true,
        'contact_id' => 'NC_CONTACT_' . md5($contactInfo['email'] . time()),
        'contact_info' => $contactInfo,
        'error' => null
    );
}

/**
 * Helper: Get first name from full name
 */
function namecheap_get_first_name($fullName)
{
    $parts = explode(' ', trim($fullName), 2);
    return !empty($parts[0]) ? $parts[0] : 'N/A';
}

/**
 * Helper: Get last name from full name
 */
function namecheap_get_last_name($fullName)
{
    $parts = explode(' ', trim($fullName), 2);
    return isset($parts[1]) && !empty($parts[1]) ? $parts[1] : 'N/A';
}

/**
 * Helper: Format phone number for Namecheap API
 * Format: +1.1234567890
 */
function namecheap_format_phone($countryCode, $phone)
{
    $cc = preg_replace('/[^0-9]/', '', $countryCode);
    $ph = preg_replace('/[^0-9]/', '', $phone);

    if (empty($cc)) $cc = '1';
    if (empty($ph)) $ph = '0000000000';

    return '+' . $cc . '.' . $ph;
}
