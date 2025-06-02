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

            <!-- Batch Upload Success Message -->
            @if (session('batch_success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa fa-check-circle me-2"></i>
                    {!! session('batch_success') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Batch Upload Error Message -->
            @if (session('batch_error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fa fa-exclamation-triangle me-2"></i>
                    {!! session('batch_error') !!}
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
                                        data-bs-toggle="modal" data-bs-target="#batchUploadModal">
                                        <i class="fas fa-upload me-1"></i> Upload
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
                                        @forelse ($users as $user)
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
                                                        <a> {{ $user->status }}</a>
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
                                        @empty
                                            <!-- No data row with correct column count -->
                                            <tr class="no-records-row">
                                                <td></td> <!-- Checkbox column -->
                                                <td></td> <!-- Employee ID column -->
                                                <td></td> <!-- Last Name column -->
                                                <td class="text-center py-4"
                                                    style="font-style: italic; color: #6c757d;">
                                                    No faculty members found
                                                </td>
                                                <td></td> <!-- Email column -->
                                                <td></td> <!-- Status column -->
                                                <td></td> <!-- Action column -->
                                            </tr>
                                        @endforelse
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
                                <label for="email" class="form-label">Email <span
                                        class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>
                            <div class="col-md-6 mb-3 input-style-1">
                                <label for="employee_number" class="form-label">Employee Number <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="employee_number"
                                    name="employee_number" required>
                                <div class="invalid-feedback">Please enter an employee number.</div>
                            </div>
                            <div class="col-md-4 mb-3 input-style-1">
                                <label for="first_name" class="form-label">First Name <span
                                        class="text-danger">*</span></label>
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
                                <label for="last_name" class="form-label">Last Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                                <div class="invalid-feedback">Please enter a last name.</div>
                            </div>
                            <div class="col-md-6 mb-3 input-style-1">
                                <label for="phone_number" class="form-label">Phone Number <span
                                        class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone_number" name="phone_number"
                                    required>
                                <div class="invalid-feedback">Please enter a phone number.</div>
                            </div>
                            <div class="col-md-6 mb-3 select-style-1">
                                <label>Department <span class="text-danger">*</span></label>
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
                                <label>Employment Status <span class="text-danger">*</span></label>
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
                                    <label>Birthdate <span class="text-danger">*</span></label>
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

    <!-- Batch Upload Modal -->
    <div class="modal fade" id="batchUploadModal" tabindex="-1" aria-labelledby="batchUploadModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="batchUploadModalLabel">
                        <i class="fas fa-upload me-2"></i>Batch Upload Faculty
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body modal-dialog-centered" style="max-height: 65vh; overflow-y: auto;">
                    <form id="batchUploadForm" action="{{ route('batch-upload-faculty') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf

                        <!-- Upload Guidelines -->
                        <div class="alert alert-info">
                            <h6 class="alert-heading mb-2">
                                <i class="fas fa-info-circle me-2"></i>Upload Guidelines
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>File Requirements:</strong></p>
                                    <ul class="mb-2">
                                        <li>Maximum 10 files per batch</li>
                                        <li>Total size: Maximum 10MB for all files combined</li>
                                        <li>Total rows: 5000 maximum</li>
                                        <li>Formats: .csv, .xlsx, .xls</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Required Columns:</strong></p>
                                    <p class="mb-0">
                                        Email, First Name, Middle Name (optional), Last Name, Employee Number, Phone Number, Department, Employment Status, Birthdate 
                                    </p>
                                    <!-- Template Download -->
                                    <p class="mt-2 mb-0">
                                        <a href="{{ route('admin.user-management.download-faculty-template') }}"
                                            class="text-primary">
                                            <i class="fa fa-download me-1"></i>Download Template
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Selections OUTSIDE the alert -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-style-1">
                                    <label>Select Batch Number <span class="text-danger">*</span></label>
                                    <select class="form-control form-select" name="batch_number" required>
                                        <option value="" disabled selected>Choose Batch Number</option>
                                        @for ($i = 1; $i <= 10; $i++)
                                            <option value="{{ $i }}">Batch {{ $i }}</option>
                                        @endfor
                                    </select>
                                    <div class="invalid-feedback">Please select a batch number.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-style-1">
                                    <label>School Year <span class="text-danger">*</span></label>
                                    <select class="form-control form-select" name="school_year" required>
                                        <option value="" disabled selected>Choose School Year</option>
                                        @php
                                            $currentYear = date('Y');
                                            $startYear = $currentYear - 5;
                                            $endYear = $currentYear;
                                        @endphp
                                        @for ($year = $endYear; $year >= $startYear; $year--)
                                            <option value="{{ $year }}"
                                                {{ $year == $currentYear ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endfor
                                    </select>
                                    <div class="invalid-feedback">Please select a school year.</div>
                                </div>
                            </div>
                        </div>

                        <!-- File Upload Section -->
                        <div class="mb-4">
                            <label class="form-label">Upload CSV/Excel Files <span
                                    class="text-danger">*</span></label>
                            <div class="upload-container border rounded p-4" style="background-color: #f8f9fa;">
                                <input type="file" class="form-control" name="upload_files[]"
                                    id="batchUploadFiles" multiple accept=".csv,.xlsx,.xls" required>
                                <div class="text-center mb-1 mt-2">
                                    <small class="text-muted">Multiple files supported (Max: 10 files, 10MB
                                        each)</small>
                                </div>
                                <div class="">
                                    <div id="filesList" class="files-list"></div>
                                    <div id="uploadValidation" class="validation-info mt-2"></div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="main-button light-btn btn-hover mb-1 me-2" data-bs-dismiss="modal"
                        id="cancelUploadBtn">Cancel</button>
                    <button type="submit" class="main-button primary-btn btn-hover mb-1" form="batchUploadForm"
                        id="startUploadBtn">
                        <i class="fas fa-upload me-1"></i>Start Batch Upload
                    </button>
                </div>
            </div>
        </div>
    </div>

</main>
<!-- ======== main-wrapper end =========== -->

<script src="../../assets/admin/js/faculty.js"></script>
@include('admin.partials.footer')

</body>

</html>