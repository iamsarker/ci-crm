<?php $this->load->view('whmazadmin/include/header');?>

<div class="content content-fluid content-wrapper">
    <div class="container pd-x-0 pd-lg-x-12 pd-xl-x-0">
        <div class="row mt-5">
            <div class="col-md-12 col-sm-12">
                <h3 class="d-flex justify-content-between">
                    <span>Domain Registrars</span>
                    <a href="<?=base_url()?>whmazadmin/domain_register/index" class="btn btn-sm btn-secondary">
                        <i class="fa fa-arrow-left"></i>&nbsp;Back
                    </a>
                </h3>
                <hr class="mg-5" />
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1 mg-b-0">
                        <li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/dashboard/index">Portal home</a></li>
                        <li class="breadcrumb-item"><a href="<?=base_url()?>whmazadmin/domain_register/index">Domain Registrars</a></li>
                        <li class="breadcrumb-item active"><a href="#">Manage registrar</a></li>
                    </ol>
                </nav>
            </div>

            <div class="col-md-12 col-sm-12 mt-5">
                <form method="post" name="entityManageForm" id="entityManageForm"
                      action="<?=base_url()?>whmazadmin/domain_register/manage/<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>">
                    <input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Registrar Name <span class="text-danger">*</span></label>
                                <input name="name" type="text" class="form-control" id="name"
                                       value="<?= !empty($detail['name']) ? $detail['name'] : ''?>"
                                       placeholder="e.g., Namecheap, GoDaddy"/>
                                <?php echo form_error('name', '<div class="error">', '</div>'); ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="platform">Platform <span class="text-danger">*</span></label>
                                <select name="platform" class="form-control" id="platform">
                                    <option value="">Select Platform</option>
                                    <option value="STARGATE" <?= (!empty($detail['platform']) && $detail['platform'] == 'STARGATE') ? 'selected' : ''?>>STARGATE (ResellerClub/Resell.biz)</option>
                                    <option value="NAMECHEAP" <?= (!empty($detail['platform']) && $detail['platform'] == 'NAMECHEAP') ? 'selected' : ''?>>NAMECHEAP</option>
                                    <option value="GODADDY" <?= (!empty($detail['platform']) && $detail['platform'] == 'GODADDY') ? 'selected' : ''?>>GODADDY</option>
                                    <option value="ENOM" <?= (!empty($detail['platform']) && $detail['platform'] == 'ENOM') ? 'selected' : ''?>>ENOM</option>
                                    <option value="OTHER" <?= (!empty($detail['platform']) && $detail['platform'] == 'OTHER') ? 'selected' : ''?>>OTHER</option>
                                </select>
                                <?php echo form_error('platform', '<div class="error">', '</div>'); ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="auth_userid">Auth User ID <span class="text-danger">*</span></label>
                                <input name="auth_userid" type="text" class="form-control" id="auth_userid"
                                       value="<?= !empty($detail['auth_userid']) ? $detail['auth_userid'] : ''?>"
                                       placeholder="API User ID"/>
                                <?php echo form_error('auth_userid', '<div class="error">', '</div>'); ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="auth_apikey">Auth API Key <span class="text-danger">*</span></label>
                                <input name="auth_apikey" type="text" class="form-control" id="auth_apikey"
                                       value="<?= !empty($detail['auth_apikey']) ? $detail['auth_apikey'] : ''?>"
                                       placeholder="API Key or Password"/>
                                <?php echo form_error('auth_apikey', '<div class="error">', '</div>'); ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="api_base_url">API Base URL <span class="text-danger">*</span></label>
                        <input name="api_base_url" type="text" class="form-control" id="api_base_url"
                               value="<?= !empty($detail['api_base_url']) ? $detail['api_base_url'] : ''?>"
                               placeholder="https://api.registrar.com"/>
                        <?php echo form_error('api_base_url', '<div class="error">', '</div>'); ?>
                    </div>

                    <div class="form-group">
                        <label for="domain_check_api">Domain Check API Endpoint</label>
                        <input name="domain_check_api" type="text" class="form-control" id="domain_check_api"
                               value="<?= !empty($detail['domain_check_api']) ? $detail['domain_check_api'] : ''?>"
                               placeholder="https://api.registrar.com/domains/check"/>
                    </div>

                    <div class="form-group">
                        <label for="suggestion_api">Domain Suggestion API Endpoint</label>
                        <input name="suggestion_api" type="text" class="form-control" id="suggestion_api"
                               value="<?= !empty($detail['suggestion_api']) ? $detail['suggestion_api'] : ''?>"
                               placeholder="https://api.registrar.com/domains/suggest"/>
                    </div>

                    <div class="form-group">
                        <label for="domain_reg_api">Domain Registration API Endpoint</label>
                        <input name="domain_reg_api" type="text" class="form-control" id="domain_reg_api"
                               value="<?= !empty($detail['domain_reg_api']) ? $detail['domain_reg_api'] : ''?>"
                               placeholder="https://api.registrar.com/domains/register"/>
                    </div>

                    <div class="form-group">
                        <label for="price_list_api">Price List API Endpoint</label>
                        <input name="price_list_api" type="text" class="form-control" id="price_list_api"
                               value="<?= !empty($detail['price_list_api']) ? $detail['price_list_api'] : ''?>"
                               placeholder="https://api.registrar.com/pricing/list"/>
                    </div>

                    <h5 class="mt-4 mb-3">Default Nameservers</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="def_ns1">Nameserver 1</label>
                                <input name="def_ns1" type="text" class="form-control" id="def_ns1"
                                       value="<?= !empty($detail['def_ns1']) ? $detail['def_ns1'] : ''?>"
                                       placeholder="ns1.yourdns.com"/>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="def_ns2">Nameserver 2</label>
                                <input name="def_ns2" type="text" class="form-control" id="def_ns2"
                                       value="<?= !empty($detail['def_ns2']) ? $detail['def_ns2'] : ''?>"
                                       placeholder="ns2.yourdns.com"/>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="def_ns3">Nameserver 3</label>
                                <input name="def_ns3" type="text" class="form-control" id="def_ns3"
                                       value="<?= !empty($detail['def_ns3']) ? $detail['def_ns3'] : ''?>"
                                       placeholder="ns3.yourdns.com"/>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="def_ns4">Nameserver 4</label>
                                <input name="def_ns4" type="text" class="form-control" id="def_ns4"
                                       value="<?= !empty($detail['def_ns4']) ? $detail['def_ns4'] : ''?>"
                                       placeholder="ns4.yourdns.com"/>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_selected" name="is_selected"
                                   value="1" <?= (!empty($detail['is_selected']) && $detail['is_selected'] == 1) ? 'checked' : ''?>>
                            <label class="custom-control-label" for="is_selected">Set as Default Registrar</label>
                        </div>
                    </div>

                    <div class="form-group mt-4">
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
<script>
$(function(){
    'use strict'

    // Show flash messages as toast
    <?php if ($this->session->flashdata('alert_success')) { ?>
        toastSuccess('<?= addslashes($this->session->flashdata('alert_success')) ?>');
    <?php } ?>
    <?php if ($this->session->flashdata('alert_error')) { ?>
        toastError('<?= addslashes($this->session->flashdata('alert_error')) ?>');
    <?php } ?>
});
</script>
<?php $this->load->view('whmazadmin/include/footer');?>
