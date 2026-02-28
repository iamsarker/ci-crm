<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
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
    <meta name="twitter:image" content="<?=base_url()?>resources/assets/img/whmaz-social.png">

    <!-- Facebook -->
    <meta property="og:url" content="https://whmaz.com/">
    <meta property="og:title" content="WHMAZ">
    <meta property="og:description" content="WHMAZ - Web Host Manager A to Z solutions. Lightweight Domain Hosting Management System.">

    <meta property="og:image" content="<?=base_url()?>resources/assets/img/whmaz-social.png">
    <meta property="og:image:secure_url" content="<?=base_url()?>resources/assets/img/whmaz-social.png">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="600">

    <!-- Meta -->
    <meta name="description" content="WHMAZ - Web Host Manager A to Z solutions. Lightweight Domain Hosting Management System.">
    <meta name="author" content="WHMAZ">

    <!-- CSRF Token for AJAX requests -->
    <?=csrf_meta()?>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="<?=base_url()?>resources/assets/img/favicon.png">

    <title>WHMAZ - Web Host Manager A to Z solutions</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Roboto+Mono" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700,900" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Rubik:300,400" rel="stylesheet" type="text/css">

    <!-- Bootstrap Icons (required by AdminLTE 4) -->
    <link href="<?=base_url()?>resources/lib/bootstrap-icons-1.13.1/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="<?=base_url()?>resources/lib/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">

    <!-- Vendor CSS -->
    <link href="<?=base_url()?>resources/lib/prismjs/themes/prism-vs.css" rel="stylesheet">
    <link href="<?=base_url()?>resources/lib/quill/quill.core.css" rel="stylesheet">
    <link href="<?=base_url()?>resources/lib/quill/quill.snow.css" rel="stylesheet">
    <link href="<?=base_url()?>resources/lib/quill/quill.bubble.css" rel="stylesheet">

    <!-- AdminLTE 4 CSS -->
    <link rel="stylesheet" href="<?=base_url()?>resources/adminlte4/dist/css/adminlte.min.css">

    <!-- AdminLTE Compatibility Layer -->
    <link rel="stylesheet" href="<?=base_url()?>resources/assets/css/adminlte-compat.css">

    <!-- DataTables & Select2 -->
    <link href="<?=base_url()?>resources/lib/datatables.net-dt/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="<?=base_url()?>resources/lib/datatables.net-responsive-dt/css/responsive.dataTables.min.css" rel="stylesheet">
    <link href="<?=base_url()?>resources/lib/select2/css/select2.min.css" rel="stylesheet">

    <!-- Angular Material & Dialogs -->
    <link rel="stylesheet" href="<?=base_url()?>resources/angular/angular-material.min.css">
    <link rel="stylesheet" href="<?=base_url()?>resources/angular/ngDialog.css">
    <link rel="stylesheet" href="<?=base_url()?>resources/angular/ngDialog-theme-default.css">
    <link rel="stylesheet" href="<?=base_url()?>resources/angular/ngToast.css">
    <link rel="stylesheet" href="<?=base_url()?>resources/angular/ngToast-animations.css">

    <!-- Toast & SweetAlert -->
    <link rel="stylesheet" href="<?=base_url()?>resources/assets/css/jquery.toast.css">
    <link rel="stylesheet" href="<?=base_url()?>resources/lib/sweetalert2/sweetalert2.min.css">

    <!-- Custom Client CSS (loaded after AdminLTE to override styles) -->
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
  <body class="layout-top-nav">
    <div class="app-wrapper">

      <!-- Top Navigation Bar -->
      <nav class="app-header navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
          <!-- Brand -->
          <a href="<?=base_url()?>clientarea" class="navbar-brand">
            <span class="brand-text fw-bold text-primary">WHM<span class="text-secondary">A-Z</span></span>
          </a>

          <!-- Mobile Toggle Button -->
          <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#clientNavbar" aria-controls="clientNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
          </button>

          <!-- Navigation Menu -->
          <div class="collapse navbar-collapse" id="clientNavbar">
            <?php if( isLoggedin() ){?>
            <ul class="navbar-nav me-auto">
              <!-- Home -->
              <li class="nav-item">
                <a href="<?=base_url()?>clientarea" class="nav-link">
                  <i class="fas fa-home me-1"></i> Home
                </a>
              </li>

              <!-- Services Dropdown -->
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fas fa-server me-1"></i> Services
                </a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="<?=base_url()?>clientarea/services"><i class="fas fa-list me-2"></i>My Services</a></li>
                  <li><a class="dropdown-item" href="<?=base_url()?>cart/services/0/0"><i class="fas fa-plus-circle me-2"></i>New Service</a></li>
                </ul>
              </li>

              <!-- Domains Dropdown -->
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fas fa-globe me-1"></i> Domains
                </a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="<?=base_url()?>clientarea/domains"><i class="fas fa-list me-2"></i>My Domains</a></li>
                  <li><a class="dropdown-item" href="<?=base_url()?>cart/domain/register"><i class="fas fa-globe me-2"></i>Register New Domain</a></li>
                  <li><a class="dropdown-item" href="<?=base_url()?>cart/domain/transfer"><i class="fas fa-exchange-alt me-2"></i>Transfer Domain</a></li>
                </ul>
              </li>

              <!-- Billing Dropdown -->
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fas fa-file-invoice-dollar me-1"></i> Billing
                </a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="<?=base_url()?>billing/invoices"><i class="fas fa-file-alt me-2"></i>My Invoices</a></li>
                </ul>
              </li>

              <!-- Supports Dropdown -->
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fas fa-headset me-1"></i> Supports
                </a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="<?=base_url()?>tickets/index"><i class="fas fa-ticket-alt me-2"></i>My Tickets</a></li>
                  <li><a class="dropdown-item" href="<?=base_url()?>tickets/newticket"><i class="fas fa-plus-circle me-2"></i>New Ticket</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item" href="<?=base_url()?>supports/KB"><i class="fas fa-book me-2"></i>Knowledge Base</a></li>
                  <li><a class="dropdown-item" href="<?=base_url()?>supports/announcements"><i class="fas fa-bullhorn me-2"></i>Announcements</a></li>
                </ul>
              </li>

              <!-- Contact Us -->
              <li class="nav-item">
                <a href="<?=base_url()?>supports/contactus" class="nav-link">
                  <i class="fas fa-envelope me-1"></i> Contact Us
                </a>
              </li>
            </ul>

            <?php } else { $menus = getMenuItems();?>
            <ul class="navbar-nav me-auto">
              <!-- Home -->
              <li class="nav-item">
                <a href="<?=base_url()?>auth/login" class="nav-link">
                  <i class="fas fa-home me-1"></i> Home
                </a>
              </li>

              <!-- Domain Dropdown -->
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fas fa-globe me-1"></i> Domain
                </a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="<?=base_url()?>cart/domain/register"><i class="fas fa-globe me-2"></i>Register Domain</a></li>
                  <li><a class="dropdown-item" href="<?=base_url()?>cart/domain/transfer"><i class="fas fa-exchange-alt me-2"></i>Transfer Domain</a></li>
                </ul>
              </li>

              <!-- Hosting Dropdown -->
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fas fa-server me-1"></i> Hosting
                </a>
                <ul class="dropdown-menu">
                  <?php foreach ($menus as $row){?>
                  <li><a class="dropdown-item" href="<?=base_url()?>cart/services/<?=$row['id']?>"><i class="fas fa-shopping-cart me-2"></i><?=htmlspecialchars($row['group_name'])?></a></li>
                  <?php }?>
                </ul>
              </li>

              <!-- Announcement -->
              <li class="nav-item">
                <a href="<?=base_url()?>supports/announcements" class="nav-link">
                  <i class="fas fa-bullhorn me-1"></i> Announcement
                </a>
              </li>

              <!-- Knowledge Base -->
              <li class="nav-item">
                <a href="<?=base_url()?>supports/KB" class="nav-link">
                  <i class="fas fa-book me-1"></i> Knowledgebase
                </a>
              </li>

              <!-- Contact Us -->
              <li class="nav-item">
                <a href="<?=base_url()?>supports/contactus" class="nav-link">
                  <i class="fas fa-envelope me-1"></i> Contact Us
                </a>
              </li>
            </ul>
            <?php }?>
          </div>

          <!-- Right Side - Cart & User Profile -->
          <ul class="navbar-nav ms-auto">
            <!-- Cart -->
            <li class="nav-item">
              <a href="<?=base_url()?>cart/view" class="nav-link position-relative" title="View cart">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-count-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cart-count-badge" style="display:none; font-size: 0.65rem;"></span>
              </a>
            </li>

            <?php if( isLoggedin() ){ $user = getUserData();?>
            <!-- User Dropdown (Logged In) -->
            <li class="nav-item dropdown dropdown-profile">
              <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="<?=base_url()?>resources/assets/img/default.jpg" class="rounded-circle me-2" alt="" style="width:32px;height:32px;object-fit:cover;">
                <span class="d-none d-lg-inline"><?=htmlspecialchars($user['first_name'])?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li class="px-3 py-2 text-center border-bottom">
                  <img src="<?=base_url()?>resources/assets/img/default.jpg" class="rounded-circle mb-2" alt="" style="width:64px;height:64px;object-fit:cover;">
                  <h6 class="mb-0"><?=htmlspecialchars($user['first_name'].' '.$user['last_name'])?></h6>
                </li>
                <li><a class="dropdown-item" href="<?=base_url()?>clientarea/changePassword"><i class="fas fa-key me-2"></i>Change Password</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?=base_url()?>auth/logout"><i class="fas fa-sign-out-alt me-2"></i>Sign Out</a></li>
              </ul>
            </li>
            <?php } else { ?>
            <!-- User Dropdown (Not Logged In) -->
            <li class="nav-item dropdown dropdown-profile">
              <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="<?=base_url()?>resources/assets/img/default.jpg" class="rounded-circle" alt="" style="width:32px;height:32px;object-fit:cover;">
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li class="px-3 py-2 text-center border-bottom">
                  <img src="<?=base_url()?>resources/assets/img/default.jpg" class="rounded-circle mb-2" alt="" style="width:64px;height:64px;object-fit:cover;">
                  <h6 class="mb-0">WHMAZ</h6>
                </li>
                <li><a class="dropdown-item" href="<?=base_url()?>auth/login"><i class="fas fa-sign-in-alt me-2"></i>Sign In</a></li>
                <li><a class="dropdown-item" href="<?=base_url()?>auth/register"><i class="fas fa-user-plus me-2"></i>Sign Up</a></li>
              </ul>
            </li>
            <?php } ?>
          </ul>
        </div>
      </nav>

      <!-- Main Content Area -->
      <main class="app-main">
        <div class="app-content">
