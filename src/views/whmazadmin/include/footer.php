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
				<a href="https://themeforest.net/licenses/standard" class="nav-link">Licenses</a>
				<a href="<?=base_url()?>resources/change-log.html" class="nav-link">Change Log</a>
				<a href="https://discordapp.com/invite/RYqkVuw" class="nav-link">Get Help</a>
			</nav>
		</div>
	</footer>


	</body>
</html>
