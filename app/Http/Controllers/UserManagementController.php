<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Department;
use App\Models\Course;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class UserManagementController extends Controller
{
    //User Management Page
    public function userManagementPage()
    {
        $admin = Auth::guard('admin')->user();
        
        //Get all users
        $users = User::all();

        return view('admin.user-management.users', compact('admin', 'users'));
    }

    //Show Faculty Page
    public function facultyPage()
    {
        $admin = Auth::guard('admin')->user();
        
        // Get users with the role "Faculty"
        $users = User::faculty()->orderBy('last_name', 'asc')->get();

        // Get all active departments using scopes
        $departments = Department::active()->ordered()->get();
        
        // Get all filter counts using the model method
        $filterCounts = User::getFacultyFilterCounts();
        $departmentCounts = $filterCounts['departments'];
        $employmentStatusCounts = $filterCounts['employment_statuses'];
        $statusCounts = $filterCounts['statuses'];

        return view('admin.user-management.faculty', compact('admin', 'users', 'departments', 'departmentCounts', 'employmentStatusCounts', 'statusCounts'));
    }

    //Show Faculty Details
    public function viewFaculty($userId)
    {
        $admin = Auth::guard('admin')->user();
        
        // Get the faculty member with the given ID
        $faculty = User::findOrFail($userId);
        
        // Get all active departments for the dropdown
        $departments = Department::where('status', 'active')->orderBy('dept_name', 'asc')->get();
        
        return view('admin.user-management.view-faculty', compact('admin', 'faculty', 'departments'));
    }

    //Store Faculty
    public function storeFaculty(Request $request)
    {
        try {
            // Log the incoming request for debugging
            \Log::info('Faculty registration attempt with data: ' . json_encode($request->all()));
            
            // Email Address Validation first
            $existingUser = User::where('email', $request->email)->first();
            if ($existingUser) {
                return response()->json([
                    'message' => 'The email is already taken. Please use another email address.',
                    'errors' => [
                        'email' => ['The email is already taken. Please use another email address.']
                    ]
                ], 422);
            }

            // Employee Number Validation
            $existingEmployee = User::where('employee_number', $request->employee_number)->first();
            if ($existingEmployee) {
                return response()->json([
                    'message' => 'The employee number is already taken. Please use another employee number.',
                    'errors' => [
                        'employee_number' => ['The employee number is already taken. Please use another employee number.']
                    ]
                ], 422);
            }
            
            $validated = $request->validate([
                'email' => 'required|email|unique:users,email',
                'first_name' => 'required|string|max:255|min:2',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255|min:2',
                'employee_number' => 'required|string|max:255|unique:users,employee_number',
                'phone_number' => 'required|string|max:20|min:10',
                'department' => 'required|string|max:255',
                'employment_status' => 'required|in:Full-Time,Part-Time',
                'birthdate' => 'required|date|before:today',
            ]);

            // Validate age
            $birthDate = new \DateTime($validated['birthdate']);
            $today = new \DateTime();
            $age = $birthDate->diff($today)->y;
            if ($age < 18) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => ['birthdate' => ['Faculty member must be at least 18 years old.']]
                ], 422);
            }

            if ($age > 100) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => ['birthdate' => ['Please enter a valid birthdate.']]
                ], 422);
            }

            // Generate a password
            $randomNumbers = rand(10000, 99999);
            $firstTwoLetters = strtoupper(substr($validated['first_name'], 0, 1) . substr($validated['last_name'], 0, 1));
            
            $specialChars = "!@#$%^&*";
            $specialChar = $specialChars[rand(0, strlen($specialChars) - 1)];
        
            $password = $randomNumbers . $firstTwoLetters . $specialChar;
            $hashedPassword = Hash::make($password);

            // Create User Account
            $user = User::create([
                'role' => 'Faculty',
                'status' => 'Active',
                'email' => strtolower($validated['email']),
                'password' => $hashedPassword,
                'first_name' => ucwords(strtolower($validated['first_name'])),
                'middle_name' => $validated['middle_name'] ? ucwords(strtolower($validated['middle_name'])) : null,
                'last_name' => ucwords(strtolower($validated['last_name'])),
                'employee_number' => strtoupper($validated['employee_number']),
                'phone_number' => $validated['phone_number'],
                'department' => $validated['department'],
                'employment_status' => $validated['employment_status'],
                'birthdate' => $validated['birthdate'],
            ]);

            \Log::info('Faculty user created successfully with ID: ' . $user->id);

            // Add try-catch specifically for mail sending
            try {
                \Log::info('Attempting to send email to: ' . $user->email);
                
                Mail::send('emails.credentials', ['user' => $user, 'password' => $password], function($message) use ($user) {
                    $message->to($user->email)
                            ->subject('PUP-Taguig Systems - Your Account Details');
                });
                
                \Log::info('Email sent successfully to: ' . $user->email);
                
            } catch (\Exception $mailError) {
                \Log::error('Email sending failed for ' . $user->email . ': ' . $mailError->getMessage());
                \Log::error('Mail error trace: ' . $mailError->getTraceAsString());
                // Continue execution even if email fails
            }

            return response()->json([
                'message' => 'Faculty added successfully! Login details have been sent to their email.',
                'user' => $user
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error: ' . json_encode($e->errors()));
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error adding faculty: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'message' => 'An error occurred while adding the faculty member.',
                'errors' => ['general' => [$e->getMessage()]]
            ], 500);
        }
    }

    // Import Faculty from CSV or Excel
    public function importFaculty(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'import_file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('import_error', 'Please upload a valid CSV or Excel file.')
                ->withErrors($validator);
        }

        try {
            $file = $request->file('import_file');
            
            // Additional file validation
            if ($file->getSize() == 0) {
                return redirect()->back()->with('import_error', 'The uploaded file is empty.');
            }
            
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Check if file has data
            if (empty($rows) || count($rows) < 2) {
                return redirect()->back()->with('import_error', 'The file must contain at least one data row besides the header.');
            }
            
            // Check maximum rows limit
            if (count($rows) > 1001) { // 1000 data rows + 1 header
                return redirect()->back()->with('import_error', 'File contains too many rows. Maximum allowed is 1000 faculty per import.');
            }

            // The first row should be headers
            $headers = array_map('strtolower', array_map('trim', $rows[0]));
            
            // Check for empty headers
            if (in_array('', $headers) || in_array(null, $headers)) {
                return redirect()->back()->with('import_error', 'Header row contains empty columns. Please ensure all columns have proper headers.');
            }
            
            // Check if all required headers are present
            $requiredHeaders = ['email', 'first name', 'last name', 'employee number', 'phone number', 'department', 'employment status'];
            $missingHeaders = array_diff($requiredHeaders, $headers);
            
            if (!empty($missingHeaders)) {
                return redirect()->back()
                    ->with('import_error', 'Missing required columns: ' . implode(', ', $missingHeaders));
            }

            // Remove the header row
            array_shift($rows);
            
            $imported = 0;
            $failed = 0;
            $emailsSent = 0;
            $emailsFailed = 0;
            $errors = [];
            $totalRows = count($rows);
            
            // Pre-check for duplicates within the file
            $fileEmails = [];
            $fileEmployeeNumbers = [];
            
            foreach ($rows as $index => $row) {
                if (empty(array_filter($row))) continue;
                
                $rowData = [];
                foreach ($headers as $colIndex => $header) {
                    $rowData[str_replace(' ', '_', strtolower($header))] = isset($row[$colIndex]) ? trim($row[$colIndex]) : null;
                }
                
                $email = strtolower($rowData['email'] ?? '');
                $employeeNumber = $rowData['employee_number'] ?? '';
                $rowNumber = $index + 2;
                
                if ($email) {
                    if (in_array($email, $fileEmails)) {
                        $errors[] = "Row $rowNumber: Email '$email' is duplicated in the file";
                    } else {
                        $fileEmails[] = $email;
                    }
                }
                
                if ($employeeNumber) {
                    if (in_array($employeeNumber, $fileEmployeeNumbers)) {
                        $errors[] = "Row $rowNumber: Employee number '$employeeNumber' is duplicated in the file";
                    } else {
                        $fileEmployeeNumbers[] = $employeeNumber;
                    }
                }
            }
            
            // Process each row
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;
                
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }
                
                // Map columns to user fields
                $rowData = [];
                foreach ($headers as $colIndex => $header) {
                    $value = isset($row[$colIndex]) ? trim($row[$colIndex]) : null;
                    $rowData[str_replace(' ', '_', strtolower($header))] = $value;
                }
                
                $hasError = false;
                $rowErrors = [];
                
                // Validate email
                $email = $rowData['email'] ?? '';
                if (empty($email)) {
                    $rowErrors[] = "Email is required";
                    $hasError = true;
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $rowErrors[] = "Email '$email' is not a valid email format";
                    $hasError = true;
                } elseif (User::where('email', strtolower($email))->exists()) {
                    $rowErrors[] = "Email '$email' already exists in the system";
                    $hasError = true;
                }
                
                // Validate first name
                $firstName = $rowData['first_name'] ?? '';
                if (empty($firstName)) {
                    $rowErrors[] = "First name is required";
                    $hasError = true;
                } elseif (strlen($firstName) < 2) {
                    $rowErrors[] = "First name '$firstName' must be at least 2 characters";
                    $hasError = true;
                } elseif (!preg_match('/^[a-zA-Z\s\-\.\']+$/', $firstName)) {
                    $rowErrors[] = "First name '$firstName' contains invalid characters";
                    $hasError = true;
                }
                
                // Validate last name
                $lastName = $rowData['last_name'] ?? '';
                if (empty($lastName)) {
                    $rowErrors[] = "Last name is required";
                    $hasError = true;
                } elseif (strlen($lastName) < 2) {
                    $rowErrors[] = "Last name '$lastName' must be at least 2 characters";
                    $hasError = true;
                } elseif (!preg_match('/^[a-zA-Z\s\-\.\']+$/', $lastName)) {
                    $rowErrors[] = "Last name '$lastName' contains invalid characters";
                    $hasError = true;
                }
                
                // Validate middle name (optional)
                $middleName = $rowData['middle_name'] ?? '';
                if (!empty($middleName) && !preg_match('/^[a-zA-Z\s\-\.\']+$/', $middleName)) {
                    $rowErrors[] = "Middle name '$middleName' contains invalid characters";
                    $hasError = true;
                }
                
                // Validate employee number
                $employeeNumber = $rowData['employee_number'] ?? '';
                if (empty($employeeNumber)) {
                    $rowErrors[] = "Employee number is required";
                    $hasError = true;
                } elseif (strlen($employeeNumber) < 3) {
                    $rowErrors[] = "Employee number '$employeeNumber' must be at least 3 characters";
                    $hasError = true;
                } elseif (User::where('employee_number', $employeeNumber)->exists()) {
                    $rowErrors[] = "Employee number '$employeeNumber' already exists in the system";
                    $hasError = true;
                }
                
                // Validate phone number
                $phoneNumber = $rowData['phone_number'] ?? '';
                if (empty($phoneNumber)) {
                    $rowErrors[] = "Phone number is required";
                    $hasError = true;
                } elseif (strlen($phoneNumber) < 10) {
                    $rowErrors[] = "Phone number '$phoneNumber' must be at least 10 digits";
                    $hasError = true;
                }
                
                // Validate department
                $department = $rowData['department'] ?? '';
                if (empty($department)) {
                    $rowErrors[] = "Department is required";
                    $hasError = true;
                }
                
                // Validate employment status
                $employmentStatus = $rowData['employment_status'] ?? '';
                $validStatuses = ['Full-Time', 'Part-Time', 'full-time', 'part-time'];
                if (empty($employmentStatus)) {
                    $rowErrors[] = "Employment status is required";
                    $hasError = true;
                } elseif (!in_array($employmentStatus, $validStatuses)) {
                    $rowErrors[] = "Employment status '$employmentStatus' is not valid. Use: Full-Time or Part-Time";
                    $hasError = true;
                }
                
                // Validate birthdate (optional)
                $birthdate = $rowData['birthdate'] ?? '';
                if (!empty($birthdate)) {
                    try {
                        $birthDate = Carbon::parse($birthdate);
                        $age = $birthDate->age;
                        if ($age < 18 || $age > 100) {
                            $rowErrors[] = "Age based on birthdate '$birthdate' must be between 18 and 100 years";
                            $hasError = true;
                        }
                    } catch (\Exception $e) {
                        $rowErrors[] = "Birthdate '$birthdate' is not a valid date format";
                        $hasError = true;
                    }
                }
                
                // If there are errors for this row, add them to the errors array
                if ($hasError) {
                    foreach ($rowErrors as $error) {
                        $errors[] = "Row $rowNumber ({$firstName} {$lastName}): {$error}";
                    }
                    $failed++;
                    continue;
                }
                
                // Create the user if all validations pass
                try {
                    // Generate a password like in storeFaculty
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
                    
                    $user->save();
                    $imported++;

                    // Send email notification with credentials
                    try {
                        Mail::send('emails.credentials', ['user' => $user, 'password' => $password], function($message) use ($user) {
                            $message->to($user->email)
                                    ->subject('PUP-Taguig Systems - Your Account Details');
                        });
                        $emailsSent++;
                    } catch (\Exception $mailError) {
                        \Log::error('Email sending failed for ' . $user->email . ': ' . $mailError->getMessage());
                        $emailsFailed++;
                        // Continue execution even if email fails
                    }
                    
                } catch (\Exception $e) {
                    $errors[] = "Row $rowNumber ({$firstName} {$lastName}): Failed to save - " . $e->getMessage();
                    $failed++;
                    continue;
                }
            }
            
            // Prepare session data
            $summary = [
                'total' => $totalRows,
                'success' => $imported,
                'failed' => $failed,
                'emails_sent' => $emailsSent,
                'emails_failed' => $emailsFailed
            ];
            
            // Set appropriate messages
            if ($imported > 0 && $failed == 0) {
                // All successful
                $emailMessage = $emailsFailed > 0 ? " Note: {$emailsFailed} email(s) failed to send." : " All login credentials have been sent via email.";
                return redirect()->back()
                    ->with('import_success', "Successfully imported all $imported faculty member(s)!{$emailMessage}")
                    ->with('import_summary', $summary);
            } elseif ($imported > 0 && $failed > 0) {
                // Partial success
                $emailMessage = $emailsFailed > 0 ? " Note: {$emailsFailed} email(s) failed to send." : "";
                return redirect()->back()
                    ->with('import_success', "Successfully imported $imported faculty member(s). $failed row(s) had errors and were skipped.{$emailMessage}")
                    ->with('import_errors', $errors)
                    ->with('import_summary', $summary);
            } elseif ($imported == 0 && $failed > 0) {
                // All failed
                return redirect()->back()
                    ->with('import_error', "No faculty members were imported. All $failed row(s) contained errors.")
                    ->with('import_errors', $errors)
                    ->with('import_summary', $summary);
            } else {
                // No data processed
                return redirect()->back()->with('import_error', 'No valid data found to import.');
            }
            
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            Log::error('Spreadsheet reading error: ' . $e->getMessage());
            return redirect()->back()->with('import_error', 'Unable to read the file. Please ensure it is a valid Excel or CSV file.');
        } catch (\Exception $e) {
            Log::error('Import error: ' . $e->getMessage());
            return redirect()->back()->with('import_error', 'Failed to import faculty: ' . $e->getMessage());
        }
    }

    // Download a template file for faculty import
    public function downloadFacultyTemplate()
    {
        // Create a template file using PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $headerRow = ['Email', 'First Name', 'Middle Name', 'Last Name', 'Employee Number', 'Phone Number', 'Department', 'Employment Status', 'Birthdate'];
        $sheet->fromArray([$headerRow], null, 'A1');
        
        // Add example row
        $exampleData = [
            'faculty@example.com',
            'John',
            'Dela Cruz',
            'Doe',
            'EMP12345',
            '09123456789',
            'Department of Information Technology',
            'Full-Time',
            '01/01/1980'
        ];
        $sheet->fromArray([$exampleData], null, 'A2');
        
        // Auto-size columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Bold the header row
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        
        // Create the file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        $tempFile = tempnam(sys_get_temp_dir(), 'faculty_template');
        $writer->save($tempFile);
        
        // Return the download response
        return response()->download($tempFile, 'faculty_import_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="faculty_import_template.xlsx"',
        ])->deleteFileAfterSend(true);
    }

    //Update Faculty Details
    public function updateFaculty(Request $request, $userId)
    {
        // Get valid departments for validation
        $validDepartments = Department::where('status', 'active')->pluck('dept_name')->toArray();
        
        // Validate request
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|email|max:255|unique:users,email,'.$userId,
            'employee_number' => 'required|string|max:50|unique:users,employee_number,'.$userId,
            'department' => [
                'required',
                'string',
                'in:' . implode(',', $validDepartments)
            ],
            'employment_status' => [
                'required',
                'string',
                'in:Full-Time,Part-Time'
            ],
            'birthdate' => 'required|date',
        ], [
            // Custom error messages
            'department.in' => 'Please select a valid department.',
            'employment_status.in' => 'Please select a valid employment status.',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Find user
        $faculty = User::findOrFail($userId);
        
        // Check if any data has changed
        $hasChanges = false;
        $fields = ['first_name', 'middle_name', 'last_name', 'phone_number', 
                'email', 'employee_number', 'department', 'employment_status', 'birthdate'];
        
        foreach ($fields as $field) {
            if ($field === 'birthdate') {
                $formattedDate = \Carbon\Carbon::parse($request->birthdate)->format('Y-m-d');
                if ($formattedDate != $faculty->birthdate) {
                    $hasChanges = true;
                    break;
                }
            } elseif ($field === 'middle_name') {
                // Handle null/empty middle name comparison
                $requestValue = $request->$field ?: null;
                $facultyValue = $faculty->$field ?: null;
                if ($requestValue != $facultyValue) {
                    $hasChanges = true;
                    break;
                }
            } elseif ($request->$field != $faculty->$field) {
                $hasChanges = true;
                break;
            }
        }
        
        if (!$hasChanges) {
            return redirect()->back()->with('error', 'No changes were made to update.');
        }
        
        // Update user
        $faculty->update([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
            'employee_number' => $request->employee_number,
            'department' => $request->department,
            'employment_status' => $request->employment_status,
            'birthdate' => \Carbon\Carbon::parse($request->birthdate)->format('Y-m-d'),
        ]);
        
        return redirect()->back()->with('success', 'Faculty details updated successfully!');
    }

    public function exportFaculty(Request $request)
{
    try {
        // Get all faculty members with their data
        $faculty = User::where('role', 'Faculty')
                      ->orderBy('last_name', 'asc')
                      ->orderBy('first_name', 'asc')
                      ->get();

        // Check if there's any faculty data
        if ($faculty->isEmpty()) {
            return redirect()->back()->with('error', 'No faculty data available to export.');
        }

        // Create filename with current date
        $filename = 'faculty_data_' . date('Y-m-d_H-i-s') . '.csv';
        
        // Set headers for CSV download
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        // Create CSV content
        $callback = function() use ($faculty) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for proper UTF-8 encoding in Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // CSV Headers
            $headers = [
                'Employee Number',
                'Email Address',
                'First Name',
                'Middle Name',
                'Last Name',
                'Phone Number',
                'Department',
                'Employment Status',
                'Account Status',
                'Birthdate',
                'Date Created',
                'Last Updated'
            ];
            
            fputcsv($file, $headers);
            
            // Add faculty data
            foreach ($faculty as $member) {
                $row = [
                    $member->employee_number ?? 'N/A',
                    $member->email ?? 'N/A',
                    $member->first_name ?? 'N/A',
                    $member->middle_name ?? 'N/A',
                    $member->last_name ?? 'N/A',
                    $member->phone_number ?? 'N/A',
                    $member->department ?? 'N/A',
                    $member->employment_status ?? 'N/A',
                    $member->status ?? 'N/A',
                    $member->birthdate ? \Carbon\Carbon::parse($member->birthdate)->format('m/d/Y') : 'N/A',
                    $member->created_at ? $member->created_at->format('m/d/Y H:i:s') : 'N/A',
                    $member->updated_at ? $member->updated_at->format('m/d/Y H:i:s') : 'N/A',
                ];
                
                fputcsv($file, $row);
            }
            
            fclose($file);
        };

        // Log the export activity
        \Log::info('Faculty data exported by admin: ' . Auth::guard('admin')->user()->email);

        return response()->stream($callback, 200, $headers);
        
    } catch (\Exception $e) {
        \Log::error('Faculty export error: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Failed to export faculty data. Please try again.');
    }
}

