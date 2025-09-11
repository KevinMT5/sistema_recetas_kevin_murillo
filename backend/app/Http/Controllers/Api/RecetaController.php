<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Receta;
use App\Models\RecetaDetalle;
use Illuminate\Support\Facades\Validator;

class RecetaController extends Controller
{
    /**
     * Display a listing of the resource.
     * Permite filtrar por médico, medicamento y fecha.
     */
    public function index(Request $request)
    {
        $query = Receta::with(['medico.establecimiento', 'paciente', 'detalles.medicamento'])
            ->withCount('detalles')
            ->when($request->medico_id, fn($q) => $q->where('medico_id', $request->medico_id))
            ->when($request->fecha, fn($q) => $q->where('fecha', $request->fecha))
            ->when($request->medicamento_id, fn($q) => $q->whereHas('detalles', fn($q2) => $q2->where('medicamento_id', $request->medicamento_id)));

        $recetas = $query->get();
        $recetas->transform(fn($r) => tap($r, fn($x) => $x->total_medicamentos = $x->detalles_count));

        return response()->json($recetas);
    }

    /**
     * Store a newly created resource in storage.
     * Crea una receta con múltiples medicamentos.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fecha' => 'required|date',
            'numero_receta' => 'required|string',
            'medico_id' => 'required|integer|exists:medicos,id',
            'paciente_id' => 'required|integer|exists:pacientes,id',
            'detalles' => 'required|array|min:1',
            'detalles.*.medicamento_id' => 'required|integer|exists:medicamentos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.dosis' => 'required|string',
            'detalles.*.indicaciones' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $receta = Receta::create($request->only(['fecha','numero_receta','medico_id','paciente_id']));

        foreach($request->detalles as $detalle){
            RecetaDetalle::create([
                'receta_id' => $receta->id,
                'medicamento_id' => $detalle['medicamento_id'],
                'cantidad' => $detalle['cantidad'],
                'dosis' => $detalle['dosis'],
                'indicaciones' => $detalle['indicaciones'] ?? null
            ]);
        }

        // Retornamos la receta completa con relaciones cargadas
        return response()->json(
            $receta->load(['medico.establecimiento', 'paciente', 'detalles.medicamento']),
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $receta = Receta::with(['medico.establecimiento', 'paciente', 'detalles.medicamento'])
            ->findOrFail($id);

        return response()->json($receta);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $receta = Receta::findOrFail($id);
        $receta->delete();

        return response()->json(['message' => 'Receta eliminada']);
    }
}
