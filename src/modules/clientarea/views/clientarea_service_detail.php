<?php $this->load->view('templates/customer/header');?>

<div class="content content-fixed content-wrapper">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">

        <!-- Page Header -->
        <div class="page-header-card page-header-services mg-b-25">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h3 class="mg-b-0"><i class="fa fa-cube mg-r-10"></i>Service Details</h3>
                    <nav aria-label="breadcrumb" class="mg-t-8">
                        <ol class="breadcrumb breadcrumb-style1 mg-b-0">
                            <li class="breadcrumb-item"><a href="<?=base_url()?>clientarea">Portal Home</a></li>
                            <li class="breadcrumb-item"><a href="<?=base_url()?>clientarea/services">My Services</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Service Details</li>
                        </ol>
                    </nav>
                </div>
                <div class="header-actions mg-t-10 mg-md-t-0">
                    <a href="<?=base_url()?>clientarea/services" class="btn btn-light">
                        <i class="fa fa-arrow-left mg-r-5"></i> Back to Services
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 col-md-4 mg-b-20">
                <!-- Server DNS Card -->
                <div class="card sidebar-card service-dns-card">
                    <div class="card-header sidebar-card-header">
                        <h6 class="mg-b-0"><i class="fa fa-server mg-r-8"></i>Server DNS</h6>
                    </div>
                    <div class="card-body pd-0">
                        <ul class="dns-list">
                            <li class="dns-item">
                                <span class="dns-label">NS1</span>
                                <span class="dns-value"><?= htmlspecialchars($dns['dns1'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                            </li>
                            <li class="dns-item">
                                <span class="dns-label">NS2</span>
                                <span class="dns-value"><?= htmlspecialchars($dns['dns2'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                            </li>
                            <?php if(!empty($dns['dns3'])): ?>
                            <li class="dns-item">
                                <span class="dns-label">NS3</span>
                                <span class="dns-value"><?= htmlspecialchars($dns['dns3'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                            </li>
                            <?php endif; ?>
                            <?php if(!empty($dns['dns4'])): ?>
                            <li class="dns-item">
                                <span class="dns-label">NS4</span>
                                <span class="dns-value"><?= htmlspecialchars($dns['dns4'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                            </li>
                            <?php endif; ?>
                            <li class="dns-item dns-item-highlight">
                                <span class="dns-label"><i class="fa fa-network-wired mg-r-5"></i>Primary IP</span>
                                <span class="dns-value font-weight-bold"><?= htmlspecialchars($dns['primar_ip'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Quick Actions Card -->
                <div class="card sidebar-card mg-t-20">
                    <div class="card-header sidebar-card-header">
                        <h6 class="mg-b-0"><i class="fa fa-bolt mg-r-8"></i>Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="<?=base_url()?>tickets/newticket" class="btn btn-outline-primary btn-sm">
                                <i class="fa fa-headset mg-r-5"></i> Open Support Ticket
                            </a>
                            <a href="<?=base_url()?>billing/invoices" class="btn btn-outline-info btn-sm">
                                <i class="fa fa-file-invoice mg-r-5"></i> View Invoices
                            </a>
                        </div>
                    </div>
                </div>

                <?php $this->load->view('templates/customer/service_nav');?>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9 col-md-8">
                <!-- Domain Header Banner -->
                <div class="service-domain-banner mg-b-20">
                    <div class="domain-icon">
                        <i class="fa fa-globe"></i>
                    </div>
                    <div class="domain-info">
                        <h2 class="domain-name"><?= htmlspecialchars($detail['hosting_domain'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></h2>
                        <span class="domain-product"><?= htmlspecialchars($detail['product_name'] ?? 'Hosting Service', ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div class="domain-status">
                        <?= isset($detail['status']) ? getServiceStatus($detail['status']) : '' ?>
                    </div>
                </div>

                <div class="row">
                    <!-- Order Details Card -->
                    <div class="col-lg-6 mg-b-20">
                        <div class="card service-detail-card">
                            <div class="card-header service-detail-header">
                                <h5 class="mg-b-0"><i class="fa fa-info-circle mg-r-10"></i>Order Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="detail-list">
                                    <div class="detail-item">
                                        <div class="detail-icon">
                                            <i class="fa fa-calendar-plus"></i>
                                        </div>
                                        <div class="detail-content">
                                            <span class="detail-label">Registration Date</span>
                                            <span class="detail-value"><?= htmlspecialchars($detail['reg_date'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-icon">
                                            <i class="fa fa-calendar-times"></i>
                                        </div>
                                        <div class="detail-content">
                                            <span class="detail-label">Expiry Date</span>
                                            <span class="detail-value"><?= htmlspecialchars($detail['exp_date'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>
                                    </div>
                                    <div class="detail-item detail-item-highlight">
                                        <div class="detail-icon">
                                            <i class="fa fa-sync-alt"></i>
                                        </div>
                                        <div class="detail-content">
                                            <span class="detail-label">Next Renewal</span>
                                            <span class="detail-value text-primary font-weight-bold"><?= htmlspecialchars($detail['next_due_date'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-icon">
                                            <i class="fa fa-receipt"></i>
                                        </div>
                                        <div class="detail-content">
                                            <span class="detail-label">Registration Amount</span>
                                            <span class="detail-value"><?= htmlspecialchars($detail['currency_code'] ?? '', ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($detail['first_pay_amount'] ?? '0.00', ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>
                                    </div>
                                    <div class="detail-item detail-item-highlight">
                                        <div class="detail-icon">
                                            <i class="fa fa-money-bill-wave"></i>
                                        </div>
                                        <div class="detail-content">
                                            <span class="detail-label">Renewal Amount</span>
                                            <span class="detail-value text-success font-weight-bold"><?= htmlspecialchars($detail['currency_code'] ?? '', ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($detail['recurring_amount'] ?? '0.00', ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-icon">
                                            <i class="fa fa-clock"></i>
                                        </div>
                                        <div class="detail-content">
                                            <span class="detail-label">Billing Cycle</span>
                                            <span class="detail-value"><?= htmlspecialchars($detail['billing_cycle'] ?? 'Monthly', ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Package/Usage Info Card -->
                    <div class="col-lg-6 mg-b-20">
                        <div class="card service-detail-card">
                            <div class="card-header service-detail-header service-detail-header-info d-flex justify-content-between align-items-center">
                                <h5 class="mg-b-0"><i class="fa fa-chart-pie mg-r-10"></i>Package / Usage</h5>
                                <button type="button" class="btn btn-sm btn-light" id="syncCpanelBtn" title="Sync from cPanel" data-service-id="<?= $detail['id'] ?>" data-has-cpanel="<?= !empty($detail['cp_username']) ? '1' : '0' ?>">
                                    <i class="fa fa-sync-alt" id="syncIcon"></i>
                                </button>
                            </div>
                            <div class="card-body">
                                <?php
                                // Set default values from cpanel_stats or use placeholders
                                $disk_used = isset($cpanel_stats['disk_used']) ? $cpanel_stats['disk_used'] : 0;
                                $disk_limit = isset($cpanel_stats['disk_limit']) ? $cpanel_stats['disk_limit'] : 'unlimited';
                                $disk_percent = isset($cpanel_stats['disk_percent']) ? $cpanel_stats['disk_percent'] : 0;

                                $bw_used = isset($cpanel_stats['bandwidth_used']) ? $cpanel_stats['bandwidth_used'] : 0;
                                $bw_limit = isset($cpanel_stats['bandwidth_limit']) ? $cpanel_stats['bandwidth_limit'] : 'unlimited';
                                $bw_percent = isset($cpanel_stats['bandwidth_percent']) ? $cpanel_stats['bandwidth_percent'] : 0;

                                $email_count = isset($cpanel_stats['email_accounts']) ? $cpanel_stats['email_accounts'] : 0;
                                $email_limit = isset($cpanel_stats['email_limit']) ? $cpanel_stats['email_limit'] : 'unlimited';
                                $email_percent = isset($cpanel_stats['email_percent']) ? $cpanel_stats['email_percent'] : 0;

                                $db_count = isset($cpanel_stats['databases']) ? $cpanel_stats['databases'] : 0;
                                $db_limit = isset($cpanel_stats['database_limit']) ? $cpanel_stats['database_limit'] : 'unlimited';
                                $db_percent = isset($cpanel_stats['database_percent']) ? $cpanel_stats['database_percent'] : 0;

                                $addon_count = isset($cpanel_stats['addon_domains']) ? $cpanel_stats['addon_domains'] : 0;
                                $addon_limit = isset($cpanel_stats['addon_limit']) ? $cpanel_stats['addon_limit'] : 'unlimited';
                                $addon_percent = isset($cpanel_stats['addon_percent']) ? $cpanel_stats['addon_percent'] : 0;

                                $last_sync = isset($cpanel_stats['last_sync']) ? $cpanel_stats['last_sync'] : null;

                                // Format display values
                                $disk_display = number_format($disk_used, 1) . ' MB / ' . ($disk_limit === 'unlimited' ? 'Unlimited' : number_format($disk_limit, 0) . ' MB');
                                $bw_display = number_format($bw_used, 1) . ' MB / ' . ($bw_limit === 'unlimited' ? 'Unlimited' : number_format($bw_limit, 0) . ' MB');
                                $email_display = $email_count . ' / ' . ($email_limit === 'unlimited' ? 'Unlimited' : $email_limit);
                                $db_display = $db_count . ' / ' . ($db_limit === 'unlimited' ? 'Unlimited' : $db_limit);
                                $addon_display = $addon_count . ' / ' . ($addon_limit === 'unlimited' ? 'Unlimited' : $addon_limit);
                                ?>

                                <div class="usage-sync-info mg-b-15">
                                    <?php if($last_sync): ?>
                                    <small class="text-muted"><i class="fa fa-clock mg-r-5"></i>Last synced: <?= htmlspecialchars($last_sync) ?></small>
                                    <?php else: ?>
                                    <small class="text-warning"><i class="fa fa-exclamation-triangle mg-r-5"></i>Click sync button to fetch usage data from cPanel</small>
                                    <?php endif; ?>
                                </div>

                                <div class="usage-list" id="usageList">
                                    <!-- Disk Space Usage -->
                                    <div class="usage-item">
                                        <div class="usage-header">
                                            <span class="usage-label"><i class="fa fa-hdd mg-r-5"></i>Disk Space</span>
                                            <span class="usage-value" id="diskUsage"><?= htmlspecialchars($disk_display) ?></span>
                                        </div>
                                        <div class="progress usage-progress">
                                            <div class="progress-bar bg-success" role="progressbar" id="diskProgress" style="width: <?= $disk_percent ?>%"></div>
                                        </div>
                                    </div>
                                    <!-- Bandwidth Usage -->
                                    <div class="usage-item">
                                        <div class="usage-header">
                                            <span class="usage-label"><i class="fa fa-tachometer-alt mg-r-5"></i>Bandwidth</span>
                                            <span class="usage-value" id="bwUsage"><?= htmlspecialchars($bw_display) ?></span>
                                        </div>
                                        <div class="progress usage-progress">
                                            <div class="progress-bar bg-info" role="progressbar" id="bwProgress" style="width: <?= $bw_percent ?>%"></div>
                                        </div>
                                    </div>
                                    <!-- Email Accounts -->
                                    <div class="usage-item">
                                        <div class="usage-header">
                                            <span class="usage-label"><i class="fa fa-envelope mg-r-5"></i>Email Accounts</span>
                                            <span class="usage-value" id="emailUsage"><?= htmlspecialchars($email_display) ?></span>
                                        </div>
                                        <div class="progress usage-progress">
                                            <div class="progress-bar bg-primary" role="progressbar" id="emailProgress" style="width: <?= $email_percent ?>%"></div>
                                        </div>
                                    </div>
                                    <!-- Databases -->
                                    <div class="usage-item">
                                        <div class="usage-header">
                                            <span class="usage-label"><i class="fa fa-database mg-r-5"></i>Databases</span>
                                            <span class="usage-value" id="dbUsage"><?= htmlspecialchars($db_display) ?></span>
                                        </div>
                                        <div class="progress usage-progress">
                                            <div class="progress-bar bg-warning" role="progressbar" id="dbProgress" style="width: <?= $db_percent ?>%"></div>
                                        </div>
                                    </div>
                                    <!-- Addon Domains -->
                                    <div class="usage-item">
                                        <div class="usage-header">
                                            <span class="usage-label"><i class="fa fa-sitemap mg-r-5"></i>Addon Domains</span>
                                            <span class="usage-value" id="addonUsage"><?= htmlspecialchars($addon_display) ?></span>
                                        </div>
                                        <div class="progress usage-progress">
                                            <div class="progress-bar bg-purple" role="progressbar" id="addonProgress" style="width: <?= $addon_percent ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description Card -->
                <?php if(!empty($detail['description'])): ?>
                <div class="card service-detail-card mg-b-20">
                    <div class="card-header service-detail-header service-detail-header-secondary">
                        <h5 class="mg-b-0"><i class="fa fa-file-alt mg-r-10"></i>Product Description</h5>
                    </div>
                    <div class="card-body">
                        <div class="product-description">
                            <?= $detail['description'] ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Instructions Card -->
                <?php if(!empty($detail['instructions'])): ?>
                <div class="card service-detail-card mg-b-20">
                    <div class="card-header service-detail-header service-detail-header-warning">
                        <h5 class="mg-b-0"><i class="fa fa-exclamation-circle mg-r-10"></i>Instructions</h5>
                    </div>
                    <div class="card-body">
                        <div class="service-instructions">
                            <?= $detail['instructions'] ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php $this->load->view('templates/customer/footer_script');?>

<script>
$(document).ready(function() {
    $('#syncCpanelBtn').on('click', function() {
        var btn = $(this);
        var icon = $('#syncIcon');
        var hasCpanel = btn.data('has-cpanel');

        // Check if cPanel is configured
        if (hasCpanel != '1') {
            showToast('warning', 'cPanel username is not configured for this service. Please contact support.');
            return;
        }

        // Disable button and show spinner
        btn.prop('disabled', true);
        icon.removeClass('fa-sync-alt').addClass('fa-spinner fa-spin');

        $.ajax({
            url: '<?= base_url() ?>clientarea/sync_cpanel_usage',
            type: 'POST',
            data: {
                service_id: btn.data('service-id'),
                <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.stats) {
                    var stats = response.stats;

                    // Update disk usage
                    var diskDisplay = formatNumber(stats.disk_used, 1) + ' MB / ' + formatLimit(stats.disk_limit, 'MB');
                    $('#diskUsage').text(diskDisplay);
                    $('#diskProgress').css('width', stats.disk_percent + '%');

                    // Update bandwidth
                    var bwDisplay = formatNumber(stats.bandwidth_used, 1) + ' MB / ' + formatLimit(stats.bandwidth_limit, 'MB');
                    $('#bwUsage').text(bwDisplay);
                    $('#bwProgress').css('width', stats.bandwidth_percent + '%');

                    // Update email accounts
                    var emailDisplay = stats.email_accounts + ' / ' + formatLimit(stats.email_limit);
                    $('#emailUsage').text(emailDisplay);
                    $('#emailProgress').css('width', stats.email_percent + '%');

                    // Update databases
                    var dbDisplay = stats.databases + ' / ' + formatLimit(stats.database_limit);
                    $('#dbUsage').text(dbDisplay);
                    $('#dbProgress').css('width', stats.database_percent + '%');

                    // Update addon domains
                    var addonDisplay = stats.addon_domains + ' / ' + formatLimit(stats.addon_limit);
                    $('#addonUsage').text(addonDisplay);
                    $('#addonProgress').css('width', stats.addon_percent + '%');

                    // Update last sync time
                    $('.usage-sync-info').html('<small class="text-muted"><i class="fa fa-clock mg-r-5"></i>Last synced: ' + stats.last_sync + '</small>');

                    showToast('success', 'Usage stats synced successfully');
                } else {
                    showToast('error', response.msg || 'Failed to sync usage stats');
                }
            },
            error: function(xhr, status, error) {
                showToast('error', 'Failed to connect to server');
            },
            complete: function() {
                // Re-enable button and restore icon
                btn.prop('disabled', false);
                icon.removeClass('fa-spinner fa-spin').addClass('fa-sync-alt');
            }
        });
    });

    function formatNumber(num, decimals) {
        return parseFloat(num).toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    function formatLimit(limit, unit) {
        if (limit === 'unlimited' || limit === 0 || limit === '0') {
            return 'Unlimited';
        }
        return limit + (unit ? ' ' + unit : '');
    }

    function showToast(type, message) {
        // Use toastr if available, otherwise alert
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
        } else if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: type === 'error' ? 'error' : (type === 'warning' ? 'warning' : 'success'),
                title: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        } else {
            alert(message);
        }
    }
});
</script>

<?php $this->load->view('templates/customer/footer');?>
