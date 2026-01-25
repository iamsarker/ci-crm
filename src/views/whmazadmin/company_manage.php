<?php $this->load->view('whmazadmin/include/header');?>

	 <div class="content content-fluid content-wrapper">
      <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

        <div class="row mt-5">
			<div class="col-md-12 col-sm-12">
				<h3 class="d-flex justify-content-between"><span>Companies</span> <a href="<?=base_url()?>whmazadmin/company/index" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i>&nbsp;Back</a></h3>
				<hr class="mg-5" />
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb breadcrumb-style1 mg-b-0">
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/company/index">Companies</a></li>
						<li class="breadcrumb-item active"><a href="#">Manage company</a></li>
					</ol>
				</nav>
			  <?php if ($this->session->flashdata('alert')) { ?>
				<?= $this->session->flashdata('alert') ?>
			  <?php } ?>

			</div>

			<div class="col-md-12 col-sm-12 mt-5">

				<ul class="nav nav-tabs" id="pageTab" role="pageTablist">
					<li class="nav-item">
						<a class="nav-link active" id="info-tab" data-bs-toggle="tab" href="#general-info" role="tab" aria-controls="general-info" aria-selected="true"><i class="fa fa-info-circle"></i>&nbsp;<span class="pt-1">General info</span></a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="service-tab" data-bs-toggle="tab" href="#service-info" role="tab" aria-controls="service-info" aria-selected="false"><i class="fa fa-sliders-h"></i>&nbsp;<span class="pt-1">Services</span></a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="domain-tab" data-bs-toggle="tab" href="#domain-info" role="tab" aria-controls="domain-info" aria-selected="false"><i class="fa fa-globe"></i>&nbsp;<span class="pt-1">Domains</span></a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="invoice-tab" data-bs-toggle="tab" href="#invoice-info" role="tab" aria-controls="invoice-info" aria-selected="false"><i class="fa fa-file-invoice"></i>&nbsp;<span class="pt-1">Invoices</span></a>
					</li>
				</ul>

				<div class="tab-content bd bd-gray-300 bd-t-0 pd-20" id="myTabContent">
					<div class="tab-pane fade show active" id="general-info" role="tabpanel" aria-labelledby="info-tab">

						<form method="post" name="entityManageForm" id="entityManageForm" action="<?=base_url()?>whmazadmin/company/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
							<?=csrf_field()?>
							<input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

							<div class="row">
								<div class="col-md-6 col-sm-12">
									<div class="form-group">
										<label for="name">Company name</label>
										<input name="name" type="text" class="form-control" id="name" value="<?= htmlspecialchars($detail['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('name', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-3 col-sm-12">
									<div class="form-group">
										<label for="first_name">First name</label>
										<input name="first_name" type="text" class="form-control" id="first_name" value="<?= htmlspecialchars($detail['first_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('first_name', '<div class="error">', '</div>'); ?>
									</div>
								</div>
								<div class="col-md-3 col-sm-12">
									<div class="form-group">
										<label for="last_name">Last name</label>
										<input name="last_name" type="text" class="form-control" id="last_name" value="<?= htmlspecialchars($detail['last_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('last_name', '<div class="error">', '</div>'); ?>
									</div>
								</div>
							</div>

							<div class="row mt-3">

								<div class="col-md-3 col-sm-12">
									<div class="form-group">
										<label for="email">Email</label>
										<input name="email" type="text" class="form-control" id="email" value="<?= htmlspecialchars($detail['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('email', '<div class="error">', '</div>'); ?>
									</div>
								</div>

								<div class="col-md-3 col-sm-12">
									<div class="form-group">
										<label for="mobile">Mobile</label>
										<input name="mobile" type="text" class="form-control" id="mobile" value="<?= htmlspecialchars($detail['mobile'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('mobile', '<div class="error">', '</div>'); ?>
									</div>
								</div>

								<div class="col-md-3 col-sm-12">
									<div class="form-group">
										<label for="phone">Phone</label>
										<input name="phone" type="text" class="form-control" id="phone" value="<?= htmlspecialchars($detail['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('phone', '<div class="error">', '</div>'); ?>
									</div>
								</div>

								<div class="col-md-3 col-sm-12">
									<div class="form-group">
										<label for="city">City</label>
										<input name="city" type="text" class="form-control" id="city" value="<?= htmlspecialchars($detail['city'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('city', '<div class="error">', '</div>'); ?>
									</div>
								</div>

							</div>

							<div class="row mt-3">
								<div class="col-md-3 col-sm-12">
									<div class="form-group">
										<label for="address">Address</label>
										<input name="address" type="text" class="form-control" id="address" value="<?= htmlspecialchars($detail['address'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('address', '<div class="error">', '</div>'); ?>
									</div>
								</div>

								<div class="col-md-3 col-sm-12">
									<div class="form-group">
										<label for="zip_code">Zip code</label>
										<input name="zip_code" type="text" class="form-control" id="zip_code" value="<?= htmlspecialchars($detail['zip_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('zip_code', '<div class="error">', '</div>'); ?>
									</div>
								</div>

								<div class="col-md-3 col-sm-12">
									<div class="form-group">
										<label for="state">State</label>
										<input name="state" type="text" class="form-control" id="state" value="<?= htmlspecialchars($detail['state'] ?? '', ENT_QUOTES, 'UTF-8') ?>"/>
										<?php echo form_error('state', '<div class="error">', '</div>'); ?>
									</div>
								</div>

								<div class="col-md-3 col-sm-12">
									<div class="form-group">
										<label for="Country">Country</label>
										<?php echo form_dropdown('country', $countries,!empty($detail['country']) ? $detail['country'] : 'Bangladesh','class="form-select select2" id="country"'); ?>
										<?php echo form_error('country', '<div class="error">', '</div>'); ?>
									</div>
								</div>
							</div>

							<div class="form-group">
								<button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-check-circle"></i>&nbsp;Save</button>
							</div>
						</form>

					</div>

					<div class="tab-pane fade" id="service-info" role="tabpanel" aria-labelledby="service-tab">
						<h6 class="mb-3">Services</h6>
						<div class="table-responsive">
							<table id="serviceListDt" class="table table-striped table-hover" style="width: 100%"></table>
						</div>
					</div>

					<div class="tab-pane fade" id="domain-info" role="tabpanel" aria-labelledby="domain-tab">
						<h6 class="mb-3">Domains</h6>
						<div class="table-responsive">
							<table id="domainListDt" class="table table-striped table-hover" style="width: 100%"></table>
						</div>
					</div>

					<div class="tab-pane fade" id="invoice-info" role="tabpanel" aria-labelledby="invoice-tab">
						<div class="table-responsive">
							<table id="invoiceListDt" class="table table-striped table-hover" style="width: 100%"></table>
						</div>
					</div>

				</div>

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
});
</script>


