<!DOCTYPE html>
<html lang="en">
  <head>

    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Twitter -->
    <meta name="twitter:site" content="@whmaz">
    <meta name="twitter:creator" content="@whmaz">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="WHMAZ">
    <meta name="twitter:description" content="WHMAZ - Web Host Manager A to Z solutions. Lightweight Domain Hosting Management System.">
    <meta name="twitter:image" content="<?=base_url()?>resources/assets/img/dashforge-social.png">

    <!-- Facebook -->
    <meta property="og:url" content="https://whmaz.com/">
    <meta property="og:title" content="WHMAZ">
    <meta property="og:description" content="WHMAZ - Web Host Manager A to Z solutions.  Lightweight Domain Hosting Management System.">

    <meta property="og:image" content="<?=base_url()?>resources/assets/img/dashforge-social.png">
    <meta property="og:image:secure_url" content="<?=base_url()?>resources/assets/img/dashforge-social.png">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="600">

    <!-- Meta -->
    <meta name="description" content="WHMAZ - Web Host Manager A to Z solutions.  Lightweight Domain Hosting Management System.">
    <meta name="author" content="Tong Bari">

    <!-- CSRF Token for AJAX requests -->
    <?=csrf_meta()?>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="<?=base_url()?>resources/assets/img/favicon.png">

    <title>WHMAZ - Web Host Manager A to Z solutions</title>

	  <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900" rel="stylesheet" type="text/css">
	  <link href="https://fonts.googleapis.com/css?family=Roboto+Mono" rel="stylesheet" type="text/css">
	  <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700,900" rel="stylesheet" type="text/css">
	  <link href="https://fonts.googleapis.com/css?family=Rubik:300,400" rel="stylesheet" type="text/css">

    <!-- vendor css -->
	  <link href="<?=base_url()?>resources/lib/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
	  <link href="<?=base_url()?>resources/lib/ionicons/css/ionicons.min.css" rel="stylesheet">
	  <link href="<?=base_url()?>resources/lib/typicons.font/typicons.css" rel="stylesheet">

	  <!-- vendor css -->
	  <link href="<?=base_url()?>resources/lib/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
	  <link href="<?=base_url()?>resources/lib/ionicons/css/ionicons.min.css" rel="stylesheet">
	  <link href="<?=base_url()?>resources/lib/remixicon/fonts/remixicon.css" rel="stylesheet">

	  <link href="<?=base_url()?>resources/lib/prismjs/themes/prism-vs.css" rel="stylesheet">
	  <link href="<?=base_url()?>resources/lib/quill/quill.core.css" rel="stylesheet">
	  <link href="<?=base_url()?>resources/lib/quill/quill.snow.css" rel="stylesheet">
	  <link href="<?=base_url()?>resources/lib/quill/quill.bubble.css" rel="stylesheet">

    <!-- DashForge CSS -->
    <link rel="stylesheet" href="<?=base_url()?>resources/assets/css/dashforge.css">
    <link rel="stylesheet" href="<?=base_url()?>resources/assets/css/dashforge.profile.css">
    <link rel="stylesheet" href="<?=base_url()?>resources/assets/css/dashforge.dashboard.css">

    <link href="<?=base_url()?>resources/lib/datatables.net-dt/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="<?=base_url()?>resources/lib/datatables.net-responsive-dt/css/responsive.dataTables.min.css" rel="stylesheet">
    <link href="<?=base_url()?>resources/lib/select2/css/select2.min.css" rel="stylesheet">

    <link rel="stylesheet" href="<?=base_url()?>resources/angular/angular-material.min.css">
    <link rel="stylesheet" href="<?=base_url()?>resources/angular/ngDialog.css">
    <link rel="stylesheet" href="<?=base_url()?>resources/angular/ngDialog-theme-default.css">
    <link rel="stylesheet" href="<?=base_url()?>resources/angular/ngToast.css">
    <link rel="stylesheet" href="<?=base_url()?>resources/angular/ngToast-animations.css">
	<link rel="stylesheet" href="<?=base_url()?>resources/assets/css/jquery.toast.css">
	<link rel="stylesheet" href="<?=base_url()?>resources/lib/sweetalert2/sweetalert2.min.css">
	<link rel="stylesheet" href="<?=base_url()?>resources/assets/css/custom.css">
    <script type="text/javascript">
		var BASE_URL="<?=base_url()?>";
		var isLoadingShown = false;
		function escapeXSS(str){

			if(!str){
				return "";}
			str = str+"";
			return str.replace(/\</g, '&lt;')
				.replace(/\>/g, '&gt;')
				.replace(/\"/g, '&quot;')
				.replace(/\'/g, '&#x27;')
				.replace(/\//g, '&#x2F;')
				//.replace(/\&/g, '&amp;')
				;

		}

		// SECURITY: Clickjacking protection - Frame busting script
		// This provides defense-in-depth in addition to X-Frame-Options header
		(function() {
			if (self !== top) {
				// Page is in an iframe - attempt to break out
				try {
					top.location.href = self.location.href;
				} catch (e) {
					// Cross-origin iframe - can't redirect, hide content instead
					document.documentElement.style.display = 'none';
					document.body.innerHTML = '<h1 style="color:red;text-align:center;margin-top:50px;">This page cannot be displayed in a frame for security reasons.</h1>';
				}
			}
		})();
	</script>
  </head>
  <body class="page-profile">

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

          <?php if( isLoggedin() ){?>
          
          <li class="nav-item"><a href="<?=base_url()?>clientarea" class="nav-link"><i data-feather="home"></i> Home</a></li>
          
          <li class="nav-item with-sub">
            <a href="" class="nav-link"><i data-feather="pie-chart"></i> Services</a>
            <ul class="navbar-menu-sub">
              <li class="nav-sub-item"><a href="<?=base_url()?>clientarea/services" class="nav-sub-link"><i data-feather="list"></i>My services</a></li>
              <li class="nav-sub-item"><a href="<?=base_url()?>cart/services/0/0" class="nav-sub-link"><i data-feather="file-plus"></i>New service</a></li>
            </ul>
          </li>

          <li class="nav-item with-sub">
            <a href="" class="nav-link"><i data-feather="pie-chart"></i> Domains</a>
            <ul class="navbar-menu-sub">
              <li class="nav-sub-item"><a href="<?=base_url()?>clientarea/domains" class="nav-sub-link"><i data-feather="list"></i>My domains</a></li>
              <li class="nav-sub-item"><a href="<?=base_url()?>cart/domain/register" class="nav-sub-link"><i data-feather="globe"></i>Register new domain</a></li>
              <li class="nav-sub-item"><a href="<?=base_url()?>cart/domain/transfer" class="nav-sub-link"><i data-feather="repeat"></i>Transfer domain</a></li>
            </ul>
          </li>

          <li class="nav-item with-sub">
            <a href="" class="nav-link"><i data-feather="pie-chart"></i> Billing</a>
            <ul class="navbar-menu-sub">
              <li class="nav-sub-item"><a href="<?=base_url()?>billing/invoices" class="nav-sub-link"><i data-feather="list"></i>My invoices</a></li>
            </ul>
          </li>

          <li class="nav-item with-sub">
            <a href="" class="nav-link"><i data-feather="pie-chart"></i> Supports</a>
            <ul class="navbar-menu-sub">
              <li class="nav-sub-item"><a href="<?=base_url()?>tickets/index" class="nav-sub-link"><i data-feather="list"></i>My Tickets</a></li>
              <li class="nav-sub-item"><a href="<?=base_url()?>tickets/newticket" class="nav-sub-link"><i data-feather="tag"></i>New Ticket</a></li>
              <li class="nav-sub-item"><a href="<?=base_url()?>supports/KB" class="nav-sub-link"><i data-feather="sun"></i>Knowledge Bases</a></li>
              <li class="nav-sub-item"><a href="<?=base_url()?>supports/announcements" class="nav-sub-link"><i data-feather="mic"></i>Announcement</a></li>
            </ul>
          </li>

          <li class="nav-item"><a href="<?=base_url()?>contactus" class="nav-link"><i data-feather="archive"></i> Contact us</a></li>
          
          <?php } else{ $menus = getMenuItems();?>
          <li class="nav-item"><a href="<?=base_url()?>auth/login" class="nav-link"><i data-feather="home"></i> Home</a></li>
		  <li class="nav-item with-sub">
			  <a href="" class="nav-link"><i data-feather="globe"></i> Domain</a>
			  <ul class="navbar-menu-sub">
				  <li class="nav-sub-item"><a href="<?=base_url()?>cart/domain/register" class="nav-sub-link"><i data-feather="globe"></i> Register domain</a></li>
				  <li class="nav-sub-item"><a href="<?=base_url()?>cart/domain/register" class="nav-sub-link"><i data-feather="repeat"></i> Transfer domain to us</a></li>
			  </ul>
		  </li>
		  <li class="nav-item with-sub">
            <a href="" class="nav-link"><i data-feather="server"></i> Hosting</a>
            <ul class="navbar-menu-sub">
				<?php foreach ($menus as $row){?>
				<li class="nav-sub-item"><a href="<?=base_url()?>cart/services/<?=$row['id']?>" class="nav-sub-link"><i data-feather="shopping-cart"></i> <?=$row['group_name']?></a></li>
				<?php }?>
            </ul>
          </li>
          <li class="nav-item"><a href="<?=base_url()?>supports/announcements" class="nav-link"><i data-feather="box"></i> Announcement</a></li>
          <li class="nav-item"><a href="<?=base_url()?>supports/KB" class="nav-link"><i data-feather="archive"></i> Knowledgebase</a></li>
          <li class="nav-item"><a href="<?=base_url()?>supports/contactus" class="nav-link"><i data-feather="archive"></i> Contact us</a></li>
          <?php }?>

        </ul>
      </div><!-- navbar-menu-wrapper -->
      <div class="navbar-right">

		  <div class="dropdown dropdown-profile">
			  <a href="<?=base_url()?>cart/view" title="View cart" class="dropdown-link">
				  <div class="bg-light cart-counting avatar avatar-sm position-relative">
					  <img src="<?=base_url()?>/resources/assets/img/cart.svg" class="rounded-circle" alt="">
					  <span class="cart-count-badge" id="cart-count-badge" style="display:none;"></span>
				  </div>
			  </a>
		  </div>
		  <?php if( isLoggedin() ){ $user = getUserData();?>
			  <div class="dropdown dropdown-profile">
				  <a href="" class="dropdown-link" data-bs-toggle="dropdown" data-display="static">
					  <div class="avatar avatar-sm"><img src="<?=base_url()?>/resources/assets/img/default.jpg" class="rounded-circle" alt=""></div>
				  </a><!-- dropdown-link -->
				  <div class="dropdown-menu dropdown-menu-end tx-13">
					  <div class="avatar avatar-lg mg-b-15"><img src="<?=base_url()?>/resources/assets/img/default.jpg" class="rounded-circle" alt=""></div>
					  <h6 class="tx-semibold mg-b-5"><?=$user['first_name'].' '.$user['last_name']?></h6>
					  <a href="<?=base_url()?>clientarea/changePassword" class="dropdown-item"><i data-feather="key"></i>Change Password</a>
					  <div class="dropdown-divider"></div>
					  <a href="<?=base_url()?>auth/logout" class="dropdown-item"><i data-feather="log-out"></i>Sign Out</a>
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
					  <a href="<?=base_url()?>auth/login" class="dropdown-item"><i data-feather="log-in"></i>Sign In</a>
					  <a href="<?=base_url()?>auth/register" class="dropdown-item"><i data-feather="user-plus"></i>Sign Up</a>
				  </div><!-- dropdown-menu -->
			  </div><!-- dropdown -->
		  <?php }?>

      </div><!-- navbar-right -->

    </header><!-- navbar -->
