<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

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

        // Check domain restrictions with enhanced domain handling
        $domain = $request->getHost();
        if (!$this->isDomainAllowed($apiKeyModel, $domain)) {
            return response()->json([
                'success' => false,
                'message' => 'Domain not allowed for this API key',
                'debug' => [
                    'requested_domain' => $domain,
                    'allowed_domains' => $apiKeyModel->allowed_domains,
                    'api_key_id' => $apiKeyModel->id
                ]
            ], 403);
        }

        // Simple rate limiting using Cache
        $rateLimitKey = 'api-requests:' . $apiKeyModel->id . ':' . now()->format('Y-m-d-H-i');
        
        try {
            $requests = Cache::get($rateLimitKey, 0);
            
            if ($requests >= $apiKeyModel->request_limit_per_minute) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rate limit exceeded. Try again later.',
                    'retry_after' => 60
                ], 429);
            }
            
            // Increment rate limit counter
            Cache::put($rateLimitKey, $requests + 1, 60); // 1 minute expiry
        } catch (\Exception $e) {
            // Continue without rate limiting if cache fails
            \Log::warning('Rate limiting failed: ' . $e->getMessage());
        }

        // Record API key usage
        try {
            $apiKeyModel->recordUsage();
        } catch (\Exception $e) {
            \Log::warning('Failed to record API usage: ' . $e->getMessage());
        }

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

    /**
     * Check if domain is allowed with comprehensive support for both local and production domains
     */
    private function isDomainAllowed($apiKeyModel, $domain)
    {
        // If no domain restrictions are set, allow all domains
        if (empty($apiKeyModel->allowed_domains)) {
            return true;
        }

        // Normalize the domain
        $domain = strtolower($domain);

        // Enhanced localhost variations for development
        $localhostDomains = [
            'localhost',
            '127.0.0.1',
            '::1',
            'localhost:8000',
            '127.0.0.1:8000',
            'localhost:3000',
            '127.0.0.1:3000',
            'localhost:80',
            '127.0.0.1:80',
            'localhost:443',
            '127.0.0.1:443'
        ];

        // Always allow localhost for development/testing
        if (in_array($domain, $localhostDomains)) {
            return true;
        }

        // Production domains to always allow
        $productionDomains = [
            'pupt-registration.site',
            'www.pupt-registration.site'
        ];

        // Always allow production domains
        if (in_array($domain, $productionDomains)) {
            return true;
        }

        // Check if the domain is in the allowed domains list
        foreach ($apiKeyModel->allowed_domains as $allowedDomain) {
            $allowedDomain = strtolower(trim($allowedDomain));
            
            // Exact match
            if ($domain === $allowedDomain) {
                return true;
            }
            
            // Wildcard subdomain support (*.example.com)
            if (strpos($allowedDomain, '*.') === 0) {
                $baseDomain = substr($allowedDomain, 2);
                if (str_ends_with($domain, '.' . $baseDomain) || $domain === $baseDomain) {
                    return true;
                }
            }
            
            // Support for pupt-registration.site and its subdomains
            if ($allowedDomain === 'pupt-registration.site' || $allowedDomain === '*.pupt-registration.site') {
                if ($domain === 'pupt-registration.site' || 
                    $domain === 'www.pupt-registration.site' || 
                    str_ends_with($domain, '.pupt-registration.site')) {
                    return true;
                }
            }
        }

        return false;
    }
}