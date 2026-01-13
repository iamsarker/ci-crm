<?php $this->load->view('whmazadmin/include/header');?>

<div class="content content-fixed content-wrapper">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0 mt-2">
        <div class="row">
			<div class="col-md-12 mb-4">
				<h3 class="d-flex justify-content-between"><span>Invoices</span> <a href="<?=base_url()?>whmazadmin/invoice/index" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i>&nbsp;Back</a></h3>
				<hr class="mg-5" />
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb breadcrumb-style1 mg-b-0">
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/invoice/index">Invoices</a></li>
						<li class="breadcrumb-item active"><a href="#">Invoice detail</a></li>
					</ol>
				</nav>
				<?php if ($this->session->flashdata('alert')) { ?>
					<?= $this->session->flashdata('alert') ?>
				<?php } ?>

			</div>

			<div class="col-md-12 mt-2">
				<div class="row">
					<div class="col-md-3 col-sm-12">
						<ul class="list-group list-group-flush">
							<li class="list-group-item">
								<button type="button" class="btn btn-outline-primary w-100" onclick="downloadInvoiceDetail()"><i class="fa fa-file-pdf"></i> Download</button>
							</li>
							<li class="list-group-item">
								<button type="button" class="btn btn-outline-warning w-100"><i class="fa fa-dollar-sign"></i> Paynow</button>
							</li>
						</ul>
					</div>
					<div class="col-md-9 col-sm-12 p-4 pdfViewContainer"> <!-- height: 1123px; width: 794px; 96DPI A4 Size -->

						<div class="pdfViewBox">

							<?php echo $htmlData;?>

						</div>
					</div>
				</div>
			</div>
        </div>
    </div><!-- container -->
</div><!-- content -->

<?php $this->load->view('whmazadmin/include/footer_script');?>

<script>
	function downloadInvoiceDetail() {
		console.log("downloading...")
		window.location = "<?=base_url()?>whmazadmin/invoice/download_invoice/<?=$company_id?>/<?=$invoice_uuid?>";
	}
</script>

<?php $this->load->view('whmazadmin/include/footer');?>
