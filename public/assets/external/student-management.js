let API_KEY = '';
let BASE_URL = '';
let studentsData = [];
let coursesData = [];
let dataTable = null; 
let currentUser = null;
let sessionToken = null;

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    // Hide preloader
    setTimeout(() => {
        document.getElementById('preloader').style.display = 'none';
    }, 1000);

    // Get URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    API_KEY = urlParams.get('api_key');

    if (!API_KEY) {
        showAlert('API key is required. Please add ?api_key=YOUR_API_KEY to the URL.', 'danger');
        return;
    }

    // Extract user data from URL if available (from login redirect)
    const urlSessionToken = urlParams.get('session_token');
    const userId = urlParams.get('user_id');
    const userRole = urlParams.get('user_role');
    const appName = urlParams.get('app_name');

    // If we have session data in URL, store it and clean the URL
    if (urlSessionToken && userId) {
        sessionToken = urlSessionToken;
        
        // Create user object from URL params
        currentUser = {
            id: userId,
            first_name: userRole, // Use role as name for now, we'll get real data from API
            email: '', // Will be populated from API
            role: userRole
        };
        
        // Store in localStorage
        localStorage.setItem('sessionToken', sessionToken);
        localStorage.setItem('userData', JSON.stringify(currentUser));
        
        // Clean the URL to remove sensitive parameters
        const cleanUrl = `${window.location.pathname}?api_key=${API_KEY}`;
        window.history.replaceState({}, document.title, cleanUrl);
        
        // Update profile display with basic info
        updateProfileDisplay(currentUser);
    } else {
        // Check for stored session data
        const storedSessionToken = localStorage.getItem('sessionToken');
        const storedUserData = localStorage.getItem('userData');
        
        if (storedSessionToken && storedUserData) {
            sessionToken = storedSessionToken;
            currentUser = JSON.parse(storedUserData);
            updateProfileDisplay(currentUser);
        }
    }

    // Determine base URL dynamically
    BASE_URL = determineBaseUrl(urlParams);
    console.log('Using BASE_URL:', BASE_URL);

    // Initialize the application
    initializeApp();
});

async function fetchLoggedInAdminData() {
    try {
        // Get admin data from API key validation or a dedicated endpoint
        const response = await makeApiCall('/api/auth/current-admin', 'GET');
        
        if (response.success && response.data.user) {
            currentUser = response.data.user;
            sessionToken = response.data.session_token;
            
            // Update profile display
            updateProfileDisplay(currentUser);
            
            // Store in localStorage for persistence
            localStorage.setItem('userData', JSON.stringify(currentUser));
            if (sessionToken) {
                localStorage.setItem('sessionToken', sessionToken);
            }
            
            return currentUser;
        }
    } catch (error) {
        console.error('Failed to fetch admin data:', error);
        
        // Check localStorage for cached data
        const cachedUserData = localStorage.getItem('userData');
        const cachedSessionToken = localStorage.getItem('sessionToken');
        
        if (cachedUserData) {
            currentUser = JSON.parse(cachedUserData);
            sessionToken = cachedSessionToken;
            updateProfileDisplay(currentUser);
            return currentUser;
        }
    }
    return null;
}

//Function to update the profile display
function updateProfileDisplay(user) {
    const profileName = document.getElementById('profileName');
    const profileRole = document.getElementById('profileRole');
    const dropdownProfileName = document.getElementById('dropdownProfileName');
    const dropdownProfileEmail = document.getElementById('dropdownProfileEmail');
    
    if (user) {
        const firstName = user.first_name || user.role || 'Admin';
        const email = user.email || '';
        const role = user.role || '';
        
        profileName.textContent = firstName;
        profileRole.textContent = role;
        dropdownProfileName.textContent = firstName;
        dropdownProfileEmail.textContent = email;
    } else {
        // Default fallback
        profileName.textContent = 'Admin';
        profileRole.textContent = '';
        dropdownProfileName.textContent = 'Admin';
        dropdownProfileEmail.textContent = '';
    }
}
// Determine the correct base URL
function determineBaseUrl(urlParams) {
    // Check if base_url is explicitly provided in URL parameters
    const paramBaseUrl = urlParams.get('base_url');
    if (paramBaseUrl) {
        return paramBaseUrl;
    }

    // Auto-detect based on current hostname
    const hostname = window.location.hostname;
    const protocol = window.location.protocol;
    const port = window.location.port;

    // Local development
    if (hostname === 'localhost' || hostname === '127.0.0.1') {
        if (port && port !== '80' && port !== '443') {
            return `${protocol}//${hostname}:${port}`;
        } else {
            return `${protocol}//${hostname}`;
        }
    }

    // Production domain
    if (hostname === 'pupt-registration.site' || hostname.endsWith('.pupt-registration.site')) {
        return `${protocol}//${hostname}`;
    }

    // Fallback - use current domain
    if (port && port !== '80' && port !== '443') {
        return `${protocol}//${hostname}:${port}`;
    } else {
        return `${protocol}//${hostname}`;
    }
}

// Initialize application
async function initializeApp() {
    try {
        // Validate API key and load initial data
        await validateAndLoadData();

        // Set up form handlers
        setupFormHandlers();

        // Setup real-time validation
        setupRealTimeValidation();

        // Show success alert only once (first-time only)
        const hasConnectedBefore = localStorage.getItem('hasConnectedBefore');
        if (!hasConnectedBefore) {
            showAlert('Connected successfully to PUP-Taguig Systems!', 'success');
            localStorage.setItem('hasConnectedBefore', 'true');
        }
    } catch (error) {
        console.error('Initialization error:', error);

        if (isApiKeyError(error)) {
            showAlert('API Authentication failed: ' + error.message, 'danger');
        } else {
            showAlert('Failed to connect to the system. Please check your connection and try again.', 'danger');
        }
    }
}

