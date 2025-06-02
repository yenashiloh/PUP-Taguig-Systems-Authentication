<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UserLoginController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group.
|
*/

// API Authentication Routes (Main focus: Login only)
Route::prefix('auth')->name('api.')->group(function () {
    // Process login (requires valid API key) - This is the main endpoint
    Route::post('/login', [UserLoginController::class, 'login'])->name('user.login');
    
    // Optional: Logout user (requires valid API key and session token)
    Route::post('/logout', [UserLoginController::class, 'logout'])->name('user.logout');
    
    // Optional: Verify session (requires valid API key and session token)
    Route::post('/verify', [UserLoginController::class, 'verifySession'])->name('user.verify');
});

// Public API Routes (require valid API key only)
Route::middleware(['api.key'])->group(function () {
    
    // Application verification
    Route::get('/verify-app', function (Request $request) {
        $apiKey = $request->apiKeyModel; // Set by middleware
        
        return response()->json([
            'success' => true,
            'message' => 'API key is valid',
            'data' => [
                'application_name' => $apiKey->application_name,
                'developer_name' => $apiKey->developer_name,
                'permissions' => $apiKey->permissions,
                'rate_limit' => $apiKey->request_limit_per_minute,
                'requests_remaining' => $apiKey->request_limit_per_minute - \Illuminate\Support\Facades\RateLimiter::attempts(
                    'api-requests:' . $apiKey->id . ':' . now()->format('Y-m-d-H-i')
                )
            ]
        ]);
    })->name('api.verify.app');
    
    // Health check
    Route::get('/health', function () {
        return response()->json([
            'success' => true,
            'message' => 'API is healthy',
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0'
        ]);
    })->name('api.health');
    
    // Get system information
    Route::get('/system-info', function () {
        return response()->json([
            'success' => true,
            'data' => [
                'system_name' => 'PUP-Taguig Systems Authentication',
                'version' => '1.0.0',
                'documentation_url' => url('/api/documentation'),
                'support_email' => 'puptloginsystem69@gmail.com',
                'main_endpoints' => [
                    'authentication' => [
                        'GET /external/login?api_key=XXX' => 'Show login form for faculty/students',
                        'POST /api/auth/login' => 'Authenticate user and return session data',
                        'POST /api/auth/logout' => 'Logout user session',
                        'POST /api/auth/verify' => 'Verify active session'
                    ],
                    'utility' => [
                        'GET /api/verify-app' => 'Verify API key and get app info',
                        'GET /api/health' => 'Check API health status',
                        'GET /api/system-info' => 'Get system information'
                    ]
                ]
            ]
        ]);
    })->name('api.system.info');
});