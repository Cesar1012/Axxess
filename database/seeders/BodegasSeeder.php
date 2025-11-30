<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bodega;

class BodegasSeeder extends Seeder
{
    public function run(): void
    {
        $bodegas = [
            [
                'nombre_bodega' => 'Bodega Principal Bogotá',
                'ubicacion_ciudad' => 'Bogotá',
                'direccion' => 'Transversal 93 No. 53-48',
                'activa' => true,
                'fecha_creacion' => now()
            ],
            [
                'nombre_bodega' => 'Bodega Medellín',
                'ubicacion_ciudad' => 'Medellín',
                'direccion' => 'Calle 10 Sur No. 48-62',
                'activa' => true,
                'fecha_creacion' => now()
            ],
            [
                'nombre_bodega' => 'Bodega Cali',
                'ubicacion_ciudad' => 'Cali',
                'direccion' => 'Carrera 100 No. 11-50',
                'activa' => true,
                'fecha_creacion' => now()
            ],
            [
                'nombre_bodega' => 'Bodega Barranquilla',
                'ubicacion_ciudad' => 'Barranquilla',
                'direccion' => 'Calle 72 No. 54-83',
                'activa' => true,
                'fecha_creacion' => now()
            ]
        ];

        foreach ($bodegas as $bodega) {
            Bodega::create($bodega);
        }
    }
}
