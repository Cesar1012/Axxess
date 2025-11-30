<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CuentaPorCobrar extends Model
{
    protected $table = 'cuentas_por_cobrar';
    public $timestamps = false;

    protected $fillable = [
        'venta_id', 'numero_factura', 'cliente_market_id', 'valor_total', 'valor_pagado',
        'saldo_pendiente', 'fecha_emision', 'fecha_vencimiento', 'dias_mora', 'estado'
    ];

    protected $casts = [
        'valor_total' => 'decimal:2',
        'valor_pagado' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'dias_mora' => 'integer'
    ];

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function clienteMarket(): BelongsTo
    {
        return $this->belongsTo(ClienteMarket::class, 'cliente_market_id');
    }

    public function recaudos(): HasMany
    {
        return $this->hasMany(Recaudo::class, 'cuenta_cobrar_id');
    }

    public function scopeVigente($query)
    {
        return $query->where('estado', 'vigente');
    }

    public function scopeVencidas($query)
    {
        return $query->where('estado', 'vencida');
    }
}
