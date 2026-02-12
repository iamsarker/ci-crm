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
							<i class="fa fa-shopping-cart"></i>
						</div>
						<div>
							<div class="stats-value" id="totalOrders">-</div>
							<div class="stats-label">Total Orders</div>
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
							<div class="stats-value" id="activeOrders">-</div>
							<div class="stats-label">Active Orders</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon info me-3">
							<i class="fa fa-calendar-plus"></i>
						</div>
						<div>
							<div class="stats-value" id="thisMonthOrders">-</div>
							<div class="stats-label">This Month</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon warning me-3">
							<i class="fa fa-dollar-sign"></i>
						</div>
						<div>
							<div class="stats-value" id="totalRevenue">-</div>
							<div class="stats-label">Total Revenue</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Orders Table -->
		<div class="card table-card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<div>
					<h4 class="mb-1"><i class="fa fa-shopping-cart me-2"></i>Orders</h4>
					<nav aria-label="breadcrumb" class="mb-0">
						<ol class="breadcrumb breadcrumb-style1 mb-0" style="background: transparent; padding: 0;">
							<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index" class="text-white-50">Dashboard</a></li>
							<li class="breadcrumb-item active text-white">Orders</li>
						</ol>
					</nav>
				</div>
				<a href="<?=base_url()?>whmazadmin/order/manage" class="btn btn-light btn-sm">
					<i class="fa fa-plus-circle me-1"></i> New Order
				</a>
			</div>
			<div class="card-body">
				<table id="orderListDt" class="table table-hover w-100"></table>
			</div>
		</div>

	</div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script');?>

<script>
$(function(){
	'use strict'

	$('#orderListDt').DataTable({
		"responsive": true,
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": "<?=base_url()?>whmazadmin/order/ssp_list_api/",
			"dataSrc": function(json) {
				$('#totalOrders').text(json.recordsTotal || 0);
				$('#activeOrders').text(json.recordsTotal || 0);
				return json.data;
			}
		},
		"order": [[0, 'desc']],
		"language": {
			"processing": '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
			"emptyTable": '<div class="text-center py-4"><i class="fa fa-shopping-cart fa-3x text-muted mb-3"></i><p class="text-muted">No orders found</p></div>',
			"zeroRecords": '<div class="text-center py-4"><i class="fa fa-search fa-3x text-muted mb-3"></i><p class="text-muted">No matching orders found</p></div>'
		},
		"columns": [
			{
				"title": "Order #",
				"data": "order_no",
				"width": "10%",
				render: function(data) {
					return '<span class="fw-bold text-primary">' + escapeXSS(data) + '</span>';
				}
			},
			{
				"title": "Company",
				"data": "company_name",
				"width": "18%",
				render: function(data) {
					return '<i class="fa fa-building me-1 text-muted"></i>' + escapeXSS(data);
				}
			},
			{
				"title": "Items",
				"data": "service_count",
				"width": "15%",
				"orderable": false,
				"searchable": false,
				render: function(data, type, row) {
					var parts = [];
					if (parseInt(row.service_count) > 0) parts.push('<span class="badge bg-info me-1">' + row.service_count + ' service(s)</span>');
					if (parseInt(row.domain_count) > 0) parts.push('<span class="badge bg-secondary">' + row.domain_count + ' domain(s)</span>');
					return parts.length > 0 ? parts.join(' ') : '<span class="text-muted">-</span>';
				}
			},
			{
				"title": "Discount",
				"data": "discount_amount",
				"width": "8%",
				"className": "text-end",
				render: function(data) {
					var val = parseFloat(data || 0);
					return val > 0 ? '<span class="text-danger">-' + val.toFixed(2) + '</span>' : '<span class="text-muted">0.00</span>';
				}
			},
			{
				"title": "Total",
				"data": "total_amount",
				"width": "10%",
				"className": "text-end",
				render: function(data, type, row) {
					return '<span class="fw-bold">' + parseFloat(data || 0).toFixed(2) + ' ' + escapeXSS(row.currency_code || '') + '</span>';
				}
			},
			{
				"title": "Recurring",
				"data": "total_amount",
				"width": "10%",
				"className": "text-end",
				"orderable": false,
				"searchable": false,
				render: function(data, type, row) {
					return '<span class="fw-bold">' + parseFloat(data || 0).toFixed(2) + ' ' + escapeXSS(row.currency_code || '') + '</span>';
				}
			},
			{
				"title": "Order Date",
				"data": "order_date",
				"width": "12%",
				render: function(data) {
					if (!data) return '-';
					var date = new Date(data);
					return '<i class="fa fa-calendar me-1 text-muted"></i>' + date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
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
					if (data == 1) {
						return '<span class="badge bg-success"><i class="fa fa-check me-1"></i>Active</span>';
					} else {
						return '<span class="badge bg-danger"><i class="fa fa-times me-1"></i>Inactive</span>';
					}
				}
			},
			{
				"title": "Actions",
				"data": "id",
				"width": "9%",
				"className": "text-center",
				"orderable": false,
				"searchable": false,
				"render": function(data, type, row) {
					let idVal = safe_encode(data);
					return '<button type="button" class="btn btn-action btn-manage" onclick="openManage(\''+idVal+'\')" title="Manage Order">' +
							'<i class="fa fa-cog"></i></button> ' +
						   '<button type="button" class="btn btn-action btn-delete" onclick="deleteRow(\''+idVal+'\', \''+escapeXSS(row['order_no'])+'\')" title="Delete Order">' +
							'<i class="fa fa-trash"></i></button>';
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
	window.location = "<?=base_url()?>whmazadmin/order/manage/" + id;
}

function deleteRow(id, title) {
	Swal.fire({
		title: 'Delete Order?',
		html: 'Are you sure you want to delete order <strong>' + title + '</strong>?<br><small class="text-muted">This action cannot be undone.</small>',
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
			window.location = "<?=base_url()?>whmazadmin/order/delete_records/" + id;
		}
	});
}
</script>

<?php $this->load->view('whmazadmin/include/footer');?>
