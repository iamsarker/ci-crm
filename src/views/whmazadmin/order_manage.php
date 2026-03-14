<?php $this->load->view('whmazadmin/include/header'); ?>
<link rel="stylesheet" href="<?=base_url()?>resources/assets/css/admin.manage_view.css">

<style>
.item-card {
	border: 1px solid #e3e8ee;
	border-radius: 8px;
	margin-bottom: 15px;
	background: #fff;
}
.item-card-header {
	background: linear-gradient(135deg, #f8f9fc 0%, #eef1f6 100%);
	padding: 12px 15px;
	border-bottom: 1px solid #e3e8ee;
	border-radius: 8px 8px 0 0;
	display: flex;
	justify-content: space-between;
	align-items: center;
}
.item-card-header .item-title {
	font-weight: 600;
	color: #1c273c;
	font-size: 14px;
}
.item-card-header .item-status {
	font-size: 12px;
}
.item-card-body {
	padding: 15px;
}
.item-detail-row {
	display: flex;
	margin-bottom: 8px;
}
.item-detail-row .label {
	width: 140px;
	color: #6c757d;
	font-size: 13px;
}
.item-detail-row .value {
	flex: 1;
	font-size: 13px;
	color: #1c273c;
}
.item-actions {
	display: flex;
	gap: 8px;
	margin-top: 12px;
	padding-top: 12px;
	border-top: 1px solid #e3e8ee;
}
.item-actions .btn {
	font-size: 12px;
	padding: 5px 12px;
}
.termination-notice {
	background: #fff3cd;
	border: 1px solid #ffc107;
	border-radius: 6px;
	padding: 8px 12px;
	font-size: 12px;
	margin-top: 10px;
}
.empty-section {
	text-align: center;
	padding: 30px;
	color: #6c757d;
}
.empty-section i {
	font-size: 40px;
	margin-bottom: 10px;
	opacity: 0.5;
}
</style>

<div class="content content-fluid content-wrapper">
	<div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">
		<!-- Page Header -->
		<div class="order-page-header">
			<div class="d-flex justify-content-between align-items-center">
				<div>
					<h3><i class="fas fa-cog me-2"></i> Manage Order #<?php echo htmlspecialchars($order['order_no']); ?></h3>
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item"><a href="<?php echo base_url(); ?>whmazadmin">Dashboard</a></li>
							<li class="breadcrumb-item"><a href="<?php echo base_url(); ?>whmazadmin/order">Orders</a></li>
							<li class="breadcrumb-item active"><a href="#">Manage #<?php echo htmlspecialchars($order['order_no']); ?></a></li>
						</ol>
					</nav>
				</div>
				<a href="<?php echo base_url(); ?>whmazadmin/order" class="btn btn-back">
					<i class="fas fa-arrow-left me-1"></i> Back to Orders
				</a>
			</div>
		</div>

		<div class="manage-form-card">
			<input type="hidden" id="order_id" value="<?php echo safe_encode($order['id']); ?>">
			<input type="hidden" id="csrf_token" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

			<div class="row">
				<!-- Left Column -->
				<div class="col-lg-6">
					<!-- Order Overview -->
					<div class="order-card mb-4">
						<div class="card-header">
							<div class="header-icon"><i class="fas fa-info-circle"></i></div>
							<h6>Order Overview</h6>
						</div>
						<div class="card-body">
							<div class="row">
								<div class="col-md-6">
									<div class="item-detail-row">
										<span class="label">Order Number:</span>
										<span class="value fw-bold">#<?php echo htmlspecialchars($order['order_no']); ?></span>
									</div>
									<div class="item-detail-row">
										<span class="label">Order Date:</span>
										<span class="value"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></span>
									</div>
									<div class="item-detail-row">
										<span class="label">Status:</span>
										<span class="value">
											<?php if ($order['status'] == 1): ?>
												<span class="badge bg-success">Active</span>
											<?php else: ?>
												<span class="badge bg-secondary">Inactive</span>
											<?php endif; ?>
										</span>
									</div>
								</div>
								<div class="col-md-6">
									<div class="item-detail-row">
										<span class="label">Amount:</span>
										<span class="value"><?php echo $order['currency_code']; ?> <?php echo number_format($order['amount'], 2); ?></span>
									</div>
									<div class="item-detail-row">
										<span class="label">Discount:</span>
										<span class="value text-danger">-<?php echo number_format($order['discount_amount'], 2); ?></span>
									</div>
									<div class="item-detail-row">
										<span class="label">Total:</span>
										<span class="value fw-bold"><?php echo $order['currency_code']; ?> <?php echo number_format($order['total_amount'], 2); ?></span>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Customer Info -->
					<div class="order-card mb-4">
						<div class="card-header">
							<div class="header-icon"><i class="fas fa-user"></i></div>
							<h6>Customer</h6>
						</div>
						<div class="card-body">
							<div class="item-detail-row">
								<span class="label">Company:</span>
								<span class="value">
									<a href="<?php echo base_url(); ?>whmazadmin/company/manage/<?php echo safe_encode($order['company_id']); ?>">
										<?php echo htmlspecialchars($order['company_name'] ?: '-'); ?>
									</a>
								</span>
							</div>
							<div class="item-detail-row">
								<span class="label">Contact:</span>
								<span class="value"><?php echo htmlspecialchars(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? '')); ?></span>
							</div>
							<div class="item-detail-row">
								<span class="label">Email:</span>
								<span class="value"><?php echo htmlspecialchars($order['company_email'] ?? ''); ?></span>
							</div>
						</div>
					</div>

					<!-- Order Actions -->
					<div class="order-card mb-4">
						<div class="card-header">
							<div class="header-icon"><i class="fas fa-tools"></i></div>
							<h6>Order Actions</h6>
						</div>
						<div class="card-body">
							<button type="button" class="btn btn-danger" onclick="cancelOrder()">
								<i class="fas fa-times-circle me-1"></i> Cancel Entire Order
							</button>
						</div>
					</div>
				</div>

				<!-- Right Column -->
				<div class="col-lg-6">
					<!-- Domain Items -->
					<div class="order-card mb-4">
						<div class="card-header">
							<div class="header-icon"><i class="fas fa-globe"></i></div>
							<h6>Domain Items (<?php echo count($order['domains']); ?>)</h6>
						</div>
						<div class="card-body">
							<?php if (empty($order['domains'])): ?>
								<div class="empty-section">
									<i class="fas fa-globe"></i>
									<p>No domain items in this order</p>
								</div>
							<?php else: ?>
								<?php foreach ($order['domains'] as $domain): ?>
									<div class="item-card" id="domain-<?php echo $domain['id']; ?>">
										<div class="item-card-header">
											<span class="item-title">
												<i class="fas fa-globe me-1"></i> <?php echo htmlspecialchars($domain['domain']); ?>
											</span>
											<span class="item-status badge bg-<?php echo $domain_statuses[$domain['status']]['class']; ?>">
												<?php echo $domain_statuses[$domain['status']]['label']; ?>
											</span>
										</div>
										<div class="item-card-body">
											<div class="item-detail-row">
												<span class="label">Order Type:</span>
												<span class="value"><?php echo $order_types[$domain['order_type']] ?? 'Unknown'; ?></span>
											</div>
											<div class="item-detail-row">
												<span class="label">Registrar:</span>
												<span class="value"><?php echo htmlspecialchars($domain['registrar_name'] ?? 'Not set'); ?></span>
											</div>
											<div class="item-detail-row">
												<span class="label">Reg. Date:</span>
												<span class="value"><?php echo date('M d, Y', strtotime($domain['reg_date'])); ?></span>
											</div>
											<div class="item-detail-row">
												<span class="label">Exp. Date:</span>
												<span class="value"><?php echo $domain['exp_date'] ? date('M d, Y', strtotime($domain['exp_date'])) : '-'; ?></span>
											</div>
											<div class="item-detail-row">
												<span class="label">Period:</span>
												<span class="value"><?php echo $domain['reg_period']; ?> year(s)</span>
											</div>

											<?php if (!empty($domain['termination_date']) && $domain['status'] != 4): ?>
												<div class="termination-notice">
													<i class="fas fa-exclamation-triangle me-1"></i>
													Scheduled for cancellation on <?php echo date('M d, Y', strtotime($domain['termination_date'])); ?>
												</div>
											<?php endif; ?>

											<?php if ($domain['status'] != 4): // Not cancelled ?>
												<div class="item-actions">
													<button type="button" class="btn btn-outline-primary btn-sm" onclick="changeRegistrar(<?php echo $domain['id']; ?>, '<?php echo htmlspecialchars($domain['domain']); ?>')">
														<i class="fas fa-exchange-alt me-1"></i> Change Registrar
													</button>
													<button type="button" class="btn btn-outline-danger btn-sm" onclick="cancelDomain(<?php echo $domain['id']; ?>, '<?php echo htmlspecialchars($domain['domain']); ?>')">
														<i class="fas fa-times me-1"></i> Cancel
													</button>
												</div>
											<?php endif; ?>
										</div>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
					</div>

					<!-- Service Items -->
					<div class="order-card mb-4">
						<div class="card-header">
							<div class="header-icon"><i class="fas fa-server"></i></div>
							<h6>Hosting Items (<?php echo count($order['services']); ?>)</h6>
						</div>
						<div class="card-body">
							<?php if (empty($order['services'])): ?>
								<div class="empty-section">
									<i class="fas fa-server"></i>
									<p>No hosting items in this order</p>
								</div>
							<?php else: ?>
								<?php foreach ($order['services'] as $service): ?>
									<div class="item-card" id="service-<?php echo $service['id']; ?>">
										<div class="item-card-header">
											<span class="item-title">
												<i class="fas fa-box me-1"></i> <?php echo htmlspecialchars($service['product_name'] ?? 'Unknown Package'); ?>
											</span>
											<span class="item-status badge bg-<?php echo $service_statuses[$service['status']]['class']; ?>">
												<?php echo $service_statuses[$service['status']]['label']; ?>
											</span>
										</div>
										<div class="item-card-body">
											<div class="item-detail-row">
												<span class="label">Group:</span>
												<span class="value"><?php echo htmlspecialchars($service['group_name'] ?? '-'); ?></span>
											</div>
											<div class="item-detail-row">
												<span class="label">Server:</span>
												<span class="value"><?php echo htmlspecialchars($service['server_name'] ?? 'Not assigned'); ?></span>
											</div>
											<div class="item-detail-row">
												<span class="label">Hosting Domain:</span>
												<span class="value"><?php echo htmlspecialchars($service['hosting_domain'] ?? '-'); ?></span>
											</div>
											<div class="item-detail-row">
												<span class="label">Billing Cycle:</span>
												<span class="value"><?php echo htmlspecialchars($service['cycle_name'] ?? '-'); ?></span>
											</div>
											<div class="item-detail-row">
												<span class="label">Reg. Date:</span>
												<span class="value"><?php echo date('M d, Y', strtotime($service['reg_date'])); ?></span>
											</div>
											<div class="item-detail-row">
												<span class="label">Exp. Date:</span>
												<span class="value"><?php echo $service['exp_date'] ? date('M d, Y', strtotime($service['exp_date'])) : '-'; ?></span>
											</div>
											<?php if (!empty($service['cp_username'])): ?>
												<div class="item-detail-row">
													<span class="label">cPanel User:</span>
													<span class="value"><code><?php echo htmlspecialchars($service['cp_username']); ?></code></span>
												</div>
											<?php endif; ?>

											<?php if (!empty($service['termination_date']) && $service['status'] != 4): ?>
												<div class="termination-notice">
													<i class="fas fa-exclamation-triangle me-1"></i>
													Scheduled for cancellation on <?php echo date('M d, Y', strtotime($service['termination_date'])); ?>
												</div>
											<?php endif; ?>

											<?php if ($service['status'] != 4): // Not terminated ?>
												<div class="item-actions">
													<button type="button" class="btn btn-outline-primary btn-sm" onclick="changePackage(<?php echo $service['id']; ?>, '<?php echo htmlspecialchars($service['product_name'] ?? ''); ?>')">
														<i class="fas fa-box me-1"></i> Change Package
													</button>
													<button type="button" class="btn btn-outline-info btn-sm" onclick="changeServer(<?php echo $service['id']; ?>, '<?php echo htmlspecialchars($service['product_name'] ?? ''); ?>')">
														<i class="fas fa-server me-1"></i> Change Server
													</button>
													<button type="button" class="btn btn-outline-danger btn-sm" onclick="cancelService(<?php echo $service['id']; ?>, '<?php echo htmlspecialchars($service['product_name'] ?? ''); ?>')">
														<i class="fas fa-times me-1"></i> Cancel
													</button>
												</div>
											<?php endif; ?>
										</div>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Change Registrar Modal -->
