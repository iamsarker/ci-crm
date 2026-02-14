<?php
/**
 * Payment_model
 *
 * Handles payment transactions, refunds, and webhook logs
 */
class Payment_model extends CI_Model
{
    private $txnTable = 'payment_transactions';
    private $refundTable = 'payment_refunds';
    private $webhookTable = 'webhook_logs';

    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // =========================================
    // Transaction Methods
    // =========================================

    /**
     * Create a new payment transaction record
     */
    function createTransaction($data)
    {
        $data['transaction_uuid'] = gen_uuid();
        $data['initiated_at'] = date('Y-m-d H:i:s');
        $data['inserted_on'] = date('Y-m-d H:i:s');

        if (!isset($data['status'])) {
            $data['status'] = 'pending';
        }

        if (!isset($data['net_amount']) && isset($data['amount'])) {
            $feeAmount = isset($data['fee_amount']) ? floatval($data['fee_amount']) : 0;
            $data['net_amount'] = floatval($data['amount']) - $feeAmount;
        }

        // Store JSON fields properly
        if (isset($data['gateway_response']) && is_array($data['gateway_response'])) {
            $data['gateway_response'] = json_encode($data['gateway_response']);
        }
        if (isset($data['metadata']) && is_array($data['metadata'])) {
            $data['metadata'] = json_encode($data['metadata']);
        }

        $this->db->insert($this->txnTable, $data);
        $insertId = $this->db->insert_id();

        if ($insertId) {
            $data['id'] = $insertId;
            return $data;
        }

        return false;
    }

    /**
     * Update transaction status
     */
    function updateTransactionStatus($transactionId, $status, $additionalData = array())
    {
        $updateData = array(
            'status' => $status,
            'updated_on' => date('Y-m-d H:i:s')
        );

        if ($status === 'completed') {
            $updateData['completed_at'] = date('Y-m-d H:i:s');
        } elseif ($status === 'refunded') {
            $updateData['refunded_at'] = date('Y-m-d H:i:s');
        }

        // Merge additional data
        $updateData = array_merge($updateData, $additionalData);

        // Handle JSON fields
        if (isset($updateData['gateway_response']) && is_array($updateData['gateway_response'])) {
            $updateData['gateway_response'] = json_encode($updateData['gateway_response']);
        }
        if (isset($updateData['webhook_payload']) && is_array($updateData['webhook_payload'])) {
            $updateData['webhook_payload'] = json_encode($updateData['webhook_payload']);
        }

        $this->db->where('id', $transactionId);
        return $this->db->update($this->txnTable, $updateData);
    }

    /**
     * Update transaction by gateway transaction ID
     */
    function updateByGatewayTxnId($gatewayTxnId, $gatewayCode, $data)
    {
        if (isset($data['gateway_response']) && is_array($data['gateway_response'])) {
            $data['gateway_response'] = json_encode($data['gateway_response']);
        }
        if (isset($data['webhook_payload']) && is_array($data['webhook_payload'])) {
            $data['webhook_payload'] = json_encode($data['webhook_payload']);
        }

        $data['updated_on'] = date('Y-m-d H:i:s');

        $this->db->where('gateway_transaction_id', $gatewayTxnId);
        $this->db->where('gateway_code', $gatewayCode);
        return $this->db->update($this->txnTable, $data);
    }

    /**
     * Get transaction by ID
     */
    function getTransactionById($id)
    {
        $this->db->where('id', $id);
        $result = $this->db->get($this->txnTable)->row_array();

        if ($result) {
            $result['gateway_response'] = !empty($result['gateway_response']) ? json_decode($result['gateway_response'], true) : null;
            $result['metadata'] = !empty($result['metadata']) ? json_decode($result['metadata'], true) : null;
        }

        return $result;
    }

    /**
     * Get transaction by UUID
     */
    function getTransactionByUuid($uuid)
    {
        $this->db->where('transaction_uuid', $uuid);
        $result = $this->db->get($this->txnTable)->row_array();

        if ($result) {
            $result['gateway_response'] = !empty($result['gateway_response']) ? json_decode($result['gateway_response'], true) : null;
            $result['metadata'] = !empty($result['metadata']) ? json_decode($result['metadata'], true) : null;
        }

        return $result;
    }

