<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Check if user is logged in with admin guard
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('login')->withErrors(['email' => 'Please login to access the admin area.']);
        }
        
        return $next($request);
    }
}
