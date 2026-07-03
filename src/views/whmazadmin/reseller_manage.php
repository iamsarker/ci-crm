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
							<h3><i class="fa fa-user-tie"></i> <?= !empty($detail['company_name']) ? htmlspecialchars($detail['company_name']) : 'New Reseller' ?></h3>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/reseller/index">Resellers</a></li>
									<li class="breadcrumb-item active"><a href="#">Manage</a></li>
								</ol>
							</nav>
						</div>
						<a href="<?=base_url()?>whmazadmin/reseller/index" class="btn btn-back">
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
					<form method="post" name="entityManageForm" id="entityManageForm" class="company-form" action="<?=base_url()?>whmazadmin/reseller/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
						<?=csrf_field()?>
						<input name="id" type="hidden" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

						<!-- Reseller Account -->
						<div class="company-form-section">
							<div class="section-title"><i class="fa fa-building"></i> Reseller Account</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="company_id"><i class="fa fa-user"></i> Company</label>
										<select name="company_id" id="company_id" class="form-select select2" required <?= !empty($detail['id']) ? 'disabled' : '' ?>>
											<option value="">-- Select a company --</option>
											<?php foreach ($companies as $comp): ?>
												<option value="<?= $comp['id'] ?>" <?= (!empty($detail['company_id']) && $detail['company_id'] == $comp['id']) ? 'selected' : '' ?>>
													<?= htmlspecialchars($comp['company_name'] . ($comp['email'] ? ' (' . $comp['email'] . ')' : ''), ENT_QUOTES, 'UTF-8') ?>
												</option>
											<?php endforeach; ?>
										</select>
										<?php if (!empty($detail['id'])): ?>
											<input type="hidden" name="company_id" value="<?= (int)$detail['company_id'] ?>" />
											<small class="text-muted">The reseller company cannot be changed after creation.</small>
										<?php endif; ?>
										<?php echo form_error('company_id', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<div class="custom-checkbox-toggle mt-4">
											<input name="allow_api" type="checkbox" id="allow_api" value="1" <?= (empty($detail['id']) || (!empty($detail['allow_api']) && $detail['allow_api'] == 1)) ? 'checked' : '' ?> />
											<label for="allow_api"><i class="fa fa-plug me-2"></i> Allow Third-Party API Access</label>
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- Pricing -->
						<div class="company-form-section">
							<div class="section-title"><i class="fa fa-percentage"></i> Reseller Pricing</div>
							<div class="row">
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="discount_type"><i class="fa fa-sliders-h"></i> Discount Type</label>
										<select name="discount_type" id="discount_type" class="form-select" required>
											<option value="percent" <?= (empty($detail['discount_type']) || $detail['discount_type'] == 'percent') ? 'selected' : '' ?>>Percentage</option>
											<option value="fixed" <?= (!empty($detail['discount_type']) && $detail['discount_type'] == 'fixed') ? 'selected' : '' ?>>Fixed Amount</option>
										</select>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="discount_value"><i class="fa fa-tag"></i> Discount Value</label>
										<input name="discount_value" type="number" step="0.01" min="0" class="form-control" id="discount_value" placeholder="0.00" value="<?= htmlspecialchars($detail['discount_value'] ?? '0', ENT_QUOTES, 'UTF-8') ?>" required />
										<small class="text-muted">Applied to this reseller's own orders.</small>
										<?php echo form_error('discount_value', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="credit_balance"><i class="fa fa-wallet"></i> Credit Balance</label>
										<input name="credit_balance" type="number" step="0.01" class="form-control" id="credit_balance" placeholder="0.00" value="<?= htmlspecialchars($detail['credit_balance'] ?? '0', ENT_QUOTES, 'UTF-8') ?>" />
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="currency_id"><i class="fa fa-coins"></i> Credit Currency</label>
										<select name="currency_id" id="currency_id" class="form-select">
											<option value="">-- Any --</option>
											<?php foreach ($currencies as $cur): ?>
												<option value="<?= $cur['id'] ?>" <?= (!empty($detail['currency_id']) && $detail['currency_id'] == $cur['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cur['currency_code'], ENT_QUOTES, 'UTF-8') ?></option>
											<?php endforeach; ?>
										</select>
									</div>
								</div>
							</div>
						</div>

						<!-- Sub-Customers -->
						<div class="company-form-section">
							<div class="section-title"><i class="fa fa-users"></i> Sub-Customers</div>
							<div class="row">
								<div class="col-md-8">
									<div class="form-group">
										<label class="form-label" for="sub_customer_ids"><i class="fa fa-user-plus"></i> Companies Managed by this Reseller</label>
										<select name="sub_customer_ids[]" id="sub_customer_ids" class="form-select select2" multiple>
											<?php foreach ($assignable_companies as $comp): ?>
												<option value="<?= $comp['id'] ?>" <?= in_array($comp['id'], $sub_customer_ids) ? 'selected' : '' ?>>
													<?= htmlspecialchars($comp['name'] . ($comp['email'] ? ' (' . $comp['email'] . ')' : ''), ENT_QUOTES, 'UTF-8') ?>
												</option>
											<?php endforeach; ?>
										</select>
										<small class="text-muted">Only non-reseller companies that are unassigned (or already under this reseller) are listed.</small>
									</div>
								</div>
							</div>
						</div>

						<!-- Notes -->
						<div class="company-form-section">
							<div class="section-title"><i class="fa fa-align-left"></i> Notes</div>
							<div class="form-group">
								<textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Internal notes about this reseller (optional)"><?= htmlspecialchars($detail['notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
							</div>
						</div>

						<!-- Submit -->
						<div class="form-group mt-4">
							<button type="submit" class="btn btn-save-company">
								<i class="fa fa-check-circle"></i> Save Reseller
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>

	</div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script');?>

<script>
$(function(){
	'use strict'
	$('#company_id').select2({ placeholder: 'Select a company...', width: '100%' });
	$('#sub_customer_ids').select2({ placeholder: 'Select sub-customers...', width: '100%' });
});
</script>

<?php $this->load->view('whmazadmin/include/footer');?>
