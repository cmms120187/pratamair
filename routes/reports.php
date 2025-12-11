<?php

// Reports routes - Group Leader and above
Route::middleware(['auth', 'role:group_leader,coordinator,ast_manager,manager,general_manager'])->group(function () {
    Route::get('pareto-machine', [\App\Http\Controllers\ParetoMachineController::class, 'index'])->name('pareto-machine.index');
    Route::get('root-cause-analysis', [\App\Http\Controllers\RootCauseAnalysisController::class, 'index'])->name('root-cause-analysis.index');
    Route::get('mttr-mtbf', [\App\Http\Controllers\MTTRMTBFController::class, 'index'])->name('mttr_mtbf.index');
    Route::get('summary-downtime', [\App\Http\Controllers\SummaryDowntimeController::class, 'index'])->name('summary_downtime.index');
    Route::get('mechanic-performance', [\App\Http\Controllers\MechanicPerformanceController::class, 'index'])->name('mechanic_performance.index');
});

