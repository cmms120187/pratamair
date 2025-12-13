<?php

// Standards CRUD - Group Leader and above (Shortened: /std/...)
Route::middleware(['auth', 'role:group_leader,coordinator,ast_manager,manager,general_manager'])->prefix('std')->group(function () {
    Route::resource('standards', \App\Http\Controllers\StandardController::class);
});

