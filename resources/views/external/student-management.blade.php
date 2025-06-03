<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student Management - External</title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="external-app">

    <link rel="shortcut icon" href="assets/images/PUPLogo.png" type="image/x-icon" />

    <!-- ========== All CSS files linkup ========= -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="{{ asset('../../assets/admin/css/main.css') }}" />
</head>

<body>
    <!-- ======== Preloader =========== -->
    <div id="preloader">
        <div class="spinner"></div>
    </div>



    <!-- API Indicator -->
    <div class="api-indicator" id="apiIndicator" style="display: none;">
        <i class="fas fa-shield-alt"></i> API Connected
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
                        <span class="text fw-bold">Menu</span>
                    </a>
                </li>
                <li class="nav-item active">
                    <a href="javascript:void(0);">
                        <span class="icon">
                            <i class="fas fa-users"></i>
                        </span>
                        <span class="text">Student Management</span>
                    </a>
                </li>
                <span class="divider">
                    <hr />
                </span>
                <li class="nav-item">
                    <a href="javascript:void(0);" onclick="disconnectApi()">
                        <span class="icon">
                            <i class="fas fa-sign-out-alt"></i>
                        </span>
                        <span class="text">Disconnect</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- ======== main-wrapper start =========== -->
    <main class="main-wrapper">
        <!-- ========== header start ========== -->


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
                                        {{-- <div class="dropdown me-2">
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
                                        </div> --}}
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
                <div class="modal-body">
                    <form id="addUserForm" novalidate>
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
                                    <div class="invalid-feedback">Please select a section.</div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3 select-style-1">
                                <label>Program <span class="text-danger">*</span></label>
                                <div class="select-position">
                                    <select id="program" class="form-control form-select" name="program" required>
                                        <option value="" disabled selected>Select your program/course</option>
                                        <!-- Programs will be loaded dynamically -->
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
                                        <option value="2024" selected>2024</option>
                                        <option value="2023">2023</option>
                                        <option value="2022">2022</option>
                                        <option value="2021">2021</option>
                                        <option value="2020">2020</option>
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
                        Start Batch Upload
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
                <div class="modal-body">
                    <form id="viewStudentForm">
                        <div id="studentDetailsContainer"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Update Student</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    @include('admin.partials.footer')

    <script>
        // Global variables
        let API_KEY = '';
        let BASE_URL = 'http://127.0.0.1:8000';
        let studentsData = [];
        let coursesData = [];

        // Initialize the application
        document.addEventListener('DOMContentLoaded', function() {
            // Hide preloader
            setTimeout(() => {
                document.getElementById('preloader').style.display = 'none';
            }, 1000);

            // Get API key from URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            API_KEY = urlParams.get('api_key');

            if (!API_KEY) {
                showAlert('API key is required. Please add ?api_key=YOUR_API_KEY to the URL.', 'danger');
                return;
            }

            // Get base URL from URL parameters if provided
            const baseUrl = urlParams.get('base_url');
            if (baseUrl) {
                BASE_URL = baseUrl;
            }

            // Show API indicator
            document.getElementById('apiIndicator').style.display = 'block';

            // Initialize the application
            initializeApp();
        });

        // Initialize application
        async function initializeApp() {
            try {
                // Add validation styles
                addValidationStyles();

                // Validate API key and load initial data
                await validateAndLoadData();

                // Set up form handlers
                setupFormHandlers();

                // Setup real-time validation
                setupRealTimeValidation();

                showAlert('Connected successfully to PUP-Taguig Systems!', 'success');
            } catch (error) {
                console.error('Initialization error:', error);
                showAlert('Failed to connect to the system. Please check your API key and try again.', 'danger');
            }
        }


        // Validate API key and load data
        async function validateAndLoadData() {
            try {
                // Load students data
                await loadStudents();

                // Load courses for dropdown
                await loadCourses();

                // Update UI with application info
                updateApplicationInfo();

            } catch (error) {
                throw new Error('Failed to validate API key or load data: ' + error.message);
            }
        }

        // Load students from API
        async function loadStudents() {
            try {
                const response = await makeApiCall('/api/students', 'GET');

                if (response.success) {
                    studentsData = response.data.students || [];
                    renderStudentsTable();
                    updateFilterCounts();
                } else {
                    throw new Error(response.message || 'Failed to load students');
                }
            } catch (error) {
                console.error('Error loading students:', error);
                throw error;
            }
        }
        // Load courses from API
        async function loadCourses() {
            try {
                const response = await makeApiCall('/api/students/courses', 'GET');

                if (response.success) {
                    coursesData = response.data.courses || [];
                    populateCoursesDropdown();
                } else {
                    console.warn('Failed to load courses:', response.message);
                    // Continue without courses if API doesn't support it
                }
            } catch (error) {
                console.warn('Error loading courses:', error);
                // Continue without courses if API doesn't support it
            }
        }

        // Make API call - FIXED VERSION
        async function makeApiCall(endpoint, method = 'GET', data = null) {
            const url = BASE_URL + endpoint;
            const options = {
                method: method,
                headers: {
                    'X-API-Key': API_KEY,
                    'Accept': 'application/json'
                }
            };

            if (data && method !== 'GET') {
                if (data instanceof FormData) {
                    // Don't set Content-Type for FormData - let browser set it
                    options.body = data;
                } else if (typeof data === 'object') {
                    // Send as JSON
                    options.headers['Content-Type'] = 'application/json';
                    options.body = JSON.stringify(data);
                } else {
                    options.body = data;
                }
            }

            console.log('Making API call:', method, url, options);

            const response = await fetch(url, options);

            if (!response.ok) {
                const errorText = await response.text();
                console.error('API Error Response:', errorText);
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        }



        // Render students table
        function renderStudentsTable() {
            const tbody = document.getElementById('studentsTableBody');
            tbody.innerHTML = '';

            studentsData.forEach(student => {
                const row = createStudentRow(student);
                tbody.appendChild(row);
            });
        }

        // Create student row
        function createStudentRow(student) {
            const tr = document.createElement('tr');
            tr.id = `user-${student.id}`;
            tr.setAttribute('data-program', student.program || '');
            tr.setAttribute('data-year', student.year || '');
            tr.setAttribute('data-section', student.section || '');
            tr.setAttribute('data-status', (student.status || '').toLowerCase());
            tr.setAttribute('data-user-id', student.id);
            tr.setAttribute('data-user-status', student.status || '');

            tr.innerHTML = `
                <td>
                    <div class="form-check">
                        <input class="form-check-input user-checkbox" type="checkbox" 
                               id="user-checkbox-${student.id}" value="${student.id}" 
                               data-status="${student.status}" onchange="updateSelectAll()">
                        <label class="form-check-label visually-hidden" for="user-checkbox-${student.id}">
                            Select ${student.first_name} ${student.last_name}
                        </label>
                    </div>
                </td>
                <td class="min-width">
                    <div class="lead">
                        <p>${student.student_number || student.employee_number || 'No ID Available'}</p>
                    </div>
                </td>
                <td class="min-width">
                    <p>${student.last_name}</p>
                </td>
                <td class="min-width">
                    <p>${student.first_name}</p>
                </td>
                <td class="min-width">
                    <p>${student.email}</p>
                </td>
                <td class="min-width">
                    <p>${student.status}</p>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <button class="btn btn-outline-primary btn-sm" onclick="viewStudent(${student.id})">
                            <i class="fas fa-eye me-1"></i> View
                        </button>
                        ${student.status === 'Active' ? 
                            `<button class="btn btn-outline-danger btn-sm" onclick="toggleAccountStatus(${student.id}, 'deactivate')">
                                                                                                                                <i class="fas fa-ban me-1"></i> Deactivate
                                                                                                                            </button>` :
                            `<button class="btn btn-outline-success btn-sm" onclick="toggleAccountStatus(${student.id}, 'reactivate')">
                                                                                                                                <i class="fas fa-check-circle me-1"></i> Reactivate
                                                                                                                            </button>`
                        }
                    </div>
                </td>
            `;

            return tr;
        }

        // Populate courses dropdown
        function populateCoursesDropdown() {
            const programSelect = document.getElementById('program');
            const programFilter = document.getElementById('programFilter');

            // Clear existing options
            programSelect.innerHTML = '<option value="" disabled selected>Select your program/course</option>';
            programFilter.innerHTML = '<option value="" disabled selected>Programs</option>';

            coursesData.forEach(course => {
                const option1 = document.createElement('option');
                option1.value = course.course_name;
                option1.textContent = course.course_name;
                programSelect.appendChild(option1);

                const option2 = document.createElement('option');
                option2.value = course.course_name;
                option2.textContent = course.course_name;
                programFilter.appendChild(option2);
            });
        }

        // Update filter counts
        function updateFilterCounts() {
            // Count by program
            const programCounts = {};
            const yearCounts = {};
            const sectionCounts = {};
            const statusCounts = {};

            studentsData.forEach(student => {
                // Program counts
                if (student.program) {
                    programCounts[student.program] = (programCounts[student.program] || 0) + 1;
                }

                // Year counts
                if (student.year) {
                    yearCounts[student.year] = (yearCounts[student.year] || 0) + 1;
                }

                // Section counts
                if (student.section) {
                    sectionCounts[student.section] = (sectionCounts[student.section] || 0) + 1;
                }

                // Status counts
                if (student.status) {
                    statusCounts[student.status] = (statusCounts[student.status] || 0) + 1;
                }
            });

            // Update filter dropdowns with counts
            updateFilterOptions('programFilter', programCounts);
            updateFilterOptions('yearFilter', yearCounts);
            updateFilterOptions('sectionFilter', sectionCounts);
            updateFilterOptions('accountStatusFilter', statusCounts);
        }

        // Update filter options with counts
        function updateFilterOptions(filterId, counts) {
            const select = document.getElementById(filterId);
            const options = select.querySelectorAll('option:not(:first-child)');

            options.forEach(option => {
                const value = option.value;
                const count = counts[value] || 0;
                const text = option.textContent.split('(')[0].trim();
                option.textContent = `${text} (${count})`;
            });
        }

        // Setup form handlers - FIXED VERSION
        function setupFormHandlers() {
            document.getElementById('addUserForm').addEventListener('submit', handleAddStudent);
            document.getElementById('batchUploadForm').addEventListener('submit', handleBatchUpload);
            document.getElementById('batchUploadFiles').addEventListener('change', handleFileSelection);

            // Use the enhanced update function
            const viewStudentForm = document.getElementById('viewStudentForm');
            if (viewStudentForm) {
                viewStudentForm.addEventListener('submit', updateStudent);
            }

            // Setup real-time validation
            setupRealTimeValidation();

            // Add validation styles
            addValidationStyles();
        }

        // Handle add student - IMPROVED VERSION
        async function handleAddStudent(e) {
            e.preventDefault();

            // Clear previous validation errors
            clearAllValidationErrors();

            const formData = new FormData(e.target);

            // Convert to object for easier debugging
            const studentData = {};
            for (let [key, value] of formData.entries()) {
                studentData[key] = value;
            }

            // Client-side validation first
            const clientValidationErrors = validateStudentData(studentData);
            if (Object.keys(clientValidationErrors).length > 0) {
                showInlineValidationErrors(clientValidationErrors);
                // Focus on first error field
                const firstErrorField = document.querySelector('.is-invalid');
                if (firstErrorField) {
                    firstErrorField.focus();
                    firstErrorField.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
                return;
            }

            console.log('Adding student with data:', studentData);

            try {
                showLoading();

                const response = await makeApiCall('/api/students', 'POST', studentData);

                if (response.success) {
                    showAlert('Student added successfully!', 'success');
                    await loadStudents(); // Reload students
                    e.target.reset();
                    clearAllValidationErrors(); // Clear any remaining errors
                    bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
                } else {
                    // Handle validation errors - show them in fields instead of alert
                    if (response.errors) {
                        showInlineValidationErrors(response.errors);

                        // Focus on first error field
                        const firstErrorField = document.querySelector('.is-invalid');
                        if (firstErrorField) {
                            firstErrorField.focus();
                            firstErrorField.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                        }

                        // Don't hide the modal - let user fix errors
                        return;
                    } else {
                        throw new Error(response.message || 'Failed to add student');
                    }
                }
            } catch (error) {
                console.error('Error adding student:', error);
                showAlert('Failed to add student: ' + error.message, 'danger');
            } finally {
                hideLoading();
            }
        }
        // Handle batch upload
        async function handleBatchUpload(e) {
            e.preventDefault();

            const formData = new FormData(e.target);

            try {
                showLoading();

                const response = await makeApiCall('/api/students/batch-upload', 'POST', formData);

                if (response.success) {
                    showAlert('Batch upload completed successfully!', 'success');
                    await loadStudents(); // Reload students
                    e.target.reset();
                    bootstrap.Modal.getInstance(document.getElementById('batchUploadModal')).hide();
                } else {
                    throw new Error(response.message || 'Failed to upload batch');
                }
            } catch (error) {
                console.error('Error uploading batch:', error);
                showAlert('Failed to upload batch: ' + error.message, 'danger');
            } finally {
                hideLoading();
            }
        }

        // Handle file selection
        function handleFileSelection(e) {
            const filesList = document.getElementById('filesList');
            const files = e.target.files;

            filesList.innerHTML = '';

            if (files.length === 0) return;

            Array.from(files).forEach((file, index) => {
                const fileDiv = document.createElement('div');
                fileDiv.className = 'file-item';
                fileDiv.innerHTML = `
                    <span><i class="fas fa-file me-2"></i>${file.name}</span>
                    <span class="text-muted">${(file.size / 1024 / 1024).toFixed(2)} MB</span>
                `;
                filesList.appendChild(fileDiv);
            });
        }

        // View student details
        async function viewStudent(studentId) {
            try {
                showLoading();

                const response = await makeApiCall(`/api/students/${studentId}`, 'GET');

                if (response.success) {
                    const student = response.data.student;
                    populateStudentModal(student);
                    new bootstrap.Modal(document.getElementById('viewStudentModal')).show();
                } else {
                    throw new Error(response.message || 'Failed to load student details');
                }
            } catch (error) {
                console.error('Error loading student:', error);
                showAlert('Failed to load student details: ' + error.message, 'danger');
            } finally {
                hideLoading();
            }
        }

        // Populate student modal - FIXED VERSION
        function populateStudentModal(student) {
            const container = document.getElementById('studentDetailsContainer');
            container.innerHTML = `
                <div class="row">
                    <div class="col-md-6 mb-3 input-style-1">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" value="${student.email || ''}" required>
                    </div>
                    <div class="col-md-6 mb-3 input-style-1">
                        <label class="form-label">Student Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="student_number" value="${student.student_number || ''}" required>
                    </div>
                    <div class="col-md-4 mb-3 input-style-1">
                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="first_name" value="${student.first_name || ''}" required>
                    </div>
                    <div class="col-md-4 mb-3 input-style-1">
                        <label class="form-label">Middle Name</label>
                        <input type="text" class="form-control" name="middle_name" value="${student.middle_name || ''}">
                    </div>
                    <div class="col-md-4 mb-3 input-style-1">
                        <label class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="last_name" value="${student.last_name || ''}" required>
                    </div>
                    <div class="col-md-6 mb-3 select-style-1">
                        <label>Program <span class="text-danger">*</span></label>
                        <select class="form-control form-select" name="program" required>
                            <option value="">Select Program</option>
                            ${coursesData.map(course => 
                                `<option value="${course.course_name}" ${course.course_name === student.program ? 'selected' : ''}>${course.course_name}</option>`
                            ).join('')}
                        </select>
                    </div>
                    <div class="col-md-6 mb-3 select-style-1">
                        <label>Year <span class="text-danger">*</span></label>
                        <select class="form-control form-select" name="year" required>
                            <option value="">Select Year</option>
                            <option value="1st Year" ${student.year === '1st Year' ? 'selected' : ''}>1st Year</option>
                            <option value="2nd Year" ${student.year === '2nd Year' ? 'selected' : ''}>2nd Year</option>
                            <option value="3rd Year" ${student.year === '3rd Year' ? 'selected' : ''}>3rd Year</option>
                            <option value="4th Year" ${student.year === '4th Year' ? 'selected' : ''}>4th Year</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3 select-style-1">
                        <label>Section <span class="text-danger">*</span></label>
                        <select class="form-control form-select" name="section" required>
                            <option value="">Select Section</option>
                            ${[1,2,3,4,5,6,7,8,9,10].map(num => 
                                `<option value="${num}" ${student.section == num ? 'selected' : ''}>${num}</option>`
                            ).join('')}
                        </select>
                    </div>
                    <div class="col-md-6 mb-3 input-style-1">
                        <label class="form-label">Birthdate</label>
                        <input type="date" class="form-control" name="birthdate" value="${student.birthdate || ''}">
                    </div>
                </div>
                <input type="hidden" name="student_id" value="${student.id}">
            `;
        }

        // Update student - FIXED VERSION
        async function updateStudent(e) {
            e.preventDefault();

            // Clear previous validation errors
            clearAllValidationErrors();

            try {
                const form = e.target;
                const formData = new FormData(form);
                const studentId = formData.get('student_id');

                // Remove student_id from the data to be sent
                formData.delete('student_id');

                // Convert FormData to regular object for validation and JSON sending
                const studentData = {};
                for (let [key, value] of formData.entries()) {
                    studentData[key] = value;
                }

                // Client-side validation first
                const clientValidationErrors = validateStudentData(studentData, true); // true for update mode
                if (Object.keys(clientValidationErrors).length > 0) {
                    showInlineValidationErrors(clientValidationErrors);
                    // Focus on first error field
                    const firstErrorField = document.querySelector('.is-invalid');
                    if (firstErrorField) {
                        firstErrorField.focus();
                        firstErrorField.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                    return;
                }

                console.log('Updating student:', studentId, 'with data:', studentData);

                showLoading();

                // Use PUT method for update and send as JSON
                const response = await fetch(`${BASE_URL}/api/students/${studentId}`, {
                    method: 'PUT',
                    headers: {
                        'X-API-Key': API_KEY,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(studentData)
                });

                const data = await response.json();

                hideLoading();

                if (data.success) {
                    showAlert('Student updated successfully!', 'success');
                    await loadStudents();
                    clearAllValidationErrors(); // Clear any remaining errors
                    bootstrap.Modal.getInstance(document.getElementById('viewStudentModal')).hide();
                } else {
                    // Handle validation errors - show them in fields instead of alert
                    if (data.errors) {
                        showInlineValidationErrors(data.errors);

                        // Focus on first error field
                        const firstErrorField = document.querySelector('.is-invalid');
                        if (firstErrorField) {
                            firstErrorField.focus();
                            firstErrorField.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                        }

                        // Don't hide the modal - let user fix errors
                        return;
                    } else {
                        throw new Error(data.message || 'Failed to update student');
                    }
                }
            } catch (error) {
                hideLoading();
                console.error('Error updating student:', error);
                showAlert('Failed to update student: ' + error.message, 'danger');
            }
        }

        // Client-side validation function
        function validateStudentData(data, isUpdate = false) {
            const errors = {};

            // Email validation
            if (!data.email || !data.email.trim()) {
                errors.email = ['Email address is required.'];
            } else if (!isValidEmail(data.email.trim())) {
                errors.email = ['Please enter a valid email address.'];
            }

            // First name validation
            if (!data.first_name || !data.first_name.trim()) {
                errors.first_name = ['First name is required.'];
            } else if (data.first_name.trim().length < 2) {
                errors.first_name = ['First name must be at least 2 characters long.'];
            } else if (!/^[a-zA-Z\s]+$/.test(data.first_name.trim())) {
                errors.first_name = ['First name can only contain letters and spaces.'];
            }

            // Middle name validation (optional but validate format if provided)
            if (data.middle_name && data.middle_name.trim() && !/^[a-zA-Z\s]+$/.test(data.middle_name.trim())) {
                errors.middle_name = ['Middle name can only contain letters and spaces.'];
            }

            // Last name validation
            if (!data.last_name || !data.last_name.trim()) {
                errors.last_name = ['Last name is required.'];
            } else if (data.last_name.trim().length < 2) {
                errors.last_name = ['Last name must be at least 2 characters long.'];
            } else if (!/^[a-zA-Z\s]+$/.test(data.last_name.trim())) {
                errors.last_name = ['Last name can only contain letters and spaces.'];
            }

            // Student number validation
            if (!data.student_number || !data.student_number.trim()) {
                errors.student_number = ['Student number is required.'];
            } else if (data.student_number.trim().length < 5) {
                errors.student_number = ['Student number must be at least 5 characters long.'];
            } else if (!/^[A-Za-z0-9\-]+$/.test(data.student_number.trim())) {
                errors.student_number = ['Student number can only contain letters, numbers, and hyphens.'];
            }

            // Program validation
            if (!data.program || !data.program.trim()) {
                errors.program = ['Program is required.'];
            }

            // Year validation
            if (!data.year || !data.year.trim()) {
                errors.year = ['Year is required.'];
            } else if (!['1st Year', '2nd Year', '3rd Year', '4th Year'].includes(data.year)) {
                errors.year = ['Please select a valid year.'];
            }

            // Section validation
            if (!data.section || !data.section.trim()) {
                errors.section = ['Section is required.'];
            } else if (!['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'].includes(data.section)) {
                errors.section = ['Please select a valid section.'];
            }

            // Birthdate validation (optional but validate if provided)
            if (data.birthdate && data.birthdate.trim()) {
                const birthDate = new Date(data.birthdate);
                const today = new Date();

                if (isNaN(birthDate.getTime())) {
                    errors.birthdate = ['Please enter a valid birthdate.'];
                } else if (birthDate > today) {
                    errors.birthdate = ['Birthdate cannot be in the future.'];
                } else {
                    const age = today.getFullYear() - birthDate.getFullYear();
                    const monthDiff = today.getMonth() - birthDate.getMonth();

                    let actualAge = age;
                    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                        actualAge--;
                    }

                    if (actualAge < 15) {
                        errors.birthdate = ['Student must be at least 15 years old.'];
                    } else if (actualAge > 100) {
                        errors.birthdate = ['Please enter a valid birthdate.'];
                    }
                }
            }

            return errors;
        }

        // Email validation helper
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Function to clear all validation errors
        function clearAllValidationErrors() {
            // Remove all existing error styling and messages
            document.querySelectorAll('.is-invalid').forEach(element => {
                element.classList.remove('is-invalid');
            });

            document.querySelectorAll('.field-error-message').forEach(element => {
                element.remove();
            });

            // Reset invalid-feedback display
            document.querySelectorAll('.invalid-feedback').forEach(element => {
                element.style.display = 'none';
                element.textContent = '';
            });
        }

        function showInlineValidationErrors(errors) {
            Object.keys(errors).forEach(fieldName => {
                const field = document.querySelector(`[name="${fieldName}"]`);
                if (field) {
                    // Add red border to the field
                    field.classList.add('is-invalid');

                    // Get the error message(s)
                    const errorMessages = Array.isArray(errors[fieldName]) ? errors[fieldName] : [errors[
                    fieldName]];
                    const errorMessage = errorMessages[0]; // Use first error message

                    // Remove any existing error message for this field
                    const existingError = field.parentNode.querySelector('.field-error-message');
                    if (existingError) {
                        existingError.remove();
                    }

                    // Create and add error message element
                    const errorElement = document.createElement('div');
                    errorElement.className = 'field-error-message text-danger mt-1';
                    errorElement.style.fontSize = '0.875rem';
                    errorElement.textContent = errorMessage;

                    // Insert error message after the field
                    field.parentNode.appendChild(errorElement);
                }
            });
        }
        // Toggle account status
        async function toggleAccountStatus(studentId, action) {
            try {
                const actionText = action === 'deactivate' ? 'deactivate' : 'reactivate';

                const result = await Swal.fire({
                    title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)} Student?`,
                    text: `Are you sure you want to ${actionText} this student?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: action === 'deactivate' ? '#dc3545' : '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: `Yes, ${actionText}!`
                });

                if (result.isConfirmed) {
                    showLoading();

                    const response = await makeApiCall(`/api/students/${studentId}/toggle-status`, 'POST', {
                        action
                    });

                    if (response.success) {
                        showAlert(`Student ${actionText}d successfully!`, 'success');
                        await loadStudents(); // Reload students
                    } else {
                        throw new Error(response.message || `Failed to ${actionText} student`);
                    }
                }
            } catch (error) {
                console.error(`Error ${action}ing student:`, error);
                showAlert(`Failed to ${action} student: ` + error.message, 'danger');
            } finally {
                hideLoading();
            }
        }

        // Bulk actions
        async function bulkAction(action) {
            const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
            const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);

            if (selectedIds.length === 0) {
                showAlert('Please select at least one student.', 'warning');
                return;
            }

            try {
                const actionText = action === 'deactivate' ? 'deactivate' : 'reactivate';

                const result = await Swal.fire({
                    title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)} Students?`,
                    text: `Are you sure you want to ${actionText} ${selectedIds.length} selected student(s)?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: action === 'deactivate' ? '#dc3545' : '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: `Yes, ${actionText} them!`
                });

                if (result.isConfirmed) {
                    showLoading();

                    const response = await makeApiCall('/api/students/bulk-toggle-status', 'POST', {
                        user_ids: selectedIds,
                        action: action
                    });

                    if (response.success) {
                        showAlert(`Successfully ${actionText}d ${response.data.updated_count} student(s)!`, 'success');
                        await loadStudents(); // Reload students
                        clearSelection();
                    } else {
                        throw new Error(response.message || `Failed to ${actionText} students`);
                    }
                }
            } catch (error) {
                console.error(`Error ${action}ing students:`, error);
                showAlert(`Failed to ${action} students: ` + error.message, 'danger');
            } finally {
                hideLoading();
            }
        }

        // Download template
        async function downloadTemplate() {
            try {
                const response = await fetch(`${BASE_URL}/api/students/download-template`, {
                    method: 'GET',
                    headers: {
                        'X-API-Key': API_KEY
                    }
                });

                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = 'student_import_template.xlsx';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);

                    showAlert('Template downloaded successfully!', 'success');
                } else {
                    throw new Error('Failed to download template');
                }
            } catch (error) {
                console.error('Error downloading template:', error);
                showAlert('Failed to download template: ' + error.message, 'danger');
            }
        }

        // Filter functions
        function filterTable() {
            const filters = getActiveFilters();
            const tbody = document.getElementById('studentsTableBody');
            const rows = tbody.querySelectorAll('tr');

            rows.forEach(row => {
                let shouldShow = true;

                // Check program filter
                if (filters.program && row.getAttribute('data-program') !== filters.program) {
                    shouldShow = false;
                }

                // Check year filter
                if (filters.year && row.getAttribute('data-year') !== filters.year) {
                    shouldShow = false;
                }

                // Check section filter
                if (filters.section && row.getAttribute('data-section') !== filters.section) {
                    shouldShow = false;
                }

                // Check status filter
                if (filters.status && row.getAttribute('data-status') !== filters.status.toLowerCase()) {
                    shouldShow = false;
                }

                row.style.display = shouldShow ? '' : 'none';
            });
        }

        function getActiveFilters() {
            return {
                program: document.getElementById('programFilter').value,
                year: document.getElementById('yearFilter').value,
                section: document.getElementById('sectionFilter').value,
                status: document.getElementById('accountStatusFilter').value
            };
        }

        // Selection functions
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.user-checkbox');

            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });

            updateSelectAll();
        }

        function updateSelectAll() {
            const checkboxes = document.querySelectorAll('.user-checkbox');
            const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
            const selectAll = document.getElementById('selectAll');
            const bulkActionsBar = document.getElementById('bulkActionsBar');
            const selectedCount = document.getElementById('selectedCount');

            // Update select all checkbox
            if (checkedBoxes.length === 0) {
                selectAll.indeterminate = false;
                selectAll.checked = false;
            } else if (checkedBoxes.length === checkboxes.length) {
                selectAll.indeterminate = false;
                selectAll.checked = true;
            } else {
                selectAll.indeterminate = true;
                selectAll.checked = false;
            }

            // Show/hide bulk actions bar
            if (checkedBoxes.length > 0) {
                bulkActionsBar.style.display = 'block';
                selectedCount.textContent = checkedBoxes.length;

                // Update button states based on selection
                updateBulkActionButtons(checkedBoxes);
            } else {
                bulkActionsBar.style.display = 'none';
            }
        }

        function updateBulkActionButtons(checkedBoxes) {
            const reactivateBtn = document.getElementById('bulkReactivateBtn');
            const deactivateBtn = document.getElementById('bulkDeactivateBtn');

            let hasActive = false;
            let hasDeactivated = false;

            checkedBoxes.forEach(checkbox => {
                const status = checkbox.getAttribute('data-status');
                if (status === 'Active') hasActive = true;
                if (status === 'Deactivated') hasDeactivated = true;
            });

            // Enable/disable buttons based on selection
            reactivateBtn.disabled = !hasDeactivated;
            deactivateBtn.disabled = !hasActive;
        }

        function clearSelection() {
            document.querySelectorAll('.user-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            updateSelectAll();
        }

        // Utility functions
        function updateApplicationInfo() {
            // You can set application name from API response or URL params
            const appName = new URLSearchParams(window.location.search).get('app_name') || 'External Application';

            // Update title
            document.title = `${appName} - Student Management`;
        }

        function showAlert(message, type = 'info') {
            const alertContainer = document.getElementById('messageContainer');

            const alertClass = {
                'success': 'alert-success',
                'danger': 'alert-danger',
                'warning': 'alert-warning',
                'info': 'alert-info'
            } [type] || 'alert-info';

            const iconClass = {
                'success': 'fa-check-circle',
                'danger': 'fa-exclamation-triangle',
                'warning': 'fa-exclamation-triangle',
                'info': 'fa-info-circle'
            } [type] || 'fa-info-circle';

            const alert = document.createElement('div');
            alert.className = `alert ${alertClass} alert-dismissible fade show`;
            alert.innerHTML = `
                <i class="fa ${iconClass} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;

            alertContainer.appendChild(alert);

            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 5000);
        }

        function showLoading() {
            document.getElementById('preloader').style.display = 'flex';
        }

        function hideLoading() {
            document.getElementById('preloader').style.display = 'none';
        }

        function disconnectApi() {
            if (confirm('Are you sure you want to disconnect from the system?')) {
                // Clear URL parameters and reload
                window.location.href = window.location.pathname;
            }
        }

        // Function to clear all validation errors
        function clearValidationErrors() {
            // Remove all existing error messages and red borders
            document.querySelectorAll('.is-invalid').forEach(element => {
                element.classList.remove('is-invalid');
            });
            document.querySelectorAll('.invalid-feedback').forEach(element => {
                element.style.display = 'none';
                element.textContent = '';
            });
            document.querySelectorAll('.text-danger.field-error').forEach(element => {
                element.remove();
            });
        }

        // Function to show field validation errors
        function showFieldValidationErrors(errors) {
            Object.keys(errors).forEach(fieldName => {
                const field = document.querySelector(`[name="${fieldName}"]`);
                if (field) {
                    // Add red border to the field
                    field.classList.add('is-invalid');

                    // Find or create error message element
                    let errorElement = field.parentNode.querySelector('.invalid-feedback');
                    if (!errorElement) {
                        // Create error element if it doesn't exist
                        errorElement = document.createElement('div');
                        errorElement.className = 'invalid-feedback text-danger field-error';
                        field.parentNode.appendChild(errorElement);
                    }

                    // Show specific error messages
                    errorElement.style.display = 'block';
                    errorElement.innerHTML = errors[fieldName].join('<br>');
                }
            });
        }

        // Enhanced input validation with real-time feedback
        function setupRealTimeValidation() {
            // Add event listeners for real-time validation
            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('is-invalid')) {
                    // Clear error when user starts typing
                    e.target.classList.remove('is-invalid');
                    const errorElement = e.target.parentNode.querySelector('.invalid-feedback');
                    if (errorElement) {
                        errorElement.style.display = 'none';
                    }
                }
            });

            // Add change listeners for select fields
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('is-invalid')) {
                    // Clear error when user selects something
                    e.target.classList.remove('is-invalid');
                    const errorElement = e.target.parentNode.querySelector('.invalid-feedback');
                    if (errorElement) {
                        errorElement.style.display = 'none';
                    }
                }
            });
        }

        // Add CSS for better error styling
        function addValidationStyles() {
            const style = document.createElement('style');
            style.textContent = `
        .is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }
        
        .invalid-feedback {
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc3545;
        }
        
        .invalid-feedback.show, 
        .is-invalid ~ .invalid-feedback {
            display: block;
        }
        
        .field-error {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            border-radius: 4px;
            padding: 8px;
            margin-top: 5px;
        }
        
        .modal .alert {
            margin-bottom: 15px;
        }
        
        /* Highlight required fields */
        .form-label .text-danger {
            color: #dc3545;
        }
        
        /* Style for validation success */
        .is-valid {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
    `;
            document.head.appendChild(style);
        }

        // Enhanced modal handling to prevent auto-close on validation errors
        function preventModalCloseOnError() {
            // Override modal hide behavior when there are validation errors
            const addModal = document.getElementById('addUserModal');
            const updateModal = document.getElementById('viewStudentModal');

            [addModal, updateModal].forEach(modal => {
                if (modal) {
                    modal.addEventListener('hide.bs.modal', function(event) {
                        // Check if there are validation errors
                        const hasErrors = modal.querySelector('.is-invalid');
                        if (hasErrors && !event.target.dataset.forceClose) {
                            // Don't close modal if there are validation errors
                            // unless specifically forced
                            event.preventDefault();
                            return false;
                        }
                    });
                }
            });
        }

        // Function to force close modal (for successful operations)
        function forceCloseModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.dataset.forceClose = 'true';
                bootstrap.Modal.getInstance(modal).hide();
                delete modal.dataset.forceClose;
            }
        }
    </script>
</body>

</html>