// Check for login data from URL or localStorage
function checkForLoginData() {
    const urlParams = new URLSearchParams(window.location.search);
    const userDataParam = urlParams.get('user_data');
    const sessionTokenParam = urlParams.get('session_token');

    if (userDataParam && sessionTokenParam) {
        try {
            const userData = {
                user: JSON.parse(decodeURIComponent(userDataParam)),
                session_token: sessionTokenParam
            };
            
            // Store in localStorage for persistence
            localStorage.setItem('userData', JSON.stringify(userData));
            
            // Clean URL
            window.history.replaceState({}, document.title, window.location.pathname + '?api_key=' + API_KEY);
            
            return userData;
        } catch (e) {
            console.error('Error parsing user data from URL:', e);
        }
    }

    // Try to get from localStorage
    const storedData = localStorage.getItem('userData');
    if (storedData) {
        try {
            return JSON.parse(storedData);
        } catch (e) {
            console.error('Error parsing stored user data:', e);
            localStorage.removeItem('userData');
        }
    }

    return null;
}

// Update profile information in header
function updateProfileInfo() {
    if (!currentUser) return;

    const profileName = document.getElementById('profileName');
    const profileRole = document.getElementById('profileRole');
    const dropdownProfileName = document.getElementById('dropdownProfileName');
    const dropdownProfileEmail = document.getElementById('dropdownProfileEmail');
    const firstName = document.getElementById('dropdownProfileName')?.textContent.split(' ')[0] || 'Not available';
    const email = document.getElementById('dropdownProfileEmail')?.textContent || 'Not available';
    console.log('First Name:', firstName);
    console.log('Email:', email);
    
    if (profileName) profileName.textContent = fullName;
    if (profileRole) profileRole.textContent = role;
    if (dropdownProfileName) dropdownProfileName.textContent = fullName;
    if (dropdownProfileEmail) dropdownProfileEmail.textContent = currentUser.email || 'user@example.com';
}

// Validate API key and load data
async function validateAndLoadData() {
    try {
        // First, validate API key by making a simple API call to an endpoint that exists
        await validateApiKey();

        // Load students data
        await loadStudents();

        // Load courses for dropdown
        await loadCourses();

        // Update UI with application info
        updateApplicationInfo();

    } catch (error) {
        console.error('Validation/loading error:', error);
        throw error; // Re-throw to be handled by initializeApp
    }
}

async function validateApiKey() {
    try {
        // Use an existing endpoint to validate the API key
        const response = await makeApiCall('/api/students', 'GET');

        if (!response.success) {
            throw new Error(response.message || 'Invalid API key');
        }

        // If we have a session token, try to get user details
        if (sessionToken && (!currentUser || !currentUser.email)) {
            try {
                const userResponse = await makeApiCall('/api/auth/verify-session', 'POST', {
                    session_token: sessionToken
                });
                
                if (userResponse.success && userResponse.data.user) {
                    currentUser = {
                        ...currentUser,
                        ...userResponse.data.user
                    };
                    updateProfileDisplay(currentUser);
                    localStorage.setItem('userData', JSON.stringify(currentUser));
                }
            } catch (error) {
                console.warn('Could not verify session:', error);
                // Continue without session verification
            }
        }

        console.log('API key validated successfully');
        return response;

    } catch (error) {
        console.error('API key validation failed:', error);

        // Check the specific error type and throw appropriate error
        if (error.message.includes('403') || error.message.includes('Domain not allowed')) {
            throw new Error('Domain not allowed for this API key');
        } else if (error.message.includes('401') || error.message.includes('Invalid API key')) {
            throw new Error('Invalid or expired API key');
        } else if (error.message.includes('Network error')) {
            throw new Error('Unable to connect to the API server');
        } else {
            throw new Error('API key validation failed: ' + error.message);
        }
    }
}


function isApiKeyError(error) {
    const errorMessage = error.message.toLowerCase();
    return (
        errorMessage.includes('invalid') && errorMessage.includes('api key') ||
        errorMessage.includes('expired') && errorMessage.includes('api key') ||
        errorMessage.includes('unauthorized') ||
        errorMessage.includes('401') ||
        errorMessage.includes('403') ||
        errorMessage.includes('domain not allowed')
    );
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
            // Don't throw error for courses - it's not critical
        }
    } catch (error) {
        console.warn('Error loading courses:', error);
        // Don't throw error for courses - it's not critical
    }
}

// Make API call with better error handling
async function makeApiCall(endpoint, method = 'GET', data = null) {
    const url = BASE_URL + endpoint;
    const options = {
        method: method,
        headers: {
            'X-API-Key': API_KEY,
            'Accept': 'application/json'
        }
    };

    // Add session token if available
    if (sessionToken) {
        options.headers['X-Session-Token'] = sessionToken;
    }

    if (data && method !== 'GET') {
        if (data instanceof FormData) {
            options.body = data;
        } else if (typeof data === 'object') {
            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(data);
        } else {
            options.body = data;
        }
    }

    console.log('Making API call:', method, url, options);

    try {
        const response = await fetch(url, options);

        // Log response details for debugging
        console.log('Response status:', response.status);
        console.log('Response headers:', Object.fromEntries(response.headers.entries()));

        if (!response.ok) {
            const errorText = await response.text();
            console.error('API Error Response:', errorText);

            // Parse JSON error if possible
            let errorData;
            try {
                errorData = JSON.parse(errorText);
            } catch (e) {
                errorData = {
                    message: errorText || `HTTP ${response.status}`
                };
            }

            // Handle specific error cases with detailed messages
            if (response.status === 403) {
                throw new Error(errorData.message ||
                    'Domain not allowed for this API key. Please check your API key configuration.');
            } else if (response.status === 401) {
                throw new Error(errorData.message || 'Invalid API key or authentication failed.');
            } else if (response.status === 404) {
                throw new Error(errorData.message || `API endpoint not found: ${endpoint}`);
            } else if (response.status >= 500) {
                throw new Error(errorData.message || 'Server error. Please try again later.');
            } else {
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            }
        }

        const responseData = await response.json();
        console.log('API Response:', responseData);
        return responseData;

    } catch (error) {
        console.error('Fetch error:', error);

        if (error.name === 'TypeError' && error.message.includes('fetch')) {
            throw new Error(
                'Network error: Unable to connect to the API. Please check your internet connection and API server.'
                );
        }

        // If it's already our custom error, re-throw it
        if (error.message.includes('Domain not allowed') ||
            error.message.includes('Invalid API key') ||
            error.message.includes('API endpoint not found') ||
            error.message.includes('Server error')) {
            throw error;
        }

        // For other errors, wrap them
        throw new Error('Connection failed: ' + error.message);
    }
}

