<?php $this->load->view('whmazadmin/include/header');?>

	 <div class="content content-fluid content-wrapper">
      <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

        <div class="row mt-5">
			<div class="col-md-12 col-sm-12">
				<h3 class="d-flex justify-content-between"><span>Expense Vendors</span> <a href="<?=base_url()?>whmazadmin/expense_vendor/index" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i>&nbsp;Back</a></h3>
				<hr class="mg-5" />
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb breadcrumb-style1 mg-b-0">
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/expense_vendor/index">Expense Vendors</a></li>
						<li class="breadcrumb-item active"><a href="#">Manage Expense vendor</a></li>
					</ol>
				</nav>
			  <?php if ($this->session->flashdata('alert')) { ?>
				<?= $this->session->flashdata('alert') ?>
			  <?php } ?>

			</div>

			<div class="col-md-12 col-sm-12 mt-5">
				<form method="post" name="entityManageForm" id="entityManageForm" action="<?=base_url()?>whmazadmin/expense_vendor/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
					<?=csrf_field()?>
					<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />
					<div class="form-group">
						<label for="vendor_name">Expense vendor name</label>
						<input name="vendor_name" type="text" class="form-control" id="vendor_name" value="<?= htmlspecialchars($detail['vendor_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
						<?php echo form_error('expense_type', '<div class="error">', '</div>'); ?>
					</div>

					<div class="form-group">
						<label for="remarks">Remarks</label>
						<textarea name="remarks" rows="3" class="form-control" id="remarks"><?= htmlspecialchars($detail['remarks'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
						<?php echo form_error('remarks', '<div class="error">', '</div>'); ?>
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
		toastSuccess(<?= json_encode($this->session->flashdata('alert_success')) ?>);
	<?php } ?>
	<?php if ($this->session->flashdata('alert_error')) { ?>
		toastError(<?= json_encode($this->session->flashdata('alert_error')) ?>);
	<?php } ?>
});
</script>


<?php $this->load->view('whmazadmin/include/footer');?>
