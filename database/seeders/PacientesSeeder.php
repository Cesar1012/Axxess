<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Paciente;

class PacientesSeeder extends Seeder
{
    public function run(): void
    {
        $pacientes = [
            [
                'codigo_paciente' => 'PAC-001',
                'nombre_completo' => 'Juan Pérez García',
                'tipo_documento' => 'CC',
                'numero_documento' => '1012345678',
                'eps_asegurador' => 'Nueva EPS',
                'telefono' => '+57-300-1234567',
                'email' => 'juan.perez@email.com',
                'direccion_entrega' => 'Calle 45 #23-15, Bogotá',
                'zona_cobertura' => 'Bogotá Norte',
                'requiere_cita_entrega' => true,
                'observaciones' => 'Paciente con movilidad reducida',
                'activo' => true,
                'fecha_registro' => now()
            ],
            [
                'codigo_paciente' => 'PAC-002',
                'nombre_completo' => 'María Rodríguez López',
                'tipo_documento' => 'CC',
                'numero_documento' => '1023456789',
                'eps_asegurador' => 'Sanitas',
                'telefono' => '+57-301-2345678',
                'email' => 'maria.rodriguez@email.com',
                'direccion_entrega' => 'Carrera 70 #80-20, Medellín',
                'zona_cobertura' => 'Medellín Centro',
                'requiere_cita_entrega' => false,
                'observaciones' => null,
                'activo' => true,
                'fecha_registro' => now()
            ],
            [
                'codigo_paciente' => 'PAC-003',
                'nombre_completo' => 'Carlos Martínez Sánchez',
                'tipo_documento' => 'CC',
                'numero_documento' => '1034567890',
                'eps_asegurador' => 'Sura',
                'telefono' => '+57-302-3456789',
                'email' => 'carlos.martinez@email.com',
                'direccion_entrega' => 'Avenida 6 #25-50, Cali',
                'zona_cobertura' => 'Cali Sur',
                'requiere_cita_entrega' => true,
                'observaciones' => 'Horario preferido: mañanas',
                'activo' => true,
                'fecha_registro' => now()
            ]
        ];

        foreach ($pacientes as $paciente) {
            Paciente::create($paciente);
        }
    }
}
