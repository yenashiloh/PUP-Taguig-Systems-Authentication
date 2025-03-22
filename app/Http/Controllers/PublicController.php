<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PublicController extends Controller
{
    // Show login page
    public function showLoginPage()
    {
        return view('login');
    }

    // Show sign up page
    public function showSignUpPage()
    {
        return view('sign-up');
    }

    // Store user account
    public function store(Request $request)
    {
        // Email Address Validation
        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser) {
            return response()->json([
                'message' => 'The email is already taken. Please use another email address.',
                'errors' => [
                    'email' => ['The email is already taken. Please use another email address.']
                ]
            ], 422);
        }
    
        try {
            $validatedData = $request->validate([
                'role' => 'required|in:Student,Faculty',
                'email' => 'required|email|unique:users,email',
                'first_name' => 'required|string|max:50|regex:/^[A-Za-z\s]{2,50}$/',
                'last_name' => 'required|string|max:50|regex:/^[A-Za-z\s]{2,50}$/',
                'middle_name' => 'nullable|string|max:50',
            ]);
    
            // Validation based on role
            if ($request->role == 'Faculty') {
                $request->validate([
                    'phone_number' => 'required|regex:/^[0-9]{11}$/',
                    'department' => 'required|string',
                ]);
            } else { // Student
                $request->validate([
                    'program' => 'required|string',
                    'year' => 'required|in:1,2,3,4,5',
                    'section' => 'required|in:1,2,3,4,5,6,7,8,9,10',
                    'birthdate' => [
                        'required',
                        'date',
                        'before:today',
                        function ($attribute, $value, $fail) {
                            $birthdate = new \DateTime($value);
                            $today = new \DateTime();
                            $age = $birthdate->diff($today)->y;
                            if ($age < 15) {
                                $fail('You must be at least 15 years old.');
                            }
                        },
                    ],
                ]);
            }
    
            // Generate a password
            $randomNumbers = rand(10000, 99999);
            $firstTwoLetters = strtoupper(substr($request->first_name, 0, 1) . substr($request->last_name, 0, 1));
            $specialChar = Str::random(1, "!@#$%^&*");
    
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
    
            // Send Email with HTML Template
            Mail::send('emails.credentials', ['user' => $user, 'password' => $password], function($message) use ($user) {
                $message->to($user->email)
                        ->subject('PUP-Taguig Systems - Your Account Details');
            });
    
            return response()->json(['message' => 'Account created successfully! Login details have been sent to your email.']);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating your account.',
                'errors' => ['general' => [$e->getMessage()]]
            ], 500);
        }
    }
}
