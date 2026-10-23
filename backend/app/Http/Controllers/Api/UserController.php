<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index() { return response()->json(User::orderBy('name')->get()); }
    public function store(Request $request) { $data = $this->data($request); $data['password'] = Hash::make($data['password']); return response()->json(User::create($data), 201); }
    public function show(User $user) { return response()->json($user->load(['paciente', 'medico'])); }
    public function update(Request $request, User $user) { $data = $this->data($request, $user); if (empty($data['password'])) unset($data['password']); else $data['password'] = Hash::make($data['password']); $user->update($data); return response()->json($user->fresh()); }
    public function destroy(User $user) { $user->update(['activo' => false]); $user->tokens()->delete(); return response()->json(['message' => 'Usuario desactivado.']); }
    private function data(Request $request, ?User $user = null) { return $request->validate(['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', Rule::unique('users')->ignore($user?->id)], 'telefono' => ['nullable', 'string', 'max:25'], 'rol' => ['required', Rule::in(['administrador', 'recepcionista', 'medico', 'paciente'])], 'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'], 'activo' => ['sometimes', 'boolean']]); }
}
