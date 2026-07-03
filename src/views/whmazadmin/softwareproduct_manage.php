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
							<h3><i class="fa fa-cube"></i> <?= !empty($detail['name']) ? htmlspecialchars($detail['name']) : 'New Software Product' ?></h3>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/softwareproduct/index">Software Products</a></li>
									<li class="breadcrumb-item active"><a href="#">Manage</a></li>
								</ol>
							</nav>
						</div>
						<a href="<?=base_url()?>whmazadmin/softwareproduct/index" class="btn btn-back">
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
					<form method="post" name="entityManageForm" id="entityManageForm" class="company-form" action="<?=base_url()?>whmazadmin/softwareproduct/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
						<?=csrf_field()?>
						<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

						<!-- Product Details Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-info-circle"></i> Product Details
							</div>
							<div class="row">
								<div class="col-md-4">
									<div class="form-group">
										<label class="form-label" for="name"><i class="fa fa-cube"></i> Product Name</label>
										<input name="name" type="text" class="form-control" id="name" placeholder="e.g. WHMAZ Pro" value="<?= htmlspecialchars($detail['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('name', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="form-label" for="plan_key"><i class="fa fa-link"></i> Product Key <small class="text-muted">(slug, optional)</small></label>
										<input name="plan_key" type="text" class="form-control" id="plan_key" placeholder="auto from name" value="<?= htmlspecialchars($detail['plan_key'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<small class="text-muted">Used in the license feature map. Leave blank to auto-generate.</small>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="form-label" for="sort_order"><i class="fa fa-sort-numeric-down"></i> Sort Order</label>
										<input name="sort_order" type="number" class="form-control" id="sort_order" min="0" value="<?= htmlspecialchars((string)($detail['sort_order'] ?? 0), ENT_QUOTES, 'UTF-8') ?>"/>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="tagline"><i class="fa fa-quote-left"></i> Tagline</label>
										<input name="tagline" type="text" class="form-control" id="tagline" placeholder="Short one-liner" value="<?= htmlspecialchars($detail['tagline'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label"><i class="fa fa-star"></i> Popular</label>
										<div class="custom-checkbox-toggle mt-2">
											<input type="checkbox" name="is_popular" id="is_popular" value="1" <?= (!empty($detail['is_popular'])) ? 'checked' : '' ?>>
											<label for="is_popular">Mark as popular</label>
										</div>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label"><i class="fa fa-toggle-on"></i> Status</label>
										<div class="custom-checkbox-toggle mt-2">
											<input type="checkbox" name="is_active" id="is_active" value="1" <?= (!isset($detail['is_active']) || $detail['is_active'] == 1) ? 'checked' : '' ?>>
											<label for="is_active">Active (visible to customers)</label>
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- Description Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-file-alt"></i> Description
							</div>
							<div class="form-group">
								<textarea name="description" class="form-control" id="description" rows="5" placeholder="Full product description (HTML allowed)..."><?= htmlspecialchars($detail['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
							</div>
						</div>

						<!-- Download Release Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-download"></i> Download Release
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="current_release_id"><i class="fa fa-box-open"></i> Linked Release</label>
										<select name="current_release_id" class="form-select" id="current_release_id">
											<option value="">-- No release linked --</option>
											<?php foreach ($releases as $rel): ?>
											<option value="<?= intval($rel['id']) ?>" <?= (!empty($detail['current_release_id']) && $detail['current_release_id'] == $rel['id']) ? 'selected' : '' ?>>
												v<?= htmlspecialchars($rel['version']) ?><?= empty($rel['product_id']) ? ' (global)' : '' ?>
											</option>
											<?php endforeach; ?>
										</select>
										<small class="text-muted">The ZIP a licensed customer downloads. Upload releases under <a href="<?=base_url()?>whmazadmin/software" target="_blank">Software Releases</a>.</small>
									</div>
								</div>
							</div>
						</div>

						<!-- Pricing Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-tags"></i> Pricing <small class="text-muted">(per currency &amp; billing cycle)</small>
							</div>
							<?php if (!empty($billing_cycles) && !empty($currencies)): ?>
							<div class="table-responsive">
								<table class="table table-bordered mb-2">
									<thead class="bg-light">
										<tr>
											<th style="min-width:120px;">Currency</th>
											<?php foreach ($billing_cycles as $cycle): ?>
											<th class="text-center" style="min-width:120px;"><?= htmlspecialchars($cycle['cycle_name']) ?></th>
											<?php endforeach; ?>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($currencies as $currency): ?>
										<tr>
											<td class="align-middle fw-bold bg-light">
												<?= htmlspecialchars($currency['symbol']) ?> <?= htmlspecialchars($currency['code']) ?>
											</td>
											<?php foreach ($billing_cycles as $cycle): ?>
											<td>
												<?php
													$existingPrice = '';
													if (isset($pricing_matrix[$currency['id']][$cycle['id']])) {
														$existingPrice = $pricing_matrix[$currency['id']][$cycle['id']];
													}
												?>
												<input type="number"
													   name="pricing[<?= intval($currency['id']) ?>][<?= intval($cycle['id']) ?>]"
													   class="form-control text-end"
													   value="<?= htmlspecialchars($existingPrice) ?>"
													   placeholder="0.00"
													   step="0.01"
													   min="0">
											</td>
											<?php endforeach; ?>
										</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
							<small class="text-muted"><i class="fa fa-info-circle"></i> Leave empty for billing cycles you don't want to offer. Use the <strong>One Time</strong> cycle for a perpetual (non-recurring) license.</small>
							<?php else: ?>
							<p class="text-muted mb-0">No currencies or billing cycles configured yet.</p>
							<?php endif; ?>
						</div>

						<!-- Features Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-list-check"></i> Entitlement Features <small class="text-muted">(license feature map)</small>
							</div>
							<p class="text-muted">Differentiated flags for this product. Use <code>1</code>/<code>0</code> for on/off, or a number/string value (e.g. <code>support_response_hours</code> = <code>24</code>). Universal features are always on and defined in config.</p>
							<div id="featuresWrap">
								<?php
									$featPairs = !empty($features) ? $features : array('' => '');
									foreach ($featPairs as $fk => $fv):
								?>
								<div class="row feature-row mb-2">
									<div class="col-md-5">
										<input type="text" name="feature_key[]" class="form-control" placeholder="feature_key" value="<?= htmlspecialchars((string)$fk, ENT_QUOTES, 'UTF-8') ?>">
									</div>
									<div class="col-md-5">
										<input type="text" name="feature_value[]" class="form-control" placeholder="value (1 / 0 / number / text)" value="<?= htmlspecialchars((string)$fv, ENT_QUOTES, 'UTF-8') ?>">
									</div>
									<div class="col-md-2">
										<button type="button" class="btn btn-outline-danger btn-sm remove-feature"><i class="fa fa-trash"></i></button>
									</div>
								</div>
								<?php endforeach; ?>
							</div>
							<button type="button" class="btn btn-outline-primary btn-sm" id="addFeature"><i class="fa fa-plus"></i> Add Feature</button>
						</div>

						<!-- Submit -->
						<div class="text-end mt-4">
							<button type="submit" class="btn-create-order">
								<i class="fa fa-save"></i> Save Product
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
$(function () {
	function featureRow() {
		return '<div class="row feature-row mb-2">' +
			'<div class="col-md-5"><input type="text" name="feature_key[]" class="form-control" placeholder="feature_key"></div>' +
			'<div class="col-md-5"><input type="text" name="feature_value[]" class="form-control" placeholder="value (1 / 0 / number / text)"></div>' +
			'<div class="col-md-2"><button type="button" class="btn btn-outline-danger btn-sm remove-feature"><i class="fa fa-trash"></i></button></div>' +
			'</div>';
	}
	$('#addFeature').on('click', function () {
		$('#featuresWrap').append(featureRow());
	});
	$('#featuresWrap').on('click', '.remove-feature', function () {
		if ($('.feature-row').length > 1) {
			$(this).closest('.feature-row').remove();
		} else {
			$(this).closest('.feature-row').find('input').val('');
		}
	});
});
</script>

<?php $this->load->view('whmazadmin/include/footer'); ?>
