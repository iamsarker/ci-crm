<?php $this->load->view('templates/customer/header');?>

	 <div class="content content-fixed content-wrapper">
      <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        

        <div class="row">
          <div class="col-md-3 col-sm-12">
            
            <div class="card card-widget card-contacts">
			  <div class="card-header">
				<h6 class="card-title mg-b-0"><i class="fa fa-tachometer-alt"></i>&nbsp;Invoice Summary</h6>
				<nav class="nav">

				</nav>
			  </div>
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




        <div class="col-md-9 col-sm-12">
			<h3>My Invoices</h3>
			<hr class="mg-5" />
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb breadcrumb-style1 mg-b-0">
					<li class="breadcrumb-item"><a href="<?=base_url()?>clientarea">Portal home</a></li>
					<li class="breadcrumb-item active"><a>My Invoices</a></li>
				</ol>
			</nav>
          <div data-label="Example" class="df-example demo-table mg-t-25">
            <table id="example1" class="table table-hover">
              <thead>
                  <tr>
                      <th class="wd-15p">Invoice#</th>
                      <th class="wd-15p">Amount</th>
                      <th class="wd-20p">Order Date</th>
                      <th class="wd-20p">Due Date</th>
                      <th class="wd-15p">Status</th>
                      <th class="wd-15p">Action</th>
                  </tr>
              </thead>
              <tbody>
                  <?php foreach($results as $row){ ?>
                      <tr>
                          <td style="cursor: pointer;" onclick="viewInvoiceDetail('<?=$row['invoice_uuid']?>')">#<?php echo $row['invoice_no']; ?></td>
                          <td><?php echo $row['total'].' '.$row['currency_code']; ?></td>
                          <td><?php echo $row['order_date']; ?></td>
                          <td><?php echo $row['due_date']; ?></td>
                          <td><?php echo $row['pay_status']; ?></td>
                          <td>
							  <ul class="btn-group mb-0">
								  <button class="btn btn-light btn-sm" type="button">
									  <i class="fa fa-cog"></i>
								  </button>
								  <button type="button" class="btn btn-sm btn-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false"><span class="visually-hidden">Toggle Dropdown</span></button>
								  <ul class="dropdown-menu">
									  <li><a class="dropdown-item" style="cursor: pointer;" onclick="viewInvoiceDetail('<?=$row['invoice_uuid']?>')"><i class="fa fa-eye text-info"></i> View</a></li>
									  <li><a class="dropdown-item" style="cursor: pointer;" onclick="downloadInvoiceDetail('<?=$row['invoice_uuid']?>')"><i class="fa fa-file-pdf text-danger"></i> Download</a></li>
									  <?php if( $row['pay_status'] != "PAID" ){ ?>
									  <li><hr class="dropdown-divider" /></li>
									  <li><a class="dropdown-item" style="cursor: pointer;"><i class="fa fa-money-bill-alt text-success"></i> Pay now</a></li>
									  <?php } ?>
								  </ul>
							  </div>
						  </td>
                      </tr>
                  <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
		
    </div><!-- container -->
  </div><!-- content -->

<?php $this->load->view('templates/customer/footer_script');?>
<script>
      $(function(){
        'use strict'
        $('#example1').DataTable({
          "aaSorting": [],
          language: {
            searchPlaceholder: 'Search...',
            sSearch: '',
            lengthMenu: '_MENU_ items/page',
          }
        });

      });

      function viewInvoiceDetail(invoice_uuid) {
		window.location = "<?=base_url()?>billing/view_invoice/"+invoice_uuid;
	  }

	  function downloadInvoiceDetail(invoice_uuid) {
		  console.log("downloading...")
		  window.location = "<?=base_url()?>billing/download_invoice/"+invoice_uuid;
	  }

	  <?php $alert_success = $this->session->flashdata('alert_success'); ?>
	  <?php if ($alert_success) { ?>
		toastSuccess(<?= json_encode(htmlspecialchars($alert_success, ENT_QUOTES, 'UTF-8')) ?>);
	  <?php } ?>
	  <?php $alert_error = $this->session->flashdata('alert_error'); ?>
	  <?php if ($alert_error) { ?>
		toastError(<?= json_encode(htmlspecialchars($alert_error, ENT_QUOTES, 'UTF-8')) ?>);
	  <?php } ?>
    </script>
<?php $this->load->view('templates/customer/footer');?>
