<?php

// Production routes
Route::middleware('auth')->group(function () {
    Route::get('production-hourly/create-bulk', [\App\Http\Controllers\ProductionHourlyController::class, 'createBulk'])->name('production-hourly.create-bulk');
    Route::post('production-hourly/bulk-fill-target', [\App\Http\Controllers\ProductionHourlyController::class, 'bulkFillTarget'])->name('production-hourly.bulk-fill-target');
    Route::get('production-hourly/show/{lineId}/{processId}/{date}', [\App\Http\Controllers\ProductionHourlyController::class, 'show'])->name('production-hourly.show-detail');
    Route::resource('production-hourly', \App\Http\Controllers\ProductionHourlyController::class);
});

