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
    // Show Course Department Page
    public function courseDepartmentPage()
    {
        $admin = Auth::guard('admin')->user();
        $users = User::all();
        $departments = Department::all();
        $courses = Course::all(); // Removed 'with' since no department relation

        return view('admin.settings.course', compact('admin', 'users', 'departments', 'courses'));
    }

    // Show Department Page
    public function DepartmentPage()
    {
        $admin = Auth::guard('admin')->user();
        $users = User::all();
        $departments = Department::all();

        return view('admin.settings.department', compact('admin', 'users', 'departments'));
    }

    // Store Department
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
            'status' => 'Active',
        ]);

        return redirect()->route('admin.settings.department')->with('success', 'Department added successfully!');
    }

    // Update Department
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

    // Toggle Department Status
    public function toggleDepartmentStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:Active,Inactive',
            ]);

            $department = Department::findOrFail($id);

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

    // Store Course
    public function storeCourse(Request $request)
    {
        $request->validate([
            'course_name' => 'required|string|max:255|unique:courses,course_name',
        ], [
            'course_name.unique' => 'This course already exists.',
            'course_name.required' => 'Please enter a course name.',
        ]);

        try {
            Course::create([
                'course_name' => $request->course_name,
                'status' => 'Active',
            ]);

            return redirect()->back()->with('success', 'Course added successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to add course. Please try again.');
        }
    }

    // Toggle Course Status
    public function toggleCourseStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:Active,Inactive',
            ]);

            $course = Course::findOrFail($id);

            if ($course->status === $request->status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Course already has this status.'
                ]);
            }

            $course->update([
                'status' => $request->status,
            ]);

            $statusText = $request->status === 'Active' ? 'enabled' : 'disabled';

            return response()->json([
                'success' => true,
                'message' => "Course {$statusText} successfully!"
            ]);

        } catch (\Exception $e) {
            \Log::error('Error toggling course status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the course status.'
            ], 500);
        }
    }

    // Destroy Course
    public function destroyCourse($id)
    {
        try {
            $course = Course::findOrFail($id);
            $course->delete();

            return response()->json(['success' => true, 'message' => 'Course deleted successfully.']);
        } catch (\Exception $e) {
            \Log::error('Error deleting course: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the course.'
            ], 500);
        }
    }

    // Destroy Department
    public function destroyDepartment($id)
    {
        try {
            $department = Department::findOrFail($id);

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
            ], 500);
        }
    }
}