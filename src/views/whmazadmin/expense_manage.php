<?php $this->load->view('whmazadmin/include/header');?>

	 <div class="content content-fluid content-wrapper">
      <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

        <div class="row mt-5">
			<div class="col-md-12 col-sm-12">
				<h3 class="d-flex justify-content-between"><span>Expenses</span> <a href="<?=base_url()?>whmazadmin/expense/index" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i>&nbsp;Back</a></h3>
				<hr class="mg-5" />
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb breadcrumb-style1 mg-b-0">
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/expense/index">Expenses</a></li>
						<li class="breadcrumb-item active"><a href="#">Manage Expense</a></li>
					</ol>
				</nav>
			  <?php if ($this->session->flashdata('alert')) { ?>
				<?= $this->session->flashdata('alert') ?>
			  <?php } ?>

			</div>

			<div class="col-md-12 col-sm-12 mt-5">
				<form method="post" name="entityManageForm" id="entityManageForm" action="<?=base_url()?>whmazadmin/expense/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" enctype="multipart/form-data">
					<?=csrf_field()?>
					<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

					<div class="row">
						<div class="col-md-6 col-sm-12">
							<label>Expense Category</label>
							<select name="expense_type_id" id="expense_type_id" class="form-select">
								<option value="" selected="selected">-- Select --</option>
								<?php foreach( $categories as $item  ){?>
									<option value="<?=htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8');?>" <?= !empty($detail['expense_type_id']) && $detail['expense_type_id'] == $item['id'] ? 'selected' : '' ?> ><?=htmlspecialchars($item['expense_type'], ENT_QUOTES, 'UTF-8');?></option>
								<?php } ?>
							</select>
							<?php echo form_error('expense_type_id', '<div class="error">', '</div>'); ?>
						</div>

						<div class="col-md-6 col-sm-12">
							<label>Expense Vendor</label>
							<select name="expense_vendor_id" id="expense_vendor_id" class="form-select">
								<option value="" selected="selected">-- Select --</option>
								<?php foreach( $vendors as $item  ){?>
								<option value="<?=htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8');?>" <?= !empty($detail['expense_vendor_id']) && $detail['expense_vendor_id'] == $item['id'] ? 'selected' : '' ?>><?=htmlspecialchars($item['vendor_name'], ENT_QUOTES, 'UTF-8');?></option>
								<?php } ?>
							</select>
							<?php echo form_error('expense_vendor_id', '<div class="error">', '</div>'); ?>
						</div>
					</div>


					<div class="row mt-3">
						<div class="col-md-4 col-sm-12">
							<div class="form-group">
								<label for="expense_date">Expense date</label>
								<input name="expense_date" type="date" class="form-control" id="expense_date" value="<?= !empty($detail['expense_date']) ? htmlspecialchars($detail['expense_date'], ENT_QUOTES, 'UTF-8') : ''?>"/>
								<?php echo form_error('expense_date', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<div class="col-md-4 col-sm-12">
							<div class="form-group">
								<label for="exp_amount">Expense amount</label>
								<input name="exp_amount" type="text" class="form-control" id="exp_amount" value="<?= !empty($detail['exp_amount']) ? htmlspecialchars($detail['exp_amount'], ENT_QUOTES, 'UTF-8') : ''?>"/>
								<?php echo form_error('exp_amount', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<div class="col-md-4 col-sm-12">
							<div class="form-group">
								<label for="paid_amount">Paid amount</label>
								<input name="paid_amount" type="text" class="form-control" id="paid_amount" value="<?= !empty($detail['paid_amount']) ? htmlspecialchars($detail['paid_amount'], ENT_QUOTES, 'UTF-8') : ''?>"/>
								<?php echo form_error('paid_amount', '<div class="error">', '</div>'); ?>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label for="remarks">Remarks</label>
						<textarea name="remarks" rows="3" class="form-control" id="remarks"><?= !empty($detail['remarks']) ? htmlspecialchars($detail['remarks'], ENT_QUOTES, 'UTF-8') : ''?></textarea>
						<?php echo form_error('remarks', '<div class="error">', '</div>'); ?>
					</div>

					<div class="form-group">
						<label>Attachment</label>
						<input type="file" name="attachment[]" id="attachment" class="form-control" multiple
							accept=".gif,.jpg,.jpeg,.png,.pdf,.txt"
							data-max-size="5242880"
							onchange="validateFileUpload(this)" />
						<small class="form-text text-muted">Allowed: GIF, JPG, PNG, PDF, TXT. Max size: 5MB per file.</small>
						<?php echo form_error('attachment[]'); ?>
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
