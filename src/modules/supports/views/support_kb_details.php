<?php $this->load->view('templates/customer/header');?>

<div class="content content-fixed content-wrapper">
  <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">

    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mg-b-25">
      <div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb breadcrumb-style1 mg-b-10">
            <li class="breadcrumb-item"><a href="<?=base_url()?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?=base_url()?>supports/KB">Knowledge Base</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?=htmlspecialchars($details['title'] ?? 'Article')?></li>
          </ol>
        </nav>
        <h4 class="mg-b-0 tx-spacing--1"><?=htmlspecialchars($details['title'] ?? 'Article')?></h4>
      </div>
      <div class="mg-t-15 mg-sm-t-0">
        <a href="<?=base_url()?>supports/KB" class="btn btn-sm btn-outline-secondary">
          <i class="fa fa-arrow-left"></i> Back to KB
        </a>
      </div>
    </div>

    <div class="row">
      <!-- Main Content -->
      <div class="col-lg-8 col-md-12">

        <?php if(!empty($details)) { ?>
        <div class="card">
          <div class="card-body pd-25">
            <!-- Article Meta -->
            <div class="d-flex align-items-center mg-b-20 pd-b-15 bd-b">
              <div class="wd-50 ht-50 bg-primary tx-white d-flex align-items-center justify-content-center rounded mg-r-15">
                <i class="fa fa-book fa-lg"></i>
              </div>
              <div class="flex-1">
                <h5 class="mg-b-3"><?=htmlspecialchars($details['title'])?></h5>
                <div class="tx-12 tx-color-03">
                  <?php if(!empty($details['tags'])) { ?>
                  <span class="mg-r-15"><i class="fa fa-tags"></i> <?=htmlspecialchars($details['tags'])?></span>
                  <?php } ?>
                  <span><i class="fa fa-eye"></i> <?=$details['total_view'] ?? 0?> views</span>
                </div>
              </div>
            </div>

            <!-- Article Content -->
            <div class="kb-article-content">
              <?= sanitize_html($details['article'] ?? '') ?>
            </div>

            <!-- Article Rating -->
            <div class="mg-t-30 pd-t-20 bd-t">
              <h6 class="tx-13 tx-uppercase tx-semibold tx-spacing-1 mg-b-15">Was this article helpful?</h6>
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
        <div class="card">
          <div class="card-body pd-40 tx-center">
            <i class="fa fa-exclamation-circle fa-3x tx-warning mg-b-20"></i>
            <h5>Article Not Found</h5>
            <p class="tx-color-03 mg-b-20">The article you're looking for doesn't exist or has been removed.</p>
            <a href="<?=base_url()?>supports/KB" class="btn btn-primary">Browse Knowledge Base</a>
          </div>
        </div>
        <?php } ?>

      </div>

      <!-- Sidebar -->
      <div class="col-lg-4 col-md-12 mg-t-25 mg-lg-t-0">

        <!-- KB Categories Card -->
        <div class="card card-widget card-contacts mg-b-20">
          <div class="card-header">
            <h6 class="card-title mg-b-0"><i class="fa fa-tags"></i>&nbsp;KB Categories</h6>
          </div>
          <ul class="list-group list-group-flush">
            <?php foreach ($cats as $cat) { ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <a href="<?=base_url()?>supports/kb_category/<?=$cat['id']?>/<?=$cat['slug']?>"><?php echo htmlspecialchars($cat['cat_title']); ?></a>
              <span class="badge rounded-pill bg-secondary"><?=$cat['total_kb']?></span>
            </li>
            <?php } ?>
          </ul>
        </div>

        <!-- Support Navigation -->
        <?php $this->load->view('templates/customer/support_nav');?>

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
