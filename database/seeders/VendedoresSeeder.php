<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vendedor;

class VendedoresSeeder extends Seeder
{
    public function run(): void
    {
        $vendedores = [
            [
                'usuario_id' => 1, // Administrador (temporal)
                'codigo_vendedor' => 'VEN-001',
                'nombre_completo' => 'Carlos Ejecutivo',
                'telefono' => '+57-312-3456789',
                'email' => 'ejecutivo@glazgroup.com',
                'zona_asignada' => 'Bogotá y Cundinamarca',
                'comision_porcentaje' => 5.00,
                'activo' => true,
                'fecha_ingreso' => now()->subMonths(6)
            ],
            [
                'usuario_id' => null,
                'codigo_vendedor' => 'VEN-002',
                'nombre_completo' => 'Laura Sánchez',
                'telefono' => '+57-313-5678901',
                'email' => 'laura.sanchez@glazgroup.com',
                'zona_asignada' => 'Antioquia',
                'comision_porcentaje' => 5.00,
                'activo' => true,
                'fecha_ingreso' => now()->subMonths(3)
            ],
            [
                'usuario_id' => null,
                'codigo_vendedor' => 'VEN-003',
                'nombre_completo' => 'Roberto Díaz',
                'telefono' => '+57-314-6789012',
                'email' => 'roberto.diaz@glazgroup.com',
                'zona_asignada' => 'Valle del Cauca',
                'comision_porcentaje' => 4.50,
                'activo' => true,
                'fecha_ingreso' => now()->subMonths(12)
            ]
        ];

        foreach ($vendedores as $vendedor) {
            Vendedor::create($vendedor);
        }
    }
}