/**
 * Export Filtered Faculty data to CSV
 */
public function exportFilteredFaculty(Request $request)
{
    try {
        // Build query based on filters
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
        
        $faculty = $query->orderBy('last_name', 'asc')
                         ->orderBy('first_name', 'asc')
                         ->get();

        // Check if there's any faculty data
        if ($faculty->isEmpty()) {
            return redirect()->back()->with('error', 'No faculty data found matching the selected filters.');
        }

        // Create filename with current date and filter info
        $filterInfo = '';
        if ($request->has('department') && !empty($request->department)) {
            $filterInfo .= '_' . str_replace(' ', '-', strtolower($request->department));
        }
        if ($request->has('employment_status') && !empty($request->employment_status)) {
            $filterInfo .= '_' . str_replace('-', '', strtolower($request->employment_status));
        }
        if ($request->has('status') && !empty($request->status)) {
            $filterInfo .= '_' . strtolower($request->status);
        }
        
        $filename = 'faculty_data' . $filterInfo . '_' . date('Y-m-d_H-i-s') . '.csv';
        
        // Set headers for CSV download
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        // Create CSV content
        $callback = function() use ($faculty, $request) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for proper UTF-8 encoding in Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Add filter information as comments
            if ($request->has('department') || $request->has('employment_status') || $request->has('status')) {
                fputcsv($file, ['# Faculty Export with Filters']);
                fputcsv($file, ['# Generated on: ' . date('Y-m-d H:i:s')]);
                
                if ($request->has('department') && !empty($request->department)) {
                    fputcsv($file, ['# Department Filter: ' . $request->department]);
                }
                if ($request->has('employment_status') && !empty($request->employment_status)) {
                    fputcsv($file, ['# Employment Status Filter: ' . $request->employment_status]);
                }
                if ($request->has('status') && !empty($request->status)) {
                    fputcsv($file, ['# Account Status Filter: ' . $request->status]);
                }
                
                fputcsv($file, ['# Total Records: ' . $faculty->count()]);
                fputcsv($file, ['']); // Empty row for separation
            }
            
            // CSV Headers
            $headers = [
                'Employee Number',
                'Email Address',
                'First Name',
                'Middle Name',
                'Last Name',
                'Phone Number',
                'Department',
                'Employment Status',
                'Account Status',
                'Birthdate',
                'Date Created',
                'Last Updated'
            ];
            
            fputcsv($file, $headers);
            
            // Add faculty data
            foreach ($faculty as $member) {
                $row = [
                    $member->employee_number ?? 'N/A',
                    $member->email ?? 'N/A',
                    $member->first_name ?? 'N/A',
                    $member->middle_name ?? 'N/A',
                    $member->last_name ?? 'N/A',
                    $member->phone_number ?? 'N/A',
                    $member->department ?? 'N/A',
                    $member->employment_status ?? 'N/A',
                    $member->status ?? 'N/A',
                    $member->birthdate ? \Carbon\Carbon::parse($member->birthdate)->format('m/d/Y') : 'N/A',
                    $member->created_at ? $member->created_at->format('m/d/Y H:i:s') : 'N/A',
                    $member->updated_at ? $member->updated_at->format('m/d/Y H:i:s') : 'N/A',
                ];
                
                fputcsv($file, $row);
            }
            
            fclose($file);
        };

        // Log the export activity
        \Log::info('Filtered faculty data exported by admin: ' . Auth::guard('admin')->user()->email . ' | Filters: ' . json_encode($request->only(['department', 'employment_status', 'status'])));

        return response()->stream($callback, 200, $headers);
        
    } catch (\Exception $e) {
        \Log::error('Filtered faculty export error: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Failed to export faculty data. Please try again.');
    }
}
    //Get Courses for Dropdown
    public function getCoursesForDropdown()
    {
        return Course::where('status', 'active')->orderBy('course_name', 'asc')->get();
    }

    //Show Student Page
   
    public function studentPage()
    {
        $admin = Auth::guard('admin')->user();
        
        // Get students sorted by last_name in ascending order
        $users = User::students()->orderBy('last_name', 'asc')->get();
        
        // Get all active courses
        $courses = Course::where('status', 'active')->orderBy('course_name', 'asc')->get();
        
        // Get all filter counts
        $filterCounts = User::getAllFilterCounts();
        $programCounts = $filterCounts['programs'];
        $yearCounts = $filterCounts['years'];
        $sectionCounts = $filterCounts['sections'];
        $statusCounts = $filterCounts['statuses'];
        
        return view('admin.user-management.student', compact('admin', 'users', 'courses', 'programCounts', 'yearCounts', 'sectionCounts', 'statusCounts'));
    }
    
    //Show Student Details
    public function viewStudent($userId)
    {
        $admin = Auth::guard('admin')->user();
        // Get the user with the given ID
        $student = User::findOrFail($userId);
    
        $courses = Course::where('status', 'active')->orderBy('course_name', 'asc')->get();
        return view('admin.user-management.view-student', compact('admin', 'student',  'courses'));
    }    


    // Update Student Details
   public function updateStudent(Request $request, $userId)
    {
        // Get valid course names for validation
        $validCourses = Course::where('status', 'active')->pluck('course_name')->toArray();
        
        // Validate request
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255|min:2|regex:/^[a-zA-Z\s\-\.\']+$/',
            'middle_name' => 'nullable|string|max:255|regex:/^[a-zA-Z\s\-\.\']+$/',
            'last_name' => 'required|string|max:255|min:2|regex:/^[a-zA-Z\s\-\.\']+$/',
            'email' => 'required|email|max:255|unique:users,email,' . $userId,
            'program' => [
                'required',
                'string',
                'in:' . implode(',', $validCourses)
            ],
            'student_number' => [
                'required',
                'string',
                'max:20',
                'min:5',
                'unique:users,student_number,' . $userId,
                'regex:/^[A-Za-z0-9\-]+$/'
            ],
            'year' => [
                'required',
                'string',
                'in:1st Year,2nd Year,3rd Year,4th Year'
            ],
            'section' => [
                'required',
                'string',
                'in:1,2,3,4,5,6,7,8,9,10'
            ],
            'birthdate' => [
                'required',
                'date',
                'before:today',
                'after:1900-01-01'
            ],
        ], [
            // Custom error messages
            'first_name.regex' => 'First name can only contain letters, spaces, hyphens, dots, and apostrophes.',
            'middle_name.regex' => 'Middle name can only contain letters, spaces, hyphens, dots, and apostrophes.',
            'last_name.regex' => 'Last name can only contain letters, spaces, hyphens, dots, and apostrophes.',
            'program.in' => 'Please select a valid program.',
            'year.in' => 'Please select a valid year.',
            'section.in' => 'Please select a valid section.',
            'student_number.regex' => 'Student number can only contain letters, numbers, and hyphens.',
            'student_number.unique' => 'This student number is already taken.',
            'email.unique' => 'This email address is already taken.',
            'birthdate.before' => 'Birthdate must be before today.',
            'birthdate.after' => 'Birthdate must be after 1900.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Find student
        $student = User::findOrFail($userId);

        // Check if any data has changed
        $fields = ['first_name', 'middle_name', 'last_name', 'email', 'program', 
                'student_number', 'year', 'section', 'birthdate'];
        $hasChanges = false;
        
        foreach ($fields as $field) {
            if ($field === 'birthdate') {
                $formattedDate = \Carbon\Carbon::parse($request->birthdate)->format('Y-m-d');
                if ($formattedDate != $student->birthdate) {
                    $hasChanges = true;
                    break;
                }
            } elseif ($field === 'middle_name') {
                // Handle null/empty middle name comparison
                $requestValue = $request->$field ?: null;
                $studentValue = $student->$field ?: null;
                if ($requestValue != $studentValue) {
                    $hasChanges = true;
                    break;
                }
            } elseif ($request->$field != $student->$field) {
                $hasChanges = true;
                break;
            }
        }

        if (!$hasChanges) {
            return redirect()->back()->with('error', 'No changes were made to update.');
        }

        // Update student
        $student->update([
            'first_name' => ucwords(strtolower($request->first_name)),
            'middle_name' => $request->middle_name ? ucwords(strtolower($request->middle_name)) : null,
            'last_name' => ucwords(strtolower($request->last_name)),
            'email' => strtolower($request->email),
            'program' => $request->program,
            'student_number' => strtoupper($request->student_number),
            'year' => $request->year,
            'section' => $request->section,
            'birthdate' => \Carbon\Carbon::parse($request->birthdate)->format('Y-m-d'),
        ]);

        return redirect()->back()->with('success', 'Student details updated successfully!');
    }

    //Deactivate and reactivate faculty
    public function toggleUserStatus(Request $request, $userId)
    {
        $user = User::find($userId);
    
        if ($user) {
            $action = $request->input('action');
    
            if ($action === 'deactivate') {
                $user->status = 'Deactivated';
            } elseif ($action === 'reactivate') {
                $user->status = 'Active';
            }
    
            $user->save();
    
            return response()->json(['success' => true]);
        }
    
        return response()->json(['success' => false]);
    } 

    //Store Student
    public function storeStudent(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|unique:users,email',
                'first_name' => 'required|string|max:255',
                'middle_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'student_number' => 'required|string|max:255|unique:users,student_number',
                'program' => 'required|string|max:255',
                'year' => 'required|string|max:255',
                'section' => 'required|string|max:255',
                'birthdate' => 'required|date',
            ]);
    
            // Generate a password
            $randomNumbers = rand(10000, 99999);
            $firstTwoLetters = strtoupper(substr($validated['first_name'], 0, 1) . substr($validated['last_name'], 0, 1));
            
            $specialChars = "!@#$%^&*";
            $specialChar = $specialChars[rand(0, strlen($specialChars) - 1)];
        
            $password = $randomNumbers . $firstTwoLetters . $specialChar;
            $hashedPassword = Hash::make($password);
    
            $user = User::create([
                'role' => 'Student',
                'status' => 'Active',
                'email' => $validated['email'],
                'password' => $hashedPassword,
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'student_number' => $validated['student_number'],
                'program' => $validated['program'],
                'year' => $validated['year'],
                'section' => $validated['section'],
                'birthdate' => $validated['birthdate'],
            ]);
    
            // Send email notification with credentials
            try {
                Mail::send('emails.credentials', ['user' => $user, 'password' => $password], function($message) use ($user) {
                    $message->to($user->email)
                            ->subject('PUP-Taguig Systems - Your Account Details');
                });
            } catch (\Exception $mailError) {
                \Log::error('Email sending failed: ' . $mailError->getMessage());
                // Continue execution even if email fails
            }
    
            return response()->json(['message' => 'Student added successfully! Login details have been sent to their email.', 'user' => $user], 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Error adding student: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['message' => 'An error occurred while adding the student.'], 500);
        }
    }

    // Import Students from CSV or Excel
    public function importStudents(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'import_file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('import_error', 'Please upload a valid CSV or Excel file.')
                ->withErrors($validator);
        }

        try {
            $file = $request->file('import_file');
            
            // Additional file validation
            if ($file->getSize() == 0) {
                return redirect()->back()->with('import_error', 'The uploaded file is empty.');
            }
            
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Check if file has data
            if (empty($rows) || count($rows) < 2) {
                return redirect()->back()->with('import_error', 'The file must contain at least one data row besides the header.');
            }
            
            // Check maximum rows limit
            if (count($rows) > 1001) { // 1000 data rows + 1 header
                return redirect()->back()->with('import_error', 'File contains too many rows. Maximum allowed is 1000 students per import.');
            }

            // The first row should be headers
            $headers = array_map('strtolower', array_map('trim', $rows[0]));
            
            // Check for empty headers
            if (in_array('', $headers) || in_array(null, $headers)) {
                return redirect()->back()->with('import_error', 'Header row contains empty columns. Please ensure all columns have proper headers.');
            }
            
            // Check if all required headers are present
            $requiredHeaders = ['email', 'first name', 'last name', 'student number', 'program', 'year', 'section'];
            $missingHeaders = array_diff($requiredHeaders, $headers);
            
            if (!empty($missingHeaders)) {
                return redirect()->back()
                    ->with('import_error', 'Missing required columns: ' . implode(', ', $missingHeaders));
            }

            // Remove the header row
            array_shift($rows);
            
            $imported = 0;
            $failed = 0;
            $emailsSent = 0;
            $emailsFailed = 0;
            $errors = [];
            $totalRows = count($rows);
            
            // Pre-check for duplicates within the file
            $fileEmails = [];
            $fileStudentNumbers = [];
            
            foreach ($rows as $index => $row) {
                if (empty(array_filter($row))) continue;
                
                $rowData = [];
                foreach ($headers as $colIndex => $header) {
                    $rowData[str_replace(' ', '_', strtolower($header))] = isset($row[$colIndex]) ? trim($row[$colIndex]) : null;
                }
                
                $email = strtolower($rowData['email'] ?? '');
                $studentNumber = $rowData['student_number'] ?? '';
                $rowNumber = $index + 2;
                
                if ($email) {
                    if (in_array($email, $fileEmails)) {
                        $errors[] = "Row $rowNumber: Email '$email' is duplicated in the file";
                    } else {
                        $fileEmails[] = $email;
                    }
                }
                
                if ($studentNumber) {
                    if (in_array($studentNumber, $fileStudentNumbers)) {
                        $errors[] = "Row $rowNumber: Student number '$studentNumber' is duplicated in the file";
                    } else {
                        $fileStudentNumbers[] = $studentNumber;
                    }
                }
            }
            
            // Process each row
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;
                
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }
                
                // Map columns to user fields
                $rowData = [];
                foreach ($headers as $colIndex => $header) {
                    $value = isset($row[$colIndex]) ? trim($row[$colIndex]) : null;
                    $rowData[str_replace(' ', '_', strtolower($header))] = $value;
                }
                
                $hasError = false;
                $rowErrors = [];
                
                // Validate email
                $email = $rowData['email'] ?? '';
                if (empty($email)) {
                    $rowErrors[] = "Email is required";
                    $hasError = true;
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $rowErrors[] = "Email '$email' is not a valid email format";
                    $hasError = true;
                } elseif (User::where('email', strtolower($email))->exists()) {
                    $rowErrors[] = "Email '$email' already exists in the system";
                    $hasError = true;
                }
                
                // Validate first name
                $firstName = $rowData['first_name'] ?? '';
                if (empty($firstName)) {
                    $rowErrors[] = "First name is required";
                    $hasError = true;
                } elseif (strlen($firstName) < 2) {
                    $rowErrors[] = "First name '$firstName' must be at least 2 characters";
                    $hasError = true;
                } elseif (!preg_match('/^[a-zA-Z\s\-\.\']+$/', $firstName)) {
                    $rowErrors[] = "First name '$firstName' contains invalid characters";
                    $hasError = true;
                }
                
                // Validate last name
                $lastName = $rowData['last_name'] ?? '';
                if (empty($lastName)) {
                    $rowErrors[] = "Last name is required";
                    $hasError = true;
                } elseif (strlen($lastName) < 2) {
                    $rowErrors[] = "Last name '$lastName' must be at least 2 characters";
                    $hasError = true;
                } elseif (!preg_match('/^[a-zA-Z\s\-\.\']+$/', $lastName)) {
                    $rowErrors[] = "Last name '$lastName' contains invalid characters";
                    $hasError = true;
                }
                
                // Validate middle name (optional)
                $middleName = $rowData['middle_name'] ?? '';
                if (!empty($middleName) && !preg_match('/^[a-zA-Z\s\-\.\']+$/', $middleName)) {
                    $rowErrors[] = "Middle name '$middleName' contains invalid characters";
                    $hasError = true;
                }
                
                // Validate student number
                $studentNumber = $rowData['student_number'] ?? '';
                if (empty($studentNumber)) {
                    $rowErrors[] = "Student number is required";
                    $hasError = true;
                } elseif (strlen($studentNumber) < 5) {
                    $rowErrors[] = "Student number '$studentNumber' must be at least 5 characters";
                    $hasError = true;
                } elseif (!preg_match('/^[A-Za-z0-9\-]+$/', $studentNumber)) {
                    $rowErrors[] = "Student number '$studentNumber' contains invalid characters";
                    $hasError = true;
                } elseif (User::where('student_number', $studentNumber)->exists()) {
                    $rowErrors[] = "Student number '$studentNumber' already exists in the system";
                    $hasError = true;
                }
                
                // Validate program
                $program = $rowData['program'] ?? '';
                if (empty($program)) {
                    $rowErrors[] = "Program is required";
                    $hasError = true;
                }
                
                // Validate year
                $year = $rowData['year'] ?? '';
                $validYears = ['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year', '1', '2', '3', '4', '5'];
                if (empty($year)) {
                    $rowErrors[] = "Year is required";
                    $hasError = true;
                } elseif (!in_array($year, $validYears)) {
                    $rowErrors[] = "Year '$year' is not valid. Use: 1st Year, 2nd Year, etc.";
                    $hasError = true;
                }
                
                // Validate section
                $section = $rowData['section'] ?? '';
                if (empty($section)) {
                    $rowErrors[] = "Section is required";
                    $hasError = true;
                } elseif (!preg_match('/^[A-Za-z0-9\-]+$/', $section)) {
                    $rowErrors[] = "Section '$section' contains invalid characters";
                    $hasError = true;
                }
                
                // Validate birthdate (optional)
                $birthdate = $rowData['birthdate'] ?? '';
                if (!empty($birthdate)) {
                    try {
                        $birthDate = Carbon::parse($birthdate);
                        $age = $birthDate->age;
                        if ($age < 15 || $age > 100) {
                            $rowErrors[] = "Age based on birthdate '$birthdate' must be between 15 and 100 years";
                            $hasError = true;
                        }
                    } catch (\Exception $e) {
                        $rowErrors[] = "Birthdate '$birthdate' is not a valid date format";
                        $hasError = true;
                    }
                }
                
                // If there are errors for this row, add them to the errors array
                if ($hasError) {
                    foreach ($rowErrors as $error) {
                        $errors[] = "Row $rowNumber ({$firstName} {$lastName}): {$error}";
                    }
                    $failed++;
                    continue;
                }
                
                // Create the user if all validations pass
                try {
                    // Generate a password like in storeStudent
                    $randomNumbers = rand(10000, 99999);
                    $firstTwoLetters = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
                    
                    $specialChars = "!@#$%^&*";
                    $specialChar = $specialChars[rand(0, strlen($specialChars) - 1)];
                
                    $password = $randomNumbers . $firstTwoLetters . $specialChar;
                    $hashedPassword = Hash::make($password);

                    $user = new User();
                    $user->role = 'Student';
                    $user->email = strtolower($email);
                    $user->password = $hashedPassword;
                    $user->first_name = ucwords(strtolower($firstName));
                    $user->middle_name = !empty($middleName) ? ucwords(strtolower($middleName)) : null;
                    $user->last_name = ucwords(strtolower($lastName));
                    $user->student_number = strtoupper($studentNumber);
                    $user->program = $program;
                    $user->year = $year;
                    $user->section = strtoupper($section);
                    $user->birthdate = !empty($birthdate) ? Carbon::parse($birthdate)->format('Y-m-d') : null;
                    $user->status = 'Active';
                    
                    $user->save();
                    $imported++;

                    // Send email notification with credentials
                    try {
                        Mail::send('emails.credentials', ['user' => $user, 'password' => $password], function($message) use ($user) {
                            $message->to($user->email)
                                    ->subject('PUP-Taguig Systems - Your Account Details');
                        });
                        $emailsSent++;
                    } catch (\Exception $mailError) {
                        \Log::error('Email sending failed for ' . $user->email . ': ' . $mailError->getMessage());
                        $emailsFailed++;
                        // Continue execution even if email fails
                    }
                    
                } catch (\Exception $e) {
                    $errors[] = "Row $rowNumber ({$firstName} {$lastName}): Failed to save - " . $e->getMessage();
                    $failed++;
                    continue;
                }
            }
            
            // Prepare session data
            $summary = [
                'total' => $totalRows,
                'success' => $imported,
                'failed' => $failed,
                'emails_sent' => $emailsSent,
                'emails_failed' => $emailsFailed
            ];
            
            // Set appropriate messages
            if ($imported > 0 && $failed == 0) {
                // All successful
                $emailMessage = $emailsFailed > 0 ? " Note: {$emailsFailed} email(s) failed to send." : " All login credentials have been sent via email.";
                return redirect()->back()
                    ->with('import_success', "Successfully imported all $imported student(s)!{$emailMessage}")
                    ->with('import_summary', $summary);
            } elseif ($imported > 0 && $failed > 0) {
                // Partial success
                $emailMessage = $emailsFailed > 0 ? " Note: {$emailsFailed} email(s) failed to send." : "";
                return redirect()->back()
                    ->with('import_success', "Successfully imported $imported student(s). $failed row(s) had errors and were skipped.{$emailMessage}")
                    ->with('import_errors', $errors)
                    ->with('import_summary', $summary);
            } elseif ($imported == 0 && $failed > 0) {
                // All failed
                return redirect()->back()
                    ->with('import_error', "No students were imported. All $failed row(s) contained errors.")
                    ->with('import_errors', $errors)
                    ->with('import_summary', $summary);
            } else {
                // No data processed
                return redirect()->back()->with('import_error', 'No valid data found to import.');
            }
            
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            Log::error('Spreadsheet reading error: ' . $e->getMessage());
            return redirect()->back()->with('import_error', 'Unable to read the file. Please ensure it is a valid Excel or CSV file.');
        } catch (\Exception $e) {
            Log::error('Import error: ' . $e->getMessage());
            return redirect()->back()->with('import_error', 'Failed to import students: ' . $e->getMessage());
        }
    }
 
    // Download a template file for student import
    public function downloadTemplate()
    {
        // Create a template file using PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
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
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        $tempFile = tempnam(sys_get_temp_dir(), 'student_template');
        $writer->save($tempFile);
        
        // Return the download response
        return response()->download($tempFile, 'student_import_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="student_import_template.xlsx"',
        ])->deleteFileAfterSend(true);
    }

    public function exportStudents(Request $request)
{
    try {
        // Get all students with their data
        $students = User::where('role', 'Student')
                       ->orderBy('last_name', 'asc')
                       ->orderBy('first_name', 'asc')
                       ->get();

        // Check if there's any student data
        if ($students->isEmpty()) {
            return redirect()->back()->with('error', 'No student data available to export.');
        }

        // Create filename with current date
        $filename = 'students_data_' . date('Y-m-d_H-i-s') . '.csv';
        
        // Set headers for CSV download
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        // Create CSV content
        $callback = function() use ($students) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for proper UTF-8 encoding in Excel
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
            
            // Add student data
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
                    $student->birthdate ? \Carbon\Carbon::parse($student->birthdate)->format('m/d/Y') : 'N/A',
                    $student->created_at ? $student->created_at->format('m/d/Y H:i:s') : 'N/A',
                    $student->updated_at ? $student->updated_at->format('m/d/Y H:i:s') : 'N/A',
                ];
                
                fputcsv($file, $row);
            }
            
            fclose($file);
        };

        // Log the export activity
        \Log::info('Students data exported by admin: ' . Auth::guard('admin')->user()->email);

        return response()->stream($callback, 200, $headers);
        
    } catch (\Exception $e) {
        \Log::error('Students export error: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Failed to export student data. Please try again.');
    }
}

