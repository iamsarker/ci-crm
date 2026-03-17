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
							<i class="fa fa-tags"></i>
						</div>
						<div>
							<div class="stats-value" id="statTotal">-</div>
							<div class="stats-label">Total Promo Codes</div>
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
							<div class="stats-value" id="statActive">-</div>
							<div class="stats-label">Active</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon warning me-3">
							<i class="fa fa-clock"></i>
						</div>
						<div>
							<div class="stats-value" id="statExpired">-</div>
							<div class="stats-label">Expired</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon info me-3">
							<i class="fa fa-receipt"></i>
						</div>
						<div>
							<div class="stats-value" id="statUsage">-</div>
							<div class="stats-label">Total Redemptions</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Promo Codes Table -->
		<div class="card table-card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<div>
					<h4 class="mb-1"><i class="fa fa-tags me-2"></i>Promo Codes</h4>
					<nav aria-label="breadcrumb" class="mb-0">
						<ol class="breadcrumb breadcrumb-style1 mb-0" class="breadcrumb-transparent">
							<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index" class="text-white-50">Dashboard</a></li>
							<li class="breadcrumb-item active text-white">Promo Codes</li>
						</ol>
					</nav>
				</div>
				<a href="<?=base_url()?>whmazadmin/promocode/manage" class="btn btn-light btn-sm">
					<i class="fa fa-plus-circle me-1"></i> Add Promo Code
				</a>
			</div>
			<div class="card-body">
				<table id="promoListDt" class="table table-hover w-100"></table>
			</div>
		</div>

	</div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script');?>

<script>
$(function(){
	'use strict'

	$('#promoListDt').DataTable({
		"responsive": true,
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": "<?=base_url()?>whmazadmin/promocode/ssp_list_api/",
			"dataSrc": function(json) {
				if (json.stats) {
					$('#statTotal').text(json.stats.total || 0);
					$('#statActive').text(json.stats.active || 0);
					$('#statExpired').text(json.stats.expired || 0);
					$('#statUsage').text(json.stats.total_usage || 0);
				}
				return json.data;
			}
		},
		"order": [[0, 'desc']],
		"language": {
			"processing": '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
			"emptyTable": '<div class="text-center py-4"><i class="fa fa-tags fa-3x text-muted mb-3"></i><p class="text-muted">No promo codes found</p></div>',
			"zeroRecords": '<div class="text-center py-4"><i class="fa fa-search fa-3x text-muted mb-3"></i><p class="text-muted">No matching promo codes found</p></div>'
		},
		"columns": [
			{
				"title": "Code",
				"data": "code",
				"width": "15%",
				render: function(data) {
					return '<span class="fw-semibold"><i class="fa fa-tag me-1 text-muted"></i><code>' + escapeXSS(data) + '</code></span>';
				}
			},
			{
				"title": "Discount",
				"data": "discount_value",
				"width": "12%",
				"searchable": false,
				render: function(data, type, row) {
					if (row.discount_type === 'percentage') {
						return '<span class="badge bg-info">' + parseFloat(data).toFixed(0) + '%</span>';
					} else {
						return '<span class="badge bg-primary">' + parseFloat(data).toFixed(2) + ' Fixed</span>';
					}
				}
			},
			{
				"title": "Applies To",
				"data": "applies_to",
				"width": "10%",
				"className": "text-center",
				"searchable": false,
				render: function(data) {
					var badges = {
						'all': '<span class="badge bg-success">All</span>',
						'products': '<span class="badge bg-warning text-dark">Products</span>',
						'customers': '<span class="badge bg-info">Customers</span>'
					};
					return badges[data] || data;
				}
			},
			{
				"title": "Validity",
				"data": "is_lifetime",
				"width": "15%",
				"searchable": false,
				render: function(data, type, row) {
					if (data == 1) {
						return '<span class="badge bg-success"><i class="fa fa-infinity me-1"></i>Lifetime</span>';
					}
					var start = row.start_date || '-';
					var end = row.end_date || '-';
					return '<small>' + escapeXSS(start) + ' <i class="fa fa-arrow-right mx-1"></i> ' + escapeXSS(end) + '</small>';
				}
			},
			{
				"title": "Usage",
				"data": "total_used",
				"width": "10%",
				"className": "text-center",
				"searchable": false,
				render: function(data, type, row) {
					var maxLabel = row.max_uses > 0 ? row.max_uses : '&infin;';
					return '<span class="badge bg-light text-dark">' + (data || 0) + ' / ' + maxLabel + '</span>';
				}
			},
			{
				"title": "Active",
				"data": "is_active",
				"width": "8%",
				"className": "text-center",
				"orderable": false,
				"searchable": false,
				render: function(data, type, row) {
					var checked = data == 1 ? 'checked' : '';
					return '<div class="form-check form-switch d-flex justify-content-center mb-0">' +
						'<input class="form-check-input" type="checkbox" ' + checked + ' onchange="toggleActive(\'' + row.encoded_id + '\', this)">' +
						'</div>';
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
					return '<button type="button" class="btn btn-action btn-manage" onclick="openManage(\'' + idVal + '\')" title="Edit"><i class="fa fa-cog"></i></button> ' +
						   '<button type="button" class="btn btn-action btn-delete" onclick="deleteRow(\'' + idVal + '\', \'' + escapeXSS(row['code']) + '\')" title="Delete"><i class="fa fa-trash"></i></button>';
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
	window.location = "<?=base_url()?>whmazadmin/promocode/manage/" + id;
}

function deleteRow(id, code) {
	Swal.fire({
		title: 'Delete Promo Code?',
		html: 'Are you sure you want to delete <strong>' + code + '</strong>?<br><small class="text-muted">This action cannot be undone.</small>',
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
			window.location = "<?=base_url()?>whmazadmin/promocode/delete_records/" + id;
		}
	});
}

function toggleActive(encodedId, el) {
	$.get("<?=base_url()?>whmazadmin/promocode/toggle_active/" + encodedId, function(resp) {
		if (resp.success) {
			var label = resp.is_active ? 'activated' : 'deactivated';
			Swal.fire({
				toast: true,
				position: 'top-end',
				icon: 'success',
				title: 'Promo code ' + label,
				showConfirmButton: false,
				timer: 1500
			});
		} else {
			el.checked = !el.checked;
			Swal.fire('Error', resp.message || 'Failed to toggle status', 'error');
		}
	}, 'json').fail(function() {
		el.checked = !el.checked;
		Swal.fire('Error', 'Failed to toggle status', 'error');
	});
}
</script>

<?php $this->load->view('whmazadmin/include/footer');?>
