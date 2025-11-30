<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\DetallePedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $query = Pedido::with(['clienteMarket', 'paciente', 'vendedor', 'detalles.producto']);

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('tipo_pedido')) {
            $query->where('tipo_pedido', $request->tipo_pedido);
        }

        if ($request->has('cliente_market_id')) {
            $query->where('cliente_market_id', $request->cliente_market_id);
        }

        $perPage = $request->get('per_page', 15);
        $pedidos = $query->orderBy('fecha_pedido', 'desc')->paginate($perPage);

        return response()->json($pedidos, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'numero_pedido' => 'required|string|max:50|unique:pedidos',
            'tipo_pedido' => 'nullable|string|max:20',
            'cliente_market_id' => 'nullable|exists:clientes_market,id',
            'paciente_id' => 'nullable|exists:pacientes,id',
            'vendedor_id' => 'nullable|exists:vendedores,id',
            'usuario_registro_id' => 'nullable|exists:usuarios,id',
            'fecha_entrega_programada' => 'nullable|date',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $subtotal = 0;
            foreach ($request->detalles as $detalle) {
                $subtotal += $detalle['cantidad'] * $detalle['precio_unitario'];
            }

            $impuesto = $subtotal * 0.19;
            $total = $subtotal + $impuesto;

            $pedido = Pedido::create([
                'numero_pedido' => $request->numero_pedido,
                'tipo_pedido' => $request->tipo_pedido,
                'cliente_market_id' => $request->cliente_market_id,
                'paciente_id' => $request->paciente_id,
                'vendedor_id' => $request->vendedor_id,
                'usuario_registro_id' => $request->usuario_registro_id ?? auth()->id(),
                'fecha_entrega_programada' => $request->fecha_entrega_programada,
                'subtotal' => $subtotal,
                'impuesto' => $impuesto,
                'descuento' => $request->descuento ?? 0,
                'total' => $total - ($request->descuento ?? 0),
                'estado' => 'pendiente',
            ]);

            foreach ($request->detalles as $detalle) {
                DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $detalle['producto_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $detalle['cantidad'] * $detalle['precio_unitario'],
                    'codigo_paciente' => $detalle['codigo_paciente'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Pedido creado exitosamente',
                'pedido' => $pedido->load('detalles.producto')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear el pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $pedido = Pedido::with(['clienteMarket', 'paciente', 'vendedor', 'detalles.producto', 'despachos'])->find($id);

        if (!$pedido) {
            return response()->json(['message' => 'Pedido no encontrado'], 404);
        }

        return response()->json($pedido, 200);
    }

    public function update(Request $request, $id)
    {
        $pedido = Pedido::find($id);

        if (!$pedido) {
            return response()->json(['message' => 'Pedido no encontrado'], 404);
        }

        $pedido->update($request->only([
            'fecha_entrega_programada',
            'observaciones',
            'estado'
        ]));

        return response()->json([
            'message' => 'Pedido actualizado exitosamente',
            'pedido' => $pedido
        ], 200);
    }

    public function destroy($id)
    {
        $pedido = Pedido::find($id);

        if (!$pedido) {
            return response()->json(['message' => 'Pedido no encontrado'], 404);
        }

        if ($pedido->estado !== 'pendiente') {
            return response()->json([
                'message' => 'Solo se pueden cancelar pedidos pendientes'
            ], 400);
        }

        $pedido->update(['estado' => 'cancelado']);

        return response()->json(['message' => 'Pedido cancelado exitosamente'], 200);
    }

    public function verDetalles($id)
    {
        $pedido = Pedido::with('detalles.producto.lotes')->find($id);

        if (!$pedido) {
            return response()->json(['message' => 'Pedido no encontrado'], 404);
        }

        return response()->json([
            'pedido' => $pedido,
            'detalles' => $pedido->detalles
        ], 200);
    }

    public function cambiarEstado(Request $request, $id)
    {
        $pedido = Pedido::find($id);

        if (!$pedido) {
            return response()->json(['message' => 'Pedido no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'estado' => 'required|in:pendiente,aprobado,en_preparacion,despachado,entregado,cancelado'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $pedido->update(['estado' => $request->estado]);

        return response()->json([
            'message' => 'Estado del pedido actualizado',
            'pedido' => $pedido
        ], 200);
    }
}
