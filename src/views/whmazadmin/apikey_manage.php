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
							<h3><i class="fa fa-key"></i> <?= !empty($detail['name']) ? htmlspecialchars($detail['name']) : 'New API Key' ?></h3>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/apikey/index">API Keys</a></li>
									<li class="breadcrumb-item active"><a href="#">Manage</a></li>
								</ol>
							</nav>
						</div>
						<a href="<?=base_url()?>whmazadmin/apikey/index" class="btn btn-back">
							<i class="fa fa-arrow-left"></i> Back to List
						</a>
					</div>
				</div>
			</div>
		</div>

		<div class="row mt-2">
			<div class="col-12">
				<div class="manage-form-card">
					<form method="post" name="entityManageForm" id="entityManageForm" class="company-form" action="<?=base_url()?>whmazadmin/apikey/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
						<?=csrf_field()?>
						<input name="id" type="hidden" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

						<!-- Identity -->
						<div class="company-form-section">
							<div class="section-title"><i class="fa fa-info-circle"></i> Key Details</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="name"><i class="fa fa-tag"></i> Key Name</label>
										<input name="name" type="text" class="form-control" id="name" placeholder="e.g. Acme Reseller Integration" value="<?= htmlspecialchars($detail['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required />
										<?php echo form_error('name', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="company_id"><i class="fa fa-building"></i> Reseller Account</label>
										<?php if (!empty($detail['id'])): ?>
											<input type="text" class="form-control" value="<?= htmlspecialchars($detail['company_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" readonly />
											<small class="text-muted">The owning reseller cannot be changed.</small>
										<?php else: ?>
											<select name="company_id" id="company_id" class="form-select select2" required>
												<option value="">-- Select a reseller --</option>
												<?php foreach ($resellers as $r): ?>
													<option value="<?= $r['id'] ?>" <?= (!empty($preselect_company) && $preselect_company == $r['id']) ? 'selected' : '' ?>>
														<?= htmlspecialchars($r['company_name'] . ($r['email'] ? ' (' . $r['email'] . ')' : ''), ENT_QUOTES, 'UTF-8') ?>
													</option>
												<?php endforeach; ?>
											</select>
											<?php if (empty($resellers)): ?>
												<small class="text-danger">No API-enabled resellers. Create one under <a href="<?=base_url()?>whmazadmin/reseller/index">Reseller Management</a> first.</small>
											<?php endif; ?>
											<?php echo form_error('company_id', '<div class="error">', '</div>'); ?>
										<?php endif; ?>
									</div>
								</div>
							</div>
							<?php if (!empty($detail['id'])): ?>
							<div class="row mt-2">
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label"><i class="fa fa-fingerprint"></i> API Key (X-Api-Key)</label>
										<input type="text" class="form-control" value="<?= htmlspecialchars($detail['key_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>" readonly />
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label"><i class="fa fa-lock"></i> Secret</label>
										<input type="text" class="form-control" value="····<?= htmlspecialchars($detail['secret_preview'] ?? '', ENT_QUOTES, 'UTF-8') ?> (hidden)" readonly />
										<small class="text-muted">Use <a href="<?=base_url()?>whmazadmin/apikey/regenerate/<?= safe_encode($detail['id']) ?>">Regenerate</a> to issue a new secret.</small>
									</div>
								</div>
							</div>
							<?php endif; ?>
						</div>

						<!-- Scopes -->
						<div class="company-form-section">
							<div class="section-title"><i class="fa fa-shield-alt"></i> Scopes (Permissions)</div>
							<div class="row">
								<?php foreach ($scope_groups as $group => $scopes): ?>
								<div class="col-md-6 mb-3">
									<div class="border rounded p-3 h-100">
										<div class="fw-semibold mb-2"><?= htmlspecialchars($group) ?></div>
										<?php foreach ($scopes as $key => $label): ?>
										<div class="form-check">
											<input class="form-check-input scope-check" type="checkbox" name="scopes[]" value="<?= $key ?>" id="scope_<?= str_replace(':','_',$key) ?>" <?= in_array($key, $selected_scopes) ? 'checked' : '' ?>>
											<label class="form-check-label" for="scope_<?= str_replace(':','_',$key) ?>">
												<code class="small"><?= htmlspecialchars($key) ?></code> — <?= htmlspecialchars($label) ?>
											</label>
										</div>
										<?php endforeach; ?>
									</div>
								</div>
								<?php endforeach; ?>
							</div>
							<button type="button" class="btn btn-sm btn-outline-secondary" id="btnToggleAllScopes"><i class="fa fa-check-double me-1"></i> Select / Clear All</button>
						</div>

						<!-- Restrictions -->
						<div class="company-form-section">
							<div class="section-title"><i class="fa fa-sliders-h"></i> Restrictions</div>
							<div class="row">
								<div class="col-md-4">
									<div class="form-group">
										<label class="form-label" for="rate_limit"><i class="fa fa-tachometer-alt"></i> Rate Limit (req/min)</label>
										<input name="rate_limit" type="number" min="0" class="form-control" id="rate_limit" placeholder="0 = unlimited" value="<?= htmlspecialchars($detail['rate_limit'] ?? '0', ENT_QUOTES, 'UTF-8') ?>" />
										<small class="text-muted">Optional per-minute cap. A hard ceiling of <strong>5 requests/second</strong> applies to every key regardless of this value.</small>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="form-label" for="expires_at"><i class="fa fa-calendar-times"></i> Expires At</label>
										<input name="expires_at" type="datetime-local" class="form-control" id="expires_at" value="<?= !empty($detail['expires_at']) ? date('Y-m-d\TH:i', strtotime($detail['expires_at'])) : '' ?>" />
										<small class="text-muted">Leave blank for no expiry.</small>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="form-label" for="ip_whitelist"><i class="fa fa-network-wired"></i> IP Allowlist</label>
										<textarea name="ip_whitelist" id="ip_whitelist" class="form-control" rows="2" placeholder="Blank = any. One per line: 1.2.3.4 or 10.0.0.0/24"><?= htmlspecialchars($detail['ip_whitelist'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
									</div>
								</div>
							</div>
						</div>

						<div class="form-group mt-4">
							<button type="submit" class="btn btn-save-company">
								<i class="fa fa-check-circle"></i> <?= !empty($detail['id']) ? 'Update API Key' : 'Create API Key' ?>
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
	$('#company_id').select2({ placeholder: 'Select a reseller...', width: '100%' });

	$('#btnToggleAllScopes').on('click', function(){
		var boxes = $('.scope-check');
		var allChecked = boxes.length === boxes.filter(':checked').length;
		boxes.prop('checked', !allChecked);
	});
});
</script>

<?php $this->load->view('whmazadmin/include/footer');?>
