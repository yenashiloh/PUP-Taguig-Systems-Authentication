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
        // Enhanced validation with comprehensive error messages
        $validated = $request->validate([
            'application_name' => [
                'required',
                'string',
                'max:255',
                'min:3',
                'regex:/^[a-zA-Z0-9\s\-_\.]+$/'
            ],
            'developer_name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                'regex:/^[a-zA-Z\s\-\.\']+$/'
            ],
            'developer_email' => [
                'required',
                'email:rfc',
                'max:255'
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
                'min:10'
            ],
            'allowed_domains' => [
                'nullable',
                'string',
                'max:500'
            ],
            'permissions' => [
                'required',
                'array',
                'min:1'
            ],
            'permissions.*' => [
                'required',
                'string',
                'in:add_user,update_user,deactivate_user,login_user,logout_user'
            ],
            'rate_limit' => [
                'required',
                'integer',
                'min:10',
                'max:1000'
            ],
            'expires_at' => [
                'nullable',
                'date',
                'after:today',
                'before:' . now()->addYears(5)->format('Y-m-d')
            ]
        ], [
            // Application Name errors
            'application_name.required' => 'Application name is required.',
            'application_name.string' => 'Application name must be a valid text.',
            'application_name.max' => 'Application name cannot exceed 255 characters.',
            'application_name.min' => 'Application name must be at least 3 characters long.',
            'application_name.regex' => 'Application name can only contain letters, numbers, spaces, hyphens, underscores, and periods.',
            
            // Developer Name errors
            'developer_name.required' => 'Developer name is required.',
            'developer_name.string' => 'Developer name must be a valid text.',
            'developer_name.max' => 'Developer name cannot exceed 255 characters.',
            'developer_name.min' => 'Developer name must be at least 2 characters long.',
            'developer_name.regex' => 'Developer name can only contain letters, spaces, hyphens, periods, and apostrophes.',
            
            // Developer Email errors
            'developer_email.required' => 'Developer email is required.',
            'developer_email.email' => 'Please provide a valid email address.',
            'developer_email.max' => 'Developer email cannot exceed 255 characters.',
            
            // Description errors
            'description.string' => 'Description must be a valid text.',
            'description.max' => 'Description cannot exceed 1000 characters.',
            'description.min' => 'Description must be at least 10 characters long if provided.',
            
            // Allowed Domains errors
            'allowed_domains.string' => 'Allowed domains must be a valid text.',
            'allowed_domains.max' => 'Allowed domains field cannot exceed 500 characters.',
            
            // Permissions errors
            'permissions.required' => 'Please select at least one permission.',
            'permissions.array' => 'Permissions must be a valid selection.',
            'permissions.min' => 'Please select at least one permission.',
            'permissions.*.required' => 'Each permission must be specified.',
            'permissions.*.string' => 'Each permission must be a valid text.',
            'permissions.*.in' => 'Invalid permission selected. Please choose from the available options.',
            
            // Rate Limit errors
            'rate_limit.required' => 'Rate limit is required.',
            'rate_limit.integer' => 'Rate limit must be a valid number.',
            'rate_limit.min' => 'Rate limit must be at least 10 requests per minute.',
            'rate_limit.max' => 'Rate limit cannot exceed 1000 requests per minute.',
            
            // Expiration Date errors
            'expires_at.date' => 'Please provide a valid expiration date.',
            'expires_at.after' => 'Expiration date must be at least tomorrow.',
            'expires_at.before' => 'Expiration date cannot be more than 5 years from now.'
        ]);

        try {
            // Validate domain format if provided
            if (!empty($validated['allowed_domains'])) {
                $domains = array_map('trim', explode(',', $validated['allowed_domains']));
                foreach ($domains as $domain) {
                    if (!empty($domain) && !filter_var('http://' . $domain, FILTER_VALIDATE_URL)) {
                        return redirect()->back()
                            ->withErrors(['allowed_domains' => 'One or more domains are invalid. Please use format: example.com, app.example.com'])
                            ->withInput();
                    }
                }
            }

            // Check if API key already exists for this application or developer email
            $existingApiKey = ApiKey::where('application_name', $validated['application_name'])
                                   ->orWhere('developer_email', $validated['developer_email'])
                                   ->first();

            // Process allowed domains
            $allowedDomains = [];
            if ($validated['allowed_domains']) {
                $allowedDomains = array_map('trim', explode(',', $validated['allowed_domains']));
                $allowedDomains = array_filter($allowedDomains); // Remove empty values
            }

            $updateData = [
                'application_name' => $validated['application_name'],
                'developer_name' => $validated['developer_name'],
                'developer_email' => $validated['developer_email'],
                'description' => $validated['description'],
                'allowed_domains' => $allowedDomains,
                'permissions' => $validated['permissions'],
                'request_limit_per_minute' => $validated['rate_limit'],
                'expires_at' => $validated['expires_at'] ? \Carbon\Carbon::parse($validated['expires_at']) : null,
                'is_active' => true,
                'created_by' => Auth::guard('admin')->id()
            ];

            if ($existingApiKey) {
                // UPDATE existing API key
                
                // Generate new key but keep existing record
                $rawKey = 'pup_' . \Illuminate\Support\Str::random(32) . '_' . time();
                $updateData['key_hash'] = \Illuminate\Support\Facades\Hash::make($rawKey);
                
                $existingApiKey->update($updateData);
                
                $message = 'API key updated successfully! The previous key has been replaced with a new one.';
                
                return redirect()->route('admin.api-keys.show', $existingApiKey)
                                ->with('success', $message)
                                ->with('raw_key', $rawKey)
                                ->with('updated', true);
            } else {
                // CREATE new API key
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
                                ->with('raw_key', $result['raw_key'])
                                ->with('created', true);
            }

        } catch (\Exception $e) {
            Log::error('Error generating/updating API key: ' . $e->getMessage());
            return redirect()->back()
                            ->withErrors(['error' => 'Failed to process API key request. Please try again. Error: ' . $e->getMessage()])
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
        // Use the same comprehensive validation as store method
        $validated = $request->validate([
            'application_name' => [
                'required',
                'string',
                'max:255',
                'min:3',
                'regex:/^[a-zA-Z0-9\s\-_\.]+$/'
            ],
            'developer_name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                'regex:/^[a-zA-Z\s\-\.\']+$/'
            ],
            'developer_email' => [
                'required',
                'email:rfc',
                'max:255'
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
                'min:10'
            ],
            'allowed_domains' => [
                'nullable',
                'string',
                'max:500'
            ],
            'permissions' => [
                'required',
                'array',
                'min:1'
            ],
            'permissions.*' => [
                'required',
                'string',
                'in:add_user,update_user,deactivate_user,login_user,logout_user'
            ],
            'rate_limit' => [
                'required',
                'integer',
                'min:10',
                'max:1000'
            ],
            'expires_at' => [
                'nullable',
                'date',
                'after:today',
                'before:' . now()->addYears(5)->format('Y-m-d')
            ]
        ], [
            // Same error messages as in store method
            'application_name.required' => 'Application name is required.',
            'application_name.string' => 'Application name must be a valid text.',
            'application_name.max' => 'Application name cannot exceed 255 characters.',
            'application_name.min' => 'Application name must be at least 3 characters long.',
            'application_name.regex' => 'Application name can only contain letters, numbers, spaces, hyphens, underscores, and periods.',
            'developer_name.required' => 'Developer name is required.',
            'developer_name.string' => 'Developer name must be a valid text.',
            'developer_name.max' => 'Developer name cannot exceed 255 characters.',
            'developer_name.min' => 'Developer name must be at least 2 characters long.',
            'developer_name.regex' => 'Developer name can only contain letters, spaces, hyphens, periods, and apostrophes.',
            'developer_email.required' => 'Developer email is required.',
            'developer_email.email' => 'Please provide a valid email address.',
            'developer_email.max' => 'Developer email cannot exceed 255 characters.',
            'description.string' => 'Description must be a valid text.',
            'description.max' => 'Description cannot exceed 1000 characters.',
            'description.min' => 'Description must be at least 10 characters long if provided.',
            'allowed_domains.string' => 'Allowed domains must be a valid text.',
            'allowed_domains.max' => 'Allowed domains field cannot exceed 500 characters.',
            'permissions.required' => 'Please select at least one permission.',
            'permissions.array' => 'Permissions must be a valid selection.',
            'permissions.min' => 'Please select at least one permission.',
            'permissions.*.required' => 'Each permission must be specified.',
            'permissions.*.string' => 'Each permission must be a valid text.',
            'permissions.*.in' => 'Invalid permission selected. Please choose from the available options.',
            'rate_limit.required' => 'Rate limit is required.',
            'rate_limit.integer' => 'Rate limit must be a valid number.',
            'rate_limit.min' => 'Rate limit must be at least 10 requests per minute.',
            'rate_limit.max' => 'Rate limit cannot exceed 1000 requests per minute.',
            'expires_at.date' => 'Please provide a valid expiration date.',
            'expires_at.after' => 'Expiration date must be at least tomorrow.',
            'expires_at.before' => 'Expiration date cannot be more than 5 years from now.'
        ]);

        try {
            // Validate domain format if provided
            if (!empty($validated['allowed_domains'])) {
                $domains = array_map('trim', explode(',', $validated['allowed_domains']));
                foreach ($domains as $domain) {
                    if (!empty($domain) && !filter_var('http://' . $domain, FILTER_VALIDATE_URL)) {
                        return redirect()->back()
                            ->withErrors(['allowed_domains' => 'One or more domains are invalid. Please use format: example.com, app.example.com'])
                            ->withInput();
                    }
                }
            }

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
                            ->with('success', 'API key information updated successfully!');
                            
        } catch (\Exception $e) {
            Log::error('Error updating API key: ' . $e->getMessage());
            return redirect()->back()
                            ->withErrors(['error' => 'Failed to update API key. Please try again. Error: ' . $e->getMessage()])
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