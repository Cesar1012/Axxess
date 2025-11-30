<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Despacho extends Model
{
    protected $table = 'despachos';
    public $timestamps = false;

    protected $fillable = [
        'numero_remision', 'numero_guia', 'pedido_id', 'paciente_id', 'cliente_market_id',
        'direccion_entrega', 'fecha_despacho', 'fecha_entrega', 'transportista',
        'vehiculo_placa', 'hora_cargue', 'requiere_cita', 'soporte_entrega',
        'documento_soporte', 'estado', 'firma_recibido', 'observaciones', 'usuario_preparo_id'
    ];

    protected $casts = [
        'fecha_despacho' => 'datetime',
        'fecha_entrega' => 'datetime',
        'hora_cargue' => 'datetime',
        'requiere_cita' => 'boolean',
        'soporte_entrega' => 'boolean'
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function clienteMarket(): BelongsTo
    {
        return $this->belongsTo(ClienteMarket::class, 'cliente_market_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_preparo_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleDespacho::class, 'despacho_id');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'despacho_id');
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }
}
