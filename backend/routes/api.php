<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EstablecimientoController;
use App\Http\Controllers\Api\MedicoController;
use App\Http\Controllers\Api\PacienteController;
use App\Http\Controllers\Api\MedicamentoController;
use App\Http\Controllers\Api\RecetaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CitaController;
use App\Http\Controllers\Api\ConsultaController;
use App\Http\Controllers\Api\ConsultorioController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EspecialidadController;
use App\Http\Controllers\Api\HorarioMedicoController;
use App\Http\Controllers\Api\UserController;

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', DashboardController::class);
    Route::get('/disponibilidad', [CitaController::class, 'disponibilidad']);
    Route::apiResource('citas', CitaController::class);
    Route::post('/citas/{cita}/consulta', [ConsultaController::class, 'store'])->middleware('role:administrador,medico');

    Route::get('/establecimientos', [EstablecimientoController::class, 'index']);
    Route::get('/medicos', [MedicoController::class, 'index']);
    Route::get('/pacientes', [PacienteController::class, 'index'])->middleware('role:administrador,recepcionista,medico');
    Route::get('/medicamentos', [MedicamentoController::class, 'index']);
    Route::get('/especialidades', [EspecialidadController::class, 'index']);
    Route::get('/consultorios', [ConsultorioController::class, 'index']);
    Route::get('/horarios', [HorarioMedicoController::class, 'index']);
    Route::get('/recetas', [RecetaController::class, 'index']);

    Route::middleware('role:administrador,recepcionista')->group(function () {
        Route::post('/pacientes', [PacienteController::class, 'store']);
        Route::get('/pacientes/{paciente}', [PacienteController::class, 'show']);
        Route::match(['put', 'patch'], '/pacientes/{paciente}', [PacienteController::class, 'update']);
    });

    Route::middleware('role:administrador,medico')->group(function () {
        Route::apiResource('recetas', RecetaController::class)->except(['index']);
    });

    Route::middleware('role:administrador')->group(function () {
        Route::apiResource('usuarios', UserController::class)->parameters(['usuarios' => 'user']);
        Route::apiResource('establecimientos', EstablecimientoController::class)->except(['index']);
        Route::apiResource('medicos', MedicoController::class)->except(['index']);
        Route::apiResource('medicamentos', MedicamentoController::class)->except(['index']);
        Route::apiResource('especialidades', EspecialidadController::class)->parameters(['especialidades' => 'especialidad'])->except(['index']);
        Route::apiResource('consultorios', ConsultorioController::class)->except(['index']);
        Route::apiResource('horarios', HorarioMedicoController::class)->except(['index']);
        Route::delete('/pacientes/{paciente}', [PacienteController::class, 'destroy']);
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', fn (Request $request) => $request->user()->load(['paciente', 'medico']));
});
