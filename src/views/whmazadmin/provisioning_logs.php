<?php $this->load->view('whmazadmin/include/header'); ?>
<link rel="stylesheet" href="<?=base_url()?>resources/assets/css/admin.list_page.css">

<div class="content content-fluid content-wrapper">
	<div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">
		<p>&nbsp;</p>
		<!-- Page Header -->
		<div class="order-page-header mt-4">
			<div class="d-flex justify-content-between align-items-center">
				<div>
					<h3><i class="fas fa-cogs me-2"></i> Provisioning Logs</h3>
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item"><a href="<?php echo base_url(); ?>whmazadmin">Dashboard</a></li>
							<li class="breadcrumb-item"><a href="<?php echo base_url(); ?>whmazadmin/order">Orders</a></li>
							<li class="breadcrumb-item active"><a href="#">Provisioning Logs</a></li>
						</ol>
					</nav>
				</div>
				<a href="<?php echo base_url(); ?>whmazadmin/order" class="btn btn-back">
					<i class="fas fa-arrow-left me-1"></i> Back to Orders
				</a>
			</div>
		</div>

		<!-- Stats Cards -->
		<div class="row mt-4 mb-4">
			<div class="col-md-3">
				<div class="stats-card stats-card-primary">
					<div class="stats-icon">
						<i class="fas fa-list"></i>
					</div>
					<div class="stats-info">
						<h3><?php echo number_format($stats['total']); ?></h3>
						<p>Total Logs</p>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="stats-card stats-card-success">
					<div class="stats-icon">
						<i class="fas fa-check-circle"></i>
					</div>
					<div class="stats-info">
						<h3><?php echo number_format($stats['success']); ?></h3>
						<p>Successful</p>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="stats-card stats-card-danger">
					<div class="stats-icon">
						<i class="fas fa-times-circle"></i>
					</div>
					<div class="stats-info">
						<h3><?php echo number_format($stats['failed']); ?></h3>
						<p>Failed</p>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="stats-card stats-card-info">
					<div class="stats-icon">
						<i class="fas fa-calendar-day"></i>
					</div>
					<div class="stats-info">
						<h3><?php echo number_format($stats['today']); ?></h3>
						<p>Today</p>
					</div>
				</div>
			</div>
		</div>

		<!-- Filters -->
		<div class="table-card mb-4">
			<div class="card-header">
				<h5 class="mb-0"><i class="fas fa-filter me-2"></i> Filters</h5>
			</div>
			<div class="card-body">
				<div class="row">
					<div class="col-md-3">
						<label class="form-label">Status</label>
						<select class="form-select" id="filterSuccess">
							<option value="">All</option>
							<option value="1">Success</option>
							<option value="0">Failed</option>
						</select>
					</div>
					<div class="col-md-3">
						<label class="form-label">Item Type</label>
						<select class="form-select" id="filterItemType">
							<option value="">All</option>
							<option value="1">Domain</option>
							<option value="2">Service</option>
						</select>
					</div>
					<div class="col-md-3">
						<label class="form-label">Action</label>
						<select class="form-select" id="filterAction">
							<option value="">All Actions</option>
							<option value="domain_register">Domain Register</option>
							<option value="domain_transfer">Domain Transfer</option>
							<option value="domain_renew">Domain Renew</option>
							<option value="service_create">Service Create</option>
							<option value="service_renew">Service Renew</option>
							<option value="service_unsuspend">Service Unsuspend</option>
						</select>
					</div>
					<div class="col-md-3 d-flex align-items-end">
						<button type="button" class="btn btn-primary" id="btnApplyFilter">
							<i class="fas fa-search me-1"></i> Apply
						</button>
						<button type="button" class="btn btn-outline-secondary ms-2" id="btnResetFilter">
							<i class="fas fa-redo me-1"></i> Reset
						</button>
					</div>
				</div>
			</div>
		</div>

		<!-- Provisioning Logs Table -->
		<div class="table-card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h5 class="mb-0"><i class="fas fa-list me-2"></i> Provisioning History</h5>
				<button type="button" class="btn btn-sm btn-outline-light" id="btnRefresh">
					<i class="fas fa-sync-alt"></i> Refresh
				</button>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-hover" id="provisioningTable">
						<thead>
							<tr>
								<th>ID</th>
								<th>Invoice</th>
								<th>Item</th>
								<th>Action</th>
								<th>Status</th>
								<th>Date</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailModal" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h5 class="modal-title"><i class="fas fa-info-circle me-2"></i> Provisioning Log Details</h5>
				<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body" id="logDetailContent">
				<div class="text-center py-4">
					<i class="fas fa-spinner fa-spin fa-2x"></i>
				</div>
			</div>
			<div class="modal-footer" id="logDetailFooter" style="display: none;">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				<button type="button" class="btn btn-warning" id="btnRetryItem">
					<i class="fas fa-redo me-1"></i> Retry This Item
				</button>
			</div>
		</div>
	</div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script');?>

