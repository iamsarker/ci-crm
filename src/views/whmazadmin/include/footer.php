	<script>
		<?php $alert_success = $this->session->flashdata('alert_success'); ?>
		<?php $this->session->set_flashdata('alert_success', NULL); ?>
		<?php if ($alert_success) { ?>
		toastSuccess(<?= json_encode(htmlspecialchars($alert_success, ENT_QUOTES, 'UTF-8')) ?>);
		<?php } ?>
		<?php $alert_error = $this->session->flashdata('alert_error'); ?>
		<?php $this->session->set_flashdata('alert_error', NULL); ?>
		<?php if ($alert_error) { ?>
		toastError(<?= json_encode(htmlspecialchars($alert_error, ENT_QUOTES, 'UTF-8')) ?>);
		<?php } ?>
	</script>

	<footer class="footer">
		<div>
			<span>&copy; 2014-<?=date("Y")?> WHMAZ v1.0.0 </span>
			<span>Maintain by <a href="https://tongbari.com/">TongBari</a></span>
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
