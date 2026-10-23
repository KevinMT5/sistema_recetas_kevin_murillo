<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'nombre', 'email', 'documento', 'fecha_nacimiento', 'telefono', 'direccion', 'contacto_emergencia'];

    protected $casts = ['fecha_nacimiento' => 'date'];

    public function recetas()
    {
        return $this->hasMany(Receta::class);
    }

    public function user() { return $this->belongsTo(User::class); }
    public function citas() { return $this->hasMany(Cita::class); }
}
