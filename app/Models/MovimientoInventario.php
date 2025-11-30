<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';
    public $timestamps = false;

    protected $fillable = [
        'producto_id', 'lote_id', 'bodega_id', 'tipo_movimiento', 'cantidad',
        'referencia_id', 'referencia_tipo', 'costo_unitario', 'valor_total',
        'stock_anterior', 'stock_posterior', 'usuario_id', 'fecha_movimiento', 'observaciones'
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'referencia_id' => 'integer',
        'costo_unitario' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'stock_anterior' => 'integer',
        'stock_posterior' => 'integer',
        'fecha_movimiento' => 'datetime'
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    public function bodega(): BelongsTo
    {
        return $this->belongsTo(Bodega::class, 'bodega_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_movimiento', $tipo);
    }
}
