<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bodega extends Model
{
    protected $table = 'bodegas';
    public $timestamps = false;

    protected $fillable = [
        'nombre_bodega', 'ubicacion_ciudad', 'direccion', 'activa', 'fecha_creacion'
    ];

    protected $casts = [
        'activa' => 'boolean',
        'fecha_creacion' => 'datetime'
    ];

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class, 'bodega_id');
    }

    public function movimientosInventario(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'bodega_id');
    }

    public function scopeActiva($query)
    {
        return $query->where('activa', true);
    }
}
