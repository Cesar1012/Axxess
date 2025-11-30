<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lote extends Model
{
    protected $table = 'lotes';
    public $timestamps = false;

    protected $fillable = [
        'producto_id', 'numero_lote', 'fecha_fabricacion', 'fecha_vencimiento',
        'cantidad_inicial', 'cantidad_actual', 'bodega_id', 'laboratorio_id',
        'deposito_llegada', 'acondicionado', 'estado', 'foto_producto'
    ];

    protected $casts = [
        'fecha_fabricacion' => 'date',
        'fecha_vencimiento' => 'date',
        'cantidad_inicial' => 'integer',
        'cantidad_actual' => 'integer',
        'acondicionado' => 'boolean'
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function bodega(): BelongsTo
    {
        return $this->belongsTo(Bodega::class, 'bodega_id');
    }

    public function laboratorio(): BelongsTo
    {
        return $this->belongsTo(Laboratorio::class, 'laboratorio_id');
    }

    public function detallesImportacion(): HasMany
    {
        return $this->hasMany(DetalleImportacion::class, 'lote_id');
    }

    public function detallesDespacho(): HasMany
    {
        return $this->hasMany(DetalleDespacho::class, 'lote_id');
    }

    public function entregasPacientes(): HasMany
    {
        return $this->hasMany(EntregaPaciente::class, 'lote_id');
    }

    public function detallesVenta(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'lote_id');
    }

    public function movimientosInventario(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'lote_id');
    }

    public function scopeDisponible($query)
    {
        return $query->where('estado', 'disponible');
    }

    public function scopeProximoAVencer($query, $dias = 90)
    {
        return $query->whereRaw('fecha_vencimiento <= CURRENT_DATE + INTERVAL ? DAY', [$dias]);
    }
}
