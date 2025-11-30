<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens;
    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'usuarios';

    /**
     * La clave primaria de la tabla
     */
    protected $primaryKey = 'id';

    /**
     * Desactivar timestamps automáticos (created_at, updated_at)
     * Ya que usamos fecha_creacion y ultimo_acceso
     */
    public $timestamps = false;

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'nombre_completo',
        'email',
        'password',
        'rol',
        'modulo_acceso',
        'telefono',
        'activo',
        'fecha_creacion',
        'ultimo_acceso'
    ];

    /**
     * Campos ocultos en las respuestas JSON
     */
    protected $hidden = [
        'password'
    ];

    /**
     * Castear atributos a tipos nativos
     */
    protected $casts = [
        'activo' => 'boolean',
        'fecha_creacion' => 'datetime',
        'ultimo_acceso' => 'datetime'
    ];

    /**
     * Relaciones
     */

    // Un usuario puede registrar muchos pedidos
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'usuario_registro_id');
    }

    // Un usuario puede preparar muchos despachos
    public function despachos(): HasMany
    {
        return $this->hasMany(Despacho::class, 'usuario_preparo_id');
    }

    // Un usuario puede registrar muchas ventas
    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'usuario_id');
    }

    // Un usuario puede generar muchos reportes
    public function reportes(): HasMany
    {
        return $this->hasMany(ReporteGenerado::class, 'usuario_id');
    }

    // Un usuario puede tener muchos movimientos de inventario
    public function movimientosInventario(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'usuario_id');
    }

    // Un usuario puede tener muchas auditorías
    public function auditorias(): HasMany
    {
        return $this->hasMany(Auditoria::class, 'usuario_id');
    }

    // Un usuario puede registrar muchas compras
    public function compras(): HasMany
    {
        return $this->hasMany(Compra::class, 'usuario_id');
    }

    /**
     * Scopes
     */

    // Filtrar usuarios activos
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    // Filtrar por rol
    public function scopePorRol($query, $rol)
    {
        return $query->where('rol', $rol);
    }

    // Filtrar por módulo de acceso
    public function scopePorModulo($query, $modulo)
    {
        return $query->where('modulo_acceso', $modulo);
    }
}
