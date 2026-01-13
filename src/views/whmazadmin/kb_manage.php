<?php $this->load->view('whmazadmin/include/header');?>

	 <div class="content content-fluid content-wrapper">
      <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

        <div class="row mt-5">
			<div class="col-md-12 col-sm-12">
				<h3 class="d-flex justify-content-between"><span>Knowledge Bases</span> <a href="<?=base_url()?>whmazadmin/kb/index" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i>&nbsp;Back</a></h3>
				<hr class="mg-5" />
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb breadcrumb-style1 mg-b-0">
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/kb/index">Knowledge Bases</a></li>
						<li class="breadcrumb-item active"><a href="#">Manage KB</a></li>
					</ol>
				</nav>
			  <?php if ($this->session->flashdata('alert')) { ?>
				<?= $this->session->flashdata('alert') ?>
			  <?php } ?>

			</div>

			<div class="col-md-12 col-sm-12 mt-5">
				<form method="post" name="entityManageForm" id="entityManageForm" action="<?=base_url()?>whmazadmin/kb/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
					<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />
					<div class="form-group">
						<label for="title">KB title</label>
						<input name="title" type="text" class="form-control make-slug" id="title" value="<?= !empty($detail['title']) ? $detail['title'] : ''?>"/>
						<?php echo form_error('title', '<div class="error">', '</div>'); ?>
					</div>

					<div class="form-group">
						<label for="slug">Slug</label>
						<input name="slug" type="text" class="form-control" id="slug" value="<?= !empty($detail['slug']) ? $detail['slug'] : ''?>"/>
						<?php echo form_error('slug', '<div class="error">', '</div>'); ?>
					</div>

					<div class="row mt-3">
						<div class="col-md-3 col-sm-12">
							<div class="form-group">
								<label for="total_view">Total View</label>
								<input name="total_view" type="text" class="form-control" id="total_view" readonly disabled value="<?= !empty($detail['total_view']) ? $detail['total_view'] : '0'?>"/>
								<?php echo form_error('total_view', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<div class="col-md-3 col-sm-12">
							<div class="form-group">
								<label for="useful">Useful</label>
								<input name="useful" type="text" class="form-control" id="useful" readonly disabled value="<?= !empty($detail['useful']) ? $detail['useful'] : '0'?>"/>
								<?php echo form_error('useful', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<div class="col-md-3 col-sm-12">
							<div class="form-group">
								<label for="upvote">Upvote</label>
								<input name="upvote" type="text" class="form-control" id="upvote" readonly disabled value="<?= !empty($detail['upvote']) ? $detail['upvote'] : '0'?>"/>
								<?php echo form_error('upvote', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<div class="col-md-3 col-sm-12">
							<div class="form-group">
								<label for="downvote">Down Vote</label>
								<input name="downvote" type="text" class="form-control" id="downvote" readonly disabled value="<?= !empty($detail['downvote']) ? $detail['downvote'] : '0'?>"/>
								<?php echo form_error('downvote', '<div class="error">', '</div>'); ?>
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-3 col-sm-12">
							<div class="form-group">
								<label for="sort_order">Serial#</label>
								<input name="sort_order" type="text" class="form-control" id="sort_order" value="<?= !empty($detail['sort_order']) ? $detail['sort_order'] : '1'?>" />
								<?php echo form_error('sort_order', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<div class="col-md-3 col-sm-12">
							<div class="form-group">
								<label for="tags">Tags</label>
								<input name="tags" type="text" class="form-control" id="tags" value="<?= !empty($detail['tags']) ? $detail['tags'] : ''?>"/>
								<?php echo form_error('tags', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<div class="col-md-6 col-sm-12">
							<?php
								$kb_cat_ids = !empty($detail['kb_cat_ids']) ? explode(",", $detail['kb_cat_ids']) : array();
							?>
							<div class="form-group">
								<label for="kb_cat_id">Category</label>
								<select name="kb_cat_id[]" id="kb_cat_id" class="form-select select2" multiple>
									<option value="" disabled>-- Select --</option>
									<?php foreach( $categories as $item  ){?>
										<option value="<?=$item['id'];?>" <?= in_array($item['id'], $kb_cat_ids) ? 'selected' : '' ?> ><?=$item['cat_title'];?></option>
									<?php } ?>
								</select>
								<?php echo form_error('kb_cat_id', '<div class="error">', '</div>'); ?>
							</div>
						</div>

					</div>

					<div class="form-group">
						<?php echo form_error('article', '<div class="error">', '</div>'); ?>
						<textarea name="article" id="article" style="display: none" id="article"><?= !empty($detail['article']) ? $detail['article'] : ''?></textarea>
						<div id="editor" style="height: 180px"><?= !empty($detail['article']) ? $detail['article'] : ''?></div>
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
