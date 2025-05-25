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

            <!-- Import Success Message -->
            @if (session('import_success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa fa-check-circle me-2"></i>
                    {{ session('import_success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Import Error Message -->
            @if (session('import_error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fa fa-exclamation-triangle me-2"></i>
                    {{ session('import_error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Detailed Import Errors -->
            @if (session('import_errors') && count(session('import_errors')) > 0)
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="alert-heading mb-2">
                                <i class="fa fa-exclamation-circle me-2"></i>
                                Import Issues Found ({{ count(session('import_errors')) }} rows with errors)
                            </h6>
                            <div class="import-errors-container"
                                style="max-height: 300px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 0.375rem; padding: 10px; background-color: #f8f9fa;">
                                @foreach (session('import_errors') as $error)
                                    <div class="error-item mb-2 p-2"
                                        style="border-left: 3px solid #dc3545; background-color: white;">
                                        <small class="text-danger fw-bold">{{ $error }}</small>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fa fa-info-circle me-1"></i>
                                    These rows were skipped. Please fix the errors and import again.
                                </small>
                            </div>
                        </div>
                        <button type="button" class="btn-close ms-2" data-bs-dismiss="alert"
                            aria-label="Close"></button>
                    </div>
                </div>
            @endif

            <!-- Import Summary -->
            @if (session('import_summary'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <h6 class="alert-heading mb-2">
                        <i class="fa fa-info-circle me-2"></i>
                        Import Summary
                    </h6>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Total Processed:</strong> {{ session('import_summary')['total'] ?? 0 }}
                        </div>
                        <div class="col-md-4">
                            <strong class="text-success">Successfully Imported:</strong>
                            {{ session('import_summary')['success'] ?? 0 }}
                        </div>
                        <div class="col-md-4">
                            <strong class="text-danger">Failed:</strong> {{ session('import_summary')['failed'] ?? 0 }}
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

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
                                        <option value="Full-Time">Full-Time
                                            ({{ $employmentStatusCounts['Full-Time'] ?? 0 }})</option>
                                        <option value="Part-Time">Part-Time
                                            ({{ $employmentStatusCounts['Part-Time'] ?? 0 }})</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" id="accountStatusFilter" onchange="filterTable()">
                                        <option value="">Account Status</option>
                                        <option value="Active">Active ({{ $statusCounts['Active'] ?? 0 }})</option>
                                        <option value="Deactivated">Deactivated
                                            ({{ $statusCounts['Deactivated'] ?? 0 }})</option>
                                    </select>
                                </div>
                                <div class="col-md-6 d-flex justify-content-end">
                                    <button class="main-button primary-btn me-2 btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#addFacultyModal">
                                        <i class="fas fa-plus me-1"></i> Add 
                                    </button>

                                    <button type="button"
                                        class="main-btn primary-btn-outline square-btn btn-hover me-2 btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#importModal">
                                        <i class="fas fa-upload me-1"></i> Import
                                    </button>
                                    <!-- Export Dropdown -->
                                    <div class="dropdown">
                                        <button class="main-btn primary-btn-outline square-btn btn-hover btn-sm"
                                            type="button" id="exportDropdown" data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            <i class="fas fa-file-export me-1"></i>Export
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="exportAllFaculty()">
                                                    <i class="fas fa-chalkboard-teacher me-2"></i> Export All Faculty
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#"
                                                    onclick="exportFilteredFaculty()">
                                                    <i class="fas fa-filter me-2"></i> Export Filtered Data
                                                </a>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-muted" href="#"
                                                    style="cursor: default;">
                                                    <small><i class="fas fa-info-circle me-2"></i> CSV format</small>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Bulk Actions Bar for Faculty -->
                            <div class="row mb-3" id="bulkActionsBar" style="display: none;">
                                <div class="col-12">
                                    <div class="alert alert-info d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <span id="selectedCount">0</span> faculty member(s) selected
                                        </div>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-success"
                                                onclick="bulkAction('reactivate')" id="bulkReactivateBtn"
                                                title="Reactivate Selected">
                                                <i class="fas fa-check-circle me-1"></i> Reactivate
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                onclick="bulkAction('deactivate')" id="bulkDeactivateBtn"
                                                title="Deactivate Selected">
                                                <i class="fas fa-ban me-1"></i> Deactivate
                                            </button>
                                            <button type="button" class="btn btn-sm btn-secondary"
                                                onclick="clearSelection()" title="Clear Selection">
                                                <i class="fas fa-times me-1"></i> Clear
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Faculty Table Section -->
                            <div class="table-wrapper table-responsive">
                                <table class="table" id="userTable">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="selectAll"
                                                        onchange="toggleSelectAll()" title="Select All">
                                                    <label class="form-check-label visually-hidden" for="selectAll">
                                                        Select All
                                                    </label>
                                                </div>
                                            </th>
                                            <th>
                                                <h6>Employee ID</h6>
                                            </th>
                                            <th>
                                                <h6>Last Name</h6>
                                            </th>
                                            <th>
                                                <h6>First Name</h6>
                                            </th>
                                            <th>
                                                <h6>Email</h6>
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
                                                data-status="{{ $user->status ?? '' }}"
                                                data-user-id="{{ $user->id }}"
                                                data-user-status="{{ $user->status }}">
                                                <td>
                                                    <div class="form-check">
                                                        <input class="form-check-input user-checkbox" type="checkbox"
                                                            id="user-checkbox-{{ $user->id }}"
                                                            value="{{ $user->id }}"
                                                            data-status="{{ $user->status }}"
                                                            onchange="updateSelectAll()">
                                                        <label class="form-check-label visually-hidden"
                                                            for="user-checkbox-{{ $user->id }}">
                                                            Select {{ $user->first_name }} {{ $user->last_name }}
                                                        </label>
                                                    </div>
                                                </td>
                                                <td class="min-width">
                                                    <div class="lead">
                                                        <p>
                                                            {{ $user->employee_number ?? 'No ID Available' }}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td class="min-width">
                                                    <p>{{ $user->last_name }}</p>
                                                </td>
                                                <td class="min-width">
                                                    <p><a href="#0">{{ $user->first_name }}</a></p>
                                                </td>
                                                <td class="min-width">
                                                    <p><a href="#0">{{ $user->email }}</a></p>
                                                </td>
                                                <td class="min-width">
                                                    <p>
                                                        <a"> {{ $user->status }}</a>
                                                    </p>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button class="btn btn-outline-primary btn-sm"
                                                            onclick="window.location='{{ route('admin.user-management.view-faculty', ['user' => $user->id]) }}'">
                                                            <i class="fas fa-eye me-1"></i> View
                                                        </button>
                                                        @if ($user->status === 'Active')
                                                            <button class="btn btn-outline-danger btn-sm"
                                                                onclick="toggleAccountStatus({{ $user->id }}, 'deactivate')">
                                                                <i class="fas fa-ban me-1"></i> Deactivate
                                                            </button>
                                                        @elseif ($user->status === 'Deactivated')
                                                            <button class="btn btn-outline-success btn-sm"
                                                                onclick="toggleAccountStatus({{ $user->id }}, 'reactivate')">
                                                                <i class="fas fa-check-circle me-1"></i> Reactivate
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                        @if (count($users) === 0)
                                            <tr class="no-records-row">
                                                <td colspan="7" class="text-center py-3">
                                                    <p class="text-muted mb-0">No records found.</p>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
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

    <!-- Add Faculty Modal -->
    <div class="modal fade" id="addFacultyModal" tabindex="-1" aria-labelledby="addFacultyModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addFacultyModalLabel">Add New Faculty</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addFacultyForm" novalidate>
                        @csrf
                        <!-- Alert placed at the top of the form -->
                        <div id="formAlert" class="alert d-none mb-3" role="alert"></div>

                        <div class="row">
                            <div class="col-md-6 mb-3 input-style-1">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>
                            <div class="col-md-6 mb-3 input-style-1">
                                <label for="employee_number" class="form-label">Employee Number</label>
                                <input type="text" class="form-control" id="employee_number"
                                    name="employee_number" required>
                                <div class="invalid-feedback">Please enter an employee number.</div>
                            </div>
                            <div class="col-md-4 mb-3 input-style-1">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name"
                                    required>
                                <div class="invalid-feedback">Please enter a first name.</div>
                            </div>
                            <div class="col-md-4 mb-3 input-style-1">
                                <label for="middle_name" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="middle_name" name="middle_name">
                                <div class="invalid-feedback">Please enter a middle name.</div>
                            </div>
                            <div class="col-md-4 mb-3 input-style-1">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                                <div class="invalid-feedback">Please enter a last name.</div>
                            </div>
                            <div class="col-md-6 mb-3 input-style-1">
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone_number" name="phone_number"
                                    required>
                                <div class="invalid-feedback">Please enter a phone number.</div>
                            </div>
                            <div class="col-md-6 mb-3 select-style-1">
                                <label>Department</label>
                                <div class="select-position">
                                    <select id="department" class="form-control form-select" name="department"
                                        required>
                                        <option value="" disabled selected>Select Department</option>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->dept_name }}">{{ $department->dept_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">Please select a department.</div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3 select-style-1">
                                <label>Employment Status</label>
                                <div class="select-position">
                                    <select id="employment_status" class="form-control form-select"
                                        name="employment_status" required>
                                        <option value="" disabled selected>Select Employment Status</option>
                                        <option value="Full-Time">Full-Time</option>
                                        <option value="Part-Time">Part-Time</option>
                                    </select>
                                    <div class="invalid-feedback">Please select an employment status.</div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="input-style-1">
                                    <label>Birthdate</label>
                                    <input type="date" class="form-control" id="birthdate" name="birthdate"
                                        required>
                                </div>
                                <div class="invalid-feedback">Please enter a birthdate.</div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="main-button light-btn btn-hover mb-1 me-2"
                        data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="main-button primary-btn btn-hover mb-1" form="addFacultyForm">Add
                        Faculty</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('admin.user-management.import-faculty') }}" method="POST"
                enctype="multipart/form-data" id="importForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importFacultyModalLabel">Import Faculty</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                            id="closeModalBtn"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="importFile" class="form-label">Upload CSV or Excel File</label>
                            <input type="file" class="form-control" name="import_file" id="importFile"
                                accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                                required>
                            <small class="text-muted">Accepted formats: .csv, .xls, .xlsx</small>
                        </div>

                        <div class="alert alert-info">
                            <p class="mb-1"><strong>Required columns:</strong></p>
                            <ul class="mb-0">
                                <li>Email</li>
                                <li>First Name</li>
                                <li>Middle Name (optional)</li>
                                <li>Last Name</li>
                                <li>Employee Number</li>
                                <li>Phone Number</li>
                                <li>Department</li>
                                <li>Employment Status (Full-Time/Part-Time)</li>
                                <li>Birthdate (format: MM/DD/YYYY)</li>
                            </ul>
                            <p class="mt-2 mb-0">
                                <a href="{{ route('admin.user-management.download-faculty-template') }}"
                                    class="text-primary">
                                    <i class="fa fa-download me-1"></i> Download template
                                </a>
                            </p>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="main-button light-btn btn-hover mb-1 me-2"
                                data-bs-dismiss="modal" id="cancelBtn">Cancel</button>
                            <button type="submit" class="main-button primary-btn" id="importBtn">
                                <span id="importBtnText">Import</span>
                                <span id="importBtnLoading" style="display: none;">
                                    <span class="spinner-border spinner-border-sm me-1" role="status"
                                        aria-hidden="true"></span>
                                    Processing...
                                </span>
                            </button>
                        </div>
                    </div>
            </form>
        </div>
    </div>

</main>
<!-- ======== main-wrapper end =========== -->
<script src="../../assets/admin/js/faculty.js"></script>
@include('admin.partials.footer')

</body>

</html>
