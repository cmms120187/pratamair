<?php

// Production routes (Shortened: /prod/...)
Route::middleware('auth')->prefix('prod')->group(function () {
    Route::get('phourly/create-bulk', [\App\Http\Controllers\ProductionHourlyController::class, 'createBulk'])->name('production-hourly.create-bulk');
    Route::post('phourly/bulk-fill-target', [\App\Http\Controllers\ProductionHourlyController::class, 'bulkFillTarget'])->name('production-hourly.bulk-fill-target');
    Route::get('phourly/show/{lineId}/{processId}/{date}', [\App\Http\Controllers\ProductionHourlyController::class, 'show'])->name('production-hourly.show-detail');
    Route::resource('phourly', \App\Http\Controllers\ProductionHourlyController::class)->names('production-hourly');
    
    // Production Daily routes
    Route::get('pdaily/get-processes-by-plant', [\App\Http\Controllers\ProductionDailyController::class, 'getProcessesByPlant'])->name('production-daily.get-processes-by-plant');
    Route::get('pdaily/get-lines-by-plant-and-process', [\App\Http\Controllers\ProductionDailyController::class, 'getLinesByPlantAndProcess'])->name('production-daily.get-lines-by-plant-and-process');
    Route::get('pdaily/get-rooms-by-plant-process-and-line', [\App\Http\Controllers\ProductionDailyController::class, 'getRoomsByPlantProcessAndLine'])->name('production-daily.get-rooms-by-plant-process-and-line');
    Route::resource('pdaily', \App\Http\Controllers\ProductionDailyController::class)->names('production-daily');
});

