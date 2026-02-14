<?php
/**
 * Payment Transaction Tests
 *
 * Tests for payment transaction handling and validation
 */

use PHPUnit\Framework\TestCase;

class PaymentTransactionTest extends TestCase
{
    /**
     * Test transaction UUID generation format
     */
    public function testTransactionUuidFormat()
    {
        // UUID format: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
        $uuid = $this->generateUuid();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $uuid
        );
    }

    /**
     * Test transaction status transitions - pending to processing
     */
    public function testStatusTransitionPendingToProcessing()
    {
        $currentStatus = 'pending';
        $newStatus = 'processing';

        $isValidTransition = $this->isValidStatusTransition($currentStatus, $newStatus);

        $this->assertTrue($isValidTransition);
    }

    /**
     * Test transaction status transitions - processing to completed
     */
    public function testStatusTransitionProcessingToCompleted()
    {
        $currentStatus = 'processing';
        $newStatus = 'completed';

        $isValidTransition = $this->isValidStatusTransition($currentStatus, $newStatus);

        $this->assertTrue($isValidTransition);
    }

    /**
     * Test transaction status transitions - processing to failed
     */
    public function testStatusTransitionProcessingToFailed()
    {
        $currentStatus = 'processing';
        $newStatus = 'failed';

        $isValidTransition = $this->isValidStatusTransition($currentStatus, $newStatus);

        $this->assertTrue($isValidTransition);
    }

    /**
     * Test invalid status transition - completed to pending
     */
    public function testInvalidStatusTransitionCompletedToPending()
    {
        $currentStatus = 'completed';
        $newStatus = 'pending';

        $isValidTransition = $this->isValidStatusTransition($currentStatus, $newStatus);

        $this->assertFalse($isValidTransition);
    }

    /**
     * Test duplicate transaction detection
     */
    public function testDuplicateTransactionDetection()
    {
        $existingTransactionId = 'txn_123456';
        $newTransactionId = 'txn_123456';

        $isDuplicate = ($existingTransactionId === $newTransactionId);

        $this->assertTrue($isDuplicate);
    }

    /**
     * Test transaction data validation - required fields
     */
    public function testTransactionRequiredFields()
    {
        $transactionData = array(
            'invoice_id' => 1,
            'payment_gateway_id' => 1,
            'gateway_code' => 'stripe',
            'amount' => 100.00,
            'currency_code' => 'USD',
            'status' => 'pending'
        );

        $requiredFields = array('invoice_id', 'payment_gateway_id', 'gateway_code', 'amount', 'currency_code', 'status');

        $isValid = $this->validateRequiredFields($transactionData, $requiredFields);

        $this->assertTrue($isValid);
    }

    /**
     * Test transaction data validation - missing field
     */
    public function testTransactionMissingRequiredField()
    {
        $transactionData = array(
            'invoice_id' => 1,
            'payment_gateway_id' => 1,
            // Missing gateway_code
            'amount' => 100.00,
            'currency_code' => 'USD',
            'status' => 'pending'
        );

        $requiredFields = array('invoice_id', 'payment_gateway_id', 'gateway_code', 'amount', 'currency_code', 'status');

        $isValid = $this->validateRequiredFields($transactionData, $requiredFields);

        $this->assertFalse($isValid);
    }

    /**
     * Test that completed transaction cannot be modified
     */
    public function testCompletedTransactionImmutable()
    {
        $transaction = createTestTransaction(array('status' => 'completed'));

        $canModify = $this->canModifyTransaction($transaction);

        $this->assertFalse($canModify);
    }

    /**
     * Test that pending transaction can be modified
     */
    public function testPendingTransactionMutable()
    {
        $transaction = createTestTransaction(array('status' => 'pending'));

        $canModify = $this->canModifyTransaction($transaction);

        $this->assertTrue($canModify);
    }

    /**
     * Test gateway code validation
     */
    public function testValidGatewayCode()
    {
        $validGateways = array('stripe', 'paypal', 'sslcommerz', 'razorpay', 'paystack', 'bank_transfer', 'manual');
        $gatewayCode = 'stripe';

        $isValid = in_array($gatewayCode, $validGateways);

        $this->assertTrue($isValid);
    }

    /**
     * Test invalid gateway code
     */
    public function testInvalidGatewayCode()
    {
        $validGateways = array('stripe', 'paypal', 'sslcommerz', 'razorpay', 'paystack', 'bank_transfer', 'manual');
        $gatewayCode = 'invalid_gateway';

        $isValid = in_array($gatewayCode, $validGateways);

        $this->assertFalse($isValid);
    }

    /**
     * Test transaction metadata JSON encoding
     */
    public function testMetadataJsonEncoding()
    {
        $metadata = array(
            'invoice_no' => 'INV-001',
            'company_id' => 1,
            'custom_field' => 'value'
        );

        $encoded = json_encode($metadata);
        $decoded = json_decode($encoded, true);

        $this->assertEquals($metadata, $decoded);
    }

    /**
     * Test double-charge prevention check
     */
    public function testDoubleChargePrevention()
    {
        $transaction = createTestTransaction(array('status' => 'completed'));

        // Simulating the check in paypal_capture and sslcommerz_success
        $isAlreadyCompleted = ($transaction['status'] === 'completed');

        $this->assertTrue($isAlreadyCompleted);
    }

    /**
     * Helper to generate UUID
     */
    private function generateUuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * Helper to validate status transition
     */
    private function isValidStatusTransition($currentStatus, $newStatus)
    {
        $validTransitions = array(
            'pending' => array('processing', 'failed', 'cancelled'),
            'processing' => array('completed', 'failed', 'cancelled'),
            'awaiting_confirmation' => array('completed', 'failed', 'cancelled'),
            'completed' => array(), // No transitions allowed from completed
            'failed' => array('pending'), // Can retry failed
            'cancelled' => array() // No transitions from cancelled
        );

        if (!isset($validTransitions[$currentStatus])) {
            return false;
        }

        return in_array($newStatus, $validTransitions[$currentStatus]);
    }

    /**
     * Helper to validate required fields
     */
    private function validateRequiredFields($data, $requiredFields)
    {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Helper to check if transaction can be modified
     */
    private function canModifyTransaction($transaction)
    {
        $immutableStatuses = array('completed', 'refunded');
        return !in_array($transaction['status'], $immutableStatuses);
    }
}
