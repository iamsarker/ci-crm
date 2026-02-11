<?php $this->load->view('templates/customer/header');?>
<div class="content content-fixed content-wrapper">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">

        <!-- Page Header -->
        <div class="page-header-card page-header-tickets mg-b-25">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h3 class="mg-b-0"><i class="fa fa-plus-circle mg-r-10"></i>Open New Ticket</h3>
                    <nav aria-label="breadcrumb" class="mg-t-8">
                        <ol class="breadcrumb breadcrumb-style1 mg-b-0">
                            <li class="breadcrumb-item"><a href="<?=base_url()?>clientarea">Portal Home</a></li>
                            <li class="breadcrumb-item"><a href="<?=base_url()?>tickets/index">Tickets</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Open New Ticket</li>
                        </ol>
                    </nav>
                </div>
                <div class="header-actions mg-t-10 mg-md-t-0">
                    <a href="<?=base_url()?>tickets/index" class="btn btn-light">
                        <i class="fa fa-list mg-r-5"></i> View All Tickets
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 col-md-4 mg-b-20">
                <!-- Recent Tickets Card -->
                <div class="card sidebar-card">
                    <div class="card-header sidebar-card-header">
                        <h6 class="mg-b-0"><i class="fa fa-history mg-r-8"></i>Recent Tickets</h6>
                    </div>
                    <div class="sidebar-ticket-list">
                        <?php if(!empty($recent)): ?>
                            <?php foreach($recent as $row): ?>
                                <?php
                                    $flagClass = '';
                                    $flagText = '';
                                    $flagIcon = '';
                                    if($row['flag'] == 1) {
                                        $flagClass = 'bg-success';
                                        $flagText = 'Open';
                                        $flagIcon = 'fa-envelope-open';
                                    } else if($row['flag'] == 2) {
                                        $flagClass = 'bg-info';
                                        $flagText = 'Answered';
                                        $flagIcon = 'fa-reply';
                                    } else if($row['flag'] == 3) {
                                        $flagClass = 'bg-warning';
                                        $flagText = 'Replied';
                                        $flagIcon = 'fa-comment';
                                    } else if($row['flag'] == 4) {
                                        $flagClass = 'bg-dark';
                                        $flagText = 'Closed';
                                        $flagIcon = 'fa-check-circle';
                                    }
                                ?>
                                <a href="<?=base_url()?>tickets/viewticket/<?=htmlspecialchars($row['id'] ?? '', ENT_QUOTES, 'UTF-8')?>" class="sidebar-ticket-item">
                                    <div class="ticket-item-icon">
                                        <i class="fa fa-ticket-alt"></i>
                                    </div>
                                    <div class="ticket-item-content">
                                        <span class="ticket-item-id">#<?=htmlspecialchars($row['id'] ?? '', ENT_QUOTES, 'UTF-8')?></span>
                                        <span class="ticket-item-title"><?=htmlspecialchars($row['title'] ?? '', ENT_QUOTES, 'UTF-8')?></span>
                                    </div>
                                    <span class="badge rounded-pill <?=$flagClass?> ticket-item-badge"><?=$flagText?></span>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="sidebar-empty-state">
                                <i class="fa fa-inbox"></i>
                                <p>No recent tickets</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php $this->load->view('templates/customer/support_nav');?>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9 col-md-8">
                <div class="card ticket-form-card">
                    <div class="card-header ticket-form-header">
                        <h5 class="mg-b-0"><i class="fa fa-edit mg-r-10"></i>Submit a Support Request</h5>
                        <p class="mg-b-0 mg-t-5 op-7">Fill out the form below and our team will respond shortly</p>
                    </div>
                    <div class="card-body">
                        <?php
                            $attributes = array('id' => 'newticketform', 'class' => 'ticket-form');
                            echo form_open_multipart("tickets/newticket", $attributes);
                        ?>
                        <?= csrf_field() ?>

                        <!-- Contact Information Section -->
                        <div class="form-section">
                            <h6 class="form-section-title"><i class="fa fa-user mg-r-8"></i>Contact Information</h6>
                            <div class="row">
                                <div class="col-md-6 mg-b-15">
                                    <label class="form-label">Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa fa-user"></i></span>
                                        <input value="<?=set_value('name', htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''), ENT_QUOTES, 'UTF-8'))?>" type="text" class="form-control" id="name" disabled>
                                    </div>
                                </div>
                                <div class="col-md-6 mg-b-15">
                                    <label class="form-label">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                                        <input value="<?=set_value('email', htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8'))?>" type="email" class="form-control" id="email" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ticket Details Section -->
                        <div class="form-section">
                            <h6 class="form-section-title"><i class="fa fa-info-circle mg-r-8"></i>Ticket Details</h6>

                            <div class="mg-b-15">
                                <label class="form-label required-field">Subject</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-heading"></i></span>
                                    <input name="title" type="text" class="form-control" id="subject" placeholder="Brief description of your issue">
                                </div>
                                <?php echo form_error('title', '<div class="error-message"><i class="fa fa-exclamation-circle"></i> ', '</div>'); ?>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mg-b-15">
                                    <label class="form-label required-field">Department</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa fa-building"></i></span>
                                        <?php echo form_dropdown('ticket_dept_id', $results, '', 'class="form-select" id="ticket_dept_id"'); ?>
                                    </div>
                                    <?php echo form_error('ticket_dept_id', '<div class="error-message"><i class="fa fa-exclamation-circle"></i> ', '</div>'); ?>
                                </div>
                                <div class="col-md-4 mg-b-15">
                                    <label class="form-label">Related Service</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa fa-server"></i></span>
                                        <?php echo form_dropdown('related_service', $services, '', 'class="form-select" id="related_service"'); ?>
                                    </div>
                                </div>
                                <div class="col-md-4 mg-b-15">
                                    <label class="form-label required-field">Priority</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa fa-flag"></i></span>
                                        <select name="priority" class="form-select">
                                            <option value="">-- Select Priority --</option>
                                            <option value="1">Low</option>
                                            <option value="2" selected>Medium</option>
                                            <option value="3">High</option>
                                            <option value="4">Critical</option>
                                        </select>
                                    </div>
                                    <?php echo form_error('priority', '<div class="error-message"><i class="fa fa-exclamation-circle"></i> ', '</div>'); ?>
                                </div>
                            </div>
                        </div>

                        <!-- Message Section -->
                        <div class="form-section">
                            <h6 class="form-section-title"><i class="fa fa-comment-alt mg-r-8"></i>Message</h6>
                            <div class="editor-wrapper">
                                <div id="editor"></div>
                            </div>
                            <textarea name="message" id="message_hidden" style="display:none;"></textarea>
                            <?php echo form_error('message', '<div class="error-message"><i class="fa fa-exclamation-circle"></i> ', '</div>'); ?>
                        </div>

                        <!-- Attachments Section -->
                        <div class="form-section">
                            <h6 class="form-section-title"><i class="fa fa-paperclip mg-r-8"></i>Attachments</h6>
                            <div class="attachment-wrapper">
                                <div class="attachment-row">
                                    <div class="attachment-input">
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fa fa-file"></i></span>
                                            <input type="file" name="attachment[]" class="form-control"
                                                accept=".gif,.jpg,.jpeg,.png,.pdf,.txt"
                                                data-max-size="5242880"
                                                onchange="validateFileUpload(this)">
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-add-attachment" onclick="add_dyamice_row()">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                                <small class="attachment-help-text">
                                    <i class="fa fa-info-circle mg-r-5"></i>Allowed: GIF, JPG, PNG, PDF, TXT. Max size: 5MB per file.
                                </small>
                                <?php echo form_error('attachment'); ?>
                            </div>
                            <span id="registryData"></span>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <button class="btn btn-primary btn-submit-ticket" type="submit">
                                <i class="fa fa-paper-plane mg-r-8"></i>Submit Ticket
                            </button>
                            <a href="<?=base_url()?>tickets/index" class="btn btn-outline-secondary btn-cancel">
                                <i class="fa fa-times mg-r-8"></i>Cancel
                            </a>
                        </div>

                        <?php echo form_close();?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('templates/customer/footer_script');?>

<script>
$(function(){
    'use strict';

    var toolbarOptions = [
        ['bold', 'italic', 'underline'],
        ['link', 'blockquote', 'code-block'],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        [{ 'script': 'sub'}, { 'script': 'super' }],
        [{ 'indent': '-1'}, { 'indent': '+1' }],
        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
        [{ 'font': [] }],
    ];

    var quill = new Quill('#editor', {
        modules: {
            toolbar: toolbarOptions
        },
        placeholder: 'Describe your issue in detail...',
        theme: 'snow'
    });

    $('#newticketform').submit(function() {
        var delta = quill.root.innerHTML;
        $('#message_hidden').val(delta);
        return true;
    });
});

var registryNo = 2;
var registry_form_no = 1;

function add_dyamice_row() {
    jQuery.get('get_ticket_attachment_row/' + registry_form_no + '/' + registryNo, function(data) {
        jQuery('#registryData').append(data);
        registryNo = registryNo + 1;
        registry_form_no = registry_form_no + 1;
    });
}

function rm_registry_form(form_id) {
    jQuery('#registry_no_' + form_id).remove();
    registryNo = registryNo - 1;
}
</script>

<?php $this->load->view('templates/customer/footer');?>
