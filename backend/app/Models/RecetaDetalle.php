<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecetaDetalle extends Model
{
    use HasFactory;

    protected $fillable = [
        'receta_id',
        'medicamento_id',
        'cantidad',
        'dosis',
        'indicaciones'
    ];

    // Relación con la receta
    public function receta()
    {
        return $this->belongsTo(Receta::class);
    }

    // Relación con el medicamento
    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class);
    }
}
