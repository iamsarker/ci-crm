<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PayHere Payment Library (Sri Lanka)
 *
 * Handles PayHere Checkout API integration. PayHere is a redirect gateway:
 * the customer is sent to PayHere's checkout via a form POST, and the
 * authoritative payment confirmation arrives server-to-server at notify_url
 * (the IPN), similar to SSLCommerz.
 *
 * Credential storage (payment_gateway table):
 *   - merchant_id     => public_key      / test_public_key
 *   - merchant_secret => secret_key      / test_secret_key
 */
class Payhere
{
    private $CI;
    private $merchant_id;
    private $merchant_secret;
    private $is_test_mode;
    private $checkout_url;

    const SANDBOX_URL = 'https://sandbox.payhere.lk/pay/checkout';
    const LIVE_URL    = 'https://www.payhere.lk/pay/checkout';

    public function __construct($config = array())
    {
        $this->CI =& get_instance();

        if (empty($config)) {
            $this->CI->load->model('PaymentGateway_model');
            $gateway = $this->CI->PaymentGateway_model->getByCode('payhere');

            if ($gateway) {
                $this->is_test_mode = $gateway['is_test_mode'] == 1;

                if ($this->is_test_mode) {
                    $this->merchant_id     = $gateway['test_public_key'] ?? '';
                    $this->merchant_secret = $gateway['test_secret_key'] ?? '';
                    $this->checkout_url    = self::SANDBOX_URL;
                } else {
                    $this->merchant_id     = $gateway['public_key'] ?? '';
                    $this->merchant_secret = $gateway['secret_key'] ?? '';
                    $this->checkout_url    = self::LIVE_URL;
                }
            }
        } else {
            $this->merchant_id     = $config['merchant_id'] ?? '';
            $this->merchant_secret = $config['merchant_secret'] ?? '';
            $this->is_test_mode    = $config['is_test_mode'] ?? true;
            $this->checkout_url    = $this->is_test_mode ? self::SANDBOX_URL : self::LIVE_URL;
        }
    }

    /**
     * Check if library is configured
     */
    public function isConfigured()
    {
        return !empty($this->merchant_id) && !empty($this->merchant_secret);
    }

    /**
     * The checkout endpoint the browser form must POST to.
     */
    public function getCheckoutUrl()
    {
        return $this->checkout_url;
    }

    /**
     * Build the checkout form parameters (including the signed hash).
     *
     * @param array $data order_id, amount, currency, items,
     *                    first_name, last_name, email, phone, address, city, country,
     *                    return_url, cancel_url, notify_url, custom_1, custom_2
     */
    public function buildCheckoutParams($data)
    {
        if (!$this->isConfigured()) {
            return array('success' => false, 'error' => 'PayHere not configured');
        }

        $amount   = number_format((float) $data['amount'], 2, '.', '');
        $currency = $data['currency'] ?? 'LKR';
        $orderId  = (string) $data['order_id'];

        // hash = UPPER( md5( merchant_id + order_id + amount + currency + UPPER(md5(merchant_secret)) ) )
        $hash = strtoupper(
            md5(
                $this->merchant_id .
                $orderId .
                $amount .
                $currency .
                strtoupper(md5($this->merchant_secret))
            )
        );

        $params = array(
            'merchant_id' => $this->merchant_id,
            'return_url'  => $data['return_url'],
            'cancel_url'  => $data['cancel_url'],
            'notify_url'  => $data['notify_url'],
            'order_id'    => $orderId,
            'items'       => $data['items'] ?? 'Invoice Payment',
            'currency'    => $currency,
            'amount'      => $amount,
            'first_name'  => $data['first_name'] ?? 'Customer',
            'last_name'   => $data['last_name'] ?? '',
            'email'       => $data['email'] ?? '',
            'phone'       => $data['phone'] ?? '',
            'address'     => $data['address'] ?? '',
            'city'        => $data['city'] ?? '',
            'country'     => $data['country'] ?? 'Sri Lanka',
            'custom_1'    => $data['custom_1'] ?? '',
            'custom_2'    => $data['custom_2'] ?? '',
            'hash'        => $hash
        );

        return array(
            'success' => true,
            'data' => array(
                'checkout_url' => $this->checkout_url,
                'params'       => $params
            )
        );
    }

    /**
     * Verify an IPN (notify_url) POST from PayHere.
     * Confirms the md5sig signature AND that status_code == 2 (success).
     */
    public function verifyNotification($post)
    {
        $merchantId     = $post['merchant_id'] ?? '';
        $orderId        = $post['order_id'] ?? '';
        $payhereAmount  = $post['payhere_amount'] ?? '';
        $payhereCurrency= $post['payhere_currency'] ?? '';
        $statusCode     = $post['status_code'] ?? '';
        $receivedSig    = $post['md5sig'] ?? '';

        if (empty($receivedSig)) {
            return array('success' => false, 'error' => 'Missing signature');
        }

        $localSig = strtoupper(
            md5(
                $merchantId .
                $orderId .
                $payhereAmount .
                $payhereCurrency .
                $statusCode .
                strtoupper(md5($this->merchant_secret))
            )
        );

        if (!hash_equals($localSig, $receivedSig)) {
            return array('success' => false, 'error' => 'Signature mismatch');
        }

        // status_code: 2=success, 0=pending, -1=canceled, -2=failed, -3=chargedback
        if ((string) $statusCode !== '2') {
            return array('success' => false, 'error' => 'Payment not successful', 'status_code' => $statusCode);
        }

        return array('success' => true, 'data' => $post, 'status_code' => $statusCode);
    }

    /**
     * Human-readable label for a PayHere status_code.
     */
    public function statusLabel($statusCode)
    {
        switch ((string) $statusCode) {
            case '2':  return 'Completed';
            case '0':  return 'Pending';
            case '-1': return 'Canceled';
            case '-2': return 'Failed';
            case '-3': return 'Chargedback';
            default:   return 'Unknown';
        }
    }

    /**
     * Normalize IPN fields into common payment details.
     */
    public function extractPaymentDetails($post)
    {
        return array(
            'transaction_id' => $post['payment_id'] ?? '',
            'order_id'       => $post['order_id'] ?? '',
            'amount'         => $post['payhere_amount'] ?? 0,
            'currency'       => $post['payhere_currency'] ?? 'LKR',
            'payment_method' => $post['method'] ?? 'payhere',
            'status_code'    => $post['status_code'] ?? '',
            'status_message' => $post['status_message'] ?? '',
            'custom_1'       => $post['custom_1'] ?? '',
            'custom_2'       => $post['custom_2'] ?? ''
        );
    }
}
