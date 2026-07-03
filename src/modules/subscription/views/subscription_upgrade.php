<?php $this->load->view('templates/customer/header'); ?>

<div class="content content-fixed content-wrapper">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">

        <div class="page-header-card">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h3><i class="fa fa-arrow-up mg-r-10"></i>Change Plan</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-style1 mg-b-0">
                            <li class="breadcrumb-item"><a href="<?=base_url()?>clientarea">Portal home</a></li>
                            <li class="breadcrumb-item"><a href="<?=base_url()?>subscription">My Software</a></li>
                            <li class="breadcrumb-item active"><a>Change Plan</a></li>
                        </ol>
                    </nav>
                </div>
                <a href="<?=base_url()?>subscription" class="btn btn-light"><i class="fa fa-arrow-left mg-r-5"></i> Back</a>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between flex-wrap">
                    <div>
                        <div class="text-muted small">Current plan</div>
                        <div class="h5 mb-0"><?= htmlspecialchars($license['plan_name']) ?></div>
                    </div>
                    <div class="text-end">
                        <div class="text-muted small">Current price</div>
                        <div class="h5 mb-0"><?= htmlspecialchars($currency_code) ?> <?= number_format((float)$license['recurring_amount'], 2) ?> / <?= htmlspecialchars($license['cycle_name'] ?? '') ?></div>
                    </div>
                </div>
                <hr>
                <p class="text-muted mb-0">
                    <i class="fa fa-info-circle mg-r-5"></i>
                    Upgrades are prorated for the <strong><?= (int)$remaining_days ?> days</strong> remaining until your next renewal and take effect once the difference is paid.
                    Downgrades apply immediately, with the new price billed from your next renewal.
                </p>
            </div>
        </div>

        <?php if (empty($options)): ?>
            <div class="alert alert-info">No other plans are available for this product.</div>
        <?php else: ?>
        <form method="post" action="<?=base_url()?>subscription/do_upgrade">
            <?= csrf_field() ?>
            <input type="hidden" name="license_id" value="<?= (int)$license['id'] ?>">

            <div class="row">
                <?php foreach ($options as $opt): ?>
                <div class="col-md-4 mb-3">
                    <label class="card h-100 plan-option" style="cursor:pointer;">
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="pricing_id" value="<?= (int)$opt['pricing_id'] ?>" required>
                                <span class="form-check-label fw-semibold"><?= htmlspecialchars($opt['name']) ?></span>
                            </div>
                            <?php if (!empty($opt['tagline'])): ?>
                                <div class="text-muted small mt-1"><?= htmlspecialchars($opt['tagline']) ?></div>
                            <?php endif; ?>
                            <div class="mt-2">
                                <span class="h5"><?= htmlspecialchars($currency_code) ?> <?= number_format((float)$opt['recurring_amount'], 2) ?></span>
                                <span class="text-muted">/ <?= htmlspecialchars($license['cycle_name'] ?? '') ?></span>
                            </div>
                            <div class="mt-2">
                                <?php if (!empty($opt['is_upgrade'])): ?>
                                    <span class="badge bg-success">Pay now: <?= htmlspecialchars($currency_code) ?> <?= number_format((float)$opt['proration'], 2) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Downgrade — applies immediately</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="text-end mt-2">
                <button type="submit" class="btn btn-primary"><i class="fa fa-check mg-r-5"></i> Confirm Change</button>
            </div>
        </form>
        <?php endif; ?>

    </div>
</div>

<?php $this->load->view('templates/customer/footer_script'); ?>
<?php $this->load->view('templates/customer/footer'); ?>
