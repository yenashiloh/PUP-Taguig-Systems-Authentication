<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StudentApiController;
use App\Http\Controllers\Auth\UserLoginController;
use App\Http\Controllers\Api\FacultyController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned the "api" middleware group. Make something great!
|
*/
Route::prefix('auth')->middleware(['api.key'])->group(function () {
    Route::post('/login', [UserLoginController::class, 'login'])->name('api.user.login');
      Route::post('/logout', [UserLoginController::class, 'logout'])->name('api.user.logout');
    Route::post('/verify-session', [UserLoginController::class, 'verifySession'])->name('api.user.verify');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Health check endpoint (no API key required)
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'PUP-Taguig API is running',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
});

// API key validation endpoint
Route::middleware(['api.key'])->get('/verify-api-key', function (Request $request) {
    $apiKey = $request->apiKeyModel;
    
    return response()->json([
        'success' => true,
        'message' => 'API key is valid',
        'data' => [
            'application_name' => $apiKey->application_name,
            'developer_name' => $apiKey->developer_name,
            'permissions' => $apiKey->formatted_permissions,
            'rate_limit' => $apiKey->request_limit_per_minute,
            'expires_at' => $apiKey->expires_at ? $apiKey->expires_at->toISOString() : null
        ]
    ]);
});

// External user authentication routes (requires API key with login_user permission)
Route::middleware(['api.key', 'api.permission:login_user'])->group(function () {
    Route::get('/external/login', [UserLoginController::class, 'showLoginForm']);
    Route::post('/user/login', [UserLoginController::class, 'login']);
    Route::post('/user/logout', [UserLoginController::class, 'logout']);
    Route::post('/user/verify-session', [UserLoginController::class, 'verifySession']);
});

// Student Management API Routes (requires API key)
Route::group(['middleware' => ['api.key']], function () {
    
    // Student Management API Routes
    Route::prefix('students')->group(function () {
        // GET routes
        Route::get('/', [StudentApiController::class, 'index']);
        Route::get('/courses', [StudentApiController::class, 'getCourses']);
        Route::get('/departments', [StudentApiController::class, 'getDepartments']);
        Route::get('/download-template', [StudentApiController::class, 'downloadTemplate']);
        Route::get('/export', [StudentApiController::class, 'export']);
        Route::get('/export-filtered', [StudentApiController::class, 'exportFiltered']);
        Route::get('/{id}', [StudentApiController::class, 'show']);
        
        // POST routes
        Route::post('/', [StudentApiController::class, 'store']);
        Route::post('/batch-upload', [StudentApiController::class, 'batchUpload']);
        Route::post('/bulk-toggle-status', [StudentApiController::class, 'bulkToggleStatus']);
        Route::post('/{id}/toggle-status', [StudentApiController::class, 'toggleStatus']);
        
        // PUT/PATCH routes for updates (both supported)
        Route::put('/{id}', [StudentApiController::class, 'update']);
        Route::patch('/{id}', [StudentApiController::class, 'update']);
        
        // Also support POST with _method=PUT for form submissions
        Route::post('/{id}', [StudentApiController::class, 'update']);
    });

    // Application info
    Route::get('/app-info', [StudentApiController::class, 'getAppInfo']);
});

Route::put('/students/{id}', [StudentApiController::class, 'update']);
Route::patch('/students/{id}', [StudentApiController::class, 'update']);

