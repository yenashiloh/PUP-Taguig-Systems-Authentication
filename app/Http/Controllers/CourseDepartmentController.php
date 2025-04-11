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
        ]);
    
        return redirect()->route('admin.settings.department')->with('success', 'Department added successfully!');
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
        $department = Department::findOrFail($id);
        $department->delete();

        return response()->json(['success' => true, 'message' => 'Department deleted successfully.']);
    }
}
