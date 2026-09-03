<!-- Top Navigation Bar -->
<nav class="app-header navbar navbar-expand-lg admin-navbar">
    <div class="container-fluid">
        <!-- Brand -->
        <a href="<?=base_url()?>whmazadmin/dashboard/index" class="navbar-brand">
            <span class="brand-text">WHM<span>A-Z</span> Admin</span>
        </a>

        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fas fa-bars text-white"></i>
        </button>

        <!-- Navigation Menu -->
        <div class="collapse navbar-collapse" id="adminNavbar">
            <?php if( isAdminLoggedIn() ){?>
            <ul class="navbar-nav me-auto">

                <!-- Customers Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-users me-1"></i> Customers
                    </a>
                    <ul class="dropdown-menu">
                        <?php if (admin_can('company')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/company/index"><i class="fas fa-building me-2"></i>Companies</a></li><?php endif; ?>
                        <?php if (admin_can('company')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/company/manage"><i class="fas fa-user-plus me-2"></i>New Company</a></li><?php endif; ?>
                    </ul>
                </li>

                <!-- Orders Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-shopping-cart me-1"></i> Orders
                    </a>
                    <ul class="dropdown-menu">
                        <?php if (admin_can('order')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/order/index"><i class="fas fa-list me-2"></i>Orders</a></li><?php endif; ?>
                        <?php if (admin_can('order')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/order/new_order"><i class="fas fa-plus-square me-2"></i>New Order</a></li><?php endif; ?>
                        <?php if (!isResellerAdmin()): ?><li><hr class="dropdown-divider"></li><?php endif; ?>
                        <?php if (admin_can('provisioning')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/provisioning/index"><i class="fas fa-cogs me-2"></i>Provisioning Logs</a></li><?php endif; ?>
                        <?php if (admin_can('cancellation')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/cancellation/index"><i class="fas fa-times-circle me-2"></i>Cancellation Requests</a></li><?php endif; ?>
                    </ul>
                </li>

                <!-- Invoicing Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-invoice-dollar me-1"></i> Invoicing
                    </a>
                    <ul class="dropdown-menu">
                        <?php if (admin_can('invoice')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/invoice/index"><i class="fas fa-file-alt me-2"></i>View Invoices</a></li><?php endif; ?>
                        <?php if (admin_can('paymentgateway')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/paymentgateway/transactions"><i class="fas fa-exchange-alt me-2"></i>Transactions</a></li><?php endif; ?>
                        <?php if (admin_can('paymentgateway')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/paymentgateway/webhooks"><i class="fas fa-satellite-dish me-2"></i>Webhook Logs</a></li><?php endif; ?>
                    </ul>
                </li>

                <!-- Supports Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-headset me-1"></i> Supports
                    </a>
                    <ul class="dropdown-menu">
                        <?php if (admin_can('kb_category')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/kb_category/index"><i class="fas fa-layer-group me-2"></i>KB Categories</a></li><?php endif; ?>
                        <?php if (admin_can('ticket_department')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/ticket_department/index"><i class="fas fa-sitemap me-2"></i>Departments</a></li><?php endif; ?>
                        <?php if (!isResellerAdmin()): ?><li><hr class="dropdown-divider"></li><?php endif; ?>
                        <?php if (admin_can('ticket')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/ticket/index"><i class="fas fa-ticket-alt me-2"></i>Tickets</a></li><?php endif; ?>
                        <?php if (admin_can('kb')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/kb/index"><i class="fas fa-book me-2"></i>Knowledge Bases</a></li><?php endif; ?>
                        <?php if (admin_can('announcement')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/announcement/index"><i class="fas fa-bullhorn me-2"></i>Announcements</a></li><?php endif; ?>
                    </ul>
                </li>

                <!-- Expenses Dropdown -->
                <?php /* The platform operator's own bookkeeping. No reseller-scoped
                        meaning at all, so the whole dropdown goes rather than
                        rendering three items that every 403. */ ?>
                <?php if (!isResellerAdmin()): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-wallet me-1"></i> Expenses
                    </a>
                    <ul class="dropdown-menu">
                        <?php if (admin_can('expense_category')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/expense_category/index"><i class="fas fa-tags me-2"></i>Expense Categories</a></li><?php endif; ?>
                        <?php if (admin_can('expense_vendor')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/expense_vendor/index"><i class="fas fa-store me-2"></i>Expense Vendors</a></li><?php endif; ?>
                        <?php if (admin_can('expense')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/expense/index"><i class="fas fa-receipt me-2"></i>Expenses</a></li><?php endif; ?>
                    </ul>
                </li>

                <?php endif; /* end Expenses */ ?>

                <!-- Settings Dropdown -->
                <?php /* Of the 18 Settings entries a reseller may reach only
                        API Keys today, so hide the dropdown entirely unless at
                        least one item survives admin_can(). */ ?>
                <?php if (!isResellerAdmin() || admin_can('apikey')): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-cog me-1"></i> Settings
                    </a>
                    <ul class="dropdown-menu">
                        <?php if (admin_can('general_setting')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/general_setting/manage"><i class="fas fa-sliders-h me-2"></i>General Settings</a></li><?php endif; ?>
                        <?php if (admin_can('server_module')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/server_module/index"><i class="fas fa-puzzle-piece me-2"></i>Server Modules</a></li><?php endif; ?>
                        <?php if (admin_can('server')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/server/index"><i class="fas fa-server me-2"></i>Servers</a></li><?php endif; ?>
                        <?php if (admin_can('currency')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/currency/index"><i class="fas fa-dollar-sign me-2"></i>Currencies</a></li><?php endif; ?>
                        <?php if (!isResellerAdmin()): ?><li><hr class="dropdown-divider"></li><?php endif; ?>
                        <?php if (admin_can('paymentgateway')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/paymentgateway/index"><i class="fas fa-credit-card me-2"></i>Payment Gateways</a></li><?php endif; ?>
                        <?php if (admin_can('promocode')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/promocode/index"><i class="fas fa-tags me-2"></i>Promo Codes</a></li><?php endif; ?>
                        <?php if (!isResellerAdmin()): ?><li><hr class="dropdown-divider"></li><?php endif; ?>
                        <?php if (admin_can('reseller')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/reseller/index"><i class="fas fa-user-tie me-2"></i>Reseller Management</a></li><?php endif; ?>
                        <?php if (admin_can('reseller_pricing')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/reseller_pricing/index"><i class="fas fa-tags me-2"></i><?= isResellerAdmin() ? 'My Selling Prices' : 'Reseller Pricing' ?></a></li><?php endif; ?>
                        <?php if (admin_can('apikey')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/apikey/index"><i class="fas fa-key me-2"></i>API Keys</a></li><?php endif; ?>
                        <?php if (!isResellerAdmin()): ?><li><hr class="dropdown-divider"></li><?php endif; ?>
                        <?php if (admin_can('service_category')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/service_category/index"><i class="fas fa-folder me-2"></i>Service Categories</a></li><?php endif; ?>
                        <?php if (admin_can('service_group')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/service_group/index"><i class="fas fa-object-group me-2"></i>Service Groups</a></li><?php endif; ?>
                        <?php if (admin_can('service_product')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/service_product/index"><i class="fas fa-hdd me-2"></i>Hosting Packages</a></li><?php endif; ?>
                        <?php if (feature_enabled('domain_registration_transfers')): ?>
                        <?php if (!isResellerAdmin()): ?><li><hr class="dropdown-divider"></li><?php endif; ?>
                        <?php if (admin_can('domain_register')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/domain_register/index"><i class="fas fa-globe me-2"></i>Domain Register</a></li><?php endif; ?>
                        <?php if (admin_can('domain_pricing')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/domain_pricing/index"><i class="fas fa-tags me-2"></i>Domain Pricing</a></li><?php endif; ?>
                        <?php endif; ?>
                        <?php if (feature_enabled('software_license_selling')): ?>
                        <?php if (!isResellerAdmin()): ?><li><hr class="dropdown-divider"></li><?php endif; ?>
                        <?php if (admin_can('softwareproduct')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/softwareproduct/index"><i class="fas fa-cube me-2"></i>Software Products</a></li><?php endif; ?>
                        <?php if (admin_can('software')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/software"><i class="fas fa-cube me-2"></i>Software Releases</a></li><?php endif; ?>
                        <?php endif; ?>
                        <?php if (!isResellerAdmin()): ?><li><hr class="dropdown-divider"></li><?php endif; ?>
                        <?php if (admin_can('email_template')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/email_template/index"><i class="fas fa-envelope me-2"></i>Email Template</a></li><?php endif; ?>
                        <?php if (admin_can('page')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/page/index"><i class="fas fa-file-code me-2"></i>Dynamic Pages</a></li><?php endif; ?>
                    </ul>
                </li>
                <?php endif; /* end Settings */ ?>
            </ul>
            <?php } else { ?>
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a href="<?=base_url()?>whmazadmin/dashboard/index" class="nav-link">
                        <i class="fas fa-home me-1"></i> Home
                    </a>
                </li>
            </ul>
            <?php } ?>
        </div>

        <!-- Right Side - User Profile -->
        <ul class="navbar-nav ms-auto">
            <?php if( isAdminLoggedIn() ){ $admin = getAdminData();?>
            <!-- Notifications Bell -->
            <li class="nav-item dropdown me-2">
                <a class="nav-link position-relative" href="#" id="notifBell" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Notifications">
                    <i class="fas fa-bell text-white"></i>
                    <span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle notif-badge-hidden" id="notif-badge"></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end notif-dropdown">
                    <li class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                        <strong class="text-dark">Notifications</strong>
                        <a href="#" class="small text-decoration-none" id="notif-mark-all">Mark all read</a>
                    </li>
                    <li>
                        <div id="notif-list" class="notif-list">
                            <div class="text-center text-muted py-4" id="notif-empty">No notifications</div>
                        </div>
                    </li>
                </ul>
            </li>
            <li class="nav-item dropdown dropdown-profile">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="avatar avatar-sm me-2">
                        <img src="<?=base_url()?>resources/assets/img/default.jpg" class="rounded-circle" alt="" class="admin-avatar-sm">
                    </div>
                    <span class="d-none d-lg-inline text-white"><?=htmlspecialchars($admin['first_name'])?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="px-3 py-2 text-center border-bottom">
                        <div class="avatar avatar-lg mx-auto mb-2">
                            <img src="<?=base_url()?>resources/assets/img/default.jpg" class="rounded-circle" alt="" class="admin-avatar-md">
                        </div>
                        <h6 class="mb-0"><?=htmlspecialchars($admin['first_name'].' '.$admin['last_name'])?></h6>
                    </li>
                    <?php if (admin_can('dashboard')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/dashboard/changePassword"><i class="fas fa-key me-2"></i>Change Password</a></li><?php endif; ?>
                    <?php if (!isResellerAdmin()): ?><li><hr class="dropdown-divider"></li><?php endif; ?>
                    <li><a class="dropdown-item text-danger" href="<?=base_url()?>whmazadmin/authenticate/logout"><i class="fas fa-sign-out-alt me-2"></i>Sign Out</a></li>
                </ul>
            </li>
            <?php } else { ?>
            <li class="nav-item dropdown dropdown-profile">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="avatar avatar-sm">
                        <img src="<?=base_url()?>resources/assets/img/default.jpg" class="rounded-circle" alt="" class="admin-avatar-sm">
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="px-3 py-2 text-center border-bottom">
                        <div class="avatar avatar-lg mx-auto mb-2">
                            <img src="<?=base_url()?>resources/assets/img/default.jpg" class="rounded-circle" alt="" class="admin-avatar-md">
                        </div>
                        <h6 class="mb-0">WHMAZ</h6>
                    </li>
                    <?php if (admin_can('authenticate')): ?><li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/authenticate/login"><i class="fas fa-sign-in-alt me-2"></i>Sign In</a></li><?php endif; ?>
                </ul>
            </li>
            <?php } ?>
        </ul>
    </div>
</nav>