<div class="modal fade" id="changeRegistrarModal" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><i class="fas fa-exchange-alt me-2"></i>Change Domain Registrar</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				<input type="hidden" id="modal_domain_id">
				<p class="mb-3">Changing registrar for: <strong id="modal_domain_name"></strong></p>

				<div class="mb-3">
					<label class="form-label">New Registrar</label>
					<select class="form-select" id="new_registrar_id">
						<?php foreach ($registrars as $reg): ?>
							<option value="<?php echo $reg['id']; ?>"><?php echo htmlspecialchars($reg['name']); ?> (<?php echo $reg['platform']; ?>)</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="form-check mb-3" id="transferCheckContainer">
					<input class="form-check-input" type="checkbox" id="trigger_transfer" checked>
					<label class="form-check-label" for="trigger_transfer">
						Initiate domain transfer to new registrar
					</label>
					<small class="text-muted d-block">If domain is active, this will start the transfer process</small>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" onclick="submitRegistrarChange()">
					<i class="fas fa-save me-1"></i> Save Changes
				</button>
			</div>
		</div>
	</div>
</div>

<!-- Change Package Modal -->
<div class="modal fade" id="changePackageModal" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><i class="fas fa-box me-2"></i>Change Hosting Package</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				<input type="hidden" id="modal_service_id">
				<p class="mb-3">Current package: <strong id="modal_current_package"></strong></p>

				<div class="mb-3">
					<label class="form-label">New Package</label>
					<select class="form-select" id="new_package_id" onchange="loadPricing()">
						<option value="">-- Select Package --</option>
						<?php foreach ($packages as $pkg): ?>
							<option value="<?php echo $pkg['id']; ?>"><?php echo htmlspecialchars($pkg['group_name'] . ' - ' . $pkg['product_name']); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="mb-3" id="pricingContainer" class="d-hidden">
					<label class="form-label">Billing Cycle</label>
					<select class="form-select" id="new_pricing_id">
						<option value="">-- Select Billing Cycle --</option>
					</select>
				</div>

				<div class="form-check mb-3">
					<input class="form-check-input" type="checkbox" id="upgrade_cpanel">
					<label class="form-check-label" for="upgrade_cpanel">
						Apply package change on cPanel server
					</label>
					<small class="text-muted d-block">This will change the cPanel package via WHM API</small>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" onclick="submitPackageChange()">
					<i class="fas fa-save me-1"></i> Save Changes
				</button>
			</div>
		</div>
	</div>
