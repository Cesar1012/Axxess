<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Lote;
use App\Models\AutorizacionInvima;
use App\Models\Venta;
use App\Models\Despacho;
use App\Models\Importacion;
use App\Exports\ReportesExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PDF;
use Excel;

class ReporteController extends Controller
{
    /**
     * Reporte de Inventario
     * GET /api/reportes/inventario
     */
    public function inventario(Request $request)
    {
        $query = Producto::with(['categoria', 'lotes' => function($q) {
            $q->where('estado', 'disponible');
        }]);

        // Filtros opcionales
        if ($request->has('bodega_id')) {
            $query->whereHas('lotes', function($q) use ($request) {
                $q->where('bodega_id', $request->bodega_id);
            });
        }

        if ($request->has('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        if ($request->has('modulo')) {
            $query->where('modulo', $request->modulo);
        }

        $productos = $query->where('activo', true)->get();

        $reporte = [
            'productos' => $productos->map(function($producto) {
                return [
                    'codigo' => $producto->codigo_producto,
                    'nombre' => $producto->nombre,
                    'stock_actual' => $producto->stock_actual,
                    'stock_minimo' => $producto->stock_minimo,
                    'lotes_vigentes' => $producto->lotes->count(),
                    'valor_inventario' => $producto->stock_actual * $producto->precio_compra,
                ];
            }),
            'total_productos' => $productos->count(),
            'valor_total' => $productos->sum(function($p) {
                return $p->stock_actual * $p->precio_compra;
            }),
            'alertas_stock_bajo' => $productos->filter(function($p) {
                return $p->stock_actual <= $p->stock_minimo;
            })->count(),
        ];

        return response()->json($reporte);
    }

    /**
     * Reporte de Vencimientos
     * GET /api/reportes/vencimientos
     */
    public function vencimientos(Request $request)
    {
        $dias = (int) $request->input('dias', 90); // ⭐ FIX: Cast a entero
        $fechaLimite = Carbon::now()->addDays($dias);

        $query = Lote::with(['producto', 'bodega'])
            ->where('estado', 'disponible')
            ->where('cantidad_actual', '>', 0)
            ->where('fecha_vencimiento', '<=', $fechaLimite)
            ->where('fecha_vencimiento', '>=', Carbon::now());

        if ($request->has('bodega_id')) {
            $query->where('bodega_id', $request->bodega_id);
        }

        $lotes = $query->orderBy('fecha_vencimiento', 'asc')->get();

        $reporte = [
            'proximos_vencer' => $lotes->map(function($lote) {
                return [
                    'producto' => $lote->producto->nombre,
                    'lote' => $lote->numero_lote,
                    'fecha_vencimiento' => $lote->fecha_vencimiento,
                    'dias_restantes' => Carbon::now()->diffInDays($lote->fecha_vencimiento),
                    'cantidad' => $lote->cantidad_actual,
                ];
            }),
            'total_lotes' => $lotes->count(),
            'valor_riesgo' => $lotes->sum(function($lote) {
                return $lote->cantidad_actual * $lote->producto->precio_compra;
            }),
        ];

        return response()->json($reporte);
    }

    /**
     * Reporte de Autorizaciones INVIMA
     * GET /api/reportes/autorizaciones-invima
     */
    public function autorizacionesInvima(Request $request)
    {
        $query = AutorizacionInvima::with(['paciente', 'producto']);

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('paciente_id')) {
            $query->where('paciente_id', $request->paciente_id);
        }

        $autorizaciones = $query->get();

        // Calcular estados
        $vigentes = $autorizaciones->filter(function($a) {
            return $a->estado === 'vigente' && $a->fecha_vencimiento >= Carbon::now();
        });

        $proximasVencer = $autorizaciones->filter(function($a) {
            return $a->estado === 'vigente' && 
                   $a->fecha_vencimiento >= Carbon::now() && 
                   $a->fecha_vencimiento <= Carbon::now()->addDays(30);
        });

        $vencidas = $autorizaciones->filter(function($a) {
            return $a->estado === 'vencida' || $a->fecha_vencimiento < Carbon::now();
        });

        $reporte = [
            'vigentes' => $vigentes->count(),
            'proximas_vencer' => $proximasVencer->count(),
            'vencidas' => $vencidas->count(),
            'saldo_pendiente_total' => $autorizaciones->sum('saldo_pendiente'),
            'detalle' => $autorizaciones->map(function($a) {
                return [
                    'paciente' => $a->paciente->nombre_completo,
                    'producto' => $a->producto->nombre,
                    'cantidad_autorizada' => $a->cantidad_autorizada,
                    'saldo_pendiente' => $a->saldo_pendiente,
                    'fecha_vencimiento' => $a->fecha_vencimiento,
                ];
            }),
        ];

        return response()->json($reporte);
    }

    /**
     * Reporte de Ventas
     * GET /api/reportes/ventas
     */
    public function ventas(Request $request)
    {
        $query = Venta::query();

        // Filtros de fecha
        if ($request->has('fecha_inicio')) {
            $query->whereDate('fecha_venta', '>=', $request->fecha_inicio);
        }

        if ($request->has('fecha_fin')) {
            $query->whereDate('fecha_venta', '<=', $request->fecha_fin);
        }

        // Filtro por módulo
        if ($request->has('modulo')) {
            $query->where('tipo_venta', $request->modulo);
        }

        // Filtro por vendedor
        if ($request->has('vendedor_id')) {
            $query->where('vendedor_id', $request->vendedor_id);
        }

        $ventas = $query->get();

        // Agrupar por mes
        $porMes = $ventas->groupBy(function($venta) {
            return Carbon::parse($venta->fecha_venta)->format('Y-m');
        })->map(function($mes, $key) {
            return [
                'mes' => $key,
                'monto' => $mes->sum('total'),
                'pedidos' => $mes->count(),
            ];
        })->values();

        $reporte = [
            'periodo' => $request->input('fecha_inicio', 'Todo') . ' - ' . $request->input('fecha_fin', 'Ahora'),
            'total_ventas' => $ventas->sum('total'),
            'total_pedidos' => $ventas->count(),
            'ventas_therapies' => $ventas->where('tipo_venta', 'therapies')->sum('total'),
            'ventas_market' => $ventas->where('tipo_venta', 'market')->sum('total'),
            'por_mes' => $porMes,
        ];

        return response()->json($reporte);
    }

