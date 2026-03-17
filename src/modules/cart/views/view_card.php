<?php $this->load->view('templates/customer/header'); ?>

<div class="content content-fixed content-wrapper" ng-app="ServicesApp">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0" ng-controller="ServiceCheckoutCtrl">

        <!-- Page Header -->
        <div class="page-header-card page-header-cart mg-b-25">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h3 class="mg-b-0"><i class="fa fa-shopping-cart mg-r-10"></i>Shopping Cart</h3>
                    <nav aria-label="breadcrumb" class="mg-t-8">
                        <ol class="breadcrumb breadcrumb-style1 mg-b-0">
                            <li class="breadcrumb-item"><a href="<?=base_url()?>">Home</a></li>
                            <li class="breadcrumb-item"><a href="<?=base_url()?>cart/services">Services</a></li>
                            <li class="breadcrumb-item active" aria-current="page">View Cart</li>
                        </ol>
                    </nav>
                </div>
                <div class="header-actions mg-t-10 mg-md-t-0">
                    <a href="<?=base_url()?>cart/services/0" class="btn btn-light">
                        <i class="fa fa-plus mg-r-5"></i> Add More Services
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
                <?php $ITEM_SL = 0; $hasItems = !empty($cart_list); ?>

                <?php if ($hasItems): ?>
                <?php
                    // Count total items including children
                    $totalItems = 0;
                    foreach ($cart_list as $item) {
                        $totalItems++;
                        if (!empty($item['children'])) {
                            $totalItems += count($item['children']);
                        }
                    }
                ?>
                <!-- Cart Items Card -->
                <div class="card cart-items-card mg-b-20">
                    <div class="card-header cart-card-header">
                        <h5 class="mg-b-0"><i class="fa fa-list mg-r-10"></i>Cart Items</h5>
                        <span class="cart-item-count"><?= $totalItems ?> item(s)</span>
                    </div>
                    <div class="card-body pd-0">
                        <div class="table-responsive">
                            <table class="table cart-table mg-b-0">
                                <thead>
                                    <tr>
                                        <th class="text-center" class="col-w-60">#</th>
                                        <th>Item Details</th>
                                        <th class="text-center" class="col-w-120">Type</th>
                                        <th class="text-right" class="col-w-180">Amount</th>
                                        <th class="text-center" class="col-w-80">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart_list as $dp): ?>
                                    <!-- Parent Item -->
                                    <tr class="cart-item-row cart-item-parent">
                                        <td class="text-center">
                                            <span class="item-number"><?= ++$ITEM_SL; ?></span>
                                        </td>
                                        <td>
                                            <div class="item-details">
                                                <span class="item-name"><?= htmlspecialchars($dp['note'], ENT_QUOTES, 'UTF-8') ?></span>
                                                <?php if (!empty($dp['hosting_domain'])): ?>
                                                <br><small class="text-muted"><i class="fa fa-globe mg-r-5"></i><?= htmlspecialchars($dp['hosting_domain'], ENT_QUOTES, 'UTF-8') ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($dp['item_type'] == 1): ?>
                                                <span class="badge badge-type badge-domain"><i class="fa fa-globe mg-r-5"></i>Domain</span>
                                                <?php if (!empty($dp['domain_action'])): ?>
                                                <br><small class="text-muted"><?= ucfirst($dp['domain_action']) ?></small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge badge-type badge-hosting"><i class="fa fa-server mg-r-5"></i>Hosting</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right">
                                            <div class="item-price">
                                                <span class="price-amount subtotal-item"><?= htmlspecialchars($dp['sub_total'], ENT_QUOTES, 'UTF-8') ?></span>
                                                <span class="price-currency"><?= htmlspecialchars($dp['currency_code'], ENT_QUOTES, 'UTF-8') ?></span>
                                                <span class="price-cycle">/ <?= htmlspecialchars($dp['billing_cycle'], ENT_QUOTES, 'UTF-8') ?></span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-remove" ng-click="clearCartData('cart/delete/<?= $dp['id'] ?>')" title="Remove Item<?= !empty($dp['children']) ? ' (includes linked items)' : '' ?>">
                                                <i class="fa fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Child Items (linked domain or hosting) -->
                                    <?php if (!empty($dp['children'])): ?>
                                        <?php foreach ($dp['children'] as $child): ?>
                                        <tr class="cart-item-row cart-item-child">
                                            <td class="text-center">
                                                <span class="item-number-child"><i class="fa fa-level-up-alt fa-rotate-90 text-muted"></i></span>
                                            </td>
                                            <td>
                                                <div class="item-details item-details-child">
                                                    <span class="item-name"><?= htmlspecialchars($child['note'], ENT_QUOTES, 'UTF-8') ?></span>
                                                    <?php if (!empty($child['hosting_domain'])): ?>
                                                    <br><small class="text-muted"><i class="fa fa-link mg-r-5"></i><?= htmlspecialchars($child['hosting_domain'], ENT_QUOTES, 'UTF-8') ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($child['item_type'] == 1): ?>
                                                    <span class="badge badge-type badge-domain badge-sm"><i class="fa fa-globe mg-r-5"></i>Domain</span>
                                                    <?php if (!empty($child['domain_action'])): ?>
                                                    <br><small class="text-muted"><?= ucfirst($child['domain_action']) ?></small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge badge-type badge-hosting badge-sm"><i class="fa fa-server mg-r-5"></i>Hosting</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-right">
                                                <div class="item-price">
                                                    <span class="price-amount subtotal-item"><?= htmlspecialchars($child['sub_total'], ENT_QUOTES, 'UTF-8') ?></span>
                                                    <span class="price-currency"><?= htmlspecialchars($child['currency_code'], ENT_QUOTES, 'UTF-8') ?></span>
                                                    <span class="price-cycle">/ <?= htmlspecialchars($child['billing_cycle'], ENT_QUOTES, 'UTF-8') ?></span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted" title="Linked to parent item"><i class="fa fa-link"></i></span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer cart-card-footer">
                        <div class="cart-total">
                            <span class="total-label">Subtotal:</span>
                            <span class="total-amount" id="subtotal">0.00</span>
                        </div>
                        <div class="cart-total cart-discount-row" id="discountRow" style="display:none;">
                            <span class="total-label text-success"><i class="fa fa-tag mg-r-5"></i>Discount (<span id="discountCode"></span>):</span>
                            <span class="total-amount text-success" id="discountAmount">-0.00</span>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2" ng-click="removePromo()" title="Remove promo code"><i class="fa fa-times"></i></button>
                        </div>
                        <hr class="my-2" style="border-color:#e3e8f1;">
                        <div class="cart-total">
                            <span class="total-label fw-bold">Order Total:</span>
                            <span class="total-amount fw-bold" id="total">0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Promo Code Card -->
                <div class="card cart-payment-card mg-b-20">
                    <div class="card-header cart-card-header" style="background: linear-gradient(135deg, #43a047 0%, #2e7d32 100%);">
                        <h5 class="mg-b-0"><i class="fa fa-tags mg-r-10"></i>Promo Code</h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-7 mg-b-10">
                                <label class="payment-label"><i class="fa fa-tag mg-r-5"></i>Have a promo code?</label>
                                <div class="input-group">
                                    <input type="text" class="form-control text-uppercase" ng-model="promo_code" placeholder="Enter promo code" id="promoCodeInput" />
                                    <button class="btn btn-success" type="button" ng-click="applyPromo()" ng-disabled="promoLoading">
                                        <span ng-hide="promoLoading"><i class="fa fa-check mg-r-5"></i>Apply</span>
                                        <span ng-show="promoLoading"><i class="fa fa-spinner fa-spin"></i></span>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-5 mg-b-10">
                                <div id="promoMessage"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Options Card -->
                <div class="card cart-payment-card mg-b-20">
                    <div class="card-header cart-card-header cart-card-header-info">
                        <h5 class="mg-b-0"><i class="fa fa-credit-card mg-r-10"></i>Payment Options</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mg-b-15">
                                <label class="payment-label"><i class="fa fa-wallet mg-r-5"></i>Payment Gateway</label>
                                <select class="form-select payment-select fontawesome" ng-model="payment_gateway">
                                    <option value="0">-- Select Payment Method --</option>
                                    <?php foreach ($payment_gateway_list as $item): ?>
                                        <option value="<?= $item['id'] ?>">&#x<?= $item['icon_fa_unicode'] ?> <?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mg-b-15">
                                <label class="payment-label"><i class="fa fa-comment-alt mg-r-5"></i>Special Instructions (Optional)</label>
                                <textarea rows="3" class="form-control instructions-textarea" ng-model="instructions" placeholder="Enter any special instructions or notes for your order..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="cart-actions">
                    <button class="btn btn-danger btn-empty-cart" ng-click="clearCartData('cart/delete_all')">
                        <i class="fa fa-trash-alt mg-r-5"></i> Empty Cart
                    </button>
                    <button class="btn btn-success btn-checkout" ng-click="btnCartCheckout()">
                        <i class="fa fa-lock mg-r-5"></i> Proceed to Checkout
                    </button>
                </div>

                <?php else: ?>
                <!-- Empty Cart State -->
                <div class="empty-cart-card">
                    <div class="empty-cart-icon">
                        <i class="fa fa-shopping-cart"></i>
                    </div>
                    <h4>Your Cart is Empty</h4>
                    <p>Looks like you haven't added any items to your cart yet.</p>
                    <a href="<?=base_url()?>cart/services/0" class="btn btn-primary btn-browse">
                        <i class="fa fa-server mg-r-5"></i> Browse Services
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- container -->
</div><!-- content -->

