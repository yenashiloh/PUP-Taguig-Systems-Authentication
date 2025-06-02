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
        'program', 'year', 'section', 'birthdate', 'status', 'employment_status',
        'batch_number', 'school_year',  'api_session_token','last_login_at',
        'last_login_ip'
    ];

    protected $hidden = [
        'password', 
        'api_session_token' // Hide session token from JSON responses
    ];

    // Relationship with Course
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

    // Scope for batch filtering
    public function scopeByBatch($query, $batchNumber)
    {
        return $query->where('batch_number', $batchNumber);
    }

    // Scope for school year filtering
    public function scopeBySchoolYear($query, $schoolYear)
    {
        return $query->where('school_year', $schoolYear);
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

    // Static method to get batch counts
    public static function getBatchCounts()
    {
        return self::whereNotNull('batch_number')
                   ->groupBy('batch_number', 'school_year')
                   ->selectRaw('batch_number, school_year, COUNT(*) as count')
                   ->get()
                   ->mapWithKeys(function ($item) {
                       return ["Batch {$item->batch_number} ({$item->school_year})" => $item->count];
                   });
    }

    // Static method to get all filter counts at once
    public static function getAllFilterCounts()
    {
        return [
            'programs' => self::getProgramCounts(),
            'years' => self::getYearCounts(),
            'sections' => self::getSectionCounts(),
            'statuses' => self::getStatusCounts(),
            'batches' => self::getBatchCounts(),
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
            'batches' => self::faculty()
                            ->whereNotNull('batch_number')
                            ->groupBy('batch_number', 'school_year')
                            ->selectRaw('batch_number, school_year, COUNT(*) as count')
                            ->get()
                            ->mapWithKeys(function ($item) {
                                return ["Batch {$item->batch_number} ({$item->school_year})" => $item->count];
                            }),
        ];
    }

    // Get formatted batch display
    public function getFormattedBatchAttribute()
    {
        if ($this->batch_number && $this->school_year) {
            return "Batch {$this->batch_number} ({$this->school_year})";
        }
        return 'N/A';
    }

    // Verify API session token
    public function verifySessionToken($token)
    {
        return $this->api_session_token && Hash::check($token, $this->api_session_token);
    }

    // Clear API session
    public function clearApiSession()
    {
        $this->update(['api_session_token' => null]);
    }

    // Check if user has active API session
    public function hasActiveApiSession()
    {
        return !is_null($this->api_session_token);
    }

    // Get user's full name
    public function getFullNameAttribute()
    {
        $name = trim($this->first_name);
        
        if ($this->middle_name) {
            $name .= ' ' . trim($this->middle_name);
        }
        
        $name .= ' ' . trim($this->last_name);
        
        return $name;
    }

    // Get user's display ID (student number or employee number)
    public function getDisplayIdAttribute()
    {
        return $this->student_number ?? $this->employee_number ?? 'No ID';
    }
}