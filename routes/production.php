<?php

// Production routes
Route::middleware('auth')->group(function () {
    Route::get('production-hourly/create-bulk', [\App\Http\Controllers\ProductionHourlyController::class, 'createBulk'])->name('production-hourly.create-bulk');
    Route::post('production-hourly/bulk-fill-target', [\App\Http\Controllers\ProductionHourlyController::class, 'bulkFillTarget'])->name('production-hourly.bulk-fill-target');
    Route::get('production-hourly/show/{lineId}/{processId}/{date}', [\App\Http\Controllers\ProductionHourlyController::class, 'show'])->name('production-hourly.show-detail');
    Route::resource('production-hourly', \App\Http\Controllers\ProductionHourlyController::class);
    
    // Production Daily routes
    Route::get('production-daily/get-processes-by-plant', [\App\Http\Controllers\ProductionDailyController::class, 'getProcessesByPlant'])->name('production-daily.get-processes-by-plant');
    Route::get('production-daily/get-lines-by-plant-and-process', [\App\Http\Controllers\ProductionDailyController::class, 'getLinesByPlantAndProcess'])->name('production-daily.get-lines-by-plant-and-process');
    Route::get('production-daily/get-rooms-by-plant-process-and-line', [\App\Http\Controllers\ProductionDailyController::class, 'getRoomsByPlantProcessAndLine'])->name('production-daily.get-rooms-by-plant-process-and-line');
    Route::resource('production-daily', \App\Http\Controllers\ProductionDailyController::class);
});

