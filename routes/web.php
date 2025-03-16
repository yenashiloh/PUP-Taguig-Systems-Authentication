<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [PublicController::class, 'showLoginPage'])->name('login');