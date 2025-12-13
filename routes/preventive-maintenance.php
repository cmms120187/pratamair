<?php

use App\Http\Controllers\PreventiveMaintenance\SchedulingController;
use App\Http\Controllers\PreventiveMaintenance\ControllingController;
use App\Http\Controllers\PreventiveMaintenance\MonitoringController;
use App\Http\Controllers\PreventiveMaintenance\UpdatingController;
use App\Http\Controllers\PreventiveMaintenance\ReportingController;

// Preventive Maintenance Routes (Shortened URL: /pm/...)
Route::middleware('auth')->prefix('pm')->name('preventive-maintenance.')->group(function () {
    // Scheduling - Custom routes must be defined BEFORE resource routes
    Route::get('scheduling/get-machines-by-type', [SchedulingController::class, 'getMachinesByType'])->name('scheduling.get-machines-by-type');
    Route::get('scheduling/get-maintenance-points-by-category', [SchedulingController::class, 'getMaintenancePointsByCategory'])->name('scheduling.get-maintenance-points-by-category');
    Route::get('scheduling/get-maintenance-point-by-category', [SchedulingController::class, 'getMaintenancePointByCategory'])->name('scheduling.get-maintenance-point-by-category');
    Route::delete('scheduling/delete-by-machine/{machineId}', [SchedulingController::class, 'deleteByMachine'])->name('scheduling.delete-by-machine');
    Route::post('scheduling/batch-update-status', [SchedulingController::class, 'batchUpdateStatus'])->name('scheduling.batch-update-status');
    Route::post('scheduling/reschedule', [SchedulingController::class, 'reschedule'])->name('scheduling.reschedule');
    Route::post('scheduling/update-pic', [SchedulingController::class, 'updatePic'])->name('scheduling.update-pic');
    Route::resource('scheduling', SchedulingController::class);
    
    // Controlling - Custom routes must be defined BEFORE resource routes (Shortened: /pm/ctrl/...)
    Route::get('ctrl/get-machines-by-type', [ControllingController::class, 'getMachinesByType'])->name('controlling.get-machines-by-type');
    Route::get('ctrl/get-maintenance-points-by-machine-and-date', [ControllingController::class, 'getMaintenancePointsByMachineAndDate'])->name('controlling.get-maintenance-points-by-machine-and-date');
    Route::post('ctrl/batch-update-status', [ControllingController::class, 'batchUpdateStatus'])->name('controlling.batch-update-status');
    Route::resource('ctrl', ControllingController::class);
    
    // Monitoring
    Route::get('monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    
    // Updating
    Route::get('updating/get-maintenance-points-by-machine-and-date', [UpdatingController::class, 'getMaintenancePointsByMachineAndDate'])->name('updating.get-maintenance-points-by-machine-and-date');
    Route::get('updating', [UpdatingController::class, 'index'])->name('updating.index');
    Route::get('updating/{id}/edit', [UpdatingController::class, 'edit'])->name('updating.edit');
    Route::put('updating/{id}', [UpdatingController::class, 'update'])->name('updating.update');
    
    // Reporting
    Route::get('reporting/get-schedule-points-by-machine-and-date', [ReportingController::class, 'getSchedulePointsByMachineAndDate'])->name('reporting.get-schedule-points-by-machine-and-date');
    Route::get('reporting', [ReportingController::class, 'index'])->name('reporting.index');
    Route::get('reporting/schedule', [ReportingController::class, 'scheduleReport'])->name('reporting.schedule');
    Route::get('reporting/execution', [ReportingController::class, 'executionReport'])->name('reporting.execution');
    Route::get('reporting/performance', [ReportingController::class, 'performanceReport'])->name('reporting.performance');
});

