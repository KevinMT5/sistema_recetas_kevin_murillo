<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medico;
use Illuminate\Http\Request;

class MedicoController extends Controller
{
    /**
     * Devuelve todos los médicos.
     */
    public function index()
    {
        $medicos = Medico::with('establecimiento')->get(); // carga el establecimiento
        return response()->json($medicos);
    }
    

    // Los otros métodos puedes dejarlos por ahora como están
}
