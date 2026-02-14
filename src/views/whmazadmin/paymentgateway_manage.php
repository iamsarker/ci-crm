<?php $this->load->view('whmazadmin/include/header'); ?>
<link rel="stylesheet" href="<?=base_url()?>resources/assets/css/admin.manage_view.css">

<div class="content content-fluid content-wrapper">
	<div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">
		<!-- Page Header -->
		<div class="order-page-header">
			<div class="d-flex justify-content-between align-items-center">
				<div>
					<h3><i class="fas fa-credit-card me-2"></i> Configure <?php echo htmlspecialchars($gateway['name'] ?? ''); ?></h3>
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb mb-0">
							<li class="breadcrumb-item"><a href="<?php echo base_url(); ?>whmazadmin">Dashboard</a></li>
							<li class="breadcrumb-item"><a href="<?php echo base_url(); ?>whmazadmin/paymentgateway">Payment Gateways</a></li>
							<li class="breadcrumb-item active"><a href="#"><?php echo htmlspecialchars($gateway['name'] ?? ''); ?></a></li>
						</ol>
					</nav>
				</div>
				<a href="<?php echo base_url(); ?>whmazadmin/paymentgateway" class="btn btn-back">
					<i class="fas fa-arrow-left me-1"></i> Back
				</a>
			</div>
		</div>

		<div class="manage-form-card">
			<form id="gateway-form" method="post">
					<input type="hidden" name="id" value="<?php echo $gateway['id']; ?>">
					<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

					<div class="row">
						<!-- Left Column -->
						<div class="col-md-6">
							<!-- Basic Settings -->
							<div class="order-card mb-4">
								<div class="card-header">
									<div class="header-icon"><i class="fas fa-info-circle"></i></div>
									<h6>Basic Settings</h6>
								</div>
								<div class="card-body">
									<div class="mb-3">
										<label class="form-label">Display Name</label>
										<input type="text" class="form-control" name="display_name"
											value="<?php echo htmlspecialchars($gateway['display_name'] ?? ''); ?>"
											placeholder="Name shown to customers">
									</div>
									<div class="mb-3">
										<label class="form-label">Description</label>
										<textarea class="form-control" name="description" rows="3"
											placeholder="Brief description shown during checkout"><?php echo htmlspecialchars($gateway['description'] ?? ''); ?></textarea>
									</div>
									<div class="row">
										<div class="col-md-6">
											<div class="mb-3">
												<label class="form-label">Sort Order</label>
												<input type="number" class="form-control" name="sort_order"
													value="<?php echo $gateway['sort_order']; ?>" min="0">
											</div>
										</div>
										<div class="col-md-6">
											<div class="mb-3">
												<label class="form-label">Status</label>
												<div class="form-check form-switch mt-2">
													<input class="form-check-input" type="checkbox" name="status"
														<?php echo $gateway['status'] == 1 ? 'checked' : ''; ?>>
													<label class="form-check-label">Enabled</label>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<!-- API Credentials (for online gateways) -->
							<?php if (in_array($gateway['gateway_code'], array('stripe', 'paypal', 'razorpay', 'paystack', 'sslcommerz'))): ?>
							<div class="order-card mb-4">
								<div class="card-header">
									<div class="header-icon"><i class="fas fa-key"></i></div>
									<h6>API Credentials</h6>
								</div>
								<div class="card-body">
									<div class="mb-3">
										<div class="form-check form-switch">
											<input class="form-check-input" type="checkbox" name="is_test_mode" id="testModeToggle"
												<?php echo $gateway['is_test_mode'] == 1 ? 'checked' : ''; ?>>
											<label class="form-check-label" for="testModeToggle">
												<i class="fas fa-flask"></i> Test/Sandbox Mode
											</label>
										</div>
										<small class="text-muted">Enable this for testing. Disable for live payments.</small>
									</div>

									<!-- Live Credentials -->
									<div class="live-credentials" style="<?php echo $gateway['is_test_mode'] ? 'display:none;' : ''; ?>">
										<h6 class="text-success"><i class="fas fa-check-circle"></i> Live Credentials</h6>
										<div class="mb-3">
											<label class="form-label">
												<?php echo $gateway['gateway_code'] === 'paypal' ? 'Client ID' : 'Public/Publishable Key'; ?>
											</label>
											<input type="text" class="form-control" name="public_key"
												value="<?php echo htmlspecialchars($gateway['public_key'] ?? ''); ?>">
										</div>
										<div class="mb-3">
											<label class="form-label">
												<?php echo $gateway['gateway_code'] === 'paypal' ? 'Client Secret' : 'Secret Key'; ?>
											</label>
											<input type="password" class="form-control" name="secret_key"
												value="<?php echo htmlspecialchars($gateway['secret_key'] ?? ''); ?>">
										</div>
									</div>

									<!-- Test Credentials -->
									<div class="test-credentials" style="<?php echo $gateway['is_test_mode'] ? '' : 'display:none;'; ?>">
										<h6 class="text-warning"><i class="fas fa-flask"></i> Test/Sandbox Credentials</h6>
										<div class="mb-3">
											<label class="form-label">
												<?php echo $gateway['gateway_code'] === 'paypal' ? 'Sandbox Client ID' : 'Test Public Key'; ?>
											</label>
											<input type="text" class="form-control" name="test_public_key"
												value="<?php echo htmlspecialchars($gateway['test_public_key'] ?? ''); ?>">
										</div>
										<div class="mb-3">
											<label class="form-label">
												<?php echo $gateway['gateway_code'] === 'paypal' ? 'Sandbox Client Secret' : 'Test Secret Key'; ?>
											</label>
											<input type="password" class="form-control" name="test_secret_key"
												value="<?php echo htmlspecialchars($gateway['test_secret_key'] ?? ''); ?>">
										</div>
									</div>

									<div class="mb-3">
										<label class="form-label">
											<?php echo $gateway['gateway_code'] === 'paypal' ? 'Webhook ID' : 'Webhook Secret'; ?>
										</label>
										<input type="text" class="form-control" name="webhook_secret"
											value="<?php echo htmlspecialchars($gateway['webhook_secret'] ?? ''); ?>">
										<small class="text-muted">Required for receiving payment notifications</small>
									</div>

									<div class="alert alert-info">
										<strong>Webhook URL:</strong><br>
										<code><?php echo base_url() . 'webhook/' . $gateway['gateway_code']; ?></code>
										<button type="button" class="btn btn-sm btn-outline-primary float-end copy-webhook-url">
											<i class="fas fa-copy"></i> Copy
										</button>
									</div>
								</div>
							</div>
							<?php endif; ?>

							<!-- Bank Transfer Details -->
							<?php if ($gateway['gateway_code'] === 'bank_transfer'): ?>
							<div class="order-card mb-4">
								<div class="card-header">
									<div class="header-icon"><i class="fas fa-university"></i></div>
									<h6>Bank Account Details</h6>
								</div>
								<div class="card-body">
									<div class="mb-3">
										<label class="form-label">Bank Name</label>
										<input type="text" class="form-control" name="bank_name"
											value="<?php echo htmlspecialchars($gateway['bank_name'] ?? ''); ?>">
									</div>
									<div class="mb-3">
										<label class="form-label">Account Name</label>
										<input type="text" class="form-control" name="account_name"
											value="<?php echo htmlspecialchars($gateway['account_name'] ?? ''); ?>">
									</div>
									<div class="mb-3">
										<label class="form-label">Account Number</label>
										<input type="text" class="form-control" name="account_number"
											value="<?php echo htmlspecialchars($gateway['account_number'] ?? ''); ?>">
									</div>
									<div class="row">
										<div class="col-md-6">
											<div class="mb-3">
												<label class="form-label">Routing/Sort Code</label>
												<input type="text" class="form-control" name="routing_number"
													value="<?php echo htmlspecialchars($gateway['routing_number'] ?? ''); ?>">
											</div>
										</div>
										<div class="col-md-6">
											<div class="mb-3">
												<label class="form-label">SWIFT/BIC Code</label>
												<input type="text" class="form-control" name="swift_code"
													value="<?php echo htmlspecialchars($gateway['swift_code'] ?? ''); ?>">
											</div>
										</div>
									</div>
									<div class="mb-3">
										<label class="form-label">IBAN</label>
										<input type="text" class="form-control" name="iban"
											value="<?php echo htmlspecialchars($gateway['iban'] ?? ''); ?>">
									</div>
								</div>
							</div>
							<?php endif; ?>

							<!-- Instructions (for manual/bank) -->
							<?php if (in_array($gateway['gateway_code'], array('manual', 'bank_transfer'))): ?>
							<div class="order-card mb-4">
								<div class="card-header">
									<div class="header-icon"><i class="fas fa-align-left"></i></div>
									<h6>Payment Instructions</h6>
								</div>
								<div class="card-body">
									<textarea class="form-control" name="instructions" rows="5"
										placeholder="Enter payment instructions to display to customers"><?php echo htmlspecialchars($gateway['instructions'] ?? ''); ?></textarea>
								</div>
							</div>
							<?php endif; ?>
						</div>

						<!-- Right Column -->
						<div class="col-md-6">
							<!-- Currency & Limits -->
							<div class="order-card mb-4">
								<div class="card-header">
									<div class="header-icon"><i class="fas fa-coins"></i></div>
									<h6>Currency & Limits</h6>
								</div>
								<div class="card-body">
									<div class="mb-3">
										<label class="form-label">Supported Currencies</label>
										<input type="text" class="form-control" name="supported_currencies"
											value="<?php echo htmlspecialchars($gateway['supported_currencies'] ?? ''); ?>"
											placeholder="USD,EUR,GBP (comma-separated)">
										<small class="text-muted">Leave empty to support all currencies</small>
									</div>
									<div class="row">
										<div class="col-md-6">
											<div class="mb-3">
												<label class="form-label">Minimum Amount</label>
												<input type="number" class="form-control" name="min_amount"
													value="<?php echo $gateway['min_amount']; ?>" step="0.01" min="0">
											</div>
										</div>
										<div class="col-md-6">
											<div class="mb-3">
												<label class="form-label">Maximum Amount</label>
												<input type="number" class="form-control" name="max_amount"
													value="<?php echo $gateway['max_amount']; ?>" step="0.01" min="0">
												<small class="text-muted">0 = No limit</small>
											</div>
										</div>
									</div>
								</div>
							</div>

							<!-- Processing Fees -->
							<div class="order-card mb-4">
								<div class="card-header">
									<div class="header-icon"><i class="fas fa-percentage"></i></div>
									<h6>Processing Fees</h6>
								</div>
								<div class="card-body">
									<div class="mb-3">
										<label class="form-label">Fee Type</label>
										<select class="form-select" name="fee_type" id="feeType">
											<option value="none" <?php echo $gateway['fee_type'] === 'none' ? 'selected' : ''; ?>>No Fee</option>
											<option value="fixed" <?php echo $gateway['fee_type'] === 'fixed' ? 'selected' : ''; ?>>Fixed Amount</option>
											<option value="percentage" <?php echo $gateway['fee_type'] === 'percentage' ? 'selected' : ''; ?>>Percentage</option>
											<option value="both" <?php echo $gateway['fee_type'] === 'both' ? 'selected' : ''; ?>>Fixed + Percentage</option>
										</select>
									</div>
									<div class="row fee-fields" style="<?php echo $gateway['fee_type'] === 'none' ? 'display:none;' : ''; ?>">
										<div class="col-md-6">
											<div class="mb-3">
												<label class="form-label">Fixed Fee</label>
												<input type="number" class="form-control" name="fee_fixed"
													value="<?php echo $gateway['fee_fixed']; ?>" step="0.01" min="0">
											</div>
										</div>
										<div class="col-md-6">
											<div class="mb-3">
												<label class="form-label">Percentage Fee (%)</label>
												<input type="number" class="form-control" name="fee_percent"
													value="<?php echo $gateway['fee_percent']; ?>" step="0.01" min="0" max="100">
											</div>
										</div>
									</div>
									<div class="mb-3 fee-fields" style="<?php echo $gateway['fee_type'] === 'none' ? 'display:none;' : ''; ?>">
										<label class="form-label">Fee Paid By</label>
										<select class="form-select" name="fee_bearer">
											<option value="merchant" <?php echo $gateway['fee_bearer'] === 'merchant' ? 'selected' : ''; ?>>Merchant (absorbed)</option>
											<option value="customer" <?php echo $gateway['fee_bearer'] === 'customer' ? 'selected' : ''; ?>>Customer (added to total)</option>
										</select>
									</div>
								</div>
							</div>

							<!-- Extra Config (for SSLCommerz, etc.) -->
							<?php if (!empty($gateway['extra_config']) || in_array($gateway['gateway_code'], array('sslcommerz'))): ?>
							<div class="order-card mb-4">
								<div class="card-header">
									<div class="header-icon"><i class="fas fa-sliders-h"></i></div>
									<h6>Additional Configuration</h6>
								</div>
								<div class="card-body">
									<div class="mb-3">
										<label class="form-label">Extra Config (JSON)</label>
										<textarea class="form-control" name="extra_config" rows="6"
											placeholder='{"key": "value"}'><?php echo is_array($gateway['extra_config']) ? json_encode($gateway['extra_config'], JSON_PRETTY_PRINT) : htmlspecialchars($gateway['extra_config'] ?? ''); ?></textarea>
										<small class="text-muted">Gateway-specific settings in JSON format</small>
									</div>
								</div>
							</div>
							<?php endif; ?>
						</div>
					</div>

					<div class="text-end mt-4">
						<button type="submit" class="btn-create-order">
							<i class="fas fa-save me-2"></i> Save Configuration
						</button>
					</div>
				</form>
		</div>
    </div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script');?>