    /**
     * Get transaction by gateway transaction ID
     */
    function getByGatewayTxnId($gatewayTxnId, $gatewayCode = null)
    {
        $this->db->where('gateway_transaction_id', $gatewayTxnId);
        if ($gatewayCode) {
            $this->db->where('gateway_code', $gatewayCode);
        }
        return $this->db->get($this->txnTable)->row_array();
    }

    /**
     * Get transaction by gateway order ID (for Razorpay, etc.)
     */
    function getByGatewayOrderId($gatewayOrderId, $gatewayCode = null)
    {
        $this->db->where('gateway_order_id', $gatewayOrderId);
        if ($gatewayCode) {
            $this->db->where('gateway_code', $gatewayCode);
        }
        return $this->db->get($this->txnTable)->row_array();
    }

    /**
     * Get transactions for an invoice
     */
    function getTransactionsByInvoice($invoiceId)
    {
        $this->db->where('invoice_id', $invoiceId);
        $this->db->order_by('initiated_at', 'DESC');
        return $this->db->get($this->txnTable)->result_array();
    }

    /**
     * Get successful transaction amount for invoice
     */
    function getSuccessfulPaymentAmount($invoiceId)
    {
        $this->db->select_sum('amount');
        $this->db->where('invoice_id', $invoiceId);
        $this->db->where('status', 'completed');
        $this->db->where('txn_type', 'payment');
        $result = $this->db->get($this->txnTable)->row();
        return $result ? floatval($result->amount) : 0;
    }

    /**
     * Check if invoice is fully paid
     */
    function isInvoicePaid($invoiceId, $invoiceTotal)
    {
        $paidAmount = $this->getSuccessfulPaymentAmount($invoiceId);
        return $paidAmount >= $invoiceTotal;
    }

    // =========================================
    // Refund Methods
    // =========================================

    /**
     * Create refund record
     */
    function createRefund($data)
    {
        $data['refund_uuid'] = gen_uuid();
        $data['requested_at'] = date('Y-m-d H:i:s');

        if (!isset($data['status'])) {
            $data['status'] = 'pending';
        }

        if (isset($data['gateway_response']) && is_array($data['gateway_response'])) {
            $data['gateway_response'] = json_encode($data['gateway_response']);
        }

        $this->db->insert($this->refundTable, $data);
        return $this->db->insert_id();
    }

    /**
     * Update refund status
     */
    function updateRefundStatus($refundId, $status, $additionalData = array())
    {
        $updateData = array(
            'status' => $status
        );

        if ($status === 'completed' || $status === 'failed') {
            $updateData['processed_at'] = date('Y-m-d H:i:s');
        }

        $updateData = array_merge($updateData, $additionalData);

        if (isset($updateData['gateway_response']) && is_array($updateData['gateway_response'])) {
            $updateData['gateway_response'] = json_encode($updateData['gateway_response']);
        }

        $this->db->where('id', $refundId);
        return $this->db->update($this->refundTable, $updateData);
    }

    /**
     * Get refund by ID
     */
    function getRefundById($id)
    {
        $this->db->where('id', $id);
        return $this->db->get($this->refundTable)->row_array();
    }

    /**
     * Get refunds for a transaction
     */
    function getRefundsByTransaction($transactionId)
    {
        $this->db->where('transaction_id', $transactionId);
        $this->db->order_by('requested_at', 'DESC');
        return $this->db->get($this->refundTable)->result_array();
    }

    // =========================================
    // Webhook Log Methods
    // =========================================

    /**
     * Log webhook
     */
    function logWebhook($gatewayCode, $eventType, $payload, $headers = null, $signature = null)
    {
        $data = array(
            'gateway_code' => $gatewayCode,
            'event_type' => $eventType,
            'payload' => is_array($payload) ? json_encode($payload) : $payload,
            'headers' => $headers ? (is_array($headers) ? json_encode($headers) : $headers) : null,
            'signature' => $signature,
            'ip_address' => $this->input->ip_address(),
            'received_at' => date('Y-m-d H:i:s'),
            'processed' => 0
        );

        // Extract event_id if present in payload
        if (is_array($payload) && isset($payload['id'])) {
            $data['event_id'] = $payload['id'];
        }

        $this->db->insert($this->webhookTable, $data);
        return $this->db->insert_id();
    }

