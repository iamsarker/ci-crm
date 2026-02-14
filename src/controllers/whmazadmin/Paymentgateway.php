<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Payment Gateway Admin Controller
 *
 * Manages payment gateway configuration
 */
class Paymentgateway extends WHMAZADMIN_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('PaymentGateway_model');
        $this->load->model('Payment_model');

        if (!$this->isLogin()) {
            redirect('/whmazadmin/authenticate/login', 'refresh');
        }
    }

    /**
     * List all payment gateways
     */
    public function index()
    {
        $data['gateways'] = $this->PaymentGateway_model->getAllGateways();
        $data['gateway_types'] = $this->PaymentGateway_model->getGatewayTypes();
        $this->load->view('whmazadmin/paymentgateway_list', $data);
    }

    /**
     * Edit/Configure a gateway
     */
    public function manage($id)
    {
        $gateway = $this->PaymentGateway_model->getById($id);

        if (empty($gateway)) {
            $this->session->set_flashdata('admin_error', 'Payment gateway not found.');
            redirect('/whmazadmin/paymentgateway');
            return;
        }

        // Decode extra_config
        if (!empty($gateway['extra_config'])) {
            $gateway['extra_config'] = json_decode($gateway['extra_config'], true);
        }

        $data['gateway'] = $gateway;
        $data['gateway_types'] = $this->PaymentGateway_model->getGatewayTypes();
        $this->load->view('whmazadmin/paymentgateway_manage', $data);
    }

    /**
     * Save gateway configuration
     */
    public function save()
    {
        header('Content-Type: application/json');

        try {
            $id = $this->input->post('id');
            $gateway = $this->PaymentGateway_model->getById($id);

            if (empty($gateway)) {
                echo json_encode(array('success' => false, 'message' => 'Gateway not found'));
                return;
            }

            $data = array(
                'display_name' => trim($this->input->post('display_name') ?? ''),
                'description' => trim($this->input->post('description') ?? ''),
                'status' => $this->input->post('status') ? 1 : 0,
                'is_test_mode' => $this->input->post('is_test_mode') ? 1 : 0,
                'sort_order' => intval($this->input->post('sort_order') ?? 0),

                // Live credentials
                'public_key' => trim($this->input->post('public_key') ?? ''),
                'secret_key' => trim($this->input->post('secret_key') ?? ''),
                'webhook_secret' => trim($this->input->post('webhook_secret') ?? ''),
                'merchant_id' => trim($this->input->post('merchant_id') ?? ''),

                // Test credentials
                'test_public_key' => trim($this->input->post('test_public_key') ?? ''),
                'test_secret_key' => trim($this->input->post('test_secret_key') ?? ''),

                // Currency and limits
                'supported_currencies' => trim($this->input->post('supported_currencies') ?? ''),
                'min_amount' => floatval($this->input->post('min_amount') ?? 0),
                'max_amount' => floatval($this->input->post('max_amount') ?? 0),

                // Fees
                'fee_type' => $this->input->post('fee_type') ?: 'none',
                'fee_fixed' => floatval($this->input->post('fee_fixed') ?? 0),
                'fee_percent' => floatval($this->input->post('fee_percent') ?? 0),
                'fee_bearer' => $this->input->post('fee_bearer') ?: 'merchant',

                // Bank transfer fields
                'bank_name' => trim($this->input->post('bank_name') ?? ''),
                'account_name' => trim($this->input->post('account_name') ?? ''),
                'account_number' => trim($this->input->post('account_number') ?? ''),
                'routing_number' => trim($this->input->post('routing_number') ?? ''),
                'swift_code' => trim($this->input->post('swift_code') ?? ''),
                'iban' => trim($this->input->post('iban') ?? ''),

                // Instructions
                'instructions' => trim($this->input->post('instructions') ?? ''),

                // Updated by
                'updated_by' => getAdminId()
            );

            // Handle extra_config (JSON)
            $extraConfig = $this->input->post('extra_config');
            if (!empty($extraConfig)) {
                // If it's a JSON string, validate it
                $decoded = json_decode($extraConfig, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data['extra_config'] = $extraConfig;
                }
            }

            // Generate webhook URL
            $data['webhook_url'] = base_url() . 'webhook/' . $gateway['gateway_code'];

            $this->PaymentGateway_model->save($data, $id);

            echo json_encode(array(
                'success' => true,
                'message' => 'Payment gateway updated successfully',
                'csrf_token' => $this->security->get_csrf_hash()
            ));

        } catch (Exception $e) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'csrf_token' => $this->security->get_csrf_hash()
            ));
        }
    }

    /**
     * Toggle gateway status
     */
    public function toggle_status()
    {
        $this->processRestCall();
        header('Content-Type: application/json');

        $id = $this->input->post('id');
        $status = $this->input->post('status') ? 1 : 0;

        $gateway = $this->PaymentGateway_model->getById($id);

        if (empty($gateway)) {
            echo json_encode(array('success' => false, 'message' => 'Gateway not found'));
            return;
        }

        // Validate credentials before enabling
        if ($status == 1) {
            $gatewayCode = $gateway['gateway_code'];

            // Check if online gateway has credentials
            if (in_array($gatewayCode, array('stripe', 'paypal', 'razorpay', 'paystack', 'sslcommerz'))) {
                $isTest = $gateway['is_test_mode'] == 1;
                $publicKey = $isTest ? $gateway['test_public_key'] : $gateway['public_key'];
                $secretKey = $isTest ? $gateway['test_secret_key'] : $gateway['secret_key'];

                if (empty($publicKey) || empty($secretKey)) {
                    echo json_encode(array(
                        'success' => false,
                        'message' => 'Please configure API credentials before enabling this gateway.'
                    ));
                    return;
                }
            }
        }

        $this->PaymentGateway_model->updateStatus($id, $status);

        echo json_encode(array(
            'success' => true,
            'message' => $status ? 'Gateway enabled' : 'Gateway disabled'
        ));
    }

    /**
     * Test gateway connection
     */
    public function test_connection($id)
    {
        $this->processRestCall();
        header('Content-Type: application/json');

        $gateway = $this->PaymentGateway_model->getById($id);

        if (empty($gateway)) {
            echo json_encode(array('success' => false, 'message' => 'Gateway not found'));
            return;
        }

        $result = array('success' => false, 'message' => 'Test not implemented for this gateway');

        switch ($gateway['gateway_code']) {
            case 'stripe':
                $result = $this->testStripeConnection($gateway);
                break;
            case 'paypal':
                $result = $this->testPayPalConnection($gateway);
                break;
        }

        echo json_encode($result);
    }

    /**
     * Test Stripe connection
     */
    private function testStripeConnection($gateway)
    {
        $this->load->library('StripePayment', array(
            'secret_key' => $gateway['is_test_mode'] ? $gateway['test_secret_key'] : $gateway['secret_key'],
            'publishable_key' => $gateway['is_test_mode'] ? $gateway['test_public_key'] : $gateway['public_key'],
            'is_test_mode' => $gateway['is_test_mode']
        ));

        if (!$this->StripePayment->isConfigured()) {
            return array('success' => false, 'message' => 'API keys not configured');
        }

        // Try to create a minimal PaymentIntent to test
        $result = $this->StripePayment->createPaymentIntent(1.00, 'USD', array('test' => 'connection'));

        if ($result['success']) {
            // Cancel the test payment intent
            $this->StripePayment->cancelPaymentIntent($result['data']['id']);
            return array('success' => true, 'message' => 'Stripe connection successful!');
        } else {
            return array('success' => false, 'message' => 'Stripe error: ' . $result['error']);
        }
    }

    /**
     * Test PayPal connection
     */
    private function testPayPalConnection($gateway)
    {
        $this->load->library('PayPalPayment', array(
            'client_id' => $gateway['is_test_mode'] ? $gateway['test_public_key'] : $gateway['public_key'],
            'client_secret' => $gateway['is_test_mode'] ? $gateway['test_secret_key'] : $gateway['secret_key'],
            'is_test_mode' => $gateway['is_test_mode']
        ));

        if (!$this->PayPalPayment->isConfigured()) {
            return array('success' => false, 'message' => 'API credentials not configured');
        }

        // Try to create a test order
        $result = $this->PayPalPayment->createOrder(1.00, 'USD', 'Connection test');

        if ($result['success']) {
            return array('success' => true, 'message' => 'PayPal connection successful!');
        } else {
            return array('success' => false, 'message' => 'PayPal error: ' . $result['error']);
        }
    }

    /**
     * View transaction history
     */
    public function transactions()
    {
        $data['gateways'] = $this->PaymentGateway_model->getAllGateways();
        $this->load->view('whmazadmin/paymentgateway_transactions', $data);
    }

    /**
     * Transaction list API (DataTables)
     */
    public function transactions_api()
    {
        $this->processRestCall();
        header('Content-Type: application/json');

        try {
            $params = $this->input->get();
            $bindings = array();
            $where = '';

            // Filter by gateway
            $gatewayCode = $this->input->get('gateway_code');
            if (!empty($gatewayCode)) {
                $where = " WHERE gateway_code = ?";
                $bindings[] = $gatewayCode;
            }

            // Filter by status
            $status = $this->input->get('status');
            if (!empty($status)) {
                $where .= empty($where) ? " WHERE status = ?" : " AND status = ?";
                $bindings[] = $status;
            }

            $sqlQuery = ssp_sql_query($params, "payment_transactions", $bindings, $where);
            $data = $this->Payment_model->getDataTableRecords($sqlQuery, $bindings);

            $response = array(
                "draw" => !empty($params['draw']) ? intval($params['draw']) : 0,
                "recordsTotal" => intval($this->Payment_model->countDataTableTotalRecords()),
                "recordsFiltered" => intval($this->Payment_model->countDataTableFilterRecords($where, $bindings)),
                "data" => $data
            );

            echo json_encode($response);

        } catch (Exception $e) {
            echo json_encode(array(
                "draw" => 0,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => array(),
                "error" => $e->getMessage()
            ));
        }
    }

    /**
     * View webhook logs
     */
    public function webhooks()
    {
        $data['gateways'] = $this->PaymentGateway_model->getAllGateways();
        $this->load->view('whmazadmin/paymentgateway_webhooks', $data);
    }

    /**
     * Webhook logs API
     */
    public function webhooks_api()
    {
        $this->processRestCall();
        header('Content-Type: application/json');

        $limit = $this->input->get('limit') ?: 50;
        $offset = $this->input->get('offset') ?: 0;
        $gatewayCode = $this->input->get('gateway_code');

        $this->db->select('*');
        $this->db->from('webhook_logs');
        if (!empty($gatewayCode)) {
            $this->db->where('gateway_code', $gatewayCode);
        }
        $this->db->order_by('received_at', 'DESC');
        $this->db->limit($limit, $offset);

        $logs = $this->db->get()->result_array();

        // Decode payload for display
        foreach ($logs as &$log) {
            $log['payload'] = json_decode($log['payload'], true);
        }

        echo json_encode(array(
            'success' => true,
            'data' => $logs
        ));
    }
}
