<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Venta extends Model
{
    protected $table = 'ventas';
    public $timestamps = false;

    protected $fillable = [
        'numero_factura', 'tipo_venta', 'pedido_id', 'despacho_id', 'cliente_market_id',
        'paciente_id', 'vendedor_id', 'usuario_id', 'fecha_venta', 'subtotal',
        'impuesto', 'descuento', 'total', 'forma_pago', 'estado', 'observaciones'
    ];

    protected $casts = [
        'fecha_venta' => 'datetime',
        'subtotal' => 'decimal:2',
        'impuesto' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2'
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function despacho(): BelongsTo
    {
        return $this->belongsTo(Despacho::class, 'despacho_id');
    }

    public function clienteMarket(): BelongsTo
    {
        return $this->belongsTo(ClienteMarket::class, 'cliente_market_id');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(Vendedor::class, 'vendedor_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    public function cuentaPorCobrar(): HasOne
    {
        return $this->hasOne(CuentaPorCobrar::class, 'venta_id');
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }
}
