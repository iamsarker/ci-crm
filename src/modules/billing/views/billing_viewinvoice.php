<?php $this->load->view('templates/customer/header'); ?>
<div class="content content-fixed content-wrapper">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <div class="row">
            <div class="col-md-3 col-sm-12">
                <!-- Invoice Summary Card -->
                <div class="card detail-sidebar-card">
                    <div class="card-header detail-sidebar-header billing-header">
                        <h6 class="card-title mg-b-0"><i class="fa fa-chart-pie mg-r-5"></i>Invoice Summary</h6>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item summary-item">
                            <span class="summary-label"><i class="fa fa-file-invoice"></i> Total Invoices</span>
                            <span class="badge rounded-pill bg-info"><?=($summary['paid']+$summary['due']+$summary['partialy'])?></span>
                        </li>
                        <li class="list-group-item summary-item">
                            <span class="summary-label"><i class="fa fa-check-circle text-success"></i> Paid</span>
                            <span class="badge rounded-pill bg-success"><?=$summary['paid']?></span>
                        </li>
                        <li class="list-group-item summary-item">
                            <span class="summary-label"><i class="fa fa-exclamation-circle text-danger"></i> Due</span>
                            <span class="badge rounded-pill bg-danger"><?=$summary['due']?></span>
                        </li>
                        <li class="list-group-item summary-item">
                            <span class="summary-label"><i class="fa fa-clock text-warning"></i> Partial</span>
                            <span class="badge rounded-pill bg-warning"><?=$summary['partialy']?></span>
                        </li>
                    </ul>
                </div>

                <?php $this->load->view('templates/customer/invoice_nav');?>
            </div>

            <div class="col-md-9 col-sm-12">
                <!-- Page Header -->
                <div class="page-header-card page-header-billing">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h3><i class="fa fa-file-invoice mg-r-10"></i>Invoice #<?= htmlspecialchars($invoice['invoice_no'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb breadcrumb-style1 mg-b-0">
                                    <li class="breadcrumb-item"><a href="<?= base_url() ?>clientarea">Portal home</a></li>
                                    <li class="breadcrumb-item"><a href="<?= base_url() ?>billing/invoices">Invoices</a></li>
                                    <li class="breadcrumb-item active"><a>View Invoice</a></li>
                                </ol>
                            </nav>
                        </div>
                        <div class="header-actions mt-2 mt-md-0">
                            <a href="<?= base_url() ?>billing/download_invoice/<?= htmlspecialchars($invoice['invoice_uuid'], ENT_QUOTES, 'UTF-8') ?>" class="btn btn-light btn-sm">
                                <i class="fa fa-file-pdf text-danger mg-r-5"></i>Download PDF
                            </a>
                            <?php if(strtoupper($invoice['pay_status'] ?? '') != 'PAID'): ?>
                            <a href="<?= base_url() ?>billing/pay_invoice/<?= htmlspecialchars($invoice['invoice_uuid'], ENT_QUOTES, 'UTF-8') ?>" class="btn btn-success btn-sm">
                                <i class="fa fa-credit-card mg-r-5"></i>Pay Now
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Invoice Content -->
                <div class="row">
                    <div class="col-12 p-4 pdfViewContainer">
                        <div class="pdfViewBox">
                            <?php echo $htmlData;?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('templates/customer/footer_script'); ?>
<?php $this->load->view('templates/customer/footer'); ?>
