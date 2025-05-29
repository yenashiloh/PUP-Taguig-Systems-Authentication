<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserValidation extends Model
{
    protected $fillable = [
        'validation_type',
        'min_digits',
        'max_digits',
        'numbers_only',
        'letters_only',
        'letters_symbols_numbers',
        'is_active'
    ];

    protected $casts = [
        'numbers_only' => 'boolean',
        'letters_only' => 'boolean',
        'letters_symbols_numbers' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Get validation rules for student number
    public static function getStudentNumberRules()
    {
        $validation = self::where('validation_type', 'student_number')
                         ->where('is_active', true)
                         ->first();
        
        if (!$validation) {
            return 'required|string|min:5|max:20'; // default rules
        }

        $rules = ['required', 'string'];
        $rules[] = 'min:' . $validation->min_digits;
        $rules[] = 'max:' . $validation->max_digits;

        if ($validation->numbers_only) {
            $rules[] = 'regex:/^[0-9]+$/';
        } elseif ($validation->letters_only) {
            $rules[] = 'regex:/^[A-Za-z]+$/';
        } elseif ($validation->letters_symbols_numbers) {
            $rules[] = 'regex:/^[A-Za-z0-9\-_]+$/';
        }

        return $rules;
    }

    // Get validation rules for employee number
    public static function getEmployeeNumberRules()
    {
        $validation = self::where('validation_type', 'employee_number')
                         ->where('is_active', true)
                         ->first();
        
        if (!$validation) {
            return 'required|string|min:3|max:20'; // default rules
        }

        $rules = ['required', 'string'];
        $rules[] = 'min:' . $validation->min_digits;
        $rules[] = 'max:' . $validation->max_digits;

        if ($validation->numbers_only) {
            $rules[] = 'regex:/^[0-9]+$/';
        } elseif ($validation->letters_only) {
            $rules[] = 'regex:/^[A-Za-z]+$/';
        } elseif ($validation->letters_symbols_numbers) {
            $rules[] = 'regex:/^[A-Za-z0-9\-_]+$/';
        }

        return $rules;
    }

    // Get validation message
    public function getValidationMessage()
    {
        $message = "must be between {$this->min_digits} and {$this->max_digits} characters";
        
        if ($this->numbers_only) {
            $message .= " and contain only numbers";
        } elseif ($this->letters_only) {
            $message .= " and contain only letters";
        } elseif ($this->letters_symbols_numbers) {
            $message .= " and contain only letters, numbers, hyphens and underscores";
        }

        return $message;
    }
}