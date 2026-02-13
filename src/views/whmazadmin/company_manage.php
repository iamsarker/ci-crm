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
							<h3><i class="fa fa-building"></i> <?= !empty($detail['name']) ? htmlspecialchars($detail['name']) : 'New Company' ?></h3>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/company/index">Companies</a></li>
									<li class="breadcrumb-item active"><a href="#">Manage</a></li>
								</ol>
							</nav>
						</div>
						<a href="<?=base_url()?>whmazadmin/company/index" class="btn btn-back">
							<i class="fa fa-arrow-left"></i> Back to List
						</a>
					</div>
				</div>

			</div>
		</div>

		<!-- Tabs Section -->
		<div class="row">
			<div class="col-12">
				<ul class="nav company-tabs" id="pageTab" role="pageTablist">
					<li class="nav-item">
						<a class="nav-link active" id="info-tab" data-bs-toggle="tab" href="#general-info" role="tab" aria-controls="general-info" aria-selected="true">
							<i class="fa fa-info-circle"></i> General Info
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="service-tab" data-bs-toggle="tab" href="#service-info" role="tab" aria-controls="service-info" aria-selected="false">
							<i class="fa fa-server"></i> Services
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="domain-tab" data-bs-toggle="tab" href="#domain-info" role="tab" aria-controls="domain-info" aria-selected="false">
							<i class="fa fa-globe"></i> Domains
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="invoice-tab" data-bs-toggle="tab" href="#invoice-info" role="tab" aria-controls="invoice-info" aria-selected="false">
							<i class="fa fa-file-invoice-dollar"></i> Invoices
						</a>
					</li>
				</ul>

				<div class="tab-content company-tab-content" id="myTabContent">
					<div class="tab-pane fade show active" id="general-info" role="tabpanel" aria-labelledby="info-tab">

						<form method="post" name="entityManageForm" id="entityManageForm" class="company-form" action="<?=base_url()?>whmazadmin/company/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
							<?=csrf_field()?>
							<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

							<!-- Company Information Section -->
							<div class="company-form-section">
								<div class="section-title">
									<i class="fa fa-building"></i> Company Information
								</div>
								<div class="row">
									<div class="col-md-6 col-sm-12">
										<div class="form-group">
											<label class="form-label" for="name"><i class="fa fa-building"></i> Company Name</label>
											<input name="name" type="text" class="form-control" id="name" placeholder="Enter company name" value="<?= htmlspecialchars($detail['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
											<?php echo form_error('name', '<div class="error">', '</div>'); ?>
										</div>
									</div>
									<div class="col-md-3 col-sm-12">
										<div class="form-group">
											<label class="form-label" for="first_name"><i class="fa fa-user"></i> First Name</label>
											<input name="first_name" type="text" class="form-control" id="first_name" placeholder="First name" value="<?= htmlspecialchars($detail['first_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
											<?php echo form_error('first_name', '<div class="error">', '</div>'); ?>
										</div>
									</div>
									<div class="col-md-3 col-sm-12">
										<div class="form-group">
											<label class="form-label" for="last_name"><i class="fa fa-user"></i> Last Name</label>
											<input name="last_name" type="text" class="form-control" id="last_name" placeholder="Last name" value="<?= htmlspecialchars($detail['last_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
											<?php echo form_error('last_name', '<div class="error">', '</div>'); ?>
										</div>
									</div>
								</div>
							</div>

							<!-- Contact Information Section -->
							<div class="company-form-section">
								<div class="section-title">
									<i class="fa fa-address-card"></i> Contact Information
								</div>
								<div class="row">
									<div class="col-md-4 col-sm-12">
										<div class="form-group">
											<label class="form-label" for="email"><i class="fa fa-envelope"></i> Email Address</label>
											<input name="email" type="email" class="form-control" id="email" placeholder="email@example.com" value="<?= htmlspecialchars($detail['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
											<?php echo form_error('email', '<div class="error">', '</div>'); ?>
										</div>
									</div>
									<div class="col-md-4 col-sm-12">
										<div class="form-group">
											<label class="form-label" for="mobile"><i class="fa fa-mobile-alt"></i> Mobile</label>
											<input name="mobile" type="text" class="form-control" id="mobile" placeholder="+880 1XXX XXXXXX" value="<?= htmlspecialchars($detail['mobile'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
											<?php echo form_error('mobile', '<div class="error">', '</div>'); ?>
										</div>
									</div>
									<div class="col-md-4 col-sm-12">
										<div class="form-group">
											<label class="form-label" for="phone"><i class="fa fa-phone"></i> Phone</label>
											<input name="phone" type="text" class="form-control" id="phone" placeholder="Office phone" value="<?= htmlspecialchars($detail['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
											<?php echo form_error('phone', '<div class="error">', '</div>'); ?>
										</div>
									</div>
								</div>
							</div>

							<!-- Address Information Section -->
							<div class="company-form-section">
								<div class="section-title">
									<i class="fa fa-map-marker-alt"></i> Address Information
								</div>
								<div class="row">
									<div class="col-md-6 col-sm-12">
										<div class="form-group">
											<label class="form-label" for="address"><i class="fa fa-map"></i> Street Address</label>
											<input name="address" type="text" class="form-control" id="address" placeholder="Street address" value="<?= htmlspecialchars($detail['address'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
											<?php echo form_error('address', '<div class="error">', '</div>'); ?>
										</div>
									</div>
									<div class="col-md-3 col-sm-12">
										<div class="form-group">
											<label class="form-label" for="city"><i class="fa fa-city"></i> City</label>
											<input name="city" type="text" class="form-control" id="city" placeholder="City" value="<?= htmlspecialchars($detail['city'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
											<?php echo form_error('city', '<div class="error">', '</div>'); ?>
										</div>
									</div>
									<div class="col-md-3 col-sm-12">
										<div class="form-group">
											<label class="form-label" for="zip_code"><i class="fa fa-mail-bulk"></i> Zip Code</label>
											<input name="zip_code" type="text" class="form-control" id="zip_code" placeholder="Zip/Postal code" value="<?= htmlspecialchars($detail['zip_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
											<?php echo form_error('zip_code', '<div class="error">', '</div>'); ?>
										</div>
									</div>
								</div>
								<div class="row mt-3">
									<div class="col-md-6 col-sm-12">
										<div class="form-group">
											<label class="form-label" for="state"><i class="fa fa-map-signs"></i> State/Province</label>
											<input name="state" type="text" class="form-control" id="state" placeholder="State or province" value="<?= htmlspecialchars($detail['state'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
											<?php echo form_error('state', '<div class="error">', '</div>'); ?>
										</div>
									</div>
									<div class="col-md-6 col-sm-12">
										<div class="form-group">
											<label class="form-label" for="country"><i class="fa fa-globe-asia"></i> Country</label>
											<?php echo form_dropdown('country', $countries, !empty($detail['country']) ? $detail['country'] : 'Bangladesh', 'class="form-select select2" id="country"'); ?>
											<?php echo form_error('country', '<div class="error">', '</div>'); ?>
										</div>
									</div>
								</div>
							</div>

							<!-- Submit Button -->
							<div class="form-group mt-4">
								<button type="submit" class="btn btn-save-company">
									<i class="fa fa-check-circle"></i> Save Company
								</button>
							</div>
						</form>

					</div>

					<div class="tab-pane fade" id="service-info" role="tabpanel" aria-labelledby="service-tab">
						<div class="company-datatable-wrapper">
							<div class="table-responsive">
								<table id="serviceListDt" class="table table-hover" style="width: 100%"></table>
							</div>
						</div>
					</div>

					<div class="tab-pane fade" id="domain-info" role="tabpanel" aria-labelledby="domain-tab">
						<div class="company-datatable-wrapper">
							<div class="table-responsive">
								<table id="domainListDt" class="table table-hover" style="width: 100%"></table>
							</div>
						</div>
					</div>

					<div class="tab-pane fade" id="invoice-info" role="tabpanel" aria-labelledby="invoice-tab">
						<div class="company-datatable-wrapper">
							<div class="table-responsive">
								<table id="invoiceListDt" class="table table-hover" style="width: 100%"></table>
							</div>
						</div>
					</div>

				</div>

			</div>
		</div>

	</div><!-- container -->
