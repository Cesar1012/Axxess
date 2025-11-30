<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alerta extends Model
{
    protected $table = 'alertas';
    public $timestamps = false;

    protected $fillable = [
        'tipo_alerta', 'modulo', 'producto_id', 'lote_id', 'paciente_id',
        'cliente_market_id', 'autorizacion_invima_id', 'licencia_importacion_id',
        'mensaje', 'fecha_creacion', 'fecha_vencimiento_alerta', 'prioridad',
        'leida', 'resuelta', 'enviar_email', 'email_enviado', 'destinatarios_email'
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'fecha_vencimiento_alerta' => 'date',
        'leida' => 'boolean',
        'resuelta' => 'boolean',
        'enviar_email' => 'boolean',
        'email_enviado' => 'boolean'
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function clienteMarket(): BelongsTo
    {
        return $this->belongsTo(ClienteMarket::class, 'cliente_market_id');
    }

    public function autorizacionInvima(): BelongsTo
    {
        return $this->belongsTo(AutorizacionInvima::class, 'autorizacion_invima_id');
    }

    public function licenciaImportacion(): BelongsTo
    {
        return $this->belongsTo(LicenciaImportacion::class, 'licencia_importacion_id');
    }

    public function scopeNoLeidas($query)
    {
        return $query->where('leida', false);
    }

    public function scopePendientes($query)
    {
        return $query->where('resuelta', false);
    }

    public function scopePorPrioridad($query, $prioridad)
    {
        return $query->where('prioridad', $prioridad);
    }
}
