<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserAccountController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'role' => 'required',
            'email' => 'required|email|unique:users,email',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
        ]);

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

        // Send Email with Credentials
        Mail::raw("Your account has been created.\nEmail: {$request->email}\nPassword: {$password}", function ($message) use ($request) {
            $message->to($request->email)
                    ->subject('Your Account Details');
        });

        return response()->json(['message' => 'Account created successfully! Login details have been sent to your email.']);
    }
}
