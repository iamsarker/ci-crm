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

    $data = json_decode($response, true);

    if ($httpCode >= 200 && $httpCode < 300) {
        return array('success' => true, 'data' => $data, 'error' => null, 'http_code' => $httpCode);
    } else {
        log_message('error', 'Domain API GET HTTP ' . $httpCode . ': ' . $response);
        return array('success' => false, 'data' => $data, 'error' => 'HTTP ' . $httpCode, 'http_code' => $httpCode);
    }
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

    $data = json_decode($response, true);

    if ($httpCode >= 200 && $httpCode < 300) {
        return array('success' => true, 'data' => $data, 'error' => null, 'http_code' => $httpCode);
    } else {
        log_message('error', 'Domain API POST HTTP ' . $httpCode . ': ' . $response);
        return array('success' => false, 'data' => $data, 'error' => 'HTTP ' . $httpCode, 'http_code' => $httpCode);
    }
}

/**
 * Make a POST request to domain API with raw post data
 * Used when we need to send array parameters like ns[]
 */
function domain_api_post_raw($url, $postData, $headers = array())
{
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

    $data = json_decode($response, true);

    if ($httpCode >= 200 && $httpCode < 300) {
        return array('success' => true, 'data' => $data, 'error' => null, 'http_code' => $httpCode);
    } else {
        log_message('error', 'Domain API POST HTTP ' . $httpCode . ': ' . $response);
        return array('success' => false, 'data' => $data, 'error' => 'HTTP ' . $httpCode, 'http_code' => $httpCode);
    }
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
        'invoice-option' => 'NoInvoice'
    );

    $response = domain_api_post($apiUrl, $params);

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
        }
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

        // Add more registrars here as needed
        // case 'enom':
        //     return enom_register_domain($registrar, $domain, $years, $contact, $nameservers);
        // case 'namecheap':
        //     return namecheap_register_domain($registrar, $domain, $years, $contact, $nameservers);

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

        default:
            log_message('error', 'Unsupported registrar platform: ' . $platform);
            return array('success' => false, 'error' => 'Unsupported registrar platform: ' . $platform);
    }
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

        default:
            return array('success' => false, 'error' => 'Unsupported registrar platform: ' . $platform);
    }
}
