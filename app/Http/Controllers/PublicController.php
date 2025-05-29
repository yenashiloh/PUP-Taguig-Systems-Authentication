<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\Course;
use App\Models\UserValidation;
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
        // Get active departments and courses for dropdowns
        $departments = Department::where('status', 'Active')->orderBy('dept_name', 'asc')->get();
        $courses = Course::where('status', 'Active')->orderBy('course_name', 'asc')->get();
        
        // Get validation settings for frontend
        $studentValidation = UserValidation::where('validation_type', 'student_number')->where('is_active', true)->first();
        $employeeValidation = UserValidation::where('validation_type', 'employee_number')->where('is_active', true)->first();
        
        return view('sign-up', compact('departments', 'courses', 'studentValidation', 'employeeValidation'));
    }

    // Store user account
    public function store(Request $request)
    {
        try {
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
                'first_name' => 'required|string|max:50|min:2|regex:/^[a-zA-Z\s]+$/',
                'last_name' => 'required|string|max:50|min:2|regex:/^[a-zA-Z\s]+$/',
                'middle_name' => 'nullable|string|max:50|regex:/^[a-zA-Z\s]+$/',
            ], [
                'first_name.regex' => 'First name can only contain letters and spaces.',
                'last_name.regex' => 'Last name can only contain letters and spaces.',
                'middle_name.regex' => 'Middle name can only contain letters and spaces.',
            ]);
        
            // Validation based on role
            if ($request->role == 'Faculty') {
                // Check if employee number already exists
                $existingEmployee = User::where('employee_number', $request->employee_number)->first();
                if ($existingEmployee) {
                    return response()->json([
                        'message' => 'The employee number is already taken. Please use another employee number.',
                        'errors' => [
                            'employee_number' => ['The employee number is already taken. Please use another employee number.']
                        ]
                    ], 422);
                }

                // Get employee number validation rules
                $employeeRules = UserValidation::getEmployeeNumberRules();
                $employeeValidation = UserValidation::where('validation_type', 'employee_number')->where('is_active', true)->first();
                
                $facultyValidationRules = [
                    'phone_number' => 'required|string|min:10|max:15',
                    'department' => 'required|string',
                    'employee_number' => array_merge(['required', 'unique:users,employee_number'], is_array($employeeRules) ? $employeeRules : explode('|', $employeeRules)),
                    'employment_status' => 'required|in:Full-Time,Part-Time',
                    'birthdate' => [
                        'required',
                        'date',
                        'before:today',
                        'after:1900-01-01',
                    ],
                ];

                // Add custom error message for employee number
                $customMessages = [];
                if ($employeeValidation) {
                    $customMessages['employee_number.regex'] = 'Employee number ' . $employeeValidation->getValidationMessage() . '.';
                    $customMessages['employee_number.min'] = 'Employee number must be at least ' . $employeeValidation->min_digits . ' characters.';
                    $customMessages['employee_number.max'] = 'Employee number cannot exceed ' . $employeeValidation->max_digits . ' characters.';
                }

                $facultyData = $request->validate($facultyValidationRules, $customMessages);

                // Age validation for faculty (must be at least 18)
                $birthdate = new \DateTime($request->birthdate);
                $today = new \DateTime();
                $age = $birthdate->diff($today)->y;
                if ($age < 18) {
                    return response()->json([
                        'message' => 'Validation failed.',
                        'errors' => ['birthdate' => ['Faculty members must be at least 18 years old.']]
                    ], 422);
                }
                if ($age > 100) {
                    return response()->json([
                        'message' => 'Validation failed.',
                        'errors' => ['birthdate' => ['Please enter a valid birthdate.']]
                    ], 422);
                }
            } else { // Student
                // Check if student number already exists
                $existingStudent = User::where('student_number', $request->student_number)->first();
                if ($existingStudent) {
                    return response()->json([
                        'message' => 'The student number is already taken. Please use another student number.',
                        'errors' => [
                            'student_number' => ['The student number is already taken. Please use another student number.']
                        ]
                    ], 422);
                }

                // Get student number validation rules
                $studentRules = UserValidation::getStudentNumberRules();
                $studentValidation = UserValidation::where('validation_type', 'student_number')->where('is_active', true)->first();

                $studentValidationRules = [
                    'program' => 'required|string',
                    'year' => 'required|string',
                    'section' => 'required|string',
                    'student_number' => array_merge(['required', 'unique:users,student_number'], is_array($studentRules) ? $studentRules : explode('|', $studentRules)),
                    'birthdate' => [
                        'required',
                        'date',
                        'before:today',
                        'after:1900-01-01',
                    ],
                ];

                // Add custom error message for student number
                $customMessages = [];
                if ($studentValidation) {
                    $customMessages['student_number.regex'] = 'Student number ' . $studentValidation->getValidationMessage() . '.';
                    $customMessages['student_number.min'] = 'Student number must be at least ' . $studentValidation->min_digits . ' characters.';
                    $customMessages['student_number.max'] = 'Student number cannot exceed ' . $studentValidation->max_digits . ' characters.';
                }

                $studentData = $request->validate($studentValidationRules, $customMessages);
                
                // Age validation for students (must be at least 15)
                $birthdate = new \DateTime($request->birthdate);
                $today = new \DateTime();
                $age = $birthdate->diff($today)->y;
                if ($age < 15) {
                    return response()->json([
                        'message' => 'Validation failed.',
                        'errors' => ['birthdate' => ['You must be at least 15 years old.']]
                    ], 422);
                }
                if ($age > 100) {
                    return response()->json([
                        'message' => 'Validation failed.',
                        'errors' => ['birthdate' => ['Please enter a valid birthdate.']]
                    ], 422);
                }
            }
        
            // Generate a password
            $randomNumbers = rand(10000, 99999);
            $firstTwoLetters = strtoupper(substr($request->first_name, 0, 1) . substr($request->last_name, 0, 1));
            
            $specialChars = "!@#$%^&*";
            $specialChar = $specialChars[rand(0, strlen($specialChars) - 1)];
        
            $password = $randomNumbers . $firstTwoLetters . $specialChar;
            $hashedPassword = Hash::make($password);
        
            // Create User Account
            $user = User::create([
                'role' => $request->role,
                'status' => 'Active',
                'email' => strtolower($request->email),
                'password' => $hashedPassword,
                'first_name' => ucwords(strtolower($request->first_name)),
                'middle_name' => $request->middle_name ? ucwords(strtolower($request->middle_name)) : null,
                'last_name' => ucwords(strtolower($request->last_name)),
                'phone_number' => $request->phone_number ?? null,
                'employee_number' => $request->employee_number ? strtoupper($request->employee_number) : null,
                'department' => $request->department ?? null,
                'employment_status' => $request->employment_status ?? null,
                'student_number' => $request->student_number ? strtoupper($request->student_number) : null,
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