<?php $this->load->view('whmazadmin/include/header'); ?>
<link href="<?=base_url()?>resources/assets/css/admin.list_page.css" rel="stylesheet">

<div class="content content-fluid content-wrapper">
	<div class="container-fluid pd-x-20 pd-lg-x-30 pd-xl-x-40">

		<p class="mt-4">&nbsp;</p>

		<!-- Stats Cards -->
		<div class="row mb-4 mt-4" id="statsRow">
			<div class="col-xl-4 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon primary me-3">
							<i class="fas fa-credit-card"></i>
						</div>
						<div>
							<div class="stats-value" id="totalGateways"><?= count($gateways ?? []) ?></div>
							<div class="stats-label">Total Gateways</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-4 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon success me-3">
							<i class="fas fa-check-circle"></i>
						</div>
						<div>
							<div class="stats-value" id="activeGateways"><?= count(array_filter($gateways ?? [], function($g) { return $g['status'] == 1; })) ?></div>
							<div class="stats-label">Active</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-4 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon warning me-3">
							<i class="fas fa-ban"></i>
						</div>
						<div>
							<div class="stats-value" id="inactiveGateways"><?= count(array_filter($gateways ?? [], function($g) { return $g['status'] != 1; })) ?></div>
							<div class="stats-label">Inactive</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Gateways Table -->
		<div class="card table-card">
			<div class="card-header-single">
				<div>
					<h4 class="mb-1"><i class="fas fa-credit-card me-2"></i>Payment Gateways</h4>
					<nav aria-label="breadcrumb" class="mb-0">
						<ol class="breadcrumb breadcrumb-style1 mb-0">
							<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
							<li class="breadcrumb-item"><a href="#">Billing</a></li>
							<li class="breadcrumb-item active">Payment Gateways</li>
						</ol>
					</nav>
				</div>
			</div>
			<div class="card-body">
				<table id="gatewayListDt" class="table table-hover w-100">
					<thead>
					<tr>
						<th>Gateway</th>
						<th>Type</th>
						<th>Mode</th>
						<th>Currencies</th>
						<th class="text-center">Status</th>
						<th class="text-center">Action</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($gateways as $gateway): ?>
						<?php
						$icon = 'fa-money-bill';
						switch ($gateway['gateway_code']) {
							case 'stripe': $icon = 'fa-credit-card'; break;
							case 'paypal': $icon = 'fab fa-paypal'; break;
							case 'razorpay': $icon = 'fa-rupee-sign'; break;
							case 'paystack': $icon = 'fa-credit-card'; break;
							case 'sslcommerz': $icon = 'fa-mobile-alt'; break;
							case 'bank_transfer': $icon = 'fa-university'; break;
							case 'manual': $icon = 'fa-hand-holding-usd'; break;
						}
						?>
						<tr>
							<td>
								<a href="<?= base_url() ?>whmazadmin/paymentgateway/manage/<?= $gateway['id'] ?>" class="text-decoration-none text-dark">
									<span class="fw-semibold">
										<i class="fas <?= $icon ?> me-1 text-muted"></i>
										<?= htmlspecialchars($gateway['name']) ?>
									</span>
								</a>
								<?php if (!empty($gateway['display_name']) && $gateway['display_name'] !== $gateway['name']): ?>
									<br><small class="text-muted"><?= htmlspecialchars($gateway['display_name']) ?></small>
								<?php endif; ?>
							</td>
							<td>
								<span class="badge bg-secondary">
									<?= isset($gateway_types[$gateway['gateway_type']]) ? $gateway_types[$gateway['gateway_type']] : $gateway['gateway_type'] ?>
								</span>
							</td>
							<td>
								<?php if ($gateway['gateway_type'] !== 'manual' && $gateway['gateway_type'] !== 'bank_transfer'): ?>
									<?php if ($gateway['is_test_mode']): ?>
										<span class="badge bg-warning text-dark"><i class="fas fa-flask me-1"></i>Test</span>
									<?php else: ?>
										<span class="badge bg-success"><i class="fas fa-check me-1"></i>Live</span>
									<?php endif; ?>
								<?php else: ?>
									<span class="text-muted">-</span>
								<?php endif; ?>
							</td>
							<td>
								<small><?= htmlspecialchars(substr($gateway['supported_currencies'], 0, 30)) ?><?= strlen($gateway['supported_currencies']) > 30 ? '...' : '' ?></small>
							</td>
							<td class="text-center">
								<div class="form-check form-switch d-inline-block">
									<input class="form-check-input gateway-status-toggle" type="checkbox"
										data-id="<?= $gateway['id'] ?>"
										data-name="<?= htmlspecialchars($gateway['name'], ENT_QUOTES, 'UTF-8') ?>"
										<?= $gateway['status'] == 1 ? 'checked' : '' ?>>
								</div>
							</td>
							<td class="text-center">
								<a href="<?= base_url() ?>whmazadmin/paymentgateway/manage/<?= $gateway['id'] ?>" class="btn btn-sm btn-outline-primary" title="Configure">
									<i class="fas fa-cog me-1"></i> Configure
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>

		<!-- Quick Links -->
		<div class="row mt-4 mb-4">
			<div class="col-md-6 mb-3">
				<div class="card table-card">
					<div class="card-header-single">
						<h5 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Transactions</h5>
					</div>
					<div class="card-body">
						<p class="text-muted mb-3">View all payment transaction history across gateways.</p>
						<a href="<?= base_url() ?>whmazadmin/paymentgateway/transactions" class="btn btn-sm btn-outline-primary">
							<i class="fas fa-list me-1"></i> View All Transactions
						</a>
					</div>
				</div>
			</div>
			<div class="col-md-6 mb-3">
				<div class="card table-card">
					<div class="card-header-single">
						<h5 class="mb-0"><i class="fas fa-globe me-2"></i>Webhook Logs</h5>
					</div>
					<div class="card-body">
						<p class="text-muted mb-3">Monitor incoming webhook events from payment gateways.</p>
						<a href="<?= base_url() ?>whmazadmin/paymentgateway/webhooks" class="btn btn-sm btn-outline-primary">
							<i class="fas fa-list me-1"></i> View Webhook Logs
						</a>
					</div>
				</div>
			</div>
		</div>

	</div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script'); ?>