// Initialize or reinitialize DataTable
function initializeDataTable() {
    // Destroy existing DataTable if it exists
    if (dataTable) {
        dataTable.destroy();
        dataTable = null;
    }

    // Initialize DataTable
    dataTable = $('#userTable').DataTable({
        "pageLength": 10,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "searching": true,
        "ordering": true,
        "info": true,
        "paging": true,
        "responsive": true,
        "order": [[1, 'asc']], // Sort by Student ID column by default
        "columnDefs": [
            {
                "targets": [0, 6], // Checkbox and Action columns
                "orderable": false,
                "searchable": false
            }
        ],
        "language": {
            "search": "Search students:",
            "lengthMenu": "Show _MENU_ students per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ students",
            "infoEmpty": "No students found",
            "infoFiltered": "(filtered from _MAX_ total students)",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            },
            "emptyTable": "No student data available"
        },
        "drawCallback": function(settings) {
            // Update selection state after DataTable redraws
            updateSelectAll();
        }
    });
}

// Render students table
function renderStudentsTable() {
    const tbody = document.getElementById('studentsTableBody');
    tbody.innerHTML = '';

    if (studentsData.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td colspan="7" class="text-center py-4">
                <i class="fas fa-users fa-2x text-muted mb-3"></i>
                <p class="text-muted">No students found</p>
            </td>
        `;
        tbody.appendChild(row);
        
        // Initialize DataTable even with empty data
        initializeDataTable();
        return;
    }

    studentsData.forEach(student => {
        const row = createStudentRow(student);
        tbody.appendChild(row);
    });

    // Initialize DataTable after populating data
    initializeDataTable();
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
            <p>${student.last_name || 'N/A'}</p>
        </td>
        <td class="min-width">
            <p>${student.first_name || 'N/A'}</p>
        </td>
        <td class="min-width">
            <p>${student.email || 'N/A'}</p>
        </td>
        <td class="min-width">
            <p>${student.status || 'N/A'}</p>
        </td>
        <td>
            <div class="btn-group" role="group">
                <button class="btn btn-outline-primary btn-sm" onclick="viewStudent(${student.id})">
                    <i class="fas fa-edit me-1"></i> Edit
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
    const programSelect = document.getElementById('add_program');
    const programFilter = document.getElementById('programFilter');

    // Clear existing options
    programSelect.innerHTML = '<option value="">Select your program/course</option>';
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
    const programCounts = {};
    const yearCounts = {};
    const sectionCounts = {};
    const statusCounts = {};

    studentsData.forEach(student => {
        if (student.program) {
            programCounts[student.program] = (programCounts[student.program] || 0) + 1;
        }
        if (student.year) {
            yearCounts[student.year] = (yearCounts[student.year] || 0) + 1;
        }
        if (student.section) {
            sectionCounts[student.section] = (sectionCounts[student.section] || 0) + 1;
        }
        if (student.status) {
            statusCounts[student.status] = (statusCounts[student.status] || 0) + 1;
        }
    });

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

// Setup form handlers
function setupFormHandlers() {
    document.getElementById('addUserForm').addEventListener('submit', handleAddStudent);
    document.getElementById('viewStudentForm').addEventListener('submit', handleUpdateStudent);
    document.getElementById('batchUploadForm').addEventListener('submit', handleBatchUpload);
    document.getElementById('batchUploadFiles').addEventListener('change', handleFileSelection);
}

// Handle add student with loading state
async function handleAddStudent(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('addStudentBtn');
    const btnText = submitBtn.querySelector('.btn-text');

    // Start loading state
    setButtonLoading(submitBtn, 'Processing...');

    try {
        // Clear previous validation errors
        clearAllValidationErrors();

        const formData = new FormData(e.target);
        const studentData = {};
        for (let [key, value] of formData.entries()) {
            studentData[key] = value;
        }

        // Client-side validation
        const clientValidationErrors = validateStudentData(studentData);
        if (Object.keys(clientValidationErrors).length > 0) {
            showInlineValidationErrors(clientValidationErrors);
            focusFirstError();
            return;
        }

        console.log('Adding student with data:', studentData);

        const response = await makeApiCall('/api/students', 'POST', studentData);

        if (response.success) {
            showAlert('Student added successfully! Login credentials have been sent to their email.',
            'success');
            await loadStudents();
            e.target.reset();
            clearAllValidationErrors();
            bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
        } else {
            if (response.errors) {
                showInlineValidationErrors(response.errors);
                focusFirstError();
                return;
            } else {
                throw new Error(response.message || 'Failed to add student');
            }
        }
    } catch (error) {
        console.error('Error adding student:', error);
        showAlert('Failed to add student: ' + error.message, 'danger');
    } finally {
        // Always reset button state
        resetButtonLoading(submitBtn, '<i class="fas fa-plus me-1"></i>Add Student');
    }
}

// Handle update student with loading state
async function handleUpdateStudent(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('updateStudentBtn');

    // Start loading state
    setButtonLoading(submitBtn, 'Updating...');

    try {
        // Clear previous validation errors
        clearAllValidationErrors();

        const formData = new FormData(e.target);
        const studentId = formData.get('student_id');
        formData.delete('student_id');

        const studentData = {};
        for (let [key, value] of formData.entries()) {
            studentData[key] = value;
        }

        // Client-side validation
        const clientValidationErrors = validateStudentData(studentData, true);
        if (Object.keys(clientValidationErrors).length > 0) {
            showInlineValidationErrors(clientValidationErrors);
            focusFirstError();
            return;
        }

        console.log('Updating student:', studentId, 'with data:', studentData);

        const response = await makeApiCall(`/api/students/${studentId}`, 'PUT', studentData);

        if (response.success) {
            showAlert('Student updated successfully!', 'success');
            await loadStudents();
            clearAllValidationErrors();
            bootstrap.Modal.getInstance(document.getElementById('viewStudentModal')).hide();
        } else {
            if (response.errors) {
                showInlineValidationErrors(response.errors);
                focusFirstError();
                return;
            } else {
                throw new Error(response.message || 'Failed to update student');
            }
        }
    } catch (error) {
        console.error('Error updating student:', error);
        showAlert('Failed to update student: ' + error.message, 'danger');
    } finally {
        // Always reset button state
        resetButtonLoading(submitBtn, '<i class="fas fa-save me-1"></i>Update Student');
    }
}

// Handle batch upload with loading state
async function handleBatchUpload(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('startUploadBtn');

    // Start loading state
    setButtonLoading(submitBtn, 'Uploading...');

    try {
        const formData = new FormData(e.target);

        const response = await makeApiCall('/api/students/batch-upload', 'POST', formData);

        if (response.success) {
            showAlert('Batch upload completed successfully!', 'success');
            await loadStudents();
            e.target.reset();
            bootstrap.Modal.getInstance(document.getElementById('batchUploadModal')).hide();
        } else {
            throw new Error(response.message || 'Failed to upload batch');
        }
    } catch (error) {
        console.error('Error uploading batch:', error);
        showAlert('Failed to upload batch: ' + error.message, 'danger');
    } finally {
        // Always reset button state
        resetButtonLoading(submitBtn, '<i class="fas fa-upload me-1"></i>Start Batch Upload');
    }
}

// Set button loading state
function setButtonLoading(button, loadingText) {
    button.classList.add('btn-loading');
    button.disabled = true;

    // Update text if provided
    if (loadingText) {
        const textSpan = button.querySelector('.btn-text');
        if (textSpan) {
            textSpan.textContent = loadingText;
        }
    }
}

// Reset button loading state
function resetButtonLoading(button, originalText) {
    button.classList.remove('btn-loading');
    button.disabled = false;

    // Reset text
    if (originalText) {
        const textSpan = button.querySelector('.btn-text');
        if (textSpan) {
            textSpan.innerHTML = originalText;
        }
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

// Populate student modal with validation structure
function populateStudentModal(student) {
    const container = document.getElementById('studentDetailsContainer');
    container.innerHTML = `
        <div class="row">
            <!-- Email -->
            <div class="col-12 col-md-6">
                <div class="input-group-validation input-style-1">
                    <label for="edit_email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="edit_email" name="email" value="${student.email || ''}" required>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <!-- Student Number -->
            <div class="col-12 col-md-6">
                <div class="input-group-validation input-style-1">
                    <label for="edit_student_number" class="form-label">Student Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="edit_student_number" name="student_number" value="${student.student_number || ''}" required>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <!-- First Name -->
            <div class="col-12 col-md-4">
                <div class="input-group-validation input-style-1">
                    <label for="edit_first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="edit_first_name" name="first_name" value="${student.first_name || ''}" required>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <!-- Middle Name -->
            <div class="col-12 col-md-4">
                <div class="input-group-validation input-style-1">
                    <label for="edit_middle_name" class="form-label">Middle Name</label>
                    <input type="text" class="form-control" id="edit_middle_name" name="middle_name" value="${student.middle_name || ''}">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <!-- Last Name -->
            <div class="col-12 col-md-4">
                <div class="input-group-validation input-style-1">
                    <label for="edit_last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="edit_last_name" name="last_name" value="${student.last_name || ''}" required>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <!-- Year -->
            <div class="col-md-6 mb-3 select-style-1">
                <label>Year <span class="text-danger">*</span></label>
                <div class="select-position">
                    <select id="edit_year" class="form-control form-select select-position" name="year" required>
                        <option value="" disabled>Select Year</option>
                        <option value="1st Year" ${student.year === '1st Year' ? 'selected' : ''}>1st Year</option>
                        <option value="2nd Year" ${student.year === '2nd Year' ? 'selected' : ''}>2nd Year</option>
                        <option value="3rd Year" ${student.year === '3rd Year' ? 'selected' : ''}>3rd Year</option>
                        <option value="4th Year" ${student.year === '4th Year' ? 'selected' : ''}>4th Year</option>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <!-- Section -->
            <div class="col-md-6 mb-3 select-style-1">
                <label>Section <span class="text-danger">*</span></label>
                <div class="select-position">
                    <select id="edit_section" class="form-control form-select select-position" name="section" required>
                        <option value="">Select Section</option>
                        <option value="1" ${student.section == '1' ? 'selected' : ''}>1</option>
                        <option value="2" ${student.section == '2' ? 'selected' : ''}>2</option>
                        <option value="3" ${student.section == '3' ? 'selected' : ''}>3</option>
                        <option value="4" ${student.section == '4' ? 'selected' : ''}>4</option>
                        <option value="5" ${student.section == '5' ? 'selected' : ''}>5</option>
                        <option value="6" ${student.section == '6' ? 'selected' : ''}>6</option>
                        <option value="7" ${student.section == '7' ? 'selected' : ''}>7</option>
                        <option value="8" ${student.section == '8' ? 'selected' : ''}>8</option>
                        <option value="9" ${student.section == '9' ? 'selected' : ''}>9</option>
                        <option value="10" ${student.section == '10' ? 'selected' : ''}>10</option>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <!-- Program -->
            <div class="col-md-6 mb-3 select-style-1">
                <label>Program <span class="text-danger">*</span></label>
                <div class="select-position">
                    <select id="edit_program" class="form-control form-select" name="program" required>
                        <option value="">Select Program</option>
                        ${coursesData.map(course => 
                            `<option value="${course.course_name}" ${course.course_name === student.program ? 'selected' : ''}>${course.course_name}</option>`
                        ).join('')}
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <!-- Birthdate -->
            <div class="col-12 col-md-6">
                <div class="input-group-validation input-style-1">
                    <label for="edit_birthdate" class="form-label">Birthdate</label>
                    <input type="date" class="form-control" id="edit_birthdate" name="birthdate" value="${student.birthdate || ''}">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
        </div>
        <input type="hidden" name="student_id" value="${student.id}">
    `;

    // Setup real-time validation for the modal fields
    setupModalValidation();
}

// Setup real-time validation for modal
function setupModalValidation() {
    const modal = document.getElementById('viewStudentModal');
    const inputs = modal.querySelectorAll('input[required], select[required]');

    inputs.forEach(input => {
        input.addEventListener('input', function() {
            validateField(this);
        });

        input.addEventListener('blur', function() {
            validateField(this);
        });

        input.addEventListener('change', function() {
            validateField(this);
        });
    });
}

// Real-time field validation
function validateField(field) {
    const fieldName = field.name;
    const value = field.value.trim();
    const validationContainer = field.closest('.input-group-validation') || field.closest('.select-style-1');
    const errorElement = validationContainer.querySelector('.invalid-feedback');

    // Clear previous states
    field.classList.remove('is-valid', 'is-invalid');
    if (errorElement) {
        errorElement.textContent = '';
        errorElement.style.display = 'none';
    }

    let isValid = true;
    let errorMessage = '';

    // Validate based on field type
    switch (fieldName) {
        case 'first_name':
        case 'last_name':
            if (!value) {
                isValid = false;
                errorMessage = fieldName.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) + ' is required';
            } else if (value.length < 2) {
                isValid = false;
                errorMessage = fieldName.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) +
                    ' must be at least 2 characters';
            } else if (!/^[a-zA-Z\s]+$/.test(value)) {
                isValid = false;
                errorMessage = fieldName.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) +
                    ' can only contain letters and spaces';
            }
            break;

        case 'middle_name':
            if (value && !/^[a-zA-Z\s]+$/.test(value)) {
                isValid = false;
                errorMessage = 'Middle name can only contain letters and spaces';
            }
            break;

        case 'email':
            if (!value) {
                isValid = false;
                errorMessage = 'Email is required';
            } else if (!isValidEmail(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address';
            }
            break;

        case 'student_number':
            if (!value) {
                isValid = false;
                errorMessage = 'Student number is required';
            } else if (value.length < 5) {
                isValid = false;
                errorMessage = 'Student number must be at least 5 characters';
            } else if (!/^[A-Za-z0-9\-]+$/.test(value)) {
                isValid = false;
                errorMessage = 'Student number can only contain letters, numbers, and hyphens';
            }
            break;

        case 'program':
        case 'year':
        case 'section':
            if (!value) {
                isValid = false;
                errorMessage = fieldName.replace(/\b\w/g, l => l.toUpperCase()) + ' is required';
            }
            break;

        case 'birthdate':
            if (value) {
                const birthDate = new Date(value);
                const today = new Date();

                if (isNaN(birthDate.getTime())) {
                    isValid = false;
                    errorMessage = 'Please enter a valid birthdate';
                } else if (birthDate > today) {
                    isValid = false;
                    errorMessage = 'Birthdate cannot be in the future';
                } else {
                    const age = today.getFullYear() - birthDate.getFullYear();
                    const monthDiff = today.getMonth() - birthDate.getMonth();

                    let actualAge = age;
                    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                        actualAge--;
                    }

                    if (actualAge < 15) {
                        isValid = false;
                        errorMessage = 'Student must be at least 15 years old';
                    } else if (actualAge > 100) {
                        isValid = false;
                        errorMessage = 'Please enter a valid birthdate';
                    }
                }
            }
            break;
    }

    // Apply validation styling
    if (!isValid && errorElement) {
        field.classList.add('is-invalid');
        errorElement.textContent = errorMessage;
        errorElement.style.display = 'block';
    } else if ((field.hasAttribute('required') || value) && errorElement) {
        field.classList.add('is-valid');
    }

    return isValid;
}

// Setup real-time validation for all forms
function setupRealTimeValidation() {
    const addForm = document.getElementById('addUserForm');

    if (addForm) {
        const inputs = addForm.querySelectorAll(
            'input[required], select[required], input[type="email"], input[name="birthdate"]');

        inputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    validateField(this);
                }
            });

            input.addEventListener('blur', function() {
                validateField(this);
            });

            input.addEventListener('change', function() {
                validateField(this);
            });
        });
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
    if (!data.birthdate || !data.birthdate.trim()) {
    errors.birthdate = ['Birthdate is required.'];
    } else {
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

//Birthdate max date setup
const today = new Date();
const yyyy = today.getFullYear();
const mm = String(today.getMonth() + 1).padStart(2, '0');
const dd = String(today.getDate()).padStart(2, '0');
const maxDate = `${yyyy}-${mm}-${dd}`;
document.getElementById('add_birthdate').setAttribute('max', maxDate);

// Email validation helper
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Clear all validation errors
function clearAllValidationErrors() {
    document.querySelectorAll('.is-invalid').forEach(element => {
        element.classList.remove('is-invalid');
    });

    document.querySelectorAll('.is-valid').forEach(element => {
        element.classList.remove('is-valid');
    });

    document.querySelectorAll('.invalid-feedback').forEach(element => {
        element.style.display = 'none';
        element.textContent = '';
    });
}

// Show inline validation errors
function showInlineValidationErrors(errors) {
    Object.keys(errors).forEach(fieldName => {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (field) {
            const validationContainer = field.closest('.input-group-validation') || field.closest(
                '.select-style-1');
            const errorElement = validationContainer ?
                validationContainer.querySelector('.invalid-feedback') :
                field.parentNode.querySelector('.invalid-feedback');

            // Add red border to the field
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');

            // Get the error message(s)
            const errorMessages = Array.isArray(errors[fieldName]) ? errors[fieldName] : [errors[
            fieldName]];
            const errorMessage = errorMessages[0];

            // Show error message
            if (errorElement) {
                errorElement.style.display = 'block';
                errorElement.textContent = errorMessage;
            }
        }
    });
}

// Focus on first error field
function focusFirstError() {
    const firstErrorField = document.querySelector('.is-invalid');
    if (firstErrorField) {
        firstErrorField.focus();
        firstErrorField.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });
    }
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
                
                // Try immediate update first
                const updateSuccess = updateStudentStatusImmediately(studentId, action);
                
                // If immediate update fails, reload the entire table
                if (!updateSuccess) {
                    console.log('Immediate update failed, reloading table...');
                    await loadStudents();
                }
                
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

// Combined function to update student status immediately
function updateStudentStatusImmediately(studentId, action) {
    try {
        console.log('Attempting immediate update for student ID:', studentId, 'Action:', action);
        
        // Update data array
        const student = studentsData.find(s => s.id == studentId);
        if (!student) {
            console.error('Student not found in data array:', studentId);
            return false;
        }
        
        const newStatus = action === 'deactivate' ? 'Deactivated' : 'Active';
        student.status = newStatus;
        console.log('Updated student data:', student.first_name, student.last_name, 'New status:', newStatus);
        
        // Find the table row
        let row = document.getElementById(`user-${studentId}`);
        if (!row) {
            row = document.querySelector(`tr[data-user-id="${studentId}"]`);
        }
        
        if (!row) {
            console.error('Table row not found for student ID:', studentId);
            return false;
        }
        
        console.log('Found table row, updating...');
        
        // Update row data attributes
        row.setAttribute('data-status', newStatus.toLowerCase());
        row.setAttribute('data-user-status', newStatus);
        
        // Update checkbox
        const checkbox = row.querySelector('.user-checkbox');
        if (checkbox) {
            checkbox.setAttribute('data-status', newStatus);
        }
        
        // Update status cell (column 5)
        const statusCell = row.children[5];
        if (statusCell) {
            statusCell.innerHTML = `<p>${newStatus}</p>`;
        }
        
        // Update action buttons (column 6)
        const actionCell = row.children[6];
        if (actionCell) {
            actionCell.innerHTML = `
                <div class="btn-group" role="group">
                    <button class="btn btn-outline-primary btn-sm" onclick="viewStudent(${student.id})">
                        <i class="fas fa-edit me-1"></i> Edit
                    </button>
                    ${newStatus === 'Active' ? 
                        `<button class="btn btn-outline-danger btn-sm" onclick="toggleAccountStatus(${student.id}, 'deactivate')">
                                <i class="fas fa-ban me-1"></i> Deactivate
                            </button>` :
                        `<button class="btn btn-outline-success btn-sm" onclick="toggleAccountStatus(${student.id}, 'reactivate')">
                                <i class="fas fa-check-circle me-1"></i> Reactivate
                            </button>`
                    }
                </div>
            `;
        }
        
        // Update DataTable if it exists
        if (dataTable) {
            try {
                const dtRow = dataTable.row(row);
                if (dtRow && dtRow.length > 0) {
                    dtRow.invalidate().draw(false);
                } else {
                    dataTable.draw(false);
                }
            } catch (e) {
                console.error('DataTable update error:', e);
                dataTable.draw(false);
            }
        }
        
        // Update filter counts and selection
        updateFilterCounts();
        updateSelectAll();
        
        console.log('Immediate update completed successfully');
        return true;
        
    } catch (error) {
        console.error('Error in immediate update:', error);
        return false;
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
                
                // Try immediate updates first
                let allUpdatesSuccessful = true;
                selectedIds.forEach(studentId => {
                    const updateSuccess = updateStudentStatusImmediately(studentId, action);
                    if (!updateSuccess) {
                        allUpdatesSuccessful = false;
                    }
                });
                
                // If any immediate update failed, reload the entire table
                if (!allUpdatesSuccessful) {
                    console.log('Some immediate updates failed, reloading table...');
                    await loadStudents();
                }
                
                // Clear selection
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

// Updated filter functions to work with DataTables
function filterTable() {
    if (!dataTable) return;
    
    const filters = getActiveFilters();
    
    // Apply custom filtering to DataTable
    $.fn.dataTable.ext.search.pop(); // Remove previous custom filter
    
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        const row = dataTable.row(dataIndex).node();
        
        if (filters.program && $(row).attr('data-program') !== filters.program) {
            return false;
        }
        
        if (filters.year && $(row).attr('data-year') !== filters.year) {
            return false;
        }
        
        if (filters.section && $(row).attr('data-section') !== filters.section) {
            return false;
        }
        
        if (filters.status && $(row).attr('data-status') !== filters.status.toLowerCase()) {
            return false;
        }
        
        return true;
    });
    
    dataTable.draw();
}

function getActiveFilters() {
    return {
        program: document.getElementById('programFilter').value,
        year: document.getElementById('yearFilter').value,
        section: document.getElementById('sectionFilter').value,
        status: document.getElementById('accountStatusFilter').value
    };
}

// Selection functions updated for DataTables
function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const isChecked = selectAllCheckbox.checked;

    const visibleCheckboxes = getVisibleCheckboxes();
    visibleCheckboxes.forEach(checkbox => {
        checkbox.checked = isChecked;
    });

    updateSelectAll();
}

function getVisibleCheckboxes() {
    if (!dataTable) return [];
    
    const visibleRows = dataTable.rows({ page: 'current', search: 'applied' }).nodes();
    const checkboxes = [];
    
    $(visibleRows).each(function() {
        const checkbox = $(this).find('.user-checkbox')[0];
        if (checkbox) {
            checkboxes.push(checkbox);
        }
    });
    
    return checkboxes;
}

function selectAllVisible() {
    const visibleCheckboxes = getVisibleCheckboxes();
    visibleCheckboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    updateSelectAll();
}

function updateSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const visibleCheckboxes = getVisibleCheckboxes();
    const checkedVisibleBoxes = visibleCheckboxes.filter(cb => cb.checked);
    const bulkActionsBar = document.getElementById('bulkActionsBar');
    const selectedCount = document.getElementById('selectedCount');

    if (checkedVisibleBoxes.length === 0) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = false;
    } else if (checkedVisibleBoxes.length === visibleCheckboxes.length && visibleCheckboxes.length > 0) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = true;
    } else {
        selectAllCheckbox.indeterminate = true;
        selectAllCheckbox.checked = false;
    }

    const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');

    if (selectedCheckboxes.length > 0) {
        bulkActionsBar.style.display = 'block';
        selectedCount.textContent = selectedCheckboxes.length;
        updateBulkActionButtons(selectedCheckboxes);
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

    reactivateBtn.disabled = !hasDeactivated;
    deactivateBtn.disabled = !hasActive;
}

function clearSelection() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');

    selectAllCheckbox.checked = false;
    selectAllCheckbox.indeterminate = false;

    userCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
    });

    updateSelectAll();
}

// Utility functions
function updateApplicationInfo() {
    const urlParams = new URLSearchParams(window.location.search);
    const appName = urlParams.get('app_name') || 'External Student Management';
    const currentDomain = window.location.hostname;

    document.title = `${appName} - Student Management`;

    console.log(`Application: ${appName} running on ${currentDomain}`);
    console.log(`API Base URL: ${BASE_URL}`);
}

function showAlert(message, type = 'info') {
    const alertContainer = document.getElementById('messageContainer');

    // Clear existing alerts of the same type to prevent duplicates
    const existingAlerts = alertContainer.querySelectorAll(`.alert-${type}`);
    existingAlerts.forEach(alert => {
        alert.remove();
    });

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

    // Auto-remove after 4 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.classList.remove('show');
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 150); // Wait for fade transition
        }
    }, 4000);
}

function showLoading() {
    document.getElementById('preloader').style.display = 'flex';
}

function hideLoading() {
    document.getElementById('preloader').style.display = 'none';
}

function disconnectApi() {
    if (confirm('Are you sure you want to disconnect from the system?')) {
        window.location.href = window.location.pathname;
    }
}

// Update the preventBackAfterLogout function
function preventBackAfterLogout() {
    // Check if user just logged out
    const justLoggedOut = sessionStorage.getItem('justLoggedOut');
    
    if (justLoggedOut === 'true') {
        // Clear the flag
        sessionStorage.removeItem('justLoggedOut');
        
        // If someone tries to access with API key after logout, block it
        const urlParams = new URLSearchParams(window.location.search);
        const apiKey = urlParams.get('api_key');
        
        if (apiKey) {
            // Remove API key and redirect to instructions
            window.history.replaceState(null, '', window.location.pathname);
            window.location.href = window.location.pathname;
            return true;
        }
    }
    return false;
}

// Add this to your DOMContentLoaded event
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

    // Determine base URL dynamically
    BASE_URL = determineBaseUrl(urlParams);
    console.log('Using BASE_URL:', BASE_URL);

    // Initialize the application
    initializeApp();
});

window.addEventListener('pageshow', function(event) {
    // Only redirect if explicitly logged out and no session token in URL
    const justLoggedOut = sessionStorage.getItem('justLoggedOut');
    const urlParams = new URLSearchParams(window.location.search);
    const hasSessionToken = urlParams.get('session_token');
    
    if (justLoggedOut === 'true' && !hasSessionToken && window.location.pathname.includes('student-management')) {
        sessionStorage.removeItem('justLoggedOut');
        const apiKey = urlParams.get('api_key');
        if (apiKey) {
            const loginUrl = `${window.location.protocol}//${window.location.host}/external/login?api_key=${apiKey}`;
            window.location.replace(loginUrl);
        }
    }
});

