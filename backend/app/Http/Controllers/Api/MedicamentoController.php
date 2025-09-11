<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicamento;
use Illuminate\Http\Request;

class MedicamentoController extends Controller
{
    /**
     * Devuelve todos los medicamentos.
     */
    public function index()
    {
        $medicamentos = Medicamento::all(); // Trae todos los registros
        return response()->json($medicamentos);
    }

    // Los demás métodos los puedes dejar vacíos por ahora
}
