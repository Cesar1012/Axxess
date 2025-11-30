<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Producto, Paciente, ClienteMarket, Pedido, Venta, Lote, Alerta, CuentaPorCobrar};
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/dashboard/general",
     *     tags={"Dashboard"},
     *     summary="Estadísticas generales del sistema",
     *     description="Retorna métricas globales del sistema AXXESS",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas generales",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="productos_activos", type="integer", example=7),
     *                 @OA\Property(property="productos_stock_bajo", type="integer", example=3),
     *                 @OA\Property(property="pacientes_activos", type="integer", example=3),
     *                 @OA\Property(property="clientes_activos", type="integer", example=3),
     *                 @OA\Property(property="pedidos_pendientes", type="integer", example=0),
     *                 @OA\Property(property="pedidos_hoy", type="integer", example=0),
     *                 @OA\Property(property="ventas_mes", type="number", example=0),
     *                 @OA\Property(property="ventas_hoy", type="number", example=0),
     *                 @OA\Property(property="lotes_proximos_vencer", type="integer", example=0),
     *                 @OA\Property(property="alertas_no_leidas", type="integer", example=0),
     *                 @OA\Property(property="alertas_criticas", type="integer", example=0),
     *                 @OA\Property(property="cuentas_vencidas", type="integer", example=0),
     *                 @OA\Property(property="valor_cuentas_por_cobrar", type="number", example=0)
     *             )
     *         )
     *     )
     * )
     */
    public function general()
    {
        $data = [
            'productos_activos' => Producto::where('activo', true)->count(),
            'productos_stock_bajo' => Producto::whereColumn('stock_actual', '<=', 'stock_minimo')->count(),
            'pacientes_activos' => Paciente::where('activo', true)->count(),
            'clientes_activos' => ClienteMarket::where('estado', 'activo')->count(),
            'pedidos_pendientes' => Pedido::where('estado', 'pendiente')->count(),
            'pedidos_hoy' => Pedido::whereDate('fecha_pedido', Carbon::today())->count(),
            'ventas_mes' => Venta::whereYear('fecha_venta', Carbon::now()->year)
                ->whereMonth('fecha_venta', Carbon::now()->month)
                ->sum('total'),
            'ventas_hoy' => Venta::whereDate('fecha_venta', Carbon::today())->sum('total'),
            'lotes_proximos_vencer' => Lote::where('fecha_vencimiento', '<=', Carbon::now()->addDays(30))
                ->where('fecha_vencimiento', '>=', Carbon::now())
                ->where('estado', 'disponible')
                ->count(),
            'alertas_no_leidas' => Alerta::where('leida', false)->count(),
            'alertas_criticas' => Alerta::where('prioridad', 'critica')->where('resuelta', false)->count(),
            'cuentas_vencidas' => CuentaPorCobrar::where('fecha_vencimiento', '<', Carbon::now())
                ->where('estado', 'vigente')
                ->count(),
            'valor_cuentas_por_cobrar' => CuentaPorCobrar::where('estado', 'vigente')->sum('saldo_pendiente')
        ];

        return response()->json(['success' => true, 'data' => $data], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/ventas",
     *     tags={"Dashboard"},
     *     summary="Estadísticas de ventas",
     *     description="Retorna métricas de ventas por período",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="periodo",
     *         in="query",
     *         description="Período de consulta",
     *         @OA\Schema(type="string", enum={"dia","semana","mes","año"}, example="mes")
     *     ),
     *     @OA\Response(response=200, description="Estadísticas de ventas")
     * )
     */
    public function ventas(Request $request)
    {
        $periodo = $request->get('periodo', 'mes');

        $query = Venta::query();

        switch ($periodo) {
            case 'dia':
                $query->whereDate('fecha_venta', Carbon::today());
                break;
            case 'semana':
                $query->whereBetween('fecha_venta', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'mes':
                $query->whereYear('fecha_venta', Carbon::now()->year)
                      ->whereMonth('fecha_venta', Carbon::now()->month);
                break;
            case 'año':
                $query->whereYear('fecha_venta', Carbon::now()->year);
                break;
        }

        $data = [
            'total_ventas' => $query->sum('total'),
            'cantidad_ventas' => $query->count(),
            'promedio_venta' => $query->count() > 0 ? $query->sum('total') / $query->count() : 0,
            'periodo' => $periodo
        ];

        return response()->json(['success' => true, 'data' => $data], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/inventario",
     *     tags={"Dashboard"},
     *     summary="Estadísticas de inventario",
     *     description="Retorna métricas del inventario actual",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Estadísticas de inventario")
     * )
     */
    public function inventario()
    {
        $data = [
            'total_productos' => Producto::where('activo', true)->count(),
            'valor_inventario' => Producto::where('activo', true)
                ->selectRaw('SUM(stock_actual * precio_compra) as total')
                ->value('total') ?? 0,
            'productos_sin_stock' => Producto::where('stock_actual', 0)->count(),
            'productos_stock_bajo' => Producto::whereColumn('stock_actual', '<=', 'stock_minimo')->count(),
            'lotes_disponibles' => Lote::where('estado', 'disponible')->count(),
            'lotes_vencidos' => Lote::where('fecha_vencimiento', '<', Carbon::now())->count(),
            'productos_por_vencer_30d' => Lote::where('fecha_vencimiento', '<=', Carbon::now()->addDays(30))
                ->where('fecha_vencimiento', '>=', Carbon::now())
                ->count()
        ];

        return response()->json(['success' => true, 'data' => $data], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/cartera",
     *     tags={"Dashboard"},
     *     summary="Estadísticas de cartera",
     *     description="Retorna métricas de cuentas por cobrar",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Estadísticas de cartera")
     * )
     */
    public function cartera()
    {
        $data = [
            'total_por_cobrar' => CuentaPorCobrar::where('estado', 'vigente')->sum('saldo_pendiente') ?? 0,
            'cuentas_vigentes' => CuentaPorCobrar::where('estado', 'vigente')
                ->where('fecha_vencimiento', '>=', Carbon::now())
                ->count(),
            'cuentas_vencidas' => CuentaPorCobrar::where('fecha_vencimiento', '<', Carbon::now())
                ->where('estado', 'vigente')
                ->count(),
            'valor_vencido' => CuentaPorCobrar::where('fecha_vencimiento', '<', Carbon::now())
                ->where('estado', 'vigente')
                ->sum('saldo_pendiente') ?? 0,
            'cuentas_pagadas_mes' => CuentaPorCobrar::where('estado', 'pagada')
                ->whereYear('fecha_emision', Carbon::now()->year)
                ->whereMonth('fecha_emision', Carbon::now()->month)
                ->count()
        ];

        return response()->json(['success' => true, 'data' => $data], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/therapies",
     *     tags={"Dashboard"},
     *     summary="Estadísticas módulo THERAPIES",
     *     description="Retorna métricas del módulo THERAPIES",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Estadísticas THERAPIES")
     * )
     */
    public function therapies()
    {
        $data = [
            'pacientes_activos' => Paciente::where('activo', true)->count(),
            'productos_therapies' => Producto::where('modulo', 'therapies')->where('activo', true)->count(),
            'entregas_mes' => \App\Models\EntregaPaciente::whereYear('fecha_entrega', Carbon::now()->year)
                ->whereMonth('fecha_entrega', Carbon::now()->month)
                ->count(),
            'autorizaciones_vigentes' => \App\Models\AutorizacionInvima::where('estado', 'vigente')->count(),
            'autorizaciones_proximas_vencer' => \App\Models\AutorizacionInvima::where('fecha_vencimiento', '<=', Carbon::now()->addDays(30))
                ->where('fecha_vencimiento', '>=', Carbon::now())
                ->where('estado', 'vigente')
                ->count()
        ];

        return response()->json(['success' => true, 'data' => $data], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/market",
     *     tags={"Dashboard"},
     *     summary="Estadísticas módulo MARKET",
     *     description="Retorna métricas del módulo MARKET",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Estadísticas MARKET")
     * )
     */
    public function market()
    {
        $data = [
            'clientes_activos' => ClienteMarket::where('estado', 'activo')->count(),
            'productos_market' => Producto::where('modulo', 'market')->where('activo', true)->count(),
            'pedidos_pendientes' => Pedido::where('tipo_pedido', 'market')->where('estado', 'pendiente')->count(),
            'pedidos_mes' => Pedido::where('tipo_pedido', 'market')
                ->whereYear('fecha_pedido', Carbon::now()->year)
                ->whereMonth('fecha_pedido', Carbon::now()->month)
                ->count(),
            'ventas_mes' => Venta::where('tipo_venta', 'market')
                ->whereYear('fecha_venta', Carbon::now()->year)
                ->whereMonth('fecha_venta', Carbon::now()->month)
                ->sum('total') ?? 0
        ];

        return response()->json(['success' => true, 'data' => $data], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/alertas",
     *     tags={"Dashboard"},
     *     summary="Resumen de alertas",
     *     description="Retorna métricas del sistema de alertas",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Resumen de alertas")
     * )
     */
    public function alertasResumen()
    {
        $data = [
            'total_alertas' => Alerta::count(),
            'no_leidas' => Alerta::where('leida', false)->count(),
            'criticas' => Alerta::where('prioridad', 'critica')->where('resuelta', false)->count(),
            'altas' => Alerta::where('prioridad', 'alta')->where('resuelta', false)->count(),
            'no_resueltas' => Alerta::where('resuelta', false)->count(),
            'por_tipo' => Alerta::where('resuelta', false)
                ->selectRaw('tipo_alerta, COUNT(*) as total')
                ->groupBy('tipo_alerta')
                ->get()
        ];

        return response()->json(['success' => true, 'data' => $data], 200);
    }
}
