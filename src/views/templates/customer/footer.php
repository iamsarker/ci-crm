        </div><!-- /.app-content -->
      </main><!-- /.app-main -->

      <!-- Footer -->
      <footer class="app-footer">
        <div class="float-end d-none d-sm-inline">
          <nav class="nav">
            <a href="<?=base_url()?>pages/terms-and-conditions" class="nav-link" target="_blank">Terms & Conditions</a>
            <a href="<?=base_url()?>pages/privacy-policy" class="nav-link" target="_blank">Privacy Policy</a>
            <a href="<?=base_url()?>pages/refund-policy" class="nav-link" target="_blank">Refund Policy</a>
          </nav>
        </div>
        <strong>&copy; 2014-<?=date("Y")?> WHMAZ v1.0.0</strong>
        <span class="ms-2">Maintain by <a href="https://whmaz.com/" target="_blank">WHMAZ</a></span>
      </footer>

    </div><!-- /.app-wrapper -->

    <!-- Flash Messages -->
    <script>
      <?php $alert_success = $this->session->flashdata('alert_success'); ?>
      <?php $this->session->set_flashdata('alert_success', NULL); ?>
      <?php if ($alert_success) { ?>
      document.addEventListener('DOMContentLoaded', function() {
        toastSuccess(<?= json_encode(htmlspecialchars($alert_success, ENT_QUOTES, 'UTF-8')) ?>);
      });
      <?php } ?>
      <?php $alert_error = $this->session->flashdata('alert_error'); ?>
      <?php $this->session->set_flashdata('alert_error', NULL); ?>
      <?php if ($alert_error) { ?>
      document.addEventListener('DOMContentLoaded', function() {
        toastError(<?= json_encode(htmlspecialchars($alert_error, ENT_QUOTES, 'UTF-8')) ?>);
      });
      <?php } ?>
    </script>

  </body>
</html>
