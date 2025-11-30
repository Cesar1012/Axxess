<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RutaEntrega extends Model
{
    protected $table = 'rutas_entrega';
    public $timestamps = false;

    protected $fillable = [
        'nombre_ruta', 'fecha_ruta', 'zona', 'transportista',
        'vehiculo_placa', 'hora_salida', 'estado', 'observaciones'
    ];

    protected $casts = [
        'fecha_ruta' => 'date',
        'hora_salida' => 'datetime'
    ];

    public function despachosRuta(): HasMany
    {
        return $this->hasMany(DespachoRuta::class, 'ruta_id');
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }
}
