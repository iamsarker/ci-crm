<?php $this->load->view('templates/customer/header');?>

<div class="content content-fixed content-wrapper" ng-app="ServicesApp">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0" ng-controller="ServiceProductCtrl">

        <!-- Page Header -->
        <div class="page-header-card page-header-cart mg-b-25">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h3 class="mg-b-0"><i class="fa fa-server mg-r-10"></i>Choose Your Plan</h3>
                    <nav aria-label="breadcrumb" class="mg-t-8">
                        <ol class="breadcrumb breadcrumb-style1 mg-b-0">
                            <li class="breadcrumb-item"><a href="<?=base_url()?>">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Hosting Services</li>
                        </ol>
                    </nav>
                </div>
                <div class="header-actions mg-t-10 mg-md-t-0">
                    <a href="<?=base_url()?>cart/view" class="btn btn-light">
                        <i class="fa fa-shopping-cart mg-r-5"></i> View Cart
                    </a>
                </div>
            </div>
        </div>

        <h1 style="display: none;"><?=$query_title?></h1>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 col-md-4 mg-b-20">
                <?php $this->load->view('templates/customer/cart_category_nav');?>
                <?php $this->load->view('templates/customer/cart_action_nav');?>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9 col-md-8">
                <!-- Intro Banner -->
                <div class="services-intro-banner mg-b-25">
                    <div class="intro-icon">
                        <i class="fa fa-rocket"></i>
                    </div>
                    <div class="intro-content">
                        <h4>Find the Perfect Hosting Plan</h4>
                        <p>Select a plan that fits your needs. All plans include 24/7 support and 99.9% uptime guarantee.</p>
                    </div>
                </div>

                <?php if (!empty($items)): ?>
                <!-- Plans Grid -->
                <div class="row plans-grid">
                    <?php foreach ($items as $row): ?>
                    <div class="col-lg-4 col-md-6 col-sm-12 mg-b-20">
                        <div class="plan-card">
                            <div class="plan-header">
                                <h4 class="plan-name"><?= htmlspecialchars($row['product_name'], ENT_QUOTES, 'UTF-8') ?></h4>
                            </div>
                            <div class="plan-body">
                                <div class="plan-description">
                                    <?= $row['product_desc'] ?>
                                </div>
                                <div class="plan-pricing">
                                    <?php
                                        $billings = is_array($row['billing']) ? $row['billing'] : json_decode($row['billing'], true);
                                        if (!empty($billings)):
                                            $firstBilling = reset($billings);
                                    ?>
                                    <div class="price-display">
                                        <span class="price-amount" id="price_display_<?= $row['id'] ?>">
                                            <?= htmlspecialchars(format($firstBilling["price"], 2), ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                        <span class="price-currency" id="currency_display_<?= $row['id'] ?>">
                                            <?= htmlspecialchars($firstBilling["currency"], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                        <span class="price-cycle" id="cycle_display_<?= $row['id'] ?>">
                                            / <?= htmlspecialchars($firstBilling["cycle_name"], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="plan-select">
                                    <label class="select-label">Billing Cycle</label>
                                    <select name="pay_term" class="form-select pay_term" id="pay_term_<?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?>" onchange="updatePriceDisplay(this, <?= $row['id'] ?>)">
                                        <?php foreach ($billings as $key => $val): ?>
                                        <option value="<?= htmlspecialchars($val["service_pricing_id"], ENT_QUOTES, 'UTF-8') ?>"
                                                data-price="<?= htmlspecialchars(format($val["price"], 2), ENT_QUOTES, 'UTF-8') ?>"
                                                data-currency="<?= htmlspecialchars($val["currency"], ENT_QUOTES, 'UTF-8') ?>"
                                                data-cycle="<?= htmlspecialchars($val["cycle_name"], ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars(format($val["price"], 2), ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($val["currency"], ENT_QUOTES, 'UTF-8') . ' / ' . htmlspecialchars($val["cycle_name"], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="plan-footer">
                                <button class="btn btn-primary btn-choose-plan" ng-click="addToService(<?= $row['id'] ?>, '<?= htmlspecialchars($row['product_name'], ENT_QUOTES, 'UTF-8') ?>')">
                                    <i class="fa fa-check-circle mg-r-5"></i> Choose Plan
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <!-- Empty State -->
                <div class="empty-state-card">
                    <div class="empty-icon">
                        <i class="fa fa-box-open"></i>
                    </div>
                    <h4>No Packages Found</h4>
                    <p>Please select a different category from the sidebar to view available packages.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Domain Modal - Enhanced for Flow-1 -->
        <div class="modal fade" id="hostingDomainModal" tabindex="-1" role="dialog" aria-labelledby="domainModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content domain-modal-content">
                    <div class="modal-header domain-modal-header">
                        <div class="modal-header-icon">
                            <i class="fa fa-globe"></i>
                        </div>
                        <div class="modal-header-text">
                            <h5 class="modal-title" id="domainModalLabel">Domain Information</h5>
                            <p class="modal-subtitle">Enter the domain name for your hosting service</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body domain-modal-body">
                        <!-- Domain Type Selection -->
                        <div class="domain-type-section mg-b-20">
                            <label class="domain-label">
                                <i class="fa fa-cog mg-r-5"></i> Domain Option
                            </label>
                            <div class="domain-type-options">
                                <label class="domain-type-option" ng-class="{'active': domain_action == 'dns_update'}">
                                    <input type="radio" name="domain_type" value="dns_update" ng-model="domain_action" ng-change="onDomainActionChange()">
                                    <div class="option-content">
                                        <div class="option-icon"><i class="fa fa-exchange-alt"></i></div>
                                        <div class="option-text">
                                            <span class="option-title">Update DNS</span>
                                            <span class="option-desc">I'll point my existing domain</span>
                                        </div>
                                    </div>
                                </label>
                                <label class="domain-type-option" ng-class="{'active': domain_action == 'register'}">
                                    <input type="radio" name="domain_type" value="register" ng-model="domain_action" ng-change="onDomainActionChange()">
                                    <div class="option-content">
                                        <div class="option-icon"><i class="fa fa-plus-circle"></i></div>
                                        <div class="option-text">
                                            <span class="option-title">Register New</span>
                                            <span class="option-desc">Register a new domain</span>
                                        </div>
                                    </div>
                                </label>
                                <label class="domain-type-option" ng-class="{'active': domain_action == 'transfer'}">
                                    <input type="radio" name="domain_type" value="transfer" ng-model="domain_action" ng-change="onDomainActionChange()">
                                    <div class="option-content">
                                        <div class="option-icon"><i class="fa fa-arrow-right"></i></div>
                                        <div class="option-text">
                                            <span class="option-title">Transfer</span>
                                            <span class="option-desc">Transfer from another registrar</span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- DNS Update: Simple domain input -->
                        <div class="domain-input-section" ng-show="domain_action == 'dns_update'">
                            <label for="hosting_domain_dns" class="domain-label">
                                <i class="fa fa-link mg-r-5"></i> Domain Name
                            </label>
                            <div class="domain-input-wrapper">
                                <span class="domain-prefix">www.</span>
                                <input type="text" class="form-control domain-input" id="hosting_domain_dns"
                                       placeholder="example.com" ng-model="hosting_domain" />
                            </div>
                            <small class="domain-hint">Enter your existing domain name (e.g., example.com)</small>
                        </div>

                        <!-- Register: Domain search -->
                        <div class="domain-register-section" ng-show="domain_action == 'register'">
                            <label class="domain-label">
                                <i class="fa fa-search mg-r-5"></i> Search Domain
                            </label>
                            <div class="input-group mg-b-10">
                                <input type="text" class="form-control" id="domain_search_input"
                                       placeholder="Enter domain name to check availability" ng-model="domain_search_keyword"
                                       ng-keypress="$event.keyCode == 13 && searchDomainAvailability()" />
                                <button class="btn btn-primary" type="button" ng-click="searchDomainAvailability()" ng-disabled="domain_searching">
                                    <i class="fa fa-search" ng-hide="domain_searching"></i>
                                    <i class="fa fa-spinner fa-spin" ng-show="domain_searching"></i>
                                    Search
                                </button>
                            </div>

                            <!-- Domain Search Results -->
                            <div class="domain-search-results" ng-show="domain_search_results.length > 0">
                                <div class="result-item" ng-repeat="result in domain_search_results"
                                     ng-class="{'selected': selected_domain.name == result.name}"
                                     ng-click="selectDomain(result)">
                                    <div class="result-domain">
                                        <i class="fa fa-check-circle text-success" ng-show="selected_domain.name == result.name"></i>
                                        <i class="fa fa-globe" ng-hide="selected_domain.name == result.name"></i>
                                        <span>{{result.name}}</span>
                                    </div>
                                    <div class="result-price">
                                        <span class="price">{{result.price | number:2}}</span>
                                        <span class="currency"><?= getCurrencyCode() ?>/yr</span>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-warning mg-t-10" ng-show="domain_search_no_results">
                                <i class="fa fa-exclamation-triangle mg-r-5"></i> Domain not available. Try a different name.
                            </div>
                        </div>

                        <!-- Transfer: Domain + EPP Code -->
                        <div class="domain-transfer-section" ng-show="domain_action == 'transfer'">
                            <div class="row">
                                <div class="col-md-7">
                                    <label for="transfer_domain" class="domain-label">
                                        <i class="fa fa-globe mg-r-5"></i> Domain Name
                                    </label>
                                    <div class="domain-input-wrapper mg-b-15">
                                        <span class="domain-prefix">www.</span>
                                        <input type="text" class="form-control domain-input" id="transfer_domain"
                                               placeholder="example.com" ng-model="hosting_domain" ng-change="onTransferDomainChange()" />
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <label for="epp_code" class="domain-label">
                                        <i class="fa fa-key mg-r-5"></i> EPP/Auth Code
                                    </label>
                                    <input type="text" class="form-control" id="epp_code"
                                           placeholder="Enter EPP code" ng-model="epp_code" />
                                </div>
                            </div>
                            <small class="domain-hint">Get the EPP/Auth code from your current registrar</small>

                            <!-- Transfer Price Display -->
                            <div class="transfer-price-info mg-t-15" ng-show="transfer_price_info">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle mg-r-5"></i>
                                    Transfer price for <strong>{{hosting_domain}}</strong>:
                                    <strong>{{transfer_price_info.price | number:2}} <?= getCurrencyCode() ?>/yr</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer domain-modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="fa fa-times mg-r-5"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-primary" ng-click="addHostingWithDomain()" ng-disabled="!canAddToCart()">
                            <i class="fa fa-cart-plus mg-r-5"></i> Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- container -->
</div><!-- content -->

<?php $this->load->view('templates/customer/footer_script');?>

<script type="text/javascript">
    var app = angular.module('ServicesApp', ['ngDialog', 'ngToast', 'ngMaterial', 'ngMessages', 'ngSanitize', 'ngAnimate']);

    // Update price display when billing cycle changes
    function updatePriceDisplay(select, productId) {
        var selectedOption = select.options[select.selectedIndex];
        var price = selectedOption.getAttribute('data-price');
        var currency = selectedOption.getAttribute('data-currency');
        var cycle = selectedOption.getAttribute('data-cycle');

        document.getElementById('price_display_' + productId).textContent = price;
        document.getElementById('currency_display_' + productId).textContent = currency;
        document.getElementById('cycle_display_' + productId).textContent = '/ ' + cycle;
    }

    // Domain type option selection
    document.addEventListener('DOMContentLoaded', function() {
        var domainOptions = document.querySelectorAll('.domain-type-option input[type="radio"]');
        domainOptions.forEach(function(radio) {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.domain-type-option').forEach(function(opt) {
                    opt.classList.remove('active');
                });
                this.closest('.domain-type-option').classList.add('active');
            });
        });
    });
</script>
<script src="<?=base_url()?>resources/angular/app/app.directives.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/app/app.services.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/app/services_controller.js?v=1.0.1"></script>
<?php $this->load->view('templates/customer/footer');?>
