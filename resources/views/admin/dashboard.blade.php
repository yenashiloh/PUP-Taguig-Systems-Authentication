<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" href="../../assets/images/PUPLogo.png" type="image/x-icon" />
    <title>Dashboard</title>

    <!-- ========== All CSS files linkup ========= -->
    <link rel="stylesheet" href="../../assets/admin/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../../assets/admin/css/lineicons.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="../../assets/admin/css/materialdesignicons.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="../../assets/admin/css/fullcalendar.css" />
    <link rel="stylesheet" href="../../assets/admin/css/fullcalendar.css" />
    <link rel="stylesheet" href="../../assets/admin/css/main.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

  </head>
  <body>
    <!-- ======== Preloader =========== -->
    <div id="preloader">
      <div class="spinner"></div>
    </div>
    <!-- ======== Preloader =========== -->

    <!-- ======== sidebar-nav start =========== -->
    <aside class="sidebar-nav-wrapper">
      <div class="navbar-logo">
        <div class="d-flex align-items-center">
            <a href="index.html">
              <img src="../../assets/images/PUPLogo.png" alt="logo" style="width: 50px; height: auto;"/>
            </a>
            <h6 class="ms-2 fw-bold mb-0" style="color:#7e0e09;">PUP-T Systems Authentication</h6>
          </div>
          
      </div>
      <nav class="sidebar-nav">
        <ul>
            <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}">
                    <span class="icon">
                        <i class="fas fa-home"></i>
                    </span>
                    <span class="text">Home</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="" >
                  <span class="icon">
                    <i class="fas fa-history"></i>
                  </span>
                  <span class="text">Audit Trail</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="" >
                  <span class="icon">
                    <i class="fas fa-users"></i>
                  </span>
                  <span class="text">user Management</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="" >
                  <span class="icon">
                    <i class="fas fa-user-shield"></i>
                  </span>
                  <span class="text">Access Control</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="" >
                  <span class="icon">
                    <i class="fas fa-cog"></i>
                  </span>
                  <span class="text">Settings</span>
                </a>
            </li>
            <span class="divider"><hr /></span>
            <li class="nav-item">
                <a href="" >
                  <span class="icon">
                    <i class="fas fa-sign-out-alt"></i>
                  </span>
                  <span class="text">Sign Out</span>
                </a>
            </li>
        </ul>
      </nav>
    </aside>
    <div class="overlay"></div>
    <!-- ======== sidebar-nav end =========== -->

    <!-- ======== main-wrapper start =========== -->
    <main class="main-wrapper">
      <!-- ========== header start ========== -->
      <header class="header">
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-5 col-md-5 col-6">
              <div class="header-left d-flex align-items-center">
                <div class="menu-toggle-btn mr-15">
                  <button id="menu-toggle" class="main-btn primary-btn btn-hover">
                    <i class="lni lni-chevron-left me-2"></i> Menu
                  </button>
                </div>
              </div>
            </div>
            <div class="col-lg-7 col-md-7 col-6">
              <div class="header-right">
                <!-- profile start -->
                <div class="profile-box ml-15">
                  <button class="dropdown-toggle bg-transparent border-0" type="button" id="profile"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="profile-info">
                      <div class="info">
                        <div class="image">
                          <img src="../../assets/admin/images/profile-picture.png" alt="" />
                        </div>
                        <div>
                          <h6 class="fw-500">Ed Mingo</h6>
                          <p>Admin</p>
                        </div>
                      </div>
                    </div>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profile">
                    <li>
                      <div class="author-info flex items-center !p-1">
                        <div class="image">
                          <img src="../../assets/admin/images/profile-picture.png" alt="image">
                        </div>
                        <div class="content">
                          <h4 class="text-sm">Ed Mingo</h4>
                          <a class="text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white text-xs" href="#">Email@gmail.com</a>
                        </div>
                      </div>
                    </li>
                    <li class="divider"></li>
                    <li>
                      <a href="#0">
                        <i class="lni lni-user"></i> View Profile
                      </a>
                    </li>
                    <li>
                      <a href="#0"> <i class="lni lni-cog"></i> Settings </a>
                    </li>
                    <li class="divider"></li>
                    <li>
                      <a href="#0"> <i class="lni lni-exit"></i> Sign Out </a>
                    </li>
                  </ul>
                </div>
                <!-- profile end -->
              </div>
            </div>
          </div>
        </div>
      </header>
      <!-- ========== header end ========== -->

      <!-- ========== section start ========== -->
      <section class="section">
        <div class="container-fluid">
          <!-- ========== title-wrapper start ========== -->
          <div class="title-wrapper pt-30">
            <div class="row align-items-center">
              <div class="col-md-6">
                <div class="title">
                  <h3> Dashboard</h3>
                </div>
              </div>
              <!-- end col -->
              <div class="col-md-6">
                <div class="breadcrumb-wrapper">
                 
                </div>
              </div>
              <!-- end col -->
            </div>
            <!-- end row -->
          </div>
          <!-- ========== title-wrapper end ========== -->
          <div class="row">
            <div class="col-xl-6 col-lg-4 col-sm-6">
              <div class="icon-card mb-30">
                <div class="icon purple">
                    <i class="lni lni-users"></i>

                </div>
                <div class="content">
                  <h6 class="mb-10">Total of Faculty Users</h6>
                  <h3 class="text-bold mb-10">34567</h3>
                </div>
              </div>
              <!-- End Icon Cart -->
            </div>
            <!-- End Col -->
            <div class="col-xl-6 col-lg-4 col-sm-6">
              <div class="icon-card mb-30">
                <div class="icon success">
                    <i class="lni lni-user"></i>
                </div>
                <div class="content">
                  <h6 class="mb-10">Total of Student Users</h6>
                  <h3 class="text-bold mb-10">567</h3>
                </div>
              </div>
              <!-- End Icon Cart -->
            </div>
            <!-- End Col -->
         
            <!-- End Col -->
          </div>
          <!-- End Row -->
        </div>
        <!-- end container -->
      </section>
      <!-- ========== section end ========== -->
      <!-- ========== footer end =========== -->
    </main>
    <!-- ======== main-wrapper end =========== -->

    <!-- ========= All Javascript files linkup ======== -->
    <script src="../../assets/admin/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/admin/js/Chart.min.js"></script>
    <script src="../../assets/admin/js/dynamic-pie-chart.js"></script>
    <script src="../../assets/admin/js/moment.min.js"></script>
    <script src="../../assets/admin/js/fullcalendar.js"></script>
    <script src="../../assets/admin/js/jvectormap.min.js"></script>
    <script src="../../assets/admin/js/world-merc.js"></script>
    <script src="../../assets/admin/js/polyfill.js"></script>
    <script src="../../assets/admin/js/main.js"></script>

    
  </body>
</html>