async function logoutUser() {
    try {
        // Show confirmation dialog
        const result = await Swal.fire({
            title: 'Logout Confirmation',
            text: 'Are you sure you want to logout?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Logout',
            cancelButtonText: 'Cancel'
        });

        if (!result.isConfirmed) {
            return;
        }

        showLoading();

        // Call logout API if we have session token
        if (sessionToken) {
            try {
                await makeApiCall('/api/auth/logout', 'POST', {
                    session_token: sessionToken
                });
            } catch (error) {
                console.warn('Logout API call failed:', error);
            }
        }

        // Clear ALL stored data
        localStorage.removeItem('userData');
        localStorage.removeItem('sessionToken');
        localStorage.removeItem('hasConnectedBefore');
        sessionStorage.clear(); // Clear all session storage
        
        // Set logout flag
        sessionStorage.setItem('justLoggedOut', 'true');
        
        currentUser = null;
        sessionToken = null;

        // Show success message
        showAlert('Logged out successfully!', 'success');

        // Redirect to login page with current API key
        setTimeout(() => {
            const loginUrl = `${BASE_URL}/external/login?api_key=${API_KEY}`;
            window.location.replace(loginUrl);
        }, 1500);

    } catch (error) {
        console.error('Logout error:', error);
        showAlert('Logout completed (with warnings)', 'warning');
        
        // Force logout
        localStorage.clear();
        sessionStorage.clear();
        sessionStorage.setItem('justLoggedOut', 'true');
        
        setTimeout(() => {
            const loginUrl = `${BASE_URL}/external/login?api_key=${API_KEY}`;
            window.location.replace(loginUrl);
        }, 1500);
    } finally {
        hideLoading();
    }
}

