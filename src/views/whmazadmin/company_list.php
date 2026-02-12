<?php $this->load->view('whmazadmin/include/header');?>
<link href="<?=base_url()?>resources/assets/css/admin.list_page.css" rel="stylesheet">

<div class="content content-fluid content-wrapper">
	<div class="container-fluid pd-x-20 pd-lg-x-30 pd-xl-x-40">

		<!-- Flash Messages -->
		<?php if ($this->session->flashdata('admin_success')) { ?>
			<div class="alert alert-success alert-dismissible fade show" role="alert">
				<i class="fa fa-check-circle me-2"></i><?= $this->session->flashdata('admin_success') ?>
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		<?php } ?>

		<?php if ($this->session->flashdata('admin_error')) { ?>
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
				<i class="fa fa-exclamation-circle me-2"></i><?= $this->session->flashdata('admin_error') ?>
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		<?php } ?>

		<!-- New Company Credentials Alert -->
		<?php if ($this->session->flashdata('new_user_credentials')) {
			$credentials = $this->session->flashdata('new_user_credentials');
		?>
		<div class="credentials-alert mb-4">
			<div class="d-flex align-items-center mb-3">
				<div class="stats-icon warning me-3">
					<i class="fa fa-key"></i>
				</div>
				<div>
					<div class="alert-title"><i class="fa fa-exclamation-triangle me-1"></i> New Company Created - Save These Credentials!</div>
					<small class="text-muted">Please copy and share these credentials securely with the customer</small>
				</div>
			</div>

			<div class="credential-item">
				<span class="credential-label"><i class="fa fa-building me-2"></i>Company Name</span>
				<span class="credential-value"><?= htmlspecialchars($credentials['company_name'], ENT_QUOTES, 'UTF-8'); ?></span>
			</div>
			<div class="credential-item">
				<span class="credential-label"><i class="fa fa-envelope me-2"></i>Email / Username</span>
				<span class="credential-value"><?= htmlspecialchars($credentials['email'], ENT_QUOTES, 'UTF-8'); ?></span>
			</div>
			<div class="credential-item">
				<span class="credential-label"><i class="fa fa-lock me-2"></i>Temporary Password</span>
				<div>
					<span class="password-value" id="tempPassword"><?= htmlspecialchars($credentials['password'], ENT_QUOTES, 'UTF-8'); ?></span>
					<button type="button" class="btn btn-sm btn-outline-success ms-2" onclick="copyToClipboard('<?= htmlspecialchars($credentials['password'], ENT_QUOTES, 'UTF-8'); ?>')">
						<i class="fa fa-copy"></i> Copy
					</button>
				</div>
			</div>

			<div class="alert alert-danger mt-3 mb-0" style="border-radius: 8px;">
				<i class="fa fa-shield-alt me-1"></i>
				<strong>Security Notice:</strong> The customer should change their password immediately after first login. This message will disappear after page refresh.
			</div>
		</div>
		<?php } ?>

		<p class="mt-4">&nbsp;</p>

		<!-- Stats Cards -->
		<div class="row mb-4 mt-4" id="statsRow">
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon primary me-3">
							<i class="fa fa-building"></i>
						</div>
						<div>
							<div class="stats-value" id="totalCompanies">-</div>
							<div class="stats-label">Total Companies</div>
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
							<div class="stats-value" id="activeCompanies">-</div>
							<div class="stats-label">Active Companies</div>
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
							<div class="stats-value" id="thisMonthCompanies">-</div>
							<div class="stats-label">New This Month</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon warning me-3">
							<i class="fa fa-globe"></i>
						</div>
						<div>
							<div class="stats-value" id="countriesCount">-</div>
							<div class="stats-label">Countries</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Companies Table -->
		<div class="card table-card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<div>
					<h4 class="mb-1"><i class="fa fa-building me-2"></i>Companies / Customers</h4>
					<nav aria-label="breadcrumb" class="mb-0">
						<ol class="breadcrumb breadcrumb-style1 mb-0" style="background: transparent; padding: 0;">
							<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index" class="text-white-50">Dashboard</a></li>
							<li class="breadcrumb-item active text-white">Companies</li>
						</ol>
					</nav>
				</div>
				<a href="<?=base_url()?>whmazadmin/company/manage" class="btn btn-light btn-sm">
					<i class="fa fa-plus-circle me-1"></i> Add New Company
				</a>
			</div>
			<div class="card-body">
				<table id="companyListDt" class="table table-hover w-100"></table>
			</div>
		</div>

	</div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script');?>

