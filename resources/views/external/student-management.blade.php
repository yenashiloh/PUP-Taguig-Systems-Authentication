<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student Management - External</title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="external-app">

    <link rel="shortcut icon" href="../assets/images/PUPLogo.png" type="image/x-icon" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- ========== All CSS files linkup ========= -->
    <link rel="stylesheet" href="../assets/admin/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../assets/admin/css/lineicons.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="../assets/admin/css/materialdesignicons.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="../assets/admin/css/fullcalendar.css" />
    <link rel="stylesheet" href="../assets/admin/css/main.css" />

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
</head>

<body>
    <!-- ======== Preloader =========== -->
    <div id="preloader">
        <div class="spinner"></div>
    </div>

    <!-- ======== sidebar-nav start =========== -->
    <aside class="sidebar-nav-wrapper">
        <div class="navbar-logo">
            <div class="d-flex align-items-center">
                <img src="../../assets/images/PUPLogo.png" alt="logo" style="width: 50px; height: auto;" />
                <h6 class="ms-2 fw-bold mb-0" style="color:#7e0e09;">PUP-T Systems Authentication</h6>
            </div>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li class="nav-item active">
                    <a href="javascript:void(0);"
                        style="pointer-events: none; cursor: default; color: var(--primary-color);">
                        <span class="text fw-bold">Management</span>
                    </a>
                </li>
                <li class="nav-item active">
                    <a href="javascript:void(0);">
                        <span class="icon">
                            <i class="fas fa-users"></i>
                        </span>
                        <span class="text">Student</span>
                    </a>
                </li>
                <span class="divider">
                    <hr />
                </span>
                <li class="nav-item">
                    <a href="javascript:void(0);" onclick="logoutUser()">
                        <span class="icon">
                      <i class="lni lni-exit"></i>
                        </span>
                        <span class="text">Sign Out</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

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
                            <!-- ========== profile start ========== -->
                            <div class="profile-box ml-15">
                                <button class="dropdown-toggle bg-transparent border-0" type="button" id="profile"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <div class="profile-info">
                                        <div class="info">
                                            <div class="image">
                                                <img src="../../../assets/admin/images/profile-picture.png"
                                                    alt="" />
                                                <span class="status-indicator bg-success"></span>
                                            </div>
                                            <div>
                                                <h6 class="fw-500 text-dark" id="profileName"></h6>
                                                <p id="profileRole">Admin</p>
                                            </div>
                                        </div>
                                    </div>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profile"   style="min-width: auto; width: auto;">
                                    <li>
                                        <div class="author-info flex items-center !p-1">
                                            <div class="image">
                                                <img src="../../../assets/admin/images/profile-picture.png"
                                                    alt="" />

                                            </div>
                                            <div class="content">
                                                <h4 class="text-sm" id="dropdownProfileName">Admin</h4>
                                                <a class="text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white text-xs"
                                                    href="#" id="dropdownProfileEmail"></a>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="divider"></li>
                                    <li>
                                        <a href="javascript:void(0);" onclick="logoutUser()">
                                              <i class="lni lni-exit"></i> Sign Out
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <!-- ========== profile end ========== -->
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
                                <h2>All Student Lists</h2>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="breadcrumb-wrapper">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item">
                                            <a href="javascript:void(0);">Student Management</a>
                                        </li>
                                        <li class="breadcrumb-item active" aria-current="page">
                                            Students
                                        </li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Message Container -->
                <div id="messageContainer"></div>

                <div class="tables-wrapper">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card-style mb-30">
                                <div class="row mb-3 align-items-center mb-2">
                                    <div class="col-md-2">
                                        <select class="form-select" id="programFilter" onchange="filterTable()">
                                            <option value="" disabled selected>Programs</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select class="form-select" id="yearFilter" onchange="filterTable()">
                                            <option value="">Year</option>
                                            <option value="1st Year">1st Year</option>
                                            <option value="2nd Year">2nd Year</option>
                                            <option value="3rd Year">3rd Year</option>
                                            <option value="4th Year">4th Year</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select class="form-select" id="sectionFilter" onchange="filterTable()">
                                            <option value="">Section</option>
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
                                    </div>
                                    <div class="col-md-2">
                                        <select class="form-select" id="accountStatusFilter"
                                            onchange="filterTable()">
                                            <option value="">Status</option>
                                            <option value="Active">Active</option>
                                            <option value="Deactivated">Deactivated</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 d-flex mb-2 justify-content-end">
                                        <button class="main-button primary-btn me-2 btn-sm d-flex align-items-center"
                                            data-bs-toggle="modal" data-bs-target="#addUserModal">
                                            <i class="fas fa-plus me-1"></i> Add
                                        </button>
                                        <button type="button"
                                            class="main-btn primary-btn-outline square-btn me-2 btn-hover btn-sm"
                                            data-bs-toggle="modal" data-bs-target="#batchUploadModal">
                                            <i class="fas fa-upload me-1"></i>Upload
                                        </button>
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
                                                <span class="ms-2 text-muted" id="selectionDetails"></span>
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
                                                <th>
                                                    <h6>Account Status</h6>
                                                </th>
                                                <th>
                                                    <h6>Action</h6>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody id="studentsTableBody">
                                            <!-- Students will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </section>
    </main>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Add New Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="overflow-x: auto; max-height: 70vh;">
                    <form id="addUserForm" novalidate>
                        <!-- Alert placed at the top of the form -->
                        <div id="formAlert" class="alert d-none mb-3" role="alert"></div>
                        <div class="form-section">
                            <div class="row">
                                <!-- Email -->
                                <div class="col-12 col-md-6">
                                    <div class="input-group-validation input-style-1">
                                        <label for="add_email" class="form-label">Email <span
                                                class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="add_email" name="email"
                                            required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <!-- Student Number -->
                                <div class="col-12 col-md-6">
                                    <div class="input-group-validation input-style-1">
                                        <label for="add_student_number" class="form-label">Student Number <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="add_student_number"
                                            name="student_number" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <!-- First Name -->
                                <div class="col-12 col-md-4">
                                    <div class="input-group-validation input-style-1">
                                        <label for="add_first_name" class="form-label">First Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="add_first_name"
                                            name="first_name" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <!-- Middle Name -->
                                <div class="col-12 col-md-4">
                                    <div class="input-group-validation input-style-1">
                                        <label for="add_middle_name" class="form-label">Middle Name</label>
                                        <input type="text" class="form-control" id="add_middle_name"
                                            name="middle_name">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <!-- Last Name -->
                                <div class="col-12 col-md-4">
                                    <div class="input-group-validation input-style-1">
                                        <label for="add_last_name" class="form-label">Last Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="add_last_name"
                                            name="last_name" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <!-- Year -->

                                <div class="col-md-6 mb-3 select-style-1">
                                    <label>Year <span class="text-danger">*</span></label>
                                    <div class="select-position">
                                        <select id="add_year" class="form-control form-select select-position"
                                            name="year" required>
                                            <option value="" disabled selected>Select Year</option>
                                            <option value="1st Year">1st Year</option>
                                            <option value="2nd Year">2nd Year</option>
                                            <option value="3rd Year">3rd Year</option>
                                            <option value="4th Year">4th Year</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <!-- Section -->
                                <div class="col-md-6 mb-3 select-style-1">
                                    <label>Section <span class="text-danger">*</span></label>
                                    <div class="select-position">
                                        <select id="add_section" class="form-control form-select select-position"
                                            name="section" required>
                                            <option value="">Select Section</option>
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
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <!-- Program -->

                                <div class="col-md-6 mb-3 select-style-1">
                                    <label>Program <span class="text-danger">*</span></label>
                                    <div class="select-position">
                                        <select id="add_program" class="form-control form-select" name="program"
                                            required>
                                            <option value="">Select Program</option>
                                            <!-- Programs will be loaded dynamically -->
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <!-- Birthdate -->
                                <div class="col-12 col-md-6">
                                    <div class="input-group-validation input-style-1">
                                        <label for="add_birthdate" class="form-label">Birthdate <span
                                                class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="add_birthdate"
                                            name="birthdate">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="main-button light-btn btn-hover mb-1 me-2"
                        data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="main-button primary-btn btn-hover mb-1" form="addUserForm"
                        id="addStudentBtn">
                        <span class="btn-text">
                            Add Student
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Student Modal -->
    <div class="modal fade" id="viewStudentModal" tabindex="-1" aria-labelledby="viewStudentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewStudentModalLabel">Student Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="overflow-x: auto; max-height: 70vh;">
                    <form id="viewStudentForm" novalidate>
                        <!-- Alert placed at the top of the form -->
                        <div id="viewFormAlert" class="alert d-none mb-3" role="alert"></div>
                        <div class="form-section">
                            <div class="row" id="studentDetailsContainer">
                                <!-- Student details will be populated here -->
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="main-button light-btn btn-hover mb-1 me-2"
                        data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="main-button primary-btn btn-hover mb-1" form="viewStudentForm"
                        id="updateStudentBtn">
                        <span class="btn-text">
                            <i class="fas fa-save me-1"></i>Update Student
                        </span>
                    </button>
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
                    <form id="batchUploadForm" enctype="multipart/form-data">
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
                                        <a href="#" onclick="downloadTemplate()" class="text-primary">
                                            <i class="fa fa-download me-1"></i>Download Template
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Selections -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="input-style-1">
                                    <label>Select Batch Number <span class="text-danger">*</span></label>
                                    <select class="form-control form-select" name="batch_number" required>
                                        <option value="" disabled selected>Choose Batch Number</option>
                                        <option value="1">Batch 1</option>
                                        <option value="2">Batch 2</option>
                                        <option value="3">Batch 3</option>
                                        <option value="4">Batch 4</option>
                                        <option value="5">Batch 5</option>
                                        <option value="6">Batch 6</option>
                                        <option value="7">Batch 7</option>
                                        <option value="8">Batch 8</option>
                                        <option value="9">Batch 9</option>
                                        <option value="10">Batch 10</option>
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
                        <span class="btn-text">Start Batch Upload
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/admin/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/admin/js/Chart.min.js"></script>
    <script src="../assets/admin/js/dynamic-pie-chart.js"></script>
    <script src="../assets/admin/js/moment.min.js"></script>
    <script src="../assets/admin/js/fullcalendar.js"></script>
    <script src="../assets/admin/js/jvectormap.min.js"></script>
    <script src="../assets/admin/js/world-merc.js"></script>
    <script src="../assets/admin/js/polyfill.js"></script>
    <script src="../assets/admin/js/main.js"></script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- jQuery (necessary for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    <script src="../../../../assets/external/student-management.js"></script>

</body>

</html>
