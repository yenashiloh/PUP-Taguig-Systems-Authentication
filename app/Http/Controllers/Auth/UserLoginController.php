<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class UserLoginController extends Controller
{
    /**
     * Show the user login form
     */
    public function showLoginForm(Request $request)
    {
        // Get the API key from header or query parameter
        $apiKey = $request->header('X-API-Key') ?? $request->get('api_key');
        
        if (!$apiKey) {
            return response()->view('errors.api-key-required', [], 400);
        }

        // Verify API key and get application info
        $apiKeyModel = $this->validateApiKey($apiKey, $request);
        
        if (!$apiKeyModel) {
            return response()->view('errors.invalid-api-key', [], 401);
        }

        // Check if API key has login permission
        if (!in_array('login_user', $apiKeyModel->permissions) && !in_array('basic_auth', $apiKeyModel->permissions)) {
            return response()->view('errors.no-login-permission', [], 403);
        }

        // Check domain restrictions with enhanced domain support
        $domain = $request->getHost();
        if (!$this->isDomainAllowed($apiKeyModel, $domain)) {
            return response()->view('errors.domain-not-allowed', ['domain' => $domain], 403);
        }

        // Get application information for the login page
        $appInfo = [
            'app_name' => $apiKeyModel->application_name,
            'developer_name' => $apiKeyModel->developer_name,
            'api_key_id' => $apiKeyModel->id
        ];

        return view('auth.user-login', compact('appInfo', 'apiKey'));
    }

    /**
     * Handle user login request
     */
    public function login(Request $request)
    {
        // Get the API key
        $apiKey = $request->header('X-API-Key') ?? $request->get('api_key');
        
        if (!$apiKey) {
            return $this->errorResponse('API key is required', 401);
        }

        // Verify API key
        $apiKeyModel = $this->validateApiKey($apiKey, $request);
        
        if (!$apiKeyModel) {
            return $this->errorResponse('Invalid or expired API key', 401);
        }

        // Simple rate limiting using Cache
        $key = 'login_attempts_' . $request->ip() . '_' . $apiKeyModel->id;
        
        try {
            $attempts = Cache::get($key, 0);
            if ($attempts >= 10) { // Increased limit for testing
                return $this->errorResponse('Too many login attempts. Try again later.', 429);
            }
        } catch (\Exception $e) {
            // Continue without rate limiting if cache fails
            \Log::warning('Login rate limiting failed: ' . $e->getMessage());
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            $this->incrementLoginAttempts($key);
            return $this->errorResponse('Invalid credentials provided', 422, $validator->errors());
        }

        // Attempt to find and authenticate user
        $user = User::where('email', $request->email)
                   ->where('status', 'Active')
                   ->whereIn('role', ['Student', 'Faculty'])
                   ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            $this->incrementLoginAttempts($key);
            return $this->errorResponse('Invalid email or password', 401);
        }

        // Check if user role is allowed by API key permissions
        $allowedRoles = $this->getAllowedRoles($apiKeyModel->permissions);
        if (!in_array($user->role, $allowedRoles)) {
            $this->incrementLoginAttempts($key);
            return $this->errorResponse('User role not allowed for this application', 403);
        }

        // Clear rate limiting on successful login
        $this->clearLoginAttempts($key);

        // Record API key usage
        try {
            $apiKeyModel->recordUsage();
        } catch (\Exception $e) {
            \Log::warning('Failed to record API usage: ' . $e->getMessage());
        }

        // Generate session token for the user
        $sessionToken = Str::random(60);
        $user->update([
            'api_session_token' => Hash::make($sessionToken),
            'last_login_at' => now(),
            'last_login_ip' => $request->ip()
        ]);

        // Prepare response data
        $userData = [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'middle_name' => $user->middle_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'status' => $user->status,
            'last_login_at' => $user->last_login_at,
        ];

        // Add role-specific data based on permissions
        if (in_array('student_data', $apiKeyModel->permissions) && $user->role === 'Student') {
            $userData = array_merge($userData, [
                'student_number' => $user->student_number,
                'program' => $user->program,
                'year' => $user->year,
                'section' => $user->section,
                'birthdate' => $user->birthdate,
            ]);
        }

        if (in_array('faculty_data', $apiKeyModel->permissions) && $user->role === 'Faculty') {
            $userData = array_merge($userData, [
                'employee_number' => $user->employee_number,
                'department' => $user->department,
                'phone_number' => $user->phone_number,
                'employment_status' => $user->employment_status,
                'birthdate' => $user->birthdate,
            ]);
        }

        // Log successful login
        \Log::info('User login via API', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'api_key_id' => $apiKeyModel->id,
            'application' => $apiKeyModel->application_name,
            'ip_address' => $request->ip()
        ]);

        // Get redirect URL based on domain detection
        $redirectUrl = $this->getRedirectUrl($request, $apiKeyModel);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'session_token' => $sessionToken,
                'user' => $userData,
                'application' => [
                    'name' => $apiKeyModel->application_name,
                    'developer' => $apiKeyModel->developer_name
                ],
                'redirect_url' => $redirectUrl
            ]
        ]);
    }

    /**
     * Get redirect URL based on domain and API key configuration
     */
    private function getRedirectUrl(Request $request, $apiKeyModel)
    {
        $currentDomain = $request->getHost();
        $scheme = $request->isSecure() ? 'https' : 'http';
        
        // If API key has allowed domains, use the first one
        if (!empty($apiKeyModel->allowed_domains)) {
            $domain = $apiKeyModel->allowed_domains[0];
            
            // Handle different domain formats
            if (strpos($domain, '://') === false) {
                // Determine scheme based on domain
                if ($domain === 'pupt-registration.site' || str_ends_with($domain, '.pupt-registration.site')) {
                    return 'https://' . $domain;
                } else {
                    return $scheme . '://' . $domain;
                }
            } else {
                return $domain;
            }
        }
        
        // Fallback to current domain
        $port = $request->getPort();
        if ($port && !in_array($port, [80, 443])) {
            return "{$scheme}://{$currentDomain}:{$port}";
        } else {
            return "{$scheme}://{$currentDomain}";
        }
    }

    /**
     * Increment login attempts with error handling
     */
    private function incrementLoginAttempts($key)
    {
        try {
            $attempts = Cache::get($key, 0);
            Cache::put($key, $attempts + 1, 300); // 5 minutes
        } catch (\Exception $e) {
            // Continue without rate limiting if cache fails
        }
    }

    /**
     * Clear login attempts with error handling
     */
    private function clearLoginAttempts($key)
    {
        try {
            Cache::forget($key);
        } catch (\Exception $e) {
            // Continue if cache fails
        }
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request)
    {
        $apiKey = $request->header('X-API-Key') ?? $request->get('api_key');
        $sessionToken = $request->header('X-Session-Token') ?? $request->get('session_token');

        if (!$apiKey || !$sessionToken) {
            return $this->errorResponse('API key and session token are required', 401);
        }

        // Find user by session token
        $users = User::whereNotNull('api_session_token')->get();
        $user = null;

        foreach ($users as $u) {
            if (Hash::check($sessionToken, $u->api_session_token)) {
                $user = $u;
                break;
            }
        }

        if ($user) {
            $user->update(['api_session_token' => null]);
            
            \Log::info('User logout via API', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'ip_address' => $request->ip()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }

    /**
     * Verify user session
     */
    public function verifySession(Request $request)
    {
        $apiKey = $request->header('X-API-Key') ?? $request->get('api_key');
        $sessionToken = $request->header('X-Session-Token') ?? $request->get('session_token');

        if (!$apiKey || !$sessionToken) {
            return $this->errorResponse('API key and session token are required', 401);
        }

        $apiKeyModel = $this->validateApiKey($apiKey, $request);
        if (!$apiKeyModel) {
            return $this->errorResponse('Invalid API key', 401);
        }

        // Find user by session token
        $users = User::whereNotNull('api_session_token')->get();
        $user = null;

        foreach ($users as $u) {
            if (Hash::check($sessionToken, $u->api_session_token)) {
                $user = $u;
                break;
            }
        }

        if (!$user) {
            return $this->errorResponse('Invalid session token', 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Session is valid',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'role' => $user->role,
                ]
            ]
        ]);
    }

    /**
     * Validate API key
     */
    private function validateApiKey($apiKey, Request $request)
    {
        $apiKeys = ApiKey::active()->get();
        
        foreach ($apiKeys as $key) {
            if ($key->verifyKey($apiKey)) {
                // Simple rate limiting using Cache
                $rateLimitKey = 'api-requests:' . $key->id . ':' . now()->format('Y-m-d-H-i');
                
                try {
                    $requests = Cache::get($rateLimitKey, 0);
                    
                    if ($requests >= $key->request_limit_per_minute) {
                        return null; // Rate limit exceeded
                    }
                    
                    Cache::put($rateLimitKey, $requests + 1, 60); // 1 minute window
                } catch (\Exception $e) {
                    // Continue without rate limiting if cache fails
                    \Log::warning('Rate limiting failed: ' . $e->getMessage());
                }
                
                return $key;
            }
        }
        
        return null;
    }

    /**
     * Get allowed roles based on permissions
     */
    private function getAllowedRoles($permissions)
    {
        $roles = [];
        
        if (in_array('student_data', $permissions) || in_array('basic_auth', $permissions) || in_array('login_user', $permissions)) {
            $roles[] = 'Student';
        }
        
        if (in_array('faculty_data', $permissions) || in_array('basic_auth', $permissions) || in_array('login_user', $permissions)) {
            $roles[] = 'Faculty';
        }
        
        return $roles;
    }

    /**
     * Check if domain is allowed with enhanced support for both domains
     */
    private function isDomainAllowed($apiKeyModel, $domain)
    {
        // Allow localhost and 127.0.0.1 for testing
        if (in_array($domain, ['localhost', '127.0.0.1', '::1'])) {
            return true;
        }
        
        // Allow production domain
        if ($domain === 'pupt-registration.site' || $domain === 'www.pupt-registration.site') {
            return true;
        }
        
        return $apiKeyModel->isDomainAllowed($domain);
    }

    /**
     * Return error response
     */
    private function errorResponse($message, $code = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message
        ];
        
        if ($errors) {
            $response['errors'] = $errors;
        }
        
        return response()->json($response, $code);
    }
}