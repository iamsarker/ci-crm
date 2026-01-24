<?php $this->load->view('whmazadmin/include/header');?>

	 <div class="content content-fluid content-wrapper">
      <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

        <div class="row mt-5">
			<div class="col-md-12 col-sm-12">
				<h3 class="d-flex justify-content-between"><span>Orders</span> <a href="<?=base_url()?>whmazadmin/order/index" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i>&nbsp;Back</a></h3>
				<hr class="mg-5" />
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb breadcrumb-style1 mg-b-0">
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/order/index">Orders</a></li>
						<li class="breadcrumb-item active"><a href="#">Manage order</a></li>
					</ol>
				</nav>
			  <?php if ($this->session->flashdata('alert')) { ?>
				<?= $this->session->flashdata('alert') ?>
			  <?php } ?>

			</div>

			<div class="col-md-12 col-sm-12 mt-5">
				<form method="post" name="entityManageForm" id="entityManageForm" action="<?=base_url()?>whmazadmin/order/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
					<?=csrf_field()?>
					<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

					<div class="row ">
						<div class="col-md-12 mt-3">
							<div class="card">
								<div class="card-header bg-info">
									<h5 class="text-white mb-0"><i class="fa fa-user"></i>&nbsp;Customer/Order detail</h5>
								</div>

								<div class="card-body row mt-2">

									<div class="col-md-4 col-sm-12">
										<div class="form-group">
											<label for="company_id">Customer/Company</label>
											<?php echo form_dropdown('company_id', $companies,!empty($detail['company_id']) ? $detail['company_id'] : '','class="form-select select2" id="company_id"'); ?>
											<?php echo form_error('company_id', '<div class="error">', '</div>'); ?>
										</div>
									</div>

									<div class="col-md-2 col-sm-12">
										<div class="form-group">
											<label for="currency_id">Currency</label>
											<?php echo form_dropdown('currency_id', $currencies,!empty($detail['currency_id']) ? $detail['currency_id'] : '','class="form-select select2" id="currency_id"'); ?>
											<?php echo form_error('currency_id', '<div class="error">', '</div>'); ?>
										</div>
									</div>

									<div class="col-md-2 col-sm-12">
										<div class="form-group">
											<label for="billing_cycle_id">Billing cycle</label>
											<?php echo form_dropdown('billing_cycle_id', $billing_cycles,!empty($detail['billing_cycle_id']) ? $detail['billing_cycle_id'] : '','class="form-select select2" id="billing_cycle_id"'); ?>
											<?php echo form_error('billing_cycle_id', '<div class="error">', '</div>'); ?>
										</div>
									</div>

									<div class="col-md-2 col-sm-12">
										<div class="form-check mt-4">
											<input name="has_notification" type="checkbox" class="form-check-input" id="has_notification" <?= !empty($detail['has_notification']) && $detail['has_notification'] == 1 ? 'checked=\"checked\"' : ''?>"/>
											<label for="has_notification" class="form-check-label mt-2px"> Send Notification?</label>
										</div>
									</div>

									<div class="col-md-2 col-sm-12">
										<div class="form-check mt-4">
											<input name="need_api_call" type="checkbox" class="form-check-input" id="need_api_call" <?= !empty($detail['need_api_call']) && $detail['need_api_call'] == 1 ? 'checked=\"checked\"' : ''?>"/>
											<label for="need_api_call" class="form-check-label mt-2px"> Execute Command/API?</label>
										</div>
									</div>
								</div>
							</div>

						</div>

						<div class="col-md-12 mt-3">
							<div class="card">
								<div class="card-header bg-info">
									<h5 class="text-white mb-0"><i class="fa fa-server"></i>&nbsp;Select hosting package</h5>
								</div>
								<div class="card-body row mt-2">

									<div class="col-md-2 col-sm-12">
										<div class="form-group">
											<label for="module_id">Module</label>
											<?php echo form_dropdown('module_id', $modules,!empty($detail['module_id']) ? $detail['module_id'] : '','class="form-select select2" id="module_id"'); ?>
											<?php echo form_error('module_id', '<div class="error">', '</div>'); ?>
										</div>
									</div>

									<div class="col-md-3 col-sm-12">
										<div class="form-group">
											<label for="server_id">Server</label>
											<?php echo form_dropdown('server_id', $servers,!empty($detail['server_id']) ? $detail['server_id'] : '','class="form-select select2" id="server_id"'); ?>
											<?php echo form_error('server_id', '<div class="error">', '</div>'); ?>
										</div>
									</div>

									<div class="col-md-2 col-sm-12">
										<div class="form-group">
											<label for="product_service_group_id">Service group</label>
											<?php echo form_dropdown('product_service_group_id', $service_groups,!empty($detail['product_service_group_id']) ? $detail['product_service_group_id'] : '','class="form-select select2" id="product_service_group_id"'); ?>
											<?php echo form_error('product_service_group_id', '<div class="error">', '</div>'); ?>
										</div>
									</div>

									<div class="col-md-3 col-sm-12">
										<div class="form-group">
											<label for="product_service_id">Package/Product service</label>
											<select class="form-select select2" id="product_service_id" name="product_service_id">
												<option value="">-- Select One --</option>
											</select>
											<?php echo form_error('product_service_id', '<div class="error">', '</div>'); ?>
										</div>
									</div>

									<div class="col-md-2 col-sm-12 ">
										<div class="form-group">
											<label for="package_amount" class="fw-bold">Price/Amount</label>
											<input type="text" class="form-control disabled fw-bold" placeholder="Price/Amount" id="package_amount" name="package_amount"/>
											<?php echo form_error('package_amount', '<div class="error">', '</div>'); ?>
										</div>
									</div>

								</div>
							</div>
						</div>

						<div class="col-md-12 mt-3">
							<div class="card">
								<div class="card-header bg-info">
									<h5 class="text-white mb-0"><i class="fa fa-globe"></i>&nbsp;Tag a domain</h5>
								</div>
								<div class="card-body row mt-2">

									<div class="col-md-3 col-sm-12 pt-4">

										<div class="form-check form-check-inline me-0">
											<input type="radio" id="radioRegister" name="order_type" value="1" class="custom-control-input order_type">
											<label class="custom-control-label" for="radioRegister">Register</label>
										</div>

										<div class="form-check form-check-inline me-0">
											<input type="radio" id="radioTransfer" name="order_type" value="2" class="custom-control-input order_type">
											<label class="custom-control-label" for="radioTransfer">Transfer</label>
										</div>

										<div class="form-check form-check-inline me-0">
											<input type="radio" id="radioNothing" name="order_type" value="3" class="custom-control-input order_type" checked>
											<label class="custom-control-label" for="radioNothing">Nothing</label>
										</div>

									</div>

									<div class="col-md-6 col-sm-12 pt-1">
										<div class="input-group mt-3 mb-3">
											<span class="input-group-text" id="basic-addon1">www.</span>
											<input type="text" class="form-control" placeholder="example.com" id="domain" name="domain" aria-label="domain_name" aria-describedby="basic-addon1">
										</div>
									</div>

									<div class="col-md-3 col-sm-12">
										<div class="form-group">
											<label for="reg_period">Register year</label>
											<?php echo form_dropdown('reg_period', getDomainRegistrationYears(),!empty($detail['reg_period']) ? $detail['reg_period'] : '','class="form-select select2" id="reg_period"'); ?>
											<?php echo form_error('reg_period', '<div class="error">', '</div>'); ?>
										</div>
									</div>

									<div class="col-md-3 col-sm-12">
										<div class="form-group">
											<label for="dom_register_id">Domain register</label>
											<?php echo form_dropdown('dom_register_id', $dom_registers,!empty($detail['dom_register_id']) ? $detail['dom_register_id'] : '','class="form-select select2" id="dom_register_id"'); ?>
											<?php echo form_error('dom_register_id', '<div class="error">', '</div>'); ?>
										</div>
									</div>


									<div class="col-md-3 col-sm-12 ">
										<div class="form-group">
											<label for="epp_code">EPP/Secret code</label>
											<input type="text" class="form-control" placeholder="Secret code" id="epp_code" name="epp_code" />
											<?php echo form_error('epp_code', '<div class="error">', '</div>'); ?>
										</div>
									</div>


									<div class="col-md-2 col-sm-12 ">
										<div class="form-group">
											<label for="domain_amount" class="fw-bold">Price/Amount</label>
											<input type="text" class="form-control disabled fw-bold" placeholder="Price/Amount" id="domain_amount" name="domain_amount" />
											<?php echo form_error('domain_amount', '<div class="error">', '</div>'); ?>
										</div>
									</div>


								</div>
							</div>
						</div>
					</div>



					<div class="card mt-3">
						<div class="card-header bg-warning-light">
							<h5 class="mb-0"><i class="fa fa-shopping-cart"></i>&nbsp;Order summary</h5>
						</div>
						<div class="card-body mt-2">

							<div class="row">
								<div class="col-md-3 col-sm-12 ">
									<div class="input-group">
										<span class="input-group-text" id="basic-addon3">Sub Total</span>
										<input type="text" class="form-control disabled" placeholder="0.0" id="sub_total" name="sub_total" aria-label="sub_total" aria-describedby="basic-addon3">
									</div>
								</div>

								<div class="col-md-3 col-sm-12 ">
									<div class="input-group">
										<input type="text" class="form-control" placeholder="coupon code" id="coupon_code" name="coupon_code" aria-label="coupon_code" aria-describedby="basic-addon4">
										<button class="btn btn-outline-success" type="button"><i class="fa fa-tag"></i> Apply coupon</button>
									</div>
								</div>

								<div class="col-md-3 col-sm-12 ">
									<div class="input-group">
										<span class="input-group-text" id="basic-addon5">Coupon Amount</span>
										<input type="text" class="form-control disabled" placeholder="Coupon Amount" id="coupon_amount" name="coupon_amount" aria-label="coupon_amount" aria-describedby="basic-addon5">
									</div>
								</div>

								<div class="col-md-3 col-sm-12 ">
									<div class="input-group">
										<span class="input-group-text" id="basic-addon6">Discount Amount</span>
										<input type="text" class="form-control text-end" placeholder="Discount Amount" id="discount_amount" name="discount_amount" aria-label="discount_amount" aria-describedby="basic-addon6">
									</div>
								</div>
							</div>

							<div class="row mt-3">
								<div class="col-md-3 col-sm-12 ">
									<div class="input-group">
										<span class="input-group-text" id="basic-addon7">Total Amount</span>
										<input type="text" class="form-control text-end" placeholder="Total Amount" id="total_amount" name="total_amount" aria-label="total_amount" aria-describedby="basic-addon7">
									</div>
								</div>

								<div class="col-md-3 col-sm-12">
									<div class="input-group">
										<span class="input-group-text" id="basic-addon7">Payment gateway</span>
										<?php echo form_dropdown('payment_gateway_id', $payment_gateways,!empty($detail['payment_gateway_id']) ? $detail['payment_gateway_id'] : '','class="form-select" id="payment_gateway_id"'); ?>
										<?php echo form_error('payment_gateway_id', '<div class="error">', '</div>'); ?>
									</div>
								</div>

								<div class="col-md-3 col-sm-12">&nbsp;</div>

								<div class="col-md-3 col-sm-12 text-end">
									<div class="form-group">
										<button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-check-circle"></i>&nbsp;Create order</button>
									</div>
								</div>
							</div>

						</div>
					</div>

				</form>
			</div>
      </div>
		
    </div><!-- container -->
  </div><!-- content -->

