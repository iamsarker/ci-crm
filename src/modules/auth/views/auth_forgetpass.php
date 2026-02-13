<?php $this->load->view('templates/customer/header');?>

<div class="content content-fixed content-auth-alt">
	<div class="auth-forgot-wrapper">
		<div class="container">
			<div class="forgot-card">
				<div class="forgot-header">
					<div class="forgot-icon">
						<i class="fa fa-key"></i>
					</div>
					<h2>Forgot Password?</h2>
					<p>No worries, we'll send you reset instructions</p>
				</div>

				<div class="forgot-body">
					<form method="post" action="" class="login-form">
						<input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>" />

						<div class="form-group">
							<label class="form-label">Email Address</label>
							<div class="input-group">
								<span class="input-group-text"><i class="fa fa-envelope"></i></span>
								<input type="email" class="form-control" name="username" placeholder="Enter your registered email" required>
							</div>
							<small class="text-muted mt-2 d-block">
								<i class="fa fa-info-circle me-1"></i> Enter the email associated with your account
							</small>
						</div>

						<button type="submit" class="btn-forgot">
							<i class="fa fa-paper-plane me-2"></i> Send Reset Link
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
