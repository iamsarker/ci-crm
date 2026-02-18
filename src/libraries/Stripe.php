<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Stripe Payment Library
 *
 * Handles Stripe payment processing via API
 * Documentation: https://stripe.com/docs/api
 */
class Stripe
{
    private $CI;
    private $secretKey;
    private $publishableKey;
    private $webhookSecret;
    private $isTestMode;
    private $apiVersion = '2023-10-16';
    private $apiBase = 'https://api.stripe.com/v1';

    public function __construct($config = array())
    {
        $this->CI =& get_instance();
        $this->CI->load->model('PaymentGateway_model');

        // Load credentials from database or config
        if (empty($config)) {
            $credentials = $this->CI->PaymentGateway_model->getGatewayCredentials('stripe');
            if ($credentials) {
                $this->secretKey = $credentials['secret_key'];
                $this->publishableKey = $credentials['public_key'];
                $this->webhookSecret = $credentials['webhook_secret'];
                $this->isTestMode = $credentials['is_test_mode'];
            }
        } else {
            $this->secretKey = isset($config['secret_key']) ? $config['secret_key'] : '';
            $this->publishableKey = isset($config['publishable_key']) ? $config['publishable_key'] : '';
            $this->webhookSecret = isset($config['webhook_secret']) ? $config['webhook_secret'] : '';
            $this->isTestMode = isset($config['is_test_mode']) ? $config['is_test_mode'] : true;
        }
    }

    /**
     * Get publishable key for frontend
     */
    public function getPublishableKey()
    {
        return $this->publishableKey;
    }

    /**
     * Check if gateway is configured
     */
    public function isConfigured()
    {
        return !empty($this->secretKey) && !empty($this->publishableKey);
    }

    /**
     * Create a PaymentIntent for checkout
     *
     * @param float $amount Amount in major currency unit (e.g., dollars)
     * @param string $currency 3-letter currency code
     * @param array $metadata Additional metadata
     * @return array
     */
    public function createPaymentIntent($amount, $currency, $metadata = array())
    {
        // Convert to smallest currency unit (cents for USD)
        $amountInCents = $this->toSmallestUnit($amount, $currency);

        $data = array(
            'amount' => $amountInCents,
            'currency' => strtolower($currency),
            'automatic_payment_methods' => array('enabled' => 'true'),
            'metadata' => $metadata
        );

        return $this->apiRequest('POST', '/payment_intents', $data);
    }

    /**
     * Retrieve a PaymentIntent
     * Expands latest_charge to get payment details
     */
    public function getPaymentIntent($paymentIntentId)
    {
        return $this->apiRequest('GET', '/payment_intents/' . $paymentIntentId . '?expand[]=latest_charge');
    }

    /**
     * Extract card details from PaymentIntent or Charge object
     * Handles both old (charges array) and new (latest_charge) Stripe API formats
     *
     * @param array $data PaymentIntent or webhook data
     * @return array Card details (brand, last4, exp_month, exp_year) or empty array
     */
    public function extractCardDetails($data)
    {
        $card = array();

        // Try latest_charge first (newer Stripe API)
        if (isset($data['latest_charge']['payment_method_details']['card'])) {
            $cardData = $data['latest_charge']['payment_method_details']['card'];
            $card = array(
                'brand' => $cardData['brand'] ?? null,
                'last4' => $cardData['last4'] ?? null,
                'exp_month' => $cardData['exp_month'] ?? null,
                'exp_year' => $cardData['exp_year'] ?? null
            );
        }
        // Fallback to charges array (older format / some webhook payloads)
        elseif (isset($data['charges']['data'][0]['payment_method_details']['card'])) {
            $cardData = $data['charges']['data'][0]['payment_method_details']['card'];
            $card = array(
                'brand' => $cardData['brand'] ?? null,
                'last4' => $cardData['last4'] ?? null,
                'exp_month' => $cardData['exp_month'] ?? null,
                'exp_year' => $cardData['exp_year'] ?? null
            );
        }

        return $card;
    }