<?php $this->load->view('whmazadmin/include/footer_script');?>

<script>
$(function(){
	'use strict'

	// Show flash messages as toast
	<?php if ($this->session->flashdata('alert_success')) { ?>
		toastSuccess('<?= addslashes($this->session->flashdata('alert_success')) ?>');
	<?php } ?>
	<?php if ($this->session->flashdata('alert_error')) { ?>
		toastError('<?= addslashes($this->session->flashdata('alert_error')) ?>');
	<?php } ?>
});
</script>

<script>
	$(document).ready(function (){
		$("input#epp_code").attr("disabled", "disabled");

		$("input.order_type").on("change", function (){
			if( $(this).val() == 2 ){
				$("input#epp_code").removeAttr("disabled");
			} else {
				$("input#epp_code").attr("disabled", "disabled");
			}
		});

		$("select#currency_id").on("change", function (){
			loadHostingPrice();
			loadDomainPrice();
		});
		$("select#reg_period").on("change", function (){
			loadDomainPrice();
		});
		$("select#billing_cycle_id").on("change", function (){
			loadHostingPrice();
		});
		$("select#product_service_id").on("change", function (){
			loadHostingPrice();
		});


		$("select#module_id").on("change", function (){
			loadHostingPackage();
		});
		$("select#server_id").on("change", function (){
			loadHostingPackage();
		});
		$("select#product_service_group_id").on("change", function (){
			loadHostingPackage();
		});

		// calculate total amount
		$("input#discount_amount").on("keyup", function(){
			calculateTotalAmount();
		});

	});

	function loadHostingPackage(){
		let module_id = $("select#module_id").val();
		let server_id = $("select#server_id").val();
		let service_group_id = $("select#product_service_group_id").val();

		if( Number(module_id) > 0 && Number(server_id) > 0 && Number(service_group_id) > 0 ){

			const data = {
				"module_id" : module_id,
				"server_id" : server_id,
				"service_group_id" : service_group_id
			};

			$("select#product_service_id").empty();

			$.ajax("<?=base_url()?>whmazadmin/package/filter_api", {
				method: "POST",
				dataType: 'json',
				headers : {
					'Content-Type': "application/json"
				},
				data: JSON.stringify(data),
				success: function (response) {
					if( response && response.code == 200 ){
						let option = "<option value=''>-- Select One --</option>";
						for (let item of response.data) {
							option += "<option value='"+item.id+"'>"+item.product_name+"</option>";
						}

						$("select#product_service_id").html(option).select2();

					} else{
						toastWarning("No package found. Try again");
					}
				},
				error: function (err) {
					console.log(err);
				}
			});
		}

	}

	function loadHostingPrice(){
		let currency_id = $("select#currency_id").val();
		let billing_cycle_id = $("select#billing_cycle_id").val();
		let product_service_id = $("select#product_service_id").val();

		if( Number(currency_id) > 0 && Number(billing_cycle_id) > 0 && Number(product_service_id) > 0 ){
			const data = {
				"currency_id" : currency_id,
				"billing_cycle_id" : billing_cycle_id,
				"product_service_id" : product_service_id
			};
			$.ajax("<?=base_url()?>whmazadmin/package/prices", {
				method: "POST",
				dataType: 'json',
				headers : {
					'Content-Type': "application/json"
				},
				data: JSON.stringify(data),
				success: function (response) {
					if( response && response.code == 200 && response.data ){
						$("input#package_amount").val(response.data.price);
					} else{
						$("input#package_amount").val("0.0");
						toastWarning("No price found. Try again");
					}
					calculateTotalAmount();
				},
				error: function (err) {
					$("input#package_amount").val("0.0");
					calculateTotalAmount();
					console.log(err);
				}
			});
		}
	}

	function loadDomainPrice(){
		let currency_id = $("select#currency_id").val();
		let reg_period = $("select#reg_period").val();
		let domain = $("input#domain").val();

		if( Number(currency_id) > 0 && Number(reg_period) > 0 && domain.trim() != "" ){
			const data = {
				"currency_id" : currency_id,
				"reg_period" : reg_period,
				"domain" : domain
			};

			$.ajax("<?=base_url()?>whmazadmin/domain_pricing/prices", {
				method: "POST",
				dataType: 'json',
				headers : {
					'Content-Type': "application/json"
				},
				data: JSON.stringify(data),
				success: function (response) {
					if( response && response.code == 200 && response.data ){
						$("input#domain_amount").val(response.data.price);
					} else{
						$("input#domain_amount").val("0.0");
						toastWarning("No price found. Try again");
					}
					calculateTotalAmount();
				},
				error: function (err) {
					$("input#domain_amount").val("0.0");
					calculateTotalAmount();
					console.log(err);
				}
			});
		}
	}


	function calculateTotalAmount(){
		let packagePrice = Number($("input#package_amount").val());
		let domainPrice = Number($("input#domain_amount").val());
		let subTotal = packagePrice + domainPrice;

		$("input#sub_total").val(subTotal.toFixed(2));

		let couponAmount = Number($("input#coupon_amount").val());
		let discountAmount = Number($("input#discount_amount").val());

		let totalDiscount = couponAmount + discountAmount;
		let totalAmount = subTotal - totalDiscount;
		$("input#total_amount").val(totalAmount < 0 ? 0 : totalAmount.toFixed(2));
	}

</script>
<?php $this->load->view('whmazadmin/include/footer');?>
