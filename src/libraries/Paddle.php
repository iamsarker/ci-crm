<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Paddle Billing Payment Library (Merchant of Record)
 *
 * Flow: paddle_init creates a Paddle transaction via the API and returns its
 * id + the client-side token; the browser opens Paddle.js overlay checkout
 * with that transaction id. The overlay stays on the same page (no redirect,
 * so no session loss). The authoritative confirmation arrives at the webhook
 * (transaction.completed / transaction.paid), verified by HMAC signature.
 *
 * Credential storage (payment_gateway table):
 *   - client-side token => public_key      / test_public_key   (Paddle.js)
 *   - API key (Bearer)  => secret_key      / test_secret_key   (server API)
 *   - webhook secret    => webhook_secret  / test_webhook_secret (signature)
 */
class Paddle
{
    private $CI;
    private $api_key;
    private $client_token;
    private $webhook_secret;
    private $is_test_mode;
    private $api_url;

    const SANDBOX_URL = 'https://sandbox-api.paddle.com';
    const LIVE_URL    = 'https://api.paddle.com';

    public function __construct($config = array())
    {
        $this->CI =& get_instance();

        if (empty($config)) {
            $this->CI->load->model('PaymentGateway_model');
            $gateway = $this->CI->PaymentGateway_model->getByCode('paddle');

            if ($gateway) {
                $this->is_test_mode = $gateway['is_test_mode'] == 1;

                // trim(): a key pasted into the admin form commonly carries a
                // trailing newline or space. That lands in the Authorization
                // header verbatim and Paddle rejects the whole request with
                // "Authentication header included, but incorrectly formatted."
                if ($this->is_test_mode) {
                    $this->client_token   = trim($gateway['test_public_key'] ?? '');
                    $this->api_key        = trim($gateway['test_secret_key'] ?? '');
                    $this->webhook_secret = trim($gateway['test_webhook_secret'] ?? '');
                    $this->api_url        = self::SANDBOX_URL;
                } else {
                    $this->client_token   = trim($gateway['public_key'] ?? '');
                    $this->api_key        = trim($gateway['secret_key'] ?? '');
                    $this->webhook_secret = trim($gateway['webhook_secret'] ?? '');
                    $this->api_url        = self::LIVE_URL;
                }
            }
        } else {
            $this->client_token   = $config['client_token'] ?? '';
            $this->api_key        = $config['api_key'] ?? '';
            $this->webhook_secret = $config['webhook_secret'] ?? '';
            $this->is_test_mode   = $config['is_test_mode'] ?? true;
            $this->api_url        = $this->is_test_mode ? self::SANDBOX_URL : self::LIVE_URL;
        }
    }

    /**
     * Server API needs the API key; the overlay needs the client token.
     */
    public function isConfigured()
    {
        return !empty($this->api_key) && !empty($this->client_token);
    }

    public function getClientToken()
    {
        return $this->client_token;
    }

    public function getEnvironment()
    {
        return $this->is_test_mode ? 'sandbox' : 'production';
    }

