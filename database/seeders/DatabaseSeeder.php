<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * ORDEN IMPORTANTE: Se ejecutan en orden de dependencias
     */
    public function run(): void
    {
        $this->call([
            // 1. Configuración y datos básicos (sin dependencias)
            ConfiguracionSistemaSeeder::class,
            CategoriasSeeder::class,
            BodegasSeeder::class,
            LaboratoriosSeeder::class,
            
            // 2. Usuarios y roles
            UsuariosSeeder::class,
            
            // 3. Módulo THERAPIES - Pacientes
            PacientesSeeder::class,
            
            // 4. Módulo MARKET - Clientes y Vendedores
            ClientesMarketSeeder::class,
            VendedoresSeeder::class,
            
            // 5. Proveedores
            ProveedoresSeeder::class,
            
            // 6. Productos (depende de Categorías)
            ProductosSeeder::class,
            
            // 7. Lotes (depende de Productos, Bodegas, Laboratorios)
            LotesSeeder::class,
            
            // 8. Autorizaciones y Licencias (depende de Pacientes y Productos)
            AutorizacionesInvimaSeeder::class,
            LicenciasImportacionSeeder::class,
            
            // 9. Pedidos (depende de Clientes, Pacientes, Vendedores, Usuarios)
            PedidosSeeder::class,
            
            // 10. Ventas (depende de Pedidos)
            VentasSeeder::class,
        ]);
        
        $this->command->info('✅ Base de datos poblada exitosamente!');
        $this->command->info('📊 Seeders ejecutados: 16');
        $this->command->newLine();
        $this->command->info('Credenciales de prueba:');
        $this->command->info('  Admin: admin@glazgroup.com / Admin2025!');
        $this->command->info('  Control: control@glazgroup.com / Control2025!');
        $this->command->info('  Ejecutivo: ejecutivo@glazgroup.com / Ejecutivo2025!');
    }
}
