<?php $this->load->view('whmazadmin/include/header');?>

	 <div class="content content-fluid content-wrapper">
      <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

        <div class="row mt-5">
			<div class="col-md-12 col-sm-12">
				<h3 class="d-flex justify-content-between"><span>Service Groups</span> <a href="<?=base_url()?>whmazadmin/service_group/index" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i>&nbsp;Back</a></h3>
				<hr class="mg-5" />
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb breadcrumb-style1 mg-b-0">
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/service_group/index">Service groups</a></li>
						<li class="breadcrumb-item active"><a href="#">Manage service group</a></li>
					</ol>
				</nav>
			  <?php if ($this->session->flashdata('alert')) { ?>
				<?= $this->session->flashdata('alert') ?>
			  <?php } ?>

			</div>

			<div class="col-md-12 col-sm-12 mt-5">
				<form method="post" name="entityManageForm" id="entityManageForm" action="<?=base_url()?>whmazadmin/service_group/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
					<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

					<div class="row">

						<div class="col-md-6 col-sm-12">
							<div class="form-group">
								<label for="subject">Service group name</label>
								<input name="group_name" type="text" class="form-control" id="group_name" value="<?= !empty($detail['group_name']) ? $detail['group_name'] : ''?>"/>
								<?php echo form_error('group_name', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<div class="col-md-6 col-sm-12">
							<div class="form-group">
								<label for="product_service_type_id">Group category</label>
								<?php echo form_dropdown('product_service_type_id', $categories,!empty($detail['product_service_type_id']) ? $detail['product_service_type_id'] : '','class="form-select select2" id="product_service_type_id"'); ?>
								<?php echo form_error('product_service_type_id', '<div class="error">', '</div>'); ?>
							</div>
						</div>

					</div>

					<div class="form-group">
						<label for="subject">Group headline</label>
						<input name="group_headline" type="text" class="form-control" id="group_headline" value="<?= !empty($detail['group_headline']) ? $detail['group_headline'] : ''?>" />
						<?php echo form_error('group_headline', '<div class="error">', '</div>'); ?>
					</div>

					<div class="form-group">
						<label for="subject">Group Tags</label>
						<input name="tags" type="text" class="form-control" id="tags" value="<?= !empty($detail['tags']) ? $detail['tags'] : ''?>"/>
						<?php echo form_error('tags', '<div class="error">', '</div>'); ?>
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

<?php $this->load->view('whmazadmin/include/footer');?>
