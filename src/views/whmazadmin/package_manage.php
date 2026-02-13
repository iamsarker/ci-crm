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
							<h3><i class="fa fa-tags"></i> Package Pricing</h3>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/package/index">Package Pricing</a></li>
									<li class="breadcrumb-item active"><a href="#">Manage</a></li>
								</ol>
							</nav>
						</div>
						<a href="<?=base_url()?>whmazadmin/package/index" class="btn btn-back">
							<i class="fa fa-arrow-left"></i> Back to List
						</a>
					</div>
				</div>
			</div>
		</div>

		<!-- Form Section -->
		<div class="row mt-4">
			<div class="col-12">
				<div class="manage-form-card">
					<form method="post" name="entityManageForm" id="entityManageForm" class="company-form" action="<?=base_url()?>whmazadmin/package/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
						<?=csrf_field()?>
						<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

						<!-- Package Details Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-box"></i> Package Details
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="product_service_id"><i class="fa fa-server"></i> Product Service <span class="text-danger">*</span></label>
										<select name="product_service_id" class="form-control form-select" id="product_service_id">
											<option value="">Select Product Service</option>
											<?php foreach($services as $service){ ?>
												<option value="<?= htmlspecialchars($service['id'] ?? '', ENT_QUOTES, 'UTF-8')?>" <?= (!empty($detail['product_service_id']) && $detail['product_service_id'] == ($service['id'] ?? '')) ? 'selected' : ''?>>
													<?= htmlspecialchars($service['product_name'] ?? '', ENT_QUOTES, 'UTF-8')?>
												</option>
											<?php } ?>
										</select>
										<?php echo form_error('product_service_id', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="currency_id"><i class="fa fa-money-bill-wave"></i> Currency <span class="text-danger">*</span></label>
										<select name="currency_id" class="form-control form-select" id="currency_id">
											<option value="">Select Currency</option>
											<?php foreach($currencies as $currency){ ?>
												<option value="<?= htmlspecialchars($currency['id'] ?? '', ENT_QUOTES, 'UTF-8')?>" <?= (!empty($detail['currency_id']) && $detail['currency_id'] == ($currency['id'] ?? '')) ? 'selected' : ''?>>
													<?= htmlspecialchars($currency['symbol'] ?? '', ENT_QUOTES, 'UTF-8') . ' (' . htmlspecialchars($currency['code'] ?? '', ENT_QUOTES, 'UTF-8') . ')'?>
												</option>
											<?php } ?>
										</select>
										<?php echo form_error('currency_id', '<div class="error">', '</div>'); ?>
									</div>
								</div>
							</div>
						</div>

						<!-- Pricing Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-dollar-sign"></i> Pricing
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="billing_cycle_id"><i class="fa fa-sync"></i> Billing Cycle <span class="text-danger">*</span></label>
										<select name="billing_cycle_id" class="form-control form-select" id="billing_cycle_id">
											<option value="">Select Billing Cycle</option>
											<?php foreach($billing_cycles as $cycle){ ?>
												<option value="<?= htmlspecialchars($cycle['id'] ?? '', ENT_QUOTES, 'UTF-8')?>" <?= (!empty($detail['billing_cycle_id']) && $detail['billing_cycle_id'] == ($cycle['id'] ?? '')) ? 'selected' : ''?>>
													<?= htmlspecialchars($cycle['cycle_name'] ?? '', ENT_QUOTES, 'UTF-8')?>
												</option>
											<?php } ?>
										</select>
										<?php echo form_error('billing_cycle_id', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="price"><i class="fa fa-tag"></i> Price <span class="text-danger">*</span></label>
										<input name="price" type="text" class="form-control" id="price" value="<?= htmlspecialchars($detail['price'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="0.00"/>
										<?php echo form_error('price', '<div class="error">', '</div>'); ?>
									</div>
								</div>
							</div>
						</div>

						<!-- Submit Button -->
						<div class="form-group mt-4">
							<button type="submit" class="btn btn-save-company">
								<i class="fa fa-check-circle"></i> Save Package
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>

	</div><!-- container -->
</div><!-- content -->

<?php $this->load->view('whmazadmin/include/footer_script');?>
<?php $this->load->view('whmazadmin/include/footer');?>
