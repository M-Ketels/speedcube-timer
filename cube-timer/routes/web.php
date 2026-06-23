<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SolveController;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::get('/solves', [SolveController::class, 'index'])->name('solvesIndex');
    Route::get('/solves/create', [SolveController::class, 'create'])->name('solvesCreate');
    Route::post('/solves', [SolveController::class, 'store'])->name('solvesStore');
    Route::get('/timer', [SolveController::class, 'timer'])->name('solvesTimer');
});

require __DIR__.'/settings.php';
