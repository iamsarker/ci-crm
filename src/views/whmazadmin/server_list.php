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
							<i class="fa fa-server"></i>
						</div>
						<div>
							<div class="stats-value" id="totalServers"><?= count($results ?? []) ?></div>
							<div class="stats-label">Total Servers</div>
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
							<div class="stats-value" id="activeServers"><?= count($results ?? []) ?></div>
							<div class="stats-label">Active Servers</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon info me-3">
							<i class="fa fa-network-wired"></i>
						</div>
						<div>
							<div class="stats-value" id="uniqueIPs"><?= count($results ?? []) ?></div>
							<div class="stats-label">Unique IPs</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon warning me-3">
							<i class="fa fa-globe"></i>
						</div>
						<div>
							<div class="stats-value" id="dnsConfigs"><?= count($results ?? []) ?></div>
							<div class="stats-label">DNS Configured</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Servers Table -->
		<div class="card table-card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<div>
					<h4 class="mb-1"><i class="fa fa-server me-2"></i>Servers</h4>
					<nav aria-label="breadcrumb" class="mb-0">
						<ol class="breadcrumb breadcrumb-style1 mb-0" style="background: transparent; padding: 0;">
							<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index" class="text-white-50">Dashboard</a></li>
							<li class="breadcrumb-item active text-white">Servers</li>
						</ol>
					</nav>
				</div>
				<a href="<?=base_url()?>whmazadmin/server/manage" class="btn btn-light btn-sm">
					<i class="fa fa-plus-circle me-1"></i> Add Server
				</a>
			</div>
			<div class="card-body">
				<table id="serverListDt" class="table table-hover w-100">
					<thead>
					<tr>
						<th>Server Name</th>
						<th>IP Address</th>
						<th>Hostname</th>
						<th>DNS 1</th>
						<th>DNS 2</th>
						<th>DNS 3</th>
						<th>DNS 4</th>
						<th class="text-center">Actions</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach($results as $row){ ?>
						<tr>
							<td>
								<span class="fw-bold text-primary"><i class="fa fa-server me-1"></i><?= htmlspecialchars($row['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
							</td>
							<td>
								<span class="badge bg-light text-dark"><i class="fa fa-network-wired me-1"></i><?= htmlspecialchars($row['ip_addr'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
							</td>
							<td>
								<i class="fa fa-globe me-1 text-muted"></i><?= htmlspecialchars($row['hostname'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
							</td>
							<td><small class="text-muted"><?= htmlspecialchars($row['dns1'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></small></td>
							<td><small class="text-muted"><?= htmlspecialchars($row['dns2'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></small></td>
							<td><small class="text-muted"><?= htmlspecialchars($row['dns3'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></small></td>
							<td><small class="text-muted"><?= htmlspecialchars($row['dns4'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></small></td>
							<td class="text-center">
								<button type="button" class="btn btn-action btn-manage" onclick="openManage('<?=safe_encode($row['id'])?>')" title="Manage Server">
									<i class="fa fa-cog"></i>
								</button>
								<button type="button" class="btn btn-action btn-delete" onclick="deleteRow('<?=safe_encode($row['id'])?>', '<?= htmlspecialchars($row['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>')" title="Delete Server">
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

	$('#serverListDt').DataTable({
		"responsive": true,
		"language": {
			"processing": '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
			"emptyTable": '<div class="text-center py-4"><i class="fa fa-server fa-3x text-muted mb-3"></i><p class="text-muted">No servers found</p></div>',
			"zeroRecords": '<div class="text-center py-4"><i class="fa fa-search fa-3x text-muted mb-3"></i><p class="text-muted">No matching servers found</p></div>'
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
	window.location = "<?=base_url()?>whmazadmin/server/manage/" + id;
}

function deleteRow(id, title) {
	Swal.fire({
		title: 'Delete Server?',
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
			window.location = "<?=base_url()?>whmazadmin/server/delete_records/" + id;
		}
	});
}
</script>

<?php $this->load->view('whmazadmin/include/footer');?>
