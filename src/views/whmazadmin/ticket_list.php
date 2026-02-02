<?php $this->load->view('whmazadmin/include/header');?>

	 <div class="content content-fluid content-wrapper" >
      <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

        <div class="row mt-5">
			<div class="col-md-12 col-sm-12">
				<h3>Tickets</h3>
				<hr class="mg-5" />
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb breadcrumb-style1 mg-b-0">
						<li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
						<li class="breadcrumb-item active"><a href="#">View tickets</a></li>
					</ol>
				</nav>
			  <?php if ($this->session->flashdata('alert')) { ?>
				<?= $this->session->flashdata('alert') ?>
			  <?php } ?>

			</div>

			<div class="col-md-12 col-sm-12 mt-5">
				<table id="ticketListDt" class="table table-striped table-hover"></table>
			</div>
      </div>
		
    </div><!-- container -->
  </div><!-- content -->

<?php $this->load->view('whmazadmin/include/footer_script');?>
<script>
      $(function(){
        	'use strict'

			$('#ticketListDt').DataTable({
				"responsive": true,
				"processing": true,
				"serverSide": true,
				"ajax": {
					"url": "<?=base_url()?>" + "whmazadmin/ticket/ssp_list_api",
				},
				order: [[0, 'desc']],
				"columns": [
					{ "title": "Ticket#", "data": "id" },
					{ "title": "Title", "data": "title", render: function(data){return escapeXSS(data);} },
					{ "title": "Company", "data": "company_name", render: function(data){return escapeXSS(data);} },
					{ "title": "Department", "data": "dept_name", render: function(data){return escapeXSS(data);} },
					{ "title": "User", "data": "user_name", render: function(data){return escapeXSS(data);} },
					{ "title": "Priority", "data": "priority",
						render: function (data) {
							if( data == 1 ){
								return '<span class="badge rounded-pill bg-secondary">Low</span>';
							} else if( data == 2 ){
								return '<span class="badge rounded-pill bg-primary">Medium</span>';
							} else if( data == 3 ){
								return '<span class="badge rounded-pill bg-warning">High</span>';
							} else if( data == 4 ){
								return '<span class="badge rounded-pill bg-danger">Critical</span>';
							}
						}
					},
					{ "title": "Status", "data": "flag",
						render: function (data) {
							if( data == 1 ){
								return '<span class="badge rounded-pill bg-success">Opened</span>';
							} else if( data == 2 ){
								return '<span class="badge rounded-pill bg-info">Answered</span>';
							} else if( data == 3 ){
								return '<span class="badge rounded-pill bg-warning">Customer reply</span>';
							} else if( data == 4 ){
								return '<span class="badge rounded-pill bg-dark">Closed</span>';
							} else {
								return '<span class="badge rounded-pill bg-danger">&nbsp;</span>';
							}
						}
					},
					{ "title": "Date", "data": "inserted_on", "searchable": true },
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
							return '<button type="button" class="btn btn-sm btn-outline-info" onclick="viewMyTicket('+data+')" title="View"><i class="fa fa-eye"></i></button>';
						}
					}
				]
			});

      });

      function viewMyTicket(tid) {
		window.location = "<?=base_url()?>whmazadmin/ticket/viewticket/"+tid;
	  }
    </script>
<?php $this->load->view('whmazadmin/include/footer');?>