<script>
$(document).ready(function() {
	var currentLogId = null;

	var table = $('#provisioningTable').DataTable({
		processing: true,
		serverSide: true,
		ajax: {
			url: '<?php echo base_url(); ?>whmazadmin/provisioning/logs_list_api',
			type: 'GET',
			data: function(d) {
				d.success = $('#filterSuccess').val();
				d.item_type = $('#filterItemType').val();
				d.action = $('#filterAction').val();
			}
		},
		columns: [
			{
				data: 'id',
				render: function(data) {
					return '<small class="text-muted">#' + data + '</small>';
				}
			},
			{
				data: 'invoice_no',
				render: function(data, type, row) {
					if (data) {
						return '<a href="<?php echo base_url(); ?>whmazadmin/invoice/manage/' + row.invoice_id + '" class="text-primary">' + data + '</a>';
					}
					return '<span class="text-muted">-</span>';
				}
			},
			{
				data: 'item_name',
				render: function(data, type, row) {
					var icon = row.item_type == 1 ? 'fa-globe' : 'fa-server';
					var label = row.item_type == 1 ? 'Domain' : 'Service';
					var name = data || '-';
					return '<i class="fas ' + icon + ' me-1 text-muted"></i> <span title="' + label + '">' + name + '</span>';
				}
			},
			{
				data: 'action',
				render: function(data) {
					var badges = {
						'domain_register': 'bg-info',
						'domain_transfer': 'bg-warning',
						'domain_renew': 'bg-secondary',
						'service_create': 'bg-primary',
						'service_renew': 'bg-secondary',
						'service_unsuspend': 'bg-success'
					};
					var badgeClass = badges[data] || 'bg-dark';
					var displayText = data ? data.replace('_', ' ').replace(/\b\w/g, function(l){ return l.toUpperCase(); }) : '-';
					return '<span class="badge ' + badgeClass + '">' + displayText + '</span>';
				}
			},
			{
				data: 'success',
				render: function(data, type, row) {
					if (data == 1) {
						return '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Success</span>';
					}
					var retryText = row.retry_count > 0 ? ' (Retry: ' + row.retry_count + ')' : '';
					return '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Failed' + retryText + '</span>';
				}
			},
			{
				data: 'inserted_on',
				render: function(data) {
					if (data) {
						var date = new Date(data);
						return '<small>' + date.toLocaleDateString() + '<br>' + date.toLocaleTimeString() + '</small>';
					}
					return '-';
				}
			},
			{
				data: 'id',
				orderable: false,
				searchable: false,
				render: function(data, type, row) {
					var html = '<button type="button" class="btn btn-sm btn-outline-primary btn-view-log me-1" data-id="' + data + '" title="View Details"><i class="fas fa-eye"></i></button>';
					if (row.success == 0) {
						html += '<button type="button" class="btn btn-sm btn-outline-warning btn-retry-item" data-id="' + data + '" title="Retry"><i class="fas fa-redo"></i></button>';
					}
					return html;
				}
			}
		],
		order: [[0, 'desc']],
		pageLength: 25,
		language: {
			processing: '<i class="fas fa-spinner fa-spin fa-2x"></i>',
			emptyTable: 'No provisioning logs found'
		}
	});

	// Apply filters
	$('#btnApplyFilter').on('click', function() {
		table.ajax.reload();
	});

	// Reset filters
	$('#btnResetFilter').on('click', function() {
		$('#filterSuccess').val('');
		$('#filterItemType').val('');
		$('#filterAction').val('');
		table.ajax.reload();
	});

	// Refresh table
	$('#btnRefresh').on('click', function() {
		table.ajax.reload();
	});

	// View log details
	$(document).on('click', '.btn-view-log', function() {
		var id = $(this).data('id');
		currentLogId = id;
		$('#logDetailContent').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
		$('#logDetailFooter').hide();
		$('#logDetailModal').modal('show');

		$.ajax({
			url: '<?php echo base_url(); ?>whmazadmin/provisioning/log_detail/' + id,
			type: 'GET',
			dataType: 'json',
			success: function(response) {
				if (response.success) {
					var log = response.data;
					var html = '<div class="row mb-3">';

					// Left column
					html += '<div class="col-md-6">';
					html += '<p><strong>Invoice:</strong> ' + (log.invoice_no ? '<a href="<?php echo base_url(); ?>whmazadmin/invoice/manage/' + log.invoice_id + '">' + log.invoice_no + '</a>' : '-') + '</p>';
					html += '<p><strong>Customer:</strong> ' + (log.customer_name || '-') + '</p>';
					html += '<p><strong>Company:</strong> ' + (log.company_name || '-') + '</p>';
					html += '</div>';

					// Right column
					html += '<div class="col-md-6">';
					html += '<p><strong>Item Type:</strong> ' + (log.item_type == 1 ? '<span class="badge bg-info">Domain</span>' : '<span class="badge bg-primary">Service</span>') + '</p>';
					html += '<p><strong>Item:</strong> ' + (log.item_name || '-') + '</p>';
					html += '<p><strong>Date:</strong> ' + log.inserted_on + '</p>';
					html += '</div></div>';

					// Action and status
					html += '<div class="row mb-3">';
					html += '<div class="col-md-6">';
					html += '<p><strong>Action:</strong> <code>' + log.action + '</code></p>';
					html += '</div>';
					html += '<div class="col-md-6">';
					if (log.success == 1) {
						html += '<p><strong>Status:</strong> <span class="badge bg-success">Success</span></p>';
					} else {
						html += '<p><strong>Status:</strong> <span class="badge bg-danger">Failed</span> (Retries: ' + log.retry_count + ')</p>';
					}
					html += '</div></div>';

					// Error message
					if (log.error_message) {
						html += '<div class="alert alert-danger mb-3">';
						html += '<strong><i class="fas fa-exclamation-triangle me-1"></i> Error:</strong><br>';
						html += '<pre class="mb-0 mt-2" style="white-space: pre-wrap;">' + log.error_message + '</pre>';
						html += '</div>';
					}

					// Response data
					if (log.response_data) {
						html += '<div class="mb-3"><strong>Response Data:</strong></div>';
						html += '<pre class="bg-dark text-light p-3 rounded" style="max-height: 300px; overflow: auto;">';
						try {
							var responseData = JSON.parse(log.response_data);
							html += JSON.stringify(responseData, null, 2);
						} catch (e) {
							html += log.response_data;
						}
						html += '</pre>';
					}

					$('#logDetailContent').html(html);

					// Show retry button only for failed items
					if (log.success == 0) {
						$('#logDetailFooter').show();
					} else {
						$('#logDetailFooter').hide();
					}
				} else {
					$('#logDetailContent').html('<div class="alert alert-danger">Failed to load log details: ' + response.message + '</div>');
				}
			},
			error: function() {
				$('#logDetailContent').html('<div class="alert alert-danger">Error loading log details</div>');
			}
		});
	});

	// Retry single item from table
	$(document).on('click', '.btn-retry-item', function() {
		var id = $(this).data('id');
		retryItem(id);
	});

	// Retry single item from modal
	$('#btnRetryItem').on('click', function() {
		if (currentLogId) {
			retryItem(currentLogId);
		}
	});

	function retryItem(logId) {
		Swal.fire({
			title: 'Retry Provisioning?',
			text: 'This will attempt to provision this item again.',
			icon: 'question',
			showCancelButton: true,
			confirmButtonColor: '#f0ad4e',
			cancelButtonColor: '#6c757d',
			confirmButtonText: 'Yes, Retry'
		}).then((result) => {
			if (result.isConfirmed) {
				$.ajax({
					url: '<?php echo base_url(); ?>whmazadmin/provisioning/retry_item/' + logId,
					type: 'POST',
					dataType: 'json',
					success: function(response) {
						if (response.success) {
							Swal.fire({
								icon: 'success',
								title: 'Success',
								text: response.message
							});
							$('#logDetailModal').modal('hide');
							table.ajax.reload();
						} else {
							Swal.fire({
								icon: 'error',
								title: 'Failed',
								text: response.message
							});
						}
					},
					error: function() {
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'An error occurred while retrying provisioning'
						});
					}
				});
			}
		});
	}
});
</script>

