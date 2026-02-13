<?php $this->load->view('whmazadmin/include/header'); ?>
<link rel="stylesheet" href="<?=base_url()?>resources/assets/css/admin.manage_view.css">
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">

<style>
.ql-editor {
	min-height: 300px;
	font-size: 14px;
}
.ql-container {
	border-bottom-left-radius: 8px;
	border-bottom-right-radius: 8px;
}
.ql-toolbar {
	border-top-left-radius: 8px;
	border-top-right-radius: 8px;
	background: #f8f9fa;
}
.history-timeline {
	position: relative;
	padding-left: 25px;
}
.history-timeline::before {
	content: '';
	position: absolute;
	left: 8px;
	top: 0;
	bottom: 0;
	width: 2px;
	background: #e9ecef;
}
.history-item {
	position: relative;
	padding: 12px 15px;
	margin-bottom: 10px;
	background: #f8f9fa;
	border-radius: 8px;
	border-left: 3px solid #00897B;
}
.history-item::before {
	content: '';
	position: absolute;
	left: -22px;
	top: 18px;
	width: 10px;
	height: 10px;
	background: #00897B;
	border-radius: 50%;
	border: 2px solid #fff;
}
.history-item.type-created::before {
	background: #28a745;
}
.history-item.type-restored::before {
	background: #fd7e14;
}
.history-item .history-meta {
	font-size: 12px;
	color: #6c757d;
}
.history-item .history-title {
	font-weight: 600;
	color: #333;
}
.badge-change-type {
	font-size: 10px;
	padding: 3px 8px;
	text-transform: uppercase;
}
.system-badge {
	background: linear-gradient(135deg, #ffc107, #ff9800);
	color: #333;
	font-size: 11px;
	padding: 4px 10px;
	border-radius: 20px;
}
</style>

<div class="content content-fluid content-wrapper">
	<div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

		<!-- Page Header -->
		<div class="row mt-4">
			<div class="col-12">
				<div class="company-page-header">
					<div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
						<div>
							<h3>
								<i class="fa fa-file-alt"></i>
								<?= !empty($detail['id']) ? 'Edit Page' : 'Create New Page' ?>
								<?php if (!empty($detail['is_system']) && $detail['is_system'] == 1): ?>
									<span class="system-badge ms-2"><i class="fa fa-lock me-1"></i>System Page</span>
								<?php endif; ?>
							</h3>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/page/index">Pages</a></li>
									<li class="breadcrumb-item active"><?= !empty($detail['id']) ? 'Edit' : 'Create' ?></li>
								</ol>
							</nav>
						</div>
						<a href="<?=base_url()?>whmazadmin/page/index" class="btn btn-back">
							<i class="fa fa-arrow-left"></i> Back to Pages
						</a>
					</div>
				</div>
			</div>
		</div>

		<!-- Form Section -->
		<div class="row mt-4">
			<div class="col-lg-8">
				<div class="manage-form-card">
					<form method="POST" name="pageForm" id="pageForm" class="company-form" action="<?=base_url()?>whmazadmin/page/manage/<?= !empty($detail['id']) ? safe_encode($detail['id']) : '' ?>">
						<?=csrf_field()?>
						<input type="hidden" name="id" value="<?= !empty($detail['id']) ? safe_encode($detail['id']) : '' ?>">

						<!-- Page Details Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-info-circle"></i> Page Details
							</div>

							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="page_title"><i class="fa fa-heading"></i> Page Title <span class="text-danger">*</span></label>
										<input type="text" name="page_title" id="page_title" class="form-control" required placeholder="Enter page title" value="<?= !empty($detail['page_title']) ? htmlspecialchars($detail['page_title']) : '' ?>">
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="page_slug"><i class="fa fa-link"></i> URL Slug <span class="text-danger">*</span></label>
										<input type="text" name="page_slug" id="page_slug" class="form-control" required placeholder="e.g., terms-and-conditions" value="<?= !empty($detail['page_slug']) ? htmlspecialchars($detail['page_slug']) : '' ?>" <?= (!empty($detail['is_system']) && $detail['is_system'] == 1) ? 'readonly' : '' ?>>
										<small class="form-text text-muted">Only letters, numbers, dashes and underscores</small>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="sort_order"><i class="fa fa-sort-numeric-up"></i> Sort Order</label>
										<input type="number" name="sort_order" id="sort_order" class="form-control" placeholder="0" value="<?= !empty($detail['sort_order']) ? intval($detail['sort_order']) : 0 ?>">
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label"><i class="fa fa-eye"></i> Published</label>
										<div class="form-check form-switch mt-2">
											<input class="form-check-input" type="checkbox" name="is_published" id="is_published" value="1" <?= (!empty($detail['is_published']) && $detail['is_published'] == 1) ? 'checked' : '' ?> style="width: 50px; height: 25px; cursor: pointer;">
											<label class="form-check-label ms-2" for="is_published" style="padding-top: 3px;">
												<span id="publishLabel"><?= (!empty($detail['is_published']) && $detail['is_published'] == 1) ? 'Published' : 'Draft' ?></span>
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- SEO Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-search"></i> SEO Settings
							</div>

							<div class="form-group">
								<label class="form-label" for="meta_title"><i class="fa fa-tag"></i> Meta Title</label>
								<input type="text" name="meta_title" id="meta_title" class="form-control" placeholder="SEO title (leave blank to use page title)" value="<?= !empty($detail['meta_title']) ? htmlspecialchars($detail['meta_title']) : '' ?>">
								<small class="form-text text-muted">Recommended: 50-60 characters</small>
							</div>

							<div class="form-group">
								<label class="form-label" for="meta_description"><i class="fa fa-align-left"></i> Meta Description</label>
								<textarea name="meta_description" id="meta_description" class="form-control" rows="2" placeholder="Brief description for search engines"><?= !empty($detail['meta_description']) ? htmlspecialchars($detail['meta_description']) : '' ?></textarea>
								<small class="form-text text-muted">Recommended: 150-160 characters</small>
							</div>

							<div class="form-group">
								<label class="form-label" for="meta_keywords"><i class="fa fa-tags"></i> Meta Keywords</label>
								<input type="text" name="meta_keywords" id="meta_keywords" class="form-control" placeholder="keyword1, keyword2, keyword3" value="<?= !empty($detail['meta_keywords']) ? htmlspecialchars($detail['meta_keywords']) : '' ?>">
							</div>
						</div>

						<!-- Content Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-edit"></i> Page Content <span class="text-danger">*</span>
							</div>

							<div id="quillEditor"><?= !empty($detail['page_content']) ? $detail['page_content'] : '' ?></div>
							<textarea name="page_content" id="page_content" style="display:none;" required><?= !empty($detail['page_content']) ? htmlspecialchars($detail['page_content']) : '' ?></textarea>
						</div>

						<!-- Submit Button -->
						<div class="form-group mt-4">
							<button type="submit" class="btn btn-save-company btn-lg">
								<i class="fa fa-save"></i> <?= !empty($detail['id']) ? 'Update Page' : 'Create Page' ?>
							</button>
						</div>
					</form>
				</div>
			</div>

			<!-- Sidebar -->
			<div class="col-lg-4">
				<!-- Page Info Card -->
				<?php if (!empty($detail['id'])): ?>
				<div class="manage-form-card mb-4">
					<div class="company-form-section mb-0">
						<div class="section-title">
							<i class="fa fa-chart-bar"></i> Page Statistics
						</div>
						<div class="d-flex justify-content-between py-2 border-bottom">
							<span class="text-muted"><i class="fa fa-eye me-1"></i> Total Views</span>
							<span class="fw-bold"><?= number_format($detail['total_view'] ?? 0) ?></span>
						</div>
						<div class="d-flex justify-content-between py-2 border-bottom">
							<span class="text-muted"><i class="fa fa-calendar-plus me-1"></i> Created</span>
							<span><?= !empty($detail['inserted_on']) ? date('M d, Y', strtotime($detail['inserted_on'])) : '-' ?></span>
						</div>
						<div class="d-flex justify-content-between py-2">
							<span class="text-muted"><i class="fa fa-calendar-check me-1"></i> Last Updated</span>
							<span><?= !empty($detail['updated_on']) ? date('M d, Y', strtotime($detail['updated_on'])) : '-' ?></span>
						</div>
					</div>
				</div>
				<?php endif; ?>

				<!-- Recent History -->
				<?php if (!empty($history) && count($history) > 0): ?>
				<div class="manage-form-card">
					<div class="company-form-section mb-0">
						<div class="section-title d-flex justify-content-between align-items-center">
							<span><i class="fa fa-history"></i> Recent Changes</span>
							<a href="<?=base_url()?>whmazadmin/page/history/<?= safe_encode($detail['id']) ?>" class="btn btn-sm btn-outline-secondary">View All</a>
						</div>
						<div class="history-timeline">
							<?php foreach (array_slice($history, 0, 5) as $h): ?>
							<div class="history-item type-<?= $h['change_type'] ?>">
								<div class="d-flex justify-content-between align-items-start">
									<div class="history-title"><?= htmlspecialchars($h['page_title']) ?></div>
									<span class="badge badge-change-type bg-<?= $h['change_type'] == 'created' ? 'success' : ($h['change_type'] == 'restored' ? 'warning' : 'info') ?>"><?= ucfirst($h['change_type']) ?></span>
								</div>
								<div class="history-meta mt-1">
									<i class="fa fa-user me-1"></i> <?= htmlspecialchars($h['changed_by_name'] ?? 'Unknown') ?>
									<span class="mx-2">|</span>
									<i class="fa fa-clock me-1"></i> <?= date('M d, Y H:i', strtotime($h['changed_at'])) ?>
								</div>
								<?php if (!empty($h['change_note'])): ?>
								<div class="history-meta mt-1 fst-italic">
									<i class="fa fa-comment me-1"></i> <?= htmlspecialchars($h['change_note']) ?>
								</div>
								<?php endif; ?>
							</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
				<?php endif; ?>

				<!-- Quick Tips -->
				<div class="manage-form-card mt-4">
					<div class="company-form-section mb-0">
						<div class="section-title">
							<i class="fa fa-lightbulb"></i> Tips
						</div>
						<ul class="list-unstyled mb-0" style="font-size: 13px; color: #666;">
							<li class="mb-2"><i class="fa fa-check text-success me-2"></i> Use descriptive slugs for better SEO</li>
							<li class="mb-2"><i class="fa fa-check text-success me-2"></i> Keep meta descriptions under 160 characters</li>
							<li class="mb-2"><i class="fa fa-check text-success me-2"></i> All changes are tracked in history</li>
							<li><i class="fa fa-check text-success me-2"></i> System pages cannot be deleted</li>
						</ul>
					</div>
				</div>
			</div>
		</div>

	</div><!-- container -->
</div><!-- content -->

<?php $this->load->view('whmazadmin/include/footer_script'); ?>
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>

<script>
$(function(){
	'use strict';

	// Initialize Quill editor
	var quill = new Quill('#quillEditor', {
		theme: 'snow',
		placeholder: 'Write your page content here...',
		modules: {
			toolbar: [
				[{ 'header': [1, 2, 3, 4, 5, 6, false] }],
				['bold', 'italic', 'underline', 'strike'],
				[{ 'color': [] }, { 'background': [] }],
				[{ 'list': 'ordered'}, { 'list': 'bullet' }],
				[{ 'align': [] }],
				['link', 'image'],
				['blockquote', 'code-block'],
				['clean']
			]
		}
	});

	// Auto-generate slug from title
	$('#page_title').on('blur', function() {
		var slugField = $('#page_slug');
		if (slugField.val() === '' && !slugField.prop('readonly')) {
			var slug = $(this).val()
				.toLowerCase()
				.replace(/[^a-z0-9\s-]/g, '')
				.replace(/\s+/g, '-')
				.replace(/-+/g, '-')
				.trim();
			slugField.val(slug);
		}
	});

	// Toggle publish label
	$('#is_published').on('change', function() {
		$('#publishLabel').text($(this).is(':checked') ? 'Published' : 'Draft');
	});

	// Form submission - sync Quill content
	$('#pageForm').on('submit', function(e) {
		var content = quill.root.innerHTML;
		if (content === '<p><br></p>' || content.trim() === '') {
			e.preventDefault();
			toastError('Page content is required');
			return false;
		}
		$('#page_content').val(content);
	});
});
</script>

<?php $this->load->view('whmazadmin/include/footer'); ?>
