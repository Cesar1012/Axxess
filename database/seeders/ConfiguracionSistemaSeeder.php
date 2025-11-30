<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConfiguracionSistema;

class ConfiguracionSistemaSeeder extends Seeder
{
    public function run(): void
    {
        $configuraciones = [
            [
                'parametro' => 'nombre_empresa',
                'valor' => 'GLAZ GROUP S.A.S.',
                'descripcion' => 'Nombre de la empresa',
                'modulo' => 'general',
                'tipo_dato' => 'string'
            ],
            [
                'parametro' => 'dias_alerta_vencimiento',
                'valor' => '90',
                'descripcion' => 'Días de alerta antes de vencimiento de lotes',
                'modulo' => 'general',
                'tipo_dato' => 'integer'
            ],
            [
                'parametro' => 'stock_minimo_default',
                'valor' => '10',
                'descripcion' => 'Stock mínimo por defecto para productos',
                'modulo' => 'general',
                'tipo_dato' => 'integer'
            ],
            [
                'parametro' => 'iva_default',
                'valor' => '19',
                'descripcion' => 'IVA por defecto (%)',
                'modulo' => 'general',
                'tipo_dato' => 'decimal'
            ],
            [
                'parametro' => 'alertas_email_enabled',
                'valor' => 'true',
                'descripcion' => 'Activar envío de alertas por email',
                'modulo' => 'alertas',
                'tipo_dato' => 'boolean'
            ],
            [
                'parametro' => 'dias_credito_default',
                'valor' => '30',
                'descripcion' => 'Días de crédito por defecto para clientes',
                'modulo' => 'market',
                'tipo_dato' => 'integer'
            ],
            [
                'parametro' => 'comision_vendedor_default',
                'valor' => '5',
                'descripcion' => 'Comisión por defecto para vendedores (%)',
                'modulo' => 'market',
                'tipo_dato' => 'decimal'
            ],
            [
                'parametro' => 'alerta_stock_bajo',
                'valor' => 'true',
                'descripcion' => 'Generar alerta cuando stock esté bajo',
                'modulo' => 'inventario',
                'tipo_dato' => 'boolean'
            ],
            [
                'parametro' => 'dias_mora_alerta',
                'valor' => '5',
                'descripcion' => 'Días de mora para generar alerta',
                'modulo' => 'cartera',
                'tipo_dato' => 'integer'
            ]
        ];

        foreach ($configuraciones as $config) {
            ConfiguracionSistema::create($config);
        }
    }
}
