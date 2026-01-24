<?php $this->load->view('templates/customer/header');?>

	 <div class="content content-fixed content-wrapper" ng-app="ServicesApp">
      <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0" ng-controller="ServiceDomainCtrl">

        <div class="row">
          <div class="col-md-3 col-sm-12">
			<?php $this->load->view('templates/customer/cart_category_nav');?>
			<?php $this->load->view('templates/customer/cart_action_nav');?>
        </div>


        <div class="col-md-9 col-sm-12">
			<h3>Register Domain</h3>
			<hr class="mg-5" />
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb breadcrumb-style1 mg-b-0">
					<li class="breadcrumb-item">Find your expected domain through us</li>
				</ol>
			</nav>
          <?php if ($this->session->flashdata('alert')) { ?>
            <?= $this->session->flashdata('alert') ?>
          <?php } ?>

			<div class="row mg-t-15">
				<div class="col-md-12">
					<div class="domain-search-panel">
						<div class="input-group domain-search-box">
							<input type="text" class="form-control" placeholder="Search your domain name" aria-label="Search your domain name" id="search_domain_name" value="<?=htmlspecialchars($domkeyword, ENT_QUOTES, 'UTF-8');?>" aria-describedby="button-addon2">
							<div class="input-group-append" ng-init="loadDomainToVar()">
								<button class="btn btn-outline-info" type="button" id="button-addon2" ng-click="btnSearchDomain()">Search</button>
							</div>
						</div>
					</div>
				</div>

				<div class="col-md-12">
					<p ng-if="data.status == 1" style="color: #6cb86c; font-size: 1.5em">
						<span>Congratulations! {{data.info.name}} is available!</span><br/>
						<b>{{data.info.price}} <?=getCurrencyCode()?></b>&nbsp;<button type="button" class="btn btn-xs btn-secondary" ng-click="addToCartRegisterDomain(data.info.domPriceId, data.info.name)" >Add to cart</button>
					</p>
					<p ng-if="data.status == 0" style="color: #d8535f; font-size: 1.5em">
						{{search_domain_name}} is unavailable!
					</p>
				</div>
			</div>


			<div class="row mg-t-15">

				<div class="col-md-12" id="avail-ext-price">
					<h2>Available domain Extensions and Prices</h2>
					<table class="table table-primary table-striped">
						<thead>
							<tr>
								<th>Domain</th>
								<th>Register</th>
								<th>Transfer</th>
								<th>Renewal</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($dom_prices as $dp){?>
							<tr>
								<td><?=htmlspecialchars($dp['extension'], ENT_QUOTES, 'UTF-8')?> </td>
								<td><?=htmlspecialchars($dp['price'], ENT_QUOTES, 'UTF-8').' '.htmlspecialchars(getCurrencyCode(), ENT_QUOTES, 'UTF-8')?></td>
								<td><?=htmlspecialchars($dp['transfer'], ENT_QUOTES, 'UTF-8').' '.htmlspecialchars(getCurrencyCode(), ENT_QUOTES, 'UTF-8')?></td>
								<td><?=htmlspecialchars($dp['renewal'], ENT_QUOTES, 'UTF-8').' '.htmlspecialchars(getCurrencyCode(), ENT_QUOTES, 'UTF-8')?></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>

				<div class="col-md-12" id="domain-suggestions" style="max-height: 350px; overflow: auto;">
					<h2>Suggestions</h2>
					<table class="table table-primary table-striped">
						<thead>
						<tr>
							<th class="text-left">Domain</th>
							<th class="text-right">Price</th>
							<th class="text-center">Action</th>
						</tr>
						</thead>
						<tbody>
							<tr ng-repeat="obj in suggestionList track by $index">
								<td>{{obj.name}}</td>
								<td class="text-right">{{obj.price}}</td>
								<td class="text-center"><button type="button" class="btn btn-xs btn-secondary" ng-click="addToCartRegisterDomain(obj.domPriceId, obj.name)" >Add to cart</button></td>
							</tr>

						</tbody>
					</table>
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
