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
        
}
