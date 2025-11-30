<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetallePedido;
use Illuminate\Http\Request;

class DetallePedidoController extends Controller
{
    public function porPedido($pedidoId)
    {
        $detalles = DetallePedido::with(['producto'])
            ->where('pedido_id', $pedidoId)
            ->get();

        $resumen = [
            'detalles' => $detalles,
            'total_items' => $detalles->count(),
            'total_cantidad' => $detalles->sum('cantidad'),
            'subtotal' => $detalles->sum('subtotal')
        ];

        return response()->json(['success' => true, 'data' => $resumen], 200);
    }

    public function porProducto($productoId)
    {
        $detalles = DetallePedido::with(['pedido.clienteMarket', 'pedido.paciente'])
            ->where('producto_id', $productoId)
            ->get();

        return response()->json(['success' => true, 'data' => $detalles, 'total' => $detalles->count()], 200);
    }
}
