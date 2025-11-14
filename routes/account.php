<?php

use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Account\ArchiveAccountController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    // Read-only operations (no rate limiting needed)
    Route::resource('accounts', AccountController::class)->only(['index', 'show', 'create', 'edit']);

    // Apply rate limiting to financial write operations (store, update, delete, archive)
    Route::middleware('throttle:financial')->group(function () {
        Route::resource('accounts', AccountController::class)->only(['store', 'update', 'destroy']);
        Route::post('accounts/{account}/archive', ArchiveAccountController::class)->name('accounts.archive');
    });
});
