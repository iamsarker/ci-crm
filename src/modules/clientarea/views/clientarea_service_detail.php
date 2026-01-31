<?php $this->load->view('templates/customer/header');?>

	 <div class="content content-fixed content-wrapper" >
      <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        

        <div class="row">
          <div class="col-md-3 col-sm-12">
            
            <div class="card card-widget card-contacts">
              <div class="card-header">
			    <h6 class="card-title mg-b-0"><i class="fa fa-server"></i>&nbsp;Server DNS</h6>
                <nav class="nav">

                </nav>
              </div>
              <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between">
					<span>NS1</span><b class="text-secondary float-right"><?= !empty($dns['dns1']) ? $dns['dns1'] : ''?></b>
                </li>
                <li class="list-group-item d-flex justify-content-between">
					<span>NS2</span><b class="text-secondary float-right"><?= !empty($dns['dns2']) ? $dns['dns2'] : ''?></b>
                </li>
                <li class="list-group-item d-flex justify-content-between">
					<span>NS3</span><span class="text-secondary float-right"><?= !empty($dns['dns3']) ? $dns['dns3'] : ''?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
					<span>NS4</span><span class="text-secondary float-right"><?= !empty($dns['dns4']) ? $dns['dns4'] : ''?></span>
                </li>
				  <li class="list-group-item d-flex justify-content-between bg-gray-1">
					  <b>Primary IP</b><b class="text-secondary float-right"><?= !empty($dns['primar_ip']) ? $dns['primar_ip'] : ''?></b>
				  </li>
              </ul>
            </div>

            <?php $this->load->view('templates/customer/service_nav');?>

        </div>




        <div class="col-md-9 col-sm-12">
			<h3>My Services</h3>
			<hr class="mg-5" />
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb breadcrumb-style1 mg-b-0">
					<li class="breadcrumb-item"><a href="<?=base_url()?>clientarea">Portal home</a></li>
					<li class="breadcrumb-item"><a href="<?=base_url()?>clientarea/services">My Services</a></li>
					<li class="breadcrumb-item active"><a>Service Detail</a></li>
				</ol>
			</nav>


			<div class="row mt-4">
				<div class="col-6">
					<div class="card" >
						<div class="card-header bg-secondary">
							<h4 class="text-white">Order Details</h4>
						</div>
						<div class="card-body">
							<table class="table">
								<tbody>
								<tr>
									<td>Domain</td>
									<td> : </td>
									<td><h4 class="text-secondary text-right"><?= !empty($detail['hosting_domain']) ? $detail['hosting_domain'] : ''?></h4></td>
								</tr>
								<tr>
									<td>Registration</td>
									<td> : </td>
									<td><h5 class="text-secondary text-right"><?= !empty($detail['reg_date']) ? $detail['reg_date'] : ''?></h5></td>
								</tr>
								<tr>
									<td>Expiry</td>
									<td> : </td>
									<td><h5 class="text-secondary text-right"><?= !empty($detail['exp_date']) ? $detail['exp_date'] : ''?></h5></td>
								</tr>
								<tr>
									<td><b>Next Renewal</b></td>
									<td> : </td>
									<td><h5 class="text-secondary text-right"><b><?= !empty($detail['next_due_date']) ? $detail['next_due_date'] : ''?></b></h5></td>
								</tr>
								<tr>
									<td>Registration Amount</td>
									<td> : </td>
									<td><h5 class="text-secondary text-right"><?= !empty($detail['next_due_date']) ? $detail['currency_code'] : ''?>&nbsp;<?= !empty($detail['next_due_date']) ? $detail['first_pay_amount'] : ''?></h5></td>
								</tr>
								<tr>
									<td><b>Renewal Amount</b></td>
									<td> : </td>
									<td><h5 class="text-secondary text-right"><b><?= !empty($detail['next_due_date']) ? $detail['currency_code'] : ''?>&nbsp;<?= !empty($detail['next_due_date']) ? $detail['recurring_amount'] : ''?></b></h5></td>
								</tr>
								<tr>
									<td><b>Status</b></td>
									<td> : </td>
									<td class="text-right"><?= isset($detail['status']) ? getServiceStatus($detail['status']) : ''?></td>
								</tr>
								<tr><td colspan="3">&nbsp;</td></tr>

								<tr>
									<td colspan="3" class="text-secondary"><?= !empty($detail['description']) ? $detail['description'] : ''?></td>
								</tr>

								</tbody>
							</table>

						</div>
					</div>
				</div>
				<div class="col-6">
					<div class="card">
						<div class="card-header bg-secondary">
							<h4 class="text-white">Package/Usage Info</h4>
						</div>
						<div class="card-body">
							<table class="table">
								<tbody>
								<tr>
									<td>Bandwidth</td>
									<td> : </td>
									<td><h5 class="text-secondary text-right">5000 MB / 15000 MB</h5></td>
								</tr>
								<tr>
									<td>Disk Space</td>
									<td> : </td>
									<td><h5 class="text-secondary text-right">300 MB / 5000 MB</td>
								</tr>
								<tr>
									<td>Emails</td>
									<td> : </td>
									<td><h5 class="text-secondary text-right">10 / Unlimited</td>
								</tr>
								<tr>
									<td>Database</td>
									<td> : </td>
									<td><h5 class="text-secondary text-right">3 / Unlimited</td>
								</tr>
								<tr>
									<td>Domains</td>
									<td> : </td>
									<td><h5 class="text-secondary text-right">3 / Unlimited</td>
								</tr>
								</tbody>
							</table>
						</div>
					</div>

					<div class="card mt-4">
						<div class="card-header bg-secondary">
							<h4 class="text-white">Instructions</h4>
						</div>
						<div class="card-body">
							<p class="card-text"><?= !empty($detail['instructions']) ? $detail['instructions'] : ''?></p>
						</div>
					</div>

				</div>
			</div>

        </div>
      </div>
		
    </div><!-- container -->
  </div><!-- content -->

<?php $this->load->view('templates/customer/footer_script');?>
<?php $this->load->view('templates/customer/footer');?>
