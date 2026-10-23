<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Consultorio;
use Illuminate\Http\Request;

class ConsultorioController extends Controller
{
    public function index(Request $request) { return response()->json(Consultorio::with('establecimiento')->when($request->establecimiento_id, fn ($q, $id) => $q->where('establecimiento_id', $id))->orderBy('nombre')->get()); }
    public function store(Request $request) { return response()->json(Consultorio::create($this->data($request))->load('establecimiento'), 201); }
    public function show(Consultorio $consultorio) { return response()->json($consultorio->load('establecimiento')); }
    public function update(Request $request, Consultorio $consultorio) { $consultorio->update($this->data($request)); return response()->json($consultorio->fresh()->load('establecimiento')); }
    public function destroy(Consultorio $consultorio) { $consultorio->update(['activo' => false]); return response()->json(['message' => 'Consultorio desactivado.']); }
    private function data(Request $request) { return $request->validate(['establecimiento_id' => ['required', 'integer', 'exists:establecimientos,id'], 'nombre' => ['required', 'string', 'max:120'], 'activo' => ['sometimes', 'boolean']]); }
}
