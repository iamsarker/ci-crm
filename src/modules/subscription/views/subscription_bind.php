<?php $this->load->view('templates/customer/header'); ?>

<div class="content content-fixed content-wrapper">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">

        <div class="page-header-card">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h3><i class="fa fa-link mg-r-10"></i>Bind Your License</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-style1 mg-b-0">
                            <li class="breadcrumb-item"><a href="<?=base_url()?>clientarea">Portal home</a></li>
                            <li class="breadcrumb-item"><a href="<?=base_url()?>subscription">My Software</a></li>
                            <li class="breadcrumb-item active"><a>Bind License</a></li>
                        </ol>
                    </nav>
                </div>
                <a href="<?=base_url()?>subscription" class="btn btn-outline-secondary"><i class="fa fa-arrow-left mg-r-5"></i> Back</a>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <i class="fa fa-cube mg-r-10 text-primary"></i>
                        <h6 class="mg-b-0"><?= htmlspecialchars($license['plan_name']) ?></h6>
                    </div>
                    <div class="card-body">

<?php $domainLocked = !empty($license['license_domain']); ?>

                        <div class="alert alert-warning d-flex align-items-start" role="alert">
                            <i class="fa fa-exclamation-triangle mg-r-10 mt-1"></i>
                            <div>
                                <?php if ($domainLocked): ?>
                                    Set the <strong>server IP</strong> where this license will run. The
                                    install <strong>domain is locked</strong> and cannot be changed.
                                <?php else: ?>
                                    Before your first download you must bind this license to the
                                    <strong>domain</strong> and <strong>server IP</strong> where the software
                                    will be installed. <strong>The domain can only be set once</strong> —
                                    please double-check it. The IP can be reset later from My Software.
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted mb-1">License Key</label>
                            <div><code style="font-size:.85rem;"><?= htmlspecialchars($license['license_key'] ?? '') ?></code></div>
                        </div>

                        <form method="post" action="<?=base_url()?>subscription/bind">
                            <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>" />
                            <input type="hidden" name="license_id" value="<?= (int) $license['id'] ?>" />

                            <div class="mb-3">
                                <label class="form-label">Install Domain <?php if (!$domainLocked): ?><span class="text-danger">*</span><?php endif; ?></label>
                                <?php if ($domainLocked): ?>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($license['license_domain']) ?>" readonly disabled>
                                    <small class="text-muted"><i class="fa fa-lock mg-r-3"></i>Domain is locked to this license.</small>
                                <?php else: ?>
                                    <input type="text" name="domain" class="form-control" placeholder="app.example.com" required value="">
                                    <small class="text-muted">The domain where you will run the software (without http:// or www).</small>
                                <?php endif; ?>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Server IP Address <span class="text-danger">*</span></label>
                                <input type="text" name="ip" class="form-control" placeholder="203.0.113.10" required
                                       value="<?= htmlspecialchars($suggested_ip ?? '') ?>">
                                <small class="text-muted">The public IP of the server that will host the install (IPv4 or IPv6).</small>
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="<?=base_url()?>subscription" class="btn btn-light mg-r-10">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-download mg-r-5"></i> Bind &amp; Download
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php $this->load->view('templates/customer/footer_script'); ?>
<?php $this->load->view('templates/customer/footer'); ?>
