<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    protected $fillable = ['codigo', 'paciente_id', 'medico_id', 'establecimiento_id', 'consultorio_id', 'creada_por', 'fecha', 'hora_inicio', 'hora_fin', 'estado', 'canal', 'motivo', 'notas', 'check_in_at'];
    protected $casts = ['fecha' => 'date:Y-m-d', 'check_in_at' => 'datetime'];

    public function paciente() { return $this->belongsTo(Paciente::class); }
    public function medico() { return $this->belongsTo(Medico::class); }
    public function establecimiento() { return $this->belongsTo(Establecimiento::class); }
    public function consultorio() { return $this->belongsTo(Consultorio::class); }
    public function creador() { return $this->belongsTo(User::class, 'creada_por'); }
    public function consulta() { return $this->hasOne(Consulta::class); }
}
