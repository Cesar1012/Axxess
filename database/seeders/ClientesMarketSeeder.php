<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClienteMarket;

class ClientesMarketSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = [
            [
                'tipo_cliente' => 'Droguería',
                'razon_social' => 'Droguería La Salud S.A.S.',
                'nit' => '900123456-7',
                'contacto_nombre' => 'Pedro González',
                'telefono' => '+57-310-9876543',
                'email' => 'compras@droguerialasalud.com',
                'direccion_entrega' => 'Calle 50 #12-34, Bogotá',
                'ciudad' => 'Bogotá',
                'persona_contacto' => 'Pedro González',
                'sucursal' => 'Principal',
                'vendedor_asignado_id' => null,
                'limite_credito' => 50000000.00,
                'dias_credito' => 30,
                'cartera_al_dia' => true,
                'estado' => 'activo',
                'fecha_registro' => now()
            ],
            [
                'tipo_cliente' => 'IPS',
                'razon_social' => 'IPS MedCare Ltda.',
                'nit' => '890234567-8',
                'contacto_nombre' => 'Ana Martínez',
                'telefono' => '+57-311-8765432',
                'email' => 'compras@ipsmedcare.com',
                'direccion_entrega' => 'Carrera 15 #90-20, Medellín',
                'ciudad' => 'Medellín',
                'persona_contacto' => 'Ana Martínez',
                'sucursal' => 'Sede Norte',
                'vendedor_asignado_id' => null,
                'limite_credito' => 80000000.00,
                'dias_credito' => 45,
                'cartera_al_dia' => true,
                'estado' => 'activo',
                'fecha_registro' => now()
            ],
            [
                'tipo_cliente' => 'Hospital',
                'razon_social' => 'Hospital San José E.S.E.',
                'nit' => '899345678-9',
                'contacto_nombre' => 'Luis Ramírez',
                'telefono' => '+57-312-7654321',
                'email' => 'suministros@hospitalsanjose.gov.co',
                'direccion_entrega' => 'Avenida 5 #30-80, Cali',
                'ciudad' => 'Cali',
                'persona_contacto' => 'Luis Ramírez',
                'sucursal' => 'Principal',
                'vendedor_asignado_id' => null,
                'limite_credito' => 150000000.00,
                'dias_credito' => 60,
                'cartera_al_dia' => true,
                'estado' => 'activo',
                'fecha_registro' => now()
            ]
        ];

        foreach ($clientes as $cliente) {
            ClienteMarket::create($cliente);
        }
    }
}
