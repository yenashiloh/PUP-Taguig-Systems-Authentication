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
        'program', 'year', 'section', 'birthdate', 'status',  'employment_status', 
    ];

    protected $hidden = ['password'];

    // Relationship with Course (assuming program field matches course_name)
    public function course()
    {
        return $this->belongsTo(Course::class, 'program', 'course_name');
    }

    public function departmentRelation()
    {
        return $this->belongsTo(Department::class, 'department', 'dept_name');
    }

    // Scope for faculty only
    public function scopeFaculty($query)
    {
        return $query->where('role', 'Faculty');
    }

    // Scope for students only
    public function scopeStudents($query)
    {
        return $query->where('role', 'Student')
                    ->whereNotNull('program')
                    ->whereNotNull('year')
                    ->whereNotNull('section');
    }

    // Static method to get program counts
    public static function getProgramCounts()
    {
        return self::students()
                   ->groupBy('program')
                   ->selectRaw('program, COUNT(*) as count')
                   ->pluck('count', 'program');
    }

    // Static method to get year counts
    public static function getYearCounts()
    {
        return self::students()
                   ->groupBy('year')
                   ->selectRaw('year, COUNT(*) as count')
                   ->pluck('count', 'year');
    }

    // Static method to get section counts
    public static function getSectionCounts()
    {
        return self::students()
                   ->groupBy('section')
                   ->selectRaw('section, COUNT(*) as count')
                   ->pluck('count', 'section');
    }

    // Static method to get status counts
    public static function getStatusCounts()
    {
        return self::students()
                   ->groupBy('status')
                   ->selectRaw('status, COUNT(*) as count')
                   ->pluck('count', 'status');
    }

    // Static method to get all filter counts at once
    public static function getAllFilterCounts()
    {
        return [
            'programs' => self::getProgramCounts(),
            'years' => self::getYearCounts(),
            'sections' => self::getSectionCounts(),
            'statuses' => self::getStatusCounts(),
        ];
    }

     // Static method to get faculty filter counts
    public static function getFacultyFilterCounts()
    {
        return [
            'departments' => self::faculty()
                                ->groupBy('department')
                                ->selectRaw('department, COUNT(*) as count')
                                ->pluck('count', 'department'),
            'employment_statuses' => self::faculty()
                                       ->groupBy('employment_status')
                                       ->selectRaw('employment_status, COUNT(*) as count')
                                       ->pluck('count', 'employment_status'),
            'statuses' => self::faculty()
                            ->groupBy('status')
                            ->selectRaw('status, COUNT(*) as count')
                            ->pluck('count', 'status'),
        ];
    }
}