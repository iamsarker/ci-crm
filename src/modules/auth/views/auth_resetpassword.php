<?php $this->load->view('templates/customer/header');?>

<div class="content content-fixed content-auth-alt content-wrapper">
      <div class="container d-flex justify-content-center ht-100p">
		<div class="mx-wd-300 wd-sm-450 ht-100p d-flex flex-column align-items-center justify-content-center">
		  <div class="sign-wrapper mg-lg-l-50 mg-xl-l-60">
			<div class="wd-200p">
				<form method="post" action="<?=base_url('auth/resetpassword/' . $token)?>">
					<input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>" />
					<h3 class="tx-color-01 mg-b-5">Set New Password</h3>
					<p class="tx-color-03 tx-16 mg-b-40">Enter your new password below.</p>

					<div class="form-group mg-b-20">
						<label>New Password</label>
						<input type="password" name="password" class="form-control" placeholder="Enter new password" required minlength="8">
					</div>
					<div class="form-group mg-b-20">
						<label>Confirm Password</label>
						<input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password" required minlength="8">
					</div>
					<button class="btn btn-primary btn-block">Reset Password</button>
					<div class="mg-t-10">
						<span class="tx-12 tx-color-03">Back to <a href="<?=base_url()?>auth/login">Login page</a></span>
					</div>
				</form>
			</div>
		  </div><!-- sign-wrapper -->
		</div><!-- media -->

	</div>
</div>

<?php $this->load->view('templates/customer/footer_script');?>
<?php $this->load->view('templates/customer/footer');?>
