<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MovimientoInventarioController extends Controller
{
    public function index(Request $request)
    {
        $query = MovimientoInventario::with(['producto', 'lote', 'bodega', 'usuario']);

        if ($request->has('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }

        if ($request->has('lote_id')) {
            $query->where('lote_id', $request->lote_id);
        }

        if ($request->has('bodega_id')) {
            $query->where('bodega_id', $request->bodega_id);
        }

        if ($request->has('tipo_movimiento')) {
            $query->where('tipo_movimiento', $request->tipo_movimiento);
        }

        if ($request->has('fecha_desde')) {
            $query->whereDate('fecha_movimiento', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta')) {
            $query->whereDate('fecha_movimiento', '<=', $request->fecha_hasta);
        }

        $perPage = $request->get('per_page', 20);
        $movimientos = $query->orderBy('fecha_movimiento', 'desc')->paginate($perPage);
        return response()->json($movimientos, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'producto_id' => 'required|exists:productos,id',
            'lote_id' => 'nullable|exists:lotes,id',
            'bodega_id' => 'nullable|exists:bodegas,id',
            'tipo_movimiento' => 'required|string|max:30',
            'cantidad' => 'required|integer',
            'referencia_id' => 'nullable|integer',
            'referencia_tipo' => 'nullable|string|max:50',
            'costo_unitario' => 'nullable|numeric|min:0',
            'valor_total' => 'nullable|numeric',
            'stock_anterior' => 'required|integer',
            'stock_posterior' => 'required|integer',
            'usuario_id' => 'required|exists:usuarios,id',
            'observaciones' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $movimiento = MovimientoInventario::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Movimiento registrado exitosamente',
            'data' => $movimiento->load(['producto', 'lote', 'bodega'])
        ], 201);
    }

    public function show($id)
    {
        $movimiento = MovimientoInventario::with(['producto', 'lote', 'bodega', 'usuario'])->find($id);
        if (!$movimiento) {
            return response()->json(['success' => false, 'message' => 'Movimiento no encontrado'], 404);
        }
        return response()->json(['success' => true, 'data' => $movimiento], 200);
    }

    public function kardexProducto($productoId, Request $request)
    {
        $query = MovimientoInventario::with(['lote', 'bodega', 'usuario'])
            ->where('producto_id', $productoId);

        if ($request->has('fecha_desde')) {
            $query->whereDate('fecha_movimiento', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta')) {
            $query->whereDate('fecha_movimiento', '<=', $request->fecha_hasta);
        }

        $movimientos = $query->orderBy('fecha_movimiento', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $movimientos,
            'total_movimientos' => $movimientos->count()
        ], 200);
    }

    public function kardexLote($loteId, Request $request)
    {
        $query = MovimientoInventario::with(['producto', 'bodega', 'usuario'])
            ->where('lote_id', $loteId);

        if ($request->has('fecha_desde')) {
            $query->whereDate('fecha_movimiento', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta')) {
            $query->whereDate('fecha_movimiento', '<=', $request->fecha_hasta);
        }

        $movimientos = $query->orderBy('fecha_movimiento', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $movimientos,
            'total_movimientos' => $movimientos->count()
        ], 200);
    }

    public function resumenPorTipo(Request $request)
    {
        $query = MovimientoInventario::query();

        if ($request->has('fecha_desde')) {
            $query->whereDate('fecha_movimiento', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta')) {
            $query->whereDate('fecha_movimiento', '<=', $request->fecha_hasta);
        }

        $resumen = $query->selectRaw('tipo_movimiento, COUNT(*) as total_movimientos, SUM(cantidad) as total_cantidad')
            ->groupBy('tipo_movimiento')
            ->get();

        return response()->json(['success' => true, 'data' => $resumen], 200);
    }
}
