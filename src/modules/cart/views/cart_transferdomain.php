<?php $this->load->view('templates/customer/header');?>

<div class="content content-fixed content-wrapper" ng-app="ServicesApp">
	<div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0" ng-controller="ServiceDomainTransferCtrl">

		<!-- Page Header -->
		<div class="page-header-card page-header-cart mg-b-25">
			<div class="d-flex justify-content-between align-items-center flex-wrap">
				<div>
					<h3 class="mg-b-0"><i class="fa fa-exchange-alt mg-r-10"></i>Transfer Domain</h3>
					<nav aria-label="breadcrumb" class="mg-t-8">
						<ol class="breadcrumb breadcrumb-style1 mg-b-0">
							<li class="breadcrumb-item"><a href="<?=base_url()?>">Home</a></li>
							<li class="breadcrumb-item active" aria-current="page">Transfer Domain</li>
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
						<i class="fa fa-exchange-alt"></i>
					</div>
					<div class="intro-content">
						<h4>Transfer Your Domain to Us</h4>
						<p>Move your domain from another registrar. You'll need your EPP/Authorization code from your current registrar.</p>
					</div>
				</div>

				<!-- Domain Transfer Card -->
				<div class="domain-search-card mg-b-25">
					<div class="domain-search-header">
						<i class="fa fa-exchange-alt"></i>
						<h5>Domain Transfer</h5>
					</div>
					<div class="domain-search-body">
						<!-- Step 1: Enter Domain -->
						<div class="transfer-step">
							<div class="step-number">1</div>
							<div class="step-content">
								<label class="domain-search-label">Enter the domain you want to transfer</label>
								<div class="domain-search-input-group">
									<span class="domain-prefix">www.</span>
									<input type="text" class="form-control" placeholder="example.com"
										   id="transfer_domain_name" ng-model="transfer_domain"
										   ng-change="onDomainChange()" ng-blur="lookupDomainPrice()">
								</div>
							</div>
						</div>

						<!-- Step 2: Enter EPP Code -->
						<div class="transfer-step mg-t-20">
							<div class="step-number">2</div>
							<div class="step-content">
								<label class="domain-search-label">Enter EPP/Authorization Code</label>
								<div class="input-group">
									<span class="input-group-text"><i class="fa fa-key"></i></span>
									<input type="text" class="form-control" placeholder="Enter your EPP/Auth code"
										   id="epp_code" ng-model="epp_code">
								</div>
								<small class="text-muted mg-t-5 d-block">
									<i class="fa fa-info-circle mg-r-5"></i>
									You can get this code from your current domain registrar.
								</small>
							</div>
						</div>

						<!-- Transfer Price Info -->
						<div class="transfer-step mg-t-20" ng-show="domain_price_info">
							<div class="step-number">3</div>
							<div class="step-content">
								<div class="transfer-price-box">
									<div class="price-info">
										<span class="price-label">Transfer Price for <strong>{{transfer_domain}}</strong></span>
										<span class="price-value">{{domain_price_info.transfer_price | number:2}} <?=getCurrencyCode()?>/yr</span>
									</div>
									<p class="price-note">
										<i class="fa fa-check-circle text-success mg-r-5"></i>
										Transfer includes 1 year extension to your domain registration.
									</p>
								</div>
							</div>
						</div>

						<!-- Error Message -->
						<div class="alert alert-danger mg-t-20" ng-show="error_message">
							<i class="fa fa-exclamation-circle mg-r-5"></i> {{error_message}}
						</div>

						<!-- Add to Cart Button -->
						<div class="mg-t-25 text-center" ng-show="domain_price_info">
							<button type="button" class="btn btn-primary btn-lg" ng-click="addTransferToCart()"
									ng-disabled="!transfer_domain || !epp_code || loading">
								<span ng-show="!loading">
									<i class="fa fa-cart-plus mg-r-5"></i> Add to Cart
								</span>
								<span ng-show="loading">
									<i class="fa fa-spinner fa-spin mg-r-5"></i> Processing...
								</span>
							</button>
						</div>
					</div>
				</div>

				<!-- Domain Extensions & Prices -->
				<div class="domain-pricing-card mg-b-25">
					<div class="domain-pricing-header">
						<i class="fa fa-tags"></i>
						<h5>Transfer Pricing</h5>
					</div>
					<div class="domain-pricing-body">
						<div class="table-responsive">
							<table class="table domain-pricing-table">
								<thead>
									<tr>
										<th>Extension</th>
										<th>Transfer Price</th>
										<th>Renewal Price</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($dom_prices as $dp){?>
									<tr>
										<td><span class="domain-ext"><?=htmlspecialchars($dp['extension'], ENT_QUOTES, 'UTF-8')?></span></td>
										<td><span class="domain-price"><?=htmlspecialchars($dp['transfer'], ENT_QUOTES, 'UTF-8')?> <small><?=htmlspecialchars(getCurrencyCode(), ENT_QUOTES, 'UTF-8')?></small></span></td>
										<td><span class="domain-price"><?=htmlspecialchars($dp['renewal'], ENT_QUOTES, 'UTF-8')?> <small><?=htmlspecialchars(getCurrencyCode(), ENT_QUOTES, 'UTF-8')?></small></span></td>
									</tr>
									<?php } ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>

				<!-- Transfer Info -->
				<div class="card mg-b-25">
					<div class="card-header bg-light">
						<h6 class="mg-b-0"><i class="fa fa-info-circle mg-r-10"></i>How Domain Transfer Works</h6>
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col-md-6">
								<h6><i class="fa fa-check text-success mg-r-5"></i> Before You Begin</h6>
								<ul class="transfer-checklist">
									<li>Domain must be at least 60 days old</li>
									<li>Domain must be unlocked at current registrar</li>
									<li>Get EPP/Auth code from current registrar</li>
									<li>Ensure admin email is accessible</li>
								</ul>
							</div>
							<div class="col-md-6">
								<h6><i class="fa fa-clock text-primary mg-r-5"></i> Transfer Process</h6>
								<ul class="transfer-checklist">
									<li>Submit transfer request with EPP code</li>
									<li>Approve transfer via email confirmation</li>
									<li>Transfer completes in 5-7 days</li>
									<li>1 year added to registration period</li>
								</ul>
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>

		<!-- Hosting Selection Modal (Flow-2: Domain Transfer → Hosting) -->
		<div class="modal fade" id="hostingSelectionModal" tabindex="-1" role="dialog" aria-labelledby="hostingModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered modal-lg">
				<div class="modal-content domain-modal-content">
					<div class="modal-header domain-modal-header">
						<div class="modal-header-icon">
							<i class="fa fa-server"></i>
						</div>
						<div class="modal-header-text">
							<h5 class="modal-title" id="hostingModalLabel">Add Hosting Service</h5>
							<p class="modal-subtitle">Would you like to add hosting for <strong>{{transfer_domain}}</strong>?</p>
						</div>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body domain-modal-body">
						<!-- Hosting Package Selection -->
						<div class="hosting-selection-section">
							<label class="domain-label mg-b-15">
								<i class="fa fa-cubes mg-r-5"></i> Select Hosting Package (Optional)
							</label>

							<div class="row" ng-if="hosting_packages.length > 0">
								<div class="col-md-6 mg-b-15" ng-repeat="pkg in hosting_packages">
									<div class="hosting-option-card" ng-class="{'selected': selected_hosting.id == pkg.id}" ng-click="selectHostingPackage(pkg)">
										<div class="hosting-option-header">
											<i class="fa fa-check-circle selected-icon" ng-show="selected_hosting.id == pkg.id"></i>
											<h6>{{pkg.product_name}}</h6>
										</div>
										<div class="hosting-option-body">
											<div class="hosting-price">
												<select class="form-select form-select-sm" ng-model="pkg.selected_pricing" ng-options="b.service_pricing_id as (b.price + ' ' + b.currency + ' / ' + b.cycle_name) for b in pkg.billing" ng-click="$event.stopPropagation()">
												</select>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="alert alert-info" ng-if="hosting_packages.length == 0 && !loading_packages">
								<i class="fa fa-info-circle mg-r-5"></i> No hosting packages available. You can continue with domain only.
							</div>

							<div class="text-center pd-20" ng-if="loading_packages">
								<i class="fa fa-spinner fa-spin fa-2x"></i>
								<p class="mg-t-10">Loading hosting packages...</p>
							</div>
						</div>
					</div>
					<div class="modal-footer domain-modal-footer">
						<button type="button" class="btn btn-light" data-bs-dismiss="modal" ng-click="skipHosting()">
							<i class="fa fa-arrow-right mg-r-5"></i> Skip, Domain Only
						</button>
						<button type="button" class="btn btn-primary" ng-click="addHostingToDomain()" ng-disabled="!selected_hosting.id">
							<i class="fa fa-cart-plus mg-r-5"></i> Add Hosting to Cart
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
</script>
<script src="<?=base_url()?>resources/angular/app/app.directives.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/app/app.services.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/app/services_controller.js?v=1.0.0"></script>

