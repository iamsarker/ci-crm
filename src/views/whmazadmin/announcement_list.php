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
							<i class="fa fa-bullhorn"></i>
						</div>
						<div>
							<div class="stats-value" id="totalAnnouncements">-</div>
							<div class="stats-label">Total Announcements</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon success me-3">
							<i class="fa fa-paper-plane"></i>
						</div>
						<div>
							<div class="stats-value" id="publishedCount">-</div>
							<div class="stats-label">Published</div>
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
							<div class="stats-value" id="totalViews">-</div>
							<div class="stats-label">Total Views</div>
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
							<div class="stats-value" id="draftCount">-</div>
							<div class="stats-label">Drafts</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Announcements Table -->
		<div class="card table-card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<div>
					<h4 class="mb-1"><i class="fa fa-bullhorn me-2"></i>Announcements</h4>
					<nav aria-label="breadcrumb" class="mb-0">
						<ol class="breadcrumb breadcrumb-style1 mb-0" style="background: transparent; padding: 0;">
							<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index" class="text-white-50">Dashboard</a></li>
							<li class="breadcrumb-item active text-white">Announcements</li>
						</ol>
					</nav>
				</div>
				<a href="<?=base_url()?>whmazadmin/announcement/manage" class="btn btn-light btn-sm">
					<i class="fa fa-plus-circle me-1"></i> Add Announcement
				</a>
			</div>
			<div class="card-body">
				<table id="announcementListDt" class="table table-hover w-100"></table>
			</div>
		</div>

	</div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script');?>

<script>
$(function(){
	'use strict'

	$('#announcementListDt').DataTable({
		"responsive": true,
		"processing": true,
		"serverSide": true,
		"ajax": {
			"url": "<?=base_url()?>whmazadmin/announcement/ssp_list_api/",
			"dataSrc": function(json) {
				$('#totalAnnouncements').text(json.recordsTotal || 0);
				return json.data;
			}
		},
		"order": [[4, 'desc']],
		"language": {
			"processing": '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
			"emptyTable": '<div class="text-center py-4"><i class="fa fa-bullhorn fa-3x text-muted mb-3"></i><p class="text-muted">No announcements found</p></div>',
			"zeroRecords": '<div class="text-center py-4"><i class="fa fa-search fa-3x text-muted mb-3"></i><p class="text-muted">No matching announcements found</p></div>'
		},
		"columns": [
			{
				"title": "Title",
				"data": "title",
				"width": "30%",
				render: function(data) {
					return '<span class="fw-semibold"><i class="fa fa-bullhorn me-1 text-muted"></i>' + escapeXSS(data) + '</span>';
				}
			},
			{
				"title": "Published",
				"data": "is_published",
				"width": "10%",
				"className": "text-center",
				"orderable": false,
				"searchable": false,
				render: function(data) {
					if (data == 1) {
						return '<span class="badge bg-success"><i class="fa fa-check me-1"></i>Yes</span>';
					} else {
						return '<span class="badge bg-secondary"><i class="fa fa-clock me-1"></i>Draft</span>';
					}
				}
			},
			{
				"title": "Publish Date",
				"data": "publish_date",
				"width": "15%",
				render: function(data) {
					if (!data) return '-';
					var date = new Date(data);
					return '<i class="fa fa-calendar me-1 text-muted"></i>' + date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
				}
			},
			{
				"title": "Views",
				"data": "total_view",
				"width": "10%",
				"className": "text-center",
				"searchable": false,
				render: function(data) {
					return '<span class="badge bg-light text-dark"><i class="fa fa-eye me-1"></i>' + (data || 0) + '</span>';
				}
			},
			{
				"title": "Last Updated",
				"data": "updated_on",
				"width": "15%",
				"searchable": false,
				render: function(data) {
					if (!data) return '-';
					var date = new Date(data);
					return '<i class="fa fa-clock me-1 text-muted"></i>' + date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
				}
			},
			{
				"title": "Status",
				"data": "status",
				"width": "10%",
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
				"width": "10%",
				"className": "text-center",
				"orderable": false,
				"searchable": false,
				"render": function(data, type, row) {
					let idVal = safe_encode(data);
					return '<button type="button" class="btn btn-action btn-manage" onclick="openManage(\'' + idVal + '\')" title="Edit Announcement"><i class="fa fa-cog"></i></button> ' +
						   '<button type="button" class="btn btn-action btn-delete" onclick="deleteRow(\'' + idVal + '\', \'' + escapeXSS(row['title']) + '\')" title="Delete Announcement"><i class="fa fa-trash"></i></button>';
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
	window.location = "<?=base_url()?>whmazadmin/announcement/manage/" + id;
}

function deleteRow(id, title) {
	Swal.fire({
		title: 'Delete Announcement?',
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
			window.location = "<?=base_url()?>whmazadmin/announcement/delete_records/" + id;
		}
	});
}
</script>

<?php $this->load->view('whmazadmin/include/footer');?>
