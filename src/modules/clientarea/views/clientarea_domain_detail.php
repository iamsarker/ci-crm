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
					<span>NS1</span><b class="text-secondary float-right"><?= htmlspecialchars($detail['ns1'] ?? '', ENT_QUOTES, 'UTF-8')?></b>
                </li>
                <li class="list-group-item d-flex justify-content-between">
					<span>NS2</span><b class="text-secondary float-right"><?= htmlspecialchars($detail['ns2'] ?? '', ENT_QUOTES, 'UTF-8')?></b>
                </li>
                <li class="list-group-item d-flex justify-content-between">
					<span>NS3</span><span class="text-secondary float-right"><?= htmlspecialchars($detail['ns3'] ?? '', ENT_QUOTES, 'UTF-8')?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
					<span>NS4</span><span class="text-secondary float-right"><?= htmlspecialchars($detail['ns4'] ?? '', ENT_QUOTES, 'UTF-8')?></span>
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
									<td><h4 class="text-secondary text-right"><?= htmlspecialchars($detail['domain'] ?? '', ENT_QUOTES, 'UTF-8')?></h4></td>
								</tr>
								<tr>
									<td>Registration</td>
									<td> : </td>
									<td><h5 class="text-secondary text-right"><?= htmlspecialchars($detail['reg_date'] ?? '', ENT_QUOTES, 'UTF-8')?></h5></td>
								</tr>
								<tr>
									<td>Expiry</td>
									<td> : </td>
									<td><h5 class="text-secondary text-right"><?= htmlspecialchars($detail['exp_date'] ?? '', ENT_QUOTES, 'UTF-8')?></h5></td>
								</tr>
								<tr>
									<td><b>Next Renewal</b></td>
									<td> : </td>
									<td><h5 class="text-secondary text-right"><b><?= htmlspecialchars($detail['next_due_date'] ?? '', ENT_QUOTES, 'UTF-8')?></b></h5></td>
								</tr>
								<tr>
									<td>Registration Amount</td>
									<td> : </td>
									<td><h5 class="text-secondary text-right"><?= htmlspecialchars($detail['currency_code'] ?? '', ENT_QUOTES, 'UTF-8')?>&nbsp;<?= htmlspecialchars($detail['first_pay_amount'] ?? '', ENT_QUOTES, 'UTF-8')?></h5></td>
								</tr>
								<tr>
									<td><b>Renewal Amount</b></td>
									<td> : </td>
									<td><h5 class="text-secondary text-right"><b><?= htmlspecialchars($detail['currency_code'] ?? '', ENT_QUOTES, 'UTF-8')?>&nbsp;<?= htmlspecialchars($detail['recurring_amount'] ?? '', ENT_QUOTES, 'UTF-8')?></b></h5></td>
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
									<td><input name="ns1" placeholder="Nameserver 1" id="ns1" value="<?= htmlspecialchars($detail['ns1'] ?? '', ENT_QUOTES, 'UTF-8') ?>" type="text" class="form-control" /></td>
								</tr>
								<tr>
									<td>DNS-2</td>
									<td><input name="ns2" placeholder="Nameserver 2" id="ns2" value="<?= htmlspecialchars($detail['ns2'] ?? '', ENT_QUOTES, 'UTF-8') ?>" type="text" class="form-control" /></td>
								</tr>
								<tr>
									<td>DNS-3</td>
									<td><input name="ns3" placeholder="Nameserver 3" id="ns3" value="<?= htmlspecialchars($detail['ns3'] ?? '', ENT_QUOTES, 'UTF-8') ?>" type="text" class="form-control" /></td>
								</tr>
								<tr>
									<td>DNS-4</td>
									<td><input name="ns4" placeholder="Nameserver 4" id="ns4" value="<?= htmlspecialchars($detail['ns4'] ?? '', ENT_QUOTES, 'UTF-8') ?>" type="text" class="form-control" /></td>
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
<script>
	<?php if ($this->session->flashdata('alert_success')) { ?>
		toastSuccess(<?= json_encode($this->session->flashdata('alert_success')) ?>);
	<?php } ?>
	<?php if ($this->session->flashdata('alert_error')) { ?>
		toastError(<?= json_encode($this->session->flashdata('alert_error')) ?>);
	<?php } ?>
</script>
<?php $this->load->view('templates/customer/footer');?>
