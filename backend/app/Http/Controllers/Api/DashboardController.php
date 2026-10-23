<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Receta;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $citas = Cita::query()->with(['paciente', 'medico.especialidadCatalogo', 'establecimiento'])->orderBy('fecha')->orderBy('hora_inicio');
        if ($request->user()->rol === 'paciente') $citas->where('paciente_id', optional($request->user()->paciente)->id ?? 0);
        if ($request->user()->rol === 'medico') $citas->where('medico_id', optional($request->user()->medico)->id ?? 0);

        $hoy = (clone $citas)->whereDate('fecha', today())->get();
        $proximas = (clone $citas)->whereDate('fecha', '>=', today())->whereNotIn('estado', ['cancelada', 'finalizada', 'no_asistio'])->limit(6)->get();
        $proximas->each(function ($cita) {
            $cita->makeHidden(['notas', 'creada_por', 'created_at', 'updated_at', 'check_in_at']);
            $cita->paciente?->makeHidden(['user_id', 'email', 'documento', 'fecha_nacimiento', 'direccion', 'contacto_emergencia', 'created_at', 'updated_at']);
            $cita->medico?->makeHidden(['user_id', 'numero_jv', 'especialidad_id', 'establecimiento_id', 'created_at', 'updated_at']);
        });
        $recetas = Receta::query();
        if ($request->user()->rol === 'paciente') $recetas->where('paciente_id', optional($request->user()->paciente)->id ?? 0);
        if ($request->user()->rol === 'medico') $recetas->where('medico_id', optional($request->user()->medico)->id ?? 0);

        return response()->json([
            'rol' => $request->user()->rol,
            'metricas' => [
                'citas_hoy' => $hoy->count(),
                'confirmadas' => $hoy->where('estado', 'confirmada')->count(),
                'en_espera' => $hoy->where('estado', 'en_espera')->count(),
                'pacientes' => in_array($request->user()->rol, ['administrador', 'recepcionista'], true) ? Paciente::count() : null,
                'medicos' => $request->user()->rol === 'administrador' ? Medico::count() : null,
                'recetas' => $recetas->count(),
            ],
            'proximas_citas' => $proximas,
        ]);
    }
}
