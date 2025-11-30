<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetalleCompra;
use Illuminate\Http\Request;

class DetalleCompraController extends Controller
{
    public function porCompra($compraId)
    {
        $detalles = DetalleCompra::with(['producto'])
            ->where('compra_id', $compraId)
            ->get();

        $resumen = [
            'detalles' => $detalles,
            'total_items' => $detalles->count(),
            'subtotal' => $detalles->sum('subtotal')
        ];

        return response()->json(['success' => true, 'data' => $resumen], 200);
    }

    public function porProducto($productoId)
    {
        $detalles = DetalleCompra::with(['compra.proveedor'])
            ->where('producto_id', $productoId)
            ->get();

        return response()->json(['success' => true, 'data' => $detalles, 'total' => $detalles->count()], 200);
    }
}
