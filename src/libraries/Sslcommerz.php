<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * SSLCommerz Payment Library
 *
 * Handles SSLCommerz payment integration
 */
class Sslcommerz
{
    private $CI;
    private $store_id;
    private $store_password;
    private $is_test_mode;
    private $api_url;

    public function __construct($config = array())
    {
        $this->CI =& get_instance();

        // Load gateway config if not passed
        if (empty($config)) {
            $this->CI->load->model('PaymentGateway_model');
            $gateway = $this->CI->PaymentGateway_model->getByCode('sslcommerz');

            if ($gateway) {
                $this->is_test_mode = $gateway['is_test_mode'] == 1;

                // Get credentials based on mode
                if ($this->is_test_mode) {
                    // Sandbox credentials
                    $this->store_id = $gateway['test_public_key'] ?? '';
                    $this->store_password = $gateway['test_secret_key'] ?? '';
                    $this->api_url = 'https://sandbox.sslcommerz.com';
                } else {
                    // Live credentials
                    $this->store_id = $gateway['public_key'] ?? '';
                    $this->store_password = $gateway['secret_key'] ?? '';
                    $this->api_url = 'https://securepay.sslcommerz.com';
                }
            }
        } else {
            $this->store_id = $config['store_id'] ?? '';
            $this->store_password = $config['store_password'] ?? '';
            $this->is_test_mode = $config['is_test_mode'] ?? true;
            $this->api_url = $this->is_test_mode
                ? 'https://sandbox.sslcommerz.com'
                : 'https://securepay.sslcommerz.com';
        }
    }

    /**
     * Check if library is configured
     */
    public function isConfigured()
    {
        return !empty($this->store_id) && !empty($this->store_password);
    }

    /**
     * Initialize payment session
     */
    public function initiatePayment($data)
    {
        if (!$this->isConfigured()) {
            return array('success' => false, 'error' => 'SSLCommerz not configured');
        }

        $postData = array(
            'store_id' => $this->store_id,
            'store_passwd' => $this->store_password,
            'total_amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'BDT',
            'tran_id' => $data['transaction_id'],
            'success_url' => $data['success_url'],
            'fail_url' => $data['fail_url'],
            'cancel_url' => $data['cancel_url'],
            'ipn_url' => $data['ipn_url'] ?? '',

            // Customer info
            'cus_name' => $data['customer_name'] ?? 'Customer',
            'cus_email' => $data['customer_email'] ?? 'customer@example.com',
            'cus_phone' => $data['customer_phone'] ?? '01700000000',
            'cus_add1' => $data['customer_address'] ?? 'Dhaka',
            'cus_city' => $data['customer_city'] ?? 'Dhaka',
            'cus_country' => $data['customer_country'] ?? 'Bangladesh',

            // Shipping info (required by SSLCommerz)
            'shipping_method' => 'NO',
            'num_of_item' => 1,

            // Product info
            'product_name' => $data['product_name'] ?? 'Invoice Payment',
            'product_category' => $data['product_category'] ?? 'Service',
            'product_profile' => 'general',

            // Optional
            'value_a' => $data['value_a'] ?? '',
            'value_b' => $data['value_b'] ?? '',
            'value_c' => $data['value_c'] ?? '',
            'value_d' => $data['value_d'] ?? ''
        );

        $response = $this->makeRequest('/gwprocess/v4/api.php', $postData);

        if ($response && isset($response['status']) && $response['status'] === 'SUCCESS') {
            return array(
                'success' => true,
                'data' => array(
                    'session_key' => $response['sessionkey'],
                    'gateway_url' => $response['GatewayPageURL'],
                    'redirect_gateway_url' => $response['redirectGatewayURL'] ?? $response['GatewayPageURL'],
                    'store_id' => $this->store_id
                )
            );
        }

        $error = $response['failedreason'] ?? 'Failed to initialize SSLCommerz payment';
        return array('success' => false, 'error' => $error);
    }

    /**
     * Validate IPN response
     */
    public function validateIPN($postData)
    {
        if (empty($postData['val_id'])) {
            return array('success' => false, 'error' => 'Invalid IPN data');
        }

        $validationUrl = $this->api_url . '/validator/api/validationserverAPI.php';
        $params = array(
            'val_id' => $postData['val_id'],
            'store_id' => $this->store_id,
            'store_passwd' => $this->store_password,
            'format' => 'json'
        );

        $url = $validationUrl . '?' . http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return array('success' => false, 'error' => 'Validation request failed: ' . $error);
        }

        $result = json_decode($response, true);

        if ($result && isset($result['status']) && $result['status'] === 'VALID') {
            return array(
                'success' => true,
                'data' => $result
            );
        }

        return array('success' => false, 'error' => 'Payment validation failed', 'data' => $result);
    }

    /**
     * Verify payment by transaction ID
     */
    public function verifyPayment($transactionId)
    {
        $url = $this->api_url . '/validator/api/merchantTransIDvalidationAPI.php';
        $params = array(
            'tran_id' => $transactionId,
            'store_id' => $this->store_id,
            'store_passwd' => $this->store_password,
            'format' => 'json'
        );

        $url = $url . '?' . http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return array('success' => false, 'error' => 'Verification request failed: ' . $error);
        }

        $result = json_decode($response, true);

        if ($result && isset($result['status']) && in_array($result['status'], ['VALID', 'VALIDATED'])) {
            return array(
                'success' => true,
                'data' => $result
            );
        }

        return array('success' => false, 'error' => 'Payment verification failed', 'data' => $result);
    }

    /**
     * Make API request
     */
    private function makeRequest($endpoint, $postData)
    {
        $url = $this->api_url . $endpoint;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            log_message('error', 'SSLCommerz API Error: ' . $error);
            return null;
        }

        return json_decode($response, true);
    }

    /**
     * Extract payment details from response
     */
    public function extractPaymentDetails($response)
    {
        $data = $response['data'] ?? $response;

        return array(
            'transaction_id' => $data['tran_id'] ?? '',
            'bank_tran_id' => $data['bank_tran_id'] ?? '',
            'val_id' => $data['val_id'] ?? '',
            'amount' => $data['amount'] ?? $data['currency_amount'] ?? 0,
            'store_amount' => $data['store_amount'] ?? 0,
            'currency' => $data['currency'] ?? $data['currency_type'] ?? 'BDT',
            'card_type' => $data['card_type'] ?? '',
            'card_brand' => $data['card_brand'] ?? '',
            'card_issuer' => $data['card_issuer'] ?? '',
            'status' => $data['status'] ?? ''
        );
    }
}
