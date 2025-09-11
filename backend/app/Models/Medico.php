<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medico extends Model
{
    use HasFactory;

    // Relación con establecimiento
    public function establecimiento()
    {
        return $this->belongsTo(Establecimiento::class);
    }
}
