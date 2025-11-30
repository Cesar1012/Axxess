<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paciente extends Model
{
    protected $table = 'pacientes';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'codigo_paciente',
        'nombre_completo',
        'tipo_documento',
        'numero_documento',
        'eps_asegurador',
        'telefono',
        'email',
        'direccion_entrega',
        'zona_cobertura',
        'requiere_cita_entrega',
        'observaciones',
        'activo',
        'fecha_registro'
    ];

    protected $casts = [
        'requiere_cita_entrega' => 'boolean',
        'activo' => 'boolean',
        'fecha_registro' => 'datetime'
    ];

    /**
     * Relaciones
     */

    // Tiene muchas autorizaciones INVIMA
    public function autorizacionesInvima(): HasMany
    {
        return $this->hasMany(AutorizacionInvima::class, 'paciente_id');
    }

    // Tiene muchos pedidos
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'paciente_id');
    }

    // Tiene muchos despachos
    public function despachos(): HasMany
    {
        return $this->hasMany(Despacho::class, 'paciente_id');
    }

    // Tiene muchas entregas
    public function entregas(): HasMany
    {
        return $this->hasMany(EntregaPaciente::class, 'paciente_id');
    }

    // Tiene muchas ventas
    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'paciente_id');
    }

    // Tiene muchas alertas
    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class, 'paciente_id');
    }

    /**
     * Scopes
     */

    // Filtrar pacientes activos
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    // Filtrar por zona de cobertura
    public function scopePorZona($query, $zona)
    {
        return $query->where('zona_cobertura', $zona);
    }

    // Filtrar pacientes que requieren cita para entrega
    public function scopeRequiereCita($query)
    {
        return $query->where('requiere_cita_entrega', true);
    }

    // Filtrar por EPS
    public function scopePorEps($query, $eps)
    {
        return $query->where('eps_asegurador', $eps);
    }
}
