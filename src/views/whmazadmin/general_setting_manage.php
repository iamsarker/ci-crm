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
					<li class="nav-item" role="presentation">
						<button class="nav-link <?= ($active_tab === 'sysconfig') ? 'active' : '' ?>" id="sysconfig-tab" data-bs-toggle="tab" data-bs-target="#sysconfigTabContent" type="button" role="tab" aria-controls="sysconfigTabContent" aria-selected="<?= ($active_tab === 'sysconfig') ? 'true' : 'false' ?>">
							<i class="fa fa-sliders-h"></i>&nbsp;System Config
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link <?= ($active_tab === 'cronjobs') ? 'active' : '' ?>" id="cronjobs-tab" data-bs-toggle="tab" data-bs-target="#cronjobsTabContent" type="button" role="tab" aria-controls="cronjobsTabContent" aria-selected="<?= ($active_tab === 'cronjobs') ? 'true' : 'false' ?>">
							<i class="fa fa-clock"></i>&nbsp;Cronjobs
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
								<div class="card-header bg-info text-white">
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

										<div class="col-md-3">
											<div class="form-group">
												<label for="email">Email <span class="text-danger">*</span></label>
												<input name="email" type="email" class="form-control" id="email" value="<?= htmlspecialchars($detail['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Enter email address"/>
												<?php echo form_error('email', '<div class="text-danger">', '</div>'); ?>
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="phone">Phone</label>
												<input name="phone" type="text" class="form-control" id="phone" value="<?= htmlspecialchars($detail['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Enter phone number"/>
											</div>
										</div>

									</div>
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="company_address">Company Address</label>
												<input name="company_address" type="text" class="form-control" id="company_address" value="<?= htmlspecialchars($detail['company_address'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Enter company address"/>
											</div>
										</div>

										<div class="col-md-3">
											<div class="form-group">
												<label for="fax">Fax</label>
												<input name="fax" type="text" class="form-control" id="fax" value="<?= htmlspecialchars($detail['fax'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Enter fax number"/>
											</div>
										</div>

										<div class="col-md-3">
											<div class="form-group">
												<label for="bin_tax">BIN / TAX ID</label>
												<input name="bin_tax" type="text" class="form-control" id="bin_tax" value="<?= htmlspecialchars($detail['bin_tax'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Enter BIN or Tax ID"/>
											</div>
										</div>

									</div>
									<div class="row">
										<div class="col-md-3">
											<div class="form-group">
												<label for="zip_code">Zip Code</label>
												<input name="zip_code" type="text" class="form-control" id="zip_code" value="<?= htmlspecialchars($detail['zip_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Enter zip code"/>
											</div>
										</div>

										<div class="col-md-3">
											<div class="form-group">
												<label for="city">City</label>
												<input name="city" type="text" class="form-control" id="city" value="<?= htmlspecialchars($detail['city'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Enter city"/>
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="state">State</label>
												<input name="state" type="text" class="form-control" id="state" value="<?= htmlspecialchars($detail['state'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Enter state"/>
											</div>
										</div>
										<div class="col-md-3">
											<div class="form-group">
												<label for="country">Country</label>
												<select name="country" class="form-select" id="country">
													<option value="">-- Select Country --</option>
													<?php if (!empty($countries)) { ?>
														<?php foreach ($countries as $c) { ?>
															<option value="<?= htmlspecialchars($c['country_name'], ENT_QUOTES, 'UTF-8') ?>" <?= ($detail['country'] ?? '') === $c['country_name'] ? 'selected' : '' ?>><?= htmlspecialchars($c['country_name'], ENT_QUOTES, 'UTF-8') ?></option>
														<?php } ?>
													<?php } ?>
												</select>
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

						<div class="d-flex justify-content-between mb-3">
							<a href="<?=base_url()?>whmazadmin/email_template/index" class="btn btn-sm btn-outline-primary"><i class="fa fa-envelope"></i>&nbsp;Manage Email Templates</a>
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
													<td>
													<?php if (!empty($rule['email_template'])) { ?>
														<code><?= htmlspecialchars($rule['email_template'], ENT_QUOTES, 'UTF-8') ?></code>
													<?php } else { ?>
														<span class="text-muted">-</span>
													<?php } ?>
												</td>
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

					<!-- ==================== SYSTEM CONFIG TAB ==================== -->
					<div class="tab-pane fade <?= ($active_tab === 'sysconfig') ? 'show active' : '' ?>" id="sysconfigTabContent" role="tabpanel" aria-labelledby="sysconfig-tab">

						<div class="alert alert-info">
							<i class="fa fa-info-circle"></i> <strong>System Configuration</strong> stores key-value pairs for system settings. Only values can be edited.
						</div>

						<?php if (!empty($sys_configs)) { ?>
							<?php foreach ($sys_configs as $group_name => $configs) { ?>
								<div class="card mb-4">
									<div class="card-header bg-secondary text-white">
										<h6 class="mb-0 text-white"><i class="fa fa-folder-open"></i>&nbsp;<?= htmlspecialchars($group_name, ENT_QUOTES, 'UTF-8') ?></h6>
									</div>
									<div class="card-body p-0">
										<table class="table table-striped table-hover mb-0">
											<thead class="table-secondary">
												<tr>
													<th width="25%">Key</th>
													<th width="55%">Value</th>
													<th width="12%">Updated</th>
													<th width="8%" class="text-center">Edit</th>
												</tr>
											</thead>
											<tbody>
												<?php foreach ($configs as $cfg) { ?>
													<tr id="cfg-row-<?= intval($cfg['id']) ?>">
														<td>
															<code class="text-primary"><?= htmlspecialchars($cfg['cnf_key'], ENT_QUOTES, 'UTF-8') ?></code>
														</td>
														<td>
															<div class="config-value-display" id="cfg-display-<?= intval($cfg['id']) ?>">
																<?php if (!empty($cfg['cnf_val'])) { ?>
																	<?php
																	// Mask sensitive values
																	$sensitiveKeys = array('secret', 'password', 'authkey', 'api_key', 'token');
																	$isSensitive = false;
																	foreach ($sensitiveKeys as $sk) {
																		if (stripos($cfg['cnf_key'], $sk) !== false) {
																			$isSensitive = true;
																			break;
																		}
																	}
																	?>
																	<?php if ($isSensitive) { ?>
																		<span class="text-muted">********</span>
																	<?php } else { ?>
																		<?= htmlspecialchars($cfg['cnf_val'], ENT_QUOTES, 'UTF-8') ?>
																	<?php } ?>
																<?php } else { ?>
																	<span class="text-muted fst-italic">Empty</span>
																<?php } ?>
															</div>
															<div class="config-value-edit d-none" id="cfg-edit-<?= intval($cfg['id']) ?>">
																<div class="input-group input-group-sm">
																	<input type="text" class="form-control" id="cfg-input-<?= intval($cfg['id']) ?>" value="<?= htmlspecialchars($cfg['cnf_val'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
																	<button type="button" class="btn btn-success" onclick="saveConfigValue(<?= intval($cfg['id']) ?>)" title="Save"><i class="fa fa-check"></i></button>
																	<button type="button" class="btn btn-secondary" onclick="cancelEditConfig(<?= intval($cfg['id']) ?>)" title="Cancel"><i class="fa fa-times"></i></button>
																</div>
															</div>
														</td>
														<td>
															<small class="text-muted"><?= !empty($cfg['updated_on']) ? date('M j, Y', strtotime($cfg['updated_on'])) : '-' ?></small>
														</td>
														<td class="text-center">
															<button type="button" class="btn btn-xs btn-secondary" onclick="editConfigValue(<?= intval($cfg['id']) ?>)" title="Edit"><i class="fa fa-pencil-alt"></i></button>
														</td>
													</tr>
												<?php } ?>
											</tbody>
										</table>
									</div>
								</div>
							<?php } ?>
						<?php } else { ?>
							<div class="card">
								<div class="card-body text-center text-muted py-5">
									<i class="fa fa-database fa-3x mb-3"></i>
									<p>No system configurations found.</p>
								</div>
							</div>
						<?php } ?>

					</div>

					<!-- ==================== CRONJOBS TAB ==================== -->
					<div class="tab-pane fade <?= ($active_tab === 'cronjobs') ? 'show active' : '' ?>" id="cronjobsTabContent" role="tabpanel" aria-labelledby="cronjobs-tab">

						<?php if (empty($cron_secret_key)) { ?>
						<div class="alert alert-warning">
							<i class="fa fa-exclamation-triangle"></i> <strong>Warning:</strong> Cron secret key is not configured. Please add <code>cron_secret_key</code> in the <a href="<?=base_url()?>whmazadmin/general_setting/manage?tab=sysconfig">System Config</a> tab first.
						</div>
						<?php } ?>

						<div class="alert alert-info">
							<i class="fa fa-info-circle"></i> Configure cronjob schedules and install them to your Linux server. Cronjobs run automated tasks like renewal invoice generation.
						</div>

						<div class="d-flex justify-content-end mb-3 gap-2">
							<button type="button" class="btn btn-sm btn-outline-primary" onclick="showCrontabPreview()"><i class="fa fa-eye"></i>&nbsp;Preview Crontab</button>
							<button type="button" class="btn btn-sm btn-success" onclick="installCrontab()" <?= empty($cron_secret_key) ? 'disabled' : '' ?>><i class="fa fa-download"></i>&nbsp;Install to Server</button>
						</div>

						<div class="card">
							<div class="card-body p-0">
								<table class="table table-striped table-hover mb-0">
									<thead class="table-dark">
										<tr>
											<th width="20%">Job Name</th>
											<th width="25%">Description</th>
											<th width="20%">Schedule</th>
											<th width="12%">Last Run</th>
											<th width="10%" class="text-center">Status</th>
											<th width="13%" class="text-center">Actions</th>
										</tr>
									</thead>
									<tbody>
										<?php if (!empty($cron_schedules)) { ?>
											<?php foreach ($cron_schedules as $cron) { ?>
												<?php
												$cronExpr = sprintf('%s %s %s %s %s',
													$cron['schedule_minute'],
													$cron['schedule_hour'],
													$cron['schedule_day'],
													$cron['schedule_month'],
													$cron['schedule_weekday']
												);
												?>
												<tr id="cron-row-<?= intval($cron['id']) ?>">
													<td>
														<strong><?= htmlspecialchars($cron['job_name'], ENT_QUOTES, 'UTF-8') ?></strong>
														<br><small class="text-muted"><code><?= htmlspecialchars($cron['job_command'], ENT_QUOTES, 'UTF-8') ?></code></small>
													</td>
													<td><?= htmlspecialchars($cron['job_description'], ENT_QUOTES, 'UTF-8') ?></td>
													<td>
														<code id="cron-expr-<?= intval($cron['id']) ?>"><?= htmlspecialchars($cronExpr, ENT_QUOTES, 'UTF-8') ?></code>
														<br><small class="text-muted" id="cron-desc-<?= intval($cron['id']) ?>">
															<?php
															// Simple schedule description
															if ($cron['schedule_minute'] === '0' && $cron['schedule_hour'] !== '*' && $cron['schedule_day'] === '*') {
																echo 'Daily at ' . str_pad($cron['schedule_hour'], 2, '0', STR_PAD_LEFT) . ':00';
															} elseif ($cron['schedule_minute'] === '0' && $cron['schedule_hour'] === '*') {
																echo 'Every hour';
															} elseif ($cron['schedule_minute'] === '*') {
																echo 'Every minute';
															} else {
																echo 'Custom schedule';
															}
															?>
														</small>
													</td>
													<td>
														<?php if (!empty($cron['last_run'])) { ?>
															<small><?= date('M j, Y H:i', strtotime($cron['last_run'])) ?></small>
														<?php } else { ?>
															<small class="text-muted">Never</small>
														<?php } ?>
													</td>
													<td class="text-center">
														<?php if (intval($cron['is_active']) === 1) { ?>
															<span class="badge bg-success" id="cron-status-<?= intval($cron['id']) ?>">Active</span>
														<?php } else { ?>
															<span class="badge bg-secondary" id="cron-status-<?= intval($cron['id']) ?>">Inactive</span>
														<?php } ?>
													</td>
													<td class="text-center">
														<button type="button" class="btn btn-xs btn-secondary" onclick="openCronModal(<?= intval($cron['id']) ?>)" title="Edit Schedule"><i class="fa fa-pencil-alt"></i></button>
														<button type="button" class="btn btn-xs <?= intval($cron['is_active']) === 1 ? 'btn-warning' : 'btn-success' ?>" onclick="toggleCronjob(<?= intval($cron['id']) ?>)" title="<?= intval($cron['is_active']) === 1 ? 'Disable' : 'Enable' ?>" id="cron-toggle-btn-<?= intval($cron['id']) ?>">
															<i class="fa <?= intval($cron['is_active']) === 1 ? 'fa-pause' : 'fa-play' ?>"></i>
														</button>
													</td>
												</tr>
											<?php } ?>
										<?php } else { ?>
											<tr>
												<td colspan="6" class="text-center text-muted py-4">No cronjobs configured.</td>
											</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>
						</div>

						<!-- Crontab Preview Card -->
						<div class="card mt-4" id="crontabPreviewCard" style="display: none;">
							<div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
								<h6 class="mb-0 text-white"><i class="fa fa-terminal"></i>&nbsp;Crontab Preview</h6>
								<button type="button" class="btn btn-sm btn-outline-light" onclick="copyCrontab()"><i class="fa fa-copy"></i>&nbsp;Copy</button>
							</div>
							<div class="card-body bg-light">
								<pre id="crontabContent" class="mb-0" style="white-space: pre-wrap; font-size: 12px;"></pre>
							</div>
						</div>

						<!-- Installation Instructions -->
						<div class="card mt-4">
							<div class="card-header bg-secondary text-white">
								<h6 class="mb-0 text-white"><i class="fa fa-terminal"></i>&nbsp;Manual Installation</h6>
							</div>
							<div class="card-body">
								<p class="mb-2">If automatic installation fails, you can manually add cronjobs to your server:</p>
								<ol class="mb-0">
									<li>Click "Preview Crontab" to see the generated crontab entries</li>
									<li>SSH into your server</li>
									<li>Run <code>crontab -e</code> to edit crontab</li>
									<li>Paste the generated entries at the end of the file</li>
									<li>Save and exit</li>
								</ol>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>

	</div><!-- container -->
</div><!-- content -->

<!-- Cronjob Schedule Modal -->
<div class="modal fade" id="cronScheduleModal" tabindex="-1" aria-labelledby="cronScheduleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="cronScheduleModalLabel">Edit Cronjob Schedule</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form id="cronScheduleForm">
					<?=csrf_field()?>
					<input type="hidden" name="id" id="cron_id" value="0"/>

					<div class="mb-3">
						<label class="form-label fw-bold">Job Name</label>
						<p class="mb-0" id="cron_job_name"></p>
					</div>

					<div class="mb-3">
						<label class="form-label fw-bold">Command</label>
						<p class="mb-0"><code id="cron_job_command"></code></p>
					</div>

					<hr/>

					<div class="alert alert-secondary py-2">
						<small><i class="fa fa-info-circle"></i> Cron format: <code>minute hour day month weekday</code></small>
					</div>

					<div class="row">
						<div class="col">
							<div class="form-group mb-3">
								<label for="cron_minute">Minute</label>
								<input name="schedule_minute" type="text" class="form-control" id="cron_minute" placeholder="0-59 or *" required/>
								<small class="text-muted">0-59</small>
							</div>
						</div>
						<div class="col">
							<div class="form-group mb-3">
								<label for="cron_hour">Hour</label>
								<input name="schedule_hour" type="text" class="form-control" id="cron_hour" placeholder="0-23 or *" required/>
								<small class="text-muted">0-23</small>
							</div>
						</div>
						<div class="col">
							<div class="form-group mb-3">
								<label for="cron_day">Day</label>
								<input name="schedule_day" type="text" class="form-control" id="cron_day" placeholder="1-31 or *" required/>
								<small class="text-muted">1-31</small>
							</div>
						</div>
						<div class="col">
							<div class="form-group mb-3">
								<label for="cron_month">Month</label>
								<input name="schedule_month" type="text" class="form-control" id="cron_month" placeholder="1-12 or *" required/>
								<small class="text-muted">1-12</small>
							</div>
						</div>
						<div class="col">
							<div class="form-group mb-3">
								<label for="cron_weekday">Weekday</label>
								<input name="schedule_weekday" type="text" class="form-control" id="cron_weekday" placeholder="0-6 or *" required/>
								<small class="text-muted">0=Sun</small>
							</div>
						</div>
					</div>

					<div class="mb-3">
						<label class="form-label">Quick Presets</label>
						<div class="btn-group btn-group-sm d-flex flex-wrap gap-1" role="group">
							<button type="button" class="btn btn-outline-secondary" onclick="setCronPreset('0', '*', '*', '*', '*')">Every hour</button>
							<button type="button" class="btn btn-outline-secondary" onclick="setCronPreset('0', '0', '*', '*', '*')">Daily midnight</button>
							<button type="button" class="btn btn-outline-secondary" onclick="setCronPreset('0', '6', '*', '*', '*')">Daily 6 AM</button>
							<button type="button" class="btn btn-outline-secondary" onclick="setCronPreset('0', '0', '*', '*', '0')">Weekly Sunday</button>
							<button type="button" class="btn btn-outline-secondary" onclick="setCronPreset('0', '0', '1', '*', '*')">Monthly</button>
						</div>
					</div>

					<div class="mb-3 p-2 bg-light rounded">
						<label class="form-label fw-bold">Preview</label>
						<p class="mb-0"><code id="cron_preview">* * * * *</code></p>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" id="btnSaveCronSchedule" onclick="saveCronSchedule()"><i class="fa fa-check-circle"></i>&nbsp;Save Schedule</button>
			</div>
		</div>
	</div>
</div>

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
						<select name="email_template" class="form-select" id="dr_email_template">
							<option value="">-- None --</option>
							<?php if (!empty($dunning_email_templates)) { ?>
								<?php foreach ($dunning_email_templates as $tpl) { ?>
									<option value="<?= htmlspecialchars($tpl['template_key'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($tpl['template_name'], ENT_QUOTES, 'UTF-8') ?></option>
								<?php } ?>
							<?php } ?>
						</select>
						<small class="text-muted">Select a DUNNING category email template. <a href="<?=base_url()?>whmazadmin/email_template/index" target="_blank">Manage templates</a></small>
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

// ==================== SYSTEM CONFIG FUNCTIONS ====================

function editConfigValue(id) {
	$('#cfg-display-' + id).addClass('d-none');
	$('#cfg-edit-' + id).removeClass('d-none');
	$('#cfg-input-' + id).focus();
}

function cancelEditConfig(id) {
	$('#cfg-edit-' + id).addClass('d-none');
	$('#cfg-display-' + id).removeClass('d-none');
}

function saveConfigValue(id) {
	var value = $('#cfg-input-' + id).val();

	$.ajax({
		url: '<?=base_url()?>whmazadmin/general_setting/update_sysconfig',
		type: 'POST',
		data: {
			<?=$this->security->get_csrf_token_name()?>: '<?=$this->security->get_csrf_hash()?>',
			id: id,
			value: value
		},
		dataType: 'json',
		success: function(resp) {
			if (resp.success == 1) {
				toastSuccess(resp.message);
				// Reload page to refresh display
				window.location = '<?=base_url()?>whmazadmin/general_setting/manage?tab=sysconfig';
			} else {
				toastError(resp.message || 'Failed to update configuration.');
			}
		},
		error: function() {
			toastError('Server error. Please try again.');
		}
	});
}

// ==================== CRONJOB FUNCTIONS ====================

function openCronModal(id) {
	$('#cronScheduleForm')[0].reset();
	$('#cron_id').val(0);

	$.ajax({
		url: '<?=base_url()?>whmazadmin/general_setting/get_cronjob/' + id,
		type: 'GET',
		dataType: 'json',
		success: function(resp) {
			if (resp.success == 1) {
				var d = resp.data;
				$('#cron_id').val(d.id);
				$('#cron_job_name').text(d.job_name);
				$('#cron_job_command').text(d.job_command);
				$('#cron_minute').val(d.schedule_minute);
				$('#cron_hour').val(d.schedule_hour);
				$('#cron_day').val(d.schedule_day);
				$('#cron_month').val(d.schedule_month);
				$('#cron_weekday').val(d.schedule_weekday);
				updateCronPreview();
				$('#cronScheduleModal').modal('show');
			} else {
				toastError(resp.message || 'Failed to load schedule.');
			}
		},
		error: function() {
			toastError('Failed to load cronjob schedule.');
		}
	});
}

function setCronPreset(minute, hour, day, month, weekday) {
	$('#cron_minute').val(minute);
	$('#cron_hour').val(hour);
	$('#cron_day').val(day);
	$('#cron_month').val(month);
	$('#cron_weekday').val(weekday);
	updateCronPreview();
}

function updateCronPreview() {
	var expr = $('#cron_minute').val() + ' ' +
		$('#cron_hour').val() + ' ' +
		$('#cron_day').val() + ' ' +
		$('#cron_month').val() + ' ' +
		$('#cron_weekday').val();
	$('#cron_preview').text(expr);
}

// Update preview on input change
$(document).on('input', '#cron_minute, #cron_hour, #cron_day, #cron_month, #cron_weekday', function() {
	updateCronPreview();
});

function saveCronSchedule() {
	var $btn = $('#btnSaveCronSchedule');

	$btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>&nbsp;Saving...');

	$.ajax({
		url: '<?=base_url()?>whmazadmin/general_setting/update_cronjob',
		type: 'POST',
		data: $('#cronScheduleForm').serialize(),
		dataType: 'json',
		success: function(resp) {
			$btn.prop('disabled', false).html('<i class="fa fa-check-circle"></i>&nbsp;Save Schedule');
			if (resp.success == 1) {
				toastSuccess(resp.message);
				$('#cronScheduleModal').modal('hide');
				window.location = '<?=base_url()?>whmazadmin/general_setting/manage?tab=cronjobs';
			} else {
				toastError(resp.message || 'Failed to save schedule.');
			}
		},
		error: function() {
			$btn.prop('disabled', false).html('<i class="fa fa-check-circle"></i>&nbsp;Save Schedule');
			toastError('Server error. Please try again.');
		}
	});
}

function toggleCronjob(id) {
	$.ajax({
		url: '<?=base_url()?>whmazadmin/general_setting/toggle_cronjob/' + id,
		type: 'POST',
		data: { <?=$this->security->get_csrf_token_name()?>: '<?=$this->security->get_csrf_hash()?>' },
		dataType: 'json',
		success: function(resp) {
			if (resp.success == 1) {
				toastSuccess(resp.message);
				// Update UI
				var $status = $('#cron-status-' + id);
				var $btn = $('#cron-toggle-btn-' + id);
				if (resp.is_active == 1) {
					$status.removeClass('bg-secondary').addClass('bg-success').text('Active');
					$btn.removeClass('btn-success').addClass('btn-warning').attr('title', 'Disable');
					$btn.find('i').removeClass('fa-play').addClass('fa-pause');
				} else {
					$status.removeClass('bg-success').addClass('bg-secondary').text('Inactive');
					$btn.removeClass('btn-warning').addClass('btn-success').attr('title', 'Enable');
					$btn.find('i').removeClass('fa-pause').addClass('fa-play');
				}
			} else {
				toastError(resp.message || 'Failed to toggle cronjob.');
			}
		},
		error: function() {
			toastError('Server error. Please try again.');
		}
	});
}

function showCrontabPreview() {
	$.ajax({
		url: '<?=base_url()?>whmazadmin/general_setting/generate_crontab',
		type: 'GET',
		dataType: 'json',
		success: function(resp) {
			if (resp.success == 1) {
				$('#crontabContent').text(resp.crontab);
				$('#crontabPreviewCard').slideDown();
			} else {
				toastError(resp.message || 'Failed to generate crontab.');
			}
		},
		error: function() {
			toastError('Server error. Please try again.');
		}
	});
}

function copyCrontab() {
	var content = $('#crontabContent').text();
	navigator.clipboard.writeText(content).then(function() {
		toastSuccess('Crontab copied to clipboard!');
	}).catch(function() {
		// Fallback for older browsers
		var $temp = $('<textarea>');
		$('body').append($temp);
		$temp.val(content).select();
		document.execCommand('copy');
		$temp.remove();
		toastSuccess('Crontab copied to clipboard!');
	});
}

function installCrontab() {
	Swal.fire({
		title: 'Install Crontab?',
		html: 'This will install the cronjob schedule to the server.<br><br><strong>Note:</strong> The web server user must have permission to modify crontab.',
		showDenyButton: true,
		icon: 'question',
		confirmButtonText: 'Yes, install',
		denyButtonText: 'Cancel',
	}).then((result) => {
		if (result.isConfirmed) {
			$.ajax({
				url: '<?=base_url()?>whmazadmin/general_setting/install_crontab',
				type: 'POST',
				data: { <?=$this->security->get_csrf_token_name()?>: '<?=$this->security->get_csrf_hash()?>' },
				dataType: 'json',
				success: function(resp) {
					if (resp.success == 1) {
						Swal.fire('Success!', resp.message, 'success');
					} else {
						Swal.fire('Failed', resp.message || 'Failed to install crontab. Try manual installation.', 'error');
					}
				},
				error: function() {
					Swal.fire('Error', 'Server error. Please try manual installation.', 'error');
				}
			});
		}
	});
}
</script>

<?php $this->load->view('whmazadmin/include/footer');?>