/**
 * Export Filtered Students data to CSV
 */
public function exportFilteredStudents(Request $request)
{
    try {
        // Build query based on filters
        $query = User::where('role', 'Student');
        
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
        
        $students = $query->orderBy('last_name', 'asc')
                          ->orderBy('first_name', 'asc')
                          ->get();

        // Check if there's any student data
        if ($students->isEmpty()) {
            return redirect()->back()->with('error', 'No student data found matching the selected filters.');
        }

        // Create filename with current date and filter info
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
        
        // Set headers for CSV download
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        // Create CSV content
        $callback = function() use ($students, $request) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for proper UTF-8 encoding in Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Add filter information as comments
            if ($request->has('program') || $request->has('year') || $request->has('section') || $request->has('status')) {
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
                fputcsv($file, ['']); // Empty row for separation
            }
            
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
            
            // Add student data
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
                    $student->birthdate ? \Carbon\Carbon::parse($student->birthdate)->format('m/d/Y') : 'N/A',
                    $student->created_at ? $student->created_at->format('m/d/Y H:i:s') : 'N/A',
                    $student->updated_at ? $student->updated_at->format('m/d/Y H:i:s') : 'N/A',
                ];
                
                fputcsv($file, $row);
            }
            
            fclose($file);
        };

        // Log the export activity
        \Log::info('Filtered students data exported by admin: ' . Auth::guard('admin')->user()->email . ' | Filters: ' . json_encode($request->only(['program', 'year', 'section', 'status'])));

        return response()->stream($callback, 200, $headers);
        
    } catch (\Exception $e) {
        \Log::error('Filtered students export error: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Failed to export student data. Please try again.');
    }
}

