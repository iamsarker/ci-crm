<?php $this->load->view('whmazadmin/include/header');?>

	 <div class="content content-fluid content-wrapper">
      <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

        <div class="row mt-5">
			<div class="col-md-12 col-sm-12">
				<h3 class="d-flex justify-content-between"><span>KB Categories</span> <a href="<?=base_url()?>whmazadmin/kb_category/index" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i>&nbsp;Back</a></h3>
				<hr class="mg-5" />
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb breadcrumb-style1 mg-b-0">
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/kb_category/index">KB Categories</a></li>
						<li class="breadcrumb-item active"><a href="#">Manage KB category</a></li>
					</ol>
				</nav>
			  <?php if ($this->session->flashdata('alert')) { ?>
				<?= $this->session->flashdata('alert') ?>
			  <?php } ?>

			</div>

			<div class="col-md-12 col-sm-12 mt-5">
				<form method="post" name="entityManageForm" id="entityManageForm" action="<?=base_url()?>whmazadmin/kb_category/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
					<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />
					<input name="parent_id" type="hidden" id="parent_id" value="<?= !empty($detail['parent_id']) ? $detail['parent_id'] : 0 ?>" />
					<div class="form-group">
						<label for="cat_title">KB category title</label>
						<input name="cat_title" type="text" class="form-control make-slug" id="cat_title" value="<?= !empty($detail['cat_title']) ? $detail['cat_title'] : ''?>"/>
						<?php echo form_error('cat_title', '<div class="error">', '</div>'); ?>
					</div>

					<div class="form-group">
						<label for="slug">Slug</label>
						<input name="slug" type="text" class="form-control" id="slug" value="<?= !empty($detail['slug']) ? $detail['slug'] : ''?>"/>
						<?php echo form_error('slug', '<div class="error">', '</div>'); ?>
					</div>

					<div class="form-group">
						<label for="description">Description</label>
						<textarea name="description" rows="3" class="form-control" id="description"><?= !empty($detail['description']) ? $detail['description'] : ''?></textarea>
						<?php echo form_error('description', '<div class="error">', '</div>'); ?>
					</div>

					<div class="form-check mb-3">
						<input name="is_hidden" type="checkbox" class="form-check-input" id="is_hidden" <?= !empty($detail['is_hidden']) && $detail['is_hidden'] == 1 ? 'checked=\"checked\"' : ''?>"/>
						<label for="is_hidden" class="form-check-label mt-1"> Is Hidden?</label>
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
