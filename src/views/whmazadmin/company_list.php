<?php $this->load->view('whmazadmin/include/header');?>

	 <div class="content content-fluid content-wrapper">
      <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

        <div class="row mt-5">
			<div class="col-md-12 col-sm-12">
				<h3 class="d-flex justify-content-between"><span>Companies</span> <a href="<?=base_url()?>whmazadmin/company/manage" class="btn btn-sm btn-secondary"><i class="fa fa-plus-square"></i>&nbsp;Add</a></h3>
				<hr class="mg-5" />
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb breadcrumb-style1 mg-b-0">
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
						<li class="breadcrumb-item active"><a href="#">Companies</a></li>
					</ol>
				</nav>
			  <?php if ($this->session->flashdata('alert')) { ?>
				<?= $this->session->flashdata('alert') ?>
			  <?php } ?>

			  <?php
			  // SECURITY: Display temporary credentials for newly created company
			  if ($this->session->flashdata('new_user_credentials')) {
				  $credentials = $this->session->flashdata('new_user_credentials');
			  ?>
				<div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
					<strong><i class="fa fa-key"></i> IMPORTANT - New Company Credentials</strong>
					<hr>
					<p class="mb-2"><strong>Company:</strong> <?= htmlspecialchars($credentials['company_name'], ENT_QUOTES, 'UTF-8'); ?></p>
					<p class="mb-2"><strong>Email/Username:</strong> <?= htmlspecialchars($credentials['email'], ENT_QUOTES, 'UTF-8'); ?></p>
					<p class="mb-2">
						<strong>Temporary Password:</strong>
						<code class="bg-dark text-white px-2 py-1" style="font-size: 1.1em;"><?= htmlspecialchars($credentials['password'], ENT_QUOTES, 'UTF-8'); ?></code>
						<button type="button" class="btn btn-sm btn-outline-secondary ml-2" onclick="copyToClipboard('<?= htmlspecialchars($credentials['password'], ENT_QUOTES, 'UTF-8'); ?>')">
							<i class="fa fa-copy"></i> Copy
						</button>
					</p>
					<hr>
					<p class="mb-0">
						<i class="fa fa-exclamation-triangle"></i>
						<strong>SECURITY NOTICE:</strong> Please copy these credentials and share them securely with the customer.
						<span class="text-danger">The customer should change their password immediately after first login.</span>
					</p>
					<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
			  <?php } ?>

			</div>

			<div class="col-md-12 col-sm-12 mt-5">
				<table id="listDataTable" class="table table-hover">
					<thead>
					<tr>
						<th class="wd-20p">Company name</th>
						<th class="wd-20p text-center">Email</th>
						<th class="wd-15p text-center">Mobile</th>
						<th class="wd-5p text-center">Zip</th>
						<th class="wd-15p text-center">Country</th>
						<th class="wd-5p text-center">Active?</th>
						<th class="wd-10p">Register date</th>
						<th class="wd-10p text-center">Action</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach($results as $row){ ?>
						<tr>
							<td><?= $row['name']; ?></td>
							<td><?= $row['email']; ?></td>
							<td><?= $row['mobile']; ?></td>
							<td><?= $row['zip_code']; ?></td>
							<td><?= $row['country']; ?></td>
							<td class="text-center">
								<?= getRowStatus($row['status']) ?>
							</td>
							<td><?= explode(" ", $row['inserted_on'])[0]; ?></td>
							<td class="text-center">
								<button type="button" class="btn btn-xs btn-secondary" onclick="openManage('<?=safe_encode($row['id'])?>')" title="Manage"><i class="fa fa-wrench"></i></button>
								<button type="button" class="btn btn-xs btn-danger" onclick="deleteRow('<?=safe_encode($row['id'])?>', '<?= $row['name']?>')" title="Delete"><i class="fa fa-trash"></i></button>
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
		toastSuccess(<?= json_encode($this->session->flashdata('alert_success')) ?>);
	<?php } ?>
	<?php if ($this->session->flashdata('alert_error')) { ?>
		toastError(<?= json_encode($this->session->flashdata('alert_error')) ?>);
	<?php } ?>

			$('#listDataTable').DataTable();

      });

	  function openManage(id) {
		  window.location = "<?=base_url()?>whmazadmin/company/manage/"+id;
	  }

	  // SECURITY: Copy password to clipboard securely
	  function copyToClipboard(text) {
		  // Modern clipboard API
		  if (navigator.clipboard && window.isSecureContext) {
			  navigator.clipboard.writeText(text).then(function() {
				  toastSuccess('Password copied to clipboard!');
			  }, function(err) {
				  // Fallback for older browsers
				  fallbackCopyToClipboard(text);
			  });
		  } else {
			  // Fallback for older browsers or non-HTTPS
			  fallbackCopyToClipboard(text);
		  }
	  }

	  function fallbackCopyToClipboard(text) {
		  const textArea = document.createElement("textarea");
		  textArea.value = text;
		  textArea.style.position = "fixed";
		  textArea.style.left = "-999999px";
		  textArea.style.top = "-999999px";
		  document.body.appendChild(textArea);
		  textArea.focus();
		  textArea.select();
		  try {
			  document.execCommand('copy');
			  toastSuccess('Password copied to clipboard!');
		  } catch (err) {
			  toastError('Failed to copy password. Please copy manually.');
		  }
		  document.body.removeChild(textArea);
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
				  window.location = "<?=base_url()?>whmazadmin/company/delete_records/"+id;
				  console.log('success');
			  } else if (result.isDenied) {
				  console.log('Changes are not saved');
			  }
		  });
	  }
    </script>
<?php $this->load->view('whmazadmin/include/footer');?>
