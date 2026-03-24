<div class="card card-widget card-contacts mt-3">
  <div class="card-header">
    <h6 class="card-title mg-b-0"><i class="fa fa-globe"></i> &nbsp;Domain Actions</h6>
    <nav class="nav">

    </nav>
  </div><!-- card-header -->
  <ul class="list-group list-group-flush">
    <li class="list-group-item">
      <div class="d-flex justify-content-between align-items-center">
        <span class="nav-sub-link" style="cursor:default;"><i class="fas fa-lock"></i>&nbsp;Transfer Lock</span>
        <div class="form-check form-switch mb-0">
          <input class="form-check-input" type="checkbox" id="transferLockToggle"
                 <?= !empty($detail['transfer_lock']) ? 'checked' : '' ?>
                 style="cursor:pointer; width:2.5em; height:1.25em;" />
        </div>
      </div>
      <div id="transferLockStatus" class="mt-1"></div>
    </li>
    <li class="list-group-item">
      <a title="Send EPP Code" href="javascript:void(0);" id="btnSendEppCode" class="nav-sub-link"><i class="fas fa-envelope"></i>&nbsp;Send EPP Code</a>
      <div id="eppCodeStatus" class="mt-2"></div>
    </li>
    <li class="list-group-item">
      <a href="<?=base_url()?>clientarea/domain_cancellation_request/<?= !empty($detail['order_id']) ? $detail['order_id'] : '0'?>/<?= !empty($detail['id']) ? $detail['id']: '0'?>" target="_blank" class="nav-sub-link text-danger"><i class="fas fa-times-circle"></i>&nbsp;Request Cancellation</a>
    </li>
  </ul>
</div>
