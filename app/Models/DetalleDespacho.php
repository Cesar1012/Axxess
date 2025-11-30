<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleDespacho extends Model
{
    protected $table = 'detalle_despachos';
    public $timestamps = false;

    protected $fillable = [
        'despacho_id', 'producto_id', 'lote_id', 'cantidad',
        'autorizacion_invima_id', 'fecha_vencimiento_lote'
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'fecha_vencimiento_lote' => 'date'
    ];

    public function despacho(): BelongsTo
    {
        return $this->belongsTo(Despacho::class, 'despacho_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    public function autorizacionInvima(): BelongsTo
    {
        return $this->belongsTo(AutorizacionInvima::class, 'autorizacion_invima_id');
    }
}
