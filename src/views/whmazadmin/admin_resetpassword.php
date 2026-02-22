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
					<div class="auth-icon-circle auth-icon-success">
						<i class="fas fa-lock"></i>
					</div>
				</div>

				<div class="auth-form-header text-center">
					<h3>Set New Password</h3>
					<p>Create a strong password for your account</p>
				</div>

				<form method="post" action="<?=base_url('whmazadmin/authenticate/resetpassword/' . $token)?>" class="admin-auth-form">
					<input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>" />

					<div class="auth-form-group">
						<label class="auth-form-label">
							<i class="fas fa-lock"></i>
							New Password
						</label>
						<div class="password-input-wrapper">
							<input type="password" name="password" class="auth-form-control" id="password" placeholder="Enter new password" required minlength="8">
							<button type="button" class="password-toggle" onclick="togglePassword('password', 'toggleIcon1')">
								<i class="fas fa-eye" id="toggleIcon1"></i>
							</button>
						</div>
						<div class="password-strength-indicator">
							<div class="strength-bar" id="strengthBar"></div>
						</div>
						<small class="auth-form-hint">Minimum 8 characters</small>
					</div>

					<div class="auth-form-group">
						<label class="auth-form-label">
							<i class="fas fa-check-circle"></i>
							Confirm Password
						</label>
						<div class="password-input-wrapper">
							<input type="password" name="confirm_password" class="auth-form-control" id="confirm_password" placeholder="Confirm new password" required minlength="8">
							<button type="button" class="password-toggle" onclick="togglePassword('confirm_password', 'toggleIcon2')">
								<i class="fas fa-eye" id="toggleIcon2"></i>
							</button>
						</div>
						<small class="password-match-indicator" id="matchIndicator"></small>
					</div>

					<button type="submit" class="auth-submit-btn">
						<i class="fas fa-save me-2"></i>
						Reset Password
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

<script>
function togglePassword(inputId, iconId) {
	const passwordInput = document.getElementById(inputId);
	const toggleIcon = document.getElementById(iconId);

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

// Password strength indicator
document.getElementById('password').addEventListener('input', function() {
	const password = this.value;
	const strengthBar = document.getElementById('strengthBar');
	let strength = 0;

	if (password.length >= 8) strength += 25;
	if (password.match(/[a-z]/)) strength += 25;
	if (password.match(/[A-Z]/)) strength += 25;
	if (password.match(/[0-9!@#$%^&*]/)) strength += 25;

	strengthBar.style.width = strength + '%';

	if (strength <= 25) {
		strengthBar.style.background = '#dc3545';
	} else if (strength <= 50) {
		strengthBar.style.background = '#ffc107';
	} else if (strength <= 75) {
		strengthBar.style.background = '#17a2b8';
	} else {
		strengthBar.style.background = '#28a745';
	}

	checkPasswordMatch();
});

// Password match indicator
document.getElementById('confirm_password').addEventListener('input', checkPasswordMatch);

function checkPasswordMatch() {
	const password = document.getElementById('password').value;
	const confirmPassword = document.getElementById('confirm_password').value;
	const matchIndicator = document.getElementById('matchIndicator');

	if (confirmPassword.length === 0) {
		matchIndicator.textContent = '';
		matchIndicator.className = 'password-match-indicator';
	} else if (password === confirmPassword) {
		matchIndicator.textContent = 'Passwords match';
		matchIndicator.className = 'password-match-indicator match-success';
	} else {
		matchIndicator.textContent = 'Passwords do not match';
		matchIndicator.className = 'password-match-indicator match-error';
	}
}
</script>

<?php $this->load->view('whmazadmin/include/footer_script');?>
<?php $this->load->view('whmazadmin/include/footer');?>
