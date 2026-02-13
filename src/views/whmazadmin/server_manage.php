<?php $this->load->view('whmazadmin/include/header');?>
<link rel="stylesheet" href="<?=base_url()?>resources/assets/css/admin.manage_view.css">

<div class="content content-fluid content-wrapper">
	<div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

		<!-- Page Header -->
		<div class="row mt-4">
			<div class="col-12">
				<div class="company-page-header">
					<div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
						<div>
							<h3><i class="fa fa-server"></i> <?= !empty($detail['name']) ? htmlspecialchars($detail['name']) : 'New Server' ?></h3>
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Dashboard</a></li>
									<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/server/index">Servers</a></li>
									<li class="breadcrumb-item active"><a href="#">Manage</a></li>
								</ol>
							</nav>
						</div>
						<a href="<?=base_url()?>whmazadmin/server/index" class="btn btn-back">
							<i class="fa fa-arrow-left"></i> Back to List
						</a>
					</div>
				</div>
			</div>
		</div>

		<!-- Form Section -->
		<div class="row mt-4">
			<div class="col-12">
				<div class="manage-form-card">
					<form method="post" name="entityManageForm" id="entityManageForm" class="company-form" action="<?=base_url()?>whmazadmin/server/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
						<?=csrf_field()?>
						<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

						<!-- Server Details Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-info-circle"></i> Server Details
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="form-label" for="name"><i class="fa fa-server"></i> Server Name</label>
										<input name="name" type="text" class="form-control" id="name" placeholder="Enter server name" value="<?= htmlspecialchars($detail['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('name', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="ip_addr"><i class="fa fa-network-wired"></i> IP Address</label>
										<input name="ip_addr" type="text" class="form-control" id="ip_addr" placeholder="192.168.1.1" value="<?= htmlspecialchars($detail['ip_addr'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('ip_addr', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="hostname"><i class="fa fa-globe"></i> Hostname</label>
										<input name="hostname" type="text" class="form-control" id="hostname" placeholder="server.example.com" value="<?= htmlspecialchars($detail['hostname'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('hostname', '<div class="error">', '</div>'); ?>
									</div>
								</div>
							</div>
						</div>

						<!-- DNS Configuration Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-globe-americas"></i> DNS Configuration
							</div>
							<div class="row">
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="dns1"><i class="fa fa-sitemap"></i> DNS1</label>
										<input name="dns1" type="text" class="form-control" id="dns1" placeholder="ns1.example.com" value="<?= htmlspecialchars($detail['dns1'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('dns1', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="dns2"><i class="fa fa-sitemap"></i> DNS2</label>
										<input name="dns2" type="text" class="form-control" id="dns2" placeholder="ns2.example.com" value="<?= htmlspecialchars($detail['dns2'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('dns2', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="dns3"><i class="fa fa-sitemap"></i> DNS3</label>
										<input name="dns3" type="text" class="form-control" id="dns3" placeholder="ns3.example.com" value="<?= htmlspecialchars($detail['dns3'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('dns3', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="dns4"><i class="fa fa-sitemap"></i> DNS4</label>
										<input name="dns4" type="text" class="form-control" id="dns4" placeholder="ns4.example.com" value="<?= htmlspecialchars($detail['dns4'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('dns4', '<div class="error">', '</div>'); ?>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="dns1_ip"><i class="fa fa-network-wired"></i> DNS1 IP</label>
										<input name="dns1_ip" type="text" class="form-control" id="dns1_ip" placeholder="IP address" value="<?= htmlspecialchars($detail['dns1_ip'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('dns1_ip', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="dns2_ip"><i class="fa fa-network-wired"></i> DNS2 IP</label>
										<input name="dns2_ip" type="text" class="form-control" id="dns2_ip" placeholder="IP address" value="<?= htmlspecialchars($detail['dns2_ip'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('dns2_ip', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="dns3_ip"><i class="fa fa-network-wired"></i> DNS3 IP</label>
										<input name="dns3_ip" type="text" class="form-control" id="dns3_ip" placeholder="IP address" value="<?= htmlspecialchars($detail['dns3_ip'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('dns3_ip', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="dns4_ip"><i class="fa fa-network-wired"></i> DNS4 IP</label>
										<input name="dns4_ip" type="text" class="form-control" id="dns4_ip" placeholder="IP address" value="<?= htmlspecialchars($detail['dns4_ip'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('dns4_ip', '<div class="error">', '</div>'); ?>
									</div>
								</div>
							</div>
						</div>

						<!-- Authentication Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-key"></i> Authentication
							</div>
							<div class="row">
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="username"><i class="fa fa-user"></i> Username</label>
										<input name="username" type="text" class="form-control" id="username" placeholder="Enter username" value="<?= htmlspecialchars($detail['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('username', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="authpass"><i class="fa fa-lock"></i> Auth Password</label>
										<input name="authpass" type="text" class="form-control" id="authpass" placeholder="••••••••" value="<?= htmlspecialchars($detail['authpass'] ?? '', ENT_QUOTES, 'UTF-8') ?>" disabled/>
										<?php echo form_error('authpass', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="access_hash"><i class="fa fa-fingerprint"></i> Access Hash</label>
										<input name="access_hash" type="text" class="form-control" id="access_hash" placeholder="API access hash" value="<?= htmlspecialchars($detail['access_hash'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('access_hash', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group">
										<label class="form-label" for="port"><i class="fa fa-plug"></i> Port</label>
										<input name="port" type="text" class="form-control" id="port" placeholder="2087" value="<?= htmlspecialchars($detail['port'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('port', '<div class="error">', '</div>'); ?>
									</div>
								</div>
							</div>
						</div>

						<!-- Additional Info Section -->
						<div class="company-form-section">
							<div class="section-title">
								<i class="fa fa-sticky-note"></i> Additional Information
							</div>
							<div class="form-group">
								<label class="form-label" for="remarks"><i class="fa fa-align-left"></i> Remarks</label>
								<textarea name="remarks" rows="3" class="form-control" id="remarks" placeholder="Enter remarks..."><?= htmlspecialchars($detail['remarks'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
								<?php echo form_error('remarks', '<div class="error">', '</div>'); ?>
							</div>

							<div class="form-group">
								<div class="custom-checkbox-toggle">
									<input name="is_secure" type="checkbox" id="is_secure" <?= !empty($detail['is_secure']) && $detail['is_secure'] == 1 ? 'checked' : ''?>/>
									<label for="is_secure">Use Secure Connection (HTTPS)</label>
								</div>
							</div>
						</div>

						<!-- Submit Button -->
						<div class="form-group mt-4">
							<button type="submit" class="btn btn-save-company">
								<i class="fa fa-check-circle"></i> Save Server
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>

	</div><!-- container -->
</div><!-- content -->

<?php $this->load->view('whmazadmin/include/footer_script');?>
<?php $this->load->view('whmazadmin/include/footer');?>
