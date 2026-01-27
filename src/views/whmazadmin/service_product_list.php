<?php $this->load->view('whmazadmin/include/header');?>

	 <div class="content content-fluid content-wrapper">
      <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

        <div class="row mt-5">
			<div class="col-md-12 col-sm-12">
				<h3 class="d-flex justify-content-between"><span>Service Products</span> <a href="<?=base_url()?>whmazadmin/service_product/manage" class="btn btn-sm btn-secondary"><i class="fa fa-plus-square"></i>&nbsp;Add</a></h3>
				<hr class="mg-5" />
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb breadcrumb-style1 mg-b-0">
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
						<li class="breadcrumb-item active"><a href="#">Service products</a></li>
					</ol>
				</nav>
			  <?php if ($this->session->flashdata('alert')) { ?>
				<?= $this->session->flashdata('alert') ?>
			  <?php } ?>

			</div>

			<div class="col-md-12 col-sm-12 mt-5">
				<table id="listDataTable" class="table table-striped table-hover" style="width: 100%"></table>
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

			$('#listDataTable').DataTable({
				"responsive": true,
				"processing": true,
				"serverSide": true,
				"ajax": {
					"url": "<?=base_url()?>whmazadmin/service_product/ssp_list_api/",
				},
				order: [[0, 'desc']],
				"columns": [
					{ "title": "ID", "data": "id", "width": "5%", "visible": false },
					{ "title": "Product Name", "data": "product_name", "width": "20%", render: function(data){ return escapeXSS(data); } },
					{ "title": "Service Group", "data": "group_name", "width": "15%", render: function(data){ return escapeXSS(data); } },
					{ "title": "Service Type", "data": "servce_type_name", "width": "15%", render: function(data){ return escapeXSS(data); } },
					{ "title": "Module", "data": "module_name", "width": "10%", render: function(data){ return escapeXSS(data); } },
					{ "title": "cPanel Pkg", "data": "cp_package", "width": "10%",
						render: function(data){
							return data ? escapeXSS(data) : '<span class="text-muted">-</span>';
						}
					},
					{
						"title": "Hidden?", "data": "is_hidden", "width": "5%",
						"className": "text-center",
						"orderable": false,
						"searchable": false,
						render: function (data) {
							if( parseInt(data) === 1 ){
								return '<span class="badge bg-warning">Yes</span>';
							} else {
								return '<span class="badge bg-success">No</span>';
							}
						}
					},
					{
						"title": "Active?", "data": "status", "width": "5%",
						"className": "text-center",
						"orderable": false,
						"searchable": false,
						render: function (data) {
							if( data == 1 ){
								return '<span class="badge bg-primary">Yes</span>';
							} else {
								return '<span class="badge bg-danger">No</span>';
							}
						}
					},
					{ "title": "Last Updated", "data": "updated_on", "width": "10%", "searchable": false },
					{
						"title" : 'Action',
						"data" : "id",
						"width": "10%",
						"className": "text-center",
						"orderable": false,
						"searchable": false,
						"render": function (data, type, row) {
							let idVal = safe_encode(data);
							return '<button type="button" class="btn btn-xs btn-secondary" onclick="openManage(\''+idVal+'\')" title="Manage"><i class="fa fa-wrench"></i></button> '
								+ '<button type="button" class="btn btn-xs btn-danger" onclick="deleteRow(\''+idVal+'\', \''+escapeXSS(row['product_name'])+'\')" title="Delete"><i class="fa fa-trash"></i></button>';
						}
					}
				]
			});

      });

	  function openManage(id) {
		  window.location = "<?=base_url()?>whmazadmin/service_product/manage/"+id;
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
				  window.location = "<?=base_url()?>whmazadmin/service_product/delete_records/"+id;
				  console.log('success');
			  } else if (result.isDenied) {
				  console.log('Changes are not saved');
			  }
		  });
	  }
    </script>
<?php $this->load->view('whmazadmin/include/footer');?>
