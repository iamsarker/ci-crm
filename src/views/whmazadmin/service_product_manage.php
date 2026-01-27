<?php $this->load->view('whmazadmin/include/header');?>

	 <div class="content content-fluid content-wrapper">
      <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

        <div class="row mt-5">
			<div class="col-md-12 col-sm-12">
				<h3 class="d-flex justify-content-between"><span>Service Products</span> <a href="<?=base_url()?>whmazadmin/service_product/index" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i>&nbsp;Back</a></h3>
				<hr class="mg-5" />
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb breadcrumb-style1 mg-b-0">
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/service_product/index">Service products</a></li>
						<li class="breadcrumb-item active"><a href="#">Manage service product</a></li>
					</ol>
				</nav>
			  <?php if ($this->session->flashdata('alert')) { ?>
				<?= $this->session->flashdata('alert') ?>
			  <?php } ?>

			</div>

			<div class="col-md-12 col-sm-12 mt-5">
				<form method="post" name="entityManageForm" id="entityManageForm" action="<?=base_url()?>whmazadmin/service_product/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
					<?=csrf_field()?>
					<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

					<div class="row">

						<div class="col-md-6 col-sm-12">
							<div class="form-group">
								<label for="product_name">Product name</label>
								<input name="product_name" type="text" class="form-control" id="product_name" value="<?= htmlspecialchars($detail['product_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
								<?php echo form_error('product_name', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<div class="col-md-6 col-sm-12">
							<div class="form-group">
								<label for="product_service_group_id">Service group</label>
								<?php echo form_dropdown('product_service_group_id', $service_groups, !empty($detail['product_service_group_id']) ? $detail['product_service_group_id'] : '', 'class="form-select select2" id="product_service_group_id"'); ?>
								<?php echo form_error('product_service_group_id', '<div class="error">', '</div>'); ?>
							</div>
						</div>

					</div>

					<div class="row mt-3">

						<div class="col-md-4 col-sm-12">
							<div class="form-group">
								<label for="product_service_type_id">Service type</label>
								<?php echo form_dropdown('product_service_type_id', $service_types, !empty($detail['product_service_type_id']) ? $detail['product_service_type_id'] : '', 'class="form-select select2" id="product_service_type_id"'); ?>
								<?php echo form_error('product_service_type_id', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<div class="col-md-4 col-sm-12">
							<div class="form-group">
								<label for="product_service_module_id">Module</label>
								<?php echo form_dropdown('product_service_module_id', $service_modules, !empty($detail['product_service_module_id']) ? $detail['product_service_module_id'] : '', 'class="form-select select2" id="product_service_module_id"'); ?>
								<?php echo form_error('product_service_module_id', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<div class="col-md-4 col-sm-12">
							<div class="form-group">
								<label for="server_id">Server</label>
								<?php echo form_dropdown('server_id', $servers, !empty($detail['server_id']) ? $detail['server_id'] : '', 'class="form-select select2" id="server_id"'); ?>
								<?php echo form_error('server_id', '<div class="error">', '</div>'); ?>
							</div>
						</div>

					</div>

					<div class="row mt-3">

						<div class="col-md-6 col-sm-12">
							<div class="form-group">
								<label for="cp_package">cPanel package name</label>
								<input name="cp_package" type="text" class="form-control" id="cp_package" value="<?= htmlspecialchars($detail['cp_package'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
								<small class="text-muted">The cPanel/WHM package name for auto-provisioning</small>
							</div>
						</div>

						<div class="col-md-6 col-sm-12">
							<div class="form-group">
								<label for="is_hidden">Visibility</label>
								<div class="form-check mt-2">
									<input class="form-check-input" type="checkbox" name="is_hidden" id="is_hidden" value="1" <?= (!empty($detail['is_hidden']) && $detail['is_hidden'] == 1) ? 'checked' : '' ?>>
									<label class="form-check-label" for="is_hidden">Hidden from client area</label>
								</div>
							</div>
						</div>

					</div>

					<div class="row mt-3">
						<div class="col-md-12 col-sm-12">
							<div class="form-group">
								<label for="product_desc">Product description</label>
								<textarea name="product_desc" class="form-control" id="product_desc" rows="5"><?= htmlspecialchars($detail['product_desc'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
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
