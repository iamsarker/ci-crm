<?php $this->load->view('templates/customer/header'); ?>
<div class="content content-fixed content-wrapper" >
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <div class="row">
            <div class="col-md-3">
                <div class="card card-widget card-contacts">
                    <div class="card-header">
                        <h6 class="card-title mg-b-0"><i class="fa fa-tag"></i>&nbsp;Ticket information</h6>
                        <nav class="nav">

                        </nav>
                    </div><!-- card-header -->

					<?php if (!empty($ticket)){ ?>
						<ul class="list-group list-group-flush">
							<?php
							$flag = getTicketStatus($ticket['flag']);
							?>
							<li class="list-group-item">
								<?= '#' . $ticket['id'] . ' - ', $ticket['title']; ?>&nbsp;<?= $flag ?>
							</li>
							<li class="list-group-item">
								<div style="width: 100%; color: #a1a1a1;">Dept.</div>
								<div><?= $ticket['dept_name']; ?></div>
							</li>
							<li class="list-group-item">
								<div style="width: 100%; color: #a1a1a1;">Priority</div><br />
								<div>
									<?php
									if ($ticket['priority'] == 1) {
										echo "Low";
									} else if ($ticket['priority'] == 2) {
										echo "Medium";
									} else if ($ticket['priority'] == 3) {
										echo "High";
									}
									?>
								</div>
							</li>
							<li class="list-group-item">
								<div style="width: 100%; color: #a1a1a1;">Submitted</div><br />
								<div><?= $ticket['inserted_on']; ?></div>
							</li>
							<li class="list-group-item">
								<div style="width: 100%; color: #a1a1a1;">Last updated</div><br />
								<div><?= $ticket['updated_on']; ?></div>
							</li>
						</ul>
					<?php }?>
                </div>

                <?php $this->load->view('templates/customer/support_nav'); ?>
            </div>
            <div class="col-md-9">
                <h3>View Ticket #<?= $tid ?></h3>
                <hr class="mg-5" />
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mg-b-0">
                        <li class="breadcrumb-item"><a href="<?= base_url() ?>clientarea">Portal home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url() ?>tickets/index">Tickets</a></li>
                        <li class="breadcrumb-item active" aria-current="#">View ticket</li>
                    </ol>
                </nav>


                
                
                
                
                <!-- form element start -->
                <?php
                    $attributes = array('id' => 'replyticketform');
                    echo form_open_multipart("tickets/replyticket/".$tid, $attributes);
                ?>
                
                <div class="row mb-4 mt-4">
                    <div class="form-group col-md-12">
                        <label>Reply Messages</label>
                        <div class="tx-13 mb-4" style="height: 180px;">
                            <div id="editor"></div>
                        </div>
                        <?php echo form_error('message', '<div class="error">', '</div>'); ?>
                    </div>
                    
                    <span id="registryData"> </span>
                    
                    <div class="form-group col-md-10 mt-3">
                        <label>Attachment</label>
                        <input type="file" name="attachment[]" class="form-control" multiple
                            accept=".gif,.jpg,.jpeg,.png,.pdf,.txt"
                            data-max-size="5242880"
                            onchange="validateFileUpload(this)">
                        <small class="form-text text-muted">Allowed: GIF, JPG, PNG, PDF, TXT. Max size: 5MB per file.</small>
                        <?php echo form_error('attachment'); ?>
                    </div>
                    <div class="form-group col-md-2 mt-4">
                        <button class="btn btn-primary mt-3" type="submit"><i class="fa fa-reply"></i> Add reply</button>
                    </div>
                </div>
                
                <?php echo form_close(); ?>
                

                <div class="row mg-t-25">
                    <div class="col-md-12">

                        <?php if( !empty($replies) ) foreach ($replies as $obj) { ?>
                            <div class="card card-widget card-contacts mg-b-10">
                                <div class="card-header">
                                    <h6 class="card-title mb-0"><i class="fa fa-user"></i>&nbsp;<?= $obj['user_name'] ?></h6>
                                    <nav class="nav">
                                        <?= $obj['inserted_on'] ?>
                                    </nav>
                                </div><!-- card-header -->
                                
                                <div class="card-body" style="padding: 10px;">
                                    <?= sanitize_html($obj['message'] ?? '') ?>
                                </div>
                                
                                <div class="card-footer">
                                    <?php
                                        if( $obj['rating'] == 0 ){
                                            echo '<a href="'.base_url().'tickets/likereplies/'.$tid.'/'. $obj["id"].'/5"><b class="text-success">Like</b></a>';
                                        } else {
                                            echo '<a href="'.base_url().'tickets/likereplies/'.$tid.'/'. $obj["id"].'/0"><b class="text-danger">Dislike</b></a>';
                                        }
                                    
                                        
                                        if( !empty($obj['attachment']) ){
                                            echo '&nbsp;&nbsp;&nbsp;&nbsp;<a target="_blank" href="'.base_url().'tickets/vtattachments/'.$tid.'/'.$obj["attachment"].'"><b>View Attachment</b></a>';
                                        }
                                    ?>
                                </div>
                                
                            </div>
                        <?php } ?>

                        <div class="card card-widget card-contacts">
                            <div class="card-header">
                                <h6 class="card-title mg-b-0"><i class="fa fa-user"></i>&nbsp;<?= !empty($ticket) ? $ticket['user_name'] : '' ?></h6>
                                <nav class="nav">
                                    <?= !empty($ticket) ? $ticket['inserted_on'] : '' ?>
                                </nav>
                            </div><!-- card-header -->
                            <div class="card-body" style="padding: 10px;">
                                <?= sanitize_html($ticket['message'] ?? '') ?>
                            </div>

                            <div class="card-footer">
                                <?php
                                    if( !empty($obj['attachment']) ){
                                        echo '<a target="_blank" href="'.base_url().'supports/vtattachments/'.$tid.'/'.$ticket["attachment"].'"><b>View Attachment</b></a>';
                                    }
                                ?>
                            </div>
                        </div>


                    </div>
                </div>

            </div>
        </div>
    </div><!-- container -->
</div><!-- content -->

<?php $this->load->view('templates/customer/footer_script'); ?>

<script>
    $(function () {
        'use strict'

        var toolbarOptions = [
            ['bold', 'italic', 'underline'], // toggled buttons
            ['link', 'blockquote', 'code-block'],

            [{'list': 'ordered'}, {'list': 'bullet'}],
            [{'script': 'sub'}, {'script': 'super'}], // superscript/subscript
            [{'indent': '-1'}, {'indent': '+1'}], // outdent/indent
            [{'header': [1, 2, 3, 4, 5, 6, false]}],
            [{'font': []}],
        ];

        var quill = new Quill('#editor', {
            modules: {
                toolbar: toolbarOptions
            },
            placeholder: 'Compose your replies...',
            theme: 'snow'
        });



        $('#replyticketform').submit(function () {
            var delta = quill.root.innerHTML;
            $(this).append('<input type="hidden" name="message" value="' + delta + '" /> ');
            return true;
        });

    });

	<?php if ($this->session->flashdata('alert_success')) { ?>
		toastSuccess(<?= json_encode($this->session->flashdata('alert_success')) ?>);
	<?php } ?>
	<?php if ($this->session->flashdata('alert_error')) { ?>
		toastError(<?= json_encode($this->session->flashdata('alert_error')) ?>);
	<?php } ?>
</script>
<?php $this->load->view('templates/customer/footer'); ?>
