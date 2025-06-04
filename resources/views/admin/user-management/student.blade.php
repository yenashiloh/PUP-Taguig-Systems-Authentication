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

            <div id="messageContainer">

                <div class="tables-wrapper">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card-style mb-30">
                                <div class="row mb-3 align-items-center mb-2">
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
                                            <option value="1st Year">1st Year ({{ $yearCounts['1st Year'] ?? 0 }})
                                            </option>
                                            <option value="2nd Year">2nd Year ({{ $yearCounts['2nd Year'] ?? 0 }})
                                            </option>
                                            <option value="3rd Year">3rd Year ({{ $yearCounts['3rd Year'] ?? 0 }})
                                            </option>
                                            <option value="4th Year">4th Year ({{ $yearCounts['4th Year'] ?? 0 }})
                                            </option>
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
                                    <div class="col-md-4 d-flex mb-2 justify-content-end">
                                        <button class="main-button primary-btn me-2 btn-sm d-flex align-items-center" data-bs-toggle="modal"
                                            data-bs-target="#addUserModal">
                                            <i class="fas fa-plus me-1"></i> Add
                                        </button>
                                        <button type="button"
                                            class="main-btn primary-btn-outline square-btn me-2 btn-hover btn-sm"
                                            data-bs-toggle="modal" data-bs-target="#batchUploadModal">
                                            <i class="fas fa-upload me-1"></i>Upload
                                        </button>
                                        <div class="dropdown me-2">
                                            <button class="main-btn primary-btn-outline square-btn btn-hover btn-sm"
                                                type="button" id="exportDropdown" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                <i class="fas fa-file-export me-1"></i> Export
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                                <li>
                                                    <a class="dropdown-item" href="#"
                                                        onclick="exportAllStudents()">
                                                        <i class="fas fa-users me-2"></i> Export All Students
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#"
                                                        onclick="exportFilteredStudents()">
                                                        <i class="fas fa-filter me-2"></i> Export Filtered Data
                                                    </a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-muted" href="#"
                                                        style="cursor: default;">
                                                        <small><i class="fas fa-info-circle me-2"></i> CSV
                                                            format</small>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bulk Actions Bar -->
                                <div class="row mb-3" id="bulkActionsBar" style="display: none;">
                                    <div class="col-12">
                                        <div
                                            class="alert alert-info d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <span id="selectedCount">0</span> student(s) selected
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

                                <div class="table-wrapper table-responsive">
                                    <table class="table" id="userTable">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px;">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="selectAll" onchange="toggleSelectAll()"
                                                            title="Select All">
                                                        <label class="form-check-label visually-hidden"
                                                            for="selectAll">
                                                            Select All
                                                        </label>
                                                    </div>
                                                </th>
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
                                                {{-- <th>
                                                    <h6>Batch Info</h6>
                                                </th> --}}
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
                                                    data-program="{{ $user->program }}"
                                                    data-year="{{ $user->year }}"
                                                    data-section="{{ $user->section }}"
                                                    data-status="{{ strtolower($user->status) }}"
                                                    data-user-id="{{ $user->id }}"
                                                    data-user-status="{{ $user->status }}">
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input user-checkbox"
                                                                type="checkbox"
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
                                                                {{ $user->student_number ?? ($user->employee_number ?? 'No ID Available') }}
                                                            </p>
                                                        </div>
                                                    </td>
                                                    <td class="min-width">
                                                        <p>{{ $user->last_name }}</p>
                                                    </td>
                                                    <td class="min-width">
                                                        <p>{{ $user->first_name }}</p>
                                                    </td>
                                                    <td class="min-width">
                                                        <p>{{ $user->email }}</p>
                                                    </td>
                                                    {{-- <td class="min-width">
                                                        @if ($user->batch_number && $user->school_year)
                                                            <span class="badge bg-info">
                                                                Batch {{ $user->batch_number }}
                                                                ({{ $user->school_year }})
                                                            </span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td> --}}
                                                    <td class="min-width">
                                                        <p>{{ $user->status }}</p>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <button class="btn btn-outline-primary btn-sm"
                                                                onclick="window.location='{{ route('admin.user-management.view-student', ['user' => $user->id]) }}'">
                                                                <i class="fas fa-eye me-1"></i> View
                                                            </button>
                                                            @if ($user->status === 'Active' || $user->status === 'active')
                                                                <button class="btn btn-outline-danger btn-sm"
                                                                    onclick="toggleAccountStatus({{ $user->id }}, 'deactivate')">
                                                                    <i class="fas fa-ban me-1"></i> Deactivate
                                                                </button>
                                                            @elseif ($user->status === 'Deactivated' || $user->status === 'deactivated')
                                                                <button class="btn btn-outline-success btn-sm"
                                                                    onclick="toggleAccountStatus({{ $user->id }}, 'reactivate')">
                                                                    <i class="fas fa-check-circle me-1"></i> Reactivate
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
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
                                <label for="email" class="form-label">Email <span
                                        class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>
                            <div class="col-md-6 mb-3 input-style-1">
                                <label for="student_number" class="form-label">Student Number <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="student_number" name="student_number"
                                    required>
                                <div class="invalid-feedback">Please enter a student number.</div>
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
                                <input type="text" class="form-control" id="middle_name" name="middle_name"
                                    required>
                                <div class="invalid-feedback">Please enter a middle name.</div>
                            </div>
                            <div class="col-md-4 mb-3 input-style-1">
                                <label for="last_name" class="form-label">Last Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                                <div class="invalid-feedback">Please enter a last name.</div>
                            </div>
                            <div class="col-md-6 mb-3 select-style-1">
                                <label>Year <span class="text-danger">*</span></label>
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
                                <label>Section <span class="text-danger">*</span></label>
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
                                <label>Program <span class="text-danger">*</span></label>
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
                    <button type="submit" class="main-button primary-btn btn-hover mb-1" form="addUserForm">Add
                        Student</button>
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
                        <i class="fas fa-upload me-2"></i>Batch Upload Students
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body modal-dialog-centered" style="max-height: 65vh; overflow-y: auto;">
                    <form id="batchUploadForm" action="{{ route('batch-upload-students') }}" method="POST"
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
                                        Email, First Name, Middle Name (optional), Last Name, Student Number, Program,
                                        Year, Section, Birthdate

                                    </p>
                                    <!-- Template Download -->
                                    <p class="mt-2 mb-0">
                                        <a href="{{ route('admin.user-management.download-template') }}"
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
                                    <small class="text-muted">Multiple files supported (Max: 10MB)</small>
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
                        Start Batch Upload
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- ========== section end ========== -->
</main>
<!-- ======== main-wrapper end =========== -->

<script></script>

<script src="../../assets/admin/js/student.js"></script>

@include('admin.partials.footer')

</body>

</html>
