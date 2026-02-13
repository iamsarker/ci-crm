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
							<h3><i class="fa fa-sitemap"></i> <?= !empty($detail['name']) ? htmlspecialchars($detail['name']) : 'New Department' ?></h3>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/ticket_department/index">Departments</a></li>
									<li class="breadcrumb-item active"><a href="#">Manage</a></li>
								</ol>
							</nav>
						</div>
						<a href="<?=base_url()?>whmazadmin/ticket_department/index" class="btn btn-back">
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
					<form method="post" name="entityManageForm" id="entityManageForm" class="company-form" action="<?=base_url()?>whmazadmin/ticket_department/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
						<?=csrf_field()?>
						<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

						<!-- Department Details Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-info-circle"></i> Department Details
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="name"><i class="fa fa-building"></i> Department Name</label>
										<input name="name" type="text" class="form-control" id="name" placeholder="Enter department name" value="<?= htmlspecialchars($detail['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('name', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="email"><i class="fa fa-envelope"></i> Email Address</label>
										<input name="email" type="email" class="form-control" id="email" placeholder="department@example.com" value="<?= htmlspecialchars($detail['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('email', '<div class="error">', '</div>'); ?>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="sort_order"><i class="fa fa-sort-numeric-down"></i> Serial #</label>
										<input name="sort_order" type="text" class="form-control" id="sort_order" placeholder="1" value="<?= htmlspecialchars($detail['sort_order'] ?? '1', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('sort_order', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-9">
									<div class="form-group">
										<label class="form-label" for="description"><i class="fa fa-align-left"></i> Description</label>
										<textarea name="description" rows="3" class="form-control" id="description" placeholder="Enter department description..."><?= htmlspecialchars($detail['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
										<?php echo form_error('description', '<div class="error">', '</div>'); ?>
									</div>
								</div>
							</div>
						</div>

						<!-- Submit Button -->
						<div class="form-group mt-4">
							<button type="submit" class="btn btn-save-company">
								<i class="fa fa-check-circle"></i> Save Department
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
