<?php $this->load->view('templates/customer/header');?>

<div class="content content-fixed content-profile content-wrapper">
      <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
      	<?php if ($this->session->flashdata('alert')) { ?>
    	<?= $this->session->flashdata('alert') ?>
		<?php } ?>
		<div class="content content-fixed content-auth" style="margin-top: 15px">
	      <div class="container">
	        <div class="media align-items-stretch justify-content-center ht-100p pos-relative">
	          <div class="sign-wrapper mg-lg-l-50 mg-xl-l-60">
	            <div class="wd-100p">
	            	<form method="post" action="">
		            	<h3 class="tx-color-01 mg-b-5">Sign In</h3>
			            <p class="tx-color-03 tx-16 mg-b-40">Welcome back! Please signin to continue.</p>

			            <div class="form-group">
			                <label>Email address</label>
			                <input type="email" class="form-control" name="username" placeholder="example@whmaz.com">
			            </div>
			            <div class="form-group">
			                <div class="d-flex justify-content-between mg-b-5">
			                	<label class="mg-b-0-f">Password</label>
			                	<a href="<?=base_url()?>auth/forgetpaswrd" class="tx-13">Forgot password?</a>
			                </div>
			                <input type="password" class="form-control" name="password" placeholder="Enter your password">
			            </div>
			            <button class="btn btn-brand-02 btn-block">Sign In</button>
			            <div class="tx-13 mg-t-20 tx-center">Don't have an account? <a href="<?=base_url()?>auth/register">Create an Account</a></div>
		            </form>
	            </div>
	          </div><!-- sign-wrapper -->
	        </div><!-- media -->
	      </div><!-- container -->
	    </div><!-- content -->
	</div>
</div>

<?php $this->load->view('templates/customer/footer');?>
<?php $this->load->view('templates/customer/footer_script');?>
