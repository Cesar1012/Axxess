<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClienteMarket extends Model
{
    protected $table = 'clientes_market';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'tipo_cliente',
        'razon_social',
        'nit',
        'contacto_nombre',
        'telefono',
        'email',
        'direccion_entrega',
        'ciudad',
        'persona_contacto',
        'sucursal',
        'vendedor_asignado_id',
        'limite_credito',
        'dias_credito',
        'cartera_al_dia',
        'estado',
        'fecha_registro'
    ];

    protected $casts = [
        'limite_credito' => 'decimal:2',
        'dias_credito' => 'integer',
        'cartera_al_dia' => 'boolean',
        'fecha_registro' => 'datetime'
    ];

    /**
     * Relaciones
     */

    // Tiene muchos pedidos
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'cliente_market_id');
    }

    // Tiene muchos despachos
    public function despachos(): HasMany
    {
        return $this->hasMany(Despacho::class, 'cliente_market_id');
    }

    // Tiene muchas ventas
    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'cliente_market_id');
    }

    // Tiene muchas cuentas por cobrar
    public function cuentasPorCobrar(): HasMany
    {
        return $this->hasMany(CuentaPorCobrar::class, 'cliente_market_id');
    }

    // Tiene muchas alertas
    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class, 'cliente_market_id');
    }

    /**
     * Scopes
     */

    // Filtrar por estado
    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }

    // Filtrar por cartera al día
    public function scopeCarteraAlDia($query)
    {
        return $query->where('cartera_al_dia', true);
    }

    // Filtrar por ciudad
    public function scopePorCiudad($query, $ciudad)
    {
        return $query->where('ciudad', $ciudad);
    }

    // Filtrar por tipo de cliente
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_cliente', $tipo);
    }
}
