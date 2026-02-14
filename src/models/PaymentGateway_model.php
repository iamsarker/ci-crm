<?php
/**
 * PaymentGateway_model
 *
 * Handles payment gateway configuration and management
 */
class PaymentGateway_model extends CI_Model
{
    private $table = 'payment_gateway';

    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get all payment gateways
     */
    function getAllGateways($activeOnly = false)
    {
        $this->db->select('*');
        $this->db->from($this->table);
        if ($activeOnly) {
            $this->db->where('status', 1);
        }
        $this->db->order_by('sort_order', 'ASC');
        $this->db->order_by('name', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Get active gateways for checkout
     */
    function getActiveGateways($currencyCode = null)
    {
        $this->db->select('id, gateway_code, gateway_type, pay_type, display_name, name, description, logo, icon_fa_unicode, fee_type, fee_fixed, fee_percent, fee_bearer, instructions, min_amount, max_amount, supported_currencies, bank_name, account_name, account_number, routing_number, swift_code, iban');
        $this->db->from($this->table);
        $this->db->where('status', 1);
        $this->db->order_by('sort_order', 'ASC');

        $gateways = $this->db->get()->result_array();

        // Filter by currency if specified
        if ($currencyCode) {
            $gateways = array_filter($gateways, function($gw) use ($currencyCode) {
                $currencies = explode(',', $gw['supported_currencies']);
                return in_array($currencyCode, array_map('trim', $currencies));
            });
        }

        return array_values($gateways);
    }

    /**
     * Get gateway by ID
     */
    function getById($id)
    {
        $this->db->where('id', intval($id));
        $query = $this->db->get($this->table);
        return $query->row_array();
    }

    /**
     * Get gateway by code
     */
    function getByCode($code)
    {
        $this->db->where('gateway_code', $code);
        $query = $this->db->get($this->table);
        return $query->row_array();
    }

    /**
     * Get gateway credentials (with test/live mode handling)
     */
    function getGatewayCredentials($gatewayCode)
    {
        $gateway = $this->getByCode($gatewayCode);

        if (empty($gateway)) {
            return null;
        }

        $isTestMode = $gateway['is_test_mode'] == 1;

        return array(
            'gateway_code' => $gateway['gateway_code'],
            'gateway_type' => $gateway['gateway_type'],
            'is_test_mode' => $isTestMode,
            'public_key' => $isTestMode ? $gateway['test_public_key'] : $gateway['public_key'],
            'secret_key' => $isTestMode ? $gateway['test_secret_key'] : $gateway['secret_key'],
            'webhook_secret' => $gateway['webhook_secret'],
            'merchant_id' => $gateway['merchant_id'],
            'extra_config' => !empty($gateway['extra_config']) ? json_decode($gateway['extra_config'], true) : array(),
            'supported_currencies' => explode(',', $gateway['supported_currencies']),
            'fee_type' => $gateway['fee_type'],
            'fee_fixed' => floatval($gateway['fee_fixed']),
            'fee_percent' => floatval($gateway['fee_percent']),
            'fee_bearer' => $gateway['fee_bearer']
        );
    }

    /**
     * Calculate processing fee
     */
    function calculateFee($gatewayId, $amount)
    {
        $gateway = $this->getById($gatewayId);

        if (empty($gateway) || $gateway['fee_type'] === 'none') {
            return 0;
        }

        $fee = 0;

        switch ($gateway['fee_type']) {
            case 'fixed':
                $fee = floatval($gateway['fee_fixed']);
                break;
            case 'percentage':
                $fee = ($amount * floatval($gateway['fee_percent'])) / 100;
                break;
            case 'both':
                $fee = floatval($gateway['fee_fixed']) + (($amount * floatval($gateway['fee_percent'])) / 100);
                break;
        }

        return round($fee, 2);
    }

    /**
     * Save gateway (insert or update)
     */
    function save($data, $id = null)
    {
        // Handle JSON fields
        if (isset($data['extra_config']) && is_array($data['extra_config'])) {
            $data['extra_config'] = json_encode($data['extra_config']);
        }

        if ($id) {
            $data['updated_on'] = date('Y-m-d H:i:s');
            $this->db->where('id', $id);
            return $this->db->update($this->table, $data);
        } else {
            $data['inserted_on'] = date('Y-m-d H:i:s');
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
    }

    /**
     * Update gateway status
     */
    function updateStatus($id, $status)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, array(
            'status' => $status ? 1 : 0,
            'updated_on' => date('Y-m-d H:i:s')
        ));
    }

    /**
     * Delete gateway (soft delete by setting status to -1)
     */
    function delete($id)
    {
        // Don't allow deleting manual payment gateway
        $gateway = $this->getById($id);
        if ($gateway && $gateway['gateway_code'] === 'manual') {
            return false;
        }

        $this->db->where('id', $id);
        return $this->db->update($this->table, array(
            'status' => -1,
            'updated_on' => date('Y-m-d H:i:s')
        ));
    }

    /**
     * Check if gateway code exists
     */
    function codeExists($code, $excludeId = null)
    {
        $this->db->where('gateway_code', $code);
        if ($excludeId) {
            $this->db->where('id !=', $excludeId);
        }
        return $this->db->count_all_results($this->table) > 0;
    }

    /**
     * Get available gateway types
     */
    function getGatewayTypes()
    {
        return array(
            'online_card' => 'Credit/Debit Card',
            'online_wallet' => 'Digital Wallet',
            'bank_transfer' => 'Bank Transfer',
            'manual' => 'Manual/Offline',
            'crypto' => 'Cryptocurrency'
        );
    }

    /**
     * Get supported gateway codes with their display names
     */
    function getSupportedGateways()
    {
        return array(
            'stripe' => array('name' => 'Stripe', 'type' => 'online_card'),
            'paypal' => array('name' => 'PayPal', 'type' => 'online_wallet'),
            'razorpay' => array('name' => 'Razorpay', 'type' => 'online_card'),
            'paystack' => array('name' => 'Paystack', 'type' => 'online_card'),
            'sslcommerz' => array('name' => 'SSLCommerz', 'type' => 'online_card'),
            'bank_transfer' => array('name' => 'Bank Transfer', 'type' => 'bank_transfer'),
            'manual' => array('name' => 'Manual Payment', 'type' => 'manual')
        );
    }

    // =========================================
    // DataTable SSP Methods
    // =========================================

    function getDataTableRecords($sqlQuery, $bindings)
    {
        return $this->db->query($sqlQuery, $bindings)->result_array();
    }

    function countDataTableTotalRecords()
    {
        return $this->db->where('status >=', 0)->count_all_results($this->table);
    }

    function countDataTableFilterRecords($where, $bindings)
    {
        $sql = "SELECT COUNT(id) as cnt FROM {$this->table} WHERE status >= 0 " . str_replace('WHERE', 'AND', $where);
        $query = $this->db->query($sql, $bindings);
        $data = $query->row_array();
        return !empty($data) ? $data['cnt'] : 0;
    }
}
