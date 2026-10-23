<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receta extends Model
{
    use HasFactory;

    protected $fillable = [
        'fecha',
        'numero_receta',
        'medico_id',
        'paciente_id',
        'consulta_id',
    ];

    protected $casts = ['fecha' => 'date:Y-m-d'];

    // Relación con el médico
    public function medico() {
        return $this->belongsTo(Medico::class);
    }

    // Relación con el paciente
    public function paciente() {
        return $this->belongsTo(Paciente::class);
    }

    public function consulta() { return $this->belongsTo(Consulta::class); }

    // Relación con los detalles de la receta (medicamentos)
    public function detalles() {
        return $this->hasMany(RecetaDetalle::class);
    }
}
