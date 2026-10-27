<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CitaController;

Route::post('/registro', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/perfil', [AuthController::class, 'perfil']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/citas', [CitaController::class, 'apiIndex']);
    Route::post('/citas', [CitaController::class, 'apiStore']);
    Route::put('/citas/{cita}', [CitaController::class, 'apiUpdate']);
    Route::delete('/citas/{cita}', [CitaController::class, 'apiDestroy']);
});
