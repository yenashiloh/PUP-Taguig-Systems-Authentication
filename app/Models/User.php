<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'role', 'email', 'password', 'first_name', 'middle_name', 'last_name',
        'phone_number', 'employee_number', 'department', 'student_number',
        'program', 'year', 'section', 'birthdate'
    ];

    protected $hidden = ['password'];
}
