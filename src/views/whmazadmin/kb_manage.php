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
							<h3><i class="fa fa-book"></i> <?= !empty($detail['title']) ? htmlspecialchars($detail['title']) : 'New Knowledge Base Article' ?></h3>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/kb/index">Knowledge Bases</a></li>
									<li class="breadcrumb-item active"><a href="#">Manage</a></li>
								</ol>
							</nav>
						</div>
						<a href="<?=base_url()?>whmazadmin/kb/index" class="btn btn-back">
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
					<form method="post" name="entityManageForm" id="entityManageForm" class="company-form" action="<?=base_url()?>whmazadmin/kb/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
						<?=csrf_field()?>
						<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

						<!-- Article Details Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-file-alt"></i> Article Details
							</div>
							<div class="row">
								<div class="col-md-8">
									<div class="form-group">
										<label class="form-label" for="title"><i class="fa fa-heading"></i> Article Title</label>
										<input name="title" type="text" class="form-control make-slug" id="title" placeholder="Enter article title" value="<?= htmlspecialchars($detail['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('title', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="form-label" for="slug"><i class="fa fa-link"></i> Slug</label>
										<input name="slug" type="text" class="form-control" id="slug" placeholder="article-slug" value="<?= htmlspecialchars($detail['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('slug', '<div class="error">', '</div>'); ?>
									</div>
								</div>
							</div>
						</div>

						<!-- Stats Section (Read Only) -->
						<?php if (!empty($detail['id'])) { ?>
						<div class="kb-stats-card mb-4">
							<div class="row">
								<div class="col-3">
									<div class="stat-item">
										<div class="stat-value"><?= htmlspecialchars($detail['total_view'] ?? '0', ENT_QUOTES, 'UTF-8') ?></div>
										<div class="stat-label"><i class="fa fa-eye"></i> Views</div>
									</div>
								</div>
								<div class="col-3">
									<div class="stat-item">
										<div class="stat-value"><?= htmlspecialchars($detail['useful'] ?? '0', ENT_QUOTES, 'UTF-8') ?></div>
										<div class="stat-label"><i class="fa fa-heart"></i> Useful</div>
									</div>
								</div>
								<div class="col-3">
									<div class="stat-item">
										<div class="stat-value"><?= htmlspecialchars($detail['upvote'] ?? '0', ENT_QUOTES, 'UTF-8') ?></div>
										<div class="stat-label"><i class="fa fa-thumbs-up"></i> Upvotes</div>
									</div>
								</div>
								<div class="col-3">
									<div class="stat-item">
										<div class="stat-value"><?= htmlspecialchars($detail['downvote'] ?? '0', ENT_QUOTES, 'UTF-8') ?></div>
										<div class="stat-label"><i class="fa fa-thumbs-down"></i> Downvotes</div>
									</div>
								</div>
							</div>
						</div>
						<?php } ?>

						<!-- Metadata Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-cog"></i> Article Settings
							</div>
							<div class="row">
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="sort_order"><i class="fa fa-sort-numeric-down"></i> Serial #</label>
										<input name="sort_order" type="text" class="form-control" id="sort_order" placeholder="1" value="<?= htmlspecialchars($detail['sort_order'] ?? '1', ENT_QUOTES, 'UTF-8') ?>" />
										<?php echo form_error('sort_order', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="form-label" for="tags"><i class="fa fa-tags"></i> Tags</label>
										<input name="tags" type="text" class="form-control" id="tags" placeholder="tag1, tag2, tag3" value="<?= htmlspecialchars($detail['tags'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('tags', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-5">
									<?php $kb_cat_ids = !empty($detail['kb_cat_ids']) ? explode(",", $detail['kb_cat_ids']) : array(); ?>
									<div class="form-group">
										<label class="form-label" for="kb_cat_id"><i class="fa fa-folder"></i> Category</label>
										<select name="kb_cat_id[]" id="kb_cat_id" class="form-select select2" multiple>
											<option value="" disabled>-- Select Category --</option>
											<?php foreach( $categories as $item ){ ?>
												<option value="<?=htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8');?>" <?= in_array($item['id'], $kb_cat_ids) ? 'selected' : '' ?>><?=htmlspecialchars($item['cat_title'], ENT_QUOTES, 'UTF-8');?></option>
											<?php } ?>
										</select>
										<?php echo form_error('kb_cat_id', '<div class="error">', '</div>'); ?>
									</div>
								</div>
							</div>
						</div>

						<!-- Article Content Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-edit"></i> Article Content
							</div>
							<div class="form-group">
								<?php echo form_error('article', '<div class="error mb-2">', '</div>'); ?>
								<textarea name="article" id="article" style="display: none"><?= htmlspecialchars($detail['article'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
								<div class="kb-editor-container">
									<div id="editor" style="height: 280px"><?= !empty($detail['article']) ? xss_cleaner($detail['article']) : ''?></div>
								</div>
							</div>

							<div class="custom-checkbox-toggle mt-3" style="max-width: 250px;">
								<input name="is_hidden" type="checkbox" id="is_hidden" <?= !empty($detail['is_hidden']) && $detail['is_hidden'] == 1 ? 'checked' : ''?>/>
								<label for="is_hidden"><i class="fa fa-eye-slash me-2"></i> Hidden Article</label>
							</div>
						</div>

						<!-- Submit Button -->
						<div class="form-group mt-4">
							<button type="submit" class="btn btn-save-company">
								<i class="fa fa-check-circle"></i> Save Article
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
			['bold', 'italic', 'underline'],
			['link', 'blockquote', 'code-block'],
			[{ 'list': 'ordered'}, { 'list': 'bullet' }],
			[{ 'script': 'sub'}, { 'script': 'super' }],
			[{ 'indent': '-1'}, { 'indent': '+1' }],
			[{ 'header': [1, 2, 3, 4, 5, 6, false] }],
			[{ 'font': [] }],
		];

		var quill = new Quill('#editor', {
			modules: {
				toolbar: toolbarOptions
			},
			placeholder: 'Compose your article...',
			theme: 'snow'
		});

		quill.on('text-change', function() {
			const content = quill.root.innerHTML.trim();
			document.querySelector('#article').innerText = content;
		});

	});
</script>

<?php $this->load->view('whmazadmin/include/footer');?>
