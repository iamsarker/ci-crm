<?php $this->load->view('templates/customer/header'); ?>

<div class="content content-fixed content-wrapper">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">

        <!-- Page Header -->
        <div class="page-header-card page-header-checkout mg-b-25">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h3 class="mg-b-0"><i class="fa fa-lock mg-r-10"></i>Secure Checkout</h3>
                    <nav aria-label="breadcrumb" class="mg-t-8">
                        <ol class="breadcrumb breadcrumb-style1 mg-b-0">
                            <li class="breadcrumb-item"><a href="<?=base_url()?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?=base_url()?>cart">Cart</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Checkout</li>
                        </ol>
                    </nav>
                </div>
                <div class="header-actions mg-t-10 mg-md-t-0">
                    <a href="<?=base_url()?>cart" class="btn btn-light">
                        <i class="fa fa-arrow-left mg-r-5"></i> Back to Cart
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 col-md-4 mg-b-20">
                <?php $this->load->view('templates/customer/cart_category_nav'); ?>
                <?php $this->load->view('templates/customer/cart_action_nav'); ?>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9 col-md-8">
                <!-- Checkout Progress -->
                <div class="checkout-progress mg-b-25">
                    <div class="progress-step completed">
                        <div class="step-icon"><i class="fa fa-shopping-cart"></i></div>
                        <span class="step-label">Cart</span>
                    </div>
                    <div class="progress-line completed"></div>
                    <div class="progress-step active">
                        <div class="step-icon"><i class="fa fa-credit-card"></i></div>
                        <span class="step-label">Payment</span>
                    </div>
                    <div class="progress-line"></div>
                    <div class="progress-step">
                        <div class="step-icon"><i class="fa fa-check-circle"></i></div>
                        <span class="step-label">Complete</span>
                    </div>
                </div>

                <!-- Payment Method Card -->
                <div class="card checkout-card mg-b-20">
                    <div class="card-header checkout-card-header">
                        <div class="header-icon">
                            <i class="fa fa-credit-card"></i>
                        </div>
                        <div class="header-text">
                            <h5 class="mg-b-0">Payment Method</h5>
                            <p class="mg-b-0">Choose how you'd like to pay for your order</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="payment-options">
                            <label class="payment-option active">
                                <input type="radio" name="payment_type" value="1" checked>
                                <div class="option-content">
                                    <div class="option-icon">
                                        <i class="fa fa-globe"></i>
                                    </div>
                                    <div class="option-details">
                                        <span class="option-title">Online Payment</span>
                                        <span class="option-desc">Pay securely using credit card, PayPal, or other online methods</span>
                                    </div>
                                    <div class="option-check">
                                        <i class="fa fa-check-circle"></i>
                                    </div>
                                </div>
                            </label>
                            <label class="payment-option">
                                <input type="radio" name="payment_type" value="2">
                                <div class="option-content">
                                    <div class="option-icon">
                                        <i class="fa fa-university"></i>
                                    </div>
                                    <div class="option-details">
                                        <span class="option-title">Offline Payment</span>
                                        <span class="option-desc">Pay via bank transfer, check, or other offline methods</span>
                                    </div>
                                    <div class="option-check">
                                        <i class="fa fa-check-circle"></i>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Security Info -->
                <div class="security-info-card mg-b-20">
                    <div class="security-icon">
                        <i class="fa fa-shield-alt"></i>
                    </div>
                    <div class="security-text">
                        <strong>Secure Transaction</strong>
                        <span>Your payment information is encrypted and secure. We never store your card details.</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="checkout-actions">
                    <a href="<?=base_url()?>cart" class="btn btn-light btn-back">
                        <i class="fa fa-arrow-left mg-r-5"></i> Back to Cart
                    </a>
                    <a href="<?=base_url()?>cart/checkout" class="btn btn-success btn-continue">
                        <i class="fa fa-lock mg-r-5"></i> Complete Order
                    </a>
                </div>
            </div>
        </div>

    </div><!-- container -->
</div><!-- content -->

<?php $this->load->view('templates/customer/footer_script'); ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Payment option selection
        var paymentOptions = document.querySelectorAll('.payment-option input[type="radio"]');
        paymentOptions.forEach(function(radio) {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.payment-option').forEach(function(opt) {
                    opt.classList.remove('active');
                });
                this.closest('.payment-option').classList.add('active');
            });
        });
    });
</script>

<?php $this->load->view('templates/customer/footer'); ?>
