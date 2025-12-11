<?php

// Standards CRUD - Group Leader and above
Route::middleware(['auth', 'role:group_leader,coordinator,ast_manager,manager,general_manager'])->group(function () {
    Route::resource('standards', \App\Http\Controllers\StandardController::class);
});

