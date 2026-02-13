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
							<i class="fa fa-file-alt"></i>
						</div>
						<div>
							<div class="stats-value" id="totalPages"><?= $stats['total'] ?? 0 ?></div>
							<div class="stats-label">Total Pages</div>
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
							<div class="stats-value" id="publishedPages"><?= $stats['published'] ?? 0 ?></div>
							<div class="stats-label">Published</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon warning me-3">
							<i class="fa fa-edit"></i>
						</div>
						<div>
							<div class="stats-value" id="draftPages"><?= $stats['draft'] ?? 0 ?></div>
							<div class="stats-label">Drafts</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon info me-3">
							<i class="fa fa-eye"></i>
						</div>
						<div>
							<div class="stats-value" id="totalViews"><?= $stats['total_views'] ?? 0 ?></div>
							<div class="stats-label">Total Views</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Pages Table -->
		<div class="card table-card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<div>
					<h4 class="mb-1"><i class="fa fa-file-alt me-2"></i>Dynamic Pages</h4>
					<nav aria-label="breadcrumb" class="mb-0">
						<ol class="breadcrumb breadcrumb-style1 mb-0" style="background: transparent; padding: 0;">
							<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index" class="text-white-50">Dashboard</a></li>
							<li class="breadcrumb-item active text-white">Pages</li>
						</ol>
					</nav>
				</div>
				<a href="<?=base_url()?>whmazadmin/page/manage" class="btn btn-light btn-sm">
					<i class="fa fa-plus-circle me-1"></i> Add Page
				</a>
			</div>
			<div class="card-body">
				<table id="pageListDt" class="table table-hover w-100"></table>
			</div>
		</div>

	</div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script');?>

<script>
$(function(){
	'use strict'

	$('#pageListDt').DataTable({
		"responsive": true,
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": "<?=base_url()?>whmazadmin/page/ssp_list_api/",
			"dataSrc": function(json) {
				if (json.stats) {
					$('#totalPages').text(json.stats.total || 0);
					$('#publishedPages').text(json.stats.published || 0);
					$('#draftPages').text(json.stats.draft || 0);
					$('#totalViews').text(json.stats.total_views || 0);
				}
				return json.data;
			}
		},
		"order": [[5, 'asc']],
		"language": {
			"processing": '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
			"emptyTable": '<div class="text-center py-4"><i class="fa fa-file-alt fa-3x text-muted mb-3"></i><p class="text-muted">No pages found</p></div>',
			"zeroRecords": '<div class="text-center py-4"><i class="fa fa-search fa-3x text-muted mb-3"></i><p class="text-muted">No matching pages found</p></div>'
		},
		"columns": [
			{
				"title": "Page Title",
				"data": "page_title",
				"width": "25%",
				render: function(data, type, row) {
					let icon = row.is_system == 1 ? '<i class="fa fa-lock text-warning me-1" title="System Page"></i>' : '<i class="fa fa-file-alt me-1 text-muted"></i>';
					return '<span class="fw-semibold">' + icon + escapeXSS(data) + '</span>';
				}
			},
			{
				"title": "Slug",
				"data": "page_slug",
				"width": "20%",
				render: function(data) {
					return '<code class="bg-light px-2 py-1 rounded">' + escapeXSS(data) + '</code>';
				}
			},
			{
				"title": "Views",
				"data": "total_view",
				"width": "8%",
				"className": "text-center",
				"searchable": false,
				render: function(data) {
					return '<span class="badge bg-light text-dark"><i class="fa fa-eye me-1"></i>' + (data || 0) + '</span>';
				}
			},
			{
				"title": "Published",
				"data": "is_published",
				"width": "10%",
				"className": "text-center",
				"orderable": false,
				"searchable": false,
				render: function(data, type, row) {
					let checked = data == 1 ? 'checked' : '';
					return '<div class="form-check form-switch d-flex justify-content-center">' +
						   '<input class="form-check-input" type="checkbox" ' + checked + ' onchange="togglePublish(\'' + row.encoded_id + '\')" style="cursor:pointer;">' +
						   '</div>';
				}
			},
			{
				"title": "Last Updated",
				"data": "updated_on",
				"width": "15%",
				"searchable": false,
				render: function(data, type, row) {
					let dateStr = data || row.inserted_on;
					if (!dateStr) return '-';
					var date = new Date(dateStr);
					return '<i class="fa fa-clock me-1 text-muted"></i>' + date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
				}
			},
			{
				"title": "Order",
				"data": "sort_order",
				"width": "7%",
				"className": "text-center",
				"searchable": false,
				render: function(data) {
					return '<span class="badge bg-secondary">' + (data || 0) + '</span>';
				}
			},
			{
				"title": "Actions",
				"data": "id",
				"width": "15%",
				"className": "text-center",
				"orderable": false,
				"searchable": false,
				"render": function(data, type, row) {
					let idVal = row.encoded_id;
					let actions = '<button type="button" class="btn btn-action btn-manage" onclick="openManage(\'' + idVal + '\')" title="Edit Page"><i class="fa fa-cog"></i></button> ';
					actions += '<button type="button" class="btn btn-action btn-info" onclick="viewHistory(\'' + idVal + '\')" title="View History"><i class="fa fa-history"></i></button> ';

					if (row.is_system != 1) {
						actions += '<button type="button" class="btn btn-action btn-delete" onclick="deleteRow(\'' + idVal + '\', \'' + escapeXSS(row['page_title']) + '\')" title="Delete Page"><i class="fa fa-trash"></i></button>';
					} else {
						actions += '<button type="button" class="btn btn-action" disabled title="System pages cannot be deleted"><i class="fa fa-lock text-muted"></i></button>';
					}

					return actions;
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
	window.location = "<?=base_url()?>whmazadmin/page/manage/" + id;
}

function viewHistory(id) {
	Swal.fire({
		title: 'Loading...',
		text: 'Please wait',
		allowOutsideClick: false,
		allowEscapeKey: false,
		showConfirmButton: false,
		didOpen: () => { Swal.showLoading(); }
	});
	window.location = "<?=base_url()?>whmazadmin/page/history/" + id;
}

function togglePublish(id) {
	$.ajax({
		url: "<?=base_url()?>whmazadmin/page/toggle_publish/" + id,
		method: "POST",
		dataType: 'json',
		success: function(response) {
			if (response.success) {
				toastSuccess(response.message);
			} else {
				toastError(response.message || 'Failed to update');
				$('#pageListDt').DataTable().ajax.reload(null, false);
			}
		},
		error: function() {
			toastError('Failed to update');
			$('#pageListDt').DataTable().ajax.reload(null, false);
		}
	});
}

function deleteRow(id, title) {
	Swal.fire({
		title: 'Delete Page?',
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
			window.location = "<?=base_url()?>whmazadmin/page/delete_records/" + id;
		}
	});
}
</script>

<?php $this->load->view('whmazadmin/include/footer');?>
