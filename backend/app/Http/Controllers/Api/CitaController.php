<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\HorarioMedico;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CitaController extends Controller
{
    private const ESTADOS = ['pendiente', 'confirmada', 'en_espera', 'en_consulta', 'finalizada', 'cancelada', 'no_asistio'];

    public function index(Request $request)
    {
        $relaciones = ['paciente', 'medico.especialidadCatalogo', 'establecimiento', 'consultorio'];
        if (in_array($request->user()->rol, ['medico', 'paciente'], true)) $relaciones[] = 'consulta';

        $query = Cita::with($relaciones)
            ->orderBy('fecha')->orderBy('hora_inicio');

        $this->scopeForUser($query, $request);

        $query->when($request->fecha, fn ($q, $fecha) => $q->whereDate('fecha', $fecha))
            ->when($request->desde, fn ($q, $desde) => $q->whereDate('fecha', '>=', $desde))
            ->when($request->hasta, fn ($q, $hasta) => $q->whereDate('fecha', '<=', $hasta))
            ->when($request->medico_id, fn ($q, $id) => $q->where('medico_id', $id))
            ->when($request->estado, fn ($q, $estado) => $q->where('estado', $estado));

        $citas = $query->get();
        if ($request->user()->rol === 'paciente') $citas->each(fn ($cita) => $cita->makeHidden(['notas', 'creada_por']));
        return response()->json($citas);
    }

    public function store(Request $request)
    {
        abort_unless(in_array($request->user()->rol, ['administrador', 'recepcionista', 'paciente'], true), 403);
        $data = $this->validated($request);
        $user = $request->user();

        if ($user->rol === 'paciente') {
            abort_unless($user->paciente, 422, 'Tu cuenta todavía no tiene un perfil de paciente.');
            $data['paciente_id'] = $user->paciente->id;
            $data['canal'] = 'portal_paciente';
        }

        $data['codigo'] = $this->nextCode();
        $data['creada_por'] = $user->id;
        $data['estado'] = $data['estado'] ?? ($user->rol === 'paciente' ? 'pendiente' : 'confirmada');
        $data['hora_fin'] = Carbon::createFromFormat('H:i', $data['hora_inicio'])
            ->addMinutes((int) ($data['duracion_minutos'] ?? 30))->format('H:i');
        unset($data['duracion_minutos']);

        if ($mensaje = $this->errorDisponibilidad($data)) return response()->json(['message' => $mensaje], 409);

        return response()->json(Cita::create($data)->load(['paciente', 'medico.especialidadCatalogo', 'establecimiento', 'consultorio']), 201);
    }

    public function show(Request $request, Cita $cita)
    {
        $this->authorizeCita($request, $cita);
        $cita->load(['paciente', 'medico.especialidadCatalogo', 'establecimiento', 'consultorio', 'consulta.recetas.detalles.medicamento']);
        if ($request->user()->rol === 'paciente') $cita->makeHidden(['notas', 'creada_por']);
        return response()->json($cita);
    }

    public function update(Request $request, Cita $cita)
    {
        $this->authorizeCita($request, $cita);
        $data = $request->validate([
            'fecha' => ['sometimes', 'date', 'after_or_equal:today'],
            'hora_inicio' => ['sometimes', 'date_format:H:i'],
            'duracion_minutos' => ['nullable', 'integer', 'min:10', 'max:240'],
            'medico_id' => ['sometimes', 'integer', 'exists:medicos,id'],
            'establecimiento_id' => ['sometimes', 'integer', 'exists:establecimientos,id'],
            'consultorio_id' => ['nullable', 'integer', 'exists:consultorios,id'],
            'estado' => ['sometimes', Rule::in(self::ESTADOS)],
            'motivo' => ['sometimes', 'string', 'max:255'],
            'notas' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($request->user()->rol === 'paciente') {
            $data = array_intersect_key($data, array_flip(['fecha', 'hora_inicio', 'motivo', 'estado']));
            if (isset($data['estado']) && $data['estado'] !== 'cancelada') {
                return response()->json(['message' => 'El paciente únicamente puede cancelar la cita.'], 403);
            }
        }

        if ($request->user()->rol === 'medico') {
            $data = array_intersect_key($data, array_flip(['estado', 'notas']));
        }

        if (isset($data['hora_inicio'])) {
            $data['hora_fin'] = Carbon::createFromFormat('H:i', $data['hora_inicio'])
                ->addMinutes((int) ($data['duracion_minutos'] ?? 30))->format('H:i');
        }
        unset($data['duracion_minutos']);

        $agendaCambia = count(array_intersect(array_keys($data), ['fecha', 'hora_inicio', 'medico_id', 'establecimiento_id'])) > 0;
        if ($agendaCambia) {
            $agenda = array_merge($cita->only(['fecha', 'hora_inicio', 'medico_id', 'establecimiento_id']), $data);
            if ($mensaje = $this->errorDisponibilidad($agenda, $cita->id)) return response()->json(['message' => $mensaje], 409);
        }

        if (($data['estado'] ?? null) === 'en_espera' && !$cita->check_in_at) {
            $data['check_in_at'] = now();
        }

        $cita->update($data);
        return response()->json($cita->fresh()->load(['paciente', 'medico.especialidadCatalogo', 'establecimiento', 'consultorio']));
    }

    public function destroy(Request $request, Cita $cita)
    {
        abort_unless($request->user()->rol === 'administrador', 403, 'Solo administración puede eliminar citas.');
        $cita->delete();
        return response()->json(['message' => 'Cita eliminada.']);
    }

    public function disponibilidad(Request $request)
    {
        $data = $request->validate([
            'medico_id' => ['required', 'integer', 'exists:medicos,id'],
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'establecimiento_id' => ['nullable', 'integer', 'exists:establecimientos,id'],
        ]);

        $fecha = Carbon::parse($data['fecha']);
        $horario = HorarioMedico::where('medico_id', $data['medico_id'])
            ->where('dia_semana', $fecha->dayOfWeekIso)
            ->where('activo', true)
            ->when($data['establecimiento_id'] ?? null, fn ($q, $id) => $q->where('establecimiento_id', $id))
            ->first();

        if (!$horario) {
            return response()->json([]);
        }

        $ocupadas = Cita::where('medico_id', $data['medico_id'])->whereDate('fecha', $data['fecha'])
            ->whereNotIn('estado', ['cancelada', 'no_asistio'])->pluck('hora_inicio')
            ->map(fn ($hora) => substr($hora, 0, 5))->all();
        $ahora = Carbon::createFromFormat('H:i:s', $horario->hora_inicio);
        $fin = Carbon::createFromFormat('H:i:s', $horario->hora_fin);
        $espacios = [];

        while ($ahora->copy()->addMinutes($horario->intervalo_minutos)->lte($fin)) {
            $hora = $ahora->format('H:i');
            $esPasado = $fecha->isToday() && $ahora->lte(now());
            if (!$esPasado && !in_array($hora, $ocupadas, true)) $espacios[] = $hora;
            $ahora->addMinutes($horario->intervalo_minutos);
        }

        return response()->json($espacios);
    }

    private function validated(Request $request)
    {
        return $request->validate([
            'paciente_id' => ['required_unless:canal,portal_paciente', 'nullable', 'integer', 'exists:pacientes,id'],
            'medico_id' => ['required', 'integer', 'exists:medicos,id'],
            'establecimiento_id' => ['required', 'integer', 'exists:establecimientos,id'],
            'consultorio_id' => ['nullable', 'integer', 'exists:consultorios,id'],
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'duracion_minutos' => ['nullable', 'integer', 'min:10', 'max:240'],
            'estado' => ['nullable', Rule::in(self::ESTADOS)],
            'canal' => ['nullable', Rule::in(['portal_paciente', 'recepcion', 'telefono', 'whatsapp'])],
            'motivo' => ['required', 'string', 'max:255'],
            'notas' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function scopeForUser($query, Request $request)
    {
        if ($request->user()->rol === 'paciente') {
            $query->where('paciente_id', optional($request->user()->paciente)->id ?? 0);
        } elseif ($request->user()->rol === 'medico') {
            $query->where('medico_id', optional($request->user()->medico)->id ?? 0);
        }
    }

    private function authorizeCita(Request $request, Cita $cita)
    {
        if ($request->user()->rol === 'paciente') abort_unless($cita->paciente_id === optional($request->user()->paciente)->id, 403);
        if ($request->user()->rol === 'medico') abort_unless($cita->medico_id === optional($request->user()->medico)->id, 403);
    }

    private function nextCode()
    {
        return 'CIT-' . now()->format('ymd') . '-' . str_pad((string) (Cita::count() + 1), 4, '0', STR_PAD_LEFT);
    }

    private function errorDisponibilidad(array $data, $exceptoId = null)
    {
        $fecha = Carbon::parse($data['fecha']);
        $hora = substr($data['hora_inicio'], 0, 5);
        $horario = HorarioMedico::where('medico_id', $data['medico_id'])
            ->where('establecimiento_id', $data['establecimiento_id'])
            ->where('dia_semana', $fecha->dayOfWeekIso)
            ->where('activo', true)
            ->whereTime('hora_inicio', '<=', $hora)
            ->whereTime('hora_fin', '>', $hora)
            ->first();

        if (!$horario) return 'El médico no atiende en esa sede, fecha y hora.';

        $inicio = Carbon::createFromFormat('H:i:s', $horario->hora_inicio);
        $solicitada = Carbon::createFromFormat('H:i', $hora);
        if ($inicio->diffInMinutes($solicitada) % $horario->intervalo_minutos !== 0) {
            return 'La hora seleccionada no coincide con los intervalos de la agenda.';
        }
        if ($fecha->isToday() && $solicitada->lte(now())) return 'No se puede reservar un horario que ya pasó.';

        $ocupada = Cita::where('medico_id', $data['medico_id'])->whereDate('fecha', $data['fecha'])
            ->whereTime('hora_inicio', $hora)->whereNotIn('estado', ['cancelada', 'no_asistio'])
            ->when($exceptoId, fn ($q) => $q->where('id', '!=', $exceptoId))->exists();

        return $ocupada ? 'Ese horario acaba de ser ocupado. Elige otro espacio.' : null;
    }
}
