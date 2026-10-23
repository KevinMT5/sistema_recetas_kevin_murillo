<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HorarioMedico extends Model
{
    protected $table = 'horarios_medicos';
    protected $fillable = ['medico_id', 'establecimiento_id', 'dia_semana', 'hora_inicio', 'hora_fin', 'intervalo_minutos', 'activo'];
    protected $casts = ['activo' => 'boolean'];

    public function medico() { return $this->belongsTo(Medico::class); }
    public function establecimiento() { return $this->belongsTo(Establecimiento::class); }
}
