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

    public function store(Request $request)
    {
        return response()->json(Medicamento::create($this->validateData($request)), 201);
    }

    public function show(Medicamento $medicamento)
    {
        return response()->json($medicamento);
    }

    public function update(Request $request, Medicamento $medicamento)
    {
        $medicamento->update($this->validateData($request));
        return response()->json($medicamento->fresh());
    }

    public function destroy(Medicamento $medicamento)
    {
        if ($medicamento->detallesReceta()->exists()) {
            return response()->json(['message' => 'No se puede eliminar un medicamento incluido en recetas.'], 409);
        }

        $medicamento->delete();
        return response()->json(['message' => 'Medicamento eliminado.']);
    }

    private function validateData(Request $request)
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'presentacion' => ['nullable', 'string', 'max:255'],
            'concentracion' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
