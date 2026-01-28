<?php $this->load->view('whmazadmin/include/header');?>

	<div class="content content-fluid content-wrapper">
		<div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

			<div class="row mt-5">
				<div class="col-md-12 col-sm-12">
					<h3 class="d-flex justify-content-between">
						<span><?= !empty($detail['id']) ? 'Edit' : 'Add' ?> Email Template</span>
						<a href="<?=base_url()?>whmazadmin/email_template/index" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i>&nbsp;Back to List</a>
					</h3>
					<hr class="mg-5" />
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb breadcrumb-style1 mg-b-0">
							<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
							<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/email_template/index">Email Templates</a></li>
							<li class="breadcrumb-item active"><a href="#"><?= !empty($detail['id']) ? 'Edit' : 'Add' ?></a></li>
						</ol>
					</nav>
				</div>

				<div class="col-md-12 col-sm-12 mt-5">
					<form method="post" name="entityManageForm" id="entityManageForm" action="<?=base_url()?>whmazadmin/email_template/manage/<?= !empty($detail['id']) ? safe_encode($detail['id']) : '' ?>">
						<?=csrf_field()?>
						<input name="id" type="hidden" value="<?= !empty($detail['id']) ? safe_encode($detail['id']) : '' ?>"/>

						<!-- Template Info -->
						<div class="card mb-4">
							<div class="card-header bg-primary text-white">
								<h5 class="mb-0">Template Information</h5>
							</div>
							<div class="card-body">
								<div class="row">
									<div class="col-md-6">
										<div class="form-group">
											<label for="template_name">Template Name <span class="text-danger">*</span></label>
											<input name="template_name" type="text" class="form-control" id="template_name" value="<?= htmlspecialchars($detail['template_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g., Overdue Reminder - First Notice"/>
											<?php echo form_error('template_name', '<div class="text-danger">', '</div>'); ?>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label for="template_key">Template Key <span class="text-danger">*</span></label>
											<input name="template_key" type="text" class="form-control" id="template_key" value="<?= htmlspecialchars($detail['template_key'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g., dunning_reminder_1"/>
											<small class="text-muted">Unique identifier. Use lowercase with underscores (e.g., dunning_reminder_1)</small>
											<?php echo form_error('template_key', '<div class="text-danger">', '</div>'); ?>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6">
										<div class="form-group">
											<label for="category">Category <span class="text-danger">*</span></label>
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
											<?php echo form_error('category', '<div class="text-danger">', '</div>'); ?>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group">
											<label for="subject">Email Subject <span class="text-danger">*</span></label>
											<input name="subject" type="text" class="form-control" id="subject" value="<?= htmlspecialchars($detail['subject'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g., Invoice #{invoice_no} - Payment Overdue"/>
											<small class="text-muted">Use placeholders like {client_name}, {invoice_no}, {amount_due}</small>
											<?php echo form_error('subject', '<div class="text-danger">', '</div>'); ?>
										</div>
									</div>
								</div>

								<div class="form-check mb-3">
									<input name="status" type="checkbox" class="form-check-input" id="status" value="1" <?= (!isset($detail['status']) || $detail['status'] == 1) ? 'checked' : '' ?>/>
									<label for="status" class="form-check-label">Active</label>
								</div>
							</div>
						</div>

						<!-- Email Body -->
						<div class="card mb-4">
							<div class="card-header bg-dark text-white">
								<h5 class="mb-0">Email Body</h5>
							</div>
							<div class="card-body">
								<div class="alert alert-info mb-3">
									<i class="fa fa-info-circle"></i> <strong>Available Placeholders:</strong>
									<div class="row mt-2">
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

								<?php echo form_error('body', '<div class="text-danger mb-2">', '</div>'); ?>
								<textarea name="body" id="body" style="display: none"><?= htmlspecialchars($detail['body'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
								<div id="editor" style="height: 300px"><?= !empty($detail['body']) ? xss_cleaner($detail['body']) : '' ?></div>
							</div>
						</div>

						<!-- Submit -->
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
