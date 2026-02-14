<?php $this->load->view('templates/customer/header'); ?>

<style>
.pending-page {
    max-width: 700px;
    margin: 40px auto;
    padding: 20px;
}

.pending-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    overflow: hidden;
}

.pending-header {
    background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
    color: white;
    padding: 30px;
    text-align: center;
}

.pending-header i {
    font-size: 48px;
    margin-bottom: 15px;
}

.pending-header h2 {
    margin: 0 0 10px 0;
    font-size: 24px;
}

.pending-body {
    padding: 30px;
}

.info-box {
    background: #fff8e1;
    border: 1px solid #ffca28;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 25px;
}

.info-box h5 {
    margin: 0 0 15px 0;
    color: #f57c00;
}

.info-table {
    width: 100%;
}

.info-table td {
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.info-table td:first-child {
    font-weight: 500;
    color: #666;
    width: 40%;
}

.info-table tr:last-child td {
    border-bottom: none;
}

.bank-details {
    background: #e3f2fd;
    border: 1px solid #64b5f6;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 25px;
}

.bank-details h5 {
    margin: 0 0 15px 0;
    color: #1976d2;
}

.instructions-box {
    background: #f5f5f5;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 25px;
}

.instructions-box h5 {
    margin: 0 0 15px 0;
    color: #333;
}

.btn-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
}

.btn-actions .btn {
    padding: 12px 30px;
    font-size: 16px;
    border-radius: 8px;
}
</style>

<div class="pending-page">
    <div class="pending-card">
        <div class="pending-header">
            <i class="fas fa-clock"></i>
            <h2>Payment Pending</h2>
            <p>Your payment is awaiting confirmation</p>
        </div>

        <div class="pending-body">
            <!-- Transaction Info -->
            <div class="info-box">
                <h5><i class="fas fa-receipt"></i> Transaction Details</h5>
                <table class="info-table">
                    <tr>
                        <td>Transaction ID</td>
                        <td><strong><?php echo strtoupper(substr($transaction['transaction_uuid'], 0, 8)); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Invoice Number</td>
                        <td><?php echo htmlspecialchars($invoice['invoice_no']); ?></td>
                    </tr>
                    <tr>
                        <td>Amount</td>
                        <td><strong><?php echo $invoice['currency_code']; ?> <?php echo number_format($transaction['amount'], 2); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Payment Method</td>
                        <td><?php echo htmlspecialchars($gateway['display_name'] ?: $gateway['name']); ?></td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td><span class="badge bg-warning text-dark">Awaiting Confirmation</span></td>
                    </tr>
                </table>
            </div>

            <?php if ($gateway['gateway_code'] === 'bank_transfer'): ?>
            <!-- Bank Transfer Details -->
            <div class="bank-details">
                <h5><i class="fas fa-university"></i> Bank Transfer Details</h5>
                <table class="info-table">
                    <?php if (!empty($gateway['bank_name'])): ?>
                    <tr><td>Bank Name</td><td><?php echo htmlspecialchars($gateway['bank_name']); ?></td></tr>
                    <?php endif; ?>
                    <?php if (!empty($gateway['account_name'])): ?>
                    <tr><td>Account Name</td><td><?php echo htmlspecialchars($gateway['account_name']); ?></td></tr>
                    <?php endif; ?>
                    <?php if (!empty($gateway['account_number'])): ?>
                    <tr><td>Account Number</td><td><strong><?php echo htmlspecialchars($gateway['account_number']); ?></strong></td></tr>
                    <?php endif; ?>
                    <?php if (!empty($gateway['routing_number'])): ?>
                    <tr><td>Routing Number</td><td><?php echo htmlspecialchars($gateway['routing_number']); ?></td></tr>
                    <?php endif; ?>
                    <?php if (!empty($gateway['swift_code'])): ?>
                    <tr><td>SWIFT Code</td><td><?php echo htmlspecialchars($gateway['swift_code']); ?></td></tr>
                    <?php endif; ?>
                    <?php if (!empty($gateway['iban'])): ?>
                    <tr><td>IBAN</td><td><?php echo htmlspecialchars($gateway['iban']); ?></td></tr>
                    <?php endif; ?>
                    <tr><td>Payment Reference</td><td><strong><?php echo $invoice['invoice_no']; ?></strong></td></tr>
                </table>
            </div>
            <?php endif; ?>

            <!-- Instructions -->
            <div class="instructions-box">
                <h5><i class="fas fa-info-circle"></i> What's Next?</h5>
                <?php if ($gateway['gateway_code'] === 'bank_transfer'): ?>
                <ol style="margin: 0; padding-left: 20px; color: #666;">
                    <li>Transfer the exact amount shown above to the bank account provided</li>
                    <li>Use your invoice number (<strong><?php echo $invoice['invoice_no']; ?></strong>) as the payment reference</li>
                    <li>Once we receive and verify your payment, your invoice will be marked as paid</li>
                    <li>You will receive an email confirmation when the payment is processed</li>
                </ol>
                <?php elseif (!empty($gateway['instructions'])): ?>
                <p style="margin: 0; color: #666;"><?php echo nl2br(htmlspecialchars($gateway['instructions'])); ?></p>
                <?php else: ?>
                <ol style="margin: 0; padding-left: 20px; color: #666;">
                    <li>Complete the payment using your preferred method</li>
                    <li>Contact our support team with your invoice number and payment details</li>
                    <li>Once verified, your invoice will be marked as paid</li>
                </ol>
                <?php endif; ?>
            </div>

            <div class="btn-actions">
                <a href="<?php echo base_url(); ?>billing/viewinvoice/<?php echo $invoice['invoice_uuid']; ?>" class="btn btn-secondary">
                    <i class="fas fa-file-invoice"></i> View Invoice
                </a>
                <a href="<?php echo base_url(); ?>billing/invoices" class="btn btn-primary">
                    <i class="fas fa-list"></i> All Invoices
                </a>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('templates/customer/footer_script'); ?>
<?php $this->load->view('templates/customer/footer'); ?>
