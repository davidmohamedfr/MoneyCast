<?php

use App\Http\Controllers\Dev\MagicLinkController;
use Illuminate\Support\Facades\Route;

Route::middleware(['dev.only'])->prefix('dev')->group(function () {
    Route::get('/magic-link/{email}', [MagicLinkController::class, 'generate'])
        ->name('dev.magic-link.generate');

    Route::get('/auth/magic/{token}', [MagicLinkController::class, 'authenticate'])
        ->name('dev.magic-link.authenticate');
});