// Error handling for API routes
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found',
        'available_endpoints' => [
            // General endpoints
            'GET /api/health' => 'Health check',
            'GET /api/verify-api-key' => 'Verify API key (requires X-API-Key header)',
            'GET /api/app-info' => 'Get application information',
            
            // Student endpoints
            'GET /api/students' => 'Get all students',
            'POST /api/students' => 'Create student (requires add_user permission)',
            'GET /api/students/{id}' => 'Get student details',
            'PUT /api/students/{id}' => 'Update student (requires update_user permission)',
            'POST /api/students/{id}/toggle-status' => 'Toggle student status (requires deactivate_user permission)',
            'POST /api/students/bulk-toggle-status' => 'Bulk toggle student status (requires deactivate_user permission)',
            'POST /api/students/batch-upload' => 'Batch upload students (requires add_user permission)',
            'GET /api/students/export' => 'Export all students',
            'GET /api/students/export-filtered' => 'Export filtered students',
            'GET /api/students/download-template' => 'Download import template',
            'GET /api/students/courses' => 'Get all courses',
            'GET /api/students/departments' => 'Get all departments',
            
            // Faculty endpoints
            'GET /api/faculty' => 'Get all faculty',
            'POST /api/faculty' => 'Create faculty member (requires add_user permission)',
            'GET /api/faculty/{id}' => 'Get faculty details',
            'PUT /api/faculty/{id}' => 'Update faculty member (requires update_user permission)',
            'POST /api/faculty/{id}/toggle-status' => 'Toggle faculty status (requires deactivate_user permission)',
            'GET /api/faculty/export' => 'Export faculty data',
            'GET /api/faculty/departments' => 'Get all departments',
            
            // Authentication endpoints
            'POST /api/auth/login' => 'User login (requires login_user permission)',
            'POST /api/auth/logout' => 'User logout (requires logout_user permission)',
            'POST /api/auth/verify-session' => 'Verify user session (requires login_user permission)',
        ],
        'note' => 'All endpoints except /health require a valid API key in X-API-Key header',
        'permissions' => [
            'add_user' => 'Create new users and batch upload functionality',
            'update_user' => 'Update user information and details',
            'deactivate_user' => 'Activate/deactivate user accounts',
            'login_user' => 'Authenticate faculty/students via API',
            'logout_user' => 'End user sessions via API'
        ]
    ], 404);
});

Route::group(['prefix' => 'faculty', 'middleware' => ['api.key']], function () {
    
    // Get all faculty (with filters and pagination)
    Route::get('/', [FacultyController::class, 'index']);
    
    // Get specific faculty member
    Route::get('{id}', [FacultyController::class, 'show']);
    
    // Create new faculty member (requires add_user permission)
    Route::middleware(['api.permission:add_user'])->post('/', [FacultyController::class, 'store']);
    
    // Update faculty member (requires update_user permission)
    Route::middleware(['api.permission:update_user'])->put('{id}', [FacultyController::class, 'update']);
    Route::middleware(['api.permission:update_user'])->patch('{id}', [FacultyController::class, 'update']);
    
    // Deactivate faculty member (requires deactivate_user permission)
    Route::middleware(['api.permission:deactivate_user'])->delete('{id}', [FacultyController::class, 'destroy']);
    
    // Reactivate faculty member (requires deactivate_user permission)
    Route::middleware(['api.permission:deactivate_user'])->patch('{id}/reactivate', [FacultyController::class, 'reactivate']);
    
    // Batch upload faculty (requires add_user permission)
    Route::middleware(['api.permission:add_user'])->post('batch-upload', [FacultyController::class, 'batchUpload']);
    
    // Bulk operations (requires deactivate_user permission)
    Route::middleware(['api.permission:deactivate_user'])->post('bulk/toggle-status', [FacultyController::class, 'bulkToggleStatus']);
    
    // Statistics (no special permission required, just API key)
    Route::get('statistics/overview', [FacultyController::class, 'statistics']);
    
    // Download template for faculty import (requires add_user permission)
    Route::middleware(['api.permission:add_user'])->get('download-template', [FacultyController::class, 'downloadTemplate']);
    
    // Export faculty data (no special permission required, just API key)
    Route::get('export', [FacultyController::class, 'export']);
    Route::get('export-filtered', [FacultyController::class, 'exportFiltered']);
    
    // Get departments for dropdown (no special permission required)
    Route::get('data/departments', [FacultyController::class, 'getDepartments']);
});

