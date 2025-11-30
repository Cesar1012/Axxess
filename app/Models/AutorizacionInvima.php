<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutorizacionInvima extends Model
{
    protected $table = 'autorizaciones_invima';
    public $timestamps = false;

    protected $fillable = [
        'numero_autorizacion', 'paciente_id', 'producto_id', 'cantidad_autorizada',
        'cantidad_despachada', 'saldo_pendiente', 'fecha_emision', 'fecha_vencimiento',
        'lote_autorizado', 'fecha_vencimiento_lote', 'estado', 'documento_soporte',
        'observaciones', 'fecha_registro'
    ];

    protected $casts = [
        'cantidad_autorizada' => 'integer',
        'cantidad_despachada' => 'integer',
        'saldo_pendiente' => 'integer',
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'fecha_vencimiento_lote' => 'date',
        'fecha_registro' => 'datetime'
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function scopeVigente($query)
    {
        return $query->where('estado', 'vigente');
    }
}
