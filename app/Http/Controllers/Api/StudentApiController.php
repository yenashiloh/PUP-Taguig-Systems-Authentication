<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Department;
use App\Models\BatchUpload;
use App\Models\AuditTrail;
use App\Models\UserValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StudentApiController extends Controller
{
    /**
     * Get all students
     */
    public function index(Request $request)
    {
        try {
            // Log the API request
            \Log::info('API: Students list requested', [
                'filters' => $request->only(['program', 'year', 'section', 'status']),
                'api_key_id' => $request->apiKeyModel->id ?? null,
                'application_name' => $request->apiKeyModel->application_name ?? 'External App'
            ]);

            $query = User::where('role', 'Student')->orderBy('last_name', 'asc');
            
            // Apply filters if provided
            if ($request->has('program') && !empty($request->program)) {
                $query->where('program', $request->program);
            }
            
            if ($request->has('year') && !empty($request->year)) {
                $query->where('year', $request->year);
            }
            
            if ($request->has('section') && !empty($request->section)) {
                $query->where('section', $request->section);
            }
            
            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }
            
            $students = $query->get();
            
            // Get filter counts
            $filterCounts = User::getAllFilterCounts();
            
            return response()->json([
                'success' => true,
                'message' => 'Students retrieved successfully',
                'data' => [
                    'students' => $students,
                    'counts' => $filterCounts,
                    'total' => $students->count(),
                    'filtered_count' => $students->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('API: Error retrieving students: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve students',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Store a new student
     */
    public function store(Request $request)
    {
        try {
            // Log the API request
            \Log::info('API: Student creation requested', [
                'data' => $request->except(['password']),
                'api_key_id' => $request->apiKeyModel->id ?? null,
                'application_name' => $request->apiKeyModel->application_name ?? 'External App'
            ]);

            // Get student number validation rules
            $studentNumberRules = UserValidation::getStudentNumberRules();
            
            // Validate request
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|unique:users,email',
                'first_name' => [
                    'required',
                    'string',
                    'max:255',
                    'min:2',
                    'regex:/^[a-zA-Z\s]+$/'
                ],
                'middle_name' => [
                    'nullable',
                    'string',
                    'max:255',
                    'regex:/^[a-zA-Z\s]*$/'
                ],
                'last_name' => [
                    'required',
                    'string',
                    'max:255',
                    'min:2',
                    'regex:/^[a-zA-Z\s]+$/'
                ],
                'student_number' => is_array($studentNumberRules) ? $studentNumberRules : explode('|', $studentNumberRules),
                'program' => 'required|string|max:255',
                'year' => 'required|string|in:1st Year,2nd Year,3rd Year,4th Year',
                'section' => 'required|string|in:1,2,3,4,5,6,7,8,9,10',
                'birthdate' => 'nullable|date|before:today',
            ], [
                'email.required' => 'Email address is required.',
                'email.email' => 'Please enter a valid email address.',
                'email.unique' => 'This email address is already taken.',
                'first_name.required' => 'First name is required.',
                'first_name.regex' => 'First name can only contain letters and spaces.',
                'last_name.required' => 'Last name is required.',
                'last_name.regex' => 'Last name can only contain letters and spaces.',
                'student_number.required' => 'Student number is required.',
                'student_number.unique' => 'This student number is already taken.',
                'program.required' => 'Program is required.',
                'year.required' => 'Year is required.',
                'year.in' => 'Please select a valid year.',
                'section.required' => 'Section is required.',
                'section.in' => 'Please select a valid section.',
                'birthdate.before' => 'Birthdate must be before today.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // Additional age validation if birthdate provided
            if (!empty($validated['birthdate'])) {
                $birthDate = new \DateTime($validated['birthdate']);
                $today = new \DateTime();
                $age = $birthDate->diff($today)->y;
                
                if ($age < 15) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Student must be at least 15 years old',
                        'errors' => ['birthdate' => ['Student must be at least 15 years old']]
                    ], 422);
                }

                if ($age > 100) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please enter a valid birthdate',
                        'errors' => ['birthdate' => ['Please enter a valid birthdate']]
                    ], 422);
                }
            }

            // Generate password
            $randomNumbers = rand(10000, 99999);
            $firstTwoLetters = strtoupper(substr($validated['first_name'], 0, 1) . substr($validated['last_name'], 0, 1));
            $specialChars = "!@#$%^&*";
            $specialChar = $specialChars[rand(0, strlen($specialChars) - 1)];
            $password = $randomNumbers . $firstTwoLetters . $specialChar;
            $hashedPassword = Hash::make($password);

            // Create student
            $user = User::create([
                'role' => 'Student',
                'status' => 'Active',
                'email' => strtolower($validated['email']),
                'password' => $hashedPassword,
                'first_name' => ucwords(strtolower($validated['first_name'])),
                'middle_name' => !empty($validated['middle_name']) ? ucwords(strtolower($validated['middle_name'])) : null,
                'last_name' => ucwords(strtolower($validated['last_name'])),
                'student_number' => strtoupper($validated['student_number']),
                'program' => $validated['program'],
                'year' => $validated['year'],
                'section' => $validated['section'],
                'birthdate' => $validated['birthdate'] ?? null,
            ]);

            // Log to audit trail
            AuditTrail::log(
                'add_student_api',
                'Added new student via API: ' . $user->first_name . ' ' . $user->last_name . ' (' . $user->student_number . ')',
                'User',
                $user->id,
                $user->first_name . ' ' . $user->last_name,
                [
                    'student_number' => $user->student_number,
                    'email' => $user->email,
                    'program' => $user->program,
                    'year' => $user->year,
                    'section' => $user->section,
                    'api_key_id' => $request->apiKeyModel->id ?? null,
                    'application_name' => $request->apiKeyModel->application_name ?? 'External App'
                ]
            );

            // Send email notification
            $emailSent = false;
            try {
                Mail::send('emails.credentials', ['user' => $user, 'password' => $password], function($message) use ($user) {
                    $message->to($user->email)
                            ->subject('PUP-Taguig Systems - Your Account Details');
                });
                $emailSent = true;
            } catch (\Exception $mailError) {
                \Log::error('API: Email sending failed: ' . $mailError->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Student created successfully!' . ($emailSent ? ' Login details have been sent to their email.' : ' Email sending failed.'),
                'data' => [
                    'student' => $user,
                    'email_sent' => $emailSent
                ]
            ], 201);

        } catch (\Exception $e) {
            \Log::error('API: Error creating student: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create student',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Show a specific student
     */
    public function show($id)
    {
        try {
            // Log the API request
            \Log::info('API: Student details requested', [
                'student_id' => $id,
                'api_key_id' => request()->apiKeyModel->id ?? null,
                'application_name' => request()->apiKeyModel->application_name ?? 'External App'
            ]);

            $student = User::where('role', 'Student')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'message' => 'Student retrieved successfully',
                'data' => [
                    'student' => $student
                ]
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
                'error' => 'The requested student does not exist'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('API: Error retrieving student: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve student',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update a student
     */
public function update(Request $request, $id)
{
    try {
        // Log the API request
        \Log::info('API: Student update requested', [
            'student_id' => $id,
            'data' => $request->except(['password']),
            'api_key_id' => $request->apiKeyModel->id ?? null,
            'application_name' => $request->apiKeyModel->application_name ?? 'External App'
        ]);

        $student = User::where('role', 'Student')->findOrFail($id);
        
        // Get valid course names for validation
        $validCourses = Course::where('status', 'active')->pluck('course_name')->toArray();
        
        // If no courses found, allow any string for program
        if (empty($validCourses)) {
            $programValidation = 'required|string|max:255';
        } else {
            $programValidation = [
                'required',
                'string',
                'in:' . implode(',', $validCourses)
            ];
        }

        // Get student number validation rules
        $studentNumberRules = UserValidation::getStudentNumberRules();
        if (is_array($studentNumberRules)) {
            $studentNumberRules[] = 'unique:users,student_number,' . $id;
        } else {
            $studentNumberRules = explode('|', $studentNumberRules);
            $studentNumberRules[] = 'unique:users,student_number,' . $id;
        }
        
        // Validate request
        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:255', 'min:2', 'regex:/^[a-zA-Z\s]+$/'],
            'last_name' => ['required', 'string', 'max:255', 'min:2', 'regex:/^[a-zA-Z\s]+$/'],
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'program' => $programValidation,
            'student_number' => $studentNumberRules,
            'year' => ['required', 'string', 'in:1st Year,2nd Year,3rd Year,4th Year'],
            'section' => ['required', 'string', 'in:1,2,3,4,5,6,7,8,9,10'],
            'birthdate' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'middle_name' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z\s]*$/'], // Added nullable middle_name
        ], [
            'first_name.required' => 'First name is required.',
            'first_name.regex' => 'First name can only contain letters and spaces.',
            'last_name.required' => 'Last name is required.',
            'last_name.regex' => 'Last name can only contain letters and spaces.',
            'email.required' => 'Email address is required.',
            'email.unique' => 'This email address is already taken.',
            'program.required' => 'Program is required.',
            'program.in' => 'Please select a valid program.',
            'student_number.required' => 'Student number is required.',
            'student_number.unique' => 'This student number is already taken.',
            'year.required' => 'Year is required.',
            'year.in' => 'Please select a valid year.',
            'section.required' => 'Section is required.',
            'section.in' => 'Please select a valid section.',
            'birthdate.before' => 'Birthdate must be before today.',
            'birthdate.after' => 'Birthdate must be after 1900.',
            'middle_name.regex' => 'Middle name can only contain letters and spaces.',
        ]);

        if ($validator->fails()) {
            \Log::warning('Validation failed', ['errors' => $validator->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // Additional age validation if birthdate provided
        if (!empty($validated['birthdate'])) {
            $birthDate = new \DateTime($validated['birthdate']);
            $today = new \DateTime();
            $age = $birthDate->diff($today)->y;
            
            if ($age < 15 || $age > 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid birthdate',
                    'errors' => ['birthdate' => ['Student must be between 15 and 100 years old']]
                ], 422);
            }
        }

        // Check if any data has changed
        $hasChanges = false;
        $fields = ['first_name', 'middle_name', 'last_name', 'email', 'program', 
                   'student_number', 'year', 'section', 'birthdate'];
        
        foreach ($fields as $field) {
            if ($field === 'birthdate') {
                $formattedDate = !empty($validated['birthdate']) ? Carbon::parse($validated['birthdate'])->format('Y-m-d') : null;
                if ($formattedDate != $student->birthdate) {
                    $hasChanges = true;
                    break;
                }
            } elseif ($field === 'middle_name') {
                // Use null coalescing to handle missing middle_name
                $requestValue = $validated['middle_name'] ?? null;
                $studentValue = $student->$field ?? null;
                if ($requestValue != $studentValue) {
                    $hasChanges = true;
                    break;
                }
            } else {
                if ($validated[$field] != $student->$field) {
                    $hasChanges = true;
                    break;
                }
            }
        }

        if (!$hasChanges) {
            return response()->json([
                'success' => false,
                'message' => 'No changes were made to update'
            ], 400);
        }

        // Store old values for audit trail
        $oldValues = $student->only($fields);

        // Update student
        $student->update([
            'first_name' => ucwords(strtolower($validated['first_name'])),
            'middle_name' => isset($validated['middle_name']) && $validated['middle_name'] ? ucwords(strtolower($validated['middle_name'])) : null,
            'last_name' => ucwords(strtolower($validated['last_name'])),
            'email' => strtolower($validated['email']),
            'program' => $validated['program'],
            'student_number' => strtoupper($validated['student_number']),
            'year' => $validated['year'],
            'section' => $validated['section'],
            'birthdate' => !empty($validated['birthdate']) ? Carbon::parse($validated['birthdate'])->format('Y-m-d') : null,
        ]);

        // Log to audit trail
        AuditTrail::log(
            'update_student_api',
            'Updated student via API: ' . $student->first_name . ' ' . $student->last_name . ' (' . $student->student_number . ')',
            'User',
            $student->id,
            $student->first_name . ' ' . $student->last_name,
            [
                'student_number' => $student->student_number,
                'email' => $student->email,
                'program' => $student->program,
                'year' => $student->year,
                'section' => $student->section,
                'api_key_id' => $request->apiKeyModel->id ?? null,
                'application_name' => $request->apiKeyModel->application_name ?? 'External App',
                'old_values' => $oldValues,
                'new_values' => $student->only($fields)
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Student updated successfully',
            'data' => [
                'student' => $student->fresh()
            ]
        ]);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        \Log::error('Student not found', ['student_id' => $id]);
        return response()->json([
            'success' => false,
            'message' => 'Student not found',
            'error' => 'The requested student does not exist'
        ], 404);
    } catch (\Exception $e) {
        \Log::error('API: Error updating student: ' . $e->getMessage(), ['student_id' => $id]);
        return response()->json([
            'success' => false,
            'message' => 'Failed to update student',
            'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
        ], 500);
    }
}

    /**
     * Toggle student status (activate/deactivate)
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            $student = User::where('role', 'Student')->findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'action' => 'required|in:activate,deactivate,reactivate'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid action',
                    'errors' => $validator->errors()
                ], 422);
            }

            $action = $request->action;
            $oldStatus = $student->status;
            $newStatus = ($action === 'deactivate') ? 'Deactivated' : 'Active';
            
            if ($oldStatus === $newStatus) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student already has the requested status'
                ], 400);
            }

            $student->update(['status' => $newStatus]);

            // Log to audit trail
            $auditAction = $action === 'deactivate' ? 'deactivate_student_api' : 'reactivate_student_api';
            AuditTrail::log(
                $auditAction,
                ucfirst($action) . 'd student via API: ' . $student->first_name . ' ' . $student->last_name . ' (' . $student->student_number . ')',
                'User',
                $student->id,
                $student->first_name . ' ' . $student->last_name,
                [
                    'student_number' => $student->student_number,
                    'email' => $student->email,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'api_key_id' => $request->apiKeyModel->id ?? null,
                    'application_name' => $request->apiKeyModel->application_name ?? 'External App'
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Student ' . $action . 'd successfully',
                'data' => [
                    'student' => $student->fresh()
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('API: Error toggling student status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update student status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk toggle student status
     */
    public function bulkToggleStatus(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_ids' => 'required|array|min:1',
                'user_ids.*' => 'integer|exists:users,id',
                'action' => 'required|in:activate,deactivate,reactivate'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userIds = $request->user_ids;
            $action = $request->action;
            $newStatus = ($action === 'deactivate') ? 'Deactivated' : 'Active';
            
            // Get students to be updated
            $students = User::where('role', 'Student')
                           ->whereIn('id', $userIds)
                           ->get();
            
            if ($students->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No students found to update'
                ], 404);
            }
            
            // Filter students that actually need status change
            $studentsToUpdate = $students->filter(function ($student) use ($newStatus) {
                return $student->status !== $newStatus;
            });
            
            if ($studentsToUpdate->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'All selected students already have the requested status'
                ], 400);
            }
            
            // Update student statuses
            $updatedCount = User::whereIn('id', $studentsToUpdate->pluck('id'))
                               ->update([
                                   'status' => $newStatus,
                                   'updated_at' => now()
                               ]);
            
            // Log bulk action to audit trail
            $auditAction = $action === 'deactivate' ? 'bulk_deactivate_students_api' : 'bulk_reactivate_students_api';
            AuditTrail::log(
                $auditAction,
                "Bulk " . ($action === 'deactivate' ? 'deactivated' : 'reactivated') . " $updatedCount student(s) via API",
                'User',
                null,
                'Bulk Action',
                [
                    'action' => $action,
                    'student_count' => $updatedCount,
                    'new_status' => $newStatus,
                    'api_key_id' => $request->apiKeyModel->id ?? null,
                    'application_name' => $request->apiKeyModel->application_name ?? 'External App',
                    'affected_students' => $studentsToUpdate->map(function($student) use ($newStatus) {
                        return [
                            'id' => $student->id,
                            'name' => $student->first_name . ' ' . $student->last_name,
                            'student_number' => $student->student_number,
                            'email' => $student->email,
                            'old_status' => $student->status,
                            'new_status' => $newStatus
                        ];
                    })->toArray()
                ]
            );
            
            return response()->json([
                'success' => true,
                'message' => "Successfully {$action}d {$updatedCount} student(s)",
                'data' => [
                    'updated_count' => $updatedCount,
                    'total_selected' => count($userIds),
                    'action' => $action,
                    'new_status' => $newStatus
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('API: Error bulk toggling student status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update student statuses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batch upload students
     */
    public function batchUpload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'batch_number' => 'required|integer|min:1|max:10',
            'school_year' => 'required|integer|min:2020|max:' . (date('Y') + 5),
            'upload_files' => 'required|array|min:1|max:10',
            'upload_files.*' => 'required|file|mimes:csv,xlsx,xls|max:10240', // 10MB limit
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $batchNumber = $request->batch_number;
            $schoolYear = $request->school_year;
            $files = $request->file('upload_files');

            // Check total file size
            $totalSize = 0;
            foreach ($files as $file) {
                $totalSize += $file->getSize();
            }

            if ($totalSize > 10 * 1024 * 1024) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total combined file size exceeds the 10MB limit'
                ], 422);
            }
            
            // Generate unique batch ID
            $batchId = BatchUpload::generateBatchId('students', $schoolYear, $batchNumber);
            
            $totalImported = 0;
            $totalFailed = 0;
            $totalRows = 0;
            $allErrors = [];
            $importedUsers = [];
            $emailsSent = 0;
            $emailsFailed = 0;
            
            // Create batch upload record
            $batchUpload = BatchUpload::create([
                'batch_id' => $batchId,
                'admin_email' => 'external_api',
                'admin_name' => $request->apiKeyModel->application_name ?? 'External App',
                'upload_type' => 'students',
                'file_name' => count($files) . ' files uploaded via API',
                'file_path' => 'batch_uploads/' . $batchId,
                'total_rows' => 0, // Will be updated
                'batch_number' => $batchNumber,
                'school_year' => $schoolYear,
                'status' => 'processing',
                'started_at' => now()
            ]);
            
            // Process files (similar to admin controller but with API responses)
            foreach ($files as $fileIndex => $file) {
                $fileName = $batchId . '_file_' . ($fileIndex + 1) . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('batch_uploads/' . $batchId, $fileName);
                
                $spreadsheet = IOFactory::load($file->getPathname());
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();
                
                if (empty($rows) || count($rows) < 2) {
                    $allErrors[] = "File " . ($fileIndex + 1) . " must contain at least one data row besides the header";
                    continue;
                }
                
                $headers = array_map('strtolower', array_map('trim', $rows[0]));
                array_shift($rows); // Remove header
                $totalRows += count($rows);
                
                foreach ($rows as $rowIndex => $row) {
                    $actualRowNumber = $rowIndex + 2;
                    
                    if (empty(array_filter($row))) continue;
                    
                    // Map data and validate (simplified version)
                    $rowData = [];
                    foreach ($headers as $colIndex => $header) {
                        $value = isset($row[$colIndex]) ? trim($row[$colIndex]) : null;
                        $rowData[str_replace(' ', '_', strtolower($header))] = $value;
                    }
                    
                    // Basic validation
                    $errors = [];
                    if (empty($rowData['email']) || !filter_var($rowData['email'], FILTER_VALIDATE_EMAIL)) {
                        $errors[] = "Invalid email";
                    }
                    if (empty($rowData['first_name'])) $errors[] = "First name required";
                    if (empty($rowData['last_name'])) $errors[] = "Last name required";
                    if (empty($rowData['student_number'])) $errors[] = "Student number required";
                    if (empty($rowData['program'])) $errors[] = "Program required";
                    if (empty($rowData['year'])) $errors[] = "Year required";
                    if (empty($rowData['section'])) $errors[] = "Section required";
                    
                    // Check for duplicates
                    if (User::where('email', strtolower($rowData['email']))->exists()) {
                        $errors[] = "Email already exists";
                    }
                    if (User::where('student_number', $rowData['student_number'])->exists()) {
                        $errors[] = "Student number already exists";
                    }
                    
                    if (!empty($errors)) {
                        $allErrors[] = "File " . ($fileIndex + 1) . " Row $actualRowNumber: " . implode(', ', $errors);
                        $totalFailed++;
                        continue;
                    }
                    
                    try {
                        // Create student
                        $randomNumbers = rand(10000, 99999);
                        $firstTwoLetters = strtoupper(substr($rowData['first_name'], 0, 1) . substr($rowData['last_name'], 0, 1));
                        $specialChars = "!@#$%^&*";
                        $specialChar = $specialChars[rand(0, strlen($specialChars) - 1)];
                        $password = $randomNumbers . $firstTwoLetters . $specialChar;

                        $user = User::create([
                            'role' => 'Student',
                            'email' => strtolower($rowData['email']),
                            'password' => Hash::make($password),
                            'first_name' => ucwords(strtolower($rowData['first_name'])),
                            'middle_name' => !empty($rowData['middle_name']) ? ucwords(strtolower($rowData['middle_name'])) : null,
                            'last_name' => ucwords(strtolower($rowData['last_name'])),
                            'student_number' => strtoupper($rowData['student_number']),
                            'program' => $rowData['program'],
                            'year' => $rowData['year'],
                            'section' => strtoupper($rowData['section']),
                            'birthdate' => !empty($rowData['birthdate']) ? Carbon::parse($rowData['birthdate'])->format('Y-m-d') : null,
                            'status' => 'Active',
                            'batch_number' => $batchNumber,
                            'school_year' => $schoolYear,
                        ]);
                        
                        $totalImported++;
                        $importedUsers[] = $user;

                        // Send email
                        try {
                            Mail::send('emails.credentials', ['user' => $user, 'password' => $password], function($message) use ($user) {
                                $message->to($user->email)->subject('PUP-Taguig Systems - Your Account Details');
                            });
                            $emailsSent++;
                        } catch (\Exception $mailError) {
                            $emailsFailed++;
                        }
                        
                    } catch (\Exception $e) {
                        $allErrors[] = "File " . ($fileIndex + 1) . " Row $actualRowNumber: Failed to save - " . $e->getMessage();
                        $totalFailed++;
                    }
                }
            }
            
            // Update batch record
            $batchUpload->update([
                'total_rows' => $totalRows,
                'successful_imports' => $totalImported,
                'failed_imports' => $totalFailed,
                'status' => $totalImported > 0 ? 'completed' : 'failed',
                'completed_at' => now(),
                'errors' => !empty($allErrors) ? $allErrors : null
            ]);

            // Log to audit trail
            AuditTrail::log(
                'batch_upload_students_api',
                "Batch uploaded $totalImported students via API (Batch: $batchNumber, School Year: $schoolYear)",
                'BatchUpload',
                $batchUpload->id,
                $batchId,
                [
                    'batch_id' => $batchId,
                    'batch_number' => $batchNumber,
                    'school_year' => $schoolYear,
                    'files_count' => count($files),
                    'total_rows' => $totalRows,
                    'successful_imports' => $totalImported,
                    'failed_imports' => $totalFailed,
                    'emails_sent' => $emailsSent,
                    'emails_failed' => $emailsFailed,
                    'api_key_id' => $request->apiKeyModel->id ?? null,
                    'application_name' => $request->apiKeyModel->application_name ?? 'External App'
                ]
            );
            
            return response()->json([
                'success' => true,
                'message' => "Batch upload completed. Successfully imported $totalImported student(s), $totalFailed failed.",
                'data' => [
                    'batch_id' => $batchId,
                    'total_rows' => $totalRows,
                    'successful_imports' => $totalImported,
                    'failed_imports' => $totalFailed,
                    'emails_sent' => $emailsSent,
                    'emails_failed' => $emailsFailed,
                    'errors' => array_slice($allErrors, 0, 10) // Return first 10 errors
                ]
            ]);
            
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            \Log::error('API: Spreadsheet reading error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to read one or more files. Please ensure they are valid Excel or CSV files.',
                'error' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('API: Batch import error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to import students',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export all students
     */
    public function export(Request $request)
    {
        try {
            $students = User::where('role', 'Student')
                           ->orderBy('last_name', 'asc')
                           ->orderBy('first_name', 'asc')
                           ->get();

            if ($students->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No student data available to export'
                ], 404);
            }

            $filename = 'students_data_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0'
            ];

            $callback = function() use ($students) {
                $file = fopen('php://output', 'w');
                
                // Add BOM for proper UTF-8 encoding
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                // CSV Headers
                $headers = [
                    'Student Number',
                    'Email Address',
                    'First Name',
                    'Middle Name',
                    'Last Name',
                    'Program',
                    'Year',
                    'Section',
                    'Account Status',
                    'Birthdate',
                    'Date Created',
                    'Last Updated'
                ];
                
                fputcsv($file, $headers);
                
                foreach ($students as $student) {
                    $row = [
                        $student->student_number ?? 'N/A',
                        $student->email ?? 'N/A',
                        $student->first_name ?? 'N/A',
                        $student->middle_name ?? 'N/A',
                        $student->last_name ?? 'N/A',
                        $student->program ?? 'N/A',
                        $student->year ?? 'N/A',
                        $student->section ?? 'N/A',
                        $student->status ?? 'N/A',
                        $student->birthdate ? Carbon::parse($student->birthdate)->format('m/d/Y') : 'N/A',
                        $student->created_at ? $student->created_at->format('m/d/Y H:i:s') : 'N/A',
                        $student->updated_at ? $student->updated_at->format('m/d/Y H:i:s') : 'N/A',
                    ];
                    
                    fputcsv($file, $row);
                }
                
                fclose($file);
            };

            // Log export activity
            AuditTrail::log(
                'export_students_api',
                'Exported students data via API',
                'Export',
                null,
                'Students Export',
                [
                    'student_count' => $students->count(),
                    'api_key_id' => $request->apiKeyModel->id ?? null,
                    'application_name' => $request->apiKeyModel->application_name ?? 'External App'
                ]
            );

            return response()->stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            \Log::error('API: Students export error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export student data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export filtered students
     */
    public function exportFiltered(Request $request)
    {
        try {
            $query = User::where('role', 'Student');
            
            // Apply filters
            if ($request->has('program') && !empty($request->program)) {
                $query->where('program', $request->program);
            }
            
            if ($request->has('year') && !empty($request->year)) {
                $query->where('year', $request->year);
            }
            
            if ($request->has('section') && !empty($request->section)) {
                $query->where('section', $request->section);
            }
            
            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }
            
            $students = $query->orderBy('last_name', 'asc')
                            ->orderBy('first_name', 'asc')
                            ->get();

            if ($students->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No student data found matching the selected filters'
                ], 404);
            }

            // Create filename with filter info
            $filterInfo = '';
            if ($request->has('program') && !empty($request->program)) {
                $filterInfo .= '_' . str_replace(' ', '-', strtolower($request->program));
            }
            if ($request->has('year') && !empty($request->year)) {
                $filterInfo .= '_' . str_replace(' ', '', strtolower($request->year));
            }
            if ($request->has('section') && !empty($request->section)) {
                $filterInfo .= '_section' . $request->section;
            }
            if ($request->has('status') && !empty($request->status)) {
                $filterInfo .= '_' . strtolower($request->status);
            }
            
            $filename = 'students_data' . $filterInfo . '_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0'
            ];

            $callback = function() use ($students, $request) {
                $file = fopen('php://output', 'w');
                
                // Add BOM for proper UTF-8 encoding
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                // Add filter information
                fputcsv($file, ['# Students Export with Filters']);
                fputcsv($file, ['# Generated on: ' . date('Y-m-d H:i:s')]);
                
                if ($request->has('program') && !empty($request->program)) {
                    fputcsv($file, ['# Program Filter: ' . $request->program]);
                }
                if ($request->has('year') && !empty($request->year)) {
                    fputcsv($file, ['# Year Filter: ' . $request->year]);
                }
                if ($request->has('section') && !empty($request->section)) {
                    fputcsv($file, ['# Section Filter: ' . $request->section]);
                }
                if ($request->has('status') && !empty($request->status)) {
                    fputcsv($file, ['# Account Status Filter: ' . $request->status]);
                }
                
                fputcsv($file, ['# Total Records: ' . $students->count()]);
                fputcsv($file, ['']); // Empty row
                
                // CSV Headers
                $headers = [
                    'Student Number',
                    'Email Address',
                    'First Name',
                    'Middle Name',
                    'Last Name',
                    'Program',
                    'Year',
                    'Section',
                    'Account Status',
                    'Birthdate',
                    'Date Created',
                    'Last Updated'
                ];
                
                fputcsv($file, $headers);
                
                foreach ($students as $student) {
                    $row = [
                        $student->student_number ?? 'N/A',
                        $student->email ?? 'N/A',
                        $student->first_name ?? 'N/A',
                        $student->middle_name ?? 'N/A',
                        $student->last_name ?? 'N/A',
                        $student->program ?? 'N/A',
                        $student->year ?? 'N/A',
                        $student->section ?? 'N/A',
                        $student->status ?? 'N/A',
                        $student->birthdate ? Carbon::parse($student->birthdate)->format('m/d/Y') : 'N/A',
                        $student->created_at ? $student->created_at->format('m/d/Y H:i:s') : 'N/A',
                        $student->updated_at ? $student->updated_at->format('m/d/Y H:i:s') : 'N/A',
                    ];
                    
                    fputcsv($file, $row);
                }
                
                fclose($file);
            };

            // Log export activity
            AuditTrail::log(
                'export_filtered_students_api',
                'Exported filtered students data via API',
                'Export',
                null,
                'Filtered Students Export',
                [
                    'student_count' => $students->count(),
                    'filters' => $request->only(['program', 'year', 'section', 'status']),
                    'api_key_id' => $request->apiKeyModel->id ?? null,
                    'application_name' => $request->apiKeyModel->application_name ?? 'External App'
                ]
            );

            return response()->stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            \Log::error('API: Filtered students export error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export filtered student data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download template for batch upload
     */
    public function downloadTemplate(Request $request)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set headers
            $headerRow = ['Email', 'First Name', 'Middle Name', 'Last Name', 'Student Number', 'Program', 'Year', 'Section', 'Birthdate'];
            $sheet->fromArray([$headerRow], null, 'A1');
            
            // Add example row
            $exampleData = [
                'student@example.com',
                'John',
                'Romero',
                'Doe',
                'K12345678',
                'Bachelor of Science in Information Technology',
                '1st Year',
                '1',
                '01/01/2000'
            ];
            $sheet->fromArray([$exampleData], null, 'A2');
            
            // Auto-size columns
            foreach (range('A', 'I') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            // Bold the header row
            $sheet->getStyle('A1:I1')->getFont()->setBold(true);
            
            // Create the file
            $writer = new Xlsx($spreadsheet);
            
            $tempFile = tempnam(sys_get_temp_dir(), 'student_template');
            $writer->save($tempFile);

            // Log template download
            AuditTrail::log(
                'download_template_api',
                'Downloaded student import template via API',
                'Download',
                null,
                'Template Download',
                [
                    'template_type' => 'student_import',
                    'api_key_id' => $request->apiKeyModel->id ?? null,
                    'application_name' => $request->apiKeyModel->application_name ?? 'External App'
                ]
            );
            
            return response()->download($tempFile, 'student_import_template.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="student_import_template.xlsx"',
            ])->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            \Log::error('API: Template download error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to download template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get courses for dropdown
     */
    public function getCourses(Request $request)
    {
        try {
            $courses = Course::where('status', 'active')
                        ->orderBy('course_name', 'asc')
                        ->get(['course_id', 'course_name', 'department_id']);
            
            return response()->json([
                'success' => true,
                'message' => 'Courses retrieved successfully',
                'data' => [
                    'courses' => $courses
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('API: Error retrieving courses: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve courses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get departments for dropdown
     */
    public function getDepartments(Request $request)
    {
        try {
            $departments = Department::where('status', 'active')
                                   ->orderBy('dept_name', 'asc')
                                   ->get(['department_id', 'dept_name']);
            
            return response()->json([
                'success' => true,
                'message' => 'Departments retrieved successfully',
                'data' => [
                    'departments' => $departments
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('API: Error retrieving departments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve departments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get application information
     */
    public function getAppInfo(Request $request)
    {
        try {
            $apiKey = $request->apiKeyModel;
            
            return response()->json([
                'success' => true,
                'message' => 'Application information retrieved successfully',
                'data' => [
                    'application_name' => $apiKey->application_name,
                    'developer_name' => $apiKey->developer_name,
                    'permissions' => $apiKey->permissions,
                    'rate_limit' => $apiKey->request_limit_per_minute,
                    'total_requests' => $apiKey->total_requests,
                    'last_used' => $apiKey->last_used_at
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('API: Error retrieving app info: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve application information',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}