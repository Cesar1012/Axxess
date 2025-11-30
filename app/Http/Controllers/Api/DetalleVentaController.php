<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetalleVenta;
use Illuminate\Http\Request;

class DetalleVentaController extends Controller
{
    public function porVenta($ventaId)
    {
        $detalles = DetalleVenta::with(['producto', 'lote'])
            ->where('venta_id', $ventaId)
            ->get();

        $resumen = [
            'detalles' => $detalles,
            'total_items' => $detalles->count(),
            'total_cantidad' => $detalles->sum('cantidad'),
            'subtotal' => $detalles->sum('subtotal')
        ];

        return response()->json(['success' => true, 'data' => $resumen], 200);
    }

    public function porProducto($productoId, Request $request)
    {
        $query = DetalleVenta::with(['venta.clienteMarket', 'lote'])
            ->where('producto_id', $productoId);

        if ($request->has('fecha_desde')) {
            $query->whereHas('venta', function($q) use ($request) {
                $q->whereDate('fecha_venta', '>=', $request->fecha_desde);
            });
        }

        if ($request->has('fecha_hasta')) {
            $query->whereHas('venta', function($q) use ($request) {
                $q->whereDate('fecha_venta', '<=', $request->fecha_hasta);
            });
        }

        $detalles = $query->get();

        return response()->json([
            'success' => true, 
            'data' => $detalles, 
            'total_vendido' => $detalles->sum('cantidad'),
            'total' => $detalles->count()
        ], 200);
    }
}
