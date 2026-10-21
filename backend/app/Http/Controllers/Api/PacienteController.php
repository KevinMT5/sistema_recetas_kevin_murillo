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

    public function store(Request $request)
    {
        return response()->json(Paciente::create($this->validateData($request)), 201);
    }

    public function show(Paciente $paciente)
    {
        return response()->json($paciente);
    }

    public function update(Request $request, Paciente $paciente)
    {
        $paciente->update($this->validateData($request));
        return response()->json($paciente->fresh());
    }

    public function destroy(Paciente $paciente)
    {
        if ($paciente->recetas()->exists()) {
            return response()->json(['message' => 'No se puede eliminar un paciente que tiene recetas.'], 409);
        }

        $paciente->delete();
        return response()->json(['message' => 'Paciente eliminado.']);
    }

    private function validateData(Request $request)
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:today'],
            'telefono' => ['nullable', 'string', 'max:25'],
            'direccion' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
