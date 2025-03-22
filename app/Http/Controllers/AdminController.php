<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    // Admin dashboard page
    public function dashboardPage()
    {
        return view('admin.dashboard');
    }
}
