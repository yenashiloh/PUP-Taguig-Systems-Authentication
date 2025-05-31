<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditTrail;
use App\Models\BatchUpload;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuditTrailController extends Controller
{
    /**
     * Display audit trail logs
     */
   public function auditTrailPage(Request $request)
{
    $admin = Auth::guard('admin')->user();
    
    // Build query for audit trails
    $query = AuditTrail::query()->orderBy('created_at', 'desc');
    
    // Apply filters
    if ($request->filled('action')) {
        $query->where('action', $request->action);
    }
    
    if ($request->filled('admin_email')) {
        $query->where('admin_email', $request->admin_email);
    }
    
    if ($request->filled('target_type')) {
        $query->where('target_type', $request->target_type);
    }
    
    // New batch filtering
    if ($request->filled('batch_number')) {
        $query->whereJsonContains('details->batch_number', (int)$request->batch_number);
    }
    
    if ($request->filled('school_year')) {
        $query->whereJsonContains('details->school_year', (int)$request->school_year);
    }
    
    if ($request->filled('date_from')) {
        $query->whereDate('created_at', '>=', $request->date_from);
    }
    
    if ($request->filled('date_to')) {
        $query->whereDate('created_at', '<=', $request->date_to);
    }
    
    // Paginate results
    $auditTrails = $query->paginate(50);
    
    // Get filter options
    $actions = AuditTrail::select('action')
                        ->distinct()
                        ->orderBy('action')
                        ->pluck('action');
                        
    $adminEmails = AuditTrail::select('admin_email')
                            ->distinct()
                            ->orderBy('admin_email')
                            ->pluck('admin_email');
                            
    $targetTypes = AuditTrail::select('target_type')
                            ->whereNotNull('target_type')
                            ->distinct()
                            ->orderBy('target_type')
                            ->pluck('target_type');
    
    // Get batch numbers and school years from batch uploads
    $batchNumbers = BatchUpload::select('batch_number')
                              ->distinct()
                              ->whereNotNull('batch_number')
                              ->orderBy('batch_number')
                              ->pluck('batch_number');
                              
    $schoolYears = BatchUpload::select('school_year')
                             ->distinct()
                             ->whereNotNull('school_year')
                             ->orderBy('school_year', 'desc')
                             ->pluck('school_year');
    
    // Get summary statistics
    $totalLogs = AuditTrail::count();
    $logsToday = AuditTrail::whereDate('created_at', today())->count();
    $logsThisWeek = AuditTrail::whereBetween('created_at', [
        now()->startOfWeek(),
        now()->endOfWeek()
    ])->count();
    $logsThisMonth = AuditTrail::whereMonth('created_at', now()->month)
                             ->whereYear('created_at', now()->year)
                             ->count();
    
    // Batch upload statistics
    $totalBatchUploads = BatchUpload::count();
    $batchUploadsToday = BatchUpload::whereDate('created_at', today())->count();
    $successfulBatches = BatchUpload::where('status', 'completed')->count();
    $failedBatches = BatchUpload::where('status', 'failed')->count();
    
    return view('admin.audit-trail.audit-trail', compact(
        'admin',
        'auditTrails',
        'actions',
        'adminEmails',
        'targetTypes',
        'batchNumbers',
        'schoolYears',
        'totalLogs',
        'logsToday',
        'logsThisWeek',
        'logsThisMonth',
        'totalBatchUploads',
        'batchUploadsToday',
        'successfulBatches',
        'failedBatches'
    ));
}
    /**
     * Show detailed audit trail entry
     */
    public function show($id)
    {
        $admin = Auth::guard('admin')->user();
        $auditTrail = AuditTrail::findOrFail($id);
        
        return view('admin.audit-trail.show', compact('admin', 'auditTrail'));
    }
    
    /**
     * Export audit trail logs
     */
    public function export(Request $request)
    {
        try {
            // Build query based on filters
            $query = AuditTrail::query()->orderBy('created_at', 'desc');
            
            // Apply same filters as index
            if ($request->filled('action')) {
                $query->where('action', $request->action);
            }
            
            if ($request->filled('admin_email')) {
                $query->where('admin_email', $request->admin_email);
            }
            
            if ($request->filled('target_type')) {
                $query->where('target_type', $request->target_type);
            }
            
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            
            $auditTrails = $query->get();
            
            if ($auditTrails->isEmpty()) {
                return redirect()->back()->with('error', 'No audit trail data found matching the selected filters.');
            }
            
            // Create filename with current date and filter info
            $filename = 'audit_trail_' . date('Y-m-d_H-i-s') . '.csv';
            
            // Set headers for CSV download
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0'
            ];
            
            // Create CSV content
            $callback = function() use ($auditTrails, $request) {
                $file = fopen('php://output', 'w');
                
                // Add BOM for proper UTF-8 encoding in Excel
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                // Add filter information as comments
                fputcsv($file, ['# Audit Trail Export']);
                fputcsv($file, ['# Generated on: ' . date('Y-m-d H:i:s')]);
                fputcsv($file, ['# Generated by: ' . Auth::guard('admin')->user()->email]);
                
                if ($request->filled('action')) {
                    fputcsv($file, ['# Action Filter: ' . $request->action]);
                }
                if ($request->filled('admin_email')) {
                    fputcsv($file, ['# Admin Filter: ' . $request->admin_email]);
                }
                if ($request->filled('target_type')) {
                    fputcsv($file, ['# Target Type Filter: ' . $request->target_type]);
                }
                if ($request->filled('date_from') || $request->filled('date_to')) {
                    $dateRange = 'Date Range: ';
                    $dateRange .= $request->filled('date_from') ? $request->date_from : 'Start';
                    $dateRange .= ' to ';
                    $dateRange .= $request->filled('date_to') ? $request->date_to : 'End';
                    fputcsv($file, ['# ' . $dateRange]);
                }
                
                fputcsv($file, ['# Total Records: ' . $auditTrails->count()]);
                fputcsv($file, ['']); // Empty row for separation
                
                // CSV Headers
                $headers = [
                    'Date/Time',
                    'Admin Email',
                    'Admin Name',
                    'Action',
                    'Description',
                    'Target Type',
                    'Target Name',
                    'IP Address',
                    'User Agent'
                ];
                
                fputcsv($file, $headers);
                
                // Add audit trail data
                foreach ($auditTrails as $trail) {
                    $row = [
                        $trail->created_at->format('Y-m-d H:i:s'),
                        $trail->admin_email ?? 'N/A',
                        $trail->admin_name ?? 'N/A',
                        $trail->formatted_action ?? $trail->action,
                        $trail->description ?? 'N/A',
                        $trail->target_type ?? 'N/A',
                        $trail->target_name ?? 'N/A',
                        $trail->ip_address ?? 'N/A',
                        $trail->user_agent ?? 'N/A'
                    ];
                    
                    fputcsv($file, $row);
                }
                
                fclose($file);
            };
            
            // Log the export activity
            \Log::info('Audit trail exported by admin: ' . Auth::guard('admin')->user()->email);
            
            return response()->stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            \Log::error('Audit trail export error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to export audit trail data. Please try again.');
        }
    }
    
    /**
     * Get audit trail statistics for dashboard
     */
    public function getStatistics()
    {
        $stats = [
            'total_logs' => AuditTrail::count(),
            'logs_today' => AuditTrail::whereDate('created_at', today())->count(),
            'logs_this_week' => AuditTrail::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'logs_this_month' => AuditTrail::whereMonth('created_at', now()->month)
                                        ->whereYear('created_at', now()->year)
                                        ->count(),
            'recent_activities' => AuditTrail::orderBy('created_at', 'desc')
                                           ->limit(10)
                                           ->get()
                                           ->map(function($trail) {
                                               return [
                                                   'action' => $trail->formatted_action,
                                                   'description' => $trail->description,
                                                   'admin' => $trail->admin_name,
                                                   'time' => $trail->created_at->diffForHumans(),
                                                   'color' => $trail->action_color
                                               ];
                                           })
        ];
        
        return response()->json($stats);
    }
    
    /**
     * Clean old audit trail logs
     */
    public function cleanOldLogs(Request $request)
    {
        try {
            $request->validate([
                'days' => 'required|integer|min:30|max:365'
            ]);
            
            $days = $request->days;
            $cutoffDate = now()->subDays($days);
            
            $deletedCount = AuditTrail::where('created_at', '<', $cutoffDate)->delete();
            
            // Log the cleanup action
            AuditTrail::log(
                'cleanup_audit_logs',
                "Cleaned up $deletedCount audit log entries older than $days days",
                'AuditTrail',
                null,
                'System Cleanup',
                [
                    'deleted_count' => $deletedCount,
                    'cutoff_date' => $cutoffDate->format('Y-m-d H:i:s'),
                    'retention_days' => $days
                ]
            );
            
            return response()->json([
                'success' => true,
                'message' => "Successfully deleted $deletedCount old audit log entries.",
                'deleted_count' => $deletedCount
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Audit trail cleanup error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to clean up old audit logs.'
            ], 500);
        }
    }
    
    /**
     * Display batch upload history
     */
    public function batchUploads(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        
        // Build query for batch uploads
        $query = BatchUpload::query()->orderBy('created_at', 'desc');
        
        // Apply filters
        if ($request->filled('upload_type')) {
            $query->where('upload_type', $request->upload_type);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('admin_email')) {
            $query->where('admin_email', $request->admin_email);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Paginate results
        $batchUploads = $query->paginate(20);
        
        // Get filter options
        $uploadTypes = BatchUpload::select('upload_type')
                                 ->distinct()
                                 ->orderBy('upload_type')
                                 ->pluck('upload_type');
                                 
        $statuses = BatchUpload::select('status')
                              ->distinct()
                              ->orderBy('status')
                              ->pluck('status');
                              
        $adminEmails = BatchUpload::select('admin_email')
                                 ->distinct()
                                 ->orderBy('admin_email')
                                 ->pluck('admin_email');
        
        // Get summary statistics
        $totalUploads = BatchUpload::count();
        $successfulUploads = BatchUpload::where('status', 'completed')->count();
        $failedUploads = BatchUpload::where('status', 'failed')->count();
        $processingUploads = BatchUpload::where('status', 'processing')->count();
        
        return view('admin.audit-trail.batch-uploads', compact(
            'admin',
            'batchUploads',
            'uploadTypes',
            'statuses',
            'adminEmails',
            'totalUploads',
            'successfulUploads',
            'failedUploads',
            'processingUploads'
        ));
    }
    
    /**
     * Show detailed batch upload information
     */
    public function showBatchUpload($id)
    {
        $admin = Auth::guard('admin')->user();
        $batchUpload = BatchUpload::findOrFail($id);
        
        return view('admin.audit-trail.batch-upload-details', compact('admin', 'batchUpload'));
    }
}