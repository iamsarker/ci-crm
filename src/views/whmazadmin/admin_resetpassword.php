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
						<form method="post" action="<?=base_url('whmazadmin/authenticate/resetpassword/' . $token)?>">
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
							<button class="btn btn-brand-02 btn-block">Reset Password</button>
							<div class="mg-t-10">
								<span class="tx-12 tx-color-03">Back to <a href="<?=base_url()?>whmazadmin/authenticate/login">Login page</a></span>
							</div>
						</form>
					</div>
				</div><!-- sign-wrapper -->
			</div><!-- media -->
		</div><!-- content -->
	</div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script');?>
<?php $this->load->view('whmazadmin/include/footer');?>
