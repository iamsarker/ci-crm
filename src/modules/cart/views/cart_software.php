<?php $this->load->view('templates/customer/header'); ?>

<div class="content content-fixed content-wrapper">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">

        <!-- Page Header -->
        <div class="page-header-card page-header-cart mg-b-25">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h3 class="mg-b-0"><i class="fa fa-cube mg-r-10"></i>Software</h3>
                    <nav aria-label="breadcrumb" class="mg-t-8">
                        <ol class="breadcrumb breadcrumb-style1 mg-b-0">
                            <li class="breadcrumb-item"><a href="<?=base_url()?>">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Software</li>
                        </ol>
                    </nav>
                </div>
                <a href="<?=base_url()?>cart/view" class="btn btn-white">
                    <i class="fa fa-shopping-cart mg-r-5"></i> View Cart
                    <span class="badge bg-primary ms-1" id="cartCountBadge"><?= (int)($cart_count ?? 0) ?></span>
                </a>
            </div>
        </div>

        <?php if (empty($products)): ?>
            <div class="alert alert-info text-center">
                <i class="fa fa-info-circle mg-r-5"></i>
                No software products are available for purchase in your selected currency yet.
            </div>
        <?php else: ?>
        <div class="row">
            <?php foreach ($products as $p): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 shadow-sm software-card <?= !empty($p['is_popular']) ? 'border-primary' : '' ?>">
                    <?php if (!empty($p['is_popular'])): ?>
                    <div class="card-header bg-primary text-white text-center py-2">
                        <i class="fa fa-star mg-r-5"></i> Most Popular
                    </div>
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <h4 class="mb-1"><?= htmlspecialchars($p['name']) ?></h4>
                        <?php if (!empty($p['tagline'])): ?>
                            <p class="text-muted mb-3"><?= htmlspecialchars($p['tagline']) ?></p>
                        <?php endif; ?>

                        <!-- Price -->
                        <div class="mb-3">
                            <span class="h2 fw-bold price-amount" data-currency="<?= htmlspecialchars($currency_code) ?>">
                                <?= htmlspecialchars($currency_code) ?> <?= number_format((float)$p['prices'][0]['recurring_amount'], 2) ?>
                            </span>
                            <span class="text-muted price-cycle">/ <?= htmlspecialchars($p['prices'][0]['cycle_name']) ?></span>
                        </div>

                        <!-- Billing cycle selector -->
                        <?php if (count($p['prices']) > 1): ?>
                        <div class="mb-3">
                            <label class="form-label small text-muted">Billing Cycle</label>
                            <select class="form-select cycle-select">
                                <?php foreach ($p['prices'] as $pr): ?>
                                <option value="<?= (int)$pr['pricing_id'] ?>"
                                        data-amount="<?= htmlspecialchars(number_format((float)$pr['recurring_amount'], 2)) ?>"
                                        data-cycle="<?= htmlspecialchars($pr['cycle_name']) ?>">
                                    <?= htmlspecialchars($pr['cycle_name']) ?> — <?= htmlspecialchars($currency_code) ?> <?= number_format((float)$pr['recurring_amount'], 2) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php else: ?>
                            <input type="hidden" class="cycle-select" value="<?= (int)$p['prices'][0]['pricing_id'] ?>">
                        <?php endif; ?>

                        <?php if (!empty($p['description'])): ?>
                            <div class="small text-muted mb-3"><?= $p['description'] ?></div>
                        <?php endif; ?>

                        <!-- Features -->
                        <?php if (!empty($p['features'])): ?>
                        <ul class="list-unstyled mb-3">
                            <?php foreach ($p['features'] as $fkey => $fval):
                                if ($fval === false || $fval === 0 || $fval === '0') continue;
                                $label = feature_label($fkey);
                            ?>
                            <li class="mb-1">
                                <i class="fa fa-check text-success mg-r-5"></i>
                                <?php if ($fval === true || $fval === 1) : ?>
                                    <?= htmlspecialchars($label) ?>
                                <?php else: ?>
                                    <?= htmlspecialchars($label) ?>: <strong><?= htmlspecialchars((string)$fval) ?></strong>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>

                        <button type="button" class="btn btn-primary mt-auto btn-add-software"
                                data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>">
                            <i class="fa fa-cart-plus mg-r-5"></i> Add to Cart
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</div>

<script>
(function () {
    var BASE_URL = '<?=base_url()?>';

    // Update the displayed price when the billing cycle changes.
    document.querySelectorAll('.cycle-select').forEach(function (sel) {
        if (sel.tagName !== 'SELECT') return;
        sel.addEventListener('change', function () {
            var opt = sel.options[sel.selectedIndex];
            var card = sel.closest('.software-card');
            var amountEl = card.querySelector('.price-amount');
            var cycleEl = card.querySelector('.price-cycle');
            var cur = amountEl.getAttribute('data-currency');
            amountEl.textContent = cur + ' ' + opt.getAttribute('data-amount');
            cycleEl.textContent = '/ ' + opt.getAttribute('data-cycle');
        });
    });

    // Add to cart -> POST JSON (endpoint reads php://input) -> go to cart.
    document.querySelectorAll('.btn-add-software').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var card = btn.closest('.software-card');
            var sel = card.querySelector('.cycle-select');
            var pricingId = sel.value;
            if (!pricingId) { return; }

            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin mg-r-5"></i> Adding...';

            fetch(BASE_URL + 'cart/addSoftwareToCart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ software_pricing_id: pricingId })
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res && (res.code === 200 || res.status === true || (res.data && res.data.cart_id))) {
                    window.location.href = BASE_URL + 'cart/view';
                } else {
                    alert((res && (res.msg || res.message)) || 'Could not add to cart.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa fa-cart-plus mg-r-5"></i> Add to Cart';
                }
            })
            .catch(function () {
                alert('Network error. Please try again.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-cart-plus mg-r-5"></i> Add to Cart';
            });
        });
    });
})();
</script>

<?php $this->load->view('templates/customer/footer_script'); ?>
<?php $this->load->view('templates/customer/footer'); ?>
