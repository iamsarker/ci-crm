<div class="card card-widget card-contacts mt-3">
  <div class="card-header">
    <h6 class="card-title mg-b-0"><i class="fa fa-globe"></i> &nbsp;Shortcut</h6>
    <nav class="nav">

    </nav>
  </div><!-- card-header -->
  <ul class="list-group list-group-flush">
    <li class="list-group-item">
      <a title="Send EPP Code" href="javascript:void(0);" id="btnSendEppCode" class="nav-sub-link"><i data-feather="mail"></i>&nbsp;Send EPP Code</a>
      <div id="eppCodeStatus" class="mt-2" style="display: none;"></div>
    </li>
    <li class="list-group-item">
      <a href="<?=base_url()?>clientarea/domain_cancellation_request/<?= !empty($detail['order_id']) ? $detail['order_id'] : '0'?>/<?= !empty($detail['id']) ? $detail['id']: '0'?>" target="_blank" class="nav-sub-link text-danger"><i data-feather="x-circle"></i>&nbsp;Request Cancellation</a>
    </li>
  </ul>
</div>

<script>
$(function(){
    $('#btnSendEppCode').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var $status = $('#eppCodeStatus');
        var domainId = $('#domain_id').val();

        if (!domainId) {
            alert('Domain ID not found');
            return;
        }

        if (!confirm('Send EPP/Authorization code to your registered email address?')) {
            return;
        }

        $btn.addClass('disabled').html('<i class="fa fa-spinner fa-spin"></i>&nbsp;Sending...');
        $status.hide();

        var csrfName = $('meta[name="csrf-token-name"]').attr('content');
        var csrfToken = $('meta[name="csrf-token-hash"]').attr('content');

        var postData = { domain_id: domainId };
        postData[csrfName] = csrfToken;

        $.ajax({
            url: BASE_URL + 'clientarea/send_epp_code',
            type: 'POST',
            data: postData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $status.removeClass('text-danger').addClass('text-success')
                           .html('<small><i class="fa fa-check-circle"></i> ' + response.msg + '</small>')
                           .fadeIn();
                } else {
                    $status.removeClass('text-success').addClass('text-danger')
                           .html('<small><i class="fa fa-exclamation-circle"></i> ' + response.msg + '</small>')
                           .fadeIn();
                }
            },
            error: function() {
                $status.removeClass('text-success').addClass('text-danger')
                       .html('<small><i class="fa fa-exclamation-circle"></i> An error occurred. Please try again.</small>')
                       .fadeIn();
            },
            complete: function() {
                $btn.removeClass('disabled').html('<i data-feather="mail"></i>&nbsp;Send EPP Code');
                feather.replace();
            }
        });
    });
});
</script>