<style>
.order-page-header {
	background: linear-gradient(135deg, #5E35B1 0%, #4527A0 100%);
	border-radius: 10px;
	padding: 25px 30px;
	box-shadow: 0 4px 15px rgba(94, 53, 177, 0.3);
}
.order-page-header h3 {
	color: #fff;
	margin: 0;
	font-weight: 600;
}
.order-page-header .breadcrumb {
	background: transparent;
	margin: 8px 0 0 0;
	padding: 0;
}
.order-page-header .breadcrumb-item a {
	color: rgba(255,255,255,0.8);
}
.order-page-header .breadcrumb-item.active a {
	color: #fff;
}
.order-page-header .breadcrumb-item + .breadcrumb-item::before {
	color: rgba(255,255,255,0.6);
}
.order-page-header .btn-back {
	background: rgba(255,255,255,0.15);
	border: 1px solid rgba(255,255,255,0.3);
	color: #fff;
	padding: 8px 18px;
	border-radius: 8px;
	font-weight: 600;
	text-decoration: none;
	transition: all 0.3s ease;
}
.order-page-header .btn-back:hover {
	background: rgba(255,255,255,0.25);
	color: #fff;
}

/* Stats Cards */
.stats-card {
	background: #fff;
	border-radius: 10px;
	padding: 20px;
	display: flex;
	align-items: center;
	box-shadow: 0 2px 10px rgba(0,0,0,0.08);
	border-left: 4px solid;
}
.stats-card-primary {
	border-left-color: #1976D2;
}
.stats-card-success {
	border-left-color: #43A047;
}
.stats-card-danger {
	border-left-color: #E53935;
}
.stats-card-info {
	border-left-color: #00ACC1;
}
.stats-card .stats-icon {
	width: 50px;
	height: 50px;
	border-radius: 10px;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 1.5rem;
	margin-right: 15px;
}
.stats-card-primary .stats-icon {
	background: rgba(25, 118, 210, 0.1);
	color: #1976D2;
}
.stats-card-success .stats-icon {
	background: rgba(67, 160, 71, 0.1);
	color: #43A047;
}
.stats-card-danger .stats-icon {
	background: rgba(229, 57, 53, 0.1);
	color: #E53935;
}
.stats-card-info .stats-icon {
	background: rgba(0, 172, 193, 0.1);
	color: #00ACC1;
}
.stats-card .stats-info h3 {
	margin: 0;
	font-size: 1.75rem;
	font-weight: 700;
	color: #333;
}
.stats-card .stats-info p {
	margin: 0;
	color: #666;
	font-size: 0.9rem;
}
</style>

<?php $this->load->view('whmazadmin/include/footer'); ?>
