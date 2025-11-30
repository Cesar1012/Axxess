<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\PacienteController;
use App\Http\Controllers\Api\ClienteMarketController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\VendedorController;
use App\Http\Controllers\Api\LaboratorioController;
use App\Http\Controllers\Api\BodegaController;
use App\Http\Controllers\Api\LoteController;
use App\Http\Controllers\Api\ProveedorController;
use App\Http\Controllers\Api\CompraController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\ConfiguracionSistemaController;
use App\Http\Controllers\Api\LicenciaImportacionController;
use App\Http\Controllers\Api\AutorizacionInvimaController;
use App\Http\Controllers\Api\ImportacionController;
use App\Http\Controllers\Api\DespachoController;
use App\Http\Controllers\Api\VentaController;
use App\Http\Controllers\Api\AlertaController;
use App\Http\Controllers\Api\CuentaPorCobrarController;
use App\Http\Controllers\Api\RecaudoController;
use App\Http\Controllers\Api\EntregaPacienteController;
use App\Http\Controllers\Api\RutaEntregaController;
use App\Http\Controllers\Api\MovimientoInventarioController;
use App\Http\Controllers\Api\AuditoriaController;
use App\Http\Controllers\ReporteController; // ⭐ CORREGIDO: Usar el original
use App\Http\Controllers\Api\ReporteGeneradoController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DetalleCompraController;
use App\Http\Controllers\Api\DetallePedidoController;
use App\Http\Controllers\Api\DetalleVentaController;
use App\Http\Controllers\Api\DetalleDespachoController;

/*
|--------------------------------------------------------------------------
| API Routes - Sistema AXXESS
|--------------------------------------------------------------------------
|
| Rutas de la API REST para el sistema AXXESS
| Base URL: /api
| Autenticación: Laravel Sanctum (Bearer Token)
|
*/

// ===================================
// RUTA DE BIENVENIDA (Pública)
// ===================================

Route::get('/', function () {
    return response()->json([
        'app' => 'Sistema AXXESS API',
        'company' => 'GLAZ GROUP S.A.S.',
        'version' => '1.0.0',
        'status' => 'active',
        'docs' => url('/api/documentation'),
        'endpoints' => [
            'auth' => '/api/auth/login',
            'usuarios' => '/api/usuarios',
            'productos' => '/api/productos',
            'therapies' => '/api/therapies/*',
            'market' => '/api/market/*'
        ]
    ]);
});

// ===================================
// RUTAS PÚBLICAS (Sin autenticación)
// ===================================

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

// ========================================
// RUTAS PROTEGIDAS (Requieren autenticación)
// ========================================

