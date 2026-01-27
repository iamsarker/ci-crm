<?php $this->load->view('whmazadmin/include/header');?>

<div class="content content-fluid content-wrapper">
	<div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

		<div class="row mt-5">
			<div class="col-md-12 col-sm-12">
				<h3 class="d-flex justify-content-between"><span>General Settings</span></h3>
				<hr class="mg-5" />
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb breadcrumb-style1 mg-b-0">
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
						<li class="breadcrumb-item active"><a href="#">General Settings</a></li>
					</ol>
				</nav>
			</div>

			<div class="col-md-12 col-sm-12 mt-4">
				<!-- Tabs Navigation -->
				<ul class="nav nav-tabs" id="settingsTabs" role="tablist">
					<li class="nav-item" role="presentation">
						<button class="nav-link <?= ($active_tab === 'general') ? 'active' : '' ?>" id="general-tab" data-bs-toggle="tab" data-bs-target="#generalTabContent" type="button" role="tab" aria-controls="generalTabContent" aria-selected="<?= ($active_tab === 'general') ? 'true' : 'false' ?>">
							<i class="fa fa-cog"></i>&nbsp;General Setting
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link <?= ($active_tab === 'dunning') ? 'active' : '' ?>" id="dunning-tab" data-bs-toggle="tab" data-bs-target="#dunningTabContent" type="button" role="tab" aria-controls="dunningTabContent" aria-selected="<?= ($active_tab === 'dunning') ? 'true' : 'false' ?>">
							<i class="fa fa-bell"></i>&nbsp;Dunning
						</button>
					</li>
				</ul>

				<!-- Tabs Content -->
				<div class="tab-content mt-4" id="settingsTabsContent">

					<!-- ==================== GENERAL SETTING TAB ==================== -->
					<div class="tab-pane fade <?= ($active_tab === 'general') ? 'show active' : '' ?>" id="generalTabContent" role="tabpanel" aria-labelledby="general-tab">
						<form method="post" name="entityManageForm" id="entityManageForm" action="<?=base_url()?>whmazadmin/general_setting/manage" enctype="multipart/form-data">
							<?=csrf_field()?>

							<!-- Site Information Section -->
							<div class="card mb-4">
								<div class="card-header bg-primary text-white">
									<h5 class="mb-0">Site Information</h5>
								</div>
								<div class="card-body">
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="site_name">Site Name <span class="text-danger">*</span></label>
												<input name="site_name" type="text" class="form-control" id="site_name" value="<?= htmlspecialchars($detail['site_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Enter site name"/>
												<?php echo form_error('site_name', '<div class="text-danger">', '</div>'); ?>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="site_desc">Site Description</label>
												<input name="site_desc" type="text" class="form-control" id="site_desc" value="<?= htmlspecialchars($detail['site_desc'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Enter site description"/>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<div class="form-group">
												<label for="admin_url">Admin URL</label>
												<input name="admin_url" type="text" class="form-control" id="admin_url" value="<?= htmlspecialchars($detail['admin_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Enter admin URL"/>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="logo">Logo</label>
												<input name="logo" type="file" class="form-control" id="logo" accept=".jpg,.jpeg,.png,.gif"/>
												<small class="text-muted">Allowed: JPG, PNG, GIF (Max 2MB)</small>
												<?php if (!empty($detail['logo'])) { ?>
													<div class="mt-2">
														<span class="text-success"><i class="fa fa-check-circle"></i> Current: <?= htmlspecialchars($detail['logo'], ENT_QUOTES, 'UTF-8') ?></span>
														<br/>
														<img src="<?= base_url() ?>uploadedfiles/mics/<?= htmlspecialchars($detail['logo'], ENT_QUOTES, 'UTF-8') ?>" alt="Logo" class="img-thumbnail mt-1" style="max-height: 80px;"/>
													</div>
												<?php } ?>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="favicon">Favicon</label>
												<input name="favicon" type="file" class="form-control" id="favicon" accept=".jpg,.jpeg,.png,.gif,.ico"/>
												<small class="text-muted">Allowed: JPG, PNG, GIF, ICO (Max 2MB)</small>
												<?php if (!empty($detail['favicon'])) { ?>
													<div class="mt-2">
														<span class="text-success"><i class="fa fa-check-circle"></i> Current: <?= htmlspecialchars($detail['favicon'], ENT_QUOTES, 'UTF-8') ?></span>
														<br/>
														<img src="<?= base_url() ?>uploadedfiles/mics/<?= htmlspecialchars($detail['favicon'], ENT_QUOTES, 'UTF-8') ?>" alt="Favicon" class="img-thumbnail mt-1" style="max-height: 50px;"/>
													</div>
												<?php } ?>
											</div>
										</div>
									</div>
								</div>
							</div>

							<!-- Company Information Section -->
							<div class="card mb-4">
								<div class="card-header bg-success text-white">
									<h5 class="mb-0">Company Information</h5>
								</div>
								<div class="card-body">
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="company_name">Company Name <span class="text-danger">*</span></label>
												<input name="company_name" type="text" class="form-control" id="company_name" value="<?= htmlspecialchars($detail['company_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Enter company name"/>
												<?php echo form_error('company_name', '<div class="text-danger">', '</div>'); ?>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="zip_code">Zip Code</label>
												<input name="zip_code" type="text" class="form-control" id="zip_code" value="<?= htmlspecialchars($detail['zip_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Enter zip code"/>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<div class="form-group">
												<label for="company_address">Company Address</label>
												<textarea name="company_address" class="form-control" id="company_address" rows="3" placeholder="Enter company address"><?= htmlspecialchars($detail['company_address'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
											</div>
										</div>
									</div>
								</div>
							</div>

							<!-- Contact Information Section -->
							<div class="card mb-4">
								<div class="card-header bg-info text-white">
									<h5 class="mb-0">Contact Information</h5>
								</div>
								<div class="card-body">
									<div class="row">
										<div class="col-md-4">
											<div class="form-group">
												<label for="email">Email <span class="text-danger">*</span></label>
												<input name="email" type="email" class="form-control" id="email" value="<?= htmlspecialchars($detail['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Enter email address"/>
												<?php echo form_error('email', '<div class="text-danger">', '</div>'); ?>
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label for="phone">Phone</label>
												<input name="phone" type="text" class="form-control" id="phone" value="<?= htmlspecialchars($detail['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Enter phone number"/>
											</div>
										</div>
										<div class="col-md-4">
											<div class="form-group">
												<label for="fax">Fax</label>
												<input name="fax" type="text" class="form-control" id="fax" value="<?= htmlspecialchars($detail['fax'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Enter fax number"/>
											</div>
										</div>
									</div>
								</div>
							</div>

							<!-- SMTP Configuration Section -->
							<div class="card mb-4">
								<div class="card-header bg-warning">
									<h5 class="mb-0">SMTP Configuration</h5>
								</div>
								<div class="card-body">
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="smtp_host">SMTP Host</label>
												<input name="smtp_host" type="text" class="form-control" id="smtp_host" value="<?= htmlspecialchars($detail['smtp_host'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g., smtp.gmail.com"/>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="smtp_port">SMTP Port</label>
												<input name="smtp_port" type="text" class="form-control" id="smtp_port" value="<?= htmlspecialchars($detail['smtp_port'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g., 587"/>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="smtp_username">SMTP Username</label>
												<input name="smtp_username" type="text" class="form-control" id="smtp_username" value="<?= htmlspecialchars($detail['smtp_username'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="SMTP username"/>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="smtp_authkey">SMTP Auth Key / Password</label>
												<input name="smtp_authkey" type="password" class="form-control" id="smtp_authkey" value="<?= htmlspecialchars($detail['smtp_authkey'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="SMTP password or auth key"/>
											</div>
										</div>
									</div>
								</div>
							</div>

							<!-- reCAPTCHA Configuration Section -->
							<div class="card mb-4">
								<div class="card-header bg-secondary text-white">
									<h5 class="mb-0">Google reCAPTCHA Configuration</h5>
								</div>
								<div class="card-body">
									<div class="alert alert-info">
										<i class="fa fa-info-circle"></i> Get your reCAPTCHA keys from <a href="https://www.google.com/recaptcha/admin" target="_blank">https://www.google.com/recaptcha/admin</a>
									</div>
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="captcha_site_key">reCAPTCHA Site Key</label>
												<input name="captcha_site_key" type="text" class="form-control" id="captcha_site_key" value="<?= htmlspecialchars($detail['captcha_site_key'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Enter site key"/>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="captcha_secret_key">reCAPTCHA Secret Key</label>
												<input name="captcha_secret_key" type="password" class="form-control" id="captcha_secret_key" value="<?= htmlspecialchars($detail['captcha_secret_key'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Enter secret key"/>
											</div>
										</div>
									</div>
								</div>
							</div>

							<!-- Submit Button -->
							<div class="form-group">
								<button type="submit" class="btn btn-primary btn-lg"><i class="fa fa-check-circle"></i>&nbsp;Save Settings</button>
							</div>

						</form>
					</div>

					<!-- ==================== DUNNING TAB ==================== -->
					<div class="tab-pane fade <?= ($active_tab === 'dunning') ? 'show active' : '' ?>" id="dunningTabContent" role="tabpanel" aria-labelledby="dunning-tab">

						<div class="alert alert-info">
							<i class="fa fa-info-circle"></i> <strong>Dunning rules</strong> define the automated actions taken when an invoice becomes overdue. Each step executes after a specified number of days past the due date. Rules are processed in step order.
						</div>

						<div class="d-flex justify-content-end mb-3">
							<button type="button" class="btn btn-sm btn-secondary" onclick="openDunningModal(0)"><i class="fa fa-plus-square"></i>&nbsp;Add Rule</button>
						</div>

						<div class="card">
							<div class="card-body p-0">
								<table class="table table-striped table-hover mb-0" id="dunningRulesTable">
									<thead class="table-dark">
										<tr>
											<th width="8%">Step</th>
											<th width="15%">Days After Due</th>
											<th width="18%">Action</th>
											<th width="24%">Email Template</th>
											<th width="10%" class="text-center">Active</th>
											<th width="15%" class="text-center">Actions</th>
										</tr>
									</thead>
									<tbody>
										<?php if (!empty($dunning_rules)) { ?>
											<?php foreach ($dunning_rules as $rule) { ?>
												<tr id="rule-row-<?= intval($rule['id']) ?>">
													<td><span class="badge bg-dark"><?= intval($rule['step_number']) ?></span></td>
													<td><?= intval($rule['days_after_due']) ?> day(s)</td>
													<td>
														<?php
														$action_badges = array(
															'EMAIL' => 'bg-primary',
															'SUSPEND' => 'bg-warning text-dark',
															'TERMINATE' => 'bg-danger'
														);
														$badge_class = $action_badges[$rule['action_type']] ?? 'bg-secondary';
														?>
														<span class="badge <?= $badge_class ?>"><?= htmlspecialchars($rule['action_type'], ENT_QUOTES, 'UTF-8') ?></span>
													</td>
													<td><?= htmlspecialchars($rule['email_template'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
													<td class="text-center">
														<?php if (intval($rule['is_active']) === 1) { ?>
															<span class="badge bg-success">Yes</span>
														<?php } else { ?>
															<span class="badge bg-danger">No</span>
														<?php } ?>
													</td>
													<td class="text-center">
														<button type="button" class="btn btn-xs btn-secondary" onclick="openDunningModal(<?= intval($rule['id']) ?>)" title="Edit"><i class="fa fa-wrench"></i></button>
														<button type="button" class="btn btn-xs btn-danger" onclick="deleteDunningRule(<?= intval($rule['id']) ?>, <?= intval($rule['step_number']) ?>)" title="Delete"><i class="fa fa-trash"></i></button>
													</td>
												</tr>
											<?php } ?>
										<?php } else { ?>
											<tr id="no-rules-row">
												<td colspan="6" class="text-center text-muted py-4">No dunning rules configured. Click "Add Rule" to create your first rule.</td>
											</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>
						</div>

						<!-- Dunning Workflow Preview -->
						<?php if (!empty($dunning_rules)) { ?>
						<div class="card mt-4">
							<div class="card-header bg-dark text-white">
								<h5 class="mb-0"><i class="fa fa-list-ol"></i>&nbsp;Dunning Workflow Preview</h5>
							</div>
							<div class="card-body">
								<div class="d-flex align-items-center flex-wrap gap-2">
									<span class="badge bg-secondary p-2">Invoice Overdue</span>
									<?php foreach ($dunning_rules as $idx => $rule) { ?>
										<i class="fa fa-arrow-right text-muted"></i>
										<span class="badge <?= $action_badges[$rule['action_type']] ?? 'bg-secondary' ?> p-2">
											Day <?= intval($rule['days_after_due']) ?>: <?= htmlspecialchars($rule['action_type'], ENT_QUOTES, 'UTF-8') ?>
										</span>
									<?php } ?>
								</div>
							</div>
						</div>
						<?php } ?>

					</div>
				</div>
			</div>
		</div>

	</div><!-- container -->
</div><!-- content -->

<!-- Dunning Rule Modal -->
<div class="modal fade" id="dunningRuleModal" tabindex="-1" aria-labelledby="dunningRuleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="dunningRuleModalLabel">Add Dunning Rule</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form id="dunningRuleForm">
					<?=csrf_field()?>
					<input type="hidden" name="id" id="dr_id" value="0"/>

					<div class="form-group mb-3">
						<label for="dr_step_number">Step Number <span class="text-danger">*</span></label>
						<input name="step_number" type="number" class="form-control" id="dr_step_number" min="1" placeholder="e.g., 1, 2, 3" required/>
						<small class="text-muted">Execution order. Lower numbers run first.</small>
					</div>

					<div class="form-group mb-3">
						<label for="dr_days_after_due">Days After Due Date <span class="text-danger">*</span></label>
						<input name="days_after_due" type="number" class="form-control" id="dr_days_after_due" min="0" placeholder="e.g., 1, 3, 7, 14" required/>
						<small class="text-muted">Number of days after the invoice due date to trigger this action.</small>
					</div>

					<div class="form-group mb-3">
						<label for="dr_action_type">Action Type <span class="text-danger">*</span></label>
						<select name="action_type" class="form-select" id="dr_action_type" required>
							<option value="">-- Select Action --</option>
							<option value="EMAIL">EMAIL - Send reminder email</option>
							<option value="SUSPEND">SUSPEND - Suspend service</option>
							<option value="TERMINATE">TERMINATE - Terminate service</option>
						</select>
					</div>

					<div class="form-group mb-3">
						<label for="dr_email_template">Email Template</label>
						<input name="email_template" type="text" class="form-control" id="dr_email_template" placeholder="e.g., overdue_reminder_1"/>
						<small class="text-muted">Template name for email action (optional).</small>
					</div>

					<div class="form-check mb-3">
						<input name="is_active" type="checkbox" class="form-check-input" id="dr_is_active" value="1" checked/>
						<label class="form-check-label" for="dr_is_active">Active</label>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" id="btnSaveDunningRule" onclick="saveDunningRule()"><i class="fa fa-check-circle"></i>&nbsp;Save Rule</button>
			</div>
		</div>
	</div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script');?>
<script>
$(function(){
	'use strict'

	// Show flash messages as toast
	<?php if ($this->session->flashdata('alert_success')) { ?>
		toastSuccess(<?= json_encode($this->session->flashdata('alert_success')) ?>);
	<?php } ?>
	<?php if ($this->session->flashdata('alert_error')) { ?>
		toastError(<?= json_encode($this->session->flashdata('alert_error')) ?>);
	<?php } ?>
});

function openDunningModal(id) {
	// Reset form
	$('#dunningRuleForm')[0].reset();
	$('#dr_id').val(0);
	$('#dr_is_active').prop('checked', true);

	if (id > 0) {
		$('#dunningRuleModalLabel').text('Edit Dunning Rule');
		// Load existing data
		$.ajax({
			url: '<?=base_url()?>whmazadmin/general_setting/get_dunning_rule/' + id,
			type: 'GET',
			dataType: 'json',
			success: function(resp) {
				if (resp.success == 1) {
					var d = resp.data;
					$('#dr_id').val(d.id);
					$('#dr_step_number').val(d.step_number);
					$('#dr_days_after_due').val(d.days_after_due);
					$('#dr_action_type').val(d.action_type);
					$('#dr_email_template').val(d.email_template);
					$('#dr_is_active').prop('checked', parseInt(d.is_active) === 1);
					$('#dunningRuleModal').modal('show');
				} else {
					toastError(resp.message || 'Failed to load rule.');
				}
			},
			error: function() {
				toastError('Failed to load dunning rule.');
			}
		});
	} else {
		$('#dunningRuleModalLabel').text('Add Dunning Rule');
		$('#dunningRuleModal').modal('show');
	}
}

function saveDunningRule() {
	var $btn = $('#btnSaveDunningRule');
	var stepNum = $('#dr_step_number').val();
	var daysAfter = $('#dr_days_after_due').val();
	var actionType = $('#dr_action_type').val();

	if (!stepNum || parseInt(stepNum) < 1) {
		toastError('Please enter a valid step number.');
		return;
	}
	if (daysAfter === '' || parseInt(daysAfter) < 0) {
		toastError('Please enter days after due date.');
		return;
	}
	if (!actionType) {
		toastError('Please select an action type.');
		return;
	}

	$btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>&nbsp;Saving...');

	$.ajax({
		url: '<?=base_url()?>whmazadmin/general_setting/save_dunning_rule',
		type: 'POST',
		data: $('#dunningRuleForm').serialize(),
		dataType: 'json',
		success: function(resp) {
			$btn.prop('disabled', false).html('<i class="fa fa-check-circle"></i>&nbsp;Save Rule');
			if (resp.success == 1) {
				toastSuccess(resp.message);
				$('#dunningRuleModal').modal('hide');
				// Reload page on dunning tab
				window.location = '<?=base_url()?>whmazadmin/general_setting/manage?tab=dunning';
			} else {
				toastError(resp.message || 'Failed to save rule.');
			}
		},
		error: function() {
			$btn.prop('disabled', false).html('<i class="fa fa-check-circle"></i>&nbsp;Save Rule');
			toastError('Server error. Please try again.');
		}
	});
}

function deleteDunningRule(id, stepNum) {
	Swal.fire({
		title: 'Do you want to delete <b>Step ' + stepNum + '</b>?',
		showDenyButton: true,
		icon: 'question',
		confirmButtonText: 'Yes, delete',
		denyButtonText: 'No, cancel',
		customClass: {
			actions: 'my-actions',
			denyButton: 'order-1 right-gap',
			confirmButton: 'order-2',
		},
	}).then((result) => {
		if (result.isConfirmed) {
			$.ajax({
				url: '<?=base_url()?>whmazadmin/general_setting/delete_dunning_rule/' + id,
				type: 'POST',
				data: { <?=$this->security->get_csrf_token_name()?>: '<?=$this->security->get_csrf_hash()?>' },
				dataType: 'json',
				success: function(resp) {
					if (resp.success == 1) {
						toastSuccess(resp.message);
						window.location = '<?=base_url()?>whmazadmin/general_setting/manage?tab=dunning';
					} else {
						toastError(resp.message || 'Failed to delete rule.');
					}
				},
				error: function() {
					toastError('Server error. Please try again.');
				}
			});
		}
	});
}
</script>

<?php $this->load->view('whmazadmin/include/footer');?>
