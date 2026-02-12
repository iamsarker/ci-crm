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
							<i class="fa fa-file-invoice"></i>
						</div>
						<div>
							<div class="stats-value" id="totalInvoices">-</div>
							<div class="stats-label">Total Invoices</div>
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
							<div class="stats-value" id="paidInvoices">-</div>
							<div class="stats-label">Paid Invoices</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon danger me-3" style="background: linear-gradient(135deg, #E53935 0%, #C62828 100%); color: #fff;">
							<i class="fa fa-exclamation-circle"></i>
						</div>
						<div>
							<div class="stats-value" id="dueInvoices">-</div>
							<div class="stats-label">Due Invoices</div>
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
							<div class="stats-value" id="totalAmount">-</div>
							<div class="stats-label">Total Amount</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Invoices Table -->
		<div class="card table-card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<div>
					<h4 class="mb-1"><i class="fa fa-file-invoice me-2"></i>Invoices</h4>
					<nav aria-label="breadcrumb" class="mb-0">
						<ol class="breadcrumb breadcrumb-style1 mb-0" style="background: transparent; padding: 0;">
							<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index" class="text-white-50">Dashboard</a></li>
							<li class="breadcrumb-item active text-white">Invoices</li>
						</ol>
					</nav>
				</div>
				<a href="<?=base_url()?>whmazadmin/invoice/manage" class="btn btn-light btn-sm">
					<i class="fa fa-plus-circle me-1"></i> New Invoice
				</a>
			</div>
			<div class="card-body">
				<table id="invoiceListDt" class="table table-hover w-100"></table>
			</div>
		</div>

	</div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script');?>

<script>
$(function(){
	'use strict'

	$('#invoiceListDt').DataTable({
		"responsive": true,
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": "<?=base_url()?>whmazadmin/invoice/ssp_list_api/",
			"dataSrc": function(json) {
				$('#totalInvoices').text(json.recordsTotal || 0);
				return json.data;
			}
		},
		"order": [[0, 'desc']],
		"language": {
			"processing": '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
			"emptyTable": '<div class="text-center py-4"><i class="fa fa-file-invoice fa-3x text-muted mb-3"></i><p class="text-muted">No invoices found</p></div>',
			"zeroRecords": '<div class="text-center py-4"><i class="fa fa-search fa-3x text-muted mb-3"></i><p class="text-muted">No matching invoices found</p></div>'
		},
		"columns": [
			{
				"title": "Invoice #",
				"data": "invoice_no",
				"width": "10%",
				render: function(data) {
					return '<span class="fw-bold text-primary">' + escapeXSS(data) + '</span>';
				}
			},
			{
				"title": "Order #",
				"data": "order_no",
				"width": "10%",
				render: function(data) {
					return '<span class="text-secondary">' + escapeXSS(data) + '</span>';
				}
			},
			{
				"title": "Company",
				"data": "company_name",
				"width": "15%",
				render: function(data) {
					return '<i class="fa fa-building me-1 text-muted"></i>' + escapeXSS(data);
				}
			},
			{
				"title": "Total",
				"data": "total",
				"width": "8%",
				"className": "text-end",
				render: function(data, type, row) {
					return '<span class="fw-bold">' + parseFloat(data || 0).toFixed(2) + '</span>';
				}
			},
			{
				"title": "Paid",
				"data": "total_paid",
				"width": "8%",
				"className": "text-end",
				render: function(data) {
					var paid = parseFloat(data || 0);
					return paid > 0 ? '<span class="text-success">' + paid.toFixed(2) + '</span>' : '<span class="text-muted">0.00</span>';
				}
			},
			{
				"title": "Balance",
				"data": "balance_due",
				"width": "8%",
				"className": "text-end",
				render: function(data) {
					var balance = parseFloat(data || 0);
					return balance > 0 ? '<span class="text-danger fw-bold">' + balance.toFixed(2) + '</span>' : '<span class="text-success">0.00</span>';
				}
			},
			{
				"title": "Currency",
				"data": "currency_code",
				"width": "6%",
				"className": "text-center",
				render: function(data) {
					return '<span class="badge bg-light text-dark">' + escapeXSS(data) + '</span>';
				}
			},
			{
				"title": "Due Date",
				"data": "due_date",
				"width": "10%",
				render: function(data) {
					if (!data) return '-';
					var date = new Date(data);
					var today = new Date();
					var isOverdue = date < today;
					var icon = isOverdue ? '<i class="fa fa-exclamation-triangle text-danger me-1"></i>' : '<i class="fa fa-calendar me-1 text-muted"></i>';
					return icon + date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
				}
			},
			{ "title": "invoice_uuid", "data": "invoice_uuid", "visible": false },
			{ "title": "company_id", "data": "company_id", "visible": false },
			{
				"title": "Pay Status",
				"data": "pay_status",
				"width": "8%",
				"className": "text-center",
				"orderable": false,
				"searchable": false,
				render: function(data) {
					if (data == 'PAID') {
						return '<span class="badge bg-success"><i class="fa fa-check me-1"></i>Paid</span>';
					} else if (data == 'DUE') {
						return '<span class="badge bg-danger"><i class="fa fa-clock me-1"></i>Due</span>';
					} else {
						return '<span class="badge bg-warning"><i class="fa fa-adjust me-1"></i>Partial</span>';
					}
				}
			},
			{
				"title": "Status",
				"data": "status",
				"width": "7%",
				"className": "text-center",
				"orderable": false,
				"searchable": false,
				render: function(data) {
					return data == 1 ? '<span class="badge bg-primary">Active</span>' : '<span class="badge bg-secondary">Inactive</span>';
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
					var paidButton = '';
					if (row['pay_status'] != "PAID") {
						paidButton = '<button type="button" class="btn btn-action btn-success-custom" onclick="markAsPaid(\''+row['invoice_uuid']+'\')" title="Mark as Paid" style="background: linear-gradient(135deg, #43A047 0%, #388E3C 100%); border: none; color: #fff;"><i class="fa fa-check"></i></button> ';
					}

					return '<button type="button" class="btn btn-action btn-manage" onclick="viewInvoice('+row['company_id']+',\''+row['invoice_uuid']+'\')" title="View Invoice"><i class="fa fa-eye"></i></button> ' +
						   '<button type="button" class="btn btn-action" onclick="downloadInvoice('+row['company_id']+',\''+row['invoice_uuid']+'\')" title="Download PDF" style="background: linear-gradient(135deg, #E53935 0%, #C62828 100%); border: none; color: #fff;"><i class="fa fa-file-pdf"></i></button> ' +
						   paidButton;
				}
			}
		]
	});
});

