<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendedor extends Model
{
    protected $table = 'vendedores';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'usuario_id', 'codigo_vendedor', 'nombre_completo', 'telefono',
        'email', 'zona_asignada', 'comision_porcentaje', 'activo', 'fecha_ingreso'
    ];

    protected $casts = [
        'comision_porcentaje' => 'decimal:2',
        'activo' => 'boolean',
        'fecha_ingreso' => 'date'
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'vendedor_id');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'vendedor_id');
    }

    public function recaudos(): HasMany
    {
        return $this->hasMany(Recaudo::class, 'vendedor_id');
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorZona($query, $zona)
    {
        return $query->where('zona_asignada', $zona);
    }
}
