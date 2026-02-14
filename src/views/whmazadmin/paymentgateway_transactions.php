<?php $this->load->view('whmazadmin/include/header'); ?>
<link rel="stylesheet" href="<?=base_url()?>resources/assets/css/admin.list_page.css">

<div class="content content-fluid content-wrapper">
	<div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">
		<p>&nbsp;</p>
		<!-- Page Header -->
		<div class="order-page-header mt-4">
			<div class="d-flex justify-content-between align-items-center">
				<div>
					<h3><i class="fas fa-exchange-alt me-2"></i> Payment Transactions</h3>
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item"><a href="<?php echo base_url(); ?>whmazadmin">Dashboard</a></li>
							<li class="breadcrumb-item"><a href="<?php echo base_url(); ?>whmazadmin/paymentgateway">Payment Gateways</a></li>
							<li class="breadcrumb-item active"><a href="#">Transactions</a></li>
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
						<select class="form-select" id="filterStatus">
							<option value="">All Status</option>
							<option value="completed">Completed</option>
							<option value="pending">Pending</option>
							<option value="processing">Processing</option>
							<option value="failed">Failed</option>
							<option value="cancelled">Cancelled</option>
							<option value="refunded">Refunded</option>
						</select>
					</div>
					<div class="col-md-3">
						<label class="form-label">Type</label>
						<select class="form-select" id="filterType">
							<option value="">All Types</option>
							<option value="payment">Payment</option>
							<option value="refund">Refund</option>
							<option value="partial_refund">Partial Refund</option>
							<option value="chargeback">Chargeback</option>
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

		<!-- Transactions Table -->
		<div class="table-card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h5 class="mb-0"><i class="fas fa-list me-2"></i> Transaction History</h5>
				<button type="button" class="btn btn-sm btn-outline-light" id="btnRefresh">
					<i class="fas fa-sync-alt"></i> Refresh
				</button>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-hover" id="transactionsTable">
						<thead>
							<tr>
								<th>ID</th>
								<th>Invoice</th>
								<th>Gateway</th>
								<th>Amount</th>
								<th>Type</th>
								<th>Status</th>
								<th>Payer</th>
								<th>Payment Method</th>
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

