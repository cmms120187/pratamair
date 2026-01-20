<?php

use App\Http\Controllers\MachineTypeController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ModelController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\PartController;

// Machine ERP Routes - Group Leader and above
Route::middleware(['auth', 'role:group_leader,coordinator,ast_manager,manager,general_manager'])->group(function () {
    Route::post('machine-erp/synchronize', [\App\Http\Controllers\MachineErpController::class, 'synchronize'])->name('machine-erp.synchronize');
    Route::resource('machine-erp', \App\Http\Controllers\MachineErpController::class);
    
    // Mutasi Routes (full CRUD requires role)
    Route::resource('mutasi', \App\Http\Controllers\MutasiController::class);
    Route::get('mutasi-bulk/scan', [\App\Http\Controllers\MutasiController::class, 'bulkScan'])->name('mutasi.bulk-scan');
    Route::post('mutasi-bulk/scan-machine', [\App\Http\Controllers\MutasiController::class, 'scanMachine'])->name('mutasi.scan-machine');
    Route::post('mutasi-bulk/store', [\App\Http\Controllers\MutasiController::class, 'bulkStore'])->name('mutasi.bulk-store');
    Route::post('mutasi-bulk/store-simple', [\App\Http\Controllers\MutasiController::class, 'bulkStoreSimple'])->name('mutasi.bulk-store-simple');
});

// Part ERP Routes - Coordinator and above
Route::middleware(['auth', 'role:coordinator,ast_manager,manager,general_manager'])->group(function () {
    Route::resource('part-erp', \App\Http\Controllers\PartErpController::class);
});

// Machinary routes
Route::middleware('auth')->group(function () {
    Route::resource('machine-types', MachineTypeController::class);
    Route::post('machine-types/import-from-machine-erp', [MachineTypeController::class, 'importFromMachineErp'])->name('machine-types.import-from-machine-erp');
    Route::post('machine-types/merge-duplicates', [MachineTypeController::class, 'mergeDuplicates'])->name('machine-types.merge-duplicates');
    
    // Maintenance Points Routes (integrated with Machine Types)
    Route::post('machine-types/{machineTypeId}/maintenance-points', [MachineTypeController::class, 'storeMaintenancePoint'])->name('machine-types.maintenance-points.store');
    Route::put('machine-types/maintenance-points/{id}', [MachineTypeController::class, 'updateMaintenancePoint'])->name('machine-types.maintenance-points.update');
    Route::delete('machine-types/maintenance-points/{id}', [MachineTypeController::class, 'destroyMaintenancePoint'])->name('machine-types.maintenance-points.destroy');
    
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
    
    Route::resource('groups', GroupController::class);
    Route::resource('parts', PartController::class);
});

