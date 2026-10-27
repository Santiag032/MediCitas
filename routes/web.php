<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CitaController;

Route::get('/', function () {
    return redirect()->route('citas.index');
});

Route::get('/citas', [CitaController::class, 'index'])
    ->name('citas.index');

Route::get('/citas/crear', [CitaController::class, 'create'])
    ->middleware('auth')
    ->name('citas.create');

Route::post('/citas', [CitaController::class, 'store'])
    ->middleware('auth')
    ->name('citas.store');
