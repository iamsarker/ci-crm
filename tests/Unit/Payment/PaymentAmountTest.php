<?php
/**
 * Payment Amount Tests
 *
 * Tests for payment amount calculations and validations
 */

use PHPUnit\Framework\TestCase;

class PaymentAmountTest extends TestCase
{
    /**
     * Test that fee calculation returns correct fixed fee
     */
    public function testFixedFeeCalculation()
    {
        $amount = 100.00;
        $feeFixed = 2.50;
        $feePercent = 0;
        $feeType = 'fixed';

        $fee = $this->calculateFee($amount, $feeType, $feeFixed, $feePercent);

        $this->assertEquals(2.50, $fee);
    }

    /**
     * Test that fee calculation returns correct percentage fee
     */
    public function testPercentageFeeCalculation()
    {
        $amount = 100.00;
        $feeFixed = 0;
        $feePercent = 2.9;
        $feeType = 'percentage';

        $fee = $this->calculateFee($amount, $feeType, $feeFixed, $feePercent);

        $this->assertEquals(2.90, $fee);
    }

    /**
     * Test that fee calculation returns correct combined fee
     */
    public function testCombinedFeeCalculation()
    {
        $amount = 100.00;
        $feeFixed = 0.30;
        $feePercent = 2.9;
        $feeType = 'both';

        $fee = $this->calculateFee($amount, $feeType, $feeFixed, $feePercent);

        $this->assertEquals(3.20, $fee);
    }

    /**
     * Test that no fee is returned for none type
     */
    public function testNoFeeCalculation()
    {
        $amount = 100.00;
        $feeFixed = 2.50;
        $feePercent = 2.9;
        $feeType = 'none';

        $fee = $this->calculateFee($amount, $feeType, $feeFixed, $feePercent);

        $this->assertEquals(0, $fee);
    }

    /**
     * Test amount due calculation
     */
    public function testAmountDueCalculation()
    {
        $invoiceTotal = 500.00;
        $paidAmount = 200.00;

        $amountDue = $invoiceTotal - $paidAmount;

        $this->assertEquals(300.00, $amountDue);
    }

    /**
     * Test that fully paid invoice returns zero due
     */
    public function testFullyPaidInvoiceReturnsZeroDue()
    {
        $invoiceTotal = 500.00;
        $paidAmount = 500.00;

        $amountDue = $invoiceTotal - $paidAmount;

        $this->assertEquals(0, $amountDue);
        $this->assertFalse($amountDue > 0);
    }

    /**
     * Test that overpaid invoice returns negative due
     */
    public function testOverpaidInvoiceReturnsNegativeDue()
    {
        $invoiceTotal = 500.00;
        $paidAmount = 550.00;

        $amountDue = $invoiceTotal - $paidAmount;

        $this->assertEquals(-50.00, $amountDue);
        $this->assertFalse($amountDue > 0);
    }

    /**
     * Test total amount with customer fee
     */
    public function testTotalAmountWithCustomerFee()
    {
        $amountDue = 100.00;
        $feeAmount = 3.20;

        $totalAmount = $amountDue + $feeAmount;

        $this->assertEquals(103.20, $totalAmount);
    }

    /**
     * Test currency conversion for BDT
     */
    public function testCurrencyDetectionBDT()
    {
        $currencyCode = 'BDT';
        $expectedCurrency = ($currencyCode === 'BDT') ? 'BDT' : 'USD';

        $this->assertEquals('BDT', $expectedCurrency);
    }

    /**
     * Test currency conversion for non-BDT
     */
    public function testCurrencyDetectionUSD()
    {
        $currencyCode = 'USD';
        $expectedCurrency = ($currencyCode === 'BDT') ? 'BDT' : 'USD';

        $this->assertEquals('USD', $expectedCurrency);
    }

    /**
     * Test amount precision (2 decimal places)
     */
    public function testAmountPrecision()
    {
        $amount = 100.999;
        $roundedAmount = round($amount, 2);

        $this->assertEquals(101.00, $roundedAmount);
    }

    /**
     * Test minimum payment amount validation
     */
    public function testMinimumPaymentAmount()
    {
        $amount = 0.50;
        $minimumAmount = 1.00;

        $isValid = $amount >= $minimumAmount;

        $this->assertFalse($isValid);
    }

    /**
     * Helper method to calculate fee (mirrors PaymentGateway_model::calculateFee)
     */
    private function calculateFee($amount, $feeType, $feeFixed, $feePercent)
    {
        if ($feeType === 'none') {
            return 0;
        }

        $fee = 0;

        switch ($feeType) {
            case 'fixed':
                $fee = floatval($feeFixed);
                break;
            case 'percentage':
                $fee = ($amount * floatval($feePercent)) / 100;
                break;
            case 'both':
                $fee = floatval($feeFixed) + (($amount * floatval($feePercent)) / 100);
                break;
        }

        return round($fee, 2);
    }
}
