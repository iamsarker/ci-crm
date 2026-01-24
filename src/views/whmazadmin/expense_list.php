<?php $this->load->view('whmazadmin/include/header');?>

	 <div class="content content-fluid content-wrapper">
      <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

        <div class="row mt-5">
			<div class="col-md-12 col-sm-12">
				<h3 class="d-flex justify-content-between"><span>Expenses</span> <a href="<?=base_url()?>whmazadmin/expense/manage" class="btn btn-sm btn-secondary"><i class="fa fa-plus-square"></i>&nbsp;Add</a></h3>
				<hr class="mg-5" />
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb breadcrumb-style1 mg-b-0">
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
						<li class="breadcrumb-item active"><a href="#">Expenses</a></li>
					</ol>
				</nav>
			  <?php if ($this->session->flashdata('alert')) { ?>
				<?= $this->session->flashdata('alert') ?>
			  <?php } ?>

			</div>

			<div class="col-md-12 col-sm-12 mt-5">
				<table id="expenseListDt" class="table table-striped table-hover"></table>
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

			$('#expenseListDt').DataTable({
			"responsive": true,
			"processing": true,
			"serverSide": true,
			"ajax": {
				"url": "<?=base_url()?>" + "whmazadmin/expense/ssp_list_api/",
			},
			order: [[5, 'desc']],
			"columns": [
				{ "title": "Expense type", "data": "expense_type", render: function(data){return escapeXSS(data);} },
				{ "title": "Vendor name", "data": "vendor_name", render: function(data){return escapeXSS(data);} },
				{ "title": "Amount", "data": "exp_amount" },
				{ "title": "Paid", "data": "paid_amount" },
				{ "title": "Remarks", "data": "remarks", render: function(data){return escapeXSS(data);} },
				{ "title": "Expense date", "data": "expense_date", "searchable": true },
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
						let id_val = safe_encode(data);
						return '<button type="button" class="btn btn-sm btn-outline-secondary edit-button" onclick="openManage(\''+id_val+'\')" title="Edit"><i class="fa fa-pencil-alt"></i></button>'
							+ '&nbsp;<button class="btn btn-sm btn-outline-danger delete-button" onclick="deleteRow(\''+id_val+'\', \''+escapeXSS(row['expense_type'])+'\')" type="button" title="Delete"><i class="fa fa-trash"></i></button>';
					}
				}
			]
		});

      });

	  function openManage(id) {
		  window.location = "<?=base_url()?>whmazadmin/expense/manage/"+id;
	  }

	  function deleteRow(id, title) {

		  Swal.fire({
			  title: 'Do you want to delete the (<b>'+title+'</b>) record?',
			  showDenyButton: true,
			  icon: 'question',
			  confirmButtonText: 'Yes, delete',
			  denyButtonText: 'No, cancel',
			  customClass: {
				  actions: 'my-actions',
				  denyButton: 'order-1 right-gap',
				  confirmButton: 'order-2',
			  },
		  }).then((result) => {
			  if (result.isConfirmed) {
				  window.location = "<?=base_url()?>whmazadmin/expense/delete_records/"+id;
				  console.log('success');
			  } else if (result.isDenied) {
				  console.log('Changes are not saved');
			  }
		  });
	  }
    </script>
<?php $this->load->view('whmazadmin/include/footer');?>
