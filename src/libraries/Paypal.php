<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PayPal Payment Library
 *
 * Handles PayPal payment processing via REST API
 * Documentation: https://developer.paypal.com/docs/api/overview/
 */
class Paypal
{
    private $CI;
    private $clientId;
    private $clientSecret;
    private $webhookId;
    private $isTestMode;
    private $accessToken;
    private $tokenExpiry;

    private $sandboxApiBase = 'https://api-m.sandbox.paypal.com';
    private $liveApiBase = 'https://api-m.paypal.com';

    public function __construct($config = array())
    {
        $this->CI =& get_instance();
        $this->CI->load->model('PaymentGateway_model');

        // Load credentials from database or config
        if (empty($config)) {
            $credentials = $this->CI->PaymentGateway_model->getGatewayCredentials('paypal');
            if ($credentials) {
                $this->clientId = $credentials['public_key']; // client_id stored in public_key
                $this->clientSecret = $credentials['secret_key']; // client_secret stored in secret_key
                $this->webhookId = $credentials['webhook_secret']; // webhook_id stored in webhook_secret
                $this->isTestMode = $credentials['is_test_mode'];
            }
        } else {
            $this->clientId = isset($config['client_id']) ? $config['client_id'] : '';
            $this->clientSecret = isset($config['client_secret']) ? $config['client_secret'] : '';
            $this->webhookId = isset($config['webhook_id']) ? $config['webhook_id'] : '';
            $this->isTestMode = isset($config['is_test_mode']) ? $config['is_test_mode'] : true;
        }
    }

    /**
     * Get API base URL based on mode
     */
    private function getApiBase()
    {
        return $this->isTestMode ? $this->sandboxApiBase : $this->liveApiBase;
    }

    /**
     * Get client ID for frontend
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Check if gateway is configured
     */
    public function isConfigured()
    {
        return !empty($this->clientId) && !empty($this->clientSecret);
    }

