<?php

use App\Domain\Dashboard\Services\DashboardService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function (DashboardService $dashboardService) {
    $data = $dashboardService->getDashboardData(auth()->id());

    return Inertia::render('Dashboard', $data);
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/account.php';
require __DIR__.'/transaction.php';
