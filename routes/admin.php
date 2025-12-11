<?php

// Upload & Download Routes - Admin only
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Room ERP Upload/Download
    Route::post('room-erp/upload', [\App\Http\Controllers\RoomErpController::class, 'upload'])->name('room-erp.upload');
    Route::get('room-erp/download', [\App\Http\Controllers\RoomErpController::class, 'download'])->name('room-erp.download');
    
    // Machine ERP Upload/Download
    Route::post('machine-erp/upload', [\App\Http\Controllers\MachineErpController::class, 'upload'])->name('machine-erp.upload');
    Route::get('machine-erp/download', [\App\Http\Controllers\MachineErpController::class, 'download'])->name('machine-erp.download');
    
    // Part ERP Upload/Download
    Route::post('part-erp/upload', [\App\Http\Controllers\PartErpController::class, 'upload'])->name('part-erp.upload');
    Route::get('part-erp/download', [\App\Http\Controllers\PartErpController::class, 'download'])->name('part-erp.download');
    
    // Downtime ERP2 Upload/Download
    Route::post('downtime-erp2/upload', [\App\Http\Controllers\DowntimeErp2Controller::class, 'upload'])->name('downtime-erp2.upload');
    Route::get('downtime-erp2/download', [\App\Http\Controllers\DowntimeErp2Controller::class, 'download'])->name('downtime-erp2.download');
});

