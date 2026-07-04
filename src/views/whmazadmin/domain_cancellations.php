<?php $this->load->view('whmazadmin/include/header'); ?>
<link rel="stylesheet" href="<?=base_url()?>resources/assets/css/admin.list_page.css">

<div class="content content-fluid content-wrapper">
	<div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">
		<p>&nbsp;</p>
		<!-- Page Header -->
		<div class="order-page-header mt-4">
			<div class="d-flex justify-content-between align-items-center">
				<div>
					<h3><i class="fas fa-times-circle me-2"></i> Domain Cancellation Requests</h3>
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item"><a href="<?php echo base_url(); ?>whmazadmin">Dashboard</a></li>
							<li class="breadcrumb-item"><a href="<?php echo base_url(); ?>whmazadmin/order">Orders</a></li>
							<li class="breadcrumb-item active"><a href="#">Cancellation Requests</a></li>
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
					<div class="stats-icon"><i class="fas fa-list"></i></div>
					<div class="stats-info">
						<h3><?php echo number_format($stats['total']); ?></h3>
						<p>Total Requests</p>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="stats-card stats-card-info">
					<div class="stats-icon"><i class="fas fa-hourglass-half"></i></div>
					<div class="stats-info">
						<h3><?php echo number_format($stats['pending']); ?></h3>
						<p>Pending</p>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="stats-card stats-card-danger">
					<div class="stats-icon"><i class="fas fa-ban"></i></div>
					<div class="stats-info">
						<h3><?php echo number_format($stats['processed']); ?></h3>
						<p>Processed</p>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="stats-card stats-card-success">
					<div class="stats-icon"><i class="fas fa-check-circle"></i></div>
					<div class="stats-info">
						<h3><?php echo number_format($stats['dismissed']); ?></h3>
						<p>Dismissed</p>
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
						<select class="form-select" id="filterStatus">
							<option value="">All</option>
							<option value="0">Pending</option>
							<option value="1">Processed</option>
							<option value="2">Dismissed</option>
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

		<!-- Requests Table -->
		<div class="table-card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h5 class="mb-0"><i class="fas fa-list me-2"></i> Requests</h5>
				<button type="button" class="btn btn-sm btn-outline-light" id="btnRefresh">
					<i class="fas fa-sync-alt"></i> Refresh
				</button>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-hover" id="cancellationTable">
						<thead>
							<tr>
								<th>ID</th>
								<th>Domain</th>
								<th>Customer</th>
								<th>Requested</th>
								<th>Status</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Process Modal -->
<div class="modal fade" id="processModal" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h5 class="modal-title"><i class="fas fa-times-circle me-2"></i> Cancellation Request</h5>
				<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				<input type="hidden" id="reqId">
				<dl class="row mb-3">
					<dt class="col-sm-4">Domain</dt><dd class="col-sm-8" id="reqDomain">-</dd>
					<dt class="col-sm-4">Customer</dt><dd class="col-sm-8" id="reqCustomer">-</dd>
					<dt class="col-sm-4">Requested</dt><dd class="col-sm-8" id="reqDate">-</dd>
					<dt class="col-sm-4">Reason</dt><dd class="col-sm-8" id="reqReason">-</dd>
				</dl>

				<div id="processControls">
					<label class="form-label">Cancellation Type</label>
					<div class="mb-3">
						<div class="form-check">
							<input class="form-check-input" type="radio" name="cancel_type" id="ctImmediate" value="immediate" checked>
							<label class="form-check-label" for="ctImmediate">Immediate (mark domain cancelled now)</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" type="radio" name="cancel_type" id="ctEnd" value="end_of_period">
							<label class="form-check-label" for="ctEnd">End of period (cancel at expiry date)</label>
						</div>
					</div>
					<div class="mb-2">
						<label class="form-label" for="adminNote">Admin Note (optional)</label>
						<textarea class="form-control" id="adminNote" rows="2" placeholder="Internal note / reason"></textarea>
					</div>
				</div>
				<div id="processedNote" class="alert alert-secondary d-none"></div>
			</div>
			<div class="modal-footer" id="processFooter">
				<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
				<button type="button" class="btn btn-warning" id="btnDismiss"><i class="fas fa-ban me-1"></i> Dismiss</button>
				<button type="button" class="btn btn-danger" id="btnProcess"><i class="fas fa-check me-1"></i> Cancel Domain</button>
			</div>
		</div>
	</div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script');?>

