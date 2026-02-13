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
							<h3><i class="fa fa-box"></i> <?= !empty($detail['product_name']) ? htmlspecialchars($detail['product_name']) : 'New Service Product' ?></h3>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/service_product/index">Service Products</a></li>
									<li class="breadcrumb-item active"><a href="#">Manage</a></li>
								</ol>
							</nav>
						</div>
						<a href="<?=base_url()?>whmazadmin/service_product/index" class="btn btn-back">
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
					<form method="post" name="entityManageForm" id="entityManageForm" class="company-form" action="<?=base_url()?>whmazadmin/service_product/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
						<?=csrf_field()?>
						<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

						<!-- Product Details Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-info-circle"></i> Product Details
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="product_name"><i class="fa fa-box"></i> Product Name</label>
										<input name="product_name" type="text" class="form-control" id="product_name" placeholder="Enter product name" value="<?= htmlspecialchars($detail['product_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('product_name', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="product_service_group_id"><i class="fa fa-object-group"></i> Service Group</label>
										<?php echo form_dropdown('product_service_group_id', $service_groups, !empty($detail['product_service_group_id']) ? $detail['product_service_group_id'] : '', 'class="form-select select2" id="product_service_group_id"'); ?>
										<?php echo form_error('product_service_group_id', '<div class="error">', '</div>'); ?>
									</div>
								</div>
							</div>
						</div>

						<!-- Service Configuration Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-cogs"></i> Service Configuration
							</div>
							<div class="row">
								<div class="col-md-4">
									<div class="form-group">
										<label class="form-label" for="product_service_type_id"><i class="fa fa-layer-group"></i> Service Type</label>
										<?php echo form_dropdown('product_service_type_id', $service_types, !empty($detail['product_service_type_id']) ? $detail['product_service_type_id'] : '', 'class="form-select select2" id="product_service_type_id"'); ?>
										<?php echo form_error('product_service_type_id', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="form-label" for="product_service_module_id"><i class="fa fa-puzzle-piece"></i> Module</label>
										<?php echo form_dropdown('product_service_module_id', $service_modules, !empty($detail['product_service_module_id']) ? $detail['product_service_module_id'] : '', 'class="form-select select2" id="product_service_module_id"'); ?>
										<?php echo form_error('product_service_module_id', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label class="form-label" for="server_id"><i class="fa fa-server"></i> Server</label>
										<?php echo form_dropdown('server_id', $servers, !empty($detail['server_id']) ? $detail['server_id'] : '', 'class="form-select select2" id="server_id"'); ?>
										<?php echo form_error('server_id', '<div class="error">', '</div>'); ?>
									</div>
								</div>
							</div>

							<div class="row" id="cp_package_row" style="display:none;">
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="cp_package"><i class="fa fa-archive"></i> cPanel Package Name</label>
										<select name="cp_package" class="form-select" id="cp_package">
											<option value="">-- Select Package --</option>
											<?php if (!empty($detail['cp_package'])): ?>
												<option value="<?= htmlspecialchars($detail['cp_package'], ENT_QUOTES, 'UTF-8') ?>" selected><?= htmlspecialchars($detail['cp_package'], ENT_QUOTES, 'UTF-8') ?></option>
											<?php endif; ?>
										</select>
										<small class="text-muted" id="cp_package_hint">Select service type, module &amp; server first</small>
										<div id="cp_package_loading" style="display:none;">
											<span class="spinner-border spinner-border-sm text-primary" role="status"></span>
											<small class="text-primary">Fetching packages from server...</small>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label"><i class="fa fa-eye-slash"></i> Visibility</label>
										<div class="custom-checkbox-toggle mt-2">
											<input type="checkbox" name="is_hidden" id="is_hidden" value="1" <?= (!empty($detail['is_hidden']) && $detail['is_hidden'] == 1) ? 'checked' : '' ?>>
											<label for="is_hidden">Hidden from client area</label>
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- Description Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-file-alt"></i> Product Description
							</div>
							<div class="form-group">
								<label class="form-label" for="product_desc"><i class="fa fa-align-left"></i> Description</label>
								<textarea name="product_desc" class="form-control" id="product_desc" rows="5" placeholder="Enter product description..."><?= htmlspecialchars($detail['product_desc'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
							</div>
						</div>

						<!-- Submit Button -->
						<div class="form-group mt-4">
							<button type="submit" class="btn btn-save-company">
								<i class="fa fa-check-circle"></i> Save Product
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

	// Mappings from PHP
	var serviceTypeKeys = <?= json_encode($service_type_keys) ?>;
	var moduleKeys = <?= json_encode($module_keys) ?>;
	var hostingTypes = ['SHARED_HOSTING', 'RESELLER_HOSTING'];
	var savedCpPackage = <?= json_encode($detail['cp_package'] ?? '') ?>;
	var packageData = {}; // Store full package details keyed by name

	// Format quota/bandwidth values for display
	function formatSize(val, unit) {
		if (!val || val === 'unlimited') return 'Unlimited';
		var num = parseInt(val);
		if (isNaN(num)) return val;
		if (unit === 'MB' && num >= 1024) return (num / 1024).toFixed(1) + ' GB';
		return num + ' ' + unit;
	}

	// Build HTML description from package details
	function buildPackageDescription(pkg) {
		var lines = [];
		lines.push('<strong>' + escapeXSS(pkg.name) + '</strong>');
		lines.push(formatSize(pkg.quota, 'MB') + ' Disk Space');
		lines.push(formatSize(pkg.bwlimit, 'MB') + ' Bandwidth');
		lines.push((pkg.maxaddon === 'unlimited' ? 'Unlimited' : pkg.maxaddon) + ' Addon Domains');
		lines.push((pkg.maxpark === 'unlimited' ? 'Unlimited' : pkg.maxpark) + ' Parked Domains');
		lines.push((pkg.maxsub === 'unlimited' ? 'Unlimited' : pkg.maxsub) + ' Subdomains');
		lines.push((pkg.maxftp === 'unlimited' ? 'Unlimited' : pkg.maxftp) + ' FTP Accounts');
		lines.push((pkg.maxsql === 'unlimited' ? 'Unlimited' : pkg.maxsql) + ' MySQL Databases');
		lines.push((pkg.maxpop === 'unlimited' ? 'Unlimited' : pkg.maxpop) + ' Email Accounts');
		lines.push((pkg.maxlst === 'unlimited' ? 'Unlimited' : pkg.maxlst) + ' Mailing Lists');
		lines.push(pkg.hasshell === 'y' ? 'Shell Access' : 'No Shell');
		lines.push(pkg.cgi === 'y' ? 'CGI Access' : 'No CGI');

		return '<ul>\n' + lines.map(function(l) { return '<li>' + l + '</li>'; }).join('\n') + '\n</ul>';
	}

	// Check if cPanel section should be visible
	function checkCpanelVisibility() {
		var typeId = $('#product_service_type_id').val();
		var moduleId = $('#product_service_module_id').val();

		var typeKey = serviceTypeKeys[typeId] || '';
		var moduleName = (moduleKeys[moduleId] || '').toLowerCase();

		if (hostingTypes.indexOf(typeKey) !== -1 && moduleName === 'cpanel') {
			$('#cp_package_row').show();
			loadPackagesIfReady();
		} else {
			$('#cp_package_row').hide();
		}
	}

	// Load packages from server when all conditions are met
	function loadPackagesIfReady() {
		var serverId = $('#server_id').val();
		if (!serverId) {
			$('#cp_package_hint').text('Please select a server to load packages');
			return;
		}

		$('#cp_package_loading').show();
		$('#cp_package_hint').hide();
		$('#cp_package').prop('disabled', true);

		$.ajax({
			url: '<?= base_url() ?>whmazadmin/service_product/get_server_packages/' + serverId,
			type: 'GET',
			dataType: 'json',
			success: function(response) {
				$('#cp_package_loading').hide();
				$('#cp_package').prop('disabled', false);

				if (response.success && response.packages) {
					var $select = $('#cp_package');
					$select.empty().append('<option value="">-- Select Package --</option>');
					packageData = {};

					$.each(response.packages, function(i, pkg) {
						packageData[pkg.name] = pkg;
						var selected = (pkg.name === savedCpPackage) ? ' selected' : '';
						$select.append('<option value="' + escapeXSS(pkg.name) + '"' + selected + '>' + escapeXSS(pkg.name) + '</option>');
					});

					$('#cp_package_hint').text(response.packages.length + ' package(s) found').show();

					// If editing and saved package exists, populate description
					if (savedCpPackage && packageData[savedCpPackage] && !$('#product_desc').val().trim()) {
						$('#product_desc').val(buildPackageDescription(packageData[savedCpPackage]));
					}
				} else {
					$('#cp_package_hint').text(response.message || 'Failed to load packages').show();
				}
			},
			error: function() {
				$('#cp_package_loading').hide();
				$('#cp_package').prop('disabled', false);
				$('#cp_package_hint').text('Error connecting to server').show();
			}
		});
	}

	// When a package is selected, populate the description field
	$('#cp_package').on('change', function() {
		var pkgName = $(this).val();
		if (pkgName && packageData[pkgName]) {
			$('#product_desc').val(buildPackageDescription(packageData[pkgName]));
		}
	});

	// Bind change events
	$('#product_service_type_id, #product_service_module_id').on('change', function() {
		checkCpanelVisibility();
	});

	$('#server_id').on('change', function() {
		checkCpanelVisibility();
	});

	// Run on page load (for edit mode)
	checkCpanelVisibility();
});
</script>

<?php $this->load->view('whmazadmin/include/footer');?>
