<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiKey;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get API key from header or query parameter
        $apiKey = $request->header('X-API-Key') ?? $request->get('api_key');
        
        if (!$apiKey) {
            return $this->errorResponse('API key is required', 401);
        }

        // Find and validate API key
        $apiKeyModel = $this->validateApiKey($apiKey, $request);
        
        if (!$apiKeyModel) {
            return $this->errorResponse('Invalid or expired API key', 401);
        }

        // Check domain restrictions (allow localhost for testing)
        $domain = $request->getHost();
        if (!$this->isDomainAllowedForTesting($apiKeyModel, $domain)) {
            return $this->errorResponse('Domain not allowed for this API key', 403);
        }

        // Simple rate limiting using Cache
        try {
            $rateLimitKey = 'api_requests_' . $apiKeyModel->id . '_' . now()->format('Y_m_d_H_i');
            $requests = Cache::get($rateLimitKey, 0);
            
            if ($requests >= $apiKeyModel->request_limit_per_minute) {
                return $this->errorResponse('Rate limit exceeded. Try again later.', 429);
            }
            
            // Increment rate limit counter
            Cache::put($rateLimitKey, $requests + 1, 60); // 1 minute expiry
        } catch (\Exception $e) {
            // If cache fails, continue without rate limiting for now
            \Log::warning('Rate limiting cache failed: ' . $e->getMessage());
        }

        // Record API usage
        try {
            $apiKeyModel->recordUsage();
        } catch (\Exception $e) {
            \Log::warning('Failed to record API usage: ' . $e->getMessage());
        }

        // Attach API key model to request for use in controllers
        $request->merge(['apiKeyModel' => $apiKeyModel]);

        return $next($request);
    }

    /**
     * Validate API key
     */
    private function validateApiKey($apiKey, Request $request)
    {
        $apiKeys = ApiKey::active()->get();
        
        foreach ($apiKeys as $key) {
            if ($key->verifyKey($apiKey)) {
                return $key;
            }
        }
        
        return null;
    }

    /**
     * Check if domain is allowed (with localhost exception for testing)
     */
    private function isDomainAllowedForTesting($apiKeyModel, $domain)
    {
        // Allow localhost and 127.0.0.1 for testing
        if (in_array($domain, ['localhost', '127.0.0.1', '::1'])) {
            return true;
        }
        
        return $apiKeyModel->isDomainAllowed($domain);
    }

    /**
     * Return error response
     */
    private function errorResponse($message, $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => $code
        ], $code);
    }
}