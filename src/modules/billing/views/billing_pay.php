<?php $this->load->view('templates/customer/header'); ?>

<style>
.payment-page {
    max-width: 900px;
    margin: 50px auto;
    padding: 20px;
}

.payment-header {
    background: linear-gradient(135deg, #00897B 0%, #00695C 100%);
    color: white;
    padding: 30px;
    border-radius: 12px 12px 0 0;
    text-align: center;
}

.payment-header h2 {
    margin: 0 0 10px 0;
    font-size: 24px;
}

.payment-header .amount {
    font-size: 36px;
    font-weight: bold;
}

.payment-body {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-top: none;
    border-radius: 0 0 12px 12px;
    padding: 30px;
}

.invoice-summary {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 30px;
}

.invoice-summary h5 {
    margin: 0 0 15px 0;
    color: #333;
}

.invoice-summary table {
    width: 100%;
}

.invoice-summary td {
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.invoice-summary td:last-child {
    text-align: right;
}

.invoice-summary tr:last-child td {
    border-bottom: none;
    font-weight: bold;
}

.payment-methods {
    margin-bottom: 30px;
}

.payment-methods h5 {
    margin-bottom: 20px;
    color: #333;
}

.payment-method-card {
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.payment-method-card:hover {
    border-color: #00897B;
    box-shadow: 0 4px 12px rgba(0, 137, 123, 0.15);
}

.payment-method-card.selected {
    border-color: #00897B;
    background: #f0fdf9;
}

.payment-method-card .method-radio {
    width: 20px;
    height: 20px;
    border: 2px solid #ccc;
    border-radius: 50%;
    margin-right: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.payment-method-card.selected .method-radio {
    border-color: #00897B;
}

.payment-method-card.selected .method-radio::after {
    content: '';
    width: 10px;
    height: 10px;
    background: #00897B;
    border-radius: 50%;
}

.proceed-section {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
    display: none;
}

.proceed-section.active {
    display: block;
}

.payment-details-section {
    margin-top: 20px;
    padding: 25px;
    background: #f8f9fa;
    border-radius: 10px;
    border: 1px solid #e0e0e0;
    display: none;
}

.payment-details-section.active {
    display: block;
}

.payment-method-card .method-header {
    display: flex;
    align-items: center;
    gap: 15px;
}

.payment-method-card .method-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    background: #f5f5f5;
    border-radius: 8px;
}

.payment-method-card .method-info h6 {
    margin: 0;
    font-size: 16px;
}

.payment-method-card .method-info p {
    margin: 5px 0 0 0;
    font-size: 13px;
    color: #666;
}

.payment-method-card .method-fee {
    margin-left: auto;
    font-size: 13px;
    color: #888;
}

.payment-form {
    display: none;
    margin-top: 20px;
    padding: 20px;
    background: #fafafa;
    border-radius: 8px;
}

.payment-form.active {
    display: block;
}

#stripe-card-element {
    padding: 15px;
    border: 1px solid #ccc;
    border-radius: 6px;
    background: white;
}

.btn-pay {
    background: linear-gradient(135deg, #00897B 0%, #00695C 100%);
    border: none;
    color: white;
    padding: 15px 40px;
    font-size: 18px;
    border-radius: 8px;
    cursor: pointer;
    width: 100%;
    margin-top: 20px;
}

.btn-pay:hover {
    background: linear-gradient(135deg, #00695C 0%, #004D40 100%);
}

.btn-pay:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.payment-error {
    background: #ffebee;
    color: #c62828;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
    display: none;
}

.payment-processing {
    text-align: center;
    padding: 40px;
    display: none;
}

.payment-processing .spinner {
    width: 50px;
    height: 50px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #00897B;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.bank-transfer-details {
    background: #fff8e1;
    border: 1px solid #ffca28;
    border-radius: 8px;
    padding: 20px;
    margin-top: 15px;
}

.bank-transfer-details h6 {
    margin: 0 0 15px 0;
    color: #f57c00;
}

.bank-transfer-details table td {
    padding: 8px 15px 8px 0;
}

.bank-transfer-details table td:first-child {
    font-weight: 500;
    color: #666;
}

/* PayPal Button Container */
#paypal-button-container {
    margin-top: 15px;
}
</style>

<div class="payment-page">
    <div class="payment-header">
        <h2><i class="fas fa-file-invoice-dollar"></i> Pay Invoice</h2>
        <div>Invoice #<?php echo htmlspecialchars($invoice['invoice_no']); ?></div>
        <div class="amount"><?php echo $invoice['currency_code']; ?> <?php echo number_format($amount_due, 2); ?></div>
        <?php if ($paid_amount > 0): ?>
        <div style="font-size: 14px; margin-top: 10px; opacity: 0.9;">
            Previously paid: <?php echo $invoice['currency_code']; ?> <?php echo number_format($paid_amount, 2); ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="payment-body">
        <!-- Invoice Summary -->
        <div class="invoice-summary">
            <h5><i class="fas fa-receipt"></i> Invoice Summary</h5>
            <table>
                <?php foreach ($invoice_items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['item']); ?></td>
                    <td><?php echo $invoice['currency_code']; ?> <?php echo number_format($item['total'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if ($invoice['tax'] > 0): ?>
                <tr>
                    <td>Tax</td>
                    <td><?php echo $invoice['currency_code']; ?> <?php echo number_format($invoice['tax'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td>Total Due</td>
                    <td><?php echo $invoice['currency_code']; ?> <?php echo number_format($amount_due, 2); ?></td>
                </tr>
            </table>
        </div>

        <!-- Error Message -->
        <div class="payment-error" id="payment-error"></div>

        <!-- Processing Overlay -->
        <div class="payment-processing" id="payment-processing">
            <div class="spinner"></div>
            <h4>Processing Payment...</h4>
            <p>Please wait while we process your payment. Do not close this page.</p>
        </div>

        <!-- Payment Methods -->
        <div class="payment-methods" id="payment-methods">
            <h5><i class="fas fa-credit-card"></i> Select Payment Method</h5>

            <?php foreach ($gateways as $gateway): ?>
            <div class="payment-method-card" data-gateway="<?php echo $gateway['gateway_code']; ?>" data-gateway-id="<?php echo $gateway['id']; ?>">
                <div class="method-header">
                    <div class="method-radio"></div>
                    <div class="method-icon">
                        <?php
                        $icon = 'fa-money-bill';
                        switch ($gateway['gateway_code']) {
                            case 'stripe': $icon = 'fa-credit-card'; break;
                            case 'paypal': $icon = 'fa-paypal'; break;
                            case 'razorpay': $icon = 'fa-rupee-sign'; break;
                            case 'sslcommerz': $icon = 'fa-mobile-alt'; break;
                            case 'paystack': $icon = 'fa-credit-card'; break;
                            case 'bank_transfer': $icon = 'fa-university'; break;
                            case 'manual': $icon = 'fa-hand-holding-usd'; break;
                        }
                        ?>
                        <i class="fas <?php echo $icon; ?>"></i>
                    </div>
                    <div class="method-info">
                        <h6><?php echo htmlspecialchars($gateway['display_name'] ?: $gateway['name']); ?></h6>
                        <p><?php echo htmlspecialchars($gateway['description'] ?? ''); ?></p>
                    </div>
                    <?php if ($gateway['fee_type'] !== 'none' && $gateway['fee_bearer'] === 'customer'): ?>
                    <div class="method-fee">
                        +<?php
                        if ($gateway['fee_type'] === 'fixed') {
                            echo $invoice['currency_code'] . ' ' . number_format($gateway['fee_fixed'], 2);
                        } elseif ($gateway['fee_type'] === 'percentage') {
                            echo $gateway['fee_percent'] . '%';
                        } else {
                            echo $invoice['currency_code'] . ' ' . number_format($gateway['fee_fixed'], 2) . ' + ' . $gateway['fee_percent'] . '%';
                        }
                        ?> fee
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Proceed Button -->
            <div class="proceed-section" id="proceed-section">
                <button type="button" class="btn-pay" id="proceed-btn">
                    <i class="fas fa-arrow-right"></i> Proceed to Payment
                </button>
            </div>
        </div>

        <!-- Payment Details Section (appears after clicking Proceed) -->
        <div class="payment-details-section" id="payment-details-section">
            <!-- Stripe Payment Form -->
            <div class="payment-form" id="stripe-form" style="display:none;">
                <h5 style="margin-bottom: 20px;"><i class="fas fa-credit-card"></i> Enter Card Details</h5>
                <label>Card Details</label>
                <div id="stripe-card-element"></div>
                <div id="stripe-card-errors" style="color: #c62828; margin-top: 10px; font-size: 14px;"></div>
                <button type="button" class="btn-pay" id="stripe-pay-btn">
                    <i class="fas fa-lock"></i> Pay <?php echo $invoice['currency_code']; ?> <?php echo number_format($amount_due, 2); ?>
                </button>
            </div>

            <!-- PayPal Button -->
            <div class="payment-form" id="paypal-form" style="display:none;">
                <h5 style="margin-bottom: 20px;"><i class="fab fa-paypal"></i> Pay with PayPal</h5>
                <div id="paypal-button-container"></div>
            </div>

            <!-- SSLCommerz -->
            <div class="payment-form" id="sslcommerz-form" style="display:none;">
                <h5 style="margin-bottom: 20px;"><i class="fas fa-mobile-alt"></i> Pay with SSLCommerz</h5>
                <p style="margin-bottom: 15px; color: #666;">
                    <i class="fas fa-info-circle"></i> You will be redirected to SSLCommerz secure payment page to complete your payment using bKash, Nagad, Cards, or Mobile Banking.
                </p>
                <button type="button" class="btn-pay" id="sslcommerz-pay-btn">
                    <i class="fas fa-lock"></i> Pay <?php echo $invoice['currency_code']; ?> <?php echo number_format($amount_due, 2); ?>
                </button>
            </div>

            <!-- Razorpay -->
            <div class="payment-form" id="razorpay-form" style="display:none;">
                <h5 style="margin-bottom: 20px;"><i class="fas fa-rupee-sign"></i> Pay with Razorpay</h5>
                <p style="margin-bottom: 15px; color: #666;">
                    <i class="fas fa-info-circle"></i> Pay securely using UPI, Cards, Net Banking, or Wallets.
                </p>
                <button type="button" class="btn-pay" id="razorpay-pay-btn">
                    <i class="fas fa-lock"></i> Pay <?php echo $invoice['currency_code']; ?> <?php echo number_format($amount_due, 2); ?>
                </button>
            </div>

            <!-- Paystack -->
            <div class="payment-form" id="paystack-form" style="display:none;">
                <h5 style="margin-bottom: 20px;"><i class="fas fa-credit-card"></i> Pay with Paystack</h5>
                <p style="margin-bottom: 15px; color: #666;">
                    <i class="fas fa-info-circle"></i> Pay securely using Cards, Bank Transfer, or Mobile Money.
                </p>
                <button type="button" class="btn-pay" id="paystack-pay-btn">
                    <i class="fas fa-lock"></i> Pay <?php echo $invoice['currency_code']; ?> <?php echo number_format($amount_due, 2); ?>
                </button>
            </div>

            <!-- Bank Transfer -->
            <div class="payment-form" id="bank-form" style="display:none;">
                <h5 style="margin-bottom: 20px;"><i class="fas fa-university"></i> Bank Transfer Details</h5>
                <div class="bank-transfer-details">
                    <table>
                        <?php
                        $bankGateway = null;
                        foreach ($gateways as $gw) {
                            if ($gw['gateway_code'] === 'bank_transfer') {
                                $bankGateway = $gw;
                                break;
                            }
                        }
                        if ($bankGateway):
                        ?>
                        <?php if (!empty($bankGateway['bank_name'])): ?>
                        <tr><td>Bank Name:</td><td><?php echo htmlspecialchars($bankGateway['bank_name']); ?></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($bankGateway['account_name'])): ?>
                        <tr><td>Account Name:</td><td><?php echo htmlspecialchars($bankGateway['account_name']); ?></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($bankGateway['account_number'])): ?>
                        <tr><td>Account Number:</td><td><?php echo htmlspecialchars($bankGateway['account_number']); ?></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($bankGateway['routing_number'])): ?>
                        <tr><td>Routing Number:</td><td><?php echo htmlspecialchars($bankGateway['routing_number']); ?></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($bankGateway['swift_code'])): ?>
                        <tr><td>SWIFT Code:</td><td><?php echo htmlspecialchars($bankGateway['swift_code']); ?></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($bankGateway['iban'])): ?>
                        <tr><td>IBAN:</td><td><?php echo htmlspecialchars($bankGateway['iban']); ?></td></tr>
                        <?php endif; ?>
                        <?php endif; ?>
                        <tr><td>Reference:</td><td><strong><?php echo $invoice['invoice_no']; ?></strong></td></tr>
                    </table>
                    <p style="margin-top: 15px; font-size: 13px; color: #666;">
                        <i class="fas fa-exclamation-triangle"></i>
                        Please include the invoice number as payment reference. Your order will be processed once payment is confirmed.
                    </p>
                </div>
                <button type="button" class="btn-pay btn-offline-pay" data-gateway="bank_transfer">
                    <i class="fas fa-check"></i> Confirm & Mark as Pending
                </button>
            </div>

            <!-- Manual Payment -->
            <div class="payment-form" id="manual-form" style="display:none;">
                <h5 style="margin-bottom: 20px;"><i class="fas fa-hand-holding-usd"></i> Manual Payment</h5>
                <div class="bank-transfer-details">
                    <?php
                    $manualGateway = null;
                    foreach ($gateways as $gw) {
                        if ($gw['gateway_code'] === 'manual') {
                            $manualGateway = $gw;
                            break;
                        }
                    }
                    ?>
                    <?php if ($manualGateway && !empty($manualGateway['instructions'])): ?>
                    <p><?php echo nl2br(htmlspecialchars($manualGateway['instructions'])); ?></p>
                    <?php else: ?>
                    <p>Please contact our support team for payment instructions. Include your invoice number: <strong><?php echo $invoice['invoice_no']; ?></strong></p>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn-pay btn-offline-pay" data-gateway="manual">
                    <i class="fas fa-check"></i> Confirm & Mark as Pending
                </button>
            </div>

            <button type="button" class="btn btn-secondary mt-3" id="back-to-methods" style="width: 100%;">
                <i class="fas fa-arrow-left"></i> Back to Payment Methods
            </button>
        </div>

        <div style="text-align: center; margin-top: 20px;">
            <a href="<?php echo base_url(); ?>billing/viewinvoice/<?php echo $invoice['invoice_uuid']; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Invoice
            </a>
        </div>
    </div>
</div>
<?php $this->load->view('templates/customer/footer_script'); ?>

<!-- Stripe JS -->
<?php if (!empty($stripe_publishable_key)): ?>
<script src="https://js.stripe.com/v3/"></script>
<?php endif; ?>

<!-- PayPal JS -->
<?php if (!empty($paypal_client_id)): ?>
<script src="https://www.paypal.com/sdk/js?client-id=<?php echo $paypal_client_id; ?>&currency=<?php echo $invoice['currency_code']; ?>"></script>
<?php endif; ?>

<script>
var invoiceUuid = '<?php echo $invoice['invoice_uuid']; ?>';
var amountDue = <?php echo $amount_due; ?>;
var currencyCode = '<?php echo $invoice['currency_code']; ?>';

var selectedGateway = null;

document.addEventListener('DOMContentLoaded', function() {
    // Payment method selection
    document.querySelectorAll('.payment-method-card').forEach(function(card) {
        card.addEventListener('click', function() {
            // Remove selected from all
            document.querySelectorAll('.payment-method-card').forEach(function(c) {
                c.classList.remove('selected');
            });

            // Select this one
            this.classList.add('selected');
            selectedGateway = this.getAttribute('data-gateway');

            // Show proceed button
            document.getElementById('proceed-section').classList.add('active');
        });
    });

    // Proceed button click
    document.getElementById('proceed-btn').addEventListener('click', function() {
        if (!selectedGateway) {
            showError('Please select a payment method');
            return;
        }

        // Hide payment methods, show payment details section
        document.getElementById('payment-methods').style.display = 'none';
        document.getElementById('payment-details-section').classList.add('active');

        // Hide all payment forms
        document.querySelectorAll('#payment-details-section .payment-form').forEach(function(form) {
            form.style.display = 'none';
        });

        // Show the selected payment form
        var formId = selectedGateway + '-form';
        var form = document.getElementById(formId);
        if (form) {
            form.style.display = 'block';
        }

        // Initialize gateway-specific components when visible
        if (selectedGateway === 'stripe' && typeof initStripeCard === 'function') {
            initStripeCard();
        }
        if (selectedGateway === 'paypal' && typeof initPayPalButtons === 'function') {
            initPayPalButtons();
        }
    });

    // Back to payment methods
    document.getElementById('back-to-methods').addEventListener('click', function() {
        document.getElementById('payment-methods').style.display = 'block';
        document.getElementById('payment-details-section').classList.remove('active');
    });
}); // End DOMContentLoaded

function showError(message) {
    var errorDiv = document.getElementById('payment-error');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
}

function hideError() {
    document.getElementById('payment-error').style.display = 'none';
}

function showProcessing() {
    document.getElementById('payment-methods').style.display = 'none';
    document.getElementById('payment-processing').style.display = 'block';
}

function hideProcessing() {
    document.getElementById('payment-methods').style.display = 'block';
    document.getElementById('payment-processing').style.display = 'none';
}

<?php if (!empty($stripe_publishable_key)): ?>
// Stripe Setup
var stripe = Stripe('<?php echo $stripe_publishable_key; ?>');
var elements = stripe.elements();
var cardElement = null;
var stripeMounted = false;

function initStripeCard() {
    if (stripeMounted) return;

    cardElement = elements.create('card', {
        style: {
            base: {
                fontSize: '16px',
                color: '#333',
                '::placeholder': { color: '#aab7c4' }
            }
        }
    });
    cardElement.mount('#stripe-card-element');
    stripeMounted = true;

    cardElement.on('change', function(event) {
        var displayError = document.getElementById('stripe-card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });
}

document.getElementById('stripe-pay-btn').addEventListener('click', function() {
    var btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    hideError();

    // First, create PaymentIntent
    fetch('<?php echo base_url(); ?>billing/pay/stripe_init', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'invoice_uuid=' + encodeURIComponent(invoiceUuid) + '&<?php echo $this->security->get_csrf_token_name(); ?>=<?php echo $this->security->get_csrf_hash(); ?>'
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (!data.success) {
            throw new Error(data.error);
        }

        // Confirm payment with Stripe
        return stripe.confirmCardPayment(data.client_secret, {
            payment_method: {
                card: cardElement
            }
        }).then(function(result) {
            return { result: result, transactionUuid: data.transaction_uuid };
        });
    })
    .then(function(data) {
        if (data.result.error) {
            throw new Error(data.result.error.message);
        }

        // Payment succeeded
        showProcessing();
        window.location.href = '<?php echo base_url(); ?>billing/pay/stripe_success/' + data.transactionUuid;
    })
    .catch(function(error) {
        showError(error.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-lock"></i> Pay <?php echo $invoice['currency_code']; ?> <?php echo number_format($amount_due, 2); ?>';
    });
});
<?php endif; ?>

// Offline Payment (Bank Transfer / Manual)
document.querySelectorAll('.btn-offline-pay').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var gateway = this.getAttribute('data-gateway');
        var originalHtml = this.innerHTML;
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        hideError();

        fetch('<?php echo base_url(); ?>billing/pay/offline_confirm', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'invoice_uuid=' + encodeURIComponent(invoiceUuid) + '&gateway=' + encodeURIComponent(gateway) + '&<?php echo $this->security->get_csrf_token_name(); ?>=<?php echo $this->security->get_csrf_hash(); ?>'
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (!data.success) {
                throw new Error(data.error || 'Failed to process request');
            }
            window.location.href = '<?php echo base_url(); ?>billing/pay/pending/' + data.transaction_uuid;
        })
        .catch(function(error) {
            showError(error.message);
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
    });
});

// SSLCommerz Payment
var sslcommerzBtn = document.getElementById('sslcommerz-pay-btn');
if (sslcommerzBtn) {
    sslcommerzBtn.addEventListener('click', function() {
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Redirecting...';
        hideError();

        fetch('<?php echo base_url(); ?>billing/pay/sslcommerz_init', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'invoice_uuid=' + encodeURIComponent(invoiceUuid) + '&<?php echo $this->security->get_csrf_token_name(); ?>=<?php echo $this->security->get_csrf_hash(); ?>'
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (!data.success) {
                throw new Error(data.error || 'Failed to initialize payment');
            }
            // Redirect to SSLCommerz payment page
            window.location.href = data.gateway_url;
        })
        .catch(function(error) {
            showError(error.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-lock"></i> Pay <?php echo $invoice['currency_code']; ?> <?php echo number_format($amount_due, 2); ?>';
        });
    });
}

// Razorpay Payment
var razorpayBtn = document.getElementById('razorpay-pay-btn');
if (razorpayBtn) {
    razorpayBtn.addEventListener('click', function() {
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        hideError();

        fetch('<?php echo base_url(); ?>billing/pay/razorpay_init', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'invoice_uuid=' + encodeURIComponent(invoiceUuid) + '&<?php echo $this->security->get_csrf_token_name(); ?>=<?php echo $this->security->get_csrf_hash(); ?>'
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (!data.success) {
                throw new Error(data.error || 'Failed to initialize payment');
            }
            // Open Razorpay checkout
            var options = {
                key: data.key_id,
                amount: data.amount,
                currency: data.currency,
                name: '<?php echo addslashes(getSettingsValue('company_name', 'WHMAZ')); ?>',
                description: 'Invoice #<?php echo $invoice['invoice_no']; ?>',
                order_id: data.order_id,
                handler: function(response) {
                    showProcessing();
                    window.location.href = '<?php echo base_url(); ?>billing/pay/razorpay_success?payment_id=' + response.razorpay_payment_id + '&order_id=' + response.razorpay_order_id + '&signature=' + response.razorpay_signature + '&transaction_uuid=' + data.transaction_uuid;
                },
                prefill: {
                    email: '<?php echo addslashes($invoice['email'] ?? ''); ?>',
                    contact: '<?php echo addslashes($invoice['phone'] ?? ''); ?>'
                },
                theme: {
                    color: '#00897B'
                },
                modal: {
                    ondismiss: function() {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-lock"></i> Pay <?php echo $invoice['currency_code']; ?> <?php echo number_format($amount_due, 2); ?>';
                    }
                }
            };
            var rzp = new Razorpay(options);
            rzp.open();
        })
        .catch(function(error) {
            showError(error.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-lock"></i> Pay <?php echo $invoice['currency_code']; ?> <?php echo number_format($amount_due, 2); ?>';
        });
    });
}

// Paystack Payment
var paystackBtn = document.getElementById('paystack-pay-btn');
if (paystackBtn) {
    paystackBtn.addEventListener('click', function() {
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        hideError();

        fetch('<?php echo base_url(); ?>billing/pay/paystack_init', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'invoice_uuid=' + encodeURIComponent(invoiceUuid) + '&<?php echo $this->security->get_csrf_token_name(); ?>=<?php echo $this->security->get_csrf_hash(); ?>'
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (!data.success) {
                throw new Error(data.error || 'Failed to initialize payment');
            }
            // Redirect to Paystack payment page
            window.location.href = data.authorization_url;
        })
        .catch(function(error) {
            showError(error.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-lock"></i> Pay <?php echo $invoice['currency_code']; ?> <?php echo number_format($amount_due, 2); ?>';
        });
    });
}

<?php if (!empty($paypal_client_id)): ?>
// PayPal Setup
var transactionUuid = null;
var paypalRendered = false;

function initPayPalButtons() {
    if (paypalRendered) return;
    paypalRendered = true;

    paypal.Buttons({
        createOrder: function(data, actions) {
            return fetch('<?php echo base_url(); ?>billing/pay/paypal_init', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'invoice_uuid=' + encodeURIComponent(invoiceUuid) + '&<?php echo $this->security->get_csrf_token_name(); ?>=<?php echo $this->security->get_csrf_hash(); ?>'
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (!data.success) {
                    throw new Error(data.error);
                }
                transactionUuid = data.transaction_uuid;
                return data.order_id;
            });
        },
        onApprove: function(data, actions) {
            showProcessing();

            return fetch('<?php echo base_url(); ?>billing/pay/paypal_capture', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'order_id=' + encodeURIComponent(data.orderID) + '&transaction_uuid=' + encodeURIComponent(transactionUuid) + '&<?php echo $this->security->get_csrf_token_name(); ?>=<?php echo $this->security->get_csrf_hash(); ?>'
            })
            .then(function(response) { return response.json(); })
            .then(function(captureData) {
                if (!captureData.success) {
                    throw new Error(captureData.error);
                }
                window.location.href = '<?php echo base_url(); ?>billing/pay/stripe_success/' + transactionUuid;
            })
            .catch(function(error) {
                hideProcessing();
                showError(error.message);
            });
        },
        onCancel: function(data) {
            if (transactionUuid) {
                window.location.href = '<?php echo base_url(); ?>billing/pay/paypal_cancel/' + transactionUuid;
            }
        },
        onError: function(err) {
            hideProcessing();
            showError('Payment failed. Please try again.');
            console.error(err);
        }
    }).render('#paypal-button-container');
}
<?php endif; ?>
</script>

<?php $this->load->view('templates/customer/footer'); ?>
