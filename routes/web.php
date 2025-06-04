<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\CourseDepartmentController;
use App\Http\Controllers\UserValidationController;
use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\Admin\ApiKeyController;
use App\Http\Controllers\Auth\UserLoginController;
use App\Http\Controllers\External\ExternalStudentController;

// Routes that should redirect logged-in admins
Route::middleware(['redirect.if.admin'])->group(function () {
    
    // Show the home page
    Route::get('/', function () {
    return view('welcome');
    })->name('home');

    // Show the login page 
    Route::get('/login', [PublicController::class, 'showLoginPage'])->name('login');
    
    // Show the sign-up page
    Route::get('/sign-up', [PublicController::class, 'showSignUpPage'])->name('sign-up');
    
    // Forgot password pages
    Route::get('/forgot-password', [PublicController::class, 'showForgotPasswordPage'])->name('forgot-password');
    Route::get('reset-password/{token}', [PublicController::class, 'showResetForm'])->name('password.reset');
});

// Public routes that don't need redirection (POST routes)
Route::post('loginPost', [PublicController::class, 'loginPost'])->name('loginPost');
Route::post('/register', [PublicController::class, 'store']);
Route::post('forgot-password', [PublicController::class, 'sendResetLinkEmail'])->name('password.email');
Route::post('reset-password', [PublicController::class, 'reset'])->name('password.update');

Route::prefix('external')->name('external.')->group(function () {
    // Show login form for external applications
    Route::get('/login', [UserLoginController::class, 'showLoginForm'])->name('login');
    
    // Process login for external applications (via web form)
    Route::post('/login', [UserLoginController::class, 'login'])->name('login.process');

     Route::get('/student-management', [ExternalStudentController::class, 'index'])->name('student-management');
});


// For testing purposes - direct access to login with API key
Route::get('/test-login/{apiKey}', function($apiKey) {
    return redirect()->route('external.login', ['api_key' => $apiKey]);
})->name('test.login');

Route::prefix('errors')->name('errors.')->group(function () {
    Route::get('api-key-required', function () {
        return view('errors.api-key-required');
    })->name('api-key-required');

    Route::get('invalid-api-key', function () {
        return view('errors.invalid-api-key');
    })->name('invalid-api-key');

    Route::get('domain-not-allowed', function () {
        return view('errors.domain-not-allowed');
    })->name('domain-not-allowed');

    Route::get('no-login-permission', function () {
        return view('errors.no-login-permission');
    })->name('no-login-permission');
});

// API documentation route (optional)
Route::get('/api/docs', function () {
    return response()->json([
        'title' => 'PUP-Taguig Student Management API',
        'version' => '1.0.0',
        'description' => 'External API for student management operations',
        'base_url' => url('/api'),
        'authentication' => [
            'type' => 'API Key',
            'header' => 'X-API-Key',
            'description' => 'Include your API key in the X-API-Key header for all requests'
        ],
        'test_endpoints' => [
            'health_check' => url('/api/health'),
            'verify_api_key' => url('/api/verify-api-key'),
            'students_list' => url('/api/students'),
            'courses_list' => url('/api/courses'),
        ],
        'external_interface' => [
            'student_management' => url('/external/students'),
            'login_form' => url('/external/login'),
        ],
        'usage_examples' => [
            'direct_html_access' => url('/external/students') . '?api_key=YOUR_API_KEY',
            'with_base_url' => url('/external/students') . '?api_key=YOUR_API_KEY&base_url=http://127.0.0.1:8000',
            'with_app_name' => url('/external/students') . '?api_key=YOUR_API_KEY&app_name=Test App',
        ],
        'localhost_testing' => [
            'generate_api_key' => 'Go to Admin Panel → API Keys → Generate New',
            'test_health' => 'GET ' . url('/api/health'),
            'test_students' => 'GET ' . url('/api/students') . ' (with X-API-Key header)',
            'access_interface' => url('/external/students') . '?api_key=YOUR_GENERATED_KEY',
        ]
    ]);
})->name('api.docs');

