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
        $this->load->library('StripePayment');

        // Verify signature
        $event = $this->StripePayment->verifyWebhook($payload, $signature);

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

        // Find transaction by gateway_transaction_id or gateway_order_id
        $transaction = $this->Payment_model->getByGatewayTxnId($paymentIntentId, 'stripe');

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

        // Extract card details if available
        if (isset($data['charges']['data'][0]['payment_method_details']['card'])) {
            $card = $data['charges']['data'][0]['payment_method_details']['card'];
            $updateData['card_brand'] = isset($card['brand']) ? $card['brand'] : null;
            $updateData['card_last4'] = isset($card['last4']) ? $card['last4'] : null;
            $updateData['card_exp_month'] = isset($card['exp_month']) ? $card['exp_month'] : null;
            $updateData['card_exp_year'] = isset($card['exp_year']) ? $card['exp_year'] : null;
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
        $this->load->library('PayPalPayment');

        // Verify webhook
        if (!$this->PayPalPayment->verifyWebhook($headers, $payload)) {
            $this->Payment_model->markWebhookProcessed($webhookId, false, 'Invalid signature');
            $this->sendResponse(400, 'Invalid signature');
            return;
        }

        // Parse event
        $event = $this->PayPalPayment->parseWebhookEvent($payload);

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
        $this->load->library('PayPalPayment');
        $captureResult = $this->PayPalPayment->captureOrder($orderId);

        if ($captureResult['success']) {
            $details = $this->PayPalPayment->extractPaymentDetails($captureResult);

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
     * Send JSON response
     */
    private function sendResponse($statusCode, $message)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(array('message' => $message));
        exit;
    }
}