public function bulkToggleUserStatus(Request $request)
{
    try {
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
            'action' => 'required|in:activate,deactivate,reactivate'
        ]);

        $userIds = $validated['user_ids'];
        $action = $validated['action'];
        
        // Normalize action
        $newStatus = ($action === 'deactivate') ? 'Deactivated' : 'Active';
        
        // Get users to be updated
        $users = User::whereIn('id', $userIds)->get();
        
        if ($users->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No users found to update.'
            ], 404);
        }
        
        // Filter users that actually need status change
        $usersToUpdate = $users->filter(function ($user) use ($newStatus) {
            return $user->status !== $newStatus;
        });
        
        if ($usersToUpdate->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'All selected users already have the requested status.'
            ], 400);
        }
        
        // Update user statuses
        $updatedCount = User::whereIn('id', $usersToUpdate->pluck('id'))->update([
            'status' => $newStatus,
            'updated_at' => now()
        ]);
        
        // Log the bulk action
        $adminEmail = Auth::guard('admin')->user()->email;
        $userNames = $usersToUpdate->map(function ($user) {
            return $user->first_name . ' ' . $user->last_name . ' (' . ($user->student_number ?? $user->employee_number) . ')';
        })->join(', ');
        
        \Log::info("Bulk {$action} performed by admin: {$adminEmail} on users: {$userNames}");
        
        return response()->json([
            'success' => true,
            'message' => "Successfully {$action}d {$updatedCount} user(s).",
            'updated_count' => $updatedCount,
            'total_selected' => count($userIds),
            'action' => $action,
            'new_status' => $newStatus
        ]);
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid request data.',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        \Log::error('Bulk toggle user status error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while updating user statuses.'
        ], 500);
    }
}
}
