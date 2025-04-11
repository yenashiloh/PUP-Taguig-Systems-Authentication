<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\CourseDepartmentController;

// Show the home page
Route::get('/', function () {
return view('welcome');
});

// Show the login page
Route::get('/login', [PublicController::class, 'showLoginPage'])->name('login');
//Login Post
Route::post('loginPost', [PublicController::class, 'loginPost'])->name('loginPost');

// Show the sign-up page
Route::get('/sign-up', [PublicController::class, 'showSignUpPage'])->name('sign-up');
// Store the user data
Route::post('/register', [PublicController::class, 'store']);

//Forgot password
Route::get('/forgot-password', [PublicController::class, 'showForgotPasswordPage'])->name('forgot-password');
//Show the reset password email link
Route::post('forgot-password', [PublicController::class, 'sendResetLinkEmail'])->name('password.email');
// Show the reset password page
Route::get('reset-password/{token}', [PublicController::class, 'showResetForm'])->name('password.reset');
// Reset the password
Route::post('reset-password', [PublicController::class, 'reset'])->name('password.update');


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
    //Update Faculty Details
    Route::put('/user-management/faculty/{user}/update', [UserManagementController::class, 'updateFaculty'])->name('admin.user-management.update-faculty');


    // Show Student Page
    Route::get('/user-management/student', [UserManagementController::class, 'studentPage'])->name('admin.user-management.student');
    //View Student Details
    Route::get('/user-management/student/{user}', [UserManagementController::class, 'viewStudent'])->name('admin.user-management.view-student');
    //Show Total Student Details
    Route::get('/dashboard/student/{user}', [AdminController::class, 'viewTotalStudent'])->name('admin.dashboard.view-total-student');
    //Update Student Details
    Route::put('/admin/user-management/update-student/{id}', [UserManagementController::class, 'updateStudent'])->name('admin.user-management.update-student');

    //Deactivate and Activate Account
    Route::post('/admin/toggle-user-status/{userId}', [UserManagementController::class, 'toggleUserStatus']);

    
    //Show Course and Department Page
    Route::get('/settings/course-department', [CourseDepartmentController::class, 'courseDepartmentPage'])->name('admin.settings.course');
    //Show Course and Department Page
    Route::get('/settings/department', [CourseDepartmentController::class, 'DepartmentPage'])->name('admin.settings.department');
    //Store Department
    Route::post('/settings/department/store', [CourseDepartmentController::class, 'storeDepartment'])->name('admin.settings.department.store');
    //Store Course
    Route::post('/store-course', [CourseDepartmentController::class, 'storeCourse'])->name('store.course');
    //Destroy Course
    Route::delete('/courses/{id}', [CourseDepartmentController::class, 'destroyCourse'])->name('courses.destroy');

    Route::delete('/departments/{id}', [CourseDepartmentController::class, 'destroyDepartment'])->name('departments.destroy');


});