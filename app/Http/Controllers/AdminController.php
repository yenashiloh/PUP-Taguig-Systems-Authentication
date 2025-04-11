<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

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
        $users = User::where('role', 'Faculty')->get();

        return view('admin.dashboard.total-faculty', compact('admin', 'users'));
    }

    //Show Total Faculty Details
    public function viewTotalFaculty($userId)
    {
        $admin = Auth::guard('admin')->user();
        // Get the user with the given ID
        $faculty = User::findOrFail($userId);
    
        return view('admin.dashboard.view-total-faculty', compact('admin', 'faculty'));
    }    

     //Show Total Student Details
     public function viewTotalStudent($userId)
     {
         $admin = Auth::guard('admin')->user();
         // Get the user with the given ID
         $student = User::findOrFail($userId);
     
         return view('admin.dashboard.view-total-student', compact('admin', 'student'));
     }    

    //Show Total Student Page
    public function totalStudentPage()
    {
        $admin = Auth::guard('admin')->user();
          
        // Get users with the role "Student"
        $users = User::where('role', 'Student')->get();
  
        return view('admin.dashboard.total-student', compact('admin', 'users'));
    }

    // Admin logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login'); 
    }
}
