<?php $this->load->view('whmazadmin/include/header');?>

	 <div class="content content-fluid content-wrapper">
      <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

        <div class="row mt-5">
			<div class="col-md-12 col-sm-12">
				<h3 class="d-flex justify-content-between"><span>Departments</span> <a href="<?=base_url()?>whmazadmin/ticket_department/index" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i>&nbsp;Back</a></h3>
				<hr class="mg-5" />
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb breadcrumb-style1 mg-b-0">
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/ticket_department/index">Departments</a></li>
						<li class="breadcrumb-item active"><a href="#">Manage department</a></li>
					</ol>
				</nav>
			  <?php if ($this->session->flashdata('alert')) { ?>
				<?= $this->session->flashdata('alert') ?>
			  <?php } ?>

			</div>

			<div class="col-md-12 col-sm-12 mt-5">
				<form method="post" name="entityManageForm" id="entityManageForm" action="<?=base_url()?>whmazadmin/ticket_department/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
					<?=csrf_field()?>
					<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />
					<div class="form-group">
						<label for="name">Name</label>
						<input name="name" type="text" class="form-control" id="name" value="<?= !empty($detail['name']) ? $detail['name'] : ''?>"/>
						<?php echo form_error('name', '<div class="error">', '</div>'); ?>
					</div>

					<div class="form-group">
						<label for="email">Email</label>
						<input name="email" type="text" class="form-control" id="email" value="<?= !empty($detail['email']) ? $detail['email'] : ''?>"/>
						<?php echo form_error('email', '<div class="error">', '</div>'); ?>
					</div>

					<div class="form-group">
						<label for="sort_order">Serial#</label>
						<input name="sort_order" type="text" class="form-control" id="sort_order" value="<?= !empty($detail['sort_order']) ? $detail['sort_order'] : 1?>"/>
						<?php echo form_error('sort_order', '<div class="error">', '</div>'); ?>
					</div>

					<div class="form-group">
						<label for="description">Description</label>
						<textarea name="description" rows="3" class="form-control" id="description"><?= !empty($detail['description']) ? $detail['description'] : ''?></textarea>
						<?php echo form_error('description', '<div class="error">', '</div>'); ?>
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