<script>
$(document).ready(function() {
	var CSRF_NAME = '<?php echo $this->security->get_csrf_token_name(); ?>';

	function statusBadge(status) {
		if (status == 0) return '<span class="badge bg-info"><i class="fas fa-hourglass-half me-1"></i> Pending</span>';
		if (status == 1) return '<span class="badge bg-danger"><i class="fas fa-ban me-1"></i> Processed</span>';
		if (status == 2) return '<span class="badge bg-secondary"><i class="fas fa-check me-1"></i> Dismissed</span>';
		return '<span class="badge bg-dark">-</span>';
	}

	function escapeHtml(s) {
		return $('<div>').text(s == null ? '' : String(s)).html();
	}

	var table = $('#cancellationTable').DataTable({
		processing: true,
		serverSide: true,
		ajax: {
			url: '<?php echo base_url(); ?>whmazadmin/cancellation/list_api',
			type: 'GET',
			data: function(d) {
				d.status = $('#filterStatus').val();
			}
		},
		columns: [
			{ data: 'id', render: function(data) { return '<small class="text-muted">#' + data + '</small>'; } },
			{ data: 'domain', render: function(data) { return '<i class="fas fa-globe me-1 text-muted"></i> ' + escapeHtml(data); } },
			{
				data: 'company_name',
				render: function(data, type, row) {
					var name = escapeHtml(data || row.customer_name || '-');
					return name;
				}
			},
			{
				data: 'requested_on',
				render: function(data) {
					if (!data) return '-';
					var date = new Date(data.replace(' ', 'T'));
					return '<small>' + date.toLocaleDateString() + '<br>' + date.toLocaleTimeString() + '</small>';
				}
			},
			{ data: 'status', render: function(data) { return statusBadge(data); } },
			{
				data: 'id',
				orderable: false,
				searchable: false,
				render: function(data, type, row) {
					var btn = '<button type="button" class="btn btn-sm btn-outline-primary btn-review" data-id="' + data + '"';
					btn += ' data-domain="' + escapeHtml(row.domain) + '"';
					btn += ' data-customer="' + escapeHtml(row.company_name || row.customer_name || '-') + '"';
					btn += ' data-date="' + escapeHtml(row.requested_on || '') + '"';
					btn += ' data-reason="' + escapeHtml(row.reason || '') + '"';
					btn += ' data-status="' + row.status + '"';
					btn += ' data-note="' + escapeHtml(row.admin_note || '') + '"';
					btn += '><i class="fas fa-eye"></i> Review</button>';
					return btn;
				}
			}
		],
		order: [[0, 'desc']],
		pageLength: 25,
		language: {
			processing: '<i class="fas fa-spinner fa-spin fa-2x"></i>',
			emptyTable: 'No cancellation requests found'
		}
	});

	$('#btnApplyFilter').on('click', function() { table.ajax.reload(); });
	$('#btnResetFilter').on('click', function() { $('#filterStatus').val(''); table.ajax.reload(); });
	$('#btnRefresh').on('click', function() { table.ajax.reload(); });

	// Open review modal
	$(document).on('click', '.btn-review', function() {
		var $b = $(this);
		var status = $b.data('status');
		$('#reqId').val($b.data('id'));
		$('#reqDomain').text($b.data('domain'));
		$('#reqCustomer').text($b.data('customer'));
		$('#reqDate').text($b.data('date') || '-');
		$('#reqReason').text($b.data('reason') || 'Not provided');
		$('#ctImmediate').prop('checked', true);
		$('#adminNote').val('');

		if (status == 0) {
			$('#processControls').show();
			$('#processedNote').addClass('d-none').text('');
			$('#btnProcess').show();
			$('#btnDismiss').show();
		} else {
			$('#processControls').hide();
			var label = (status == 1) ? 'This request was already processed (domain cancelled).' : 'This request was dismissed.';
			var note = $b.data('note');
			if (note) label += ' Note: ' + note;
			$('#processedNote').removeClass('d-none').text(label);
			$('#btnProcess').hide();
			$('#btnDismiss').hide();
		}

		$('#processModal').modal('show');
	});

	function postAction(url, confirmTitle, confirmText, confirmColor, confirmBtn) {
		var id = $('#reqId').val();
		var note = $('#adminNote').val();
		var cancelType = $('input[name="cancel_type"]:checked').val();

		Swal.fire({
			title: confirmTitle,
			text: confirmText,
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: confirmColor,
			cancelButtonColor: '#6c757d',
			confirmButtonText: confirmBtn
		}).then(function(result) {
			if (!result.isConfirmed) return;

			var payload = { id: id, note: note, cancel_type: cancelType };
			payload[CSRF_NAME] = '<?php echo $this->security->get_csrf_hash(); ?>';

			$.ajax({
				url: url,
				type: 'POST',
				dataType: 'json',
				data: payload,
				success: function(response) {
					Swal.fire({
						icon: response.success ? 'success' : 'error',
						title: response.success ? 'Done' : 'Failed',
						text: response.message
					});
					if (response.success) {
						$('#processModal').modal('hide');
						table.ajax.reload();
					}
				},
				error: function() {
					Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred. Please try again.' });
				}
			});
		});
	}

	$('#btnProcess').on('click', function() {
		postAction('<?php echo base_url(); ?>whmazadmin/cancellation/process',
			'Cancel this domain?', 'The domain will be cancelled per the selected type.', '#d33', 'Yes, cancel domain');
	});

	$('#btnDismiss').on('click', function() {
		postAction('<?php echo base_url(); ?>whmazadmin/cancellation/dismiss',
			'Dismiss this request?', 'The request will be closed without cancelling the domain.', '#f0ad4e', 'Yes, dismiss');
	});
});
</script>