function viewInvoice(companyId, invoiceUuid) {
	Swal.fire({
		title: 'Loading...',
		text: 'Please wait',
		allowOutsideClick: false,
		allowEscapeKey: false,
		showConfirmButton: false,
		didOpen: () => { Swal.showLoading(); }
	});
	window.location = "<?=base_url()?>whmazadmin/invoice/view_invoice/" + companyId + "/" + invoiceUuid;
}

function downloadInvoice(companyId, invoiceUuid) {
	window.location = "<?=base_url()?>whmazadmin/invoice/download_invoice/" + companyId + "/" + invoiceUuid;
}

function markAsPaid(invoiceUuid) {
	Swal.fire({
		title: 'Mark as Paid?',
		text: 'Are you sure you want to mark this invoice as paid?',
		icon: 'question',
		showCancelButton: true,
		confirmButtonColor: '#28a745',
		cancelButtonColor: '#6c757d',
		confirmButtonText: '<i class="fa fa-check me-1"></i> Yes, Mark as Paid',
		cancelButtonText: 'Cancel',
		reverseButtons: true
	}).then((result) => {
		if (result.isConfirmed) {
			Swal.fire({
				title: 'Processing...',
				text: 'Please wait',
				allowOutsideClick: false,
				allowEscapeKey: false,
				showConfirmButton: false,
				didOpen: () => { Swal.showLoading(); }
			});

			$.ajax({
				url: "<?=base_url()?>whmazadmin/invoice/mark_as_paid",
				type: "POST",
				contentType: "application/json",
				data: JSON.stringify({ invoice_uuid: invoiceUuid }),
				dataType: "json",
				success: function(response) {
					Swal.close();
					if (response.success) {
						toastSuccess(response.message);
						setTimeout(function() { location.reload(); }, 1000);
					} else {
						toastError(response.message);
					}
				},
				error: function() {
					Swal.close();
					toastError('An error occurred while updating the invoice status');
				}
			});
		}
	});
}
</script>

<?php $this->load->view('whmazadmin/include/footer');?>
