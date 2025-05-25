@include('admin.partials.link')
<title>Faculty</title>

@include('admin.partials.side-bar')

<!-- ======== main-wrapper start =========== -->
<main class="main-wrapper">
    @include('admin.partials.header')
    <!-- ========== section start ========== -->
    <section class="section">
        <div class="container-fluid">
            <!-- ========== title-wrapper start ========== -->
            <div class="title-wrapper pt-30">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="title">
                            <h2>All Faculty Lists</h2>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="breadcrumb-wrapper">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('admin.user-management.users') }}">User Management</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">
                                        Faculty
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
                <!-- end row -->
            </div>
            <!-- ========== title-wrapper end ========== -->

            <div class="tables-wrapper">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card-style mb-30">
                            <!-- Filter Section -->
                            <div class="row mb-3 align-items-center">
                                <div class="col-md-2">
                                    <select class="form-select" id="departmentFilter" onchange="filterTable()">
                                        <option value="">Department</option>
                                        @foreach ($departments as $department)
                                            @php
                                                $count = $departmentCounts[$department->dept_name] ?? 0;
                                            @endphp
                                            <option value="{{ $department->dept_name }}">
                                                {{ $department->dept_name }} ({{ $count }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" id="employmentStatusFilter" onchange="filterTable()">
                                        <option value="">Employment Status</option>
                                        <option value="Full-Time">Full-Time ({{ $employmentStatusCounts['Full-Time'] ?? 0 }})</option>
                                        <option value="Part-Time">Part-Time ({{ $employmentStatusCounts['Part-Time'] ?? 0 }})</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" id="accountStatusFilter" onchange="filterTable()">
                                        <option value="">Account Status</option>
                                        <option value="Active">Active ({{ $statusCounts['Active'] ?? 0 }})</option>
                                        <option value="Deactivated">Deactivated ({{ $statusCounts['Deactivated'] ?? 0 }})</option>
                                    </select>
                                </div>
                            </div>
                            <!-- End Filter Section -->

                            <!-- Table Section -->
                            <div class="table-wrapper table-responsive">
                                <table class="table" id="userTable">
                                    <thead>
                                        <tr>
                                            <th>
                                                <h6>ID Number</h6>
                                            </th>
                                            <th>
                                                <h6>First Name</h6>
                                            </th>
                                            <th>
                                                <h6>Last Name</h6>
                                            </th>
                                            <th>
                                                <h6>Account Status</h6>
                                            </th>
                                            <th>
                                                <h6>Action</h6>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($users as $user)
                                            <tr id="user-{{ $user->id }}"
                                                data-department="{{ $user->department ?? '' }}"
                                                data-employment-status="{{ $user->employment_status ?? '' }}"
                                                data-status="{{ $user->status ?? '' }}">
                                                <td class="min-width">
                                                    <div class="lead">
                                                        <p>
                                                            {{ $user->student_number ?? ($user->employee_number ?? 'No ID Available') }}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td class="min-width">
                                                    <p><a href="#0">{{ $user->first_name }}</a></p>
                                                </td>
                                                <td class="min-width">
                                                    <p>{{ $user->last_name }}</p>
                                                </td>
                                                <td class="min-width">
                                                    <p class="status-text">{{ $user->status }}</p>
                                                </td>
                                                <td>
                                                    <button class="main-button secondary-btn btn-hover mb-1"
                                                        onclick="window.location='{{ route('admin.user-management.view-faculty', ['user' => $user->id]) }}'">
                                                        View
                                                    </button>
                                                    <span class="toggle-btn-container">
                                                        @if ($user->status === 'Active')
                                                            <button class="main-button danger-btn btn-hover mb-1"
                                                                onclick="toggleAccountStatus({{ $user->id }}, 'deactivate')">
                                                                Deactivate
                                                            </button>
                                                        @elseif ($user->status === 'Deactivated')
                                                            <button class="main-button warning-btn btn-hover mb-1"
                                                                onclick="toggleAccountStatus({{ $user->id }}, 'reactivate')">
                                                                Reactivate
                                                            </button>
                                                        @endif
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <!-- End Table Section -->
                        </div>
                        <!-- end card -->
                    </div>
                    <!-- end col -->
                </div>
                <!-- end row -->
            </div>
            <!-- end tables-wrapper -->
        </div>
        <!-- end container -->
    </section>
    <!-- ========== section end ========== -->
</main>
<!-- ======== main-wrapper end =========== -->
<script src="../../assets/admin/js/faculty.js"></script>
@include('admin.partials.footer')

</body>

</html>
