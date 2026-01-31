<?php $this->load->view('templates/customer/header');?>

	<div class="content content-fixed content-wrapper">
      <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0 mt-2">
        <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
          <div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb breadcrumb-style1 mg-b-10">
                <li class="breadcrumb-item"><a href="<?=base_url()?>clientarea/index">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Change Password</li>
              </ol>
            </nav>
            <h4 class="mg-b-0 tx-spacing--1">Change Password</h4>
          </div>
        </div>

        <div class="row">
			<div class="col-sm-12 col-md-3 col-lg-3">&nbsp;</div>
			<div class="col-sm-12 col-md-6 col-lg-5">
				<div class="card">
				  <div class="card-header">
					<h6 class="mg-b-0"><i class="fa fa-lock"></i>&nbsp;Update Your Password</h6>
				  </div>
				  <div class="card-body">
					<form method="POST" action="<?=base_url()?>clientarea/changePassword">
					  <?=csrf_field()?>

					  <div class="form-group">
						<label class="tx-10 tx-uppercase tx-medium tx-spacing-1 mg-b-5">Current Password <span class="tx-danger">*</span></label>
						<input type="password" name="current_password" class="form-control" required minlength="8" placeholder="Enter current password">
					  </div>

					  <div class="form-group">
						<label class="tx-10 tx-uppercase tx-medium tx-spacing-1 mg-b-5">New Password <span class="tx-danger">*</span></label>
						<input type="password" name="new_password" class="form-control" required minlength="8" placeholder="Min. 8 characters">
					  </div>

					  <div class="form-group">
						<label class="tx-10 tx-uppercase tx-medium tx-spacing-1 mg-b-5">Confirm New Password <span class="tx-danger">*</span></label>
						<input type="password" name="confirm_password" class="form-control" required minlength="8" placeholder="Re-enter new password">
					  </div>

					  <button type="submit" class="btn btn-primary btn-block">Change Password</button>
					</form>
				  </div>
				</div>
			</div>
			<div class="col-sm-12 col-md-3 col-lg-3">&nbsp;</div>
        </div>

      </div>
    </div>

<?php $this->load->view('templates/customer/footer_script');?>
<?php $this->load->view('templates/customer/footer');?>
