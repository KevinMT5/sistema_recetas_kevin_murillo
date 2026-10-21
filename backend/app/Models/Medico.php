<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medico extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'numero_jv', 'especialidad', 'establecimiento_id'];

    // Relación con establecimiento
    public function establecimiento()
    {
        return $this->belongsTo(Establecimiento::class);
    }

    public function recetas()
    {
        return $this->hasMany(Receta::class);
    }
}
