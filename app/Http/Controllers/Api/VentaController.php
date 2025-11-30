<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Exports\VentasExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        $query = Venta::with(['clienteMarket', 'vendedor', 'usuario']);

        if ($request->has('cliente_market_id')) {
            $query->where('cliente_market_id', $request->cliente_market_id);
        }

        if ($request->has('tipo_venta')) {
            $query->where('tipo_venta', $request->tipo_venta);
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('fecha_desde')) {
            $query->whereDate('fecha_venta', '>=', $request->fecha_desde);
        }

        $perPage = $request->get('per_page', 15);
        $ventas = $query->orderBy('fecha_venta', 'desc')->paginate($perPage);
        return response()->json($ventas, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'numero_factura' => 'required|string|max:50|unique:ventas,numero_factura',
            'tipo_venta' => 'required|string|max:20',
            'pedido_id' => 'nullable|exists:pedidos,id',
            'despacho_id' => 'nullable|exists:despachos,id',
            'usuario_id' => 'required|exists:usuarios,id',
            'subtotal' => 'required|numeric|min:0',
            'impuesto' => 'nullable|numeric|min:0',
            'descuento' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'forma_pago' => 'nullable|string|in:contado,credito',
            'estado' => 'nullable|string|in:completada,anulada',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.lote_id' => 'required|exists:lotes,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.subtotal' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $venta = Venta::create([
                'numero_factura' => $request->numero_factura,
                'tipo_venta' => $request->tipo_venta,
                'pedido_id' => $request->pedido_id,
                'despacho_id' => $request->despacho_id,
                'cliente_market_id' => $request->cliente_market_id,
                'paciente_id' => $request->paciente_id,
                'vendedor_id' => $request->vendedor_id,
                'usuario_id' => $request->usuario_id,
                'subtotal' => $request->subtotal,
                'impuesto' => $request->impuesto ?? 0,
                'descuento' => $request->descuento ?? 0,
                'total' => $request->total,
                'forma_pago' => $request->forma_pago ?? 'contado',
                'estado' => $request->estado ?? 'completada',
                'observaciones' => $request->observaciones
            ]);

            foreach ($request->detalles as $detalle) {
                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $detalle['producto_id'],
                    'lote_id' => $detalle['lote_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $detalle['subtotal']
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Venta creada', 'data' => $venta->load('detalles')], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $venta = Venta::with(['clienteMarket', 'vendedor', 'detalles.producto', 'detalles.lote'])->find($id);
        if (!$venta) {
            return response()->json(['success' => false, 'message' => 'Venta no encontrada'], 404);
        }
        return response()->json(['success' => true, 'data' => $venta], 200);
    }

    public function update(Request $request, $id)
    {
        $venta = Venta::find($id);
        if (!$venta) {
            return response()->json(['success' => false, 'message' => 'Venta no encontrada'], 404);
        }

        $venta->update($request->only(['estado', 'observaciones']));
        return response()->json(['success' => true, 'message' => 'Venta actualizada', 'data' => $venta], 200);
    }

    public function destroy($id)
    {
        $venta = Venta::find($id);
        if (!$venta) {
            return response()->json(['success' => false, 'message' => 'Venta no encontrada'], 404);
        }

        $venta->update(['estado' => 'anulada']);
        return response()->json(['success' => true, 'message' => 'Venta anulada'], 200);
    }

    /**
     * Exportar ventas a Excel
     */
    public function exportExcel()
    {
        return Excel::download(new VentasExport, 'ventas_' . date('Y-m-d') . '.xlsx');
    }
}
