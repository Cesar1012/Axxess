<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteGenerado extends Model
{
    protected $table = 'reportes_generados';
    public $timestamps = false;

    protected $fillable = [
        'tipo_reporte', 'usuario_id', 'modulo', 'fecha_generacion',
        'parametros', 'archivo_resultado', 'enviado_email', 'email_destinatario'
    ];

    protected $casts = [
        'fecha_generacion' => 'datetime',
        'parametros' => 'array',
        'enviado_email' => 'boolean'
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function scopePorModulo($query, $modulo)
    {
        return $query->where('modulo', $modulo);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_reporte', $tipo);
    }
}
