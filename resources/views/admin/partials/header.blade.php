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
                                         <img src="../../../assets/admin/images/profile-picture.png" alt="" />
                                     </div>
                                     <div>
                                         <h6 class="fw-500">{{ $admin->first_name }} </h6>
                                         <p>Admin</p>
                                     </div>
                                 </div>
                             </div>
                             <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profile"
                                 style="min-width: auto; width: auto;">
                                 <li>
                                     <div class="author-info flex items-center !p-1">
                                         <div class="image">
                                             <img src="../../../assets/admin/images/profile-picture.png" alt="image">
                                         </div>
                                         <div class="content">
                                             <h4 class="text-sm">{{ $admin->first_name }} {{ $admin->last_name }}</h4>
                                             <a class="text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white text-xs"
                                                 href="{{ route('admin.profile') }}">{{ $admin->email }}</a>
                                         </div>
                                     </div>
                                 </li>
                                 <li class="divider"></li>
                                 <li>
                                     <a href="{{ route('admin.profile') }}">
                                         <i class="lni lni-user"></i> View Profile
                                     </a>
                                 <li>
                                     <a href="#0"> <i class="lni lni-cog"></i> Settings </a>
                                 </li>
                                 <li class="divider"></li>
                                 <li>
                                     <a href="#" onclick="logoutConfirmation()">
                                         <i class="lni lni-exit"></i> Sign Out
                                     </a>
                                 </li>
                             </ul>
                     </div>
                     <!-- profile end -->
                 </div>
             </div>
         </div>
     </div>

     <!-- Logout Confirmation Modal -->
     <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
         @csrf
     </form>
     <!-- Logout Confirmation Modal end -->
 </header>
 <!-- ========== header end ========== -->
