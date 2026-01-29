<?php $this->load->view('templates/customer/header');?>

<div class="content content-fixed content-wrapper" ng-app="ServicesApp">
	<div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0" ng-controller="ServiceProductCtrl">

		<div class="row">
			<div class="col-md-3 col-sm-12">
				<?php $this->load->view('templates/customer/cart_category_nav');?>
				<?php $this->load->view('templates/customer/cart_action_nav');?>
			</div>


			<div class="col-md-9 col-sm-12">
				<h1 style="display: none;"><?=$query_title?></h1>
				<h3>Choose your plan</h3>
				<hr class="mg-5" />
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb breadcrumb-style1 mg-b-0">
						<li class="breadcrumb-item">Find best hosting from us</li>
					</ol>
				</nav>

				<div class="row justify-content-center mg-t-15">

					<?php foreach ($items as $row){?>
						<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 d-flex flex-column">
							<h4 class="mg-t-15 mg-b-10"><?=$row['product_name']?></h4>
							<p class="tx-color-03 mg-b-10"><?=$row['product_desc']?></p>
							<div class="tx-rubik tx-normal mg-b-10 mg-t-auto text-center">
								<?php
									$billings = is_array($row['billing']) ? $row['billing'] : json_decode($row['billing'], true);
									echo "<select name='pay_term' class='form-select pay_term' id='pay_term_".htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8')."' >";
									foreach ($billings as $key => $val ){?>
										<option value="<?=htmlspecialchars($val["service_pricing_id"], ENT_QUOTES, 'UTF-8')?>"><?=htmlspecialchars(format($val["price"],2), ENT_QUOTES, 'UTF-8').' '.htmlspecialchars($val["currency"], ENT_QUOTES, 'UTF-8').'/'.htmlspecialchars($val["cycle_name"], ENT_QUOTES, 'UTF-8')?></option>
									<?php }
									echo "</select>";
								?>
							</div>
							<button class="btn btn-primary btn-block" ng-click="addToService(<?=$row['id']?>, '<?=$row['product_name']?>')">Choose Plan</button>
						</div>
					<?php }?>

					<h4 class="text-center">
						<?= empty($items) ? "No package found. Click on other categories" : "" ?>
					</h4>

				</div>

			</div>
		</div>

		<div class="modal fade bd-example-modal-lg" id="hostingDomainModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel">Domain Information</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
							</button>
					</div>
					<div class="modal-body">
						<form>
							<div class="form-group">
								<label for="recipient-name" class="col-form-label">Domain name with extension</label>
								<input type="text" class="form-control" id="hosting_domain" placeholder="tongbari.com" ng-model="hosting_domain" />
							</div>
							<div class="form-group">
								<div class="btn-group btn-group-toggle" data-bs-toggle="buttons">
									<label class="btn btn-info active">
										<input type="radio" name="domain_type" id="domain_type1" value="0" ng-model="hosting_domain_type" autocomplete="off" checked> Update DNS
									</label>
									<label class="btn btn-info">
										<input type="radio" name="domain_type" id="domain_type2" value="1" ng-model="hosting_domain_type" autocomplete="off"> Register
									</label>
									<label class="btn btn-info">
										<input type="radio" name="domain_type" id="domain_type3" value="2" ng-model="hosting_domain_type" autocomplete="off"> Transfer
									</label>
								</div>
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
						<button type="button" class="btn btn-primary" ng-click="addToCartApiCall()">Proceed</button>
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
<script src="<?=base_url()?>resources/angular/app/services_controller.js?v=1.0.1"></script>
<script>
	<?php $alert_success = $this->session->flashdata('alert_success'); ?>
	<?php if ($alert_success) { ?>
		toastSuccess(<?= json_encode(htmlspecialchars($alert_success, ENT_QUOTES, 'UTF-8')) ?>);
	<?php } ?>
	<?php $alert_error = $this->session->flashdata('alert_error'); ?>
	<?php if ($alert_error) { ?>
		toastError(<?= json_encode(htmlspecialchars($alert_error, ENT_QUOTES, 'UTF-8')) ?>);
	<?php } ?>
</script>
<?php $this->load->view('templates/customer/footer');?>
