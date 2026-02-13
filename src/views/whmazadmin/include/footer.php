	<script>
		<?php $admin_success = $this->session->flashdata('admin_success'); ?>
		<?php $this->session->set_flashdata('admin_success', NULL); ?>
		<?php if ($admin_success) { ?>
		toastSuccess(<?= json_encode(htmlspecialchars($admin_success, ENT_QUOTES, 'UTF-8')) ?>);
		<?php } ?>
		<?php $admin_error = $this->session->flashdata('admin_error'); ?>
		<?php $this->session->set_flashdata('admin_error', NULL); ?>
		<?php if ($admin_error) { ?>
		toastError(<?= json_encode(htmlspecialchars($admin_error, ENT_QUOTES, 'UTF-8')) ?>);
		<?php } ?>
	</script>

	<footer class="footer">
		<div>
			<span>&copy; 2014-<?=date("Y")?> WHMAZ v1.0.0 </span>
			<span>Maintain by <a href="https://whmaz.com/">WHMAZ</a></span>
		</div>
		<div>
			<nav class="nav">
				<a href="<?=base_url()?>pages/terms-and-conditions" class="nav-link" target="_blank">Terms & Conditions</a>
				<a href="<?=base_url()?>pages/privacy-policy" class="nav-link" target="_blank">Privacy Policy</a>
				<a href="<?=base_url()?>pages/refund-policy" class="nav-link" target="_blank">Refund Policy</a>
			</nav>
		</div>
	</footer>


	</body>
</html>
