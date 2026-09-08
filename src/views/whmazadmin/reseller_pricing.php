<?php $this->load->view('whmazadmin/include/header');?>
<link rel="stylesheet" href="<?=base_url()?>resources/assets/css/admin.manage_view.css">

<?php
	// Both audiences share this screen. $is_owner is true when a RESELLER is
	// looking at their own numbers -- for them the whole page is READ-ONLY.
	// Platform staff additionally get the reseller selector and the one
	// editable column on the page: the reseller's negotiated cost.
	//
	// v2.1: the reseller's own selling price is gone. Their customer pays the
	// platform's retail price; the reseller's margin is retail minus cost and
	// is settled through the wallet.
	$types = array(
		'domain'   => array('label' => 'Domains',  'icon' => 'fa-globe'),
		'hosting'  => array('label' => 'Hosting',  'icon' => 'fa-server'),
		'software' => array('label' => 'Software', 'icon' => 'fa-box'),
	);
	$isDomain  = ($type === 'domain');
	$baseUrl   = base_url() . 'whmazadmin/reseller_pricing/index';
	$resParam  = $is_owner ? '' : '&reseller=' . intval($reseller_company_id);
	$pageTitle = $is_owner ? 'My Wholesale Pricing' : 'Reseller Pricing';

	// sys_cnf group RESELLER -- the bottom rung of the cost ladder.
	$gdValue = isset($global_discount['reseller_default_discount_value'])
		? (float) $global_discount['reseller_default_discount_value'] : 0.0;
	$gdType  = isset($global_discount['reseller_default_discount_type'])
		? strtolower(trim($global_discount['reseller_default_discount_type'])) : 'percent';
	// Mirror Pricing_model::globalDiscount()'s normalisation so the banner can
	// never claim a percentage the resolver would read as a fixed amount.
	$gdIsFixed = ($gdType === 'fixed' || $gdType === 'flat');

	// Margin cell: negative is shown in red rather than clamped away.
	if (!function_exists('rp_margin_cell')) {
		function rp_margin_cell($amount) {
			$cls = ($amount < 0) ? 'text-danger fw-bold' : (($amount > 0) ? 'text-success' : 'text-muted');
			return '<span class="' . $cls . '">' . number_format($amount, 2) . '</span>';
		}
	}
?>

<div class="content content-fluid content-wrapper">
	<div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

		<div class="row mt-4">
			<div class="col-12">
				<div class="company-page-header">
					<div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
						<div>
							<h3><i class="fa fa-tags"></i> <?= $pageTitle ?></h3>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
									<li class="breadcrumb-item active"><a href="#"><?= $pageTitle ?></a></li>
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
									You are setting this reseller's <strong>negotiated cost</strong>. Clearing a cost
									falls back to the platform-wide cost set on the product, then to the discount on
									their profile, then to the global default below.
								</small>
							</div>
						</div>
					</div>
					<?php endif; ?>

					<!-- Global default reseller discount (sys_cnf group RESELLER) -->
					<div class="alert <?= $gdValue > 0 ? 'alert-info' : 'alert-light border' ?>" style="font-size:13px;">
						<i class="fa fa-globe"></i>
						<strong>Global default reseller discount:</strong>
						<?php if ($gdValue > 0): ?>
							<?= $gdIsFixed
								? number_format($gdValue, 2) . ' off every item'
								: rtrim(rtrim(number_format($gdValue, 2), '0'), '.') . '% off retail' ?>
							<span class="text-muted">— applied to any item with no cost of its own and no discount on
							<?= $is_owner ? 'your' : 'the reseller\'s' ?> profile.</span>
						<?php else: ?>
							<span class="text-muted">not set — items with no cost of their own cost the full retail price.</span>
						<?php endif; ?>
						<?php if (!$is_owner): ?>
						<a href="<?=base_url()?>whmazadmin/general_setting/manage?tab=sysconfig" class="ms-2">Change</a>
						<?php endif; ?>
					</div>

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
							<i class="fa fa-info-circle"></i> Select a reseller above to see their costs and margins.
						</div>
					<?php elseif (empty($rows)): ?>
						<div class="alert alert-warning mb-0">
							<i class="fa fa-exclamation-triangle"></i> Nothing is priced in this category yet.
						</div>
					<?php else: ?>

					<div class="alert alert-secondary" style="font-size:13px;">
						<i class="fa fa-info-circle"></i>
						<strong>Retail</strong> is what <?= $is_owner ? 'your' : 'this reseller\'s' ?> customers are
						charged — the platform's own price, identical for every buyer.
						<strong>Cost</strong> is what <?= $is_owner ? 'you pay' : 'this reseller pays' ?> the platform,
						taken from <?= $is_owner ? 'your' : 'their' ?> account credit when an order provisions.
						<strong>Margin</strong> is the difference, and it is <?= $is_owner ? 'yours' : 'theirs' ?> to keep.
						<?php if ($is_owner): ?>
						Only platform staff can change either number.
						<?php else: ?>
						Only the cost column is editable<?= $isDomain ? ' (register, transfer and renewal are set independently)' : '' ?>;
						clear a cost to fall back down the ladder.
						<?php endif; ?>
					</div>

					<div class="table-responsive">
						<table class="table table-bordered table-sm align-middle">
							<thead class="bg-light">
								<tr>
									<th style="min-width:180px;">Item</th>
									<th style="min-width:90px;">Currency</th>
									<th class="text-center" style="min-width:110px;">Retail<br><small class="text-muted fw-normal">customer pays</small></th>
									<th class="text-center" style="min-width:130px;">Cost<br><small class="text-muted fw-normal"><?= $is_owner ? 'you pay us' : 'they pay us' ?></small></th>
									<th class="text-center" style="min-width:110px;">Margin</th>
									<th class="text-center" style="min-width:90px;">Margin %</th>
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

									<td class="text-end">
										<strong><?= number_format($row['base_price'], 2) ?></strong>
										<?php if ($isDomain): ?>
										<br><small class="text-muted">t <?= number_format($row['base_transfer'], 2) ?>
										 / r <?= number_format($row['base_renewal'], 2) ?></small>
										<?php endif; ?>
									</td>

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

									<td class="text-end">
										<?= rp_margin_cell($row['margin_price']) ?>
										<?php if ($isDomain): ?>
										<br><small>t <?= rp_margin_cell($row['margin_transfer']) ?>
										 / r <?= rp_margin_cell($row['margin_renewal']) ?></small>
										<?php endif; ?>
									</td>

									<td class="text-end">
										<?= rp_margin_cell($row['margin_pct']) ?><span class="text-muted">%</span>
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
	var BASE     = '<?=base_url()?>whmazadmin/reseller_pricing/';
	var TYPE     = '<?= htmlspecialchars($type, ENT_QUOTES) ?>';
	var RESELLER = <?= intval($reseller_company_id) ?>;

	<?php if (!$is_owner): ?>
	$('#resellerPicker').on('change', function () {
		var id = parseInt($(this).val(), 10) || 0;
		window.location.href = '<?= $baseUrl ?>?type=' + TYPE + (id > 0 ? '&reseller=' + id : '');
	});

	// Cost edits save on change -- there is no separate button. The margin
	// columns are rendered server-side, so reload the row's page figures by
	// recomputing locally would drift; instead the reply just confirms, and the
	// next page load shows the recomputed margin.
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
				Swal.fire({
					icon: 'success', title: 'Cost saved',
					text: res.msg || 'Cost saved.',
					timer: 1200, showConfirmButton: false
				}).then(function () { window.location.reload(); });
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
