<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Auditoria extends Model
{
    protected $table = 'auditoria';
    public $timestamps = false;

    protected $fillable = [
        'usuario_id', 'modulo', 'accion', 'tabla_afectada', 'registro_id',
        'datos_anteriores', 'datos_nuevos', 'ip_origen', 'fecha_hora'
    ];

    protected $casts = [
        'registro_id' => 'integer',
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array',
        'fecha_hora' => 'datetime'
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function scopePorModulo($query, $modulo)
    {
        return $query->where('modulo', $modulo);
    }

    public function scopePorAccion($query, $accion)
    {
        return $query->where('accion', $accion);
    }
}