<script>
$(function(){
	'use strict'

	var companyTable = $('#companyListDt').DataTable({
		"responsive": true,
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": "<?=base_url()?>whmazadmin/company/ssp_list_api/",
			"dataSrc": function(json) {
				// Update stats from response
				$('#totalCompanies').text(json.recordsTotal || 0);
				$('#activeCompanies').text(json.recordsTotal || 0);
				return json.data;
			}
		},
		"order": [[6, 'desc']],
		"language": {
			"processing": '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
			"emptyTable": '<div class="text-center py-4"><i class="fa fa-building fa-3x text-muted mb-3"></i><p class="text-muted">No companies found</p></div>',
			"zeroRecords": '<div class="text-center py-4"><i class="fa fa-search fa-3x text-muted mb-3"></i><p class="text-muted">No matching companies found</p></div>'
		},
		"columns": [
			{
				"title": "Company",
				"data": "name",
				"width": "22%",
				render: function(data, type, row) {
					var html = '<div class="company-name">' + escapeXSS(data) + '</div>';
					if (row.first_name || row.last_name) {
						html += '<small class="text-muted">' + escapeXSS((row.first_name || '') + ' ' + (row.last_name || '')) + '</small>';
					}
					return html;
				}
			},
			{
				"title": "Email",
				"data": "email",
				"width": "20%",
				render: function(data) {
					return '<span class="company-email"><i class="fa fa-envelope me-1 text-muted"></i>' + escapeXSS(data) + '</span>';
				}
			},
			{
				"title": "Mobile",
				"data": "mobile",
				"width": "12%",
				render: function(data) {
					return '<i class="fa fa-phone me-1 text-muted"></i>' + escapeXSS(data || '-');
				}
			},
			{
				"title": "Location",
				"data": "country",
				"width": "15%",
				render: function(data, type, row) {
					var location = [];
					if (row.city) location.push(escapeXSS(row.city));
					if (data) location.push(escapeXSS(data));
					return '<i class="fa fa-map-marker-alt me-1 text-muted"></i>' + (location.join(', ') || '-');
				}
			},
			{
				"title": "Status",
				"data": "status",
				"width": "8%",
				"className": "text-center",
				"orderable": false,
				"searchable": false,
				render: function (data, type) {
					if (data == 1) {
						return '<span class="badge bg-success"><i class="fa fa-check me-1"></i>Active</span>';
					} else {
						return '<span class="badge bg-danger"><i class="fa fa-times me-1"></i>Inactive</span>';
					}
				}
			},
			{
				"title": "Registered",
				"data": "inserted_on",
				"width": "12%",
				"searchable": false,
				render: function(data) {
					if (!data) return '-';
					var date = new Date(data);
					return '<i class="fa fa-calendar me-1 text-muted"></i>' + date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
				}
			},
			{
				"title": "Actions",
				"data": "id",
				"width": "11%",
				"className": "text-center",
				"orderable": false,
				"searchable": false,
				"render": function (data, type, row, meta) {
					let idVal = safe_encode(data);
					return '<button type="button" class="btn btn-action btn-manage" onclick="openManage(\''+idVal+'\')" title="Manage Company">' +
							'<i class="fa fa-cog"></i></button> ' +
						   '<button type="button" class="btn btn-action btn-delete" onclick="deleteRow(\''+idVal+'\', \''+escapeXSS(row['name'])+'\')" title="Delete Company">' +
							'<i class="fa fa-trash"></i></button>';
				}
			}
		],
		"drawCallback": function(settings) {
			// Initialize tooltips
			$('[title]').tooltip({ placement: 'top', trigger: 'hover' });
		}
	});

});

function openManage(id) {
	// Show loading overlay
	Swal.fire({
		title: 'Loading...',
		text: 'Please wait',
		allowOutsideClick: false,
		allowEscapeKey: false,
		showConfirmButton: false,
		didOpen: () => {
			Swal.showLoading();
		}
	});
	window.location = "<?=base_url()?>whmazadmin/company/manage/" + id;
}

// Copy password to clipboard
function copyToClipboard(text) {
	if (navigator.clipboard && window.isSecureContext) {
		navigator.clipboard.writeText(text).then(function() {
			toastSuccess('Password copied to clipboard!');
		}, function(err) {
			fallbackCopyToClipboard(text);
		});
	} else {
		fallbackCopyToClipboard(text);
	}
}

function fallbackCopyToClipboard(text) {
	const textArea = document.createElement("textarea");
	textArea.value = text;
	textArea.style.position = "fixed";
	textArea.style.left = "-999999px";
	textArea.style.top = "-999999px";
	document.body.appendChild(textArea);
	textArea.focus();
	textArea.select();
	try {
		document.execCommand('copy');
		toastSuccess('Password copied to clipboard!');
	} catch (err) {
		toastError('Failed to copy password. Please copy manually.');
	}
	document.body.removeChild(textArea);
}

function deleteRow(id, title) {
	Swal.fire({
		title: 'Delete Company?',
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
				didOpen: () => {
					Swal.showLoading();
				}
			});
			window.location = "<?=base_url()?>whmazadmin/company/delete_records/" + id;
		}
	});
}
</script>

<?php $this->load->view('whmazadmin/include/footer');?>
