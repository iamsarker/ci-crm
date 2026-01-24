<?php $this->load->view('templates/customer/header');?>

<div class="content content-fixed content-auth-alt content-wrapper">
      <div class="container d-flex justify-content-center ht-100p">
      	<?php if ($this->session->flashdata('alert')) { ?>
    	<?= $this->session->flashdata('alert') ?>
		<?php } ?>
		<div class="mx-wd-300 wd-sm-450 mg-t-100 mg-b-150 ht-100p d-flex flex-column align-items-center justify-content-center">
		  <div class="sign-wrapper mg-lg-l-50 mg-xl-l-60">
			<div class="wd-100p">
				<form method="post" action="">
					<?=csrf_field()?>
					<h3 class="tx-color-01 mg-b-5">Reset your password</h3>
					<p class="tx-color-03 tx-16 mg-b-40">Do you forget your password? </p>

					<div class="wd-100p d-flex flex-column flex-sm-row mg-b-40">
						<input type="text"  name="username" class="form-control wd-sm-250 flex-fill" placeholder="Enter username/email address">
						<button class="btn btn-brand-02 mg-sm-l-10 mg-t-10 mg-sm-t-0">Reset Password</button>
					</div>
					<span class="tx-12 tx-color-03">Back to <a href="<?=base_url()?>auth/login">Login page</a></span>
				</form>
			</div>
		  </div><!-- sign-wrapper -->
		</div><!-- media -->

	</div>
</div>

<?php $this->load->view('templates/customer/footer_script');?>
<?php $this->load->view('templates/customer/footer');?>
