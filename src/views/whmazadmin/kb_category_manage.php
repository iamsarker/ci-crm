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
							<h3><i class="fa fa-folder-open"></i> <?= !empty($detail['cat_title']) ? htmlspecialchars($detail['cat_title']) : 'New KB Category' ?></h3>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/kb_category/index">KB Categories</a></li>
									<li class="breadcrumb-item active"><a href="#">Manage</a></li>
								</ol>
							</nav>
						</div>
						<a href="<?=base_url()?>whmazadmin/kb_category/index" class="btn btn-back">
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
					<form method="post" name="entityManageForm" id="entityManageForm" class="company-form" action="<?=base_url()?>whmazadmin/kb_category/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
						<?=csrf_field()?>
						<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />
						<input name="parent_id" type="hidden" id="parent_id" value="<?= htmlspecialchars($detail['parent_id'] ?? '0', ENT_QUOTES, 'UTF-8') ?>" />

						<!-- Category Details Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-info-circle"></i> Category Details
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="cat_title"><i class="fa fa-heading"></i> Category Title</label>
										<input name="cat_title" type="text" class="form-control make-slug" id="cat_title" placeholder="Enter category title" value="<?= htmlspecialchars($detail['cat_title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('cat_title', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="slug"><i class="fa fa-link"></i> Slug</label>
										<input name="slug" type="text" class="form-control" id="slug" placeholder="category-slug" value="<?= htmlspecialchars($detail['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('slug', '<div class="error">', '</div>'); ?>
									</div>
								</div>
							</div>

							<div class="form-group">
								<label class="form-label" for="description"><i class="fa fa-align-left"></i> Description</label>
								<textarea name="description" rows="3" class="form-control" id="description" placeholder="Enter category description..."><?= htmlspecialchars($detail['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
								<?php echo form_error('description', '<div class="error">', '</div>'); ?>
							</div>

							<div class="custom-checkbox-toggle" style="max-width: 250px;">
								<input name="is_hidden" type="checkbox" id="is_hidden" <?= !empty($detail['is_hidden']) && $detail['is_hidden'] == 1 ? 'checked' : ''?>/>
								<label for="is_hidden"><i class="fa fa-eye-slash me-2"></i> Hidden Category</label>
							</div>
						</div>

						<!-- Submit Button -->
						<div class="form-group mt-4">
							<button type="submit" class="btn btn-save-company">
								<i class="fa fa-check-circle"></i> Save Category
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
