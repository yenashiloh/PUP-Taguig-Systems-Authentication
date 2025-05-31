<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditTrail extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'admin_email',
        'admin_name',
        'action',
        'target_type',
        'target_id',
        'target_name',
        'details',
        'description',
        'ip_address',
        'user_agent',
        'created_at'
    ];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime'
    ];

    /**
     * Log an audit trail entry
     */
    public static function log($action, $description, $targetType = null, $targetId = null, $targetName = null, $details = null)
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin) {
            return null;
        }

        return self::create([
            'admin_email' => $admin->email,
            'admin_name' => $admin->first_name . ' ' . $admin->last_name,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'target_name' => $targetName,
            'details' => $details,
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now()
        ]);
    }

    /**
     * Get formatted action name
     */
    public function getFormattedActionAttribute()
    {
        $actions = [
            'add_student' => 'Add Student',
            'batch_upload_students' => 'Batch Upload Students',
            'add_faculty' => 'Add Faculty',
            'batch_upload_faculty' => 'Batch Upload Faculty',
            'update_student' => 'Update Student',
            'update_faculty' => 'Update Faculty',
            'deactivate_user' => 'Deactivate User',
            'reactivate_user' => 'Reactivate User',
            'bulk_deactivate_users' => 'Bulk Deactivate Users',
            'bulk_reactivate_users' => 'Bulk Reactivate Users',
            'add_department' => 'Add Department',
            'update_department' => 'Update Department',
            'delete_department' => 'Delete Department',
            'add_course' => 'Add Course',
            'update_course' => 'Update Course',
            'delete_course' => 'Delete Course',
        ];

        return $actions[$this->action] ?? ucwords(str_replace('_', ' ', $this->action));
    }

    /**
     * Get action color for display
     */
    public function getActionColorAttribute()
    {
        $colors = [
            'add_student' => 'success',
            'batch_upload_students' => 'info',
            'add_faculty' => 'success',
            'batch_upload_faculty' => 'info',
            'update_student' => 'warning',
            'update_faculty' => 'warning',
            'deactivate_user' => 'danger',
            'reactivate_user' => 'success',
            'bulk_deactivate_users' => 'danger',
            'bulk_reactivate_users' => 'success',
            'add_department' => 'primary',
            'update_department' => 'warning',
            'delete_department' => 'danger',
            'add_course' => 'primary',
            'update_course' => 'warning',
            'delete_course' => 'danger',
        ];

        return $colors[$this->action] ?? 'secondary';
    }

    /**
     * Scope for recent entries
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for specific admin
     */
    public function scopeByAdmin($query, $adminEmail)
    {
        return $query->where('admin_email', $adminEmail);
    }

    /**
     * Scope for specific action
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }
}