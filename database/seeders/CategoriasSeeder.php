<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriasSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            // THERAPIES
            [
                'nombre' => 'Medicamentos de Alto Costo',
                'tipo_modulo' => 'therapies',
                'descripcion' => 'Medicamentos especializados de alto costo para enfermedades huérfanas',
                'activa' => true
            ],
            [
                'nombre' => 'Biotecnológicos',
                'tipo_modulo' => 'therapies',
                'descripcion' => 'Productos biotecnológicos y terapias avanzadas',
                'activa' => true
            ],
            [
                'nombre' => 'Inmunoterapias',
                'tipo_modulo' => 'therapies',
                'descripcion' => 'Medicamentos para el sistema inmunológico',
                'activa' => true
            ],
            // MARKET
            [
                'nombre' => 'Medicamentos Generales',
                'tipo_modulo' => 'market',
                'descripcion' => 'Medicamentos de uso común y venta masiva',
                'activa' => true
            ],
            [
                'nombre' => 'Dispositivos Médicos',
                'tipo_modulo' => 'market',
                'descripcion' => 'Equipos y dispositivos médicos',
                'activa' => true
            ],
            [
                'nombre' => 'Material Quirúrgico',
                'tipo_modulo' => 'market',
                'descripcion' => 'Insumos y materiales para cirugía',
                'activa' => true
            ],
            // AMBOS
            [
                'nombre' => 'Suplementos',
                'tipo_modulo' => 'ambos',
                'descripcion' => 'Suplementos nutricionales y vitaminas',
                'activa' => true
            ],
            [
                'nombre' => 'Insumos de Cadena de Frío',
                'tipo_modulo' => 'ambos',
                'descripcion' => 'Neveras portátiles, geles refrigerantes',
                'activa' => true
            ]
        ];

        foreach ($categorias as $categoria) {
            Categoria::create($categoria);
        }
    }
}
