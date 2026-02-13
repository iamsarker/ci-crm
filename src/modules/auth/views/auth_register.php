<?php $this->load->view('templates/customer/header');?>

<?php if (!empty($captcha_site_key)) { ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php } ?>

<div class="content content-fixed content-auth-alt">
	<div class="auth-register-wrapper">
		<div class="container">
			<div class="register-card">
				<div class="register-header">
					<div class="register-icon">
						<i class="fa fa-user-plus"></i>
					</div>
					<h2>Create Your Account</h2>
					<p>Join us today! Registration is free and only takes a minute.</p>
				</div>

				<div class="register-body">
					<form method="post" action="" class="register-form">
						<input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>" />

						<div class="register-row">
							<!-- Left Column - Personal Information -->
							<div class="register-col">
								<div class="register-section">
									<div class="register-section-title">
										<i class="fa fa-user"></i> Personal Information
									</div>

									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label class="form-label">First Name <span class="required">*</span></label>
												<input type="text" class="form-control" name="reg[first_name]" placeholder="Enter first name" required>
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label class="form-label">Last Name <span class="required">*</span></label>
												<input type="text" class="form-control" name="reg[last_name]" placeholder="Enter last name" required>
											</div>
										</div>
									</div>

									<div class="form-group">
										<label class="form-label">Email Address <span class="required">*</span></label>
										<div class="input-group">
											<span class="input-group-text"><i class="fa fa-envelope"></i></span>
											<input type="email" class="form-control" name="reg[email]" placeholder="example@email.com" required>
										</div>
									</div>

									<div class="form-group">
										<label class="form-label">Mobile Number <span class="required">*</span></label>
										<div class="input-group">
											<span class="input-group-text"><i class="fa fa-phone"></i></span>
											<input type="tel" class="form-control" name="reg[mobile]" placeholder="+1 234 567 8900" required>
										</div>
									</div>
								</div>

								<div class="register-section">
									<div class="register-section-title">
										<i class="fa fa-lock"></i> Account Security
									</div>

									<div class="form-group">
										<label class="form-label">Password <span class="required">*</span></label>
										<div class="input-group">
											<span class="input-group-text"><i class="fa fa-key"></i></span>
											<input type="password" class="form-control" name="reg[password]" placeholder="Create a strong password" required minlength="8">
										</div>
										<div class="password-requirements">
											<i class="fa fa-info-circle"></i> Min 8 characters with uppercase, lowercase & number
										</div>
									</div>

									<div class="form-group">
										<label class="form-label">Confirm Password <span class="required">*</span></label>
										<div class="input-group">
											<span class="input-group-text"><i class="fa fa-check-double"></i></span>
											<input type="password" class="form-control" name="reg[confirm_password]" placeholder="Re-enter your password" required minlength="8">
										</div>
									</div>
								</div>
							</div>

							<!-- Right Column - Address Information -->
							<div class="register-col">
								<div class="register-section">
									<div class="register-section-title">
										<i class="fa fa-map-marker-alt"></i> Address Information
									</div>

									<div class="form-group">
										<label class="form-label">Street Address</label>
										<input type="text" class="form-control" name="reg[address]" placeholder="Enter your street address">
									</div>

									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label class="form-label">City</label>
												<input type="text" class="form-control" name="reg[city]" placeholder="City">
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label class="form-label">ZIP / Postal Code</label>
												<input type="text" class="form-control" name="reg[zip_code]" placeholder="ZIP Code">
											</div>
										</div>
									</div>

									<div class="form-group">
										<label class="form-label">State / Province</label>
										<input type="text" class="form-control" name="reg[state]" placeholder="State or Province">
									</div>

									<div class="form-group">
										<label class="form-label">Country</label>
										<select class="form-control form-select" name="reg[country]">
											<option value="">-- Select Country --</option>
											<?php if (!empty($countries)) { foreach ($countries as $country) { ?>
												<option value="<?= htmlspecialchars($country['country_name'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($country['country_name'], ENT_QUOTES, 'UTF-8') ?></option>
											<?php } } ?>
										</select>
									</div>
								</div>

								<div class="register-section">
									<div class="terms-checkbox">
										<input type="checkbox" id="termsAgree" name="terms" required>
										<label for="termsAgree">
											By creating an account, you agree to our
											<a href="<?=base_url()?>pages/terms-and-conditions" target="_blank">Terms of Service</a>
											and
											<a href="<?=base_url()?>pages/privacy-policy" target="_blank">Privacy Policy</a>.
										</label>
									</div>

									<?php if (!empty($captcha_site_key)) { ?>
									<div class="recaptcha-wrapper">
										<div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($captcha_site_key, ENT_QUOTES, 'UTF-8') ?>"></div>
									</div>
									<?php } ?>

									<button type="submit" class="btn-register">
										<i class="fa fa-user-plus"></i> Create Account
									</button>

									<div class="login-link">
										Already have an account? <a href="<?=base_url()?>auth/login">Sign In</a>
									</div>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<?php $this->load->view('templates/customer/footer_script');?>
<?php $this->load->view('templates/customer/footer');?>
