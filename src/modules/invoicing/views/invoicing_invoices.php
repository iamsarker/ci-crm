<?php $this->load->view('templates/customer/header');?>

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
                            <h3><i class="fa fa-file-invoice-dollar mg-r-10"></i>My Invoices</h3>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb breadcrumb-style1 mg-b-0">
                                    <li class="breadcrumb-item"><a href="<?=base_url()?>clientarea">Portal home</a></li>
                                    <li class="breadcrumb-item active"><a>My Invoices</a></li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Invoices Table Card -->
                <div class="card services-card">
                    <div class="card-body">
                        <?php if(empty($results)): ?>
                        <div class="empty-state">
                            <i class="fa fa-file-invoice"></i>
                            <p>No invoices found</p>
                        </div>
                        <?php else: ?>
                        <table id="example1" class="table services-table invoices-table">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Amount</th>
                                    <th>Invoice Date</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($results as $row): ?>
                                <?php
                                // Every cell below renders HTML (icon + <span>), and DataTables
                                // sorts DOM tables on the cell's innerHTML. Without an explicit
                                // data-order the dates sort alphabetically ("Apr 02, 2026" before
                                // "Jan 05, 2025"), the amounts sort as strings ("900.00" above
                                // "1,234.00") and the status sorts by CSS class. These give each
                                // column a real sort key. Invoice # sorts on id, which is the same
                                // sequence as invoice_no but is an integer -- invoice_no is a
                                // varchar, so "999" would otherwise beat "1000".
                                $payStatus = strtoupper($row['pay_status'] ?? '');
                                ?>
                                <tr>
                                    <td data-order="<?= (int)$row['id'] ?>">
                                        <a href="javascript:void(0);" onclick="viewInvoiceDetail('<?= htmlspecialchars($row['invoice_uuid'], ENT_QUOTES, 'UTF-8') ?>')" class="invoice-link">
                                            <i class="fa fa-file-invoice"></i>
                                            #<?= htmlspecialchars($row['invoice_no'], ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                    </td>
                                    <td data-order="<?= (float)$row['total'] ?>">
                                        <span class="amount-cell">
                                            <strong><?= htmlspecialchars($row['currency_code'], ENT_QUOTES, 'UTF-8') ?></strong>
                                            <?= number_format((float)$row['total'], 2) ?>
                                        </span>
                                    </td>
                                    <td data-order="<?= !empty($row['order_date']) ? (int)strtotime($row['order_date']) : 0 ?>">
                                        <span class="date-cell">
                                            <i class="fa fa-calendar-alt"></i>
                                            <?= !empty($row['order_date']) ? date('M d, Y', strtotime($row['order_date'])) : '-' ?>
                                        </span>
                                    </td>
                                    <td data-order="<?= !empty($row['due_date']) ? (int)strtotime($row['due_date']) : 0 ?>">
                                        <span class="date-cell">
                                            <i class="fa fa-calendar-check"></i>
                                            <?= !empty($row['due_date']) ? date('M d, Y', strtotime($row['due_date'])) : '-' ?>
                                        </span>
                                    </td>
                                    <td data-order="<?= htmlspecialchars($payStatus, ENT_QUOTES, 'UTF-8') ?>">
                                        <?php
                                        $statusClass = 'bg-secondary';
                                        $statusIcon = 'fa-question-circle';

                                        if ($payStatus == 'PAID') {
                                            $statusClass = 'bg-success';
                                            $statusIcon = 'fa-check-circle';
                                        } elseif ($payStatus == 'DUE' || $payStatus == 'UNPAID') {
                                            $statusClass = 'bg-danger';
                                            $statusIcon = 'fa-exclamation-circle';
                                        } elseif ($payStatus == 'PARTIAL' || $payStatus == 'PARTIALY') {
                                            $statusClass = 'bg-warning';
                                            $statusIcon = 'fa-clock';
                                        } elseif ($payStatus == 'CANCELLED') {
                                            $statusClass = 'bg-dark';
                                            $statusIcon = 'fa-ban';
                                        }
                                        ?>
                                        <span class="badge <?= $statusClass ?> status-badge">
                                            <i class="fa <?= $statusIcon ?> mg-r-3"></i>
                                            <?= htmlspecialchars($row['pay_status'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group action-btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewInvoiceDetail('<?= htmlspecialchars($row['invoice_uuid'], ENT_QUOTES, 'UTF-8') ?>')" title="View Invoice">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="downloadInvoiceDetail('<?= htmlspecialchars($row['invoice_uuid'], ENT_QUOTES, 'UTF-8') ?>')" title="Download PDF">
                                                <i class="fa fa-file-pdf"></i>
                                            </button>
                                            <?php if(strtoupper($row['pay_status']) != 'PAID'): ?>
                                            <a href="<?=base_url()?>invoicing/pay/invoice/<?= htmlspecialchars($row['invoice_uuid'], ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-success" title="Pay Now">
                                                <i class="fa fa-credit-card"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
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

    $('#example1').DataTable({
        // Keep the server's order (invoices.id DESC = newest invoice first),
        // same as My Services / My Domains. NOTE: "order" cannot be used
        // alongside "aaSorting" -- they are the same option, and DataTables
        // keeps the Hungarian name when both are supplied, so an "order" here
        // would be silently ignored.
        "aaSorting": [],
        language: {
            searchPlaceholder: 'Search invoices...',
            sSearch: '',
            lengthMenu: '_MENU_ items/page',
        },
        "columnDefs": [
            { "orderable": false, "targets": 5 }
        ]
    });
});

function viewInvoiceDetail(invoice_uuid) {
    window.location = "<?=base_url()?>invoicing/view_invoice/" + invoice_uuid;
}

function downloadInvoiceDetail(invoice_uuid) {
    window.location = "<?=base_url()?>invoicing/download_invoice/" + invoice_uuid;
}
</script>
<?php $this->load->view('templates/customer/footer');?>
