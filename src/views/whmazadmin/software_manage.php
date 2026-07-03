<?php $this->load->view('whmazadmin/include/header'); ?>
<link rel="stylesheet" href="<?=base_url()?>resources/assets/css/admin.manage_view.css">
<link rel="stylesheet" href="<?=base_url()?>resources/assets/css/admin.list_page.css">

<div class="content content-fluid content-wrapper">
    <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

        <!-- Page Header -->
        <div class="order-page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3><i class="fas fa-cube me-2"></i> Software Releases</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard">Dashboard</a></li>
                            <li class="breadcrumb-item active"><a href="#">Software Releases</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="manage-form-card">

            <!-- Upload new release -->
            <div class="order-card mb-4">
                <div class="card-header">
                    <div class="header-icon"><i class="fas fa-upload"></i></div>
                    <h6>Upload New Release</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Upload the installable ZIP for a software product. Tag it to a product
                        (or leave <strong>Global</strong> to serve any product). Customers with an
                        active license download the release linked on their product; plan
                        differences are enforced at runtime via the license check.
                    </p>
                    <form action="<?=base_url()?>whmazadmin/software/upload" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Version <span class="text-danger">*</span></label>
                                <input type="text" name="version" class="form-control" placeholder="e.g. 1.4.0" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Product</label>
                                <select name="product_id" class="form-select">
                                    <option value="">Global (all products)</option>
                                    <?php foreach (($products ?? array()) as $prod): ?>
                                    <option value="<?= intval($prod['id']) ?>"><?= htmlspecialchars($prod['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Software ZIP <span class="text-danger">*</span></label>
                                <input type="file" name="software_zip" class="form-control" accept=".zip" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Changelog</label>
                            <textarea name="changelog" class="form-control" rows="3" placeholder="What changed in this release"></textarea>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="is_current" name="is_current" value="1" checked>
                            <label class="form-check-label" for="is_current">Make this the current download</label>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn-create-order">
                                <i class="fas fa-upload me-2"></i> Upload Release
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Existing releases -->
            <div class="order-card mb-4">
                <div class="card-header">
                    <div class="header-icon"><i class="fas fa-list"></i></div>
                    <h6>Releases</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Version</th>
                                    <th>File</th>
                                    <th>Size</th>
                                    <th>Uploaded</th>
                                    <th>Current</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($releases)): ?>
                                <tr><td colspan="6" class="text-center text-muted">No releases uploaded yet.</td></tr>
                            <?php else: foreach ($releases as $r): ?>
                                <tr>
                                    <td><strong><?= html_escape($r['version']) ?></strong></td>
                                    <td><?= html_escape($r['original_name'] ?: $r['file_name']) ?></td>
                                    <td><?= !empty($r['file_size']) ? number_format($r['file_size'] / 1048576, 2) . ' MB' : '—' ?></td>
                                    <td><?= !empty($r['uploaded_on']) ? date('M j, Y H:i', strtotime($r['uploaded_on'])) : '—' ?></td>
                                    <td>
                                        <?php if (!empty($r['is_current'])): ?>
                                            <span class="badge bg-success">Current</span>
                                        <?php else: ?>
                                            <a href="<?=base_url()?>whmazadmin/software/set_current/<?= (int)$r['id'] ?>" class="badge bg-secondary text-decoration-none">Set current</a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?=base_url()?>whmazadmin/software/download/<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-primary" title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <a href="<?=base_url()?>whmazadmin/software/delete_records/<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete"
                                           onclick="return confirm('Remove this release?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
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
</div>

<?php $this->load->view('whmazadmin/include/footer_script'); ?>
<?php $this->load->view('whmazadmin/include/footer'); ?>
