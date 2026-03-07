<?php $this->load->view('templates/customer/header');?>

<div class="content content-fixed content-wrapper">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-sm-12">
                <!-- KB Categories Card -->
                <div class="card detail-sidebar-card">
                    <div class="card-header detail-sidebar-header">
						<h6 class="card-title mg-b-0"><i class="fa fa-folder-open text-white mg-r-10"></i>&nbsp;Browse by Category</h6>
                    </div>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($cats as $cat) { ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="<?=base_url()?>supports/kb_category/<?=$cat['id']?>/<?=$cat['slug']?>"><?php echo htmlspecialchars($cat['cat_title']); ?></a>
                            <span class="badge rounded-pill bg-primary"><?=$cat['total_kb']?></span>
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
                            <h3><i class="fa fa-file-alt mg-r-10"></i><?=htmlspecialchars($details['title'] ?? 'Article')?></h3>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb breadcrumb-style1 mg-b-0">
                                    <li class="breadcrumb-item"><a href="<?=base_url()?>clientarea">Portal home</a></li>
                                    <li class="breadcrumb-item"><a href="<?=base_url()?>supports/KB">Knowledge Base</a></li>
                                    <li class="breadcrumb-item active"><a><?=htmlspecialchars($details['title'] ?? 'Article')?></a></li>
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

                <?php if(!empty($details)) { ?>
                <!-- Article Card -->
                <div class="card services-card">
                    <div class="card-body pd-25">
                        <!-- Article Meta -->
                        <div class="d-flex justify-content-between align-items-center mg-b-20 pb-3 border-bottom">
                            <div class="flex-1 m-2">
                                <h5 class="mg-b-3 tx-semibold"><i class="fa fa-book"></i>&nbsp;<?=htmlspecialchars($details['title'])?></h5>
                                <div class="kb-article-tags">
                                    <?php if(!empty($details['tags'])) { ?>
                                    <span class="mg-r-15"><i class="fa fa-tags"></i> <?=htmlspecialchars($details['tags'])?></span>
                                    <?php } ?>
                                    <span><i class="fa fa-eye"></i> <?=$details['total_view'] ?? 0?> views</span>
                                </div>
                            </div>
                        </div>

                        <!-- Article Content -->
                        <div class="kb-article-content p-2">
                            <?= sanitize_html($details['article'] ?? '') ?>
                        </div>

                        <!-- Article Rating -->
                        <div class="mt-4 p-2">
                            <h6 class="tx-semibold mg-b-15">Was this article helpful?</h6>
                            <div class="d-flex align-items-center">
                                <button class="btn btn-outline-success btn-sm mg-r-10" onclick="rateArticle(<?=$details['id']?>, 'up')">
                                    <i class="fa fa-thumbs-up"></i> Yes (<?=$details['upvote'] ?? 0?>)
                                </button>
                                <button class="btn btn-outline-danger btn-sm" onclick="rateArticle(<?=$details['id']?>, 'down')">
                                    <i class="fa fa-thumbs-down"></i> No (<?=$details['downvote'] ?? 0?>)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } else { ?>
                <!-- Article Not Found -->
                <div class="card services-card">
                    <div class="card-body">
                        <div class="empty-state">
                            <i class="fa fa-exclamation-circle text-warning"></i>
                            <h5>Article Not Found</h5>
                            <p class="text-muted">The article you're looking for doesn't exist or has been removed.</p>
                            <a href="<?=base_url()?>supports/KB" class="btn btn-primary btn-sm mt-2">Browse Knowledge Base</a>
                        </div>
                    </div>
                </div>
                <?php } ?>

            </div>
        </div>
    </div>
</div>

<?php $this->load->view('templates/customer/footer');?>
<?php $this->load->view('templates/customer/footer_script');?>

<script>
function rateArticle(id, vote) {
    // Placeholder for article rating functionality
    alert('Thank you for your feedback!');
}
</script>
