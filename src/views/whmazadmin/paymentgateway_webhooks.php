<?php $this->load->view('whmazadmin/include/header'); ?>
<link rel="stylesheet" href="<?=base_url()?>resources/assets/css/admin.list_page.css">

<div class="content content-fluid content-wrapper">
	<div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">
		<p>&nbsp;</p>
		<!-- Page Header -->
		<div class="order-page-header mt-4">
			<div class="d-flex justify-content-between align-items-center">
				<div>
					<h3><i class="fas fa-satellite-dish me-2"></i> Webhook Logs</h3>
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item"><a href="<?php echo base_url(); ?>whmazadmin">Dashboard</a></li>
							<li class="breadcrumb-item"><a href="<?php echo base_url(); ?>whmazadmin/paymentgateway">Payment Gateways</a></li>
							<li class="breadcrumb-item active"><a href="#">Webhook Logs</a></li>
						</ol>
					</nav>
				</div>
				<a href="<?php echo base_url(); ?>whmazadmin/paymentgateway" class="btn btn-back">
					<i class="fas fa-arrow-left me-1"></i> Back
				</a>
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
						<label class="form-label">Gateway</label>
						<select class="form-select" id="filterGateway">
							<option value="">All Gateways</option>
							<?php foreach ($gateways as $gateway): ?>
							<option value="<?php echo $gateway['gateway_code']; ?>"><?php echo htmlspecialchars($gateway['name']); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-3">
						<label class="form-label">Status</label>
						<select class="form-select" id="filterProcessed">
							<option value="">All</option>
							<option value="1">Processed</option>
							<option value="0">Pending</option>
						</select>
					</div>
					<div class="col-md-3">
						<label class="form-label">Signature</label>
						<select class="form-select" id="filterSignature">
							<option value="">All</option>
							<option value="1">Valid</option>
							<option value="0">Invalid</option>
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

		<!-- Webhook Logs Table -->
		<div class="table-card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h5 class="mb-0"><i class="fas fa-list me-2"></i> Webhook History</h5>
				<button type="button" class="btn btn-sm btn-outline-light" id="btnRefresh">
					<i class="fas fa-sync-alt"></i> Refresh
				</button>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-hover" id="webhooksTable">
						<thead>
							<tr>
								<th>ID</th>
								<th>Gateway</th>
								<th>Event Type</th>
								<th>Event ID</th>
								<th>Signature</th>
								<th>Processed</th>
								<th>Result</th>
								<th>Received</th>
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

<!-- Webhook Details Modal -->
<div class="modal fade" id="webhookModal" tabindex="-1">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h5 class="modal-title"><i class="fas fa-code me-2"></i> Webhook Payload</h5>
				<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body" id="webhookDetails">
				<div class="text-center py-4">
					<i class="fas fa-spinner fa-spin fa-2x"></i>
				</div>
			</div>
		</div>
	</div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script');?>

