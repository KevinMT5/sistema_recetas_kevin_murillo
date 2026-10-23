<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Especialidad;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EspecialidadController extends Controller
{
    public function index() { return response()->json(Especialidad::orderBy('nombre')->get()); }
    public function store(Request $request) { return response()->json(Especialidad::create($this->data($request)), 201); }
    public function show(Especialidad $especialidad) { return response()->json($especialidad); }
    public function update(Request $request, Especialidad $especialidad) { $especialidad->update($this->data($request, $especialidad)); return response()->json($especialidad->fresh()); }
    public function destroy(Especialidad $especialidad) { $especialidad->update(['activo' => false]); return response()->json(['message' => 'Especialidad desactivada.']); }
    private function data(Request $request, ?Especialidad $especialidad = null) { return $request->validate(['nombre' => ['required', 'string', 'max:120', Rule::unique('especialidades')->ignore($especialidad?->id)], 'duracion_cita' => ['required', 'integer', 'min:10', 'max:240'], 'activo' => ['sometimes', 'boolean']]); }
}
