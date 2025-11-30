<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    protected $table = 'categorias';
    public $timestamps = false;

    protected $fillable = ['nombre', 'tipo_modulo', 'descripcion', 'activa'];
    protected $casts = ['activa' => 'boolean'];

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'categoria_id');
    }

    public function scopeActiva($query)
    {
        return $query->where('activa', true);
    }
}
