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
							<h3><i class="fa fa-globe"></i> Domain Pricing</h3>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/domain_pricing/index">Domain Pricing</a></li>
									<li class="breadcrumb-item active"><a href="#">Manage</a></li>
								</ol>
							</nav>
						</div>
						<a href="<?=base_url()?>whmazadmin/domain_pricing/index" class="btn btn-back">
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
					<form method="post" name="entityManageForm" id="entityManageForm" class="company-form" action="<?=base_url()?>whmazadmin/domain_pricing/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
						<?=csrf_field()?>
						<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

						<!-- Domain Extension Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-globe"></i> Domain Extension
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="dom_extension_id"><i class="fa fa-at"></i> Domain Extension <span class="text-danger">*</span></label>
										<select name="dom_extension_id" class="form-control form-select" id="dom_extension_id">
											<option value="">Select Extension</option>
											<?php foreach($extensions as $ext){ ?>
												<option value="<?= htmlspecialchars($ext['id'], ENT_QUOTES, 'UTF-8')?>" <?= (!empty($detail['dom_extension_id']) && $detail['dom_extension_id'] == $ext['id']) ? 'selected' : ''?>>
													<?= htmlspecialchars($ext['extension'], ENT_QUOTES, 'UTF-8')?>
												</option>
											<?php } ?>
										</select>
										<?php echo form_error('dom_extension_id', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="currency_id"><i class="fa fa-money-bill-wave"></i> Currency <span class="text-danger">*</span></label>
										<select name="currency_id" class="form-control form-select" id="currency_id">
											<option value="">Select Currency</option>
											<?php foreach($currencies as $currency){ ?>
												<option value="<?= htmlspecialchars($currency['id'], ENT_QUOTES, 'UTF-8')?>" <?= (!empty($detail['currency_id']) && $detail['currency_id'] == $currency['id']) ? 'selected' : ''?>>
													<?= htmlspecialchars($currency['symbol'], ENT_QUOTES, 'UTF-8') . ' (' . htmlspecialchars($currency['code'], ENT_QUOTES, 'UTF-8') . ')'?>
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
								<i class="fa fa-tags"></i> Pricing Details
							</div>
							<div class="row">
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="reg_period"><i class="fa fa-calendar"></i> Registration Period (Years) <span class="text-danger">*</span></label>
										<input name="reg_period" type="number" class="form-control" id="reg_period" value="<?= htmlspecialchars($detail['reg_period'] ?? '1', ENT_QUOTES, 'UTF-8') ?>" placeholder="1"/>
										<?php echo form_error('reg_period', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="price"><i class="fa fa-plus-circle"></i> Registration Price <span class="text-danger">*</span></label>
										<input name="price" type="text" class="form-control" id="price" value="<?= htmlspecialchars($detail['price'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="0.00"/>
										<?php echo form_error('price', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="transfer"><i class="fa fa-exchange-alt"></i> Transfer Price <span class="text-danger">*</span></label>
										<input name="transfer" type="text" class="form-control" id="transfer" value="<?= htmlspecialchars($detail['transfer'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="0.00"/>
										<?php echo form_error('transfer', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="renewal"><i class="fa fa-sync"></i> Renewal Price <span class="text-danger">*</span></label>
										<input name="renewal" type="text" class="form-control" id="renewal" value="<?= htmlspecialchars($detail['renewal'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="0.00"/>
										<?php echo form_error('renewal', '<div class="error">', '</div>'); ?>
									</div>
								</div>
							</div>
						</div>

						<!-- Submit Button -->
						<div class="form-group mt-4">
							<button type="submit" class="btn btn-save-company">
								<i class="fa fa-check-circle"></i> Save Pricing
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
