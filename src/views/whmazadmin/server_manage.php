<?php $this->load->view('whmazadmin/include/header');?>

	 <div class="content content-fluid content-wrapper">
      <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

        <div class="row mt-5">
			<div class="col-md-12 col-sm-12">
				<h3 class="d-flex justify-content-between"><span>Servers</span> <a href="<?=base_url()?>whmazadmin/server/index" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i>&nbsp;Back</a></h3>
				<hr class="mg-5" />
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb breadcrumb-style1 mg-b-0">
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/server/index">Servers</a></li>
						<li class="breadcrumb-item active"><a href="#">Manage KB category</a></li>
					</ol>
				</nav>
			  <?php if ($this->session->flashdata('alert')) { ?>
				<?= $this->session->flashdata('alert') ?>
			  <?php } ?>

			</div>

			<div class="col-md-12 col-sm-12 mt-5">
				<form method="post" name="entityManageForm" id="entityManageForm" action="<?=base_url()?>whmazadmin/server/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
					<?=csrf_field()?>
					<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

					<div class="row">
						<div class="col-md-6 col-sm-12">
							<div class="form-group">
								<label for="name">Server name</label>
								<input name="name" type="text" class="form-control" id="name" value="<?= htmlspecialchars($detail['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
								<?php echo form_error('name', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<div class="col-md-3 col-sm-12">
							<div class="form-group">
								<label for="ip_addr">IP Address</label>
								<input name="ip_addr" type="text" class="form-control" id="ip_addr" value="<?= htmlspecialchars($detail['ip_addr'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
								<?php echo form_error('ip_addr', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<div class="col-md-3 col-sm-12">
							<div class="form-group">
								<label for="hostname">Hostname</label>
								<input name="hostname" type="text" class="form-control" id="hostname" value="<?= htmlspecialchars($detail['hostname'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
								<?php echo form_error('hostname', '<div class="error">', '</div>'); ?>
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-3 col-sm-12">
							<div class="form-group">
								<label for="dns1">DNS1</label>
								<input name="dns1" type="text" class="form-control" id="dns1" value="<?= htmlspecialchars($detail['dns1'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
								<?php echo form_error('dns1', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<div class="col-md-3 col-sm-12">
							<div class="form-group">
								<label for="dns2">DNS2</label>
								<input name="dns2" type="text" class="form-control" id="dns2" value="<?= htmlspecialchars($detail['dns2'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
								<?php echo form_error('dns2', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<div class="col-md-3 col-sm-12">
							<div class="form-group">
								<label for="dns3">DNS3</label>
								<input name="dns3" type="text" class="form-control" id="dns3" value="<?= htmlspecialchars($detail['dns3'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
								<?php echo form_error('dns3', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<div class="col-md-3 col-sm-12">
							<div class="form-group">
								<label for="dns4">DNS4</label>
								<input name="dns4" type="text" class="form-control" id="dns4" value="<?= htmlspecialchars($detail['dns4'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
								<?php echo form_error('dns4', '<div class="error">', '</div>'); ?>
							</div>
						</div>
					</div>

					<div class="row mt-3">
						<div class="col-md-3 col-sm-12">
							<div class="form-group">
								<label for="dns1_ip">DNS1 IP</label>
								<input name="dns1_ip" type="text" class="form-control" id="dns1_ip" value="<?= htmlspecialchars($detail['dns1_ip'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
								<?php echo form_error('dns1_ip', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<div class="col-md-3 col-sm-12">
							<div class="form-group">
								<label for="dns2_ip">DNS2 IP</label>
								<input name="dns2_ip" type="text" class="form-control" id="dns2_ip" value="<?= htmlspecialchars($detail['dns2_ip'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
								<?php echo form_error('dns2_ip', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<div class="col-md-3 col-sm-12">
							<div class="form-group">
								<label for="dns3_ip">DNS3 IP</label>
								<input name="dns3_ip" type="text" class="form-control" id="dns3_ip" value="<?= htmlspecialchars($detail['dns3_ip'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
								<?php echo form_error('dns3_ip', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<div class="col-md-3 col-sm-12">
							<div class="form-group">
								<label for="dns4_ip">DNS4 IP</label>
								<input name="dns4_ip" type="text" class="form-control" id="dns4_ip" value="<?= htmlspecialchars($detail['dns4_ip'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
								<?php echo form_error('dns4_ip', '<div class="error">', '</div>'); ?>
							</div>
						</div>
					</div>


					<div class="row mt-3">
						<div class="col-md-3 col-sm-12">
							<div class="form-group">
								<label for="username">Username</label>
								<input name="username" type="text" class="form-control" id="username" value="<?= htmlspecialchars($detail['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
								<?php echo form_error('username', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<div class="col-md-3 col-sm-12">
							<div class="form-group">
								<label for="authpass">Auth Pass</label>
								<input name="authpass" type="text" class="form-control" id="authpass" value="<?= htmlspecialchars($detail['authpass'] ?? '', ENT_QUOTES, 'UTF-8') ?>" disabled/>
								<?php echo form_error('authpass', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<div class="col-md-3 col-sm-12">
							<div class="form-group">
								<label for="access_hash">Access Hash</label>
								<input name="access_hash" type="text" class="form-control" id="access_hash" value="<?= htmlspecialchars($detail['access_hash'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
								<?php echo form_error('access_hash', '<div class="error">', '</div>'); ?>
							</div>
						</div>

						<div class="col-md-3 col-sm-12">
							<div class="form-group">
								<label for="port">Port</label>
								<input name="port" type="text" class="form-control" id="port" value="<?= htmlspecialchars($detail['port'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
								<?php echo form_error('port', '<div class="error">', '</div>'); ?>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label for="remarks">Remarks</label>
						<textarea name="remarks" rows="3" class="form-control" id="remarks"><?= htmlspecialchars($detail['remarks'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
						<?php echo form_error('remarks', '<div class="error">', '</div>'); ?>
					</div>

					<div class="form-check mb-3">
						<input name="is_secure" type="checkbox" class="form-check-input" id="is_secure" <?= !empty($detail['is_secure']) && $detail['is_secure'] == 1 ? 'checked=\"checked\"' : ''?>"/>
						<label for="is_secure" class="form-check-label mt-1"> Is Secure?</label>
					</div>

					<div class="form-group">
						<button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-check-circle"></i>&nbsp;Save</button>
					</div>
				</form>
			</div>
      </div>
		
    </div><!-- container -->
  </div><!-- content -->

<?php $this->load->view('whmazadmin/include/footer_script');?>

<script>
$(function(){
	'use strict'

	// Show flash messages as toast
	<?php if ($this->session->flashdata('alert_success')) { ?>
		toastSuccess(<?= json_encode($this->session->flashdata('alert_success')) ?>);
	<?php } ?>
	<?php if ($this->session->flashdata('alert_error')) { ?>
		toastError(<?= json_encode($this->session->flashdata('alert_error')) ?>);
	<?php } ?>
});
</script>


<?php $this->load->view('whmazadmin/include/footer');?>
