<?php $this->load->view('whmazadmin/include/header');?>

	 <div class="content content-fluid content-wrapper">
      <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

        <div class="row mt-5">
			<div class="col-md-12 col-sm-12">
				<h3 class="d-flex justify-content-between"><span>Invoices</span> <a href="<?=base_url()?>whmazadmin/invoice/manage" class="btn btn-sm btn-secondary"><i class="fa fa-plus-square"></i>&nbsp;Add</a></h3>
				<hr class="mg-5" />
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb breadcrumb-style1 mg-b-0">
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
						<li class="breadcrumb-item active"><a href="#">Invoices</a></li>
					</ol>
				</nav>
			  <?php if ($this->session->flashdata('alert')) { ?>
				<?= $this->session->flashdata('alert') ?>
			  <?php } ?>

			</div>

			<div class="col-md-12 col-sm-12 mt-5">
				<table id="invoiceListDt" class="table table-striped table-hover"></table>
			</div>
      </div>
		
    </div><!-- container -->
  </div><!-- content -->

<?php $this->load->view('whmazadmin/include/footer_script');?>
<script>
      $(function(){
        	'use strict'
	// SECURITY: Show flash messages as toast with XSS protection
	<?php if ($this->session->flashdata('alert_success')) { ?>
		toastSuccess(<?= json_encode($this->session->flashdata('alert_success')) ?>);
	<?php } ?>
	<?php if ($this->session->flashdata('alert_error')) { ?>
		toastError(<?= json_encode($this->session->flashdata('alert_error')) ?>);
	<?php } ?>


			$('#invoiceListDt').DataTable({
				"responsive": true,
				"processing": true,
				"serverSide": true,
				"ajax": {
					"url": "<?=base_url()?>" + "whmazadmin/invoice/ssp_list_api/",
				},
				order: [[0, 'desc']],
				"columns": [
					{ "title": "Invoice#", "data": "invoice_no", render: function(data){return escapeXSS(data);} },
					{ "title": "Order#", "data": "order_no", render: function(data){return escapeXSS(data);} },
					{ "title": "Company name", "data": "company_name", render: function(data){return escapeXSS(data);} },
					{ "title": "Total", "data": "total" },
					{ "title": "Currency", "data": "currency_code", render: function(data){return escapeXSS(data);} },
					{ "title": "Due date", "data": "due_date", "searchable": true },
					{ "title": "invoice_uuid", "data": "invoice_uuid", "orderable": false, "searchable": false, "visible":false },
					{ "title": "company_id", "data": "company_id", "orderable": false, "searchable": false, "visible":false },
					{
						"title": "Pay status", "data": "pay_status", "orderable": false, "searchable": false,
						render: function (data, type) {
							if( data == 'DUE' ){
								return '<span class="badge bg-danger">Due</span>';
							} else if( data == 'PAID' ){
								return '<span class="badge bg-success">Paid</span>';
							} else {
								return '<span class="badge bg-warning">Partial</span>';
							}
						}
					},
					{
						"title": "Active?", "data": "status", "orderable": false, "searchable": false,
						render: function (data, type) {
							if( data == 1 ){
								return '<span class="badge bg-primary">Yes</span>';
							} else {
								return '<span class="badge bg-danger">No</span>';
							}
						}
					},
					{
						"title" : 'Action',
						"data" : "id",
						"orderable": false,
						"searchable": false,
						"render": function (data, type, row, meta) {

							let paidButtonHtml = '';

							if( row['pay_status'] != "PAID" ){
								paidButtonHtml = '<li><hr class="dropdown-divider"></li>'+
									'<li><a class="dropdown-item" style="cursor: pointer;" onclick="markAsPaid(\''+row['invoice_uuid']+'\')" title="Mark as Paid"><i class="fa fa-money-bill-alt text-success"></i> Mark as Paid</a></li>';
							}

							return '<div class="btn-group mb-0">'+
								'<button class="btn btn-light btn-sm" type="button"><i class="fa fa-cog"></i></button>'+
								'<button type="button" class="btn btn-sm btn-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false"><span class="visually-hidden">Toggle Dropdown</span></button>'+
								'<ul class="dropdown-menu">'+
									'<li><a class="dropdown-item" style="cursor: pointer;" onclick="viewInvoiceDetail('+row['company_id']+',\''+row['invoice_uuid']+'\')"><i class="fa fa-eye text-info"></i> View</a></li>'+
									'<li><a class="dropdown-item" style="cursor: pointer;" onclick="downloadInvoiceDetail('+row['company_id']+',\''+row['invoice_uuid']+'\')"><i class="fa fa-file-pdf text-danger"></i> Download</a></li>'+
									paidButtonHtml +
								'</ul>'+
							'</div>';
						}
					}
				]
			});

      });

	  function viewInvoiceDetail(company_id,invoice_uuid) {
		  window.location = "<?=base_url()?>whmazadmin/invoice/view_invoice/"+company_id+"/"+invoice_uuid;
	  }

	  function downloadInvoiceDetail(company_id,invoice_uuid) {
		  console.log("downloading...")
		  window.location = "<?=base_url()?>whmazadmin/invoice/download_invoice/"+company_id+"/"+invoice_uuid;
	  }

	  function markAsPaid(invoiceUuid) {
		  if (!confirm('Are you sure you want to mark this invoice as paid?')) {
			  return;
		  }

		  $.ajax({
			  url: "<?=base_url()?>whmazadmin/invoice/mark_as_paid",
			  type: "POST",
			  contentType: "application/json",
			  data: JSON.stringify({
				  invoice_uuid: invoiceUuid
			  }),
			  dataType: "json",
			  beforeSend: function() {
				  // Disable button to prevent multiple clicks
				  $('button[onclick="markAsPaid()"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
			  },
			  success: function(response) {
				  if (response.success) {
					  toastSuccess(response.message);
					  // Reload page after 1 second to show updated status
					  setTimeout(function() {
						  location.reload();
					  }, 1000);
				  } else {
					  toastError(response.message);
					  // Re-enable button on error
					  $('button[onclick="markAsPaid()"]').prop('disabled', false).html('<i class="fa fa-dollar-sign"></i> Mark as Paid');
				  }
			  },
			  error: function(xhr, status, error) {
				  toastError('An error occurred while updating the invoice status');
				  // Re-enable button on error
				  $('button[onclick="markAsPaid()"]').prop('disabled', false).html('<i class="fa fa-dollar-sign"></i> Mark as Paid');
			  }
		  });
	  }

    </script>
<?php $this->load->view('whmazadmin/include/footer');?>
