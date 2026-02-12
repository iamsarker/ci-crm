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
							<i class="fa fa-receipt"></i>
						</div>
						<div>
							<div class="stats-value" id="totalExpenses">-</div>
							<div class="stats-label">Total Expenses</div>
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
							<div class="stats-value" id="paidExpenses">-</div>
							<div class="stats-label">Paid</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon info me-3">
							<i class="fa fa-dollar-sign"></i>
						</div>
						<div>
							<div class="stats-value" id="totalAmount">-</div>
							<div class="stats-label">Total Amount</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon warning me-3">
							<i class="fa fa-hourglass-half"></i>
						</div>
						<div>
							<div class="stats-value" id="pendingExpenses">-</div>
							<div class="stats-label">Pending</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Expenses Table -->
		<div class="card table-card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<div>
					<h4 class="mb-1"><i class="fa fa-receipt me-2"></i>Expenses</h4>
					<nav aria-label="breadcrumb" class="mb-0">
						<ol class="breadcrumb breadcrumb-style1 mb-0" style="background: transparent; padding: 0;">
							<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index" class="text-white-50">Dashboard</a></li>
							<li class="breadcrumb-item active text-white">Expenses</li>
						</ol>
					</nav>
				</div>
				<a href="<?=base_url()?>whmazadmin/expense/manage" class="btn btn-light btn-sm">
					<i class="fa fa-plus-circle me-1"></i> Add Expense
				</a>
			</div>
			<div class="card-body">
				<table id="expenseListDt" class="table table-hover w-100"></table>
			</div>
		</div>

	</div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script');?>

<script>
$(function(){
	'use strict'

	$('#expenseListDt').DataTable({
		"responsive": true,
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": "<?=base_url()?>whmazadmin/expense/ssp_list_api/",
			"dataSrc": function(json) {
				$('#totalExpenses').text(json.recordsTotal || 0);
				return json.data;
			}
		},
		"order": [[5, 'desc']],
		"language": {
			"processing": '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
			"emptyTable": '<div class="text-center py-4"><i class="fa fa-receipt fa-3x text-muted mb-3"></i><p class="text-muted">No expenses found</p></div>',
			"zeroRecords": '<div class="text-center py-4"><i class="fa fa-search fa-3x text-muted mb-3"></i><p class="text-muted">No matching expenses found</p></div>'
		},
		"columns": [
			{
				"title": "Expense Type",
				"data": "expense_type",
				"width": "18%",
				render: function(data) {
					return '<span class="fw-semibold"><i class="fa fa-tag me-1 text-muted"></i>' + escapeXSS(data) + '</span>';
				}
			},
			{
				"title": "Vendor",
				"data": "vendor_name",
				"width": "15%",
				render: function(data) {
					return '<i class="fa fa-store me-1 text-muted"></i>' + escapeXSS(data);
				}
			},
			{
				"title": "Amount",
				"data": "exp_amount",
				"width": "12%",
				"className": "text-end",
				render: function(data) {
					return '<span class="fw-bold text-danger">' + parseFloat(data || 0).toFixed(2) + '</span>';
				}
			},
			{
				"title": "Paid",
				"data": "paid_amount",
				"width": "12%",
				"className": "text-end",
				render: function(data) {
					var paid = parseFloat(data || 0);
					return paid > 0 ? '<span class="fw-bold text-success">' + paid.toFixed(2) + '</span>' : '<span class="text-muted">0.00</span>';
				}
			},
			{
				"title": "Remarks",
				"data": "remarks",
				"width": "18%",
				render: function(data) {
					return '<small class="text-muted">' + escapeXSS(data || '-') + '</small>';
				}
			},
			{
				"title": "Expense Date",
				"data": "expense_date",
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
				"width": "8%",
				"className": "text-center",
				"orderable": false,
				"searchable": false,
				"render": function(data, type, row) {
					let idVal = safe_encode(data);
					return '<button type="button" class="btn btn-action btn-manage" onclick="openManage(\'' + idVal + '\')" title="Edit Expense"><i class="fa fa-cog"></i></button> ' +
						   '<button type="button" class="btn btn-action btn-delete" onclick="deleteRow(\'' + idVal + '\', \'' + escapeXSS(row['expense_type']) + '\')" title="Delete Expense"><i class="fa fa-trash"></i></button>';
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
	window.location = "<?=base_url()?>whmazadmin/expense/manage/" + id;
}

function deleteRow(id, title) {
	Swal.fire({
		title: 'Delete Expense?',
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
			window.location = "<?=base_url()?>whmazadmin/expense/delete_records/" + id;
		}
	});
}
</script>

<?php $this->load->view('whmazadmin/include/footer');?>
