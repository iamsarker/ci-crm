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
							<i class="fa fa-ticket-alt"></i>
						</div>
						<div>
							<div class="stats-value" id="totalTickets">-</div>
							<div class="stats-label">Total Tickets</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon success me-3">
							<i class="fa fa-envelope-open"></i>
						</div>
						<div>
							<div class="stats-value" id="openTickets">-</div>
							<div class="stats-label">Open Tickets</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon warning me-3">
							<i class="fa fa-reply"></i>
						</div>
						<div>
							<div class="stats-value" id="awaitingReply">-</div>
							<div class="stats-label">Awaiting Reply</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon info me-3">
							<i class="fa fa-check-double"></i>
						</div>
						<div>
							<div class="stats-value" id="closedTickets">-</div>
							<div class="stats-label">Closed Tickets</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Tickets Table -->
		<div class="card table-card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<div>
					<h4 class="mb-1"><i class="fa fa-ticket-alt me-2"></i>Support Tickets</h4>
					<nav aria-label="breadcrumb" class="mb-0">
						<ol class="breadcrumb breadcrumb-style1 mb-0" style="background: transparent; padding: 0;">
							<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index" class="text-white-50">Dashboard</a></li>
							<li class="breadcrumb-item active text-white">Tickets</li>
						</ol>
					</nav>
				</div>
			</div>
			<div class="card-body">
				<table id="ticketListDt" class="table table-hover w-100"></table>
			</div>
		</div>

	</div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script');?>

<script>
$(function(){
	'use strict'

	$('#ticketListDt').DataTable({
		"responsive": true,
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": "<?=base_url()?>whmazadmin/ticket/ssp_list_api",
			"dataSrc": function(json) {
				$('#totalTickets').text(json.recordsTotal || 0);
				return json.data;
			}
		},
		"order": [[0, 'desc']],
		"language": {
			"processing": '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
			"emptyTable": '<div class="text-center py-4"><i class="fa fa-ticket-alt fa-3x text-muted mb-3"></i><p class="text-muted">No tickets found</p></div>',
			"zeroRecords": '<div class="text-center py-4"><i class="fa fa-search fa-3x text-muted mb-3"></i><p class="text-muted">No matching tickets found</p></div>'
		},
		"columns": [
			{
				"title": "Ticket #",
				"data": "id",
				"width": "8%",
				render: function(data) {
					return '<span class="fw-bold text-primary">#' + data + '</span>';
				}
			},
			{
				"title": "Subject",
				"data": "title",
				"width": "25%",
				render: function(data) {
					return '<span class="fw-semibold">' + escapeXSS(data) + '</span>';
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
				"title": "Department",
				"data": "dept_name",
				"width": "12%",
				render: function(data) {
					return '<i class="fa fa-folder me-1 text-muted"></i>' + escapeXSS(data);
				}
			},
			{
				"title": "User",
				"data": "user_name",
				"width": "12%",
				render: function(data) {
					return '<i class="fa fa-user me-1 text-muted"></i>' + escapeXSS(data);
				}
			},
			{
				"title": "Priority",
				"data": "priority",
				"width": "8%",
				"className": "text-center",
				render: function(data) {
					switch(parseInt(data)) {
						case 1: return '<span class="badge bg-secondary">Low</span>';
						case 2: return '<span class="badge bg-info">Medium</span>';
						case 3: return '<span class="badge bg-warning text-dark">High</span>';
						case 4: return '<span class="badge bg-danger">Critical</span>';
						default: return '<span class="badge bg-light text-dark">-</span>';
					}
				}
			},
			{
				"title": "Status",
				"data": "flag",
				"width": "10%",
				"className": "text-center",
				render: function(data) {
					switch(parseInt(data)) {
						case 1: return '<span class="badge bg-success"><i class="fa fa-envelope-open me-1"></i>Open</span>';
						case 2: return '<span class="badge bg-info"><i class="fa fa-reply me-1"></i>Answered</span>';
						case 3: return '<span class="badge bg-warning text-dark"><i class="fa fa-comment me-1"></i>Customer Reply</span>';
						case 4: return '<span class="badge bg-dark"><i class="fa fa-check me-1"></i>Closed</span>';
						default: return '<span class="badge bg-secondary">-</span>';
					}
				}
			},
			{
				"title": "Date",
				"data": "inserted_on",
				"width": "10%",
				render: function(data) {
					if (!data) return '-';
					var date = new Date(data);
					return '<i class="fa fa-clock me-1 text-muted"></i>' + date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
				}
			},
			{
				"title": "Actions",
				"data": "id",
				"width": "8%",
				"className": "text-center",
				"orderable": false,
				"searchable": false,
				"render": function(data) {
					return '<button type="button" class="btn btn-action btn-manage" onclick="viewTicket('+data+')" title="View Ticket"><i class="fa fa-eye"></i></button>';
				}
			}
		]
	});
});

function viewTicket(tid) {
	Swal.fire({
		title: 'Loading...',
		text: 'Please wait',
		allowOutsideClick: false,
		allowEscapeKey: false,
		showConfirmButton: false,
		didOpen: () => { Swal.showLoading(); }
	});
	window.location = "<?=base_url()?>whmazadmin/ticket/viewticket/" + tid;
}
</script>

<?php $this->load->view('whmazadmin/include/footer');?>
