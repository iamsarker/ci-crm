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
							<h3><i class="fa fa-envelope"></i> <?= !empty($detail['template_name']) ? htmlspecialchars($detail['template_name']) : 'New Email Template' ?></h3>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/email_template/index">Email Templates</a></li>
									<li class="breadcrumb-item active"><a href="#">Manage</a></li>
								</ol>
							</nav>
						</div>
						<a href="<?=base_url()?>whmazadmin/email_template/index" class="btn btn-back">
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
					<form method="post" name="entityManageForm" id="entityManageForm" class="company-form" action="<?=base_url()?>whmazadmin/email_template/manage/<?= !empty($detail['id']) ? safe_encode($detail['id']) : '' ?>">
						<?=csrf_field()?>
						<input name="id" type="hidden" value="<?= !empty($detail['id']) ? safe_encode($detail['id']) : '' ?>"/>

						<!-- Template Info Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-info-circle"></i> Template Information
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="template_name"><i class="fa fa-tag"></i> Template Name <span class="text-danger">*</span></label>
										<input name="template_name" type="text" class="form-control" id="template_name" placeholder="e.g., Overdue Reminder - First Notice" value="<?= htmlspecialchars($detail['template_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('template_name', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="template_key"><i class="fa fa-key"></i> Template Key <span class="text-danger">*</span></label>
										<input name="template_key" type="text" class="form-control" id="template_key" placeholder="e.g., dunning_reminder_1" value="<?= htmlspecialchars($detail['template_key'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<small class="form-text text-muted">Unique identifier. Use lowercase with underscores (e.g., dunning_reminder_1)</small>
										<?php echo form_error('template_key', '<div class="error">', '</div>'); ?>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="category"><i class="fa fa-folder"></i> Category <span class="text-danger">*</span></label>
										<select name="category" class="form-select" id="category">
											<option value="">-- Select Category --</option>
											<option value="DUNNING" <?= (!empty($detail['category']) && $detail['category'] == 'DUNNING') ? 'selected' : '' ?>>DUNNING - Overdue payment reminders</option>
											<option value="INVOICE" <?= (!empty($detail['category']) && $detail['category'] == 'INVOICE') ? 'selected' : '' ?>>INVOICE - Invoice notifications</option>
											<option value="ORDER" <?= (!empty($detail['category']) && $detail['category'] == 'ORDER') ? 'selected' : '' ?>>ORDER - Order confirmations</option>
											<option value="SERVICE" <?= (!empty($detail['category']) && $detail['category'] == 'SERVICE') ? 'selected' : '' ?>>SERVICE - Service notifications</option>
											<option value="SUPPORT" <?= (!empty($detail['category']) && $detail['category'] == 'SUPPORT') ? 'selected' : '' ?>>SUPPORT - Support ticket emails</option>
											<option value="AUTH" <?= (!empty($detail['category']) && $detail['category'] == 'AUTH') ? 'selected' : '' ?>>AUTH - Authentication emails</option>
											<option value="GENERAL" <?= (!empty($detail['category']) && $detail['category'] == 'GENERAL') ? 'selected' : '' ?>>GENERAL - General notifications</option>
										</select>
										<?php echo form_error('category', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="subject"><i class="fa fa-heading"></i> Email Subject <span class="text-danger">*</span></label>
										<input name="subject" type="text" class="form-control" id="subject" placeholder="e.g., Invoice #{invoice_no} - Payment Overdue" value="<?= htmlspecialchars($detail['subject'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<small class="form-text text-muted">Use placeholders like {client_name}, {invoice_no}, {amount_due}</small>
										<?php echo form_error('subject', '<div class="error">', '</div>'); ?>
									</div>
								</div>
							</div>

							<div class="form-group">
								<div class="custom-checkbox-toggle">
									<input name="status" type="checkbox" id="status" value="1" <?= (!isset($detail['status']) || $detail['status'] == 1) ? 'checked' : '' ?>/>
									<label for="status">Active</label>
								</div>
							</div>
						</div>

						<!-- Placeholders Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-code"></i> Available Placeholders
							</div>
							<div class="alert alert-info mb-0">
								<div class="row">
									<div class="col-md-3">
										<code>{client_name}</code> - Client name<br>
										<code>{company_name}</code> - Company name<br>
										<code>{client_email}</code> - Client email
									</div>
									<div class="col-md-3">
										<code>{invoice_no}</code> - Invoice number<br>
										<code>{invoice_date}</code> - Invoice date<br>
										<code>{due_date}</code> - Due date
									</div>
									<div class="col-md-3">
										<code>{amount_due}</code> - Amount due<br>
										<code>{currency}</code> - Currency code<br>
										<code>{days_overdue}</code> - Days overdue
									</div>
									<div class="col-md-3">
										<code>{invoice_url}</code> - Invoice link<br>
										<code>{site_name}</code> - Site name<br>
										<code>{site_url}</code> - Site URL
									</div>
								</div>
							</div>
						</div>

						<!-- Email Body Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-file-alt"></i> Email Body
							</div>
							<div class="form-group">
								<?php echo form_error('body', '<div class="error">', '</div>'); ?>
								<textarea name="body" id="body" style="display: none"><?= htmlspecialchars($detail['body'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
								<div id="editor" style="height: 300px"><?= !empty($detail['body']) ? xss_cleaner($detail['body']) : '' ?></div>
							</div>
						</div>

						<!-- Submit Button -->
						<div class="form-group mt-4">
							<button type="submit" class="btn btn-save-company">
								<i class="fa fa-check-circle"></i> Save Template
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
		[{ 'color': [] }, { 'background': [] }],
		[{ 'align': [] }],
		['clean']
	];

	var quill = new Quill('#editor', {
		modules: {
			toolbar: toolbarOptions
		},
		placeholder: 'Compose your email template...',
		theme: 'snow'
	});

	quill.on('text-change', function() {
		const content = quill.root.innerHTML.trim();
		document.querySelector('#body').innerText = content;
	});
});
</script>

<?php $this->load->view('whmazadmin/include/footer');?>
