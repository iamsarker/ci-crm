<?php $this->load->view('templates/customer/header');?>

	<div class="content content-fixed content-wrapper">
      <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0 mt-2">
        <div class="d-sm-flex align-items-center justify-content-between mg-b-20 mg-lg-b-25 mg-xl-b-30">
          <div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb breadcrumb-style1 mg-b-10">
                <li class="breadcrumb-item"><a href="<?=base_url()?>clientarea/index">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">API Keys</li>
              </ol>
            </nav>
            <h4 class="mg-b-0 tx-spacing--1"><i class="fas fa-key"></i>&nbsp;API Keys</h4>
            <p class="tx-13 tx-color-03 mg-b-0 mg-t-5">
              Programmatic access to your reseller account. Base URL:
              <code><?=base_url()?>api/v1</code>. Send <code>X-Api-Key</code> and <code>X-Api-Secret</code> headers.
            </p>
          </div>
        </div>

        <div class="row">
          <!-- Create key -->
          <div class="col-lg-4 mg-b-20">
            <div class="card">
              <div class="card-header"><h6 class="mg-b-0"><i class="fas fa-plus-circle"></i>&nbsp;Create API Key</h6></div>
              <div class="card-body">
                <?php if (!empty($allow_api)): ?>
                <form method="POST" action="<?=base_url()?>clientarea/apikey_create">
                  <?=csrf_field()?>
                  <div class="form-group">
                    <label class="tx-10 tx-uppercase tx-medium tx-spacing-1 mg-b-5">Key Name <span class="tx-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required maxlength="150" placeholder="e.g. My Integration">
                  </div>
                  <div class="form-group">
                    <label class="tx-10 tx-uppercase tx-medium tx-spacing-1 mg-b-5">IP Allowlist</label>
                    <textarea name="ip_whitelist" class="form-control" rows="2" placeholder="Blank = any. One per line: 1.2.3.4 or 10.0.0.0/24"></textarea>
                  </div>
                  <div class="form-group">
                    <label class="tx-10 tx-uppercase tx-medium tx-spacing-1 mg-b-5">Expires At</label>
                    <input type="datetime-local" name="expires_at" class="form-control">
                    <small class="tx-12 tx-color-03">Leave blank for no expiry.</small>
                  </div>
                  <div class="alert alert-light tx-12 mg-b-15" role="alert">
                    <i class="fas fa-info-circle"></i> New keys have <strong>full reseller API access</strong> and are limited to <strong>5 requests/second</strong>. The secret is shown <u>once</u>.
                  </div>
                  <button type="submit" class="btn btn-primary btn-block">Create Key</button>
                </form>
                <?php else: ?>
                <div class="text-center pd-y-20">
                  <i class="fas fa-lock tx-28 tx-color-04 mg-b-10"></i>
                  <p class="tx-13 tx-color-03 mg-b-0">API access is currently <strong>disabled</strong> for your account. Please contact support to enable it. Existing keys below can still be revoked or deleted.</p>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Keys list -->
          <div class="col-lg-8 mg-b-20">
            <div class="card">
              <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mg-b-0"><i class="fas fa-list"></i>&nbsp;Your Keys</h6>
                <span class="tx-12 tx-color-03">
                  <?=intval($stats['active'])?> active &middot; <?=intval($stats['revoked'])?> revoked
                </span>
              </div>
              <div class="card-body pd-0">
                <?php if (!empty($keys)): ?>
                <div class="table-responsive">
                  <table class="table mg-b-0">
                    <thead>
                      <tr>
                        <th class="tx-12 tx-uppercase">Name / Key</th>
                        <th class="tx-12 tx-uppercase">Status</th>
                        <th class="tx-12 tx-uppercase">Last Used</th>
                        <th class="tx-12 tx-uppercase text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($keys as $k):
                        $enc = safe_encode($k['id']);
                        $expired = !empty($k['expires_at']) && strtotime($k['expires_at']) < time();
                        $active  = intval($k['status']) === 1;
                      ?>
                      <tr>
                        <td>
                          <div class="tx-medium"><?=htmlspecialchars($k['name'], ENT_QUOTES, 'UTF-8')?></div>
                          <div class="tx-12"><code><?=htmlspecialchars($k['key_id'], ENT_QUOTES, 'UTF-8')?></code></div>
                          <div class="tx-11 tx-color-03">secret &middot;&middot;&middot;&middot;<?=htmlspecialchars($k['secret_preview'] ?? '', ENT_QUOTES, 'UTF-8')?> &middot; <span class="badge bg-light text-dark">Full access</span></div>
                        </td>
                        <td>
                          <?php if ($active && $expired): ?>
                            <span class="badge bg-warning text-dark">Expired</span>
                          <?php elseif ($active): ?>
                            <span class="badge bg-success">Active</span>
                          <?php else: ?>
                            <span class="badge bg-danger">Revoked</span>
                          <?php endif; ?>
                        </td>
                        <td class="tx-12">
                          <?php if (!empty($k['last_used_at'])): ?>
                            <?=htmlspecialchars($k['last_used_at'], ENT_QUOTES, 'UTF-8')?>
                            <div class="tx-11 tx-color-03"><?=htmlspecialchars($k['last_used_ip'] ?? '', ENT_QUOTES, 'UTF-8')?></div>
                          <?php else: ?>
                            <span class="tx-color-03">never</span>
                          <?php endif; ?>
                        </td>
                        <td class="text-end">
                          <button type="button" class="btn btn-xs btn-outline-primary" title="Regenerate secret"
                            onclick="akConfirm('regenerate','<?=$enc?>','<?=htmlspecialchars(addslashes($k['name']), ENT_QUOTES, 'UTF-8')?>')"><i class="fas fa-sync"></i></button>
                          <?php if ($active): ?>
                          <button type="button" class="btn btn-xs btn-outline-warning" title="Revoke"
                            onclick="akConfirm('revoke','<?=$enc?>','<?=htmlspecialchars(addslashes($k['name']), ENT_QUOTES, 'UTF-8')?>')"><i class="fas fa-ban"></i></button>
                          <?php else: ?>
                          <button type="button" class="btn btn-xs btn-outline-success" title="Re-activate"
                            onclick="akConfirm('activate','<?=$enc?>','<?=htmlspecialchars(addslashes($k['name']), ENT_QUOTES, 'UTF-8')?>')"><i class="fas fa-check"></i></button>
                          <?php endif; ?>
                          <button type="button" class="btn btn-xs btn-outline-danger" title="Delete"
                            onclick="akConfirm('delete','<?=$enc?>','<?=htmlspecialchars(addslashes($k['name']), ENT_QUOTES, 'UTF-8')?>')"><i class="fas fa-trash"></i></button>
                        </td>
                      </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
                <?php else: ?>
                <div class="text-center pd-y-40">
                  <i class="fas fa-key tx-40 tx-color-04 mg-b-10"></i>
                  <p class="tx-color-03 mg-b-0">No API keys yet. Create one to get started.</p>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

