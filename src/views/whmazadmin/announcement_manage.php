<?php $this->load->view('whmazadmin/include/header');?>

	 <div class="content content-fluid content-wrapper">
      <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

        <div class="row mt-5">
			<div class="col-md-12 col-sm-12">
				<h3 class="d-flex justify-content-between"><span>Announcements</span> <a href="<?=base_url()?>whmazadmin/announcement/index" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i>&nbsp;Back</a></h3>
				<hr class="mg-5" />
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb breadcrumb-style1 mg-b-0">
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/announcement/index">Announcements</a></li>
						<li class="breadcrumb-item active"><a href="#">Manage announcement</a></li>
					</ol>
				</nav>
			  <?php if ($this->session->flashdata('alert')) { ?>
				<?= $this->session->flashdata('alert') ?>
			  <?php } ?>

			</div>

			<div class="col-md-12 col-sm-12 mt-5">
				<form method="post" name="entityManageForm" id="entityManageForm" action="<?=base_url()?>whmazadmin/announcement/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
					<?=csrf_field()?>
					<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

					<div class="form-group">
						<label for="title">Title</label>
						<input name="title" type="text" class="form-control make-slug" id="title" value="<?= htmlspecialchars($detail['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
						<?php echo form_error('title', '<div class="error">', '</div>'); ?>
					</div>

					<div class="form-group">
						<label for="slug">Slug</label>
						<input name="slug" type="text" class="form-control" id="slug" value="<?= htmlspecialchars($detail['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
						<?php echo form_error('slug', '<div class="error">', '</div>'); ?>
					</div>

					<div class="row mt-3">
						<div class="col-md-6 col-sm-12">
							<div class="form-group">
								<label for="tags">Tags</label>
								<input name="tags" type="text" class="form-control" id="tags" value="<?= htmlspecialchars($detail['tags'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
								<?php echo form_error('tags', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<div class="col-md-2 col-sm-12">
							<div class="form-check mt-4">
								<input name="is_published" type="checkbox" class="form-check-input" id="is_published" <?= !empty($detail['is_published']) && $detail['is_published'] == 1 ? 'checked=\"checked\"' : ''?>"/>
								<label for="is_published" class="form-check-label mt-1"> Is Publish?</label>
							</div>
						</div>
						<div class="col-md-2 col-sm-12">
							<div class="form-group">
								<label for="publish_date">Publish date</label>
								<input name="publish_date" type="text" class="form-control" id="publish_date" readonly disabled value="<?= htmlspecialchars($detail['publish_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
								<?php echo form_error('publish_date', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<div class="col-md-2 col-sm-12">
							<div class="form-group">
								<label for="total_view">Total view</label>
								<input name="total_view" type="text" class="form-control" id="total_view" readonly disabled value="<?= htmlspecialchars($detail['total_view'] ?? '0', ENT_QUOTES, 'UTF-8') ?>"/>
								<?php echo form_error('total_view', '<div class="error">', '</div>'); ?>
							</div>
						</div>

					</div>


					<div class="form-group mb-3">
						<?php echo form_error('description', '<div class="error">', '</div>'); ?>
						<textarea name="description" id="description" style="display: none"><?= htmlspecialchars($detail['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
						<div id="editor" style="height: 300px"><?= !empty($detail['description']) ? xss_cleaner($detail['description']) : ''?></div>
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
		toastSuccess(<?= json_encode($this->session->flashdata('alert_success')) ?>);
	<?php } ?>
	<?php if ($this->session->flashdata('alert_error')) { ?>
		toastError(<?= json_encode($this->session->flashdata('alert_error')) ?>);
	<?php } ?>
});
</script>

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