</div>

<!-- Change Server Modal -->
<div class="modal fade" id="changeServerModal" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><i class="fas fa-server me-2"></i>Change Server</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				<input type="hidden" id="modal_server_service_id">
				<p class="mb-3">Service: <strong id="modal_server_service_name"></strong></p>

				<div class="mb-3">
					<label class="form-label">New Server</label>
					<select class="form-select" id="new_server_id" onchange="loadServerPackages()">
						<option value="">-- Select Server --</option>
						<?php foreach ($servers as $srv): ?>
							<option value="<?php echo $srv['id']; ?>"><?php echo htmlspecialchars($srv['name']); ?> (<?php echo $srv['hostname']; ?>)</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="mb-3" id="serverPackageContainer" class="d-hidden">
					<label class="form-label">Package on New Server</label>
					<select class="form-select" id="new_server_package_id">
						<option value="">-- Select Package --</option>
					</select>
				</div>

				<div class="form-check mb-3">
					<input class="form-check-input" type="checkbox" id="migrate_server">
					<label class="form-check-label" for="migrate_server">
						Migrate account to new server
					</label>
					<small class="text-muted d-block">Create account on new server and migrate data</small>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" onclick="submitServerChange()">
					<i class="fas fa-save me-1"></i> Save Changes
				</button>
			</div>
		</div>
	</div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script'); ?>

