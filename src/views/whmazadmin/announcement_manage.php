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
							<h3><i class="fa fa-bullhorn"></i> <?= !empty($detail['title']) ? htmlspecialchars($detail['title']) : 'New Announcement' ?></h3>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/announcement/index">Announcements</a></li>
									<li class="breadcrumb-item active"><a href="#">Manage</a></li>
								</ol>
							</nav>
						</div>
						<a href="<?=base_url()?>whmazadmin/announcement/index" class="btn btn-back">
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
					<form method="post" name="entityManageForm" id="entityManageForm" class="company-form" action="<?=base_url()?>whmazadmin/announcement/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
						<?=csrf_field()?>
						<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

						<!-- Announcement Details Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-info-circle"></i> Announcement Details
							</div>
							<div class="form-group">
								<label class="form-label" for="title"><i class="fa fa-heading"></i> Title</label>
								<input name="title" type="text" class="form-control make-slug" id="title" placeholder="Enter announcement title" value="<?= htmlspecialchars($detail['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
								<?php echo form_error('title', '<div class="error">', '</div>'); ?>
							</div>

							<div class="form-group">
								<label class="form-label" for="slug"><i class="fa fa-link"></i> Slug</label>
								<input name="slug" type="text" class="form-control" id="slug" placeholder="announcement-url-slug" value="<?= htmlspecialchars($detail['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
								<?php echo form_error('slug', '<div class="error">', '</div>'); ?>
							</div>

							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="tags"><i class="fa fa-tags"></i> Tags</label>
										<input name="tags" type="text" class="form-control" id="tags" placeholder="tag1, tag2, tag3" value="<?= htmlspecialchars($detail['tags'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('tags', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label class="form-label">&nbsp;</label>
										<div class="custom-checkbox-toggle mt-2">
											<input name="is_published" type="checkbox" id="is_published" <?= !empty($detail['is_published']) && $detail['is_published'] == 1 ? 'checked' : ''?>/>
											<label for="is_published">Published</label>
										</div>
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label class="form-label" for="publish_date"><i class="fa fa-calendar"></i> Publish Date</label>
										<input name="publish_date" type="text" class="form-control" id="publish_date" readonly disabled value="<?= htmlspecialchars($detail['publish_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
									</div>
								</div>
								<div class="col-md-2">
									<div class="form-group">
										<label class="form-label" for="total_view"><i class="fa fa-eye"></i> Total Views</label>
										<input name="total_view" type="text" class="form-control" id="total_view" readonly disabled value="<?= htmlspecialchars($detail['total_view'] ?? '0', ENT_QUOTES, 'UTF-8') ?>"/>
									</div>
								</div>
							</div>
						</div>

						<!-- Content Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-file-alt"></i> Announcement Content
							</div>
							<div class="form-group">
								<?php echo form_error('description', '<div class="error">', '</div>'); ?>
								<textarea name="description" id="description" style="display: none"><?= htmlspecialchars($detail['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
								<div id="editor" style="height: 300px"><?= !empty($detail['description']) ? xss_cleaner($detail['description']) : ''?></div>
							</div>
						</div>

						<!-- Submit Button -->
						<div class="form-group mt-4">
							<button type="submit" class="btn btn-save-company">
								<i class="fa fa-check-circle"></i> Save Announcement
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>

	</div><!-- container -->
</div><!-- content -->

<?php $this->load->view('whmazadmin/include/footer_script');?>

<script>
	$(function(){
		'use strict'

		var toolbarOptions = [
			['bold', 'italic', 'underline'],        // toggled buttons
			['link', 'blockquote', 'code-block'],

			[{ 'list': 'ordered'}, { 'list': 'bullet' }],
			[{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript
			[{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent
			[{ 'header': [1, 2, 3, 4, 5, 6, false] }],
			[{ 'font': [] }],

		];

		var quill = new Quill('#editor', {
			modules: {
				toolbar: toolbarOptions
			},
			placeholder: 'Compose your announcement...',
			theme: 'snow'
		});

		quill.on('text-change', function() {
			const content = quill.root.innerHTML.trim();
			document.querySelector('#description').innerText = content;
		});

	});
</script>
<?php $this->load->view('whmazadmin/include/footer');?>
