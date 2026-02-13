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
							<h3><i class="fa fa-globe"></i> <?= !empty($detail['name']) ? htmlspecialchars($detail['name']) : 'New Domain Registrar' ?></h3>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/domain_register/index">Domain Registrars</a></li>
									<li class="breadcrumb-item active"><a href="#">Manage</a></li>
								</ol>
							</nav>
						</div>
						<a href="<?=base_url()?>whmazadmin/domain_register/index" class="btn btn-back">
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
					<form method="post" name="entityManageForm" id="entityManageForm" class="company-form" action="<?=base_url()?>whmazadmin/domain_register/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
						<?=csrf_field()?>
						<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

						<!-- Registrar Details Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-info-circle"></i> Registrar Details
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="name"><i class="fa fa-building"></i> Registrar Name <span class="text-danger">*</span></label>
										<input name="name" type="text" class="form-control" id="name" placeholder="e.g., Namecheap, GoDaddy" value="<?= htmlspecialchars($detail['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('name', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="platform"><i class="fa fa-code"></i> Platform <span class="text-danger">*</span></label>
										<select name="platform" class="form-select" id="platform">
											<option value="">Select Platform</option>
											<option value="STARGATE" <?= (!empty($detail['platform']) && $detail['platform'] == 'STARGATE') ? 'selected' : ''?>>STARGATE (ResellerClub/Resell.biz)</option>
											<option value="NAMECHEAP" <?= (!empty($detail['platform']) && $detail['platform'] == 'NAMECHEAP') ? 'selected' : ''?>>NAMECHEAP</option>
											<option value="GODADDY" <?= (!empty($detail['platform']) && $detail['platform'] == 'GODADDY') ? 'selected' : ''?>>GODADDY</option>
											<option value="ENOM" <?= (!empty($detail['platform']) && $detail['platform'] == 'ENOM') ? 'selected' : ''?>>ENOM</option>
											<option value="OTHER" <?= (!empty($detail['platform']) && $detail['platform'] == 'OTHER') ? 'selected' : ''?>>OTHER</option>
										</select>
										<?php echo form_error('platform', '<div class="error">', '</div>'); ?>
									</div>
								</div>
							</div>
						</div>

						<!-- Authentication Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-key"></i> Authentication
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="auth_userid"><i class="fa fa-user"></i> Auth User ID <span class="text-danger">*</span></label>
										<input name="auth_userid" type="text" class="form-control" id="auth_userid" placeholder="API User ID" value="<?= htmlspecialchars($detail['auth_userid'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('auth_userid', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="auth_apikey"><i class="fa fa-lock"></i> Auth API Key <span class="text-danger">*</span></label>
										<input name="auth_apikey" type="text" class="form-control" id="auth_apikey" placeholder="API Key or Password" value="<?= htmlspecialchars($detail['auth_apikey'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('auth_apikey', '<div class="error">', '</div>'); ?>
									</div>
								</div>
							</div>
						</div>

						<!-- API Endpoints Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-link"></i> API Endpoints
							</div>
							<div class="form-group">
								<label class="form-label" for="api_base_url"><i class="fa fa-server"></i> API Base URL <span class="text-danger">*</span></label>
								<input name="api_base_url" type="text" class="form-control" id="api_base_url" placeholder="https://api.registrar.com" value="<?= htmlspecialchars($detail['api_base_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
								<?php echo form_error('api_base_url', '<div class="error">', '</div>'); ?>
							</div>

							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="domain_check_api"><i class="fa fa-search"></i> Domain Check API</label>
										<input name="domain_check_api" type="text" class="form-control" id="domain_check_api" placeholder="https://api.registrar.com/domains/check" value="<?= htmlspecialchars($detail['domain_check_api'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="suggestion_api"><i class="fa fa-lightbulb"></i> Suggestion API</label>
										<input name="suggestion_api" type="text" class="form-control" id="suggestion_api" placeholder="https://api.registrar.com/domains/suggest" value="<?= htmlspecialchars($detail['suggestion_api'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="domain_reg_api"><i class="fa fa-plus-circle"></i> Domain Registration API</label>
										<input name="domain_reg_api" type="text" class="form-control" id="domain_reg_api" placeholder="https://api.registrar.com/domains/register" value="<?= htmlspecialchars($detail['domain_reg_api'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="ns_update_api"><i class="fa fa-edit"></i> Nameserver Update API</label>
										<input name="ns_update_api" type="text" class="form-control" id="ns_update_api" placeholder="https://api.registrar.com/domains/modify-ns" value="<?= htmlspecialchars($detail['ns_update_api'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<small class="form-text text-muted">API endpoint for updating domain nameservers</small>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="contact_details_api"><i class="fa fa-address-card"></i> Contact Details API</label>
										<input name="contact_details_api" type="text" class="form-control" id="contact_details_api" placeholder="https://api.registrar.com/domains/details" value="<?= htmlspecialchars($detail['contact_details_api'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<small class="form-text text-muted">API endpoint for fetching domain contact details</small>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="contact_update_api"><i class="fa fa-user-edit"></i> Contact Update API</label>
										<input name="contact_update_api" type="text" class="form-control" id="contact_update_api" placeholder="https://api.registrar.com/domains/modify-contact" value="<?= htmlspecialchars($detail['contact_update_api'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<small class="form-text text-muted">API endpoint for updating domain contact information</small>
									</div>
								</div>
							</div>

							<div class="form-group">
								<label class="form-label" for="price_list_api"><i class="fa fa-dollar-sign"></i> Price List API</label>
								<input name="price_list_api" type="text" class="form-control" id="price_list_api" placeholder="https://api.registrar.com/pricing/list" value="<?= htmlspecialchars($detail['price_list_api'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
							</div>
						</div>

						<!-- Default Nameservers Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-sitemap"></i> Default Nameservers
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="def_ns1"><i class="fa fa-globe"></i> Nameserver 1</label>
										<input name="def_ns1" type="text" class="form-control" id="def_ns1" placeholder="ns1.yourdns.com" value="<?= htmlspecialchars($detail['def_ns1'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="def_ns2"><i class="fa fa-globe"></i> Nameserver 2</label>
										<input name="def_ns2" type="text" class="form-control" id="def_ns2" placeholder="ns2.yourdns.com" value="<?= htmlspecialchars($detail['def_ns2'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="def_ns3"><i class="fa fa-globe"></i> Nameserver 3</label>
										<input name="def_ns3" type="text" class="form-control" id="def_ns3" placeholder="ns3.yourdns.com" value="<?= htmlspecialchars($detail['def_ns3'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="def_ns4"><i class="fa fa-globe"></i> Nameserver 4</label>
										<input name="def_ns4" type="text" class="form-control" id="def_ns4" placeholder="ns4.yourdns.com" value="<?= htmlspecialchars($detail['def_ns4'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
									</div>
								</div>
							</div>

							<div class="form-group">
								<div class="custom-checkbox-toggle">
									<input type="checkbox" id="is_selected" name="is_selected" value="1" <?= (!empty($detail['is_selected']) && $detail['is_selected'] == 1) ? 'checked' : ''?>>
									<label for="is_selected">Set as Default Registrar</label>
								</div>
							</div>
						</div>

						<!-- Submit Button -->
						<div class="form-group mt-4">
							<button type="submit" class="btn btn-save-company">
								<i class="fa fa-check-circle"></i> Save Registrar
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
