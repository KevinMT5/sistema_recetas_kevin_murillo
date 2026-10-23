<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    protected $table = 'especialidades';
    protected $fillable = ['nombre', 'duracion_cita', 'activo'];
    protected $casts = ['activo' => 'boolean'];
}
