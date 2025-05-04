<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class PublicController extends Controller
{
    // Show login page
    public function showLoginPage()
    {
        return view('login');
    }

    // Login Post                                                                                                                                                 
    public function loginPost(Request $request)
    {
        // Validate the input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);
    
        // Check if validation fails
        if ($validator->fails()) {
            return redirect()->route('login')
                            ->withErrors($validator)
                            ->withInput();
        }
    
        // admin login first
        if (Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password])) {
            return redirect()->route('admin.dashboard');
        }
        
        // regular user login
        if (Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password])) {
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }
    
        return back()->withErrors(['email' => 'The email and password do not match.']);
    }

    // Show sign up page
    public function showSignUpPage()
    {
        return view('sign-up');
    }

    // Store user account
    public function store(Request $request)
    {
        try {
            // First step: Get a more detailed error message
            \Log::info('Registration attempt with data: ' . json_encode($request->all()));
            
            // Email Address Validation first
            $existingUser = User::where('email', $request->email)->first();
            if ($existingUser) {
                return response()->json([
                    'message' => 'The email is already taken. Please use another email address.',
                    'errors' => [
                        'email' => ['The email is already taken. Please use another email address.']
                    ]
                ], 422);
            }
        
            // Basic validation for all users
            $validatedData = $request->validate([
                'role' => 'required|in:Student,Faculty',
                'email' => 'required|email|unique:users,email',
                'first_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
                'middle_name' => 'nullable|string|max:50',
            ]);
        
            // Validation based on role
            if ($request->role == 'Faculty') {
                $facultyData = $request->validate([
                    'phone_number' => 'required|string',
                    'department' => 'required|string',
                    'employee_number' => 'required|string',
                ]);
            } else { // Student
                $studentData = $request->validate([
                    'program' => 'required|string',
                    'year' => 'required|string',
                    'section' => 'required|string',
                    'student_number' => 'required|string',
                    'birthdate' => [
                        'required',
                        'date',
                        'before:today',
                    ],
                ]);
                
                // Age validation separately to avoid potential errors
                $birthdate = new \DateTime($request->birthdate);
                $today = new \DateTime();
                $age = $birthdate->diff($today)->y;
                if ($age < 15) {
                    return response()->json([
                        'message' => 'Validation failed.',
                        'errors' => ['birthdate' => ['You must be at least 15 years old.']]
                    ], 422);
                }
            }
        
            // Generate a password
            $randomNumbers = rand(10000, 99999);
            $firstTwoLetters = strtoupper(substr($request->first_name, 0, 1) . substr($request->last_name, 0, 1));
            
            // Fix the Str::random to use proper syntax
            $specialChars = "!@#$%^&*";
            $specialChar = $specialChars[rand(0, strlen($specialChars) - 1)];
        
            $password = $randomNumbers . $firstTwoLetters . $specialChar;
            $hashedPassword = Hash::make($password);
        
            // Create User Account
            $user = User::create([
                'role' => $request->role,
                'email' => $request->email,
                'password' => $hashedPassword,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name ?? null,
                'last_name' => $request->last_name,
                'phone_number' => $request->phone_number ?? null,
                'employee_number' => $request->employee_number ?? null,
                'department' => $request->department ?? null,
                'student_number' => $request->student_number ?? null,
                'program' => $request->program ?? null,
                'year' => $request->year ?? null,
                'section' => $request->section ?? null,
                'birthdate' => $request->birthdate ?? null,
            ]);
        
            // Add try-catch specifically for mail sending
            try {
                Mail::send('emails.credentials', ['user' => $user, 'password' => $password], function($message) use ($user) {
                    $message->to($user->email)
                            ->subject('PUP-Taguig Systems - Your Account Details');
                });
            } catch (\Exception $mailError) {
                \Log::error('Email sending failed: ' . $mailError->getMessage());
                // Continue execution even if email fails
            }
        
            return response()->json(['message' => 'Account created successfully! Login details have been sent to your email.']);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Registration error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'message' => 'An error occurred while creating your account.',
                'errors' => ['general' => [$e->getMessage()]]
            ], 500);
        }
    }
    // Show forgot password page
    public function showForgotPasswordPage()
    {
        return view('forgot-password');
    }

    // Send password reset link
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'Password reset link has been sent to your email!');
        } else {
            return back()->withErrors(['email' => 'No registered user found with this email address.']);
        }
    }

    // Show reset password form
    public function showResetForm(Request $request, $token)
    {
        return view('reset-password', ['token' => $token, 'email' => $request->email]);
    }

    // Reset password
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);
    
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
    
                $user->save();
    
                event(new PasswordReset($user));
            }
        );
    
        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Your password has been reset successfully! You can now log in.');
        } else {
            return back()->withErrors(['email' => 'Failed to reset the password. Please try again.']);
        }
    }
}
