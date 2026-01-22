<?php $this->load->view('whmazadmin/include/header');?>

	 <div class="content content-fluid content-wrapper">
      <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

        <div class="row mt-5">
			<div class="col-md-12 col-sm-12">
				<h3 class="d-flex justify-content-between"><span>Package Pricing</span> <a href="<?=base_url()?>whmazadmin/package/manage" class="btn btn-sm btn-secondary"><i class="fa fa-plus-square"></i>&nbsp;Add</a></h3>
				<hr class="mg-5" />
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb breadcrumb-style1 mg-b-0">
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
						<li class="breadcrumb-item active"><a href="#">Package Pricing</a></li>
					</ol>
				</nav>
			</div>

			<div class="col-md-12 col-sm-12 mt-5">
				<table id="listDataTable" class="table table-striped table-hover"></table>
			</div>
      </div>

    </div><!-- container -->
  </div><!-- content -->

<?php $this->load->view('whmazadmin/include/footer_script');?>
<script>
      // Helper function to escape HTML
      function escapeHtml(text) {
          var map = {
              '&': '&amp;',
              '<': '&lt;',
              '>': '&gt;',
              '"': '&quot;',
              "'": '&#039;'
          };
          return text.replace(/[&<>"']/g, function(m) { return map[m]; });
      }

      $(function(){
        	'use strict'

			// Show flash messages as toast
			<?php if ($this->session->flashdata('alert_success')) { ?>
				toastSuccess('<?= addslashes($this->session->flashdata('alert_success')) ?>');
			<?php } ?>
			<?php if ($this->session->flashdata('alert_error')) { ?>
				toastError('<?= addslashes($this->session->flashdata('alert_error')) ?>');
			<?php } ?>

			$('#listDataTable').DataTable({
				"responsive": true,
				"processing": true,
				"serverSide": true,
				"ajax": {
					"url": "<?=base_url()?>whmazadmin/package/ssp_list_api/",
				},
				order: [[0, 'desc']],
				"columns": [
					{ "title": "ID", "data": "id", "width": "5%" },
					{ "title": "Product Service", "data": "product_name", "width": "20%" },
					{
						"title": "Currency", "data": "currency_code", "width": "10%",
						"orderable": false,
						"render": function (data, type, row) {
							return row.currency_symbol + ' (' + row.currency_code + ')';
						}
					},
					{ "title": "Billing Cycle", "data": "cycle_name", "width": "15%" },
					{
						"title": "Price", "data": "price", "width": "10%",
						"className": "text-right",
						"render": function (data, type) {
							return parseFloat(data).toFixed(2);
						}
					},
					{
						"title": "Active?", "data": "status", "width": "10%",
						"className": "text-center",
						"orderable": false,
						"searchable": false,
						"render": function (data, type) {
							if (data == 1) {
								return '<span class="badge bg-primary">Yes</span>';
							} else {
								return '<span class="badge bg-danger">No</span>';
							}
						}
					},
					{ "title": "Last Updated", "data": "updated_on", "width": "15%" },
					{ "title": "encoded_id", "data": "encoded_id", "visible": false, "orderable": false, "searchable": false },
					{
						"title": "Action",
						"data": "encoded_id",
						"width": "15%",
						"className": "text-center",
						"orderable": false,
						"searchable": false,
						"render": function (data, type, row) {
							return '<button type="button" class="btn btn-xs btn-secondary" onclick="openManage(\'' + data + '\')" title="Manage"><i class="fa fa-wrench"></i></button> ' +
								   '<button type="button" class="btn btn-xs btn-danger" onclick="deleteRow(\'' + data + '\', \'' + escapeHtml(row.product_name) + '\')" title="Delete"><i class="fa fa-trash"></i></button>';
						}
					}
				]
			});

      });

	  function openManage(id) {
		  window.location = "<?=base_url()?>whmazadmin/package/manage/"+id;
	  }

	  function deleteRow(id, title) {
		  Swal.fire({
			  title: 'Do you want to delete the (<b>'+title+'</b>) pricing record?',
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
				  window.location = "<?=base_url()?>whmazadmin/package/delete_records/"+id;
				  console.log('success');
			  } else if (result.isDenied) {
				  console.log('Changes are not saved');
			  }
		  });
	  }
    </script>
<?php $this->load->view('whmazadmin/include/footer');?>
