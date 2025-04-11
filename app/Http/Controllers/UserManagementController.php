<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

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
        
        // Get users with the role "Student"
        $users = User::where('role', 'Student')->get();

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
                $user->status = 'Activate';
            }
    
            $user->save();
    
            return response()->json(['success' => true]);
        }
    
        return response()->json(['success' => false]);
    }    
    
}
