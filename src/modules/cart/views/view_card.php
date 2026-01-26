<?php $this->load->view('templates/customer/header'); ?>

<div class="content content-fixed content-wrapper" ng-app="ServicesApp">
	<div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0" ng-controller="ServiceCheckoutCtrl">

		<div class="row">
			<div class="col-md-3 col-sm-12">
				<?php $this->load->view('templates/customer/cart_category_nav'); ?>
				<?php $this->load->view('templates/customer/cart_action_nav'); ?>
			</div>

			<div class="col-md-9 col-sm-12">
				<h3>View cart</h3>
				<hr class="mg-5"/>
				<div class="row mg-t-15">
					<div class="col-md-12 mb-2">
						<table class="table table-primary table-striped">
							<thead>
							<tr>
								<th class="text-center">SL</th>
								<th>Service Type</th>
								<th>Item</th>
								<th class="text-right">Sub Total</th>
								<th class="text-center">Action</th>
							</tr>
							</thead>
							<tbody>
							<?php $ITEM_SL = 0;foreach ($cart_list as $dp) { ?>
								<tr>
									<td class="text-center"><?= ++$ITEM_SL; ?> </td>
									<td><?php echo ($dp['item_type']==1)? "Domain":"Order/Hosting" ?> </td>
									<td><?= $dp['note'] ?> </td>
									<td class="text-right"> <span id="subtotal"> <?= $dp['sub_total'] ?></span> <?= $dp['currency_code'].'/'.$dp['billing_cycle'] ?> </td>
									<td class="text-center">
										<a ng-click="clearCartData('cart/delete/<?= $dp['id'] ?>')"
										   href="">
											<i class="fa fa-trash" style="color:red"></i>
										</a>
									</td>
								</tr>
							<?php } ?>
							</tbody>
						</table>
						<table class="table table-borderless">
							<tbody>
							<tr>
								<td class="text-right">
									<h4>Total:</h4>
								</td>
								<td class="text-right" style="width: 150px;">
									<h4 id="total">0.0</h4>
								</td>
							</tr>
							</tbody>
						</table>

						<div class="card card-widget card-contacts" style="margin-top:15px;">
							<div class="card-header">
								<h6 class="card-title mg-b-0"><i class="fa fa-money"></i> &nbsp;Choose Payment Type</h6>
								<nav class="nav">

								</nav>
							</div><!-- card-header -->
							<div class="list-group list-group-flush">
								<li class="list-group-item">
									<select class="form-select fontawesome" ng-model="payment_gateway">
										<option value="0"> Select </option>

										<?php
											foreach ($payment_gateway_list as $item){
												echo '<option value="'.$item->id.'"> &#x'.$item->icon_fa_unicode.' '.$item->name.'</option>';
											}
										?>

									</select>
								</li>
								<li class="list-group-item">
									<textarea rows="3" class="form-control" ng-model="instructions" placeholder="Special Instructions"></textarea>
								</li>
							</div>
						</div>
					</div>
					<?php if($ITEM_SL > 0){?>
						<div class="col-md-6">
							<a ng-click="clearCartData('cart/delete_all')" href="" class="btn btn-danger" type="cancel"><i class="fa fa-trash-alt"></i> Empty Shopping Cart</a>
						</div>
						<div class="col-md-6 text-right">
							<button class="btn btn-primary" type="button" ng-click="btnCartCheckout()" ><i class="fa fa-money-bill-alt"></i> Checkout</button>
						</div>
					<?php }?>
				</div>

			</div>
		</div>

	</div><!-- container -->
</div><!-- content -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script type="text/javascript">
	$(function () {
		var TotalValue = 0;

		$("td span#subtotal").each(function (index, value) {
			currentRow = parseFloat($(this).text());
			TotalValue += currentRow
		});

		document.getElementById('total').innerHTML = TotalValue;

	});
</script>

<?php $this->load->view('templates/customer/footer_script'); ?>

<script type="text/javascript">
	var app = angular.module('ServicesApp', ['ngDialog', 'ngToast', 'ngMaterial', 'ngMessages', 'ngSanitize', 'ngAnimate']);
</script>
<script src="<?=base_url()?>resources/angular/app/app.directives.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/app/app.services.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/app/services_controller.js?v=1.0.0"></script>
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
<?php $this->load->view('templates/customer/footer'); ?>
