<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BatchUpload extends Model
{
    protected $fillable = [
        'batch_id',
        'admin_email',
        'admin_name',
        'upload_type',
        'file_name',
        'file_path',
        'total_rows',
        'successful_imports',
        'failed_imports',
        'import_summary',
        'errors',
        'status',
        'batch_number',
        'school_year',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'import_summary' => 'array',
        'errors' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    /**
     * Generate a unique batch ID
     */
    public static function generateBatchId($type, $schoolYear, $batchNumber)
    {
        $typePrefix = strtoupper($type);
        $batchStr = str_pad($batchNumber, 2, '0', STR_PAD_LEFT);
        return "BATCH_{$typePrefix}_{$schoolYear}_B{$batchStr}_" . strtoupper(Str::random(6));
    }

    /**
     * Get formatted upload type
     */
    public function getFormattedUploadTypeAttribute()
    {
        return ucfirst($this->upload_type);
    }

    /**
     * Get status color for display
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'processing' => 'warning',
            'completed' => 'success',
            'failed' => 'danger'
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Get processing duration
     */
    public function getProcessingDurationAttribute()
    {
        if (!$this->completed_at) {
            return 'Processing...';
        }

        $duration = $this->started_at->diffInSeconds($this->completed_at);
        
        if ($duration < 60) {
            return $duration . ' seconds';
        } elseif ($duration < 3600) {
            return round($duration / 60, 1) . ' minutes';
        } else {
            return round($duration / 3600, 1) . ' hours';
        }
    }

    /**
     * Get success rate percentage
     */
    public function getSuccessRateAttribute()
    {
        if ($this->total_rows == 0) {
            return 0;
        }

        return round(($this->successful_imports / $this->total_rows) * 100, 1);
    }

    /**
     * Get formatted batch info
     */
    public function getFormattedBatchAttribute()
    {
        if ($this->batch_number && $this->school_year) {
            return "Batch {$this->batch_number} ({$this->school_year})";
        }
        return 'N/A';
    }

    /**
     * Scope for recent uploads
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
     * Scope for specific upload type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('upload_type', $type);
    }

    /**
     * Scope for specific batch
     */
    public function scopeByBatch($query, $batchNumber, $schoolYear = null)
    {
        $query->where('batch_number', $batchNumber);
        
        if ($schoolYear) {
            $query->where('school_year', $schoolYear);
        }
        
        return $query;
    }

    /**
     * Scope for specific school year
     */
    public function scopeBySchoolYear($query, $schoolYear)
    {
        return $query->where('school_year', $schoolYear);
    }
}