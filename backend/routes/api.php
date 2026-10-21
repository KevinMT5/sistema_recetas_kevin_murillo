<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EstablecimientoController;
use App\Http\Controllers\Api\MedicoController;
use App\Http\Controllers\Api\PacienteController;
use App\Http\Controllers\Api\MedicamentoController;
use App\Http\Controllers\Api\RecetaController;
use App\Http\Controllers\Api\AuthController;

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('establecimientos', EstablecimientoController::class);
    Route::apiResource('medicos', MedicoController::class);
    Route::apiResource('pacientes', PacienteController::class);
    Route::apiResource('medicamentos', MedicamentoController::class);
    Route::apiResource('recetas', RecetaController::class);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
