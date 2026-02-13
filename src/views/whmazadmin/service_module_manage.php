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
							<h3><i class="fa fa-puzzle-piece"></i> <?= !empty($detail['module_name']) ? htmlspecialchars($detail['module_name']) : 'New Service Module' ?></h3>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/service_module/index">Service Modules</a></li>
									<li class="breadcrumb-item active"><a href="#">Manage</a></li>
								</ol>
							</nav>
						</div>
						<a href="<?=base_url()?>whmazadmin/service_module/index" class="btn btn-back">
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
					<form method="post" name="entityManageForm" id="entityManageForm" class="company-form" action="<?=base_url()?>whmazadmin/service_module/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
						<?=csrf_field()?>
						<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

						<!-- Module Details Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-info-circle"></i> Module Details
							</div>
							<div class="form-group">
								<label class="form-label" for="module_name"><i class="fa fa-puzzle-piece"></i> Module Name</label>
								<input name="module_name" type="text" class="form-control" id="module_name" placeholder="Enter module name" value="<?= htmlspecialchars($detail['module_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
								<?php echo form_error('module_name', '<div class="error">', '</div>'); ?>
							</div>

							<div class="form-group">
								<label class="form-label" for="remarks"><i class="fa fa-align-left"></i> Remarks</label>
								<textarea name="remarks" rows="3" class="form-control" id="remarks" placeholder="Enter remarks..."><?= htmlspecialchars($detail['remarks'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
								<?php echo form_error('remarks', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<!-- Submit Button -->
						<div class="form-group mt-4">
							<button type="submit" class="btn btn-save-company">
								<i class="fa fa-check-circle"></i> Save Module
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
