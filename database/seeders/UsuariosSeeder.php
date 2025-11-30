<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;

class UsuariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar tabla (MySQL compatible)
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Usuario::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. ADMINISTRADOR
        Usuario::create([
            'nombre_completo' => 'Administrador Sistema',
            'email' => 'admin@glazgroup.com',
            'password' => Hash::make('Admin2025!'),
            'rol' => 'administrador',
            'modulo_acceso' => 'ambos',
            'telefono' => '3001234567',
            'activo' => true,
            'fecha_creacion' => now(),
        ]);

        // 2. CONTROL
        Usuario::create([
            'nombre_completo' => 'María Control Operativo',
            'email' => 'control@glazgroup.com',
            'password' => Hash::make('Control2025!'),
            'rol' => 'control',
            'modulo_acceso' => 'ambos',
            'telefono' => '3009876543',
            'activo' => true,
            'fecha_creacion' => now(),
        ]);

        // 3. CONSULTA
        Usuario::create([
            'nombre_completo' => 'Carlos Consulta',
            'email' => 'consulta@glazgroup.com',
            'password' => Hash::make('Consulta2025!'),
            'rol' => 'consulta',
            'modulo_acceso' => 'ambos',
            'telefono' => '3005554321',
            'activo' => true,
            'fecha_creacion' => now(),
        ]);

        // 4. EJECUTIVO COMERCIAL (MARKET)
        Usuario::create([
            'nombre_completo' => 'Ana Ejecutiva Market',
            'email' => 'comercial@glazgroup.com',
            'password' => Hash::make('Market2025!'),
            'rol' => 'ejecutivo_comercial',
            'modulo_acceso' => 'axxess_market',
            'telefono' => '3008889999',
            'activo' => true,
            'fecha_creacion' => now(),
        ]);

        // 5. EJECUTIVO THERAPIES
        Usuario::create([
            'nombre_completo' => 'Luis Ejecutivo Therapies',
            'email' => 'therapies@glazgroup.com',
            'password' => Hash::make('Therapies2025!'),
            'rol' => 'control',
            'modulo_acceso' => 'axxess_therapies',
            'telefono' => '3007778888',
            'activo' => true,
            'fecha_creacion' => now(),
        ]);

        // 6. USUARIO INACTIVO (para pruebas)
        Usuario::create([
            'nombre_completo' => 'Usuario Inactivo',
            'email' => 'inactivo@glazgroup.com',
            'password' => Hash::make('Inactivo2025!'),
            'rol' => 'consulta',
            'modulo_acceso' => 'ambos',
            'telefono' => '3006667777',
            'activo' => false,
            'fecha_creacion' => now(),
        ]);

        $this->command->info('✅ 6 usuarios creados exitosamente');
        $this->command->info('');
        $this->command->info('📋 CREDENCIALES:');
        $this->command->info('');
        $this->command->info('1. ADMINISTRADOR');
        $this->command->info('   Email: admin@glazgroup.com');
        $this->command->info('   Pass: Admin2025!');
        $this->command->info('');
        $this->command->info('2. CONTROL');
        $this->command->info('   Email: control@glazgroup.com');
        $this->command->info('   Pass: Control2025!');
        $this->command->info('');
        $this->command->info('3. CONSULTA');
        $this->command->info('   Email: consulta@glazgroup.com');
        $this->command->info('   Pass: Consulta2025!');
        $this->command->info('');
        $this->command->info('4. EJECUTIVO COMERCIAL (MARKET)');
        $this->command->info('   Email: comercial@glazgroup.com');
        $this->command->info('   Pass: Market2025!');
        $this->command->info('');
        $this->command->info('5. EJECUTIVO THERAPIES');
        $this->command->info('   Email: therapies@glazgroup.com');
        $this->command->info('   Pass: Therapies2025!');
    }
}
