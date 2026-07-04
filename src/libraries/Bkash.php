<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * bKash Tokenized Checkout Payment Library
 *
 * Handles bKash (Bangladesh) tokenized-checkout payment integration.
 * Flow: grantToken() -> createPayment() -> redirect to bkashURL ->
 *       bKash redirects back to callbackURL -> executePayment().
 *
 * Credential storage (payment_gateway table):
 *   - app_key    => public_key      / test_public_key
 *   - app_secret => secret_key      / test_secret_key
 *   - username   => extra_config.username      / extra_config.sandbox_username
 *   - password   => extra_config.password      / extra_config.sandbox_password
 *   - optional base URL overrides => extra_config.sandbox_url / extra_config.live_url
 */
class Bkash
{
    private $CI;
    private $app_key;
    private $app_secret;
    private $username;
    private $password;
    private $is_test_mode;
    private $api_url;

    // Default tokenized-checkout base URLs (v1.2.0-beta)
    const SANDBOX_URL = 'https://tokenized.sandbox.bka.sh/v1.2.0-beta';
    const LIVE_URL    = 'https://tokenized.pay.bka.sh/v1.2.0-beta';

    public function __construct($config = array())
    {
        $this->CI =& get_instance();

        // Load gateway config if not passed
        if (empty($config)) {
            $this->CI->load->model('PaymentGateway_model');
            $gateway = $this->CI->PaymentGateway_model->getByCode('bkash');

            if ($gateway) {
                $this->is_test_mode = $gateway['is_test_mode'] == 1;

                $extra = !empty($gateway['extra_config']) ? json_decode($gateway['extra_config'], true) : array();
                if (!is_array($extra)) {
                    $extra = array();
                }

                if ($this->is_test_mode) {
                    $this->app_key    = $gateway['test_public_key'] ?? '';
                    $this->app_secret = $gateway['test_secret_key'] ?? '';
                    $this->username   = $extra['sandbox_username'] ?? '';
                    $this->password   = $extra['sandbox_password'] ?? '';
                    $this->api_url    = !empty($extra['sandbox_url']) ? rtrim($extra['sandbox_url'], '/') : self::SANDBOX_URL;
                } else {
                    $this->app_key    = $gateway['public_key'] ?? '';
                    $this->app_secret = $gateway['secret_key'] ?? '';
                    $this->username   = $extra['username'] ?? '';
                    $this->password   = $extra['password'] ?? '';
                    $this->api_url    = !empty($extra['live_url']) ? rtrim($extra['live_url'], '/') : self::LIVE_URL;
                }
            }
        } else {
            $this->app_key    = $config['app_key'] ?? '';
            $this->app_secret = $config['app_secret'] ?? '';
            $this->username   = $config['username'] ?? '';
            $this->password   = $config['password'] ?? '';
            $this->is_test_mode = $config['is_test_mode'] ?? true;
            $this->api_url    = $this->is_test_mode ? self::SANDBOX_URL : self::LIVE_URL;
        }
    }

    /**
     * Check if library is configured
     */
    public function isConfigured()
    {
        return !empty($this->app_key) && !empty($this->app_secret)
            && !empty($this->username) && !empty($this->password);
    }

    /**
     * Step 1: Grant an id_token used to authorize create/execute calls.
     */
    public function grantToken()
    {
        if (!$this->isConfigured()) {
            return array('success' => false, 'error' => 'bKash not configured');
        }

        $headers = array(
            'Content-Type: application/json',
            'Accept: application/json',
            'username: ' . $this->username,
            'password: ' . $this->password
        );

        $body = array(
            'app_key'    => $this->app_key,
            'app_secret' => $this->app_secret
        );

        $response = $this->makeRequest('/tokenized/checkout/token/grant', $body, $headers);

        if ($response && !empty($response['id_token'])) {
            return array(
                'success' => true,
                'data' => array(
                    'id_token'      => $response['id_token'],
                    'token_type'    => $response['token_type'] ?? 'Bearer',
                    'expires_in'    => $response['expires_in'] ?? 3600,
                    'refresh_token' => $response['refresh_token'] ?? ''
                )
            );
        }

        $error = $response['statusMessage'] ?? ($response['msg'] ?? 'Failed to obtain bKash token');
        return array('success' => false, 'error' => $error);
    }

