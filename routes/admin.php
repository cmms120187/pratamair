<?php

// Upload & Download Routes - Admin only
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Room ERP Upload/Download
    Route::post('room-erp/upload', [\App\Http\Controllers\RoomErpController::class, 'upload'])->name('room-erp.upload');
    Route::get('room-erp/download', [\App\Http\Controllers\RoomErpController::class, 'download'])->name('room-erp.download');
    
    // Machine ERP Upload/Download
    Route::post('machine-erp/upload', [\App\Http\Controllers\MachineErpController::class, 'upload'])->name('machine-erp.upload');
    Route::get('machine-erp/download', [\App\Http\Controllers\MachineErpController::class, 'download'])->name('machine-erp.download');
    
    // Part ERP Upload/Download - Moved to routes/machinary.php to avoid route conflicts with resource
    
    // Downtime ERP2 Upload/Download - Moved to routes/downtime.php to avoid route conflicts
});

