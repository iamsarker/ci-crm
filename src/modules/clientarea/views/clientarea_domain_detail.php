<?php $this->load->view('templates/customer/header');?>

	 <div class="content content-fixed content-wrapper">
      <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        

        <div class="row">
          <div class="col-md-3 col-sm-12">
            
            <div class="card card-widget card-contacts">
              <div class="card-header">
			    <h6 class="card-title mg-b-0"><i class="fa fa-location-arrow"></i>&nbsp;Nameservers</h6>
                <nav class="nav">

                </nav>
              </div>
              <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between">
					<span>NS1</span><b class="text-secondary float-right"><?= !empty($detail['ns1']) ? $detail['ns1'] : ''?></b>
                </li>
                <li class="list-group-item d-flex justify-content-between">
					<span>NS2</span><b class="text-secondary float-right"><?= !empty($detail['ns2']) ? $detail['ns2'] : ''?></b>
                </li>
                <li class="list-group-item d-flex justify-content-between">
					<span>NS3</span><span class="text-secondary float-right"><?= !empty($detail['ns3']) ? $detail['ns3'] : ''?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
					<span>NS4</span><span class="text-secondary float-right"><?= !empty($detail['ns4']) ? $detail['ns4'] : ''?></span>
                </li>
              </ul>
            </div>

            <?php $this->load->view('templates/customer/domain_nav');?>

        </div>




        <div class="col-md-9 col-sm-12">
			<h3>My Domains</h3>
			<hr class="mg-5" />
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb breadcrumb-style1 mg-b-0">
					<li class="breadcrumb-item"><a href="<?=base_url()?>clientarea">Portal home</a></li>
					<li class="breadcrumb-item"><a href="<?=base_url()?>clientarea/domains">My Domains</a></li>
					<li class="breadcrumb-item active"><a>Domain Detail</a></li>
				</ol>
			</nav>
          <?php if ($this->session->flashdata('alert')) { ?>
            <?= $this->session->flashdata('alert') ?>
          <?php } ?>


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
									<td><h4 class="text-secondary text-right"><?= !empty($detail['domain']) ? $detail['domain'] : ''?></h4></td>
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
									<td><h5 class="text-secondary text-right"><?= !empty($detail['currency_code']) ? $detail['currency_code'] : ''?>&nbsp;<?= !empty($detail['first_pay_amount']) ? $detail['first_pay_amount'] : ''?></h5></td>
								</tr>
								<tr>
									<td><b>Renewal Amount</b></td>
									<td> : </td>
									<td><h5 class="text-secondary text-right"><b><?= !empty($detail['currency_code']) ? $detail['currency_code'] : ''?>&nbsp;<?= !empty($detail['recurring_amount']) ? $detail['recurring_amount'] : ''?></b></h5></td>
								</tr>
								<tr>
									<td><b>Status</b></td>
									<td> : </td>
									<td class="text-right"><?= isset($detail['status']) ? getDomainStatus($detail['status']) : ''?></td>
								</tr>

								</tbody>
							</table>

						</div>
					</div>
				</div>
				<div class="col-6">
					<div class="card">
						<div class="card-header bg-secondary">
							<h4 class="text-white">DNS TYPE</h4>
						</div>
						<div class="card-body">
							<label for="default_ns"><input type="radio" name="dns_type" value="default_ns" id="default_ns" <?= !empty($detail['dns_type']) && $detail['dns_type'] == 'default_ns' ? 'checked' : '' ?> /> Default NS </label>
							<label for="custom_ns"><input type="radio" name="dns_type" value="custom_ns" id="custom_ns" <?= !empty($detail['dns_type']) && $detail['dns_type'] == 'custom_ns' ? 'checked' : '' ?> /> Custom NS </label>
							<label for="records"><input type="radio" name="dns_type" value="records" id="records" <?= !empty($detail['dns_type']) && $detail['dns_type'] == 'records' ? 'checked' : '' ?> /> Records </label>
						</div>
					</div>

					<div class="card mt-2">
						<div class="card-header bg-secondary">
							<h4 class="text-white"><i class="fa fa-pen-square"></i> Update Nameserver</h4>
						</div>
						<div class="card-body">
							<table class="table">
								<tbody>
								<tr>
									<td>DNS-1</td>
									<td><input name="ns1" placeholder="Nameserver 1" id="ns1" value="<?= !empty($detail['ns1']) ? htmlspecialchars($detail['ns1'], ENT_QUOTES, 'UTF-8') : ''?>" type="text" class="form-control" /></td>
								</tr>
								<tr>
									<td>DNS-2</td>
									<td><input name="ns2" placeholder="Nameserver 2" id="ns2" value="<?= !empty($detail['ns2']) ? htmlspecialchars($detail['ns2'], ENT_QUOTES, 'UTF-8') : ''?>" type="text" class="form-control" /></td>
								</tr>
								<tr>
									<td>DNS-3</td>
									<td><input name="ns3" placeholder="Nameserver 3" id="ns3" value="<?= !empty($detail['ns3']) ? htmlspecialchars($detail['ns3'], ENT_QUOTES, 'UTF-8') : ''?>" type="text" class="form-control" /></td>
								</tr>
								<tr>
									<td>DNS-4</td>
									<td><input name="ns4" placeholder="Nameserver 4" id="ns4" value="<?= !empty($detail['ns4']) ? htmlspecialchars($detail['ns4'], ENT_QUOTES, 'UTF-8') : ''?>" type="text" class="form-control" /></td>
								</tr>

								</tbody>
							</table>
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
