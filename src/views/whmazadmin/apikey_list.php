<?php $this->load->view('whmazadmin/include/header');?>
<link href="<?=base_url()?>resources/assets/css/admin.list_page.css" rel="stylesheet">

<div class="content content-fluid content-wrapper">
	<div class="container-fluid pd-x-20 pd-lg-x-30 pd-xl-x-40">

		<p class="mt-4">&nbsp;</p>

		<!-- Stats Cards -->
		<div class="row mb-4 mt-4" id="statsRow">
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card"><div class="card-body d-flex align-items-center">
					<div class="stats-icon primary me-3"><i class="fa fa-key"></i></div>
					<div><div class="stats-value" id="statTotal">-</div><div class="stats-label">Total Keys</div></div>
				</div></div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card"><div class="card-body d-flex align-items-center">
					<div class="stats-icon success me-3"><i class="fa fa-check-circle"></i></div>
					<div><div class="stats-value" id="statActive">-</div><div class="stats-label">Active</div></div>
				</div></div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card"><div class="card-body d-flex align-items-center">
					<div class="stats-icon warning me-3"><i class="fa fa-ban"></i></div>
					<div><div class="stats-value" id="statRevoked">-</div><div class="stats-label">Revoked</div></div>
				</div></div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card"><div class="card-body d-flex align-items-center">
					<div class="stats-icon info me-3"><i class="fa fa-exchange-alt"></i></div>
					<div><div class="stats-value" id="statReq">-</div><div class="stats-label">Requests Today</div></div>
				</div></div>
			</div>
		</div>

		<!-- API Keys Table -->
		<div class="card table-card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<div>
					<h4 class="mb-1"><i class="fa fa-key me-2"></i>API Keys</h4>
					<nav aria-label="breadcrumb" class="mb-0">
						<ol class="breadcrumb breadcrumb-style1 mb-0">
							<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index" class="text-white-50">Dashboard</a></li>
							<li class="breadcrumb-item"><a href="#">Settings</a></li>
							<li class="breadcrumb-item active text-white">API Keys</li>
						</ol>
					</nav>
				</div>
				<a href="<?=base_url()?>whmazadmin/apikey/manage" class="btn btn-light btn-sm">
					<i class="fa fa-plus-circle me-1"></i> Create API Key
				</a>
			</div>
			<div class="card-body">
				<table id="apikeyListDt" class="table table-hover w-100"></table>
			</div>
		</div>

	</div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script');?>

<?php $newCred = $this->session->flashdata('new_api_credential'); ?>
<script>
$(function(){
	'use strict'

	$('#apikeyListDt').DataTable({
		"responsive": true,
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": "<?=base_url()?>whmazadmin/apikey/ssp_list_api/",
			"dataSrc": function(json) {
				if (json.stats) {
					$('#statTotal').text(json.stats.total || 0);
					$('#statActive').text(json.stats.active || 0);
					$('#statRevoked').text(json.stats.revoked || 0);
					$('#statReq').text(json.stats.requests_today || 0);
				}
				return json.data;
			}
		},
		"order": [[0, 'desc']],
		"language": {
			"processing": '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
			"emptyTable": '<div class="text-center py-4"><i class="fa fa-key fa-3x text-muted mb-3"></i><p class="text-muted">No API keys yet</p></div>',
			"zeroRecords": '<div class="text-center py-4"><i class="fa fa-search fa-3x text-muted mb-3"></i><p class="text-muted">No matching keys</p></div>'
		},
		"columns": [
			{
				"title": "Name / Reseller",
				"data": "name",
				"width": "22%",
				render: function(data, type, row) {
					return '<span class="fw-semibold">' + escapeXSS(data || '-') + '</span>' +
						   '<div class="small text-muted"><i class="fa fa-building me-1"></i>' + escapeXSS(row.company_name || '') + '</div>';
				}
			},
			{
				"title": "Key ID",
				"data": "key_id",
				"width": "20%",
				render: function(data, type, row) {
					return '<code>' + escapeXSS(data || '') + '</code>' +
						   '<div class="small text-muted">secret ····' + escapeXSS(row.secret_preview || '') + '</div>';
				}
			},
			{
				"title": "Scopes",
				"data": "scopes",
				"width": "10%",
				"className": "text-center",
				"orderable": false,
				"searchable": false,
				render: function(data) {
					var n = 0;
					try { var arr = JSON.parse(data || '[]'); n = arr.length; } catch(e) {}
					return '<span class="badge bg-light text-dark">' + n + ' scopes</span>';
				}
			},
			{
				"title": "Last Used",
				"data": "last_used_at",
				"width": "14%",
				"searchable": false,
				render: function(data, type, row) {
					if (!data) return '<span class="text-muted small">never</span>';
					return '<span class="small">' + escapeXSS(data) + '</span><div class="small text-muted">' + escapeXSS(row.last_used_ip || '') + '</div>';
				}
			},
			{
				"title": "Status",
				"data": "status",
				"width": "10%",
				"className": "text-center",
				"searchable": false,
				render: function(data, type, row) {
					var expired = row.expires_at && (new Date(row.expires_at.replace(' ','T')) < new Date());
					if (data == 1 && expired) return '<span class="badge bg-warning text-dark">Expired</span>';
					if (data == 1) return '<span class="badge bg-success">Active</span>';
					if (data == 2) return '<span class="badge bg-danger">Revoked</span>';
					return '<span class="badge bg-secondary">-</span>';
				}
			},
			{
				"title": "Actions",
				"data": "id",
				"width": "18%",
				"className": "text-center",
				"orderable": false,
				"searchable": false,
				"render": function(data, type, row) {
					let idVal = safe_encode(data);
					var toggle = (row.status == 1)
						? '<button type="button" class="btn btn-action btn-delete" onclick="setStatus(\'' + idVal + '\',\'revoke\',\'' + escapeXSS(row.name) + '\')" title="Revoke"><i class="fa fa-ban"></i></button> '
						: '<button type="button" class="btn btn-action btn-manage" onclick="setStatus(\'' + idVal + '\',\'activate\',\'' + escapeXSS(row.name) + '\')" title="Re-activate"><i class="fa fa-check"></i></button> ';
					return '<button type="button" class="btn btn-action btn-manage" onclick="regenerate(\'' + idVal + '\',\'' + escapeXSS(row.name) + '\')" title="Regenerate secret"><i class="fa fa-sync"></i></button> ' +
						   '<button type="button" class="btn btn-action btn-manage" onclick="openManage(\'' + idVal + '\')" title="Edit"><i class="fa fa-cog"></i></button> ' +
						   toggle +
						   '<button type="button" class="btn btn-action btn-delete" onclick="deleteRow(\'' + idVal + '\',\'' + escapeXSS(row.name) + '\')" title="Delete"><i class="fa fa-trash"></i></button>';
				}
			}
		]
	});

	<?php if (!empty($newCred)): ?>
	showCredential(<?= json_encode($newCred['key_id']) ?>, <?= json_encode($newCred['secret']) ?>, <?= json_encode($newCred['name']) ?>);
	<?php endif; ?>
});

