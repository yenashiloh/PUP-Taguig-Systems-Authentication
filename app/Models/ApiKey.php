<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ApiKey extends Model
{
    protected $fillable = [
        'key_name',
        'key_hash',
        'application_name',
        'developer_name',
        'developer_email',
        'description',
        'allowed_domains',
        'permissions',
        'is_active',
        'request_limit_per_minute',
        'expires_at',
        'created_by'
    ];

    protected $casts = [
        'allowed_domains' => 'array',
        'permissions' => 'array',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    // Relationship with admin who created it
    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    // Generate a new API key
    public static function generateKey($applicationName, $developerName, $developerEmail, $createdBy, $options = [])
    {
        $rawKey = 'pup_' . Str::random(32) . '_' . time();
        $keyName = $options['key_name'] ?? Str::slug($applicationName) . '-key';
        
        $apiKey = self::create([
            'key_name' => $keyName,
            'key_hash' => Hash::make($rawKey),
            'application_name' => $applicationName,
            'developer_name' => $developerName,
            'developer_email' => $developerEmail,
            'description' => $options['description'] ?? null,
            'allowed_domains' => $options['allowed_domains'] ?? [],
            'permissions' => $options['permissions'] ?? ['login_user'],
            'request_limit_per_minute' => $options['rate_limit'] ?? 100,
            'expires_at' => $options['expires_at'] ?? null,
            'created_by' => $createdBy,
        ]);

        return [
            'api_key' => $apiKey,
            'raw_key' => $rawKey // Only returned once during creation
        ];
    }

    // Verify if a raw key matches this hashed key
    public function verifyKey($rawKey)
    {
        return Hash::check($rawKey, $this->key_hash);
    }

    // Check if key is valid (active and not expired)
    public function isValid()
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    // Record usage
    public function recordUsage()
    {
        $this->increment('total_requests');
        $this->update(['last_used_at' => now()]);
    }

    // Check if domain is allowed
    public function isDomainAllowed($domain)
    {
        if (empty($this->allowed_domains)) {
            return true; // No restrictions
        }

        return in_array($domain, $this->allowed_domains);
    }

    // Check if API key has specific permission
    public function hasPermission($permission)
    {
        return in_array($permission, $this->permissions ?? []);
    }

    // Scope for active keys
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    // Get formatted permissions
    public function getFormattedPermissionsAttribute()
    {
        $permissionMap = [
            'add_user' => 'Add User/Batch Upload',
            'update_user' => 'Update User Information',
            'deactivate_user' => 'Deactivate User',
            'login_user' => 'Login User',
            'logout_user' => 'Logout User',
        ];

        return collect($this->permissions)->map(function($perm) use ($permissionMap) {
            return $permissionMap[$perm] ?? ucwords(str_replace('_', ' ', $perm));
        })->toArray();
    }

    // Get available permissions list (for validation)
    public static function getAvailablePermissions()
    {
        return [
            'add_user' => 'Add User/Batch Upload',
            'update_user' => 'Update User Information',
            'deactivate_user' => 'Deactivate User',
            'login_user' => 'Login User',
            'logout_user' => 'Logout User',
        ];
    }
}