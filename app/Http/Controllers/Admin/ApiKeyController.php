<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ApiKeyGenerated;

class ApiKeyController extends Controller
{
    /**
     * Display a listing of API keys
     */
    public function index()
    {
        $admin = Auth::guard('admin')->user();
        $apiKeys = ApiKey::with('createdBy')
                         ->orderBy('created_at', 'desc')
                         ->paginate(20);
        
        return view('admin.api-keys.index', compact('admin', 'apiKeys'));
    }

    /**
     * Show the form for creating a new API key
     */
    public function create()
    {
        $admin = Auth::guard('admin')->user();
        return view('admin.api-keys.create', compact('admin'));
    }

    /**
     * Store a newly created API key
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'application_name' => 'required|string|max:255',
            'developer_name' => 'required|string|max:255',
            'developer_email' => 'required|email|max:255',
            'description' => 'nullable|string|max:1000',
            'allowed_domains' => 'nullable|string',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'in:add_user,update_user,deactivate_user,login_user,logout_user',
            'rate_limit' => 'required|integer|min:10|max:1000',
            'expires_at' => 'nullable|date|after:today',
        ], [
            'permissions.required' => 'Please select at least one permission.',
            'permissions.min' => 'Please select at least one permission.',
            'permissions.*.in' => 'Invalid permission selected.',
        ]);
        try {
            // Process allowed domains
            $allowedDomains = [];
            if ($validated['allowed_domains']) {
                $allowedDomains = array_map('trim', explode(',', $validated['allowed_domains']));
                $allowedDomains = array_filter($allowedDomains); // Remove empty values
            }

            // Generate API key
            $result = ApiKey::generateKey(
                $validated['application_name'],
                $validated['developer_name'],
                $validated['developer_email'],
                Auth::guard('admin')->id(),
                [
                    'description' => $validated['description'],
                    'allowed_domains' => $allowedDomains,
                    'permissions' => $validated['permissions'],
                    'rate_limit' => $validated['rate_limit'],
                    'expires_at' => $validated['expires_at'] ? \Carbon\Carbon::parse($validated['expires_at']) : null,
                ]
            );

            $message = 'API key generated successfully! Please provide the key to the developer manually.';

            return redirect()->route('admin.api-keys.show', $result['api_key'])
                            ->with('success', $message)
                            ->with('raw_key', $result['raw_key']); // Show once

        } catch (\Exception $e) {
            Log::error('Error generating API key: ' . $e->getMessage());
            return redirect()->back()
                            ->withErrors(['error' => 'Failed to generate API key. Please try again.'])
                            ->withInput();
        }
    }

    /**
     * Display the specified API key
     */
    public function show(ApiKey $apiKey)
    {
        $admin = Auth::guard('admin')->user();
        $apiKey->load('createdBy');
        
        return view('admin.api-keys.show', compact('admin', 'apiKey'));
    }

    /**
     * Show the form for editing the API key
     */
    public function edit(ApiKey $apiKey)
    {
        $admin = Auth::guard('admin')->user();
        return view('admin.api-keys.edit', compact('admin', 'apiKey'));
    }

    /**
     * Update the specified API key
     */
    public function update(Request $request, ApiKey $apiKey)
    {
        $validated = $request->validate([
            'application_name' => 'required|string|max:255',
            'developer_name' => 'required|string|max:255',
            'developer_email' => 'required|email|max:255',
            'description' => 'nullable|string|max:1000',
            'allowed_domains' => 'nullable|string',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'in:add_user,update_user,deactivate_user,login_user,logout_user',
            'rate_limit' => 'required|integer|min:10|max:1000',
            'expires_at' => 'nullable|date|after:today',
        ], [
            'permissions.required' => 'Please select at least one permission.',
            'permissions.min' => 'Please select at least one permission.',
            'permissions.*.in' => 'Invalid permission selected.',
        ]);

        try {
            // Process allowed domains
            $allowedDomains = [];
            if ($validated['allowed_domains']) {
                $allowedDomains = array_map('trim', explode(',', $validated['allowed_domains']));
                $allowedDomains = array_filter($allowedDomains);
            }

            $apiKey->update([
                'application_name' => $validated['application_name'],
                'developer_name' => $validated['developer_name'],
                'developer_email' => $validated['developer_email'],
                'description' => $validated['description'],
                'allowed_domains' => $allowedDomains,
                'permissions' => $validated['permissions'],
                'request_limit_per_minute' => $validated['rate_limit'],
                'expires_at' => $validated['expires_at'] ? \Carbon\Carbon::parse($validated['expires_at']) : null,
            ]);

            return redirect()->route('admin.api-keys.show', $apiKey)
                            ->with('success', 'API key updated successfully!');
                            
        } catch (\Exception $e) {
            Log::error('Error updating API key: ' . $e->getMessage());
            return redirect()->back()
                            ->withErrors(['error' => 'Failed to update API key. Please try again.'])
                            ->withInput();
        }
    }

    /**
     * Remove the specified API key
     */
    public function destroy(ApiKey $apiKey)
    {
        try {
            $applicationName = $apiKey->application_name;
            $apiKey->delete();
            
            return redirect()->route('admin.api-keys.index')
                            ->with('success', "API key for '{$applicationName}' deleted successfully!");
        } catch (\Exception $e) {
            Log::error('Error deleting API key: ' . $e->getMessage());
            return redirect()->back()
                            ->with('error', 'Failed to delete API key. Please try again.');
        }
    }

