<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DespachoRuta extends Model
{
    protected $table = 'despachos_ruta';
    public $timestamps = false;

    protected $fillable = [
        'ruta_id', 'despacho_id', 'orden_entrega', 'hora_estimada'
    ];

    protected $casts = [
        'orden_entrega' => 'integer',
        'hora_estimada' => 'datetime'
    ];

    public function ruta(): BelongsTo
    {
        return $this->belongsTo(RutaEntrega::class, 'ruta_id');
    }

    public function despacho(): BelongsTo
    {
        return $this->belongsTo(Despacho::class, 'despacho_id');
    }
}
