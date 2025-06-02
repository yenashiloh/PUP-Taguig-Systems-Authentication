<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request that requires both API key and user session.
     */
    public function handle(Request $request, Closure $next)
    {
        // First check API key (this should be handled by api.key middleware first)
        if (!isset($request->apiKeyModel)) {
            return response()->json([
                'success' => false,
                'message' => 'API key validation required'
            ], 401);
        }

        // Check for session token
        $sessionToken = $request->header('X-Session-Token') ?? $request->get('session_token');
        
        if (!$sessionToken) {
            return response()->json([
                'success' => false,
                'message' => 'Session token is required'
            ], 401);
        }

        // Find user by session token
        $user = $this->findUserBySessionToken($sessionToken);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired session token'
            ], 401);
        }

        // Check if user is still active
        if ($user->status !== 'Active') {
            return response()->json([
                'success' => false,
                'message' => 'User account is not active'
            ], 403);
        }

        // Add user to request
        $request->user = $user;
        $request->authenticatedUser = $user;

        return $next($request);
    }

    /**
     * Find user by session token
     */
    private function findUserBySessionToken($sessionToken)
    {
        $users = User::whereNotNull('api_session_token')
                    ->where('status', 'Active')
                    ->get();
        
        foreach ($users as $user) {
            if (Hash::check($sessionToken, $user->api_session_token)) {
                return $user;
            }
        }
        
        return null;
    }
}