<?php $this->load->view('templates/customer/footer_script');?>

<?php $newCred = $this->session->flashdata('new_api_credential'); ?>
<script>
function akConfirm(action, id, name) {
  var meta = {
    regenerate: { title: 'Regenerate Secret?', html: 'A new secret will be issued for <strong>' + name + '</strong>. The current secret stops working immediately.', color: '#0168fa', btn: 'Regenerate' },
    revoke:     { title: 'Revoke Key?', html: 'Revoke <strong>' + name + '</strong>? Requests using it will be rejected.', color: '#d33', btn: 'Revoke' },
    activate:   { title: 'Re-activate Key?', html: 'Re-activate <strong>' + name + '</strong>?', color: '#28a745', btn: 'Re-activate' },
    delete:     { title: 'Delete Key?', html: 'Permanently delete <strong>' + name + '</strong>? This cannot be undone.', color: '#d33', btn: 'Delete' }
  }[action];
  Swal.fire({
    title: meta.title, html: meta.html, icon: 'warning', showCancelButton: true,
    confirmButtonColor: meta.color, confirmButtonText: meta.btn, reverseButtons: true
  }).then(function(r){
    if (r.isConfirmed) window.location = "<?=base_url()?>clientarea/apikey_" + action + "/" + id;
  });
}

<?php if (!empty($newCred)): ?>
Swal.fire({
  title: '<i class="fas fa-key" style="color:#28a745"></i> API Credentials',
  html:
    '<p class="mb-2">Copy these now for <strong><?=htmlspecialchars(addslashes($newCred['name']), ENT_QUOTES, 'UTF-8')?></strong>. The secret is shown <u>only once</u>.</p>' +
    '<div class="text-start">' +
    '<label class="small text-muted mb-1">API Key (X-Api-Key)</label>' +
    '<div class="input-group mb-3"><input id="akKey" class="form-control" readonly value="<?=htmlspecialchars($newCred['key_id'], ENT_QUOTES, 'UTF-8')?>"><button class="btn btn-outline-secondary" type="button" onclick="akCopy(\'akKey\')"><i class="fas fa-copy"></i></button></div>' +
    '<label class="small text-muted mb-1">API Secret (X-Api-Secret)</label>' +
    '<div class="input-group"><input id="akSecret" class="form-control" readonly value="<?=htmlspecialchars($newCred['secret'], ENT_QUOTES, 'UTF-8')?>"><button class="btn btn-outline-secondary" type="button" onclick="akCopy(\'akSecret\')"><i class="fas fa-copy"></i></button></div>' +
    '</div>',
  width: 600, confirmButtonText: 'I have copied them', allowOutsideClick: false
});
function akCopy(id){ var el=document.getElementById(id); el.select(); if(navigator.clipboard) navigator.clipboard.writeText(el.value); }
<?php endif; ?>
</script>

<?php $this->load->view('templates/customer/footer');?>
