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
				<table id="listDataTable" class="table table-hover">
					<thead>
					<tr>
						<th class="wd-15p">Expense type</th>
						<th class="wd-15p">Vendor name</th>
						<th class="wd-10p">Amount</th>
						<th class="wd-10p">Paid</th>
						<th class="wd-20p">Remarks</th>
						<th class="wd-15p">Expense date</th>
						<th class="wd-20p text-center">Action</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach($results as $row){ ?>
						<tr>
							<td><?php echo $row['expense_type']; ?></td>
							<td><?php echo $row['vendor_name']; ?></td>
							<td><?php echo $row['exp_amount']; ?></td>
							<td><?php echo $row['paid_amount']; ?></td>
							<td><?php echo $row['remarks']; ?></td>
							<td><?php echo $row['expense_date']; ?></td>
							<td class="text-center">
								<button type="button" class="btn btn-xs btn-secondary" onclick="openManage('<?=safe_encode($row['id'])?>')" title="Manage"><i class="fa fa-wrench"></i></button>
								<button type="button" class="btn btn-xs btn-danger" onclick="deleteRow('<?=safe_encode($row['id'])?>', '<?= $row['expense_type']?>')" title="Delete"><i class="fa fa-trash"></i></button>
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
