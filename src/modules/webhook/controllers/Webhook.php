<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Webhook Controller
 *
 * Handles payment gateway webhooks/callbacks
 * These endpoints are public (no authentication required)
 */
class Webhook extends WHMAZ_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('Payment_model');
        $this->load->model('PaymentGateway_model');
        $this->load->model('Invoice_model');
    }

    /**
     * Stripe Webhook Handler
     * URL: /webhook/stripe
     */
    public function stripe()
    {
        // Get raw input
        $payload = file_get_contents('php://input');
        $signature = isset($_SERVER['HTTP_STRIPE_SIGNATURE']) ? $_SERVER['HTTP_STRIPE_SIGNATURE'] : '';

        // Log the webhook
        $webhookId = $this->Payment_model->logWebhook(
            'stripe',
            null,
            $payload,
            $this->getRequestHeaders(),
            $signature
        );

        // Load Stripe library
        $this->load->library('Stripe');

        // Verify signature
        $event = $this->stripe->verifyWebhook($payload, $signature);

        if (!$event) {
            $this->Payment_model->markWebhookProcessed($webhookId, false, 'Invalid signature');
            $this->sendResponse(400, 'Invalid signature');
            return;
        }

        // Check if already processed
        if ($this->Payment_model->isWebhookProcessed($event['id'], 'stripe')) {
            $this->Payment_model->markWebhookProcessed($webhookId, true, 'Already processed');
            $this->sendResponse(200, 'Already processed');
            return;
        }

        // Update webhook log with event type
        $this->db->where('id', $webhookId);
        $this->db->update('webhook_logs', array(
            'event_type' => $event['type'],
            'event_id' => $event['id']
        ));

        // Process event
        $result = $this->processStripeEvent($event);

        $this->Payment_model->markWebhookProcessed($webhookId, true, $result);
        $this->sendResponse(200, $result);
    }

    /**
     * Process Stripe webhook event
     */
    private function processStripeEvent($event)
    {
        $eventType = $event['type'];
        $data = $event['data']['object'];

        switch ($eventType) {
            case 'payment_intent.succeeded':
                return $this->handleStripePaymentSuccess($data);

            case 'payment_intent.payment_failed':
                return $this->handleStripePaymentFailed($data);

            case 'checkout.session.completed':
                return $this->handleStripeCheckoutComplete($data);

            case 'charge.refunded':
                return $this->handleStripeRefund($data);

            default:
                log_message('info', 'Stripe webhook: Unhandled event type - ' . $eventType);
                return 'Event type not handled: ' . $eventType;
        }
    }

    /**
     * Handle Stripe payment_intent.succeeded
     */
    private function handleStripePaymentSuccess($data)
    {
        $paymentIntentId = $data['id'];

        // Find transaction by gateway_order_id first (where PaymentIntent ID is stored during init)
        $transaction = $this->Payment_model->getByGatewayOrderId($paymentIntentId, 'stripe');

        // Fallback to gateway_transaction_id
        if (!$transaction) {
            $transaction = $this->Payment_model->getByGatewayTxnId($paymentIntentId, 'stripe');
        }

        if (!$transaction) {
            // Try metadata
            if (isset($data['metadata']['transaction_id'])) {
                $transaction = $this->Payment_model->getTransactionById($data['metadata']['transaction_id']);
            }
        }

        if (!$transaction) {
            log_message('error', 'Stripe webhook: Transaction not found for PI ' . $paymentIntentId);
            return 'Transaction not found';
        }

        // Update transaction
        $updateData = array(
            'status' => 'completed',
            'gateway_transaction_id' => $paymentIntentId,
            'payment_method' => isset($data['payment_method_types'][0]) ? $data['payment_method_types'][0] : 'card',
            'gateway_response' => $data
        );

        // Extract card details using library method (handles both old and new API formats)
        $card = $this->stripe->extractCardDetails($data);
        if (!empty($card)) {
            $updateData['card_brand'] = $card['brand'];
            $updateData['card_last4'] = $card['last4'];
            $updateData['card_exp_month'] = $card['exp_month'];
            $updateData['card_exp_year'] = $card['exp_year'];
        }

        $this->Payment_model->updateTransactionStatus($transaction['id'], 'completed', $updateData);

        // Process successful payment (update invoice, provision services)
        $this->Payment_model->processSuccessfulPayment($transaction['id']);
        $this->Payment_model->recordInvoiceTxn($transaction['id']);

        log_message('info', 'Stripe webhook: Payment succeeded for transaction #' . $transaction['id']);
        return 'Payment processed successfully';
    }

    /**
     * Handle Stripe payment_intent.payment_failed
     */
    private function handleStripePaymentFailed($data)
    {
        $paymentIntentId = $data['id'];

        $transaction = $this->Payment_model->getByGatewayTxnId($paymentIntentId, 'stripe');

        if (!$transaction) {
            return 'Transaction not found';
        }

        $failureReason = isset($data['last_payment_error']['message']) ? $data['last_payment_error']['message'] : 'Payment failed';

        $this->Payment_model->updateTransactionStatus($transaction['id'], 'failed', array(
            'failure_reason' => $failureReason,
            'gateway_response' => $data
        ));

        log_message('info', 'Stripe webhook: Payment failed for transaction #' . $transaction['id'] . ' - ' . $failureReason);
        return 'Payment failure recorded';
    }

    /**
     * Handle Stripe checkout.session.completed
     */
    private function handleStripeCheckoutComplete($data)
    {
        $sessionId = $data['id'];
        $paymentIntentId = isset($data['payment_intent']) ? $data['payment_intent'] : null;

        // Find transaction
        $transaction = $this->Payment_model->getByGatewayOrderId($sessionId, 'stripe');

        if (!$transaction && isset($data['metadata']['transaction_id'])) {
            $transaction = $this->Payment_model->getTransactionById($data['metadata']['transaction_id']);
        }

        if (!$transaction) {
            return 'Transaction not found';
        }

        // Update with payment intent ID
        $updateData = array(
            'status' => 'completed',
            'gateway_transaction_id' => $paymentIntentId,
            'payer_email' => isset($data['customer_details']['email']) ? $data['customer_details']['email'] : null,
            'payer_name' => isset($data['customer_details']['name']) ? $data['customer_details']['name'] : null,
            'gateway_response' => $data
        );

        $this->Payment_model->updateTransactionStatus($transaction['id'], 'completed', $updateData);

        // Process successful payment
        $this->Payment_model->processSuccessfulPayment($transaction['id']);
        $this->Payment_model->recordInvoiceTxn($transaction['id']);

        log_message('info', 'Stripe webhook: Checkout completed for transaction #' . $transaction['id']);
        return 'Checkout processed successfully';
    }

    /**
     * Handle Stripe charge.refunded
     */
    private function handleStripeRefund($data)
    {
        $chargeId = $data['id'];
        $paymentIntentId = $data['payment_intent'];

        $transaction = $this->Payment_model->getByGatewayTxnId($paymentIntentId, 'stripe');

        if (!$transaction) {
            return 'Transaction not found';
        }

        // Check if fully refunded
        if ($data['refunded'] && $data['amount_refunded'] >= $data['amount']) {
            $this->Payment_model->updateTransactionStatus($transaction['id'], 'refunded', array(
                'gateway_response' => $data
            ));
        }

        log_message('info', 'Stripe webhook: Refund processed for transaction #' . $transaction['id']);
        return 'Refund processed';
    }

    /**
     * PayPal Webhook Handler
     * URL: /webhook/paypal
     */
    public function paypal()
    {
        // Get raw input
        $payload = file_get_contents('php://input');
        $headers = $this->getRequestHeaders();

        // Log the webhook
        $webhookId = $this->Payment_model->logWebhook(
            'paypal',
            null,
            $payload,
            $headers,
            isset($headers['paypal-transmission-sig']) ? $headers['paypal-transmission-sig'] : null
        );

        // Load PayPal library
        $this->load->library('Paypal');

        // Verify webhook
        if (!$this->paypal->verifyWebhook($headers, $payload)) {
            $this->Payment_model->markWebhookProcessed($webhookId, false, 'Invalid signature');
            $this->sendResponse(400, 'Invalid signature');
            return;
        }

        // Parse event
        $event = $this->paypal->parseWebhookEvent($payload);

        if (!$event) {
            $this->Payment_model->markWebhookProcessed($webhookId, false, 'Invalid payload');
            $this->sendResponse(400, 'Invalid payload');
            return;
        }

        // Check if already processed
        if ($this->Payment_model->isWebhookProcessed($event['id'], 'paypal')) {
            $this->Payment_model->markWebhookProcessed($webhookId, true, 'Already processed');
            $this->sendResponse(200, 'Already processed');
            return;
        }

        // Update webhook log
        $this->db->where('id', $webhookId);
        $this->db->update('webhook_logs', array(
            'event_type' => $event['event_type'],
            'event_id' => $event['id']
        ));

        // Process event
        $result = $this->processPayPalEvent($event);

        $this->Payment_model->markWebhookProcessed($webhookId, true, $result);
        $this->sendResponse(200, $result);
    }

    /**
     * Process PayPal webhook event
     */
    private function processPayPalEvent($event)
    {
        $eventType = $event['event_type'];
        $resource = $event['resource'];

        switch ($eventType) {
            case 'CHECKOUT.ORDER.APPROVED':
                return $this->handlePayPalOrderApproved($resource);

            case 'PAYMENT.CAPTURE.COMPLETED':
                return $this->handlePayPalCaptureCompleted($resource);

            case 'PAYMENT.CAPTURE.DENIED':
            case 'PAYMENT.CAPTURE.DECLINED':
                return $this->handlePayPalCaptureFailed($resource);

            case 'PAYMENT.CAPTURE.REFUNDED':
                return $this->handlePayPalRefund($resource);

            default:
                log_message('info', 'PayPal webhook: Unhandled event type - ' . $eventType);
                return 'Event type not handled: ' . $eventType;
        }
    }

    /**
     * Handle PayPal CHECKOUT.ORDER.APPROVED
     */
    private function handlePayPalOrderApproved($resource)
    {
        $orderId = $resource['id'];

        // Find transaction
        $transaction = $this->Payment_model->getByGatewayOrderId($orderId, 'paypal');

        if (!$transaction) {
            return 'Transaction not found';
        }

        // Auto-capture the order
        $this->load->library('Paypal');
        $captureResult = $this->paypal->captureOrder($orderId);

        if ($captureResult['success']) {
            $details = $this->paypal->extractPaymentDetails($captureResult);

            $this->Payment_model->updateTransactionStatus($transaction['id'], 'completed', array(
                'gateway_transaction_id' => $details['transaction_id'],
                'payer_email' => $details['payer_email'],
                'payer_name' => $details['payer_name'],
                'gateway_response' => $captureResult['data']
            ));

            // Process successful payment
            $this->Payment_model->processSuccessfulPayment($transaction['id']);
            $this->Payment_model->recordInvoiceTxn($transaction['id']);

            log_message('info', 'PayPal webhook: Order captured for transaction #' . $transaction['id']);
            return 'Order captured successfully';
        } else {
            log_message('error', 'PayPal webhook: Failed to capture order - ' . $captureResult['error']);
            return 'Capture failed: ' . $captureResult['error'];
        }
    }

    /**
     * Handle PayPal PAYMENT.CAPTURE.COMPLETED
     */
    private function handlePayPalCaptureCompleted($resource)
    {
        $captureId = $resource['id'];

        // Extract order ID from links
        $orderId = null;
        if (isset($resource['supplementary_data']['related_ids']['order_id'])) {
            $orderId = $resource['supplementary_data']['related_ids']['order_id'];
        }

        // Find transaction
        $transaction = null;
        if ($orderId) {
            $transaction = $this->Payment_model->getByGatewayOrderId($orderId, 'paypal');
        }
        if (!$transaction) {
            $transaction = $this->Payment_model->getByGatewayTxnId($captureId, 'paypal');
        }

        if (!$transaction) {
            return 'Transaction not found';
        }

        // Already completed?
        if ($transaction['status'] === 'completed') {
            return 'Already processed';
        }

        $this->Payment_model->updateTransactionStatus($transaction['id'], 'completed', array(
            'gateway_transaction_id' => $captureId,
            'gateway_response' => $resource
        ));

        // Process successful payment
        $this->Payment_model->processSuccessfulPayment($transaction['id']);
        $this->Payment_model->recordInvoiceTxn($transaction['id']);

        log_message('info', 'PayPal webhook: Capture completed for transaction #' . $transaction['id']);
        return 'Payment processed successfully';
    }

    /**
     * Handle PayPal capture failed
     */
    private function handlePayPalCaptureFailed($resource)
    {
        $captureId = $resource['id'];

        $transaction = $this->Payment_model->getByGatewayTxnId($captureId, 'paypal');

        if (!$transaction) {
            return 'Transaction not found';
        }

        $this->Payment_model->updateTransactionStatus($transaction['id'], 'failed', array(
            'failure_reason' => 'Payment capture failed',
            'gateway_response' => $resource
        ));

        log_message('info', 'PayPal webhook: Capture failed for transaction #' . $transaction['id']);
        return 'Failure recorded';
    }

    /**
     * Handle PayPal refund
     */
    private function handlePayPalRefund($resource)
    {
        $refundId = $resource['id'];
        $captureId = isset($resource['links'][0]['href']) ? basename(parse_url($resource['links'][0]['href'], PHP_URL_PATH)) : null;

        // Find original transaction
        $transaction = $this->Payment_model->getByGatewayTxnId($captureId, 'paypal');

        if ($transaction) {
            // Create refund record
            $this->Payment_model->createRefund(array(
                'transaction_id' => $transaction['id'],
                'invoice_id' => $transaction['invoice_id'],
                'gateway_refund_id' => $refundId,
                'amount' => floatval($resource['amount']['value']),
                'currency_code' => $resource['amount']['currency_code'],
                'status' => 'completed',
                'gateway_response' => $resource
            ));

            log_message('info', 'PayPal webhook: Refund processed for transaction #' . $transaction['id']);
            return 'Refund processed';
        }

        return 'Original transaction not found';
    }

    /**
     * Get all request headers
     */
    private function getRequestHeaders()
    {
        $headers = array();

        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$header] = $value;
            }
        }

        return $headers;
    }

    /**
     * SSLCommerz IPN Handler
     * URL: /webhook/sslcommerz
     */
    public function sslcommerz()
    {
        // Get raw input
        $payload = file_get_contents('php://input');

        // Log the webhook
        $webhookId = $this->Payment_model->logWebhook(
            'sslcommerz',
            $_POST['status'] ?? 'unknown',
            $payload,
            $this->getRequestHeaders(),
            null
        );

        // Get transaction UUID from value_a
        $transactionUuid = $_POST['value_a'] ?? null;
        $status = $_POST['status'] ?? '';

        if (empty($transactionUuid)) {
            $this->Payment_model->markWebhookProcessed($webhookId, false, 'Missing transaction reference');
            $this->sendResponse(400, 'Missing transaction reference');
            return;
        }

        $transaction = $this->Payment_model->getTransactionByUuid($transactionUuid);

        if (!$transaction) {
            $this->Payment_model->markWebhookProcessed($webhookId, false, 'Transaction not found');
            $this->sendResponse(400, 'Transaction not found');
            return;
        }

        // Already completed
        if ($transaction['status'] === 'completed') {
            $this->Payment_model->markWebhookProcessed($webhookId, true, 'Already processed');
            $this->sendResponse(200, 'Already processed');
            return;
        }

        if ($status === 'VALID' || $status === 'VALIDATED') {
            // Validate with SSLCommerz
            $this->load->library('Sslcommerz');
            $validation = $this->sslcommerz->validateIPN($_POST);

            if ($validation['success']) {
                $details = $this->sslcommerz->extractPaymentDetails($validation);

                $this->Payment_model->updateTransactionStatus($transaction['id'], 'completed', array(
                    'gateway_transaction_id' => $details['bank_tran_id'],
                    'payment_method' => $details['card_type'] ?? 'sslcommerz',
                    'gateway_response' => $validation['data']
                ));

                $this->Payment_model->processSuccessfulPayment($transaction['id']);
                $this->Payment_model->recordInvoiceTxn($transaction['id']);

                $this->Payment_model->markWebhookProcessed($webhookId, true, 'Payment completed');
                log_message('info', 'SSLCommerz IPN: Payment completed for transaction #' . $transaction['id']);
            } else {
                $this->Payment_model->markWebhookProcessed($webhookId, false, 'Validation failed');
            }
        } elseif ($status === 'FAILED') {
            $this->Payment_model->updateTransactionStatus($transaction['id'], 'failed', array(
                'failure_reason' => $_POST['error'] ?? 'Payment failed',
                'gateway_response' => $_POST
            ));
            $this->Payment_model->markWebhookProcessed($webhookId, true, 'Payment failed');
            log_message('info', 'SSLCommerz IPN: Payment failed for transaction #' . $transaction['id']);
        } elseif ($status === 'CANCELLED') {
            $this->Payment_model->updateTransactionStatus($transaction['id'], 'cancelled', array(
                'gateway_response' => $_POST
            ));
            $this->Payment_model->markWebhookProcessed($webhookId, true, 'Payment cancelled');
            log_message('info', 'SSLCommerz IPN: Payment cancelled for transaction #' . $transaction['id']);
        }

        $this->sendResponse(200, 'IPN Received');
    }

    /**
     * PayHere IPN Handler (Sri Lanka)
     * URL: /webhook/payhere  (this is the authoritative payment confirmation)
     */
    public function payhere()
    {
        $payload = file_get_contents('php://input');

        $webhookId = $this->Payment_model->logWebhook(
            'payhere',
            $_POST['status_code'] ?? 'unknown',
            !empty($payload) ? $payload : json_encode($_POST),
            $this->getRequestHeaders(),
            $_POST['md5sig'] ?? null
        );

        // order_id was set to the transaction uuid during init
        $transactionUuid = $_POST['order_id'] ?? null;
        $statusCode = $_POST['status_code'] ?? '';

        if (empty($transactionUuid)) {
            $this->Payment_model->markWebhookProcessed($webhookId, false, 'Missing order reference');
            $this->sendResponse(400, 'Missing order reference');
            return;
        }

        $transaction = $this->Payment_model->getTransactionByUuid($transactionUuid);

        if (!$transaction) {
            $this->Payment_model->markWebhookProcessed($webhookId, false, 'Transaction not found');
            $this->sendResponse(400, 'Transaction not found');
            return;
        }

        // Already completed
        if ($transaction['status'] === 'completed') {
            $this->Payment_model->markWebhookProcessed($webhookId, true, 'Already processed');
            $this->sendResponse(200, 'Already processed');
            return;
        }

        $this->load->library('Payhere');
        $verification = $this->payhere->verifyNotification($_POST);

        if ($verification['success']) {
            // Signature valid AND status_code == 2 (success)
            $details = $this->payhere->extractPaymentDetails($_POST);

            // Anti-tamper: the amount/currency PayHere actually charged must match this
            // transaction. The md5sig only proves PayHere sent it — it does not bind the
            // amount to our order unless the merchant enabled checkout-hash enforcement.
            $paidAmount = (float) ($_POST['payhere_amount'] ?? 0);
            $paidCurrency = $_POST['payhere_currency'] ?? '';
            if ($paidAmount + 0.01 < (float) $transaction['amount'] || $paidCurrency !== $transaction['currency_code']) {
                log_message('error', 'PayHere amount/currency mismatch for ' . $transaction['transaction_uuid']
                    . ': charged ' . $paidAmount . ' ' . $paidCurrency
                    . ', expected ' . $transaction['amount'] . ' ' . $transaction['currency_code']);
                $this->Payment_model->markWebhookProcessed($webhookId, false, 'Amount/currency mismatch');
                $this->sendResponse(200, 'Amount mismatch');
                return;
            }

            $this->Payment_model->updateTransactionStatus($transaction['id'], 'completed', array(
                'gateway_transaction_id' => $details['transaction_id'],
                'payment_method' => $details['payment_method'] ?: 'payhere',
                'gateway_response' => $_POST
            ));

            $this->Payment_model->processSuccessfulPayment($transaction['id']);
            $this->Payment_model->recordInvoiceTxn($transaction['id']);

            $this->Payment_model->markWebhookProcessed($webhookId, true, 'Payment completed');
            log_message('info', 'PayHere IPN: Payment completed for transaction #' . $transaction['id']);
        } else {
            // Signature mismatch is a hard failure (do not touch the transaction)
            if (($verification['error'] ?? '') === 'Signature mismatch') {
                $this->Payment_model->markWebhookProcessed($webhookId, false, 'Signature mismatch');
                $this->sendResponse(400, 'Invalid signature');
                return;
            }

            // Valid signature but a non-success status: -1 cancelled, -2 failed, -3 chargedback
            if ((string) $statusCode === '-1') {
                $this->Payment_model->updateTransactionStatus($transaction['id'], 'cancelled', array('gateway_response' => $_POST));
            } elseif ((string) $statusCode === '-3') {
                $this->Payment_model->updateTransactionStatus($transaction['id'], 'refunded', array('gateway_response' => $_POST));
            } elseif ((string) $statusCode === '-2') {
                $this->Payment_model->updateTransactionStatus($transaction['id'], 'failed', array(
                    'failure_reason' => $_POST['status_message'] ?? 'Payment failed',
                    'gateway_response' => $_POST
                ));
            }
            // status_code 0 (pending): leave as-is; a later IPN will finalize it
            $this->Payment_model->markWebhookProcessed($webhookId, true, 'Status ' . $statusCode);
            log_message('info', 'PayHere IPN: status ' . $statusCode . ' for transaction #' . $transaction['id']);
        }

        $this->sendResponse(200, 'IPN Received');
    }

    /**
     * Paddle Billing Webhook Handler
     * URL: /webhook/paddle  (authoritative payment confirmation)
     */
    public function paddle()
    {
        // Defense-in-depth: only accept deliveries from Paddle's published live IPs
        // (the HMAC signature is still verified below — this is an additional gate).
        // NOTE: if this app sits behind a reverse proxy / CDN (Cloudflare, load
        // balancer, etc.), set $config['proxy_ips'] in config.php so CI resolves the
        // real client IP from X-Forwarded-For; otherwise ip_address() returns the
        // proxy's IP and legitimate Paddle deliveries would be rejected here.
        $clientIp = $this->input->ip_address();
        if (!$this->paddleWebhookIpAllowed($clientIp)) {
            log_message('error', 'Paddle webhook rejected: source IP ' . $clientIp . ' not in Paddle allowlist');
            $this->sendResponse(403, 'Forbidden');
            return;
        }

        $payload = file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_PADDLE_SIGNATURE'] ?? '';

        $event = json_decode($payload, true);
        $eventType = $event['event_type'] ?? 'unknown';

        $webhookId = $this->Payment_model->logWebhook(
            'paddle',
            $eventType,
            $payload,
            $this->getRequestHeaders(),
            $sigHeader
        );

        // Verify HMAC signature before trusting anything
        $this->load->library('Paddle');
        if (!$this->paddle->verifyWebhookSignature($payload, $sigHeader)) {
            $this->Payment_model->markWebhookProcessed($webhookId, false, 'Signature verification failed');
            $this->sendResponse(400, 'Invalid signature');
            return;
        }

        $details = $this->paddle->extractPaymentDetails($event);

        // Idempotency: skip an event we already processed (Paddle retries deliveries).
        if (!empty($details['event_id']) && $this->Payment_model->isWebhookProcessed($details['event_id'], 'paddle')) {
            $this->sendResponse(200, 'Already processed');
            return;
        }

        // Act only on the single terminal event. Paddle emits transaction.paid AND
        // transaction.completed for one payment; handling both would double-process.
        if ($eventType !== 'transaction.completed') {
            $this->Payment_model->markWebhookProcessed($webhookId, true, 'Ignored event: ' . $eventType);
            $this->sendResponse(200, 'Event ignored');
            return;
        }

        // Map back to our transaction: prefer custom_data.transaction_uuid, fallback to Paddle txn id
        $transactionUuid = $details['custom_data']['transaction_uuid'] ?? null;
        $transaction = null;
        if (!empty($transactionUuid)) {
            $transaction = $this->Payment_model->getTransactionByUuid($transactionUuid);
        }
        if (!$transaction && !empty($details['transaction_id'])) {
            $transaction = $this->Payment_model->getByGatewayOrderId($details['transaction_id'], 'paddle');
        }

        if (!$transaction) {
            $this->Payment_model->markWebhookProcessed($webhookId, false, 'Transaction not found');
            $this->sendResponse(200, 'Transaction not found');
            return;
        }

        // Never resurrect a terminal transaction (a replayed completed event must not
        // re-pay an invoice that was since refunded or cancelled).
        if (in_array($transaction['status'], array('completed', 'refunded', 'cancelled'))) {
            $this->Payment_model->markWebhookProcessed($webhookId, true, 'Already ' . $transaction['status']);
            $this->sendResponse(200, 'Already ' . $transaction['status']);
            return;
        }

        // Anti-tamper: reject underpayment. Paddle is a Merchant of Record and may add
        // sales tax/VAT on top of our amount, so grand_total can legitimately exceed it —
        // only a payment for LESS than the transaction amount is suspicious.
        if ((float) $details['amount'] + 0.01 < (float) $transaction['amount']
            || strtoupper($details['currency']) !== strtoupper($transaction['currency_code'])) {
            log_message('error', 'Paddle amount/currency mismatch for ' . $transaction['transaction_uuid']
                . ': charged ' . $details['amount'] . ' ' . $details['currency']
                . ', expected ' . $transaction['amount'] . ' ' . $transaction['currency_code']);
            $this->Payment_model->markWebhookProcessed($webhookId, false, 'Amount/currency mismatch');
            $this->sendResponse(200, 'Amount mismatch');
            return;
        }

        $this->Payment_model->updateTransactionStatus($transaction['id'], 'completed', array(
            'gateway_transaction_id' => $details['transaction_id'],
            'payment_method' => 'paddle',
            'gateway_response' => $event['data'] ?? $event
        ));

        $this->Payment_model->processSuccessfulPayment($transaction['id']);
        $this->Payment_model->recordInvoiceTxn($transaction['id']);

        $this->Payment_model->markWebhookProcessed($webhookId, true, 'Payment completed');
        log_message('info', 'Paddle webhook: Payment completed for transaction #' . $transaction['id']);

        $this->sendResponse(200, 'OK');
    }

    /**
     * Send JSON response
     */
    private function sendResponse($statusCode, $message)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(array('message' => $message));
        exit;
    }

    /**
     * Whether $ip falls within Paddle's published live webhook IP ranges.
     *
     * The list is fetched from https://api.paddle.com/ips (the authoritative
     * source, which can change over time) and cached locally — never hard-coded.
     * On a fetch failure the last cached list is used; if no list can be
     * established at all, the check is skipped (returns true) so a transient
     * outage of that endpoint cannot drop legitimate, HMAC-verified deliveries.
     * The signature check remains the primary gate.
     */
    private function paddleWebhookIpAllowed($ip)
    {
        $cidrs = $this->fetchPaddleAllowedCidrs();
        if (empty($cidrs)) {
            log_message('error', 'Paddle IP allowlist unavailable; skipping IP check (HMAC still enforced)');
            return true; // fail-open: don't drop verified webhooks on a list-fetch outage
        }
        foreach ($cidrs as $cidr) {
            if ($this->ipInCidr($ip, $cidr)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Fetch + cache Paddle's webhook source IP CIDRs (data.ipv4_cidrs from the
     * /ips endpoint). Cached to src/cache/paddle_ips.json for 24h. Falls back to
     * a stale cache if the live fetch fails.
     */
    private function fetchPaddleAllowedCidrs()
    {
        $cacheFile = APPPATH . 'cache/paddle_ips.json';
        $ttl = 86400; // 24 hours

        if (is_file($cacheFile) && (time() - filemtime($cacheFile) < $ttl)) {
            $cached = json_decode(@file_get_contents($cacheFile), true);
            if (!empty($cached['ipv4_cidrs'])) {
                return $cached['ipv4_cidrs'];
            }
        }

        $ch = curl_init('https://api.paddle.com/ips');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        if (ENVIRONMENT === 'production') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if (!$err && $resp) {
            $data = json_decode($resp, true);
            $cidrs = isset($data['data']['ipv4_cidrs']) ? $data['data']['ipv4_cidrs'] : array();
            if (!empty($cidrs)) {
                @file_put_contents($cacheFile, json_encode(array('ipv4_cidrs' => $cidrs, 'fetched_at' => time())));
                return $cidrs;
            }
        } elseif ($err) {
            log_message('error', 'Paddle IPs fetch failed: ' . $err);
        }

        // Fetch failed or returned nothing usable — fall back to any stale cache.
        if (is_file($cacheFile)) {
            $cached = json_decode(@file_get_contents($cacheFile), true);
            if (!empty($cached['ipv4_cidrs'])) {
                return $cached['ipv4_cidrs'];
            }
        }
        return array();
    }

    /**
     * Match an IPv4 address against a CIDR range (a bare IP is treated as /32).
     */
    private function ipInCidr($ip, $cidr)
    {
        if (strpos($cidr, '/') === false) {
            $cidr .= '/32';
        }
        list($subnet, $bits) = explode('/', $cidr, 2);

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        if ($ipLong === false || $subnetLong === false) {
            return false; // not a valid IPv4 (e.g. IPv6) — no match
        }

        $bits = (int) $bits;
        if ($bits <= 0) {
            return true;
        }
        $mask = (~((1 << (32 - $bits)) - 1)) & 0xFFFFFFFF;
        return (($ipLong & $mask) === ($subnetLong & $mask));
    }
}
