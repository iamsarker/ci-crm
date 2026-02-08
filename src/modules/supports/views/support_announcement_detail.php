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
            <li class="breadcrumb-item active" aria-current="page"><?=htmlspecialchars($details['title'] ?? 'Announcement')?></li>
          </ol>
        </nav>
        <h4 class="mg-b-0 tx-spacing--1"><?=htmlspecialchars($details['title'] ?? 'Announcement')?></h4>
      </div>
      <div class="mg-t-15 mg-sm-t-0">
        <a href="<?=base_url()?>supports/announcements" class="btn btn-sm btn-outline-secondary">
          <i class="fa fa-arrow-left"></i> Back to Announcements
        </a>
      </div>
    </div>

    <div class="row">
      <!-- Main Content -->
      <div class="col-lg-8 col-md-12">

        <?php if(!empty($details)) { ?>
        <div class="card">
          <div class="card-body pd-25">
            <!-- Announcement Meta -->
            <div class="d-flex align-items-center mg-b-20 pd-b-15 bd-b">
              <div class="wd-50 ht-50 bg-primary tx-white d-flex align-items-center justify-content-center rounded mg-r-15">
                <i class="fa fa-bullhorn fa-lg"></i>
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

            <!-- Announcement Content -->
            <div class="announcement-content">
              <?= sanitize_html($details['description'] ?? '') ?>
            </div>

            <!-- Share Buttons -->
            <div class="mg-t-30 pd-t-20 bd-t">
              <h6 class="tx-13 tx-uppercase tx-semibold tx-spacing-1 mg-b-15">Share this announcement</h6>
              <div class="d-flex">
                <a href="javascript:void(0)" onclick="shareOn('facebook')" class="btn btn-outline-primary btn-sm mg-r-10">
                  <i class="fab fa-facebook-f"></i>
                </a>
                <a href="javascript:void(0)" onclick="shareOn('twitter')" class="btn btn-outline-info btn-sm mg-r-10">
                  <i class="fab fa-twitter"></i>
                </a>
                <a href="javascript:void(0)" onclick="shareOn('linkedin')" class="btn btn-outline-secondary btn-sm mg-r-10">
                  <i class="fab fa-linkedin-in"></i>
                </a>
                <a href="javascript:void(0)" onclick="copyLink()" class="btn btn-outline-dark btn-sm">
                  <i class="fa fa-link"></i> Copy Link
                </a>
              </div>
            </div>
          </div>
        </div>
        <?php } else { ?>
        <div class="card">
          <div class="card-body pd-40 tx-center">
            <i class="fa fa-exclamation-circle fa-3x tx-warning mg-b-20"></i>
            <h5>Announcement Not Found</h5>
            <p class="tx-color-03 mg-b-20">The announcement you're looking for doesn't exist or has been removed.</p>
            <a href="<?=base_url()?>supports/announcements" class="btn btn-primary">Browse Announcements</a>
          </div>
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
              <?php foreach ($archive as $item) { ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <a href="<?=base_url()?>supports/announcements_archive/<?=$item['year']?>/<?=$item['month']?>">
                  <i class="fa fa-folder-open tx-color-03 mg-r-5"></i><?=$item['month_name']?>
                </a>
                <span class="badge rounded-pill bg-secondary"><?=$item['total']?></span>
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

<script>
function shareOn(platform) {
  var url = encodeURIComponent(window.location.href);
  var title = encodeURIComponent(document.title);
  var shareUrl = '';

  switch(platform) {
    case 'facebook':
      shareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + url;
      break;
    case 'twitter':
      shareUrl = 'https://twitter.com/intent/tweet?url=' + url + '&text=' + title;
      break;
    case 'linkedin':
      shareUrl = 'https://www.linkedin.com/shareArticle?mini=true&url=' + url + '&title=' + title;
      break;
  }

  if(shareUrl) {
    window.open(shareUrl, '_blank', 'width=600,height=400');
  }
}

function copyLink() {
  navigator.clipboard.writeText(window.location.href).then(function() {
    alert('Link copied to clipboard!');
  });
}
</script>