    /**
     * Step 2: Create a payment. Returns paymentID + bkashURL (redirect target).
     *
     * @param array $data amount, currency, merchant_invoice_number, payer_reference, callback_url, id_token
     */
    public function createPayment($data)
    {
        $idToken = $data['id_token'] ?? '';
        if (empty($idToken)) {
            return array('success' => false, 'error' => 'Missing bKash token');
        }

        $headers = $this->authHeaders($idToken);

        $body = array(
            'mode'                  => '0011', // one-off tokenized checkout (no agreement)
            'payerReference'        => (string)($data['payer_reference'] ?? ' '),
            'callbackURL'           => $data['callback_url'],
            'amount'                => number_format((float)$data['amount'], 2, '.', ''),
            'currency'              => $data['currency'] ?? 'BDT',
            'intent'                => 'sale',
            'merchantInvoiceNumber' => (string)$data['merchant_invoice_number']
        );

        $response = $this->makeRequest('/tokenized/checkout/create', $body, $headers);

        if ($response && !empty($response['paymentID']) && !empty($response['bkashURL'])) {
            return array(
                'success' => true,
                'data' => array(
                    'payment_id'   => $response['paymentID'],
                    'bkash_url'    => $response['bkashURL'],
                    'status_code'  => $response['statusCode'] ?? '',
                    'raw'          => $response
                )
            );
        }

        $error = $response['statusMessage'] ?? 'Failed to create bKash payment';
        return array('success' => false, 'error' => $error, 'data' => $response);
    }

    /**
     * Step 3: Execute the payment after the customer returns from bKash.
     */
    public function executePayment($paymentId, $idToken)
    {
        if (empty($idToken)) {
            return array('success' => false, 'error' => 'Missing bKash token');
        }

        $headers = $this->authHeaders($idToken);
        $body = array('paymentID' => $paymentId);

        $response = $this->makeRequest('/tokenized/checkout/execute', $body, $headers);

        // Successful execution: statusCode 0000 and transactionStatus Completed
        if ($response && isset($response['statusCode']) && $response['statusCode'] === '0000'
            && (($response['transactionStatus'] ?? '') === 'Completed')) {
            return array('success' => true, 'data' => $response);
        }

        $error = $response['statusMessage'] ?? 'bKash payment execution failed';
        return array('success' => false, 'error' => $error, 'data' => $response);
    }

    /**
     * Query the status of a payment (used by the webhook / reconciliation).
     */
    public function queryPayment($paymentId, $idToken)
    {
        if (empty($idToken)) {
            return array('success' => false, 'error' => 'Missing bKash token');
        }

        $headers = $this->authHeaders($idToken);
        $body = array('paymentID' => $paymentId);

        $response = $this->makeRequest('/tokenized/checkout/payment/status', $body, $headers);

        if ($response && isset($response['statusCode']) && $response['statusCode'] === '0000') {
            return array('success' => true, 'data' => $response);
        }

        $error = $response['statusMessage'] ?? 'bKash payment query failed';
        return array('success' => false, 'error' => $error, 'data' => $response);
    }

    /**
     * Normalize an execute/query response into common fields.
     */
    public function extractPaymentDetails($response)
    {
        $data = $response['data'] ?? $response;

        return array(
            'payment_id'         => $data['paymentID'] ?? '',
            'transaction_id'     => $data['trxID'] ?? '',
            'amount'             => $data['amount'] ?? 0,
            'currency'           => $data['currency'] ?? 'BDT',
            'transaction_status' => $data['transactionStatus'] ?? '',
            'payment_time'       => $data['paymentExecuteTime'] ?? '',
            'customer_msisdn'    => $data['customerMsisdn'] ?? '',
            'invoice_number'     => $data['merchantInvoiceNumber'] ?? '',
            'status'             => $data['statusCode'] ?? ''
        );
    }

    /**
     * Standard authorization headers for create/execute/query calls.
     */
    private function authHeaders($idToken)
    {
        return array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: ' . $idToken,
            'X-APP-Key: ' . $this->app_key
        );
    }

    /**
     * Make a JSON POST API request.
     */
    private function makeRequest($endpoint, $body, $headers)
    {
        $url = $this->api_url . $endpoint;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        // SSL verification: enabled in production, disabled in development
        if (ENVIRONMENT === 'production') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            log_message('error', 'bKash API Error (' . $endpoint . '): ' . $error);
            return null;
        }

        return json_decode($response, true);
    }
}
