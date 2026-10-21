<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Establecimiento;
use Illuminate\Support\Facades\Validator;

class EstablecimientoController extends Controller
{
    // Listar todos los establecimientos
    public function index()
    {
        $establecimientos = Establecimiento::all();
        return response()->json($establecimientos);
    }

    // Crear un nuevo establecimiento
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $establecimiento = Establecimiento::create($request->only(['nombre','direccion']));
        return response()->json($establecimiento, 201);
    }

    // Mostrar un establecimiento específico
    public function show($id)
    {
        $establecimiento = Establecimiento::findOrFail($id);
        return response()->json($establecimiento);
    }

    // Actualizar un establecimiento
    public function update(Request $request, $id)
    {
        $establecimiento = Establecimiento::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'direccion' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $establecimiento->update($request->only(['nombre','direccion']));
        return response()->json($establecimiento);
    }

    // Eliminar un establecimiento
    public function destroy($id)
    {
        $establecimiento = Establecimiento::findOrFail($id);
        if ($establecimiento->medicos()->exists()) {
            return response()->json(['message' => 'No se puede eliminar un establecimiento que tiene médicos.'], 409);
        }

        $establecimiento->delete();
        return response()->json(['message' => 'Establecimiento eliminado']);
    }
}
