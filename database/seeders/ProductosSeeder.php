<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;

class ProductosSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [
            // THERAPIES - Medicamentos de Alto Costo
            [
                'codigo_producto' => 'THER-001',
                'codigo_barras' => '7891234567890',
                'nombre' => 'Eculizumab 300mg',
                'molecula' => 'Eculizumab',
                'descripcion' => 'Anticuerpo monoclonal para HPN',
                'categoria_id' => 1, // Medicamentos de Alto Costo
                'modulo' => 'therapies',
                'precio_compra' => 15000000.00,
                'precio_venta' => 18000000.00,
                'precio_mayorista' => null,
                'stock_minimo' => 5,
                'stock_actual' => 0,
                'unidad_medida' => 'vial',
                'requiere_cadena_frio' => true,
                'requiere_acondicionamiento' => true,
                'lote_inicial' => null,
                'fecha_vencimiento_inicial' => null,
                'impuesto_iva' => 0.00,
                'registro_sanitario' => 'INVIMA-2023-0001234',
                'fecha_vencimiento_registro' => '2028-12-31',
                'activo' => true,
                'fecha_creacion' => now()
            ],
            [
                'codigo_producto' => 'THER-002',
                'codigo_barras' => '7891234567891',
                'nombre' => 'Rituximab 500mg',
                'molecula' => 'Rituximab',
                'descripcion' => 'Anticuerpo monoclonal para linfoma',
                'categoria_id' => 2, // Biotecnológicos
                'modulo' => 'therapies',
                'precio_compra' => 3500000.00,
                'precio_venta' => 4200000.00,
                'precio_mayorista' => null,
                'stock_minimo' => 10,
                'stock_actual' => 0,
                'unidad_medida' => 'vial',
                'requiere_cadena_frio' => true,
                'requiere_acondicionamiento' => false,
                'lote_inicial' => null,
                'fecha_vencimiento_inicial' => null,
                'impuesto_iva' => 0.00,
                'registro_sanitario' => 'INVIMA-2023-0001235',
                'fecha_vencimiento_registro' => '2027-06-30',
                'activo' => true,
                'fecha_creacion' => now()
            ],
            // MARKET - Medicamentos Generales
            [
                'codigo_producto' => 'MKT-001',
                'codigo_barras' => '7891234567892',
                'nombre' => 'Paracetamol 500mg',
                'molecula' => 'Paracetamol',
                'descripcion' => 'Analgésico y antipirético',
                'categoria_id' => 4, // Medicamentos Generales
                'modulo' => 'market',
                'precio_compra' => 5000.00,
                'precio_venta' => 8000.00,
                'precio_mayorista' => 6500.00,
                'stock_minimo' => 100,
                'stock_actual' => 0,
                'unidad_medida' => 'caja x 50 tabletas',
                'requiere_cadena_frio' => false,
                'requiere_acondicionamiento' => false,
                'lote_inicial' => null,
                'fecha_vencimiento_inicial' => null,
                'impuesto_iva' => 19.00,
                'registro_sanitario' => 'INVIMA-2022-0005432',
                'fecha_vencimiento_registro' => '2026-12-31',
                'activo' => true,
                'fecha_creacion' => now()
            ],
            [
                'codigo_producto' => 'MKT-002',
                'codigo_barras' => '7891234567893',
                'nombre' => 'Ibuprofeno 400mg',
                'molecula' => 'Ibuprofeno',
                'descripcion' => 'Antiinflamatorio no esteroideo',
                'categoria_id' => 4,
                'modulo' => 'market',
                'precio_compra' => 8000.00,
                'precio_venta' => 12000.00,
                'precio_mayorista' => 10000.00,
                'stock_minimo' => 80,
                'stock_actual' => 0,
                'unidad_medida' => 'caja x 30 tabletas',
                'requiere_cadena_frio' => false,
                'requiere_acondicionamiento' => false,
                'lote_inicial' => null,
                'fecha_vencimiento_inicial' => null,
                'impuesto_iva' => 19.00,
                'registro_sanitario' => 'INVIMA-2022-0006789',
                'fecha_vencimiento_registro' => '2027-03-31',
                'activo' => true,
                'fecha_creacion' => now()
            ],
            // Insumos de Cadena de Frío
            [
                'codigo_producto' => 'INS-001',
                'codigo_barras' => '7891234567894',
                'nombre' => 'Nevera Portátil 5L',
                'molecula' => null,
                'descripcion' => 'Nevera portátil para transporte de medicamentos',
                'categoria_id' => 8, // Insumos de Cadena de Frío
                'modulo' => 'market',
                'precio_compra' => 120000.00,
                'precio_venta' => 180000.00,
                'precio_mayorista' => 150000.00,
                'stock_minimo' => 20,
                'stock_actual' => 0,
                'unidad_medida' => 'unidad',
                'requiere_cadena_frio' => false,
                'requiere_acondicionamiento' => false,
                'lote_inicial' => null,
                'fecha_vencimiento_inicial' => null,
                'impuesto_iva' => 19.00,
                'registro_sanitario' => null,
                'fecha_vencimiento_registro' => null,
                'activo' => true,
                'fecha_creacion' => now()
            ]
        ];

        foreach ($productos as $producto) {
            Producto::create($producto);
        }
    }
}
