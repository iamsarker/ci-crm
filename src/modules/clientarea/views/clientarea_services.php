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
                <div class="page-header-card">
                    <h3><i class="fa fa-server mg-r-10"></i>My Services</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-style1 mg-b-0">
                            <li class="breadcrumb-item"><a href="<?=base_url()?>clientarea">Portal home</a></li>
                            <li class="breadcrumb-item active"><a>My Services</a></li>
                        </ol>
                    </nav>
                </div>

                <div class="card services-card">
                    <div class="card-body">
                        <?php if(!empty($results)): ?>
                        <table id="example1" class="table services-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Order Date</th>
                                    <th>Expiry Date</th>
                                    <th>Service Details</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($results as $row): ?>
                                <tr>
                                    <td>
                                        <a href="<?=base_url()?>clientarea/service_detail/<?=$row['id']?>" class="order-link">
                                            <i class="fa fa-external-link-alt"></i>
                                            #<?php echo $row['order_id']; ?>
                                        </a>
                                    </td>
                                    <td class="date-cell">
                                        <i class="fa fa-calendar-plus"></i>
                                        <?php echo date('M d, Y', strtotime($row['reg_date'])); ?>
                                    </td>
                                    <td class="date-cell">
                                        <i class="fa fa-calendar-times"></i>
                                        <?php echo date('M d, Y', strtotime($row['exp_date'])); ?>
                                    </td>
                                    <td>
                                        <div class="service-description">
                                            <span class="service-name"><?php echo $row['description']; ?></span>
                                            <?php if(!empty($row['hosting_domain'])): ?>
                                            <span class="service-domain">
                                                <i class="fa fa-globe"></i>
                                                <?php echo $row['hosting_domain']; ?>
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?php echo getServiceStatus($row['status']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="fa fa-inbox"></i>
                            <h5>No Services Found</h5>
                            <p>You don't have any active services yet.</p>
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
                searchPlaceholder: 'Search services...',
                sSearch: '<i class="fa fa-search"></i>',
                lengthMenu: 'Show _MENU_ services',
                info: 'Showing _START_ to _END_ of _TOTAL_ services',
                infoEmpty: 'No services found',
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
