<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
  <head>
	  <?php $this->load->view('whmazadmin/include/header_script');?>
  </head>
  <body class="layout-top-nav">
    <div class="app-wrapper">
      <?php $this->load->view('whmazadmin/include/header_menus');?>

      <!-- Main Content Area -->
      <main class="app-main">
        <div class="app-content">
          <?php $__licenseGraceUntil = $this->session->userdata('LICENSE_GRACE_UNTIL'); ?>
          <?php if (!empty($__licenseGraceUntil)):
              $__graceTs    = strtotime($__licenseGraceUntil);
              $__graceLabel = $__graceTs ? date('M j, Y', $__graceTs) : $__licenseGraceUntil;
              $__daysLeft   = $__graceTs ? (int) ceil(($__graceTs - time()) / 86400) : null;
          ?>
          <div class="container-fluid pt-3">
            <div class="alert alert-warning d-flex align-items-center mb-0" role="alert">
              <i class="fas fa-exclamation-triangle me-2"></i>
              <div>
                <strong>License grace period.</strong>
                Your license is running on a grace window until <strong><?= htmlspecialchars($__graceLabel) ?></strong><?php if ($__daysLeft !== null && $__daysLeft >= 0): ?> (<?= $__daysLeft ?> day<?= $__daysLeft == 1 ? '' : 's' ?> remaining)<?php endif; ?>. Please renew your license to avoid losing admin access.
              </div>
            </div>
          </div>
          <?php endif; ?>
