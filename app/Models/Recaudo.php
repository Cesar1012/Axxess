<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recaudo extends Model
{
    protected $table = 'recaudos';
    public $timestamps = false;

    protected $fillable = [
        'cuenta_cobrar_id', 'numero_factura', 'vendedor_id', 'fecha_recaudo',
        'valor_recaudado', 'forma_pago', 'numero_transaccion', 'documento_soporte', 'observaciones'
    ];

    protected $casts = [
        'fecha_recaudo' => 'date',
        'valor_recaudado' => 'decimal:2'
    ];

    public function cuentaPorCobrar(): BelongsTo
    {
        return $this->belongsTo(CuentaPorCobrar::class, 'cuenta_cobrar_id');
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(Vendedor::class, 'vendedor_id');
    }
}