    /**
     * Mark webhook as processed
     */
    function markWebhookProcessed($webhookId, $signatureValid, $result = null)
    {
        $this->db->where('id', $webhookId);
        return $this->db->update($this->webhookTable, array(
            'processed' => 1,
            'processed_at' => date('Y-m-d H:i:s'),
            'signature_valid' => $signatureValid ? 1 : 0,
            'process_result' => $result
        ));
    }

    /**
     * Check if webhook event already processed
     */
    function isWebhookProcessed($eventId, $gatewayCode)
    {
        $this->db->where('event_id', $eventId);
        $this->db->where('gateway_code', $gatewayCode);
        $this->db->where('processed', 1);
        return $this->db->count_all_results($this->webhookTable) > 0;
    }

    // =========================================
    // Invoice Integration Methods
    // =========================================

    /**
     * Process successful payment - update invoice and trigger provisioning
     */
    function processSuccessfulPayment($transactionId)
    {
        $transaction = $this->getTransactionById($transactionId);

        if (empty($transaction)) {
            log_message('error', 'processSuccessfulPayment: Transaction not found - ID: ' . $transactionId);
            return false;
        }

        $invoiceId = $transaction['invoice_id'];

        // Get invoice details
        $this->load->model('Invoice_model');
        $invoice = $this->db->where('id', $invoiceId)->get('invoices')->row_array();

        if (empty($invoice)) {
            log_message('error', 'processSuccessfulPayment: Invoice not found - ID: ' . $invoiceId);
            return false;
        }

        // Check if fully paid
        $totalPaid = $this->getSuccessfulPaymentAmount($invoiceId);
        $invoiceTotal = floatval($invoice['total']);

        if ($totalPaid >= $invoiceTotal) {
            // Mark invoice as PAID
            $this->db->where('id', $invoiceId);
            $this->db->update('invoices', array(
                'pay_status' => 'PAID',
                'updated_on' => date('Y-m-d H:i:s')
            ));

            // Trigger service provisioning
            $this->Invoice_model->provisionPaidServices($invoiceId);

            log_message('info', 'Invoice #' . $invoiceId . ' marked as PAID. Total: ' . $invoiceTotal . ', Paid: ' . $totalPaid);
            return true;

        } elseif ($totalPaid > 0) {
            // Partial payment
            $this->db->where('id', $invoiceId);
            $this->db->update('invoices', array(
                'pay_status' => 'PARTIAL',
                'updated_on' => date('Y-m-d H:i:s')
            ));

            log_message('info', 'Invoice #' . $invoiceId . ' partially paid. Total: ' . $invoiceTotal . ', Paid: ' . $totalPaid);
            return true;
        }

        return false;
    }

    /**
     * Record payment in legacy invoice_txn table as well
     */
    function recordInvoiceTxn($transactionId)
    {
        $transaction = $this->getTransactionById($transactionId);

        if (empty($transaction) || $transaction['status'] !== 'completed') {
            return false;
        }

        // Check if already recorded
        $this->db->where('payment_transaction_id', $transactionId);
        if ($this->db->count_all_results('invoice_txn') > 0) {
            return true; // Already recorded
        }

        $txnData = array(
            'invoice_id' => $transaction['invoice_id'],
            'payment_gateway_id' => $transaction['payment_gateway_id'],
            'payment_transaction_id' => $transactionId,
            'transaction_id' => $transaction['gateway_transaction_id'],
            'txn_date' => date('Y-m-d'),
            'amount' => $transaction['amount'],
            'currency_code' => $transaction['currency_code'],
            'type' => $transaction['txn_type'],
            'status' => 1, // Success
            'remarks' => 'Payment via ' . ucfirst($transaction['gateway_code']),
            'inserted_on' => date('Y-m-d H:i:s'),
            'inserted_by' => $transaction['inserted_by']
        );

        $this->db->insert('invoice_txn', $txnData);
        return $this->db->insert_id() > 0;
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
        return $this->db->count_all_results($this->txnTable);
    }

    function countDataTableFilterRecords($where, $bindings)
    {
        $sql = "SELECT COUNT(id) as cnt FROM {$this->txnTable} " . $where;
        $query = $this->db->query($sql, $bindings);
        $data = $query->row_array();
        return !empty($data) ? $data['cnt'] : 0;
    }
}
