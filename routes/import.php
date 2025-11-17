<?php

use App\Http\Controllers\Import\ImportController;
use App\Http\Controllers\Import\ImportProgressController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('imports', ImportController::class)->only(['index', 'show', 'create']);
    Route::get('imports/{import}/progress', [ImportProgressController::class, 'stream'])->name('imports.progress');

    Route::middleware('throttle:financial')->group(function () {
        Route::resource('imports', ImportController::class)->only(['store', 'update', 'destroy']);
    });
});
