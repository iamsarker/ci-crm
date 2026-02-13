<?php $this->load->view('templates/customer/header');?>

<div class="content content-fixed content-auth-alt">
	<div class="auth-forgot-wrapper">
		<div class="container">
			<div class="forgot-card">
				<div class="forgot-header">
					<div class="forgot-icon">
						<i class="fa fa-lock"></i>
					</div>
					<h2>Set New Password</h2>
					<p>Create a strong password for your account</p>
				</div>

				<div class="forgot-body">
					<form method="post" action="<?=base_url('auth/resetpassword/' . $token)?>" class="login-form">
						<input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>" />

						<div class="form-group">
							<label class="form-label">New Password</label>
							<div class="input-group">
								<span class="input-group-text"><i class="fa fa-key"></i></span>
								<input type="password" class="form-control" name="password" placeholder="Enter new password" required minlength="8">
							</div>
							<div class="password-requirements">
								<i class="fa fa-info-circle"></i> Min 8 characters with uppercase, lowercase & number
							</div>
						</div>

						<div class="form-group">
							<label class="form-label">Confirm Password</label>
							<div class="input-group">
								<span class="input-group-text"><i class="fa fa-check-double"></i></span>
								<input type="password" class="form-control" name="confirm_password" placeholder="Re-enter new password" required minlength="8">
							</div>
						</div>

						<button type="submit" class="btn-forgot">
							<i class="fa fa-save me-2"></i> Reset Password
						</button>

						<div class="register-link">
							<i class="fa fa-arrow-left me-1"></i> Back to <a href="<?=base_url()?>auth/login">Sign In</a>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<?php $this->load->view('templates/customer/footer_script');?>
<?php $this->load->view('templates/customer/footer');?>
