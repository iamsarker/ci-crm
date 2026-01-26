<?php $this->load->view('templates/customer/header');?>
<div class="content content-fixed content-wrapper" >
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <div class="row">
            <div class="col-md-3">
				<div class="card card-widget card-contacts">
					<div class="card-header">
						<h6 class="card-title mg-b-0"><i class="fa fa-history"></i>&nbsp;Recent tickets</h6>
						<nav class="nav">

						</nav>
					</div><!-- card-header -->
					<ul class="list-group list-group-flush">
						<?php foreach($recent as $row){
							$flag = "";
							if( $row['flag']==1 ) {
								$flag = '<span class="badge rounded-pill bg-success float-right">Opened</span>';
							} else if( $row['flag']==2 ) {
								$flag = '<span class="badge rounded-pill bg-info float-right">Answered</span>';
							} else if( $row['flag']==3 ) {
								$flag = '<span class="badge rounded-pill bg-warning float-right">Customer reply</span>';
							} else if( $row['flag']==4 ) {
								$flag =  '<span class="badge rounded-pill bg-dark float-right">Closed</span>';
							}
						?>
						<li class="list-group-item">
							<a href="<?=base_url()?>supports/viewticket/<?=$row['id']?>" style="font-size: 9pt;"><?='#'.$row['id'].' - ',$row['title'];?>&nbsp;<?=$flag?></a>
						</li>
						<?php } ?>
					</ul>
				</div>

                <?php $this->load->view('templates/customer/support_nav');?>
            </div>
            <div class="col-md-9">
                <h3>Open Ticket</h3>
                <hr class="mg-5" />
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb breadcrumb-style1 mg-b-0">
                    <li class="breadcrumb-item"><a href="<?=base_url()?>clientarea">Portal home</a></li>
                    <li class="breadcrumb-item"><a href="<?=base_url()?>tickets/index">Tickets</a></li>
                    <li class="breadcrumb-item active" aria-current="#">Open new ticket</li>
                  </ol>
                </nav>



                <!-- form element start -->
                <?php
					$attributes = array('id' => 'newticketform');
					echo form_open_multipart("tickets/newticket", $attributes);?>
                    <div class="row  mg-t-25">
                        <div class="form-group col-md-6">
                            <label for="name">Name</label>
                            <input value="<?=set_value('name', $user['first_name'].' '.$user['last_name'])?>" type="text" class="form-control" id="name" placeholder="Name" disabled>
                            <?php echo form_error('name'); ?>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="email">Email</label>
                            <input value="<?=set_value('name', $user['email'])?>" type="email" class="form-control" id="email" placeholder="Email" disabled>
                            <?php echo form_error('email'); ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-12 col-xs-12">
                            <label for="subject">Subject</label>
                            <input name="title" type="subject" class="form-control" id="subject">
                            <?php echo form_error('title', '<div class="error">', '</div>'); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-3">
                        	<label>Department</label>
                            <?php echo form_dropdown('ticket_dept_id', $results,'','class="form-select" id="ticket_dept_id"'); ?>
                            <?php echo form_error('ticket_dept_id', '<div class="error">', '</div>'); ?>
                        </div>
                        <div class="form-group col-md-6">
							<label>Related Service</label>
							<select class="form-select">

							</select>
                        </div>
                        <div class="form-group col-md-3">
							<label>Priority</label>
							<select name="priority" class="form-select">
								<option value="" selected="selected">-- Select --</option>
								<option value="1">Low</option>
								<option value="2" selected>Medium</option>
								<option value="3">High</option>
								<option value="4">Critical</option>
							</select>
                        	<?php echo form_error('priority', '<div class="error">', '</div>'); ?>
                        </div>
                    </div>
                    <div class="row mg-b-20">
                        <div class="form-group col-md-12">
                            <label>Messages</label>
							<div class="tx-13 mg-b-25" style="height: 180px;">
								<div id="editor"></div>
							</div>
							<?php echo form_error('message', '<div class="error">', '</div>'); ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-10">
                            <label>Attachment</label>
                            <input type="file" name="attachment[]" class="form-control"
                                accept=".gif,.jpg,.jpeg,.png,.pdf,.txt"
                                data-max-size="5242880"
                                onchange="validateFileUpload(this)">
                            <small class="form-text text-muted">Allowed: GIF, JPG, PNG, PDF, TXT. Max size: 5MB per file.</small>
                            <?php echo form_error('attachment'); ?>
                        </div><!-- form-group -->
                        <div class="col-md-2">
							<button onclick="add_dyamice_row()" type="button" class="btn btn-white btn-secondary add-more mg-t-25 "><i class="fa fa-plus"></i>&nbsp;Add More</button>
                        </div>
                    </div>
                    <span id="registryData"> </span>
                    <button class="btn btn-primary" type="submit">Submit</button>
                    <button class="btn btn-secondary" type="cancel">Cancel</button>
                    <?php echo form_close();?>
            </div>
        </div>
    </div><!-- container -->
</div><!-- content -->

    <?php $this->load->view('templates/customer/footer_script');?>

    <script>
      $(function(){
        'use strict'

			var toolbarOptions = [
				['bold', 'italic', 'underline'],        // toggled buttons
				['link', 'blockquote', 'code-block'],

				[{ 'list': 'ordered'}, { 'list': 'bullet' }],
				[{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript
				[{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent
				[{ 'header': [1, 2, 3, 4, 5, 6, false] }],
				[{ 'font': [] }],

			];

			var quill = new Quill('#editor', {
				modules: {
				toolbar: toolbarOptions
				},
				placeholder: 'Compose your message...',
				theme: 'snow'
			});



		  $('#newticketform').submit(function() {
			  var delta = quill.root.innerHTML;
			  $(this).append('<input type="hidden" name="message" value="'+delta+'" /> ');
			  return true;
		  });

        });

        var registryNo = 2;
        var registry_form_no = 1;
        function add_dyamice_row() {
            jQuery.get('get_ticket_attachment_row/' + registry_form_no + '/' + registryNo, function (data) {
                jQuery('#registryData').append(data);
                registryNo = registryNo + 1;
                registry_form_no = registry_form_no + 1;
            });
        }

        function rm_registry_form(form_id) {
            jQuery('#registry_no_' + form_id).remove();
            registryNo = registryNo - 1;
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
