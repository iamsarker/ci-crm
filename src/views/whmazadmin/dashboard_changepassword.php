<?php $this->load->view('whmazadmin/include/header'); ?>
<link rel="stylesheet" href="<?=base_url()?>resources/assets/css/admin.manage_view.css">

<div class="content content-fluid content-wrapper">
	<div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

		<!-- Page Header -->
		<div class="row mt-4">
			<div class="col-12">
				<div class="company-page-header">
					<div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
						<div>
							<h3><i class="fa fa-key"></i> Change Password</h3>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
									<li class="breadcrumb-item active"><a href="#">Change Password</a></li>
								</ol>
							</nav>
						</div>
						<a href="<?=base_url()?>whmazadmin/dashboard/index" class="btn btn-back">
							<i class="fa fa-arrow-left"></i> Back to Dashboard
						</a>
					</div>
				</div>
			</div>
		</div>

		<!-- Form Section -->
		<div class="row mt-4">
			<div class="col-md-2"></div>
			<div class="col-md-8 col-lg-6">
				<div class="manage-form-card">
					<form method="POST" name="changePasswordForm" id="changePasswordForm" class="company-form" action="<?=base_url()?>whmazadmin/dashboard/changePassword">
						<?=csrf_field()?>

						<!-- Password Update Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-lock"></i> Update Your Password
							</div>

							<div class="form-group">
								<label class="form-label" for="current_password"><i class="fa fa-unlock-alt"></i> Current Password <span class="text-danger">*</span></label>
								<input type="password" name="current_password" id="current_password" class="form-control" required minlength="8" placeholder="Enter current password">
								<?php echo form_error('current_password', '<div class="error">', '</div>'); ?>
							</div>

							<div class="form-group">
								<label class="form-label" for="new_password"><i class="fa fa-key"></i> New Password <span class="text-danger">*</span></label>
								<input type="password" name="new_password" id="new_password" class="form-control" required minlength="8" placeholder="Min. 8 characters">
								<small class="form-text text-muted">Must contain uppercase (A-Z), lowercase (a-z), and number (0-9)</small>
								<?php echo form_error('new_password', '<div class="error">', '</div>'); ?>
							</div>

							<div class="form-group">
								<label class="form-label" for="confirm_password"><i class="fa fa-check-double"></i> Confirm New Password <span class="text-danger">*</span></label>
								<input type="password" name="confirm_password" id="confirm_password" class="form-control" required minlength="8" placeholder="Re-enter new password">
								<?php echo form_error('confirm_password', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<!-- Submit Button -->
						<div class="form-group mt-4">
							<button type="submit" class="btn btn-save-company btn-lg w-100">
								<i class="fa fa-check-circle"></i> Change Password
							</button>
						</div>
					</form>
				</div>
			</div>
			<div class="col-md-2"></div>
		</div>

	</div><!-- container -->
</div><!-- content -->

<?php $this->load->view('whmazadmin/include/footer_script'); ?>
<?php $this->load->view('whmazadmin/include/footer'); ?>