<style>
.order-page-header {
	background: linear-gradient(135deg, #5E35B1 0%, #4527A0 100%);
	border-radius: 10px;
	padding: 25px 30px;
	box-shadow: 0 4px 15px rgba(94, 53, 177, 0.3);
}
.order-page-header h3 { color: #fff; margin: 0; font-weight: 600; }
.order-page-header .breadcrumb { background: transparent; margin: 8px 0 0 0; padding: 0; }
.order-page-header .breadcrumb-item a { color: rgba(255,255,255,0.8); }
.order-page-header .breadcrumb-item.active a { color: #fff; }
.order-page-header .breadcrumb-item + .breadcrumb-item::before { color: rgba(255,255,255,0.6); }
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
.order-page-header .btn-back:hover { background: rgba(255,255,255,0.25); color: #fff; }

.stats-card {
	background: #fff;
	border-radius: 10px;
	padding: 20px;
	display: flex;
	align-items: center;
	box-shadow: 0 2px 10px rgba(0,0,0,0.08);
	border-left: 4px solid;
}
.stats-card-primary { border-left-color: #1976D2; }
.stats-card-success { border-left-color: #43A047; }
.stats-card-danger { border-left-color: #E53935; }
.stats-card-info { border-left-color: #00ACC1; }
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
.stats-card-primary .stats-icon { background: rgba(25, 118, 210, 0.1); color: #1976D2; }
.stats-card-success .stats-icon { background: rgba(67, 160, 71, 0.1); color: #43A047; }
.stats-card-danger .stats-icon { background: rgba(229, 57, 53, 0.1); color: #E53935; }
.stats-card-info .stats-icon { background: rgba(0, 172, 193, 0.1); color: #00ACC1; }
.stats-card .stats-info h3 { margin: 0; font-size: 1.75rem; font-weight: 700; color: #333; }
.stats-card .stats-info p { margin: 0; color: #666; font-size: 0.9rem; }
</style>

<?php $this->load->view('whmazadmin/include/footer'); ?>
