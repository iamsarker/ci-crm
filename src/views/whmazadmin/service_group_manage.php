<?php $this->load->view('whmazadmin/include/header');?>
<link rel="stylesheet" href="<?=base_url()?>resources/assets/css/admin.manage_view.css">

<div class="content content-fluid content-wrapper">
	<div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

		<!-- Page Header -->
		<div class="row mt-4">
			<div class="col-12">
				<div class="company-page-header">
					<div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
						<div>
							<h3><i class="fa fa-object-group"></i> <?= !empty($detail['group_name']) ? htmlspecialchars($detail['group_name']) : 'New Service Group' ?></h3>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/service_group/index">Service Groups</a></li>
									<li class="breadcrumb-item active"><a href="#">Manage</a></li>
								</ol>
							</nav>
						</div>
						<a href="<?=base_url()?>whmazadmin/service_group/index" class="btn btn-back">
							<i class="fa fa-arrow-left"></i> Back to List
						</a>
					</div>
				</div>
			</div>
		</div>

		<!-- Form Section -->
		<div class="row mt-4">
			<div class="col-12">
				<div class="manage-form-card">
					<form method="post" name="entityManageForm" id="entityManageForm" class="company-form" action="<?=base_url()?>whmazadmin/service_group/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
						<?=csrf_field()?>
						<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

						<!-- Group Details Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-info-circle"></i> Group Details
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="group_name"><i class="fa fa-object-group"></i> Group Name</label>
										<input name="group_name" type="text" class="form-control" id="group_name" placeholder="Enter group name" value="<?= htmlspecialchars($detail['group_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('group_name', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="product_service_type_id"><i class="fa fa-layer-group"></i> Group Category</label>
										<?php echo form_dropdown('product_service_type_id', $categories,!empty($detail['product_service_type_id']) ? $detail['product_service_type_id'] : '','class="form-select select2" id="product_service_type_id"'); ?>
										<?php echo form_error('product_service_type_id', '<div class="error">', '</div>'); ?>
									</div>
								</div>
							</div>

							<div class="form-group">
								<label class="form-label" for="group_headline"><i class="fa fa-heading"></i> Group Headline</label>
								<input name="group_headline" type="text" class="form-control" id="group_headline" placeholder="Enter headline" value="<?= htmlspecialchars($detail['group_headline'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
								<?php echo form_error('group_headline', '<div class="error">', '</div>'); ?>
							</div>

							<div class="form-group">
								<label class="form-label" for="tags"><i class="fa fa-tags"></i> Group Tags</label>
								<input name="tags" type="text" class="form-control" id="tags" placeholder="tag1, tag2, tag3" value="<?= htmlspecialchars($detail['tags'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
								<?php echo form_error('tags', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<!-- Submit Button -->
						<div class="form-group mt-4">
							<button type="submit" class="btn btn-save-company">
								<i class="fa fa-check-circle"></i> Save Group
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>

	</div><!-- container -->
</div><!-- content -->

<?php $this->load->view('whmazadmin/include/footer_script');?>
<?php $this->load->view('whmazadmin/include/footer');?>
