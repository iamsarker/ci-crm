<?php $this->load->view('whmazadmin/include/header');?>
<link rel="stylesheet" href="<?=base_url()?>resources/assets/css/admin.manage_view.css">

<?php
	// Both audiences share this screen. $is_owner is true when a RESELLER is
	// looking at their own prices; false for platform staff, who additionally
	// get the reseller selector and the editable cost column.
	$types = array(
		'domain'   => array('label' => 'Domains',  'icon' => 'fa-globe'),
		'hosting'  => array('label' => 'Hosting',  'icon' => 'fa-server'),
		'software' => array('label' => 'Software', 'icon' => 'fa-box'),
	);
	$isDomain  = ($type === 'domain');
	$baseUrl   = base_url() . 'whmazadmin/reseller_pricing/index';
	$resParam  = $is_owner ? '' : '&reseller=' . intval($reseller_company_id);
?>

<div class="content content-fluid content-wrapper">
	<div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

		<div class="row mt-4">
			<div class="col-12">
				<div class="company-page-header">
					<div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
						<div>
							<h3><i class="fa fa-tags"></i> <?= $is_owner ? 'My Selling Prices' : 'Reseller Pricing' ?></h3>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
									<li class="breadcrumb-item active"><a href="#"><?= $is_owner ? 'My Selling Prices' : 'Reseller Pricing' ?></a></li>
								</ol>
							</nav>
						</div>
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
									<option value="0">— Select a reseller —</option>
									<?php foreach ($resellers as $r): ?>
									<option value="<?= intval($r['company_id']) ?>" <?= intval($r['company_id']) === intval($reseller_company_id) ? 'selected' : '' ?>>
										<?= htmlspecialchars($r['name']) ?>
									</option>
									<?php endforeach; ?>
								</select>
								<small class="text-muted">
									You are editing this reseller's <strong>selling prices</strong> and their
									<strong>negotiated cost</strong>. A blank cost falls back to the platform-wide
									cost set on the product, then to the discount on their profile.
								</small>
							</div>
						</div>
					</div>
					<?php endif; ?>

					<!-- Item type tabs -->
					<ul class="nav nav-tabs mb-3">
						<?php foreach ($types as $key => $meta): ?>
						<li class="nav-item">
							<a class="nav-link <?= $type === $key ? 'active' : '' ?>"
							   href="<?= $baseUrl ?>?type=<?= $key . $resParam ?>">
								<i class="fa <?= $meta['icon'] ?>"></i> <?= $meta['label'] ?>
							</a>
						</li>
						<?php endforeach; ?>
					</ul>

					<?php if (intval($reseller_company_id) <= 0): ?>
						<div class="alert alert-info mb-0">
							<i class="fa fa-info-circle"></i> Select a reseller above to edit their prices.
						</div>
					<?php elseif (empty($rows)): ?>
						<div class="alert alert-warning mb-0">
							<i class="fa fa-exclamation-triangle"></i> Nothing is priced in this category yet.
						</div>
					<?php else: ?>

					<div class="alert alert-secondary" style="font-size:13px;">
						<i class="fa fa-info-circle"></i>
						<strong>Cost</strong> is what <?= $is_owner ? 'you pay' : 'this reseller pays' ?> the platform.
						<strong><?= $is_owner ? 'My price' : 'Their price' ?></strong> is what
						<?= $is_owner ? 'your' : 'their' ?> customers are charged.
						A price can never be saved below its cost, and each component
						<?= $isDomain ? '(register, transfer, renewal) ' : '' ?>is floored independently.
						Leave a field blank to fall back to the platform's retail price.
					</div>

					<div class="table-responsive">
						<table class="table table-bordered table-sm align-middle">
							<thead class="bg-light">
								<tr>
									<th style="min-width:180px;">Item</th>
									<th style="min-width:90px;">Currency</th>
									<th class="text-center" style="min-width:110px;">Retail</th>
									<th class="text-center" style="min-width:110px;">Cost</th>
									<th class="text-center" style="min-width:120px;"><?= $isDomain ? 'Price (register)' : 'Price' ?></th>
									<?php if ($isDomain): ?>
									<th class="text-center" style="min-width:120px;">Price (transfer)</th>
									<th class="text-center" style="min-width:120px;">Price (renewal)</th>
									<?php endif; ?>
									<th style="min-width:90px;"></th>
								</tr>
							</thead>
							<tbody>
							<?php foreach ($rows as $row): ?>
								<tr data-pricing-id="<?= intval($row['pricing_id']) ?>">
									<td>
										<strong><?= htmlspecialchars($row['label']) ?></strong>
										<?php if (!empty($row['sub'])): ?>
										<br><small class="text-muted"><?= htmlspecialchars($row['sub']) ?></small>
										<?php endif; ?>
									</td>
									<td><?= htmlspecialchars($row['currency_symbol'] . ' ' . $row['currency_code']) ?></td>
									<td class="text-end text-muted"><?= number_format($row['base_price'], 2) ?></td>
									<td class="text-end">
										<?php if ($is_owner): ?>
											<strong><?= number_format($row['cost_price'], 2) ?></strong>
											<?php if ($isDomain): ?>
											<br><small class="text-muted">t <?= number_format($row['cost_transfer'], 2) ?>
											 / r <?= number_format($row['cost_renewal'], 2) ?></small>
											<?php endif; ?>
										<?php else: ?>
											<input type="number" step="0.01" min="0"
												   class="form-control form-control-sm text-end cost-input"
												   data-component="cost_price"
												   value="<?= htmlspecialchars($row['cost_price']) ?>"
												   title="Negotiated cost for this reseller">
											<?php if ($isDomain): ?>
											<input type="number" step="0.01" min="0"
												   class="form-control form-control-sm text-end mt-1 cost-input"
												   data-component="cost_transfer" placeholder="transfer"
												   value="<?= htmlspecialchars($row['cost_transfer']) ?>">
											<input type="number" step="0.01" min="0"
												   class="form-control form-control-sm text-end mt-1 cost-input"
												   data-component="cost_renewal" placeholder="renewal"
												   value="<?= htmlspecialchars($row['cost_renewal']) ?>">
											<?php endif; ?>
										<?php endif; ?>
									</td>
									<td>
										<input type="number" step="0.01" min="0"
											   class="form-control form-control-sm text-end price-input"
											   data-component="price"
											   data-floor="<?= htmlspecialchars($row['cost_price']) ?>"
											   value="<?= htmlspecialchars($row['my_price']) ?>"
											   placeholder="<?= number_format($row['base_price'], 2) ?>">
									</td>
									<?php if ($isDomain): ?>
									<td>
										<input type="number" step="0.01" min="0"
											   class="form-control form-control-sm text-end price-input"
											   data-component="transfer_price"
											   data-floor="<?= htmlspecialchars($row['cost_transfer']) ?>"
											   value="<?= htmlspecialchars($row['my_transfer']) ?>"
											   placeholder="same as register">
									</td>
									<td>
										<input type="number" step="0.01" min="0"
											   class="form-control form-control-sm text-end price-input"
											   data-component="renewal_price"
											   data-floor="<?= htmlspecialchars($row['cost_renewal']) ?>"
											   value="<?= htmlspecialchars($row['my_renewal']) ?>"
											   placeholder="same as register">
									</td>
									<?php endif; ?>
									<td>
										<button type="button" class="btn btn-sm btn-primary btn-save-row">
											<i class="fa fa-save"></i>
										</button>
									</td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					</div>

					<?php endif; ?>
				</div>
			</div>
		</div>

	</div><!-- container -->
