<header class="navbar navbar-header navbar-header-fixed">
	<a href="" id="mainMenuOpen" class="burger-menu"><i data-feather="menu"></i></a>
	<div class="navbar-brand">
		<a href="<?=base_url()?>clientarea" class="df-logo">WHM<span>A-Z</span></a>
	</div><!-- navbar-brand -->
	<div id="navbarMenu" class="navbar-menu-wrapper">
		<div class="navbar-menu-header">
			<a href="<?=base_url()?>clientarea" class="df-logo">WHM<span>A-Z</span></a>
			<a id="mainMenuClose" href=""><i data-feather="x"></i></a>
		</div><!-- navbar-menu-header -->
		<ul class="nav navbar-menu">
			<li class="nav-label pd-l-20 pd-lg-l-25 d-lg-none">Main Navigation</li>

			<?php if( isAdminLoggedIn() ){?>

				<li class="nav-item"><a href="<?=base_url()?>whmazadmin/dashboard/index" class="nav-link"><i data-feather="home"></i> Home</a></li>

				<li class="nav-item with-sub">
					<a href="" class="nav-link"><i data-feather="users"></i> Customers</a>
					<ul class="navbar-menu-sub">
						<li class="nav-sub-item"><a href="<?=base_url()?>whmazadmin/company/index" class="nav-sub-link"><i data-feather="users"></i>Companies</a></li>
						<li class="nav-sub-item"><a href="<?=base_url()?>whmazadmin/company/manage" class="nav-sub-link"><i data-feather="user-plus"></i>New company</a></li>
					</ul>
				</li>

				<li class="nav-item with-sub">
					<a href="" class="nav-link"><i data-feather="pie-chart"></i> Orders</a>
					<ul class="navbar-menu-sub">
						<li class="nav-sub-item"><a href="<?=base_url()?>whmazadmin/order/index" class="nav-sub-link"><i data-feather="shopping-cart"></i>Orders</a></li>
						<li class="nav-sub-item"><a href="<?=base_url()?>whmazadmin/order/manage" class="nav-sub-link"><i data-feather="plus-square"></i>New order</a></li>
					</ul>
				</li>

				<li class="nav-item with-sub">
					<a href="" class="nav-link"><i data-feather="pie-chart"></i> Billing</a>
					<ul class="navbar-menu-sub">
						<li class="nav-sub-item"><a href="<?=base_url()?>whmazadmin/invoice/index" class="nav-sub-link"><i data-feather="file-text"></i>View invoices</a></li>
						<li class="nav-sub-item"><a href="<?=base_url()?>whmazadmin/invoice/add" class="nav-sub-link"><i data-feather="file-plus"></i>New invoice</a></li>
						<li class="nav-sub-item">&nbsp;</li>
						<li class="nav-sub-item"><a href="<?=base_url()?>whmazadmin/billing/quotes" class="nav-sub-link"><i data-feather="file-minus"></i>View quotes</a></li>
						<li class="nav-sub-item"><a href="<?=base_url()?>whmazadmin/billing/quotes" class="nav-sub-link"><i data-feather="file-plus"></i>New quote</a></li>
					</ul>
				</li>

				<li class="nav-item with-sub">
					<a href="" class="nav-link"><i data-feather="tag"></i> Supports</a>
					<ul class="navbar-menu-sub">
						<li class="nav-sub-item"><a href="<?=base_url()?>whmazadmin/kb_category/index" class="nav-sub-link"><i data-feather="layers"></i> KB Categories</a></li>
						<li class="nav-sub-item"><a href="<?=base_url()?>whmazadmin/ticket_department/index" class="nav-sub-link"><i data-feather="grid"></i> Departments</a></li>
						<li class="nav-sub-item">&nbsp;</li>
						<li class="nav-sub-item"><a href="<?=base_url()?>whmazadmin/ticket/index" class="nav-sub-link"><i data-feather="tag"></i>Tickets</a></li>
						<li class="nav-sub-item"><a href="<?=base_url()?>whmazadmin/kb/index" class="nav-sub-link"><i data-feather="sun"></i>Knowledge Bases</a></li>
						<li class="nav-sub-item"><a href="<?=base_url()?>whmazadmin/announcement/index" class="nav-sub-link"><i data-feather="mic"></i>Announcements</a></li>
					</ul>
				</li>

				<li class="nav-item with-sub">
					<a href="" class="nav-link"><i data-feather="credit-card"></i> Expenses</a>
					<ul class="navbar-menu-sub">
						<li class="nav-sub-item"><a href="<?=base_url()?>whmazadmin/expense_category/index" class="nav-sub-link"><i data-feather="layers"></i> Expense Categories</a></li>
						<li class="nav-sub-item"><a href="<?=base_url()?>whmazadmin/expense_vendor/index" class="nav-sub-link"><i data-feather="archive"></i> Expense Vendors</a></li>
						<li class="nav-sub-item"><a href="<?=base_url()?>whmazadmin/expense/index" class="nav-sub-link"><i data-feather="credit-card"></i> Expenses</a></li>
					</ul>
				</li>

				<li class="nav-item with-sub">
					<a href="" class="nav-link"><i data-feather="settings"></i> Settings</a>
					<ul class="navbar-menu-sub">
						<li class="nav-sub-item"><a href="<?=base_url()?>whmazadmin/general_setting/manage" class="nav-sub-link"><i data-feather="settings"></i>General Settings</a></li>
						<li class="nav-sub-item"><a href="<?=base_url()?>whmazadmin/server/index" class="nav-sub-link"><i data-feather="server"></i>Servers</a></li>
						<li class="nav-sub-item"><a href="<?=base_url()?>whmazadmin/currency/index" class="nav-sub-link"><i data-feather="dollar-sign"></i>Currencies</a></li>
						<li class="nav-sub-item"><a href="<?=base_url()?>whmazadmin/service_category/index" class="nav-sub-link"><i data-feather="layers"></i>Service Categories</a></li>
						<li class="nav-sub-item"><a href="<?=base_url()?>whmazadmin/service_group/index" class="nav-sub-link"><i data-feather="aperture"></i>Service Groups</a></li>
						<li class="nav-sub-item"><a href="<?=base_url()?>whmazadmin/service_module/index" class="nav-sub-link"><i data-feather="aperture"></i>Service Modules</a></li>
						<li class="nav-sub-item">&nbsp;</li>
						<li class="nav-sub-item"><a href="<?=base_url()?>whmazadmin/service_product/index" class="nav-sub-link"><i data-feather="hard-drive"></i>Hosting Management</a></li>
						<li class="nav-sub-item"><a href="<?=base_url()?>whmazadmin/package/index" class="nav-sub-link"><i data-feather="sliders"></i>Hosting Pricing</a></li>
						<li class="nav-sub-item"><a href="<?=base_url()?>whmazadmin/domain_register/index" class="nav-sub-link"><i data-feather="globe"></i>Domain Register</a></li>
						<li class="nav-sub-item"><a href="<?=base_url()?>whmazadmin/domain_pricing/index" class="nav-sub-link"><i data-feather="sliders"></i>Domain Pricing</a></li>
						<li class="nav-sub-item">&nbsp;</li>

						 <li class="nav-sub-item"><a href="<?=base_url()?>whmazadmin/email_template/index" class="nav-sub-link"><i data-feather="mail"></i> Email Template</a></li>
						<!-- <li class="nav-sub-item"><a href="<?=base_url()?>whmazadmin/contactus" class="nav-sub-link"><i data-feather="map-pin"></i> Contact us</a></li> -->
					</ul>
				</li>

			<?php } else{ $menus = getMenuItems();?>
				<li class="nav-item"><a href="<?=base_url()?>whmazadmin/dashboard/index" class="nav-link"><i data-feather="home"></i> Home</a></li>
			<?php }?>

		</ul>
	</div><!-- navbar-menu-wrapper -->
	<div class="navbar-right">

		<?php if( isAdminLoggedIn() ){ $admin = getAdminData();?>
			<div class="dropdown dropdown-profile">
				<a href="" class="dropdown-link" data-bs-toggle="dropdown" data-display="static">
					<div class="avatar avatar-sm"><img src="<?=base_url()?>/resources/assets/img/default.jpg" class="rounded-circle" alt=""></div>
				</a><!-- dropdown-link -->
				<div class="dropdown-menu dropdown-menu-end tx-13">
					<div class="avatar avatar-lg mg-b-15"><img src="<?=base_url()?>/resources/assets/img/default.jpg" class="rounded-circle" alt=""></div>
					<h6 class="tx-semibold mg-b-5"><?=$admin['first_name'].' '.$admin['last_name']?></h6>
					<div class="dropdown-divider"></div>
					<a href="<?=base_url()?>whmazadmin/dashboard/changePassword" class="dropdown-item"><i data-feather="key"></i>Change Password</a>
					<a href="<?=base_url()?>whmazadmin/authenticate/logout" class="dropdown-item"><i data-feather="log-out"></i>Sign Out</a>
				</div><!-- dropdown-menu -->
			</div><!-- dropdown -->
		<?php } else {?>
			<div class="dropdown dropdown-profile">
				<a href="" class="dropdown-link" data-bs-toggle="dropdown" data-display="static">
					<div class="avatar avatar-sm"><img src="<?=base_url()?>/resources/assets/img/default.jpg" class="rounded-circle" alt=""></div>
				</a><!-- dropdown-link -->
				<div class="dropdown-menu dropdown-menu-end tx-13">
					<div class="avatar avatar-lg mg-b-15"><img src="<?=base_url()?>/resources/assets/img/default.jpg" class="rounded-circle" alt=""></div>
					<h6 class="tx-semibold mg-b-5">WHMAZ</h6>
					<div class="dropdown-divider"></div>
					<a href="<?=base_url()?>whmazadmin/authenticate/login" class="dropdown-item"><i data-feather="log-in"></i>Sign In</a>
				</div><!-- dropdown-menu -->
			</div><!-- dropdown -->
		<?php }?>

	</div><!-- navbar-right -->

</header><!-- navbar -->
