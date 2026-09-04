<?php $this->load->view('whmazadmin/include/header');?>
<link rel="stylesheet" href="<?=base_url()?>resources/assets/css/admin.manage_view.css">

<?php
	// Shared by both audiences. $is_owner is true when a RESELLER is looking at
	// their own wallet; false for platform staff, who additionally get the
	// reseller selector and the manual-adjustment panel.
	$title    = $is_owner ? 'My Account Credit' : 'Reseller Wallet';
	$sym      = !empty($wallet['currency_symbol']) ? $wallet['currency_symbol'] : '';
	$code     = !empty($wallet['currency_code'])   ? $wallet['currency_code']   : '';
	$balance  = !empty($wallet) ? (float) $wallet['credit_balance'] : 0.00;
	$limit    = !empty($wallet) ? (float) $wallet['credit_limit']   : 0.00;
	$available = $balance + $limit;          // what can still be spent
	$negative  = $balance < 0;
	$indexUrl  = base_url() . 'whmazadmin/reseller_wallet/index';
	$resParam  = $is_owner ? '' : '?reseller=' . (int) $reseller_company_id;

	$totalPages = $page_size > 0 ? (int) ceil($total_rows / $page_size) : 1;

	$typeMeta = array(
		'topup'      => array('Top-up',     'success',   'fa-plus-circle'),
		'debit'      => array('Order cost', 'secondary', 'fa-minus-circle'),
		'refund'     => array('Refund',     'info',      'fa-undo'),
		'adjustment' => array('Adjustment', 'warning',   'fa-sliders-h'),
	);
?>

