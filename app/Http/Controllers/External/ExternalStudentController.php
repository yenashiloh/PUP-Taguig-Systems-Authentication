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
        $baseUrl = $request->get('base_url', url('/'));
        $appName = $request->get('app_name', 'External Student Management');
        
        // If no API key provided, show instructions
        if (!$apiKey) {
            return view('external.instructions', [
                'docs_url' => route('api.docs'),
                'example_url' => route('external.student-management') . '?api_key=YOUR_API_KEY'
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
}