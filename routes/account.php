<?php

use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Account\ArchiveAccountController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('accounts', AccountController::class);
    Route::post('accounts/{account}/archive', ArchiveAccountController::class)->name('accounts.archive');
});
