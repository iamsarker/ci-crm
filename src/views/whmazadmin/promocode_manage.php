<?php $this->load->view('whmazadmin/include/header');?>
<link rel="stylesheet" href="<?=base_url()?>resources/assets/css/admin.manage_view.css">

<div class="content content-fluid content-wrapper">
	<div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

		<!-- Page Header -->
		<div class="row mt-2">
			<div class="col-12">
				<div class="company-page-header">
					<div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
						<div>
							<h3><i class="fa fa-tags"></i> <?= !empty($detail['code']) ? htmlspecialchars($detail['code']) : 'New Promo Code' ?></h3>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/promocode/index">Promo Codes</a></li>
									<li class="breadcrumb-item active"><a href="#">Manage</a></li>
								</ol>
							</nav>
						</div>
						<a href="<?=base_url()?>whmazadmin/promocode/index" class="btn btn-back">
							<i class="fa fa-arrow-left"></i> Back to List
						</a>
					</div>
				</div>
			</div>
		</div>

		<!-- Form Section -->
		<div class="row mt-2">
			<div class="col-12">
				<div class="manage-form-card">
					<form method="post" name="entityManageForm" id="entityManageForm" class="company-form" action="<?=base_url()?>whmazadmin/promocode/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
						<?=csrf_field()?>
						<input name="id" type="hidden" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

						<!-- Basic Info Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-info-circle"></i> Basic Information
							</div>
							<div class="row">
								<div class="col-md-4">
									<div class="form-group">
										<label class="form-label" for="code"><i class="fa fa-tag"></i> Promo Code</label>
										<div class="input-group">
											<input name="code" type="text" class="form-control text-uppercase" id="code" placeholder="e.g. SAVE20" value="<?= htmlspecialchars($detail['code'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required />
											<button type="button" class="btn btn-outline-secondary" id="btnGenerateCode" title="Generate random code"><i class="fa fa-random"></i></button>
										</div>
										<?php echo form_error('code', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-8">
									<div class="form-group">
										<label class="form-label" for="description"><i class="fa fa-align-left"></i> Description (Optional)</label>
										<input name="description" type="text" class="form-control" id="description" placeholder="Internal description for this promo code" value="<?= htmlspecialchars($detail['description'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
									</div>
								</div>
							</div>
						</div>

						<!-- Discount Settings Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-percentage"></i> Discount Settings
							</div>
							<div class="row">
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="discount_type"><i class="fa fa-sliders-h"></i> Discount Type</label>
										<select name="discount_type" id="discount_type" class="form-select" required>
											<option value="fixed" <?= (!empty($detail['discount_type']) && $detail['discount_type'] == 'fixed') ? 'selected' : '' ?>>Fixed Amount</option>
											<option value="percentage" <?= (!empty($detail['discount_type']) && $detail['discount_type'] == 'percentage') ? 'selected' : '' ?>>Percentage</option>
										</select>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="discount_value"><i class="fa fa-dollar-sign"></i> Discount Value</label>
										<input name="discount_value" type="number" step="0.01" min="0.01" class="form-control" id="discount_value" placeholder="0.00" value="<?= htmlspecialchars($detail['discount_value'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required />
										<?php echo form_error('discount_value', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-3" id="currencyGroup">
									<div class="form-group">
										<label class="form-label" for="currency_id"><i class="fa fa-coins"></i> Currency</label>
										<select name="currency_id" id="currency_id" class="form-select">
											<option value="">-- Any Currency --</option>
											<?php foreach ($currencies as $cur): ?>
												<option value="<?= $cur['id'] ?>" <?= (!empty($detail['currency_id']) && $detail['currency_id'] == $cur['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cur['currency_code'] . ' - ' . $cur['currency_name'], ENT_QUOTES, 'UTF-8') ?></option>
											<?php endforeach; ?>
										</select>
									</div>
								</div>
								<div class="col-md-3" id="maxDiscountGroup" style="display:none;">
									<div class="form-group">
										<label class="form-label" for="max_discount_amount"><i class="fa fa-hand-holding-usd"></i> Max Discount Cap</label>
										<input name="max_discount_amount" type="number" step="0.01" min="0" class="form-control" id="max_discount_amount" placeholder="0 = no cap" value="<?= htmlspecialchars($detail['max_discount_amount'] ?? '0', ENT_QUOTES, 'UTF-8') ?>" />
									</div>
								</div>
							</div>
							<div class="row mt-2">
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="min_order_amount"><i class="fa fa-shopping-basket"></i> Min Order Amount</label>
										<input name="min_order_amount" type="number" step="0.01" min="0" class="form-control" id="min_order_amount" placeholder="0 = no minimum" value="<?= htmlspecialchars($detail['min_order_amount'] ?? '0', ENT_QUOTES, 'UTF-8') ?>" />
									</div>
								</div>
							</div>
						</div>

						<!-- Validity Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-calendar-alt"></i> Validity Period
							</div>
							<div class="row">
								<div class="col-md-3">
									<div class="form-group">
										<div class="custom-checkbox-toggle mt-4">
											<input name="is_lifetime" type="checkbox" id="is_lifetime" value="1" <?= (!empty($detail['is_lifetime']) && $detail['is_lifetime'] == 1) ? 'checked' : '' ?> />
											<label for="is_lifetime"><i class="fa fa-infinity me-2"></i> Lifetime (No Expiry)</label>
										</div>
									</div>
								</div>
								<div class="col-md-3" id="startDateGroup">
									<div class="form-group">
										<label class="form-label" for="start_date"><i class="fa fa-calendar-plus"></i> Start Date</label>
										<input name="start_date" type="date" class="form-control" id="start_date" value="<?= htmlspecialchars($detail['start_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
									</div>
								</div>
								<div class="col-md-3" id="endDateGroup">
									<div class="form-group">
										<label class="form-label" for="end_date"><i class="fa fa-calendar-minus"></i> End Date</label>
										<input name="end_date" type="date" class="form-control" id="end_date" value="<?= htmlspecialchars($detail['end_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
									</div>
								</div>
							</div>
						</div>

						<!-- Usage Limits Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-tachometer-alt"></i> Usage Limits
							</div>
							<div class="row">
								<div class="col-md-4">
									<div class="form-group">
										<label class="form-label" for="max_uses"><i class="fa fa-users"></i> Max Total Uses</label>
										<input name="max_uses" type="number" min="0" class="form-control" id="max_uses" placeholder="0 = unlimited" value="<?= htmlspecialchars($detail['max_uses'] ?? '0', ENT_QUOTES, 'UTF-8') ?>" />
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="form-label" for="max_uses_per_customer"><i class="fa fa-user"></i> Max Uses Per Customer</label>
										<input name="max_uses_per_customer" type="number" min="0" class="form-control" id="max_uses_per_customer" placeholder="0 = unlimited" value="<?= htmlspecialchars($detail['max_uses_per_customer'] ?? '0', ENT_QUOTES, 'UTF-8') ?>" />
									</div>
								</div>
							</div>
						</div>

						<!-- Targeting Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-bullseye"></i> Targeting
							</div>
							<div class="row">
								<div class="col-md-4">
									<div class="form-group">
										<label class="form-label" for="applies_to"><i class="fa fa-filter"></i> Applies To</label>
										<select name="applies_to" id="applies_to" class="form-select">
											<option value="all" <?= (empty($detail['applies_to']) || $detail['applies_to'] == 'all') ? 'selected' : '' ?>>All Orders</option>
											<option value="products" <?= (!empty($detail['applies_to']) && $detail['applies_to'] == 'products') ? 'selected' : '' ?>>Specific Products</option>
											<option value="customers" <?= (!empty($detail['applies_to']) && $detail['applies_to'] == 'customers') ? 'selected' : '' ?>>Specific Customers</option>
										</select>
									</div>
								</div>
							</div>

							<!-- Product Selection (shown when applies_to = products) -->
							<div class="row mt-3" id="productSelectionGroup" style="display:none;">
								<div class="col-md-8">
									<div class="form-group">
										<label class="form-label" for="product_ids"><i class="fa fa-box"></i> Select Products</label>
										<select name="product_ids[]" id="product_ids" class="form-select select2" multiple>
											<?php foreach ($products as $prod): ?>
												<option value="<?= $prod['id'] ?>" <?= in_array($prod['id'], $product_mappings) ? 'selected' : '' ?>><?= htmlspecialchars(($prod['type_name'] ? $prod['type_name'] . ' - ' : '') . $prod['product_name'], ENT_QUOTES, 'UTF-8') ?></option>
											<?php endforeach; ?>
										</select>
									</div>
								</div>
							</div>

							<!-- Customer Selection (shown when applies_to = customers) -->
							<div class="row mt-3" id="customerSelectionGroup" style="display:none;">
								<div class="col-md-8">
									<div class="form-group">
										<label class="form-label" for="company_ids"><i class="fa fa-building"></i> Select Customers</label>
										<select name="company_ids[]" id="company_ids" class="form-select select2" multiple>
											<?php foreach ($companies as $comp): ?>
												<option value="<?= $comp['id'] ?>" <?= in_array($comp['id'], $customer_mappings) ? 'selected' : '' ?>><?= htmlspecialchars($comp['company_name'], ENT_QUOTES, 'UTF-8') ?></option>
											<?php endforeach; ?>
										</select>
									</div>
								</div>
							</div>
						</div>

						<!-- Status -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-toggle-on"></i> Status
							</div>
							<div class="custom-checkbox-toggle">
								<input name="is_active" type="checkbox" id="is_active" value="1" <?= (empty($detail['id']) || (!empty($detail['is_active']) && $detail['is_active'] == 1)) ? 'checked' : '' ?> />
								<label for="is_active"><i class="fa fa-check-circle me-2"></i> Active</label>
							</div>
						</div>

						<!-- Usage Stats (only for existing records) -->
						<?php if (!empty($detail['id'])): ?>
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-chart-bar"></i> Usage Statistics
							</div>
							<div class="row">
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label">Total Used</label>
										<input type="text" class="form-control" value="<?= htmlspecialchars($detail['total_used'] ?? '0', ENT_QUOTES, 'UTF-8') ?>" readonly />
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label">Created On</label>
										<input type="text" class="form-control" value="<?= htmlspecialchars($detail['inserted_on'] ?? '-', ENT_QUOTES, 'UTF-8') ?>" readonly />
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label">Last Updated</label>
										<input type="text" class="form-control" value="<?= htmlspecialchars($detail['updated_on'] ?? '-', ENT_QUOTES, 'UTF-8') ?>" readonly />
									</div>
								</div>
							</div>
						</div>
						<?php endif; ?>

						<!-- Submit Button -->
						<div class="form-group mt-4">
							<button type="submit" class="btn btn-save-company">
								<i class="fa fa-check-circle"></i> Save Promo Code
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>

	</div><!-- container -->
</div><!-- content -->

<?php $this->load->view('whmazadmin/include/footer_script');?>

<script>
$(function(){
	'use strict'

	// Initialize Select2
	$('#product_ids').select2({ placeholder: 'Select products...', width: '100%' });
	$('#company_ids').select2({ placeholder: 'Select customers...', width: '100%' });

	// Toggle discount type dependent fields
	function updateDiscountTypeFields() {
		var type = $('#discount_type').val();
		if (type === 'fixed') {
			$('#currencyGroup').show();
			$('#maxDiscountGroup').hide();
		} else {
			$('#currencyGroup').hide();
			$('#maxDiscountGroup').show();
		}
	}
	$('#discount_type').on('change', updateDiscountTypeFields);
	updateDiscountTypeFields();

	// Toggle lifetime / date fields
	function updateLifetimeFields() {
		var isLifetime = $('#is_lifetime').is(':checked');
		if (isLifetime) {
			$('#startDateGroup, #endDateGroup').hide();
		} else {
			$('#startDateGroup, #endDateGroup').show();
		}
	}
	$('#is_lifetime').on('change', updateLifetimeFields);
	updateLifetimeFields();

	// Toggle applies_to dependent fields
	function updateAppliesTo() {
		var val = $('#applies_to').val();
		$('#productSelectionGroup').toggle(val === 'products');
		$('#customerSelectionGroup').toggle(val === 'customers');
	}
	$('#applies_to').on('change', updateAppliesTo);
	updateAppliesTo();

	// Generate random code
	$('#btnGenerateCode').on('click', function() {
		var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		var code = '';
		for (var i = 0; i < 8; i++) {
			code += chars.charAt(Math.floor(Math.random() * chars.length));
		}
		$('#code').val(code);
	});

	// Force uppercase on code input
	$('#code').on('input', function() {
		this.value = this.value.toUpperCase().replace(/[^A-Z0-9_-]/g, '');
	});
});
</script>

<?php $this->load->view('whmazadmin/include/footer');?>
