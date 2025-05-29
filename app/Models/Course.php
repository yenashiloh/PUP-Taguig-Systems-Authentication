<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $primaryKey = 'course_id';

    protected $fillable = ['course_name', 'department_id', 'status'];

    // Relationship with Department
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    // Relationship with Users (students)
    public function students()
    {
        return $this->hasMany(User::class, 'program', 'course_name');
    }

    // Scope for active courses
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope for ordered courses
    public function scopeOrdered($query)
    {
        return $query->orderBy('course_name', 'asc');
    }

    // Scope to include department information
    public function scopeWithDepartment($query)
    {
        return $query->with('department');
    }
}