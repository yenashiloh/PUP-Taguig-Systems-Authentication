<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $primaryKey = 'department_id';

    protected $fillable = [
        'dept_name', 'status',
    ];

    // Scope for active departments
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope for ordered departments
    public function scopeOrdered($query)
    {
        return $query->orderBy('dept_name', 'asc');
    }

    // Relationship with users (faculty)
    public function faculty()
    {
        return $this->hasMany(User::class, 'department', 'dept_name');
    }

    
}