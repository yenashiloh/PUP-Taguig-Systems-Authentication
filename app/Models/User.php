<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;  

    protected $fillable = [
        'role', 'email', 'password', 'first_name', 'middle_name', 'last_name',
        'phone_number', 'employee_number', 'department', 'student_number',
        'program', 'year', 'section', 'birthdate', 'status'
    ];

    protected $hidden = ['password'];
}