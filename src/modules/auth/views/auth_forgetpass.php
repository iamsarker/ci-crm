<?php $this->load->view('templates/customer/header');?>

<div class="content content-fixed content-auth-alt content-wrapper">
      <div class="container d-flex justify-content-center ht-100p">
		<div class="mx-wd-300 wd-sm-450 ht-100p d-flex flex-column align-items-center justify-content-center">
		  <div class="sign-wrapper mg-lg-l-50 mg-xl-l-60">
			<div class="wd-200p">
				<form method="post" action="">
					<input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>" />
					<h3 class="tx-color-01 mg-b-5">Reset your password</h3>
					<p class="tx-color-03 tx-16 mg-b-40">Do you forget your password? </p>

					<div class="wd-200p d-flex flex-column flex-sm-row">
						<input type="text"  name="username" class="form-control wd-sm-250 flex-fill" placeholder="Enter username/email address">
						<button class="btn btn-primary ms-1">Reset Password</button>
					</div>
					<span class="tx-12 tx-color-03">Back to <a href="<?=base_url()?>auth/login">Login page</a></span>
				</form>
			</div>
		  </div><!-- sign-wrapper -->
		</div><!-- media -->

	</div>
</div>

<?php $this->load->view('templates/customer/footer_script');?>
<script>
	<?php if ($this->session->flashdata('alert_success')) { ?>
		toastSuccess(<?= json_encode($this->session->flashdata('alert_success')) ?>);
	<?php } ?>
	<?php if ($this->session->flashdata('alert_error')) { ?>
		toastError(<?= json_encode($this->session->flashdata('alert_error')) ?>);
	<?php } ?>
</script>
<?php $this->load->view('templates/customer/footer');?>
