<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Establecimiento extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'direccion'];

    public function medicos()
    {
        return $this->hasMany(Medico::class);
    }

    public function consultorios() { return $this->hasMany(Consultorio::class); }
    public function citas() { return $this->hasMany(Cita::class); }
}
