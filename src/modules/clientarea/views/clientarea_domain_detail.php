<?php $this->load->view('templates/customer/header');?>

<div class="content content-fixed content-wrapper">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <div class="row">
            <div class="col-md-3 col-sm-12">
                <div class="card detail-sidebar-card">
                    <div class="card-header detail-sidebar-header">
                        <h6 class="card-title mg-b-0"><i class="fa fa-server mg-r-5"></i>Current Nameservers</h6>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item ns-item">
                            <span class="ns-label">NS1</span>
                            <span class="ns-value"><?= htmlspecialchars($detail['ns1'] ?? '-', ENT_QUOTES, 'UTF-8')?></span>
                        </li>
                        <li class="list-group-item ns-item">
                            <span class="ns-label">NS2</span>
                            <span class="ns-value"><?= htmlspecialchars($detail['ns2'] ?? '-', ENT_QUOTES, 'UTF-8')?></span>
                        </li>
                        <li class="list-group-item ns-item">
                            <span class="ns-label">NS3</span>
                            <span class="ns-value"><?= htmlspecialchars($detail['ns3'] ?? '-', ENT_QUOTES, 'UTF-8')?></span>
                        </li>
                        <li class="list-group-item ns-item">
                            <span class="ns-label">NS4</span>
                            <span class="ns-value"><?= htmlspecialchars($detail['ns4'] ?? '-', ENT_QUOTES, 'UTF-8')?></span>
                        </li>
                    </ul>
                </div>

                <?php $this->load->view('templates/customer/domain_nav');?>
            </div>

            <div class="col-md-9 col-sm-12">
                <div class="page-header-card page-header-domains">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h3><i class="fa fa-globe mg-r-10"></i><?= htmlspecialchars($detail['domain'] ?? 'Domain Detail', ENT_QUOTES, 'UTF-8')?></h3>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb breadcrumb-style1 mg-b-0">
                                    <li class="breadcrumb-item"><a href="<?=base_url()?>clientarea">Portal home</a></li>
                                    <li class="breadcrumb-item"><a href="<?=base_url()?>clientarea/domains">My Domains</a></li>
                                    <li class="breadcrumb-item active"><a>Domain Detail</a></li>
                                </ol>
                            </nav>
                        </div>
                        <div class="header-status mt-2 mt-md-0">
                            <?= isset($detail['status']) ? getDomainStatus($detail['status']) : ''?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6 col-md-12 mb-4">
                        <div class="card detail-card h-100">
                            <div class="card-header detail-card-header">
                                <h5 class="mg-b-0"><i class="fa fa-info-circle mg-r-10"></i>Order Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="detail-info-list">
                                    <div class="detail-info-item">
                                        <div class="detail-info-label">
                                            <i class="fa fa-globe"></i>Domain Name
                                        </div>
                                        <div class="detail-info-value domain-highlight">
                                            <?= htmlspecialchars($detail['domain'] ?? '', ENT_QUOTES, 'UTF-8')?>
                                        </div>
                                    </div>
                                    <div class="detail-info-item">
                                        <div class="detail-info-label">
                                            <i class="fa fa-calendar-plus"></i>Registration Date
                                        </div>
                                        <div class="detail-info-value">
                                            <?= !empty($detail['reg_date']) ? date('M d, Y', strtotime($detail['reg_date'])) : '-' ?>
                                        </div>
                                    </div>
                                    <div class="detail-info-item">
                                        <div class="detail-info-label">
                                            <i class="fa fa-calendar-times"></i>Expiry Date
                                        </div>
                                        <div class="detail-info-value">
                                            <?= !empty($detail['exp_date']) ? date('M d, Y', strtotime($detail['exp_date'])) : '-' ?>
                                        </div>
                                    </div>
                                    <div class="detail-info-item highlight-row">
                                        <div class="detail-info-label">
                                            <i class="fa fa-redo"></i>Next Renewal
                                        </div>
                                        <div class="detail-info-value text-primary fw-bold">
                                            <?= !empty($detail['next_renewal_date']) ? date('M d, Y', strtotime($detail['next_renewal_date'])) : '-' ?>
                                        </div>
                                    </div>
                                    <div class="detail-info-item">
                                        <div class="detail-info-label">
                                            <i class="fa fa-receipt"></i>Registration Amount
                                        </div>
                                        <div class="detail-info-value">
                                            <?= htmlspecialchars($detail['currency_code'] ?? '', ENT_QUOTES, 'UTF-8')?> <?= htmlspecialchars($detail['first_pay_amount'] ?? '0.00', ENT_QUOTES, 'UTF-8')?>
                                        </div>
                                    </div>
                                    <div class="detail-info-item highlight-row">
                                        <div class="detail-info-label">
                                            <i class="fa fa-sync-alt"></i>Renewal Amount
                                        </div>
                                        <div class="detail-info-value text-success fw-bold">
                                            <?= htmlspecialchars($detail['currency_code'] ?? '', ENT_QUOTES, 'UTF-8')?> <?= htmlspecialchars($detail['recurring_amount'] ?? '0.00', ENT_QUOTES, 'UTF-8')?>
                                        </div>
                                    </div>
                                </div>

                                <br /><br />

                                <div class="sync-section mt-4">
                                    <button type="button" id="btnSyncDomain" class="btn btn-outline-primary btn-sync">
                                        <i class="fa fa-sync-alt mg-r-5"></i>Sync from Registrar
                                    </button>
                                    <span class="sync-info text-muted ms-3">
                                        <?php if(!empty($detail['last_contact_sync'])): ?>
                                            Last synced: <?= date('M d, Y H:i', strtotime($detail['last_contact_sync'])) ?>
                                        <?php else: ?>
                                            Never synced
                                        <?php endif; ?>
                                    </span>
                                    <div id="syncStatus" class="sync-status mt-2" class="sync-status-container"></div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-12 mb-4">
                        <div class="card detail-card mb-4">
                            <div class="card-header detail-card-header">
                                <h5 class="mg-b-0"><i class="fa fa-cog mg-r-10"></i>DNS Configuration</h5>
                            </div>
                            <div class="card-body">
                                <div class="dns-type-selector">
                                    <label class="dns-type-option <?= !empty($detail['dns_type']) && $detail['dns_type'] == 'default_ns' ? 'active' : '' ?>">
                                        <input type="radio" name="dns_type" value="default_ns" id="default_ns" <?= !empty($detail['dns_type']) && $detail['dns_type'] == 'default_ns' ? 'checked' : '' ?> />
                                        <i class="fa fa-server"></i>
                                        <span>Default NS</span>
                                    </label>
                                    <label class="dns-type-option <?= (empty($detail['dns_type']) || $detail['dns_type'] == 'custom_ns' || $detail['dns_type'] == 'records') ? 'active' : '' ?>">
                                        <input type="radio" name="dns_type" value="custom_ns" id="custom_ns" <?= (empty($detail['dns_type']) || $detail['dns_type'] == 'custom_ns' || $detail['dns_type'] == 'records') ? 'checked' : '' ?> />
                                        <i class="fa fa-edit"></i>
                                        <span>Custom NS</span>
                                    </label>
                                </div>
                                <p class="text-muted small mb-0 mt-2">
                                    <i class="fa fa-info-circle mg-r-5"></i>
                                    Choose <strong>Default NS</strong> to point this domain at our nameservers, or <strong>Custom NS</strong> to set your own below.
                                </p>
                            </div>
                        </div>

                        <div class="card detail-card">
                            <div class="card-header detail-card-header d-flex justify-content-between align-items-center">
                                <h5 class="mg-b-0"><i class="fa fa-pen mg-r-10"></i>Update Nameservers</h5>
                            </div>
                            <div class="card-body">
                                <input type="hidden" id="domain_id" value="<?= htmlspecialchars($detail['id'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />

                                <div id="defaultNsNote" class="alert alert-info" style="display:none;">
                                    <i class="fa fa-info-circle mg-r-5"></i>
                                    <strong>Default NS</strong> selected. Clicking below applies the registrar's default nameservers to this domain.
                                </div>

                                <div class="ns-input-group">
                                    <div class="ns-input-item">
                                        <label for="ns1">Nameserver 1 <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fa fa-server"></i></span>
                                            <input name="ns1" placeholder="ns1.example.com" id="ns1" value="<?= htmlspecialchars($detail['ns1'] ?? '', ENT_QUOTES, 'UTF-8') ?>" type="text" class="form-control" required />
                                        </div>
                                    </div>
                                    <div class="ns-input-item">
                                        <label for="ns2">Nameserver 2 <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fa fa-server"></i></span>
                                            <input name="ns2" placeholder="ns2.example.com" id="ns2" value="<?= htmlspecialchars($detail['ns2'] ?? '', ENT_QUOTES, 'UTF-8') ?>" type="text" class="form-control" required />
                                        </div>
                                    </div>
                                    <div class="ns-input-item">
                                        <label for="ns3">Nameserver 3 <span class="text-muted">(Optional)</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fa fa-server"></i></span>
                                            <input name="ns3" placeholder="ns3.example.com" id="ns3" value="<?= htmlspecialchars($detail['ns3'] ?? '', ENT_QUOTES, 'UTF-8') ?>" type="text" class="form-control" />
                                        </div>
                                    </div>
                                    <div class="ns-input-item">
                                        <label for="ns4">Nameserver 4 <span class="text-muted">(Optional)</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fa fa-server"></i></span>
                                            <input name="ns4" placeholder="ns4.example.com" id="ns4" value="<?= htmlspecialchars($detail['ns4'] ?? '', ENT_QUOTES, 'UTF-8') ?>" type="text" class="form-control" />
                                        </div>
                                    </div>
                                </div>

                                <div class="ns-update-actions mt-4">
                                    <button type="button" id="btnUpdateNS" class="btn btn-primary btn-update-ns">
                                        <i class="fa fa-save mg-r-5"></i>Update Nameservers
                                    </button>
                                    <div id="nsUpdateStatus" class="ns-update-status mt-3" class="sync-status-container"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information Section -->
                <div class="row">
                    <div class="col-12">
                        <div class="card detail-card">
                            <div class="card-header detail-card-header">
                                <h5 class="mg-b-0"><i class="fa fa-address-card mg-r-10"></i>Contact Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="contact-input-item">
                                            <label for="contact_name">Full Name <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa fa-user"></i></span>
                                                <input type="text" class="form-control" id="contact_name" placeholder="John Doe"
                                                       value="<?= htmlspecialchars($detail['contact_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="contact-input-item">
                                            <label for="contact_company">Company/Organization</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa fa-building"></i></span>
                                                <input type="text" class="form-control" id="contact_company" placeholder="Company Name"
                                                       value="<?= htmlspecialchars($detail['contact_company'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="contact-input-item">
                                            <label for="contact_email">Email Address <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                                                <input type="email" class="form-control" id="contact_email" placeholder="email@example.com"
                                                       value="<?= htmlspecialchars($detail['contact_email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="contact-input-item">
                                            <label for="contact_phone">Phone Number</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa fa-phone"></i></span>
                                                <input type="text" class="form-control" id="contact_phone" placeholder="+1.1234567890"
                                                       value="<?= htmlspecialchars($detail['contact_phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="contact-input-item">
                                            <label for="contact_address1">Address Line 1</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa fa-map-marker-alt"></i></span>
                                                <input type="text" class="form-control" id="contact_address1" placeholder="Street Address"
                                                       value="<?= htmlspecialchars($detail['contact_address1'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="contact-input-item">
                                            <label for="contact_address2">Address Line 2</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa fa-map-marker-alt"></i></span>
                                                <input type="text" class="form-control" id="contact_address2" placeholder="Apt, Suite, etc."
                                                       value="<?= htmlspecialchars($detail['contact_address2'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="contact-input-item">
                                            <label for="contact_city">City</label>
                                            <input type="text" class="form-control" id="contact_city" placeholder="City"
                                                   value="<?= htmlspecialchars($detail['contact_city'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="contact-input-item">
                                            <label for="contact_state">State/Province</label>
                                            <input type="text" class="form-control" id="contact_state" placeholder="State"
                                                   value="<?= htmlspecialchars($detail['contact_state'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="contact-input-item">
                                            <label for="contact_zip">ZIP/Postal Code</label>
                                            <input type="text" class="form-control" id="contact_zip" placeholder="12345"
                                                   value="<?= htmlspecialchars($detail['contact_zip'] ?? '', ENT_QUOTES, 'UTF-8') ?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="contact-input-item">
                                            <label for="contact_country">Country</label>
                                            <select class="form-select" id="contact_country">
                                                <option value="">Select Country</option>
                                                <?php foreach($countries as $country): ?>
                                                <option value="<?= htmlspecialchars($country['country_code'], ENT_QUOTES, 'UTF-8') ?>"
                                                    <?= ($detail['contact_country'] ?? '') == $country['country_code'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($country['country_name'], ENT_QUOTES, 'UTF-8') ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="contact-update-actions mt-4">
                                    <button type="button" id="btnUpdateContacts" class="btn btn-primary btn-update-ns">
                                        <i class="fa fa-save mg-r-5"></i>Update Contact Information
                                    </button>
                                    <div id="contactUpdateStatus" class="ns-update-status mt-3" class="sync-status-container"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Private (Child) Nameservers Section -->
                <div class="row">
                    <div class="col-12">
                        <div class="card detail-card">
                            <div class="card-header detail-card-header d-flex justify-content-between align-items-center">
                                <h5 class="mg-b-0"><i class="fa fa-sitemap mg-r-10"></i>Private Nameservers (Child NS)</h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">
                                    Create your own nameservers under this domain (glue records), e.g.
                                    <code>ns1.<?= htmlspecialchars($detail['domain'] ?? 'yourdomain.com', ENT_QUOTES, 'UTF-8') ?></code>.
                                    These are registered at the registrar and can then be used as nameservers.
                                </p>

                                <div class="table-responsive">
                                    <table class="table table-sm align-middle" id="childNsTable">
                                        <thead>
                                            <tr>
                                                <th>Nameserver Host</th>
                                                <th>IP Address</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php if (!empty($child_ns)): ?>
                                            <?php foreach ($child_ns as $cns): ?>
                                            <tr data-child-id="<?= (int)$cns['id'] ?>">
                                                <td><?= htmlspecialchars($cns['hostname'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($cns['ip'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-child-ns" data-child-id="<?= (int)$cns['id'] ?>" data-host="<?= htmlspecialchars($cns['hostname'], ENT_QUOTES, 'UTF-8') ?>">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                            <tr id="childNsEmptyRow" style="<?= !empty($child_ns) ? 'display:none;' : '' ?>">
                                                <td colspan="3" class="text-center text-muted py-3">No private nameservers yet.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <hr>

                                <div class="row g-2 align-items-end">
                                    <div class="col-md-5">
                                        <label for="child_ns_host" class="form-label small mb-1">Nameserver Host</label>
                                        <input type="text" class="form-control" id="child_ns_host" placeholder="ns1.<?= htmlspecialchars($detail['domain'] ?? 'yourdomain.com', ENT_QUOTES, 'UTF-8') ?>" />
                                    </div>
                                    <div class="col-md-4">
                                        <label for="child_ns_ip" class="form-label small mb-1">IP Address</label>
                                        <input type="text" class="form-control" id="child_ns_ip" placeholder="192.0.2.1" />
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" id="btnAddChildNs" class="btn btn-primary w-100">
                                            <i class="fa fa-plus mg-r-5"></i>Add
                                        </button>
                                    </div>
                                </div>
                                <div id="childNsStatus" class="ns-update-status mt-3"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('templates/customer/footer_script');?>
<script>
$(function(){
    'use strict';

    // DNS type selector interaction
    function applyDnsTypeState() {
        var dnsType = $('input[name="dns_type"]:checked').val() || 'custom_ns';
        var isDefault = (dnsType === 'default_ns');
        // With Default NS, the registrar's default nameservers are applied
        // server-side, so the custom inputs are locked to avoid confusion.
        $('#ns1, #ns2, #ns3, #ns4').prop('disabled', isDefault);
        $('#defaultNsNote').toggle(isDefault);
        $('#btnUpdateNS').html(isDefault
            ? '<i class="fa fa-save mg-r-5"></i>Apply Default Nameservers'
            : '<i class="fa fa-save mg-r-5"></i>Update Nameservers');
    }

    $('.dns-type-option input[type="radio"]').on('change', function() {
        $('.dns-type-option').removeClass('active');
        $(this).closest('.dns-type-option').addClass('active');
        applyDnsTypeState();
    });
    applyDnsTypeState();

    // Update Nameservers button click
    $('#btnUpdateNS').on('click', function() {
        var $btn = $(this);
        var $status = $('#nsUpdateStatus');

        // Get values
        var domainId = $('#domain_id').val();
        var ns1 = $('#ns1').val().trim();
        var ns2 = $('#ns2').val().trim();
        var ns3 = $('#ns3').val().trim();
        var ns4 = $('#ns4').val().trim();
        var dnsType = $('input[name="dns_type"]:checked').val() || 'custom_ns';

        // Custom NS requires valid nameserver input; Default NS is filled server-side.
        if (dnsType !== 'default_ns') {
            // Basic validation
            if (!ns1 || !ns2) {
                showStatus('error', 'Nameserver 1 and Nameserver 2 are required.');
                return;
            }

            // Nameserver format validation
            var nsPattern = /^[a-zA-Z0-9][a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}$/;
            if (!nsPattern.test(ns1) || !nsPattern.test(ns2)) {
                showStatus('error', 'Please enter valid nameserver addresses (e.g., ns1.example.com)');
                return;
            }

            if (ns3 && !nsPattern.test(ns3)) {
                showStatus('error', 'Nameserver 3 format is invalid.');
                return;
            }

            if (ns4 && !nsPattern.test(ns4)) {
                showStatus('error', 'Nameserver 4 format is invalid.');
                return;
            }
        }

        // Disable button and show loading
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mg-r-5"></i>Updating...');
        $status.hide();

        // Get CSRF token
        var csrfName = $('meta[name="csrf-token-name"]').attr('content');
        var csrfToken = $('meta[name="csrf-token-hash"]').attr('content');

        var postData = {
            domain_id: domainId,
            ns1: ns1,
            ns2: ns2,
            ns3: ns3,
            ns4: ns4,
            dns_type: dnsType
        };
        postData[csrfName] = csrfToken;

        $.ajax({
            url: BASE_URL + 'clientarea/update_nameservers',
            type: 'POST',
            data: postData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showStatus('success', response.msg);
                    if (dnsType === 'default_ns') {
                        // Registrar defaults were applied server-side; reload to show them
                        setTimeout(function() { location.reload(); }, 1200);
                    } else {
                        // Update sidebar nameservers display
                        updateSidebarNS(ns1, ns2, ns3, ns4);
                    }
                } else {
                    showStatus('error', response.msg || 'Failed to update nameservers.');
                }
            },
            error: function(xhr, status, error) {
                showStatus('error', 'An error occurred. Please try again.');
                console.error('AJAX Error:', error);
            },
            complete: function() {
                $btn.prop('disabled', false);
                applyDnsTypeState();
            }
        });
    });

    function showStatus(type, message) {
        var $status = $('#nsUpdateStatus');
        var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

        $status.removeClass('alert-success alert-danger')
               .addClass('alert ' + alertClass)
               .html('<i class="fa ' + icon + ' mg-r-5"></i>' + escapeXSS(message))
               .fadeIn();

        // Auto-hide success messages after 5 seconds
        if (type === 'success') {
            setTimeout(function() {
                $status.fadeOut();
            }, 5000);
        }
    }

    function updateSidebarNS(ns1, ns2, ns3, ns4) {
        var $nsItems = $('.detail-sidebar-card .ns-item .ns-value');
        $nsItems.eq(0).text(ns1 || '-');
        $nsItems.eq(1).text(ns2 || '-');
        $nsItems.eq(2).text(ns3 || '-');
        $nsItems.eq(3).text(ns4 || '-');
    }

    // Sync Domain Data button
    $('#btnSyncDomain').on('click', function() {
        var $btn = $(this);
        var $status = $('#syncStatus');
        var domainId = $('#domain_id').val();

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mg-r-5"></i>Syncing...');
        $status.hide();

        var csrfName = $('meta[name="csrf-token-name"]').attr('content');
        var csrfToken = $('meta[name="csrf-token-hash"]').attr('content');

        var postData = { domain_id: domainId };
        postData[csrfName] = csrfToken;

        $.ajax({
            url: BASE_URL + 'clientarea/sync_domain_data',
            type: 'POST',
            data: postData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showSyncStatus('success', response.msg);
                    // Update form fields with synced data
                    if (response.data) {
                        var d = response.data;
                        // Nameservers
                        if (d.ns1) $('#ns1').val(d.ns1);
                        if (d.ns2) $('#ns2').val(d.ns2);
                        if (d.ns3) $('#ns3').val(d.ns3);
                        if (d.ns4) $('#ns4').val(d.ns4);
                        updateSidebarNS(d.ns1 || '', d.ns2 || '', d.ns3 || '', d.ns4 || '');
                        // Contacts
                        if (d.contact_name) $('#contact_name').val(d.contact_name);
                        if (d.contact_company) $('#contact_company').val(d.contact_company);
                        if (d.contact_email) $('#contact_email').val(d.contact_email);
                        if (d.contact_phone) $('#contact_phone').val(d.contact_phone);
                        if (d.contact_address1) $('#contact_address1').val(d.contact_address1);
                        if (d.contact_address2) $('#contact_address2').val(d.contact_address2);
                        if (d.contact_city) $('#contact_city').val(d.contact_city);
                        if (d.contact_state) $('#contact_state').val(d.contact_state);
                        if (d.contact_zip) $('#contact_zip').val(d.contact_zip);
                        if (d.contact_country) $('#contact_country').val(d.contact_country);
                    }
                    // Update sync time
                    $('.sync-info').text('Last synced: Just now');
                } else {
                    showSyncStatus('error', response.msg || 'Sync failed.');
                }
            },
            error: function(xhr, status, error) {
                var errMsg = 'An error occurred during sync.';
                try {
                    var resp = JSON.parse(xhr.responseText);
                    if (resp.msg) errMsg = resp.msg;
                } catch(e) {
                    if (xhr.responseText) errMsg = xhr.responseText.substring(0, 200);
                }
                showSyncStatus('error', errMsg);
                console.error('Sync Error:', xhr.responseText);
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fa fa-sync-alt mg-r-5"></i>Sync from Registrar');
            }
        });
    });

    function showSyncStatus(type, message) {
        var $status = $('#syncStatus');
        var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

        $status.removeClass('alert-success alert-danger')
               .addClass('alert ' + alertClass)
               .html('<i class="fa ' + icon + ' mg-r-5"></i>' + escapeXSS(message))
               .fadeIn();

        if (type === 'success') {
            setTimeout(function() { $status.fadeOut(); }, 5000);
        }
    }

    // Update Contacts button
    $('#btnUpdateContacts').on('click', function() {
        var $btn = $(this);
        var $status = $('#contactUpdateStatus');
        var domainId = $('#domain_id').val();

        var contactName = $('#contact_name').val().trim();
        var contactEmail = $('#contact_email').val().trim();

        // Validation
        if (!contactName || !contactEmail) {
            showContactStatus('error', 'Name and Email are required.');
            return;
        }

        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(contactEmail)) {
            showContactStatus('error', 'Please enter a valid email address.');
            return;
        }

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mg-r-5"></i>Updating...');
        $status.hide();

        var csrfName = $('meta[name="csrf-token-name"]').attr('content');
        var csrfToken = $('meta[name="csrf-token-hash"]').attr('content');

        var postData = {
            domain_id: domainId,
            contact_name: contactName,
            contact_company: $('#contact_company').val().trim(),
            contact_email: contactEmail,
            contact_phone: $('#contact_phone').val().trim(),
            contact_address1: $('#contact_address1').val().trim(),
            contact_address2: $('#contact_address2').val().trim(),
            contact_city: $('#contact_city').val().trim(),
            contact_state: $('#contact_state').val().trim(),
            contact_zip: $('#contact_zip').val().trim(),
            contact_country: $('#contact_country').val().trim()
        };
        postData[csrfName] = csrfToken;

        $.ajax({
            url: BASE_URL + 'clientarea/update_contacts',
            type: 'POST',
            data: postData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showContactStatus('success', response.msg);
                } else {
                    showContactStatus('error', response.msg || 'Failed to update contacts.');
                }
            },
            error: function() {
                showContactStatus('error', 'An error occurred. Please try again.');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fa fa-save mg-r-5"></i>Update Contact Information');
            }
        });
    });

    function showContactStatus(type, message) {
        var $status = $('#contactUpdateStatus');
        var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

        $status.removeClass('alert-success alert-danger')
               .addClass('alert ' + alertClass)
               .html('<i class="fa ' + icon + ' mg-r-5"></i>' + escapeXSS(message))
               .fadeIn();

        if (type === 'success') {
            setTimeout(function() { $status.fadeOut(); }, 5000);
        }
    }

    // Fetch live transfer lock status after page load (fallback to DB value on failure)
    (function() {
        var domainId = $('#domain_id').val();
        if (!domainId) return;

        var $toggle = $('#transferLockToggle');

        var csrfName = $('meta[name="csrf-token-name"]').attr('content');
        var csrfToken = $('meta[name="csrf-token-hash"]').attr('content');
        var postData = { domain_id: domainId };
        postData[csrfName] = csrfToken;

        $.ajax({
            url: BASE_URL + 'clientarea/get_transfer_lock',
            type: 'POST',
            data: postData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $toggle.prop('checked', response.locked == 1);
                }
                // On failure, keep the DB value already set in the checkbox
            }
        });
    })();

    // Transfer Lock toggle change
    $('#transferLockToggle').on('change', function() {
        var $toggle = $(this);
        var isChecked = $toggle.is(':checked');
        var action = isChecked ? 'lock' : 'unlock';
        var domainId = $('#domain_id').val();
        var actionLabel = isChecked ? 'Enable' : 'Disable';

        Swal.fire({
            title: actionLabel + ' Transfer Lock?',
            text: isChecked
                ? 'This prevents unauthorized domain transfers.'
                : 'This allows the domain to be transferred to another registrar.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0168fa',
            confirmButtonText: 'Yes, ' + actionLabel,
            cancelButtonText: 'Cancel'
        }).then(function(result) {
            if (!result.isConfirmed) {
                $toggle.prop('checked', !isChecked);
                return;
            }

            Swal.fire({
                title: 'Please wait...',
                text: (isChecked ? 'Enabling' : 'Disabling') + ' transfer lock at registrar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: function() { Swal.showLoading(); }
            });

            var csrfName = $('meta[name="csrf-token-name"]').attr('content');
            var csrfToken = $('meta[name="csrf-token-hash"]').attr('content');
            var postData = { domain_id: domainId, action: action };
            postData[csrfName] = csrfToken;

            $.ajax({
                url: BASE_URL + 'clientarea/toggle_transfer_lock',
                type: 'POST',
                data: postData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.msg,
                            confirmButtonColor: '#0168fa'
                        }).then(function() { location.reload(); });
                    } else {
                        $toggle.prop('checked', !isChecked);
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed',
                            text: response.msg || 'Failed to update transfer lock.',
                            confirmButtonColor: '#0168fa'
                        }).then(function() { location.reload(); });
                    }
                },
                error: function() {
                    $toggle.prop('checked', !isChecked);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred. Please try again.',
                        confirmButtonColor: '#0168fa'
                    }).then(function() { location.reload(); });
                }
            });
        });
    });

    // Send EPP Code button
    $('#btnSendEppCode').on('click', function(e) {
        e.preventDefault();
        var domainId = $('#domain_id').val();

        if (!domainId) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Domain ID not found', confirmButtonColor: '#0168fa' });
            return;
        }

        Swal.fire({
            title: 'Send EPP Code?',
            text: 'The EPP/Authorization code will be sent to your registered email address.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0168fa',
            confirmButtonText: 'Yes, Send',
            cancelButtonText: 'Cancel'
        }).then(function(result) {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Please wait...',
                text: 'Fetching EPP code from registrar and sending to your email',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: function() { Swal.showLoading(); }
            });

            var csrfName = $('meta[name="csrf-token-name"]').attr('content');
            var csrfToken = $('meta[name="csrf-token-hash"]').attr('content');
            var postData = { domain_id: domainId };
            postData[csrfName] = csrfToken;

            $.ajax({
                url: BASE_URL + 'clientarea/send_epp_code',
                type: 'POST',
                data: postData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'EPP Code Sent',
                            text: response.msg,
                            confirmButtonColor: '#0168fa'
                        }).then(function() { location.reload(); });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed',
                            text: response.msg || 'Failed to send EPP code.',
                            confirmButtonColor: '#0168fa'
                        }).then(function() { location.reload(); });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred. Please try again.',
                        confirmButtonColor: '#0168fa'
                    }).then(function() { location.reload(); });
                }
            });
        });
    });
    // ---- Private (child) nameservers ----
    function childNsStatus(type, message) {
        var $s = $('#childNsStatus');
        var cls = type === 'success' ? 'alert-success' : 'alert-danger';
        var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        $s.removeClass('alert-success alert-danger').addClass('alert ' + cls)
          .html('<i class="fa ' + icon + ' mg-r-5"></i>' + escapeXSS(message)).fadeIn();
        if (type === 'success') setTimeout(function(){ $s.fadeOut(); }, 5000);
    }

    $('#btnAddChildNs').on('click', function() {
        var $btn = $(this);
        var domainId = $('#domain_id').val();
        var host = ($('#child_ns_host').val() || '').trim().toLowerCase();
        var ip = ($('#child_ns_ip').val() || '').trim();

        if (!host || !ip) {
            childNsStatus('error', 'Nameserver host and IP are required.');
            return;
        }

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mg-r-5"></i>Adding...');

        var csrfName = $('meta[name="csrf-token-name"]').attr('content');
        var csrfToken = $('meta[name="csrf-token-hash"]').attr('content');
        var postData = { domain_id: domainId, hostname: host, ip: ip };
        postData[csrfName] = csrfToken;

        $.ajax({
            url: BASE_URL + 'clientarea/child_ns_add',
            type: 'POST', data: postData, dataType: 'json',
            success: function(response) {
                if (response.success) {
                    childNsStatus('success', response.msg);
                    setTimeout(function(){ location.reload(); }, 1000);
                } else {
                    childNsStatus('error', response.msg || 'Failed to add child nameserver.');
                }
            },
            error: function() { childNsStatus('error', 'An error occurred. Please try again.'); },
            complete: function() { $btn.prop('disabled', false).html('<i class="fa fa-plus mg-r-5"></i>Add'); }
        });
    });

    $('#childNsTable').on('click', '.btn-delete-child-ns', function() {
        var childId = $(this).data('child-id');
        var host = $(this).data('host');
        var domainId = $('#domain_id').val();

        Swal.fire({
            title: 'Delete Nameserver?',
            html: 'Remove <strong>' + escapeXSS(String(host)) + '</strong> from the registrar?',
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#d9534f', confirmButtonText: 'Yes, delete', cancelButtonText: 'Cancel'
        }).then(function(result) {
            if (!result.isConfirmed) return;

            Swal.fire({ title: 'Please wait...', allowOutsideClick: false, allowEscapeKey: false, didOpen: function(){ Swal.showLoading(); } });

            var csrfName = $('meta[name="csrf-token-name"]').attr('content');
            var csrfToken = $('meta[name="csrf-token-hash"]').attr('content');
            var postData = { domain_id: domainId, child_ns_id: childId };
            postData[csrfName] = csrfToken;

            $.ajax({
                url: BASE_URL + 'clientarea/child_ns_delete',
                type: 'POST', data: postData, dataType: 'json',
                success: function(response) {
                    Swal.fire({
                        icon: response.success ? 'success' : 'error',
                        title: response.success ? 'Deleted' : 'Failed',
                        text: response.msg,
                        confirmButtonColor: '#0168fa'
                    }).then(function(){ if (response.success) location.reload(); });
                },
                error: function() {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred. Please try again.', confirmButtonColor: '#0168fa' });
                }
            });
        });
    });

    // Request Cancellation button
    $('#btnRequestCancellation').on('click', function(e) {
        e.preventDefault();
        var domainId = $('#domain_id').val();

        if (!domainId) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Domain ID not found', confirmButtonColor: '#0168fa' });
            return;
        }

        Swal.fire({
            title: 'Request Domain Cancellation?',
            text: 'This submits a cancellation request to our team. Your domain stays active until we process it.',
            input: 'textarea',
            inputLabel: 'Reason (optional)',
            inputPlaceholder: 'Tell us why you want to cancel this domain...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d9534f',
            confirmButtonText: 'Submit Request',
            cancelButtonText: 'Cancel'
        }).then(function(result) {
            if (!result.isConfirmed) return;

            var reason = result.value || '';

            Swal.fire({
                title: 'Submitting...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: function() { Swal.showLoading(); }
            });

            var csrfName = $('meta[name="csrf-token-name"]').attr('content');
            var csrfToken = $('meta[name="csrf-token-hash"]').attr('content');
            var postData = { domain_id: domainId, reason: reason };
            postData[csrfName] = csrfToken;

            $.ajax({
                url: BASE_URL + 'clientarea/domain_cancellation_request',
                type: 'POST',
                data: postData,
                dataType: 'json',
                success: function(response) {
                    Swal.fire({
                        icon: response.success ? 'success' : 'error',
                        title: response.success ? 'Request Submitted' : 'Failed',
                        text: response.msg || (response.success ? 'Your request was submitted.' : 'Failed to submit request.'),
                        confirmButtonColor: '#0168fa'
                    });
                },
                error: function() {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred. Please try again.', confirmButtonColor: '#0168fa' });
                }
            });
        });
    });
});
</script>
<?php $this->load->view('templates/customer/footer');?>
