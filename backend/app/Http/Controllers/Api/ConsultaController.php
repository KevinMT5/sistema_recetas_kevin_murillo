<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Consulta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsultaController extends Controller
{
    public function store(Request $request, Cita $cita)
    {
        $user = $request->user();
        if ($user->rol === 'medico') abort_unless($cita->medico_id === optional($user->medico)->id, 403);

        $data = $request->validate([
            'motivo_consulta' => ['nullable', 'string'],
            'diagnostico' => ['nullable', 'string'],
            'indicaciones' => ['nullable', 'string'],
            'signos_vitales' => ['nullable', 'array'],
        ]);

        $consulta = DB::transaction(function () use ($cita, $data) {
            $consulta = Consulta::updateOrCreate(['cita_id' => $cita->id], array_merge($data, [
                'paciente_id' => $cita->paciente_id,
                'medico_id' => $cita->medico_id,
                'fecha_atencion' => now(),
            ]));
            $cita->update(['estado' => !empty($data['diagnostico']) ? 'finalizada' : 'en_consulta']);
            return $consulta;
        });

        return response()->json($consulta->load(['cita', 'paciente', 'medico']), 201);
    }
}
