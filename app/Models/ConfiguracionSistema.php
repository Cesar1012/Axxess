<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionSistema extends Model
{
    protected $table = 'configuracion_sistema';
    public $timestamps = false;

    protected $fillable = [
        'parametro', 'valor', 'descripcion', 'modulo', 'tipo_dato'
    ];

    public function scopePorModulo($query, $modulo)
    {
        return $query->where('modulo', $modulo);
    }

    public function scopePorParametro($query, $parametro)
    {
        return $query->where('parametro', $parametro);
    }

    // Método helper para obtener valor de configuración
    public static function getValor($parametro, $default = null)
    {
        $config = self::where('parametro', $parametro)->first();
        return $config ? $config->valor : $default;
    }

    // Método helper para actualizar configuración
    public static function setValor($parametro, $valor)
    {
        return self::updateOrCreate(
            ['parametro' => $parametro],
            ['valor' => $valor]
        );
    }
}
