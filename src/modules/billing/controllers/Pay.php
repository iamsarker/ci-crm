<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pay Controller
 *
 * Handles payment processing for invoices
 */
class Pay extends WHMAZ_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('Payment_model');
        $this->load->model('PaymentGateway_model');
        $this->load->model('Billing_model');
        $this->load->model('Invoice_model');
    }

    /**
     * Payment page for invoice
     * URL: /pay/invoice/{invoice_uuid}
     */
    public function invoice($invoice_uuid)
    {
        $companyId = getCompanyId();

        if ($companyId <= 0) {
            redirect(base_url() . 'auth/login?redirect-url=' . urlencode(base_url() . 'billing/pay/invoice/' . $invoice_uuid));
            return;
        }

        // Get invoice
        $invoice = $this->Billing_model->getInvoiceByUuid($invoice_uuid, $companyId);

        if (empty($invoice)) {
            $this->session->set_flashdata('alert_error', 'Invoice not found.');
            redirect(base_url() . 'billing/invoices');
            return;
        }

        // Check if already paid
        if (strtoupper($invoice['pay_status']) === 'PAID') {
            $this->session->set_flashdata('alert_success', 'This invoice has already been paid.');
            redirect(base_url() . 'billing/view_invoice/' . $invoice_uuid);
            return;
        }

        // Calculate amount due
        $paidAmount = $this->Payment_model->getSuccessfulPaymentAmount($invoice['id']);
        $amountDue = floatval($invoice['total']) - $paidAmount;

        // Get active payment gateways
        $gateways = $this->PaymentGateway_model->getActiveGateways($invoice['currency_code'] ?? null);

        // Get invoice items
        $invoiceItems = $this->Billing_model->getInvoiceItems($invoice['id']);

        $data['invoice'] = $invoice;
        $data['invoice_items'] = $invoiceItems ?: array();
        $data['amount_due'] = $amountDue;
        $data['paid_amount'] = $paidAmount;
        $data['gateways'] = $gateways ?: array();
        $data['stripe_publishable_key'] = '';
        $data['paypal_client_id'] = '';

        // Load gateway-specific keys for frontend
        $stripeGateway = $this->PaymentGateway_model->getByCode('stripe');
        if ($stripeGateway && $stripeGateway['status'] == 1) {
            $isTest = $stripeGateway['is_test_mode'] == 1;
            $data['stripe_publishable_key'] = $isTest ? $stripeGateway['test_public_key'] : $stripeGateway['public_key'];
        }

        $paypalGateway = $this->PaymentGateway_model->getByCode('paypal');
        if ($paypalGateway && $paypalGateway['status'] == 1) {
            $isTest = $paypalGateway['is_test_mode'] == 1;
            $data['paypal_client_id'] = $isTest ? $paypalGateway['test_public_key'] : $paypalGateway['public_key'];
            $data['paypal_mode'] = $isTest ? 'sandbox' : 'production';
        }

        $this->load->view('billing_pay', $data);
    }

    /**
     * Initiate Stripe payment (AJAX)
     * Creates PaymentIntent and returns client_secret
     */
    public function stripe_init()
    {
        header('Content-Type: application/json');

        $companyId = getCompanyId();
        $userId = getCustomerId();

        if ($companyId <= 0) {
            echo json_encode(array('success' => false, 'error' => 'Please login to continue'));
            return;
        }

        $invoice_uuid = $this->input->post('invoice_uuid');
        $invoice = $this->Billing_model->getInvoiceByUuid($invoice_uuid, $companyId);

        if (empty($invoice)) {
            echo json_encode(array('success' => false, 'error' => 'Invoice not found'));
            return;
        }

        // Calculate amount due
        $paidAmount = $this->Payment_model->getSuccessfulPaymentAmount($invoice['id']);
        $amountDue = floatval($invoice['total']) - $paidAmount;

        if ($amountDue <= 0) {
            echo json_encode(array('success' => false, 'error' => 'Invoice is already fully paid'));
            return;
        }

        // Get gateway info
        $gateway = $this->PaymentGateway_model->getByCode('stripe');
        if (!$gateway || $gateway['status'] != 1) {
            echo json_encode(array('success' => false, 'error' => 'Stripe is not available'));
            return;
        }

        // Calculate fee if customer pays
        $feeAmount = 0;
        if ($gateway['fee_bearer'] === 'customer') {
            $feeAmount = $this->PaymentGateway_model->calculateFee($gateway['id'], $amountDue);
        }
        $totalAmount = $amountDue + $feeAmount;

        // Create transaction record
        $transaction = $this->Payment_model->createTransaction(array(
            'invoice_id' => $invoice['id'],
            'payment_gateway_id' => $gateway['id'],
            'gateway_code' => 'stripe',
            'amount' => $totalAmount,
            'fee_amount' => $feeAmount,
            'net_amount' => $amountDue,
            'currency_code' => $invoice['currency_code'],
            'txn_type' => 'payment',
            'status' => 'pending',
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'inserted_by' => $userId,
            'metadata' => array(
                'invoice_no' => $invoice['invoice_no'],
                'company_id' => $companyId
            )
        ));

        // Load Stripe library and create PaymentIntent
        $this->load->library('Stripe');

        $result = $this->stripe->createPaymentIntent(
            $totalAmount,
            $invoice['currency_code'],
            array(
                'invoice_id' => $invoice['id'],
                'invoice_uuid' => $invoice_uuid,
                'transaction_id' => $transaction['id']
            )
        );

        if ($result['success']) {
            // Update transaction with gateway order ID
            $this->Payment_model->updateTransactionStatus($transaction['id'], 'processing', array(
                'gateway_order_id' => $result['data']['id'],
                'gateway_response' => $result['data']
            ));

            echo json_encode(array(
                'success' => true,
                'client_secret' => $result['data']['client_secret'],
                'transaction_uuid' => $transaction['transaction_uuid'],
                'amount' => $totalAmount,
                'currency' => $invoice['currency_code']
            ));
        } else {
            // Update transaction as failed
            $this->Payment_model->updateTransactionStatus($transaction['id'], 'failed', array(
                'failure_reason' => $result['error']
            ));

            echo json_encode(array(
                'success' => false,
                'error' => $result['error']
            ));
        }
    }

    /**
     * Stripe payment success callback (client-side redirect)
     */
    public function stripe_success($transaction_uuid)
    {
        $transaction = $this->Payment_model->getTransactionByUuid($transaction_uuid);

        if (!$transaction) {
            $this->session->set_flashdata('alert_error', 'Transaction not found.');
            redirect(base_url() . 'billing/invoices');
            return;
        }

        // Get invoice UUID for redirect
        $invoice = $this->db->where('id', $transaction['invoice_id'])->get('invoices')->row_array();

        if ($transaction['status'] === 'completed') {
            $this->session->set_flashdata('alert_success', 'Payment successful! Thank you for your payment.');
        } else {
            // Payment might still be processing
            $this->session->set_flashdata('alert_info', 'Payment is being processed. You will receive a confirmation email shortly.');
        }

        redirect(base_url() . 'billing/view_invoice/' . $invoice['invoice_uuid']);
    }

    /**
     * Initiate PayPal payment (AJAX)
     * Creates PayPal order and returns order ID
     */
    public function paypal_init()
    {
        header('Content-Type: application/json');

        $companyId = getCompanyId();
        $userId = getCustomerId();

        if ($companyId <= 0) {
            echo json_encode(array('success' => false, 'error' => 'Please login to continue'));
            return;
        }

        $invoice_uuid = $this->input->post('invoice_uuid');
        $invoice = $this->Billing_model->getInvoiceByUuid($invoice_uuid, $companyId);

        if (empty($invoice)) {
            echo json_encode(array('success' => false, 'error' => 'Invoice not found'));
            return;
        }

        // Calculate amount due
        $paidAmount = $this->Payment_model->getSuccessfulPaymentAmount($invoice['id']);
        $amountDue = floatval($invoice['total']) - $paidAmount;

        if ($amountDue <= 0) {
            echo json_encode(array('success' => false, 'error' => 'Invoice is already fully paid'));
            return;
        }

        // Get gateway info
        $gateway = $this->PaymentGateway_model->getByCode('paypal');
        if (!$gateway || $gateway['status'] != 1) {
            echo json_encode(array('success' => false, 'error' => 'PayPal is not available'));
            return;
        }

        // Calculate fee if customer pays
        $feeAmount = 0;
        if ($gateway['fee_bearer'] === 'customer') {
            $feeAmount = $this->PaymentGateway_model->calculateFee($gateway['id'], $amountDue);
        }
        $totalAmount = $amountDue + $feeAmount;

        // Create transaction record
        $transaction = $this->Payment_model->createTransaction(array(
            'invoice_id' => $invoice['id'],
            'payment_gateway_id' => $gateway['id'],
            'gateway_code' => 'paypal',
            'amount' => $totalAmount,
            'fee_amount' => $feeAmount,
            'net_amount' => $amountDue,
            'currency_code' => $invoice['currency_code'],
            'txn_type' => 'payment',
            'status' => 'pending',
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'inserted_by' => $userId,
            'metadata' => array(
                'invoice_no' => $invoice['invoice_no'],
                'company_id' => $companyId
            )
        ));

        // Load PayPal library and create order
        $this->load->library('Paypal');

        $result = $this->paypal->createOrder(
            $totalAmount,
            $invoice['currency_code'],
            'Invoice #' . $invoice['invoice_no'],
            array(
                'invoice_id' => $invoice['id'],
                'transaction_id' => $transaction['id']
            )
        );

        if ($result['success']) {
            // Update transaction with PayPal order ID
            $this->Payment_model->updateTransactionStatus($transaction['id'], 'processing', array(
                'gateway_order_id' => $result['data']['id'],
                'gateway_response' => $result['data']
            ));

            echo json_encode(array(
                'success' => true,
                'order_id' => $result['data']['id'],
                'transaction_uuid' => $transaction['transaction_uuid']
            ));
        } else {
            $this->Payment_model->updateTransactionStatus($transaction['id'], 'failed', array(
                'failure_reason' => $result['error']
            ));

            echo json_encode(array(
                'success' => false,
                'error' => $result['error']
            ));
        }
    }

    /**
     * Capture PayPal payment (AJAX - called after buyer approves)
     */
    public function paypal_capture()
    {
        header('Content-Type: application/json');

        $orderId = $this->input->post('order_id');
        $transactionUuid = $this->input->post('transaction_uuid');

        if (empty($orderId) || empty($transactionUuid)) {
            echo json_encode(array('success' => false, 'error' => 'Missing parameters'));
            return;
        }

        $transaction = $this->Payment_model->getTransactionByUuid($transactionUuid);

        if (!$transaction) {
            echo json_encode(array('success' => false, 'error' => 'Transaction not found'));
            return;
        }

        // Load PayPal library and capture
        $this->load->library('Paypal');

        $result = $this->paypal->captureOrder($orderId);

        if ($result['success']) {
            $details = $this->paypal->extractPaymentDetails($result);

            // Update transaction
            $this->Payment_model->updateTransactionStatus($transaction['id'], 'completed', array(
                'gateway_transaction_id' => $details['transaction_id'],
                'payer_email' => $details['payer_email'],
                'payer_name' => $details['payer_name'],
                'gateway_response' => $result['data']
            ));

            // Process successful payment
            $this->Payment_model->processSuccessfulPayment($transaction['id']);
            $this->Payment_model->recordInvoiceTxn($transaction['id']);

            echo json_encode(array(
                'success' => true,
                'transaction_id' => $details['transaction_id']
            ));
        } else {
            $this->Payment_model->updateTransactionStatus($transaction['id'], 'failed', array(
                'failure_reason' => $result['error']
            ));

            echo json_encode(array(
                'success' => false,
                'error' => $result['error']
            ));
        }
    }

    /**
     * PayPal payment cancelled
     */
    public function paypal_cancel($transaction_uuid)
    {
        $transaction = $this->Payment_model->getTransactionByUuid($transaction_uuid);

        if ($transaction) {
            $this->Payment_model->updateTransactionStatus($transaction['id'], 'cancelled');

            $invoice = $this->db->where('id', $transaction['invoice_id'])->get('invoices')->row_array();
            $this->session->set_flashdata('alert_info', 'Payment was cancelled.');
            redirect(base_url() . 'billing/view_invoice/' . $invoice['invoice_uuid']);
        } else {
            redirect(base_url() . 'billing/invoices');
        }
    }

    /**
     * Confirm offline payment (Bank Transfer / Manual)
     * Creates a pending transaction for admin to confirm later
     */
    public function offline_confirm()
    {
        header('Content-Type: application/json');

        $companyId = getCompanyId();
        $userId = getCustomerId();

        if ($companyId <= 0) {
            echo json_encode(array('success' => false, 'error' => 'Please login to continue'));
            return;
        }

        $invoice_uuid = $this->input->post('invoice_uuid');
        $gatewayCode = $this->input->post('gateway');

        if (empty($invoice_uuid) || empty($gatewayCode)) {
            echo json_encode(array('success' => false, 'error' => 'Missing parameters'));
            return;
        }

        $invoice = $this->Billing_model->getInvoiceByUuid($invoice_uuid, $companyId);

        if (empty($invoice)) {
            echo json_encode(array('success' => false, 'error' => 'Invoice not found'));
            return;
        }

        // Calculate amount due
        $paidAmount = $this->Payment_model->getSuccessfulPaymentAmount($invoice['id']);
        $amountDue = floatval($invoice['total']) - $paidAmount;

        if ($amountDue <= 0) {
            echo json_encode(array('success' => false, 'error' => 'Invoice is already fully paid'));
            return;
        }

        // Get gateway info
        $gateway = $this->PaymentGateway_model->getByCode($gatewayCode);
        if (!$gateway || $gateway['status'] != 1) {
            echo json_encode(array('success' => false, 'error' => 'Payment method not available'));
            return;
        }

        // Create pending transaction record
        $transaction = $this->Payment_model->createTransaction(array(
            'invoice_id' => $invoice['id'],
            'payment_gateway_id' => $gateway['id'],
            'gateway_code' => $gatewayCode,
            'amount' => $amountDue,
            'fee_amount' => 0,
            'net_amount' => $amountDue,
            'currency_code' => $invoice['currency_code'],
            'txn_type' => 'payment',
            'status' => 'awaiting_confirmation',
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'inserted_by' => $userId,
            'metadata' => array(
                'invoice_no' => $invoice['invoice_no'],
                'company_id' => $companyId,
                'payment_method' => $gatewayCode
            )
        ));

        echo json_encode(array(
            'success' => true,
            'transaction_uuid' => $transaction['transaction_uuid'],
            'message' => 'Payment marked as pending. Please complete the payment as instructed.'
        ));
    }

    /**
     * Pending payment confirmation page
     */
    public function pending($transaction_uuid)
    {
        $companyId = getCompanyId();

        if ($companyId <= 0) {
            redirect(base_url() . 'auth/login');
            return;
        }

        $transaction = $this->Payment_model->getTransactionByUuid($transaction_uuid);

        if (!$transaction) {
            $this->session->set_flashdata('alert_error', 'Transaction not found.');
            redirect(base_url() . 'billing/invoices');
            return;
        }

        // Get invoice
        $invoice = $this->db->where('id', $transaction['invoice_id'])->get('invoices')->row_array();

        // Get gateway details
        $gateway = $this->PaymentGateway_model->getById($transaction['payment_gateway_id']);

        $data['transaction'] = $transaction;
        $data['invoice'] = $invoice;
        $data['gateway'] = $gateway;

        $this->load->view('billing_pay_pending', $data);
    }

    /**
     * Record manual/offline payment (admin confirmation)
     */
    public function record_manual()
    {
        header('Content-Type: application/json');

        // This would typically be called by admin to record offline payments
        // For now, just show instructions to customer

        echo json_encode(array(
            'success' => true,
            'message' => 'Please follow the payment instructions and contact support once payment is made.'
        ));
    }

    /**
     * Initiate SSLCommerz payment
     */
    public function sslcommerz_init()
    {
        header('Content-Type: application/json');

        $companyId = getCompanyId();
        $userId = getCustomerId();

        if ($companyId <= 0) {
            echo json_encode(array('success' => false, 'error' => 'Please login to continue'));
            return;
        }

        $invoice_uuid = $this->input->post('invoice_uuid');
        $invoice = $this->Billing_model->getInvoiceByUuid($invoice_uuid, $companyId);

        if (empty($invoice)) {
            echo json_encode(array('success' => false, 'error' => 'Invoice not found'));
            return;
        }

        // Calculate amount due
        $paidAmount = $this->Payment_model->getSuccessfulPaymentAmount($invoice['id']);
        $amountDue = floatval($invoice['total']) - $paidAmount;

        if ($amountDue <= 0) {
            echo json_encode(array('success' => false, 'error' => 'Invoice is already fully paid'));
            return;
        }

        // Get gateway info
        $gateway = $this->PaymentGateway_model->getByCode('sslcommerz');
        if (!$gateway || $gateway['status'] != 1) {
            echo json_encode(array('success' => false, 'error' => 'SSLCommerz is not available'));
            return;
        }

        // Calculate fee if customer pays
        $feeAmount = 0;
        if ($gateway['fee_bearer'] === 'customer') {
            $feeAmount = $this->PaymentGateway_model->calculateFee($gateway['id'], $amountDue);
        }
        $totalAmount = $amountDue + $feeAmount;

        // Create transaction record
        $transaction = $this->Payment_model->createTransaction(array(
            'invoice_id' => $invoice['id'],
            'payment_gateway_id' => $gateway['id'],
            'gateway_code' => 'sslcommerz',
            'amount' => $totalAmount,
            'fee_amount' => $feeAmount,
            'net_amount' => $amountDue,
            'currency_code' => $invoice['currency_code'],
            'txn_type' => 'payment',
            'status' => 'pending',
            'ip_address' => $this->input->ip_address(),
            'user_agent' => $this->input->user_agent(),
            'inserted_by' => $userId,
            'metadata' => array(
                'invoice_no' => $invoice['invoice_no'],
                'company_id' => $companyId
            )
        ));

        // Get customer info
        $customer = $this->db->where('id', $companyId)->get('companies')->row_array();

        // Load SSLCommerz library
        $this->load->library('Sslcommerz');

        $result = $this->sslcommerz->initiatePayment(array(
            'amount' => $totalAmount,
            'currency' => $invoice['currency_code'] === 'BDT' ? 'BDT' : 'USD',
            'transaction_id' => $transaction['transaction_uuid'],
            'success_url' => base_url() . 'billing/pay/sslcommerz_success',
            'fail_url' => base_url() . 'billing/pay/sslcommerz_fail',
            'cancel_url' => base_url() . 'billing/pay/sslcommerz_cancel',
            'ipn_url' => base_url() . 'webhook/sslcommerz',
            'customer_name' => $customer['company_name'] ?? 'Customer',
            'customer_email' => $customer['email'] ?? '',
            'customer_phone' => $customer['phone'] ?? '',
            'customer_address' => $customer['address'] ?? '',
            'customer_city' => $customer['city'] ?? '',
            'customer_country' => $customer['country'] ?? 'Bangladesh',
            'product_name' => 'Invoice #' . $invoice['invoice_no'],
            'value_a' => $transaction['transaction_uuid'],
            'value_b' => $invoice_uuid
        ));

        if ($result['success']) {
            // Update transaction with session key
            $this->Payment_model->updateTransactionStatus($transaction['id'], 'processing', array(
                'gateway_order_id' => $result['data']['session_key'],
                'gateway_response' => $result['data']
            ));

            echo json_encode(array(
                'success' => true,
                'gateway_url' => $result['data']['gateway_url'],
                'transaction_uuid' => $transaction['transaction_uuid']
            ));
        } else {
            $this->Payment_model->updateTransactionStatus($transaction['id'], 'failed', array(
                'failure_reason' => $result['error']
            ));

            echo json_encode(array(
                'success' => false,
                'error' => $result['error']
            ));
        }
    }

    /**
     * SSLCommerz payment success callback
     */
    public function sslcommerz_success()
    {
        $transactionUuid = $this->input->post('value_a') ?? $this->input->get('value_a');
        $invoiceUuid = $this->input->post('value_b') ?? $this->input->get('value_b');
        $valId = $this->input->post('val_id') ?? $this->input->get('val_id');

        if (empty($transactionUuid)) {
            $this->session->set_flashdata('alert_error', 'Invalid payment response.');
            redirect(base_url() . 'billing/invoices');
            return;
        }

        $transaction = $this->Payment_model->getTransactionByUuid($transactionUuid);

        if (!$transaction) {
            $this->session->set_flashdata('alert_error', 'Transaction not found.');
            redirect(base_url() . 'billing/invoices');
            return;
        }

        // Validate payment with SSLCommerz
        $this->load->library('Sslcommerz');
        $validation = $this->sslcommerz->validateIPN($_POST);

        if ($validation['success']) {
            $details = $this->sslcommerz->extractPaymentDetails($validation);

            // Update transaction
            $this->Payment_model->updateTransactionStatus($transaction['id'], 'completed', array(
                'gateway_transaction_id' => $details['bank_tran_id'],
                'gateway_response' => $validation['data']
            ));

            // Process successful payment
            $this->Payment_model->processSuccessfulPayment($transaction['id']);
            $this->Payment_model->recordInvoiceTxn($transaction['id']);

            $this->session->set_flashdata('alert_success', 'Payment successful! Thank you for your payment.');
        } else {
            $this->session->set_flashdata('alert_info', 'Payment is being verified. You will receive confirmation shortly.');
        }

        // Redirect to invoice
        $invoice = $this->db->where('id', $transaction['invoice_id'])->get('invoices')->row_array();
        redirect(base_url() . 'billing/view_invoice/' . $invoice['invoice_uuid']);
    }

    /**
     * SSLCommerz payment fail callback
     */
    public function sslcommerz_fail()
    {
        $transactionUuid = $this->input->post('value_a') ?? $this->input->get('value_a');

        if (!empty($transactionUuid)) {
            $transaction = $this->Payment_model->getTransactionByUuid($transactionUuid);
            if ($transaction) {
                $this->Payment_model->updateTransactionStatus($transaction['id'], 'failed', array(
                    'failure_reason' => 'Payment failed at gateway'
                ));

                $invoice = $this->db->where('id', $transaction['invoice_id'])->get('invoices')->row_array();
                $this->session->set_flashdata('alert_error', 'Payment failed. Please try again.');
                redirect(base_url() . 'billing/view_invoice/' . $invoice['invoice_uuid']);
                return;
            }
        }

        $this->session->set_flashdata('alert_error', 'Payment failed.');
        redirect(base_url() . 'billing/invoices');
    }

    /**
     * SSLCommerz payment cancel callback
     */
    public function sslcommerz_cancel()
    {
        $transactionUuid = $this->input->post('value_a') ?? $this->input->get('value_a');

        if (!empty($transactionUuid)) {
            $transaction = $this->Payment_model->getTransactionByUuid($transactionUuid);
            if ($transaction) {
                $this->Payment_model->updateTransactionStatus($transaction['id'], 'cancelled');

                $invoice = $this->db->where('id', $transaction['invoice_id'])->get('invoices')->row_array();
                $this->session->set_flashdata('alert_info', 'Payment was cancelled.');
                redirect(base_url() . 'billing/view_invoice/' . $invoice['invoice_uuid']);
                return;
            }
        }

        $this->session->set_flashdata('alert_info', 'Payment was cancelled.');
        redirect(base_url() . 'billing/invoices');
    }

    /**
     * Get payment status (AJAX)
     */
    public function status($transaction_uuid)
    {
        header('Content-Type: application/json');

        $transaction = $this->Payment_model->getTransactionByUuid($transaction_uuid);

        if (!$transaction) {
            echo json_encode(array('success' => false, 'error' => 'Transaction not found'));
            return;
        }

        echo json_encode(array(
            'success' => true,
            'status' => $transaction['status'],
            'completed' => $transaction['status'] === 'completed'
        ));
    }
}
