<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Proveedor;

class ProveedoresSeeder extends Seeder
{
    public function run(): void
    {
        $proveedores = [
            [
                'nombre_proveedor' => 'Distribuidora MediFarma S.A.',
                'nit' => '900456789-1',
                'contacto_nombre' => 'Ricardo Torres',
                'telefono' => '+57-315-1234567',
                'email' => 'ventas@medifarma.com',
                'direccion' => 'Calle 80 #45-30, Bogotá',
                'ciudad' => 'Bogotá',
                'activo' => true,
                'fecha_registro' => now()
            ],
            [
                'nombre_proveedor' => 'Insumos Médicos Colombia',
                'nit' => '890567890-2',
                'contacto_nombre' => 'Patricia Gómez',
                'telefono' => '+57-316-2345678',
                'email' => 'contacto@insumedicos.com',
                'direccion' => 'Carrera 40 #25-15, Medellín',
                'ciudad' => 'Medellín',
                'activo' => true,
                'fecha_registro' => now()
            ]
        ];

        foreach ($proveedores as $proveedor) {
            Proveedor::create($proveedor);
        }
    }
}
