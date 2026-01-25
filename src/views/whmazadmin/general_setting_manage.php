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

			<div class="col-md-12 col-sm-12 mt-5">
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
										<input name="site_name" type="text" class="form-control" id="site_name" value="<?= !empty($detail['site_name']) ? htmlspecialchars($detail['site_name'], ENT_QUOTES, 'UTF-8') : ''?>" placeholder="Enter site name"/>
										<?php echo form_error('site_name', '<div class="text-danger">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="site_desc">Site Description</label>
										<input name="site_desc" type="text" class="form-control" id="site_desc" value="<?= !empty($detail['site_desc']) ? htmlspecialchars($detail['site_desc'], ENT_QUOTES, 'UTF-8') : ''?>" placeholder="Enter site description"/>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<label for="admin_url">Admin URL</label>
										<input name="admin_url" type="text" class="form-control" id="admin_url" value="<?= !empty($detail['admin_url']) ? htmlspecialchars($detail['admin_url'], ENT_QUOTES, 'UTF-8') : ''?>" placeholder="Enter admin URL"/>
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
										<input name="company_name" type="text" class="form-control" id="company_name" value="<?= !empty($detail['company_name']) ? htmlspecialchars($detail['company_name'], ENT_QUOTES, 'UTF-8') : ''?>" placeholder="Enter company name"/>
										<?php echo form_error('company_name', '<div class="text-danger">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="zip_code">Zip Code</label>
										<input name="zip_code" type="text" class="form-control" id="zip_code" value="<?= !empty($detail['zip_code']) ? htmlspecialchars($detail['zip_code'], ENT_QUOTES, 'UTF-8') : ''?>" placeholder="Enter zip code"/>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<label for="company_address">Company Address</label>
										<textarea name="company_address" class="form-control" id="company_address" rows="3" placeholder="Enter company address"><?= !empty($detail['company_address']) ? htmlspecialchars($detail['company_address'], ENT_QUOTES, 'UTF-8') : ''?></textarea>
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
										<input name="email" type="email" class="form-control" id="email" value="<?= !empty($detail['email']) ? htmlspecialchars($detail['email'], ENT_QUOTES, 'UTF-8') : ''?>" placeholder="Enter email address"/>
										<?php echo form_error('email', '<div class="text-danger">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label for="phone">Phone</label>
										<input name="phone" type="text" class="form-control" id="phone" value="<?= !empty($detail['phone']) ? htmlspecialchars($detail['phone'], ENT_QUOTES, 'UTF-8') : ''?>" placeholder="Enter phone number"/>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label for="fax">Fax</label>
										<input name="fax" type="text" class="form-control" id="fax" value="<?= !empty($detail['fax']) ? htmlspecialchars($detail['fax'], ENT_QUOTES, 'UTF-8') : ''?>" placeholder="Enter fax number"/>
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
										<input name="smtp_host" type="text" class="form-control" id="smtp_host" value="<?= !empty($detail['smtp_host']) ? htmlspecialchars($detail['smtp_host'], ENT_QUOTES, 'UTF-8') : ''?>" placeholder="e.g., smtp.gmail.com"/>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="smtp_port">SMTP Port</label>
										<input name="smtp_port" type="text" class="form-control" id="smtp_port" value="<?= !empty($detail['smtp_port']) ? htmlspecialchars($detail['smtp_port'], ENT_QUOTES, 'UTF-8') : ''?>" placeholder="e.g., 587"/>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label for="smtp_username">SMTP Username</label>
										<input name="smtp_username" type="text" class="form-control" id="smtp_username" value="<?= !empty($detail['smtp_username']) ? htmlspecialchars($detail['smtp_username'], ENT_QUOTES, 'UTF-8') : ''?>" placeholder="SMTP username"/>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="smtp_authkey">SMTP Auth Key / Password</label>
										<input name="smtp_authkey" type="password" class="form-control" id="smtp_authkey" value="<?= !empty($detail['smtp_authkey']) ? htmlspecialchars($detail['smtp_authkey'], ENT_QUOTES, 'UTF-8') : ''?>" placeholder="SMTP password or auth key"/>
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
										<input name="captcha_site_key" type="text" class="form-control" id="captcha_site_key" value="<?= !empty($detail['captcha_site_key']) ? htmlspecialchars($detail['captcha_site_key'], ENT_QUOTES, 'UTF-8') : ''?>" placeholder="Enter site key"/>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="captcha_secret_key">reCAPTCHA Secret Key</label>
										<input name="captcha_secret_key" type="password" class="form-control" id="captcha_secret_key" value="<?= !empty($detail['captcha_secret_key']) ? htmlspecialchars($detail['captcha_secret_key'], ENT_QUOTES, 'UTF-8') : ''?>" placeholder="Enter secret key"/>
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
		</div>

	</div><!-- container -->
</div><!-- content -->

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
</script>

<?php $this->load->view('whmazadmin/include/footer');?>
