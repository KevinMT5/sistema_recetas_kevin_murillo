<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultorio extends Model
{
    protected $fillable = ['establecimiento_id', 'nombre', 'activo'];
    protected $casts = ['activo' => 'boolean'];

    public function establecimiento() { return $this->belongsTo(Establecimiento::class); }
}
