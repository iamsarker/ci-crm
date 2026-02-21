<?php $this->load->view('whmazadmin/include/header'); ?>

<div class="content content-fluid content-wrapper">
	<div class="container-fluid pd-x-20 pd-lg-x-30 pd-xl-x-40">

		<p class="mt-4">&nbsp;</p>

		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-header">
						<h4 class="card-title"><i class="fas fa-credit-card"></i> Payment Gateways</h4>
						<p class="text-muted mb-0">Configure payment methods for your customers</p>
					</div>
					<div class="card-body">
						<div class="table-responsive">
							<table class="table table-hover">
								<thead>
									<tr>
										<th style="width: 50px;">Order</th>
										<th>Gateway</th>
										<th>Type</th>
										<th>Mode</th>
										<th>Currencies</th>
										<th>Status</th>
										<th style="width: 200px;">Actions</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($gateways as $gateway): ?>
									<tr>
										<td><?php echo $gateway['sort_order']; ?></td>
										<td>
											<div class="d-flex align-items-center">
												<div class="gateway-icon me-3" style="width: 40px; height: 40px; background: #f5f5f5; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
													<?php
													$icon = 'fa-money-bill';
													switch ($gateway['gateway_code']) {
														case 'stripe': $icon = 'fa-credit-card'; break;
														case 'paypal': $icon = 'fa-paypal'; break;
														case 'razorpay': $icon = 'fa-rupee-sign'; break;
														case 'paystack': $icon = 'fa-credit-card'; break;
														case 'sslcommerz': $icon = 'fa-mobile-alt'; break;
														case 'bank_transfer': $icon = 'fa-university'; break;
														case 'manual': $icon = 'fa-hand-holding-usd'; break;
													}
													?>
													<i class="fas <?php echo $icon; ?>"></i>
												</div>
												<div>
													<strong><?php echo htmlspecialchars($gateway['name']); ?></strong>
													<?php if (!empty($gateway['display_name']) && $gateway['display_name'] !== $gateway['name']): ?>
													<br><small class="text-muted"><?php echo htmlspecialchars($gateway['display_name']); ?></small>
													<?php endif; ?>
												</div>
											</div>
										</td>
										<td>
											<span class="badge bg-secondary">
												<?php echo isset($gateway_types[$gateway['gateway_type']]) ? $gateway_types[$gateway['gateway_type']] : $gateway['gateway_type']; ?>
											</span>
										</td>
										<td>
											<?php if ($gateway['gateway_type'] !== 'manual' && $gateway['gateway_type'] !== 'bank_transfer'): ?>
											<?php if ($gateway['is_test_mode']): ?>
											<span class="badge bg-warning text-dark"><i class="fas fa-flask"></i> Test</span>
											<?php else: ?>
											<span class="badge bg-success"><i class="fas fa-check"></i> Live</span>
											<?php endif; ?>
											<?php else: ?>
											<span class="text-muted">-</span>
											<?php endif; ?>
										</td>
										<td>
											<small><?php echo htmlspecialchars(substr($gateway['supported_currencies'], 0, 30)); ?><?php echo strlen($gateway['supported_currencies']) > 30 ? '...' : ''; ?></small>
										</td>
										<td>
											<div class="form-check form-switch">
												<input class="form-check-input gateway-status-toggle" type="checkbox"
													data-id="<?php echo $gateway['id']; ?>"
													<?php echo $gateway['status'] == 1 ? 'checked' : ''; ?>>
											</div>
										</td>
										<td>
											<a href="<?php echo base_url(); ?>whmazadmin/paymentgateway/manage/<?php echo $gateway['id']; ?>" class="btn btn-sm btn-primary">
												<i class="fas fa-cog"></i> Configure
											</a>
											<?php if (in_array($gateway['gateway_code'], array('stripe', 'paypal'))): ?>
											<button type="button" class="btn btn-sm btn-outline-secondary test-connection" data-id="<?php echo $gateway['id']; ?>">
												<i class="fas fa-plug"></i> Test
											</button>
											<?php endif; ?>
										</td>
									</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Quick Links -->
		<div class="row mt-4">
			<div class="col-md-6">
				<div class="card">
					<div class="card-header">
						<h5 class="card-title mb-0"><i class="fas fa-exchange-alt"></i> Recent Transactions</h5>
					</div>
					<div class="card-body">
						<a href="<?php echo base_url(); ?>whmazadmin/paymentgateway/transactions" class="btn btn-outline-primary">
							View All Transactions
						</a>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="card">
					<div class="card-header">
						<h5 class="card-title mb-0"><i class="fas fa-webhook"></i> Webhook Logs</h5>
					</div>
					<div class="card-body">
						<a href="<?php echo base_url(); ?>whmazadmin/paymentgateway/webhooks" class="btn btn-outline-primary">
							View Webhook Logs
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script');?>

<script>
$(document).ready(function() {
    // Toggle gateway status
    $('.gateway-status-toggle').on('change', function(e) {
        e.preventDefault();
        var checkbox = $(this);
        var id = checkbox.data('id');
        var status = checkbox.is(':checked') ? 1 : 0;
        var actionText = status ? 'enable' : 'disable';
        var gatewayName = checkbox.closest('tr').find('strong').text();

        // Revert checkbox state until confirmed
        checkbox.prop('checked', !status);

        Swal.fire({
            title: 'Confirm Action',
            text: 'Are you sure you want to ' + actionText + ' "' + gatewayName + '" payment gateway?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: status ? '#28a745' : '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: status ? '<i class="fas fa-check"></i> Yes, Enable' : '<i class="fas fa-ban"></i> Yes, Disable',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Processing...',
                    text: 'Updating gateway status',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '<?php echo base_url(); ?>whmazadmin/paymentgateway/toggle_status',
                    type: 'POST',
                    data: {
                        id: id,
                        status: status,
                        '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            checkbox.prop('checked', status);
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to update gateway status. Please try again.'
                        });
                    }
                });
            }
        });
    });

    // Test connection
    $('.test-connection').on('click', function() {
        var btn = $(this);
        var id = btn.data('id');
        var originalHtml = btn.html();

        btn.html('<i class="fas fa-spinner fa-spin"></i> Testing...').prop('disabled', true);

        $.ajax({
            url: '<?php echo base_url(); ?>whmazadmin/paymentgateway/test_connection/' + id,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Connection test failed');
            },
            complete: function() {
                btn.html(originalHtml).prop('disabled', false);
            }
        });
    });
});
</script>

<?php $this->load->view('whmazadmin/include/footer'); ?>
