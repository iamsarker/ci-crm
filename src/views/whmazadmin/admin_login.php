<?php $this->load->view('whmazadmin/include/login_header');?>

<div class="content content-fixed content-profile content-wrapper">
      <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
      	<?php if ($this->session->flashdata('alert')) { ?>
    	<?= $this->session->flashdata('alert') ?>
		<?php } ?>
		<div class="content-auth mt-4">
			<div class="media align-items-stretch justify-content-center ht-100p pos-relative">
			  <div class="sign-wrapper mg-lg-l-50 mg-xl-l-60">
				<div class="wd-400">
					<form method="post" action="">
						<h3 class="tx-color-01 mg-b-5">Sign In</h3>
						<p class="tx-color-03 tx-16 mg-b-40">Welcome back! Please signin to continue.</p>

						<div class="form-group">
							<label>Username/Email</label>
							<input type="text" class="form-control" name="username" placeholder="Username/Email">
						</div>
						<div class="form-group">
							<div class="d-flex justify-content-between mg-b-5">
								<label class="mg-b-0-f">Password</label>
								<a href="<?=base_url()?>whmazadmin/authenticate/forgetpaswrd" class="tx-13">Forgot password?</a>
							</div>
							<input type="password" class="form-control" name="password" placeholder="Enter your password">
						</div>
						<button class="btn btn-brand-02 btn-block">Sign In</button>
					</form>
				</div>
			  </div><!-- sign-wrapper -->
			</div><!-- media -->
	    </div><!-- content -->
	</div>
</div>

<?php $this->load->view('whmazadmin/include/footer');?>
<?php $this->load->view('whmazadmin/include/footer_script');?>