<script>
const baseUrl = '<?php echo base_url(); ?>';
const csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
const csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';

// Change Registrar
function changeRegistrar(domainId, domainName) {
	$('#modal_domain_id').val(domainId);
	$('#modal_domain_name').text(domainName);
	$('#changeRegistrarModal').modal('show');
}

function submitRegistrarChange() {
	const domainId = $('#modal_domain_id').val();
	const registrarId = $('#new_registrar_id').val();
	const triggerTransfer = $('#trigger_transfer').is(':checked') ? '1' : '0';

	Swal.fire({
		title: 'Updating...',
		text: 'Please wait',
		allowOutsideClick: false,
		showConfirmButton: false,
		didOpen: () => { Swal.showLoading(); }
	});

	$.post(baseUrl + 'whmazadmin/order/update_domain_api', {
		[csrfName]: csrfHash,
		domain_id: safe_encode(domainId),
		registrar_id: registrarId,
		trigger_transfer: triggerTransfer
	}, function(response) {
		$('#changeRegistrarModal').modal('hide');
		if (response.success) {
			Swal.fire({
				icon: 'success',
				title: 'Success',
				text: response.message,
				timer: 2000,
				showConfirmButton: false
			}).then(() => {
				location.reload();
			});
		} else {
			Swal.fire({
				icon: 'error',
				title: 'Error',
				text: response.message
			});
		}
	}, 'json').fail(function() {
		Swal.fire({ icon: 'error', title: 'Error', text: 'Request failed' });
	});
}

