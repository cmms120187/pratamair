<?php

use App\Http\Controllers\PredictiveMaintenance\SchedulingController as PredictiveSchedulingController;
use App\Http\Controllers\PredictiveMaintenance\ControllingController as PredictiveControllingController;
use App\Http\Controllers\PredictiveMaintenance\MonitoringController as PredictiveMonitoringController;
use App\Http\Controllers\PredictiveMaintenance\UpdatingController as PredictiveUpdatingController;
use App\Http\Controllers\PredictiveMaintenance\ReportingController as PredictiveReportingController;

// Predictive Maintenance Routes (Shortened URL: /pdm/...)
Route::middleware('auth')->prefix('pdm')->name('predictive-maintenance.')->group(function () {
    // Scheduling - Custom routes must be defined BEFORE resource routes
    Route::post('scheduling/update-pic', [PredictiveSchedulingController::class, 'updatePic'])->name('scheduling.update-pic');
    Route::post('scheduling/reschedule', [PredictiveSchedulingController::class, 'reschedule'])->name('scheduling.reschedule');
    Route::resource('scheduling', PredictiveSchedulingController::class);
    
    // Controlling - Custom routes must be defined BEFORE resource routes (Shortened: /pdm/ctrl/...)
    Route::get('ctrl/get-machines-by-type', [PredictiveControllingController::class, 'getMachinesByType'])->name('controlling.get-machines-by-type');
    Route::get('ctrl/get-maintenance-points-by-machine-and-date', [PredictiveControllingController::class, 'getMaintenancePointsByMachineAndDate'])->name('controlling.get-maintenance-points-by-machine-and-date');
    Route::get('ctrl/machine-condition/{machineId}', [PredictiveControllingController::class, 'showMachineCondition'])->name('controlling.machine-condition');
    Route::get('ctrl/export', [PredictiveControllingController::class, 'export'])->name('controlling.export');
    Route::post('ctrl/import', [PredictiveControllingController::class, 'import'])->name('controlling.import');
    Route::resource('ctrl', PredictiveControllingController::class);
    
    // Monitoring
    Route::get('monitoring', [PredictiveMonitoringController::class, 'index'])->name('monitoring.index');
    
    // Updating - Custom routes must be defined BEFORE resource routes
    Route::get('updating/get-maintenance-points-by-machine-and-date', [PredictiveUpdatingController::class, 'getMaintenancePointsByMachineAndDate'])->name('updating.get-maintenance-points-by-machine-and-date');
    Route::get('updating/create-from-schedule', [PredictiveUpdatingController::class, 'createFromSchedule'])->name('updating.create-from-schedule');
    Route::get('updating', [PredictiveUpdatingController::class, 'index'])->name('updating.index');
    Route::get('updating/{id}/edit', [PredictiveUpdatingController::class, 'edit'])->name('updating.edit');
    Route::put('updating/batch-update', [PredictiveUpdatingController::class, 'batchUpdate'])->name('updating.batch-update');
    Route::put('updating/{id}', [PredictiveUpdatingController::class, 'update'])->name('updating.update');
    
    // Reporting
    Route::get('reporting/get-schedule-points-by-machine-and-date', [PredictiveReportingController::class, 'getSchedulePointsByMachineAndDate'])->name('reporting.get-schedule-points-by-machine-and-date');
    Route::get('reporting/get-point-trends-by-machine', [PredictiveReportingController::class, 'getPointTrendsByMachine'])->name('reporting.get-point-trends-by-machine');
    Route::get('reporting', [PredictiveReportingController::class, 'index'])->name('reporting.index');
    Route::get('reporting/schedule', [PredictiveReportingController::class, 'scheduleReport'])->name('reporting.schedule');
    Route::get('reporting/execution', [PredictiveReportingController::class, 'executionReport'])->name('reporting.execution');
    Route::get('reporting/performance', [PredictiveReportingController::class, 'performanceReport'])->name('reporting.performance');
});

