<?php $this->load->view('templates/customer/header');?>

<div class="content content-fixed content-wrapper">
  <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">

    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mg-b-25">
      <div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb breadcrumb-style1 mg-b-10">
            <li class="breadcrumb-item"><a href="<?=base_url()?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?=base_url()?>supports/announcements">Announcements</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?=$month_name?></li>
          </ol>
        </nav>
        <h4 class="mg-b-0 tx-spacing--1">Announcements - <?=$month_name?></h4>
        <p class="tx-color-03 mg-b-0"><?=$total?> announcements in this period</p>
      </div>
      <div class="mg-t-15 mg-sm-t-0">
        <a href="<?=base_url()?>supports/announcements" class="btn btn-sm btn-outline-secondary">
          <i class="fa fa-arrow-left"></i> All Announcements
        </a>
      </div>
    </div>

    <div class="row">
      <!-- Main Content -->
      <div class="col-lg-8 col-md-12">

        <!-- Announcements Grid -->
        <div class="row row-xs">
          <?php foreach($results as $row) { ?>
          <div class="col-md-6 mg-b-20">
            <div class="card h-100 card-hover">
              <div class="card-body pd-20">
                <div class="d-flex align-items-start">
                  <div class="wd-50 ht-50 bg-primary tx-white d-flex align-items-center justify-content-center rounded mg-r-15 flex-shrink-0">
                    <i class="fa fa-bullhorn fa-lg"></i>
                  </div>
                  <div class="flex-1">
                    <h6 class="mg-b-5">
                      <a href="<?=base_url()?>supports/view_announcement/<?=$row['id']?>/<?=$row['slug']?>" class="tx-dark">
                        <?=htmlspecialchars($row['title'])?>
                      </a>
                    </h6>
                    <?php if(!empty($row['tags'])) { ?>
                    <div class="tx-12 tx-color-03 mg-b-10">
                      <i class="fa fa-tags"></i> <?=htmlspecialchars($row['tags'])?>
                    </div>
                    <?php } ?>
                    <div class="tx-12 tx-color-03">
                      <i class="fa fa-eye"></i> <?=$row['total_view'] ?? 0?> views
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-footer bg-transparent pd-y-10">
                <a href="<?=base_url()?>supports/view_announcement/<?=$row['id']?>/<?=$row['slug']?>" class="btn btn-sm btn-outline-primary">
                  Read More <i class="fa fa-arrow-right mg-l-5"></i>
                </a>
              </div>
            </div>
          </div>
          <?php } ?>
        </div>

        <?php if(empty($results)) { ?>
        <div class="card">
          <div class="card-body pd-40 tx-center">
            <i class="fa fa-bullhorn fa-3x tx-gray-400 mg-b-20"></i>
            <h5 class="tx-color-03">No Announcements</h5>
            <p class="tx-color-03 mg-b-0">There are no announcements for this period.</p>
          </div>
        </div>
        <?php } ?>

        <!-- List View -->
        <?php if(!empty($results)) { ?>
        <h5 class="mg-t-30 mg-b-15"><i class="fa fa-list tx-primary"></i> All Announcements</h5>
        <div class="card">
          <ul class="list-group list-group-flush">
            <?php foreach($results as $row) { ?>
            <li class="list-group-item pd-y-15">
              <div class="d-flex align-items-center">
                <div class="wd-40 ht-40 bg-light d-flex align-items-center justify-content-center rounded mg-r-15">
                  <i class="fa fa-bullhorn tx-primary"></i>
                </div>
                <div class="flex-1">
                  <h6 class="mg-b-3">
                    <a href="<?=base_url()?>supports/view_announcement/<?=$row['id']?>/<?=$row['slug']?>" class="tx-dark tx-semibold">
                      <?=htmlspecialchars($row['title'])?>
                    </a>
                  </h6>
                  <?php if(!empty($row['tags'])) { ?>
                  <div class="tx-12 tx-color-03">
                    <i class="fa fa-tags"></i> <?=htmlspecialchars($row['tags'])?>
                  </div>
                  <?php } ?>
                </div>
                <div class="tx-12 tx-color-03">
                  <i class="fa fa-eye"></i> <?=$row['total_view'] ?? 0?>
                </div>
              </div>
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
              <nav aria-label="Archive pagination">
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
        <?php } ?>

      </div>

      <!-- Sidebar -->
      <div class="col-lg-4 col-md-12 mg-t-25 mg-lg-t-0">

        <!-- Announcement Archive Card -->
        <div class="card card-widget card-contacts mg-b-20">
          <div class="card-header">
            <h6 class="card-title mg-b-0"><i class="fa fa-calendar-alt"></i>&nbsp;Archive</h6>
          </div>
          <ul class="list-group list-group-flush">
            <?php if(!empty($archive)) { ?>
              <?php foreach ($archive as $item) {
                $isActive = ($item['year'] == $year && $item['month'] == $month);
              ?>
              <li class="list-group-item d-flex justify-content-between align-items-center <?=$isActive ? 'active' : ''?>">
                <a href="<?=base_url()?>supports/announcements_archive/<?=$item['year']?>/<?=$item['month']?>" class="<?=$isActive ? 'tx-white' : ''?>">
                  <i class="fa fa-folder-open mg-r-5 <?=$isActive ? '' : 'tx-color-03'?>"></i><?=$item['month_name']?>
                </a>
                <span class="badge rounded-pill <?=$isActive ? 'bg-white tx-primary' : 'bg-secondary'?>"><?=$item['total']?></span>
              </li>
              <?php } ?>
            <?php } else { ?>
              <li class="list-group-item tx-color-03 tx-center">No archives available</li>
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

<style>
.card-hover:hover {
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
  transform: translateY(-2px);
  transition: all 0.2s ease;
}
</style>
