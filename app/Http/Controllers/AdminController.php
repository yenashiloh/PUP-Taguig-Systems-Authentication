<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use App\Models\Course;
use App\Models\AuditTrail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // Admin dashboard page
    public function dashboardPage()
    {
        // Get admin user data from the admin guard
        $admin = Auth::guard('admin')->user();
        
        // Count the number of users with the role "Faculty"
        $facultyCount = User::where('role', 'Faculty')->count();
        // Count the number of users with the role "Student"
        $studentCount = User::where('role', 'Student')->count();

        // Count the number of users deactivated
        $deactivatedCount = User::where('status', 'Deactivated')->count();
        // Count the number of users active
        $activeCount = User::where('status', 'Active')->count();
    
        // Get the current year and month
        $currentYear = now()->year;
        $currentMonth = now()->month;
        
        // Get the count of users
        $monthlyRegistrations = User::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->whereYear('created_at', $currentYear)
                ->groupBy('month')
                ->orderBy('month', 'asc')
                ->get()
                ->keyBy('month');
        
        // Prepare data for the chart
        $months = [];
        $registrations = [];
        
        // All months from January to the current month
        for ($month = 1; $month <= $currentMonth; $month++) {
            $monthName = Carbon::createFromDate($currentYear, $month, 1)->format('F');
            $months[] = $monthName;
            
            // Get registration count 
            $registrations[] = $monthlyRegistrations->get($month, (object)['count' => 0])->count;
    }
        
        return view('admin.dashboard.dashboard', compact('facultyCount', 'studentCount', 'deactivatedCount', 'months', 'registrations', 'admin', 'activeCount'));
    }

    //Show Total Faculty Page
    public function totalFacultyPage()
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

        return view('admin.dashboard.total-faculty', compact('admin', 'users', 'departments', 'departmentCounts', 'employmentStatusCounts', 'statusCounts'));
    }

    //Show Total Faculty Details
    public function viewTotalFaculty($userId)
    {
        $admin = Auth::guard('admin')->user();
        
        // Get the faculty member with the given ID
        $faculty = User::findOrFail($userId);
        
        // Get all active departments for the dropdown
        $departments = Department::where('status', 'active')->orderBy('dept_name', 'asc')->get();

        return view('admin.dashboard.view-total-faculty', compact('admin', 'faculty', 'departments'));
    }    

     //Show Total Student Details
    public function viewTotalStudent($userId)
    {
        $admin = Auth::guard('admin')->user();
        // Get the user with the given ID
        $student = User::findOrFail($userId);
    
        $courses = Course::where('status', 'active')->orderBy('course_name', 'asc')->get();
     
         return view('admin.dashboard.view-total-student', compact('admin', 'student',  'courses'));
     }    

    //Show Total Student Page
    public function totalStudentPage()
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
        
        return view('admin.dashboard.total-student', compact('admin', 'users', 'courses', 'programCounts', 'yearCounts', 'sectionCounts', 'statusCounts'));
    }

    // Admin logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login'); 
    }

    /**
     * Show Deactivated Users Page
     */
    public function deactivatedUsersPage()
    {
        $admin = Auth::guard('admin')->user();
        
        // Get all deactivated users (both faculty and students)
        $users = User::where('status', 'Deactivated')
                    ->orderBy('role', 'asc')
                    ->orderBy('last_name', 'asc')
                    ->get();
        
        // Get counts by role
        $deactivatedFacultyCount = User::where('status', 'Deactivated')
                                    ->where('role', 'Faculty')
                                    ->count();
        
        $deactivatedStudentCount = User::where('status', 'Deactivated')
                                    ->where('role', 'Student')
                                    ->count();
        
        // Get filter counts for deactivated users
        $roleCounts = [
            'Faculty' => $deactivatedFacultyCount,
            'Student' => $deactivatedStudentCount
        ];
        
        // Get department counts for deactivated faculty
        $departmentCounts = User::where('status', 'Deactivated')
                            ->where('role', 'Faculty')
                            ->whereNotNull('department')
                            ->groupBy('department')
                            ->selectRaw('department, COUNT(*) as count')
                            ->pluck('count', 'department');
        
        // Get program counts for deactivated students
        $programCounts = User::where('status', 'Deactivated')
                            ->where('role', 'Student')
                            ->whereNotNull('program')
                            ->groupBy('program')
                            ->selectRaw('program, COUNT(*) as count')
                            ->pluck('count', 'program');
        
        // Get year counts for deactivated students
        $yearCounts = User::where('status', 'Deactivated')
                        ->where('role', 'Student')
                        ->whereNotNull('year')
                        ->groupBy('year')
                        ->selectRaw('year, COUNT(*) as count')
                        ->pluck('count', 'year');
        
        // Get all departments and courses for filters
        $departments = Department::active()->ordered()->get();
        $courses = Course::where('status', 'active')->orderBy('course_name', 'asc')->get();
        
        return view('admin.dashboard.deactivated-users', compact(
            'admin', 
            'users', 
            'roleCounts',
            'departmentCounts', 
            'programCounts', 
            'yearCounts',
            'departments',
            'courses'
        ));
    }

    /**
     * Show Deactivated User Details
     */
    public function viewDeactivatedUser($userId)
    {
        $admin = Auth::guard('admin')->user();
        
        // Get the deactivated user
        $user = User::where('id', $userId)
                ->where('status', 'Deactivated')
                ->firstOrFail();
        
        // Get departments and courses for dropdowns
        $departments = Department::where('status', 'active')->orderBy('dept_name', 'asc')->get();
        $courses = Course::where('status', 'active')->orderBy('course_name', 'asc')->get();
        
        return view('admin.dashboard.view-deactivated-user', compact(
            'admin', 
            'user', 
            'departments', 
            'courses'
        ));
    }

    /**
     * Reactivate User with Audit Trail
     */
    public function reactivateUser(Request $request, $userId)
    {
        try {
            $user = User::findOrFail($userId);
            
            if ($user->status !== 'Deactivated') {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not deactivated.'
                ]);
            }
            
            $oldStatus = $user->status;
            $user->update(['status' => 'Active']);
            
            // Log to audit trail
            AuditTrail::log(
                'reactivate_user',
                'Reactivated user: ' . $user->first_name . ' ' . $user->last_name . ' (' . ($user->student_number ?? $user->employee_number) . ')',
                'User',
                $user->id,
                $user->first_name . ' ' . $user->last_name,
                [
                    'user_id' => $user->id,
                    'user_type' => $user->role,
                    'id_number' => $user->student_number ?? $user->employee_number,
                    'email' => $user->email,
                    'old_status' => $oldStatus,
                    'new_status' => 'Active'
                ]
            );
            
            // Log the reactivation
            \Log::info('User reactivated by admin: ' . Auth::guard('admin')->user()->email . ' | User: ' . $user->email);
            
            return response()->json([
                'success' => true,
                'message' => 'User reactivated successfully!'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error reactivating user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while reactivating the user.'
            ], 500);
        }
    }

    /**
     * Bulk Reactivate Users with Audit Trail
     */
    public function bulkReactivateUsers(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_ids' => 'required|array|min:1',
                'user_ids.*' => 'integer|exists:users,id'
            ]);

            $userIds = $validated['user_ids'];
            
            // Get deactivated users to be reactivated
            $users = User::whereIn('id', $userIds)
                        ->where('status', 'Deactivated')
                        ->get();
            
            if ($users->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No deactivated users found to reactivate.'
                ]);
            }
            
            // Update user statuses
            $updatedCount = User::whereIn('id', $users->pluck('id'))
                            ->update([
                                'status' => 'Active',
                                'updated_at' => now()
                            ]);
            
            // Log to audit trail
            AuditTrail::log(
                'bulk_reactivate_users',
                "Bulk reactivated $updatedCount user(s)",
                'User',
                null,
                'Bulk Action',
                [
                    'action' => 'bulk_reactivate',
                    'user_count' => $updatedCount,
                    'new_status' => 'Active',
                    'affected_users' => $users->map(function($user) {
                        return [
                            'id' => $user->id,
                            'name' => $user->first_name . ' ' . $user->last_name,
                            'id_number' => $user->student_number ?? $user->employee_number,
                            'email' => $user->email,
                            'role' => $user->role,
                            'old_status' => 'Deactivated',
                            'new_status' => 'Active'
                        ];
                    })->toArray()
                ]
            );
            
            // Log the bulk action
            $adminEmail = Auth::guard('admin')->user()->email;
            $userNames = $users->map(function ($user) {
                return $user->first_name . ' ' . $user->last_name . ' (' . ($user->student_number ?? $user->employee_number) . ')';
            })->join(', ');
            
            \Log::info("Bulk reactivation performed by admin: {$adminEmail} on users: {$userNames}");
            
            return response()->json([
                'success' => true,
                'message' => "Successfully reactivated {$updatedCount} user(s).",
                'updated_count' => $updatedCount
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Bulk reactivate users error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while reactivating users.'
            ], 500);
        }
    }

    public function showProfile()
    {
        $admin = Auth::guard('admin')->user();
        return view('admin.profile.view-profile', compact('admin'));
    }

    /**
     * Update Admin Profile Information
     */
    public function updateProfile(Request $request)
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            $validated = $request->validate([
                'first_name' => [
                    'required',
                    'string',
                    'max:255',
                    'min:2',
                    'regex:/^[a-zA-Z\s]+$/'
                ],
                'last_name' => [
                    'required',
                    'string',
                    'max:255',
                    'min:2',
                    'regex:/^[a-zA-Z\s]+$/'
                ],
                'email' => 'required|email|max:255|unique:admins,email,' . $admin->id,
                'contact_number' => [
                    'required',
                    'string',
                    'max:20',
                    'min:10',
                    'regex:/^[0-9\+\-\(\)\s]+$/'
                ],
            ], [
                'first_name.required' => 'First name is required.',
                'first_name.min' => 'First name must be at least 2 characters long.',
                'first_name.regex' => 'First name can only contain letters and spaces.',
                
                'last_name.required' => 'Last name is required.',
                'last_name.min' => 'Last name must be at least 2 characters long.',
                'last_name.regex' => 'Last name can only contain letters and spaces.',
                
                'email.required' => 'Email address is required.',
                'email.email' => 'Please enter a valid email address.',
                'email.unique' => 'This email address is already taken.',
                
                'contact_number.required' => 'Contact number is required.',
                'contact_number.min' => 'Contact number must be at least 10 digits long.',
                'contact_number.regex' => 'Contact number can only contain numbers, spaces, hyphens, parentheses, and plus sign.',
            ]);

            // Check if any data has changed
            $hasChanges = false;
            $fields = ['first_name', 'last_name', 'email', 'contact_number'];
            
            foreach ($fields as $field) {
                if ($request->$field != $admin->$field) {
                    $hasChanges = true;
                    break;
                }
            }
            
            if (!$hasChanges) {
                return redirect()->back()->with('error', 'No changes were made to update.');
            }

            // Update admin profile
            $admin->update([
                'first_name' => ucwords(strtolower($validated['first_name'])),
                'last_name' => ucwords(strtolower($validated['last_name'])),
                'email' => strtolower($validated['email']),
                'contact_number' => $validated['contact_number'],
            ]);

            // Log the profile update
            \Log::info('Admin profile updated: ' . $admin->email);

            return redirect()->back()->with('success', 'Profile updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Error updating admin profile: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while updating your profile.');
        }
    }

    /**
     * Update Admin Password
     */
    public function updatePassword(Request $request)
    {
        try {
            $admin = Auth::guard('admin')->user();
            
            $validated = $request->validate([
                'current_password' => 'required',
                'new_password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
                ],
                'new_password_confirmation' => 'required',
            ], [
                'current_password.required' => 'Current password is required.',
                'new_password.required' => 'New password is required.',
                'new_password.min' => 'New password must be at least 8 characters long.',
                'new_password.confirmed' => 'New password confirmation does not match.',
                'new_password.regex' => 'New password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
                'new_password_confirmation.required' => 'Please confirm your new password.',
            ]);

            // Verify current password
            if (!Hash::check($validated['current_password'], $admin->password)) {
                return redirect()->back()
                    ->withErrors(['current_password' => 'Current password is incorrect.'])
                    ->withInput();
            }

            // Check if new password is different from current password
            if (Hash::check($validated['new_password'], $admin->password)) {
                return redirect()->back()
                    ->withErrors(['new_password' => 'New password must be different from your current password.'])
                    ->withInput();
            }

            // Update password
            $admin->update([
                'password' => Hash::make($validated['new_password']),
            ]);

            // Log the password update
            \Log::info('Admin password updated: ' . $admin->email);

            return redirect()->back()->with('password_success', 'Password updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Error updating admin password: ' . $e->getMessage());
            return redirect()->back()->with('password_error', 'An error occurred while updating your password.');
        }
    }
}