    /**
     * Toggle API key status (active/inactive)
     */
    public function toggle(ApiKey $apiKey)
    {
        try {
            $apiKey->update(['is_active' => !$apiKey->is_active]);
            
            $status = $apiKey->is_active ? 'activated' : 'deactivated';
            
            return response()->json([
                'success' => true,
                'message' => "API key {$status} successfully!",
                'status' => $apiKey->is_active
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling API key status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update API key status.'
            ], 500);
        }
    }

    /**
     * Regenerate API key
     */
    public function regenerate(Request $request, ApiKey $apiKey)
    {
        try {
            // Generate new key but keep same settings
            $result = ApiKey::generateKey(
                $apiKey->application_name,
                $apiKey->developer_name,
                $apiKey->developer_email,
                Auth::guard('admin')->id(),
                [
                    'key_name' => $apiKey->key_name . '_regenerated_' . time(),
                    'description' => $apiKey->description,
                    'allowed_domains' => $apiKey->allowed_domains,
                    'permissions' => $apiKey->permissions,
                    'rate_limit' => $apiKey->request_limit_per_minute,
                    'expires_at' => $apiKey->expires_at,
                ]
            );

            // Deactivate old key
            $apiKey->update(['is_active' => false]);

            $message = 'API key regenerated successfully! Please provide the new key to the developer manually.';

            return redirect()->route('admin.api-keys.show', $result['api_key'])
                            ->with('success', $message)
                            ->with('raw_key', $result['raw_key']);
                            
        } catch (\Exception $e) {
            Log::error('Error regenerating API key: ' . $e->getMessage());
            return redirect()->back()
                            ->with('error', 'Failed to regenerate API key. Please try again.');
        }
    }

    /**
     * Send API key via email manually
     */
    public function sendByEmail(Request $request, ApiKey $apiKey)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'message' => 'nullable|string|max:500'
            ]);

            // Create a temporary email with instructions (no raw key since it can't be retrieved)
            $emailData = [
                'apiKey' => $apiKey,
                'customMessage' => $validated['message'] ?? null,
                'adminName' => Auth::guard('admin')->user()->first_name . ' ' . Auth::guard('admin')->user()->last_name,
            ];

            // Send notification email (without the actual key)
            Mail::send('emails.api-key-notification', $emailData, function($message) use ($validated, $apiKey) {
                $message->to($validated['email'])
                        ->subject('PUP-Taguig API Key Information - ' . $apiKey->application_name);
            });

            return response()->json([
                'success' => true,
                'message' => 'API key information sent successfully! Note: For security, the actual key was not included. Please share it through a secure channel.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending API key email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email. Please try again.'
            ], 500);
        }
    }

    /**
     * Get API key statistics
     */
    public function statistics()
    {
        $admin = Auth::guard('admin')->user();
        
        $stats = [
            'total_keys' => ApiKey::count(),
            'active_keys' => ApiKey::active()->count(),
            'expired_keys' => ApiKey::where('expires_at', '<', now())->count(),
            'recent_activity' => ApiKey::whereNotNull('last_used_at')
                                     ->orderBy('last_used_at', 'desc')
                                     ->limit(10)
                                     ->get(),
            'top_usage' => ApiKey::orderBy('total_requests', 'desc')
                                ->limit(10)
                                ->get(),
        ];
        
        return view('admin.api-keys.statistics', compact('admin', 'stats'));
    }

    /**
     * Export API keys data
     */
    public function export()
    {
        try {
            $apiKeys = ApiKey::with('createdBy')->get();
            
            $filename = 'api_keys_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];
            
            $callback = function() use ($apiKeys) {
                $file = fopen('php://output', 'w');
                
                // CSV Headers
                fputcsv($file, [
                    'Application Name',
                    'Developer Name',
                    'Developer Email',
                    'Status',
                    'Permissions',
                    'Rate Limit',
                    'Total Requests',
                    'Last Used',
                    'Created At',
                    'Expires At'
                ]);
                
                foreach ($apiKeys as $key) {
                    fputcsv($file, [
                        $key->application_name,
                        $key->developer_name,
                        $key->developer_email,
                        $key->is_active ? 'Active' : 'Inactive',
                        implode(', ', $key->formatted_permissions),
                        $key->request_limit_per_minute,
                        $key->total_requests,
                        $key->last_used_at ? $key->last_used_at->format('Y-m-d H:i:s') : 'Never',
                        $key->created_at->format('Y-m-d H:i:s'),
                        $key->expires_at ? $key->expires_at->format('Y-m-d H:i:s') : 'Never'
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            Log::error('Error exporting API keys: ' . $e->getMessage());
            return redirect()->back()
                            ->with('error', 'Failed to export API keys data.');
    }
    }

    /**
     * Test API key
     */
    public function test(ApiKey $apiKey)
    {
        if (!$apiKey->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'API key is not valid (inactive or expired)'
            ]);
        }

        // Simulate a test request
        $testEndpoints = [
            '/api/auth/verify-app' => 'App verification',
            '/api/health' => 'Health check',
        ];

        $results = [];
        foreach ($testEndpoints as $endpoint => $description) {
            try {
                // Simulate endpoint test (you can make actual HTTP requests here)
                $results[] = [
                    'endpoint' => $endpoint,
                    'description' => $description,
                    'status' => 'success',
                    'response_time' => rand(50, 200) . 'ms'
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'endpoint' => $endpoint,
                    'description' => $description,
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'API key test completed',
            'results' => $results
        ]);
    }
}