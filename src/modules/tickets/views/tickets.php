<?php $this->load->view('templates/customer/header');?>

	 <div class="content content-fixed content-wrapper" >
      <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        

        <div class="row">
          <div class="col-md-3 col-sm-12">
            
            <div class="card card-widget card-contacts">
              <div class="card-header">
			    <h6 class="card-title mg-b-0"><i class="fa fa-tachometer-alt"></i>&nbsp;Summary</h6>
                <nav class="nav">

                </nav>
              </div><!-- card-header -->
              <ul class="list-group list-group-flush">
                <li class="list-group-item">
                  Open&nbsp;<span class="badge rounded-pill bg-success float-right"><?=$summary['opened']?></span>
                </li>
                <li class="list-group-item">
                  Answered&nbsp;<span class="badge rounded-pill bg-info float-right"><?=$summary['answered']?></span>
                </li>
                <li class="list-group-item">
                  Customer reply&nbsp;<span class="badge rounded-pill bg-warning float-right"><?=$summary['replied']?></span>
                </li>
                <li class="list-group-item">
					Closed&nbsp;<span class="badge rounded-pill bg-dark float-right"><?=$summary['closed']?></span>
                </li>
              </ul>
            </div>

            <?php $this->load->view('templates/customer/support_nav');?>


        </div>




        <div class="col-md-9 col-sm-12">
			<h3>My Tickets</h3>
			<hr class="mg-5" />
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb breadcrumb-style1 mg-b-0">
					<li class="breadcrumb-item"><a href="<?=base_url()?>clientarea">Portal home</a></li>
					<li class="breadcrumb-item active"><a href="#">My Tickets</a></li>
				</ol>
			</nav>
          <div data-label="Example" class="df-example demo-table mg-t-25">
            <table id="example1" class="table table-hover">
              <thead>
                  <tr>
                      <th class="wd-15p">Ticket#</th>
                      <th class="wd-35p">Subject</th>
                      <th class="wd-20p">Department</th>
                      <th class="wd-15p">Status</th>
                      <th class="wd-30p">Last updated</th>
                  </tr>
              </thead>
              <tbody>
                  <?php foreach($results as $row){ ?>
                      <tr style="cursor: pointer;" onclick="viewMyTicket(<?=$row['id']?>)">
                          <td>#<?php echo $row['id']; ?></td>
                          <td><?php echo $row['title']; ?></td>
                          <td><?php echo $row['dept_name']; ?></td>
                          <td class="text-center">
							  <?= getTicketStatus($row['flag'])?>
						  </td>
                          <td><?php echo $row['updated_on']; ?></td>
                      </tr>
                  <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
		
    </div><!-- container -->
  </div><!-- content -->

<?php $this->load->view('templates/customer/footer_script');?>
<script>
      $(function(){
        'use strict'
        $('#example1').DataTable({
          "aaSorting": [],
          language: {
            searchPlaceholder: 'Search...',
            sSearch: '',
            lengthMenu: '_MENU_ items/page',
          }
        });

      });

      function viewMyTicket(tid) {
		window.location = "<?=base_url()?>tickets/viewticket/"+tid;
	  }

	  <?php $alert_success = $this->session->flashdata('alert_success'); ?>
	  <?php if ($alert_success) { ?>
		toastSuccess(<?= json_encode(htmlspecialchars($alert_success, ENT_QUOTES, 'UTF-8')) ?>);
	  <?php } ?>
	  <?php $alert_error = $this->session->flashdata('alert_error'); ?>
	  <?php if ($alert_error) { ?>
		toastError(<?= json_encode(htmlspecialchars($alert_error, ENT_QUOTES, 'UTF-8')) ?>);
	  <?php } ?>
    </script>
<?php $this->load->view('templates/customer/footer');?>
