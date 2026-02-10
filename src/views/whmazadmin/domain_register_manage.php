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
					<?=csrf_field()?>
                    <input name="id" type="hidden" id="id" value="<?= safe_encode(!empty($detail['id']) ? $detail['id'] : 0)?>" />

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Registrar Name <span class="text-danger">*</span></label>
                                <input name="name" type="text" class="form-control" id="name"
                                       value="<?= htmlspecialchars($detail['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                       placeholder="e.g., Namecheap, GoDaddy"/>
                                <?php echo form_error('name', '<div class="error">', '</div>'); ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="platform">Platform <span class="text-danger">*</span></label>
                                <select name="platform" class="form-control form-select" id="platform">
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
                                       value="<?= htmlspecialchars($detail['auth_userid'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                       placeholder="API User ID"/>
                                <?php echo form_error('auth_userid', '<div class="error">', '</div>'); ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="auth_apikey">Auth API Key <span class="text-danger">*</span></label>
                                <input name="auth_apikey" type="text" class="form-control" id="auth_apikey"
                                       value="<?= htmlspecialchars($detail['auth_apikey'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                       placeholder="API Key or Password"/>
                                <?php echo form_error('auth_apikey', '<div class="error">', '</div>'); ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="api_base_url">API Base URL <span class="text-danger">*</span></label>
                        <input name="api_base_url" type="text" class="form-control" id="api_base_url"
                               value="<?= htmlspecialchars($detail['api_base_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="https://api.registrar.com"/>
                        <?php echo form_error('api_base_url', '<div class="error">', '</div>'); ?>
                    </div>

                    <div class="form-group">
                        <label for="domain_check_api">Domain Check API Endpoint</label>
                        <input name="domain_check_api" type="text" class="form-control" id="domain_check_api"
                               value="<?= htmlspecialchars($detail['domain_check_api'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="https://api.registrar.com/domains/check"/>
                    </div>

                    <div class="form-group">
                        <label for="suggestion_api">Domain Suggestion API Endpoint</label>
                        <input name="suggestion_api" type="text" class="form-control" id="suggestion_api"
                               value="<?= htmlspecialchars($detail['suggestion_api'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="https://api.registrar.com/domains/suggest"/>
                    </div>

                    <div class="form-group">
                        <label for="domain_reg_api">Domain Registration API Endpoint</label>
                        <input name="domain_reg_api" type="text" class="form-control" id="domain_reg_api"
                               value="<?= htmlspecialchars($detail['domain_reg_api'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="https://api.registrar.com/domains/register"/>
                    </div>

                    <div class="form-group">
                        <label for="ns_update_api">Nameserver Update API Endpoint</label>
                        <input name="ns_update_api" type="text" class="form-control" id="ns_update_api"
                               value="<?= htmlspecialchars($detail['ns_update_api'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="https://api.registrar.com/domains/modify-ns"/>
                        <small class="form-text text-muted">API endpoint for updating domain nameservers</small>
                    </div>

                    <div class="form-group">
                        <label for="contact_details_api">Contact Details API Endpoint</label>
                        <input name="contact_details_api" type="text" class="form-control" id="contact_details_api"
                               value="<?= htmlspecialchars($detail['contact_details_api'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="https://api.registrar.com/domains/details"/>
                        <small class="form-text text-muted">API endpoint for fetching domain contact details</small>
                    </div>

                    <div class="form-group">
                        <label for="contact_update_api">Contact Update API Endpoint</label>
                        <input name="contact_update_api" type="text" class="form-control" id="contact_update_api"
                               value="<?= htmlspecialchars($detail['contact_update_api'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="https://api.registrar.com/domains/modify-contact"/>
                        <small class="form-text text-muted">API endpoint for updating domain contact information</small>
                    </div>

                    <div class="form-group">
                        <label for="price_list_api">Price List API Endpoint</label>
                        <input name="price_list_api" type="text" class="form-control" id="price_list_api"
                               value="<?= htmlspecialchars($detail['price_list_api'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="https://api.registrar.com/pricing/list"/>
                    </div>

                    <h5 class="mt-4 mb-3">Default Nameservers</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="def_ns1">Nameserver 1</label>
                                <input name="def_ns1" type="text" class="form-control" id="def_ns1"
                                       value="<?= htmlspecialchars($detail['def_ns1'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                       placeholder="ns1.yourdns.com"/>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="def_ns2">Nameserver 2</label>
                                <input name="def_ns2" type="text" class="form-control" id="def_ns2"
                                       value="<?= htmlspecialchars($detail['def_ns2'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                       placeholder="ns2.yourdns.com"/>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="def_ns3">Nameserver 3</label>
                                <input name="def_ns3" type="text" class="form-control" id="def_ns3"
                                       value="<?= htmlspecialchars($detail['def_ns3'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                       placeholder="ns3.yourdns.com"/>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="def_ns4">Nameserver 4</label>
                                <input name="def_ns4" type="text" class="form-control" id="def_ns4"
                                       value="<?= htmlspecialchars($detail['def_ns4'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
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
<?php $this->load->view('whmazadmin/include/footer');?>
