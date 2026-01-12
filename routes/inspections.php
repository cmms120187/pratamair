<?php

use App\Http\Controllers\InspectionController;
use App\Http\Controllers\InspectionTemplateController;
use App\Http\Controllers\Inspection\SchedulingController;
use App\Http\Controllers\Inspection\UpdatingController;
use App\Http\Controllers\Inspection\ReportingController;

// Inspection Routes
Route::middleware('auth')->group(function () {
    // Inspection Templates (Point Inspeksi)
    Route::get('inspection-templates/get-by-machine-type', [InspectionController::class, 'getTemplateByMachineType'])->name('inspections.get-template-by-machine-type');
    Route::resource('inspection-templates', InspectionTemplateController::class);
    
    // Inspection Scheduling
    Route::prefix('inspections')->name('inspections.')->group(function () {
        Route::resource('scheduling', SchedulingController::class);
        Route::post('scheduling/update-pic', [SchedulingController::class, 'updatePic'])->name('scheduling.update-pic');
        Route::resource('updating', UpdatingController::class)->only(['index', 'create', 'store', 'edit', 'update']);
        Route::get('updating/create/{scheduleId}', [UpdatingController::class, 'create'])->name('updating.create');
        Route::resource('reporting', ReportingController::class)->only(['index', 'show']);
    });
    
    // Inspection Executions (legacy - keep for backward compatibility)
    Route::resource('inspections', InspectionController::class);
});
