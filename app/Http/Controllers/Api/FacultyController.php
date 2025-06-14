<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Department;
use App\Models\AuditTrail;
use App\Models\BatchUpload;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Illuminate\Support\Str;

class FacultyController extends Controller
{
    /**
     * Store a new faculty member
     */
    public function store(Request $request)
    {
        try {
            // Log the incoming request for debugging
            Log::info('Faculty API registration attempt with data: ' . json_encode($request->all()));
            
            // Validate all required fields with proper rules
            $validated = $request->validate([
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
                'employee_number' => 'required|string|max:255|unique:users,employee_number',
                'phone_number' => [
                    'required',
                    'string',
                    'max:20',
                    'min:10',
                    'regex:/^[0-9\+\-\(\)\s]+$/'
                ],
                'department' => 'required|string|max:255',
                'employment_status' => 'required|in:Full-Time,Part-Time',
                'birthdate' => 'required|date|before:today',
            ], [
                // Custom error messages
                'email.required' => 'Email address is required.',
                'email.email' => 'Please enter a valid email address.',
                'email.unique' => 'This email address is already taken. Please use another email address.',
                
                'first_name.required' => 'First name is required.',
                'first_name.min' => 'First name must be at least 2 characters long.',
                'first_name.regex' => 'First name can only contain letters and spaces.',
                
                'middle_name.regex' => 'Middle name can only contain letters and spaces.',
                
                'last_name.required' => 'Last name is required.',
                'last_name.min' => 'Last name must be at least 2 characters long.',
                'last_name.regex' => 'Last name can only contain letters and spaces.',
                
                'employee_number.required' => 'Employee number is required.',
                'employee_number.unique' => 'This employee number is already taken. Please use another employee number.',
                
                'phone_number.required' => 'Phone number is required.',
                'phone_number.min' => 'Phone number must be at least 10 digits long.',
                'phone_number.regex' => 'Phone number can only contain numbers, spaces, hyphens, parentheses, and plus sign.',
                
                'department.required' => 'Department is required.',
                'employment_status.required' => 'Employment status is required.',
                'employment_status.in' => 'Employment status must be either Full-Time or Part-Time.',
                
                'birthdate.required' => 'Birthdate is required.',
                'birthdate.date' => 'Please enter a valid birthdate.',
                'birthdate.before' => 'Birthdate must be before today.',
            ]);

            // Validate age (must be between 18 and 100)
            $birthDate = Carbon::parse($validated['birthdate']);
            $age = $birthDate->age;
            if ($age < 18 || $age > 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Age based on birthdate must be between 18 and 100 years.',
                    'errors' => ['birthdate' => ['Age must be between 18 and 100 years.']]
                ], 422);
            }

            // Generate password
            $randomNumbers = rand(10000, 99999);
            $firstTwoLetters = strtoupper(substr($validated['first_name'], 0, 1) . substr($validated['last_name'], 0, 1));
            $specialChars = "!@#$%^&*";
            $specialChar = $specialChars[rand(0, strlen($specialChars) - 1)];
            $password = $randomNumbers . $firstTwoLetters . $specialChar;

            // Create the faculty user
            $faculty = User::create([
                'role' => 'Faculty',
                'email' => strtolower($validated['email']),
                'password' => Hash::make($password),
                'first_name' => ucwords(strtolower($validated['first_name'])),
                'middle_name' => !empty($validated['middle_name']) ? ucwords(strtolower($validated['middle_name'])) : null,
                'last_name' => ucwords(strtolower($validated['last_name'])),
                'employee_number' => strtoupper($validated['employee_number']),
                'phone_number' => $validated['phone_number'],
                'department' => $validated['department'],
                'employment_status' => $validated['employment_status'],
                'birthdate' => $birthDate->format('Y-m-d'),
                'status' => 'Active',
            ]);

            // Send email notification
            $emailSent = false;
            try {
                Mail::send('emails.credentials', ['user' => $faculty, 'password' => $password], function($message) use ($faculty) {
                    $message->to($faculty->email)
                            ->subject('PUP-Taguig Systems - Your Account Details');
                });
                $emailSent = true;
            } catch (\Exception $mailError) {
                Log::error('Email sending failed for faculty: ' . $faculty->email . ' - ' . $mailError->getMessage());
            }

