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
							<i class="fa fa-cube"></i>
						</div>
						<div>
							<div class="stats-value" id="totalProducts">-</div>
							<div class="stats-label">Total Products</div>
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
							<div class="stats-value" id="activeProducts">-</div>
							<div class="stats-label">Active Products</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon info me-3">
							<i class="fa fa-layer-group"></i>
						</div>
						<div>
							<div class="stats-value" id="serviceGroups">-</div>
							<div class="stats-label">Service Groups</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon warning me-3">
							<i class="fa fa-eye-slash"></i>
						</div>
						<div>
							<div class="stats-value" id="hiddenProducts">-</div>
							<div class="stats-label">Hidden Products</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Products Table -->
		<div class="card table-card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<div>
					<h4 class="mb-1"><i class="fa fa-cube me-2"></i>Service Products</h4>
					<nav aria-label="breadcrumb" class="mb-0">
						<ol class="breadcrumb breadcrumb-style1 mb-0" style="background: transparent; padding: 0;">
							<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index" class="text-white-50">Dashboard</a></li>
							<li class="breadcrumb-item active text-white">Service Products</li>
						</ol>
					</nav>
				</div>
				<a href="<?=base_url()?>whmazadmin/service_product/manage" class="btn btn-light btn-sm">
					<i class="fa fa-plus-circle me-1"></i> Add Product
				</a>
			</div>
			<div class="card-body">
				<table id="productListDt" class="table table-hover w-100"></table>
			</div>
		</div>

	</div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script');?>

<script>
$(function(){
	'use strict'

	$('#productListDt').DataTable({
		"responsive": true,
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": "<?=base_url()?>whmazadmin/service_product/ssp_list_api/",
			"dataSrc": function(json) {
				$('#totalProducts').text(json.recordsTotal || 0);
				$('#activeProducts').text(json.recordsTotal || 0);
				return json.data;
			}
		},
		"order": [[0, 'desc']],
		"language": {
			"processing": '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
			"emptyTable": '<div class="text-center py-4"><i class="fa fa-cube fa-3x text-muted mb-3"></i><p class="text-muted">No products found</p></div>',
			"zeroRecords": '<div class="text-center py-4"><i class="fa fa-search fa-3x text-muted mb-3"></i><p class="text-muted">No matching products found</p></div>'
		},
		"columns": [
			{ "title": "ID", "data": "id", "width": "5%", "visible": false },
			{
				"title": "Product Name",
				"data": "product_name",
				"width": "20%",
				render: function(data) {
					return '<span class="fw-semibold"><i class="fa fa-cube me-1 text-muted"></i>' + escapeXSS(data) + '</span>';
				}
			},
			{
				"title": "Service Group",
				"data": "group_name",
				"width": "15%",
				render: function(data) {
					return '<i class="fa fa-layer-group me-1 text-muted"></i>' + escapeXSS(data);
				}
			},
			{
				"title": "Service Type",
				"data": "servce_type_name",
				"width": "12%",
				render: function(data) {
					return '<i class="fa fa-tags me-1 text-muted"></i>' + escapeXSS(data);
				}
			},
			{
				"title": "Module",
				"data": "module_name",
				"width": "10%",
				render: function(data) {
					return '<span class="badge bg-info">' + escapeXSS(data) + '</span>';
				}
			},
			{
				"title": "cPanel Package",
				"data": "cp_package",
				"width": "10%",
				render: function(data) {
					return data ? '<span class="badge bg-light text-dark"><i class="fa fa-server me-1"></i>' + escapeXSS(data) + '</span>' : '<span class="text-muted">-</span>';
				}
			},
			{
				"title": "Hidden",
				"data": "is_hidden",
				"width": "6%",
				"className": "text-center",
				"orderable": false,
				"searchable": false,
				render: function(data) {
					if (parseInt(data) === 1) {
						return '<span class="badge bg-warning text-dark"><i class="fa fa-eye-slash me-1"></i>Yes</span>';
					} else {
						return '<span class="badge bg-success"><i class="fa fa-eye me-1"></i>No</span>';
					}
				}
			},
			{
				"title": "Status",
				"data": "status",
				"width": "6%",
				"className": "text-center",
				"orderable": false,
				"searchable": false,
				render: function(data) {
					if (data == 1) {
						return '<span class="badge bg-success"><i class="fa fa-check me-1"></i>Active</span>';
					} else {
						return '<span class="badge bg-danger"><i class="fa fa-times me-1"></i>Inactive</span>';
					}
				}
			},
			{
				"title": "Last Updated",
				"data": "updated_on",
				"width": "10%",
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
				"width": "8%",
				"className": "text-center",
				"orderable": false,
				"searchable": false,
				"render": function(data, type, row) {
					let idVal = safe_encode(data);
					return '<button type="button" class="btn btn-action btn-manage" onclick="openManage(\'' + idVal + '\')" title="Manage Product"><i class="fa fa-cog"></i></button> ' +
						   '<button type="button" class="btn btn-action btn-delete" onclick="deleteRow(\'' + idVal + '\', \'' + escapeXSS(row['product_name']) + '\')" title="Delete Product"><i class="fa fa-trash"></i></button>';
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
	window.location = "<?=base_url()?>whmazadmin/service_product/manage/" + id;
}

function deleteRow(id, title) {
	Swal.fire({
		title: 'Delete Product?',
		html: 'Are you sure you want to delete <strong>' + title + '</strong>?<br><small class="text-muted">This action cannot be undone.</small>',
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
			window.location = "<?=base_url()?>whmazadmin/service_product/delete_records/" + id;
		}
	});
}
</script>

<?php $this->load->view('whmazadmin/include/footer');?>
