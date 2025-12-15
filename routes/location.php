<?php

use App\Http\Controllers\PlantController;
use App\Http\Controllers\ProcessController;
use App\Http\Controllers\LineController;
use App\Http\Controllers\RoomController;

// Location routes - Coordinator and above
Route::middleware(['auth', 'role:coordinator,ast_manager,manager,general_manager'])->group(function () {
    Route::resource('plants', PlantController::class);
    Route::post('plants/import-from-room-erp', [PlantController::class, 'importFromRoomErp'])->name('plants.import-from-room-erp');
    
    Route::resource('processes', ProcessController::class);
    Route::post('processes/import-from-room-erp', [ProcessController::class, 'importFromRoomErp'])->name('processes.import-from-room-erp');
    
    Route::resource('lines', LineController::class);
    Route::post('lines/import-from-room-erp', [LineController::class, 'importFromRoomErp'])->name('lines.import-from-room-erp');
    
    // Room ERP Routes
    Route::resource('room-erp', \App\Http\Controllers\RoomErpController::class);
    
    // Custom routes for rooms (must be BEFORE resource route)
    Route::get('rooms/get-lines-by-plant', [RoomController::class, 'getLinesByPlant'])->name('rooms.get-lines-by-plant');
    Route::post('rooms/import-from-room-erp', [RoomController::class, 'importFromRoomErp'])->name('rooms.import-from-room-erp');
    Route::post('rooms/synchronize', [RoomController::class, 'synchronize'])->name('rooms.synchronize');
    Route::resource('rooms', RoomController::class);
});