Route::middleware('auth:sanctum')->group(function () {
    
    // --- Autenticación ---
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    });

    // --- Usuarios ---
    Route::apiResource('usuarios', UsuarioController::class);
    
    // --- Productos e Inventario ---
    Route::apiResource('productos', ProductoController::class);
    Route::get('productos/buscar/{termino}', [ProductoController::class, 'buscar']);
    Route::get('productos/codigo-barras/{codigo}', [ProductoController::class, 'porCodigoBarras']);
    Route::get('productos/export/excel', [ProductoController::class, 'exportExcel']);
    Route::post('productos/import/excel', [ProductoController::class, 'importExcel']);
    
    // --- Laboratorios ---
    Route::apiResource('laboratorios', LaboratorioController::class);
    Route::get('laboratorios/nit/{nit}', [LaboratorioController::class, 'buscarPorNit']);
    
    // --- Bodegas ---
    Route::apiResource('bodegas', BodegaController::class);
    Route::get('bodegas/{id}/inventario', [BodegaController::class, 'inventario']);
    
    // --- Lotes ---
    Route::apiResource('lotes', LoteController::class);
    Route::get('lotes/proximos-vencer', [LoteController::class, 'proximosAVencer']);
    Route::get('lotes/vencidos', [LoteController::class, 'vencidos']);
    
    // --- Proveedores ---
    Route::apiResource('proveedores', ProveedorController::class);
    
    // --- Compras ---
    Route::apiResource('compras', CompraController::class);
    
    // --- Categorías ---
    Route::apiResource('categorias', CategoriaController::class);
    
    // --- Configuración del Sistema ---
    Route::apiResource('configuracion', ConfiguracionSistemaController::class);
    Route::get('configuracion/parametro/{parametro}', [ConfiguracionSistemaController::class, 'getByParametro']);
    
    // --- MÓDULO THERAPIES ---
    Route::prefix('therapies')->group(function () {
        Route::apiResource('pacientes', PacienteController::class);
        Route::get('pacientes/buscar/{termino}', [PacienteController::class, 'buscar']);
    });
    
    // --- LICENCIAS E IMPORTACIONES ---
    Route::apiResource('licencias-importacion', LicenciaImportacionController::class);
    Route::get('licencias-importacion/proximas-vencer', [LicenciaImportacionController::class, 'proximasAVencer']);
    
    Route::apiResource('autorizaciones-invima', AutorizacionInvimaController::class);
    Route::get('autorizaciones-invima/proximas-vencer', [AutorizacionInvimaController::class, 'proximasAVencer']);
    
    Route::apiResource('importaciones', ImportacionController::class);
    
    // --- DESPACHOS Y VENTAS ---
    Route::apiResource('despachos', DespachoController::class);
    Route::apiResource('ventas', VentaController::class);
    Route::get('ventas/export/excel', [VentaController::class, 'exportExcel']);
    
    // --- ENTREGAS PACIENTES ---
    Route::apiResource('entregas-pacientes', EntregaPacienteController::class);
    Route::get('entregas-pacientes/paciente/{pacienteId}', [EntregaPacienteController::class, 'porPaciente']);
    
    // --- RUTAS DE ENTREGA ---
    Route::apiResource('rutas-entrega', RutaEntregaController::class);
    Route::post('rutas-entrega/{id}/agregar-despacho', [RutaEntregaController::class, 'agregarDespacho']);
    
    // --- CUENTAS POR COBRAR Y RECAUDOS ---
    Route::apiResource('cuentas-por-cobrar', CuentaPorCobrarController::class);
    Route::get('cuentas-por-cobrar/vencidas', [CuentaPorCobrarController::class, 'vencidas']);
    Route::get('cuentas-por-cobrar/por-vencer', [CuentaPorCobrarController::class, 'porVencer']);
    
    Route::apiResource('recaudos', RecaudoController::class);
    Route::get('recaudos/vendedor/{vendedorId}', [RecaudoController::class, 'porVendedor']);
    
    // --- ALERTAS ---
    Route::apiResource('alertas', AlertaController::class);
    Route::patch('alertas/{id}/marcar-leida', [AlertaController::class, 'marcarLeida']);
    Route::patch('alertas/{id}/marcar-resuelta', [AlertaController::class, 'marcarResuelta']);
    Route::get('alertas/no-leidas', [AlertaController::class, 'noLeidas']);
    Route::get('alertas/prioridad/{prioridad}', [AlertaController::class, 'porPrioridad']);
    
    // --- MOVIMIENTOS DE INVENTARIO (KARDEX) ---
    Route::apiResource('movimientos-inventario', MovimientoInventarioController::class)->only(['index', 'store', 'show']);
    Route::get('movimientos-inventario/kardex/producto/{productoId}', [MovimientoInventarioController::class, 'kardexProducto']);
    Route::get('movimientos-inventario/kardex/lote/{loteId}', [MovimientoInventarioController::class, 'kardexLote']);
    Route::get('movimientos-inventario/resumen-tipo', [MovimientoInventarioController::class, 'resumenPorTipo']);
    
    // --- AUDITORÍA ---
    Route::get('auditoria', [AuditoriaController::class, 'index']);
    Route::get('auditoria/{id}', [AuditoriaController::class, 'show']);
    Route::get('auditoria/usuario/{usuarioId}', [AuditoriaController::class, 'porUsuario']);
    Route::get('auditoria/modulo/{modulo}', [AuditoriaController::class, 'porModulo']);
    Route::get('auditoria/resumen-acciones', [AuditoriaController::class, 'resumenAcciones']);
    
    // --- REPORTES DINÁMICOS (ANTES del apiResource) ---
    Route::prefix('reportes')->group(function () {
        Route::get('inventario', [ReporteController::class, 'inventario']);
        Route::get('vencimientos', [ReporteController::class, 'vencimientos']);
        Route::get('autorizaciones-invima', [ReporteController::class, 'autorizacionesInvima']);
        Route::get('ventas', [ReporteController::class, 'ventas']);
        Route::get('despachos', [ReporteController::class, 'despachos']);
        Route::get('importaciones', [ReporteController::class, 'importaciones']);
        
        // Exportación
        Route::post('export/pdf', [ReporteController::class, 'exportPDF']);
        Route::post('export/excel', [ReporteController::class, 'exportExcel']);
        Route::post('export/csv', [ReporteController::class, 'exportCSV']);
    });
    
    // --- REPORTES GENERADOS (CRUD histórico) ---
    Route::apiResource('reportes', ReporteGeneradoController::class);
    Route::get('reportes/usuario/{usuarioId}', [ReporteGeneradoController::class, 'porUsuario']);
    Route::get('reportes/resumen-tipo', [ReporteGeneradoController::class, 'resumenPorTipo']);
    
    // --- DASHBOARD ---
    Route::prefix('dashboard')->group(function () {
        Route::get('general', [DashboardController::class, 'general']);
        Route::get('ventas', [DashboardController::class, 'ventas']);
        Route::get('inventario', [DashboardController::class, 'inventario']);
        Route::get('cartera', [DashboardController::class, 'cartera']);
        Route::get('therapies', [DashboardController::class, 'therapies']);
        Route::get('market', [DashboardController::class, 'market']);
        Route::get('alertas', [DashboardController::class, 'alertasResumen']);
    });
    
    // --- DETALLES (CONSULTAS) ---
    Route::get('detalles/compras/{compraId}', [DetalleCompraController::class, 'porCompra']);
    Route::get('detalles/compras/producto/{productoId}', [DetalleCompraController::class, 'porProducto']);
    
    Route::get('detalles/pedidos/{pedidoId}', [DetallePedidoController::class, 'porPedido']);
    Route::get('detalles/pedidos/producto/{productoId}', [DetallePedidoController::class, 'porProducto']);
    
    Route::get('detalles/ventas/{ventaId}', [DetalleVentaController::class, 'porVenta']);
    Route::get('detalles/ventas/producto/{productoId}', [DetalleVentaController::class, 'porProducto']);
    
    Route::get('detalles/despachos/{despachoId}', [DetalleDespachoController::class, 'porDespacho']);
    Route::get('detalles/despachos/producto/{productoId}', [DetalleDespachoController::class, 'porProducto']);
    Route::get('detalles/despachos/lote/{loteId}', [DetalleDespachoController::class, 'porLote']);
    
    // --- MÓDULO MARKET ---
    Route::prefix('market')->group(function () {
        Route::apiResource('clientes', ClienteMarketController::class);
        Route::apiResource('pedidos', PedidoController::class);
        Route::apiResource('vendedores', VendedorController::class);
        
        // Rutas adicionales para pedidos
        Route::get('pedidos/{id}/detalles', [PedidoController::class, 'verDetalles']);
        Route::patch('pedidos/{id}/estado', [PedidoController::class, 'cambiarEstado']);
        
        // Rutas adicionales para clientes
        Route::get('clientes/{id}/pedidos', [ClienteMarketController::class, 'pedidos']);
        Route::get('clientes/{id}/cartera', [ClienteMarketController::class, 'estadoCartera']);
    });

    // --- ADMINISTRACIÓN ---
    Route::prefix('admin')->group(function () {
        Route::apiResource('usuarios', UsuarioController::class);
        Route::patch('usuarios/{id}/toggle-status', [UsuarioController::class, 'toggleStatus']);
        Route::get('usuarios/stats', [UsuarioController::class, 'stats']);
    });

    // --- REPORTES ---
    Route::prefix('reportes')->group(function () {
        Route::get('inventario', [ReporteController::class, 'inventario']);
        Route::get('vencimientos', [ReporteController::class, 'vencimientos']);
        Route::get('autorizaciones-invima', [ReporteController::class, 'autorizacionesInvima']);
        Route::get('ventas', [ReporteController::class, 'ventas']);
        Route::get('despachos', [ReporteController::class, 'despachos']);
        Route::get('importaciones', [ReporteController::class, 'importaciones']);
        
        // Exportación (preparado para futuro)
        Route::post('export/pdf', [ReporteController::class, 'exportPDF']);
        Route::post('export/excel', [ReporteController::class, 'exportExcel']);
        Route::post('export/csv', [ReporteController::class, 'exportCSV']);
    });

});

// ===================================
// RUTA DE VERIFICACIÓN DE SALUD
// ===================================

Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now()->toIso8601String(),
        'database' => 'connected'
    ]);
});
