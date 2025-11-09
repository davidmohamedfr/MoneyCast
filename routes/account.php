<?php

use App\Http\Controllers\Account\AccountController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('accounts', AccountController::class);
});