// Change Package
function changePackage(serviceId, currentPackage) {
	$('#modal_service_id').val(serviceId);
	$('#modal_current_package').text(currentPackage);
	$('#new_package_id').val('');
	$('#pricingContainer').hide();
	$('#changePackageModal').modal('show');
}

function loadPricing() {
	const packageId = $('#new_package_id').val();
	if (!packageId) {
		$('#pricingContainer').hide();
		return;
	}

	$.get(baseUrl + 'whmazadmin/order/get_pricing_api', { package_id: packageId }, function(response) {
		if (response.success && response.data.length > 0) {
			let options = '<option value="">-- Select Billing Cycle --</option>';
			response.data.forEach(function(item) {
				options += '<option value="' + item.id + '">' + item.cycle_name + ' - $' + parseFloat(item.price).toFixed(2) + '</option>';
			});
			$('#new_pricing_id').html(options);
			$('#pricingContainer').show();
		}
	}, 'json');
}

function submitPackageChange() {
	const serviceId = $('#modal_service_id').val();
	const packageId = $('#new_package_id').val();
	const pricingId = $('#new_pricing_id').val();
	const upgradeCpanel = $('#upgrade_cpanel').is(':checked') ? '1' : '0';

	if (!packageId) {
		Swal.fire({ icon: 'warning', title: 'Warning', text: 'Please select a package' });
		return;
	}

	Swal.fire({
		title: 'Updating...',
		text: 'Please wait',
		allowOutsideClick: false,
		showConfirmButton: false,
		didOpen: () => { Swal.showLoading(); }
	});

	$.post(baseUrl + 'whmazadmin/order/update_service_api', {
		[csrfName]: csrfHash,
		service_id: safe_encode(serviceId),
		package_id: packageId,
		pricing_id: pricingId,
		upgrade_cpanel: upgradeCpanel
	}, function(response) {
		$('#changePackageModal').modal('hide');
		if (response.success) {
			Swal.fire({
				icon: 'success',
				title: 'Success',
				text: response.message,
				timer: 2000,
				showConfirmButton: false
			}).then(() => {
				location.reload();
			});
		} else {
			Swal.fire({
				icon: 'error',
				title: 'Error',
				text: response.message
			});
		}
	}, 'json').fail(function() {
		Swal.fire({ icon: 'error', title: 'Error', text: 'Request failed' });
	});
}