</div><!-- content -->

<script>
(function () {
	var BASE      = '<?=base_url()?>whmazadmin/reseller_pricing/';
	var TYPE      = '<?= htmlspecialchars($type, ENT_QUOTES) ?>';
	var RESELLER  = <?= intval($reseller_company_id) ?>;
	var IS_OWNER  = <?= $is_owner ? 'true' : 'false' ?>;

	<?php if (!$is_owner): ?>
	$('#resellerPicker').on('change', function () {
		var id = parseInt($(this).val(), 10) || 0;
		window.location.href = '<?= $baseUrl ?>?type=' + TYPE + (id > 0 ? '&reseller=' + id : '');
	});
	<?php endif; ?>

	// The floor hint is cosmetic. Pricing_model::saveResellerRetail() is the
	// check that counts -- it runs server-side and per component, so a POST that
	// skips this form is validated by exactly the same code.
	$(document).on('input', '.price-input', function () {
		var floor = parseFloat($(this).data('floor'));
		var val   = parseFloat($(this).val());
		$(this).toggleClass('is-invalid', !isNaN(val) && !isNaN(floor) && val < floor);
	});

	$(document).on('click', '.btn-save-row', function () {
		var $btn = $(this), $tr = $btn.closest('tr');
		var payload = {
			type:               TYPE,
			pricing_id:         $tr.data('pricing-id'),
			reseller_company_id: RESELLER
		};
		$tr.find('.price-input').each(function () {
			payload[$(this).data('component')] = $(this).val();
		});
		payload['<?= $this->security->get_csrf_token_name() ?>'] = '<?= $this->security->get_csrf_hash() ?>';

		$btn.prop('disabled', true);
		$.post(BASE + 'save', payload, function (res) {
			$btn.prop('disabled', false);
			if (res && parseInt(res.code, 10) === 200) {
				Swal.fire({icon: 'success', title: 'Saved', text: res.msg || 'Price saved.', timer: 1600, showConfirmButton: false});
				$tr.find('.price-input').removeClass('is-invalid');
			} else {
				Swal.fire({icon: 'error', title: 'Not saved', text: (res && res.msg) || 'Could not save that price.'});
			}
		}, 'json').fail(function () {
			$btn.prop('disabled', false);
			Swal.fire({icon: 'error', title: 'Error', text: 'Request failed. Please retry.'});
		});
	});

	<?php if (!$is_owner): ?>
	// Cost edits save on blur -- there is no separate button for them, and a
	// cost change can cascade into the reseller's selling price, so the reply
	// reloads the row rather than pretending nothing else moved.
	$(document).on('change', '.cost-input', function () {
		var $tr = $(this).closest('tr');
		var payload = {
			type:                TYPE,
			pricing_id:          $tr.data('pricing-id'),
			reseller_company_id: RESELLER
		};
		$tr.find('.cost-input').each(function () {
			payload[$(this).data('component')] = $(this).val();
		});
		payload['<?= $this->security->get_csrf_token_name() ?>'] = '<?= $this->security->get_csrf_hash() ?>';

		$.post(BASE + 'save_cost', payload, function (res) {
			if (res && parseInt(res.code, 10) === 200) {
				var lifted = res.data && res.data.lifted;
				Swal.fire({
					icon: 'success', title: 'Cost saved',
					text: res.msg || 'Cost saved.',
					timer: lifted ? undefined : 1600,
					showConfirmButton: !!lifted
				}).then(function () { if (lifted) window.location.reload(); });
			} else {
				Swal.fire({icon: 'error', title: 'Not saved', text: (res && res.msg) || 'Could not save that cost.'});
			}
		}, 'json');
	});
	<?php endif; ?>
})();
</script>

<?php $this->load->view('whmazadmin/include/footer_script');?>
<?php $this->load->view('whmazadmin/include/footer');?>