    /**
     * Get access token (with caching)
     */
    private function getAccessToken()
    {
        // Return cached token if still valid
        if ($this->accessToken && $this->tokenExpiry && time() < $this->tokenExpiry) {
            return $this->accessToken;
        }

        $url = $this->getApiBase() . '/v1/oauth2/token';

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
        curl_setopt($curl, CURLOPT_USERPWD, $this->clientId . ':' . $this->clientSecret);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Accept-Language: en_US',
            'Content-Type: application/x-www-form-urlencoded'
        ));
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            $this->accessToken = $data['access_token'];
            // Cache token for slightly less than its actual expiry
            $this->tokenExpiry = time() + ($data['expires_in'] - 60);
            return $this->accessToken;
        }

        log_message('error', 'PayPal OAuth token request failed: HTTP ' . $httpCode);
        return null;
    }

    /**
     * Create an Order (PayPal Checkout)
     *
     * @param float $amount Total amount
     * @param string $currency 3-letter currency code
     * @param string $description Order description
     * @param array $metadata Custom metadata (stored in custom_id)
     * @return array
     */
    public function createOrder($amount, $currency, $description = '', $metadata = array())
    {
        $data = array(
            'intent' => 'CAPTURE',
            'purchase_units' => array(
                array(
                    'amount' => array(
                        'currency_code' => strtoupper($currency),
                        'value' => number_format($amount, 2, '.', '')
                    ),
                    'description' => $description,
                    'custom_id' => !empty($metadata) ? json_encode($metadata) : null
                )
            )
        );

        return $this->apiRequest('POST', '/v2/checkout/orders', $data);
    }

    /**
     * Create an Order with return URLs (for redirect flow)
     */
    public function createOrderWithUrls($amount, $currency, $returnUrl, $cancelUrl, $description = '', $metadata = array())
    {
        $data = array(
            'intent' => 'CAPTURE',
            'purchase_units' => array(
                array(
                    'amount' => array(
                        'currency_code' => strtoupper($currency),
                        'value' => number_format($amount, 2, '.', '')
                    ),
                    'description' => $description,
                    'custom_id' => !empty($metadata) ? json_encode($metadata) : null
                )
            ),
            'application_context' => array(
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
                'brand_name' => $this->CI->config->item('site_name') ?: 'Our Store',
                'landing_page' => 'LOGIN',
                'user_action' => 'PAY_NOW'
            )
        );

        return $this->apiRequest('POST', '/v2/checkout/orders', $data);
    }

    /**
     * Get Order details
     */
    public function getOrder($orderId)
    {
        return $this->apiRequest('GET', '/v2/checkout/orders/' . $orderId);
    }

    /**
     * Capture an Order (complete payment after approval)
     */
    public function captureOrder($orderId)
    {
        return $this->apiRequest('POST', '/v2/checkout/orders/' . $orderId . '/capture', array());
    }

    /**
     * Create a Refund
     *
     * @param string $captureId The capture ID to refund
     * @param float|null $amount Amount to refund (null for full)
     * @param string $currency Currency code
     * @param string $reason Refund reason
     * @return array
     */
    public function createRefund($captureId, $amount = null, $currency = 'USD', $reason = '')
    {
        $data = array();

        if ($amount !== null) {
            $data['amount'] = array(
                'value' => number_format($amount, 2, '.', ''),
                'currency_code' => strtoupper($currency)
            );
        }

        if ($reason) {
            $data['note_to_payer'] = $reason;
        }

        return $this->apiRequest('POST', '/v2/payments/captures/' . $captureId . '/refund', $data);
    }

    /**
     * Get Refund details
     */
    public function getRefund($refundId)
    {
        return $this->apiRequest('GET', '/v2/payments/refunds/' . $refundId);
    }

    /**
     * Verify webhook signature
     *
     * @param array $headers Request headers
     * @param string $body Raw request body
     * @return bool
     */
    public function verifyWebhook($headers, $body)
    {
        if (empty($this->webhookId)) {
            log_message('error', 'PayPal webhook ID not configured');
            return false;
        }

        // Get required headers (case-insensitive)
        $headerLower = array_change_key_case($headers, CASE_LOWER);

        $transmissionId = isset($headerLower['paypal-transmission-id']) ? $headerLower['paypal-transmission-id'] : null;
        $transmissionTime = isset($headerLower['paypal-transmission-time']) ? $headerLower['paypal-transmission-time'] : null;
        $certUrl = isset($headerLower['paypal-cert-url']) ? $headerLower['paypal-cert-url'] : null;
        $authAlgo = isset($headerLower['paypal-auth-algo']) ? $headerLower['paypal-auth-algo'] : null;
        $transmissionSig = isset($headerLower['paypal-transmission-sig']) ? $headerLower['paypal-transmission-sig'] : null;

        if (!$transmissionId || !$transmissionTime || !$transmissionSig) {
            log_message('error', 'PayPal webhook missing required headers');
            return false;
        }

        // Use PayPal's verification API
        $data = array(
            'auth_algo' => $authAlgo,
            'cert_url' => $certUrl,
            'transmission_id' => $transmissionId,
            'transmission_sig' => $transmissionSig,
            'transmission_time' => $transmissionTime,
            'webhook_id' => $this->webhookId,
            'webhook_event' => json_decode($body, true)
        );

        $result = $this->apiRequest('POST', '/v1/notifications/verify-webhook-signature', $data);

        if ($result['success'] && isset($result['data']['verification_status'])) {
            return $result['data']['verification_status'] === 'SUCCESS';
        }

        return false;
    }

    /**
     * Parse webhook event
     */
    public function parseWebhookEvent($body)
    {
        $event = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $event;
    }

    /**
     * Extract payment details from captured order
     */
    public function extractPaymentDetails($captureResponse)
    {
        if (!isset($captureResponse['data']['purchase_units'][0]['payments']['captures'][0])) {
            return null;
        }

        $capture = $captureResponse['data']['purchase_units'][0]['payments']['captures'][0];
        $payer = isset($captureResponse['data']['payer']) ? $captureResponse['data']['payer'] : array();

        return array(
            'transaction_id' => $capture['id'],
            'order_id' => $captureResponse['data']['id'],
            'status' => strtolower($capture['status']),
            'amount' => floatval($capture['amount']['value']),
            'currency' => $capture['amount']['currency_code'],
            'payer_email' => isset($payer['email_address']) ? $payer['email_address'] : null,
            'payer_name' => isset($payer['name']) ? trim($payer['name']['given_name'] . ' ' . $payer['name']['surname']) : null,
            'payer_id' => isset($payer['payer_id']) ? $payer['payer_id'] : null
        );
    }

    /**
     * Make API request to PayPal
     */
    private function apiRequest($method, $endpoint, $data = null)
    {
        $token = $this->getAccessToken();

        if (!$token) {
            return array(
                'success' => false,
                'error' => 'Failed to get access token'
            );
        }

        $url = $this->getApiBase() . $endpoint;

        $curl = curl_init();

        // Extend PHP execution time
        set_time_limit(120);

        $headers = array(
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        );

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            if ($data !== null) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'PATCH') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
            if ($data !== null) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($response === false) {
            log_message('error', 'PayPal API cURL error: ' . $curlError);
            return array(
                'success' => false,
                'error' => 'Connection error: ' . $curlError
            );
        }

        $decoded = json_decode($response, true);

        // PayPal returns 200, 201, 204 for success
        if ($httpCode >= 200 && $httpCode < 300) {
            return array(
                'success' => true,
                'data' => $decoded ?: array()
            );
        } else {
            $errorMessage = 'Unknown error';

            if (isset($decoded['message'])) {
                $errorMessage = $decoded['message'];
            } elseif (isset($decoded['error_description'])) {
                $errorMessage = $decoded['error_description'];
            } elseif (isset($decoded['details'][0]['description'])) {
                $errorMessage = $decoded['details'][0]['description'];
            }

            log_message('error', 'PayPal API error: ' . $errorMessage . ' (HTTP ' . $httpCode . ')');

            return array(
                'success' => false,
                'error' => $errorMessage,
                'error_code' => isset($decoded['name']) ? $decoded['name'] : null,
                'details' => isset($decoded['details']) ? $decoded['details'] : null,
                'http_code' => $httpCode
            );
        }
    }
}
