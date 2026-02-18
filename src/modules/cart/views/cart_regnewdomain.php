<?php $this->load->view('templates/customer/header');?>
<?php if (!empty($captcha_site_key)) { ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
	var RECAPTCHA_SITE_KEY = '<?= htmlspecialchars($captcha_site_key, ENT_QUOTES, 'UTF-8') ?>';
</script>
<?php } else { ?>
<script>
	var RECAPTCHA_SITE_KEY = '';
</script>
<?php } ?>

<div class="content content-fixed content-wrapper" ng-app="ServicesApp">
	<div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0" ng-controller="ServiceDomainCtrl">

		<!-- Page Header -->
		<div class="page-header-card page-header-cart mg-b-25">
			<div class="d-flex justify-content-between align-items-center flex-wrap">
				<div>
					<h3 class="mg-b-0"><i class="fa fa-globe mg-r-10"></i>Register Domain</h3>
					<nav aria-label="breadcrumb" class="mg-t-8">
						<ol class="breadcrumb breadcrumb-style1 mg-b-0">
							<li class="breadcrumb-item"><a href="<?=base_url()?>">Home</a></li>
							<li class="breadcrumb-item active" aria-current="page">Register Domain</li>
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
						<i class="fa fa-search"></i>
					</div>
					<div class="intro-content">
						<h4>Find Your Perfect Domain</h4>
						<p>Search for your ideal domain name. We offer competitive prices and instant registration.</p>
					</div>
				</div>

				<!-- Domain Search Card -->
				<div class="domain-search-card mg-b-25">
					<div class="domain-search-header">
						<i class="fa fa-globe"></i>
						<h5>Domain Search</h5>
					</div>
					<div class="domain-search-body">
						<div class="row align-items-end">
							<?php if (!empty($captcha_site_key)) { ?>
							<div class="col-lg-4 col-md-12 mg-b-15 mg-lg-b-0">
								<div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($captcha_site_key, ENT_QUOTES, 'UTF-8') ?>"></div>
							</div>
							<div class="col-lg-8 col-md-12">
							<?php } else { ?>
							<div class="col-12">
							<?php } ?>
								<label class="domain-search-label">Enter your desired domain name</label>
								<div class="domain-search-input-group" ng-init="loadDomainToVar()">
									<span class="domain-prefix">www.</span>
									<input type="text" class="form-control" placeholder="example.com" id="search_domain_name" value="<?= empty($domkeyword) ? '' : htmlspecialchars($domkeyword, ENT_QUOTES, 'UTF-8');?>">
									<button class="btn btn-search" type="button" ng-click="btnSearchDomain()">
										<i class="fa fa-search mg-r-5"></i> Search
									</button>
								</div>
							</div>
						</div>

						<!-- Search Result -->
						<div class="domain-result mg-t-20" ng-if="data.status == 1">
							<div class="domain-available">
								<div class="result-icon">
									<i class="fa fa-check-circle"></i>
								</div>
								<div class="result-content">
									<h4>Congratulations!</h4>
									<p><strong>{{data.info[0].name}}</strong> is available!</p>
									<div class="result-price">
										<span class="price">{{data.info[0].price}} <?=getCurrencyCode()?></span>
										<button type="button" class="btn btn-primary btn-add-domain" ng-click="addToCartRegisterDomain(data.info[0].domPriceId, data.info[0].name)">
											<i class="fa fa-cart-plus mg-r-5"></i> Add to Cart
										</button>
									</div>
								</div>
							</div>
						</div>

						<div class="domain-result mg-t-20" ng-if="data.status == 0">
							<div class="domain-unavailable">
								<div class="result-icon">
									<i class="fa fa-times-circle"></i>
								</div>
								<div class="result-content">
									<h4>Domain Unavailable</h4>
									<p><strong>{{search_domain_name}}</strong> is not available. Try another name or check suggestions below.</p>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Domain Extensions & Prices -->
				<div class="domain-pricing-card mg-b-25" ng-show="showPricing">
					<div class="domain-pricing-header">
						<i class="fa fa-tags"></i>
						<h5>Available Extensions & Prices</h5>
					</div>
					<div class="domain-pricing-body">
						<div class="table-responsive">
							<table class="table domain-pricing-table">
								<thead>
									<tr>
										<th>Extension</th>
										<th>Register</th>
										<th>Transfer</th>
										<th>Renewal</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($dom_prices as $dp){?>
									<tr>
										<td><span class="domain-ext"><?=htmlspecialchars($dp['extension'], ENT_QUOTES, 'UTF-8')?></span></td>
										<td><span class="domain-price"><?=htmlspecialchars($dp['price'], ENT_QUOTES, 'UTF-8')?> <small><?=htmlspecialchars(getCurrencyCode(), ENT_QUOTES, 'UTF-8')?></small></span></td>
										<td><span class="domain-price"><?=htmlspecialchars($dp['transfer'], ENT_QUOTES, 'UTF-8')?> <small><?=htmlspecialchars(getCurrencyCode(), ENT_QUOTES, 'UTF-8')?></small></span></td>
										<td><span class="domain-price"><?=htmlspecialchars($dp['renewal'], ENT_QUOTES, 'UTF-8')?> <small><?=htmlspecialchars(getCurrencyCode(), ENT_QUOTES, 'UTF-8')?></small></span></td>
									</tr>
									<?php } ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>

				<!-- Domain Suggestions -->
				<div class="domain-suggestions-card" ng-show="showSuggestions && suggestionList.length > 0">
					<div class="domain-suggestions-header">
						<i class="fa fa-lightbulb"></i>
						<h5>Domain Suggestions</h5>
					</div>
					<div class="domain-suggestions-body">
						<div class="table-responsive" style="max-height: 350px; overflow: auto;">
							<table class="table domain-suggestions-table">
								<thead>
									<tr>
										<th class="text-left">Domain</th>
										<th class="text-right">Price</th>
										<th class="text-center">Action</th>
									</tr>
								</thead>
								<tbody>
									<tr ng-repeat="obj in suggestionList track by $index">
										<td><span class="suggestion-domain">{{obj.name}}</span></td>
										<td class="text-right"><span class="suggestion-price">{{obj.price}}</span></td>
										<td class="text-center">
											<button type="button" class="btn btn-sm btn-outline-primary" ng-click="addToCartRegisterDomain(obj.domPriceId, obj.name)">
												<i class="fa fa-cart-plus mg-r-5"></i> Add
											</button>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>

			</div>
		</div>

		<!-- Hosting Selection Modal (Flow-2: Domain → Hosting) -->
		<div class="modal fade" id="hostingSelectionModal" tabindex="-1" role="dialog" aria-labelledby="hostingModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered modal-lg">
				<div class="modal-content domain-modal-content">
					<div class="modal-header domain-modal-header">
						<div class="modal-header-icon">
							<i class="fa fa-server"></i>
						</div>
						<div class="modal-header-text">
							<h5 class="modal-title" id="hostingModalLabel">Add Hosting Service</h5>
							<p class="modal-subtitle">Would you like to add hosting for <strong>{{added_domain_name}}</strong>?</p>
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

<?php $this->load->view('templates/customer/footer');?>
