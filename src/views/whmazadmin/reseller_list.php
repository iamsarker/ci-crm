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
						<div class="stats-icon primary me-3"><i class="fa fa-user-tie"></i></div>
						<div>
							<div class="stats-value" id="statTotal">-</div>
							<div class="stats-label">Total Resellers</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon success me-3"><i class="fa fa-users"></i></div>
						<div>
							<div class="stats-value" id="statSubs">-</div>
							<div class="stats-label">Sub-Customers</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon info me-3"><i class="fa fa-key"></i></div>
						<div>
							<div class="stats-value" id="statKeys">-</div>
							<div class="stats-label">Active API Keys</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon warning me-3"><i class="fa fa-plug"></i></div>
						<div>
							<div class="stats-value"><a href="<?=base_url()?>whmazadmin/apikey/index" class="text-decoration-none">Manage</a></div>
							<div class="stats-label">API Keys</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Resellers Table -->
		<div class="card table-card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<div>
					<h4 class="mb-1"><i class="fa fa-user-tie me-2"></i>Reseller Management</h4>
					<nav aria-label="breadcrumb" class="mb-0">
						<ol class="breadcrumb breadcrumb-style1 mb-0">
							<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index" class="text-white-50">Dashboard</a></li>
							<li class="breadcrumb-item"><a href="#">Settings</a></li>
							<li class="breadcrumb-item active text-white">Resellers</li>
						</ol>
					</nav>
				</div>
				<a href="<?=base_url()?>whmazadmin/reseller/manage" class="btn btn-light btn-sm">
					<i class="fa fa-plus-circle me-1"></i> Add Reseller
				</a>
			</div>
			<div class="card-body">
				<table id="resellerListDt" class="table table-hover w-100"></table>
			</div>
		</div>

	</div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script');?>

<script>
$(function(){
	'use strict'

	$('#resellerListDt').DataTable({
		"responsive": true,
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": "<?=base_url()?>whmazadmin/reseller/ssp_list_api/",
			"dataSrc": function(json) {
				if (json.stats) {
					$('#statTotal').text(json.stats.total || 0);
					$('#statSubs').text(json.stats.sub_customers || 0);
					$('#statKeys').text(json.stats.api_keys || 0);
				}
				return json.data;
			}
		},
		"order": [[0, 'asc']],
		"language": {
			"processing": '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
			"emptyTable": '<div class="text-center py-4"><i class="fa fa-user-tie fa-3x text-muted mb-3"></i><p class="text-muted">No resellers yet</p></div>',
			"zeroRecords": '<div class="text-center py-4"><i class="fa fa-search fa-3x text-muted mb-3"></i><p class="text-muted">No matching resellers found</p></div>'
		},
		"columns": [
			{
				"title": "Reseller",
				"data": "company_name",
				"width": "26%",
				render: function(data, type, row) {
					var email = row.company_email ? '<div class="small text-muted">' + escapeXSS(row.company_email) + '</div>' : '';
					return '<span class="fw-semibold"><i class="fa fa-building me-1 text-muted"></i>' + escapeXSS(data || '-') + '</span>' + email;
				}
			},
			{
				"title": "Discount",
				"data": "discount_value",
				"width": "12%",
				"searchable": false,
				render: function(data, type, row) {
					if (row.discount_type === 'percent') {
						return '<span class="badge bg-info">' + parseFloat(data).toFixed(2) + '%</span>';
					}
					return '<span class="badge bg-primary">' + parseFloat(data).toFixed(2) + ' Fixed</span>';
				}
			},
			{
				"title": "Credit",
				"data": "credit_balance",
				"width": "12%",
				"searchable": false,
				render: function(data, type, row) {
					var cur = row.currency_code ? (escapeXSS(row.currency_code) + ' ') : '';
					return '<span class="fw-semibold">' + cur + parseFloat(data || 0).toFixed(2) + '</span>';
				}
			},
			{
				"title": "Sub-Customers",
				"data": "sub_customer_count",
				"width": "12%",
				"className": "text-center",
				"searchable": false,
				render: function(data) {
					return '<span class="badge bg-light text-dark"><i class="fa fa-users me-1"></i>' + (data || 0) + '</span>';
				}
			},
			{
				"title": "API",
				"data": "allow_api",
				"width": "12%",
				"className": "text-center",
				"searchable": false,
				render: function(data, type, row) {
					var keys = row.active_api_keys || 0;
					if (data == 1) {
						return '<span class="badge bg-success"><i class="fa fa-plug me-1"></i>Enabled</span> <span class="badge bg-light text-dark">' + keys + ' keys</span>';
					}
					return '<span class="badge bg-secondary">Disabled</span>';
				}
			},
			{
				"title": "Actions",
				"data": "id",
				"width": "14%",
				"className": "text-center",
				"orderable": false,
				"searchable": false,
				"render": function(data, type, row) {
					let idVal = safe_encode(data);
					return '<a class="btn btn-action btn-manage" href="<?=base_url()?>whmazadmin/apikey/index?company=' + row.company_id + '" title="API Keys"><i class="fa fa-key"></i></a> ' +
						   '<button type="button" class="btn btn-action btn-manage" onclick="openManage(\'' + idVal + '\')" title="Edit"><i class="fa fa-cog"></i></button> ' +
						   '<button type="button" class="btn btn-action btn-delete" onclick="deleteRow(\'' + idVal + '\', \'' + escapeXSS(row['company_name']) + '\')" title="Remove"><i class="fa fa-trash"></i></button>';
				}
			}
		]
	});
});

function openManage(id) {
	Swal.fire({ title: 'Loading...', text: 'Please wait', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false, didOpen: () => { Swal.showLoading(); } });
	window.location = "<?=base_url()?>whmazadmin/reseller/manage/" + id;
}

function deleteRow(id, name) {
	Swal.fire({
		title: 'Remove Reseller?',
		html: 'Remove <strong>' + name + '</strong> as a reseller?<br><small class="text-muted">Sub-customers will be detached. The company itself is not deleted.</small>',
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#d33',
		cancelButtonColor: '#6c757d',
		confirmButtonText: '<i class="fa fa-trash me-1"></i> Yes, Remove',
		cancelButtonText: 'Cancel',
		reverseButtons: true
	}).then((result) => {
		if (result.isConfirmed) {
			Swal.fire({ title: 'Removing...', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false, didOpen: () => { Swal.showLoading(); } });
			window.location = "<?=base_url()?>whmazadmin/reseller/delete_records/" + id;
		}
	});
}
</script>

<?php $this->load->view('whmazadmin/include/footer');?>
