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
							<i class="fa fa-globe"></i>
						</div>
						<div>
							<div class="stats-value" id="totalPricing">-</div>
							<div class="stats-label">Total Pricing</div>
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
							<div class="stats-value" id="activePricing">-</div>
							<div class="stats-label">Active</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon info me-3">
							<i class="fa fa-link"></i>
						</div>
						<div>
							<div class="stats-value" id="uniqueExtensions">-</div>
							<div class="stats-label">Extensions</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon warning me-3">
							<i class="fa fa-coins"></i>
						</div>
						<div>
							<div class="stats-value" id="currencies">-</div>
							<div class="stats-label">Currencies</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Domain Pricing Table -->
		<div class="card table-card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<div>
					<h4 class="mb-1"><i class="fa fa-globe me-2"></i>Domain Pricing</h4>
					<nav aria-label="breadcrumb" class="mb-0">
						<ol class="breadcrumb breadcrumb-style1 mb-0" style="background: transparent; padding: 0;">
							<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index" class="text-white-50">Dashboard</a></li>
							<li class="breadcrumb-item active text-white">Domain Pricing</li>
						</ol>
					</nav>
				</div>
				<a href="<?=base_url()?>whmazadmin/domain_pricing/manage" class="btn btn-light btn-sm">
					<i class="fa fa-plus-circle me-1"></i> Add Pricing
				</a>
			</div>
			<div class="card-body">
				<table id="domainPricingDt" class="table table-hover w-100"></table>
			</div>
		</div>

	</div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script');?>

<script>
$(function(){
	'use strict'

	$('#domainPricingDt').DataTable({
		"responsive": true,
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": "<?=base_url()?>whmazadmin/domain_pricing/ssp_list_api/",
			"dataSrc": function(json) {
				$('#totalPricing').text(json.recordsTotal || 0);
				return json.data;
			}
		},
		"order": [[0, 'desc']],
		"language": {
			"processing": '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
			"emptyTable": '<div class="text-center py-4"><i class="fa fa-globe fa-3x text-muted mb-3"></i><p class="text-muted">No domain pricing found</p></div>',
			"zeroRecords": '<div class="text-center py-4"><i class="fa fa-search fa-3x text-muted mb-3"></i><p class="text-muted">No matching pricing found</p></div>'
		},
		"columns": [
			{
				"title": "Extension",
				"data": "extension",
				"width": "12%",
				render: function(data) {
					return '<span class="fw-semibold"><i class="fa fa-globe me-1 text-muted"></i>' + escapeXSS(data) + '</span>';
				}
			},
			{
				"title": "Currency",
				"data": "currency_code",
				"width": "10%",
				"orderable": false,
				"render": function(data, type, row) {
					return '<span class="badge bg-light text-dark">' + escapeXSS(row.currency_symbol) + ' ' + escapeXSS(row.currency_code) + '</span>';
				}
			},
			{
				"title": "Period",
				"data": "reg_period",
				"width": "8%",
				"className": "text-center",
				"render": function(data) {
					return '<span class="badge bg-secondary">' + parseInt(data) + ' yr</span>';
				}
			},
			{
				"title": "Registration",
				"data": "price",
				"width": "12%",
				"className": "text-end",
				"render": function(data) {
					return '<span class="fw-bold text-primary">' + parseFloat(data).toFixed(2) + '</span>';
				}
			},
			{
				"title": "Transfer",
				"data": "transfer",
				"width": "12%",
				"className": "text-end",
				"render": function(data) {
					return '<span class="fw-bold text-info">' + parseFloat(data).toFixed(2) + '</span>';
				}
			},
			{
				"title": "Renewal",
				"data": "renewal",
				"width": "12%",
				"className": "text-end",
				"render": function(data) {
					return '<span class="fw-bold text-warning">' + parseFloat(data).toFixed(2) + '</span>';
				}
			},
			{
				"title": "Status",
				"data": "status",
				"width": "8%",
				"className": "text-center",
				"orderable": false,
				"searchable": false,
				"render": function(data) {
					if (data == 1) {
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
					return '<i class="fa fa-clock me-1 text-muted"></i>' + date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
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
					return '<button type="button" class="btn btn-action btn-manage" onclick="openManage(\'' + idVal + '\')" title="Edit Pricing"><i class="fa fa-cog"></i></button> ' +
						   '<button type="button" class="btn btn-action btn-delete" onclick="deleteRow(\'' + idVal + '\', \'' + escapeXSS(row.extension) + '\')" title="Delete Pricing"><i class="fa fa-trash"></i></button>';
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
	window.location = "<?=base_url()?>whmazadmin/domain_pricing/manage/" + id;
}

function deleteRow(id, extension) {
	Swal.fire({
		title: 'Delete Pricing?',
		html: 'Are you sure you want to delete <strong>' + escapeXSS(extension) + '</strong> pricing?<br><small class="text-muted">This action cannot be undone.</small>',
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
			window.location = "<?=base_url()?>whmazadmin/domain_pricing/delete_records/" + id;
		}
	});
}
</script>

<?php $this->load->view('whmazadmin/include/footer');?>
