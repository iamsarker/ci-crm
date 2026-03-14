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
							<h3><i class="fa fa-plus-circle"></i> New Support Ticket</h3>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/ticket/index">Tickets</a></li>
									<li class="breadcrumb-item active"><a href="#">New Ticket</a></li>
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

		<!-- New Ticket Form -->
		<div class="row mt-4">
			<div class="col-12">
				<div class="manage-form-card">
					<?php
						$attributes = array('id' => 'newticketform', 'class' => 'needs-validation');
						echo form_open_multipart("whmazadmin/ticket/add", $attributes);
					?>

					<!-- Customer Selection Section -->
					<div class="company-form-section">
						<div class="section-title">
							<i class="fa fa-user"></i> Customer Information
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group mb-3">
									<label class="form-label" for="company_id"><i class="fa fa-building"></i> Select Customer <span class="text-danger">*</span></label>
									<select name="company_id" id="company_id" class="form-select select2" required>
										<option value="">-- Select Customer --</option>
										<?php if(!empty($customers)): ?>
											<?php foreach($customers as $customer): ?>
												<option value="<?= htmlspecialchars($customer['id'], ENT_QUOTES, 'UTF-8') ?>">
													<?= htmlspecialchars($customer['name'] . ' (' . $customer['email'] . ')', ENT_QUOTES, 'UTF-8') ?>
												</option>
											<?php endforeach; ?>
										<?php endif; ?>
									</select>
									<?php echo form_error('company_id', '<div class="text-danger small mt-1">', '</div>'); ?>
								</div>
							</div>
						</div>
					</div>

					<!-- Ticket Details Section -->
					<div class="company-form-section">
						<div class="section-title">
							<i class="fa fa-ticket-alt"></i> Ticket Details
						</div>
						<div class="row">
							<div class="col-md-12 mb-3">
								<div class="form-group">
									<label class="form-label" for="title"><i class="fa fa-heading"></i> Subject <span class="text-danger">*</span></label>
									<input type="text" name="title" id="title" class="form-control" placeholder="Enter ticket subject" value="<?= set_value('title') ?>" required>
									<?php echo form_error('title', '<div class="text-danger small mt-1">', '</div>'); ?>
								</div>
							</div>
							<div class="col-md-6 mb-3">
								<div class="form-group">
									<label class="form-label" for="ticket_dept_id"><i class="fa fa-sitemap"></i> Department <span class="text-danger">*</span></label>
									<?php echo form_dropdown('ticket_dept_id', $results, set_value('ticket_dept_id'), 'class="form-select" id="ticket_dept_id" required'); ?>
									<?php echo form_error('ticket_dept_id', '<div class="text-danger small mt-1">', '</div>'); ?>
								</div>
							</div>
							<div class="col-md-6 mb-3">
								<div class="form-group">
									<label class="form-label" for="priority"><i class="fa fa-flag"></i> Priority <span class="text-danger">*</span></label>
									<select name="priority" id="priority" class="form-select" required>
										<option value="">-- Select Priority --</option>
										<option value="1" <?= set_select('priority', '1') ?>>Low</option>
										<option value="2" <?= set_select('priority', '2', TRUE) ?>>Medium</option>
										<option value="3" <?= set_select('priority', '3') ?>>High</option>
										<option value="4" <?= set_select('priority', '4') ?>>Critical</option>
									</select>
									<?php echo form_error('priority', '<div class="text-danger small mt-1">', '</div>'); ?>
								</div>
							</div>
						</div>
					</div>

					<!-- Message Section -->
					<div class="company-form-section">
						<div class="section-title">
							<i class="fa fa-comment-alt"></i> Message
						</div>
						<div class="form-group mb-3">
							<label class="form-label" for="editor"><i class="fa fa-edit"></i> Ticket Message <span class="text-danger">*</span></label>
							<div class="editor-container-md">
								<div id="editor"><?= set_value('message') ?></div>
							</div>
							<?php echo form_error('message', '<div class="text-danger small mt-1">', '</div>'); ?>
						</div>
					</div>

					<!-- Attachment Section -->
					<div class="company-form-section">
						<div class="section-title">
							<i class="fa fa-paperclip"></i> Attachments
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group mb-3">
									<label class="form-label"><i class="fa fa-file"></i> Attach Files</label>
									<input type="file" name="attachment[]" class="form-control" multiple
										accept=".gif,.jpg,.jpeg,.png,.pdf,.txt"
										data-max-size="5242880"
										onchange="validateFileUpload(this)">
									<small class="form-text text-muted">Allowed: GIF, JPG, PNG, PDF, TXT. Max size: 5MB per file.</small>
									<?php echo form_error('attachment', '<div class="text-danger small mt-1">', '</div>'); ?>
								</div>
							</div>
						</div>
						<span id="registryData"></span>
					</div>

					<!-- Form Actions -->
					<div class="text-end mt-4">
						<a href="<?=base_url()?>whmazadmin/ticket/index" class="btn btn-secondary me-2">
							<i class="fa fa-times"></i> Cancel
						</a>
						<button type="submit" class="btn btn-save-company">
							<i class="fa fa-paper-plane"></i> Create Ticket
						</button>
					</div>

					<?php echo form_close(); ?>
				</div>
			</div>
		</div>

	</div><!-- container -->
</div><!-- content -->

<?php $this->load->view('whmazadmin/include/footer_script');?>

<script>
$(function() {
	'use strict';

	// Initialize Select2 for customer dropdown
	if ($.fn.select2) {
		$('.select2').select2({
			placeholder: '-- Select Customer --',
			allowClear: true,
			width: '100%'
		});
	}

	// Initialize Quill editor
	var toolbarOptions = [
		['bold', 'italic', 'underline'],
		['link', 'blockquote', 'code-block'],
		[{'list': 'ordered'}, {'list': 'bullet'}],
		[{'script': 'sub'}, {'script': 'super'}],
		[{'indent': '-1'}, {'indent': '+1'}],
		[{'header': [1, 2, 3, 4, 5, 6, false]}],
		[{'font': []}],
	];

	var quill = new Quill('#editor', {
		modules: {
			toolbar: toolbarOptions
		},
		placeholder: 'Compose your message...',
		theme: 'snow'
	});

	// Handle form submission
	$('#newticketform').submit(function() {
		var delta = quill.root.innerHTML;
		$(this).append('<input type="hidden" name="message" value="' + delta + '" />');
		return true;
	});
});

// File upload validation
function validateFileUpload(input) {
	var maxSize = parseInt(input.getAttribute('data-max-size')) || 5242880;
	var files = input.files;

	for (var i = 0; i < files.length; i++) {
		if (files[i].size > maxSize) {
			alert('File "' + files[i].name + '" exceeds the maximum size of 5MB.');
			input.value = '';
			return false;
		}
	}
	return true;
}
</script>

<?php $this->load->view('whmazadmin/include/footer');?>