    /**
     * Reporte de Despachos
     * GET /api/reportes/despachos
     */
    public function despachos(Request $request)
    {
        $query = Despacho::query();

        if ($request->has('fecha_inicio')) {
            $query->whereDate('fecha_despacho', '>=', $request->fecha_inicio);
        }

        if ($request->has('fecha_fin')) {
            $query->whereDate('fecha_despacho', '<=', $request->fecha_fin);
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        $despachos = $query->get();

        // Agrupar por zona si existe el campo direccion_entrega
        $porZona = $despachos->groupBy(function($despacho) {
            // Extraer ciudad/zona de la dirección (simplificado)
            $direccion = $despacho->direccion_entrega ?? 'Sin zona';
            return explode(',', $direccion)[0] ?? 'Sin zona';
        })->map(function($zona, $key) {
            return [
                'zona' => $key,
                'cantidad' => $zona->count(),
            ];
        })->values();

        $reporte = [
            'total_despachos' => $despachos->count(),
            'completados' => $despachos->where('estado', 'entregado')->count(),
            'pendientes' => $despachos->whereIn('estado', ['preparado', 'en_ruta'])->count(),
            'por_zona' => $porZona,
        ];

        return response()->json($reporte);
    }

    /**
     * Reporte de Importaciones
     * GET /api/reportes/importaciones
     */
    public function importaciones(Request $request)
    {
        $query = Importacion::with(['laboratorio', 'licenciaImportacion']);

        if ($request->has('fecha_inicio')) {
            $query->whereDate('fecha_importacion', '>=', $request->fecha_inicio);
        }

        if ($request->has('fecha_fin')) {
            $query->whereDate('fecha_importacion', '<=', $request->fecha_fin);
        }

        if ($request->has('laboratorio_id')) {
            $query->where('laboratorio_id', $request->laboratorio_id);
        }

        $importaciones = $query->get();

        // Agrupar por laboratorio
        $porLaboratorio = $importaciones->groupBy('laboratorio_id')->map(function($grupo) {
            $laboratorio = $grupo->first()->laboratorio;
            return [
                'laboratorio' => $laboratorio ? $laboratorio->nombre_laboratorio : 'Sin laboratorio',
                'cantidad' => $grupo->count(),
                'valor' => $grupo->sum('valor_total'),
            ];
        })->values();

        // Contar licencias activas
        $licenciasActivas = DB::table('licencias_importacion')
            ->where('estado', 'vigente')
            ->count();

        $reporte = [
            'total_importaciones' => $importaciones->count(),
            'valor_total' => $importaciones->sum('valor_total'),
            'por_laboratorio' => $porLaboratorio,
            'licencias_activas' => $licenciasActivas,
        ];

        return response()->json($reporte);
    }

    /**
     * Exportar reporte a PDF
     * POST /api/reportes/export/pdf
     */
    public function exportPDF(Request $request)
    {
        $request->validate([
            'tipo_reporte' => 'required|string|in:inventario,vencimientos,autorizacionesInvima,ventas,despachos,importaciones'
        ]);
        
        $tipo = $request->tipo_reporte;
        
        // Obtener datos del reporte
        $tempRequest = new Request($request->parametros ?? []);
        $data = $this->$tipo($tempRequest)->getData(true);
        
        // Generar PDF
        $pdf = PDF::loadView("reportes.{$tipo}-pdf", $data);
        
        return $pdf->download("reporte_{$tipo}_" . date('Y-m-d') . ".pdf");
    }

    /**
     * Exportar reporte a Excel
     * POST /api/reportes/export/excel
     */
    public function exportExcel(Request $request)
    {
        $request->validate([
            'tipo_reporte' => 'required|string|in:inventario,vencimientos,autorizacionesInvima,ventas,despachos,importaciones'
        ]);
        
        $tipo = $request->tipo_reporte;
        
        // Obtener datos del reporte
        $tempRequest = new Request($request->parametros ?? []);
        $data = $this->$tipo($tempRequest)->getData(true);
        
        return Excel::download(
            new ReportesExport($tipo, $data), 
            "reporte_{$tipo}_" . date('Y-m-d') . ".xlsx"
        );
    }

    /**
     * Exportar reporte a CSV
     * POST /api/reportes/export/csv
     */
    public function exportCSV(Request $request)
    {
        $request->validate([
            'tipo_reporte' => 'required|string|in:inventario,vencimientos,autorizacionesInvima,ventas,despachos,importaciones'
        ]);
        
        $tipo = $request->tipo_reporte;
        
        // Obtener datos del reporte
        $tempRequest = new Request($request->parametros ?? []);
        $data = $this->$tipo($tempRequest)->getData(true);
        
        return Excel::download(
            new ReportesExport($tipo, $data), 
            "reporte_{$tipo}_" . date('Y-m-d') . ".csv",
            \Maatwebsite\Excel\Excel::CSV
        );
    }
}