</div><!-- content -->

<!-- Service Management Modal -->
<div class="modal fade service-modal" id="serviceManageModal" tabindex="-1" aria-labelledby="serviceManageModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="serviceManageModalLabel"><i class="fa fa-server"></i> Manage Service</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form id="serviceManageForm" class="company-form">
					<?=csrf_field()?>
					<input type="hidden" name="service_id" id="modal_service_id" value="">

					<!-- Service Details Section -->
					<div class="company-form-section">
						<div class="section-title">
							<i class="fa fa-info-circle"></i> Service Details
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label class="form-label"><i class="fa fa-hashtag"></i> Service ID</label>
									<input type="text" class="form-control" id="modal_display_id" readonly>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="form-label"><i class="fa fa-tag"></i> Service Type</label>
									<input type="text" class="form-control" id="modal_service_type" readonly>
								</div>
							</div>
						</div>
						<div class="row mt-3">
							<div class="col-md-6">
								<div class="form-group">
									<label class="form-label"><i class="fa fa-globe"></i> Hosting Domain</label>
									<input type="text" class="form-control" id="modal_hosting_domain" readonly>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="form-label"><i class="fa fa-box"></i> Product/Package</label>
									<input type="text" class="form-control" id="modal_product_name" readonly>
								</div>
							</div>
						</div>
					</div>

					<!-- cPanel Configuration Section -->
					<div class="company-form-section">
						<div class="section-title">
							<i class="fa fa-cloud"></i> cPanel Configuration
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label class="form-label"><i class="fa fa-user"></i> cPanel Username <span class="text-danger">*</span></label>
									<input type="text" class="form-control" name="cp_username" id="modal_cp_username" placeholder="Enter cPanel username">
									<small class="text-muted mt-1 d-block">Max 8 characters, lowercase letters and numbers only</small>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="form-label"><i class="fa fa-toggle-on"></i> Service Status</label>
									<select class="form-select" name="service_status" id="modal_service_status">
										<option value="0">Pending</option>
										<option value="1">Active</option>
										<option value="2">Expired</option>
										<option value="3">Suspended</option>
										<option value="4">Terminated</option>
									</select>
								</div>
							</div>
						</div>
						<div class="row mt-3">
							<div class="col-md-6">
								<div class="form-group">
									<label class="form-label"><i class="fa fa-cube"></i> cPanel Package</label>
									<input type="text" class="form-control" id="modal_cp_package" readonly>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="form-label"><i class="fa fa-sync"></i> Sync Status</label>
									<div class="mt-2">
										<span id="modal_sync_status" class="sync-status-badge not-synced">Not synced</span>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div id="cpanel_section" style="display:none;">
						<!-- cPanel Actions Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-terminal"></i> cPanel Actions
							</div>
							<div class="modal-alert-info">
								<small><i class="fa fa-info-circle"></i> <strong>Note:</strong> These actions will directly affect the cPanel server. Use with caution.</small>
							</div>
							<div class="cpanel-actions d-flex flex-wrap gap-2 mt-3">
								<button type="button" class="btn btn-outline-success btn-sm" id="btnCreateCpanel" title="Create cPanel Account">
									<i class="fa fa-plus-circle"></i> Create Account
								</button>
								<button type="button" class="btn btn-outline-info btn-sm" id="btnSyncCpanel" title="Sync from cPanel">
									<i class="fa fa-sync" id="syncIcon"></i> Sync Info
								</button>
								<button type="button" class="btn btn-outline-warning btn-sm" id="btnSuspendCpanel" title="Suspend Account">
									<i class="fa fa-pause-circle"></i> Suspend
								</button>
								<button type="button" class="btn btn-outline-primary btn-sm" id="btnUnsuspendCpanel" title="Unsuspend Account">
									<i class="fa fa-play-circle"></i> Unsuspend
								</button>
								<button type="button" class="btn btn-outline-danger btn-sm" id="btnTerminateCpanel" title="Terminate Account">
									<i class="fa fa-trash"></i> Terminate
								</button>
							</div>
						</div>

						<!-- Usage Stats Section -->
						<div id="usage_stats_section" style="display:none;">
							<div class="company-form-section">
								<div class="section-title">
									<i class="fa fa-chart-pie"></i> Package / Usage Stats
								</div>
								<small class="text-muted d-block mb-3" id="last_sync_time"></small>

								<div class="row">
									<div class="col-md-6">
										<div class="usage-stat-item">
											<div class="stat-header">
												<small><i class="fa fa-hdd"></i> Disk Space</small>
												<small id="disk_usage_text">0 MB / Unlimited</small>
											</div>
											<div class="progress">
												<div class="progress-bar bg-success" id="disk_progress" style="width: 0%"></div>
											</div>
										</div>
										<div class="usage-stat-item">
											<div class="stat-header">
												<small><i class="fa fa-tachometer-alt"></i> Bandwidth</small>
												<small id="bw_usage_text">0 MB / Unlimited</small>
											</div>
											<div class="progress">
												<div class="progress-bar bg-info" id="bw_progress" style="width: 0%"></div>
											</div>
										</div>
										<div class="usage-stat-item">
											<div class="stat-header">
												<small><i class="fa fa-envelope"></i> Email Accounts</small>
												<small id="email_usage_text">0 / Unlimited</small>
											</div>
											<div class="progress">
												<div class="progress-bar bg-primary" id="email_progress" style="width: 0%"></div>
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="usage-stat-item">
											<div class="stat-header">
												<small><i class="fa fa-database"></i> Databases</small>
												<small id="db_usage_text">0 / Unlimited</small>
											</div>
											<div class="progress">
												<div class="progress-bar bg-warning" id="db_progress" style="width: 0%"></div>
											</div>
										</div>
										<div class="usage-stat-item">
											<div class="stat-header">
												<small><i class="fa fa-sitemap"></i> Addon Domains</small>
												<small id="addon_usage_text">0 / Unlimited</small>
											</div>
											<div class="progress">
												<div class="progress-bar bg-secondary" id="addon_progress" style="width: 0%"></div>
											</div>
										</div>
										<div class="usage-stat-item">
											<div class="stat-header">
												<small><i class="fa fa-folder"></i> Subdomains</small>
												<small id="subdomain_usage_text">0 / Unlimited</small>
											</div>
											<div class="progress">
												<div class="progress-bar bg-dark" id="subdomain_progress" style="width: 0%"></div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</form>

				<div id="modal_loading" class="text-center py-5" style="display:none;">
					<div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
						<span class="visually-hidden">Loading...</span>
					</div>
					<p class="mt-3 text-muted">Processing request...</p>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
					<i class="fa fa-times"></i> Close
				</button>
				<button type="button" class="btn btn-save-company" id="btnSaveService">
					<i class="fa fa-save"></i> Save Changes
				</button>
			</div>
		</div>
	</div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script');?>


