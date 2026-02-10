<?php $this->load->view('templates/customer/header');?>

<div class="content content-fixed content-wrapper">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <div class="row">
            <div class="col-md-3 col-sm-12">
                <div class="card detail-sidebar-card">
                    <div class="card-header detail-sidebar-header">
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
                <div class="page-header-card page-header-domains">
                    <h3><i class="fa fa-globe mg-r-10"></i>My Domains</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-style1 mg-b-0">
                            <li class="breadcrumb-item"><a href="<?=base_url()?>clientarea">Portal home</a></li>
                            <li class="breadcrumb-item active"><a>My Domains</a></li>
                        </ol>
                    </nav>
                </div>

                <div class="card services-card">
                    <div class="card-body">
                        <?php if(!empty($results)): ?>
                        <table id="example1" class="table services-table domains-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Domain Name</th>
                                    <th>Registration Date</th>
                                    <th>Expiry Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($results as $row): ?>
                                <tr>
                                    <td>
                                        <a href="<?=base_url()?>clientarea/domain_detail/<?=$row['id']?>" class="order-link">
                                            <i class="fa fa-external-link-alt"></i>
                                            #<?php echo $row['order_id']; ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="domain-name-cell">
                                            <i class="fa fa-globe domain-icon"></i>
                                            <span class="domain-text"><?php echo $row['domain']; ?></span>
                                        </div>
                                    </td>
                                    <td class="date-cell">
                                        <i class="fa fa-calendar-plus"></i>
                                        <?php echo date('M d, Y', strtotime($row['reg_date'])); ?>
                                    </td>
                                    <td class="date-cell">
                                        <i class="fa fa-calendar-times"></i>
                                        <?php echo date('M d, Y', strtotime($row['exp_date'])); ?>
                                    </td>
                                    <td><?php echo getDomainStatus($row['status']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="fa fa-globe"></i>
                            <h5>No Domains Found</h5>
                            <p>You don't have any registered domains yet.</p>
                            <a href="<?=base_url()?>cart/domain/register" class="btn btn-primary btn-sm mt-3">
                                <i class="fa fa-plus mg-r-5"></i>Register a Domain
                            </a>
                        </div>
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

    if($('#example1 tbody tr').length > 0) {
        $('#example1').DataTable({
            "aaSorting": [],
            "responsive": true,
            "language": {
                searchPlaceholder: 'Search domains...',
                sSearch: '<i class="fa fa-search"></i>',
                lengthMenu: 'Show _MENU_ domains',
                info: 'Showing _START_ to _END_ of _TOTAL_ domains',
                infoEmpty: 'No domains found',
                paginate: {
                    previous: '<i class="fa fa-chevron-left"></i>',
                    next: '<i class="fa fa-chevron-right"></i>'
                }
            },
            "dom": '<"d-flex justify-content-between align-items-center mb-3"lf>t<"d-flex justify-content-between align-items-center mt-3"ip>',
        });
    }
});
</script>
<?php $this->load->view('templates/customer/footer');?>
