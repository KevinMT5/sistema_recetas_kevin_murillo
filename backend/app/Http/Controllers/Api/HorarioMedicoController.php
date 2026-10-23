<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HorarioMedico;
use Illuminate\Http\Request;

class HorarioMedicoController extends Controller
{
    public function index(Request $request) { return response()->json(HorarioMedico::with(['medico', 'establecimiento'])->when($request->medico_id, fn ($q, $id) => $q->where('medico_id', $id))->orderBy('dia_semana')->orderBy('hora_inicio')->get()); }
    public function store(Request $request) { return response()->json(HorarioMedico::create($this->data($request))->load(['medico', 'establecimiento']), 201); }
    public function show(HorarioMedico $horario) { return response()->json($horario->load(['medico', 'establecimiento'])); }
    public function update(Request $request, HorarioMedico $horario) { $horario->update($this->data($request)); return response()->json($horario->fresh()->load(['medico', 'establecimiento'])); }
    public function destroy(HorarioMedico $horario) { $horario->delete(); return response()->json(['message' => 'Horario eliminado.']); }
    private function data(Request $request) { return $request->validate(['medico_id' => ['required', 'integer', 'exists:medicos,id'], 'establecimiento_id' => ['required', 'integer', 'exists:establecimientos,id'], 'dia_semana' => ['required', 'integer', 'between:1,7'], 'hora_inicio' => ['required', 'date_format:H:i'], 'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'], 'intervalo_minutos' => ['required', 'integer', 'min:10', 'max:240'], 'activo' => ['sometimes', 'boolean']]); }
}
