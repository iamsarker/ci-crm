<?php $this->load->view('whmazadmin/include/header');?>
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
							<i class="fa fa-puzzle-piece"></i>
						</div>
						<div>
							<div class="stats-value" id="totalModules"><?= count($results ?? []) ?></div>
							<div class="stats-label">Total Modules</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-4 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon success me-3">
							<i class="fa fa-check-circle"></i>
						</div>
						<div>
							<div class="stats-value" id="activeModules"><?= count(array_filter($results ?? [], function($r) { return $r['status'] == 1; })) ?></div>
							<div class="stats-label">Active</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-4 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon warning me-3">
							<i class="fa fa-ban"></i>
						</div>
						<div>
							<div class="stats-value" id="inactiveModules"><?= count(array_filter($results ?? [], function($r) { return $r['status'] != 1; })) ?></div>
							<div class="stats-label">Inactive</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Modules Table -->
		<div class="card table-card">
			<div class="card-header-single">
				<div>
					<h4 class="mb-1"><i class="fa fa-puzzle-piece me-2"></i>Server Modules</h4>
					<nav aria-label="breadcrumb" class="mb-0">
						<ol class="breadcrumb breadcrumb-style1 mb-0">
							<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
							<li class="breadcrumb-item active">Server Modules</li>
						</ol>
					</nav>
				</div>
			</div>
			<div class="card-body">
				<table id="moduleListDt" class="table table-hover w-100">
					<thead>
					<tr>
						<th>Module Name</th>
						<th>Remarks</th>
						<th class="text-center">Status</th>
						<th>Last Updated</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach($results as $row){ ?>
						<tr>
							<td>
								<span class="fw-semibold"><i class="fa fa-puzzle-piece me-1 text-muted"></i><?= htmlspecialchars($row['module_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
							</td>
							<td>
								<small class="text-muted"><?= htmlspecialchars($row['remarks'] ?? '-', ENT_QUOTES, 'UTF-8') ?></small>
							</td>
							<td class="text-center">
								<div class="form-check form-switch d-inline-block">
									<input class="form-check-input module-status-toggle" type="checkbox" data-id="<?= safe_encode($row['id']) ?>" data-name="<?= htmlspecialchars($row['module_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" <?= $row['status'] == 1 ? 'checked' : '' ?>>
								</div>
							</td>
							<td>
								<?php if (!empty($row['updated_on'])): ?>
									<i class="fa fa-clock me-1 text-muted"></i><?= date('M d, Y', strtotime($row['updated_on'])) ?>
								<?php else: ?>
									<span class="text-muted">-</span>
								<?php endif; ?>
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

	$('#moduleListDt').DataTable({
		"responsive": true,
		"paging": false,
		"searching": false,
		"info": false,
		"language": {
			"emptyTable": '<div class="text-center py-4"><i class="fa fa-puzzle-piece fa-3x text-muted mb-3"></i><p class="text-muted">No modules found</p></div>'
		}
	});

	// Status toggle
	$('.module-status-toggle').on('change', function() {
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
					url: '<?= base_url() ?>whmazadmin/service_module/toggle_status_api',
					type: 'POST',
					data: { id: id, <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>' },
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

<?php $this->load->view('whmazadmin/include/footer');?>
