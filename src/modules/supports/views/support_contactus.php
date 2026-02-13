<?php $this->load->view('templates/customer/header');?>
<?php if (!empty($captcha_site_key)) { ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php } ?>

<div class="content content-fixed content-auth-alt">
	<div class="auth-register-wrapper">
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-lg-10 col-xl-8">
					<div class="register-card">
						<div class="register-header">
							<div class="register-icon">
								<i class="fa fa-envelope"></i>
							</div>
							<h2>Contact Us</h2>
							<p>Have a question or feedback? We'd love to hear from you!</p>
						</div>

						<div class="register-body">
							<form method="post" action="" class="register-form">
								<input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>" />

								<div class="register-row">
									<!-- Left Column -->
									<div class="register-col">
										<div class="register-section">
											<div class="register-section-title">
												<i class="fa fa-user"></i> Your Information
											</div>

											<div class="form-group">
												<label class="form-label">Full Name <span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-text"><i class="fa fa-user"></i></span>
													<input type="text" class="form-control" name="name" placeholder="Enter your full name" required value="<?= $this->input->post('name') ? htmlspecialchars($this->input->post('name')) : '' ?>">
												</div>
											</div>

											<div class="form-group">
												<label class="form-label">Email Address <span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-text"><i class="fa fa-envelope"></i></span>
													<input type="email" class="form-control" name="email" placeholder="Enter your email address" required value="<?= $this->input->post('email') ? htmlspecialchars($this->input->post('email')) : '' ?>">
												</div>
											</div>

											<div class="form-group">
												<label class="form-label">Subject <span class="required">*</span></label>
												<div class="input-group">
													<span class="input-group-text"><i class="fa fa-tag"></i></span>
													<input type="text" class="form-control" name="subject" placeholder="What is this about?" required value="<?= $this->input->post('subject') ? htmlspecialchars($this->input->post('subject')) : '' ?>">
												</div>
											</div>
										</div>
									</div>

									<!-- Right Column -->
									<div class="register-col">
										<div class="register-section">
											<div class="register-section-title">
												<i class="fa fa-comment-alt"></i> Your Message
											</div>

											<div class="form-group">
												<label class="form-label">Message <span class="required">*</span></label>
												<textarea class="form-control" name="message" rows="6" placeholder="Type your message here..." required style="border-radius: 8px; resize: vertical;"><?= $this->input->post('message') ? htmlspecialchars($this->input->post('message')) : '' ?></textarea>
											</div>

											<?php if (!empty($captcha_site_key)) { ?>
											<div class="recaptcha-wrapper">
												<div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($captcha_site_key, ENT_QUOTES, 'UTF-8') ?>"></div>
											</div>
											<?php } ?>

											<button type="submit" class="btn-register">
												<i class="fa fa-paper-plane"></i> Send Message
											</button>

											<div class="login-link">
												<i class="fa fa-arrow-left me-1"></i> Back to <a href="<?=base_url()?>">Home</a>
											</div>
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>

					<!-- Contact Info Cards -->
					<div class="row mt-4">
						<div class="col-md-4 mb-3">
							<div class="contact-info-card">
								<div class="contact-info-icon">
									<i class="fa fa-map-marker-alt"></i>
								</div>
								<h5>Our Address</h5>
								<p><?= !empty(getAppSettings()->address) ? htmlspecialchars(getAppSettings()->address) : 'Address not available' ?></p>
							</div>
						</div>
						<div class="col-md-4 mb-3">
							<div class="contact-info-card">
								<div class="contact-info-icon">
									<i class="fa fa-phone-alt"></i>
								</div>
								<h5>Phone Number</h5>
								<p><?= !empty(getAppSettings()->phone) ? htmlspecialchars(getAppSettings()->phone) : 'Phone not available' ?></p>
							</div>
						</div>
						<div class="col-md-4 mb-3">
							<div class="contact-info-card">
								<div class="contact-info-icon">
									<i class="fa fa-envelope"></i>
								</div>
								<h5>Email Address</h5>
								<p><?= !empty(getAppSettings()->email) ? htmlspecialchars(getAppSettings()->email) : 'Email not available' ?></p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php $this->load->view('templates/customer/footer_script');?>
<?php $this->load->view('templates/customer/footer');?>
