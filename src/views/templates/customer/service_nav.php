<div class="card card-widget card-contacts mt-3">
  <div class="card-header">
    <h6 class="card-title mg-b-0"><i class="fa fa-globe"></i> &nbsp;Shortcut</h6>
    <nav class="nav">

    </nav>
  </div><!-- card-header -->
  <ul class="list-group list-group-flush">
    <li class="list-group-item">
      <a href="<?=base_url()?>clientarea/cpanel_single_sign_on/<?= !empty($detail['order_id']) ? $detail['order_id'] : '0'?>/<?= !empty($detail['id']) ? $detail['id'] : '0'?>" target="_blank" class="nav-sub-link"><i data-feather="globe"></i>&nbsp;Login to cPanel</a>
    </li>
    <li class="list-group-item">
      <a href="<?=base_url()?>clientarea/webmail_single_sign_on/<?= !empty($detail['order_id']) ? $detail['order_id'] : '0'?>/<?= !empty($detail['id']) ?  $detail['id']: '0'?>" target="_blank" class="nav-sub-link"><i data-feather="mail"></i>&nbsp;Login to Webmail</a>
    </li>
    <li class="list-group-item">
      <a href="<?=base_url()?>clientarea/announcements" target="_blank" class="nav-sub-link"><i data-feather="lock"></i>&nbsp;Change Password</a>
    </li>
    <li class="list-group-item">
      <a href="<?=base_url()?>clientarea/service_cancellation_request/<?= !empty($detail['order_id']) ? $detail['order_id'] : '0'?>/<?= !empty($detail['id']) ? $detail['id']: '0'?>" target="_blank" class="nav-sub-link text-danger"><i data-feather="x-circle"></i>&nbsp;Request Cancellation</a>
    </li>
  </ul>
</div>
