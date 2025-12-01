<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DowntimeErpController;
use App\Http\Controllers\PlantController;
use App\Http\Controllers\ProcessController;
use App\Http\Controllers\LineController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\MachineTypeController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ModelController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DowntimeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProblemController;
use App\Http\Controllers\ProblemMmController;
use App\Http\Controllers\ReasonController;
use App\Http\Controllers\ActionController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\MaintenancePointController;
use App\Http\Controllers\PreventiveMaintenance\SchedulingController;
use App\Http\Controllers\PreventiveMaintenance\ControllingController;
use App\Http\Controllers\PreventiveMaintenance\MonitoringController;
use App\Http\Controllers\PreventiveMaintenance\UpdatingController;
use App\Http\Controllers\PreventiveMaintenance\ReportingController;
use App\Http\Controllers\PredictiveMaintenance\SchedulingController as PredictiveSchedulingController;
use App\Http\Controllers\PredictiveMaintenance\ControllingController as PredictiveControllingController;
use App\Http\Controllers\PredictiveMaintenance\MonitoringController as PredictiveMonitoringController;
use App\Http\Controllers\PredictiveMaintenance\UpdatingController as PredictiveUpdatingController;
use App\Http\Controllers\PredictiveMaintenance\ReportingController as PredictiveReportingController;

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

    // Resource Routes for Different Controllers
    // Location routes - Coordinator and above
    Route::middleware('role:coordinator,ast_manager,manager,general_manager')->group(function () {
        Route::resource('plants', PlantController::class);
        Route::post('plants/import-from-room-erp', [PlantController::class, 'importFromRoomErp'])->name('plants.import-from-room-erp');
        Route::resource('processes', ProcessController::class);
        Route::post('processes/import-from-room-erp', [ProcessController::class, 'importFromRoomErp'])->name('processes.import-from-room-erp');
        Route::resource('lines', LineController::class);
        Route::post('lines/import-from-room-erp', [LineController::class, 'importFromRoomErp'])->name('lines.import-from-room-erp');
        // Room ERP Routes
        Route::post('room-erp/upload', [\App\Http\Controllers\RoomErpController::class, 'upload'])->name('room-erp.upload');
        Route::get('room-erp/download', [\App\Http\Controllers\RoomErpController::class, 'download'])->name('room-erp.download');
        Route::resource('room-erp', \App\Http\Controllers\RoomErpController::class);
    });
    
    // Machine ERP Routes - Group Leader and above
    Route::middleware('role:group_leader,coordinator,ast_manager,manager,general_manager')->group(function () {
        Route::post('machine-erp/upload', [\App\Http\Controllers\MachineErpController::class, 'upload'])->name('machine-erp.upload');
        Route::get('machine-erp/download', [\App\Http\Controllers\MachineErpController::class, 'download'])->name('machine-erp.download');
        Route::post('machine-erp/synchronize', [\App\Http\Controllers\MachineErpController::class, 'synchronize'])->name('machine-erp.synchronize');
        Route::resource('machine-erp', \App\Http\Controllers\MachineErpController::class);
        
        // Mutasi Routes
        Route::resource('mutasi', \App\Http\Controllers\MutasiController::class);
    });
    
    // Part ERP Routes - Coordinator and above
    Route::middleware('role:coordinator,ast_manager,manager,general_manager')->group(function () {
        Route::post('part-erp/upload', [\App\Http\Controllers\PartErpController::class, 'upload'])->name('part-erp.upload');
        Route::get('part-erp/download', [\App\Http\Controllers\PartErpController::class, 'download'])->name('part-erp.download');
        Route::resource('part-erp', \App\Http\Controllers\PartErpController::class);
    });
    
    // Downtime ERP2 Routes
    Route::post('downtime-erp2/upload', [\App\Http\Controllers\DowntimeErp2Controller::class, 'upload'])->name('downtime-erp2.upload');
    Route::get('downtime-erp2/download', [\App\Http\Controllers\DowntimeErp2Controller::class, 'download'])->name('downtime-erp2.download');
    Route::resource('downtime-erp2', \App\Http\Controllers\DowntimeErp2Controller::class);
    
    // Activity Routes
    Route::post('activities/upload', [\App\Http\Controllers\ActivityController::class, 'upload'])->name('activities.upload');
    Route::get('activities/download', [\App\Http\Controllers\ActivityController::class, 'download'])->name('activities.download');
    Route::get('activities/search-mechanic', [\App\Http\Controllers\ActivityController::class, 'searchMechanic'])->name('activities.search-mechanic');
    Route::post('activities/batch-update-location', [\App\Http\Controllers\ActivityController::class, 'batchUpdateLocation'])->name('activities.batch-update-location')->middleware('role:admin');
    Route::resource('activities', \App\Http\Controllers\ActivityController::class);
    
    // Custom routes for rooms (must be BEFORE resource route)
    Route::get('rooms/get-lines-by-plant', [RoomController::class, 'getLinesByPlant'])->name('rooms.get-lines-by-plant');
    Route::post('rooms/import-from-room-erp', [RoomController::class, 'importFromRoomErp'])->name('rooms.import-from-room-erp');
    Route::post('rooms/synchronize', [RoomController::class, 'synchronize'])->name('rooms.synchronize');
    Route::resource('rooms', RoomController::class);
    Route::resource('machine-types', MachineTypeController::class);
    Route::post('machine-types/import-from-machine-erp', [MachineTypeController::class, 'importFromMachineErp'])->name('machine-types.import-from-machine-erp');
    Route::post('machine-types/merge-duplicates', [MachineTypeController::class, 'mergeDuplicates'])->name('machine-types.merge-duplicates');
    Route::resource('brands', BrandController::class);
    Route::post('brands/import-from-machine-erp', [BrandController::class, 'importFromMachineErp'])->name('brands.import-from-machine-erp');
    Route::post('brands/merge-duplicates', [BrandController::class, 'mergeDuplicates'])->name('brands.merge-duplicates');
    Route::resource('systems', SystemController::class);
    Route::resource('models', ModelController::class);
    Route::post('models/import-from-machine-erp', [ModelController::class, 'importFromMachineErp'])->name('models.import-from-machine-erp');
    Route::post('models/merge-duplicates', [ModelController::class, 'mergeDuplicates'])->name('models.merge-duplicates');
    // Custom routes for machines (must be BEFORE resource route)
    Route::get('machines/get-brands-by-type', [MachineController::class, 'getBrandsByType'])->name('machines.get-brands-by-type');
    Route::get('machines/get-models-by-type-and-brand', [MachineController::class, 'getModelsByTypeAndBrand'])->name('machines.get-models-by-type-and-brand');
    Route::get('machines/get-lines-by-plant', [MachineController::class, 'getLinesByPlant'])->name('machines.get-lines-by-plant');
    Route::get('machines/get-rooms-by-plant-and-line', [MachineController::class, 'getRoomsByPlantAndLine'])->name('machines.get-rooms-by-plant-and-line');
    Route::resource('machines', MachineController::class);
    // Custom routes for users (must be BEFORE resource route) - Coordinator and above
    Route::middleware('role:coordinator,ast_manager,manager,general_manager')->group(function () {
        Route::post('users/batch-update', [UserController::class, 'batchUpdate'])->name('users.batch-update');
        Route::get('users/organizational-structure', [\App\Http\Controllers\OrganizationalStructureController::class, 'index'])->name('users.organizational-structure.index');
        Route::get('users/organizational-structure/chart', [\App\Http\Controllers\OrganizationalStructureController::class, 'chart'])->name('users.organizational-structure.chart');
        Route::resource('users', UserController::class);
    });
    
    // Work Orders - Team Leader and above
    Route::middleware('role:team_leader,group_leader,coordinator,ast_manager,manager,general_manager')->group(function () {
        Route::resource('work-orders', \App\Http\Controllers\WorkOrderController::class);
    });
    
    // Reports - Group Leader and above
    Route::middleware('role:group_leader,coordinator,ast_manager,manager,general_manager')->group(function () {
        Route::get('pareto-machine', [\App\Http\Controllers\ParetoMachineController::class, 'index'])->name('pareto-machine.index');
        Route::get('root-cause-analysis', [\App\Http\Controllers\RootCauseAnalysisController::class, 'index'])->name('root-cause-analysis.index');
    });
    // Custom routes for downtimes (MUST be BEFORE resource route to avoid route conflicts)
    Route::post('downtimes/search-machine', [DowntimeController::class, 'searchMachine'])->name('downtimes.search-machine');
    Route::get('downtimes/search-mechanic', [DowntimeController::class, 'searchMechanic'])->name('downtimes.search-mechanic');
    Route::get('downtimes/get-parts-by-systems', [DowntimeController::class, 'getPartsBySystems'])->name('downtimes.get-parts-by-systems');
    Route::get('downtimes/get-problems-by-systems', [DowntimeController::class, 'getProblemsBySystems'])->name('downtimes.get-problems-by-systems');
    Route::get('downtimes/get-processes-by-plant', [DowntimeController::class, 'getProcessesByPlant'])->name('downtimes.get-processes-by-plant');
    Route::get('downtimes/get-lines-by-plant-and-process', [DowntimeController::class, 'getLinesByPlantAndProcess'])->name('downtimes.get-lines-by-plant-and-process');
    Route::get('downtimes/get-rooms-by-plant-and-line', [DowntimeController::class, 'getRoomsByPlantAndLine'])->name('downtimes.get-rooms-by-plant-and-line');
    Route::post('downtimes/update-machine-location', [DowntimeController::class, 'updateMachineLocation'])->name('downtimes.update-machine-location');
    Route::resource('downtimes', DowntimeController::class);
    Route::post('downtime_erp/search-machine', [DowntimeErpController::class, 'searchMachine'])->name('downtime_erp.search-machine');
    Route::resource('problems', ProblemController::class);
    Route::resource('problem-mms', ProblemMmController::class);
    Route::resource('reasons', ReasonController::class);
    Route::resource('actions', ActionController::class);
    Route::resource('groups', GroupController::class);
    Route::resource('parts', PartController::class);
    
    // Maintenance Points Routes (integrated with Machine Types)
    Route::post('machine-types/{machineTypeId}/maintenance-points', [MachineTypeController::class, 'storeMaintenancePoint'])->name('machine-types.maintenance-points.store');
    Route::put('machine-types/maintenance-points/{id}', [MachineTypeController::class, 'updateMaintenancePoint'])->name('machine-types.maintenance-points.update');
    Route::delete('machine-types/maintenance-points/{id}', [MachineTypeController::class, 'destroyMaintenancePoint'])->name('machine-types.maintenance-points.destroy');
    
    // Preventive Maintenance Routes
    Route::prefix('preventive-maintenance')->name('preventive-maintenance.')->group(function () {
        // Scheduling - Custom routes must be defined BEFORE resource routes
        Route::get('scheduling/get-machines-by-type', [SchedulingController::class, 'getMachinesByType'])->name('scheduling.get-machines-by-type');
        Route::get('scheduling/get-maintenance-points-by-category', [SchedulingController::class, 'getMaintenancePointsByCategory'])->name('scheduling.get-maintenance-points-by-category');
        Route::get('scheduling/get-maintenance-point-by-category', [SchedulingController::class, 'getMaintenancePointByCategory'])->name('scheduling.get-maintenance-point-by-category');
        Route::delete('scheduling/delete-by-machine/{machineId}', [SchedulingController::class, 'deleteByMachine'])->name('scheduling.delete-by-machine');
        Route::post('scheduling/batch-update-status', [SchedulingController::class, 'batchUpdateStatus'])->name('scheduling.batch-update-status');
        Route::post('scheduling/reschedule', [SchedulingController::class, 'reschedule'])->name('scheduling.reschedule');
        Route::resource('scheduling', SchedulingController::class);
        
        // Controlling - Custom routes must be defined BEFORE resource routes
        Route::get('controlling/get-machines-by-type', [ControllingController::class, 'getMachinesByType'])->name('controlling.get-machines-by-type');
        Route::get('controlling/get-maintenance-points-by-machine-and-date', [ControllingController::class, 'getMaintenancePointsByMachineAndDate'])->name('controlling.get-maintenance-points-by-machine-and-date');
        Route::post('controlling/batch-update-status', [ControllingController::class, 'batchUpdateStatus'])->name('controlling.batch-update-status');
        Route::resource('controlling', ControllingController::class);
        
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
    
    // Predictive Maintenance Routes
    Route::prefix('predictive-maintenance')->name('predictive-maintenance.')->group(function () {
        // Scheduling - Custom routes must be defined BEFORE resource routes
        Route::post('scheduling/update-pic', [PredictiveSchedulingController::class, 'updatePic'])->name('scheduling.update-pic');
        Route::post('scheduling/reschedule', [PredictiveSchedulingController::class, 'reschedule'])->name('scheduling.reschedule');
        Route::resource('scheduling', PredictiveSchedulingController::class);
        
        // Controlling - Custom routes must be defined BEFORE resource routes
        Route::get('controlling/get-machines-by-type', [PredictiveControllingController::class, 'getMachinesByType'])->name('controlling.get-machines-by-type');
        Route::get('controlling/get-maintenance-points-by-machine-and-date', [PredictiveControllingController::class, 'getMaintenancePointsByMachineAndDate'])->name('controlling.get-maintenance-points-by-machine-and-date');
        Route::get('controlling/machine-condition/{machineId}', [PredictiveControllingController::class, 'showMachineCondition'])->name('controlling.machine-condition');
        Route::resource('controlling', PredictiveControllingController::class);
        
        // Monitoring
        Route::get('monitoring', [PredictiveMonitoringController::class, 'index'])->name('monitoring.index');
        
        // Updating - Custom routes must be defined BEFORE resource routes
        Route::get('updating/get-maintenance-points-by-machine-and-date', [PredictiveUpdatingController::class, 'getMaintenancePointsByMachineAndDate'])->name('updating.get-maintenance-points-by-machine-and-date');
        Route::get('updating', [PredictiveUpdatingController::class, 'index'])->name('updating.index');
        Route::get('updating/{id}/edit', [PredictiveUpdatingController::class, 'edit'])->name('updating.edit');
        Route::put('updating/{id}', [PredictiveUpdatingController::class, 'update'])->name('updating.update');
        
        // Reporting
        Route::get('reporting', [PredictiveReportingController::class, 'index'])->name('reporting.index');
        Route::get('reporting/schedule', [PredictiveReportingController::class, 'scheduleReport'])->name('reporting.schedule');
        Route::get('reporting/execution', [PredictiveReportingController::class, 'executionReport'])->name('reporting.execution');
        Route::get('reporting/performance', [PredictiveReportingController::class, 'performanceReport'])->name('reporting.performance');
    });
    
    // Standards CRUD - Group Leader and above
    Route::middleware('role:group_leader,coordinator,ast_manager,manager,general_manager')->group(function () {
        Route::resource('standards', \App\Http\Controllers\StandardController::class);
    });
    
    // Permissions Management - Admin only
    Route::middleware('role:admin')->group(function () {
        Route::get('permissions', [\App\Http\Controllers\PermissionController::class, 'index'])->name('permissions.index');
        Route::put('permissions', [\App\Http\Controllers\PermissionController::class, 'update'])->name('permissions.update');
    });
});