function showCredential(keyId, secret, name) {
	Swal.fire({
		title: '<i class="fa fa-key text-success"></i> API Credentials',
		html:
			'<p class="mb-2">Copy these now for <strong>' + escapeXSS(name) + '</strong>. The secret is shown <u>only once</u>.</p>' +
			'<div class="text-start">' +
			'<label class="small text-muted mb-1">API Key (X-Api-Key)</label>' +
			'<div class="input-group mb-3"><input id="credKey" class="form-control" readonly value="' + escapeXSS(keyId) + '"><button class="btn btn-outline-secondary" type="button" onclick="copyField(\'credKey\')"><i class="fa fa-copy"></i></button></div>' +
			'<label class="small text-muted mb-1">API Secret (X-Api-Secret)</label>' +
			'<div class="input-group"><input id="credSecret" class="form-control" readonly value="' + escapeXSS(secret) + '"><button class="btn btn-outline-secondary" type="button" onclick="copyField(\'credSecret\')"><i class="fa fa-copy"></i></button></div>' +
			'</div>',
		width: 600,
		confirmButtonText: 'I have copied them',
		allowOutsideClick: false
	});
}

function copyField(id) {
	var el = document.getElementById(id);
	el.select();
	navigator.clipboard.writeText(el.value).then(function(){
		Swal.showValidationMessage ? null : null;
	});
}

function openManage(id) {
	window.location = "<?=base_url()?>whmazadmin/apikey/manage/" + id;
}

function regenerate(id, name) {
	Swal.fire({
		title: 'Regenerate Secret?',
		html: 'Generate a new secret for <strong>' + name + '</strong>?<br><small class="text-muted">The current secret stops working immediately.</small>',
		icon: 'warning', showCancelButton: true, confirmButtonColor: '#0168fa',
		confirmButtonText: 'Regenerate', reverseButtons: true
	}).then((r) => { if (r.isConfirmed) window.location = "<?=base_url()?>whmazadmin/apikey/regenerate/" + id; });
}

function setStatus(id, action, name) {
	var revoke = action === 'revoke';
	Swal.fire({
		title: (revoke ? 'Revoke' : 'Re-activate') + ' Key?',
		html: (revoke ? 'Revoke' : 'Re-activate') + ' <strong>' + name + '</strong>?',
		icon: 'warning', showCancelButton: true,
		confirmButtonColor: revoke ? '#d33' : '#28a745',
		confirmButtonText: revoke ? 'Revoke' : 'Re-activate', reverseButtons: true
	}).then((r) => { if (r.isConfirmed) window.location = "<?=base_url()?>whmazadmin/apikey/" + action + "/" + id; });
}

function deleteRow(id, name) {
	Swal.fire({
		title: 'Delete API Key?',
		html: 'Permanently delete <strong>' + name + '</strong>?<br><small class="text-muted">This cannot be undone.</small>',
		icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33',
		confirmButtonText: '<i class="fa fa-trash me-1"></i> Delete', reverseButtons: true
	}).then((r) => { if (r.isConfirmed) window.location = "<?=base_url()?>whmazadmin/apikey/delete_records/" + id; });
}
</script>

<?php $this->load->view('whmazadmin/include/footer');?>
