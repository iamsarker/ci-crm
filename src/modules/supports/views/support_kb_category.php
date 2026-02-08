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
            <li class="breadcrumb-item active" aria-current="page"><?=htmlspecialchars($category['cat_title'] ?? 'Category')?></li>
          </ol>
        </nav>
        <h4 class="mg-b-0 tx-spacing--1"><?=htmlspecialchars($category['cat_title'] ?? 'Category')?></h4>
        <?php if(!empty($category['description'])) { ?>
        <p class="tx-color-03 mg-b-0"><?=htmlspecialchars($category['description'])?></p>
        <?php } ?>
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

        <!-- Articles in Category -->
        <div class="card">
          <div class="card-header pd-y-15 d-flex justify-content-between align-items-center">
            <h6 class="mg-b-0"><i class="fa fa-folder-open tx-primary"></i> Articles in "<?=htmlspecialchars($category['cat_title'] ?? '')?>"</h6>
            <span class="tx-12 tx-color-03"><?=$total?> articles</span>
          </div>
          <ul class="list-group list-group-flush">
            <?php foreach($results as $row) { ?>
            <li class="list-group-item pd-y-15">
              <div class="d-flex align-items-start">
                <div class="wd-40 ht-40 bg-light d-flex align-items-center justify-content-center rounded mg-r-15">
                  <i class="fa fa-file-alt tx-primary"></i>
                </div>
                <div class="flex-1">
                  <h6 class="mg-b-3">
                    <a href="<?=base_url()?>supports/view_kb/<?=$row['id']?>/<?=$row['slug']?>" class="tx-dark tx-semibold"><?php echo htmlspecialchars($row['title']); ?></a>
                  </h6>
                  <?php if(!empty($row['tags'])) { ?>
                  <div class="tx-12 tx-color-03">
                    <i class="fa fa-tags"></i> <?php echo htmlspecialchars($row['tags']); ?>
                  </div>
                  <?php } ?>
                </div>
                <div class="tx-12 tx-color-03">
                  <i class="fa fa-eye"></i> <?=$row['total_view'] ?? 0?>
                </div>
              </div>
            </li>
            <?php } ?>
            <?php if(empty($results)) { ?>
            <li class="list-group-item pd-y-25 tx-center tx-color-03">
              <i class="fa fa-folder-open fa-2x mg-b-10"></i>
              <p class="mg-b-0">No articles found in this category</p>
            </li>
            <?php } ?>
          </ul>

          <!-- Pagination -->
          <?php if($total_pages > 1) { ?>
          <div class="card-footer bg-transparent">
            <div class="d-flex justify-content-between align-items-center">
              <span class="tx-12 tx-color-03">
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

      <!-- Sidebar -->
      <div class="col-lg-4 col-md-12 mg-t-25 mg-lg-t-0">

        <!-- KB Categories Card -->
        <div class="card card-widget card-contacts mg-b-20">
          <div class="card-header">
            <h6 class="card-title mg-b-0"><i class="fa fa-tags"></i>&nbsp;KB Categories</h6>
          </div>
          <ul class="list-group list-group-flush">
            <?php foreach ($cats as $cat) { ?>
            <li class="list-group-item d-flex justify-content-between align-items-center <?=($cat['id'] == ($category['id'] ?? 0)) ? 'active' : ''?>">
              <a href="<?=base_url()?>supports/kb_category/<?=$cat['id']?>/<?=$cat['slug']?>" class="<?=($cat['id'] == ($category['id'] ?? 0)) ? 'tx-white' : ''?>"><?php echo htmlspecialchars($cat['cat_title']); ?></a>
              <span class="badge rounded-pill <?=($cat['id'] == ($category['id'] ?? 0)) ? 'bg-white tx-primary' : 'bg-secondary'?>"><?=$cat['total_kb']?></span>
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
