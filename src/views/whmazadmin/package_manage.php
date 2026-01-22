<?php $this->load->view('whmazadmin/include/header');?>

	 <div class="content content-fluid content-wrapper">
      <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

        <div class="row mt-5">
			<div class="col-md-12 col-sm-12">
				<h3 class="d-flex justify-content-between"><span>Package Pricing</span> <a href="<?=base_url()?>whmazadmin/package/index" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i>&nbsp;Back</a></h3>
				<hr class="mg-5" />
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb breadcrumb-style1 mg-b-0">
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/package/index">Package Pricing</a></li>
						<li class="breadcrumb-item active"><a href="#">Manage package pricing</a></li>
					</ol>
				</nav>
			</div>

			<div class="col-md-12 col-sm-12 mt-5">
				<form method="post" name="entityManageForm" id="entityManageForm" action="<?=base_url()?>whmazadmin/package/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
					<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

					<div class="form-group">
						<label for="product_service_id">Product Service <span class="text-danger">*</span></label>
						<select name="product_service_id" class="form-control" id="product_service_id">
							<option value="">Select Product Service</option>
							<?php foreach($services as $service){ ?>
								<option value="<?= $service['id']?>" <?= (!empty($detail['product_service_id']) && $detail['product_service_id'] == $service['id']) ? 'selected' : ''?>>
									<?= $service['product_name']?>
								</option>
							<?php } ?>
						</select>
						<?php echo form_error('product_service_id', '<div class="error">', '</div>'); ?>
					</div>

					<div class="form-group">
						<label for="currency_id">Currency <span class="text-danger">*</span></label>
						<select name="currency_id" class="form-control" id="currency_id">
							<option value="">Select Currency</option>
							<?php foreach($currencies as $currency){ ?>
								<option value="<?= $currency['id']?>" <?= (!empty($detail['currency_id']) && $detail['currency_id'] == $currency['id']) ? 'selected' : ''?>>
									<?= $currency['symbol'] . ' (' . $currency['code'] . ')'?>
								</option>
							<?php } ?>
						</select>
						<?php echo form_error('currency_id', '<div class="error">', '</div>'); ?>
					</div>

					<div class="form-group">
						<label for="billing_cycle_id">Billing Cycle <span class="text-danger">*</span></label>
						<select name="billing_cycle_id" class="form-control" id="billing_cycle_id">
							<option value="">Select Billing Cycle</option>
							<?php foreach($billing_cycles as $cycle){ ?>
								<option value="<?= $cycle['id']?>" <?= (!empty($detail['billing_cycle_id']) && $detail['billing_cycle_id'] == $cycle['id']) ? 'selected' : ''?>>
									<?= $cycle['cycle_name']?>
								</option>
							<?php } ?>
						</select>
						<?php echo form_error('billing_cycle_id', '<div class="error">', '</div>'); ?>
					</div>

					<div class="form-group">
						<label for="price">Price <span class="text-danger">*</span></label>
						<input name="price" type="text" class="form-control" id="price" value="<?= !empty($detail['price']) ? $detail['price'] : ''?>" placeholder="0.00"/>
						<?php echo form_error('price', '<div class="error">', '</div>'); ?>
					</div>

					<div class="form-group">
						<button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-check-circle"></i>&nbsp;Save</button>
					</div>
				</form>
			</div>
      </div>

    </div><!-- container -->
  </div><!-- content -->

<?php $this->load->view('whmazadmin/include/footer_script');?>
<script>
$(function(){
	'use strict'

	// Show flash messages as toast
	<?php if ($this->session->flashdata('alert_success')) { ?>
		toastSuccess('<?= addslashes($this->session->flashdata('alert_success')) ?>');
	<?php } ?>
	<?php if ($this->session->flashdata('alert_error')) { ?>
		toastError('<?= addslashes($this->session->flashdata('alert_error')) ?>');
	<?php } ?>
});
</script>

<?php $this->load->view('whmazadmin/include/footer');?>