// Admin protected routes
Route::middleware(['admin.auth'])->group(function () {
    // Admin logout
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');

    // Admin dashboard page
    Route::get('/dashboard', [AdminController::class, 'dashboardPage'])->name('admin.dashboard');
    
    // Show the total faculty page
    Route::get('/dashboard/faculty', [AdminController::class, 'totalFacultyPage'])->name('admin.total-faculty');
    
    // Show the total faculty details page
    Route::get('/dashboard/faculty/{user}', [AdminController::class, 'viewTotalFaculty'])->name('admin.dashboard.view-total-faculty');
    
    // Show the total student page
    Route::get('/dashboard/student', [AdminController::class, 'totalStudentPage'])->name('admin.total-student');

    // Show User Management Page
    Route::get('/user-management', [UserManagementController::class, 'userManagementPage'])->name('admin.user-management.users');
    
    // Show Faculty Page
    Route::get('/user-management/faculty', [UserManagementController::class, 'facultyPage'])->name('admin.user-management.faculty');
    
    // Show Faculty Details
    Route::get('/user-management/faculty/{user}', [UserManagementController::class, 'viewFaculty'])->name('admin.user-management.view-faculty');
    
    // Update Faculty Details
    Route::put('/user-management/faculty/{user}/update', [UserManagementController::class, 'updateFaculty'])->name('admin.user-management.update-faculty');
    Route::post('/students/store', [UserManagementController::class, 'storeStudent'])->name('admin.user-management.store-student');

    // Show Student Page
    Route::get('/user-management/student', [UserManagementController::class, 'studentPage'])->name('admin.user-management.student');
    
    // View Student Details
    Route::get('/user-management/student/{user}', [UserManagementController::class, 'viewStudent'])->name('admin.user-management.view-student');
    
    // Show Total Student Details
    Route::get('/dashboard/student/{user}', [AdminController::class, 'viewTotalStudent'])->name('admin.dashboard.view-total-student');
    
    // Update Student Details
    Route::put('/admin/user-management/update-student/{id}', [UserManagementController::class, 'updateStudent'])->name('admin.user-management.update-student');

    // Deactivate and Activate Account

    Route::post('/admin/toggle-user-status/{userId}', [UserManagementController::class, 'toggleUserStatus']);
        Route::post('/bulk-toggle-user-status', [UserManagementController::class, 'bulkToggleUserStatus'])->name('bulk-toggle-user-status');
    // Show Course and Department Page

    
    Route::get('/settings/course-department', [CourseDepartmentController::class, 'courseDepartmentPage'])->name('admin.settings.course');
    
    // Show Department Page
    Route::get('/settings/department', [CourseDepartmentController::class, 'DepartmentPage'])->name('admin.settings.department');
    
    // Store Department
    Route::post('/settings/department/store', [CourseDepartmentController::class, 'storeDepartment'])->name('admin.settings.department.store');
    
    // Store Course
    Route::post('/store-course', [CourseDepartmentController::class, 'storeCourse'])->name('store.course');
    
    // Destroy Course
    Route::delete('/courses/{id}', [CourseDepartmentController::class, 'destroyCourse'])->name('courses.destroy');

    //Delete Department
    Route::delete('/departments/{id}', [CourseDepartmentController::class, 'destroyDepartment'])->name('departments.destroy');

    // Update Course
    Route::put('/courses/{id}/update', [CourseDepartmentController::class, 'updateCourse'])->name('courses.update');

    // Download template for student import
    Route::get('/user-management/download-template', [UserManagementController::class, 'downloadTemplate'])->name('admin.user-management.download-template');

    // Store Faculty
    Route::post('/faculty/store', [UserManagementController::class, 'storeFaculty'])->name('admin.user-management.store-faculty');

    // Batch upload 
    Route::post('/batch-upload-students', [UserManagementController::class, 'batchUploadStudents'])->name('batch-upload-students');
    Route::post('/admin/batch-upload-faculty', [UserManagementController::class, 'batchUploadFaculty'])->name('batch-upload-faculty');
    Route::post('/batch-upload-students', [UserManagementController::class, 'batchUploadStudents'])->name('batch-upload-students');

    // Download template for faculty import
    Route::get('/user-management/download-faculty-template', [UserManagementController::class, 'downloadFacultyTemplate'])->name('admin.user-management.download-faculty-template');

    // Export all faculty data
    Route::get('/user-management/export-faculty', [UserManagementController::class, 'exportFaculty'])->name('admin.user-management.export-faculty');

    // Export filtered faculty data
    Route::get('/user-management/export-filtered-faculty', [UserManagementController::class, 'exportFilteredFaculty'])->name('admin.user-management.export-filtered-faculty');

    // Export all students data
    Route::get('/user-management/export-students', [UserManagementController::class, 'exportStudents'])
        ->name('admin.user-management.export-students');

    // Export filtered students data
    Route::get('/user-management/export-filtered-students', [UserManagementController::class, 'exportFilteredStudents'])->name('admin.user-management.export-filtered-students');
    Route::post('/admin/bulk-toggle-user-status', [UserManagementController::class, 'bulkToggleUserStatus'])->name('admin.bulk-toggle-user-status');

    //Update Department
    Route::put('/admin/settings/department/{id}/update', [CourseDepartmentController::class, 'updateDepartment'])->name('admin.settings.department.update');
    //Add Department
    Route::post('/admin/settings/department/{id}/toggle-status', [CourseDepartmentController::class, 'toggleDepartmentStatus'])->name('admin.settings.department.toggle-status');
    Route::post('/courses/{id}/toggle-status', [CourseDepartmentController::class, 'toggleCourseStatus']);

     // User Validation Routes
    Route::get('/settings/user-validation', [UserValidationController::class, 'showUserValidation'])->name('admin.settings.user-validation');
    Route::post('/settings/user-validation/update-student', [UserValidationController::class, 'updateStudentValidation'])->name('admin.settings.user-validation.update-student');
    Route::post('/settings/user-validation/update-employee', [UserValidationController::class, 'updateEmployeeValidation'])->name('admin.settings.user-validation.update-employee');

    // Show the deactivated users page
    Route::get('/dashboard/deactivated-users', [AdminController::class, 'deactivatedUsersPage'])->name('admin.deactivated-users');

    // Show deactivated user details
    Route::get('/dashboard/deactivated-users/{user}', [AdminController::class, 'viewDeactivatedUser'])->name('admin.dashboard.view-deactivated-user');

    // Reactivate single user
    Route::post('/admin/reactivate-user/{userId}', [AdminController::class, 'reactivateUser'])->name('admin.reactivate-user');

    // Bulk reactivate users
    Route::post('/admin/bulk-reactivate-users', [AdminController::class, 'bulkReactivateUsers'])->name('admin.bulk-reactivate-users');

    // Admin Profile Routes
    Route::get('/admin/profile', [AdminController::class, 'showProfile'])->name('admin.profile');
    Route::put('/admin/profile/update', [AdminController::class, 'updateProfile'])->name('admin.profile.update');
    Route::put('/admin/profile/update-password', [AdminController::class, 'updatePassword'])->name('admin.profile.update-password');

     // Audit Trail Logs
    Route::get('/audit-trails', [AuditTrailController::class, 'auditTrailPage'])->name('admin.audit-trail.audit-trail');
    Route::get('/audit-trails/{id}', [AuditTrailController::class, 'show'])->name('audit.trails.show');

    // Export Audit Logs
    Route::get('/audit-trails-export', [AuditTrailController::class, 'export'])->name('audit.trails.export');

    // Audit Trail Statistics (for dashboard)
    Route::get('/audit-trails/statistics', [AuditTrailController::class, 'getStatistics'])->name('audit.trails.statistics');

    // Cleanup Old Logs
    Route::post('/audit-trails/cleanup', [AuditTrailController::class, 'cleanOldLogs'])->name('audit.trails.cleanup');

    // Batch Upload Logs
    Route::get('/batch-uploads', [AuditTrailController::class, 'batchUploads'])->name('batch.uploads.index');
    Route::get('/batch-uploads/{id}', [AuditTrailController::class, 'showBatchUpload'])->name('batch.uploads.show');

    Route::get('/admin/api-keys', [ApiKeyController::class, 'index'])->name('admin.api-keys.index');
    Route::get('/admin/api-keys/create', [ApiKeyController::class, 'create'])->name('admin.api-keys.create');
    Route::post('/admin/api-keys', [ApiKeyController::class, 'store'])->name('admin.api-keys.store');
    Route::get('/admin/api-keys/{apiKey}', [ApiKeyController::class, 'show'])->name('admin.api-keys.show');
    Route::get('/admin/api-keys/{apiKey}/edit', [ApiKeyController::class, 'edit'])->name('admin.api-keys.edit');
    Route::put('/admin/api-keys/{apiKey}', [ApiKeyController::class, 'update'])->name('admin.api-keys.update');
    Route::delete('/admin/api-keys/{apiKey}', [ApiKeyController::class, 'destroy'])->name('admin.api-keys.destroy');
    
    // API Key Actions
    Route::post('/admin/api-keys/{apiKey}/toggle', [ApiKeyController::class, 'toggle'])->name('admin.api-keys.toggle');
    Route::post('/admin/api-keys/{apiKey}/regenerate', [ApiKeyController::class, 'regenerate'])->name('admin.api-keys.regenerate');
    Route::post('/admin/api-keys/{apiKey}/test', [ApiKeyController::class, 'test'])->name('admin.api-keys.test');
    Route::post('/{apiKey}/send-email', [ApiKeyController::class, 'sendByEmail'])->name('admin.api-keys.send-email');
    
    // Statistics and Export
    Route::get('/admin/api-keys-stats', [ApiKeyController::class, 'statistics'])->name('admin.api-keys.statistics');
    Route::get('/admin/api-keys-export', [ApiKeyController::class, 'export'])->name('admin.api-keys.export');
    
});