<script>
$(document).ready(function() {
	var table = $('#webhooksTable').DataTable({
		processing: true,
		serverSide: true,
		ajax: {
			url: '<?php echo base_url(); ?>whmazadmin/paymentgateway/webhooks_list_api',
			type: 'GET',
			data: function(d) {
				d.gateway_code = $('#filterGateway').val();
				d.processed = $('#filterProcessed').val();
				d.signature_valid = $('#filterSignature').val();
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
				data: 'gateway_code',
				render: function(data) {
					var icons = {
						'stripe': 'fa-credit-card',
						'paypal': 'fa-paypal',
						'razorpay': 'fa-rupee-sign',
						'sslcommerz': 'fa-mobile-alt'
					};
					var icon = icons[data] || 'fa-globe';
					return '<i class="fas ' + icon + ' me-1"></i> ' + (data ? data.charAt(0).toUpperCase() + data.slice(1) : '-');
				}
			},
			{
				data: 'event_type',
				render: function(data) {
					return data ? '<code class="small">' + data + '</code>' : '-';
				}
			},
			{
				data: 'event_id',
				render: function(data) {
					if (data && data.length > 20) {
						return '<small title="' + data + '">' + data.substring(0, 20) + '...</small>';
					}
					return data ? '<small>' + data + '</small>' : '-';
				}
			},
			{
				data: 'signature_valid',
				render: function(data) {
					if (data === null || data === '') return '<span class="text-muted">-</span>';
					if (data == 1) {
						return '<span class="badge bg-success"><i class="fas fa-check"></i> Valid</span>';
					}
					return '<span class="badge bg-danger"><i class="fas fa-times"></i> Invalid</span>';
				}
			},
			{
				data: 'processed',
				render: function(data, type, row) {
					if (data == 1) {
						return '<span class="badge bg-success"><i class="fas fa-check-circle"></i> Yes</span>';
					}
					return '<span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> Pending</span>';
				}
			},
			{
				data: 'process_result',
				render: function(data) {
					if (!data) return '-';
					var isError = data.toLowerCase().includes('error') || data.toLowerCase().includes('fail');
					var badgeClass = isError ? 'bg-danger' : 'bg-info';
					if (data.length > 30) {
						return '<span class="badge ' + badgeClass + '" title="' + data + '">' + data.substring(0, 30) + '...</span>';
					}
					return '<span class="badge ' + badgeClass + '">' + data + '</span>';
				}
			},
			{
				data: 'received_at',
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
				render: function(data) {
					return '<button type="button" class="btn btn-sm btn-outline-primary btn-view-webhook" data-id="' + data + '"><i class="fas fa-eye"></i></button>';
				}
			}
		],
		order: [[0, 'desc']],
		pageLength: 25,
		language: {
			processing: '<i class="fas fa-spinner fa-spin fa-2x"></i>',
			emptyTable: 'No webhook logs found'
		}
	});

	// Apply filters
	$('#btnApplyFilter').on('click', function() {
		table.ajax.reload();
	});

	// Reset filters
	$('#btnResetFilter').on('click', function() {
		$('#filterGateway').val('');
		$('#filterProcessed').val('');
		$('#filterSignature').val('');
		table.ajax.reload();
	});

	// Refresh table
	$('#btnRefresh').on('click', function() {
		table.ajax.reload();
	});

	// View webhook payload
	$(document).on('click', '.btn-view-webhook', function() {
		var id = $(this).data('id');
		$('#webhookDetails').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
		$('#webhookModal').modal('show');

		$.ajax({
			url: '<?php echo base_url(); ?>whmazadmin/paymentgateway/webhook_detail/' + id,
			type: 'GET',
			dataType: 'json',
			success: function(response) {
				if (response.success) {
					var log = response.data;
					var html = '<div class="row mb-3">';
					html += '<div class="col-md-6">';
					html += '<p><strong>Gateway:</strong> ' + log.gateway_code + '</p>';
					html += '<p><strong>Event Type:</strong> ' + (log.event_type || '-') + '</p>';
					html += '<p><strong>Event ID:</strong> ' + (log.event_id || '-') + '</p>';
					html += '</div>';
					html += '<div class="col-md-6">';
					html += '<p><strong>IP Address:</strong> ' + (log.ip_address || '-') + '</p>';
					html += '<p><strong>Received:</strong> ' + log.received_at + '</p>';
					html += '<p><strong>Processed:</strong> ' + (log.processed == 1 ? 'Yes' : 'No') + '</p>';
					html += '</div></div>';

					if (log.signature) {
						html += '<div class="mb-3"><strong>Signature:</strong><br><code class="small">' + log.signature + '</code></div>';
					}

					if (log.process_result) {
						var alertClass = log.process_result.toLowerCase().includes('error') ? 'alert-danger' : 'alert-info';
						html += '<div class="alert ' + alertClass + '"><strong>Result:</strong> ' + log.process_result + '</div>';
					}

					html += '<div class="mb-3"><strong>Payload:</strong></div>';
					html += '<pre class="bg-dark text-light p-3 rounded" style="max-height: 400px; overflow: auto;">';
					try {
						var payload = JSON.parse(log.payload);
						html += JSON.stringify(payload, null, 2);
					} catch (e) {
						html += log.payload;
					}
					html += '</pre>';

					if (log.headers) {
						html += '<div class="mt-3"><strong>Headers:</strong></div>';
						html += '<pre class="bg-secondary text-light p-3 rounded" style="max-height: 200px; overflow: auto;">';
						try {
							var headers = JSON.parse(log.headers);
							html += JSON.stringify(headers, null, 2);
						} catch (e) {
							html += log.headers;
						}
						html += '</pre>';
					}

					$('#webhookDetails').html(html);
				} else {
					$('#webhookDetails').html('<div class="alert alert-danger">Failed to load webhook details</div>');
				}
			},
			error: function() {
				$('#webhookDetails').html('<div class="alert alert-danger">Error loading webhook details</div>');
			}
		});
	});
});
</script>

<style>
.order-page-header {
	background: linear-gradient(135deg, #1976D2 0%, #1565C0 100%);
	border-radius: 10px;
	padding: 25px 30px;
	box-shadow: 0 4px 15px rgba(25, 118, 210, 0.3);
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
</style>

<?php $this->load->view('whmazadmin/include/footer'); ?>
