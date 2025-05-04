<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;


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
        $users = User::where('role', 'Faculty')->get();

        return view('admin.user-management.faculty', compact('admin', 'users'));
    }

    //Show Faculty Details
    public function viewFaculty($userId)
    {
        $admin = Auth::guard('admin')->user();
        // Get the user with the given ID
        $faculty = User::findOrFail($userId);
    
        return view('admin.user-management.view-faculty', compact('admin', 'faculty'));
    }    

    //Show Student Page
    public function studentPage()
{
    $admin = Auth::guard('admin')->user();

    // Get users with the role "Student" and non-null program, year, section
    // And sort by last_name in ascending order
    $users = User::where('role', 'Student')
                ->whereNotNull('program')
                ->whereNotNull('year')
                ->whereNotNull('section')
                ->orderBy('last_name', 'asc')
                ->get();

    // You don't need to fetch programs, years, sections from the database anymore
    // since we're using static dropdown options

    return view('admin.user-management.student', compact('admin', 'users'));
}
    
    //Show Student Details
    public function viewStudent($userId)
    {
        $admin = Auth::guard('admin')->user();
        // Get the user with the given ID
        $student = User::findOrFail($userId);
    
        return view('admin.user-management.view-student', compact('admin', 'student'));
    }    

    //Update Faculty Details
    public function updateFaculty(Request $request, $userId)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|email|max:255|unique:users,email,'.$userId,
            'employee_number' => 'required|string|max:50|unique:users,employee_number,'.$userId,
            'department' => 'required|string|max:255',
            'birthdate' => 'required|date',
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
                'email', 'employee_number', 'department', 'birthdate'];
        
        foreach ($fields as $field) {
            if ($request->filled($field) && $faculty->{$field} != $request->{$field}) {
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
            'birthdate' => $request->birthdate,
        ]);
        
        return redirect()->back()->with('success', 'Faculty details updated successfully!');
    }

    // Update Student Details
    public function updateStudent(Request $request, $userId)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $userId,
            'program' => 'required|string|max:255',
            'student_number' => 'required|string|max:255',
            'year' => 'required|string|max:50',
            'section' => 'required|string|max:50',
            'birthdate' => 'required|date',
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
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'program' => $request->program,
            'student_number' => $request->student_number,
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

    public function importStudents(Request $request)
    {
        // Validate the uploaded file
        $validator = Validator::make($request->all(), [
            'import_file' => 'required|file|mimes:csv,xlsx,xls',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', 'Please upload a valid CSV or Excel file.')
                ->withErrors($validator);
        }

        try {
            $file = $request->file('import_file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // The first row should be headers
            $headers = array_map('strtolower', $rows[0]);
            
            // Check if all required headers are present
            $requiredHeaders = ['email', 'first name', 'last name', 'student number', 'program', 'year', 'section'];
            $missingHeaders = array_diff($requiredHeaders, array_map('strtolower', $headers));
            
            if (!empty($missingHeaders)) {
                return redirect()->back()
                    ->with('error', 'Missing required columns: ' . implode(', ', $missingHeaders));
            }

            // Remove the header row
            array_shift($rows);
            
            $imported = 0;
            $errors = [];
            
            // Process each row
            foreach ($rows as $index => $row) {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }
                
                // Map columns to user fields
                $rowData = [];
                foreach ($headers as $colIndex => $header) {
                    $rowData[str_replace(' ', '_', strtolower($header))] = $row[$colIndex] ?? null;
                }
                
                // Validate the row data
                $rowValidator = Validator::make($rowData, [
                    'email' => 'required|email|unique:users,email',
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'student_number' => 'required|string|unique:users,student_number',
                    'program' => 'required|string',
                    'year' => 'required|string',
                    'section' => 'required',
                ]);
                
                if ($rowValidator->fails()) {
                    $errors[] = "Row " . ($index + 2) . ": " . implode(', ', $rowValidator->errors()->all());
                    continue;
                }
                
                // Create the user
                $user = new User();
                $user->role = 'Student';
                $user->email = $rowData['email'];
                $user->first_name = $rowData['first_name'];
                $user->middle_name = $rowData['middle_name'] ?? null;
                $user->last_name = $rowData['last_name'];
                $user->student_number = $rowData['student_number'];
                $user->program = $rowData['program'];
                $user->year = $rowData['year'];
                $user->section = $rowData['section'];
                $user->birthdate = isset($rowData['birthdate']) ? date('Y-m-d', strtotime($rowData['birthdate'])) : null;
                $user->status = 'Active';
                
                // Generate a random password
                $password = Str::random(10);
                $user->password = Hash::make($password);
                
                $user->save();
                $imported++;
                
                // Here you would typically send an email to the user with their password
                // But we'll leave that part for you to implement
            }
            
            $message = "Successfully imported $imported student(s).";
            if (!empty($errors)) {
                $message .= " There were " . count($errors) . " errors.";
                session(['import_errors' => $errors]);
            }
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            Log::error('Import error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to import students: ' . $e->getMessage());
        }
    }

    /**
     * Download a template file for student import
     */
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
