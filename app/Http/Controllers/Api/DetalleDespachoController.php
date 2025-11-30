<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetalleDespacho;
use Illuminate\Http\Request;

class DetalleDespachoController extends Controller
{
    public function porDespacho($despachoId)
    {
        $detalles = DetalleDespacho::with(['producto', 'lote', 'autorizacionInvima'])
            ->where('despacho_id', $despachoId)
            ->get();

        $resumen = [
            'detalles' => $detalles,
            'total_items' => $detalles->count(),
            'total_cantidad' => $detalles->sum('cantidad')
        ];

        return response()->json(['success' => true, 'data' => $resumen], 200);
    }

    public function porProducto($productoId)
    {
        $detalles = DetalleDespacho::with(['despacho.pedido', 'lote'])
            ->where('producto_id', $productoId)
            ->get();

        return response()->json([
            'success' => true, 
            'data' => $detalles, 
            'total_despachado' => $detalles->sum('cantidad'),
            'total' => $detalles->count()
        ], 200);
    }

    public function porLote($loteId)
    {
        $detalles = DetalleDespacho::with(['despacho', 'producto'])
            ->where('lote_id', $loteId)
            ->get();

        return response()->json([
            'success' => true, 
            'data' => $detalles, 
            'total_despachado' => $detalles->sum('cantidad'),
            'total_despachos' => $detalles->count()
        ], 200);
    }
}
