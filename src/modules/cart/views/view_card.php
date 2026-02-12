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
                    <a href="<?=base_url()?>cart/services" class="btn btn-light">
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
                <!-- Cart Items Card -->
                <div class="card cart-items-card mg-b-20">
                    <div class="card-header cart-card-header">
                        <h5 class="mg-b-0"><i class="fa fa-list mg-r-10"></i>Cart Items</h5>
                        <span class="cart-item-count"><?= count($cart_list) ?> item(s)</span>
                    </div>
                    <div class="card-body pd-0">
                        <div class="table-responsive">
                            <table class="table cart-table mg-b-0">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 60px;">#</th>
                                        <th>Item Details</th>
                                        <th class="text-center" style="width: 120px;">Type</th>
                                        <th class="text-right" style="width: 180px;">Amount</th>
                                        <th class="text-center" style="width: 80px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart_list as $dp): ?>
                                    <tr class="cart-item-row">
                                        <td class="text-center">
                                            <span class="item-number"><?= ++$ITEM_SL; ?></span>
                                        </td>
                                        <td>
                                            <div class="item-details">
                                                <span class="item-name"><?= htmlspecialchars($dp['note'], ENT_QUOTES, 'UTF-8') ?></span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($dp['item_type'] == 1): ?>
                                                <span class="badge badge-type badge-domain"><i class="fa fa-globe mg-r-5"></i>Domain</span>
                                            <?php else: ?>
                                                <span class="badge badge-type badge-hosting"><i class="fa fa-server mg-r-5"></i>Hosting</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right">
                                            <div class="item-price">
                                                <span class="price-amount" id="subtotal"><?= htmlspecialchars($dp['sub_total'], ENT_QUOTES, 'UTF-8') ?></span>
                                                <span class="price-currency"><?= htmlspecialchars($dp['currency_code'], ENT_QUOTES, 'UTF-8') ?></span>
                                                <span class="price-cycle">/ <?= htmlspecialchars($dp['billing_cycle'], ENT_QUOTES, 'UTF-8') ?></span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-remove" ng-click="clearCartData('cart/delete/<?= $dp['id'] ?>')" title="Remove Item">
                                                <i class="fa fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer cart-card-footer">
                        <div class="cart-total">
                            <span class="total-label">Order Total:</span>
                            <span class="total-amount" id="total">0.00</span>
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
                                        <option value="<?= $item->id ?>">&#x<?= $item->icon_fa_unicode ?> <?= htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8') ?></option>
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
                    <a href="<?=base_url()?>cart/services" class="btn btn-primary btn-browse">
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

    // Calculate total on page load
    $(function () {
        var TotalValue = 0;
        $("span#subtotal").each(function (index, value) {
            currentRow = parseFloat($(this).text());
            TotalValue += currentRow;
        });
        document.getElementById('total').innerHTML = TotalValue.toFixed(2);
    });
</script>
<script src="<?=base_url()?>resources/angular/app/app.directives.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/app/app.services.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/app/services_controller.js?v=1.0.0"></script>
<?php $this->load->view('templates/customer/footer'); ?>
