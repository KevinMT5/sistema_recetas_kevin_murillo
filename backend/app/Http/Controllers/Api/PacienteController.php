<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use Illuminate\Http\Request;

class PacienteController extends Controller
{
    /**
     * Devuelve todos los pacientes.
     */
    public function index()
    {
        $pacientes = Paciente::all(); // Trae todos los registros
        return response()->json($pacientes);
    }

    // Los otros métodos puedes dejarlos vacíos por ahora
}
