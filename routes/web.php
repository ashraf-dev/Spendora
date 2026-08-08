<?php

use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('{locale?}', WelcomeController::class)
    ->whereIn('locale', ['en', 'ar'])
    ->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