<script>
	$(function(){
		'use strict'

		// Services DataTable
		$('#serviceListDt').DataTable({
			"responsive": true,
			"processing": true,
			"serverSide": true,
			"ajax": {
				"url": "<?=base_url()?>" + "whmazadmin/company/ssp_services_api/"+ "<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>",
			},
			"columns": [
				{ "title": "ID", "data": "id" },
				{ "title": "Hosting Domain", "data": "hosting_domain",
					render: function (data, type, row) {
						return data ? data : '-';
					}
				},
				{ "title": "First Pay", "data": "first_pay_amount",
					render: function (data, type, row) {
						return parseFloat(data).toFixed(2);
					}
				},
				{ "title": "Recurring", "data": "recurring_amount",
					render: function (data, type, row) {
						return parseFloat(data).toFixed(2);
					}
				},
				{ "title": "Reg Date", "data": "reg_date" },
				{ "title": "Next Due", "data": "next_due_date" },
				{ "title": "CompanyId", "data": "company_id", "visible": false, "orderable": true, "searchable": true },
				{
					"title": "Status", "data": "status", "orderable": false, "searchable": false,
					render: function (data, type) {
						switch(parseInt(data)) {
							case 0: return '<span class="badge bg-warning">Pending</span>';
							case 1: return '<span class="badge bg-success">Active</span>';
							case 2: return '<span class="badge bg-danger">Expired</span>';
							case 3: return '<span class="badge bg-secondary">Suspended</span>';
							case 4: return '<span class="badge bg-dark">Terminated</span>';
							default: return '<span class="badge bg-secondary">Unknown</span>';
						}
					}
				}
			]
		});

		// Domains DataTable
		$('#domainListDt').DataTable({
			"responsive": true,
			"processing": true,
			"serverSide": true,
			"ajax": {
				"url": "<?=base_url()?>" + "whmazadmin/company/ssp_domains_api/"+ "<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>",
			},
			"columns": [
				{ "title": "ID", "data": "id" },
				{ "title": "Domain", "data": "domain" },
				{ "title": "First Pay", "data": "first_pay_amount",
					render: function (data, type, row) {
						return parseFloat(data).toFixed(2);
					}
				},
				{ "title": "Recurring", "data": "recurring_amount",
					render: function (data, type, row) {
						return parseFloat(data).toFixed(2);
					}
				},
				{ "title": "Reg Date", "data": "reg_date" },
				{ "title": "Exp Date", "data": "exp_date" },
				{ "title": "Next Due", "data": "next_due_date" },
				{ "title": "CompanyId", "data": "company_id", "visible": false, "orderable": true, "searchable": true },
				{
					"title": "Status", "data": "status", "orderable": false, "searchable": false,
					render: function (data, type) {
						switch(parseInt(data)) {
							case 0: return '<span class="badge bg-warning">Pending Reg</span>';
							case 1: return '<span class="badge bg-success">Active</span>';
							case 2: return '<span class="badge bg-danger">Expired</span>';
							case 3: return '<span class="badge bg-info">Grace</span>';
							case 4: return '<span class="badge bg-dark">Cancelled</span>';
							case 5: return '<span class="badge bg-warning">Pending Transfer</span>';
							case 6: return '<span class="badge bg-secondary">Deleted</span>';
							default: return '<span class="badge bg-secondary">Unknown</span>';
						}
					}
				}
			]
		});

		// Invoices DataTable
		$('#invoiceListDt').DataTable({
			"responsive": true,
			"processing": true,
			"serverSide": true,
			"ajax": {
				"url": "<?=base_url()?>" + "whmazadmin/invoice/ssp_list_api/"+ "<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>",
			},
			"columns": [
				{ "title": "Invoice#", "data": "invoice_no" },
				{ "title": "Order#", "data": "order_no" },
				{ "title": "Amount", "data": "total",
					render: function (data, type, row, meta) {
						return parseFloat(data).toFixed(2) + " " + row['currency_code'];
					}
				},
				{ "title": "Due date", "data": "due_date" },
				{ "title": "Currency", "data": "currency_code", "visible": false, "orderable": false, "searchable": false },
				{ "title": "CompanyId", "data": "company_id", "visible": false, "orderable": true, "searchable": true },
				{ "title": "Generate At", "data": "inserted_on", "searchable": false },
				{
					"title": "Pay Status", "data": "pay_status", "orderable": false, "searchable": false,
					render: function (data, type) {
						if( data == "PAID" ){
							return '<span class="badge bg-success">Paid</span>';
						} else if( data == "DUE" ){
							return '<span class="badge bg-danger">Due</span>';
						} else {
							return '<span class="badge bg-warning">Partial</span>';
						}
					}
				},
				{
					"title": "Active?", "data": "status", "orderable": false, "searchable": false,
					render: function (data, type) {
						if( data == 1 ){
							return '<span class="badge bg-success">Yes</span>';
						} else {
							return '<span class="badge bg-danger">No</span>';
						}
					}
				},
				{
					"title" : 'Action',
					"data" : "invoice_uuid",
					"orderable": false,
					"searchable": false,
					"render": function (data, type, row) {
						return '<a href="<?=base_url()?>whmazadmin/invoice/view_invoice/<?= $detail['id']?>/' + data + '" class="btn btn-sm btn-outline-secondary" title="View Invoice"><i class="fa fa-eye"></i></a>';
					}
				}
			]
		});

	});

</script>

<?php $this->load->view('whmazadmin/include/footer');?>
