<?php $this->load->view('templates/customer/header'); ?>
<div class="content content-fixed content-wrapper">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0 mt-2">
        <div class="row">
			<div class="col-md-3 col-sm-12">

				<div class="card card-widget card-contacts">
					<div class="card-header">
						<h6 class="card-title mg-b-0"><i class="fa fa-tachometer-alt"></i>&nbsp;Invoice Summary</h6>
						<nav class="nav">

						</nav>
					</div><!-- card-header -->
					<ul class="list-group list-group-flush">
						<li class="list-group-item">
							Total&nbsp;<span class="badge rounded-pill bg-info float-right"><?=($summary['paid']+$summary['due']+$summary['partialy'])?></span>
						</li>
						<li class="list-group-item">
							Paid&nbsp;<span class="badge rounded-pill bg-success float-right"><?=$summary['paid']?></span>
						</li>
						<li class="list-group-item">
							Due&nbsp;<span class="badge rounded-pill bg-danger float-right"><?=$summary['due']?></span>
						</li>
						<li class="list-group-item">
							Partial&nbsp;<span class="badge rounded-pill bg-warning float-right"><?=$summary['partialy']?></span>
						</li>
					</ul>
				</div>

				<?php $this->load->view('templates/customer/invoice_nav');?>


			</div>

            <div class="col-md-9">
                <h3>Viewing Invoice #<?= $invoice['invoice_no'] ?></h3>
                <hr class="mg-5" />
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mg-b-0">
                        <li class="breadcrumb-item"><a href="<?= base_url() ?>clientarea">Portal home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url() ?>billing/invoices">Invoices</a></li>
                        <li class="breadcrumb-item active" aria-current="#">View Invoice</li>
                    </ol>
                </nav>


                
                
				<div class="row">
					<div class="col-12 p-4 pdfViewContainer"> <!-- height: 1123px; width: 794px; 96DPI A4 Size -->

						<div class="pdfViewBox">

							<?php echo $htmlData;?>

						</div>
					</div>
				</div>

            </div>
        </div>
    </div><!-- container -->
</div><!-- content -->

<?php $this->load->view('templates/customer/footer_script'); ?>
<?php $this->load->view('templates/customer/footer'); ?>
