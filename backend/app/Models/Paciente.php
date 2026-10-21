<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'fecha_nacimiento', 'telefono', 'direccion'];

    protected $casts = ['fecha_nacimiento' => 'date'];

    public function recetas()
    {
        return $this->hasMany(Receta::class);
    }
}
