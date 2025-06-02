<?php
use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\RedirectIfAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register the admin auth middleware
        $middleware->alias([
            'admin.auth' => AdminAuth::class,
            'redirect.if.admin' => RedirectIfAdmin::class, 
            'api.key' => \App\Http\Middleware\ValidateApiKey::class,
        ]);
        
        //Prevent back history middleware
        $middleware->append([
            \App\Http\Middleware\PreventBackHistory::class,
        ]);

        $middleware->group('api', [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();