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
							<h3><i class="fa fa-ticket-alt"></i> Ticket #<?= htmlspecialchars($tid ?? '', ENT_QUOTES, 'UTF-8') ?></h3>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/ticket/index">Tickets</a></li>
									<li class="breadcrumb-item active"><a href="#">View Ticket</a></li>
								</ol>
							</nav>
						</div>
						<a href="<?=base_url()?>whmazadmin/ticket/index" class="btn btn-back">
							<i class="fa fa-arrow-left"></i> Back to List
						</a>
					</div>
				</div>
			</div>
		</div>

		<!-- Reply Form Section -->
		<div class="row mt-4">
			<div class="col-12">
				<div class="manage-form-card">
					<?php
						$attributes = array('id' => 'replyticketform');
						echo form_open_multipart("whmazadmin/ticket/replyticket/".$tid, $attributes);
					?>
					<div class="company-form-section">
						<div class="section-title">
							<i class="fa fa-reply"></i> Reply to Ticket
						</div>
						<div class="form-group">
							<label class="form-label" for="editor"><i class="fa fa-comment"></i> Reply Message</label>
							<div class="tx-13 mb-3" style="height: 180px;">
								<div id="editor"></div>
							</div>
							<?php echo form_error('message', '<div class="error">', '</div>'); ?>
						</div>

						<span id="registryData"> </span>

						<div class="row">
							<div class="col-md-10">
								<div class="form-group">
									<label class="form-label"><i class="fa fa-paperclip"></i> Attachment</label>
									<input type="file" name="attachment[]" class="form-control" multiple
										accept=".gif,.jpg,.jpeg,.png,.pdf,.txt"
										data-max-size="5242880"
										onchange="validateFileUpload(this)">
									<small class="form-text text-muted">Allowed: GIF, JPG, PNG, PDF, TXT. Max size: 5MB per file.</small>
									<?php echo form_error('attachment'); ?>
								</div>
							</div>
							<div class="col-md-2">
								<div class="form-group mt-4">
									<button class="btn btn-save-company mt-2" type="submit"><i class="fa fa-reply"></i> Add Reply</button>
								</div>
							</div>
						</div>
					</div>
					<?php echo form_close(); ?>
				</div>
			</div>
		</div>

		<!-- Ticket Replies Section -->
		<div class="row mt-4">
			<div class="col-12">
				<?php if( !empty($replies) ) foreach ($replies as $obj) { ?>
					<div class="manage-form-card mb-3">
						<div class="company-form-section">
							<div class="d-flex justify-content-between align-items-center mb-3">
								<div class="section-title mb-0">
									<i class="fa fa-user"></i> <?= htmlspecialchars($obj['user_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
								</div>
								<span class="text-muted"><?= htmlspecialchars($obj['inserted_on'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
							</div>
							<div class="ticket-message-body mb-3">
								<?= sanitize_html($obj['message'] ?? '') ?>
							</div>
							<div class="ticket-actions">
								<?php
									if( $obj['rating'] == 0 ){
										echo '<a href="'.base_url().'whmazadmin/ticket/likereplies/'.$tid.'/'. $obj["id"].'/5" class="btn btn-sm btn-outline-success"><i class="fa fa-thumbs-up"></i> Like</a>';
									} else {
										echo '<a href="'.base_url().'whmazadmin/ticket/likereplies/'.$tid.'/'. $obj["id"].'/0" class="btn btn-sm btn-outline-danger"><i class="fa fa-thumbs-down"></i> Dislike</a>';
									}

									if( !empty($obj['attachment']) ){
										echo '&nbsp;&nbsp;<a target="_blank" href="'.base_url().'whmazadmin/ticket/vtattachments/'.$tid.'/'.$obj["attachment"].'" class="btn btn-sm btn-outline-primary"><i class="fa fa-paperclip"></i> View Attachment</a>';
									}
								?>
							</div>
						</div>
					</div>
				<?php } ?>

				<!-- Original Ticket -->
				<div class="manage-form-card">
					<div class="company-form-section">
						<div class="d-flex justify-content-between align-items-center mb-3">
							<div class="section-title mb-0">
								<i class="fa fa-user"></i> <?= htmlspecialchars($ticket['user_name'] ?? '', ENT_QUOTES, 'UTF-8') ?> <span class="badge bg-primary">Original</span>
							</div>
							<span class="text-muted"><?= htmlspecialchars($ticket['inserted_on'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
						</div>
						<div class="ticket-message-body mb-3">
							<?= sanitize_html($ticket['message'] ?? '') ?>
						</div>
						<div class="ticket-actions">
							<?php
								if( !empty($ticket['attachment']) ){
									echo '<a target="_blank" href="'.base_url().'supports/vtattachments/'.$tid.'/'.$ticket["attachment"].'" class="btn btn-sm btn-outline-primary"><i class="fa fa-paperclip"></i> View Attachment</a>';
								}
							?>
						</div>
					</div>
				</div>
			</div>
		</div>

	</div><!-- container -->
</div><!-- content -->

<?php $this->load->view('whmazadmin/include/footer_script');?>

<script>
	$(function () {
		'use strict'

		var toolbarOptions = [
			['bold', 'italic', 'underline'], // toggled buttons
			['link', 'blockquote', 'code-block'],

			[{'list': 'ordered'}, {'list': 'bullet'}],
			[{'script': 'sub'}, {'script': 'super'}], // superscript/subscript
			[{'indent': '-1'}, {'indent': '+1'}], // outdent/indent
			[{'header': [1, 2, 3, 4, 5, 6, false]}],
			[{'font': []}],
		];

		var quill = new Quill('#editor', {
			modules: {
				toolbar: toolbarOptions
			},
			placeholder: 'Compose your replies...',
			theme: 'snow'
		});



		$('#replyticketform').submit(function () {
			var delta = quill.root.innerHTML;
			$(this).append('<input type="hidden" name="message" value="' + delta + '" /> ');
			return true;
		});

	});

</script>
<?php $this->load->view('whmazadmin/include/footer');?>