    /**
     * Confirm a PaymentIntent
     */
    public function confirmPaymentIntent($paymentIntentId, $paymentMethodId = null)
    {
        $data = array();
        if ($paymentMethodId) {
            $data['payment_method'] = $paymentMethodId;
        }

        return $this->apiRequest('POST', '/payment_intents/' . $paymentIntentId . '/confirm', $data);
    }

    /**
     * Cancel a PaymentIntent
     */
    public function cancelPaymentIntent($paymentIntentId)
    {
        return $this->apiRequest('POST', '/payment_intents/' . $paymentIntentId . '/cancel');
    }

    /**
     * Create a Checkout Session (hosted checkout)
     *
     * @param array $lineItems Array of line items
     * @param string $successUrl URL to redirect on success
     * @param string $cancelUrl URL to redirect on cancel
     * @param array $metadata Additional metadata
     * @return array
     */
    public function createCheckoutSession($lineItems, $successUrl, $cancelUrl, $metadata = array())
    {
        $data = array(
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'line_items' => $lineItems,
            'metadata' => $metadata
        );

        return $this->apiRequest('POST', '/checkout/sessions', $data);
    }

    /**
     * Helper: Create line item for checkout session
     */
    public function createLineItem($name, $amount, $currency, $quantity = 1, $description = null)
    {
        $item = array(
            'price_data' => array(
                'currency' => strtolower($currency),
                'unit_amount' => $this->toSmallestUnit($amount, $currency),
                'product_data' => array(
                    'name' => $name
                )
            ),
            'quantity' => $quantity
        );

        if ($description) {
            $item['price_data']['product_data']['description'] = $description;
        }

        return $item;
    }

    /**
     * Retrieve a Checkout Session
     */
    public function getCheckoutSession($sessionId)
    {
        return $this->apiRequest('GET', '/checkout/sessions/' . $sessionId);
    }

    /**
     * Create a Refund
     *
     * @param string $paymentIntentId PaymentIntent to refund
     * @param float|null $amount Amount to refund (null for full refund)
     * @param string $reason Reason: duplicate, fraudulent, requested_by_customer
     * @return array
     */
    public function createRefund($paymentIntentId, $amount = null, $reason = 'requested_by_customer')
    {
        $data = array(
            'payment_intent' => $paymentIntentId,
            'reason' => $reason
        );

        if ($amount !== null) {
            // Get currency from payment intent to convert amount
            $pi = $this->getPaymentIntent($paymentIntentId);
            if ($pi['success'] && isset($pi['data']['currency'])) {
                $data['amount'] = $this->toSmallestUnit($amount, $pi['data']['currency']);
            }
        }

        return $this->apiRequest('POST', '/refunds', $data);
    }

    /**
     * Retrieve a Refund
     */
    public function getRefund($refundId)
    {
        return $this->apiRequest('GET', '/refunds/' . $refundId);
    }

    /**
     * Create a Customer
     */
    public function createCustomer($email, $name = null, $metadata = array())
    {
        $data = array(
            'email' => $email,
            'metadata' => $metadata
        );

        if ($name) {
            $data['name'] = $name;
        }

        return $this->apiRequest('POST', '/customers', $data);
    }

    /**
     * Retrieve a Customer
     */
    public function getCustomer($customerId)
    {
        return $this->apiRequest('GET', '/customers/' . $customerId);
    }

    /**
     * Verify webhook signature
     *
     * @param string $payload Raw request body
     * @param string $signature Stripe-Signature header
     * @return array|false Returns event data if valid, false if invalid
     */
    public function verifyWebhook($payload, $signature)
    {
        if (empty($this->webhookSecret)) {
            log_message('error', 'Stripe webhook secret not configured');
            return false;
        }

        $sigParts = $this->parseSignatureHeader($signature);
        if (!$sigParts) {
            return false;
        }

        $timestamp = $sigParts['t'];
        $signatures = $sigParts['v1'];

        // Check timestamp (must be within 5 minutes)
        if (abs(time() - $timestamp) > 300) {
            log_message('error', 'Stripe webhook timestamp too old');
            return false;
        }

        // Compute expected signature
        $signedPayload = $timestamp . '.' . $payload;
        $expectedSignature = hash_hmac('sha256', $signedPayload, $this->webhookSecret);

        // Verify signature
        $signatureValid = false;
        foreach ($signatures as $sig) {
            if (hash_equals($expectedSignature, $sig)) {
                $signatureValid = true;
                break;
            }
        }

        if (!$signatureValid) {
            log_message('error', 'Stripe webhook signature verification failed');
            return false;
        }

        // Parse and return event
        $event = json_decode($payload, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            log_message('error', 'Stripe webhook JSON decode error');
            return false;
        }

        return $event;
    }

