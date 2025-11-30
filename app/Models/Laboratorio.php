<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Laboratorio extends Model
{
    protected $table = 'laboratorios';
    public $timestamps = false;

    protected $fillable = [
        'nombre_laboratorio', 'nit', 'pais_origen', 'contacto_nombre',
        'telefono', 'email', 'direccion', 'activo', 'fecha_registro'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_registro' => 'datetime'
    ];

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class, 'laboratorio_id');
    }

    public function importaciones(): HasMany
    {
        return $this->hasMany(Importacion::class, 'laboratorio_id');
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
}