// Prevent browser back button after logout
(function() {
    // Check if user just logged out
    const justLoggedOut = sessionStorage.getItem('justLoggedOut');
    if (justLoggedOut === 'true') {
        
        // Check if this is a fresh login (has session_token)
        const urlParams = new URLSearchParams(window.location.search);
        const hasSessionToken = urlParams.get('session_token');
        
        // If this is NOT a fresh login, then redirect
        if (!hasSessionToken && window.location.pathname.includes('student-management')) {
            // Clear the flag
            sessionStorage.removeItem('justLoggedOut');
            
            // Extract API key and redirect to login
            const apiKey = urlParams.get('api_key');
            if (apiKey) {
                const loginUrl = `${window.location.protocol}//${window.location.host}/external/login?api_key=${apiKey}`;
                window.location.replace(loginUrl);
                return;
            }
        } else if (hasSessionToken) {
            // This is a fresh login, clear the logout flag
            sessionStorage.removeItem('justLoggedOut');
        }
    }
})();

// Function to handle login success redirect
function handleLoginSuccess(apiKey, sessionToken, userData) {
    // Store the session data
    if (sessionToken) {
        localStorage.setItem('sessionToken', sessionToken);
    }
    if (userData) {
        localStorage.setItem('userData', JSON.stringify(userData));
    }
    
    // Redirect to student management with API key
    const studentManagementUrl = `${window.location.protocol}//${window.location.host}/external/student-management?api_key=${apiKey}`;
    window.location.replace(studentManagementUrl);
}

