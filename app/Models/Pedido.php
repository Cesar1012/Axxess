<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    protected $table = 'pedidos';
    public $timestamps = false;

    protected $fillable = [
        'numero_pedido', 'tipo_pedido', 'cliente_market_id', 'paciente_id',
        'vendedor_id', 'usuario_registro_id', 'fecha_pedido', 'fecha_entrega_programada',
        'subtotal', 'descuento', 'impuesto', 'total', 'estado', 'observaciones',
        'requiere_nevera', 'requiere_gel'
    ];

    protected $casts = [
        'fecha_pedido' => 'datetime',
        'fecha_entrega_programada' => 'date',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'impuesto' => 'decimal:2',
        'total' => 'decimal:2',
        'requiere_nevera' => 'boolean',
        'requiere_gel' => 'boolean'
    ];

    // Relaciones
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
        return $this->belongsTo(Usuario::class, 'usuario_registro_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class, 'pedido_id');
    }

    public function despachos(): HasMany
    {
        return $this->hasMany(Despacho::class, 'pedido_id');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'pedido_id');
    }

    // Scopes
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }
}
