<?php $this->load->view('whmazadmin/include/header');?>
<link href="<?=base_url()?>resources/assets/css/admin.list_page.css" rel="stylesheet">

<div class="content content-fluid content-wrapper">
	<div class="container-fluid pd-x-20 pd-lg-x-30 pd-xl-x-40">

		<p class="mt-4">&nbsp;</p>

		<!-- Stats Cards -->
		<div class="row mb-4 mt-4" id="statsRow">
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon primary me-3">
							<i class="fa fa-coins"></i>
						</div>
						<div>
							<div class="stats-value" id="totalCurrencies"><?= count($results ?? []) ?></div>
							<div class="stats-label">Total Currencies</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon success me-3">
							<i class="fa fa-check-circle"></i>
						</div>
						<div>
							<div class="stats-value" id="activeCurrencies"><?= count(array_filter($results ?? [], function($r) { return $r['status'] == 1; })) ?></div>
							<div class="stats-label">Active</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon info me-3">
							<i class="fa fa-star"></i>
						</div>
						<div>
							<div class="stats-value" id="defaultCurrency"><?= count(array_filter($results ?? [], function($r) { return $r['is_default'] == 1; })) ?></div>
							<div class="stats-label">Default</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon warning me-3">
							<i class="fa fa-exchange-alt"></i>
						</div>
						<div>
							<div class="stats-value" id="exchangeRates"><?= count($results ?? []) ?></div>
							<div class="stats-label">Exchange Rates</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Currencies Table -->
		<div class="card table-card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<div>
					<h4 class="mb-1"><i class="fa fa-coins me-2"></i>Currencies</h4>
					<nav aria-label="breadcrumb" class="mb-0">
						<ol class="breadcrumb breadcrumb-style1 mb-0" style="background: transparent; padding: 0;">
							<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index" class="text-white-50">Dashboard</a></li>
							<li class="breadcrumb-item active text-white">Currencies</li>
						</ol>
					</nav>
				</div>
				<a href="<?=base_url()?>whmazadmin/currency/manage" class="btn btn-light btn-sm">
					<i class="fa fa-plus-circle me-1"></i> Add Currency
				</a>
			</div>
			<div class="card-body">
				<table id="currencyListDt" class="table table-hover w-100">
					<thead>
					<tr>
						<th>Code</th>
						<th>Symbol</th>
						<th class="text-end">Exchange Rate</th>
						<th class="text-center">Default</th>
						<th class="text-center">Status</th>
						<th>Last Updated</th>
						<th class="text-center">Actions</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach($results as $row){ ?>
						<tr>
							<td>
								<span class="fw-bold text-primary"><i class="fa fa-money-bill me-1"></i><?= htmlspecialchars($row['code'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
							</td>
							<td>
								<span class="badge bg-light text-dark fs-6"><?= htmlspecialchars($row['symbol'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
							</td>
							<td class="text-end">
								<span class="fw-semibold"><?= number_format((float)($row['rate'] ?? 0), 4) ?></span>
							</td>
							<td class="text-center">
								<?php if ($row['is_default'] == 1): ?>
									<span class="badge bg-info"><i class="fa fa-star me-1"></i>Default</span>
								<?php else: ?>
									<span class="text-muted">-</span>
								<?php endif; ?>
							</td>
							<td class="text-center">
								<?php if ($row['status'] == 1): ?>
									<span class="badge bg-success"><i class="fa fa-check me-1"></i>Active</span>
								<?php else: ?>
									<span class="badge bg-danger"><i class="fa fa-times me-1"></i>Inactive</span>
								<?php endif; ?>
							</td>
							<td>
								<?php if (!empty($row['updated_on'])): ?>
									<i class="fa fa-clock me-1 text-muted"></i><?= date('M d, Y', strtotime($row['updated_on'])) ?>
								<?php else: ?>
									<span class="text-muted">-</span>
								<?php endif; ?>
							</td>
							<td class="text-center">
								<button type="button" class="btn btn-action btn-manage" onclick="openManage('<?=safe_encode($row['id'])?>')" title="Manage Currency">
									<i class="fa fa-cog"></i>
								</button>
								<button type="button" class="btn btn-action btn-delete" onclick="deleteRow('<?=safe_encode($row['id'])?>', <?= json_encode($row['code'] ?? '') ?>)" title="Delete Currency">
									<i class="fa fa-trash"></i>
								</button>
							</td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
			</div>
		</div>

	</div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script');?>

<script>
$(function(){
	'use strict'

	$('#currencyListDt').DataTable({
		"responsive": true,
		"language": {
			"processing": '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
			"emptyTable": '<div class="text-center py-4"><i class="fa fa-coins fa-3x text-muted mb-3"></i><p class="text-muted">No currencies found</p></div>',
			"zeroRecords": '<div class="text-center py-4"><i class="fa fa-search fa-3x text-muted mb-3"></i><p class="text-muted">No matching currencies found</p></div>'
		}
	});
});

function openManage(id) {
	Swal.fire({
		title: 'Loading...',
		text: 'Please wait',
		allowOutsideClick: false,
		allowEscapeKey: false,
		showConfirmButton: false,
		didOpen: () => { Swal.showLoading(); }
	});
	window.location = "<?=base_url()?>whmazadmin/currency/manage/" + id;
}

function deleteRow(id, title) {
	Swal.fire({
		title: 'Delete Currency?',
		html: 'Are you sure you want to delete <strong>' + escapeXSS(title) + '</strong>?<br><small class="text-muted">This action cannot be undone.</small>',
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#d33',
		cancelButtonColor: '#6c757d',
		confirmButtonText: '<i class="fa fa-trash me-1"></i> Yes, Delete',
		cancelButtonText: 'Cancel',
		reverseButtons: true
	}).then((result) => {
		if (result.isConfirmed) {
			Swal.fire({
				title: 'Deleting...',
				text: 'Please wait',
				allowOutsideClick: false,
				allowEscapeKey: false,
				showConfirmButton: false,
				didOpen: () => { Swal.showLoading(); }
			});
			window.location = "<?=base_url()?>whmazadmin/currency/delete_records/" + id;
		}
	});
}
</script>

<?php $this->load->view('whmazadmin/include/footer');?>