<style>
/* Transfer Step Styling */
.transfer-step {
	display: flex;
	align-items: flex-start;
	gap: 15px;
}

.step-number {
	width: 32px;
	height: 32px;
	background: linear-gradient(135deg, #0168fa 0%, #6f42c1 100%);
	color: #fff;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	font-weight: 600;
	font-size: 14px;
	flex-shrink: 0;
}

.step-content {
	flex: 1;
}

.transfer-price-box {
	background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
	border-radius: 10px;
	padding: 20px;
	border: 1px solid #a5d6a7;
}

.transfer-price-box .price-info {
	display: flex;
	justify-content: space-between;
	align-items: center;
	flex-wrap: wrap;
	gap: 10px;
}

.transfer-price-box .price-label {
	font-size: 15px;
	color: #2e7d32;
}

.transfer-price-box .price-value {
	font-size: 24px;
	font-weight: 700;
	color: #1b5e20;
}

.transfer-price-box .price-note {
	margin-top: 10px;
	margin-bottom: 0;
	font-size: 13px;
	color: #388e3c;
}

.transfer-checklist {
	list-style: none;
	padding-left: 0;
	margin-bottom: 0;
}

.transfer-checklist li {
	padding: 8px 0;
	border-bottom: 1px solid #eee;
	font-size: 14px;
}

.transfer-checklist li:last-child {
	border-bottom: none;
}

.transfer-checklist li::before {
	content: "\f00c";
	font-family: "Font Awesome 5 Free";
	font-weight: 900;
	color: #28a745;
	margin-right: 10px;
}
</style>

<?php $this->load->view('templates/customer/footer');?>