            // Log audit trail
            AuditTrail::log(
                'add_faculty',
                "Added new faculty: {$faculty->first_name} {$faculty->last_name} ({$faculty->employee_number})",
                'User',
                $faculty->id,
                $faculty->first_name . ' ' . $faculty->last_name,
                [
                    'employee_number' => $faculty->employee_number,
                    'email' => $faculty->email,
                    'department' => $faculty->department,
                    'employment_status' => $faculty->employment_status
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Faculty member created successfully.',
                'data' => [
                    'faculty' => $faculty,
                    'email_sent' => $emailSent,
                    'generated_password' => $password // Remove this in production for security
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Faculty creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the faculty member.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing faculty member
     */
    public function update(Request $request, $id)
    {
        try {
            $faculty = User::where('role', 'Faculty')->findOrFail($id);
            
            // Validate with unique rules excluding current user
            $validated = $request->validate([
                'email' => 'required|email|unique:users,email,' . $faculty->id,
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
                'employee_number' => 'required|string|max:255|unique:users,employee_number,' . $faculty->id,
                'phone_number' => [
                    'required',
                    'string',
                    'max:20',
                    'min:10',
                    'regex:/^[0-9\+\-\(\)\s]+$/'
                ],
                'department' => 'required|string|max:255',
                'employment_status' => 'required|in:Full-Time,Part-Time',
                'birthdate' => 'required|date|before:today',
            ], [
                // Same custom error messages as store method
                'email.required' => 'Email address is required.',
                'email.email' => 'Please enter a valid email address.',
                'email.unique' => 'This email address is already taken. Please use another email address.',
                
                'first_name.required' => 'First name is required.',
                'first_name.min' => 'First name must be at least 2 characters long.',
                'first_name.regex' => 'First name can only contain letters and spaces.',
                
                'middle_name.regex' => 'Middle name can only contain letters and spaces.',
                
                'last_name.required' => 'Last name is required.',
                'last_name.min' => 'Last name must be at least 2 characters long.',
                'last_name.regex' => 'Last name can only contain letters and spaces.',
                
                'employee_number.required' => 'Employee number is required.',
                'employee_number.unique' => 'This employee number is already taken. Please use another employee number.',
                
                'phone_number.required' => 'Phone number is required.',
                'phone_number.min' => 'Phone number must be at least 10 digits long.',
                'phone_number.regex' => 'Phone number can only contain numbers, spaces, hyphens, parentheses, and plus sign.',
                
                'department.required' => 'Department is required.',
                'employment_status.required' => 'Employment status is required.',
                'employment_status.in' => 'Employment status must be either Full-Time or Part-Time.',
                
                'birthdate.required' => 'Birthdate is required.',
                'birthdate.date' => 'Please enter a valid birthdate.',
                'birthdate.before' => 'Birthdate must be before today.',
            ]);

            // Validate age (must be between 18 and 100)
            $birthDate = Carbon::parse($validated['birthdate']);
            $age = $birthDate->age;
            if ($age < 18 || $age > 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Age based on birthdate must be between 18 and 100 years.',
                    'errors' => ['birthdate' => ['Age must be between 18 and 100 years.']]
                ], 422);
            }

            // Store original data for audit
            $originalData = $faculty->toArray();

            // Update faculty
            $faculty->update([
                'first_name' => ucwords(strtolower($validated['first_name'])),
                'middle_name' => !empty($validated['middle_name']) ? ucwords(strtolower($validated['middle_name'])) : null,
                'last_name' => ucwords(strtolower($validated['last_name'])),
                'phone_number' => $validated['phone_number'],
                'email' => strtolower($validated['email']),
                'employee_number' => strtoupper($validated['employee_number']),
                'department' => $validated['department'],
                'employment_status' => $validated['employment_status'],
                'birthdate' => $birthDate->format('Y-m-d'),
            ]);

            // Log audit trail
            AuditTrail::log(
                'update_faculty',
                "Updated faculty: {$faculty->first_name} {$faculty->last_name} ({$faculty->employee_number})",
                'User',
                $faculty->id,
                $faculty->first_name . ' ' . $faculty->last_name,
                [
                    'original' => $originalData,
                    'updated' => $faculty->fresh()->toArray()
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Faculty member updated successfully.',
                'data' => ['faculty' => $faculty->fresh()]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Faculty member not found.'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Faculty update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the faculty member.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batch upload faculty members from Excel/CSV files
     */
    public function batchUpload(Request $request)
    {
        try {
            // Validate file upload
            $request->validate([
                'files' => 'required|array|max:5',
                'files.*' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max per file
            ], [
                'files.required' => 'Please select at least one file to upload.',
                'files.array' => 'Files must be provided as an array.',
                'files.max' => 'You can upload a maximum of 5 files at once.',
                'files.*.required' => 'Each file is required.',
                'files.*.file' => 'Each upload must be a valid file.',
                'files.*.mimes' => 'Files must be Excel (.xlsx, .xls) or CSV (.csv) format.',
                'files.*.max' => 'Each file must not exceed 10MB.'
            ]);

            $files = $request->file('files');
            
            // Generate batch IDs and numbers
            $batchNumber = rand(1, 999);
            $schoolYear = date('Y') . '-' . (date('Y') + 1);
            $batchId = BatchUpload::generateBatchId('faculty', $schoolYear, $batchNumber);
            
            // Initialize counters
            $totalImported = 0;
            $totalFailed = 0;
            $totalRows = 0;
            $emailsSent = 0;
            $emailsFailed = 0;
            $allErrors = [];
            $importedUsers = [];

            // Pre-validate all files and get total row count
            $fileDataCache = [];
            foreach ($files as $fileIndex => $file) {
                try {
                    // Additional file validation
                    if ($file->getSize() == 0) {
                        return response()->json([
                            'success' => false,
                            'message' => "File " . ($fileIndex + 1) . " is empty."
                        ], 422);
                    }

                    // Load the spreadsheet
                    $spreadsheet = IOFactory::load($file->getPathname());
                    $worksheet = $spreadsheet->getActiveSheet();
                    $rows = $worksheet->toArray();
                    
                    // Check if file has data
                    if (empty($rows) || count($rows) < 2) {
                        return response()->json([
                            'success' => false,
                            'message' => "File " . ($fileIndex + 1) . " must contain at least one data row besides the header."
                        ], 422);
                    }
                    
                    // Process headers
                    $header = array_shift($rows);
                    $normalizedHeaders = array_map(function($value) {
                        return strtolower(str_replace(' ', '_', trim($value)));
                    }, $header);
                    
                    // Check for empty headers
                    if (in_array('', $normalizedHeaders) || in_array(null, $normalizedHeaders)) {
                        return response()->json([
                            'success' => false,
                            'message' => "File " . ($fileIndex + 1) . " header row contains empty columns. Please ensure all columns have proper headers."
                        ], 422);
                    }

                    // Expected headers for faculty
                    $expectedHeaders = [
                        'email', 'first_name', 'middle_name', 'last_name', 
                        'employee_number', 'phone_number', 'department', 
                        'employment_status', 'birthdate'
                    ];

                    // Check if all required headers are present (excluding optional middle_name and birthdate)
                    $requiredHeaders = ['email', 'first_name', 'last_name', 'employee_number', 'phone_number', 'department', 'employment_status'];
                    $missingHeaders = array_diff($requiredHeaders, $normalizedHeaders);
                    
                    if (!empty($missingHeaders)) {
                        return response()->json([
                            'success' => false,
                            'message' => "File " . ($fileIndex + 1) . " is missing required columns: " . implode(', ', array_map(function($h) { return str_replace('_', ' ', $h); }, $missingHeaders)) . ". Required columns are: " . implode(', ', array_map(function($h) { return str_replace('_', ' ', $h); }, $requiredHeaders)) . ". Optional columns: Middle Name, Birthdate"
                        ], 422);
                    }

                    // Remove empty rows
                    $rows = array_filter($rows, function($row) {
                        return !empty(array_filter($row));
                    });

                    $totalRows += count($rows);
                    
                    // Cache the processed data
                    $fileDataCache[$fileIndex] = [
                        'headers' => $normalizedHeaders,
                        'rows' => $rows,
                        'filename' => $file->getClientOriginalName()
                    ];

                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => "Error processing file " . ($fileIndex + 1) . ": " . $e->getMessage()
                    ], 422);
                }
            }

            // Check total rows limit
            if ($totalRows > 5000) {
                return response()->json([
                    'success' => false,
                    'message' => "Total rows across all files ($totalRows) exceeds the maximum limit of 5000 rows."
                ], 422);
            }

            // Create batch upload record
            $batchUpload = BatchUpload::create([
                'batch_id' => $batchId,
                'admin_email' => 'external_api',
                'admin_name' => $request->apiKeyModel->application_name ?? 'External App',
                'upload_type' => 'faculty',
                'file_name' => count($files) . ' files uploaded',
                'file_path' => 'batch_uploads/' . $batchId,
                'total_rows' => $totalRows,
                'successful_imports' => 0,
                'failed_imports' => 0,
                'batch_number' => $batchNumber,
                'school_year' => $schoolYear,
                'status' => 'processing',
                'started_at' => now()
            ]);

            // Process each cached file
            foreach ($fileDataCache as $fileIndex => $fileData) {
                $normalizedHeaders = $fileData['headers'];
                $rows = $fileData['rows'];
                $filename = $fileData['filename'];

                // Process each row
                foreach ($rows as $rowIndex => $row) {
                    $actualRowNumber = $rowIndex + 2; // +2 because we removed header and array is 0-indexed
                    $rowData = array_combine($normalizedHeaders, $row);
                    
                    $rowErrors = [];
                    $hasError = false;

                    // Validate first name
                    $firstName = trim($rowData['first_name'] ?? '');
                    if (empty($firstName)) {
                        $rowErrors[] = "First Name is required";
                        $hasError = true;
                    } elseif (strlen($firstName) < 2) {
                        $rowErrors[] = "First Name must be at least 2 characters";
                        $hasError = true;
                    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $firstName)) {
                        $rowErrors[] = "First Name can only contain letters and spaces";
                        $hasError = true;
                    }
                    
                    // Validate last name
                    $lastName = trim($rowData['last_name'] ?? '');
                    if (empty($lastName)) {
                        $rowErrors[] = "Last Name is required";
                        $hasError = true;
                    } elseif (strlen($lastName) < 2) {
                        $rowErrors[] = "Last Name must be at least 2 characters";
                        $hasError = true;
                    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $lastName)) {
                        $rowErrors[] = "Last Name can only contain letters and spaces";
                        $hasError = true;
                    }
                    
                    // Validate middle name (optional)
                    $middleName = trim($rowData['middle_name'] ?? '');
                    if (!empty($middleName) && !preg_match('/^[a-zA-Z\s]+$/', $middleName)) {
                        $rowErrors[] = "Middle Name can only contain letters and spaces";
                        $hasError = true;
                    }
                    
                    // Validate email
                    $email = trim($rowData['email'] ?? '');
                    if (empty($email)) {
                        $rowErrors[] = "Email is required";
                        $hasError = true;
                    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $rowErrors[] = "Email is not a valid email address";
                        $hasError = true;
                    } elseif (User::where('email', strtolower($email))->exists()) {
                        $rowErrors[] = "Email already exists already taken.";
                        $hasError = true;
                    }
                    
                    // Validate employee number
                    $employeeNumber = trim($rowData['employee_number'] ?? '');
                    if (empty($employeeNumber)) {
                        $rowErrors[] = "Employee Number is required";
                        $hasError = true;
                    } elseif (strlen($employeeNumber) < 3) {
                        $rowErrors[] = "Employee Number must be at least 3 characters";
                        $hasError = true;
                    } elseif (User::where('employee_number', strtoupper($employeeNumber))->exists()) {
                        $rowErrors[] = "Employee Number already exists already taken.";
                        $hasError = true;
                    }
                    
                    // Validate phone number
                    $phoneNumber = trim($rowData['phone_number'] ?? '');
                    if (empty($phoneNumber)) {
                        $rowErrors[] = "Phone Number is required";
                        $hasError = true;
                    } elseif (strlen($phoneNumber) < 10) {
                        $rowErrors[] = "Phone Number must be at least 10 digits";
                        $hasError = true;
                    }
                    
                    // Validate department
                    $department = trim($rowData['department'] ?? '');
                    if (empty($department)) {
                        $rowErrors[] = "Department is required";
                        $hasError = true;
                    }
                    
                    // Validate employment status
                    $employmentStatus = trim($rowData['employment_status'] ?? '');
                    $validStatuses = ['Full-Time', 'Part-Time', 'full-time', 'part-time'];
                    if (empty($employmentStatus)) {
                        $rowErrors[] = "Employment Status is required";
                        $hasError = true;
                    } elseif (!in_array($employmentStatus, $validStatuses)) {
                        $rowErrors[] = "Employment Status is not valid. Use: Full-Time or Part-Time";
                        $hasError = true;
                    }
                    
                    // Validate birthdate (optional)
                    $birthdate = trim($rowData['birthdate'] ?? '');
                    if (!empty($birthdate)) {
                        try {
                            $birthDate = Carbon::parse($birthdate);
                            $age = $birthDate->age;
                            if ($age < 18 || $age > 100) {
                                $rowErrors[] = "Age based on Birthdate must be between 18 and 100 years";
                                $hasError = true;
                            }
                        } catch (\Exception $e) {
                            $rowErrors[] = "Birthdate is not a valid date format";
                            $hasError = true;
                        }
                    }
                    
                    // If there are errors for this row, add them to the errors array
                    if ($hasError) {
                        foreach ($rowErrors as $error) {
                            $allErrors[] = "File: {$filename}, Row {$actualRowNumber} ({$firstName} {$lastName}): {$error}";
                        }
                        $totalFailed++;
                        continue;
                    }
                    
                    // Create the user if all validations pass
                    try {
                        // Generate password
                        $randomNumbers = rand(10000, 99999);
                        $firstTwoLetters = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
                        $specialChars = "!@#$%^&*";
                        $specialChar = $specialChars[rand(0, strlen($specialChars) - 1)];
                        $password = $randomNumbers . $firstTwoLetters . $specialChar;
                        $hashedPassword = Hash::make($password);

                        $user = new User();
                        $user->role = 'Faculty';
                        $user->email = strtolower($email);
                        $user->password = $hashedPassword;
                        $user->first_name = ucwords(strtolower($firstName));
                        $user->middle_name = !empty($middleName) ? ucwords(strtolower($middleName)) : null;
                        $user->last_name = ucwords(strtolower($lastName));
                        $user->employee_number = strtoupper($employeeNumber);
                        $user->phone_number = $phoneNumber;
                        $user->department = $department;
                        $user->employment_status = ucfirst(strtolower($employmentStatus));
                        $user->birthdate = !empty($birthdate) ? Carbon::parse($birthdate)->format('Y-m-d') : null;
                        $user->status = 'Active';
                        // Make batch_number and school_year optional by omitting them or setting to null
                        // $user->batch_number = $batchNumber; // Removed
                        // $user->school_year = $schoolYear;   // Removed
                        
                        $user->save();
                        $totalImported++;
                        $importedUsers[] = $user;

                        // Send email notification
                        try {
                            Mail::send('emails.credentials', ['user' => $user, 'password' => $password], function($message) use ($user) {
                                $message->to($user->email)
                                        ->subject('PUP-Taguig Systems - Your Account Details');
                            });
                            $emailsSent++;
                        } catch (\Exception $mailError) {
                            $allErrors[] = "File: {$filename}, Row {$actualRowNumber}: Email sending failed for {$user->email}";
                            $emailsFailed++;
                        }
                        
                    } catch (\Exception $e) {
                        $allErrors[] = "File: {$filename}, Row {$actualRowNumber} ({$firstName} {$lastName}): Failed to save - " . $e->getMessage();
                        $totalFailed++;
                    }
                }
            }

            // Update batch upload record
            $batchUpload->update([
                'successful_imports' => $totalImported,
                'failed_imports' => $totalFailed,
                'errors' => $allErrors,
                'status' => 'completed',
                'completed_at' => now(),
                'import_summary' => [
                    'total_processed' => $totalImported + $totalFailed,
                    'successful_imports' => $totalImported,
                    'failed_imports' => $totalFailed,
                    'emails_sent' => $emailsSent,
                    'emails_failed' => $emailsFailed,
                    'files_processed' => count($files)
                ]
            ]);

            // Log audit trail
            AuditTrail::log(
                'batch_upload_faculty',
                "Batch upload faculty: {$totalImported} imported, {$totalFailed} failed",
                'BatchUpload',
                $batchUpload->id,
                $batchId,
                [
                    'total_records' => $totalImported + $totalFailed,
                    'successful_imports' => $totalImported,
                    'failed_imports' => $totalFailed,
                    'emails_sent' => $emailsSent,
                    'emails_failed' => $emailsFailed,
                    'school_year' => $schoolYear,
                    'files_processed' => count($files)
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Batch upload completed.',
                'data' => [
                    'batch_id' => $batchId,
                    'batch_number' => $batchNumber,
                    'total_processed' => $totalImported + $totalFailed,
                    'successful_imports' => $totalImported,
                    'failed_imports' => $totalFailed,
                    'emails_sent' => $emailsSent,
                    'emails_failed' => $emailsFailed,
                    'errors' => $allErrors,
                    'imported_faculty' => $importedUsers
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'File validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Faculty batch upload failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during batch upload.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all faculty members
     */
    public function index(Request $request)
    {
        try {
            $query = User::where('role', 'Faculty');

            // Apply filters if provided
            if ($request->has('department') && !empty($request->department)) {
                $query->where('department', $request->department);
            }

            if ($request->has('employment_status') && !empty($request->employment_status)) {
                $query->where('employment_status', $request->employment_status);
            }

            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }

            // Apply sorting
            $sortBy = $request->input('sort_by', 'last_name');
            $sortOrder = $request->input('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Apply pagination
            $perPage = $request->input('per_page', 15);
            $faculty = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $faculty
            ], 200);

        } catch (\Exception $e) {
            Log::error('Faculty index failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching faculty members.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific faculty member
     */
    public function show($id)
    {
        try {
            $faculty = User::where('role', 'Faculty')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => ['faculty' => $faculty]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Faculty member not found.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Faculty show failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching the faculty member.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a faculty member (soft delete by changing status)
     */
    public function destroy($id)
    {
        try {
            $faculty = User::where('role', 'Faculty')->findOrFail($id);
            
            // Store original status for audit
            $originalStatus = $faculty->status;
            
            // Update status to Inactive instead of hard delete
            $faculty->update(['status' => 'Inactive']);

            // Log audit trail
            AuditTrail::log(
                'deactivate_user',
                "Deactivated faculty: {$faculty->first_name} {$faculty->last_name} ({$faculty->employee_number})",
                'User',
                $faculty->id,
                $faculty->first_name . ' ' . $faculty->last_name,
                [
                    'original_status' => $originalStatus,
                    'new_status' => 'Inactive'
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Faculty member deactivated successfully.',
                'data' => ['faculty' => $faculty->fresh()]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Faculty member not found.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Faculty deactivation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deactivating the faculty member.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reactivate a faculty member
     */
    public function reactivate($id)
    {
        try {
            $faculty = User::where('role', 'Faculty')->findOrFail($id);
            
            // Store original status for audit
            $originalStatus = $faculty->status;
            
            // Update status to Active
            $faculty->update(['status' => 'Active']);

            // Log audit trail
            AuditTrail::log(
                'reactivate_user',
                "Reactivated faculty: {$faculty->first_name} {$faculty->last_name} ({$faculty->employee_number})",
                'User',
                $faculty->id,
                $faculty->first_name . ' ' . $faculty->last_name,
                [
                    'original_status' => $originalStatus,
                    'new_status' => 'Active'
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Faculty member reactivated successfully.',
                'data' => ['faculty' => $faculty->fresh()]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Faculty member not found.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Faculty reactivation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while reactivating the faculty member.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk toggle status for multiple faculty members
     */
    public function bulkToggleStatus(Request $request)
    {
        try {
            $request->validate([
                'faculty_ids' => 'required|array|min:1',
                'faculty_ids.*' => 'required|integer|exists:users,id',
                'action' => 'required|in:activate,deactivate'
            ], [
                'faculty_ids.required' => 'Please select at least one faculty member.',
                'faculty_ids.array' => 'Faculty IDs must be provided as an array.',
                'faculty_ids.min' => 'Please select at least one faculty member.',
                'faculty_ids.*.exists' => 'One or more selected faculty members do not exist.',
                'action.required' => 'Action is required.',
                'action.in' => 'Action must be either activate or deactivate.'
            ]);

            $facultyIds = $request->input('faculty_ids');
            $action = $request->input('action');
            $newStatus = $action === 'activate' ? 'Active' : 'Inactive';

            // Get faculty members
            $faculty = User::where('role', 'Faculty')
                          ->whereIn('id', $facultyIds)
                          ->get();

            if ($faculty->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No faculty members found with the provided IDs.'
                ], 404);
            }

            $updated = 0;
            $errors = [];

            foreach ($faculty as $member) {
                try {
                    $originalStatus = $member->status;
                    $member->update(['status' => $newStatus]);

                    // Log audit trail for each member
                    AuditTrail::log(
                        $action === 'activate' ? 'reactivate_user' : 'deactivate_user',
                        ucfirst($action) . "d faculty: {$member->first_name} {$member->last_name} ({$member->employee_number})",
                        'User',
                        $member->id,
                        $member->first_name . ' ' . $member->last_name,
                        [
                            'original_status' => $originalStatus,
                            'new_status' => $newStatus,
                            'bulk_action' => true
                        ]
                    );

                    $updated++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to update {$member->first_name} {$member->last_name}: " . $e->getMessage();
                }
            }

            // Log bulk action audit trail
            AuditTrail::log(
                $action === 'activate' ? 'bulk_reactivate_users' : 'bulk_deactivate_users',
                "Bulk " . $action . "d {$updated} faculty members",
                null,
                null,
                null,
                [
                    'faculty_count' => $updated,
                    'action' => $action,
                    'faculty_ids' => $facultyIds,
                    'errors' => $errors
                ]
            );

            return response()->json([
                'success' => true,
                'message' => "Successfully {$action}d {$updated} faculty member(s).",
                'data' => [
                    'updated_count' => $updated,
                    'errors' => $errors,
                    'faculty' => $faculty->fresh()
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Bulk faculty status toggle failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during bulk status update.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get faculty statistics
     */
    public function statistics()
    {
        try {
            $stats = [
                'total_faculty' => User::where('role', 'Faculty')->count(),
                'active_faculty' => User::where('role', 'Faculty')->where('status', 'Active')->count(),
                'inactive_faculty' => User::where('role', 'Faculty')->where('status', 'Inactive')->count(),
                'full_time_faculty' => User::where('role', 'Faculty')->where('employment_status', 'Full-Time')->count(),
                'part_time_faculty' => User::where('role', 'Faculty')->where('employment_status', 'Part-Time')->count(),
                'departments' => User::where('role', 'Faculty')
                    ->selectRaw('department, COUNT(*) as count')
                    ->groupBy('department')
                    ->orderBy('count', 'desc')
                    ->get(),
                'recent_additions' => User::where('role', 'Faculty')
                    ->where('created_at', '>=', Carbon::now()->subDays(30))
                    ->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            Log::error('Faculty statistics failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching statistics.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}