<?php $this->load->view('whmazadmin/include/header');?>

<div class="content content-fluid content-wrapper">
    <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">
        <div class="row mt-5">
            <div class="col-md-12 col-sm-12">
                <h3 class="d-flex justify-content-between">
                    <span>Domain Registrars</span>
                    <a href="<?=base_url()?>whmazadmin/domain_register/manage" class="btn btn-sm btn-secondary">
                        <i class="fa fa-plus-square"></i>&nbsp;Add
                    </a>
                </h3>
                <hr class="mg-5" />
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mg-b-0">
                        <li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
                        <li class="breadcrumb-item active"><a href="#">Domain Registrars</a></li>
                    </ol>
                </nav>
            </div>

            <div class="col-md-12 col-sm-12 mt-5">
                <table id="listDataTable" class="table table-striped table-hover"></table>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script');?>
<script>

$(function(){
    'use strict'

    // SECURITY: Show flash messages as toast with XSS protection
    <?php if ($this->session->flashdata('alert_success')) { ?>
        toastSuccess(<?= json_encode($this->session->flashdata('alert_success')) ?>);
    <?php } ?>
    <?php if ($this->session->flashdata('alert_error')) { ?>
        toastError(<?= json_encode($this->session->flashdata('alert_error')) ?>);
    <?php } ?>

    $('#listDataTable').DataTable({
        "responsive": true,
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?=base_url()?>whmazadmin/domain_register/ssp_list_api/",
        },
        order: [[0, 'desc']],
        "columns": [
            { "title": "ID", "data": "id", "width": "5%" },
            { "title": "Registrar Name", "data": "name", "width": "15%", render: function(data){return escapeXSS(data);} },
            { "title": "Platform", "data": "platform", "width": "15%", render: function(data){return escapeXSS(data);} },
            { "title": "API Base URL", "data": "api_base_url", "width": "25%", render: function(data){return escapeXSS(data);} },
            {
                "title": "Default?", "data": "is_selected", "width": "10%",
                "className": "text-center",
                "orderable": false,
                "searchable": false,
                "render": function (data, type) {
                    if (data == 1) {
                        return '<span class="badge bg-success">Yes</span>';
                    } else {
                        return '<span class="badge bg-secondary">No</span>';
                    }
                }
            },
            {
                "title": "Active?", "data": "status", "width": "15%",
                "className": "text-center",
                "orderable": false,
                "searchable": false,
                "render": function (data, type) {
                    if (data == 1) {
                        return '<span class="badge bg-primary">Yes</span>';
                    } else {
                        return '<span class="badge bg-danger">No</span>';
                    }
                }
            },
            { "title": "Last Updated", "data": "updated_on", "width": "15%" },
            {
                "title": "Action",
                "data": "id",
                "width": "20%",
                "className": "text-center",
                "orderable": false,
                "searchable": false,
                "render": function (data, type, row) {
					let idVal = safe_encode(data);
                    return '<button type="button" class="btn btn-xs btn-secondary" onclick="openManage(\'' + idVal + '\')" title="Manage"><i class="fa fa-wrench"></i></button> ' +
                           '<button type="button" class="btn btn-xs btn-danger" onclick="deleteRow(\'' + idVal + '\', \'' + escapeXSS(row.name) + '\')" title="Delete"><i class="fa fa-trash"></i></button>';
                }
            }
        ]
    });
});

function openManage(id) {
    window.location = "<?=base_url()?>whmazadmin/domain_register/manage/"+id;
}

function deleteRow(id, title) {
    Swal.fire({
        title: 'Do you want to delete the (<b>'+title+'</b>) registrar?',
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
            window.location = "<?=base_url()?>whmazadmin/domain_register/delete_records/"+id;
        }
    });
}
</script>
<?php $this->load->view('whmazadmin/include/footer');?>
