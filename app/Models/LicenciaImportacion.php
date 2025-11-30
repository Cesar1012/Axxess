<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LicenciaImportacion extends Model
{
    protected $table = 'licencias_importacion';
    public $timestamps = false;

    protected $fillable = [
        'numero_licencia', 'producto_id', 'cantidad_autorizada', 'cantidad_importada',
        'saldo_pendiente', 'fecha_emision', 'fecha_vencimiento', 'lote_autorizado',
        'fecha_vencimiento_lote', 'codigo_paciente', 'tipo_licencia', 'estado',
        'documento_soporte', 'observaciones'
    ];

    protected $casts = [
        'cantidad_autorizada' => 'integer',
        'cantidad_importada' => 'integer',
        'saldo_pendiente' => 'integer',
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'fecha_vencimiento_lote' => 'date'
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function importaciones(): HasMany
    {
        return $this->hasMany(Importacion::class, 'licencia_importacion_id');
    }

    public function scopeVigente($query)
    {
        return $query->where('estado', 'vigente');
    }
}
