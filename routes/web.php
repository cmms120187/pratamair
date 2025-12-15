<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;

// Default Route
Route::get('/', function () {
    return view('welcome');
});

// Dashboard Route with Middleware
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// Profile Routes with Middleware
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Include modular route files
    require __DIR__.'/location.php';
    require __DIR__.'/machinary.php';
    require __DIR__.'/downtime.php';
    require __DIR__.'/production.php';
    require __DIR__.'/users.php';
    require __DIR__.'/preventive-maintenance.php';
    require __DIR__.'/predictive-maintenance.php';
    require __DIR__.'/reports.php';
    require __DIR__.'/standards.php';
    require __DIR__.'/admin.php';
});

// Contact Form Route
Route::post('/contact', [\App\Http\Controllers\ContactController::class, 'send'])->name('contact.send');

// Authentication Routes
require __DIR__.'/auth.php';
