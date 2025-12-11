<?php

use App\Http\Controllers\UserController;

// Users routes
Route::middleware('auth')->group(function () {
    // Activity Routes
    // IMPORTANT: Custom routes must be defined BEFORE resource route to avoid route conflicts
    Route::get('activities/search-mechanic', [\App\Http\Controllers\ActivityController::class, 'searchMechanic'])->name('activities.search-mechanic');
    Route::post('activities/batch-update-location', [\App\Http\Controllers\ActivityController::class, 'batchUpdateLocation'])->name('activities.batch-update-location')->middleware('role:admin');
    Route::get('activities/download', [\App\Http\Controllers\ActivityController::class, 'download'])->name('activities.download')->middleware('role:admin');
    Route::post('activities/upload', [\App\Http\Controllers\ActivityController::class, 'upload'])->name('activities.upload')->middleware('role:admin');
    Route::resource('activities', \App\Http\Controllers\ActivityController::class);
    
    // Custom routes for users (must be BEFORE resource route) - Coordinator and above
    Route::middleware('role:coordinator,ast_manager,manager,general_manager')->group(function () {
        Route::post('users/batch-update', [UserController::class, 'batchUpdate'])->name('users.batch-update');
        Route::get('users/organizational-structure', [\App\Http\Controllers\OrganizationalStructureController::class, 'index'])->name('users.organizational-structure.index');
        Route::get('users/organizational-structure/chart', [\App\Http\Controllers\OrganizationalStructureController::class, 'chart'])->name('users.organizational-structure.chart');
        Route::resource('users', UserController::class);
    });
    
    // Permissions Management - Admin only
    Route::middleware('role:admin')->group(function () {
        Route::get('permissions', [\App\Http\Controllers\PermissionController::class, 'index'])->name('permissions.index');
        Route::put('permissions', [\App\Http\Controllers\PermissionController::class, 'update'])->name('permissions.update');
    });
});

