<?php $this->load->view('whmazadmin/include/header');?>

<link href="<?=base_url()?>resources/assets/css/admin.manage_view.css" rel="stylesheet">

<div class="content content-fluid content-wrapper">
	<div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

		<!-- Page Header -->
		<div class="order-page-header">
			<div class="d-flex justify-content-between align-items-center">
				<div>
					<h3><i class="fa fa-shopping-cart me-2"></i>Create New Order</h3>
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb breadcrumb-style1 mb-0">
							<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
							<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/order/index">Orders</a></li>
							<li class="breadcrumb-item active"><a href="#">New Order</a></li>
						</ol>
					</nav>
				</div>
				<a href="<?=base_url()?>whmazadmin/order/index" class="btn btn-light btn-sm px-4">
					<i class="fa fa-arrow-left me-2"></i>Back to Orders
				</a>
			</div>
		</div>

		<?php if ($this->session->flashdata('alert')) { ?>
			<?= $this->session->flashdata('alert') ?>
		<?php } ?>

		<form method="post" name="entityManageForm" id="entityManageForm" action="<?=base_url()?>whmazadmin/order/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
			<?=csrf_field()?>
			<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

			<!-- Customer & Order Details -->
			<div class="order-card">
				<div class="card-header">
					<div class="header-icon"><i class="fa fa-user"></i></div>
					<h6>Customer & Order Details</h6>
				</div>
				<div class="card-body">
					<div class="row g-4">
						<div class="col-md-6">
							<label class="form-label"><i class="fa fa-building"></i>Customer / Company</label>
							<?php echo form_dropdown('company_id', $companies, !empty($detail['company_id']) ? $detail['company_id'] : '', 'class="form-select select2" id="company_id"'); ?>
							<?php echo form_error('company_id', '<div class="text-danger small mt-1">', '</div>'); ?>
						</div>
						<div class="col-md-3">
							<label class="form-label"><i class="fa fa-dollar-sign"></i>Currency</label>
							<?php echo form_dropdown('currency_id', $currencies, !empty($detail['currency_id']) ? $detail['currency_id'] : '', 'class="form-select select2" id="currency_id"'); ?>
							<?php echo form_error('currency_id', '<div class="text-danger small mt-1">', '</div>'); ?>
						</div>
						<div class="col-md-3">
							<label class="form-label"><i class="fa fa-sync"></i>Billing Cycle</label>
							<?php echo form_dropdown('billing_cycle_id', $billing_cycles, !empty($detail['billing_cycle_id']) ? $detail['billing_cycle_id'] : '', 'class="form-select select2" id="billing_cycle_id"'); ?>
							<?php echo form_error('billing_cycle_id', '<div class="text-danger small mt-1">', '</div>'); ?>
						</div>

					</div>
				</div>
			</div>

			<!-- Hosting Package -->
			<div class="order-card">
				<div class="card-header">
					<div class="header-icon"><i class="fa fa-server"></i></div>
					<h6>Hosting Package</h6>
				</div>
				<div class="card-body">
					<div class="row g-4">
						<div class="col-md-2">
							<label class="form-label"><i class="fa fa-cube"></i>Module</label>
							<?php echo form_dropdown('module_id', $modules, !empty($detail['module_id']) ? $detail['module_id'] : '', 'class="form-select select2" id="module_id"'); ?>
							<?php echo form_error('module_id', '<div class="text-danger small mt-1">', '</div>'); ?>
						</div>
						<div class="col-md-3">
							<label class="form-label"><i class="fa fa-hdd"></i>Server</label>
							<?php echo form_dropdown('server_id', $servers, !empty($detail['server_id']) ? $detail['server_id'] : '', 'class="form-select select2" id="server_id"'); ?>
							<?php echo form_error('server_id', '<div class="text-danger small mt-1">', '</div>'); ?>
						</div>
						<div class="col-md-3">
							<label class="form-label"><i class="fa fa-layer-group"></i>Service Group</label>
							<?php echo form_dropdown('product_service_group_id', $service_groups, !empty($detail['product_service_group_id']) ? $detail['product_service_group_id'] : '', 'class="form-select select2" id="product_service_group_id"'); ?>
							<?php echo form_error('product_service_group_id', '<div class="text-danger small mt-1">', '</div>'); ?>
						</div>
						<div class="col-md-4">
							<label class="form-label"><i class="fa fa-box"></i>Package / Product</label>
							<select class="form-select select2" id="product_service_id" name="product_service_id">
								<option value="">-- Select Package --</option>
							</select>
							<?php echo form_error('product_service_id', '<div class="text-danger small mt-1">', '</div>'); ?>
						</div>
						<input type="hidden" id="package_amount" name="package_amount" value="0" />
					</div>
				</div>
			</div>

			<!-- Domain Section -->
			<div class="order-card">
				<div class="card-header">
					<div class="header-icon"><i class="fa fa-globe"></i></div>
					<h6>Domain Configuration</h6>
				</div>
				<div class="card-body">
					<div class="row g-4">
						<div class="col-md-12">
							<label class="form-label"><i class="fa fa-tasks"></i>Domain Action</label>
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

						<div class="col-md-5">
							<label class="form-label"><i class="fa fa-globe"></i>Domain Name</label>
							<div class="domain-input-group">
								<div class="input-group">
									<span class="input-group-text">www.</span>
									<input type="text" class="form-control" placeholder="example.com" id="domain" name="domain">
								</div>
							</div>
						</div>

						<div class="col-md-2">
							<label class="form-label"><i class="fa fa-calendar"></i>Registration Period</label>
							<?php echo form_dropdown('reg_period', getDomainRegistrationYears(), !empty($detail['reg_period']) ? $detail['reg_period'] : '', 'class="form-select select2" id="reg_period"'); ?>
							<?php echo form_error('reg_period', '<div class="text-danger small mt-1">', '</div>'); ?>
						</div>

						<div class="col-md-5">
							<label class="form-label"><i class="fa fa-server"></i>Domain Registrar</label>
							<?php echo form_dropdown('dom_register_id', $dom_registers, !empty($detail['dom_register_id']) ? $detail['dom_register_id'] : '', 'class="form-select select2" id="dom_register_id"'); ?>
							<?php echo form_error('dom_register_id', '<div class="text-danger small mt-1">', '</div>'); ?>
						</div>
						<input type="hidden" id="domain_amount" name="domain_amount" value="0" />

						<div class="col-md-12" id="epp_code_section" style="display: none;">
							<label class="form-label"><i class="fa fa-key"></i>EPP / Authorization Code</label>
							<input type="text" class="form-control" placeholder="Enter EPP/Auth code for transfer" id="epp_code" name="epp_code" />
							<small class="text-muted">Required for domain transfers</small>
						</div>
					</div>
				</div>
			</div>

			<!-- Order Summary -->
			<div class="order-card">
				<div class="card-header">
					<div class="header-icon"><i class="fa fa-calculator"></i></div>
					<h6>Order Summary</h6>
				</div>
				<div class="card-body">
					<div class="row g-4">
						<div class="col-md-4">
							<div class="coupon-section">
								<label class="form-label"><i class="fa fa-ticket-alt"></i>Apply Coupon Code</label>
								<div class="input-group">
									<input type="text" class="form-control" placeholder="Enter coupon code" id="coupon_code" name="coupon_code">
									<button class="btn btn-outline-success" type="button" id="btn_apply_coupon">
										<i class="fa fa-check me-1"></i>Apply
									</button>
								</div>
							</div>

							<div class="form-group mt-4">
								<label class="form-label"><i class="fa fa-credit-card"></i>Payment Gateway</label>
								<?php echo form_dropdown('payment_gateway_id', $payment_gateways, !empty($detail['payment_gateway_id']) ? $detail['payment_gateway_id'] : '', 'class="form-select select2" id="payment_gateway_id"'); ?>
								<?php echo form_error('payment_gateway_id', '<div class="text-danger small mt-1">', '</div>'); ?>
							</div>

						</div>

						<div class="col-md-5">
							<div class="summary-section">
								<div class="summary-row">
									<span class="summary-label"><i class="fa fa-server me-2 text-primary"></i>Hosting Package</span>
									<div class="summary-value">
										<input type="text" class="form-control form-control-sm" id="display_package_amount" readonly value="0.00">
									</div>
								</div>
								<div class="summary-row">
									<span class="summary-label"><i class="fa fa-globe me-2 text-danger"></i>Domain</span>
									<div class="summary-value">
										<input type="text" class="form-control form-control-sm" id="display_domain_amount" readonly value="0.00">
									</div>
								</div>
								<div class="summary-row">
									<span class="summary-label"><i class="fa fa-receipt me-2 text-info"></i>Sub Total</span>
									<div class="summary-value">
										<input type="text" class="form-control form-control-sm" id="sub_total" name="sub_total" readonly value="0.00">
									</div>
								</div>
								<div class="summary-row">
									<span class="summary-label"><i class="fa fa-ticket-alt me-2 text-success"></i>Coupon Discount</span>
									<div class="summary-value">
										<input type="text" class="form-control form-control-sm" id="coupon_amount" name="coupon_amount" readonly value="0.00">
									</div>
								</div>
								<div class="summary-row">
									<span class="summary-label"><i class="fa fa-percent me-2 text-warning"></i>Manual Discount</span>
									<div class="summary-value">
										<input type="text" class="form-control form-control-sm" id="discount_amount" name="discount_amount" placeholder="0.00">
									</div>
								</div>
								<div class="summary-row total-row">
									<span class="summary-label"><i class="fa fa-coins me-2"></i>Total Amount</span>
									<div class="summary-value">
										<input type="text" class="form-control form-control-sm fw-bold" id="total_amount" name="total_amount" readonly value="0.00" style="font-size: 1.2rem; background: rgba(255,255,255,0.2); color: #fff; border: none;">
									</div>
								</div>
							</div>
						</div>

						<div class="col-md-3">

							<div class="form-group mt-4">
								<label class="form-label"><i class="fa fa-calendar-alt"></i>Registration Date</label>
								<input type="date" class="form-control" id="reg_date" name="reg_date" value="<?= !empty($detail['reg_date']) ? $detail['reg_date'] : date('Y-m-d') ?>">
								<?php echo form_error('reg_date', '<div class="text-danger small mt-1">', '</div>'); ?>
							</div>

							<div class="form-group mt-4">
								<label class="form-label"><i class="fa fa-cog"></i>Options</label>
								<div class="d-flex gap-2">
									<div class="checkbox-toggle">
										<input name="has_notification" type="checkbox" id="has_notification" <?= !empty($detail['has_notification']) && $detail['has_notification'] == 1 ? 'checked' : ''?>>
										<label for="has_notification"><i class="fa fa-bell me-1"></i>Notify</label>
									</div>
									<div class="checkbox-toggle">
										<input name="need_api_call" type="checkbox" id="need_api_call" <?= !empty($detail['need_api_call']) && $detail['need_api_call'] == 1 ? 'checked' : ''?>>
										<label for="need_api_call"><i class="fa fa-code me-1"></i>API</label>
									</div>
								</div>
							</div>


							<div class="d-grid mt-4">
								<button type="submit" class="btn btn-create-order btn-lg">
									<i class="fa fa-check-circle me-2"></i>Create Order
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>

		</form>

	</div>
</div>

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