    /**
     * Create a Paddle transaction with a non-catalog (inline) price.
     *
     * @param array $data amount, currency, description, custom_data (assoc), customer_email
     * @return array ['success'=>bool,'data'=>['transaction_id','status',...]|'error']
     */
    public function createTransaction($data)
    {
        if (!$this->isConfigured()) {
            return array('success' => false, 'error' => 'Paddle not configured');
        }

        // Paddle expects the amount in the currency's minor units, as a string.
        $currency = strtoupper($data['currency'] ?? 'USD');
        $minorAmount = (string) intval(round(((float) $data['amount']) * $this->minorUnitFactor($currency)));
        $desc = $data['description'] ?? 'Invoice Payment';

        $body = array(
            'items' => array(
                array(
                    'quantity' => 1,
                    'price' => array(
                        'description' => $desc,
                        'name'        => $desc,
                        'unit_price'  => array(
                            'amount'        => $minorAmount,
                            'currency_code' => $currency
                        ),
                        'product' => array(
                            'name'         => $data['product_name'] ?? 'Invoice Payment',
                            'tax_category' => 'standard'
                        )
                    )
                )
            ),
            'collection_mode' => 'automatic',
            'custom_data'     => $data['custom_data'] ?? new stdClass()
        );

        if (!empty($data['customer_email'])) {
            $body['customer'] = array('email' => $data['customer_email']);
        }

        $response = $this->apiRequest('POST', '/transactions', $body);

        if ($response && !empty($response['data']['id'])) {
            return array(
                'success' => true,
                'data' => array(
                    'transaction_id' => $response['data']['id'],
                    'status'         => $response['data']['status'] ?? '',
                    'raw'            => $response['data']
                )
            );
        }

        $error = $this->extractApiError($response);

        // Log the machine-readable code alongside the human detail — the code
        // (e.g. authentication_malformed vs authentication_missing vs
        // invalid_credentials) is what distinguishes a bad header from a bad
        // key, and the customer-facing banner only ever shows the detail.
        log_message('error', 'Paddle createTransaction failed ['
            . ($response['error']['code'] ?? 'unknown') . '] mode='
            . $this->getEnvironment() . ' api_key_len=' . strlen($this->api_key)
            . ' detail=' . $error);

        return array('success' => false, 'error' => $error, 'data' => $response);
    }

    /**
     * Fetch a transaction (used by the webhook / reconciliation).
     */
    public function getTransaction($transactionId)
    {
        if (empty($this->api_key)) {
            return array('success' => false, 'error' => 'Paddle not configured');
        }

        $response = $this->apiRequest('GET', '/transactions/' . rawurlencode($transactionId));

        if ($response && !empty($response['data']['id'])) {
            return array('success' => true, 'data' => $response['data']);
        }

        return array('success' => false, 'error' => $this->extractApiError($response), 'data' => $response);
    }

    /**
     * Verify a Paddle webhook signature.
     *
     * @param string $rawBody   The unprocessed request body (php://input)
     * @param string $sigHeader The Paddle-Signature header: "ts=...;h1=..."
     */
    public function verifyWebhookSignature($rawBody, $sigHeader)
    {
        if (empty($this->webhook_secret) || empty($sigHeader)) {
            return false;
        }

        // Parse "ts=...;h1=..."
        $ts = null; $h1 = null;
        foreach (explode(';', $sigHeader) as $part) {
            $kv = explode('=', trim($part), 2);
            if (count($kv) === 2) {
                if ($kv[0] === 'ts') $ts = $kv[1];
                if ($kv[0] === 'h1') $h1 = $kv[1];
            }
        }

        if (empty($ts) || empty($h1)) {
            return false;
        }

        $signedPayload = $ts . ':' . $rawBody;
        $computed = hash_hmac('sha256', $signedPayload, $this->webhook_secret);

        return hash_equals($computed, $h1);
    }

    /**
     * Normalize a Paddle transaction webhook payload into common fields.
     */
    public function extractPaymentDetails($event)
    {
        $data = $event['data'] ?? array();
        $totals = $data['details']['totals'] ?? array();
        $currency = $totals['currency_code'] ?? ($data['currency_code'] ?? 'USD');

        $factor = $this->minorUnitFactor($currency);

        return array(
            'transaction_id' => $data['id'] ?? '',
            'status'         => $data['status'] ?? '',
            'amount'         => isset($totals['grand_total']) ? ($totals['grand_total'] / $factor) : 0,
            'currency'       => $currency,
            'custom_data'    => $data['custom_data'] ?? array(),
            'event_id'       => $event['event_id'] ?? '',
            'event_type'     => $event['event_type'] ?? '',

            // Paddle's own references — the pairing an accountant needs to
            // reconcile our invoice against Paddle's payout statement.
            'paddle_invoice_id'     => $data['invoice_id'] ?? '',
            'paddle_invoice_number' => $data['invoice_number'] ?? '',
            'paddle_customer_id'    => $data['customer_id'] ?? '',
            'billed_at'             => $data['billed_at'] ?? '',

            // Merchant-of-Record economics. Paddle reports these in minor units
            // AS STRINGS ('55' = $0.55). They are what actually lands in the
            // payout, so storing them turns gross figures into real revenue.
            // Null (not zero) when absent — on some transactions Paddle only
            // finalises these at payout time, and 0.00 would be a lie.
            'fee'      => isset($totals['fee'])      ? ($totals['fee'] / $factor)      : null,
            'earnings' => isset($totals['earnings']) ? ($totals['earnings'] / $factor) : null,
            'tax'      => isset($totals['tax'])      ? ($totals['tax'] / $factor)      : null,
        );
    }

