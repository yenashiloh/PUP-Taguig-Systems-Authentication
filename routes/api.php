<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StudentApiController;
use App\Http\Controllers\Auth\UserLoginController;

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
Route::middleware(['api.key'])->prefix('students')->group(function () {
    
    // Basic student operations (requires basic permissions)
    Route::get('/', [StudentApiController::class, 'index']);
    Route::get('/{id}', [StudentApiController::class, 'show']);
    
    // Student creation (requires add_user permission)
    Route::middleware(['api.permission:add_user'])->group(function () {
        Route::post('/', [StudentApiController::class, 'store']);
        Route::post('/batch-upload', [StudentApiController::class, 'batchUpload']);
    });
    
    // Download template (no additional permission required)
    Route::get('/download-template', [StudentApiController::class, 'downloadTemplate']);
    
    // Student updates (requires update_user permission) - FIXED
    Route::middleware(['api.permission:update_user'])->group(function () {
        Route::post('/{id}', [StudentApiController::class, 'update']);
    });
    
    // Student deactivation (requires deactivate_user permission)
    Route::middleware(['api.permission:deactivate_user'])->group(function () {
        Route::post('/{id}/toggle-status', [StudentApiController::class, 'toggleStatus']);
        Route::post('/bulk-toggle-status', [StudentApiController::class, 'bulkToggleStatus']);
    });
    
    // Export functions (basic permissions required)
    Route::get('/export', [StudentApiController::class, 'export']);
    Route::get('/export-filtered', [StudentApiController::class, 'exportFiltered']);
});

// Helper data endpoints (requires API key only)
Route::middleware(['api.key'])->group(function () {
    Route::get('/courses', [StudentApiController::class, 'getCourses']);
    Route::get('/departments', [StudentApiController::class, 'getDepartments']);
    Route::get('/app-info', [StudentApiController::class, 'getAppInfo']);
});

// Error handling for API routes
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found',
        'available_endpoints' => [
            'GET /api/health' => 'Health check',
            'GET /api/verify-api-key' => 'Verify API key (requires X-API-Key header)',
            'GET /api/students' => 'Get all students',
            'POST /api/students' => 'Create student',
            'GET /api/students/{id}' => 'Get student details',
            'PUT /api/students/{id}' => 'Update student',
            'POST /api/students/{id}/toggle-status' => 'Toggle student status',
            'POST /api/students/bulk-toggle-status' => 'Bulk toggle student status',
            'POST /api/students/batch-upload' => 'Batch upload students',
            'GET /api/students/export' => 'Export all students',
            'GET /api/students/export-filtered' => 'Export filtered students',
            'GET /api/students/download-template' => 'Download import template',
            'GET /api/courses' => 'Get all courses',
            'GET /api/departments' => 'Get all departments',
            'GET /api/app-info' => 'Get application information'
        ]
    ], 404);
});