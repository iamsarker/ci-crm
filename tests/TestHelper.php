<?php
/**
 * Test Helper Functions
 *
 * Common functions and mocks for testing
 */

/**
 * Mock CI instance for testing
 */
class CI_Mock
{
    public $db;
    public $session;
    public $input;

    public function __construct()
    {
        $this->db = new DB_Mock();
        $this->session = new Session_Mock();
        $this->input = new Input_Mock();
    }
}

/**
 * Mock Database class
 */
class DB_Mock
{
    private $transactionStarted = false;
    private $transactionStatus = true;

    public function trans_start()
    {
        $this->transactionStarted = true;
    }

    public function trans_complete()
    {
        $this->transactionStarted = false;
    }

    public function trans_rollback()
    {
        $this->transactionStarted = false;
        $this->transactionStatus = false;
    }

    public function trans_status()
    {
        return $this->transactionStatus;
    }

    public function setTransactionStatus($status)
    {
        $this->transactionStatus = $status;
    }

    public function where($field, $value = null)
    {
        return $this;
    }

    public function get($table)
    {
        return new Query_Result_Mock();
    }

    public function insert($table, $data)
    {
        return true;
    }

    public function update($table, $data)
    {
        return true;
    }

    public function insert_id()
    {
        return 1;
    }
}

/**
 * Mock Query Result
 */
class Query_Result_Mock
{
    public function row_array()
    {
        return array('id' => 1);
    }

    public function result_array()
    {
        return array();
    }
}

/**
 * Mock Session class
 */
class Session_Mock
{
    private $flashdata = array();
    private $userdata = array();

    public function set_flashdata($key, $value)
    {
        $this->flashdata[$key] = $value;
    }

    public function flashdata($key)
    {
        return isset($this->flashdata[$key]) ? $this->flashdata[$key] : null;
    }

    public function set_userdata($key, $value)
    {
        $this->userdata[$key] = $value;
    }

    public function userdata($key)
    {
        return isset($this->userdata[$key]) ? $this->userdata[$key] : null;
    }
}

/**
 * Mock Input class
 */
class Input_Mock
{
    private $postData = array();
    private $getData = array();

    public function post($key = null)
    {
        if ($key === null) {
            return $this->postData;
        }
        return isset($this->postData[$key]) ? $this->postData[$key] : null;
    }

    public function get($key = null)
    {
        if ($key === null) {
            return $this->getData;
        }
        return isset($this->getData[$key]) ? $this->getData[$key] : null;
    }

    public function setPost($data)
    {
        $this->postData = $data;
    }

    public function setGet($data)
    {
        $this->getData = $data;
    }

    public function ip_address()
    {
        return '127.0.0.1';
    }

    public function user_agent()
    {
        return 'PHPUnit Test Agent';
    }
}

/**
 * Helper to create a test transaction array
 */
function createTestTransaction($overrides = array())
{
    $defaults = array(
        'id' => 1,
        'transaction_uuid' => 'test-uuid-12345',
        'invoice_id' => 1,
        'payment_gateway_id' => 1,
        'gateway_code' => 'stripe',
        'amount' => 100.00,
        'fee_amount' => 0,
        'net_amount' => 100.00,
        'currency_code' => 'USD',
        'txn_type' => 'payment',
        'status' => 'pending',
        'gateway_order_id' => null,
        'gateway_transaction_id' => null,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Test',
        'inserted_on' => date('Y-m-d H:i:s'),
        'metadata' => json_encode(array())
    );

    return array_merge($defaults, $overrides);
}

/**
 * Helper to create a test invoice array
 */
function createTestInvoice($overrides = array())
{
    $defaults = array(
        'id' => 1,
        'invoice_uuid' => 'inv-uuid-12345',
        'invoice_no' => 'INV-001',
        'company_id' => 1,
        'total' => 100.00,
        'subtotal' => 100.00,
        'tax' => 0,
        'discount' => 0,
        'currency_code' => 'USD',
        'pay_status' => 'UNPAID',
        'due_date' => date('Y-m-d', strtotime('+30 days')),
        'inserted_on' => date('Y-m-d H:i:s')
    );

    return array_merge($defaults, $overrides);
}

/**
 * Helper to create a test gateway array
 */
function createTestGateway($overrides = array())
{
    $defaults = array(
        'id' => 1,
        'gateway_code' => 'stripe',
        'name' => 'Stripe',
        'status' => 1,
        'is_test_mode' => 1,
        'fee_type' => 'none',
        'fee_fixed' => 0,
        'fee_percent' => 0,
        'fee_bearer' => 'merchant'
    );

    return array_merge($defaults, $overrides);
}
