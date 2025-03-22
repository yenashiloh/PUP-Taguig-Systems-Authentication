<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\AdminController;

// Show the home page
Route::get('/', function () {
    return view('welcome');
});

// Show the login page
Route::get('/login', [PublicController::class, 'showLoginPage'])->name('login');
// Show the sign-up page
Route::get('/sign-up', [PublicController::class, 'showSignUpPage'])->name('sign-up');
// Store the user data
Route::post('/register', [PublicController::class, 'store']);

// Admin dashboard page
Route::get('/dashboard', [AdminController::class, 'dashboardPage'])->name('admin.dashboard');