// Change Server
function changeServer(serviceId, serviceName) {
	$('#modal_server_service_id').val(serviceId);
	$('#modal_server_service_name').text(serviceName);
	$('#new_server_id').val('');
	$('#serverPackageContainer').hide();
	$('#changeServerModal').modal('show');
}

function loadServerPackages() {
	const serverId = $('#new_server_id').val();
	if (!serverId) {
		$('#serverPackageContainer').hide();
		return;
	}

	$.get(baseUrl + 'whmazadmin/order/get_packages_api', { server_id: serverId }, function(response) {
		if (response.success && response.data.length > 0) {
			let options = '<option value="">-- Select Package --</option>';
			response.data.forEach(function(item) {
				options += '<option value="' + item.id + '">' + (item.group_name || '') + ' - ' + item.product_name + '</option>';
			});
			$('#new_server_package_id').html(options);
			$('#serverPackageContainer').show();
		} else {
			$('#serverPackageContainer').hide();
		}
	}, 'json');
}

function submitServerChange() {
	const serviceId = $('#modal_server_service_id').val();
	const packageId = $('#new_server_package_id').val();
	const migrateServer = $('#migrate_server').is(':checked') ? '1' : '0';

	if (!packageId) {
		Swal.fire({ icon: 'warning', title: 'Warning', text: 'Please select a package' });
		return;
	}

	Swal.fire({
		title: 'Updating...',
		text: 'Please wait',
		allowOutsideClick: false,
		showConfirmButton: false,
		didOpen: () => { Swal.showLoading(); }
	});

	$.post(baseUrl + 'whmazadmin/order/update_service_api', {
		[csrfName]: csrfHash,
		service_id: safe_encode(serviceId),
		package_id: packageId,
		migrate_server: migrateServer
	}, function(response) {
		$('#changeServerModal').modal('hide');
		if (response.success) {
			Swal.fire({
				icon: 'success',
				title: 'Success',
				text: response.message,
				timer: 2000,
				showConfirmButton: false
			}).then(() => {
				location.reload();
			});
		} else {
			Swal.fire({
				icon: 'error',
				title: 'Error',
				text: response.message
			});
		}
	}, 'json').fail(function() {
		Swal.fire({ icon: 'error', title: 'Error', text: 'Request failed' });
	});
}

// Cancel Domain
function cancelDomain(domainId, domainName) {
	Swal.fire({
		title: 'Cancel Domain',
		html: '<p>Cancel domain: <strong>' + domainName + '</strong></p>' +
			  '<div class="text-start">' +
			  '<div class="mb-3"><label class="form-label">Cancellation Type</label>' +
			  '<select id="swal_cancel_type" class="form-select">' +
			  '<option value="immediate">Immediate - Cancel now</option>' +
			  '<option value="end_of_period">End of Period - Cancel at expiry</option>' +
			  '</select></div>' +
			  '<div class="mb-3"><label class="form-label">Reason (optional)</label>' +
			  '<textarea id="swal_cancel_reason" class="form-control" rows="2"></textarea></div>' +
			  '</div>',
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#d33',
		confirmButtonText: 'Cancel Domain',
		cancelButtonText: 'Close',
		preConfirm: () => {
			return {
				cancelType: document.getElementById('swal_cancel_type').value,
				reason: document.getElementById('swal_cancel_reason').value
			};
		}
	}).then((result) => {
		if (result.isConfirmed) {
			$.post(baseUrl + 'whmazadmin/order/cancel_domain_api', {
				[csrfName]: csrfHash,
				domain_id: safe_encode(domainId),
				cancel_type: result.value.cancelType,
				reason: result.value.reason
			}, function(response) {
				if (response.success) {
					Swal.fire({
						icon: 'success',
						title: 'Success',
						text: response.message,
						timer: 2000,
						showConfirmButton: false
					}).then(() => {
						location.reload();
					});
				} else {
					Swal.fire({ icon: 'error', title: 'Error', text: response.message });
				}
			}, 'json');
		}
	});
}

