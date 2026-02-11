<?php

use App\Http\Controllers\DowntimeController;
use App\Http\Controllers\ProblemController;
use App\Http\Controllers\ProblemMmController;
use App\Http\Controllers\ReasonController;
use App\Http\Controllers\ActionController;
use App\Http\Controllers\DowntimeErpController;

// Downtime routes
Route::middleware('auth')->group(function () {
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
    Route::post('problems/preview-from-downtime', [ProblemController::class, 'previewFromDowntime'])->name('problems.preview-from-downtime');
    Route::post('problems/extract-from-downtime', [ProblemController::class, 'extractFromDowntime'])->name('problems.extract-from-downtime');
    Route::resource('problems', ProblemController::class);
    Route::resource('problem-mms', ProblemMmController::class);
    Route::post('reasons/preview-from-downtime', [ReasonController::class, 'previewFromDowntime'])->name('reasons.preview-from-downtime');
    Route::post('reasons/extract-from-downtime', [ReasonController::class, 'extractFromDowntime'])->name('reasons.extract-from-downtime');
    Route::resource('reasons', ReasonController::class);
    Route::post('actions/preview-from-downtime', [ActionController::class, 'previewFromDowntime'])->name('actions.preview-from-downtime');
    Route::post('actions/extract-from-downtime', [ActionController::class, 'extractFromDowntime'])->name('actions.extract-from-downtime');
    Route::resource('actions', ActionController::class);
    
    // Downtime ERP2 Routes - Custom routes must be BEFORE resource route to avoid conflicts
    Route::get('downtime-erp2/download', [\App\Http\Controllers\DowntimeErp2Controller::class, 'download'])->name('downtime-erp2.download')->middleware('role:admin');
    Route::post('downtime-erp2/upload', [\App\Http\Controllers\DowntimeErp2Controller::class, 'upload'])->name('downtime-erp2.upload')->middleware('role:admin');
    
    // Mutasi routes for downtime-erp2 form (accessible by all authenticated users)
    Route::get('mutasi/get-rooms', [\App\Http\Controllers\MutasiController::class, 'getRooms'])->name('mutasi.get-rooms');
    Route::post('mutasi/store-from-downtime', [\App\Http\Controllers\MutasiController::class, 'storeFromDowntime'])->name('mutasi.store-from-downtime');
    
    Route::resource('downtime-erp2', \App\Http\Controllers\DowntimeErp2Controller::class);
    
    // Work Orders - Team Leader and above
    Route::middleware('role:team_leader,group_leader,coordinator,ast_manager,manager,general_manager')->group(function () {
        Route::resource('work-orders', \App\Http\Controllers\WorkOrderController::class);
    });
});

// Downtime ERP Routes (outside auth middleware - check if this is intentional)
Route::resource('downtime_erp', DowntimeErpController::class);
Route::post('downtime_erp/import', [DowntimeErpController::class, 'import'])->name('downtime_erp.import');

