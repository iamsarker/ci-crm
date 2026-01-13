<?php $this->load->view('whmazadmin/include/header');?>

	 <div class="content content-fluid content-wrapper">
      <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

        <div class="row mt-5">
			<div class="col-md-12 col-sm-12">
				<h3 class="d-flex justify-content-between"><span>Currencies</span> <a href="<?=base_url()?>whmazadmin/currency/index" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i>&nbsp;Back</a></h3>
				<hr class="mg-5" />
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb breadcrumb-style1 mg-b-0">
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/currency/index">Currencies</a></li>
						<li class="breadcrumb-item active"><a href="#">Manage currency</a></li>
					</ol>
				</nav>
			  <?php if ($this->session->flashdata('alert')) { ?>
				<?= $this->session->flashdata('alert') ?>
			  <?php } ?>

			</div>

			<div class="col-md-12 col-sm-12 mt-5">
				<form method="post" name="entityManageForm" id="entityManageForm" action="<?=base_url()?>whmazadmin/currency/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
					<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />
					<input name="format" type="hidden" id="format" value="<?= !empty($detail['format']) ? $detail['format'] : 1?>" />

					<div class="form-group">
						<label for="code">Code</label>
						<input name="code" type="text" class="form-control" id="code" value="<?= !empty($detail['code']) ? $detail['code'] : ''?>"/>
						<?php echo form_error('code', '<div class="error">', '</div>'); ?>
					</div>

					<div class="form-group">
						<label for="symbol">Symbol</label>
						<input name="symbol" type="text" class="form-control" id="symbol" value="<?= !empty($detail['symbol']) ? $detail['symbol'] : ''?>"/>
						<?php echo form_error('symbol', '<div class="error">', '</div>'); ?>
					</div>

					<div class="form-group">
						<label for="rate">Rate</label>
						<input name="rate" type="text" class="form-control" id="rate" value="<?= !empty($detail['rate']) ? $detail['rate'] : ''?>"/>
						<?php echo form_error('rate', '<div class="error">', '</div>'); ?>
					</div>

					<div class="form-check mb-3">
						<input name="is_default" type="checkbox" class="form-check-input" id="is_default" <?= !empty($detail['is_default']) && $detail['is_default'] == 1 ? 'checked=\"checked\"' : ''?>"/>
						<label for="is_default" class="form-check-label mt-3px"> Is Default?</label>
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

<?php $this->load->view('whmazadmin/include/footer');?>
