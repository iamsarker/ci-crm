<?php $this->load->view('whmazadmin/include/header'); ?>
<link rel="stylesheet" href="<?=base_url()?>resources/assets/css/admin.manage_view.css">

<style>
.history-card {
	background: #fff;
	border-radius: 12px;
	box-shadow: 0 2px 10px rgba(0,0,0,0.08);
	margin-bottom: 15px;
	border: 1px solid #e9ecef;
	transition: all 0.3s ease;
}
.history-card:hover {
	box-shadow: 0 4px 15px rgba(0,0,0,0.12);
	transform: translateY(-2px);
}
.history-card .card-header {
	background: linear-gradient(135deg, #f8f9fa, #e9ecef);
	border-bottom: 1px solid #e9ecef;
	padding: 15px 20px;
	border-radius: 12px 12px 0 0;
}
.history-card .card-body {
	padding: 20px;
}
.history-card.type-created .card-header {
	background: linear-gradient(135deg, #d4edda, #c3e6cb);
	border-left: 4px solid #28a745;
}
.history-card.type-updated .card-header {
	background: linear-gradient(135deg, #d1ecf1, #bee5eb);
	border-left: 4px solid #17a2b8;
}
.history-card.type-restored .card-header {
	background: linear-gradient(135deg, #fff3cd, #ffeeba);
	border-left: 4px solid #fd7e14;
}
.badge-type {
	font-size: 11px;
	padding: 5px 12px;
	border-radius: 20px;
	text-transform: uppercase;
	font-weight: 600;
}
.badge-created { background: #28a745; color: #fff; }
.badge-updated { background: #17a2b8; color: #fff; }
.badge-restored { background: #fd7e14; color: #fff; }
.content-preview {
	background: #f8f9fa;
	border-radius: 8px;
	padding: 15px;
	max-height: 150px;
	overflow: hidden;
	position: relative;
	font-size: 13px;
	color: #666;
}
.content-preview::after {
	content: '';
	position: absolute;
	bottom: 0;
	left: 0;
	right: 0;
	height: 40px;
	background: linear-gradient(transparent, #f8f9fa);
}
.meta-info {
	display: flex;
	gap: 20px;
	flex-wrap: wrap;
	font-size: 13px;
	color: #6c757d;
}
.meta-info i {
	color: #00897B;
}
.current-version-badge {
	background: linear-gradient(135deg, #00897B, #00695C);
	color: #fff;
	padding: 4px 12px;
	border-radius: 20px;
	font-size: 11px;
	font-weight: 600;
}
.version-number {
	background: #e9ecef;
	color: #495057;
	padding: 4px 10px;
	border-radius: 6px;
	font-size: 12px;
	font-weight: 600;
}

/* Modal styles */
.history-modal .modal-header {
	background: linear-gradient(135deg, #00897B, #00695C);
	color: #fff;
}
.history-modal .modal-header .btn-close {
	filter: invert(1);
}
.history-content-display {
	background: #f8f9fa;
	border-radius: 8px;
	padding: 20px;
	max-height: 400px;
	overflow-y: auto;
}
</style>

<div class="content content-fluid content-wrapper">
	<div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

		<!-- Page Header -->
		<div class="row mt-4">
			<div class="col-12">
				<div class="company-page-header">
					<div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
						<div>
							<h3><i class="fa fa-history"></i> Version History</h3>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/page/index">Pages</a></li>
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/page/manage/<?= safe_encode($page['id']) ?>"><?= htmlspecialchars($page['page_title']) ?></a></li>
									<li class="breadcrumb-item active">History</li>
								</ol>
							</nav>
						</div>
						<div class="d-flex gap-2">
							<a href="<?=base_url()?>whmazadmin/page/manage/<?= safe_encode($page['id']) ?>" class="btn btn-back">
								<i class="fa fa-edit"></i> Edit Page
							</a>
							<a href="<?=base_url()?>whmazadmin/page/index" class="btn btn-back">
								<i class="fa fa-arrow-left"></i> Back to Pages
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Current Page Info -->
		<div class="row mt-4">
			<div class="col-12">
				<div class="manage-form-card">
					<div class="company-form-section mb-0">
						<div class="section-title d-flex justify-content-between align-items-center">
							<span><i class="fa fa-file-alt"></i> Current Version</span>
							<span class="current-version-badge"><i class="fa fa-check me-1"></i> Live</span>
						</div>
						<div class="row">
							<div class="col-md-6">
								<h5 class="mb-2"><?= htmlspecialchars($page['page_title']) ?></h5>
								<p class="text-muted mb-0">
									<code class="bg-light px-2 py-1 rounded">/<?= htmlspecialchars($page['page_slug']) ?></code>
								</p>
							</div>
							<div class="col-md-6 text-md-end">
								<div class="meta-info justify-content-md-end">
									<span><i class="fa fa-calendar-check me-1"></i> Updated: <?= !empty($page['updated_on']) ? date('M d, Y H:i', strtotime($page['updated_on'])) : '-' ?></span>
									<span><i class="fa fa-eye me-1"></i> <?= number_format($page['total_view'] ?? 0) ?> views</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- History Timeline -->
		<div class="row mt-4">
			<div class="col-12">
				<h5 class="mb-3"><i class="fa fa-stream me-2 text-muted"></i> All Versions (<?= count($history) ?>)</h5>

				<?php if (empty($history)): ?>
				<div class="manage-form-card">
					<div class="text-center py-5">
						<i class="fa fa-history fa-4x text-muted mb-3"></i>
						<p class="text-muted mb-0">No history records found for this page.</p>
					</div>
				</div>
				<?php else: ?>
					<?php $version = count($history); foreach ($history as $h): ?>
					<div class="history-card type-<?= $h['change_type'] ?>">
						<div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
							<div class="d-flex align-items-center gap-3">
								<span class="version-number">v<?= $version ?></span>
								<span class="badge-type badge-<?= $h['change_type'] ?>"><?= ucfirst($h['change_type']) ?></span>
								<span class="fw-semibold"><?= htmlspecialchars($h['page_title']) ?></span>
							</div>
							<div class="d-flex gap-2">
								<button type="button" class="btn btn-sm btn-outline-primary" onclick="viewVersion('<?= safe_encode($h['id']) ?>')">
									<i class="fa fa-eye me-1"></i> View
								</button>
								<?php if ($version > 1): ?>
								<button type="button" class="btn btn-sm btn-outline-warning" onclick="restoreVersion('<?= safe_encode($h['id']) ?>', '<?= htmlspecialchars(addslashes($h['page_title'])) ?>', '<?= date('M d, Y H:i', strtotime($h['changed_at'])) ?>')">
									<i class="fa fa-undo me-1"></i> Restore
								</button>
								<?php endif; ?>
							</div>
						</div>
						<div class="card-body">
							<div class="meta-info mb-3">
								<span><i class="fa fa-user me-1"></i> <?= htmlspecialchars($h['changed_by_name'] ?? 'Unknown') ?></span>
								<span><i class="fa fa-clock me-1"></i> <?= date('M d, Y H:i:s', strtotime($h['changed_at'])) ?></span>
								<?php if (!empty($h['change_note'])): ?>
								<span><i class="fa fa-comment me-1"></i> <?= htmlspecialchars($h['change_note']) ?></span>
								<?php endif; ?>
							</div>
							<div class="content-preview">
								<?= strip_tags($h['page_content']) ?>
							</div>
						</div>
					</div>
					<?php $version--; endforeach; ?>
				<?php endif; ?>
			</div>
		</div>

	</div><!-- container -->
</div><!-- content -->

<!-- View History Modal -->
<div class="modal fade history-modal" id="viewHistoryModal" tabindex="-1">
	<div class="modal-dialog modal-lg modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"><i class="fa fa-history me-2"></i> <span id="modalVersionTitle">Version Details</span></h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				<div class="mb-3">
					<label class="form-label fw-bold">Page Title</label>
					<p id="modalPageTitle" class="mb-0">-</p>
				</div>
				<div class="mb-3">
					<label class="form-label fw-bold">Meta Title</label>
					<p id="modalMetaTitle" class="mb-0 text-muted">-</p>
				</div>
				<div class="mb-3">
					<label class="form-label fw-bold">Meta Description</label>
					<p id="modalMetaDesc" class="mb-0 text-muted">-</p>
				</div>
				<div class="mb-3">
					<label class="form-label fw-bold">Content</label>
					<div class="history-content-display" id="modalContent">-</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script'); ?>

<script>
function viewVersion(historyId) {
	Swal.fire({
		title: 'Loading...',
		text: 'Fetching version details',
		allowOutsideClick: false,
		allowEscapeKey: false,
		showConfirmButton: false,
		didOpen: () => { Swal.showLoading(); }
	});

	$.ajax({
		url: '<?=base_url()?>whmazadmin/page/view_history/' + historyId,
		method: 'GET',
		dataType: 'json',
		success: function(response) {
			Swal.close();
			if (response.success) {
				var data = response.data;
				$('#modalPageTitle').text(data.page_title || '-');
				$('#modalMetaTitle').text(data.meta_title || '-');
				$('#modalMetaDesc').text(data.meta_description || '-');
				$('#modalContent').html(data.page_content || '-');
				$('#viewHistoryModal').modal('show');
			} else {
				toastError(response.message || 'Failed to load version');
			}
		},
		error: function() {
			Swal.close();
			toastError('Failed to load version details');
		}
	});
}

function restoreVersion(historyId, title, date) {
	Swal.fire({
		title: 'Restore this version?',
		html: 'This will restore the page content to:<br><strong>' + title + '</strong><br><small class="text-muted">from ' + date + '</small>',
		icon: 'question',
		showCancelButton: true,
		confirmButtonColor: '#fd7e14',
		cancelButtonColor: '#6c757d',
		confirmButtonText: '<i class="fa fa-undo me-1"></i> Yes, Restore',
		cancelButtonText: 'Cancel',
		reverseButtons: true
	}).then((result) => {
		if (result.isConfirmed) {
			Swal.fire({
				title: 'Restoring...',
				text: 'Please wait',
				allowOutsideClick: false,
				allowEscapeKey: false,
				showConfirmButton: false,
				didOpen: () => { Swal.showLoading(); }
			});
			window.location = '<?=base_url()?>whmazadmin/page/restore_history/' + historyId;
		}
	});
}
</script>

<?php $this->load->view('whmazadmin/include/footer'); ?>
