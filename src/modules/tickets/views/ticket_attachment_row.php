<div class="row" id="registry_no_<?php echo $sub_registry_form_inc; ?>">
    <div class="form-group col-md-10 ">
        <input type="file" name="attachment[]" class="form-control"
            accept=".gif,.jpg,.jpeg,.png,.pdf,.txt"
            data-max-size="5242880"
            onchange="validateFileUpload(this)">
    </div><!-- form-group -->
    <div class="form-group col-md-2 ">
        <button onclick="rm_registry_form(<?= $sub_registry_form_inc; ?>)" type="button" class="btn btn-danger pd-x-50 remove">Del</button>
    </div>
</div>
