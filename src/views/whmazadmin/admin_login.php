<?php $this->load->view('whmazadmin/include/login_header');?>
<?php if (!empty($captcha_site_key)) { ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php } ?>
<link rel="stylesheet" href="<?=base_url()?>resources/assets/css/admin.custom.css">

<div class="admin-auth-wrapper">
	<div class="admin-auth-container">
		<!-- Left Panel - Branding -->
		<div class="admin-auth-branding">
			<div class="branding-content">
				<div class="brand-logo">
					<i class="fas fa-shield-alt"></i>
				</div>
				<h2>Admin Portal</h2>
				<p>Secure access to your management dashboard</p>
				<div class="branding-features">
					<div class="feature-item">
						<i class="fas fa-check-circle"></i>
						<span>Manage customers & orders</span>
					</div>
					<div class="feature-item">
						<i class="fas fa-check-circle"></i>
						<span>Monitor billing & invoices</span>
					</div>
					<div class="feature-item">
						<i class="fas fa-check-circle"></i>
						<span>Handle support tickets</span>
					</div>
				</div>
			</div>
			<div class="branding-footer">
				<small>&copy; <?= date('Y') ?> <?= $company_name ?? 'WHMAZ CRM' ?></small>
			</div>
		</div>

		<!-- Right Panel - Login Form -->
		<div class="admin-auth-form-panel">
			<?php if ($this->session->flashdata('alert')) { ?>
			<div class="auth-alert-container">
				<?= $this->session->flashdata('alert') ?>
			</div>
			<?php } ?>

			<div class="admin-auth-form-wrapper">
				<div class="auth-form-header">
					<h3>Welcome Back</h3>
					<p>Sign in to your admin account</p>
				</div>

				<form method="post" action="" class="admin-auth-form">
					<input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>" />

					<div class="auth-form-group">
						<label class="auth-form-label">
							<i class="fas fa-user"></i>
							Username or Email
						</label>
						<input type="text" class="auth-form-control" name="username" placeholder="Enter your username or email" required>
					</div>

					<div class="auth-form-group">
						<div class="d-flex justify-content-between align-items-center mb-2">
							<label class="auth-form-label mb-0">
								<i class="fas fa-lock"></i>
								Password
							</label>
							<a href="<?=base_url()?>whmazadmin/authenticate/forgetpaswrd" class="auth-forgot-link">Forgot password?</a>
						</div>
						<div class="password-input-wrapper">
							<input type="password" class="auth-form-control" name="password" id="password" placeholder="Enter your password" required>
							<button type="button" class="password-toggle" onclick="togglePassword()">
								<i class="fas fa-eye" id="toggleIcon"></i>
							</button>
						</div>
					</div>

					<?php if (!empty($captcha_site_key)) { ?>
					<div class="auth-form-group">
						<div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($captcha_site_key, ENT_QUOTES, 'UTF-8') ?>"></div>
					</div>
					<?php } ?>

					<button type="submit" class="auth-submit-btn">
						<i class="fas fa-sign-in-alt me-2"></i>
						Sign In
					</button>
				</form>

				<div class="auth-form-footer">
					<a href="<?=base_url()?>">
						<i class="fas fa-arrow-left me-1"></i>
						Back to Website
					</a>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
function togglePassword() {
	const passwordInput = document.getElementById('password');
	const toggleIcon = document.getElementById('toggleIcon');

	if (passwordInput.type === 'password') {
		passwordInput.type = 'text';
		toggleIcon.classList.remove('fa-eye');
		toggleIcon.classList.add('fa-eye-slash');
	} else {
		passwordInput.type = 'password';
		toggleIcon.classList.remove('fa-eye-slash');
		toggleIcon.classList.add('fa-eye');
	}
}
</script>

<?php $this->load->view('whmazadmin/include/footer_script');?>
<?php $this->load->view('whmazadmin/include/footer');?>
