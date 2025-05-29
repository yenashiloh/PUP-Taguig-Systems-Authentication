<?php

namespace App\Http\Controllers;

use App\Models\UserValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserValidationController extends Controller
{
    // Show User Validation Page
    public function showUserValidation()
    {
        $admin = Auth::guard('admin')->user();
        
        // Get existing validations or create defaults
        $studentValidation = UserValidation::where('validation_type', 'student_number')->first();
        $employeeValidation = UserValidation::where('validation_type', 'employee_number')->first();
        
        // Create default validations if they don't exist
        if (!$studentValidation) {
            $studentValidation = UserValidation::create([
                'validation_type' => 'student_number',
                'min_digits' => 5,
                'max_digits' => 20,
                'numbers_only' => false,
                'letters_only' => false,
                'letters_symbols_numbers' => true,
                'is_active' => true
            ]);
        }
        
        if (!$employeeValidation) {
            $employeeValidation = UserValidation::create([
                'validation_type' => 'employee_number',
                'min_digits' => 3,
                'max_digits' => 20,
                'numbers_only' => false,
                'letters_only' => false,
                'letters_symbols_numbers' => true,
                'is_active' => true
            ]);
        }
        
        return view('admin.settings.user-validation', compact('admin', 'studentValidation', 'employeeValidation'));
    }

    // Update Student Number Validation
    public function updateStudentValidation(Request $request)
    {
        $request->validate([
            'min_digits' => 'required|integer|min:1|max:50',
            'max_digits' => 'required|integer|min:1|max:50',
            'allowed_characters' => 'required|in:numbers_only,letters_only,letters_symbols_numbers'
        ], [
            'min_digits.required' => 'Minimum digits is required.',
            'min_digits.integer' => 'Minimum digits must be a number.',
            'min_digits.min' => 'Minimum digits must be at least 1.',
            'min_digits.max' => 'Minimum digits cannot exceed 50.',
            'max_digits.required' => 'Maximum digits is required.',
            'max_digits.integer' => 'Maximum digits must be a number.',
            'max_digits.min' => 'Maximum digits must be at least 1.',
            'max_digits.max' => 'Maximum digits cannot exceed 50.',
            'allowed_characters.required' => 'Please select allowed characters.',
            'allowed_characters.in' => 'Invalid character selection.'
        ]);

        // Validate that max is greater than min
        if ($request->max_digits <= $request->min_digits) {
            return response()->json([
                'success' => false,
                'message' => 'Maximum digits must be greater than minimum digits.'
            ]);
        }

        try {
            $validation = UserValidation::where('validation_type', 'student_number')->first();
            
            if (!$validation) {
                $validation = new UserValidation();
                $validation->validation_type = 'student_number';
            }

            $validation->min_digits = $request->min_digits;
            $validation->max_digits = $request->max_digits;
            $validation->numbers_only = $request->allowed_characters === 'numbers_only';
            $validation->letters_only = $request->allowed_characters === 'letters_only';
            $validation->letters_symbols_numbers = $request->allowed_characters === 'letters_symbols_numbers';
            $validation->is_active = true;
            $validation->save();

            return response()->json([
                'success' => true,
                'message' => 'Student number validation updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the validation.'
            ]);
        }
    }

    // Update Employee Number Validation
    public function updateEmployeeValidation(Request $request)
    {
        $request->validate([
            'min_digits' => 'required|integer|min:1|max:50',
            'max_digits' => 'required|integer|min:1|max:50',
            'allowed_characters' => 'required|in:numbers_only,letters_only,letters_symbols_numbers'
        ], [
            'min_digits.required' => 'Minimum digits is required.',
            'min_digits.integer' => 'Minimum digits must be a number.',
            'min_digits.min' => 'Minimum digits must be at least 1.',
            'min_digits.max' => 'Minimum digits cannot exceed 50.',
            'max_digits.required' => 'Maximum digits is required.',
            'max_digits.integer' => 'Maximum digits must be a number.',
            'max_digits.min' => 'Maximum digits must be at least 1.',
            'max_digits.max' => 'Maximum digits cannot exceed 50.',
            'allowed_characters.required' => 'Please select allowed characters.',
            'allowed_characters.in' => 'Invalid character selection.'
        ]);

        // Validate that max is greater than min
        if ($request->max_digits <= $request->min_digits) {
            return response()->json([
                'success' => false,
                'message' => 'Maximum digits must be greater than minimum digits.'
            ]);
        }

        try {
            $validation = UserValidation::where('validation_type', 'employee_number')->first();
            
            if (!$validation) {
                $validation = new UserValidation();
                $validation->validation_type = 'employee_number';
            }

            $validation->min_digits = $request->min_digits;
            $validation->max_digits = $request->max_digits;
            $validation->numbers_only = $request->allowed_characters === 'numbers_only';
            $validation->letters_only = $request->allowed_characters === 'letters_only';
            $validation->letters_symbols_numbers = $request->allowed_characters === 'letters_symbols_numbers';
            $validation->is_active = true;
            $validation->save();

            return response()->json([
                'success' => true,
                'message' => 'Employee number validation updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the validation.'
            ]);
        }
    }
}