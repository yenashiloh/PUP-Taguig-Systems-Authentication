<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register your custom middleware aliases
        $middleware->alias([
            // API Key Middleware
            'api.key' => \App\Http\Middleware\ApiKeyMiddleware::class,
            'api.auth' => \App\Http\Middleware\ApiAuthMiddleware::class,
            'api.permission' => \App\Http\Middleware\ApiPermissionMiddleware::class,
            'validate.api.key' => \App\Http\Middleware\ValidateApiKey::class,
            
            // Admin Middleware
            'admin.auth' => \App\Http\Middleware\AdminAuth::class,
            'redirect.if.admin' => \App\Http\Middleware\RedirectIfAdmin::class,
            'prevent.back.history' => \App\Http\Middleware\PreventBackHistory::class,
        ]);

        // Customize API middleware group to remove default throttling
        // This allows API keys to handle their own rate limiting
        $middleware->group('api', [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
        
        // Web middleware group (standard Laravel setup)
        $middleware->group('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Global middleware (optional - uncomment if needed)
        // $middleware->append([
        //     \App\Http\Middleware\PreventBackHistory::class,
        // ]);

        // Priority middleware (runs first - optional)
        // $middleware->priority([
        //     \App\Http\Middleware\ApiKeyMiddleware::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Custom exception handling for API routes
        $exceptions->render(function (\Exception $e, $request) {
            // Handle API exceptions differently
            if ($request->is('api/*')) {
                $status = 500;
                $message = 'Internal server error';
                
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    $status = 422;
                    $message = 'Validation failed';
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                        'errors' => $e->errors()
                    ], $status);
                }
                
                if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    $status = 404;
                    $message = 'Resource not found';
                }
                
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                    $status = 404;
                    $message = 'Endpoint not found';
                }
                
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
                    $status = 405;
                    $message = 'Method not allowed';
                }
                
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException) {
                    $status = 429;
                    $message = 'Rate limit exceeded';
                }

                // Don't expose internal errors in production
                if (!config('app.debug')) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                        'error_code' => $status
                    ], $status);
                }

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'error' => $e->getMessage(),
                    'error_code' => $status
                ], $status);
            }
        });
    })->create();

/*
|--------------------------------------------------------------------------
| API Middleware Usage Examples
|--------------------------------------------------------------------------
|
| Here are examples of how to use the registered middleware in your routes:
|
| Basic API key validation:
| Route::middleware(['api.key'])->get('/api/students', [StudentApiController::class, 'index']);
|
| API key with specific permission:
| Route::middleware(['api.key', 'api.permission:add_user'])->post('/api/students', [StudentApiController::class, 'store']);
|
| API key with user authentication:
| Route::middleware(['api.key', 'api.auth'])->get('/api/user/profile', [UserController::class, 'profile']);
|
| Multiple permissions:
| Route::middleware(['api.key', 'api.permission:update_user,deactivate_user'])->put('/api/students/{id}', [StudentApiController::class, 'update']);
|
| Admin authentication:
| Route::middleware(['admin.auth'])->group(function () {
|     Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
| });
|
*/

/*
|--------------------------------------------------------------------------
| API Key Permissions Reference
|--------------------------------------------------------------------------
|
| Available permissions that can be assigned to API keys:
|
| - add_user: Create new users and batch upload functionality
| - update_user: Update user information and details
| - deactivate_user: Activate/deactivate user accounts
| - login_user: Authenticate faculty/students via API
| - logout_user: End user sessions via API
|
| These permissions are checked by the 'api.permission' middleware.
|
*/