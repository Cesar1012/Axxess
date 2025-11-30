<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    protected $table = 'proveedores';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nombre_proveedor', 'nit', 'contacto_nombre', 'telefono',
        'email', 'direccion', 'ciudad', 'activo', 'fecha_registro'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_registro' => 'datetime'
    ];

    public function compras(): HasMany
    {
        return $this->hasMany(Compra::class, 'proveedor_id');
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorCiudad($query, $ciudad)
    {
        return $query->where('ciudad', $ciudad);
    }
}
