<?php $this->load->view('whmazadmin/include/header');?>

	 <div class="content content-fluid content-wrapper">
      <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

        <div class="row mt-5">
			<div class="col-md-12 col-sm-12">
				<h3 class="d-flex justify-content-between"><span>Domain Pricing</span> <a href="<?=base_url()?>whmazadmin/domain_pricing/index" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i>&nbsp;Back</a></h3>
				<hr class="mg-5" />
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb breadcrumb-style1 mg-b-0">
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/domain_pricing/index">Domain Pricing</a></li>
						<li class="breadcrumb-item active"><a href="#">Manage domain pricing</a></li>
					</ol>
				</nav>
			</div>

			<div class="col-md-12 col-sm-12 mt-5">
				<form method="post" name="entityManageForm" id="entityManageForm" action="<?=base_url()?>whmazadmin/domain_pricing/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
					<?=csrf_field()?>
					<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="dom_extension_id">Domain Extension <span class="text-danger">*</span></label>
								<select name="dom_extension_id" class="form-control form-select" id="dom_extension_id">
									<option value="">Select Extension</option>
									<?php foreach($extensions as $ext){ ?>
										<option value="<?= htmlspecialchars($ext['id'], ENT_QUOTES, 'UTF-8')?>" <?= (!empty($detail['dom_extension_id']) && $detail['dom_extension_id'] == $ext['id']) ? 'selected' : ''?>>
											<?= htmlspecialchars($ext['extension'], ENT_QUOTES, 'UTF-8')?>
										</option>
									<?php } ?>
								</select>
								<?php echo form_error('dom_extension_id', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<div class="col-md-6">
							<div class="form-group">
								<label for="currency_id">Currency <span class="text-danger">*</span></label>
								<select name="currency_id" class="form-control form-select" id="currency_id">
									<option value="">Select Currency</option>
									<?php foreach($currencies as $currency){ ?>
										<option value="<?= htmlspecialchars($currency['id'], ENT_QUOTES, 'UTF-8')?>" <?= (!empty($detail['currency_id']) && $detail['currency_id'] == $currency['id']) ? 'selected' : ''?>>
											<?= htmlspecialchars($currency['symbol'], ENT_QUOTES, 'UTF-8') . ' (' . htmlspecialchars($currency['code'], ENT_QUOTES, 'UTF-8') . ')'?>
										</option>
									<?php } ?>
								</select>
								<?php echo form_error('currency_id', '<div class="error">', '</div>'); ?>
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-3">
							<div class="form-group">
								<label for="reg_period">Registration Period (Years) <span class="text-danger">*</span></label>
								<input name="reg_period" type="number" class="form-control" id="reg_period" value="<?= htmlspecialchars($detail['reg_period'] ?? '1', ENT_QUOTES, 'UTF-8') ?>" placeholder="1"/>
								<?php echo form_error('reg_period', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<div class="col-md-3">
							<div class="form-group">
								<label for="price">Registration Price <span class="text-danger">*</span></label>
								<input name="price" type="text" class="form-control" id="price" value="<?= htmlspecialchars($detail['price'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="0.00"/>
								<?php echo form_error('price', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<div class="col-md-3">
							<div class="form-group">
								<label for="transfer">Transfer Price <span class="text-danger">*</span></label>
								<input name="transfer" type="text" class="form-control" id="transfer" value="<?= htmlspecialchars($detail['transfer'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="0.00"/>
								<?php echo form_error('transfer', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<div class="col-md-3">
							<div class="form-group">
								<label for="renewal">Renewal Price <span class="text-danger">*</span></label>
								<input name="renewal" type="text" class="form-control" id="renewal" value="<?= htmlspecialchars($detail['renewal'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="0.00"/>
								<?php echo form_error('renewal', '<div class="error">', '</div>'); ?>
							</div>
						</div>
					</div>

					<div class="form-group mt-3">
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
		toastSuccess(<?= json_encode($this->session->flashdata('alert_success')) ?>);
	<?php } ?>
	<?php if ($this->session->flashdata('alert_error')) { ?>
		toastError(<?= json_encode($this->session->flashdata('alert_error')) ?>);
	<?php } ?>
});
</script>

<?php $this->load->view('whmazadmin/include/footer');?>
