<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntregaPaciente extends Model
{
    protected $table = 'entregas_pacientes';
    public $timestamps = false;

    protected $fillable = [
        'paciente_id', 'despacho_id', 'autorizacion_invima_id', 'producto_id', 'lote_id',
        'cantidad_viales', 'fecha_entrega', 'fecha_aplicacion', 'viales_utilizados_terapia',
        'requirio_acondicionamiento', 'insumos_nevera', 'insumos_gel', 'costo_insumos',
        'comision_entrega', 'documento_soporte', 'observaciones'
    ];

    protected $casts = [
        'cantidad_viales' => 'integer',
        'fecha_entrega' => 'date',
        'fecha_aplicacion' => 'date',
        'viales_utilizados_terapia' => 'integer',
        'requirio_acondicionamiento' => 'boolean',
        'insumos_nevera' => 'boolean',
        'insumos_gel' => 'boolean',
        'costo_insumos' => 'decimal:2',
        'comision_entrega' => 'decimal:2'
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function despacho(): BelongsTo
    {
        return $this->belongsTo(Despacho::class, 'despacho_id');
    }

    public function autorizacionInvima(): BelongsTo
    {
        return $this->belongsTo(AutorizacionInvima::class, 'autorizacion_invima_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }
}