// Cancel Service
function cancelService(serviceId, serviceName) {
	Swal.fire({
		title: 'Cancel Hosting Service',
		html: '<p>Cancel service: <strong>' + serviceName + '</strong></p>' +
			  '<div class="text-start">' +
			  '<div class="mb-3"><label class="form-label">Cancellation Type</label>' +
			  '<select id="swal_cancel_type" class="form-select">' +
			  '<option value="immediate">Immediate - Cancel now</option>' +
			  '<option value="end_of_period">End of Period - Cancel at expiry</option>' +
			  '</select></div>' +
			  '<div class="mb-3"><label class="form-label">Reason (optional)</label>' +
			  '<textarea id="swal_cancel_reason" class="form-control" rows="2"></textarea></div>' +
			  '<div class="form-check mb-3">' +
			  '<input class="form-check-input" type="checkbox" id="swal_delete_cpanel">' +
			  '<label class="form-check-label" for="swal_delete_cpanel">Delete cPanel account</label>' +
			  '</div></div>',
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#d33',
		confirmButtonText: 'Cancel Service',
		cancelButtonText: 'Close',
		preConfirm: () => {
			return {
				cancelType: document.getElementById('swal_cancel_type').value,
				reason: document.getElementById('swal_cancel_reason').value,
				deleteCpanel: document.getElementById('swal_delete_cpanel').checked ? '1' : '0'
			};
		}
	}).then((result) => {
		if (result.isConfirmed) {
			$.post(baseUrl + 'whmazadmin/order/cancel_service_api', {
				[csrfName]: csrfHash,
				service_id: safe_encode(serviceId),
				cancel_type: result.value.cancelType,
				reason: result.value.reason,
				delete_cpanel: result.value.deleteCpanel
			}, function(response) {
				if (response.success) {
					Swal.fire({
						icon: 'success',
						title: 'Success',
						text: response.message,
						timer: 2000,
						showConfirmButton: false
					}).then(() => {
						location.reload();
					});
				} else {
					Swal.fire({ icon: 'error', title: 'Error', text: response.message });
				}
			}, 'json');
		}
	});
}

// Cancel Entire Order
function cancelOrder() {
	Swal.fire({
		title: 'Cancel Entire Order',
		html: '<p>This will cancel all domains and services in this order.</p>' +
			  '<div class="text-start">' +
			  '<div class="mb-3"><label class="form-label">Cancellation Type</label>' +
			  '<select id="swal_cancel_type" class="form-select">' +
			  '<option value="immediate">Immediate - Cancel everything now</option>' +
			  '<option value="end_of_period">End of Period - Cancel items at their expiry</option>' +
			  '</select></div>' +
			  '<div class="mb-3"><label class="form-label">Reason (optional)</label>' +
			  '<textarea id="swal_cancel_reason" class="form-control" rows="2"></textarea></div>' +
			  '</div>',
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#d33',
		confirmButtonText: 'Cancel Order',
		cancelButtonText: 'Close',
		preConfirm: () => {
			return {
				cancelType: document.getElementById('swal_cancel_type').value,
				reason: document.getElementById('swal_cancel_reason').value
			};
		}
	}).then((result) => {
		if (result.isConfirmed) {
			$.post(baseUrl + 'whmazadmin/order/cancel_order_api', {
				[csrfName]: csrfHash,
				order_id: $('#order_id').val(),
				cancel_type: result.value.cancelType,
				reason: result.value.reason
			}, function(response) {
				if (response.success) {
					Swal.fire({
						icon: 'success',
						title: 'Success',
						text: response.message,
						timer: 2000,
						showConfirmButton: false
					}).then(() => {
						location.reload();
					});
				} else {
					Swal.fire({ icon: 'error', title: 'Error', text: response.message });
				}
			}, 'json');
		}
	});
}
</script>

<?php $this->load->view('whmazadmin/include/footer'); ?>
