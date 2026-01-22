<?php $this->load->view('whmazadmin/include/header');?>

	 <div class="content content-fluid content-wrapper">
      <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

        <div class="row mt-5">
			<div class="col-md-12 col-sm-12">
				<h3 class="d-flex justify-content-between"><span>Service Modules</span> <a href="<?=base_url()?>whmazadmin/service_module/manage" class="btn btn-sm btn-secondary"><i class="fa fa-plus-square"></i>&nbsp;Add</a></h3>
				<hr class="mg-5" />
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb breadcrumb-style1 mg-b-0">
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
						<li class="breadcrumb-item active"><a href="#">Service modules</a></li>
					</ol>
				</nav>
			  <?php if ($this->session->flashdata('alert')) { ?>
				<?= $this->session->flashdata('alert') ?>
			  <?php } ?>

			</div>

			<div class="col-md-12 col-sm-12 mt-5">
				<table id="listDataTable" class="table table-hover">
					<thead>
					<tr>
						<th class="wd-15p">Group module</th>
						<th class="wd-15p">Remarks</th>
						<th class="wd-10p text-center">Active?</th>
						<th class="wd-15p">Last updated</th>
						<th class="wd-25p text-center">Action</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach($results as $row){ ?>
						<tr>
							<td><?php echo $row['module_name']; ?></td>
							<td><?php echo $row['remarks']; ?></td>
							<td class="text-center">
								<?= getRowStatus($row['status']) ?>
							</td>
							<td><?php echo $row['updated_on']; ?></td>
							<td class="text-center">
								<button type="button" class="btn btn-xs btn-secondary" onclick="openManage('<?=safe_encode($row['id'])?>')" title="Manage"><i class="fa fa-wrench"></i></button>
								<button type="button" class="btn btn-xs btn-danger" onclick="deleteRow('<?=safe_encode($row['id'])?>', '<?= $row['module_name']?>')" title="Delete"><i class="fa fa-trash"></i></button>
							</td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
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

			$('#listDataTable').DataTable();

      });

	  function openManage(id) {
		  window.location = "<?=base_url()?>whmazadmin/service_module/manage/"+id;
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
				  window.location = "<?=base_url()?>whmazadmin/service_module/delete_records/"+id;
				  console.log('success');
			  } else if (result.isDenied) {
				  console.log('Changes are not saved');
			  }
		  });
	  }
    </script>
<?php $this->load->view('whmazadmin/include/footer');?>
