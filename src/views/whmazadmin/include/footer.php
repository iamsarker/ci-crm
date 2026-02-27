        </div><!-- /.app-content -->
      </main><!-- /.app-main -->

      <!-- Footer -->
      <footer class="app-footer">
        <div class="float-end d-none d-sm-inline">
          Maintain by <a href="https://whmaz.com/" target="_blank">WHMAZ</a>
        </div>
        <strong>&copy; 2014-<?=date("Y")?> WHMAZ v1.0.0</strong>
      </footer>

    </div><!-- /.app-wrapper -->

    <!-- Flash Messages -->
    <script>
      <?php $admin_success = $this->session->flashdata('admin_success'); ?>
      <?php $this->session->set_flashdata('admin_success', NULL); ?>
      <?php if ($admin_success) { ?>
      document.addEventListener('DOMContentLoaded', function() {
        toastSuccess(<?= json_encode(htmlspecialchars($admin_success, ENT_QUOTES, 'UTF-8')) ?>);
      });
      <?php } ?>
      <?php $admin_error = $this->session->flashdata('admin_error'); ?>
      <?php $this->session->set_flashdata('admin_error', NULL); ?>
      <?php if ($admin_error) { ?>
      document.addEventListener('DOMContentLoaded', function() {
        toastError(<?= json_encode(htmlspecialchars($admin_error, ENT_QUOTES, 'UTF-8')) ?>);
      });
      <?php } ?>
    </script>

  </body>
</html>
