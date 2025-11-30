<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Laboratorio;

class LaboratoriosSeeder extends Seeder
{
    public function run(): void
    {
        $laboratorios = [
            [
                'nombre_laboratorio' => 'Pfizer Inc.',
                'nit' => '900123456-1',
                'pais_origen' => 'Estados Unidos',
                'contacto_nombre' => 'John Smith',
                'telefono' => '+1-212-733-2323',
                'email' => 'contacto@pfizer.com',
                'direccion' => '235 East 42nd Street, New York',
                'activo' => true,
                'fecha_registro' => now()
            ],
            [
                'nombre_laboratorio' => 'Roche Colombia',
                'nit' => '860123789-2',
                'pais_origen' => 'Suiza',
                'contacto_nombre' => 'María González',
                'telefono' => '+57-1-6587000',
                'email' => 'contacto@roche.com.co',
                'direccion' => 'Calle 100 No. 19-54, Bogotá',
                'activo' => true,
                'fecha_registro' => now()
            ],
            [
                'nombre_laboratorio' => 'Novartis Pharma',
                'nit' => '890234567-3',
                'pais_origen' => 'Suiza',
                'contacto_nombre' => 'Carlos Mendez',
                'telefono' => '+41-61-324-1111',
                'email' => 'info@novartis.com',
                'direccion' => 'Basel, Switzerland',
                'activo' => true,
                'fecha_registro' => now()
            ],
            [
                'nombre_laboratorio' => 'Grifols S.A.',
                'nit' => '870345678-4',
                'pais_origen' => 'España',
                'contacto_nombre' => 'Ana López',
                'telefono' => '+34-93-571-0100',
                'email' => 'contacto@grifols.com',
                'direccion' => 'Barcelona, España',
                'activo' => true,
                'fecha_registro' => now()
            ]
        ];

        foreach ($laboratorios as $laboratorio) {
            Laboratorio::create($laboratorio);
        }
    }
}
