# CI-CRM Coding Standards and Patterns

## Table of Contents
1. [Architecture Overview](#architecture-overview)
2. [View Templates Structure](#view-templates-structure)
3. [Controller Patterns](#controller-patterns)
4. [Model Patterns](#model-patterns)
5. [Database Query Patterns](#database-query-patterns)
6. [Form and Validation Patterns](#form-and-validation-patterns)
7. [JavaScript and AJAX Patterns](#javascript-and-ajax-patterns)
8. [Helper Functions Usage](#helper-functions-usage)
9. [Naming Conventions](#naming-conventions)
10. [Security Best Practices](#security-best-practices)

---

## Architecture Overview

### Technology Stack
- **Backend:** CodeIgniter 3.x with HMVC (Wiredesignz Modular Extensions)
- **Frontend:** Bootstrap 5 + DashForge Theme
- **JavaScript Framework:** AngularJS 1.x
- **Data Tables:** DataTables with server-side processing
- **Icons:** Feather Icons, FontAwesome, Ionicons
- **Dialogs:** SweetAlert2
- **Enhanced Selects:** Select2

### Dual Portal Structure
- **Admin Portal:** Traditional controllers in `src/controllers/whmazadmin/`
- **Client Portal:** HMVC modules in `src/modules/`

---

## View Templates Structure

### Admin Portal View Pattern

#### Standard Admin View Structure
```php
<?php $this->load->view('whmazadmin/include/header'); ?>

<div class="content content-fluid content-wrapper">
    <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">

        <div class="row mt-5">
            <div class="col-md-12 col-sm-12">
                <!-- Page Title with Action Button -->
                <h3 class="d-flex justify-content-between">
                    <span>Page Title</span>
                    <a href="<?=base_url()?>whmazadmin/entity/action" class="btn btn-sm btn-secondary">
                        <i class="fa fa-plus-square"></i>&nbsp;Add New
                    </a>
                </h3>
                <hr class="mg-5" />

                <!-- Breadcrumb Navigation -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mg-b-0">
                        <li class="breadcrumb-item">
                            <a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a>
                        </li>
                        <li class="breadcrumb-item active">
                            <a href="#">Current Page</a>
                        </li>
                    </ol>
                </nav>

                <!-- Flash Messages -->
                <?php if ($this->session->flashdata('alert')) { ?>
                    <?= $this->session->flashdata('alert') ?>
                <?php } ?>
            </div>

            <!-- Main Content Area -->
            <div class="col-md-12 col-sm-12 mt-5">
                <!-- Page-specific content here -->
            </div>
        </div>

    </div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script'); ?>
<script>
    // Page-specific JavaScript
</script>
<?php $this->load->view('whmazadmin/include/footer'); ?>
```

#### Admin View Components

**Header Components:**
- `whmazadmin/include/header.php` - Main wrapper
- `whmazadmin/include/header_script.php` - CSS includes, meta tags
- `whmazadmin/include/header_menus.php` - Navigation sidebar

**Footer Components:**
- `whmazadmin/include/footer_script.php` - JavaScript libraries
- `whmazadmin/include/footer.php` - Footer with theme customizer

### Client Portal View Pattern

#### Standard Client View Structure
```php
<?php $this->load->view('templates/customer/header'); ?>

<div class="content content-fixed content-wrapper">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0 mt-2">

        <!-- Breadcrumb and Title -->
        <div class="d-sm-flex align-items-center justify-content-between mg-b-20">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mg-b-10">
                        <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                        <li class="breadcrumb-item active">Current Page</li>
                    </ol>
                </nav>
                <h4 class="mg-b-0 tx-spacing--1">Page Title</h4>
            </div>
            <div class="d-none d-md-block">
                <!-- Quick action buttons -->
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if ($this->session->flashdata('alert')) { ?>
            <?= $this->session->flashdata('alert') ?>
        <?php } ?>

        <!-- Main Content -->
        <div class="row">
            <!-- Content here -->
        </div>

    </div>
</div>

<?php $this->load->view('templates/customer/footer_script'); ?>
<script>
    // Page-specific JavaScript
</script>
<?php $this->load->view('templates/customer/footer'); ?>
```

### Form Structure Pattern

#### Tabbed Form Layout
```php
<!-- Tab Navigation -->
<ul class="nav nav-tabs" id="pageTab" role="pageTablist">
    <li class="nav-item">
        <a class="nav-link active" id="info-tab" data-bs-toggle="tab" href="#general-info">
            <i class="fa fa-info-circle"></i>&nbsp;General info
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="service-tab" data-bs-toggle="tab" href="#service-info">
            <i class="fa fa-sliders-h"></i>&nbsp;Services
        </a>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content bd bd-gray-300 bd-t-0 pd-20">
    <div class="tab-pane fade show active" id="general-info">
        <form method="post" action="<?=base_url()?>whmazadmin/controller/manage/<?= safe_encode($id)?>">
            <!-- Form fields here -->
        </form>
    </div>

    <div class="tab-pane fade" id="service-info">
        <!-- Second tab content -->
    </div>
</div>
```

#### Standard Form Fields
```php
<!-- Text Input -->
<div class="col-md-6 col-sm-12">
    <div class="form-group">
        <label for="name">Company name</label>
        <input name="name" type="text" class="form-control" id="name"
               value="<?= !empty($detail['name']) ? $detail['name'] : ''?>"/>
        <?php echo form_error('name', '<div class="error">', '</div>'); ?>
    </div>
</div>

<!-- Email Input -->
<div class="col-md-6 col-sm-12">
    <div class="form-group">
        <label for="email">Email</label>
        <input name="email" type="email" class="form-control" id="email"
               value="<?= !empty($detail['email']) ? $detail['email'] : ''?>"/>
        <?php echo form_error('email', '<div class="error">', '</div>'); ?>
    </div>
</div>

<!-- Dropdown (using CI helper) -->
<div class="col-md-3 col-sm-12">
    <div class="form-group">
        <label for="country">Country</label>
        <?php echo form_dropdown('country', $countries,
                   !empty($detail['country']) ? $detail['country'] : '',
                   'class="form-select select2" id="country"'); ?>
        <?php echo form_error('country', '<div class="error">', '</div>'); ?>
    </div>
</div>

<!-- Textarea -->
<div class="col-md-12 col-sm-12">
    <div class="form-group">
        <label for="address">Address</label>
        <textarea name="address" class="form-control" id="address" rows="3"><?= !empty($detail['address']) ? $detail['address'] : ''?></textarea>
        <?php echo form_error('address', '<div class="error">', '</div>'); ?>
    </div>
</div>

<!-- Hidden Input -->
<input name="id" type="hidden" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

<!-- Submit Button -->
<div class="form-group">
    <button type="submit" class="btn btn-sm btn-primary">
        <i class="fa fa-check-circle"></i>&nbsp;Save
    </button>
    <a href="<?=base_url()?>whmazadmin/controller/index" class="btn btn-sm btn-secondary">
        <i class="fa fa-times"></i>&nbsp;Cancel
    </a>
</div>
```

### DataTable Patterns

#### Pattern 1: Simple Client-Side DataTable
```php
<!-- View -->
<table id="listDataTable" class="table table-hover">
    <thead>
        <tr>
            <th class="wd-20p">Company name</th>
            <th class="wd-20p text-center">Email</th>
            <th class="wd-10p text-center">Action</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($results as $row){ ?>
        <tr>
            <td><?= $row['name']; ?></td>
            <td class="text-center"><?= $row['email']; ?></td>
            <td class="text-center">
                <button type="button" class="btn btn-xs btn-secondary"
                        onclick="openManage('<?=safe_encode($row['id'])?>')"
                        title="Manage">
                    <i class="fa fa-wrench"></i>
                </button>
                <button type="button" class="btn btn-xs btn-danger"
                        onclick="deleteRow('<?=safe_encode($row['id'])?>', '<?= $row['name']?>')"
                        title="Delete">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<script>
$(function(){
    'use strict'
    $('#listDataTable').DataTable({
        responsive: true,
        language: {
            searchPlaceholder: 'Search...',
            sSearch: '',
        }
    });
});

function openManage(id) {
    window.location = "<?=base_url()?>whmazadmin/company/manage/"+id;
}

function deleteRow(id, title) {
    Swal.fire({
        title: 'Do you want to delete the (<b>'+title+'</b>) record?',
        showDenyButton: true,
        icon: 'question',
        confirmButtonText: 'Yes, delete',
        denyButtonText: 'No, cancel',
    }).then((result) => {
        if (result.isConfirmed) {
            window.location = "<?=base_url()?>whmazadmin/company/delete_records/"+id;
        }
    });
}
</script>
```

#### Pattern 2: Server-Side DataTable
```php
<!-- View -->
<table id="orderListDt" class="table table-striped table-hover"></table>

<script>
$(function(){
    'use strict'

    $('#orderListDt').DataTable({
        "responsive": true,
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?=base_url()?>" + "whmazadmin/invoice/ssp_list_api/",
        },
        order: [[0, 'desc']],
        "columns": [
            { "title": "Invoice#", "data": "invoice_no" },
            { "title": "Order#", "data": "order_no" },
            { "title": "Company name", "data": "company_name" },
            { "title": "Total", "data": "total" },
            { "title": "Currency", "data": "currency_code" },
            { "title": "Due date", "data": "due_date", "searchable": true },
            {
                "title": "invoice_uuid",
                "data": "invoice_uuid",
                "orderable": false,
                "searchable": false,
                "visible": false
            },
            {
                "title": "Pay status",
                "data": "pay_status",
                "orderable": false,
                "searchable": false,
                render: function (data, type) {
                    if( data == 'DUE' ){
                        return '<span class="badge bg-danger">Due</span>';
                    } else if( data == 'PAID' ){
                        return '<span class="badge bg-success">Paid</span>';
                    } else {
                        return '<span class="badge bg-warning">Partial</span>';
                    }
                }
            },
            {
                "title": 'Action',
                "data": "id",
                "orderable": false,
                "searchable": false,
                "render": function (data, type, row, meta) {
                    return '<div class="btn-group">'+
                        '<button class="btn btn-light btn-sm" type="button">'+
                            '<i class="fa fa-cog"></i>'+
                        '</button>'+
                        '<button type="button" class="btn btn-sm btn-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">'+
                            '<span class="visually-hidden">Toggle Dropdown</span>'+
                        '</button>'+
                        '<ul class="dropdown-menu">'+
                            '<li><a class="dropdown-item" onclick="viewDetail(\''+row['invoice_uuid']+'\')">'+
                                '<i class="fa fa-eye text-info"></i> View</a></li>'+
                            '<li><a class="dropdown-item" onclick="downloadPDF(\''+row['invoice_uuid']+'\')">'+
                                '<i class="fa fa-file-pdf text-danger"></i> Download</a></li>'+
                        '</ul>'+
                    '</div>';
                }
            }
        ]
    });
});
</script>
```

#### Controller for Server-Side DataTable
```php
public function ssp_list_api()
{
    $this->processRestCall();
    $params = $this->input->get();
    $bindings = array();
    $where = array();

    $sqlQuery = ssp_sql_query($params, "invoice_view", $bindings, $where);

    $data = $this->Invoice_model->getDataTableRecords($sqlQuery, $bindings);

    echo json_encode(array(
        "draw"            => !empty($params['draw']) ? $params['draw'] : 0,
        "recordsTotal"    => intval($this->Invoice_model->countDataTableTotalRecords()),
        "recordsFiltered" => intval($this->Invoice_model->countDataTableFilterRecords($where, $bindings)),
        "data"            => $data
    ));
}
```

#### Pattern 3: Server-Side DataTable with JOINs (Package Pricing Example)

**Use Case:** When you need server-side pagination with multiple table JOINs and custom column rendering.

**Complete Implementation:**

##### 1. Controller (Package.php)
```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Package extends WHMAZADMIN_Controller {

    function __construct(){
        parent::__construct();
        $this->load->model('Package_model');
        $this->load->model('Common_model');
        if (!$this->isLogin()) {
            redirect('/whmazadmin/authenticate/login', 'refresh');
        }
    }

    public function index()
    {
        $data['results'] = array();
        $this->load->view('whmazadmin/package_list', $data);
    }

    public function ssp_list_api()
    {
        $this->processRestCall();
        $params = $this->input->get();

        $bindings = array();
        $where = '';

        try {
            $sqlQuery = $this->Package_model->buildDataTableQuery($params, $bindings, $where);
            $data = $this->Package_model->getDataTableRecords($sqlQuery, $bindings);

            $response = array(
                "draw"            => !empty($params['draw']) ? intval($params['draw']) : 0,
                "recordsTotal"    => intval($this->Package_model->countDataTableTotalRecords()),
                "recordsFiltered" => intval($this->Package_model->countDataTableFilterRecords($where, $bindings)),
                "data"            => $data
            );

            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(array("error" => $e->getMessage()));
            exit;
        }
    }

    public function manage($id_val = null)
    {
        if( $this->input->post() ){
            $this->form_validation->set_rules('product_service_id', 'Product Service', 'required|trim');
            $this->form_validation->set_rules('currency_id', 'Currency', 'required|trim');
            $this->form_validation->set_rules('billing_cycle_id', 'Billing Cycle', 'required|trim');
            $this->form_validation->set_rules('price', 'Price', 'required|trim|numeric');

            if ($this->form_validation->run() == true){
                $form_data = array(
                    'id'                => safe_decode($this->input->post('id')),
                    'product_service_id'=> $this->input->post('product_service_id'),
                    'currency_id'       => $this->input->post('currency_id'),
                    'billing_cycle_id'  => $this->input->post('billing_cycle_id'),
                    'price'             => $this->input->post('price'),
                    'status'            => 1
                );

                if( intval($form_data['id']) > 0 ){
                    $oldEntity = $this->Package_model->getPricingDetail(safe_decode($id_val));
                    $form_data['updated_on'] = getDateTime();
                    $form_data['updated_by'] = getAdminId();
                    $form_data['inserted_on'] = $oldEntity['inserted_on'];
                    $form_data['inserted_by'] = $oldEntity['inserted_by'];
                } else {
                    $form_data['inserted_on'] = getDateTime();
                    $form_data['inserted_by'] = getAdminId();
                }

                if($this->Package_model->savePricingData($form_data)){
                    $this->session->set_flashdata('alert', successAlert('Package pricing has been saved successfully.'));
                    redirect("whmazadmin/package/index");
                }else {
                    $this->session->set_flashdata('alert', errorAlert('Something went wrong. Try again'));
                }
            }
        }

        if( !empty($id_val) ){
            $data['detail'] = $this->Package_model->getPricingDetail(safe_decode($id_val));
        } else {
            $data['detail'] = array();
        }

        // Load dropdown data
        $data['services'] = $this->Package_model->getAllServices();
        $data['currencies'] = $this->Package_model->getAllCurrencies();
        $data['billing_cycles'] = $this->Package_model->getAllBillingCycles();

        $this->load->view('whmazadmin/package_manage', $data);
    }

    public function delete_records($id_val)
    {
        $entity = $this->Package_model->getPricingDetail(safe_decode($id_val));
        $entity["status"] = 0;
        $entity["deleted_on"] = getDateTime();
        $entity["deleted_by"] = getAdminId();

        $this->Package_model->savePricingData($entity);
        $this->session->set_flashdata('alert', successAlert('Package pricing has been deleted successfully.'));

        redirect('whmazadmin/package/index');
    }
}
```

##### 2. Model (Package_model.php)
```php
<?php
class Package_model extends CI_Model{
    var $table;
    var $pricing_table;

    function __construct(){
        parent::__construct();
        $this->load->database();
        $this->table = "product_services";
        $this->pricing_table = "product_service_pricing";
    }

    // CRUD operations for product_service_pricing table
    function loadAllPricingData() {
        $sql = "SELECT psp.*, ps.product_name, c.code as currency_code, c.symbol as currency_symbol, bc.cycle_name
                FROM product_service_pricing psp
                LEFT JOIN product_services ps ON psp.product_service_id = ps.id
                LEFT JOIN currencies c ON psp.currency_id = c.id
                LEFT JOIN billing_cycle bc ON psp.billing_cycle_id = bc.id
                WHERE psp.status=1
                ORDER BY psp.id DESC";
        $data = $this->db->query($sql)->result_array();
        return $data;
    }

    function getPricingDetail($id) {
        $sql = "SELECT * FROM product_service_pricing WHERE id=? and status=1 ";
        $data = $this->db->query($sql, array($id))->result_array();
        return !empty($data) ? $data[0] : array();
    }

    function savePricingData($data) {
        $return = array();
        if ($this->db->replace('product_service_pricing', $data)) {
            $return['success'] = 1;
        } else {
            $return['success'] = 0;
        }
        return $return;
    }

    // Server-side pagination methods
    function getDataTableRecords($sqlQuery, $bindings) {
        $data = $this->db->query($sqlQuery, $bindings)->result_array();

        // Add encoded ID to each row for URL-safe links
        foreach ($data as &$row) {
            $row['encoded_id'] = safe_encode($row['id']);
        }

        return $data;
    }

    function countDataTableTotalRecords() {
        $sql = "SELECT COUNT(psp.id) as cnt
                FROM product_service_pricing psp
                WHERE psp.status=1";
        $query = $this->db->query($sql);
        $data = $query->result_array();
        return !empty($data) ? $data[0]['cnt'] : 0;
    }

    function countDataTableFilterRecords($where, $bindings) {
        $sql = "SELECT COUNT(psp.id) as cnt
                FROM product_service_pricing psp
                LEFT JOIN product_services ps ON psp.product_service_id = ps.id
                LEFT JOIN currencies c ON psp.currency_id = c.id
                LEFT JOIN billing_cycle bc ON psp.billing_cycle_id = bc.id
                $where";
        $query = $this->db->query($sql, $bindings);
        $data = $query->result_array();
        return !empty($data) ? $data[0]['cnt'] : 0;
    }

    function buildDataTableQuery($request, &$bindings, &$where) {
        // Build the SQL query with proper joins
        $limit = ssp_limit($request);
        $order = ssp_order($request);
        $where = ssp_filter($request, $bindings);

        // Add wildcards to bindings for LIKE clauses
        for ($i = 0; $i < count($bindings); $i++) {
            $bindings[$i] = '%' . $bindings[$i] . '%';
        }

        // Replace table alias for WHERE clause
        $where = str_replace('`id`', '`psp`.`id`', $where);
        $where = str_replace('`product_name`', '`ps`.`product_name`', $where);
        $where = str_replace('`currency_code`', '`c`.`code`', $where);
        $where = str_replace('`cycle_name`', '`bc`.`cycle_name`', $where);
        $where = str_replace('`price`', 'CAST(`psp`.`price` AS CHAR)', $where);
        $where = str_replace('`updated_on`', '`psp`.`updated_on`', $where);

        // Add status condition
        if (!empty($where)) {
            $where .= " AND psp.status=1";
        } else {
            $where = "WHERE psp.status=1";
        }

        // Replace column names in ORDER BY clause
        $order = str_replace('`id`', '`psp`.`id`', $order);
        $order = str_replace('`product_name`', '`ps`.`product_name`', $order);
        $order = str_replace('`currency_code`', '`c`.`code`', $order);
        $order = str_replace('`cycle_name`', '`bc`.`cycle_name`', $order);
        $order = str_replace('`price`', '`psp`.`price`', $order);
        $order = str_replace('`updated_on`', '`psp`.`updated_on`', $order);

        // Main query to get the data
        $sql = "SELECT psp.id, psp.product_service_id, psp.currency_id, psp.billing_cycle_id,
                       psp.price, psp.status, psp.updated_on,
                       ps.product_name, c.code as currency_code, c.symbol as currency_symbol,
                       bc.cycle_name
                FROM product_service_pricing psp
                LEFT JOIN product_services ps ON psp.product_service_id = ps.id
                LEFT JOIN currencies c ON psp.currency_id = c.id
                LEFT JOIN billing_cycle bc ON psp.billing_cycle_id = bc.id
                $where $order $limit";

        return $sql;
    }

    // Get all active product services for dropdown
    function getAllServices() {
        $sql = "SELECT id, product_name FROM product_services WHERE status=1 ORDER BY product_name";
        return $this->db->query($sql)->result_array();
    }

    // Get all active currencies for dropdown
    function getAllCurrencies() {
        $sql = "SELECT id, code, symbol FROM currencies WHERE status=1 ORDER BY code";
        return $this->db->query($sql)->result_array();
    }

    // Get all active billing cycles for dropdown
    function getAllBillingCycles() {
        $sql = "SELECT id, cycle_name FROM billing_cycle WHERE status=1 ORDER BY id";
        return $this->db->query($sql)->result_array();
    }
}
```

##### 3. List View (package_list.php)
```php
<?php $this->load->view('whmazadmin/include/header');?>

<div class="content content-fluid content-wrapper">
    <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">
        <div class="row mt-5">
            <div class="col-md-12 col-sm-12">
                <h3 class="d-flex justify-content-between">
                    <span>Package Pricing</span>
                    <a href="<?=base_url()?>whmazadmin/package/manage" class="btn btn-sm btn-secondary">
                        <i class="fa fa-plus-square"></i>&nbsp;Add
                    </a>
                </h3>
                <hr class="mg-5" />
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mg-b-0">
                        <li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
                        <li class="breadcrumb-item active"><a href="#">Package Pricing</a></li>
                    </ol>
                </nav>
                <?php if ($this->session->flashdata('alert')) { ?>
                    <?= $this->session->flashdata('alert') ?>
                <?php } ?>
            </div>

            <div class="col-md-12 col-sm-12 mt-5">
                <table id="listDataTable" class="table table-striped table-hover"></table>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script');?>
<script>
// Helper function to escape HTML
function escapeHtml(text) {
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

$(function(){
    'use strict'

    $('#listDataTable').DataTable({
        "responsive": true,
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?=base_url()?>whmazadmin/package/ssp_list_api/",
        },
        order: [[0, 'desc']],
        "columns": [
            { "title": "ID", "data": "id", "width": "5%" },
            { "title": "Product Service", "data": "product_name", "width": "20%" },
            {
                "title": "Currency", "data": "currency_code", "width": "10%",
                "orderable": false,
                "render": function (data, type, row) {
                    return row.currency_symbol + ' (' + row.currency_code + ')';
                }
            },
            { "title": "Billing Cycle", "data": "cycle_name", "width": "15%" },
            {
                "title": "Price", "data": "price", "width": "10%",
                "className": "text-right",
                "render": function (data, type) {
                    return parseFloat(data).toFixed(2);
                }
            },
            {
                "title": "Active?", "data": "status", "width": "10%",
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
            { "title": "encoded_id", "data": "encoded_id", "visible": false, "orderable": false, "searchable": false },
            {
                "title": "Action",
                "data": "encoded_id",
                "width": "15%",
                "className": "text-center",
                "orderable": false,
                "searchable": false,
                "render": function (data, type, row) {
                    return '<button type="button" class="btn btn-xs btn-secondary" onclick="openManage(\'' + data + '\')" title="Manage"><i class="fa fa-wrench"></i></button> ' +
                           '<button type="button" class="btn btn-xs btn-danger" onclick="deleteRow(\'' + data + '\', \'' + escapeHtml(row.product_name) + '\')" title="Delete"><i class="fa fa-trash"></i></button>';
                }
            }
        ]
    });
});

function openManage(id) {
    window.location = "<?=base_url()?>whmazadmin/package/manage/"+id;
}

function deleteRow(id, title) {
    Swal.fire({
        title: 'Do you want to delete the (<b>'+title+'</b>) pricing record?',
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
            window.location = "<?=base_url()?>whmazadmin/package/delete_records/"+id;
        }
    });
}
</script>
<?php $this->load->view('whmazadmin/include/footer');?>
```

##### 4. Manage View (package_manage.php)
```php
<?php $this->load->view('whmazadmin/include/header');?>

<div class="content content-fluid content-wrapper">
    <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">
        <div class="row mt-5">
            <div class="col-md-12 col-sm-12">
                <h3 class="d-flex justify-content-between">
                    <span>Package Pricing</span>
                    <a href="<?=base_url()?>whmazadmin/package/index" class="btn btn-sm btn-secondary">
                        <i class="fa fa-arrow-left"></i>&nbsp;Back
                    </a>
                </h3>
                <hr class="mg-5" />
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mg-b-0">
                        <li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
                        <li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/package/index">Package Pricing</a></li>
                        <li class="breadcrumb-item active"><a href="#">Manage package pricing</a></li>
                    </ol>
                </nav>
                <?php if ($this->session->flashdata('alert')) { ?>
                    <?= $this->session->flashdata('alert') ?>
                <?php } ?>
            </div>

            <div class="col-md-12 col-sm-12 mt-5">
                <form method="post" name="entityManageForm" id="entityManageForm"
                      action="<?=base_url()?>whmazadmin/package/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
                    <input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

                    <!-- NOTE: All select elements should have both "form-control" and "form-select" classes -->
                    <div class="form-group">
                        <label for="product_service_id">Product Service <span class="text-danger">*</span></label>
                        <select name="product_service_id" class="form-control form-select" id="product_service_id">
                            <option value="">Select Product Service</option>
                            <?php foreach($services as $service){ ?>
                                <option value="<?= $service['id']?>"
                                        <?= (!empty($detail['product_service_id']) && $detail['product_service_id'] == $service['id']) ? 'selected' : ''?>>
                                    <?= $service['product_name']?>
                                </option>
                            <?php } ?>
                        </select>
                        <?php echo form_error('product_service_id', '<div class="error">', '</div>'); ?>
                    </div>

                    <div class="form-group">
                        <label for="currency_id">Currency <span class="text-danger">*</span></label>
                        <select name="currency_id" class="form-control form-select" id="currency_id">
                            <option value="">Select Currency</option>
                            <?php foreach($currencies as $currency){ ?>
                                <option value="<?= $currency['id']?>"
                                        <?= (!empty($detail['currency_id']) && $detail['currency_id'] == $currency['id']) ? 'selected' : ''?>>
                                    <?= $currency['symbol'] . ' (' . $currency['code'] . ')'?>
                                </option>
                            <?php } ?>
                        </select>
                        <?php echo form_error('currency_id', '<div class="error">', '</div>'); ?>
                    </div>

                    <div class="form-group">
                        <label for="billing_cycle_id">Billing Cycle <span class="text-danger">*</span></label>
                        <select name="billing_cycle_id" class="form-control form-select" id="billing_cycle_id">
                            <option value="">Select Billing Cycle</option>
                            <?php foreach($billing_cycles as $cycle){ ?>
                                <option value="<?= $cycle['id']?>"
                                        <?= (!empty($detail['billing_cycle_id']) && $detail['billing_cycle_id'] == $cycle['id']) ? 'selected' : ''?>>
                                    <?= $cycle['cycle_name']?>
                                </option>
                            <?php } ?>
                        </select>
                        <?php echo form_error('billing_cycle_id', '<div class="error">', '</div>'); ?>
                    </div>

                    <div class="form-group">
                        <label for="price">Price <span class="text-danger">*</span></label>
                        <input name="price" type="text" class="form-control" id="price"
                               value="<?= !empty($detail['price']) ? $detail['price'] : ''?>" placeholder="0.00"/>
                        <?php echo form_error('price', '<div class="error">', '</div>'); ?>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fa fa-check-circle"></i>&nbsp;Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('whmazadmin/include/footer_script');?>
<?php $this->load->view('whmazadmin/include/footer');?>
```

**Key Points:**
1. **Server-Side Encoding:** IDs are encoded on the server using `safe_encode()` to avoid "disallowed characters" errors
2. **Custom Column Rendering:** Combines multiple fields (currency symbol + code) in render functions
3. **Proper JOINs:** Uses `buildDataTableQuery()` to handle complex JOINs with column aliasing
4. **Wildcard Bindings:** Adds `%` wildcards for LIKE clause searches
5. **Status Filtering:** Always filters by `status=1` to show only active records
6. **Error Handling:** Try-catch block with proper JSON response headers
7. **Security:** Uses `escapeHtml()` to prevent XSS in delete confirmations

### AngularJS Integration Pattern

#### View with AngularJS
```php
<div class="content content-fixed content-wrapper" ng-app="AdminDashboardApp">
    <div class="container pd-x-0 pd-lg-x-10 pd-xl-x-0" ng-controller="AdminDashboardCtrl">

        <!-- Summary Cards -->
        <div class="row row-xs" ng-init="getSummaryInfo()">
            <div class="col-sm-6 col-lg-3">
                <div class="card card-body bg-success-light">
                    <h6 class="tx-uppercase tx-11 tx-spacing-1 tx-color-02 tx-semibold mg-b-8">
                        Customers
                    </h6>
                    <div class="d-flex d-lg-block d-xl-flex align-items-end">
                        <h3 class="tx-normal tx-rubik mg-b-0 mg-r-5 lh-1">
                            {{summary[0].cnt}}
                            <img src="<?=base_url()?>resources/assets/img/working.gif"
                                 ng-if="summary[0].cnt < 0" style="height: 23px" />
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dynamic List -->
        <div class="col-md-6 col-xl-4 mg-t-10" ng-init="getPendingOrders()">
            <div class="card ht-100p">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h6 class="mg-b-0">Pending orders</h6>
                    <div class="d-flex align-items-center tx-18">
                        <a href="#" ng-click="getPendingOrders()" class="link-03 lh-0">
                            <i class="icon ion-md-refresh"></i>
                        </a>
                    </div>
                </div>
                <ul class="list-group list-group-flush tx-13">
                    <li class="list-group-item d-flex pd-sm-x-20"
                        ng-repeat="obj in orders track by $index">
                        <div class="pd-l-10">
                            <p class="tx-medium mg-b-0">
                                <a href="{{baseurl}}whmazadmin/order/view/{{obj.order_uuid}}">
                                    Order #{{obj.order_no}}
                                </a>
                            </p>
                            <small class="tx-12 tx-color-03 mg-b-0">
                                Amount {{obj.currency_code}} {{obj.total_amount}}
                            </small>
                        </div>
                        <div class="mg-l-auto text-right">
                            <span ng-show="obj.status=='PAID'"
                                  class="badge rounded-pill bg-success">Paid</span>
                            <span ng-show="obj.status=='DUE'"
                                  class="badge rounded-pill bg-danger">Due</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

    </div>
</div>

<script type="text/javascript">
    var app = angular.module('AdminDashboardApp', ['ngDialog', 'ngToast', 'ngMaterial', 'ngMessages', 'ngSanitize', 'ngAnimate']);
</script>
<script src="<?=base_url()?>resources/angular/app/app.directives.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/app/app.services.js?v=1.0.0"></script>
<script src="<?=base_url()?>resources/angular/app/admindashboard_controller.js?v=1.0.0"></script>
```

---

## Controller Patterns

### Admin Controller Pattern

#### Base Admin Controller
**File:** `src/core/WHMAZADMIN_Controller.php`

```php
<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once APPPATH . "third_party/MX/Controller.php";

class WHMAZADMIN_Controller extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('Adminauth_model');
    }

    function isLogin(){
        $admin = $this->session->has_userdata('ADMIN') ?
                 $this->session->userdata('ADMIN') :
                 array('id' => 0, 'email' => '');

        if( !empty($admin) && $admin['id'] > 0 ){
            $cnt = $this->Adminauth_model->countDbSession($admin['id']);
            if( $cnt > 0 ){
                return true;
            } else{
                $resp = array('id' => 0, 'email' => '');
                $this->session->set_userdata('ADMIN', $resp);
            }
        }
        return false;
    }

    function processRestCall(){
        $_POST = json_decode(file_get_contents('php://input'), true);
    }

    function AppResponse($code, $msg, $data=array() ){
        return json_encode(array("code"=>$code, "msg"=>$msg, "data"=>$data));
    }
}
```

#### Standard CRUD Controller
**File:** `src/controllers/whmazadmin/Company.php`

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Company extends WHMAZADMIN_Controller {

    function __construct(){
        parent::__construct();
        $this->load->model('Company_model');
        $this->load->model('Common_model');

        // Authentication check
        if (!$this->isLogin()) {
            redirect('/whmazadmin/authenticate/login', 'refresh');
        }
    }

    /**
     * List all records
     */
    public function index()
    {
        $data['results'] = $this->Company_model->loadAllData();
        $this->load->view('whmazadmin/company_list', $data);
    }

    /**
     * Add/Edit form
     * @param string|null $id_val Encoded ID
     */
    public function manage($id_val = null)
    {
        if( $this->input->post() ){
            // Validation rules
            $this->form_validation->set_rules('name', 'Name', 'required|trim');
            $this->form_validation->set_message('name', 'Name is required');

            $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
            $this->form_validation->set_message('email', 'Valid email is required');

            if ($this->form_validation->run() == true){

                // Build form data array
                $form_data = array(
                    'id'           => safe_decode($this->input->post('id')),
                    'name'         => xss_cleaner($this->input->post('name')),
                    'email'        => xss_cleaner($this->input->post('email')),
                    'mobile'       => xss_cleaner($this->input->post('mobile')),
                    'address'      => xss_cleaner($this->input->post('address')),
                    'country'      => xss_cleaner($this->input->post('country')),
                    'status'       => 1
                );

                // Update or Insert logic
                if( intval($form_data['id']) > 0 ){
                    // Update existing record
                    $oldEntity = $this->Company_model->getDetail(safe_decode($id_val));
                    $form_data['updated_on'] = getDateTime();
                    $form_data['updated_by'] = getAdminId();
                    $form_data['inserted_on'] = $oldEntity['inserted_on'];
                    $form_data['inserted_by'] = $oldEntity['inserted_by'];
                } else {
                    // Insert new record
                    $form_data['inserted_on'] = getDateTime();
                    $form_data['inserted_by'] = getAdminId();
                }

                // Save to database
                $resp = $this->Company_model->saveData($form_data);
                if($resp){
                    $this->session->set_flashdata('alert',
                        successAlert('Customer has been saved successfully.'));
                    redirect("whmazadmin/company/index");
                }else {
                    $this->session->set_flashdata('alert',
                        errorAlert('Something went wrong. Try again'));
                }
            }
        }

        // Load form with data (edit) or empty (add)
        if( !empty($id_val) ){
            $data['detail'] = $this->Company_model->getDetail(safe_decode($id_val));
        } else {
            $data['detail'] = array();
        }

        // Load dropdown data
        $data['countries'] = $this->Common_model->generate_dropdown(
            'countries','country_name','country_name');

        $this->load->view('whmazadmin/company_manage', $data);
    }

    /**
     * Soft delete record
     * @param string $id_val Encoded ID
     */
    public function delete_records($id_val)
    {
        $entity = $this->Company_model->getDetail(safe_decode($id_val));
        $entity["status"] = 0;
        $entity["deleted_on"] = getDateTime();
        $entity["deleted_by"] = getAdminId();

        $this->Company_model->saveData($entity);
        $this->session->set_flashdata('alert',
            successAlert('Customer has been deleted successfully.'));

        redirect('whmazadmin/company/index');
    }
}
```

### Client Controller Pattern (HMVC Module)

#### Base Client Controller
**File:** `src/core/WHMAZ_Controller.php`

```php
<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once APPPATH . "third_party/MX/Controller.php";

class WHMAZ_Controller extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('Auth_model');
        $this->load->model('Cart_model');
        $this->loadDefaultCurrency();
    }

    public function loadDefaultCurrency(){
        if( empty($this->session->currency_id) &&
            empty($this->session->currency_code) ){
            $cr = $this->Cart_model->getCurrencies();
            foreach ($cr as $rw){
                if( $rw['is_default'] == 1 ){
                    $this->session->currency_id = $rw['id'];
                    $this->session->currency_code = $rw['code'];
                    break;
                }
            }
        }
    }

    function isLogin(){
        $user = $this->session->has_userdata('CUSTOMER') ?
                $this->session->userdata("CUSTOMER") :
                array();

        if( !empty($user) && $user['id'] > 0 ){
            $cnt = $this->Auth_model->countDbSession($user['id']);
            if( $cnt > 0 ){
                return true;
            }
        }
        return false;
    }
}
```

#### Module Controller Example
**File:** `src/modules/auth/controllers/Auth.php`

```php
<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends WHMAZ_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('Auth_model');
    }

    public function login()
    {
        $redirectUrl = isset($_GET["redirect-url"]) ? $_GET["redirect-url"] : "";

        if ($this->input->post()) {
            $username = xss_cleaner($this->input->post('username'));
            $password = xss_cleaner($this->input->post('password'));

            $resp = $this->Auth_model->doLogin($username, $password);
            if ($resp['status_code'] == 1) {
                $this->session->set_userdata("CUSTOMER", $resp['data']);

                if( !empty($redirectUrl) ){
                    header("Location: ".$redirectUrl);
                    die();
                } else{
                    redirect('/clientarea/index', 'refresh');
                }
            } else {
                $this->session->set_flashdata('alert',
                    errorAlert('Invalid username/password. Try Again'));
            }
        }

        $this->load->view('auth_login');
    }

    public function logout()
    {
        $resp = array('id' => 0, 'email' => '');
        $this->session->unset_userdata('CUSTOMER', $resp);
        $this->session->sess_destroy();
        $this->session->set_flashdata('alert', errorAlert('Logout success !!!'));
        redirect('/auth/login', 'refresh');
    }

    public function register()
    {
        if ($this->input->post()) {
            $this->form_validation->set_rules('first_name', 'First name', 'required|trim');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]|trim');

            if ($this->form_validation->run() == true){
                $form_data = array(
                    'first_name'   => xss_cleaner($this->input->post('first_name')),
                    'last_name'    => xss_cleaner($this->input->post('last_name')),
                    'email'        => xss_cleaner($this->input->post('email')),
                    'password'     => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
                    'inserted_on'  => getDateTime(),
                    'status'       => 1
                );

                $resp = $this->Auth_model->registerUser($form_data);
                if($resp){
                    $this->session->set_flashdata('alert',
                        successAlert('Registration successful. Please login.'));
                    redirect('auth/login');
                }
            }
        }

        $this->load->view('auth_register');
    }
}
```

---

## Model Patterns

### Standard Model Structure

**File:** `src/models/Company_model.php`

```php
<?php
class Company_model extends CI_Model{
    var $table;

    function __construct(){
        parent::__construct();
        $this->load->database();
        $this->table = "companies";
    }

    /**
     * Load all active records
     * @return array
     */
    function loadAllData() {
        $sql = "SELECT * FROM $this->table WHERE status=1 ORDER BY id DESC";
        $data = $this->db->query($sql)->result_array();
        return $data;
    }

    /**
     * Get single record by ID
     * @param int $id
     * @return array
     */
    function getDetail($id) {
        $sql = "SELECT * FROM $this->table WHERE id=? and status=1";
        $data = $this->db->query($sql, array($id))->result_array();
        return !empty($data) ? $data[0] : array();
    }

    /**
     * Insert or Update record
     * @param array $data
     * @return array
     */
    function saveData($data) {
        $return['id'] = 0;

        if( !empty($data['id']) && $data['id'] > 0){
            // Update existing record
            $this->db->where('id', $data['id']);
            if ($this->db->update($this->table, $data)) {
                $return['id'] = $data['id'];
            }
        } else {
            // Insert new record
            unset($data['id']); // Remove id field for insert
            if ($this->db->insert($this->table, $data)) {
                $return['id'] = $this->db->insert_id();
            }
        }

        return $return;
    }

    /**
     * Count records for specific condition
     * @param string $email
     * @return int
     */
    function countByEmail($email) {
        $sql = "SELECT COUNT(*) as cnt FROM $this->table WHERE email=? AND status=1";
        $data = $this->db->query($sql, array($email))->result_array();
        return !empty($data) ? $data[0]['cnt'] : 0;
    }
}
?>
```

### Common Model (Reusable Functions)

**File:** `src/models/Common_model.php`

```php
<?php
class Common_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Generate dropdown array for forms
     * @param string $table Table name
     * @param string $id ID field name
     * @param string $field Display field name
     * @param string|null $field2 Optional second display field
     * @param string|null $field3 Optional third display field
     * @return array
     */
    public function generate_dropdown($table, $id, $field, $field2=null, $field3=null) {
        $data[''] = '-- Select One --';

        if( !empty($field2) && !empty($field3) ){
            $this->db->select("$id, $field, $field2, $field3");
        } else if( !empty($field2) && empty($field3) ){
            $this->db->select("$id, $field, $field2");
        } else {
            $this->db->select("$id, $field");
        }

        $this->db->from($table);
        $this->db->order_by($id, 'DESC');
        $this->db->where("status", 1);
        $query = $this->db->get();

        foreach ($query->result_array() AS $rows) {
            $data[$rows[$id]] = $rows[$field] .
                (!empty( $field2 ) ? ' - '.$rows[$field2] : '') .
                (!empty( $field3 ) ? ' - '.$rows[$field3] : '');
        }

        return $data;
    }

    /**
     * Generic insert
     * @param string $table
     * @param array $data
     * @return bool
     */
    public function save($table, $data) {
        if ($this->db->insert($table, $data)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Generic update
     * @param string $table
     * @param array $data
     * @param int $id
     * @return bool
     */
    public function update($table, $data, $id) {
        $this->db->where('id', $id);
        if ($this->db->update($table, $data)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get single record by ID
     * @param string $table
     * @param int $id
     * @return object|array
     */
    public function get_data_by_id($table, $id) {
        $this->db->select("*");
        $this->db->from($table);
        $this->db->where(array(
            'status'=>'1',
            'id'=> $id
        ));
        $query = $this->db->get();

        if ($query) {
            $res = $query->result();
            return $res[0];
        } else {
            return array();
        }
    }
}
?>
```

---

## Database Query Patterns

### Query Methods

#### 1. Simple Query
```php
$sql = "SELECT * FROM companies WHERE status=1";
$data = $this->db->query($sql)->result_array();
```

#### 2. Query with Parameter Binding (Recommended)
```php
$sql = "SELECT * FROM companies WHERE id=? AND status=?";
$data = $this->db->query($sql, array($id, 1))->result_array();
```

#### 3. Query Builder Pattern
```php
$this->db->select("id, name, email");
$this->db->from("companies");
$this->db->where("status", 1);
$this->db->where("country", "Bangladesh");
$this->db->order_by("id", "DESC");
$this->db->limit(10);
$query = $this->db->get();
$data = $query->result_array();
```

#### 4. Join Query
```php
$sql = "SELECT c.*, u.first_name, u.last_name
        FROM companies c
        LEFT JOIN users u ON c.id = u.company_id
        WHERE c.status=? AND u.status=?";
$data = $this->db->query($sql, array(1, 1))->result_array();
```

#### 5. Insert
```php
$data = array(
    'name'  => 'Company Name',
    'email' => 'email@example.com',
    'status'=> 1
);
$this->db->insert('companies', $data);
$insert_id = $this->db->insert_id();
```

#### 6. Update
```php
$data = array(
    'name'  => 'Updated Name',
    'email' => 'newemail@example.com'
);
$this->db->where('id', $id);
$this->db->update('companies', $data);
```

#### 7. Delete (Soft Delete Recommended)
```php
// Soft delete (recommended)
$data = array(
    'status' => 0,
    'deleted_on' => getDateTime(),
    'deleted_by' => getAdminId()
);
$this->db->where('id', $id);
$this->db->update('companies', $data);

// Hard delete (use with caution)
$this->db->where('id', $id);
$this->db->delete('companies');
```

### DataTable Server-Side Processing

#### Helper Function Usage
**File:** `src/helpers/ssp_helper.php`

```php
// Controller method
public function ssp_list_api()
{
    $this->processRestCall();
    $params = $this->input->get();
    $bindings = array();
    $where = array();

    // Build SQL query using helper
    $sqlQuery = ssp_sql_query($params, "invoice_view", $bindings, $where);

    // Get data
    $data = $this->Invoice_model->getDataTableRecords($sqlQuery, $bindings);

    // Return JSON response
    echo json_encode(array(
        "draw"            => !empty($params['draw']) ? $params['draw'] : 0,
        "recordsTotal"    => intval($this->Invoice_model->countDataTableTotalRecords()),
        "recordsFiltered" => intval($this->Invoice_model->countDataTableFilterRecords($where, $bindings)),
        "data"            => $data
    ));
}

// Model methods
public function getDataTableRecords($sqlQuery, $bindings) {
    return $this->db->query($sqlQuery, $bindings)->result_array();
}

public function countDataTableTotalRecords() {
    return $this->db->query("SELECT COUNT(*) as cnt FROM invoice_view")->row()->cnt;
}

public function countDataTableFilterRecords($where, $bindings) {
    return $this->db->query("SELECT COUNT(*) as cnt FROM invoice_view $where", $bindings)->row()->cnt;
}
```

---

## Form and Validation Patterns

### Form Validation

#### Controller Validation
```php
if( $this->input->post() ){
    // Set validation rules
    $this->form_validation->set_rules('name', 'Name', 'required|trim');
    $this->form_validation->set_message('name', 'Name is required');

    $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
    $this->form_validation->set_message('email', 'Valid email is required');

    $this->form_validation->set_rules('mobile', 'Mobile', 'required|numeric|trim');
    $this->form_validation->set_message('mobile', 'Valid mobile number is required');

    $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]|max_length[20]|trim');
    $this->form_validation->set_message('password', 'Password must be 6-20 characters');

    if ($this->form_validation->run() == true){
        // Process form
        $form_data = array(
            'name'    => xss_cleaner($this->input->post('name')),
            'email'   => xss_cleaner($this->input->post('email')),
            'mobile'  => xss_cleaner($this->input->post('mobile')),
            'password'=> password_hash($this->input->post('password'), PASSWORD_DEFAULT)
        );

        // Save data...
        $resp = $this->Model->saveData($form_data);
        if($resp){
            $this->session->set_flashdata('alert', successAlert('Record saved successfully.'));
            redirect("controller/index");
        }else {
            $this->session->set_flashdata('alert', errorAlert('Something went wrong. Try again'));
        }
    }
}
```

### Common Validation Rules

| Rule | Description | Example |
|------|-------------|---------|
| `required` | Field cannot be empty | `'required'` |
| `valid_email` | Must be valid email | `'required\|valid_email'` |
| `min_length[n]` | Minimum length | `'min_length[6]'` |
| `max_length[n]` | Maximum length | `'max_length[20]'` |
| `numeric` | Must be numeric | `'required\|numeric'` |
| `alpha` | Alphabetic characters only | `'required\|alpha'` |
| `alpha_numeric` | Alphanumeric only | `'required\|alpha_numeric'` |
| `trim` | Trim whitespace | `'required\|trim'` |
| `matches[field]` | Must match another field | `'matches[password]'` |

### Display Validation Errors

```php
<!-- Individual field error -->
<?php echo form_error('field_name', '<div class="error">', '</div>'); ?>

<!-- All errors at once -->
<?php echo validation_errors('<div class="error">', '</div>'); ?>
```

---

## JavaScript and AJAX Patterns

### AngularJS Patterns

#### Service Factory
**File:** `resources/angular/app/app.services.js`

```javascript
app.factory('Communication', function ($http, $q, $timeout, CommunicationService) {
    return {
        request: function (type, url, reqData) {
            var deferred = $q.defer();
            $timeout(function () {
                var req = CommunicationService.getRequest(type, url, reqData);
                $http(req).then(
                    function (msg) {
                        deferred.resolve(msg.data);
                    },
                    function (err) {
                        deferred.reject(err);
                    }
                );
            });
            return deferred.promise;
        },
    };
});

app.service('CommunicationService', function () {
    this.getRequest = function (type, url, reqData) {
        return {
            method: type,
            url: url,
            headers: {'Content-Type': 'application/json'},
            data: reqData
        };
    };
});
```

#### Controller Pattern
**File:** `resources/angular/app/admindashboard_controller.js`

```javascript
app.controller('AdminDashboardCtrl', function ($scope, $http, Communication) {
    $scope.baseurl = BASE_URL;
    $scope.tickets = [];
    $scope.invoices = [];
    $scope.summary = [];

    // Get summary data
    $scope.getSummaryInfo = function(){
        $scope.summary = [];
        $scope.summary[0] = {"cnt":-1}; // Loading indicator
        $scope.summary[1] = {"cnt":-1};

        var req = Communication.request("POST", BASE_URL + 'whmazadmin/dashboard/summary_api', {});
        req.then(function (resp) {
            $scope.summary = resp;
        }, function (err) {
            console.log("summary error", JSON.stringify(err));
        });
    };

    // Get support tickets
    $scope.getSupportTickets = function(){
        $scope.tickets = [];
        var req = Communication.request("POST", BASE_URL + 'whmazadmin/ticket/recent_list_api', {"limit":5});
        req.then(function (resp) {
            $scope.tickets = resp;
        }, function (err) {
            console.log("tickets error", JSON.stringify(err));
        });
    };

    // Refresh data
    $scope.refreshData = function(){
        $scope.getSummaryInfo();
        $scope.getSupportTickets();
    };
});
```

### jQuery AJAX Pattern

```javascript
// GET Request
$.ajax({
    url: "<?=base_url()?>controller/method/" + id,
    method: "GET",
    dataType: "json",
    success: function(response){
        if(response.code == 1){
            console.log("Success:", response.data);
        } else {
            console.log("Error:", response.msg);
        }
    },
    error: function(xhr, status, error){
        console.log("AJAX Error:", error);
    }
});

// POST Request
$.ajax({
    url: "<?=base_url()?>controller/method",
    method: "POST",
    data: {
        field1: value1,
        field2: value2
    },
    dataType: "json",
    success: function(response){
        if(response.code == 1){
            Swal.fire('Success', response.msg, 'success');
        } else {
            Swal.fire('Error', response.msg, 'error');
        }
    }
});
```

### SweetAlert2 Patterns

#### Confirmation Dialog
```javascript
Swal.fire({
    title: 'Do you want to delete the (<b>'+title+'</b>) record?',
    showDenyButton: true,
    icon: 'question',
    confirmButtonText: 'Yes, delete',
    denyButtonText: 'No, cancel',
}).then((result) => {
    if (result.isConfirmed) {
        window.location = "<?=base_url()?>controller/delete/"+id;
    }
});
```

#### Success Message
```javascript
Swal.fire('Success', 'Record has been saved successfully.', 'success');
```

#### Error Message
```javascript
Swal.fire('Error', 'Something went wrong. Try again.', 'error');
```

### Select2 Initialization

```javascript
$(function(){
    'use strict'

    // Simple select2
    $('.select2').select2();

    // Select2 with options
    $('.select2').select2({
        placeholder: "Select an option",
        allowClear: true,
        width: '100%'
    });
});
```

---

## Helper Functions Usage

### Authentication Helpers

```php
// Customer authentication
if( isLoggedin() ){
    $customerId = getCustomerId();
    $companyId = getCompanyId();
    $userData = getUserData();
    $fullName = getCustomerFullName();
}

// Admin authentication
if( isAdminLoggedIn() ){
    $adminId = getAdminId();
    $adminData = getAdminData();
    $fullName = getAdminFullName();
}
```

### Alert Helpers

```php
// Success alert
$this->session->set_flashdata('alert', successAlert('Record saved successfully.'));

// Error alert
$this->session->set_flashdata('alert', errorAlert('Something went wrong. Try again'));

// Info alert
$this->session->set_flashdata('alert', primaryAlert('Please check your email for verification link.'));
```

### Security Helpers

```php
// XSS cleaning
$name = xss_cleaner($this->input->post('name'));

// URL-safe encoding
$encoded_id = safe_encode($id);
$decoded_id = safe_decode($encoded_id);

// UUID generation
$uuid = gen_uuid();
```

### Date/Time Helpers

```php
// Current datetime
$datetime = getDateTime(); // Y-m-d H:i:s

// Current date
$date = getDateOnly(); // Y-m-d

// Add days
$future_date = getDateAddDay(7); // Add 7 days

// Add months
$future_date = getDateAddMonth(1); // Add 1 month
```

### Status Badge Helpers

```php
<!-- Service status -->
<?= getServiceStatus($row['service_status']) ?>

<!-- Domain status -->
<?= getDomainStatus($row['domain_status']) ?>

<!-- Ticket status -->
<?= getTicketStatus($row['ticket_status']) ?>

<!-- Generic Yes/No status -->
<?= getRowStatus($row['status']) ?>
```

### Number Formatting

```php
// Format number with 2 decimal places
$formatted = format($amount, 2); // 1234.56

// Currency formatting
$price = format($row['price'], 2) . ' ' . $row['currency_code'];
```

---

## Naming Conventions

### Controllers
- **Class Name:** PascalCase (e.g., `Company`, `Expense_category`, `Service_group`)
- **File Name:** PascalCase.php (e.g., `Company.php`, `Expense_category.php`)
- **Method Names:** camelCase (e.g., `index()`, `manage()`, `delete_records()`)
- **Private Methods:** Prefix with underscore `_methodName()`

**Example:**
```php
class Company extends WHMAZADMIN_Controller {
    public function index() {}
    public function manage($id = null) {}
    public function delete_records($id) {}
    private function _validateData($data) {}
}
```

### Models
- **Class Name:** PascalCase with `_model` suffix (e.g., `Company_model`, `Invoice_model`)
- **File Name:** Same as class name (e.g., `Company_model.php`)
- **Method Names:** camelCase (e.g., `loadAllData()`, `getDetail()`, `saveData()`)
- **Table Property:** `var $table;`

**Example:**
```php
class Company_model extends CI_Model{
    var $table;

    function __construct(){
        $this->table = "companies";
    }

    function loadAllData() {}
    function getDetail($id) {}
    function saveData($data) {}
}
```

### Views
- **File Name:** lowercase with underscores (e.g., `company_list.php`, `invoice_manage.php`)
- **Admin Views:** `views/whmazadmin/entity_action.php`
- **Client Views:** `modules/module_name/views/view_name.php`

### Database
- **Table Names:** Lowercase with underscores, plural (e.g., `companies`, `invoices`, `order_services`)
- **Column Names:** Lowercase with underscores (e.g., `first_name`, `email_address`, `created_on`)
- **Primary Key:** Always `id` (auto-increment integer)
- **Foreign Keys:** `{table_singular}_id` (e.g., `company_id`, `user_id`)
- **Status Column:** `status` (TINYINT: 1=active, 0=deleted)
- **Timestamp Columns:**
  - `inserted_on` (DATETIME)
  - `updated_on` (DATETIME)
  - `deleted_on` (DATETIME)
- **User Tracking:**
  - `inserted_by` (INT)
  - `updated_by` (INT)
  - `deleted_by` (INT)

### Variables
- **PHP Variables:** $camelCase or $snake_case
- **Database Columns:** snake_case
- **JavaScript Variables:** camelCase
- **Constants:** UPPER_SNAKE_CASE

**Example:**
```php
// Controller
$companyId = $this->input->post('company_id');
$userData = getUserData();

// Database column
$row['first_name']
$row['email_address']

// JavaScript
var userId = 123;
var companyName = "ABC Corp";
```

### Functions/Methods
- **Public Methods:** camelCase (e.g., `getData()`, `processOrder()`)
- **Private Methods:** camelCase with underscore prefix (e.g., `_validateInput()`)
- **Helper Functions:** camelCase or snake_case (e.g., `getDateTime()`, `safe_encode()`)

---

## Security Best Practices

### 1. Input Validation and Sanitization

#### Always Use XSS Cleaning
```php
// Clean all user inputs
$name = xss_cleaner($this->input->post('name'));
$email = xss_cleaner($this->input->post('email'));

// For arrays
$data = array(
    'name'  => xss_cleaner($this->input->post('name')),
    'email' => xss_cleaner($this->input->post('email'))
);
```

#### Form Validation
```php
// Always validate before processing
$this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
$this->form_validation->set_rules('password', 'Password', 'required|min_length[6]|trim');

if ($this->form_validation->run() == true){
    // Process only if validation passes
}
```

### 2. SQL Injection Prevention

#### Use Parameter Binding
```php
// GOOD - Use parameter binding
$sql = "SELECT * FROM users WHERE email=? AND status=?";
$data = $this->db->query($sql, array($email, 1))->result_array();

// BAD - Don't concatenate user input
$sql = "SELECT * FROM users WHERE email='$email' AND status=1"; // DON'T DO THIS
```

#### Use Query Builder
```php
// Query Builder automatically escapes values
$this->db->where('email', $email);
$this->db->where('status', 1);
$query = $this->db->get('users');
```

### 3. Password Security

#### Hash Passwords
```php
// When storing password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// When verifying password
if (password_verify($input_password, $stored_hash)) {
    // Password is correct
}
```

### 4. Session Security

#### Authentication Check
```php
// In constructor
if (!$this->isLogin()) {
    redirect('/whmazadmin/authenticate/login', 'refresh');
}

// Session validation with database
function isLogin(){
    $admin = $this->session->userdata('ADMIN');
    if( !empty($admin) && $admin['id'] > 0 ){
        $cnt = $this->Adminauth_model->countDbSession($admin['id']);
        if( $cnt > 0 ){
            return true;
        }
    }
    return false;
}
```

### 5. URL Parameter Security

#### Encode Sensitive IDs
```php
// Encode ID in URL
$encoded_id = safe_encode($id);
<a href="<?=base_url()?>controller/edit/<?=$encoded_id?>">Edit</a>

// Decode in controller
$id = safe_decode($this->uri->segment(3));
```

### 6. File Upload Security

```php
$config = array(
    'upload_path'   => './uploadedfiles/',
    'allowed_types' => 'gif|jpg|jpeg|png|pdf', // Restrict file types
    'max_size'      => 2048, // 2MB max
    'encrypt_name'  => true  // Encrypt file name
);

$this->load->library('upload', $config);

if ($this->upload->do_upload('file')) {
    $file_data = $this->upload->data();
} else {
    $error = $this->upload->display_errors();
}
```

### 7. CSRF Protection

#### Enable in Config
```php
// config/config.php
$config['csrf_protection'] = TRUE;
$config['csrf_token_name'] = 'csrf_token';
$config['csrf_cookie_name'] = 'csrf_cookie';
```

#### In Forms
```php
<!-- CSRF token is automatically added to forms -->
<form method="post">
    <?php echo form_hidden('csrf_token', $this->security->get_csrf_hash()); ?>
    <!-- Form fields -->
</form>
```

### 8. Output Escaping

```php
// Escape output in views
<?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?>

// Or use CodeIgniter's xss_clean for display
<?= xss_clean($row['description']) ?>
```

### 9. Soft Delete Instead of Hard Delete

```php
// Soft delete (recommended)
$entity["status"] = 0;
$entity["deleted_on"] = getDateTime();
$entity["deleted_by"] = getAdminId();
$this->Model->saveData($entity);

// Avoid hard delete
// $this->db->delete('table', array('id' => $id)); // Use only when necessary
```

### 10. Error Handling

```php
// Don't expose sensitive error information
if (ENVIRONMENT !== 'production') {
    // Show detailed errors in development
    ini_set('display_errors', 1);
} else {
    // Hide errors in production
    ini_set('display_errors', 0);
    // Log errors instead
    log_message('error', 'Error message here');
}
```

### Security Checklist

- [ ] All user inputs are cleaned with `xss_cleaner()`
- [ ] Form validation is implemented
- [ ] SQL queries use parameter binding
- [ ] Passwords are hashed with `password_hash()`
- [ ] Authentication check in protected controllers
- [ ] Sensitive IDs are encoded in URLs
- [ ] File uploads are restricted by type and size
- [ ] CSRF protection is enabled
- [ ] Output is escaped in views
- [ ] Soft delete is used instead of hard delete
- [ ] Error logging is configured for production

---

## Additional Best Practices

### 1. Code Comments
```php
/**
 * Get company details by ID
 * @param int $id Company ID
 * @return array Company details or empty array
 */
public function getDetail($id) {
    // Implementation
}
```

### 2. DRY Principle
- Use Common_model for reusable functions
- Create helper functions for repeated code
- Use base controllers for shared functionality

### 3. Consistent Error Messages
```php
successAlert('Record has been saved successfully.')
errorAlert('Something went wrong. Try again')
primaryAlert('Please check your email for verification link.')
```

### 4. Database Transactions
```php
$this->db->trans_start();

// Multiple database operations
$this->db->insert('table1', $data1);
$this->db->update('table2', $data2, array('id' => $id));

$this->db->trans_complete();

if ($this->db->trans_status() === FALSE) {
    // Transaction failed
} else {
    // Transaction successful
}
```

### 5. Logging
```php
// Log errors
log_message('error', 'Error message: ' . $error);

// Log info
log_message('info', 'User logged in: ' . $user_id);

// Log debug
log_message('debug', 'Debug info: ' . print_r($data, true));
```

---

**Last Updated:** 2026-01-13
**Version:** 1.0
