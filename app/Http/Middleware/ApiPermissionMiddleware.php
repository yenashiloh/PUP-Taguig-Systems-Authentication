<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;

class ApiPermissionMiddleware
{
    /**
     * Handle an incoming request that requires specific permissions.
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        // Check if API key model exists (should be set by api.key middleware)
        if (!isset($request->apiKeyModel)) {
            return response()->json([
                'success' => false,
                'message' => 'API key validation required'
            ], 401);
        }

        $apiKeyModel = $request->apiKeyModel;

        // Check if API key has the required permission
        if (!in_array($permission, $apiKeyModel->permissions)) {
            return response()->json([
                'success' => false,
                'message' => "API key does not have '{$permission}' permission"
            ], 403);
        }

        // If checking for role-specific permissions, also verify user role
        if (isset($request->user)) {
            $user = $request->user;
            
            if ($permission === 'student_data' && $user->role !== 'Student') {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not a student'
                ], 403);
            }
            
            if ($permission === 'faculty_data' && $user->role !== 'Faculty') {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not a faculty member'
                ], 403);
            }
        }

        return $next($request);
    }
}