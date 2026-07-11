<?php $this->load->view('templates/customer/header'); ?>

<style>
  .home-hero {
    position: relative;
    overflow: hidden;
    border-radius: 1rem;
    background: linear-gradient(135deg, #0168fa 0%, #6f42c1 100%);
    color: #fff;
    padding: 4.5rem 1.5rem;
    text-align: center;
  }
  .home-hero::after {
    content: "";
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 20% 20%, rgba(255, 255, 255, .18), transparent 45%),
                radial-gradient(circle at 85% 30%, rgba(255, 255, 255, .12), transparent 40%);
    pointer-events: none;
  }
  .home-hero > * { position: relative; z-index: 1; }
  .home-hero h1 {
    font-weight: 800;
    font-size: clamp(1.9rem, 4vw, 3rem);
    line-height: 1.15;
    margin-bottom: 1rem;
  }
  .home-hero p.lead {
    max-width: 640px;
    margin: 0 auto 2rem;
    font-size: 1.15rem;
    color: rgba(255, 255, 255, .9);
  }
  .btn-get-started {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border: none;
    color: #fff;
    font-weight: 700;
    padding: .85rem 2.25rem;
    border-radius: .6rem;
    box-shadow: 0 10px 25px rgba(16, 185, 129, .35);
    transition: transform .15s ease, box-shadow .15s ease;
  }
  .btn-get-started:hover {
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 14px 30px rgba(16, 185, 129, .45);
  }
  .btn-hero-outline {
    border: 2px solid rgba(255, 255, 255, .7);
    color: #fff;
    font-weight: 600;
    padding: .78rem 1.9rem;
    border-radius: .6rem;
    transition: background .15s ease, color .15s ease;
  }
  .btn-hero-outline:hover { background: #fff; color: #0168fa; }

  .home-feature-card {
    height: 100%;
    border: 1px solid #eef0f4;
    border-radius: .85rem;
    padding: 1.75rem 1.5rem;
    background: #fff;
    transition: transform .15s ease, box-shadow .15s ease;
  }
  .home-feature-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(30, 41, 59, .1);
  }
  .home-feature-icon {
    width: 52px;
    height: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: .75rem;
    font-size: 1.4rem;
    color: #0168fa;
    background: linear-gradient(135deg, #e8f0ff 0%, #f0e9ff 100%);
    margin-bottom: 1rem;
  }
  .home-cta-band {
    border-radius: 1rem;
    background: linear-gradient(135deg, #f0fdf4 0%, #ecfeff 100%);
    border: 1px solid #d1fae5;
    padding: 3rem 1.5rem;
    text-align: center;
  }
</style>

<div class="container py-4">

  <!-- Hero -->
  <section class="home-hero mb-5">
    <?php if (isLoggedin()) { $u = getUserData(); ?>
      <h1>Welcome back<?= !empty($u['first_name']) ? ', ' . htmlspecialchars($u['first_name']) : '' ?>!</h1>
      <p class="lead">Jump straight into your dashboard to manage your domains, hosting, software and invoices.</p>
      <div class="d-flex flex-wrap gap-3 justify-content-center">
        <a href="<?= base_url() ?>clientarea" class="btn btn-get-started btn-lg">
          <i class="fas fa-gauge-high me-2"></i> Go to Dashboard
        </a>
      </div>
    <?php } else { ?>
      <h1>Everything you need to launch and grow online</h1>
      <p class="lead">Register domains, order hosting, manage software licenses and handle billing — all from one secure WHMAZ client portal.</p>
      <div class="d-flex flex-wrap gap-3 justify-content-center">
        <a href="<?= base_url() ?>auth/register" class="btn btn-get-started btn-lg">
          <i class="fas fa-rocket me-2"></i> Get Started
        </a>
        <a href="<?= base_url() ?>auth/login" class="btn btn-hero-outline btn-lg">
          <i class="fas fa-sign-in-alt me-2"></i> Sign In
        </a>
      </div>
    <?php } ?>
  </section>

  <!-- Features -->
  <div class="text-center mb-4">
    <h2 class="fw-bold">One portal for all your services</h2>
    <p class="text-muted">Manage every part of your online presence in a single place.</p>
  </div>

  <div class="row g-4 mb-5">
    <div class="col-md-6 col-lg-4">
      <div class="home-feature-card">
        <div class="home-feature-icon"><i class="fas fa-globe"></i></div>
        <h5 class="fw-bold">Domains</h5>
        <p class="text-muted mb-0">Search, register and transfer domain names in seconds, with easy DNS management.</p>
      </div>
    </div>
    <div class="col-md-6 col-lg-4">
      <div class="home-feature-card">
        <div class="home-feature-icon"><i class="fas fa-server"></i></div>
        <h5 class="fw-bold">Hosting</h5>
        <p class="text-muted mb-0">Order hosting plans with automated provisioning across cPanel, Plesk and DirectAdmin.</p>
      </div>
    </div>
    <div class="col-md-6 col-lg-4">
      <div class="home-feature-card">
        <div class="home-feature-icon"><i class="fas fa-cube"></i></div>
        <h5 class="fw-bold">Software &amp; Licenses</h5>
        <p class="text-muted mb-0">Purchase and manage software subscriptions and license keys from your account.</p>
      </div>
    </div>
    <div class="col-md-6 col-lg-4">
      <div class="home-feature-card">
        <div class="home-feature-icon"><i class="fas fa-file-invoice-dollar"></i></div>
        <h5 class="fw-bold">Billing &amp; Invoices</h5>
        <p class="text-muted mb-0">View invoices, track payments and keep your billing details up to date.</p>
      </div>
    </div>
    <div class="col-md-6 col-lg-4">
      <div class="home-feature-card">
        <div class="home-feature-icon"><i class="fas fa-headset"></i></div>
        <h5 class="fw-bold">Support</h5>
        <p class="text-muted mb-0">Open tickets, browse the knowledge base and stay updated with announcements.</p>
      </div>
    </div>
    <div class="col-md-6 col-lg-4">
      <div class="home-feature-card">
        <div class="home-feature-icon"><i class="fas fa-bolt"></i></div>
        <h5 class="fw-bold">Automation</h5>
        <p class="text-muted mb-0">Automated ordering, renewals and provisioning keep your services running smoothly.</p>
      </div>
    </div>
  </div>

  <!-- Bottom CTA -->
  <?php if (!isLoggedin()) { ?>
  <section class="home-cta-band mb-4">
    <h2 class="fw-bold mb-2">Ready to get started?</h2>
    <p class="text-muted mb-4">Create your free account and set up your first service in minutes.</p>
    <a href="<?= base_url() ?>auth/register" class="btn btn-get-started btn-lg">
      <i class="fas fa-rocket me-2"></i> Get Started
    </a>
  </section>
  <?php } ?>

</div>

<?php $this->load->view('templates/customer/footer_script'); ?>
<?php $this->load->view('templates/customer/footer'); ?>
