<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medico extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'nombre', 'numero_jv', 'especialidad', 'especialidad_id', 'establecimiento_id'];

    public function user() { return $this->belongsTo(User::class); }
    public function especialidadCatalogo() { return $this->belongsTo(Especialidad::class, 'especialidad_id'); }

    // Relación con establecimiento
    public function establecimiento()
    {
        return $this->belongsTo(Establecimiento::class);
    }

    public function recetas()
    {
        return $this->hasMany(Receta::class);
    }

    public function citas() { return $this->hasMany(Cita::class); }
    public function horarios() { return $this->hasMany(HorarioMedico::class); }
}
