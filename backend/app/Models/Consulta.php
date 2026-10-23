<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consulta extends Model
{
    protected $fillable = ['cita_id', 'paciente_id', 'medico_id', 'fecha_atencion', 'motivo_consulta', 'diagnostico', 'indicaciones', 'signos_vitales'];
    protected $casts = ['fecha_atencion' => 'datetime', 'signos_vitales' => 'array'];

    public function cita() { return $this->belongsTo(Cita::class); }
    public function paciente() { return $this->belongsTo(Paciente::class); }
    public function medico() { return $this->belongsTo(Medico::class); }
    public function recetas() { return $this->hasMany(Receta::class); }
}
