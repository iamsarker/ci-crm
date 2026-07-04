<?php
$this->load->view('templates/customer/header');
$statusMap = array(
	0 => array('Pending', 'warning'),
	1 => array('Active', 'success'),
	2 => array('Expired', 'secondary'),
	3 => array('Suspended', 'danger'),
	4 => array('Terminated', 'dark'),
);
?>

<div class="content content-fixed content-wrapper">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">

        <div class="page-header-card">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h3><i class="fa fa-cube mg-r-10"></i>My Software</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-style1 mg-b-0">
                            <li class="breadcrumb-item"><a href="<?=base_url()?>clientarea">Portal home</a></li>
                            <li class="breadcrumb-item active"><a>My Software</a></li>
                        </ol>
                    </nav>
                </div>
                <a href="<?=base_url()?>cart/software" class="btn btn-primary"><i class="fa fa-cart-plus mg-r-5"></i> Buy Software</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <?php if (empty($licenses)): ?>
                    <div class="text-center py-5">
                        <i class="fa fa-cube fa-3x text-muted mb-3 d-block"></i>
                        <p class="text-muted">You haven't purchased any software yet.</p>
                        <a href="<?=base_url()?>cart/software" class="btn btn-primary"><i class="fa fa-cart-plus mg-r-5"></i> Browse Software</a>
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Billing</th>
                                <th>License Key</th>
                                <th>Next Renewal</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($licenses as $lic):
                                $st = isset($statusMap[(int)$lic['status']]) ? $statusMap[(int)$lic['status']] : array('Unknown','secondary');
                                $isActive = (int)$lic['status'] === 1;
                            ?>
                            <tr>
                                <td>
                                    <span class="fw-semibold"><i class="fa fa-cube mg-r-5 text-muted"></i><?= htmlspecialchars($lic['plan_name']) ?></span>
                                    <?php if (!empty($lic['pending_invoice_id'])): ?>
                                        <span class="badge bg-info ms-1">Change pending payment</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($lic['currency_code'] ?? '') ?> <?= number_format((float)$lic['recurring_amount'], 2) ?> / <?= htmlspecialchars($lic['cycle_name'] ?? '') ?></td>
                                <td>
                                    <?php if ($isActive && !empty($lic['license_key'])): ?>
                                        <code style="font-size:.8rem;"><?= htmlspecialchars($lic['license_key']) ?></code>
                                        <?php if (!empty($lic['license_domain']) && !empty($lic['license_ip'])): ?>
                                            <div class="small text-muted mt-1" title="This license is bound to this install">
                                                <i class="fa fa-link mg-r-3"></i><?= htmlspecialchars($lic['license_domain']) ?> &middot; <?= htmlspecialchars($lic['license_ip']) ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= !empty($lic['next_renewal_date']) ? date('M j, Y', strtotime($lic['next_renewal_date'])) : '&mdash;' ?></td>
                                <td class="text-center"><span class="badge bg-<?= $st[1] ?>"><?= $st[0] ?></span></td>
                                <td class="text-end">
                                    <?php if ($isActive): ?>
                                        <a href="<?=base_url()?>subscription/download/<?= (int)$lic['id'] ?>" class="btn btn-sm btn-outline-primary" title="Download"><i class="fa fa-download"></i></a>
                                        <?php if (!empty($lic['license_ip'])): ?>
                                            <form method="post" action="<?=base_url()?>subscription/reset_ip" class="d-inline" onsubmit="return confirm('Reset the bound IP for this license? You will set a new server IP the next time you download. The domain stays locked.');">
                                                <input type="hidden" name="<?=$this->security->get_csrf_token_name()?>" value="<?=$this->security->get_csrf_hash()?>" />
                                                <input type="hidden" name="license_id" value="<?= (int)$lic['id'] ?>" />
                                                <button type="submit" class="btn btn-sm btn-outline-warning" title="Reset bound IP"><i class="fa fa-sync-alt mg-r-3"></i>Reset IP</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if (!empty($lic['family_group']) && empty($lic['pending_invoice_id'])): ?>
                                            <a href="<?=base_url()?>subscription/upgrade/<?= (int)$lic['id'] ?>" class="btn btn-sm btn-outline-success" title="Change plan"><i class="fa fa-arrow-up"></i> Upgrade</a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php $this->load->view('templates/customer/footer_script'); ?>
<?php $this->load->view('templates/customer/footer'); ?>
