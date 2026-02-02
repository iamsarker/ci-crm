<?php $this->load->view('whmazadmin/include/header');?>

	 <div class="content content-fluid content-wrapper">
      <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

        <div class="row mt-5">
			<div class="col-md-12 col-sm-12">
				<h3 class="d-flex justify-content-between"><span>Orders</span> <a href="<?=base_url()?>whmazadmin/order/manage" class="btn btn-sm btn-secondary"><i class="fa fa-plus-square"></i>&nbsp;Add</a></h3>
				<hr class="mg-5" />
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb breadcrumb-style1 mg-b-0">
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
						<li class="breadcrumb-item active"><a href="#">Orders</a></li>
					</ol>
				</nav>
			  <?php if ($this->session->flashdata('alert')) { ?>
				<?= $this->session->flashdata('alert') ?>
			  <?php } ?>

			</div>

			<div class="col-md-12 col-sm-12 mt-5">
				<table id="orderListDt" class="table table-striped table-hover"></table>
			</div>
      </div>
		
    </div><!-- container -->
  </div><!-- content -->

<?php $this->load->view('whmazadmin/include/footer_script');?>
<script>
      $(function(){
        	'use strict'

			$('#orderListDt').DataTable({
				"responsive": true,
				"processing": true,
				"serverSide": true,
				"ajax": {
					"url": "<?=base_url()?>" + "whmazadmin/order/ssp_list_api/",
				},
				order: [[0, 'desc']],
				"columns": [
					{ "title": "Order#", "data": "order_no", render: function(data){return escapeXSS(data);} },
					{ "title": "Company name", "data": "company_name", render: function(data){return escapeXSS(data);} },
					{
						"title": "Items", "data": "service_count", "orderable": false, "searchable": false,
						render: function (data, type, row) {
							var parts = [];
							if (parseInt(row.service_count) > 0) parts.push(row.service_count + ' service(s)');
							if (parseInt(row.domain_count) > 0) parts.push(row.domain_count + ' domain(s)');
							return parts.length > 0 ? escapeXSS(parts.join(', ')) : '<span class="text-muted">-</span>';
						}
					},
					{ "title": "Discount", "data": "discount_amount" },
					{ "title": "Total", "data": "total_amount" },
					{
						"title": "Recurring", "data": "services_recurring_total", "orderable": false, "searchable": false,
						render: function (data, type, row) {
							var total = parseFloat(row.services_recurring_total || 0) + parseFloat(row.domains_recurring_total || 0);
							return total > 0 ? total.toFixed(2) : '<span class="text-muted">-</span>';
						}
					},
					{ "title": "Currency", "data": "currency_code", render: function(data){return escapeXSS(data);} },
					{ "title": "Order date", "data": "order_date", "searchable": true },
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
						"render": function (data) {
							return '<a href="<?=base_url()?>whmazadmin/order/manage/' + data + '" class="btn btn-sm btn-outline-secondary edit-button" data-id="'+data+'" type="button" title="Edit"><i class="fa fa-pencil-alt"></i></a>'
								+ '&nbsp;<button class="btn btn-sm btn-outline-danger delete-button" data-id="'+data+'" type="button" title="Delete"><i class="fa fa-trash"></i></button>';
						}
					}
				]
			});

      });

      function viewMyTicket(tid) {
		window.location = "<?=base_url()?>whmazadmin/order/manage/"+tid;
	  }
    </script>
<?php $this->load->view('whmazadmin/include/footer');?>