    /**
     * Pull card/payment-method details out of a transaction webhook payload.
     * Mirrors Stripe::extractCardDetails() so both webhooks populate the same
     * payment_transactions columns.
     *
     * ⚠️ data.payments[] holds EVERY attempt, not just the successful one — a
     * real payload here carried a captured Visa alongside an 'action_required'
     * Google Pay entry. Selecting [0] would be right by luck at best, so the
     * captured payment is matched by status.
     *
     * @return array card_brand, card_last4, card_exp_month, card_exp_year,
     *               cardholder_name, method_type — empty array if none captured.
     */
    public function extractCardDetails($event)
    {
        $data     = $event['data'] ?? $event;
        $payments = $data['payments'] ?? array();
        if (!is_array($payments) || empty($payments)) {
            return array();
        }

        $chosen = null;
        foreach ($payments as $payment) {
            $status = $payment['status'] ?? '';
            if ($status === 'captured' || $status === 'completed') {
                $chosen = $payment;
                break;
            }
        }

        if ($chosen === null) {
            return array();
        }

        $details = $chosen['method_details'] ?? array();
        $card    = $details['card'] ?? array();

        return array(
            'method_type'      => $details['type'] ?? '',
            'card_brand'       => $card['type'] ?? '',
            'card_last4'       => $card['last4'] ?? '',
            'card_exp_month'   => $card['expiry_month'] ?? null,
            'card_exp_year'    => $card['expiry_year'] ?? null,
            'cardholder_name'  => $card['cardholder_name'] ?? '',
        );
    }

    /**
     * Convert a Paddle minor-unit amount (string or int) to major units.
     * Public so callers verifying against the API response use the same
     * zero-decimal table as the webhook payload path, rather than repeating it.
     */
    public function minorToMajor($amount, $currency)
    {
        return ((float) $amount) / $this->minorUnitFactor($currency);
    }

    /**
     * Minor-unit factor for a currency: zero-decimal currencies (e.g. JPY, KRW)
     * have no minor unit so the factor is 1; everything else is 100.
     * Used for BOTH outbound (major->minor) and inbound (minor->major) conversion
     * so the two directions can never disagree.
     */
    private function minorUnitFactor($currency)
    {
        $zeroDecimal = array('BIF', 'CLP', 'DJF', 'GNF', 'ISK', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF');
        return in_array(strtoupper($currency), $zeroDecimal) ? 1 : 100;
    }

    /**
     * Make a Paddle API request.
     */
    private function apiRequest($method, $endpoint, $body = null)
    {
        $url = $this->api_url . $endpoint;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $this->api_key,
            'Content-Type: application/json',
            'Accept: application/json'
        ));

        if ($method !== 'GET' && $body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

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
            log_message('error', 'Paddle API Error (' . $endpoint . '): ' . $error);
            return null;
        }

        return json_decode($response, true);
    }

    /**
     * Pull a human-readable error out of a Paddle API error response.
     */
    private function extractApiError($response)
    {
        if (!empty($response['error']['detail'])) {
            return $response['error']['detail'];
        }
        if (!empty($response['error']['code'])) {
            return $response['error']['code'];
        }
        return 'Failed to create Paddle transaction';
    }
}
