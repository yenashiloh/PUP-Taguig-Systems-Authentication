<?php

namespace App\Http\Controllers\External;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExternalStudentController extends Controller
{
    /**
     * Show the external student management interface
     */
    public function index(Request $request)
    {
        // Get parameters from URL
        $apiKey = $request->get('api_key');
        $appName = $request->get('app_name', 'External Student Management');
        
        // Determine base URL dynamically
        $baseUrl = $this->determineBaseUrl($request);
        
        // If no API key provided, show instructions
        if (!$apiKey) {
            return view('external.instructions', [
                'docs_url' => route('api.docs'),
                'example_url' => $this->generateExampleUrl($request),
                'base_url' => $baseUrl
            ]);
        }
        
        // Return the student management view with parameters
        return view('external.student-management', [
            'api_key' => $apiKey,
            'base_url' => $baseUrl,
            'app_name' => $appName,
            'docs_url' => route('api.docs')
        ]);
    }
    
    /**
     * Determine the correct base URL based on the current request
     */
    private function determineBaseUrl(Request $request)
    {
        // Check if base_url is explicitly provided
        $baseUrl = $request->get('base_url');
        if ($baseUrl) {
            return $baseUrl;
        }
        
        // Get current domain information
        $scheme = $request->isSecure() ? 'https' : 'http';
        $host = $request->getHost();
        $port = $request->getPort();
        
        // Handle localhost/development
        if ($host === 'localhost' || $host === '127.0.0.1') {
            if ($port && !in_array($port, [80, 443])) {
                return "{$scheme}://{$host}:{$port}";
            } else {
                return "{$scheme}://{$host}";
            }
        }
        
        // Handle production domain
        if ($host === 'pupt-registration.site' || str_ends_with($host, '.pupt-registration.site')) {
            return "{$scheme}://{$host}";
        }
        
        // Fallback to current domain
        if ($port && !in_array($port, [80, 443])) {
            return "{$scheme}://{$host}:{$port}";
        } else {
            return "{$scheme}://{$host}";
        }
    }
    
    /**
     * Generate example URL for instructions
     */
    private function generateExampleUrl(Request $request)
    {
        $baseUrl = $this->determineBaseUrl($request);
        $route = route('external.student-management');
        
        // For localhost, show localhost example
        if (str_contains($baseUrl, '127.0.0.1') || str_contains($baseUrl, 'localhost')) {
            return $route . '?api_key=YOUR_API_KEY&base_url=' . urlencode($baseUrl);
        }
        
        // For production, show production example
        return $route . '?api_key=YOUR_API_KEY';
    }
}