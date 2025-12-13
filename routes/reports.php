<?php

// Reports routes - Group Leader and above (Shortened: /rpt/...)
Route::middleware(['auth', 'role:group_leader,coordinator,ast_manager,manager,general_manager'])->prefix('rpt')->group(function () {
    Route::get('pareto', [\App\Http\Controllers\ParetoMachineController::class, 'index'])->name('pareto-machine.index');
    Route::get('rca', [\App\Http\Controllers\RootCauseAnalysisController::class, 'index'])->name('root-cause-analysis.index');
    Route::get('mttr', [\App\Http\Controllers\MTTRMTBFController::class, 'index'])->name('mttr_mtbf.index');
    Route::get('summary', [\App\Http\Controllers\SummaryDowntimeController::class, 'index'])->name('summary_downtime.index');
    Route::get('mechanic', [\App\Http\Controllers\MechanicPerformanceController::class, 'index'])->name('mechanic_performance.index');
    Route::get('oee', [\App\Http\Controllers\OeeController::class, 'index'])->name('oee.index');
});

