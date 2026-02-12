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
							<i class="fa fa-envelope"></i>
						</div>
						<div>
							<div class="stats-value" id="totalTemplates">-</div>
							<div class="stats-label">Total Templates</div>
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
							<div class="stats-value" id="activeTemplates">-</div>
							<div class="stats-label">Active</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon info me-3">
							<i class="fa fa-folder"></i>
						</div>
						<div>
							<div class="stats-value" id="categories">-</div>
							<div class="stats-label">Categories</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon warning me-3">
							<i class="fa fa-ban"></i>
						</div>
						<div>
							<div class="stats-value" id="inactiveTemplates">-</div>
							<div class="stats-label">Inactive</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Email Templates Table -->
		<div class="card table-card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<div>
					<h4 class="mb-1"><i class="fa fa-envelope me-2"></i>Email Templates</h4>
					<nav aria-label="breadcrumb" class="mb-0">
						<ol class="breadcrumb breadcrumb-style1 mb-0" style="background: transparent; padding: 0;">
							<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index" class="text-white-50">Dashboard</a></li>
							<li class="breadcrumb-item active text-white">Email Templates</li>
						</ol>
					</nav>
				</div>
				<a href="<?=base_url()?>whmazadmin/email_template/manage" class="btn btn-light btn-sm">
					<i class="fa fa-plus-circle me-1"></i> Add Template
				</a>
			</div>
			<div class="card-body">
				<table id="emailTemplateDt" class="table table-hover w-100"></table>
			</div>
		</div>

	</div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script');?>

<script>
$(function(){
	'use strict'

	$('#emailTemplateDt').DataTable({
		"responsive": true,
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": "<?=base_url()?>whmazadmin/email_template/ssp_list_api/",
			"dataSrc": function(json) {
				$('#totalTemplates').text(json.recordsTotal || 0);
				return json.data;
			}
		},
		"order": [[0, 'desc']],
		"language": {
			"processing": '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
			"emptyTable": '<div class="text-center py-4"><i class="fa fa-envelope fa-3x text-muted mb-3"></i><p class="text-muted">No email templates found</p></div>',
			"zeroRecords": '<div class="text-center py-4"><i class="fa fa-search fa-3x text-muted mb-3"></i><p class="text-muted">No matching templates found</p></div>'
		},
		"columns": [
			{
				"title": "ID",
				"data": "id",
				"width": "5%",
				"visible": false
			},
			{
				"title": "Template Name",
				"data": "template_name",
				"width": "22%",
				render: function(data) {
					return '<span class="fw-semibold"><i class="fa fa-envelope me-1 text-muted"></i>' + escapeXSS(data) + '</span>';
				}
			},
			{
				"title": "Key",
				"data": "template_key",
				"width": "15%",
				render: function(data) {
					return '<code class="text-primary">' + escapeXSS(data) + '</code>';
				}
			},
			{
				"title": "Subject",
				"data": "subject",
				"width": "20%",
				render: function(data) {
					return '<small class="text-muted">' + escapeXSS(data) + '</small>';
				}
			},
			{
				"title": "Category",
				"data": "category",
				"width": "12%",
				render: function(data) {
					var colors = {
						'DUNNING': 'bg-warning text-dark',
						'INVOICE': 'bg-info',
						'ORDER': 'bg-primary',
						'AUTH': 'bg-secondary',
						'SUPPORT': 'bg-success',
						'GENERAL': 'bg-dark'
					};
					var icons = {
						'DUNNING': 'fa-exclamation-triangle',
						'INVOICE': 'fa-file-invoice',
						'ORDER': 'fa-shopping-cart',
						'AUTH': 'fa-key',
						'SUPPORT': 'fa-headset',
						'GENERAL': 'fa-envelope'
					};
					var cls = colors[data] || 'bg-secondary';
					var icon = icons[data] || 'fa-tag';
					return '<span class="badge ' + cls + '"><i class="fa ' + icon + ' me-1"></i>' + escapeXSS(data) + '</span>';
				}
			},
			{
				"title": "Status",
				"data": "status",
				"width": "8%",
				"className": "text-center",
				"orderable": false,
				"searchable": false,
				render: function(data) {
					if (parseInt(data) === 1) {
						return '<span class="badge bg-success"><i class="fa fa-check me-1"></i>Active</span>';
					} else {
						return '<span class="badge bg-danger"><i class="fa fa-times me-1"></i>Inactive</span>';
					}
				}
			},
			{
				"title": "Updated",
				"data": "updated_on",
				"width": "12%",
				"searchable": false,
				render: function(data) {
					if (!data) return '-';
					var date = new Date(data);
					return '<i class="fa fa-clock me-1 text-muted"></i>' + date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
				}
			},
			{
				"title": "Actions",
				"data": "id",
				"width": "10%",
				"className": "text-center",
				"orderable": false,
				"searchable": false,
				"render": function(data, type, row) {
					let idVal = safe_encode(data);
					return '<button type="button" class="btn btn-action btn-manage" onclick="openManage(\'' + idVal + '\')" title="Edit Template"><i class="fa fa-cog"></i></button> ' +
						   '<button type="button" class="btn btn-action btn-delete" onclick="deleteRow(\'' + idVal + '\', \'' + escapeXSS(row['template_name']) + '\')" title="Delete Template"><i class="fa fa-trash"></i></button>';
				}
			}
		]
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
	window.location = "<?=base_url()?>whmazadmin/email_template/manage/" + id;
}

function deleteRow(id, title) {
	Swal.fire({
		title: 'Delete Template?',
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
			window.location = "<?=base_url()?>whmazadmin/email_template/delete_records/" + id;
		}
	});
}
</script>

<?php $this->load->view('whmazadmin/include/footer');?>
