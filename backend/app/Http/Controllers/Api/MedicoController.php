<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medico;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        return response()->json(Medico::create($data)->load('establecimiento'), 201);
    }

    public function show(Medico $medico)
    {
        return response()->json($medico->load('establecimiento'));
    }

    public function update(Request $request, Medico $medico)
    {
        $medico->update($this->validateData($request, $medico->id));
        return response()->json($medico->fresh()->load('establecimiento'));
    }

    public function destroy(Medico $medico)
    {
        if ($medico->recetas()->exists()) {
            return response()->json(['message' => 'No se puede eliminar un médico que tiene recetas.'], 409);
        }

        $medico->delete();
        return response()->json(['message' => 'Médico eliminado.']);
    }

    private function validateData(Request $request, $id = null)
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'numero_jv' => ['required', 'string', 'max:255', Rule::unique('medicos')->ignore($id)],
            'especialidad' => ['nullable', 'string', 'max:255'],
            'establecimiento_id' => ['required', 'integer', 'exists:establecimientos,id'],
        ]);
    }
}
