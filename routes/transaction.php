<?php

use App\Http\Controllers\Transaction\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    // Read-only operations (no rate limiting needed)
    Route::resource('transactions', TransactionController::class)->only(['index', 'create', 'edit']);

    // Apply rate limiting to financial write operations (store, update, delete)
    Route::middleware('throttle:financial')->group(function () {
        Route::resource('transactions', TransactionController::class)->only(['store', 'update', 'destroy']);
    });
});
