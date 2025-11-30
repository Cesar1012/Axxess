<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Compra;
use App\Models\DetalleCompra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

/**
 * @group Compras
 * 
 * API para gestionar compras a proveedores
 */
class CompraController extends Controller
{
    public function index(Request $request)
    {
        $query = Compra::with(['proveedor', 'usuario']);

        if ($request->has('proveedor_id')) {
            $query->where('proveedor_id', $request->proveedor_id);
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('fecha_desde')) {
            $query->whereDate('fecha_compra', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta')) {
            $query->whereDate('fecha_compra', '<=', $request->fecha_hasta);
        }

        $perPage = $request->get('per_page', 15);
        $compras = $query->orderBy('fecha_compra', 'desc')->paginate($perPage);

        return response()->json($compras, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'numero_compra' => 'required|string|max:50|unique:compras,numero_compra',
            'proveedor_id' => 'required|exists:proveedores,id',
            'usuario_id' => 'required|exists:usuarios,id',
            'fecha_compra' => 'required|date',
            'numero_factura' => 'nullable|string|max:100',
            'subtotal' => 'required|numeric|min:0',
            'impuesto' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'estado' => 'nullable|string|in:pendiente,aprobada,recibida,cancelada',
            'observaciones' => 'nullable|string',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.subtotal' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $compra = Compra::create([
                'numero_compra' => $request->numero_compra,
                'proveedor_id' => $request->proveedor_id,
                'usuario_id' => $request->usuario_id,
                'fecha_compra' => $request->fecha_compra,
                'numero_factura' => $request->numero_factura,
                'subtotal' => $request->subtotal,
                'impuesto' => $request->impuesto ?? 0,
                'total' => $request->total,
                'estado' => $request->estado ?? 'pendiente',
                'observaciones' => $request->observaciones
            ]);

            foreach ($request->detalles as $detalle) {
                DetalleCompra::create([
                    'compra_id' => $compra->id,
                    'producto_id' => $detalle['producto_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $detalle['subtotal']
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Compra creada exitosamente',
                'data' => $compra->load(['proveedor', 'detalles.producto'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la compra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $compra = Compra::with([
            'proveedor',
            'usuario',
            'detalles.producto'
        ])->find($id);

        if (!$compra) {
            return response()->json([
                'success' => false,
                'message' => 'Compra no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $compra
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $compra = Compra::find($id);

        if (!$compra) {
            return response()->json([
                'success' => false,
                'message' => 'Compra no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'estado' => 'nullable|string|in:pendiente,aprobada,recibida,cancelada',
            'observaciones' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $compra->update($request->only(['estado', 'observaciones']));

        return response()->json([
            'success' => true,
            'message' => 'Compra actualizada exitosamente',
            'data' => $compra->load(['proveedor', 'detalles.producto'])
        ], 200);
    }

    public function destroy($id)
    {
        $compra = Compra::find($id);

        if (!$compra) {
            return response()->json([
                'success' => false,
                'message' => 'Compra no encontrada'
            ], 404);
        }

        if ($compra->estado === 'recibida') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar una compra recibida'
            ], 400);
        }

        $compra->update(['estado' => 'cancelada']);

        return response()->json([
            'success' => true,
            'message' => 'Compra cancelada exitosamente'
        ], 200);
    }
}
