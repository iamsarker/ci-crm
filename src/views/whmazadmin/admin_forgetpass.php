<?php $this->load->view('whmazadmin/include/login_header');?>
<link rel="stylesheet" href="<?=base_url()?>resources/assets/css/admin.custom.css">

<div class="admin-auth-wrapper">
	<div class="admin-auth-container admin-auth-container-single">
		<!-- Form Panel Only -->
		<div class="admin-auth-form-panel admin-auth-form-panel-full">
			<?php if ($this->session->flashdata('alert')) { ?>
			<div class="auth-alert-container">
				<?= $this->session->flashdata('alert') ?>
			</div>
			<?php } ?>

			<div class="admin-auth-form-wrapper">
				<div class="auth-icon-header">
					<div class="auth-icon-circle">
						<i class="fas fa-key"></i>
					</div>
				</div>

				<div class="auth-form-header text-center">
					<h3>Forgot Password?</h3>
					<p>No worries! Enter your email and we'll send you a reset link.</p>
				</div>

				<form method="post" action="" class="admin-auth-form">
					<input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>" />

					<div class="auth-form-group">
						<label class="auth-form-label">
							<i class="fas fa-envelope"></i>
							Username or Email Address
						</label>
						<input type="text" name="username" class="auth-form-control" placeholder="Enter your username or email address" required>
					</div>

					<button type="submit" class="auth-submit-btn">
						<i class="fas fa-paper-plane me-2"></i>
						Send Reset Link
					</button>
				</form>

				<div class="auth-form-footer text-center">
					<a href="<?=base_url()?>whmazadmin/authenticate/login">
						<i class="fas fa-arrow-left me-1"></i>
						Back to Login
					</a>
				</div>
			</div>
		</div>
	</div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script');?>
<?php $this->load->view('whmazadmin/include/footer');?>
