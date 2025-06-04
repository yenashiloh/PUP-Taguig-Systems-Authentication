<!-- ======== sidebar-nav start =========== -->
<aside class="sidebar-nav-wrapper">
    <div class="navbar-logo">
        <div class="d-flex align-items-center">
            <a href="index.html">
                <img src="../../../assets/images/PUPLogo.png" alt="logo" style="width: 50px; height: auto;" />
            </a>
            <h6 class="ms-2 fw-bold mb-0" style="color:#7e0e09;">PUP-T Systems Authentication</h6>
        </div>

    </div>
    <nav class="sidebar-nav">
        <ul>
            <li class="nav-item active">
                <a href="javascript:void(0);" style="pointer-events: none; cursor: default; color:#7e0e09">
                    <span class="text fw-bold">Menu</span>
                </a>
            </li>
            <li
                class="nav-item {{ request()->routeIs('admin.dashboard') ||
                request()->routeIs('admin.total-faculty') ||
                request()->routeIs('admin.total-student') ||
                request()->routeIs('admin.dashboard.view-total-student') ||
                request()->routeIs('admin.dashboard.view-total-faculty')
                    ? 'active'
                    : '' }}">
                <a href="{{ route('admin.dashboard') }}">
                    <span class="icon">
                        <i class="fas fa-home"></i>
                    </span>
                    <span class="text">Home</span>
                </a>
            </li>
            <li class="nav-item {{ request()->routeIs('admin.audit-trail.audit-trail') ? 'active' : '' }}">
                <a href="{{ route('admin.audit-trail.audit-trail') }}">
                    <span class="icon">
                        <i class="fas fa-history"></i>
                    </span>
                    <span class="text">Audit Trail</span>
                </a>
            </li>

            <li
                class="nav-item {{ request()->routeIs('admin.user-management.users') ||
                request()->routeIs('admin.user-management.faculty') ||
                request()->routeIs('admin.user-management.student') ||
                request()->routeIs('admin.user-management.view-faculty') ||
                request()->routeIs('admin.user-management.view-student')
                    ? 'active'
                    : '' }}">
                <a href="{{ route('admin.user-management.users') }}">
                    <span class="icon">
                        <i class="fas fa-users"></i>
                    </span>
                    <span class="text">User Management</span>
                </a>
            </li>
            <span class="divider">
                <hr />
            </span>
            <li class="nav-item active">
                <a href="javascript:void(0);" style="pointer-events: none; cursor: default; color:#7e0e09">
                    <span class="text fw-bold">Settings</span>
                </a>
            </li>
            <li class="nav-item {{ request()->routeIs('admin.settings.department') ? 'active' : '' }}">
                <a href="{{ route('admin.settings.department') }}">
                    <span class="icon">
                        <i class="fas fa-book"></i>
                    </span>
                    <span class="text">Department</span>
                </a>
            </li>
            <li class="nav-item {{ request()->routeIs('admin.settings.course') ? 'active' : '' }}">
                <a href="{{ route('admin.settings.course') }}">
                    <span class="icon">
                        <i class="fas fa-book"></i>
                    </span>
                    <span class="text">Course</span>
                </a>
            </li>
            <li class="nav-item {{ request()->routeIs('admin.settings.user-validation') ? 'active' : '' }}">
                <a href="{{ route('admin.settings.user-validation') }}">
                    <span class="icon">
                        <i class="fas fa-user-check"></i>
                    </span>
                    <span class="text">User Validation</span>
                </a>
            </li>
            <li class="nav-item {{ request()->routeIs('admin.api-keys.*') ? 'active' : '' }}">
                <a href="{{ route('admin.api-keys.index') }}">
                    <span class="icon">
                        <i class="fas fa-key"></i>
                    </span>
                    <span class="text">API Keys</span>
                </a>
            </li>
            <span class="divider">
                <hr />
            </span>
            <li class="nav-item">
                <a href="#" onclick="logoutConfirmation()">
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
