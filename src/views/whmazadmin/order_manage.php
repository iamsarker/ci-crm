<?php $this->load->view('whmazadmin/include/header');?>
<link rel="stylesheet" href="<?=base_url()?>resources/assets/css/admin.manage_view.css">

<div class="content content-fluid content-wrapper">
	<div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

		<!-- Page Header -->
		<div class="row mt-4">
			<div class="col-12">
				<div class="company-page-header">
					<div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
						<div>
							<h3><i class="fa fa-shopping-cart"></i> Create New Order</h3>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/order/index">Orders</a></li>
									<li class="breadcrumb-item active"><a href="#">New Order</a></li>
								</ol>
							</nav>
						</div>
						<a href="<?=base_url()?>whmazadmin/order/index" class="btn btn-back">
							<i class="fa fa-arrow-left"></i> Back to Orders
						</a>
					</div>
				</div>
			</div>
		</div>

		<!-- Form Section -->
		<div class="row mt-4">
			<div class="col-12">
				<div class="manage-form-card">
					<form method="post" name="entityManageForm" id="entityManageForm" class="company-form" action="<?=base_url()?>whmazadmin/order/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
						<?=csrf_field()?>
						<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

						<!-- Customer & Order Details -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-user"></i> Customer & Order Details
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="company_id"><i class="fa fa-building"></i> Customer / Company</label>
										<?php echo form_dropdown('company_id', $companies, !empty($detail['company_id']) ? $detail['company_id'] : '', 'class="form-select select2" id="company_id"'); ?>
										<?php echo form_error('company_id', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="currency_id"><i class="fa fa-dollar-sign"></i> Currency</label>
										<?php echo form_dropdown('currency_id', $currencies, !empty($detail['currency_id']) ? $detail['currency_id'] : '', 'class="form-select select2" id="currency_id"'); ?>
										<?php echo form_error('currency_id', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="billing_cycle_id"><i class="fa fa-sync"></i> Billing Cycle</label>
										<?php echo form_dropdown('billing_cycle_id', $billing_cycles, !empty($detail['billing_cycle_id']) ? $detail['billing_cycle_id'] : '', 'class="form-select select2" id="billing_cycle_id"'); ?>
										<?php echo form_error('billing_cycle_id', '<div class="error">', '</div>'); ?>
									</div>
								</div>
							</div>
						</div>

						<!-- Hosting Package -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-server"></i> Hosting Package
							</div>
							<div class="row">
								<div class="col-md-2">
									<div class="form-group">
										<label class="form-label" for="module_id"><i class="fa fa-cube"></i> Module</label>
										<?php echo form_dropdown('module_id', $modules, !empty($detail['module_id']) ? $detail['module_id'] : '', 'class="form-select select2" id="module_id"'); ?>
										<?php echo form_error('module_id', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="server_id"><i class="fa fa-hdd"></i> Server</label>
										<?php echo form_dropdown('server_id', $servers, !empty($detail['server_id']) ? $detail['server_id'] : '', 'class="form-select select2" id="server_id"'); ?>
										<?php echo form_error('server_id', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="product_service_group_id"><i class="fa fa-layer-group"></i> Service Group</label>
										<?php echo form_dropdown('product_service_group_id', $service_groups, !empty($detail['product_service_group_id']) ? $detail['product_service_group_id'] : '', 'class="form-select select2" id="product_service_group_id"'); ?>
										<?php echo form_error('product_service_group_id', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="form-label" for="product_service_id"><i class="fa fa-box"></i> Package / Product</label>
										<select class="form-select select2" id="product_service_id" name="product_service_id">
											<option value="">-- Select Package --</option>
										</select>
										<?php echo form_error('product_service_id', '<div class="error">', '</div>'); ?>
									</div>
								</div>
							</div>
							<input type="hidden" id="package_amount" name="package_amount" value="0" />
						</div>

						<!-- Domain Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-globe"></i> Domain Configuration
							</div>
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<label class="form-label"><i class="fa fa-tasks"></i> Domain Action</label>
										<div class="d-flex flex-wrap gap-2">
											<label class="form-check-custom" id="label_radioRegister">
												<input type="radio" id="radioRegister" name="order_type" value="1" class="order_type">
												<span><i class="fa fa-plus-circle me-1 text-success"></i>Register New Domain</span>
											</label>
											<label class="form-check-custom" id="label_radioTransfer">
												<input type="radio" id="radioTransfer" name="order_type" value="2" class="order_type">
												<span><i class="fa fa-exchange-alt me-1 text-info"></i>Transfer Domain</span>
											</label>
											<label class="form-check-custom active" id="label_radioNothing">
												<input type="radio" id="radioNothing" name="order_type" value="3" class="order_type" checked>
												<span><i class="fa fa-ban me-1 text-secondary"></i>No Domain</span>
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-5">
									<div class="form-group">
										<label class="form-label" for="domain"><i class="fa fa-globe"></i> Domain Name</label>
										<div class="input-group">
											<span class="input-group-text">www.</span>
											<input type="text" class="form-control" placeholder="example.com" id="domain" name="domain">
										</div>
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label class="form-label" for="reg_period"><i class="fa fa-calendar"></i> Registration Period</label>
										<?php echo form_dropdown('reg_period', getDomainRegistrationYears(), !empty($detail['reg_period']) ? $detail['reg_period'] : '', 'class="form-select select2" id="reg_period"'); ?>
										<?php echo form_error('reg_period', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-5">
									<div class="form-group">
										<label class="form-label" for="dom_register_id"><i class="fa fa-server"></i> Domain Registrar</label>
										<?php echo form_dropdown('dom_register_id', $dom_registers, !empty($detail['dom_register_id']) ? $detail['dom_register_id'] : '', 'class="form-select select2" id="dom_register_id"'); ?>
										<?php echo form_error('dom_register_id', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<input type="hidden" id="domain_amount" name="domain_amount" value="0" />
								<div class="col-md-12" id="epp_code_section" style="display: none;">
									<div class="form-group">
										<label class="form-label" for="epp_code"><i class="fa fa-key"></i> EPP / Authorization Code</label>
										<input type="text" class="form-control" placeholder="Enter EPP/Auth code for transfer" id="epp_code" name="epp_code" />
										<small class="form-text text-muted">Required for domain transfers</small>
									</div>
								</div>
							</div>
						</div>

						<!-- Order Summary -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-calculator"></i> Order Summary
							</div>
							<div class="row">
								<div class="col-md-4">
									<div class="form-group">
										<label class="form-label" for="coupon_code"><i class="fa fa-ticket-alt"></i> Apply Coupon Code</label>
										<div class="input-group">
											<input type="text" class="form-control" placeholder="Enter coupon code" id="coupon_code" name="coupon_code">
											<button class="btn btn-outline-success" type="button" id="btn_apply_coupon">
												<i class="fa fa-check"></i> Apply
											</button>
										</div>
									</div>
									<div class="form-group">
										<label class="form-label" for="payment_gateway_id"><i class="fa fa-credit-card"></i> Payment Gateway</label>
										<?php echo form_dropdown('payment_gateway_id', $payment_gateways, !empty($detail['payment_gateway_id']) ? $detail['payment_gateway_id'] : '', 'class="form-select select2" id="payment_gateway_id"'); ?>
										<?php echo form_error('payment_gateway_id', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-5">
									<div class="order-summary-box">
										<div class="summary-row">
											<span class="summary-label"><i class="fa fa-server text-primary"></i> Hosting Package</span>
											<input type="text" class="form-control form-control-sm summary-input" id="display_package_amount" readonly value="0.00">
										</div>
										<div class="summary-row">
											<span class="summary-label"><i class="fa fa-globe text-danger"></i> Domain</span>
											<input type="text" class="form-control form-control-sm summary-input" id="display_domain_amount" readonly value="0.00">
										</div>
										<div class="summary-row">
											<span class="summary-label"><i class="fa fa-receipt text-info"></i> Sub Total</span>
											<input type="text" class="form-control form-control-sm summary-input" id="sub_total" name="sub_total" readonly value="0.00">
										</div>
										<div class="summary-row">
											<span class="summary-label"><i class="fa fa-ticket-alt text-success"></i> Coupon Discount</span>
											<input type="text" class="form-control form-control-sm summary-input" id="coupon_amount" name="coupon_amount" readonly value="0.00">
										</div>
										<div class="summary-row">
											<span class="summary-label"><i class="fa fa-percent text-warning"></i> Manual Discount</span>
											<input type="text" class="form-control form-control-sm summary-input" id="discount_amount" name="discount_amount" placeholder="0.00">
										</div>
										<div class="summary-row total-row">
											<span class="summary-label"><i class="fa fa-coins"></i> Total Amount</span>
											<input type="text" class="form-control form-control-sm summary-input total-input" id="total_amount" name="total_amount" readonly value="0.00">
										</div>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="reg_date"><i class="fa fa-calendar-alt"></i> Registration Date</label>
										<input type="date" class="form-control" id="reg_date" name="reg_date" value="<?= !empty($detail['reg_date']) ? $detail['reg_date'] : date('Y-m-d') ?>">
										<?php echo form_error('reg_date', '<div class="error">', '</div>'); ?>
									</div>
									<div class="form-group">
										<label class="form-label"><i class="fa fa-cog"></i> Options</label>
										<div class="d-flex gap-3">
											<div class="custom-checkbox-toggle">
												<input name="has_notification" type="checkbox" id="has_notification" <?= !empty($detail['has_notification']) && $detail['has_notification'] == 1 ? 'checked' : ''?>>
												<label for="has_notification"><i class="fa fa-bell"></i> Notify</label>
											</div>
											<div class="custom-checkbox-toggle">
												<input name="need_api_call" type="checkbox" id="need_api_call" <?= !empty($detail['need_api_call']) && $detail['need_api_call'] == 1 ? 'checked' : ''?>>
												<label for="need_api_call"><i class="fa fa-code"></i> API</label>
											</div>
										</div>
									</div>
									<div class="form-group mt-4">
										<button type="submit" class="btn btn-save-company btn-lg w-100">
											<i class="fa fa-check-circle"></i> Create Order
										</button>
									</div>
								</div>
							</div>
						</div>

					</form>
				</div>
			</div>
		</div>

	</div><!-- container -->
</div><!-- content -->

<?php $this->load->view('whmazadmin/include/footer_script');?>

<script>
$(document).ready(function(){
	// Initialize select2 with width option
	$('.select2').select2({
		width: '100%',
		placeholder: function(){
			return $(this).data('placeholder') || '-- Select --';
		}
	});

	// Radio button styling
	$("input.order_type").on("change", function(){
		$(".form-check-custom").removeClass("active");
		$(this).closest(".form-check-custom").addClass("active");

		if($(this).val() == 2){
			$("#epp_code_section").slideDown();
		} else {
			$("#epp_code_section").slideUp();
		}
	});

	// Dropdown change events
	$("select#currency_id").on("change", function(){
		loadHostingPrice();
		loadDomainPrice();
	});

	$("select#reg_period").on("change", function(){
		loadDomainPrice();
	});

	$("select#billing_cycle_id").on("change", function(){
		loadHostingPrice();
	});

	$("select#product_service_id").on("change", function(){
		loadHostingPrice();
	});

	$("select#module_id, select#server_id, select#product_service_group_id").on("change", function(){
		loadHostingPackage();
	});

	// Domain input change
	$("input#domain").on("blur", function(){
		loadDomainPrice();
	});

	// Discount amount change
	$("input#discount_amount").on("keyup", function(){
		calculateTotalAmount();
	});
});

function loadHostingPackage(){
	let module_id = $("select#module_id").val();
	let server_id = $("select#server_id").val();
	let service_group_id = $("select#product_service_group_id").val();

	if(Number(module_id) > 0 && Number(server_id) > 0 && Number(service_group_id) > 0){
		const data = {
			"module_id": module_id,
			"server_id": server_id,
			"service_group_id": service_group_id
		};

		$("select#product_service_id").html('<option value="">Loading...</option>');

		$.ajax("<?=base_url()?>whmazadmin/package/filter_api", {
			method: "POST",
			dataType: 'json',
			headers: {'Content-Type': "application/json"},
			data: JSON.stringify(data),
			success: function(response){
				if(response && response.code == 200){
					let option = "<option value=''>-- Select Package --</option>";
					for(let item of response.data){
						option += "<option value='"+escapeXSS(item.id)+"'>"+escapeXSS(item.product_name)+"</option>";
					}
					$("select#product_service_id").html(option).select2({
						width: '100%',
						placeholder: '-- Select Package --'
					});
				} else {
					$("select#product_service_id").html("<option value=''>-- No packages found --</option>");
					toastWarning("No package found. Try different selection.");
				}
			},
			error: function(err){
				$("select#product_service_id").html("<option value=''>-- Error loading --</option>");
				console.log(err);
			}
		});
	}
}

function loadHostingPrice(){
	let currency_id = $("select#currency_id").val();
	let billing_cycle_id = $("select#billing_cycle_id").val();
	let product_service_id = $("select#product_service_id").val();

	if(Number(currency_id) > 0 && Number(billing_cycle_id) > 0 && Number(product_service_id) > 0){
		const data = {
			"currency_id": currency_id,
			"billing_cycle_id": billing_cycle_id,
			"product_service_id": product_service_id
		};

		$.ajax("<?=base_url()?>whmazadmin/package/prices", {
			method: "POST",
			dataType: 'json',
			headers: {'Content-Type': "application/json"},
			data: JSON.stringify(data),
			success: function(response){
				if(response && response.code == 200 && response.data){
					$("input#package_amount").val(parseFloat(response.data.price).toFixed(2));
					$("input#display_package_amount").val(parseFloat(response.data.price).toFixed(2));
				} else {
					$("input#package_amount").val("0.00");
					$("input#display_package_amount").val("0.00");
					toastWarning("No price found for selected package.");
				}
				calculateTotalAmount();
			},
			error: function(err){
				$("input#package_amount").val("0.00");
				$("input#display_package_amount").val("0.00");
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

	if(Number(currency_id) > 0 && Number(reg_period) > 0 && domain.trim() != ""){
		const data = {
			"currency_id": currency_id,
			"reg_period": reg_period,
			"domain": domain
		};

		$.ajax("<?=base_url()?>whmazadmin/domain_pricing/prices", {
			method: "POST",
			dataType: 'json',
			headers: {'Content-Type': "application/json"},
			data: JSON.stringify(data),
			success: function(response){
				if(response && response.code == 200 && response.data){
					$("input#domain_amount").val(parseFloat(response.data.price).toFixed(2));
					$("input#display_domain_amount").val(parseFloat(response.data.price).toFixed(2));
				} else {
					$("input#domain_amount").val("0.00");
					$("input#display_domain_amount").val("0.00");
					toastWarning("No price found for this domain extension.");
				}
				calculateTotalAmount();
			},
			error: function(err){
				$("input#domain_amount").val("0.00");
				$("input#display_domain_amount").val("0.00");
				calculateTotalAmount();
				console.log(err);
			}
		});
	}
}

function calculateTotalAmount(){
	let packagePrice = parseFloat($("input#package_amount").val()) || 0;
	let domainPrice = parseFloat($("input#domain_amount").val()) || 0;
	let subTotal = packagePrice + domainPrice;

	$("input#sub_total").val(subTotal.toFixed(2));

	let couponAmount = parseFloat($("input#coupon_amount").val()) || 0;
	let discountAmount = parseFloat($("input#discount_amount").val()) || 0;

	let totalDiscount = couponAmount + discountAmount;
	let totalAmount = subTotal - totalDiscount;
	$("input#total_amount").val(totalAmount < 0 ? "0.00" : totalAmount.toFixed(2));
}
</script>

<?php $this->load->view('whmazadmin/include/footer');?>