<?php $this->load->view('templates/customer/footer_script'); ?>

<script type="text/javascript">
    var app = angular.module('ServicesApp', ['ngDialog', 'ngToast', 'ngMaterial', 'ngMessages', 'ngSanitize', 'ngAnimate']);

    // Calculate total on page load (handles hierarchical cart items)
    var cartSubTotal = 0;
    var promoDiscountAmount = 0;

    $(function () {
        $("span.subtotal-item").each(function (index, value) {
            var currentRow = parseFloat($(this).text()) || 0;
            cartSubTotal += currentRow;
        });
        document.getElementById('subtotal').innerHTML = cartSubTotal.toFixed(2);
        document.getElementById('total').innerHTML = cartSubTotal.toFixed(2);
    });

    function updateCartTotal() {
        var finalTotal = cartSubTotal - promoDiscountAmount;
        if (finalTotal < 0) finalTotal = 0;
        document.getElementById('total').innerHTML = finalTotal.toFixed(2);
    }

    function showPromoDiscount(code, amount) {
        promoDiscountAmount = amount;
        document.getElementById('discountCode').innerHTML = code;
        document.getElementById('discountAmount').innerHTML = '-' + amount.toFixed(2);
        document.getElementById('discountRow').style.display = 'flex';
        updateCartTotal();
    }

    function hidePromoDiscount() {
        promoDiscountAmount = 0;
        document.getElementById('discountRow').style.display = 'none';
        updateCartTotal();
    }
</script>
<script src="<?=base_url()?>resources/angular/app/app.directives.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/app/app.services.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/app/services_controller.js?v=1.0.0"></script>
<?php $this->load->view('templates/customer/footer'); ?>