<div class="content content-fluid content-wrapper">
	<div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

		<div class="row mt-4">
			<div class="col-12">
				<div class="company-page-header">
					<div>
						<h3><i class="fa fa-wallet"></i> <?= $title ?></h3>
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb mb-0">
								<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
								<li class="breadcrumb-item active"><a href="#"><?= $title ?></a></li>
							</ol>
						</nav>
					</div>
				</div>
			</div>
		</div>

		<div class="row mt-4">
			<div class="col-12">
				<div class="manage-form-card">

				<?php if (!$is_owner): ?>
					<!-- Reseller selector (platform staff only) -->
					<div class="company-form-section">
						<div class="section-title"><i class="fa fa-handshake"></i> Reseller</div>
						<div class="row">
							<div class="col-md-6">
								<select class="form-control" id="resellerPicker">
									<option value="0">&mdash; Select a reseller &mdash;</option>
									<?php foreach ($resellers as $r): ?>
									<option value="<?= (int) $r['company_id'] ?>" <?= (int) $r['company_id'] === (int) $reseller_company_id ? 'selected' : '' ?>>
										<?= htmlspecialchars($r['name'], ENT_QUOTES, 'UTF-8') ?>
									</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
					</div>
				<?php endif; ?>

				<?php if (empty($wallet)): ?>
					<div class="alert alert-info mb-0">
						<i class="fa fa-info-circle me-1"></i>
						<?= $is_owner
							? 'No wallet is configured for your account yet. Please contact us.'
							: 'Select a reseller to see their credit account.' ?>
					</div>
				<?php else: ?>

					<?php if (!empty($drift)): ?>
					<?php /* Should be impossible: the ledger row and the cached balance are
					        written in one transaction under one lock. If it happens, the
					        number below cannot be trusted, and saying so is more useful
					        than rendering it as fact. */ ?>
					<div class="alert alert-danger">
						<i class="fa fa-exclamation-triangle me-1"></i>
						<strong>Balance does not reconcile with the ledger.</strong>
						Cached <?= number_format((float) $drift[0]['cached_balance'], 2) ?>
						vs ledger <?= number_format((float) $drift[0]['ledger_sum'], 2) ?>
						(difference <?= number_format((float) $drift[0]['drift'], 2) ?>).
						Something has written <code>credit_balance</code> directly. Do not act on the figure below.
					</div>
					<?php endif; ?>

					<!-- Balance -->
					<div class="row g-3 mb-4">
						<div class="col-md-4">
							<div class="card h-100">
								<div class="card-body">
									<div class="text-muted small text-uppercase">Current Balance</div>
									<div class="h3 mb-0 <?= $negative ? 'text-danger' : 'text-success' ?>">
										<?= $sym ?><?= number_format($balance, 2) ?>
										<small class="text-muted"><?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?></small>
									</div>
									<?php if ($negative): ?>
										<div class="small text-danger mt-1">
											<i class="fa fa-exclamation-circle"></i> Overdrawn &mdash; new orders are held until this is cleared.
										</div>
									<?php endif; ?>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="card h-100">
								<div class="card-body">
									<div class="text-muted small text-uppercase">Credit Limit</div>
									<div class="h3 mb-0"><?= $sym ?><?= number_format($limit, 2) ?></div>
									<div class="small text-muted mt-1">
										<?= $limit > 0 ? 'Permitted overdraft.' : 'No overdraft permitted.' ?>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="card h-100">
								<div class="card-body">
									<div class="text-muted small text-uppercase">Available to Spend</div>
									<div class="h3 mb-0 <?= $available <= 0 ? 'text-danger' : '' ?>">
										<?= $sym ?><?= number_format($available, 2) ?>
									</div>
									<div class="small text-muted mt-1">Balance plus credit limit.</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Held orders -->
					<?php if (!empty($held)): ?>
					<div class="company-form-section">
						<div class="section-title text-danger"><i class="fa fa-pause-circle"></i> Orders Held for Credit</div>
						<p class="text-muted small">
							These invoices are <strong>paid</strong> but their services have not been set up, because the
							account could not cover their cost. They are held, not cancelled &mdash; top up and they are
							provisioned automatically on the next scheduled run.
						</p>
						<div class="table-responsive">
							<table class="table table-sm align-middle mb-0">
								<thead><tr><th>Invoice</th><th></th></tr></thead>
								<tbody>
								<?php foreach ($held as $h): ?>
									<tr>
										<td>#<?= htmlspecialchars($h['invoice_no'], ENT_QUOTES, 'UTF-8') ?></td>
										<td class="text-end">
											<a class="btn btn-sm btn-outline-secondary"
											   href="<?=base_url()?>whmazadmin/provisioning/index">View provisioning log</a>
										</td>
									</tr>
								<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>
					<?php endif; ?>

					<!-- Top up -->
					<div class="company-form-section">
						<div class="section-title"><i class="fa fa-plus-circle"></i> Add Credit</div>
						<?php if (empty($wallet['currency_id'])): ?>
							<div class="alert alert-warning mb-0">
								<i class="fa fa-exclamation-triangle me-1"></i>
								No credit currency is set on this reseller profile, so a top-up invoice cannot be raised.
								<?= $is_owner ? 'Please contact us.' : 'Set one on Reseller Management first.' ?>
							</div>
						<?php else: ?>
						<form method="post" action="<?=base_url()?>whmazadmin/reseller_wallet/topup">
							<?= form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()) ?>
							<input type="hidden" name="reseller_company_id" value="<?= (int) $reseller_company_id ?>" />
							<div class="row g-2 align-items-end">
								<div class="col-md-4">
									<label class="form-label" for="amount">Amount (<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>)</label>
									<input type="number" step="0.01" min="0.01" name="amount" id="amount"
									       class="form-control" placeholder="0.00" required />
								</div>
								<div class="col-md-4">
									<button type="submit" class="btn btn-primary">
										<i class="fa fa-file-invoice me-1"></i>
										<?= $is_owner ? 'Raise Invoice &amp; Pay' : 'Raise Top-Up Invoice' ?>
									</button>
								</div>
							</div>
							<small class="text-muted d-block mt-2">
								<?php if ($is_owner): ?>
									This raises an invoice and takes you straight to the payment page. Your balance is
									credited once the payment is confirmed.
								<?php else: ?>
									Raises an invoice against this reseller. The wallet is credited when it is paid
									&mdash; including when you mark it paid for a bank transfer.
								<?php endif; ?>
							</small>
						</form>
						<?php endif; ?>
					</div>

					<!-- Manual adjustment (platform staff only) -->
					<?php if (!$is_owner): ?>
					<div class="company-form-section">
						<div class="section-title"><i class="fa fa-sliders-h"></i> Manual Adjustment</div>
						<form method="post" action="<?=base_url()?>whmazadmin/reseller_wallet/adjust">
							<?= form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()) ?>
							<input type="hidden" name="reseller_company_id" value="<?= (int) $reseller_company_id ?>" />
							<div class="row g-2 align-items-end">
								<div class="col-md-2">
									<label class="form-label" for="adj_direction">Direction</label>
									<select name="direction" id="adj_direction" class="form-select">
										<option value="credit">Credit (+)</option>
										<option value="debit">Debit (&minus;)</option>
									</select>
								</div>
								<div class="col-md-2">
									<label class="form-label" for="adj_amount">Amount</label>
									<input type="number" step="0.01" min="0.01" name="amount" id="adj_amount"
									       class="form-control" placeholder="0.00" required />
								</div>
								<div class="col-md-5">
									<label class="form-label" for="adj_note">Reason</label>
									<input type="text" name="note" id="adj_note" class="form-control" maxlength="255"
									       placeholder="e.g. refund for failed registration" required />
								</div>
								<div class="col-md-3">
									<button type="submit" class="btn btn-warning">
										<i class="fa fa-save me-1"></i> Post Adjustment
									</button>
								</div>
							</div>
							<small class="text-muted d-block mt-2">
								Writes an audited ledger entry. The reason is required and is shown on the reseller's
								statement &mdash; this replaces the old free-text credit balance field, which recorded
								neither who changed it nor why.
							</small>
						</form>
					</div>
					<?php endif; ?>

					<!-- Statement -->
					<div class="company-form-section">
						<div class="section-title"><i class="fa fa-list"></i> Statement</div>
						<?php if (empty($statement)): ?>
							<p class="text-muted mb-0">No transactions yet.</p>
						<?php else: ?>
						<div class="table-responsive">
							<table class="table table-striped table-sm align-middle mb-0">
								<thead>
									<tr>
										<th>Date</th>
										<th>Type</th>
										<th>Description</th>
										<th class="text-end">Amount</th>
										<th class="text-end">Balance</th>
									</tr>
								</thead>
								<tbody>
								<?php foreach ($statement as $t):
									$meta = isset($typeMeta[$t['txn_type']])
										? $typeMeta[$t['txn_type']]
										: array(ucfirst($t['txn_type']), 'secondary', 'fa-circle');
									$amt = (float) $t['amount'];
								?>
									<tr>
										<td class="text-nowrap"><?= !empty($t['inserted_on']) ? date('d M Y H:i', strtotime($t['inserted_on'])) : '-' ?></td>
										<td>
											<span class="badge bg-<?= $meta[1] ?>">
												<i class="fa <?= $meta[2] ?> me-1"></i><?= $meta[0] ?>
											</span>
										</td>
										<td><?= htmlspecialchars((string) $t['description'], ENT_QUOTES, 'UTF-8') ?></td>
										<td class="text-end <?= $amt < 0 ? 'text-danger' : 'text-success' ?>">
											<?= $amt < 0 ? '&minus;' : '+' ?><?= $sym ?><?= number_format(abs($amt), 2) ?>
										</td>
										<td class="text-end"><?= $sym ?><?= number_format((float) $t['balance_after'], 2) ?></td>
									</tr>
								<?php endforeach; ?>
								</tbody>
							</table>
						</div>

						<?php if ($totalPages > 1): ?>
						<nav class="mt-3">
							<ul class="pagination pagination-sm mb-0">
								<?php for ($p = 1; $p <= $totalPages; $p++): ?>
								<li class="page-item <?= $p === (int) $page ? 'active' : '' ?>">
									<a class="page-link" href="<?= $indexUrl . ($is_owner ? '?' : $resParam . '&') . 'page=' . $p ?>"><?= $p ?></a>
								</li>
								<?php endfor; ?>
							</ul>
						</nav>
						<?php endif; ?>
						<?php endif; ?>
					</div>

				<?php endif; /* wallet exists */ ?>

				</div>
			</div>
		</div>
	</div>
</div>

<script>
(function () {
	var picker = document.getElementById('resellerPicker');
	if (!picker) return;
	picker.addEventListener('change', function () {
		var id = parseInt(picker.value, 10) || 0;
		window.location.href = '<?=base_url()?>whmazadmin/reseller_wallet/index' + (id > 0 ? '?reseller=' + id : '');
	});
})();
</script>

<?php $this->load->view('whmazadmin/include/footer_script');?>
<?php $this->load->view('whmazadmin/include/footer');?>
