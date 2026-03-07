<?php $this->load->view('templates/customer/header');?>

<div class="content content-fixed content-wrapper">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-sm-12">
                <!-- KB Categories Card -->
                <div class="card detail-sidebar-card">
                    <div class="card-header detail-sidebar-header">
                        <h6 class="card-title mg-b-0"><i class="fa fa-tags mg-r-5"></i>KB Categories</h6>
                    </div>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($cats as $cat) { ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center <?=($cat['id'] == ($category['id'] ?? 0)) ? 'active' : ''?>">
                            <a href="<?=base_url()?>supports/kb_category/<?=$cat['id']?>/<?=$cat['slug']?>" class="<?=($cat['id'] == ($category['id'] ?? 0)) ? 'text-white' : ''?>"><?php echo htmlspecialchars($cat['cat_title']); ?></a>
                            <span class="badge rounded-pill <?=($cat['id'] == ($category['id'] ?? 0)) ? 'bg-white text-primary' : 'bg-primary'?>"><?=$cat['total_kb']?></span>
                        </li>
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
                            <h3><i class="fa fa-folder-open mg-r-10"></i><?=htmlspecialchars($category['cat_title'] ?? 'Category')?></h3>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb breadcrumb-style1 mg-b-0">
                                    <li class="breadcrumb-item"><a href="<?=base_url()?>clientarea">Portal home</a></li>
                                    <li class="breadcrumb-item"><a href="<?=base_url()?>supports/KB">Knowledge Base</a></li>
                                    <li class="breadcrumb-item active"><a><?=htmlspecialchars($category['cat_title'] ?? 'Category')?></a></li>
                                </ol>
                            </nav>
                        </div>
                        <div class="header-actions mt-2 mt-md-0">
                            <a href="<?=base_url()?>supports/KB" class="btn btn-light btn-sm">
                                <i class="fa fa-arrow-left mg-r-5"></i>Back to KB
                            </a>
                        </div>
                    </div>
                </div>

                <?php if(!empty($category['description'])) { ?>
                <div class="alert alert-info mg-b-20">
                    <i class="fa fa-info-circle mg-r-5"></i> <?=htmlspecialchars($category['description'])?>
                </div>
                <?php } ?>

                <!-- Articles in Category -->
                <div class="card services-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mg-b-0"><i class="fa fa-file-alt text-primary mg-r-10"></i>Articles in "<?=htmlspecialchars($category['cat_title'] ?? '')?>"</h6>
                        <span class="text-muted small"><?=$total?> articles</span>
                    </div>
                    <ul class="list-group list-group-flush">
                        <?php foreach($results as $row) { ?>
                        <li class="list-group-item kb-article-item">
                            <div class="d-flex align-items-start">
                                <div class="kb-article-icon">
                                    <i class="fa fa-file-alt"></i>
                                </div>
                                <div class="flex-1 mg-l-15">
                                    <h6 class="mg-b-3">
                                        <a href="<?=base_url()?>supports/view_kb/<?=$row['id']?>/<?=$row['slug']?>" class="kb-article-link"><?php echo htmlspecialchars($row['title']); ?></a>
                                    </h6>
                                    <?php if(!empty($row['tags'])) { ?>
                                    <div class="kb-article-tags">
                                        <i class="fa fa-tags"></i> <?php echo htmlspecialchars($row['tags']); ?>
                                    </div>
                                    <?php } ?>
                                </div>
                                <div class="kb-article-views">
                                    <i class="fa fa-eye"></i> <?=$row['total_view'] ?? 0?>
                                </div>
                            </div>
                        </li>
                        <?php } ?>
                        <?php if(empty($results)) { ?>
                        <li class="list-group-item">
                            <div class="empty-state">
                                <i class="fa fa-folder-open"></i>
                                <p>No articles found in this category</p>
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
                            <nav aria-label="KB Category pagination">
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
