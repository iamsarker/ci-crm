<?php $this->load->view('templates/customer/header');?>

<div class="content content-fixed content-auth-alt">
	<div class="auth-login-wrapper">
		<div class="container">
			<div class="login-card">
				<div class="login-header">
					<div class="login-icon">
						<i class="fa fa-sign-in-alt"></i>
					</div>
					<h2>Welcome Back!</h2>
					<p>Sign in to access your account</p>
				</div>

				<div class="login-body">
					<form method="post" action="" class="login-form">
						<input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>" />

						<div class="form-group">
							<label class="form-label">Email Address</label>
							<div class="input-group">
								<span class="input-group-text"><i class="fa fa-envelope"></i></span>
								<input type="email" class="form-control" name="username" placeholder="Enter your email" required>
							</div>
						</div>

						<div class="form-group">
							<label class="form-label">
								Password
								<a href="<?=base_url()?>auth/forgetpaswrd">Forgot password?</a>
							</label>
							<div class="input-group">
								<span class="input-group-text"><i class="fa fa-lock"></i></span>
								<input type="password" class="form-control" name="password" placeholder="Enter your password" required>
							</div>
						</div>

						<button type="submit" class="btn-login">
							<i class="fa fa-sign-in-alt me-2"></i> Sign In
						</button>

						<div class="register-link">
							Don't have an account? <a href="<?=base_url()?>auth/register">Create Account</a>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<?php $this->load->view('templates/customer/footer_script');?>
<?php $this->load->view('templates/customer/footer');?>
