<?php $this->load->view('templates/customer/header'); ?>
<div class="content content-fixed content-wrapper">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0">
        <div class="row">
            <div class="col-md-3 col-sm-12">
                <!-- Ticket Information Card -->
                <div class="card detail-sidebar-card">
                    <div class="card-header detail-sidebar-header">
                        <h6 class="card-title mg-b-0"><i class="fa fa-info-circle mg-r-5"></i>Ticket Info</h6>
                    </div>
                    <?php if (!empty($ticket)): ?>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item ticket-info-item">
                            <div class="ticket-info-label">Status</div>
                            <div class="ticket-info-value"><?= getTicketStatus($ticket['flag']) ?></div>
                        </li>
                        <li class="list-group-item ticket-info-item">
                            <div class="ticket-info-label"><i class="fa fa-building mg-r-5"></i>Department</div>
                            <div class="ticket-info-value"><?= htmlspecialchars($ticket['dept_name'], ENT_QUOTES, 'UTF-8') ?></div>
                        </li>
                        <li class="list-group-item ticket-info-item">
                            <div class="ticket-info-label"><i class="fa fa-flag mg-r-5"></i>Priority</div>
                            <div class="ticket-info-value">
                                <?php
                                $priorityClass = 'bg-secondary';
                                $priorityText = 'Unknown';
                                if ($ticket['priority'] == 1) {
                                    $priorityClass = 'bg-success';
                                    $priorityText = 'Low';
                                } else if ($ticket['priority'] == 2) {
                                    $priorityClass = 'bg-info';
                                    $priorityText = 'Medium';
                                } else if ($ticket['priority'] == 3) {
                                    $priorityClass = 'bg-warning';
                                    $priorityText = 'High';
                                } else if ($ticket['priority'] == 4) {
                                    $priorityClass = 'bg-danger';
                                    $priorityText = 'Critical';
                                }
                                ?>
                                <span class="badge <?= $priorityClass ?>"><?= $priorityText ?></span>
                            </div>
                        </li>
                        <li class="list-group-item ticket-info-item">
                            <div class="ticket-info-label"><i class="fa fa-calendar-plus mg-r-5"></i>Submitted</div>
                            <div class="ticket-info-value"><?= !empty($ticket['inserted_on']) ? date('M d, Y H:i', strtotime($ticket['inserted_on'])) : '-' ?></div>
                        </li>
                        <li class="list-group-item ticket-info-item">
                            <div class="ticket-info-label"><i class="fa fa-clock mg-r-5"></i>Last Updated</div>
                            <div class="ticket-info-value"><?= !empty($ticket['updated_on']) ? date('M d, Y H:i', strtotime($ticket['updated_on'])) : '-' ?></div>
                        </li>
                    </ul>
                    <?php endif; ?>
                </div>

                <?php $this->load->view('templates/customer/support_nav'); ?>
            </div>

            <div class="col-md-9 col-sm-12">
                <!-- Page Header -->
                <div class="page-header-card page-header-tickets">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h3><i class="fa fa-ticket-alt mg-r-10"></i>#<?= htmlspecialchars($tid, ENT_QUOTES, 'UTF-8') ?> - <?= !empty($ticket) ? htmlspecialchars($ticket['title'], ENT_QUOTES, 'UTF-8') : 'View Ticket' ?></h3>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb breadcrumb-style1 mg-b-0">
                                    <li class="breadcrumb-item"><a href="<?= base_url() ?>clientarea">Portal home</a></li>
                                    <li class="breadcrumb-item"><a href="<?= base_url() ?>tickets/index">Tickets</a></li>
                                    <li class="breadcrumb-item active"><a>View Ticket</a></li>
                                </ol>
                            </nav>
                        </div>
                        <div class="header-status mt-2 mt-md-0">
                            <?= !empty($ticket) ? getTicketStatus($ticket['flag']) : '' ?>
                        </div>
                    </div>
                </div>

                <!-- Reply Form Card -->
                <div class="card ticket-reply-card">
                    <div class="card-header ticket-reply-header">
                        <h6 class="mg-b-0"><i class="fa fa-reply mg-r-10"></i>Post a Reply</h6>
                    </div>
                    <div class="card-body">
                        <?php
                        $attributes = array('id' => 'replyticketform');
                        echo form_open_multipart("tickets/replyticket/".$tid, $attributes);
                        ?>
                        <?= csrf_field() ?>

                        <div class="form-group mb-4">
                            <div class="ticket-editor-wrapper">
                                <div id="editor"></div>
                            </div>
                            <textarea name="message" id="message_hidden" style="display:none;"></textarea>
                            <?php echo form_error('message', '<div class="error mt-2">', '</div>'); ?>
                        </div>

                        <span id="registryData"></span>

                        <div class="row align-items-end">
                            <div class="col-md-9">
                                <div class="form-group mb-0">
                                    <label class="form-label"><i class="fa fa-paperclip mg-r-5"></i>Attachment</label>
                                    <input type="file" name="attachment[]" class="form-control" multiple
                                        accept=".gif,.jpg,.jpeg,.png,.pdf,.txt"
                                        data-max-size="5242880"
                                        onchange="validateFileUpload(this)">
                                    <small class="form-text text-muted"><i class="fa fa-info-circle mg-r-5"></i>Allowed: GIF, JPG, PNG, PDF, TXT. Max size: 5MB per file.</small>
                                    <?php echo form_error('attachment'); ?>
                                </div>
                            </div>
                            <div class="col-md-3 text-md-end mt-3 mt-md-0">
                                <button class="btn btn-primary btn-reply-submit" type="submit">
                                    <i class="fa fa-paper-plane mg-r-5"></i> Send Reply
                                </button>
                            </div>
                        </div>

                        <?php echo form_close(); ?>
                    </div>
                </div>

                <!-- Conversation Thread -->
                <div class="ticket-conversation">
                    <h6 class="conversation-title"><i class="fa fa-comments mg-r-10"></i>Conversation Thread</h6>

                    <?php if(!empty($replies)) foreach ($replies as $obj): ?>
                    <!-- Reply Message -->
                    <div class="ticket-message <?= ($obj['user_type'] ?? '') == 'admin' ? 'message-staff' : 'message-customer' ?>">
                        <div class="message-header">
                            <div class="message-avatar">
                                <i class="fa fa-user"></i>
                            </div>
                            <div class="message-meta">
                                <span class="message-author"><?= htmlspecialchars($obj['user_name'], ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="message-time"><i class="fa fa-clock mg-r-5"></i><?= !empty($obj['inserted_on']) ? date('M d, Y H:i', strtotime($obj['inserted_on'])) : '' ?></span>
                            </div>
                        </div>
                        <div class="message-body">
                            <?= sanitize_html($obj['message'] ?? '') ?>
                        </div>
                        <div class="message-footer">
                            <div class="message-actions">
                                <?php if($obj['rating'] == 0): ?>
                                <a href="<?= base_url() ?>tickets/likereplies/<?= $tid ?>/<?= $obj['id'] ?>/5" class="btn btn-sm btn-outline-success">
                                    <i class="fa fa-thumbs-up mg-r-5"></i>Helpful
                                </a>
                                <?php else: ?>
                                <a href="<?= base_url() ?>tickets/likereplies/<?= $tid ?>/<?= $obj['id'] ?>/0" class="btn btn-sm btn-outline-secondary">
                                    <i class="fa fa-thumbs-down mg-r-5"></i>Not Helpful
                                </a>
                                <?php endif; ?>

                                <?php if(!empty($obj['attachment'])): ?>
                                <a target="_blank" href="<?= base_url() ?>tickets/vtattachments/<?= $tid ?>/<?= $obj['attachment'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fa fa-paperclip mg-r-5"></i>View Attachment
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <!-- Original Ticket Message -->
                    <div class="ticket-message message-original">
                        <div class="message-header">
                            <div class="message-avatar original">
                                <i class="fa fa-user"></i>
                            </div>
                            <div class="message-meta">
                                <span class="message-author"><?= !empty($ticket) ? htmlspecialchars($ticket['user_name'], ENT_QUOTES, 'UTF-8') : '' ?></span>
                                <span class="message-badge">Original Message</span>
                                <span class="message-time"><i class="fa fa-clock mg-r-5"></i><?= !empty($ticket['inserted_on']) ? date('M d, Y H:i', strtotime($ticket['inserted_on'])) : '' ?></span>
                            </div>
                        </div>
                        <div class="message-body">
                            <?= sanitize_html($ticket['message'] ?? '') ?>
                        </div>
                        <?php if(!empty($ticket['attachment'])): ?>
                        <div class="message-footer">
                            <div class="message-actions">
                                <a target="_blank" href="<?= base_url() ?>tickets/vtattachments/<?= $tid ?>/<?= $ticket['attachment'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fa fa-paperclip mg-r-5"></i>View Attachment
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

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
            $('#message_hidden').val(delta);
            return true;
        });

    });

</script>
<?php $this->load->view('templates/customer/footer'); ?>
