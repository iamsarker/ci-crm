<?php $this->load->view('templates/customer/header'); ?>

<div class="payment-page">
    <div class="payment-header">
        <h2><i class="fas fa-file-invoice-dollar"></i> Complete Your Payment</h2>
        <div>Your secure checkout will open on this page</div>
    </div>

    <div class="payment-body">
        <div id="resume-error" class="alert alert-danger d-hidden"></div>

        <div id="resume-loading" class="text-center py-4">
            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
            <p class="mt-3 mb-0">Opening secure checkout&hellip;</p>
        </div>

        <div id="resume-actions" class="text-center py-4 d-hidden">
            <p class="mb-3">The checkout window did not open, or you closed it.</p>
            <button type="button" class="btn-pay" id="resume-retry-btn">
                <i class="fas fa-lock"></i> Open Checkout
            </button>
        </div>

        <div class="text-center mt-3">
            <a href="<?php echo base_url(); ?>invoicing/invoices" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Go to My Invoices
            </a>
        </div>
    </div>
</div>

<?php if (!empty($paddle_client_token)): ?>
<script src="https://cdn.paddle.com/paddle/v2/paddle.js"></script>
<?php endif; ?>

<script>
(function () {
    // Server-side validated against /^txn_[A-Za-z0-9]+$/ before reaching here.
    var transactionId = '<?php echo htmlspecialchars($paddle_transaction_id, ENT_QUOTES); ?>';
    var clientToken   = '<?php echo htmlspecialchars($paddle_client_token, ENT_QUOTES); ?>';
    var environment   = '<?php echo htmlspecialchars($paddle_environment, ENT_QUOTES); ?>';

    var loadingEl = document.getElementById('resume-loading');
    var actionsEl = document.getElementById('resume-actions');
    var errorEl   = document.getElementById('resume-error');

    function showError(msg) {
        errorEl.textContent = msg;
        errorEl.classList.remove('d-hidden');
        loadingEl.classList.add('d-hidden');
        actionsEl.classList.add('d-hidden');
    }

    function showRetry() {
        loadingEl.classList.add('d-hidden');
        actionsEl.classList.remove('d-hidden');
    }

    if (!transactionId) {
        showError('No payment reference was supplied. Please open the invoice from your account and pay from there.');
        return;
    }
    if (!clientToken || typeof Paddle === 'undefined') {
        showError('Checkout is unavailable right now. Please open the invoice from your account, or contact support.');
        return;
    }

    if (environment === 'sandbox') {
        Paddle.Environment.set('sandbox');
    }

    Paddle.Initialize({
        token: clientToken,
        eventCallback: function (ev) {
            if (ev && ev.name === 'checkout.completed') {
                // The webhook is what actually marks the invoice PAID; this only
                // moves the customer somewhere sensible once Paddle confirms.
                loadingEl.innerHTML =
                    '<i class="fas fa-check-circle fa-2x text-success"></i>' +
                    '<p class="mt-3 mb-0">Payment received. Redirecting&hellip;</p>';
                loadingEl.classList.remove('d-hidden');
                actionsEl.classList.add('d-hidden');
                setTimeout(function () {
                    window.location.href = '<?php echo base_url(); ?>invoicing/invoices';
                }, 2500);
            }
        }
    });

    function openCheckout() {
        try {
            Paddle.Checkout.open({ transactionId: transactionId });
            // Offer a retry in case the overlay is dismissed.
            setTimeout(showRetry, 1500);
        } catch (e) {
            showError('Could not open the checkout. Please contact support.');
        }
    }

    document.getElementById('resume-retry-btn').addEventListener('click', openCheckout);
    openCheckout();
})();
</script>

<?php $this->load->view('templates/customer/footer'); ?>
