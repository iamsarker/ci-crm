<?php
$this->load->view('whmazadmin/include/header');
$total    = count($products);
$active   = 0;
$priced   = 0;
foreach ($products as $p) {
	if (!empty($p['is_active'])) { $active++; }
	if (!empty($p['pricing_count'])) { $priced++; }
}
?>
<link href="<?=base_url()?>resources/assets/css/admin.list_page.css" rel="stylesheet">

<div class="content content-fluid content-wrapper">
	<div class="container-fluid pd-x-20 pd-lg-x-30 pd-xl-x-40">

		<p class="mt-4">&nbsp;</p>

		<!-- Stats Cards -->
		<div class="row mb-4 mt-4">
			<div class="col-xl-4 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon primary me-3"><i class="fa fa-cube"></i></div>
						<div>
							<div class="stats-value"><?= intval($total) ?></div>
							<div class="stats-label">Total Products</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-4 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon success me-3"><i class="fa fa-check-circle"></i></div>
						<div>
							<div class="stats-value"><?= intval($active) ?></div>
							<div class="stats-label">Active Products</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-4 col-md-6 mb-3">
				<div class="card stats-card">
					<div class="card-body d-flex align-items-center">
						<div class="stats-icon info me-3"><i class="fa fa-tags"></i></div>
						<div>
							<div class="stats-value"><?= intval($priced) ?></div>
							<div class="stats-label">Products with Pricing</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Products Table -->
		<div class="card table-card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<div>
					<h4 class="mb-1"><i class="fa fa-cube me-2"></i>Software Products</h4>
					<nav aria-label="breadcrumb" class="mb-0">
						<ol class="breadcrumb breadcrumb-style1 mb-0">
							<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
							<li class="breadcrumb-item active">Software Products</li>
						</ol>
					</nav>
				</div>
				<a href="<?=base_url()?>whmazadmin/softwareproduct/manage" class="btn btn-light btn-sm">
					<i class="fa fa-plus-circle me-1"></i> Add Product
				</a>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-hover w-100">
						<thead>
							<tr>
								<th>Product</th>
								<th>Key</th>
								<th class="text-center">Pricing</th>
								<th class="text-center">Popular</th>
								<th class="text-center">Status</th>
								<th class="text-end">Actions</th>
							</tr>
						</thead>
						<tbody>
							<?php if (empty($products)): ?>
							<tr>
								<td colspan="6" class="text-center py-4">
									<i class="fa fa-cube fa-3x text-muted mb-3 d-block"></i>
									<p class="text-muted mb-0">No software products yet. Click <strong>Add Product</strong> to create one.</p>
								</td>
							</tr>
							<?php else: foreach ($products as $p): $eid = safe_encode($p['id']); ?>
							<tr>
								<td>
									<span class="fw-semibold"><i class="fa fa-cube me-1 text-muted"></i><?= htmlspecialchars($p['name']) ?></span>
									<?php if (!empty($p['tagline'])): ?>
										<div class="text-muted small"><?= htmlspecialchars($p['tagline']) ?></div>
									<?php endif; ?>
								</td>
								<td><code><?= htmlspecialchars($p['plan_key']) ?></code></td>
								<td class="text-center">
									<?php if (!empty($p['pricing_count'])): ?>
										<span class="badge bg-success"><?= intval($p['pricing_count']) ?> price(s)</span>
									<?php else: ?>
										<span class="badge bg-warning text-dark">No pricing</span>
									<?php endif; ?>
								</td>
								<td class="text-center">
									<?= !empty($p['is_popular']) ? '<i class="fa fa-star text-warning"></i>' : '<span class="text-muted">-</span>' ?>
								</td>
								<td class="text-center">
									<?php if (!empty($p['is_active'])): ?>
										<span class="badge bg-success"><i class="fa fa-check me-1"></i>Active</span>
									<?php else: ?>
										<span class="badge bg-secondary"><i class="fa fa-ban me-1"></i>Inactive</span>
									<?php endif; ?>
								</td>
								<td class="text-end">
									<a href="<?=base_url()?>whmazadmin/softwareproduct/manage/<?= $eid ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fa fa-edit"></i></a>
									<a href="<?=base_url()?>whmazadmin/softwareproduct/toggle_active/<?= $eid ?>" class="btn btn-sm btn-outline-secondary" title="Toggle status"><i class="fa fa-power-off"></i></a>
									<a href="<?=base_url()?>whmazadmin/softwareproduct/delete_records/<?= $eid ?>" class="btn btn-sm btn-outline-danger btn-delete-confirm" title="Delete"><i class="fa fa-trash"></i></a>
								</td>
							</tr>
							<?php endforeach; endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>

	</div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script');?>

<script>
$(function () {
	$('.btn-delete-confirm').on('click', function (e) {
		if (!confirm('Delete this software product? This cannot be undone.')) {
			e.preventDefault();
		}
	});
});
</script>

<?php $this->load->view('whmazadmin/include/footer'); ?>