// Handle page visibility changes (when user tries to go back)
document.addEventListener('DOMContentLoaded', function() {
    // Hide preloader
    setTimeout(() => {
        document.getElementById('preloader').style.display = 'none';
    }, 1000);

    // Get URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    API_KEY = urlParams.get('api_key');

    if (!API_KEY) {
        showAlert('API key is required. Please add ?api_key=YOUR_API_KEY to the URL.', 'danger');
        return;
    }

    // Extract user data from URL if available (from login redirect)
    const urlSessionToken = urlParams.get('session_token');
    const userId = urlParams.get('user_id');
    const userRole = urlParams.get('user_role');
    const appName = urlParams.get('app_name');

    // Determine base URL dynamically
    BASE_URL = determineBaseUrl(urlParams);
    console.log('Using BASE_URL:', BASE_URL);

    // If we have session data in URL, this is a fresh login - handle it properly
    if (urlSessionToken && userId) {
        console.log('Fresh login detected, processing session data...');
        
        sessionToken = urlSessionToken;
        
        // Create user object from URL params
        currentUser = {
            id: userId,
            first_name: userRole,
            email: '',
            role: userRole
        };
        
        // Store in localStorage
        localStorage.setItem('sessionToken', sessionToken);
        localStorage.setItem('userData', JSON.stringify(currentUser));
        
        // Update profile display immediately
        updateProfileDisplay(currentUser);
        
        // Clear any logout flags since this is a fresh login
        sessionStorage.removeItem('justLoggedOut');
        
        // Initialize the application first, then clean URL
        initializeAppWithSession().then(() => {
            // Clean the URL after successful initialization
            const cleanUrl = `${window.location.pathname}?api_key=${API_KEY}`;
            window.history.replaceState({}, document.title, cleanUrl);
        });
        
    } else {
        // Check for stored session data
        const storedSessionToken = localStorage.getItem('sessionToken');
        const storedUserData = localStorage.getItem('userData');
        
        if (storedSessionToken && storedUserData) {
            sessionToken = storedSessionToken;
            currentUser = JSON.parse(storedUserData);
            updateProfileDisplay(currentUser);
        }
        
        // Initialize the application normally
        initializeApp();
    }
});

//Separate function for handling fresh login
async function initializeAppWithSession() {
    try {
        console.log('Initializing app with fresh session...');
        
        // Validate API key and load initial data
        await validateAndLoadData();

        // Set up form handlers
        setupFormHandlers();

        // Setup real-time validation
        setupRealTimeValidation();

        // Show success alert for fresh login
        showAlert('Logged in successfully!', 'success');
        
        console.log('Fresh login initialization completed');
        
    } catch (error) {
        console.error('Fresh login initialization error:', error);

        if (isApiKeyError(error)) {
            showAlert('API Authentication failed: ' + error.message, 'danger');
        } else {
            showAlert('Failed to connect to the system. Please check your connection and try again.', 'danger');
        }
    }
}