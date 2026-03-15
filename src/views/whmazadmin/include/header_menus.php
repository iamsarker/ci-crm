<!-- Top Navigation Bar -->
<nav class="app-header navbar navbar-expand-lg admin-navbar">
    <div class="container-fluid">
        <!-- Brand -->
        <a href="<?=base_url()?>whmazadmin" class="navbar-brand">
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
                <!-- Home -->
                <li class="nav-item">
                    <a href="<?=base_url()?>whmazadmin/dashboard/index" class="nav-link">
                        <i class="fas fa-home me-1"></i> Home
                    </a>
                </li>

                <!-- Customers Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-users me-1"></i> Customers
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/company/index"><i class="fas fa-building me-2"></i>Companies</a></li>
                        <li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/company/manage"><i class="fas fa-user-plus me-2"></i>New Company</a></li>
                    </ul>
                </li>

                <!-- Orders Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-shopping-cart me-1"></i> Orders
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/order/index"><i class="fas fa-list me-2"></i>Orders</a></li>
                        <li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/order/new_order"><i class="fas fa-plus-square me-2"></i>New Order</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/provisioning/index"><i class="fas fa-cogs me-2"></i>Provisioning Logs</a></li>
                    </ul>
                </li>

                <!-- Billing Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-invoice-dollar me-1"></i> Billing
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/invoice/index"><i class="fas fa-file-alt me-2"></i>View Invoices</a></li>
                        <li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/paymentgateway/index"><i class="fas fa-credit-card me-2"></i>Payment Gateways</a></li>
                        <li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/paymentgateway/transactions"><i class="fas fa-exchange-alt me-2"></i>Transactions</a></li>
                    </ul>
                </li>

                <!-- Supports Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-headset me-1"></i> Supports
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/kb_category/index"><i class="fas fa-layer-group me-2"></i>KB Categories</a></li>
                        <li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/ticket_department/index"><i class="fas fa-sitemap me-2"></i>Departments</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/ticket/index"><i class="fas fa-ticket-alt me-2"></i>Tickets</a></li>
                        <li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/kb/index"><i class="fas fa-book me-2"></i>Knowledge Bases</a></li>
                        <li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/announcement/index"><i class="fas fa-bullhorn me-2"></i>Announcements</a></li>
                    </ul>
                </li>

                <!-- Expenses Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-wallet me-1"></i> Expenses
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/expense_category/index"><i class="fas fa-tags me-2"></i>Expense Categories</a></li>
                        <li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/expense_vendor/index"><i class="fas fa-store me-2"></i>Expense Vendors</a></li>
                        <li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/expense/index"><i class="fas fa-receipt me-2"></i>Expenses</a></li>
                    </ul>
                </li>

                <!-- Settings Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-cog me-1"></i> Settings
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/general_setting/manage"><i class="fas fa-sliders-h me-2"></i>General Settings</a></li>
                        <li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/server/index"><i class="fas fa-server me-2"></i>Servers</a></li>
                        <li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/currency/index"><i class="fas fa-dollar-sign me-2"></i>Currencies</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/service_category/index"><i class="fas fa-folder me-2"></i>Service Categories</a></li>
                        <li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/service_group/index"><i class="fas fa-object-group me-2"></i>Service Groups</a></li>
                        <li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/service_module/index"><i class="fas fa-puzzle-piece me-2"></i>Service Modules</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/service_product/index"><i class="fas fa-hdd me-2"></i>Hosting Packages</a></li>
                        <li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/domain_register/index"><i class="fas fa-globe me-2"></i>Domain Register</a></li>
                        <li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/domain_pricing/index"><i class="fas fa-tags me-2"></i>Domain Pricing</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/email_template/index"><i class="fas fa-envelope me-2"></i>Email Template</a></li>
                        <li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/page/index"><i class="fas fa-file-code me-2"></i>Dynamic Pages</a></li>
                    </ul>
                </li>
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
                    <li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/dashboard/changePassword"><i class="fas fa-key me-2"></i>Change Password</a></li>
                    <li><hr class="dropdown-divider"></li>
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
                    <li><a class="dropdown-item" href="<?=base_url()?>whmazadmin/authenticate/login"><i class="fas fa-sign-in-alt me-2"></i>Sign In</a></li>
                </ul>
            </li>
            <?php } ?>
        </ul>
    </div>
</nav>
