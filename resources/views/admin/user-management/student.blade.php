@include('admin.partials.link')
<title>Student</title>

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
                            <h2>All Student Lists</h2>
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
                                        Student
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
                <!-- end row -->
            </div>

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
                            <div class="row mb-3 align-items-center">
                                <div class="col-md-2">
                                    <select class="form-select" id="programFilter" onchange="filterTable()">
                                        <option value="" disabled selected>Programs</option>
                                        @foreach ($courses as $course)
                                            @php
                                                $count = $programCounts[$course->course_name] ?? 0;
                                            @endphp
                                            <option value="{{ $course->course_name }}">
                                                {{ $course->course_name }} ({{ $count }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" id="yearFilter" onchange="filterTable()">
                                        <option value="">Year</option>
                                        <option value="1st Year">1st Year ({{ $yearCounts['1st Year'] ?? 0 }})</option>
                                        <option value="2nd Year">2nd Year ({{ $yearCounts['2nd Year'] ?? 0 }})</option>
                                        <option value="3rd Year">3rd Year ({{ $yearCounts['3rd Year'] ?? 0 }})</option>
                                        <option value="4th Year">4th Year ({{ $yearCounts['4th Year'] ?? 0 }})</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" id="sectionFilter" onchange="filterTable()">
                                        <option value="">Section</option>
                                        <option value="1">1 ({{ $sectionCounts['1'] ?? 0 }})</option>
                                        <option value="2">2 ({{ $sectionCounts['2'] ?? 0 }})</option>
                                        <option value="3">3 ({{ $sectionCounts['3'] ?? 0 }})</option>
                                        <option value="4">4 ({{ $sectionCounts['4'] ?? 0 }})</option>
                                        <option value="5">5 ({{ $sectionCounts['5'] ?? 0 }})</option>
                                        <option value="6">6 ({{ $sectionCounts['6'] ?? 0 }})</option>
                                        <option value="7">7 ({{ $sectionCounts['7'] ?? 0 }})</option>
                                        <option value="8">8 ({{ $sectionCounts['8'] ?? 0 }})</option>
                                        <option value="9">9 ({{ $sectionCounts['9'] ?? 0 }})</option>
                                        <option value="10">10 ({{ $sectionCounts['10'] ?? 0 }})</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" id="accountStatusFilter" onchange="filterTable()">
                                        <option value="">Status</option>
                                        <option value="Active">Active ({{ $statusCounts['Active'] ?? 0 }})</option>
                                        <option value="Deactivated">Deactivated
                                            ({{ $statusCounts['Deactivated'] ?? 0 }})</option>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex justify-content-end">
                                    <button class="main-button primary-btn me-2 btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#addUserModal">Add Users</button>
                                    <button type="button"
                                        class="main-btn primary-btn-outline square-btn btn-hover btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#importModal">
                                        Import User Data
                                    </button>
                                </div>
                            </div>


                            <div class="table-wrapper table-responsive">
                                <table class="table" id="userTable">
                                    <thead>
                                        <tr>
                                            <th>
                                                <h6>Student ID</h6>
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
                                            <tr id="user-{{ $user->id }}" data-program="{{ $user->program }}"
                                                data-year="{{ $user->year }}" data-section="{{ $user->section }}"
                                                data-status="{{ strtolower($user->status) }}">
                                                <td class="min-width">
                                                    <div class="lead">
                                                        <p>
                                                            {{ $user->student_number ?? ($user->employee_number ?? 'No ID Available') }}
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
                                                    <p class="status-text">{{ $user->status }}</p>
                                                </td>
                                                <td>
                                                    <button class="main-button secondary-btn btn-hover mb-1"
                                                        onclick="window.location='{{ route('admin.user-management.view-student', ['user' => $user->id]) }}'">
                                                        View
                                                    </button>
                                                    <span class="toggle-btn-container">
                                                        @if ($user->status === 'Active' || $user->status === 'active')
                                                            <button class="main-button danger-btn btn-hover mb-1"
                                                                onclick="toggleAccountStatus({{ $user->id }}, 'deactivate')">
                                                                Deactivate
                                                            </button>
                                                        @elseif ($user->status === 'Deactivated' || $user->status === 'deactivated')
                                                            <button class="main-button warning-btn btn-hover mb-1"
                                                                onclick="toggleAccountStatus({{ $user->id }}, 'reactivate')">
                                                                Reactivate
                                                            </button>
                                                        @endif
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                        @if (count($users) === 0)
                                            <tr class="no-records-row">
                                                <td colspan="6" class="text-center py-3">
                                                    <p class="text-muted mb-0">No records found.</p>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Add New Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm" novalidate>
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
                                <label for="student_number" class="form-label">Student Number</label>
                                <input type="text" class="form-control" id="student_number" name="student_number"
                                    required>
                                <div class="invalid-feedback">Please enter a student number.</div>
                            </div>
                            <div class="col-md-4 mb-3 input-style-1">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name"
                                    required>
                                <div class="invalid-feedback">Please enter a first name.</div>
                            </div>
                            <div class="col-md-4 mb-3 input-style-1">
                                <label for="middle_name" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="middle_name" name="middle_name"
                                    required>
                                <div class="invalid-feedback">Please enter a middle name.</div>
                            </div>
                            <div class="col-md-4 mb-3 input-style-1">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                                <div class="invalid-feedback">Please enter a last name.</div>
                            </div>
                            <div class="col-md-6 mb-3 select-style-1">
                                <label>Year</label>
                                <div class="select-position">
                                    <select id="year" class="form-control form-select select-position"
                                        name="year" required>
                                        <option value="" disabled selected>Select Year</option>
                                        <option value="1st Year">1st Year</option>
                                        <option value="2nd Year">2nd Year</option>
                                        <option value="3rd Year">3rd Year</option>
                                        <option value="4th Year">4th Year</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a year.</div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3 select-style-1">
                                <label>Section</label>
                                <div class="select-position">
                                    <select id="section" class="form-control form-select select-position"
                                        name="section" required>
                                        <option value="" disabled selected>Select Section</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                        <option value="6">6</option>
                                        <option value="7">7</option>
                                        <option value="8">8</option>
                                        <option value="9">9</option>
                                        <option value="10">10</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a year.</div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3 select-style-1">
                                <label>Program</label>
                                <div class="select-position">
                                    <select id="program" class="form-control form-select" name="program" required>
                                        <option value="" disabled selected>Select your program/course</option>
                                        @foreach ($courses as $course)
                                            <option value="{{ $course->course_name }}">{{ $course->course_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">Please select a program.</div>
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
                    <button type="submit" class="main-button primary-btn btn-hover mb-1" form="addUserForm">Add
                        Student</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('admin.user-management.import-students') }}" method="POST"
                enctype="multipart/form-data" id="importForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importUsersModalLabel">Import Students</h5>
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
                                <li>Student Number</li>
                                <li>Program</li>
                                <li>Year</li>
                                <li>Section</li>
                                <li>Birthdate (format: MM/DD/YYYY)</li>
                            </ul>
                            <p class="mt-2 mb-0">
                                <a href="{{ route('admin.user-management.download-template') }}"
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

    <!-- ========== section end ========== -->
</main>
<!-- ======== main-wrapper end =========== -->
{{-- <script src="../../assets/admin/js/faculty.js"></script> --}}
<script src="../../assets/admin/js/student.js"></script>

@include('admin.partials.footer')

</body>

</html>