<script>
$(function(){
	'use strict'

	$('#gatewayListDt').DataTable({
		"responsive": true,
		"paging": false,
		"searching": false,
		"info": false,
		"language": {
			"emptyTable": '<div class="text-center py-4"><i class="fas fa-credit-card fa-3x text-muted mb-3"></i><p class="text-muted">No payment gateways found</p></div>'
		}
	});

	// Toggle gateway status
	$('.gateway-status-toggle').on('change', function() {
		var $toggle = $(this);
		var id = $toggle.data('id');
		var name = $toggle.data('name');
		var newStatus = $toggle.is(':checked') ? 'Active' : 'Inactive';

		Swal.fire({
			title: 'Change Status?',
			html: 'Set <strong>' + escapeXSS(name) + '</strong> to <strong>' + newStatus + '</strong>?',
			icon: 'question',
			showCancelButton: true,
			confirmButtonColor: '#0168fa',
			cancelButtonColor: '#6c757d',
			confirmButtonText: 'Yes, Update',
			cancelButtonText: 'Cancel',
			reverseButtons: true
		}).then((result) => {
			if (result.isConfirmed) {
				$.ajax({
					url: '<?= base_url() ?>whmazadmin/paymentgateway/toggle_status',
					type: 'POST',
					data: {
						id: id,
						status: $toggle.is(':checked') ? 1 : 0,
						<?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>'
					},
					dataType: 'json',
					success: function(response) {
						if (response.success) {
							Swal.fire({ icon: 'success', title: 'Updated', text: response.message, timer: 1500, showConfirmButton: false });
						} else {
							Swal.fire({ icon: 'error', title: 'Error', text: response.message });
							$toggle.prop('checked', !$toggle.is(':checked'));
						}
					},
					error: function() {
						Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to update status' });
						$toggle.prop('checked', !$toggle.is(':checked'));
					}
				});
			} else {
				// Revert toggle
				$toggle.prop('checked', !$toggle.is(':checked'));
			}
		});
	});
});
</script>

<?php $this->load->view('whmazadmin/include/footer'); ?>
