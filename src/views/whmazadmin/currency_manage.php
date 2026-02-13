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
							<h3><i class="fa fa-money-bill-wave"></i> <?= !empty($detail['code']) ? htmlspecialchars($detail['code']) : 'New Currency' ?></h3>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/currency/index">Currencies</a></li>
									<li class="breadcrumb-item active"><a href="#">Manage</a></li>
								</ol>
							</nav>
						</div>
						<a href="<?=base_url()?>whmazadmin/currency/index" class="btn btn-back">
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
					<form method="post" name="entityManageForm" id="entityManageForm" class="company-form" action="<?=base_url()?>whmazadmin/currency/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
						<?=csrf_field()?>
						<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />
						<input name="format" type="hidden" id="format" value="<?= htmlspecialchars($detail['format'] ?? '1', ENT_QUOTES, 'UTF-8') ?>" />

						<!-- Currency Details Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-info-circle"></i> Currency Details
							</div>
							<div class="row">
								<div class="col-md-4">
									<div class="form-group">
										<label class="form-label" for="code"><i class="fa fa-tag"></i> Code</label>
										<input name="code" type="text" class="form-control" id="code" placeholder="USD, EUR, GBP" value="<?= htmlspecialchars($detail['code'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('code', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="form-label" for="symbol"><i class="fa fa-dollar-sign"></i> Symbol</label>
										<input name="symbol" type="text" class="form-control" id="symbol" placeholder="$, €, £" value="<?= htmlspecialchars($detail['symbol'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('symbol', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="form-label" for="rate"><i class="fa fa-exchange-alt"></i> Exchange Rate</label>
										<input name="rate" type="text" class="form-control" id="rate" placeholder="1.00" value="<?= htmlspecialchars($detail['rate'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('rate', '<div class="error">', '</div>'); ?>
									</div>
								</div>
							</div>

							<div class="form-group">
								<div class="custom-checkbox-toggle">
									<input name="is_default" type="checkbox" id="is_default" <?= !empty($detail['is_default']) && $detail['is_default'] == 1 ? 'checked' : ''?>/>
									<label for="is_default">Set as Default Currency</label>
								</div>
							</div>
						</div>

						<!-- Submit Button -->
						<div class="form-group mt-4">
							<button type="submit" class="btn btn-save-company">
								<i class="fa fa-check-circle"></i> Save Currency
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
