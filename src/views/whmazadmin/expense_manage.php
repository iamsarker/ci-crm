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
							<h3><i class="fa fa-receipt"></i> <?= !empty($detail['id']) ? 'Edit Expense' : 'New Expense' ?></h3>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/expense/index">Expenses</a></li>
									<li class="breadcrumb-item active"><a href="#">Manage</a></li>
								</ol>
							</nav>
						</div>
						<a href="<?=base_url()?>whmazadmin/expense/index" class="btn btn-back">
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
					<form method="post" name="entityManageForm" id="entityManageForm" class="company-form" action="<?=base_url()?>whmazadmin/expense/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" enctype="multipart/form-data">
						<?=csrf_field()?>
						<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

						<!-- Expense Category & Vendor Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-folder-open"></i> Category & Vendor
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="expense_type_id"><i class="fa fa-folder"></i> Expense Category</label>
										<select name="expense_type_id" id="expense_type_id" class="form-select">
											<option value="">-- Select --</option>
											<?php foreach( $categories as $item  ){?>
												<option value="<?=htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8');?>" <?= !empty($detail['expense_type_id']) && $detail['expense_type_id'] == $item['id'] ? 'selected' : '' ?> ><?=htmlspecialchars($item['expense_type'], ENT_QUOTES, 'UTF-8');?></option>
											<?php } ?>
										</select>
										<?php echo form_error('expense_type_id', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="expense_vendor_id"><i class="fa fa-store"></i> Expense Vendor</label>
										<select name="expense_vendor_id" id="expense_vendor_id" class="form-select">
											<option value="">-- Select --</option>
											<?php foreach( $vendors as $item  ){?>
											<option value="<?=htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8');?>" <?= !empty($detail['expense_vendor_id']) && $detail['expense_vendor_id'] == $item['id'] ? 'selected' : '' ?>><?=htmlspecialchars($item['vendor_name'], ENT_QUOTES, 'UTF-8');?></option>
											<?php } ?>
										</select>
										<?php echo form_error('expense_vendor_id', '<div class="error">', '</div>'); ?>
									</div>
								</div>
							</div>
						</div>

						<!-- Amount Details Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-money-bill-wave"></i> Amount Details
							</div>
							<div class="row">
								<div class="col-md-4">
									<div class="form-group">
										<label class="form-label" for="expense_date"><i class="fa fa-calendar"></i> Expense Date</label>
										<input name="expense_date" type="date" class="form-control" id="expense_date" value="<?= htmlspecialchars($detail['expense_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('expense_date', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="form-label" for="exp_amount"><i class="fa fa-receipt"></i> Expense Amount</label>
										<input name="exp_amount" type="text" class="form-control" id="exp_amount" placeholder="0.00" value="<?= htmlspecialchars($detail['exp_amount'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('exp_amount', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="form-label" for="paid_amount"><i class="fa fa-check-circle"></i> Paid Amount</label>
										<input name="paid_amount" type="text" class="form-control" id="paid_amount" placeholder="0.00" value="<?= htmlspecialchars($detail['paid_amount'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('paid_amount', '<div class="error">', '</div>'); ?>
									</div>
								</div>
							</div>
						</div>

						<!-- Additional Info Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-info-circle"></i> Additional Information
							</div>
							<div class="form-group">
								<label class="form-label" for="remarks"><i class="fa fa-align-left"></i> Remarks</label>
								<textarea name="remarks" rows="3" class="form-control" id="remarks" placeholder="Enter remarks..."><?= htmlspecialchars($detail['remarks'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
								<?php echo form_error('remarks', '<div class="error">', '</div>'); ?>
							</div>

							<div class="form-group">
								<label class="form-label"><i class="fa fa-paperclip"></i> Attachment</label>
								<?php if(!empty($detail['attachment'])): ?>
									<div class="mb-2">
										<label class="form-label text-muted"><i class="fa fa-file"></i> Current Attachments:</label>
										<div class="d-flex flex-wrap gap-2">
											<?php
											$attachments = explode(',', $detail['attachment']);
											foreach($attachments as $file):
												$file = trim($file);
												if(!empty($file)):
													$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
													$icon = in_array($ext, ['jpg','jpeg','png','gif']) ? 'fa-image' : 'fa-file-alt';
											?>
												<a href="<?=base_url()?>uploadedfiles/expenses/<?=htmlspecialchars($file, ENT_QUOTES, 'UTF-8')?>"
												   target="_blank" class="btn btn-sm btn-outline-primary">
													<i class="fa <?=$icon?>"></i> <?=htmlspecialchars($file, ENT_QUOTES, 'UTF-8')?>
												</a>
											<?php
												endif;
											endforeach;
											?>
										</div>
									</div>
								<?php endif; ?>
								<input type="file" name="attachment[]" id="attachment" class="form-control" multiple
									accept=".gif,.jpg,.jpeg,.png,.pdf,.txt"
									data-max-size="5242880"
									onchange="validateFileUpload(this)" />
								<small class="form-text text-muted">Allowed: GIF, JPG, PNG, PDF, TXT. Max size: 5MB per file.<?= !empty($detail['attachment']) ? ' New uploads will replace existing attachments.' : '' ?></small>
								<?php echo form_error('attachment[]'); ?>
							</div>
						</div>

						<!-- Submit Button -->
						<div class="form-group mt-4">
							<button type="submit" class="btn btn-save-company">
								<i class="fa fa-check-circle"></i> Save Expense
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
