<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compra extends Model
{
    protected $table = 'compras';
    public $timestamps = false;

    protected $fillable = [
        'numero_compra', 'proveedor_id', 'usuario_id', 'fecha_compra',
        'numero_factura', 'subtotal', 'impuesto', 'total', 'estado', 'observaciones'
    ];

    protected $casts = [
        'fecha_compra' => 'date',
        'subtotal' => 'decimal:2',
        'impuesto' => 'decimal:2',
        'total' => 'decimal:2'
    ];

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleCompra::class, 'compra_id');
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }
}