<script>
	$(function(){
		'use strict'

		// Services DataTable
		var serviceTable = $('#serviceListDt').DataTable({
			"responsive": true,
			"processing": true,
			"serverSide": true,
			"ajax": {
				"url": "<?=base_url()?>" + "whmazadmin/company/ssp_services_api/"+ "<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>",
			},
			"columns": [
				{ "title": "ID", "data": "id" },
				{ "title": "Hosting Domain", "data": "hosting_domain",
					render: function (data, type, row) {
						return data ? data : '-';
					}
				},
				{ "title": "cPanel User", "data": "cp_username",
					render: function (data, type, row) {
						return data ? '<code>' + data + '</code>' : '<span class="text-muted">-</span>';
					}
				},
				{ "title": "First Pay", "data": "first_pay_amount",
					render: function (data, type, row) {
						return parseFloat(data).toFixed(2);
					}
				},
				{ "title": "Recurring", "data": "recurring_amount",
					render: function (data, type, row) {
						return parseFloat(data).toFixed(2);
					}
				},
				{ "title": "Reg Date", "data": "reg_date" },
				{ "title": "Next Renewal", "data": "next_renewal_date" },
				{ "title": "CompanyId", "data": "company_id", "visible": false, "orderable": true, "searchable": true },
				{
					"title": "Status", "data": "status", "orderable": false, "searchable": false,
					render: function (data, type) {
						switch(parseInt(data)) {
							case 0: return '<span class="badge bg-warning">Pending</span>';
							case 1: return '<span class="badge bg-success">Active</span>';
							case 2: return '<span class="badge bg-danger">Expired</span>';
							case 3: return '<span class="badge bg-secondary">Suspended</span>';
							case 4: return '<span class="badge bg-dark">Terminated</span>';
							default: return '<span class="badge bg-secondary">Unknown</span>';
						}
					}
				},
				{
					"title": "Action", "data": "id", "orderable": false, "searchable": false,
					render: function (data, type, row) {
						return '<button type="button" class="btn btn-sm btn-outline-primary btn-manage-service" data-service-id="' + data + '" title="Manage Service"><i class="fa fa-cog"></i> Manage</button>';
					}
				}
			]
		});

		// Domains DataTable
		$('#domainListDt').DataTable({
			"responsive": true,
			"processing": true,
			"serverSide": true,
			"ajax": {
				"url": "<?=base_url()?>" + "whmazadmin/company/ssp_domains_api/"+ "<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>",
			},
			"columns": [
				{ "title": "ID", "data": "id" },
				{ "title": "Domain", "data": "domain" },
				{ "title": "First Pay", "data": "first_pay_amount",
					render: function (data, type, row) {
						return parseFloat(data).toFixed(2);
					}
				},
				{ "title": "Recurring", "data": "recurring_amount",
					render: function (data, type, row) {
						return parseFloat(data).toFixed(2);
					}
				},
				{ "title": "Reg Date", "data": "reg_date" },
				{ "title": "Exp Date", "data": "exp_date" },
				{ "title": "Next Renewal", "data": "next_renewal_date" },
				{ "title": "CompanyId", "data": "company_id", "visible": false, "orderable": true, "searchable": true },
				{
					"title": "Status", "data": "status", "orderable": false, "searchable": false,
					render: function (data, type) {
						switch(parseInt(data)) {
							case 0: return '<span class="badge bg-warning">Pending Reg</span>';
							case 1: return '<span class="badge bg-success">Active</span>';
							case 2: return '<span class="badge bg-danger">Expired</span>';
							case 3: return '<span class="badge bg-info">Grace</span>';
							case 4: return '<span class="badge bg-dark">Cancelled</span>';
							case 5: return '<span class="badge bg-warning">Pending Transfer</span>';
							case 6: return '<span class="badge bg-secondary">Deleted</span>';
							default: return '<span class="badge bg-secondary">Unknown</span>';
						}
					}
				}
			]
		});

		// Invoices DataTable
		$('#invoiceListDt').DataTable({
			"responsive": true,
			"processing": true,
			"serverSide": true,
			"ajax": {
				"url": "<?=base_url()?>" + "whmazadmin/invoice/ssp_list_api/"+ "<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>",
			},
			"columns": [
				{ "title": "Invoice#", "data": "invoice_no" },
				{ "title": "Order#", "data": "order_no" },
				{ "title": "Amount", "data": "total",
					render: function (data, type, row, meta) {
						return parseFloat(data).toFixed(2) + " " + row['currency_code'];
					}
				},
				{ "title": "Due date", "data": "due_date" },
				{ "title": "Currency", "data": "currency_code", "visible": false, "orderable": false, "searchable": false },
				{ "title": "CompanyId", "data": "company_id", "visible": false, "orderable": true, "searchable": true },
				{ "title": "Generate At", "data": "inserted_on", "searchable": false },
				{
					"title": "Pay Status", "data": "pay_status", "orderable": false, "searchable": false,
					render: function (data, type) {
						if( data == "PAID" ){
							return '<span class="badge bg-success">Paid</span>';
						} else if( data == "DUE" ){
							return '<span class="badge bg-danger">Due</span>';
						} else {
							return '<span class="badge bg-warning">Partial</span>';
						}
					}
				},
				{
					"title": "Active?", "data": "status", "orderable": false, "searchable": false,
					render: function (data, type) {
						if( data == 1 ){
							return '<span class="badge bg-success">Yes</span>';
						} else {
							return '<span class="badge bg-danger">No</span>';
						}
					}
				},
				{
					"title" : 'Action',
					"data" : "invoice_uuid",
					"orderable": false,
					"searchable": false,
					"render": function (data, type, row) {
						return '<a href="<?=base_url()?>whmazadmin/invoice/view_invoice/<?= !empty($detail['id']) ? $detail['id'] : 0 ?>/' + data + '" class="btn btn-sm btn-outline-secondary" title="View Invoice"><i class="fa fa-eye"></i></a>';
					}
				}
			]
		});

		// Service Management Modal handlers
		var serviceModal = new bootstrap.Modal(document.getElementById('serviceManageModal'));
		var currentServiceId = null;
		var companyId = "<?= !empty($detail['id']) ? $detail['id'] : 0 ?>";

		// Open modal when clicking Manage button
		$(document).on('click', '.btn-manage-service', function() {
			currentServiceId = $(this).data('service-id');

			// Show modal immediately with loading state
			$('#modal_loading').show();
			$('#serviceManageForm').hide();
			$('#usage_stats_section').hide();
			serviceModal.show();

			// Then load the service details
			loadServiceDetails(currentServiceId);
		});

		// Load service details into modal
		function loadServiceDetails(serviceId) {
			$('#modal_loading').show();
			$('#serviceManageForm').hide();

			$.ajax({
				url: '<?=base_url()?>whmazadmin/company/get_service_detail/' + serviceId + '/' + companyId,
				type: 'GET',
				dataType: 'json',
				success: function(response) {
					$('#modal_loading').hide();
					$('#serviceManageForm').show();

					if (response.success) {
						var data = response.data;
						$('#modal_service_id').val(data.id);
						$('#modal_display_id').val(data.id);
						$('#modal_hosting_domain').val(data.hosting_domain || '-');
						$('#modal_product_name').val(data.product_name || '-');
						$('#modal_service_type').val(data.product_service_type_key || 'OTHER');
						$('#modal_cp_username').val(data.cp_username || '');
						$('#modal_service_status').val(data.status);
						$('#modal_cp_package').val(data.cp_package || 'Not configured');

						// Show sync status
						if (data.is_synced == 1) {
							$('#modal_sync_status').removeClass('not-synced').addClass('synced').text('Synced');
						} else {
							$('#modal_sync_status').removeClass('synced').addClass('not-synced').text('Not synced');
						}

						// Show cPanel section only for hosting types
						var hostingTypes = ['SHARED_HOSTING', 'RESELLER_HOSTING'];
						if (hostingTypes.includes(data.product_service_type_key)) {
							$('#cpanel_section').show();
						} else {
							$('#cpanel_section').hide();
						}
					} else {
						toastError(response.message || 'Failed to load service details');
						serviceModal.hide();
					}
				},
				error: function() {
					$('#modal_loading').hide();
					toastError('Failed to load service details');
					serviceModal.hide();
				}
			});
		}

		// Save service changes
		$('#btnSaveService').on('click', function() {
			var serviceId = $('#modal_service_id').val();
			var cpUsername = $('#modal_cp_username').val();
			var status = $('#modal_service_status').val();

			// Basic validation for cPanel username
			if (cpUsername && !/^[a-z][a-z0-9]{0,7}$/.test(cpUsername)) {
				toastError('cPanel username must start with a letter, contain only lowercase letters and numbers, and be max 8 characters');
				return;
			}

			$.ajax({
				url: '<?=base_url()?>whmazadmin/company/update_service/' + serviceId,
				type: 'POST',
				data: {
					cp_username: cpUsername,
					status: status,
					<?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>'
				},
				dataType: 'json',
				success: function(response) {
					if (response.success) {
						toastSuccess(response.message || 'Service updated successfully');
						serviceModal.hide();
						serviceTable.ajax.reload();
					} else {
						toastError(response.message || 'Failed to update service');
					}
				},
				error: function() {
					toastError('Failed to update service');
				}
			});
		});

		// Create cPanel Account
		$('#btnCreateCpanel').on('click', function() {
			var serviceId = $('#modal_service_id').val();
			var cpUsername = $('#modal_cp_username').val();

			if (!cpUsername) {
				toastError('Please enter a cPanel username first');
				return;
			}

			if (!confirm('Are you sure you want to create a cPanel account for this service?')) {
				return;
			}

			$('#modal_loading').show();
			$('#serviceManageForm').hide();

			$.ajax({
				url: '<?=base_url()?>whmazadmin/company/create_cpanel_account/' + serviceId,
				type: 'POST',
				data: {
					cp_username: cpUsername,
					<?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>'
				},
				dataType: 'json',
				success: function(response) {
					$('#modal_loading').hide();
					$('#serviceManageForm').show();

					if (response.success) {
						toastSuccess(response.message || 'cPanel account created successfully');
						loadServiceDetails(serviceId);
						serviceTable.ajax.reload();
					} else {
						toastError(response.message || 'Failed to create cPanel account');
					}
				},
				error: function() {
					$('#modal_loading').hide();
					$('#serviceManageForm').show();
					toastError('Failed to create cPanel account');
				}
			});
		});

		// Sync cPanel Info
		$('#btnSyncCpanel').on('click', function() {
			var btn = $(this);
			var icon = $('#syncIcon');
			var serviceId = $('#modal_service_id').val();
			var cpUsername = $('#modal_cp_username').val();

			if (!cpUsername) {
				toastError('No cPanel username configured');
				return;
			}

			// Disable button and show spinner
			btn.prop('disabled', true);
			icon.removeClass('fa-sync').addClass('fa-spinner fa-spin');

			$.ajax({
				url: '<?=base_url()?>whmazadmin/company/sync_cpanel_account/' + serviceId,
				type: 'POST',
				data: {
					<?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>'
				},
				dataType: 'json',
				success: function(response) {
					if (response.success && response.stats) {
						var stats = response.stats;

						// Show usage stats section
						$('#usage_stats_section').show();

						// Update disk usage
						var diskDisplay = formatNumber(stats.disk_used, 1) + ' MB / ' + formatLimit(stats.disk_limit, 'MB');
						$('#disk_usage_text').text(diskDisplay);
						$('#disk_progress').css('width', (stats.disk_percent || 0) + '%');

						// Update bandwidth
						var bwDisplay = formatNumber(stats.bandwidth_used, 1) + ' MB / ' + formatLimit(stats.bandwidth_limit, 'MB');
						$('#bw_usage_text').text(bwDisplay);
						$('#bw_progress').css('width', (stats.bandwidth_percent || 0) + '%');

						// Update email accounts
						var emailDisplay = (stats.email_accounts || 0) + ' / ' + formatLimit(stats.email_limit);
						$('#email_usage_text').text(emailDisplay);
						$('#email_progress').css('width', (stats.email_percent || 0) + '%');

						// Update databases
						var dbDisplay = (stats.databases || 0) + ' / ' + formatLimit(stats.database_limit);
						$('#db_usage_text').text(dbDisplay);
						$('#db_progress').css('width', (stats.database_percent || 0) + '%');

						// Update addon domains
						var addonDisplay = (stats.addon_domains || 0) + ' / ' + formatLimit(stats.addon_limit);
						$('#addon_usage_text').text(addonDisplay);
						$('#addon_progress').css('width', (stats.addon_percent || 0) + '%');

						// Update subdomains
						var subdomainDisplay = (stats.subdomains || 0) + ' / ' + formatLimit(stats.subdomain_limit);
						$('#subdomain_usage_text').text(subdomainDisplay);
						$('#subdomain_progress').css('width', (stats.subdomain_percent || 0) + '%');

						// Update last sync time
						$('#last_sync_time').html('<i class="fa fa-clock"></i> Last synced: ' + (stats.last_sync || 'Just now'));

						// Update sync status badge
						$('#modal_sync_status').removeClass('not-synced').addClass('synced').text('Synced');

						toastSuccess(response.message || 'cPanel usage stats synced successfully');
					} else {
						toastError(response.message || 'Failed to sync cPanel info');
					}
				},
				error: function() {
					toastError('Failed to sync cPanel info');
				},
				complete: function() {
					// Re-enable button and restore icon
					btn.prop('disabled', false);
					icon.removeClass('fa-spinner fa-spin').addClass('fa-sync');
				}
			});
		});

		// Helper functions for formatting
		function formatNumber(num, decimals) {
			return parseFloat(num || 0).toFixed(decimals);
		}

		function formatLimit(limit, unit) {
			if (limit === 'unlimited' || limit === 0 || limit === '0' || !limit) {
				return 'Unlimited';
			}
			return limit + (unit ? ' ' + unit : '');
		}

		// Suspend cPanel Account
		$('#btnSuspendCpanel').on('click', function() {
			var serviceId = $('#modal_service_id').val();
			var cpUsername = $('#modal_cp_username').val();

			if (!cpUsername) {
				toastError('No cPanel username configured');
				return;
			}

			if (!confirm('Are you sure you want to SUSPEND the cPanel account "' + cpUsername + '"?')) {
				return;
			}

			$('#modal_loading').show();
			$('#serviceManageForm').hide();

			$.ajax({
				url: '<?=base_url()?>whmazadmin/company/suspend_cpanel_account/' + serviceId,
				type: 'POST',
				data: {
					<?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>'
				},
				dataType: 'json',
				success: function(response) {
					$('#modal_loading').hide();
					$('#serviceManageForm').show();

					if (response.success) {
						toastSuccess(response.message || 'cPanel account suspended successfully');
						loadServiceDetails(serviceId);
						serviceTable.ajax.reload();
					} else {
						toastError(response.message || 'Failed to suspend cPanel account');
					}
				},
				error: function() {
					$('#modal_loading').hide();
					$('#serviceManageForm').show();
					toastError('Failed to suspend cPanel account');
				}
			});
		});

		// Unsuspend cPanel Account
		$('#btnUnsuspendCpanel').on('click', function() {
			var serviceId = $('#modal_service_id').val();
			var cpUsername = $('#modal_cp_username').val();

			if (!cpUsername) {
				toastError('No cPanel username configured');
				return;
			}

			if (!confirm('Are you sure you want to UNSUSPEND the cPanel account "' + cpUsername + '"?')) {
				return;
			}

			$('#modal_loading').show();
			$('#serviceManageForm').hide();

			$.ajax({
				url: '<?=base_url()?>whmazadmin/company/unsuspend_cpanel_account/' + serviceId,
				type: 'POST',
				data: {
					<?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>'
				},
				dataType: 'json',
				success: function(response) {
					$('#modal_loading').hide();
					$('#serviceManageForm').show();

					if (response.success) {
						toastSuccess(response.message || 'cPanel account unsuspended successfully');
						loadServiceDetails(serviceId);
						serviceTable.ajax.reload();
					} else {
						toastError(response.message || 'Failed to unsuspend cPanel account');
					}
				},
				error: function() {
					$('#modal_loading').hide();
					$('#serviceManageForm').show();
					toastError('Failed to unsuspend cPanel account');
				}
			});
		});

		// Terminate cPanel Account
		$('#btnTerminateCpanel').on('click', function() {
			var serviceId = $('#modal_service_id').val();
			var cpUsername = $('#modal_cp_username').val();

			if (!cpUsername) {
				toastError('No cPanel username configured');
				return;
			}

			if (!confirm('WARNING: This will permanently DELETE the cPanel account "' + cpUsername + '" and all its data. This action cannot be undone!\n\nAre you sure you want to continue?')) {
				return;
			}

			$('#modal_loading').show();
			$('#serviceManageForm').hide();

			$.ajax({
				url: '<?=base_url()?>whmazadmin/company/terminate_cpanel_account/' + serviceId,
				type: 'POST',
				data: {
					<?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>'
				},
				dataType: 'json',
				success: function(response) {
					$('#modal_loading').hide();
					$('#serviceManageForm').show();

					if (response.success) {
						toastSuccess(response.message || 'cPanel account terminated successfully');
						loadServiceDetails(serviceId);
						serviceTable.ajax.reload();
					} else {
						toastError(response.message || 'Failed to terminate cPanel account');
					}
				},
				error: function() {
					$('#modal_loading').hide();
					$('#serviceManageForm').show();
					toastError('Failed to terminate cPanel account');
				}
			});
		});

	});

</script>

<?php $this->load->view('whmazadmin/include/footer');?>