// Downtime ERP Routes
Route::resource('downtime_erp', DowntimeErpController::class);
Route::post('downtime_erp/import', [DowntimeErpController::class, 'import'])->name('downtime_erp.import');

// MTTR & MTBF Routes - Group Leader and above
Route::get('mttr-mtbf', [\App\Http\Controllers\MTTRMTBFController::class, 'index'])
    ->middleware(['auth', 'role:group_leader,coordinator,ast_manager,manager,general_manager'])
    ->name('mttr_mtbf.index');

// Summary Downtime Routes - Group Leader and above
Route::get('summary-downtime', [\App\Http\Controllers\SummaryDowntimeController::class, 'index'])
    ->middleware(['auth', 'role:group_leader,coordinator,ast_manager,manager,general_manager'])
    ->name('summary_downtime.index');

// Mechanic Performance Routes - Group Leader and above
Route::get('mechanic-performance', [\App\Http\Controllers\MechanicPerformanceController::class, 'index'])
    ->middleware(['auth', 'role:group_leader,coordinator,ast_manager,manager,general_manager'])
    ->name('mechanic_performance.index');

// Contact Form Route
Route::post('/contact', [\App\Http\Controllers\ContactController::class, 'send'])->name('contact.send');

// Authentication Routes
require __DIR__.'/auth.php';
