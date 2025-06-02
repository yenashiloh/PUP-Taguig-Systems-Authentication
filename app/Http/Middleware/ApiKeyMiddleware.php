<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-Key') ?? $request->get('api_key');
        
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key is required'
            ], 401);
        }

        // Find and validate API key
        $apiKeyModel = $this->validateApiKey($apiKey);
        
        if (!$apiKeyModel) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired API key'
            ], 401);
        }

        // Check domain restrictions
        $domain = $request->getHost();
        if (!$apiKeyModel->isDomainAllowed($domain)) {
            return response()->json([
                'success' => false,
                'message' => 'Domain not allowed for this API key'
            ], 403);
        }

        // Check rate limiting
        $rateLimitKey = 'api-requests:' . $apiKeyModel->id . ':' . now()->format('Y-m-d-H-i');
        $requests = RateLimiter::attempts($rateLimitKey);
        
        if ($requests >= $apiKeyModel->request_limit_per_minute) {
            return response()->json([
                'success' => false,
                'message' => 'Rate limit exceeded. Try again later.',
                'retry_after' => 60
            ], 429);
        }
        
        // Increment rate limit counter
        RateLimiter::hit($rateLimitKey, 60);

        // Record API key usage
        $apiKeyModel->recordUsage();

        // Add API key model to request for use in controllers
        $request->apiKeyModel = $apiKeyModel;

        return $next($request);
    }

    /**
     * Validate API key
     */
    private function validateApiKey($apiKey)
    {
        $apiKeys = ApiKey::active()->get();
        
        foreach ($apiKeys as $key) {
            if ($key->verifyKey($apiKey)) {
                return $key;
            }
        }
        
        return null;
    }
}
