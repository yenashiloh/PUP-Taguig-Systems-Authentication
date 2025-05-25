<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Department;
use App\Models\User;
use App\Models\Course;

class CourseDepartmentController extends Controller
{
    //Show Course Department Page
    public function courseDepartmentPage()
    {
        $admin = Auth::guard('admin')->user();
        
        //Get all users
        $users = User::all();
        $departments = Department::all();
        $courses = Course::with('department')->get();

        return view('admin.settings.course', compact('admin', 'users', 'departments', 'courses'));
    }

    //Show Course Department Page
    public function DepartmentPage()
    {
        $admin = Auth::guard('admin')->user();
        //Get all users
        $users = User::all();

        $departments = Department::all();

        return view('admin.settings.department', compact('admin', 'users', 'departments'));
    }

    //Store Department
      public function storeDepartment(Request $request)
    {
        $request->validate([
            'dept_name' => 'required|string|max:255|unique:departments,dept_name',
        ], [
            'dept_name.unique' => 'This department already exists.',
            'dept_name.required' => 'Please enter a department name.',
        ]);
    
        Department::create([
            'dept_name' => $request->dept_name,
            'status' => 'Active', // Set default status
        ]);
    
        return redirect()->route('admin.settings.department')->with('success', 'Department added successfully!');
    }
    
 public function updateDepartment(Request $request, $id)
    {
        try {
            $request->validate([
                'dept_name' => 'required|string|max:255|unique:departments,dept_name,' . $id . ',department_id',
                'status' => 'required|in:Active,Inactive',
            ], [
                'dept_name.unique' => 'This department name already exists.',
                'dept_name.required' => 'Please enter a department name.',
                'status.required' => 'Please select a status.',
                'status.in' => 'Status must be either Active or Inactive.',
            ]);

            $department = Department::findOrFail($id);
            
            // Check if any changes were made
            if ($department->dept_name === $request->dept_name && $department->status === $request->status) {
                return response()->json([
                    'success' => false,
                    'message' => 'No changes were made to update.'
                ]);
            }
            
            $department->update([
                'dept_name' => $request->dept_name,
                'status' => $request->status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Department updated successfully!'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating department: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the department.'
            ], 500);
        }
    }

     public function toggleDepartmentStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:Active,Inactive',
            ]);

            $department = Department::findOrFail($id);
            
            // Check if the status is actually changing
            if ($department->status === $request->status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Department already has this status.'
                ]);
            }
            
            $department->update([
                'status' => $request->status,
            ]);

            $statusText = $request->status === 'Active' ? 'enabled' : 'disabled';

            return response()->json([
                'success' => true,
                'message' => "Department {$statusText} successfully!"
            ]);

        } catch (\Exception $e) {
            \Log::error('Error toggling department status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the department status.'
            ], 500);
        }
    }
    

    //Store Course
    public function storeCourse(Request $request)
    {
        $request->validate([
            'course_name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,department_id', 
        ]);
    
        // Check if the combination already exists
        $existingCourse = Course::where('course_name', $request->course_name)
                                ->where('department_id', $request->department_id)
                                ->first();
    
        if ($existingCourse) {
            return redirect()->back()->withErrors(['duplicate' => 'The course already exists for this department.'])->withInput();
        }
    
        try {
            Course::create([
                'course_name' => $request->course_name,
                'department_id' => $request->department_id,
                'status' => 'Active', 
            ]);
    
            return redirect()->back()->with('success', 'Course added successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to add course. Please try again.');
        }
    }

    //Destroy Course
    public function destroyCourse($id)
    {
        $course = Course::findOrFail($id); 
        $course->delete();
    
        return response()->json(['success' => true, 'message' => 'Course deleted successfully.']);
    }

    //Destroy Department
     public function destroyDepartment($id)
    {
        try {
            $department = Department::findOrFail($id);
            
            // Check if any courses are associated with this department
            $coursesCount = Course::where('department_id', $id)->count();
            
            if ($coursesCount > 0) {
                return response()->json([
                    'success' => false, 
                    'message' => "Cannot delete this department. {$coursesCount} course(s) are associated with it."
                ]);
            }
            
            // Check if any faculty are in this department
            $facultyCount = User::where('department', $department->dept_name)
                               ->where('role', 'Faculty')
                               ->count();
            
            if ($facultyCount > 0) {
                return response()->json([
                    'success' => false, 
                    'message' => "Cannot delete this department. {$facultyCount} faculty member(s) are assigned to it."
                ]);
            }
            
            $department->delete();

            return response()->json([
                'success' => true, 
                'message' => 'Department deleted successfully.'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error deleting department: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'An error occurred while deleting the department.'
            ]);
        }
    }
}
