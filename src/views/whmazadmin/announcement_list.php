<?php $this->load->view('whmazadmin/include/header');?>

	 <div class="content content-fluid content-wrapper">
      <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

        <div class="row mt-5">
			<div class="col-md-12 col-sm-12">
				<h3 class="d-flex justify-content-between"><span>Announcements</span> <a href="<?=base_url()?>whmazadmin/announcement/manage" class="btn btn-sm btn-secondary"><i class="fa fa-plus-square"></i>&nbsp;Add</a></h3>
				<hr class="mg-5" />
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb breadcrumb-style1 mg-b-0">
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
						<li class="breadcrumb-item active"><a href="#">Announcements</a></li>
					</ol>
				</nav>
			  <?php if ($this->session->flashdata('alert')) { ?>
				<?= $this->session->flashdata('alert') ?>
			  <?php } ?>

			</div>

			<div class="col-md-12 col-sm-12 mt-5">
				<table id="announcementListDt" class="table table-striped table-hover"></table>
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
		toastSuccess('<?= addslashes($this->session->flashdata('alert_success')) ?>');
	<?php } ?>
	<?php if ($this->session->flashdata('alert_error')) { ?>
		toastError('<?= addslashes($this->session->flashdata('alert_error')) ?>');
	<?php } ?>

			$('#announcementListDt').DataTable({
			"responsive": true,
			"processing": true,
			"serverSide": true,
			"ajax": {
				"url": "<?=base_url()?>" + "whmazadmin/announcement/ssp_list_api/",
			},
			order: [[4, 'desc']],
			"columns": [
				{ "title": "Title", "data": "title" },
				{
					"title": "Published?",
					"data": "is_published",
					"orderable": false,
					"searchable": false,
					render: function (data, type) {
						if( data == 1 ){
							return '<span class="badge bg-success">Yes</span>';
						} else {
							return '<span class="badge bg-secondary">No</span>';
						}
					}
				},
				{ "title": "Publish date", "data": "publish_date", "searchable": true },
				{ "title": "Total views", "data": "total_view", "searchable": false },
				{ "title": "Last updated", "data": "updated_on", "searchable": false },
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
						let idVal = safe_encode(data);
						return '<button type="button" class="btn btn-sm btn-outline-secondary edit-button" onclick="openManage(\''+idVal+'\')" title="Edit"><i class="fa fa-pencil-alt"></i></button>'
							+ '&nbsp;<button class="btn btn-sm btn-outline-danger delete-button" onclick="deleteRow(\''+idVal+'\', \''+row['title']+'\')" type="button" title="Delete"><i class="fa fa-trash"></i></button>';
					}
				}
			]
		});

      });

	  function openManage(id) {
		  window.location = "<?=base_url()?>whmazadmin/announcement/manage/"+id;
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
				  window.location = "<?=base_url()?>whmazadmin/announcement/delete_records/"+id;
				  console.log('success');
			  } else if (result.isDenied) {
				  console.log('Changes are not saved');
			  }
		  });
	  }
    </script>
<?php $this->load->view('whmazadmin/include/footer');?>
