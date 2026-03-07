<?php $this->load->view('templates/customer/header');?>

<div class="content content-fixed content-wrapper">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-sm-12">
                <!-- Announcement Archive Card -->
                <div class="card detail-sidebar-card">
                    <div class="card-header detail-sidebar-header">
                        <h6 class="card-title mg-b-0"><i class="fa fa-calendar-alt text-white mg-r-10"></i>&nbsp;Archive</h6>
                    </div>
                    <ul class="list-group list-group-flush">
                        <?php if(!empty($archive)) { ?>
                            <?php foreach ($archive as $item) { ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="<?=base_url()?>supports/announcements_archive/<?=$item['year']?>/<?=$item['month']?>">
                                    <i class="fa fa-folder-open text-muted mg-r-5"></i><?=$item['month_name']?>
                                </a>
                                <span class="badge rounded-pill bg-primary"><?=$item['total']?></span>
                            </li>
                            <?php } ?>
                        <?php } else { ?>
                            <li class="list-group-item text-muted text-center">No archives available</li>
                        <?php } ?>
                    </ul>
                </div>

                <?php $this->load->view('templates/customer/support_nav');?>
            </div>

            <div class="col-md-9 col-sm-12">
                <!-- Page Header -->
                <div class="page-header-card page-header-kb">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h3><i class="fa fa-bullhorn mg-r-10"></i>Announcements</h3>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb breadcrumb-style1 mg-b-0">
                                    <li class="breadcrumb-item"><a href="<?=base_url()?>clientarea">Portal home</a></li>
                                    <li class="breadcrumb-item active"><a>Announcements</a></li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- All Announcements -->
                <div class="d-flex justify-content-between align-items-center mg-b-15">
                    <h5 class="mg-b-0 tx-semibold"><i class="fa fa-bullhorn text-primary mg-r-10"></i>Latest Announcements</h5>
                    <span class="text-muted"><?=$total?> announcements</span>
                </div>
                <div class="card services-card">
                    <ul class="list-group list-group-flush" id="announcement-list">
                        <?php if(!empty($results)) { ?>
                            <?php foreach($results as $row) { ?>
                            <li class="list-group-item kb-article-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="mg-l-15">
                                        <h6 class="mg-b-3 p-1">
                                            <i class="fa fa-bullhorn"></i>&nbsp;<a href="<?=base_url()?>supports/view_announcement/<?=$row['id']?>/<?=$row['slug']?>" class="kb-article-link"><?=htmlspecialchars($row['title'])?></a>
                                        </h6>
                                        <?php if(!empty($row['tags'])) { ?>
                                        <div class="kb-article-tags">
                                            <i class="fa fa-tags"></i> <?=htmlspecialchars($row['tags'])?>
                                        </div>
                                        <?php } ?>
                                    </div>
                                    <div class="kb-article-views">
                                        <i class="fa fa-eye"></i> <?=$row['total_view'] ?? 0?>
                                    </div>
                                </div>
                            </li>
                            <?php } ?>
                        <?php } else { ?>
                        <li class="list-group-item">
                            <div class="empty-state">
                                <i class="fa fa-bullhorn"></i>
                                <p>No announcements at this time</p>
                            </div>
                        </li>
                        <?php } ?>
                    </ul>

                    <!-- Pagination -->
                    <?php if($total_pages > 1) { ?>
                    <div class="card-footer bg-transparent">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">
                                Page <?=$current_page?> of <?=$total_pages?>
                            </span>
                            <nav aria-label="Announcements pagination">
                                <ul class="pagination pagination-sm mg-b-0">
                                    <!-- Previous -->
                                    <li class="page-item <?=($current_page <= 1) ? 'disabled' : ''?>">
                                        <a class="page-link" href="<?=$base_url?>/<?=$current_page - 1?>" aria-label="Previous">
                                            <i class="fa fa-chevron-left"></i>
                                        </a>
                                    </li>

                                    <?php
                                    $start_page = max(1, $current_page - 2);
                                    $end_page = min($total_pages, $current_page + 2);

                                    if($start_page > 1) { ?>
                                        <li class="page-item"><a class="page-link" href="<?=$base_url?>/1">1</a></li>
                                        <?php if($start_page > 2) { ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                        <?php } ?>
                                    <?php }

                                    for($i = $start_page; $i <= $end_page; $i++) { ?>
                                        <li class="page-item <?=($i == $current_page) ? 'active' : ''?>">
                                            <a class="page-link" href="<?=$base_url?>/<?=$i?>"><?=$i?></a>
                                        </li>
                                    <?php }

                                    if($end_page < $total_pages) {
                                        if($end_page < $total_pages - 1) { ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                        <?php } ?>
                                        <li class="page-item"><a class="page-link" href="<?=$base_url?>/<?=$total_pages?>"><?=$total_pages?></a></li>
                                    <?php } ?>

                                    <!-- Next -->
                                    <li class="page-item <?=($current_page >= $total_pages) ? 'disabled' : ''?>">
                                        <a class="page-link" href="<?=$base_url?>/<?=$current_page + 1?>" aria-label="Next">
                                            <i class="fa fa-chevron-right"></i>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                    <?php } ?>
                </div>

            </div>
        </div>
    </div>
</div>

<?php $this->load->view('templates/customer/footer');?>
<?php $this->load->view('templates/customer/footer_script');?>