<script>
$(document).ready(function() {
    // Toggle test/live credentials
    $('#testModeToggle').on('change', function() {
        if ($(this).is(':checked')) {
            $('.test-credentials').show();
            $('.live-credentials').hide();
        } else {
            $('.test-credentials').hide();
            $('.live-credentials').show();
        }
    });

    // Toggle fee fields
    $('#feeType').on('change', function() {
        if ($(this).val() === 'none') {
            $('.fee-fields').hide();
        } else {
            $('.fee-fields').show();
        }
    });

    // Copy webhook URL
    $('.copy-webhook-url').on('click', function() {
        var url = $(this).parent().find('code').text();
        navigator.clipboard.writeText(url).then(function() {
			toastSuccess('Webhook URL copied to clipboard');
        });
    });

    // Form submission
    $('#gateway-form').on('submit', function(e) {
        e.preventDefault();

        var btn = $(this).find('button[type="submit"]');
        var originalHtml = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);

        $.ajax({
            url: '<?php echo base_url(); ?>whmazadmin/paymentgateway/save',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastSuccess(response.message);
                } else {
                    toastError(response.message);
                }
                // Update CSRF token if returned
                if (response.csrf_token) {
                    $('input[name="<?php echo $this->security->get_csrf_token_name(); ?>"]').val(response.csrf_token);
                }
            },
            error: function() {
				toastError('Failed to save configuration');
            },
            complete: function() {
                btn.html(originalHtml).prop('disabled', false);
            }
        });
    });
});
</script>

<?php $this->load->view('whmazadmin/include/footer'); ?>