    /**
     * Parse Stripe-Signature header
     */
    private function parseSignatureHeader($header)
    {
        $result = array('t' => null, 'v1' => array());

        $pairs = explode(',', $header);
        foreach ($pairs as $pair) {
            $parts = explode('=', trim($pair), 2);
            if (count($parts) !== 2) continue;

            $key = $parts[0];
            $value = $parts[1];

            if ($key === 't') {
                $result['t'] = intval($value);
            } elseif ($key === 'v1') {
                $result['v1'][] = $value;
            }
        }

        if (!$result['t'] || empty($result['v1'])) {
            return false;
        }

        return $result;
    }

    /**
     * Convert amount to smallest currency unit
     */
    private function toSmallestUnit($amount, $currency)
    {
        // Zero-decimal currencies
        $zeroDecimal = array('bif', 'clp', 'djf', 'gnf', 'jpy', 'kmf', 'krw', 'mga', 'pyg', 'rwf', 'ugx', 'vnd', 'vuv', 'xaf', 'xof', 'xpf');

        $currency = strtolower($currency);

        if (in_array($currency, $zeroDecimal)) {
            return intval($amount);
        }

        // Most currencies use cents (2 decimal places)
        return intval(round($amount * 100));
    }

    /**
     * Convert amount from smallest currency unit
     */
    public function fromSmallestUnit($amount, $currency)
    {
        $zeroDecimal = array('bif', 'clp', 'djf', 'gnf', 'jpy', 'kmf', 'krw', 'mga', 'pyg', 'rwf', 'ugx', 'vnd', 'vuv', 'xaf', 'xof', 'xpf');

        $currency = strtolower($currency);

        if (in_array($currency, $zeroDecimal)) {
            return floatval($amount);
        }

        return floatval($amount) / 100;
    }

    /**
     * Make API request to Stripe
     */
    private function apiRequest($method, $endpoint, $data = array())
    {
        $url = $this->apiBase . $endpoint;

        $curl = curl_init();

        // Extend PHP execution time
        set_time_limit(120);

        $headers = array(
            'Authorization: Bearer ' . $this->secretKey,
            'Stripe-Version: ' . $this->apiVersion,
            'Content-Type: application/x-www-form-urlencoded'
        );

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->buildQueryString($data));
        } elseif ($method === 'DELETE') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($response === false) {
            log_message('error', 'Stripe API cURL error: ' . $curlError);
            return array(
                'success' => false,
                'error' => 'Connection error: ' . $curlError
            );
        }

        $decoded = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return array(
                'success' => true,
                'data' => $decoded
            );
        } else {
            $errorMessage = 'Unknown error';
            if (isset($decoded['error']['message'])) {
                $errorMessage = $decoded['error']['message'];
            }

            log_message('error', 'Stripe API error: ' . $errorMessage . ' (HTTP ' . $httpCode . ')');

            return array(
                'success' => false,
                'error' => $errorMessage,
                'error_code' => isset($decoded['error']['code']) ? $decoded['error']['code'] : null,
                'error_type' => isset($decoded['error']['type']) ? $decoded['error']['type'] : null,
                'http_code' => $httpCode
            );
        }
    }

    /**
     * Build query string for Stripe API (handles nested arrays)
     */
    private function buildQueryString($data, $prefix = '')
    {
        $result = array();

        foreach ($data as $key => $value) {
            $fullKey = $prefix ? $prefix . '[' . $key . ']' : $key;

            if (is_array($value)) {
                $result[] = $this->buildQueryString($value, $fullKey);
            } else {
                $result[] = urlencode($fullKey) . '=' . urlencode($value);
            }
        }

        return implode('&', $result);
    }
}
