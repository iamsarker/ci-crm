<?php $this->load->view('templates/customer/header');?>

<div class="content content-fixed content-wrapper">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <div class="row">
            <div class="col-md-3 col-sm-12">
                <!-- Ticket Summary Card -->
                <div class="card detail-sidebar-card">
                    <div class="card-header detail-sidebar-header">
                        <h6 class="card-title mg-b-0"><i class="fa fa-chart-pie mg-r-5"></i>Ticket Summary</h6>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item summary-item">
                            <span class="summary-label"><i class="fa fa-envelope-open text-success"></i> Open</span>
                            <span class="badge rounded-pill bg-success"><?=$summary['opened']?></span>
                        </li>
                        <li class="list-group-item summary-item">
                            <span class="summary-label"><i class="fa fa-reply text-info"></i> Answered</span>
                            <span class="badge rounded-pill bg-info"><?=$summary['answered']?></span>
                        </li>
                        <li class="list-group-item summary-item">
                            <span class="summary-label"><i class="fa fa-comment text-warning"></i> Customer Reply</span>
                            <span class="badge rounded-pill bg-warning"><?=$summary['replied']?></span>
                        </li>
                        <li class="list-group-item summary-item">
                            <span class="summary-label"><i class="fa fa-check-circle text-secondary"></i> Closed</span>
                            <span class="badge rounded-pill bg-dark"><?=$summary['closed']?></span>
                        </li>
                    </ul>
                </div>

                <?php $this->load->view('templates/customer/support_nav');?>
            </div>

            <div class="col-md-9 col-sm-12">
                <!-- Page Header -->
                <div class="page-header-card page-header-tickets">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h3><i class="fa fa-headset mg-r-10"></i>My Tickets</h3>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb breadcrumb-style1 mg-b-0">
                                    <li class="breadcrumb-item"><a href="<?=base_url()?>clientarea">Portal home</a></li>
                                    <li class="breadcrumb-item active"><a>My Tickets</a></li>
                                </ol>
                            </nav>
                        </div>
                        <div class="header-actions mt-2 mt-md-0">
                            <a href="<?=base_url()?>tickets/new_ticket" class="btn btn-light btn-sm">
                                <i class="fa fa-plus mg-r-5"></i>New Ticket
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tickets Table Card -->
                <div class="card services-card">
                    <div class="card-body">
                        <?php if(empty($results)): ?>
                        <div class="empty-state">
                            <i class="fa fa-ticket-alt"></i>
                            <p>No tickets found</p>
                            <a href="<?=base_url()?>tickets/new_ticket" class="btn btn-primary btn-sm mt-2">
                                <i class="fa fa-plus mg-r-5"></i>Create New Ticket
                            </a>
                        </div>
                        <?php else: ?>
                        <table id="example1" class="table services-table tickets-table">
                            <thead>
                                <tr>
                                    <th>Ticket #</th>
                                    <th>Subject</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Last Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($results as $row): ?>
                                <tr class="ticket-row" onclick="viewMyTicket(<?= intval($row['id']) ?>)">
                                    <td>
                                        <span class="ticket-link">
                                            <i class="fa fa-ticket-alt"></i>
                                            #<?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="ticket-subject">
                                            <?= htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="department-badge">
                                            <i class="fa fa-building mg-r-5"></i><?= htmlspecialchars($row['dept_name'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?= getTicketStatus($row['flag']) ?>
                                    </td>
                                    <td>
                                        <span class="date-cell">
                                            <i class="fa fa-clock"></i>
                                            <?= !empty($row['updated_on']) ? date('M d, Y H:i', strtotime($row['updated_on'])) : '-' ?>
                                        </span>
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
        "aaSorting": [],
        "order": [[4, "desc"]],
        language: {
            searchPlaceholder: 'Search tickets...',
            sSearch: '',
            lengthMenu: '_MENU_ items/page',
        }
    });
});

function viewMyTicket(tid) {
    window.location = "<?=base_url()?>tickets/viewticket/" + tid;
}
</script>
<?php $this->load->view('templates/customer/footer');?>