<!-- Transaction Details Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h5 class="modal-title"><i class="fas fa-receipt me-2"></i> Transaction Details</h5>
				<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body" id="transactionDetails">
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
	var table = $('#transactionsTable').DataTable({
		processing: true,
		serverSide: true,
		ajax: {
			url: '<?php echo base_url(); ?>whmazadmin/paymentgateway/transactions_api',
			type: 'GET',
			data: function(d) {
				d.gateway_code = $('#filterGateway').val();
				d.status = $('#filterStatus').val();
				d.txn_type = $('#filterType').val();
			}
		},
		columns: [
			{
				data: 'id',
				render: function(data, type, row) {
					return '<small class="text-muted">#' + data + '</small>';
				}
			},
			{
				data: 'invoice_id',
				render: function(data, type, row) {
					if (data) {
						return '<a href="<?php echo base_url(); ?>whmazadmin/invoice/manage/' + data + '" class="text-primary">#' + data + '</a>';
					}
					return '-';
				}
			},
			{
				data: 'gateway_code',
				render: function(data, type, row) {
					var icons = {
						'stripe': 'fa-credit-card',
						'paypal': 'fa-paypal',
						'razorpay': 'fa-rupee-sign',
						'sslcommerz': 'fa-mobile-alt',
						'bank_transfer': 'fa-university',
						'manual': 'fa-hand-holding-usd'
					};
					var icon = icons[data] || 'fa-money-bill';
					return '<i class="fas ' + icon + ' me-1"></i> ' + data.charAt(0).toUpperCase() + data.slice(1);
				}
			},
			{
				data: 'amount',
				render: function(data, type, row) {
					var html = '<strong>' + parseFloat(data).toFixed(2) + ' ' + (row.currency_code || '') + '</strong>';
					if (row.fee_amount && parseFloat(row.fee_amount) > 0) {
						html += '<br><small class="text-muted">Fee: ' + parseFloat(row.fee_amount).toFixed(2) + '</small>';
					}
					return html;
				}
			},
			{
				data: 'txn_type',
				render: function(data, type, row) {
					var badges = {
						'payment': 'bg-primary',
						'refund': 'bg-warning',
						'partial_refund': 'bg-info',
						'chargeback': 'bg-danger',
						'credit': 'bg-success'
					};
					var badgeClass = badges[data] || 'bg-secondary';
					return '<span class="badge ' + badgeClass + '">' + (data || 'payment').replace('_', ' ') + '</span>';
				}
			},
			{
				data: 'status',
				render: function(data, type, row) {
					var badges = {
						'completed': 'bg-success',
						'pending': 'bg-warning text-dark',
						'processing': 'bg-info',
						'failed': 'bg-danger',
						'cancelled': 'bg-secondary',
						'refunded': 'bg-dark'
					};
					var icons = {
						'completed': 'fa-check-circle',
						'pending': 'fa-clock',
						'processing': 'fa-spinner fa-spin',
						'failed': 'fa-times-circle',
						'cancelled': 'fa-ban',
						'refunded': 'fa-undo'
					};
					var badgeClass = badges[data] || 'bg-secondary';
					var icon = icons[data] || 'fa-question';
					return '<span class="badge ' + badgeClass + '"><i class="fas ' + icon + ' me-1"></i>' + (data || 'unknown') + '</span>';
				}
			},
			{
				data: 'payer_email',
				render: function(data, type, row) {
					if (row.payer_name || data) {
						var html = '';
						if (row.payer_name) html += '<strong>' + row.payer_name + '</strong><br>';
						if (data) html += '<small class="text-muted">' + data + '</small>';
						return html;
					}
					return '-';
				}
			},
			{
				data: 'payment_method',
				render: function(data, type, row) {
					var html = data ? data.charAt(0).toUpperCase() + data.slice(1) : '-';
					if (row.card_brand && row.card_last4) {
						html = '<i class="fab fa-cc-' + row.card_brand.toLowerCase() + ' me-1"></i> **** ' + row.card_last4;
					}
					return html;
				}
			},
			{
				data: 'initiated_at',
				render: function(data, type, row) {
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
					return '<button type="button" class="btn btn-sm btn-outline-primary btn-view-txn" data-id="' + row.id + '"><i class="fas fa-eye"></i></button>';
				}
			}
		],
		order: [[0, 'desc']],
		pageLength: 25,
		language: {
			processing: '<i class="fas fa-spinner fa-spin fa-2x"></i>',
			emptyTable: 'No transactions found'
		}
	});

	// Apply filters
	$('#btnApplyFilter').on('click', function() {
		table.ajax.reload();
	});

	// Reset filters
	$('#btnResetFilter').on('click', function() {
		$('#filterGateway').val('');
		$('#filterStatus').val('');
		$('#filterType').val('');
		table.ajax.reload();
	});

	// Refresh table
	$('#btnRefresh').on('click', function() {
		table.ajax.reload();
	});

	// View transaction details
	$(document).on('click', '.btn-view-txn', function() {
		var id = $(this).data('id');
		$('#transactionDetails').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
		$('#transactionModal').modal('show');

		$.ajax({
			url: '<?php echo base_url(); ?>whmazadmin/paymentgateway/transaction_detail/' + id,
			type: 'GET',
			dataType: 'json',
			success: function(response) {
				if (response.success) {
					var txn = response.data;
					var html = '<div class="row">';
					html += '<div class="col-md-6">';
					html += '<h6 class="text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Basic Info</h6>';
					html += '<table class="table table-sm">';
					html += '<tr><td class="text-muted">Transaction ID</td><td><strong>' + txn.transaction_uuid + '</strong></td></tr>';
					html += '<tr><td class="text-muted">Gateway TXN ID</td><td>' + (txn.gateway_transaction_id || '-') + '</td></tr>';
					html += '<tr><td class="text-muted">Invoice</td><td><a href="<?php echo base_url(); ?>whmazadmin/invoice/manage/' + txn.invoice_id + '">#' + txn.invoice_id + '</a></td></tr>';
					html += '<tr><td class="text-muted">Gateway</td><td>' + txn.gateway_code + '</td></tr>';
					html += '<tr><td class="text-muted">Status</td><td>' + txn.status + '</td></tr>';
					html += '</table></div>';

					html += '<div class="col-md-6">';
					html += '<h6 class="text-success mb-3"><i class="fas fa-dollar-sign me-2"></i>Amount Details</h6>';
					html += '<table class="table table-sm">';
					html += '<tr><td class="text-muted">Amount</td><td><strong>' + parseFloat(txn.amount).toFixed(2) + ' ' + txn.currency_code + '</strong></td></tr>';
					html += '<tr><td class="text-muted">Fee</td><td>' + parseFloat(txn.fee_amount || 0).toFixed(2) + ' ' + txn.currency_code + '</td></tr>';
					html += '<tr><td class="text-muted">Net Amount</td><td>' + parseFloat(txn.net_amount || txn.amount).toFixed(2) + ' ' + txn.currency_code + '</td></tr>';
					html += '</table></div></div>';

					if (txn.payer_email || txn.payer_name) {
						html += '<hr><h6 class="text-info mb-3"><i class="fas fa-user me-2"></i>Payer Info</h6>';
						html += '<p><strong>' + (txn.payer_name || '') + '</strong><br>' + (txn.payer_email || '') + '<br>' + (txn.payer_phone || '') + '</p>';
					}

					if (txn.failure_reason) {
						html += '<hr><div class="alert alert-danger"><strong>Failure Reason:</strong> ' + txn.failure_reason + '</div>';
					}

					$('#transactionDetails').html(html);
				} else {
					$('#transactionDetails').html('<div class="alert alert-danger">Failed to load transaction details</div>');
				}
			},
			error: function() {
				$('#transactionDetails').html('<div class="alert alert-danger">Error loading transaction details</div>');
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
