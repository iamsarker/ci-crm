<?php $this->load->view('templates/customer/header');?>
<?php if (!empty($captcha_site_key)) { ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php } ?>

<div class="content content-fixed content-profile content-wrapper">
      <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">

		<div class="content content-fixed content-auth mt-0">
	      <div class="container">

	      	<?php if ($this->session->flashdata('alert')) { ?>
        	<?= $this->session->flashdata('alert') ?>
    		<?php } ?>

	      	<form method="post" action="">
	      		<input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>" />
	        <div class="media align-items-stretch justify-content-center ht-100p pos-relative">
	          	<div class="sign-wrapper mg-lg-l-50 mg-xl-l-60">
		            <div class="wd-100p">

		            	<h4 class="tx-color-01 mg-b-5">Create new account</h4>
		            	<p class="tx-color-03 tx-16 mg-b-20">It's free to signup and only takes a minute.</p>

		            	<div class="form-group">
			                <label>First name *</label>
			                <input type="text" class="form-control" name="reg[first_name]" placeholder="First name">
			            </div>

			            <div class="form-group">
			                <label>Last name *</label>
			                <input type="text" class="form-control" name="reg[last_name]" placeholder="Last name">
			            </div>

			            <div class="form-group">
			                <label>Email *</label>
			                <input type="email" class="form-control" name="reg[email]" placeholder="example@whmaz.com">
			            </div>

			            <div class="form-group">
			                <label>Mobile no *</label>
			                <input type="tel" class="form-control" name="reg[mobile]" placeholder="Mobile no">
			            </div>

			            <div class="form-group">
			                <label>Password *</label>
			                <input type="password" class="form-control" name="reg[password]" placeholder="Password">
			            </div>

			            <div class="form-group">
			                <label>Re-type Password *</label>
			                <input type="password" class="form-control" name="reg[password]" placeholder="Retype Password">
			            </div>
			            
		            </div>
	          	</div>

	          	<div class="sign-wrapper mg-lg-l-50 mg-xl-l-60">
		            <div class="wd-100p">

		            	<div class="form-group">
			                <label>Address</label>
			                <input type="text" class="form-control" name="reg[address]" placeholder="Address">
			            </div>

			            <div class="form-group">
			                <label>City</label>
			                <input type="text" class="form-control" name="reg[city]" placeholder="City">
			            </div>

			            <div class="form-group">
			                <label>Zip cide</label>
			                <input type="text" class="form-control" name="reg[zip_code]" placeholder="Zip cide">
			            </div>

			            <div class="form-group">
			                <label>State</label>
			                <input type="text" class="form-control" name="reg[state]" placeholder="State">
			            </div>
			            
			            <div class="form-group">
			                <label>Country</label>
			                <select class="form-control form-select" name="reg[country]">
			                	<option value="">-- Select Country --</option>
			                	<?php if (!empty($countries)) { foreach ($countries as $country) { ?>
			                		<option value="<?= htmlspecialchars($country['country_name'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($country['country_name'], ENT_QUOTES, 'UTF-8') ?></option>
			                	<?php } } ?>
			                </select>
			            </div>

			            <div class="form-group tx-12">
                			By clicking <strong>Create an account</strong> below, you agree to our <br />terms of service and privacy statement.
              			</div>

			            <?php if (!empty($captcha_site_key)) { ?>
			            <div class="form-group">
			                <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($captcha_site_key, ENT_QUOTES, 'UTF-8') ?>"></div>
			            </div>
			            <?php } ?>

			            <button class="btn btn-brand-02 btn-block">Sign Up</button>
			            <div class="tx-13 mg-t-20 tx-center">Already have an account? <a href="<?=base_url()?>auth/login">Login now</a></div>
		            </div>
	          	</div>
	        </div>
	    	</form>
	      </div><!-- container -->
	    </div><!-- content -->
	</div>
</div>

<?php $this->load->view('templates/customer/footer_script');?>
<?php $this->load->view('templates/customer/footer');?>
