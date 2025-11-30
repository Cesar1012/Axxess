<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Importacion extends Model
{
    protected $table = 'importaciones';
    public $timestamps = false;

    protected $fillable = [
        'numero_importacion', 'licencia_importacion_id', 'laboratorio_id',
        'fecha_importacion', 'numero_declaracion', 'numero_factura', 'numero_guia',
        'valor_fob', 'valor_total', 'documento_autorizacion', 'documento_declaracion',
        'documento_factura', 'documento_guia', 'documento_licencia', 'estado', 'observaciones'
    ];

    protected $casts = [
        'fecha_importacion' => 'date',
        'valor_fob' => 'decimal:2',
        'valor_total' => 'decimal:2'
    ];

    public function licenciaImportacion(): BelongsTo
    {
        return $this->belongsTo(LicenciaImportacion::class, 'licencia_importacion_id');
    }

    public function laboratorio(): BelongsTo
    {
        return $this->belongsTo(Laboratorio::class, 'laboratorio_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleImportacion::class, 'importacion_id');
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }
}
